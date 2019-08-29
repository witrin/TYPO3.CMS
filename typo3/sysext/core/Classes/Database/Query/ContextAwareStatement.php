<?php
declare(strict_types=1);
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


use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\FetchMode;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Query\Restriction\RecordRestrictionInterface;

/**
 * Takes a Doctrine/DBAL statement and does certain "post-query" restriction checks in order to apply
 * proper restriction checks after the records were loaded from the DB.
 */
class ContextAwareStatement implements \IteratorAggregate, ResultStatement
{
    /**
     * The executed DBAL statement.
     *
     * @var Statement
     */
    protected $stmt;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var string
     */
    protected $queriedTable;

    /**
     * @var RecordRestrictionInterface
     */
    protected $restriction;

    public function __construct(Statement $stmt, Context $context, string $queriedTable, RecordRestrictionInterface $restriction)
    {
        $this->stmt = $stmt;
        $this->context = $context;
        $this->queriedTable = $queriedTable;
        $this->restriction = $restriction;
    }

    /**
     * @inheritDoc
     */
    public function closeCursor()
    {
        return $this->stmt->closeCursor();
    }

    /**
     * @inheritDoc
     */
    public function columnCount()
    {
        return $this->stmt->columnCount();
    }

    /**
     * {@inheritdoc}
     */
    public function setFetchMode($fetchMode, $arg2 = null, $arg3 = null)
    {
        return $this->stmt->setFetchMode($fetchMode, $arg2, $arg3);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        while (($result = $this->stmt->fetch()) !== false) {
            if ($this->isRecordRestricted($result)) {
                continue;
            }
            yield $result;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($fetchMode = null, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        do {
            $result = $this->stmt->fetch($fetchMode);
        } while (is_array($result) && $this->isRecordRestricted($result));
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($fetchMode = null, $fetchArgument = null, $ctorArgs = null)
    {
        if ($fetchArgument) {
            $result = $this->stmt->fetchAll($fetchMode, $fetchArgument);
        } else {
            $result = $this->stmt->fetchAll($fetchMode);
        }

        if (is_array($result)) {
            return array_filter($result, function($row) {
                return $this->isRecordRestricted($row);
            });
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchColumn($columnIndex = 0)
    {
        $record = $this->stmt->fetch(FetchMode::MIXED);
        if (!$this->isRecordRestricted($record)) {
            return $record[$columnIndex] ?? false;
        }
        return null;
    }

    protected function isRecordRestricted(array $record)
    {
        return $this->restriction->isRecordRestricted($this->queriedTable, $record);
    }
}
