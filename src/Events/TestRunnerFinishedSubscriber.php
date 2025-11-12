<?php

declare(strict_types=1);

namespace Pest\Qase\Events;

use Pest\Qase\QaseReporterInterface;
use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;

final class TestRunnerFinishedSubscriber implements FinishedSubscriber
{
    private QaseReporterInterface $reporter;

    public function __construct(QaseReporterInterface $reporter)
    {
        $this->reporter = $reporter;
    }

    public function notify(Finished $event): void
    {
        $this->reporter->completeTestRun();
    }
}
