# Codeception Module for PHP OpenAPI Mock Server

[![CI](https://github.com/WebProject-xyz/codeception-module-openapi-server-mock/actions/workflows/ci.yml/badge.svg)](https://github.com/WebProject-xyz/codeception-module-openapi-server-mock/actions/workflows/ci.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/webproject-xyz/codeception-module-openapi-server-mock.svg)](https://packagist.org/packages/webproject-xyz/codeception-module-openapi-server-mock)
[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Codeception](https://img.shields.io/badge/codeception-%5E5.0-red.svg)](https://codeception.com/)

> **Orchestrate and control high-performance OpenAPI mocks directly from your PHP testing suite.**

This module provides a seamless integration between [Codeception](https://codeception.com/) and the [PHP OpenAPI Mock Server](https://github.com/WebProject-xyz/php-openapi-mock-server). It eliminates the need for complex Docker setups or manual process management by handling the mock server lifecycle and providing fluent test actions.

---

## 🚀 Key Features

- **Zero-Config Lifecycle:** Automatically starts the mock server before your test suite and terminates it after completion.
- **Intelligent Discovery:** Auto-detects the mock server binary within your `vendor` directory or local development path.
- **Dynamic Response Control:** Change expected status codes and named examples per-test using simple PHP actions.
- **Headless & Fast:** Uses the native PHP built-in server for sub-millisecond overhead, perfect for CI/CD pipelines.
- **Seamless Integration:** Designed to work perfectly alongside Codeception's `REST` and `PhpBrowser` modules.

---

## 📦 Installation

Install the package via Composer:

```bash
composer require --dev webproject-xyz/codeception-module-openapi-server-mock
```

### Prerequisites
- **PHP:** ^8.3
- **Codeception:** ^5.0
- **Mock Server:** [php-openapi-mock-server](https://github.com/WebProject-xyz/php-openapi-mock-server) (installed automatically as a dependency)

---

## 🖥️ Configuration

Enable the module in your `acceptance.suite.yml` or `functional.suite.yml`:

```yaml
actor: AcceptanceTester
modules:
    enabled:
        - REST:
            depends: PhpBrowser
            url: http://localhost:8080/
        - PhpBrowser:
            url: http://localhost:8080/
        - WebProject\Codeception\Module\OpenApiServerMock:
            port: 8080
            spec: tests/Support/Data/openapi.yaml
            waitTimeout: 10
```

### Configuration Options

| Option | Description | Default |
| :--- | :--- | :--- |
| `path` | Path to the `php-openapi-mock-server` directory. | *Auto-detected* |
| `port` | Port the mock server should listen on. | `8080` |
| `host` | Host address for the server. | `localhost` |
| `spec` | Relative path to your OpenAPI `.yaml` or `.json` file. | `data/openapi.yaml` |
| `startServer` | Automatically start the server process. | `true` |
| `waitTimeout` | Seconds to wait for the server to become ready. | `5` |
| `stopOnFinish`| Terminate the server process after the suite ends. | `true` |

---

## 🛠️ Usage

Once configured, run `vendor/bin/codecept build` to generate the actions in your `AcceptanceTester`.

### Basic Actions

```php
// Force a specific HTTP status code for the next request
$I->haveOpenApiMockStatusCode(201);

// Force a specific named example from your OpenAPI spec
$I->haveOpenApiMockExample('SuccessResponse');

// Enable/Disable the mock server (useful for fallthrough testing)
$I->setOpenApiMockActive(false);

// Get the mock server URL dynamically
$url = $I->getOpenApiMockServerUrl();
```

---

## 💡 Advanced Mocking

The module allows you to test how your application handles various API scenarios, including error states and edge cases.

### Testing Error Handling
```php
public function testInternalServerError(AcceptanceTester $I) {
    // Force a 500 Internal Server Error
    $I->haveOpenApiMockStatusCode(500);
    $I->sendGet('/users');
    
    $I->seeResponseCodeIs(500);
}

public function testValidationFailure(AcceptanceTester $I) {
    // Force a 400 Bad Request
    $I->haveOpenApiMockStatusCode(400);
    $I->sendPost('/users', []); // Missing required data
    
    $I->seeResponseCodeIs(400);
}
```

### Using Named Examples
If your OpenAPI specification includes `examples` for a response, you can force the server to return a specific one:
```php
public function testSpecificMockData(AcceptanceTester $I) {
    $I->haveOpenApiMockExample('AliceUser');
    $I->sendGet('/users/1');
    
    $I->seeResponseContainsJson(['name' => 'Alice']);
}
```

---

## 🌐 CI/CD Integration

Since the module starts the mock server using the built-in PHP server, it works out-of-the-box in GitHub Actions without requiring Docker.

### GitHub Actions Example
```yaml
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
      - name: Install dependencies
        run: composer install
      - name: Build Codeception
        run: vendor/bin/codecept build
      - name: Run Tests
        run: vendor/bin/codecept run Acceptance
```

---

## 🔍 Troubleshooting

### Port already in use
If you see an error about the address being already in use, change the `port` in your suite configuration:
```yaml
WebProject\Codeception\Module\OpenApiServerMock:
    port: 8081 # Change from default 8080
```

### Mock server path not found
The module tries to auto-detect the path. If it fails, provide it explicitly:
```yaml
WebProject\Codeception\Module\OpenApiServerMock:
    path: ./vendor/webproject-xyz/php-openapi-mock-server
```

### Server start timeout
If your specification is very large, the server might take longer to initialize. Increase the `waitTimeout`:
```yaml
WebProject\Codeception\Module\OpenApiServerMock:
    waitTimeout: 15 # Wait up to 15 seconds
```

---

## 🧪 Development & Testing

We maintain high standards for this module:
- **Static Analysis:** PHPStan Level 8.
- **Coding Style:** Strict PSR-12/Symfony standards.
- **Automation:** GrumPHP hooks ensure all commits are verified.

### Commands
```bash
composer stan      # Run static analysis
composer test      # Run acceptance tests
composer cs:check  # Check coding standards
```

---

## 🤝 Contributing

Contributions are welcome! Please see our [CONTRIBUTING.md](CONTRIBUTING.md) for details.

1. Fork the Project.
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`).
3. Commit your Changes (`git commit -m 'feat: Add some AmazingFeature'`).
4. Push to the Branch (`git push origin feature/AmazingFeature`).
5. Open a Pull Request.

---

## 📜 License

Distributed under the **MIT** License. See `LICENSE` for more information.

---

## ✉️ Support & Contact

- **Issues:** Please use the [GitHub Issue Tracker](https://github.com/WebProject-xyz/codeception-module-openapi-server-mock/issues).
- **Website:** [webproject.xyz](https://www.webproject.xyz)
