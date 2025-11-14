<?php

declare(strict_types=1);

namespace Pest\Qase;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Qase\PhpCommons\Reporters\ReporterFactory;

final class QaseExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $coreReporter = ReporterFactory::create('pestphp/pest', 'conoredwardscp/pest-plugin-qase');

        $reporter = QaseReporter::getInstance($coreReporter);

        $facade->registerSubscribers(
            new Events\TestConsideredRiskySubscriber($reporter),
            new Events\TestPreparedSubscriber($reporter),
            new Events\TestFinishedSubscriber($reporter),
            new Events\TestFailedSubscriber($reporter),
            new Events\TestErroredSubscriber($reporter),
            new Events\TestMarkedIncompleteSubscriber($reporter),
            new Events\TestSkippedSubscriber($reporter),
            new Events\TestWarningTriggeredSubscriber($reporter),
            new Events\TestPassedSubscriber($reporter),
            new Events\TestRunnerFinishedSubscriber($reporter),
            new Events\TestRunnerStartedSubscriber($reporter),
        );
    }
}
