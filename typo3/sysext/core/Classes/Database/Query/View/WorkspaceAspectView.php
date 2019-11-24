<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Database\Query\View;

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
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\ColumnIdentifierCollection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\TableIdentifier;
use TYPO3\CMS\Core\Database\Query\VersionMap;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class WorkspaceAspectView implements QueryViewInterface
{
    private const PARAMETER_PREFIX = ':_';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var WorkspaceAspect
     */
    private $workspaceAspect;

    /**
     * Initializes a new WorkspaceAspectView.
     * 
     * @param Connection $connection
     * @param WorkspaceAspect $workspaceAspect
     */
    public function __construct(Connection $connection, WorkspaceAspect $workspaceAspect)
    {
        $this->connection = $connection;
        $this->workspaceAspect = $workspaceAspect;
    }

    /**
     * @inheritdoc
     */
    public function buildQuery(TableIdentifier $tableIdentifier, ?ColumnIdentifierCollection $columnIdentifiers): ?QueryBuilder
    {
        $tableName = $tableIdentifier->getTableName();
        $workspaceId = (int) $this->workspaceAspect->getId();

        if (!$this->hasAspect($tableName)) {
            return null;
        }

        $queryBuilder = $this->getQueryBuilder()
            ->from($tableName);

        $queryBuilder
            ->getRestrictions()
            ->removeAll();

        $this->project($tableName, $columnIdentifiers, $queryBuilder);

        $queryBuilder
            ->leftJoin(
                $tableName,
                $tableName,
                'version',
                (string) $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(
                        $tableName . '.uid',
                        $queryBuilder->quoteIdentifier('version.t3ver_oid')
                    ),
                    $queryBuilder->expr()->eq(
                        $tableName . '.t3ver_wsid',
                        $queryBuilder->createNamedParameter(
                            0,
                            \PDO::PARAM_INT,
                            self::PARAMETER_PREFIX . md5('workspaceLive')
                        )
                    ),
                    $queryBuilder->expr()->eq(
                        'version.t3ver_wsid',
                        $queryBuilder->createNamedParameter(
                            $workspaceId,
                            \PDO::PARAM_INT,
                            self::PARAMETER_PREFIX . md5('workspaceContext')
                        )
                    )
                )
            )
            ->leftJoin(
                $tableName,
                $tableName,
                'original',
                (string) $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(
                        $tableName . '.t3ver_oid',
                        $queryBuilder->quoteIdentifier('original.uid')
                    ),
                    $queryBuilder->expr()->eq(
                        $tableName . '.t3ver_wsid',
                        $queryBuilder->createNamedParameter(
                            $workspaceId,
                            \PDO::PARAM_INT,
                            self::PARAMETER_PREFIX . md5('workspaceContext')
                        )
                    ),
                    $queryBuilder->expr()->in(
                        'original.t3ver_wsid',
                        $queryBuilder->createNamedParameter(
                            [0, $workspaceId],
                            Connection::PARAM_INT_ARRAY,
                            self::PARAMETER_PREFIX . md5('workspaceIdentifiers')
                        )
                    )
                )
            )
            ->leftJoin(
                $tableName,
                $tableName,
                'placeholder',
                (string) $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(
                        $tableName . '.t3ver_oid',
                        $queryBuilder->quoteIdentifier('placeholder.t3ver_move_id')
                    ),
                    $queryBuilder->expr()->neq(
                        'placeholder.t3ver_wsid',
                        $queryBuilder->createNamedParameter(
                            0,
                            \PDO::PARAM_INT,
                            self::PARAMETER_PREFIX . md5('workspaceLive')
                        )
                    ),
                    $queryBuilder->expr()->eq(
                        $tableName . '.t3ver_wsid',
                        $queryBuilder->createNamedParameter(
                            $workspaceId,
                            \PDO::PARAM_INT,
                            self::PARAMETER_PREFIX . md5('workspaceContext')
                        )
                    ),
                    $queryBuilder->expr()->in(
                        'placeholder.t3ver_wsid',
                        $queryBuilder->createNamedParameter(
                            [0, $workspaceId],
                            Connection::PARAM_INT_ARRAY,
                            self::PARAMETER_PREFIX . md5('workspaceIdentifiers')
                        )
                    )
                )
            )
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq(
                            $tableName . '.t3ver_wsid',
                            $queryBuilder->createNamedParameter(
                                0,
                                \PDO::PARAM_INT,
                                self::PARAMETER_PREFIX . md5('workspaceLive')
                            )
                        ),
                        $queryBuilder->expr()->isNull(
                            'version.uid'
                        )
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq(
                            $tableName . '.t3ver_wsid',
                            $queryBuilder->createNamedParameter(
                                $workspaceId,
                                \PDO::PARAM_INT,
                                self::PARAMETER_PREFIX . md5('workspaceContext')
                            )
                        ),
                        $queryBuilder->expr()->notIn(
                            $tableName . '.t3ver_state',
                            $queryBuilder->createNamedParameter(
                                [1, 3],
                                Connection::PARAM_INT_ARRAY,
                                self::PARAMETER_PREFIX . md5('workspaceStates')
                            )
                        )
                    )
                )
            );

        return $queryBuilder;
    }

    private function project(string $tableName, ?ColumnIdentifierCollection $columnIdentifiers, QueryBuilder $queryBuilder): QueryBuilder
    {
        $fieldNames = [];
        // As long as we do not have all columns used in the 
        // outer query we have to project them all.
        if ($columnIdentifiers === null) {
            $fieldNames = array_map(function ($tableColumn) {
                return $tableColumn->getName();
            }, GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable($tableName)
                ->getSchemaManager()
                ->listTableDetails($tableName)
                ->getColumns()
            );
        }

        if ($this->hasAspect($tableName)) {
            $fieldLiterals = [
                'uid' => [
                    'literal' => 'COALESCE(%s,%s)',
                    'identifiers' => ['original.uid', $tableName . '.uid'],
                ],
                'pid' => [
                    'literal' => 'COALESCE(%s,%s,%s)',
                    'identifiers' => ['placeholder.pid', 'original.pid', $tableName . '.pid'],
                ],
                'deleted' => [
                    'literal' => 'CASE WHEN %s = 2 THEN 1 ELSE %s END',
                    'identifiers' => [$tableName . '.t3ver_state', $tableName . '.deleted'],
                ],
                'sorting' => [
                    'literal' => 'COALESCE(%s,%s)',
                    'identifiers' => ['placeholder.sorting', $tableName . '.sorting'],
                ],
            ];

            foreach ($fieldNames as $fieldName) {
                if (isset($fieldLiterals[$fieldName])) {
                    $queryBuilder->addSelectLiteral(
                        sprintf(
                            $fieldLiterals[$fieldName]['literal'] 
                                . ' AS ' . $queryBuilder->quoteIdentifier($fieldName),
                            ...array_map(function($identifier) use ($queryBuilder) {
                                return $queryBuilder->quoteIdentifier($identifier);
                            }, $fieldLiterals[$fieldName]['identifiers'])
                        )
                    );
                } else {
                    $queryBuilder->addSelect($tableName . '.' . $fieldName);
                }
            }
        } else {
            foreach ($fieldNames as $fieldName) {
                $queryBuilder->addSelect($tableName . '.' . $fieldName);
            }
        }

        return $queryBuilder;
    }

    private function hasAspect(string $tableName): bool
    {
        return isset($GLOBALS['TCA'][$tableName]['ctrl']['versioningWS']);
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(QueryBuilder::class, $this->connection);
    }
}
