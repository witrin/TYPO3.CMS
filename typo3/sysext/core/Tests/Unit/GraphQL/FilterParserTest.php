<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Tests\Unit\GraphQL;

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

use TYPO3\CMS\Core\Persistence\GraphQL\FilterParser;
use TYPO3\CMS\Core\Persistence\GraphQL\SyntaxException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/*
* Test case
*/
class FilterParserTest extends UnitTestCase
{
    public function invalidExpressionProvider()
    {
        return [
            ['foo.bar'],
            ['#foo != bar'],
            ['foo. >= bar'],
            ['foo <= 123bar'],
            ['123foo > bar'],
            ['.foo < bar'],
            ['foo <= .bar'],
            ['(foo < bar'],
            ['foo = bar)('],
            ['foo = 0123'],
            ['foo = .1.2'],
            ['foo = -0123'],
            ['foo = +123'],
            ['foo in [`bar`, 123]'],
            ['(foo = )bar'],
            ['null = bar'],
            ['foo = false And bar = null'],
        ];
    }

   /**
    * @test
    * @dataProvider invalidExpressionProvider
    */
    public function parseThrowsSyntaxExceptionForInvalidExpressions($expression)
    {
        $this->expectException(SyntaxException::class);
        FilterParser::parse($expression);
    }

    public function expressionProvider()
    {
        return [
            [
                'foo = bar',
                [
                    'type' => 'predicate',
                    'operator' => '=',
                    'left' => ['type' => 'path', 'segments' => ['foo']],
                    'right' => ['type' => 'path', 'segments' => ['bar']]
                ]
            ],
            [
                'foo.bar != baz',
                [
                    'type' => 'predicate',
                    'operator' => '!=',
                    'left' => ['type' => 'path', 'segments' => ['foo', 'bar']],
                    'right' => ['type' => 'path', 'segments' => ['baz']]
                ]
            ],
            [
                'not foo.bar.baz >= 123',
                [
                    'type' => 'predicate',
                    'operator' => 'not',
                    'left' => [
                        'type' => 'predicate',
                        'operator' => '>=',
                        'left' => ['type' => 'path', 'segments' => ['foo', 'bar', 'baz']],
                        'right' => ['type' => 'integer', 'value' => 123]
                    ]
                ]
            ],
            [
                'foo.bar != `:`',
                [
                    'type' => 'predicate',
                    'operator' => '!=',
                    'left' => ['type' => 'path', 'segments' => ['foo', 'bar']],
                    'right' => ['type' => 'string', 'value' => ':']
                ]
            ],
            [
                'foo.bar on baz match `^.*\`.*$`',
                [
                    'type' => 'predicate',
                    'operator' => 'match',
                    'left' => ['type' => 'path', 'segments' => ['foo', 'bar'], 'constraint' => 'baz'],
                    'right' => ['type' => 'regex', 'value' => '^.*\`.*$']
                ]
            ],
            [
                'foo in [1.23, 12.3, -.123, 0.123]',
                [
                    'type' => 'predicate',
                    'operator' => 'in',
                    'left' => ['type' => 'path', 'segments' => ['foo']],
                    'right' => ['type' => 'list', 'value' => [1.23, 12.3, -.123, .123]]
                ]
            ],
            [
                'foo in [0, 12, -123]',
                [
                    'type' => 'predicate',
                    'operator' => 'in',
                    'left' => ['type' => 'path', 'segments' => ['foo']],
                    'right' => ['type' => 'list', 'value' => [0, 12, -123]]
                ]
            ],
            [
                'foo in [`bar`, `baz`, `qux`]',
                [
                    'type' => 'predicate',
                    'operator' => 'in',
                    'left' => ['type' => 'path', 'segments' => ['foo']],
                    'right' => ['type' => 'list', 'value' => ['bar', 'baz', 'qux']]
                ]
            ],
            [
                'foo = bar or not baz match :qux',
                [
                    'type' => 'predicate',
                    'operator' => 'or',
                    'left' => [
                        'type' => 'predicate',
                        'operator' => '=',
                        'left' => ['type' => 'path', 'segments' => ['foo']],
                        'right' => ['type' => 'path', 'segments' => ['bar']]
                    ],
                    'right' => [
                        'type' => 'predicate',
                        'operator' => 'not',
                        'left' => [
                            'type' => 'predicate',
                            'operator' => 'match',
                            'left' => ['type' => 'path', 'segments' => ['baz']],
                            'right' => ['type' => 'parameter', 'name' => 'qux']
                        ]
                    ]
                ]
            ],
            [
                'not foo = false and bar = baz or qux = true',
                [
                    'type' => 'predicate',
                    'operator' => 'or',
                    'left' =>[
                        'type' => 'predicate',
                        'operator' => 'and',
                        'left' => [
                            'type' => 'predicate',
                            'operator' => 'not',
                            'left' => [
                                'type' => 'predicate',
                                'operator' => '=',
                                'left' => ['type' => 'path', 'segments' => ['foo']],
                                'right' => ['type' => 'boolean', 'value' => false]
                            ]
                        ],
                        'right' =>  [
                            'type' => 'predicate',
                            'operator' => '=',
                            'left' => ['type' => 'path', 'segments' => ['bar']],
                            'right' => ['type' => 'path', 'segments' => ['baz']]
                        ]
                    ],
                    'right' =>  [
                        'type' => 'predicate',
                        'operator' => '=',
                        'left' => ['type' => 'path', 'segments' => ['qux']],
                        'right' => ['type' => 'boolean', 'value' => true]
                    ]
                ]
            ],
            [
                'not (foo = null and (baz = true or foo :bar()))',
                [
                    'type' => 'predicate',
                    'operator' => 'not',
                    'left' =>[
                        'type' => 'predicate',
                        'operator' => 'and',
                        'left' => [
                            'type' => 'predicate',
                            'operator' => '=',
                            'left' => ['type' => 'path', 'segments' => ['foo']],
                            'right' => ['type' => 'none', 'value' => null]
                        ],
                        'right' => [
                            'type' => 'predicate',
                            'operator' => 'or',
                            'left' => [
                                'type' => 'predicate',
                                'operator' => '=',
                                'left' => ['type' => 'path', 'segments' => ['baz']],
                                'right' => ['type' => 'boolean', 'value' => true]
                            ],
                            'right' => [
                                'type' => 'predicate',
                                'operator' => ['type' => 'comparator', 'name' => 'bar'],
                                'left' => ['type' => 'path', 'segments' => ['foo']]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider expressionProvider
     */
    public function parseValidExpressions($expression, $expected)
    {
        $this->assertEquals($expected, FilterParser::parse($expression));
    }
}