<?php

declare(strict_types=1);

namespace Pest\Qase\Events;

use Pest\Qase\QaseReporterInterface;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

final class TestFinishedSubscriber implements FinishedSubscriber
{
    private QaseReporterInterface $reporter;

    public function __construct(
        QaseReporterInterface $reporter
    )
    {
        $this->reporter = $reporter;
    }


    public function notify(Finished $event): void
    {
        $test = $event->test();

        if (!($test instanceof TestMethod)) {
            return;
        }

        $this->reporter->completeTest($test);
    }
}
