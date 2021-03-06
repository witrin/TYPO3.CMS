<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\GraphQL\Database;

use GraphQL\Type\Definition\ResolveInfo;
use TYPO3\CMS\Core\Configuration\MetaModel\ActivePropertyRelation;
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

class PassiveOneToManyEntityRelationResolver extends AbstractPassiveEntityRelationResolver
{

    public static function canResolve(PropertyDefinition $propertyDefinition)
    {
        if ($propertyDefinition->isManyToManyRelationProperty()) {
            return false;
        }

        foreach ($propertyDefinition->getActiveRelations() as $activeRelation) {
            if (!($activeRelation instanceof ActivePropertyRelation)) {
                return false;
            }
        }

        return true;
    }

    protected function fetchData(array $row, array $data): array
    {
        $data = parent::fetchData($row, $data);

        $propertyConfiguration = $this->getPropertyDefinition()->getConfiguration();

        if (isset($propertyConfiguration['config']['symmetric_field'])) {
            $data[$row[$propertyConfiguration['config']['symmetric_field']]][] = $row;
        }

        return $data;
    }

    protected function getTable(): string
    {
        return reset($this->getPropertyDefinition()->getRelationTableNames());
    }

    protected function getType(array $source): string
    {
        return reset($this->getPropertyDefinition()->getRelationTableNames());
    }

    protected function getForeignKeyField(): string
    {
        Assert::count($this->getPropertyDefinition()->getActiveRelations(), 1);

        $activeRelation = reset($this->getPropertyDefinition()->getActiveRelations());

        Assert::isInstanceOf($activeRelation, ActivePropertyRelation::class);

        return $activeRelation->getTo()->getName();
    }

    protected function getCondition(array $keys, QueryBuilder $builder, ResolveInfo $info): array
    {
        $condition = parent::getCondition($keys, $builder, $info);

        $propertyConfiguration = $this->getPropertyDefinition()->getConfiguration();

        if (isset($propertyConfiguration['config']['foreign_table_field'])) {
            $condition[] = $builder->expr()->eq(
                $propertyConfiguration['config']['foreign_table_field'],
                $builder->createNamedParameter($this->getPropertyDefinition()->getEntityDefinition()->getName())
            );

            if (isset($propertyConfiguration['config']['symmetric_field'])) {
                $condition[] = $builder->expr()->andX(
                    array_pop($condition),
                    $builder->expr()->eq(
                        $propertyConfiguration['config']['symmetric_field'],
                        $builder->createNamedParameter($this->getPropertyDefinition()->getEntityDefinition()->getName())
                    )
                );
            }
        }

        foreach ($propertyConfiguration['config']['foreign_match_fields'] ?? [] as $field => $match) {
            $condition[] = $builder->expr()->eq($field, $builder->createNamedParameter($match));
        }

        return $condition;
    }
}