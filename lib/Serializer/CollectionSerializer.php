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

namespace Granule\DataBind\Serializer;

use Granule\DataBind\DependencyResolver;
use Granule\DataBind\DependencyResolverAware;
use Granule\DataBind\TypeDeclaration;
use Granule\DataBind\Serializer;
use Granule\DataBind\Type;
use Granule\Util\Collection;

class CollectionSerializer extends Serializer implements DependencyResolverAware
{
    use ValueTypeExtraction;

    private DependencyResolver $resolver;

    public function setResolver(DependencyResolver $resolver): void
    {
        $this->resolver = $resolver;
    }

    public function matches(Type $type): bool
    {
        return $type->is(Collection::class);
    }

    public function serialize($data): array
    {
        /** @var Collection $data */
        $result = [];
        if ($vType = $this->getValueType(TypeDeclaration::fromData($data))) {
            $serializer = $this->resolver->resolve($vType);
            foreach ($data->toArray() as $item) {
                $result[] = $serializer->serialize($item);
            }
        } else {
            foreach ($data->toArray() as $item) {
                $result[] = $this->resolver->resolve(
                    TypeDeclaration::fromData($item)
                )->serialize($item);
            }
        }

        return $result;
    }

    protected function unserializeItem($data, Type $type)
    {
        /** @var array $data */
        if ($vType = $this->getValueType($type)) {
            /** @var Collection\CollectionBuilder $builder */
            $builder = call_user_func([$type->getName(), 'builder']);
            $vSerializer = $this->resolver->resolve($vType);
            foreach ($data as $v) {
                $builder->add($vSerializer->unserialize($v, $vType));
            }

            return $builder->build();
        }

        return call_user_func([$type->getName(), 'fromArray'], $data);
    }
}
