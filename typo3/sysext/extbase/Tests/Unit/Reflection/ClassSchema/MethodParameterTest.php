<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Extbase\Tests\Unit\Reflection\ClassSchema;

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

use TYPO3\CMS\Extbase\Reflection\ClassSchema;
use TYPO3\CMS\Extbase\Tests\Unit\Reflection\Fixture\DummyClassWithAllTypesOfMethods;
use TYPO3\CMS\Extbase\Tests\Unit\Reflection\Fixture\DummyClassWithConstructorAndConstructorArgumentsWithDependencies;
use TYPO3\CMS\Extbase\Tests\Unit\Reflection\Fixture\DummyClassWithGettersAndSetters;
use TYPO3\CMS\Extbase\Tests\Unit\Reflection\Fixture\DummyController;
use TYPO3\CMS\Extbase\Tests\Unit\Reflection\Fixture\DummyControllerWithIgnoreValidationDoctrineAnnotation;
use TYPO3\CMS\Extbase\Tests\Unit\Reflection\Fixture\Validation\Validator\DummyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class TYPO3\CMS\Extbase\Tests\Unit\Reflection\MethodParameterTest
 */
class MethodParameterTest extends UnitTestCase
{
    /**
     * @test
     */
    public function classSchemaDetectsMandatoryParams(): void
    {
        static::assertFalse(
            (new ClassSchema(DummyClassWithAllTypesOfMethods::class))
            ->getMethod('methodWithMandatoryParam')
            ->getParameter('param')
            ->isOptional()
        );
    }

    /**
     * @test
     */
    public function classSchemaDetectsNullableParams(): void
    {
        static::markTestSkipped('Skip until MethodParameter::allowsNull() is needed and properly implemented');

        static::assertTrue(
            (new ClassSchema(DummyClassWithAllTypesOfMethods::class))
                ->getMethod('methodWithNullableParam')
                ->getParameter('param')
                ->allowsNull()
        );
    }

    /**
     * @test
     */
    public function classSchemaDetectsDefaultValueParams(): void
    {
        static::assertSame(
            'foo',
            (new ClassSchema(DummyClassWithAllTypesOfMethods::class))
                ->getMethod('methodWithDefaultValueParam')
                ->getParameter('param')
                ->getDefaultValue()
        );
    }

    /**
     * @test
     */
    public function classSchemaDetectsParamTypeFromTypeHint(): void
    {
        static::assertSame(
            'string',
            (new ClassSchema(DummyClassWithAllTypesOfMethods::class))
                ->getMethod('methodWithTypeHintedParam')
                ->getParameter('param')
                ->getType()
        );
    }

    /**
     * @test
     */
    public function classSchemaDetectsIgnoreValidationAnnotation(): void
    {
        $classSchemaMethod = (new ClassSchema(DummyControllerWithIgnoreValidationDoctrineAnnotation::class))
            ->getMethod('someAction');
        static::assertTrue($classSchemaMethod->getParameter('foo')->ignoreValidation());
        static::assertTrue($classSchemaMethod->getParameter('bar')->ignoreValidation());

        static::expectException(ClassSchema\Exception\NoSuchMethodParameterException::class);
        $classSchemaMethod->getParameter('baz')->ignoreValidation();
    }

    /**
     * @test
     */
    public function classSchemaDetectsConstructorArgumentsWithDependencies(): void
    {
        $classSchema = new ClassSchema(DummyClassWithConstructorAndConstructorArgumentsWithDependencies::class);
        static::assertTrue($classSchema->hasConstructor());

        $method = $classSchema->getMethod('__construct');
        static::assertSame(DummyClassWithGettersAndSetters::class, $method->getParameter('foo')->getDependency());
    }

    /**
     * @test
     */
    public function classSchemaDetectsValidateAnnotationsOfControllerActions(): void
    {
        $this->resetSingletonInstances = true;
        $classSchema = new ClassSchema(DummyController::class);
        static::assertSame(
            [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'minimum' => 1,
                        'maximum' => 10,
                    ],
                    'className' => StringLengthValidator::class
                ],
                [
                    'name' => 'NotEmpty',
                    'options' => [],
                    'className' => NotEmptyValidator::class
                ],
                [
                    'name' => 'TYPO3.CMS.Extbase:NotEmpty',
                    'options' => [],
                    'className' => NotEmptyValidator::class
                ],
                [
                    'name' => 'TYPO3.CMS.Extbase.Tests.Unit.Reflection.Fixture:DummyValidator',
                    'options' => [],
                    'className' => DummyValidator::class
                ],
                [
                    'name' => '\TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator',
                    'options' => [],
                    'className' => NotEmptyValidator::class
                ],
                [
                    'name' => NotEmptyValidator::class,
                    'options' => [],
                    'className' => NotEmptyValidator::class
                ]
            ],
            $classSchema->getMethod('methodWithValidateAnnotationsAction')->getParameter('fooParam')->getValidators()
        );
    }
}
