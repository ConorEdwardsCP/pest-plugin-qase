<?php

declare(strict_types=1);

namespace Pest\Qase\Traits;

use Pest\Qase\QaseReporter;

/**
 * Fluent builder for adding Qase metadata in Pest tests.
 *
 * This class provides a chainable API for adding test metadata
 * that would typically be added via PHP attributes in PHPUnit tests.
 */
trait HasQaseMetadata
{
    protected abstract function getReporter(): QaseReporter;

    /**
     * Link test to Qase test case ID(s).
     *
     * @param  int  ...$ids  The Qase test case ID(s)
     * @return self
     */
    public function caseId(int ...$ids): self
    {
        foreach ($ids as $id) {
            $this->getReporter()->addMetadataToCurrentTest('id', $id);
        }

        return $this;
    }

    /**
     * Add test suite(s) for organization.
     *
     * @param string ...$suites One or more suite names
     * @return self
     */
    public function suite(string ...$suites): self
    {
        foreach ($suites as $suite) {
            $this->getReporter()->addMetadataToCurrentTest('suite', $suite);
        }
        return $this;
    }

    /**
     * Add custom field to test result.
     *
     * @param string $name Field name (e.g., 'severity', 'description')
     * @param string $value Field value
     * @return self
     */
    public function field(string $name, string $value): self
    {
        $this->getReporter()->addMetadataToCurrentTest('field', [$name => $value]);
        return $this;
    }

    /**
     * Add parameter to test result.
     *
     * @param string $name Parameter name
     * @param string $value Parameter value
     * @return self
     */
    public function parameter(string $name, string $value): self
    {
        $this->getReporter()->addMetadataToCurrentTest('parameter', [$name => $value]);
        return $this;
    }

    /**
     * Set custom title for the test.
     * Note: You can also use Qase::title() for this.
     *
     * @param string $title Custom test title
     * @return self
     */
    public function title(string $title): self
    {
        $this->getReporter()->updateTitle($title);
        return $this;
    }
}
