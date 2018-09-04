<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Tests\Unit\Configuration\GraphQL;

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

use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ListOfType;
use TYPO3\CMS\Core\Configuration\GraphQL\SchemaDefinitionFactory;
use TYPO3\CMS\Core\Configuration\MetaModel\EntityRelationMapFactory;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class SchemaDefinitionFactoryTest extends UnitTestCase
{

    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function entitesAreMappedToQueryRootProperties()
    {
        $configuration = [
            'foo' => [
                'columns' => [
                    'bar' => [
                        'config' => [
                            'type' => 'input',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->mapDefinition($configuration);

        $this->assertEquals($result->getQueryType()->getField('foo')->getType()->name, 'foo');
    }

    /**
     * @test
     */
    public function inputIsMappedToStringProperty()
    {
        $configuration = [
            'bar' => [
                'columns' => [
                    'baz' => [
                        'config' => [
                            'type' => 'input',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->mapDefinition($configuration);

        $this->assert($result->getType('bar')->getField('baz')->getType() instanceof StringType);
    }

    /**
     * @test
     */
    public function checkedIsMappedToBooleanProperty()
    {
        $configuration = [
            'foo' => [
                'columns' => [
                    'qux' => [
                        'config' => [
                            'type' => 'checked',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->mapDefinition($configuration);

        $this->assert($result->getType('foo')->getField('qux')->getType() instanceof BooleanType);
    }

    /**
     * @test
     */
    public function selectWithItemsOnlyIsMappedToStringProperty()
    {
        $configuration = [
            'qux' => [
                'columns' => [
                    'baz' => [
                        'config' => [
                            'type' => 'select',
                            'items' => [
                                '', '0',
                                '', '1',
                            ],
                            'maxitems' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->mapDefinition($configuration);

        $this->assert($result->getType('qux')->getField('baz')->getType() instanceof StringType);
    }

    /**
     * @test
     */
    public function multiSelectWithItemsOnlyIsMappedToListOfStringProperty()
    {
        $configuration = [
            'foo' => [
                'columns' => [
                    'bar' => [
                        'config' => [
                            'type' => 'select',
                            'items' => [
                                '', 0,
                                '', 1,
                            ],
                            'maxitems' => 10,
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->mapDefinition($configuration);

        $this->assert(
            $result->getType('foo')->getField('bar')->getType() instanceof ListOfType &&
            $result->getType('foo')->getField('bar')->getType()->ofType instanceof StringType
            );
    }

    /**
     * @test
     */
    public function selectWithEntitiesIsMappedToEntityProperty()
    {
        $configuration = [
            'bar' => [
                'columns' => [
                    'qux' => [
                        'config' => [
                            'type' => 'select',
                            'foreign_table' => 'foo',
                            'maxitems' => 1,
                        ],
                    ],
                ],
            ],
            'foo' => [
                'columns' => [
                    'bar' => [
                        'config' => [
                            'type' => 'input',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->mapDefinition($configuration);

        $this->assertEquals($result->getType('bar')->getField('qux')->getType(), $result->getType('foo'));
    }

    public function selectWithEntitiesIsMappedToListOfEntityProperty()
    {
        $configuration = [
            'baz' => [
                'columns' => [
                    'qux' => [
                        'config' => [
                            'type' => 'select',
                            'foreign_table' => 'bar',
                            'maxitems' => 2,
                        ],
                    ],
                ],
            ],
            'bar' => [
                'columns' => [
                    'bar' => [
                        'config' => [
                            'type' => 'input',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->mapDefinition($configuration);

        $this->assert($result->getType('baz')->getField('qux')->getType() instanceof ListOfType);
        $this->assertEquals($result->getType('baz')->getField('qux')->getType()->ofType, $result->getType('bar'));
    }

    public function groupWithEntityItemsIsMappedToListOfEntityProperty()
    {
        $configuration = [
            'baz' => [
                'columns' => [
                    'qux' => [
                        'config' => [
                            'type' => 'select',
                            'foreign_table' => 'bar',
                            'maxitems' => 2,
                        ],
                    ],
                ],
            ],
            'bar' => [
                'columns' => [
                    'bar' => [
                        'config' => [
                            'type' => 'input',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->mapDefinition($configuration);

        $this->assert($result->getType('baz')->getField('qux')->getType() instanceof ListOfType);
        $this->assertEquals($result->getType('baz')->getField('qux')->getType()->ofType, $result->getType('bar'));
    }

    protected function mapDefinition($configuration)
    {
        // encapsulate the interface which is subject to change
        $entityRelationMapFactory = new EntityRelationMapFactory(
            $configuration,
            EntityRelationMapFactory::INSTRUCTION_DEFAULT
        );
        $schemaDefinitionFactory = new SchemaDefinitionFactory();

        return $schemaDefinitionFactory->create($entityRelationMapFactory->create());
    }
}
