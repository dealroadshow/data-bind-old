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

namespace Granule\Tests\DataBind\_fixtures;

use Granule\Tests\DataBind\_fixtures\SubNs\TestArrayMap as AliasForMap;
use Granule\Tests\DataBind\_fixtures\SubNs\TestEnum;

class TestObject
{
    /** @var string|null */
    private $compatibilityNullableString;
    /** @var null|string */
    private $leftNullableString;
    /** @var string|null */
    private $rightNullableString;
    /** @var string */
    private $somestring;
    /** @var int */
    protected $someint;
    /** @var TestInternalObject[] */
    private $layers = [];
    /** @var  */
    private $layer;
    /** @var \DateTimeImmutable */
    private $birthdate;
    /** @var TestInternalObject[] */
    private $collection;
    /** @var AliasForMap */
    private $map;
    /** @var bool */
    protected $somebool;
    /** @var TestEnum */
    private $question;

    public function getLayer(): TestInternalObject
    {
        return $this->layer;
    }

    public function getCollection(): TestCollection
    {
        return $this->collection;
    }
}
