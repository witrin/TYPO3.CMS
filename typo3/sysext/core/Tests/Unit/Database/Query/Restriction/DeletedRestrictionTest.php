<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Tests\Unit\Database\Query\Restriction;

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

use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

class DeletedRestrictionTest extends AbstractRestrictionTestCase
{
    /**
     * @test
     */
    public function buildRestrictionsAddsDeletedWhereClause()
    {
        $GLOBALS['TCA']['aTable']['ctrl'] = [
            'delete' => 'deleted',
        ];
        $subject = new DeletedRestriction();
        $expression = $subject->buildExpression(['aTable' => 'aTable'], $this->expressionBuilder);
        $this->assertSame('"aTable"."deleted" = 0', (string)$expression);
    }

    /**
     * @return array
     */
    public function recordRestrictionIsCorrectDataProvider(): array
    {
        return [
            'deleted=0' => [
                ['deleted' => 0],
                false,
            ],
            'deleted=1' => [
                ['deleted' => 1],
                true,
            ]
        ];
    }

    /**
     * @param array $record
     * @param bool $expectation
     * @return void
     *
     * @test
     * @dataProvider recordRestrictionIsCorrectDataProvider
     */
    public function recordRestrictionIsCorrect(array $record, bool $expectation): void
    {
        $GLOBALS['TCA']['aTable']['ctrl'] = [
            'delete' => 'deleted',
        ];
        $restriction = new DeletedRestriction();
        static::assertSame($expectation, $restriction->isRecordRestricted('aTable', $record));
    }
}
