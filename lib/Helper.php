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

namespace Granule\DataBind;

use ReflectionProperty;

final class Helper
{
    /** @psalm-suppress UnusedConstructor */
    private function __construct()
    {
    }

    public static function getDocStatement(
        ReflectionProperty $reflectionProperty,
        string $statement
    ): ?string {
        $regexp = sprintf('/@%s\s+([\\a-zA-Z0-9_?|]+)\s+[.*]/s', $statement);
        if (preg_match_all($regexp, $reflectionProperty->getDocComment(), $matches)) {
            return $matches[1][0];
        }

        return null;
    }

    public static function isBuiltinType(string $type): bool
    {
        return in_array($type, [
            'bool', 'boolean',
            'int', 'integer',
            'float', 'double',
            'string',
            'array', 'iterable'
        ]);
    }
}
