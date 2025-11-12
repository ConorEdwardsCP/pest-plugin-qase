<?php

declare(strict_types=1);

namespace Pest\Qase\Events;

use Pest\Qase\QaseReporterInterface;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\Test\WarningTriggeredSubscriber;

final class TestWarningTriggeredSubscriber implements WarningTriggeredSubscriber
{
    private QaseReporterInterface $reporter;

    public function __construct(
        QaseReporterInterface $reporter
    ) {
        $this->reporter = $reporter;
    }

    public function notify(WarningTriggered $event): void
    {
        $test = $event->test();

        if (! ($test instanceof TestMethod)) {
            return;
        }

        $this->reporter->updateStatus($test, 'invalid', $event->message());
    }
}
