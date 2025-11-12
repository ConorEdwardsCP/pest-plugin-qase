<?php

namespace Pest\Qase;


use Pest\Qase\Traits\HasQaseMetadata;
use RuntimeException;

trait InteractsWithQase {
    public function qase(): Qase
    {
        $reporter = QaseReporter::getInstanceWithoutInit();

        if(!isset($reporter)) {
            throw new RuntimeException('Qase reporter not initialized. Ensure QaseExtension is registered in phpunit.xml');
        }

        return new Qase($reporter);
    }
}
