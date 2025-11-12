<?php

namespace Pest\Qase;


use RuntimeException;

trait InteractsWithQase {
    public function qase(): QaseMetadataBuilder
    {
        $reporter = QaseReporter::getInstanceWithoutInit();

        if(!isset($reporter)) {
            throw new RuntimeException('Qase reporter not initialized. Ensure QaseExtension is registered in phpunit.xml');
        }

        return new QaseMetadataBuilder($reporter);
    }
}
