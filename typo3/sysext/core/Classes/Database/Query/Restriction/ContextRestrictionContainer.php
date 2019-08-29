<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Database\Query\Restriction;

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
use TYPO3\CMS\Core\Context\RestrictionAwareInterface;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;

/**
 * This is the container with restrictions that are added based on the context
 */
class ContextRestrictionContainer extends AbstractRestrictionContainer implements RecordRestrictionInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * Creates instances of restrictions based on the Context
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        foreach ($context->getAllAspects() as $aspect) {
            if ($aspect instanceof RestrictionAwareInterface) {
                foreach ($aspect->resolveRestrictions() as $restriction) {
                    $this->add($restriction);
                }
            }
        }
    }

    /**
     * Main method to build expressions for given tables.
     * Iterating over all registered expressions and combine them with AND
     *
     * @param array $queriedTables Array of tables, where array key is table alias and value is a table name
     * @param ExpressionBuilder $expressionBuilder Expression builder instance to add restrictions with
     * @return CompositeExpression The result of query builder expression(s)
     */
    public function buildExpression(array $queriedTables, ExpressionBuilder $expressionBuilder): CompositeExpression
    {
        $constraints = [];
        foreach ($this->restrictions as $restriction) {
            // Result restricitons will be applied afterwards in isRecordResult
            if ($restriction instanceof RecordRestrictionInterface) {
                continue;
            }
            $constraints[] = $restriction->buildExpression($queriedTables, $expressionBuilder);
        }
        return $expressionBuilder->andX(...$constraints);
    }

    /**
     * Main method to filter if a record could be used.
     *
     * @param string $tableName
     * @param array $record
     * @return bool
     */
    public function isRecordRestricted(string $tableName, array $record): bool
    {
        foreach ($this->restrictions as $restriction) {
            if (!$restriction instanceof RecordRestrictionInterface) {
                continue;
            }
            if ($restriction->isRecordRestricted($tableName, $record)) {
                return true;
            }
        }
        return false;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
