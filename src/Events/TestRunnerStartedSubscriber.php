<?php

declare(strict_types=1);

namespace Pest\Qase\Events;

use Pest\Qase\QaseReporterInterface;
use PHPUnit\Event\TestRunner\Started;
use PHPUnit\Event\TestRunner\StartedSubscriber;

final class TestRunnerStartedSubscriber implements StartedSubscriber
{
    public function __construct(private readonly QaseReporterInterface $reporter) {}

    public function notify(Started $event): void
    {
        $this->reporter->startTestRun();
    }
}
