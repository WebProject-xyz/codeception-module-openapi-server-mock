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
composer test               # Run all tests
vendor/bin/codecept run Acceptance  # Run acceptance tests specifically
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
*   `codeception.yml`: Global Codeception configuration.
*   `phpstan.neon`: PHPStan configuration (Level 8).
*   `.php-cs-fixer.php`: Coding style rules.
*   `grumphp.yml`: Automation task definitions.
