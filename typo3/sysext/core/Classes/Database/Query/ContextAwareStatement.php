<?php
declare(strict_types=1);
namespace TYPO3\CMS\Core\Database\Query;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Driver\Statement;
use PDO;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Versioning\VersionState;

/**
 * Takes a Doctrine/DBAL statement and does certain "post-query" restriction checks in order to apply
 * - proper restriction checks after the records were loaded from the DB
 * - handles workspace and language overlays
 */
class ContextAwareStatement implements \IteratorAggregate, ResultStatement
{
    /**
     * Contains information on mapping and restrictions.
     *
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * The executed DBAL statement.
     *
     * @var Statement
     */
    protected $stmt;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \TYPO3\CMS\Core\Context\LanguageAspect
     */
    protected $languageAspect;

    /**
     * @var WorkspaceAspect
     */
    protected $workspaceAspect;

    public function __construct(Statement $stmt, QueryBuilder $queryBuilder, Context $context)
    {
        $this->stmt = $stmt;
        $this->queryBuilder = $queryBuilder;
        $this->context = $context;
        $this->languageAspect = $context->getAspect('language');
        $this->workspaceAspect = $context->getAspect('workspace');
    }

    /**
     * Closes the cursor, freeing the database resources used by this statement.
     *
     * @return bool TRUE on success, FALSE on failure.
     */
    public function closeCursor()
    {
        return $this->stmt->closeCursor();
    }

    /**
     * Returns the number of columns in the result set.
     *
     * @return int
     */
    public function columnCount()
    {
        return $this->stmt->columnCount();
    }

    /**
     * {@inheritdoc}
     */
    public function setFetchMode($fetchMode, $arg2 = null, $arg3 = null)
    {
        return $this->stmt->setFetchMode($fetchMode, $arg2, $arg3);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->stmt;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($fetchMode = null, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        do {
            $result = $this->stmt->fetch($fetchMode, $cursorOrientation, $cursorOffset);
            if (!is_array($result) || !is_object($result)) {
                return $result;
            }
            $result = $this->enrich($result, $fetchMode);
            $isRestricted = $this->queryBuilder->getRestrictions()->isRecordRestricted(
                $this->queryBuilder->getQueryPart('from'),
                $result
            );
        // fetch next item, since current one is restricted
        } while ($isRestricted);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($fetchMode = null, $fetchArgument = null, $ctorArgs = null)
    {
        return $this->stmt->fetchAll($fetchMode, $fetchArgument);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchColumn($columnIndex = 0)
    {
        return $this->stmt->fetchColumn($columnIndex);
    }

    /**
     * @param $result
     * @param null $fetchMode
     * @return array|object|\stdClass|bool returns FALSE if could not be enriched
     */
    protected function enrich($result, $fetchMode = null)
    {
        if (is_array($result)) {
            return $this->enrichArrayResult($result, $fetchMode);
        }
        if ($result instanceof \stdClass) {
            return $this->enrichObjectResult($result, $fetchMode);
        }
        return $this->enrichClassResult($result, $fetchMode);
    }

    /**
     * @param array $result
     * @param null $fetchMode
     * @return array
     */
    protected function enrichArrayResult(array $result, $fetchMode = null)
    {
        // todo consider NUMERIC & BOTH
        $result = $this->overlayLiveVersionWithPossibleVersionedRecord($result);
        return $result;
    }

    /**
     * @param \stdClass $result
     * @param null $fetchMode
     * @return \stdClass
     */
    protected function enrichObjectResult(\stdClass $result, $fetchMode = null)
    {
        return $result;
    }

    /**
     * @param object $result
     * @param null $fetchMode
     * @return object
     *
     * @see https://www.php.net/manual/en/pdostatement.fetch.php
     */
    protected function enrichClassResult($result, $fetchMode = null)
    {
        return $result;
    }


    /**
     * Find a workspace record for a live version.
     *
     * This is indicated by:
     * - A workspaceId was requested via the context
     * - We have the fields "uid", "pid", "t3ver_wsid" and "t3ver_state" in the result.
     * - The result has "t3ver_wsid" = 0, "pid" != -1
     *
     * We then fetch a full record of the offline version in this workspace via a custom SQL query looking for
     * - t3ver_oid = uid field from the result
     * - t3ver_wsid = requested workspaceId
     *
     * If nothing found, there is no offline version of this record.
     * If something was found, then
     * - we additionally set "_ORIG_uid" and "_ORIG_pid" the uid and pid from the versioned record.
     * - we apply the live UID and live PID from the live page on to the result array.
     *
     * If the t3ver_state is set to 2 (= deleted), then we return "false" immediately, indicating that this record
     * does not exist anymore.
     *
     *
     * TODO:
     * - move placeholder to move pointer resolving by evaluating "t3ver_state"
     *
     * @param array $result
     * @return mixed
     */
    protected function overlayLiveVersionWithPossibleVersionedRecord($result)
    {
        // If a live version is requested, nothing else is needed.
        if ($this->workspaceAspect->getId() === 0) {
            return $result;
        }

        // These fields are required to be set.
        if (!isset($result['uid'], $result['pid'], $result['t3ver_wsid'], $result['t3ver_state'])) {
            return $result;
        }

        $workspaceId = (int)$result['t3ver_wsid'];
        // This overlay only affects live versions, so do not touch other versions
        if ($workspaceId !== 0) {
            return $result;
        }

        $liveUid = (int)$result['uid'];
        $livePid = (int)$result['pid'];

        // Get the record for this workspace
        $qb = clone $this->queryBuilder;
        $table = $qb->getQueryPart('from');
        $qb->resetRestrictions();
        $qb->getRestrictions()->removeAll();
        // @todo what happens with "JOINs" - they might deliver more results...
        $workspaceVersion = $qb->where(
            $qb->expr()->eq($table . '.t3ver_oid', $liveUid),
            $qb->expr()->eq($table . '.t3ver_wsid', $this->workspaceAspect->getId())
        )->setMaxResults(1)->execute()->fetch();

        // No versioned record found, so this can be skipped
        if (!is_array($workspaceVersion)) {
            return $result;
        }

        $rowVersionState = VersionState::cast($workspaceVersion['t3ver_state'] ?? null);
        if (
            $rowVersionState->equals(VersionState::NEW_PLACEHOLDER)
            || $rowVersionState->equals(VersionState::DELETE_PLACEHOLDER)
        ) {
            // Unset record if it turned out to be deleted in workspace
            return false;
        }

        $result = $workspaceVersion;
        $result['_ORIG_uid'] = $workspaceVersion['uid'];
        $result['_ORIG_pid'] = $workspaceVersion['pid'];
        $result['uid'] = $liveUid;
        $result['pid'] = $livePid;
        return $result;
    }



}
