<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Tests\Functional\Persistence;

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
use TYPO3\CMS\Core\Persistence\EntityReader;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case
 */
class EntityReaderTest extends FunctionalTestCase
{

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3/sysext/core/Tests/Functional/Persistence/Fixtures/Extensions/persistence',
    ];

    protected function setUp()
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DataSet/LiveDefaultEntities.csv');
    }

    public function scalarPropertyQueryProvider()
    {
        return [
            [
                '{
                    tt_content {
                        uid
                        header
                    }
                }',
                [
                    'data' => [
                        'tt_content' => [
                            ['uid' => 512, 'header' => 'Content 1'],
                            ['uid' => 513, 'header' => 'Content 2'],
                            ['uid' => 514, 'header' => 'Content 3']
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity {
                        uid
                        scalar_float,
                        scalar_string
                    }
                }',
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            ['uid' => 1024, 'scalar_float' => 0, 'scalar_string' => 'String'],
                            ['uid' => 1025, 'scalar_float' => 3.1415, 'scalar_string' => null],
                            ['uid' => 1026, 'scalar_float' => 0, 'scalar_string' => null],
                            ['uid' => 1027, 'scalar_float' => -3.1415, 'scalar_string' => 'String'],
                            ['uid' => 1028, 'scalar_float' => 0, 'scalar_string' => null]
                        ]
                    ]
                ]
            ],
            [
                '{
                    pages {
                        title
                    }
                }',
                [
                    'data' => [
                        'pages' => [
                            ['title' => 'Page 1'],
                            ['title' => 'Page 1.1'],
                            ['title' => 'Page 1.2']
                        ]
                    ]
                ]
            ],
            [
                '{
                    sys_category {
                        uid
                    }
                }',
                [
                    'data' => [
                        'sys_category' => [
                            ['uid' => 32],
                            ['uid' => 33],
                            ['uid' => 34]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider scalarPropertyQueryProvider
     */
    public function readScalarProperty(string $query, array $expected)
    {
        $reader = new EntityReader();
        $result = $reader->execute($query);
        $this->assertEquals($expected, $result);
    }

    public function relationPropertyQueryProvider()
    {
        return [
            [
                '{
                    tx_persistence_entity {
                        title
                        relation_inline_11_file_reference {
                            title
                        }
                    }
                }',
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            [
                                'title' => 'Entity 1',
                                'relation_inline_11_file_reference' => [
                                    ['title' => 'File reference 1']
                                ]
                            ],
                            [
                                'title' => 'Entity 2',
                                'relation_inline_11_file_reference' => []
                            ],
                            [
                                'title' => 'Entity 3',
                                'relation_inline_11_file_reference' => []
                            ],
                            [
                                'title' => 'Entity 4',
                                'relation_inline_11_file_reference' => []
                            ],
                            [
                                'title' => 'Entity 5',
                                'relation_inline_11_file_reference' => []
                            ]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity {
                        title
                        relation_inline_1n_file_reference {
                            title
                            description
                        }
                    }
                }',
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            [
                                'title' => 'Entity 1',
                                'relation_inline_1n_file_reference' => [
                                    ['title' => 'File reference 2', 'description' => null],
                                    ['title' => 'File reference 3', 'description' => null]
                                ]
                            ],
                            [
                                'title' => 'Entity 2',
                                'relation_inline_1n_file_reference' => []
                            ],
                            [
                                'title' => 'Entity 3',
                                'relation_inline_1n_file_reference' => []
                            ],
                            [
                                'title' => 'Entity 4',
                                'relation_inline_1n_file_reference' => []
                            ],
                            [
                                'title' => 'Entity 5',
                                'relation_inline_1n_file_reference' => []
                            ]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity {
                        title
                        relation_inline_1n_csv_file_reference {
                            title
                        }
                    }
                }',
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            [
                                'title' => 'Entity 1',
                                'relation_inline_1n_csv_file_reference' => [
                                    ['title' => 'File reference 4'],
                                    ['title' => 'File reference 5']
                                ]
                            ],
                            [
                                'title' => 'Entity 2',
                                'relation_inline_1n_csv_file_reference' => []
                            ],
                            [
                                'title' => 'Entity 3',
                                'relation_inline_1n_csv_file_reference' => []
                            ],
                            [
                                'title' => 'Entity 4',
                                'relation_inline_1n_csv_file_reference' => []
                            ],
                            [
                                'title' => 'Entity 5',
                                'relation_inline_1n_csv_file_reference' => []
                            ]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity {
                        title
                        relation_inline_mn_mm_content {
                            header
                        }
                    }
                }',
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            [
                                'title' => 'Entity 1',
                                'relation_inline_mn_mm_content' => [
                                    ['header' => 'Content 1'],
                                    ['header' => 'Content 2']
                                ]
                            ],
                            [
                                'title' => 'Entity 2',
                                'relation_inline_mn_mm_content' => []
                            ],
                            [
                                'title' => 'Entity 3',
                                'relation_inline_mn_mm_content' => []
                            ],
                            [
                                'title' => 'Entity 4',
                                'relation_inline_mn_mm_content' => []
                            ],
                            [
                                'title' => 'Entity 5',
                                'relation_inline_mn_mm_content' => []
                            ]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity {
                        title
                        relation_inline_mn_symmetric_entity {
                            peer {
                                title
                            }
                        }
                    }
                }',
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            [
                                'title' => 'Entity 1',
                                'relation_inline_mn_symmetric_entity' => [
                                    [
                                        'peer' => [
                                            ['title' => 'Entity 2']
                                        ]
                                    ],
                                    [
                                        'peer' => [
                                            ['title' => 'Entity 3']
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'title' => 'Entity 2',
                                'relation_inline_mn_symmetric_entity' => [
                                    [
                                        'peer' => [
                                            ['title' => 'Entity 2']
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'title' => 'Entity 3',
                                'relation_inline_mn_symmetric_entity' => [
                                    [
                                        'peer' => [
                                            ['title' => 'Entity 3']
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'title' => 'Entity 4',
                                'relation_inline_mn_symmetric_entity' => []
                            ],
                            [
                                'title' => 'Entity 5',
                                'relation_inline_mn_symmetric_entity' => []
                            ]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity {
                        title
                        relation_select_1n_page {
                            title
                        }
                    }
                }',
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            [
                                'title' => 'Entity 1',
                                'relation_select_1n_page' => []
                            ],
                            [
                                'title' => 'Entity 2',
                                'relation_select_1n_page' => [
                                    ['title' => 'Page 1.1']
                                ]
                            ],
                            [
                                'title' => 'Entity 3',
                                'relation_select_1n_page' => []
                            ],
                            [
                                'title' => 'Entity 4',
                                'relation_select_1n_page' => []
                            ],
                            [
                                'title' => 'Entity 5',
                                'relation_select_1n_page' => []
                            ]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity {
                        title
                        relation_select_mn_csv_category {
                            title
                        }
                    }
                }',
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            [
                                'title' => 'Entity 1',
                                'relation_select_mn_csv_category' => []
                            ],
                            [
                                'title' => 'Entity 2',
                                'relation_select_mn_csv_category' => [
                                    ['title' => 'Category 1.1'],
                                    ['title' => 'Category 1.2']
                                ]
                            ],
                            [
                                'title' => 'Entity 3',
                                'relation_select_mn_csv_category' => []
                            ],
                            [
                                'title' => 'Entity 4',
                                'relation_select_mn_csv_category' => []
                            ],
                            [
                                'title' => 'Entity 5',
                                'relation_select_mn_csv_category' => []
                            ]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity {
                        title
                        relation_select_mn_mm_content {
                            header
                        }
                    }
                }',
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            [
                                'title' => 'Entity 1',
                                'relation_select_mn_mm_content' => [
                                ]
                            ],
                            [
                                'title' => 'Entity 2',
                                'relation_select_mn_mm_content' => [
                                    ['header' => 'Content 1'],
                                    ['header' => 'Content 2']
                                ]
                            ],
                            [
                                'title' => 'Entity 3',
                                'relation_select_mn_mm_content' => []
                            ],
                            [
                                'title' => 'Entity 4',
                                'relation_select_mn_mm_content' => []
                            ],
                            [
                                'title' => 'Entity 5',
                                'relation_select_mn_mm_content' => []
                            ]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity {
                        title
                        relation_group_1n_content_page {
                            ... on tt_content {
                                header
                            }
                            ... on pages {
                                title
                            }
                        }
                    }
                }',
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            [
                                'title' => 'Entity 1',
                                'relation_group_1n_content_page' => []
                            ],
                            [
                                'title' => 'Entity 2',
                                'relation_group_1n_content_page' => []
                            ],
                            [
                                'title' => 'Entity 3',
                                'relation_group_1n_content_page' => [
                                    ['title' => 'Page 1.2']
                                ]
                            ],
                            [
                                'title' => 'Entity 4',
                                'relation_group_1n_content_page' => []
                            ],
                            [
                                'title' => 'Entity 5',
                                'relation_group_1n_content_page' => []
                            ]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity {
                        title
                        relation_group_mn_csv_content_page {
                            ... on tt_content {
                                header
                            }
                            ... on pages {
                                title
                            }
                        }
                    }
                }',
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            [
                                'title' => 'Entity 1',
                                'relation_group_mn_csv_content_page' => []
                            ],
                            [
                                'title' => 'Entity 2',
                                'relation_group_mn_csv_content_page' => []
                            ],
                            [
                                'title' => 'Entity 3',
                                'relation_group_mn_csv_content_page' => [
                                    ['header' => 'Content 2'],
                                    ['title' => 'Page 1.1']
                                ]
                            ],
                            [
                                'title' => 'Entity 4',
                                'relation_group_mn_csv_content_page' => []
                            ],
                            [
                                'title' => 'Entity 5',
                                'relation_group_mn_csv_content_page' => []
                            ]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity {
                        title
                        relation_group_mn_mm_content_page {
                            ... on tt_content {
                                header
                            }
                            ... on pages {
                                title
                            }
                        }
                    }
                }',
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            [
                                'title' => 'Entity 1',
                                'relation_group_mn_mm_content_page' => []
                            ],
                            [
                                'title' => 'Entity 2',
                                'relation_group_mn_mm_content_page' => []
                            ],
                            [
                                'title' => 'Entity 3',
                                'relation_group_mn_mm_content_page' => [
                                    ['title' => 'Page 1.2'],
                                    ['header' => 'Content 3']
                                ]
                            ],
                            [
                                'title' => 'Entity 4',
                                'relation_group_mn_mm_content_page' => []
                            ],
                            [
                                'title' => 'Entity 5',
                                'relation_group_mn_mm_content_page' => []
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider relationPropertyQueryProvider
     */
    public function readRelationProperty(string $query, array $expected)
    {
        $reader = new EntityReader();
        $result = $reader->execute($query);
        $this->assertEquals($expected, $result);
    }

    public function filterRestrictedQueryProvider()
    {
        return [
            [
                '{
                    tx_persistence_entity(filter: "scalar_string = `String`") {
                        uid
                    }
                }',
                [],
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            ['uid' => 1024],
                            ['uid' => 1027]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity(filter: "not uid = 1026") {
                        uid
                    }
                }',
                [],
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            ['uid' => 1024],
                            ['uid' => 1025],
                            ['uid' => 1027],
                            ['uid' => 1028]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity(filter: "scalar_float = 3.1415 or scalar_integer = 1") {
                        uid
                    }
                }',
                [],
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            ['uid' => 1025],
                            ['uid' => 1026],
                            ['uid' => 1027]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity(filter: "scalar_float = -3.1415 or scalar_float = 3.1415 and l10n_state = null") {
                        uid
                    }
                }',
                [],
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            ['uid' => 1025],
                            ['uid' => 1027]
                        ]
                    ]
                ]
            ],
            [
                '{
                    tx_persistence_entity(filter: "scalar_text = `` and not (scalar_float = -3.1415 or scalar_integer = null)") {
                        uid
                    }
                }',
                [],
                [
                    'data' => [
                        'tx_persistence_entity' => [
                            ['uid' => 1024],
                            ['uid' => 1025],
                            ['uid' => 1026]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider filterRestrictedQueryProvider
     */
    public function readFilterRestricted(string $query, array $bindings, array $expected)
    {
        $reader = new EntityReader();
        $result = $reader->execute($query, [], $bindings);
        $this->assertEquals($expected, $result);
    }

    public function contextRestrictedQueryProvider()
    {
        return [];
    }

    /**
     * @test
     * @dataProvider contextRestrictedQueryProvider
     */
    public function readContextRestricted(string $query, Context $context, array $expected)
    {
        $reader = new EntityReader();
        $result = $reader->execute($query, [], [], $context);
        $this->assertEquals($expected, $result);
    }
}
