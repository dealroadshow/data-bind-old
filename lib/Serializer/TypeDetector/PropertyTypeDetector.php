<?php
/*
 * MIT License
 *
 * Copyright (c) 2021 Finsight LLC
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

use Granule\DataBind\TypeDeclaration;
use Granule\DataBind\Helper;
use Granule\DataBind\Serializer\TypeDetector;
use ReflectionProperty;

class PropertyTypeDetector extends TypeDetector {
    protected function perform(ReflectionProperty $property): ?TypeDeclaration {
        if ($property->hasType()) {

            $type = TypeDeclaration::fromReflectionProperty($property);

            if (!Helper::isBuiltinType($type->getName()) && !class_exists($type->getName())) {
                $typeName = PropertyDocCommentTypeDetector::resolveObjectType(
                    $type->getName(),
                    $property->getDeclaringClass()->getFileName()
                );

                if (!$typeName || !class_exists($typeName)) {
                    $sameNsTypeName = $property
                                          ->getDeclaringClass()
                                          ->getNamespaceName()
                                      .'\\'.$type->getName();

                    if (class_exists($sameNsTypeName)) {
                        return $type->withName($sameNsTypeName);
                    }

                    return null;
                }

                $type = $type->withName($typeName);
            }

            return $type;
        }


        return null;
    }

}