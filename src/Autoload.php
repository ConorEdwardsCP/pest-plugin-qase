<?php

declare(strict_types=1);

namespace Pest\Qase;

use Pest\Plugin;
use PHPUnit\Framework\TestCase;

Plugin::uses(InteractsWithQase::class);

function qase(): TestCase
{
    return test()->qase(...func_get_args()); // @phpstan-ignore-line
}
