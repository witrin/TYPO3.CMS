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

use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;

class HiddenRestrictionTest extends AbstractRestrictionTestCase
{
    /**
     * @test
     */
    public function buildRestrictionsAddsHiddenWhereClause()
    {
        $GLOBALS['TCA']['aTable']['ctrl'] = [
            'enablecolumns' => [
                'disabled' => 'myHiddenField',
            ],
        ];
        $subject = new HiddenRestriction();
        $expression = $subject->buildExpression(['aTable' => 'aTable'], $this->expressionBuilder);
        $this->assertSame('"aTable"."myHiddenField" = 0', (string)$expression);
    }

    /**
     * @return array
     */
    public function recordRestrictionIsCorrectDataProvider(): array
    {
        return [
            'hidden=0' => [
                ['myHiddenField' => 0],
                false,
            ],
            'hidden=1' => [
                ['myHiddenField' => 1],
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
            'enablecolumns' => [
                'disabled' => 'myHiddenField',
            ],
        ];
        $restriction = new HiddenRestriction();
        static::assertSame($expectation, $restriction->isRecordRestricted('aTable', $record));
    }
}
