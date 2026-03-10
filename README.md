# Codeception Module for PHP OpenAPI Mock Server

[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue.svg)](https://www.php.net/)

This Codeception module provides easy access to and management of the [PHP OpenAPI Mock Server](https://github.com/WebProject-xyz/php-openapi-mock-server). It allows you to automatically start/stop the mock server and control its behavior (status codes, examples, activation) directly from your tests.

## 🚀 Features

- **Auto-Start/Stop:** Automatically starts the mock server before the test suite and stops it afterwards.
- **Dynamic Control:** Change expected status codes and examples per test.
- **Toggle Activation:** Easily enable or disable the mock server for specific requests.
- **Integration:** Seamlessly works with `REST` and `PhpBrowser` modules.

## 📦 Installation

```bash
composer require --dev webproject-xyz/codeception-module-openapi-server-mock
```

## 🖥️ Configuration

Enable the module in your `acceptance.suite.yml` (or `functional.suite.yml` if you use it for API tests):

```yaml
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
| `path` | Absolute path to the `php-openapi-mock-server` directory. | *Auto-detected in vendor* |
| `port` | Port the mock server should run on. | `8080` |
| `host` | Host the mock server should bind to. | `localhost` |
| `spec` | Path to your OpenAPI specification file (`.yaml` or `.json`). | `data/openapi.yaml` |
| `startServer` | Whether to automatically start the server. | `true` |
| `waitTimeout` | Seconds to wait for the server to become ready. | `5` |
| `stopOnFinish` | Whether to automatically stop the server after the suite. | `true` |

## 🛠️ Usage

### Basic Usage

The module provides several actions to your `AcceptanceTester` (or `FunctionalTester`):

```php
// Force a specific status code for the next request
$I->haveOpenApiMockStatusCode(201);

// Force a specific named example from the spec
$I->haveOpenApiMockExample('success-case');

// Disable the mock server for the next request (falls through to original app if configured)
$I->setOpenApiMockActive(false);

// Get the base URL of the mock server
$url = $I->getOpenApiMockServerUrl();
```

### Example Test

```php
public function testCreateUser(AcceptanceTester $I)
{
    $I->haveOpenApiMockStatusCode(201);
    $I->sendPost('/users', ['name' => 'John Doe']);
    $I->seeResponseCodeIs(201);
}
```

## 📜 License

Distributed under the **MIT** License. See `LICENSE` for more information.
