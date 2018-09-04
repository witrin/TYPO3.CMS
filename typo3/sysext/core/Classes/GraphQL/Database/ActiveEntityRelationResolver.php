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

class ActiveEntityRelationResolver extends AbstractEntityRelationResolver
{
    /**
     * @var array
     */
    protected $keys = [];

    /**
     * @var array
     */
    protected $result = null;

    public static function canResolve(PropertyDefinition $propertyDefinition)
    {
        if ($propertyDefinition->isManyToManyRelationProperty()) {
            return false;
        }

        foreach ($propertyDefinition->getActiveRelations() as $activeRelation) {
            if (!($activeRelation instanceof ActiveEntityRelation)) {
                return false;
            }
        }

        return true;
    }

    public function collect($source, ResolveInfo $info)
    {
        if ($source !== null) {
            Assert::keyExists($source, $this->getPropertyDefinition()->getName());
            $this->keys = array_merge_recursive($this->keys, $this->getForeignKeys($source[$this->getPropertyDefinition()->getName()]));
        }
    }

    public function resolve($source, ResolveInfo $info): array
    {
        Assert::keyExists($source, $this->getPropertyDefinition()->getName());

        $result = [];

        if ($this->result === null) {
            $this->result = [];

            $foreignKeyField = $this->getForeignKeyField();

            foreach ($this->getPropertyDefinition()->getRelationTableNames() as $table) {
                $builder = $this->getBuilder($table, $info);
                $statement = $builder->execute();

                while ($row = $statement->fetch()) {
                    $row['__table'] = $table;
                    $this->result[$table][$row[$foreignKeyField]] = $row;
                }
            }
        }

        foreach ($this->getForeignKeys($source[$this->getPropertyDefinition()->getName()]) as $table => $foreignKeys) {
            foreach ($foreignKeys as $foreignKey) {
                $result[] = $this->result[$table][$foreignKey];
            }
        }

        return $result;
    }

    protected function getBuilder(string $table, ResolveInfo $info)
    {
        $builder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($table);

        $builder->getRestrictions()
            ->removeAll();

        $builder->select(...$this->getColumns($table, $builder, $info))
            ->from($table);

        $condition = $this->getCondition($table, $builder, $info);

        if (!empty($condition)) {
            $builder->where(...$condition);
        }

        return $builder;
    }

    protected function getCondition(string $table, QueryBuilder $builder, ResolveInfo $info)
    {
        $condition = GeneralUtility::makeInstance(FilterProcessor::class, $info, $builder)->process();
        $condition = $condition !== null ? [$condition] : [];

        $propertyConfiguration = $this->getPropertyDefinition()->getConfiguration();

        $condition[] = $builder->expr()->in(
            $this->getForeignKeyField(),
            $builder->createNamedParameter($this->keys[$table], Connection::PARAM_INT_ARRAY)
        );

        if (isset($propertyConfiguration['config']['foreign_table_field'])) {
            $condition[] = $builder->expr()->eq(
                $propertyConfiguration['config']['foreign_table_field'],
                $builder->createNamedParameter($this->getPropertyDefinition()->getEntityDefinition()->getName())
            );
        }

        foreach ($propertyConfiguration['config']['foreign_match_fields'] ?? [] as $field => $match) {
            $condition[] = $builder->expr()->eq($field, $builder->createNamedParameter($match));
        }

        return $condition;
    }

    /**
     * @todo Standard compliance.
     */
    protected function getColumns(string $table, QueryBuilder $builder, ResolveInfo $info)
    {
        $columns = [];

        foreach ($info->fieldNodes[0]->selectionSet->selections as $selection) {
            if ($selection->kind === 'Field') {
                $columns[] = $selection->name->value;
            }

            if ($selection->kind === 'InlineFragment' && $selection->typeCondition->name->value === $table) {
                foreach ($selection->selectionSet->selections as $selection) {
                    if ($selection->kind === 'Field') {
                        $columns[] = $selection->name->value;
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

    protected function getForeignKeyField()
    {
        return 'uid';
    }

    protected function getForeignKeys($commaSeparatedValues)
    {
        $foreignKeys = [];
        $defaultTable = reset($this->getPropertyDefinition()->getRelationTableNames());
        $commaSeparatedValues = array_unique($commaSeparatedValues ? explode(',', (string)$commaSeparatedValues) : []);

        foreach ($commaSeparatedValues as $commaSeparatedValue) {
            $separatorPosition = strrpos($commaSeparatedValue, '_');
            $table = $separatorPosition ? substr($commaSeparatedValue, 0, $separatorPosition) : $defaultTable;
            $foreignKeys[$table][] = substr($commaSeparatedValue, ($separatorPosition ?: -1) + 1);
        }

        return $foreignKeys;
    }
}