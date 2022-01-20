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

namespace Granule\DataBind\Serializer\TypeDetector;

use Google\Protobuf\Internal\Message;
use Granule\DataBind\TypeDeclaration;
use Granule\DataBind\Serializer\TypeDetector;
use ReflectionProperty;
use ReflectionClass;

class AccessorTypeDetector extends TypeDetector
{
    protected function perform(ReflectionProperty $property): ?TypeDeclaration
    {
        $propertyName = $property->getName();
        $getterSuffix = ucfirst($propertyName);
        $reflectionClass = $property->getDeclaringClass();

        // Exclude proto Messages getters
        if ($property->hasType()) {
            $propertyReflection = new ReflectionClass($property->getType()->getName());

            if ($propertyReflection->isSubclassOf(Message::class)) {
                return TypeDeclaration::fromReflection($property->getType());
            }
        }

        foreach ([
            'get'.$getterSuffix,
            'is'.$getterSuffix,
            $getterSuffix
        ] as $getterName) {
            if ($reflectionClass->hasMethod($getterName)) {
                $getter = $reflectionClass->getMethod($getterName);
                if ($getter->hasReturnType()) {
                    return TypeDeclaration::fromReflection($getter->getReturnType());
                }
            }
        }

        return null;
    }
}
