<?php

declare(strict_types=1);

namespace Pest\Qase;

use Pest\Plugin;

Plugin::uses(InteractsWithQase::class);

function qase(): Qase
{
    return test()->qase(); // @phpstan-ignore-line
}
