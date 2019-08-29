<?php
declare(strict_types = 1);
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

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\QueryRestrictionContainerInterface;
use TYPO3\CMS\Core\Database\Query\Restriction\RecordRestrictionInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Object oriented approach to building SQL queries.
 *
 * This is an advanced query over the simple Doctrine DBAL / TYPO3 QueryBuilder by taking into account the
 * context - that is next to enableFields the proper fetching of resolved langauge and workspace records, if given.
 *
 *
 * For this to work, the concept of map/reduce is applied. Example:
 *
 * - Fetch all records that match live and workspace ID
 * - Fetch all records that match the target language + the fallback records
 *
 * Then filter out the criteria that match based on enableFields + "overlays".
 */
class ContextAwareQueryBuilder extends QueryBuilder
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var QueryBuilder[]
     */
    private $queryBuilders = [];

    /**
     * Initializes a new QueryBuilder.
     *
     * @param Connection $connection The DBAL Connection.
     * @param Context $context
     * @param QueryRestrictionContainerInterface $restrictionContainer
     * @param \Doctrine\DBAL\Query\QueryBuilder $concreteQueryBuilder
     * @param array $additionalRestrictions
     */
    public function __construct(
        Connection $connection,
        Context $context,
        QueryRestrictionContainerInterface $restrictionContainer = null,
        \Doctrine\DBAL\Query\QueryBuilder $concreteQueryBuilder = null,
        array $additionalRestrictions = null
    ) {
        parent::__construct($connection, $restrictionContainer, $concreteQueryBuilder, $additionalRestrictions);
        $this->context = $context;
    }

    /**
     * Executes this query using the bound parameters and their types.
     *
     * @return \Doctrine\DBAL\Driver\Statement|ContextAwareStatement|int
     */
    public function execute()
    {
        if ($this->getType() !== \Doctrine\DBAL\Query\QueryBuilder::SELECT) {
            return parent::execute();
        }
        if (!$this->context->hasAspect('workspace') || !$this->context->hasAspect('language')) {
            return parent::execute();
        }

        // @todo not sure whether this "unquote" is correct, otherwise collect in 'from()'
        $tableName = $this->unquoteSingleIdentifier($this->concreteQueryBuilder->getQueryPart('from')[0]['table']);
        $workspaceResult = $this->resolveWorkspaceAspect($tableName);

        if ($workspaceResult !== null) {
            $originalWhereConditions = $this->concreteQueryBuilder->getQueryPart('where');
            $this->concreteQueryBuilder->andWhere(
                $this->expr()->in('uid', $this->createNamedParameter($workspaceResult['uids'], Connection::PARAM_INT_ARRAY))
            );
            $this->addAdditionalWhereConditions();
            $result = new ContextAwareStatement($this->concreteQueryBuilder->execute(), $this->context, $tableName, $this->restrictionContainer, $workspaceResult['map']);
            $this->concreteQueryBuilder->add('where', $originalWhereConditions, false);
        }
/*


        // Set additional query restrictions
        $originalWhereConditions = $this->addAdditionalWhereConditions();
        $result = $this->concreteQueryBuilder->execute();
        // Restore the original query conditions in case the user keeps
        // on modifying the state.
        $this->concreteQueryBuilder->add('where', $originalWhereConditions, false);

        // Do post-restriction checks on per-record basis
        if ($this->restrictionContainer instanceof RecordRestrictionInterface) {
            $result = GeneralUtility::makeInstance(
                ContextAwareStatement::class,
                $result,
                $this->context,
                (string)$this->concreteQueryBuilder->getQueryPart('from'),
                $this->restrictionContainer
            );
        }
*/

        return $result;
    }

    private function resolveWorkspaceAspect(string $tableName): ?array
    {
        if (!$this->context->hasAspect('workspace')) {
            return null;
        }

        /** @var \TYPO3\CMS\Core\Context\WorkspaceAspect $workspaceAspect */
        $workspaceAspect = $this->context->getAspect('workspace');

        $subQueryBuilder = GeneralUtility::makeInstance(QueryBuilder::class, $this->connection);
        $subQueryBuilder->select('uid')->from($tableName)->andWhere(
            $subQueryBuilder->expr()->eq('t3ver_wsid', (int)$workspaceAspect->getId())
        );

        $queryBuilder = GeneralUtility::makeInstance(QueryBuilder::class, $this->connection);
        $queryBuilder->from($tableName)->select('uid', 't3ver_oid')->where(
            $queryBuilder->expr()->eq('t3ver_wsid', $queryBuilder->createNamedParameter($workspaceAspect->getId(), Connection::PARAM_INT)),
            $queryBuilder->expr()->notIn('t3ver_state', $queryBuilder->createNamedParameter([-1, 2, 3], Connection::PARAM_INT_ARRAY))
        );
        $versionRecords = $queryBuilder->execute()->fetchAll();
        $versionRecordIds = array_column($versionRecords, 'uid');
        $versionRecordPointerIds = array_column($versionRecordIds, 't3ver_oid');

        $queryBuilder = GeneralUtility::makeInstance(QueryBuilder::class, $this->connection);
        $queryBuilder->from($tableName)->select('uid')->where(
            $queryBuilder->expr()->eq('t3ver_wsid', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
            $queryBuilder->expr()->eq('t3ver_state', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
            $queryBuilder->expr()->notIn('uid', $queryBuilder->createNamedParameter($versionRecordPointerIds, Connection::PARAM_INT_ARRAY))
        );
        $liveRecords = $queryBuilder->execute()->fetchAll();
        $liveRecordIds = array_column($liveRecords, 'uid');

        $queryBuilder = GeneralUtility::makeInstance(QueryBuilder::class, $this->connection);
        $queryBuilder->from($tableName)->select('uid', 'pid')->where(
            $queryBuilder->expr()->in('uid', $queryBuilder->createNamedParameter($versionRecordPointerIds, Connection::PARAM_INT_ARRAY))
        );
        $liveMapRecords = $queryBuilder->execute()->fetchAll();
        $liveMapRecords = array_combine(
            array_map('intval', array_column($liveMapRecords, 'uid')),
            array_values($liveMapRecords)
        );

        if (count($versionRecords) !== 0) {
            $mapArray = array_combine(
                array_map('intval', $versionRecordIds),
                array_map(
                    function(array $versionRecord) {
                        return array_map('intval', $versionRecord);
                    },
                    $versionRecords
                )
            );

            $mapArray = array_map(
                function(array $map) use ($liveMapRecords) {
                    $pointerId = $map['t3ver_oid'];
                    $map['pid'] = $liveMapRecords[$pointerId]['pid'];
                    return $map;
                },
                $mapArray
            );
        }

        return [
            'uids' => array_merge($versionRecordIds, $liveRecordIds),
            'map' => VersionMap::fromArray($mapArray ?? [])
        ];
    }

    private function resolveLanguageAspect(string $tableName)
    {

    }

    /**
     * Deep clone of the QueryBuilder
     * @see \Doctrine\DBAL\Query\QueryBuilder::__clone()
     */
    public function __clone()
    {
        parent::__clone();;
        $this->context = clone $this->context;
    }
}
