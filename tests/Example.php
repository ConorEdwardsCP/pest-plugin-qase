<?php

use Pest\Qase\Qase;

use function Pest\Qase\qase;

it('has qase', function () {
    expect(function_exists('Pest\Qase\qase'))->toBeTrue();
});
