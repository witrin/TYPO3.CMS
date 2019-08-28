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

/**
 * Interface that allows to determine whether records shall be restricted. This is used after
 * records have been retrieved from database - in contrast to QueryRestrictionInterface with
 * is invoked earlier.
 */
interface RecordRestrictionInterface
{
    /**
     * Determine whether record shall be restricted
     *
     * @param string $tableName to be evaluated
     * @param array $record to be evaluated
     * @return bool whether record shall be restricted
     */
    public function isRecordRestricted(string $tableName, array $record): bool;
}
