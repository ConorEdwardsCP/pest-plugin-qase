<?php

declare(strict_types=1);

namespace Pest\Qase\Events;

use Pest\Qase\QaseReporterInterface;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\SkippedSubscriber;

final class TestSkippedSubscriber implements SkippedSubscriber
{
    private QaseReporterInterface $reporter;

    public function __construct(
        QaseReporterInterface $reporter
    )
    {
        $this->reporter = $reporter;
    }

    public function notify(Skipped $event): void
    {
        $test = $event->test();

        if (!($test instanceof TestMethod)) {
            return;
        }

        $this->reporter->updateStatus($test, 'skipped', $event->message());
    }
}
