<?php

use Pest\Qase\Qase;
use function Pest\Qase\qase;

it('has qase', function () {
    expect(function_exists('Pest\Qase\qase'))->toBeTrue();
});

it('can use the qase instance', function () {
    expect(qase()->caseId(1))->toBeInstanceOf(Qase::class);
});

it('uses qase suite', function () {
    qase()
        ->suite('Test Suite. Delete Me.');

    expect(true)->toBeTrue();
});
