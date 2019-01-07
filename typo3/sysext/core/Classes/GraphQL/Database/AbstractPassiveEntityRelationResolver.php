<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\GraphQL\Database;

use GraphQL\Type\Definition\ResolveInfo;
use TYPO3\CMS\Core\Configuration\MetaModel\ActiveEntityRelation;
use TYPO3\CMS\Core\Configuration\MetaModel\ActivePropertyRelation;
use TYPO3\CMS\Core\Configuration\MetaModel\PropertyDefinition;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\GraphQL\AbstractEntityRelationResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Webmozart\Assert\Assert;

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

abstract class AbstractPassiveEntityRelationResolver extends AbstractEntityRelationResolver
{
    /**
     * @var array
     */
    protected $keys = [];

    /**
     * @var array
     */
    protected $result = null;

    public function collect($source, ResolveInfo $info)
    {
        if ($source !== null) {
            Assert::keyExists($source, 'uid');
            $this->keys[] = $source['uid'];
        }
    }

    public function resolve($source, ResolveInfo $info): array
    {
        if ($this->result === null) {
            $this->result = [];

            $builder = $this->getBuilder($info);
            $statement = $builder->execute();

            while ($row = $statement->fetch()) {
                $row['__table'] = $this->getType($row);

                $this->fetchData($row);
            }
        }

        return $this->result[$source['uid']] ?? [];
    }

    protected abstract function getType(array $source): string;

    protected abstract function getTable(): string;

    protected abstract function getForeignKeyField(): string;

    protected function fetchData(array $row)
    {
        $this->result[$row[$this->getForeignKeyField()]][] = $row;
    }

    protected function getBuilder(ResolveInfo $info)
    {
        $table = $this->getTable();
        $builder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);

        $builder->getRestrictions()
            ->removeAll();

        $builder->select(...$this->getColumns($builder, $info))
            ->from($table);

        $condition = $this->getCondition($builder, $info);

        if (!empty($condition)) {
            $builder->where(...$condition);
        }

        return $builder;
    }

    protected function getCondition(QueryBuilder $builder, ResolveInfo $info)
    {
        $condition = GeneralUtility::makeInstance(FilterProcessor::class, $info, $builder)->process();
        $condition = $condition !== null ? [$condition] : [];

        $propertyConfiguration = $this->getPropertyDefinition()->getConfiguration();

        $condition[] = $builder->expr()->in(
            $this->getForeignKeyField(),
            $builder->createNamedParameter($this->keys, Connection::PARAM_INT_ARRAY)
        );

        return $condition;
    }

    protected function getColumns(QueryBuilder $builder, ResolveInfo $info)
    {
        $columns = [];

        foreach ($info->fieldNodes[0]->selectionSet->selections as $selection) {
            if ($selection->kind === 'Field') {
                $columns[] = $selection->name->value;
            }

            if ($selection->kind === 'InlineFragment') {
                foreach ($selection->selectionSet->selections as $inlineSelection) {
                    if ($inlineSelection->kind === 'Field') {
                        $columns[] = $selection->typeCondition->name->value . '.' . $inlineSelection->name->value;
                    }
                }
            }
        }

        $foreignKeyField = $this->getForeignKeyField();

        if ($foreignKeyField) {
            $columns[] = $foreignKeyField;
        }

        return $columns;
    }
}