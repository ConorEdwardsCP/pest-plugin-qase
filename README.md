# Pest Qase Plugin

**Seamlessly integrate your Pest tests with Qase Test Management System**

[![Latest Version on Packagist](https://img.shields.io/packagist/v/conoredwardscp/pest-plugin-qase.svg?style=flat-square)](https://packagist.org/packages/conoredwardscp/pest-plugin-qase)
[![Total Downloads](https://img.shields.io/packagist/dt/conoredwardscp/pest-plugin-qase.svg?style=flat-square)](https://packagist.org/packages/conoredwardscp/pest-plugin-qase)
[![License](https://img.shields.io/packagist/l/conoredwardscp/pest-plugin-qase.svg?style=flat-square)](https://packagist.org/packages/conoredwardscp/pest-plugin-qase)

## What is it?

The Pest Qase Plugin is a powerful integration that automatically reports your Pest test results to [Qase TMS](https://qase.io/). Track test execution, manage test cases, and maintain comprehensive test documentation—all without leaving your testing workflow.

### Why use it?

✨ **Automatic Reporting** — Test results flow directly to Qase TMS after each run

🔗 **Easy Linking** — Connect tests to Qase test cases with a simple `caseId()` call

📊 **Rich Metadata** — Add custom fields, parameters, comments, and attachments

🎯 **Smart Organization** — Group tests into suites for better management

⚡ **Parallel Execution** — Fully supports concurrent test runs

## Requirements

- PHP 8.2 or higher
- Pest 3.0 or 4.0
- A [Qase](https://qase.io/) account with an API token

## Installation

Install the plugin via Composer:

```bash
composer require conoredwardscp/pest-plugin-qase --dev
```

Then, register the extension in your `phpunit.xml`:

```xml
<phpunit>
    <extensions>
        <bootstrap class="Pest\Qase\QaseExtension"/>
    </extensions>
</phpunit>
```

## Configuration

Configure the plugin using environment variables. You can set these in your `.env` file, CI/CD pipeline, or export them in your terminal:

```bash
# Required: Set the reporting mode
QASE_MODE=testops  # Options: testops, report, off

# Required: Your Qase API token
QASE_TESTOPS_API_TOKEN=your_api_token_here

# Required: Your Qase project code
QASE_TESTOPS_PROJECT=PROJECT_CODE

# Optional: Link results to an existing test run
QASE_TESTOPS_RUN_ID=123

# Optional: Set a custom test run title
QASE_TESTOPS_RUN_TITLE="My Test Run"

# Optional: For parallel execution (automatically set by most test runners)
TEST_TOKEN=thread_identifier
```

> **💡 Tip:** Get your API token from your Qase account settings, and find your project code in the project URL or settings.

## Usage

### Basic Test Case Linking

Link a Pest test to a Qase test case by its ID:

```php
it('validates user login', function () {
    qase()->caseId(123);

    $user = loginUser('test@example.com', 'password');

    expect($user)->toBeLoggedIn();
});
```

### Multiple Test Case IDs

One test can report to multiple Qase test cases:

```php
it('validates complex authentication flow', function () {
    qase()->caseId(101, 102, 103);

    // Your test code here
});
```

### Organizing with Suites

Group related tests using suites for better organization:

```php
it('validates user authentication', function () {
    qase()
        ->suite('Authentication')
        ->suite('Security')
        ->caseId(200);

    // Your test code here
});
```

### Adding Custom Fields

Enhance your test results with custom metadata:

```php
it('checks critical API endpoint', function () {
    qase()
        ->caseId(300)
        ->field('severity', 'critical')
        ->field('priority', 'high')
        ->field('layer', 'api');

    // Your test code here
});
```

### Adding Parameters

Track test parameters for better context:

```php
it('validates cross-browser compatibility', function () {
    qase()
        ->caseId(400)
        ->parameter('browser', 'chrome')
        ->parameter('environment', 'staging')
        ->parameter('viewport', '1920x1080');

    // Your test code here
});
```

### Custom Test Titles

Override the default test name with a custom title:

```php
it('tc_auth_001_validates_login_with_valid_credentials', function () {
    qase()
        ->caseId(500)
        ->title('User Login with Valid Credentials');

    // Your test code here
});
```

### Adding Comments

Add runtime comments to your test results for additional context:

```php
it('performs data migration', function () {
    qase()->caseId(600);

    qase()->comment('Starting migration of 10,000 records');

    $result = migrateData();

    qase()->comment('Migration completed successfully');
    qase()->comment('Total time: ' . $result->duration . ' seconds');

    expect($result->success)->toBeTrue();
});
```

### Adding Attachments

Attach files or content directly to your test results:

```php
it('generates user report', function () {
    qase()->caseId(700);

    $report = generateReport();

    // Attach a single file
    qase()->attach('/path/to/screenshot.png');

    // Attach multiple files
    qase()->attach([
        '/path/to/log.txt',
        '/path/to/report.pdf'
    ]);

    // Attach content directly
    qase()->attach((object)[
        'title' => 'api-response.json',
        'content' => json_encode($report),
        'mime' => 'application/json'
    ]);

    expect($report)->toBeValid();
});
```

### Fluent API Chaining

Chain multiple methods together for clean, readable test setup:

```php
it('validates complete user journey', function () {
    qase()
        ->caseId(800)
        ->suite('Integration Tests')
        ->suite('User Journey')
        ->field('severity', 'high')
        ->field('priority', 'critical')
        ->parameter('environment', 'production')
        ->parameter('user_type', 'premium')
        ->title('Complete Premium User Journey');

    // Your test code here
});
```

### Working with Data Providers

The plugin automatically handles PHPUnit data providers:

```php
it('validates different user roles', function ($role, $permissions) {
    qase()
        ->caseId(900)
        ->parameter('role', $role)
        ->parameter('permissions', implode(',', $permissions));

    $user = createUserWithRole($role);

    expect($user->permissions)->toBe($permissions);
})->with([
    ['admin', ['read', 'write', 'delete']],
    ['editor', ['read', 'write']],
    ['viewer', ['read']],
]);
```

### Parallel Test Execution

The plugin is fully thread-safe and supports parallel execution out of the box:

```bash
# Pest parallel execution
./vendor/bin/pest --parallel

# With custom process count
./vendor/bin/pest --parallel --processes=4
```

## API Reference

### Global Helper

**`qase()`**
Returns the Qase instance for the current test.

### Available Methods

All methods return `self` for method chaining.

#### `caseId(int ...$ids): self`
Link the test to one or more Qase test case IDs.

```php
qase()->caseId(123);           // Single ID
qase()->caseId(123, 456, 789); // Multiple IDs
```

#### `suite(string ...$suites): self`
Add one or more suites to organize the test.

```php
qase()->suite('Authentication');
qase()->suite('API', 'Integration', 'Smoke');
```

#### `field(string $name, string $value): self`
Add a custom field to the test result.

```php
qase()->field('severity', 'critical');
qase()->field('priority', 'high');
```

#### `parameter(string $name, string $value): self`
Add a parameter to the test result.

```php
qase()->parameter('browser', 'chrome');
qase()->parameter('environment', 'staging');
```

#### `title(string $title): self`
Set a custom title for the test result.

```php
qase()->title('User Login with Valid Credentials');
```

#### `comment(string $message): void`
Add a comment to the test result during execution.

```php
qase()->comment('Starting validation phase');
qase()->comment('Validation completed successfully');
```

#### `attach(mixed $input): void`
Add an attachment to the test result.

```php
// Single file path
qase()->attach('/path/to/file.png');

// Multiple file paths
qase()->attach(['/path/to/file1.txt', '/path/to/file2.json']);

// Content object
qase()->attach((object)[
    'title' => 'filename.txt',
    'content' => 'file contents',
    'mime' => 'text/plain'
]);
```

## Development

### Running Tests

```bash
# Run all tests
composer test

# Run specific test suites
composer test:unit      # Unit tests
composer test:lint      # Code style checks
composer test:types     # Static analysis
```

### Code Quality

```bash
# Fix code style
composer lint

# Run refactoring
composer refacto
```

## License

Pest Qase Plugin is open-sourced software licensed under the [MIT license](LICENSE.md).

## Links

- **[Pest Documentation](https://pestphp.com)** — Learn more about Pest
- **[Qase Documentation](https://qase.io/docs)** — Qase TMS documentation
- **[Pest Repository](https://github.com/pestphp/pest)** — Main Pest framework

---

Built with ❤️ by the Pest community
