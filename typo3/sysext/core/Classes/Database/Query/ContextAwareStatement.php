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
use TYPO3\CMS\Core\Database\Query\Restriction\RecordRestrictionInterface;

/**
 * Takes a Doctrine/DBAL statement and does certain "post-query" restriction checks in order to apply
 * - proper restriction checks after the records were loaded from the DB
 * - handles workspace and language overlays
 */
class ContextAwareStatement implements ResultStatement, \IteratorAggregate
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

    public function __construct(Statement $stmt, QueryBuilder $queryBuilder)
    {
        $this->stmt = $stmt;
        $this->queryBuilder = $queryBuilder;
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
}
