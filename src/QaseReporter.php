<?php

declare(strict_types=1);

namespace Pest\Qase;

use PHPUnit\Event\Code\TestMethod;
use Qase\PhpCommons\Interfaces\ReporterInterface;
use Qase\PhpCommons\Models\Attachment;
use Qase\PhpCommons\Models\Result;
use Qase\PhpCommons\Utils\Signature;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class QaseReporter implements QaseReporterInterface
{
    private static QaseReporter $instance;

    /**
     * @var array<Result>
     */
    private array $testResults = [];

    private ?string $currentKey = null;

    private function __construct(private readonly ReporterInterface $reporter) {}

    public static function getInstance(ReporterInterface $reporter): QaseReporter
    {
        if (! isset(self::$instance)) {
            self::$instance = new QaseReporter($reporter);
        }

        return self::$instance;
    }

    public static function getInstanceWithoutInit(): ?QaseReporter
    {
        return self::$instance ?? null;
    }

    public function startTestRun(): void
    {
        $this->reporter->startRun();
    }

    public function completeTestRun(): void
    {
        $this->reporter->completeRun();
    }

    public function startTest(TestMethod $test): void
    {
        $key = $this->getTestKey($test);
        $this->currentKey = $key;

        $testResult = new Result;
        $testResult->title = $this->getCleanTestTitle($test) ?? $test->methodName();
        $testResult->signature = $this->createSignature($test);
        $testResult->execution->setThread($this->getThread());

        $this->testResults[$key] = $testResult;
    }

    public function updateStatus(TestMethod $test, string $status, ?string $message = null, ?string $stackTrace = null): void
    {
        $key = $this->getTestKey($test);

        if (! isset($this->testResults[$key])) {
            $this->startTest($test);
            $this->testResults[$key]->execution->setStatus($status);
            $this->testResults[$key]->execution->finish();

            $this->handleMessage($key, $message);
            $this->reporter->addResult($this->testResults[$key]);

            return;
        }

        $this->testResults[$key]->execution->setStatus($status);
        $this->handleMessage($key, $message);

        if ($stackTrace) {
            $this->testResults[$key]->execution->setStackTrace($stackTrace);
        }
    }

    private function handleMessage(string $key, ?string $message): void
    {
        if ($message) {
            $this->testResults[$key]->message = $this->testResults[$key]->message."\n".$message."\n";
        }
    }

    public function completeTest(TestMethod $test): void
    {
        $key = $this->getTestKey($test);
        $this->testResults[$key]->execution->finish();

        $this->reporter->addResult($this->testResults[$key]);
    }

    private function getTestKey(TestMethod $test): string
    {
        $baseKey = $test->className().'::'.$test->methodName().':'.$test->line();

        // Include data provider data in key to make each iteration unique
        $dataProviderParams = $this->extractDataProviderParams($test);
        if ($dataProviderParams !== []) {
            $paramsHash = $this->generateParamsHash($dataProviderParams);

            return $baseKey.':'.$paramsHash;
        }

        return $baseKey;
    }

    private function createSignature(// @phpstan-ignore-line
        TestMethod $test,
        ?array $ids = null,
        ?array $suites = null,
        ?array $params = null
    ): string {
        $finalSuites = [];
        if ($suites) {
            $finalSuites = $suites;
        } else {
            $suites = explode('\\', $test->className());
            foreach ($suites as $suite) {
                $finalSuites[] = $suite;
            }
        }

        return Signature::generateSignature($ids, $finalSuites, $params);
    }

    private function getThread(): string
    {
        return $_ENV['TEST_TOKEN'] ?? 'default'; // @phpstan-ignore-line
    }

    /**
     * Get clean test title without "it " prefix and dataset suffix
     *
     * @return string Clean test title
     */
    private function getCleanTestTitle(TestMethod $test): ?string
    {
        $prettifiedName = $test->testDox()->prettifiedMethodName();

        // Remove "it " prefix if present
        $title = preg_replace('/^it\s+/', '', $prettifiedName);

        // Remove " with data set ..." suffix if present
        return preg_replace('/\s+with data set\s+.+$/', '', (string) $title);
    }

    public function addComment(string $message): void
    {
        if (! $this->currentKey) {
            return;
        }

        $this->testResults[$this->currentKey]->message = $this->testResults[$this->currentKey]->message.$message."\n";
    }

    public function updateTitle(string $title): void
    {
        if (! $this->currentKey) {
            return;
        }

        $this->testResults[$this->currentKey]->title = $title;
    }

    public function addAttachment(mixed $input): void
    {
        if (! $this->currentKey) {
            return;
        }

        if (is_string($input)) {
            $this->testResults[$this->currentKey]->attachments[] = Attachment::createFileAttachment($input);

            return;
        }

        if (is_array($input)) {
            foreach ($input as $item) {
                if (is_string($item)) {
                    $this->testResults[$this->currentKey]->attachments[] = Attachment::createFileAttachment($item);
                }
            }

            return;
        }

        if (is_object($input)) {
            $data = (array) $input;
            $this->testResults[$this->currentKey]->attachments[] = Attachment::createContentAttachment(
                $data['title'] ?? 'attachment', // @phpstan-ignore-line
                $data['content'] ?? null, // @phpstan-ignore-line
                $data['mime'] ?? null // @phpstan-ignore-line
            );
        }
    }

    /**
     * Add metadata to the current test at runtime.
     * Used by the Pest helper API via QaseMetadataBuilder.
     *
     * @param  string  $type  Type of metadata: 'id', 'suite', 'field', 'parameter'
     * @param  mixed  $value  The value to add
     */
    public function addMetadataToCurrentTest(string $type, mixed $value): void
    {
        if (! $this->currentKey) {
            return;
        }

        match ($type) {
            'id' => $this->testResults[$this->currentKey]->testOpsIds[] = $value,
            'suite' => $this->testResults[$this->currentKey]->relations->addSuite($value), // @phpstan-ignore-line
            'field' => $this->testResults[$this->currentKey]->fields = array_merge(
                $this->testResults[$this->currentKey]->fields,
                $value // @phpstan-ignore-line
            ),
            'parameter' => $this->testResults[$this->currentKey]->params = array_merge(
                $this->testResults[$this->currentKey]->params,
                $value // @phpstan-ignore-line
            ),
            default => null
        };
    }

    /**
     * Extract parameters from PHPUnit data provider
     *
     * @return array<string, string> Array of parameter name => value pairs
     */
    private function extractDataProviderParams(TestMethod $test): array
    {
        try {
            $originalData = $this->getOriginalDataProviderData($test);
            if ($originalData) {
                return $this->normalizeDataProviderData($originalData);
            }

            return [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Get original data provider data by calling the data provider method directly
     * This preserves associative array structure
     *
     * @return array|null Original data provider data or null if not available
     */
    private function getOriginalDataProviderData(TestMethod $test): ?array // @phpstan-ignore-line
    {
        try {
            $dataProviderMethodName = $this->getDataProviderMethodName($test);
            if ($dataProviderMethodName === null) {
                return null;
            }

            $allDataSets = $this->invokeDataProviderMethod($test->className(), $dataProviderMethodName);
            if (! is_array($allDataSets)) {
                return null;
            }

            $dataSetName = $this->getCurrentDataSetName($test);
            if ($dataSetName === null) {
                return null;
            }

            return $this->findDataSet($allDataSets, $dataSetName);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Get data provider method name from test method attributes
     *
     * @throws ReflectionException
     */
    private function getDataProviderMethodName(TestMethod $test): ?string
    {
        $testReflection = new ReflectionMethod($test->className(), $test->methodName());

        foreach ($testReflection->getAttributes() as $attribute) {
            $attributeName = $attribute->getName();
            if (str_contains($attributeName, 'DataProvider')) {
                $args = $attribute->getArguments();
                if (! empty($args) && is_string($args[0])) {
                    return $args[0];
                }
            }
        }

        return null;
    }

    /**
     * Invoke data provider method to get all data sets
     *
     * @param  class-string  $className
     *
     * @throws ReflectionException
     */
    private function invokeDataProviderMethod(string $className, string $methodName): ?array // @phpstan-ignore-line
    {
        $classReflection = new ReflectionClass($className);
        if (! $classReflection->hasMethod($methodName)) {
            return null;
        }

        $method = $classReflection->getMethod($methodName);
        if (! $method->isStatic() || ! $method->isPublic()) {
            return null;
        }

        $result = $method->invoke(null);

        return is_array($result) ? $result : null;
    }

    /**
     * Get current data set name from test data
     *
     * @throws ReflectionException
     */
    private function getCurrentDataSetName(TestMethod $test): int|string|null
    {
        if (! method_exists($test, 'testData')) { // @phpstan-ignore-line
            return null;
        }

        $testDataObj = $test->testData();
        $dataProviderData = $this->getDataProviderDataFromCollection($testDataObj);

        if ($dataProviderData === null) {
            return null;
        }

        return $this->getDataSetName($dataProviderData); // @phpstan-ignore-line
    }

    /**
     * Get DataFromDataProvider object from TestDataCollection
     */
    private function getDataProviderDataFromCollection(object $testDataObj): mixed
    {
        $reflection = new ReflectionClass($testDataObj);

        if (! $reflection->hasProperty('data')) {
            return null;
        }

        $dataProperty = $reflection->getProperty('data');
        $dataArray = $dataProperty->getValue($testDataObj);

        return (is_array($dataArray) && $dataArray !== []) ? $dataArray[0] : null;
    }

    /**
     * Get data set name from DataFromDataProvider object
     *
     * @throws ReflectionException
     */
    private function getDataSetName(object $dataProviderData): mixed
    {
        $reflection = new ReflectionClass($dataProviderData);

        if (! $reflection->hasMethod('dataSetName')) {
            return null;
        }

        $method = $reflection->getMethod('dataSetName');

        return $method->invoke($dataProviderData);
    }

    /**
     * Find matching data set in all data sets by name or index
     */
    private function findDataSet(array $allDataSets, int|string $dataSetName): ?array // @phpstan-ignore-line
    {
        if (isset($allDataSets[$dataSetName])) {
            return $allDataSets[$dataSetName]; // @phpstan-ignore-line
        }

        if (is_int($dataSetName)) {
            $allDataSetsValues = array_values($allDataSets);

            return $allDataSetsValues[$dataSetName] ?? null; // @phpstan-ignore-line
        }

        return null;
    }

    /**
     * Normalize data provider data into parameter format
     * Handles different data provider formats:
     * - Simple array: ['v1', 'v2'] -> ['param0' => 'v1', 'param1' => 'v2']
     * - Associative array: ['version' => 'v1'] -> ['version' => 'v1']
     * - Parameter pairs: ['version', 'v1'] -> ['version' => 'v1']
     *
     * @param  array  $data  Data from data provider
     * @return array<string, string> Normalized parameters
     */
    private function normalizeDataProviderData(array $data): array // @phpstan-ignore-line
    {
        $params = [];

        // Check if indexed array with parameter pairs [name, value, ...]
        if ($this->isIndexedArray($data) && $this->looksLikeParameterPairs($data)) {
            $counter = count($data);
            for ($i = 0; $i < $counter; $i += 2) {
                $params[$this->convertValueToString($data[$i])] = $this->convertValueToString($data[$i + 1]);
            }

            return $params;
        }

        // Handle as regular array (indexed or associative)
        foreach ($data as $key => $value) {
            $paramName = is_numeric($key) ? 'param'.$key : $key;
            $params[$paramName] = $this->convertValueToString($value);
        }

        return $params;
    }

    /**
     * Check if array looks like parameter pairs [name, value, name, value, ...]
     */
    private function looksLikeParameterPairs(array $data): bool // @phpstan-ignore-line
    {
        if (count($data) < 2 || count($data) % 2 !== 0) {
            return false;
        }
        $counter = count($data);

        for ($i = 0; $i < $counter; $i += 2) {
            $nameCandidate = $data[$i];

            // Name must be a non-empty string that looks like a parameter name
            if (! is_string($nameCandidate)
                || ($nameCandidate === '' || $nameCandidate === '0')
                || is_numeric($nameCandidate)
                || strlen($nameCandidate) < 3
                || ! preg_match('/^[a-zA-Z_]\w*$/', $nameCandidate)
                || $nameCandidate === ($data[$i + 1] ?? null)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if array is indexed (numeric keys starting from 0)
     */
    private function isIndexedArray(array $array): bool // @phpstan-ignore-line
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Convert any value to string for parameter representation
     */
    private function convertValueToString(mixed $value): string
    {
        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // @phpstan-ignore-line
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }

            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // @phpstan-ignore-line
        }

        return '';
    }

    /**
     * Generate a hash from parameters for use in test key
     *
     * @param  array<string, string>  $params
     */
    private function generateParamsHash(array $params): string
    {
        ksort($params);
        $paramString = http_build_query($params);

        return md5($paramString);
    }
}
