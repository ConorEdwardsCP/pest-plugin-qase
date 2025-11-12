<?php

declare(strict_types=1);

namespace Pest\Qase\Events;

use Pest\Qase\QaseReporterInterface;
use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;

final class TestRunnerFinishedSubscriber implements FinishedSubscriber
{
    public function __construct(private readonly QaseReporterInterface $reporter) {}

    public function notify(Finished $event): void
    {
        $this->reporter->completeTestRun();
    }
}
