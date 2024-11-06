<?php

declare(strict_types=1);

namespace Granule\Tests\DataBind;

use Granule\DataBind\DependencyResolver;
use Granule\DataBind\Extractor\ClassExtractor;
use Granule\DataBind\Serializer\UnknownTypeException;
use Granule\Tests\DataBind\_fixtures\SubNs\TestArrayMap;
use Granule\Tests\DataBind\_fixtures\SubNs\TestEnum;
use Granule\Tests\DataBind\_fixtures\SubNs\TestEnumWording;
use Granule\Tests\DataBind\_fixtures\TestCollection;
use Granule\Tests\DataBind\_fixtures\TestInternalObject;
use Granule\Tests\DataBind\_fixtures\TestObject;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 * @coversDefaultClass \Granule\DataBind\Extractor\ClassExtractor
 */
class ClassExtractorTest extends TestCase
{
    private static DependencyResolver $resolver;

    public static function setUpBeforeClass(): void
    {
        $resolver = DependencyResolver::builder()->build();
        self::$resolver = $resolver;
    }

    /**
     * @test
     *
     * @dataProvider getPrimitiveDataForClassExtractor
     * @covers ::toSimpleType
     */
    public function it_should_return_scalar_types(mixed $value): void
    {
        switch (gettype($value)) {
            case 'integer':
                $this->assertIsInt((new ClassExtractor(self::$resolver, $value))->toSimpleType());

                break;
            case 'float':
            case 'double':
                $this->assertIsFloat((new ClassExtractor(self::$resolver, $value))->toSimpleType());

                break;
            case 'boolean':
                $this->assertIsBool((new ClassExtractor(self::$resolver, $value))->toSimpleType());

                break;
            case 'object':
            case 'string':
                $this->assertIsString((new ClassExtractor(self::$resolver, $value))->toSimpleType());

                break;
            default:
                throw new UnknownTypeException();
        }
    }

    /**
     * @test
     * @dataProvider getNotPrimitiveDataForClassExtractor
     * @covers ::toSimpleType
     */
    public function it_should_return_not_scalar_types(object $value): void
    {
        $this->assertIsNotScalar($value);
        $this->assertIsArray((new ClassExtractor(self::$resolver, $value))->toSimpleType());
    }

    public function getNotPrimitiveDataForClassExtractor(): array
    {
        return [
            [TestCollection::builder()->add(new TestInternalObject())->build()],
            [new TestInternalObject()],
            [new TestObject()],
            [TestArrayMap::builder()->add('key1', new TestInternalObject())->build()],
        ];
    }

    public function getPrimitiveDataForClassExtractor(): array
    {
        return [
            [25],
            [25.1],
            [true],
            ['some_string'],
            [new \DateTimeImmutable()],
            [TestEnum::yes()],
            [TestEnumWording::Yes()],
        ];
    }
}
