<?php

namespace Pest\Qase;

use RuntimeException;

trait InteractsWithQase
{ // @phpstan-ignore-line
    public function qase(): Qase
    {
        $reporter = QaseReporter::getInstanceWithoutInit();

        if (! isset($reporter)) {
            throw new RuntimeException('Qase reporter not initialized. Ensure QaseExtension is registered in phpunit.xml');
        }

        return new Qase($reporter);
    }
}
