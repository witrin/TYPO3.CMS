<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\GraphQL\Database;

use GraphQL\Type\Definition\ResolveInfo;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\GraphQL\FilterParser;

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

class FilterProcessor
{
    protected const OPERATOR_MAPPING =  [
        'and' => 'andX',
        'or' => 'orX',
        'in' => 'in',
        '=' => 'eq',
        '!=' => 'neq',
        '<' => 'lt',
        '>' => 'gt',
        '<=' => 'gte',
        '>=' => 'lte'
    ];

    protected const NEGATED_OPERATOR_MAPPING = [
        'and' => 'orX',
        'or' => 'andX',
        'in' => 'notIn',
        '=' => 'neq',
        '!=' => 'eq',
        '<' => 'gte',
        '>' => 'lte',
        '<=' => 'lt',
        '>=' => 'gt'
    ];

    protected $info = null;

    protected $builder = null;

    protected $node = null;

    public function __construct(ResolveInfo $info, QueryBuilder $builder)
    {
        $this->info = $info;
        $this->builder = $builder;

        foreach ($this->info->operation->selectionSet->selections[0]->arguments as $argument) {
            if ($argument->name->value === 'filter') {
                $this->node = $argument->value->kind === 'StringValue'
                    ? FilterParser::parse($argument->value->value) : $argument->value->value;
                break;
            }
        }
    }

    public function process()
    {
        return $this->node !== null ? $this->processPredicate($this->node) : null;
    }

    protected function processPredicate($node, $negate = false)
    {
        $operator = $negate ? self::NEGATED_OPERATOR_MAPPING : self::OPERATOR_MAPPING;

        if ($node['right']['type'] === 'none' && ($node['operator'] === '=' || $node['operator'] === '!=')) {
            return $this->builder->expr()->{$node['operator'] === '=' && !$negate ? 'isNull' : 'isNotNull'}(
                $this->processPath($node['left'])
            );
        } else if (array_key_exists($node['operator'], $operator)) {
            if ($node['operator'] === 'and' || $node['operator'] === 'or') {
                return $this->builder->expr()->{$operator[$node['operator']]}(
                    $this->{'process'.ucfirst($node['left']['type'])}($node['left'], $negate),
                    $this->{'process'.ucfirst($node['right']['type'])}($node['right'], $negate)
                );
            } else {
                return $this->builder->expr()->{$operator[$node['operator']]}(
                    $this->processPath($node['left']),
                    $this->{'process'.ucfirst($node['right']['type'])}($node['right'])
                );
            }
        } else if($node['operator'] === 'not') {
            return $this->processPredicate($node['left'], true);
        } else {
            throw new \Exception('Unkown operand \'' . $node['operator'] . '\'');
        }
    }

    protected function processPath(array $node)
    {
        return implode('.', $node['segments']);
    }

    protected function processInteger(array $node)
    {
        return $this->builder->createNamedParameter($node['value'], \PDO::PARAM_INT);
    }

    protected function processString(array $node)
    {
        return $this->builder->createNamedParameter($node['value'], \PDO::PARAM_STR);
    }

    protected function processBoolean(array $node)
    {
        return $this->builder->createNamedParameter($node['value'], \PDO::PARAM_BOOL);
    }

    protected function processFloat(array $node)
    {
        return $this->builder->createNamedParameter($node['value'], \PDO::PARAM_STR);
    }

    protected function processNone(array $node)
    {
        return 'NULL';
    }

    protected function processParameter(array $node)
    {
        // ...
    }
}