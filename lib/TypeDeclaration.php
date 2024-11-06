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

use ReflectionNamedType;
use ReflectionProperty;

class TypeDeclaration extends Type
{
    private bool $nullable;
    private bool $inArray;

    protected function __construct(
        string $name,
        bool $nullable = false,
        bool $inArray = false,
    ) {
        parent::__construct($name);
        $this->nullable = $nullable;
        $this->inArray = $inArray;
    }

    public static function fromSignature(string $signature): TypeDeclaration
    {
        $compatibilityNullable = str_starts_with($signature, '?');
        $leftNullable = str_starts_with($signature, 'null|');
        $rightNullable = str_ends_with($signature, '|null');
        if ($compatibilityNullable) {
            $signature = substr($signature, 1);
        } elseif ($leftNullable) {
            $signature = substr($signature, 5);
        } elseif ($rightNullable) {
            $signature = substr($signature, 0, -5);
        }

        $inArray = str_ends_with($signature, '[]');
        $signature = $inArray ? substr($signature, 0, -2) : $signature;

        return new self(
            $signature,
            $compatibilityNullable || $leftNullable || $rightNullable,
            $inArray
        );
    }

    public static function fromReflection(ReflectionNamedType $reflection): TypeDeclaration
    {
        return new self(
            $reflection->getName(),
            $reflection->allowsNull()
        );
    }

    public static function fromReflectionProperty(ReflectionProperty $reflection): TypeDeclaration
    {
        /** @var ReflectionNamedType $type */
        $type = $reflection->getType();

        return new self(
            $type->getName(),
            $type->allowsNull()
        );
    }

    public static function fromName(string $name): TypeDeclaration
    {
        return new self($name);
    }

    public function withName(string $newName): TypeDeclaration
    {
        return new self(
            $newName,
            $this->isNullable(),
            $this->isInArray()
        );
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isInArray(): bool
    {
        return $this->inArray;
    }

    public function getDeclaration(): string
    {
        $signature = $this->getName();
        if ($this->isInArray()) {
            $signature = sprintf('%s[]', $signature);
        }

        if ($this->isNullable()) {
            $signature = sprintf('?%s', $signature);
        }

        return $signature;
    }
}
