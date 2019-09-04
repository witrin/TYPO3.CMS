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
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\QueryRestrictionContainerInterface;
use TYPO3\CMS\Core\Database\Query\Restriction\RecordRestrictionInterface;
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
     * @var SelectIdentifierCollection
     */
    private $selectIdentifierCollection;

    /**
     * @var TableIdentifier
     */
    private $tableIdentifier;

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

    public function select(string ...$selects): QueryBuilder
    {
        $this->selectIdentifierCollection = SelectIdentifierCollection::fromExpressions(...$selects);
        return parent::select(...$selects);
    }

    public function from(string $from, string $alias = null): QueryBuilder
    {
        $this->tableIdentifier = TableIdentifier::create($from, $alias);
        return parent::from($from, $alias);
    }

    /**
     * Executes this query using the bound parameters and their types.
     *
     * @return \Doctrine\DBAL\Driver\Statement|ContextAwareStatement|int
     */
    public function execute()
    {
        if ($this->getType() !== \Doctrine\DBAL\Query\QueryBuilder::SELECT) {
            return parent::execute();
        }
        if (!$this->context->hasAspect('workspace') || !$this->context->hasAspect('language')) {
            return parent::execute();
        }

        // @todo not sure whether this "unquote" is correct, otherwise collect in 'from()'
        $tableName = $this->tableIdentifier->getTableName();

        if ($this->context->hasAspect('workspace')) {
            $workspaceResolver = new \TYPO3\CMS\Core\Database\Query\Context\WorkspaceAspectResolver(
                $this->connection,
                $this->context->getAspect('workspace'),
                $tableName
            );

            $originalWhereConditions = $this->concreteQueryBuilder->getQueryPart('where');
            $this->concreteQueryBuilder->andWhere(
                $this->expr()->in(
                    'uid',
                    $this->concreteQueryBuilder->createNamedParameter($workspaceResolver->getUids(), Connection::PARAM_INT_ARRAY)
                )
            );

            $additionalIdentifiers = $this->appendAdditionalSelects('uid', 'pid');

            // @todo Why are there three restrictions for workspaces?!
            $this->addAdditionalWhereConditions(
                \TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction::class,
                \TYPO3\CMS\Core\Database\Query\Restriction\FrontendWorkspaceRestriction::class,
                \TYPO3\CMS\Core\Database\Query\Restriction\BackendWorkspaceRestriction::class
            );
            $result = GeneralUtility::makeInstance(
                ContextAwareStatement::class,
                $additionalIdentifiers,
                $this->concreteQueryBuilder->execute(),
                $this->context,
                $this->tableIdentifier,
                $this->restrictionContainer,
                $workspaceResolver->getVersionMap()
            );
            $this->concreteQueryBuilder->add('where', $originalWhereConditions, false);
        }

        return $result;
    }

    private function appendAdditionalSelects(string ...$fieldNames): array
    {
        $additionalSelects = [];
        $additionalIdentifiers = [];
        foreach ($fieldNames as $fieldName) {
            if ($this->selectIdentifierCollection->hasFieldName($this->tableIdentifier, $fieldName)) {
                continue;
            }
            $alias = md5(uniqid($fieldName, true));
            $additionalIdentifiers[$fieldName] = $alias;
            $additionalSelects[$alias] = sprintf(
                '%s.%s AS %s',
                $this->tableIdentifier->getAlias() ?? $this->tableIdentifier->getTableName(),
                $fieldName,
                $alias
            );
        }
        if (count($additionalSelects) === 0) {
            return [];
        }
        $this->concreteQueryBuilder->add(
            'select',
            $this->quoteIdentifiersForSelect(array_values($additionalSelects)),
            true
        );
        return $additionalIdentifiers;
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
