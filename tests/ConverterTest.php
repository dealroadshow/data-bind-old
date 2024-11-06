<?php

declare(strict_types=1);
/*
 * MIT License
 *
 * Copyright (c) 2017 Eugene Bogachov
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Granule\Tests\DataBind;

use DateTimeImmutable;
use Granule\DataBind\Converter;
use Granule\DataBind\DependencyResolver;
use Granule\Tests\DataBind\_fixtures\SubNs\TestArrayMap;
use Granule\Tests\DataBind\_fixtures\SubNs\TestEnum;
use Granule\Tests\DataBind\_fixtures\SubNs\TestNativeEnum;
use Granule\Tests\DataBind\_fixtures\TestCollection;
use Granule\Tests\DataBind\_fixtures\TestInternalObject;
use Granule\Tests\DataBind\_fixtures\TestObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use UnitEnum;

/**
 * @group integration
 * @coversDefaultClass Converter
 */
class ConverterTest extends TestCase
{
    private static Converter $converter;

    public static function setUpBeforeClass(): void
    {
        $resolver = DependencyResolver::builder()->build();
        self::$converter = new Converter($resolver);
    }

    public function getFixture(): array
    {
        return [
            [
                [
                    'compatibilityNullableString' => null,
                    'leftNullableString' => null,
                    'rightNullableString' => null,
                    'somestring' => 'some text',
                    'someint' => 1235,
                    'layers' => [
                        ['name' => 'layer 1'],
                        ['name' => 'layer 18'],
                    ],
                    'collection' => [
                        ['name' => 'layer 1'],
                        ['name' => 'layer 18'],
                    ],
                    'collection2' => [
                        ['name' => 'layer 1'],
                        ['name' => 'layer 18'],
                    ],
                    'map' => [
                        'l1' => ['name' => 'layer 1'],
                        'l18' => ['name' => 'layer 18'],
                    ],
                    'map2' => [
                        'l1' => ['name' => 'layer 1'],
                        'l18' => ['name' => 'layer 18'],
                    ],
                    'somebool' => true,
                    'question' => 'yes',
                    'layer' => ['name' => 'the one'],
                    'birthdate' => 'Friday, 20-Jul-84 00:00:00 UTC',
                    'nativeEnum' => ['name' => 'One'],
                    'strictTypedNullableString' => null,
                ],
                TestObject::class,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getFixture
     *
     * @param array  $fixture
     * @param string $class
     * @covers ::fromArray
     * @covers ::toObject
     */
    public function it_should_unserialize_basic_structure(array $fixture, string $class): void
    {
        /** @var TestObject $deserealized */
        $deserealized = self::$converter
            ->fromArray($fixture)
            ->toObject($class);

        $this->assertInstanceOf($class, $deserealized);
    }

    public function inlineTypeProvider(): array
    {
        $fixture = $this->getFixture()[0][0];

        return [
            'compatibilityNullableString' => [
                $fixture,
                'compatibilityNullableString',
                function ($value) {
                    return is_null($value);
                },
                function ($value) {
                    return $value;
                },
                null,
            ],
            'leftNullableString' => [
                $fixture,
                'leftNullableString',
                function ($value) {
                    return is_null($value);
                },
                function ($value) {
                    return $value;
                },
                null,
            ],
            'rightNullableString' => [
                $fixture,
                'rightNullableString',
                function ($value) {
                    return is_null($value);
                },
                function ($value) {
                    return $value;
                },
                null,
            ],
            'string' => [
                $fixture,
                'somestring',
                function ($value) {
                    return is_string($value);
                },
                function ($value) {
                    return $value;
                },
                'some text',
            ],
            'int' => [
                $fixture,
                'someint',
                function ($value) {
                    return is_integer($value);
                },
                function ($value) {
                    return $value;
                },
                1235,
            ],
            'object' => [
                $fixture,
                'layer',
                function ($value) {
                    return $value instanceof TestInternalObject;
                },
                function (TestInternalObject $value) {
                    return $value->getName();
                },
                'the one',
            ],
            'DateTime' => [
                $fixture,
                'birthdate',
                function ($value) {
                    return $value instanceof DateTimeImmutable;
                },
                function (DateTimeImmutable $value) {
                    return $value->format(DATE_RFC850);
                },
                'Friday, 20-Jul-84 00:00:00 UTC',
            ],
            'bool' => [
                $fixture,
                'somebool',
                function ($value) {
                    return is_bool($value);
                },
                function ($value) {
                    return $value;
                },
                true,
            ],
            'Enum' => [
                $fixture,
                'question',
                function ($value) {
                    return $value instanceof TestEnum;
                },
                function ($value) {
                    return $value;
                },
                TestEnum::yes(),
            ],
            'enum' => [
                $fixture,
                'nativeEnum',
                function ($value) {
                    return $value instanceof UnitEnum;
                },
                function ($value) {
                    return $value;
                },
                TestNativeEnum::One,
            ],
            'strictTypedNullableString' => [
                $fixture,
                'strictTypedNullableString',
                function ($value) {
                    return is_null($value);
                },
                function ($value) {
                    return $value;
                },
                null,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider inlineTypeProvider
     *
     * @param array    $fixture
     * @param string   $param
     * @param callable $check
     * @param callable $cast
     * @param mixed    $expected
     *
     * @throws ReflectionException
     *
     * @covers ::fromArray
     * @covers ::toObject
     */
    public function it_should_unserialize_specific_type(
        array $fixture,
        string $param,
        callable $check,
        callable $cast,
        $expected,
    ): void {
        /** @var TestObject $deserialized */
        $deserialized = self::$converter
            ->fromArray($fixture)
            ->toObject(TestObject::class);

        $reflector = new ReflectionClass($deserialized);

        $property = $reflector->getProperty($param);
        $property->setAccessible(true);
        $value = $property->getValue($deserialized);
        $this->assertTrue($check($value), 'Check value type');
        $this->assertTrue($expected === $cast($value), 'Check value equality');
    }

    public function listTypeProvider(): array
    {
        $fixture = $this->getFixture()[0][0];

        return [
            'object[]' => [
                $fixture,
                'layers',
                function ($value) {
                    return is_array($value);
                },
                2,
                function ($value) {
                    return $value[0] instanceof TestInternalObject;
                },
                function ($value) {
                    return $value[0]->getName();
                },
                'layer 1',
            ],
            'ArrayCollection' => [
                $fixture,
                'collection',
                function ($value) {
                    return $value instanceof TestCollection;
                },
                2,
                function ($value) {
                    return $value[0] instanceof TestInternalObject;
                },
                function ($value) {
                    return $value[0]->getName();
                },
                'layer 1',
            ],
            'ArrayMap' => [
                $fixture,
                'map',
                function ($value) {
                    return $value instanceof TestArrayMap;
                },
                2,
                function ($value) {
                    return $value['l18'] instanceof TestInternalObject;
                },
                function ($value) {
                    return $value['l18']->getName();
                },
                'layer 18',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider listTypeProvider
     *
     * @param array    $fixture
     * @param string   $param
     * @param callable $checkListType
     * @param int      $count
     * @param callable $checkItemType
     * @param callable $castItem
     * @param          $expected
     *
     * @throws ReflectionException
     *
     * @covers ::fromArray
     * @covers ::toObject
     */
    public function it_should_unserialize_list_type(
        array $fixture,
        string $param,
        callable $checkListType,
        int $count,
        callable $checkItemType,
        callable $castItem,
        $expected,
    ): void {
        /** @var TestObject $deserealized */
        $deserealized = self::$converter
            ->fromArray($fixture)
            ->toObject(TestObject::class);

        $reflector = new ReflectionClass($deserealized);

        $property = $reflector->getProperty($param);
        $property->setAccessible(true);
        $value = $property->getValue($deserealized);
        $this->assertTrue($checkListType($value), 'List type mismatch');
        $this->assertEquals($count, count($value), 'Count items mismatch');
        $this->assertTrue($checkItemType($value), 'Item type mismatch');
        $this->assertEquals($expected, $castItem($value), 'Item check');
    }

    /**
     * @test
     * @dataProvider getFixture
     * @ depends is_should_deserialize_basic_structure
     *
     * @param array  $fixture
     * @param string $class
     *
     * @covers ::fromArray
     * @covers ::toObject
     * @covers ::fromObject
     * @covers ::toSimpleType
     */
    public function it_should_serialize_basic_object(array $fixture, string $class): void
    {
        /** @var TestObject $deserialized */
        $deserialized = self::$converter
            ->fromArray($fixture)
            ->toObject($class);

        $serialized = self::$converter
            ->fromObject($deserialized)
            ->toSimpleType();

        $this->assertTrue(is_array($serialized));
        $this->assertTrue($serialized == $fixture);
    }
}
