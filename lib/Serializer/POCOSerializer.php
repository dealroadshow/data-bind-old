<?php
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

use Google\Protobuf\Internal\Message;
use Granule\DataBind\DependencyResolver;
use Granule\DataBind\DependencyResolverAware;
use Granule\DataBind\InvalidDataException;
use Granule\DataBind\Serializer;
use Granule\DataBind\Type;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Plain Old Control Object serializer
 */
class POCOSerializer extends Serializer implements DependencyResolverAware
{
    private TypeDetector $typeDetector;
    private DependencyResolver $resolver;
    private mixed $skipNull;
    private mixed $extendableClasses;

    public function setResolver(DependencyResolver $resolver): void
    {
        $this->resolver = $resolver;
    }

    public function __construct(TypeDetector $typeDetector, $skipNull = false, $extendableClasses = [])
    {
        $this->typeDetector = $typeDetector;
        $this->skipNull = $skipNull;
        $this->extendableClasses = $extendableClasses;
    }

    public function matches(Type $type): bool
    {
        return class_exists($type->getName()) && !$type->is(Message::class);
    }

    public function serialize(mixed $data): array
    {
        $response = [];
        foreach ($this->extractProperties($data) as $reflectionProperty) {
            if (!$reflectionProperty->isPublic()) {
                $reflectionProperty->setAccessible(true);
            }
            if ($reflectionProperty->isInitialized($data)) {
                $value = $reflectionProperty->getValue($data);
                if ($value !== null) {
                    $serializer = $this->resolver->resolve(Type::fromData($value));
                    $response[$reflectionProperty->getName()] = $serializer->serialize($value);
                } elseif (!$this->skipNull) {
                    $response[$reflectionProperty->getName()] = null;
                }
            } else {
                $response[$reflectionProperty->getName()] = null;
            }
        }

        return $response;
    }

    private function isExtendableClass($data): bool
    {
        foreach ($this->extendableClasses as $extendableClass) {
            if (is_a($data, $extendableClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $data
     *
     * @return ReflectionProperty[]
     */
    private function extractProperties($data): array
    {
        if ($this->isExtendableClass($data)) {
            return $this->getAllProperties($data);
        }

        return (new ReflectionClass($data))->getProperties();
    }

    private function getAllProperties($data): array
    {
        $properties = [];

        $reflectionClass = new ReflectionClass($data);
        do {
            $currentProperties = [];
            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $currentProperties[$reflectionProperty->getName()] = $reflectionProperty;
            }
            $properties = array_merge($currentProperties, $properties);
            $reflectionClass = $reflectionClass->getParentClass();
        } while ($reflectionClass);

        return $properties;
    }

    /**
     * @throws ReflectionException
     */
    protected function unserializeItem($data, Type $type): object
    {
        $class = new ReflectionClass($type->getName());
        $object = $class->newInstanceWithoutConstructor();

        if (!is_array($data)) {
            throw InvalidDataException::fromTypeAndData($type, $data);
        }

        foreach ($this->extractProperties($object) as $property) {
            $key = $property->getName();
            $type = $this->typeDetector->detect($property);

            if (!array_key_exists($key, $data) || $data[$key] === null) {
                if ($type->isNullable()) {
                    continue;
                } else {
                    throw NullValueException::fromPropertyWithType($property, $type);
                }
            } else {
                $serializer = $this->resolver->resolve($type);

                if (!$property->isPublic()) {
                    $property->setAccessible(true);
                }

                $property->setValue($object, $serializer->unserialize($data[$key], $type));
            }
        }

        return $object;
    }
}
