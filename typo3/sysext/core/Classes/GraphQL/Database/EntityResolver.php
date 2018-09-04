<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\GraphQL\Database;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ResolveInfo;
use TYPO3\CMS\Core\Configuration\MetaModel\EntityDefinition;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\GraphQL\AbstractEntityResolver;
use TYPO3\CMS\Core\GraphQL\Database\FilterProcessor;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

class EntityResolver extends AbstractEntityResolver
{

    public function resolve($source, ResolveInfo $info): array
    {
        return $this->getBuilder($info)
            ->execute()
            ->fetchAll();
    }

    protected function getBuilder(ResolveInfo $info)
    {
        $table = $this->getTable($info);

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
        return $condition !== null ? [$condition] : [];
    }

    protected function getTable(ResolveInfo $info)
    {
        return $this->getEntityDefinition()->getName();
    }

    protected function getColumns(QueryBuilder $builder, ResolveInfo $info)
    {
        $columns = ['uid'];

        foreach ($info->fieldNodes[0]->selectionSet->selections as $selection) {
            if ($selection->kind === 'Field' && $selection->name->value !== 'uid') {
                $columns[] = $selection->name->value;
            }
        }

        return $columns;
    }
}