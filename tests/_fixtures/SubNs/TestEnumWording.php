<?php

declare(strict_types=1);

namespace Granule\Tests\DataBind\_fixtures\SubNs;

use Granule\Util\EnumWording;

/**
 * @method static TestEnum Yes()
 * @method static TestEnum No()
 */
class TestEnumWording extends EnumWording
{
    private const
        Yes = 'yes',
    No = 'no';
}
