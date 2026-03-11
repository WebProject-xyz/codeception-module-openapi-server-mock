# Codeception Module for PHP OpenAPI Mock Server - Project Context

This directory contains a Codeception module designed to provide easy access to and control over the [PHP OpenAPI Mock Server](https://github.com/WebProject-xyz/php-openapi-mock-server).

## Project Overview

*   **Type:** PHP Library / Codeception Module
*   **Purpose:** Orchestrate a standalone OpenAPI mock server during automated tests and provide high-level actions for test scenarios.
*   **Key Technologies:**
    *   **Language:** PHP 8.3+ (PSR-12, Symfony, and Risky rulesets)
    *   **Testing:** Codeception 5.x
    *   **Process Management:** Symfony Process
    *   **CI/CD:** GitHub Actions (configured in `.github/workflows/ci.yml`)
    *   **Automation:** GrumPHP (commit hooks), PHP-CS-Fixer, PHPStan (Level 8)

## Architecture

The module `WebProject\Codeception\Module\OpenApiServerMock` acts as a **Helper Module**.
- **Lifecycle Management:** It starts a PHP built-in server running the `openapi-mock-server` binary before the suite and stops it after.
- **Auto-Detection:** It attempts to locate the mock server automatically via class reflection or directory traversal.
- **Dynamic Control:** It communicates with the mock server via custom HTTP headers (`X-OpenApi-Mock-Active`, `X-OpenApi-Mock-StatusCode`, `X-OpenApi-Mock-Example`) to manipulate mock responses on the fly.
- **Dependencies:** Designed to be used alongside `REST` and `PhpBrowser` modules.

## Building and Running

### Prerequisites
*   PHP 8.3 or higher.
*   Composer.

### Setup
```bash
composer install
vendor/bin/codecept build  # Generate Tester actions
```

### Testing
```bash
composer test               # Run all tests (Acceptance + Unit)
vendor/bin/codecept run Acceptance  # Run acceptance tests specifically
vendor/bin/codecept run Unit        # Run unit tests specifically
```

### Static Analysis and Linting
```bash
composer stan               # Run PHPStan (Level 8)
composer cs:check           # Check coding standards
composer cs:fix             # Fix coding standards
```

## Development Conventions

*   **Namespaces:**
    *   Source: `WebProject\Codeception\Module\`
    *   Tests: `WebProject\Codeception\Module\Tests\`
*   **Coding Standard:** Strict adherence to PSR-12 and Symfony coding styles via PHP-CS-Fixer.
*   **Strict Typing:** `declare(strict_types=1);` is required in all PHP files.
*   **Type Safety:** PHPStan Level 8 is enforced. Avoid `mixed` where possible; use specific iterable types in PHPDoc.
*   **Commit Hooks:** GrumPHP runs `php-cs-fixer`, `phpstan`, and `codeception` on every commit.

## Key Files

*   `src/OpenApiServerMock.php`: The main module logic.
*   `tests/Acceptance/MockServerCest.php`: Main acceptance tests for the module.
*   `tests/Unit/OpenApiServerMockTest.php`: Unit tests for the module's core logic.
*   `codeception.yml`: Global Codeception configuration.
*   `phpstan.neon`: PHPStan configuration (Level 8).
*   `.php-cs-fixer.php`: Coding style rules.
*   `grumphp.yml`: Automation task definitions.

## Lessons Learned & Troubleshooting

### Absolute Path Resolution for OpenAPI Specifications
The mock server process is started with its own `vendor` directory as the working directory (`cwd`) to facilitate binary execution. This means any relative paths for the `OPENAPI_SPEC` environment variable will be resolved against that library path, leading to `500 Internal Server Error` (CONFIG_ERROR) if the specification is not found.
- **Rule:** Always resolve the `spec` configuration to an **absolute path** (using `realpath()`) before passing it to the server process.

### Port Availability
To prevent confusing errors where the mock server seems to be running but does not behave as expected (e.g., if another process is already listening on the same port), the module now performs a pre-start check.
- **Implementation:** `fsockopen()` is used to verify that the configured port is free before attempting to start the built-in PHP server.
- **Behavior:** A descriptive `RuntimeException` is thrown if the port is already in use.

### Unit Testing Patterns
Unit tests for the `OpenApiServerMock` module are located in `tests/Unit/OpenApiServerMockTest.php`. They focus on:
- **Initialization & Configuration**: Verifying path auto-detection, validation of the mock server directory, and specification file existence.
- **Mock Control**: Ensuring that methods like `haveOpenApiMockStatusCode` correctly communicate with dependent modules (`REST`, `PhpBrowser`) by injecting the appropriate HTTP headers.
- **Isolation**: Using `ModuleContainer` mocks and `ReflectionClass` to test internal logic without requiring a running mock server.

