<?php

declare(strict_types=1);

namespace Pest\Qase\Events;

use Pest\Qase\QaseReporterInterface;
use Pest\Qase\StatusDetector;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\MarkedIncompleteSubscriber;

final class TestMarkedIncompleteSubscriber implements MarkedIncompleteSubscriber
{
    public function __construct(private readonly QaseReporterInterface $reporter) {}

    public function notify(MarkedIncomplete $event): void
    {
        $test = $event->test();

        if (! ($test instanceof TestMethod)) {
            return;
        }

        $throwable = $event->throwable();
        $status = StatusDetector::getStatusForFailure($throwable);

        $this->reporter->updateStatus($test, $status, $throwable->message(), $throwable->asString());
    }
}
