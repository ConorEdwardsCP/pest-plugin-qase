<?php

declare(strict_types=1);

namespace Pest\Qase\Events;

use Pest\Qase\QaseReporterInterface;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;

final class TestPreparedSubscriber implements PreparedSubscriber
{
    public function __construct(private readonly QaseReporterInterface $reporter) {}

    public function notify(Prepared $event): void
    {
        $test = $event->test();

        if (! ($test instanceof TestMethod)) {
            return;
        }

        $this->reporter->startTest($test);
    }
}
