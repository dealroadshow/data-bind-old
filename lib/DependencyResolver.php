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

namespace Granule\DataBind;

use Granule\DataBind\Serializer\CollectionSerializer;
use Granule\DataBind\Serializer\DateTimeSerializer;
use Granule\DataBind\Serializer\EnumSerializer;
use Granule\DataBind\Serializer\EnumWordingSerializer;
use Granule\DataBind\Serializer\MapSerializer;
use Granule\DataBind\Serializer\MessageSerializer;
use Granule\DataBind\Serializer\NativeEnumSerializer;
use Granule\DataBind\Serializer\POCOSerializer;
use Granule\DataBind\Serializer\PrimitiveTypeSerializer;
use Granule\DataBind\Serializer\TypeDetector\AccessorTypeDetector;
use Granule\DataBind\Serializer\TypeDetector\PropertyDocCommentTypeDetector;
use Granule\DataBind\Serializer\TypeDetector\PropertyTypeDetector;

class DependencyResolver
{
    /** @var Serializer[] */
    private array $serializers = [];

    public function __construct(DependencyResolverBuilder $builder)
    {
        $this->serializers = $builder->getSerializers();

        foreach ($this->serializers as $serializer) {
            if ($serializer instanceof DependencyResolverAware) {
                $serializer->setResolver($this);
            }
        }
    }

    public static function emptyBuilder(): DependencyResolverBuilder
    {
        return new DependencyResolverBuilder();
    }

    public static function builder(): DependencyResolverBuilder
    {
        return static::emptyBuilder()
            ->add(new MessageSerializer())
            ->add(new DateTimeSerializer())
            ->add(new PrimitiveTypeSerializer())
            ->add(new CollectionSerializer())
            ->add(new MapSerializer())
            ->add(new EnumWordingSerializer())
            ->add(new EnumSerializer())
            ->add(new NativeEnumSerializer())
            ->addBottom(
                new POCOSerializer(
                    new PropertyTypeDetector(
                        new AccessorTypeDetector(
                            new PropertyDocCommentTypeDetector()
                        )
                    )
                )
            );
    }

    public function resolve(Type $type): Serializer
    {
        foreach ($this->serializers as $serializer) {
            if ($serializer->matches($type)) {
                return $serializer;
            }
        }

        throw SerializerNotFoundException::fromType($type);
    }
}
