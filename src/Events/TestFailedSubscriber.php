<?php

declare(strict_types=1);

namespace Pest\Qase\Events;

use Pest\Qase\QaseReporterInterface;
use Pest\Qase\StatusDetector;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;

final class TestFailedSubscriber implements FailedSubscriber
{
    private QaseReporterInterface $reporter;

    public function __construct(
        QaseReporterInterface $reporter
    ) {
        $this->reporter = $reporter;
    }

    public function notify(Failed $event): void
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
