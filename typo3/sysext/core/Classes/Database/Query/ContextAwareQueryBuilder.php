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
use TYPO3\CMS\Core\Database\Query\Restriction\ContextRestrictionContainer;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\QueryRestrictionContainerInterface;
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
            return $this->concreteQueryBuilder->execute();
        }

        if (!$this->restrictionContainer instanceof ContextRestrictionContainer) {
            // Set additional query restrictions
            $originalWhereConditions = $this->addAdditionalWhereConditions();
            $result = $this->concreteQueryBuilder->execute();
            // Restore the original query conditions in case the user keeps
            // on modifying the state.
            $this->concreteQueryBuilder->add('where', $originalWhereConditions, false);
        } else {
            // remove all restrictions, but keep DeletedRestriction (if defined)
            // @todo introduce ExistenceAwareRestriction (or something like this)
            $restrictions = clone $this->restrictionContainer;
            $this->restrictionContainer->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            // Set additional query restrictions
            $originalWhereConditions = $this->addAdditionalWhereConditions();
            $result = new ContextAwareStatement($this->concreteQueryBuilder->execute(), $this, $this->context);
            $this->concreteQueryBuilder->add('where', $originalWhereConditions, false);
        }

        return $result;
    }

    /**
     * Gets the complete SQL string formed by the current specifications of this QueryBuilder.
     *
     * If the statement is a SELECT TYPE query restrictions based on TCA settings will
     * automatically be applied based on the current QuerySettings.
     *
     * @return string The SQL query string.
     */
    public function getSQL(): string
    {
        if ($this->getType() !== \Doctrine\DBAL\Query\QueryBuilder::SELECT) {
            return $this->concreteQueryBuilder->getSQL();
        }

        // Set additional query restrictions
        $originalWhereConditions = $this->addAdditionalWhereConditions();

        $sql = $this->concreteQueryBuilder->getSQL();

        // Restore the original query conditions in case the user keeps
        // on modifying the state.
        $this->concreteQueryBuilder->add('where', $originalWhereConditions, false);

        return $sql;
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