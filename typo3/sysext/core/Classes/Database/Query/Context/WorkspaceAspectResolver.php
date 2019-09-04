<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Database\Query\Context;

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

use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\VersionMap;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class WorkspaceAspectResolver
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var WorkspaceAspect
     */
    private $workspaceAspect;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var int[]
     */
    private $uids;

    /**
     * @var \TYPO3\CMS\Core\Database\Query\VersionMap
     */
    private $versionMap;

    public function __construct(Connection $connection, WorkspaceAspect $workspaceAspect, string $tableName)
    {
        $this->connection = $connection;
        $this->workspaceAspect = $workspaceAspect;
        $this->tableName = $tableName;

        $workspaceId = (int)$this->workspaceAspect->getId();
        if ($workspaceId === 0) {
            $this->resolveLive();
        } else {
            $this->resolveDraft($workspaceId);
        }
    }

    /**
     * @return mixed
     */
    public function getUids(): array
    {
        return $this->uids;
    }

    /**
     * @return VersionMap
     */
    public function getVersionMap(): VersionMap
    {
        return $this->versionMap;
    }

    private function resolveLive()
    {
        // @todo pid<>-1 is currently skipped here...
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->from($this->tableName)->select('uid', 't3ver_oid')->where(
            $queryBuilder->expr()->eq('t3ver_wsid', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
            $queryBuilder->expr()->eq('t3ver_state', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
        );
        $liveRecords = $queryBuilder->execute()->fetchAll();
        $liveRecordIds = $this->resolveIntegerColumns($liveRecords, 'uid');

        $this->uids = $liveRecordIds;
        $this->versionMap = VersionMap::fromArray([]);
    }

    private function resolveDraft(int $workspaceId)
    {
        $subQueryBuilder = $this->createQueryBuilder();
        $subQueryBuilder->select('uid')->from($this->tableName)->andWhere(
            $subQueryBuilder->expr()->eq('t3ver_wsid', $workspaceId)
        );

        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->from($this->tableName)->select('uid', 't3ver_oid', 't3ver_state')->where(
            $queryBuilder->expr()->eq('t3ver_wsid', $queryBuilder->createNamedParameter($workspaceId, Connection::PARAM_INT))
            // $queryBuilder->expr()->notIn('t3ver_state', $queryBuilder->createNamedParameter([1, 2, 3], Connection::PARAM_INT_ARRAY))
        );
        $allVersionRecords = $queryBuilder->execute()->fetchAll();

        $versionRecords = array_filter($allVersionRecords, function(array $record) {
            return !in_array((int)$record['t3ver_state'], [1, 2, 3], true);
        });
        $versionRecordIds = $this->resolveIntegerColumns($versionRecords, 'uid');
        $versionRecordPointerIds = $this->resolveIntegerColumns($versionRecords, 't3ver_oid');

        $placeHolderRecords = array_filter($allVersionRecords, function(array $record) {
            return in_array((int)$record['t3ver_state'], [1, 2, 3], true);
        });
        // @todo ...
        $placeHolderRecordPointerIds = $this->resolveIntegerColumns($placeHolderRecords, 't3ver_oid');

        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->from($this->tableName)->select('uid')->where(
            $queryBuilder->expr()->eq('t3ver_wsid', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
            $queryBuilder->expr()->eq('t3ver_state', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
            $queryBuilder->expr()->notIn('uid', $queryBuilder->createNamedParameter(array_merge($versionRecordPointerIds, $placeHolderRecordPointerIds), Connection::PARAM_INT_ARRAY))
        );
        $liveRecords = $queryBuilder->execute()->fetchAll();
        $liveRecordIds = $this->resolveIntegerColumns($liveRecords, 'uid');

        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder->from($this->tableName)->select('uid', 'pid')->where(
            $queryBuilder->expr()->in('uid', $queryBuilder->createNamedParameter($versionRecordPointerIds, Connection::PARAM_INT_ARRAY))
        );
        $liveMapRecords = $queryBuilder->execute()->fetchAll();
        $liveMapRecords = array_combine(
            $this->resolveIntegerColumns($liveMapRecords, 'uid'),
            array_values($liveMapRecords)
        );

        if (count($versionRecords) !== 0) {
            $mapArray = array_combine(
                $versionRecordIds,
                array_map(
                    function(array $versionRecord) {
                        unset($versionRecord['uid']);
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

        $this->uids = array_merge($versionRecordIds, $liveRecordIds);
        $this->versionMap = VersionMap::fromArray($mapArray ?? []);
    }

    private function resolveIntegerColumns(array $records, string $columnName): array
    {
        return array_map('intval', array_column($records, $columnName));
    }

    private function createQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(QueryBuilder::class, $this->connection);
    }
}
