<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\GraphQL\Database;

use GraphQL\Type\Definition\ResolveInfo;
use TYPO3\CMS\Core\Configuration\MetaModel\PropertyDefinition;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
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

class PassiveManyToManyEntityRelationResolver extends AbstractPassiveEntityRelationResolver
{

    public static function canResolve(PropertyDefinition $propertyDefinition)
    {
        return $propertyDefinition->isManyToManyRelationProperty();
    }

    protected function getTable(): string
    {
        return $this->getPropertyDefinition()->getManyToManyTableName();
    }

    protected function getType(array $source): string
    {
        Assert::keyExists($source, 'tablenames');
        return $source['tablenames'];
    }

    protected function getForeignKeyField(): string
    {
        return 'uid_local';
    }

    protected function getBuilder(ResolveInfo $info)
    {
        $builder = parent::getBuilder($info);

        $associativeTable = $this->getTable();

        foreach ($this->getPropertyDefinition()->getRelationTableNames() as $table) {
            $builder->leftJoin(
                $associativeTable,
                $table,
                $table,
                (string)$builder->expr()->andX(
                    $builder->expr()->eq(
                        $associativeTable . '.uid_foreign',
                        $builder->quoteIdentifier($table . '.uid')
                    ),
                    $builder->expr()->eq(
                        $associativeTable . '.tablenames',
                        $builder->createNamedParameter($table)
                    )
                )
            )->orderBy($associativeTable . '.sorting');
        }

        return $builder;
    }

    protected function getCondition(QueryBuilder $builder, ResolveInfo $info)
    {
        $condition = parent::getCondition($builder, $info);

        foreach ($propertyConfiguration['config']['MM_match_fields'] as $field => $match) {
            $condition[] = $builder->expr()->eq($field, $builder->createNamedParameter($match));
        }

        $condition[] = $builder->expr()->eq(
            $this->getTable() . '.fieldname',
            $builder->createNamedParameter($this->getPropertyDefinition()->getName())
        );

        return $condition;
    }

    protected function getColumns(QueryBuilder $builder, ResolveInfo $info)
    {
        $columns = parent::getColumns($builder, $info);

        $columns[] = $this->getTable() . '.tablenames';

        return $columns;
    }
}