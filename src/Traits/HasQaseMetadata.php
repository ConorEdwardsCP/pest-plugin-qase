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
    abstract protected function getReporter(): QaseReporter;

    /**
     * Link test to Qase test case ID(s).
     *
     * @param  int  ...$ids  The Qase test case ID(s)
     */
    public function caseId(int ...$ids): static
    {
        foreach ($ids as $id) {
            $this->getReporter()->addMetadataToCurrentTest('id', $id);
        }

        return $this;
    }

    /**
     * Add test suite(s) for organization.
     *
     * @param  string  ...$suites  One or more suite names
     */
    public function suite(string ...$suites): static
    {
        foreach ($suites as $suite) {
            $this->getReporter()->addMetadataToCurrentTest('suite', $suite);
        }

        return $this;
    }

    /**
     * Add custom field to test result.
     *
     * @param  string  $name  Field name (e.g., 'severity', 'description')
     * @param  string  $value  Field value
     */
    public function field(string $name, string $value): static
    {
        $this->getReporter()->addMetadataToCurrentTest('field', [$name => $value]);

        return $this;
    }

    /**
     * Add parameter to test result.
     *
     * @param  string  $name  Parameter name
     * @param  string  $value  Parameter value
     */
    public function parameter(string $name, string $value): static
    {
        $this->getReporter()->addMetadataToCurrentTest('parameter', [$name => $value]);

        return $this;
    }

    /**
     * Set custom title for the test.
     * Note: You can also use Qase::title() for this.
     *
     * @param  string  $title  Custom test title
     */
    public function title(string $title): static
    {
        $this->getReporter()->updateTitle($title);

        return $this;
    }
}
