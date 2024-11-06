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

namespace Granule\DataBind\Serializer\TypeDetector;

use Granule\DataBind\TypeDeclaration;
use Granule\DataBind\Helper;
use Granule\DataBind\Serializer\TypeDetector;
use PhpToken;
use ReflectionProperty;

class PropertyDocCommentTypeDetector extends TypeDetector
{
    protected function perform(ReflectionProperty $property): ?TypeDeclaration
    {
        if ($doc = Helper::getDocStatement($property, 'var')) {
            $type = TypeDeclaration::fromSignature($doc);

            if (!Helper::isBuiltinType($type->getName()) && !class_exists($type->getName())) {
                $typeName = self::resolveObjectType(
                    $type->getName(),
                    $property->getDeclaringClass()->getFileName()
                );

                if (!$typeName || !class_exists($typeName)) {
                    $sameNsTypeName = $property
                                          ->getDeclaringClass()
                                          ->getNamespaceName()
                                      . '\\' . $type->getName();

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

    public static function resolveObjectType(string $shortName, string $file): ?string
    {
        $tokens = PhpToken::tokenize(file_get_contents($file));
        $ns = [];
        $nsTokens = false;
        $aliasUsed = false;
        $nsGroupPrefix = [];

        foreach ($tokens as $token) {
            if ($token->id === T_CLASS) {
                return null;
            } elseif ($token->id === T_USE) {
                $nsTokens = true;
            } elseif ($token->id === T_NAME_QUALIFIED && $nsTokens) {
                $ns[] = $token->text;
            } elseif ($token->id === T_STRING && $nsTokens) {
                if ($aliasUsed && $ns && $token->text === $shortName) {
                    return implode('', $ns);
                }

                $ns[] = $token->text;
            } elseif ($token->id === T_NS_SEPARATOR && $nsTokens) {
                $ns[] = $token->text;
            } elseif ($token->id === T_AS && $nsTokens) {
                $aliasUsed = true;
            } elseif ($token->text === ';' && $nsTokens) {
                $nsTokens = false;

                if ($ns && end($ns) === $shortName) {
                    return implode('', $ns);
                }
                if (str_ends_with(implode('', $ns), $shortName)) {
                    return implode('', $ns);
                }

                $aliasUsed = false;
                $ns = [];
            } elseif ($token->text === '{' && $nsTokens) {
                $nsGroupPrefix = $ns;
            } elseif ($token->text === '}' && $nsTokens && $nsGroupPrefix) {
                $nsGroupPrefix = [];
            } elseif ($token->text === ',' && $nsTokens && $nsGroupPrefix) {
                if ($ns && end($ns) === $shortName) {
                    return implode('', $ns);
                }

                $aliasUsed = false;
                $ns = $nsGroupPrefix;
            }
        }

        return null;
    }
}
