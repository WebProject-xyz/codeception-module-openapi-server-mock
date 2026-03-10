# Contributing to Codeception Module for PHP OpenAPI Mock Server

First off, thank you for considering contributing to this project! It's people like you who make the open-source community such an amazing place to learn, inspire, and create.

## 🛠️ Development Setup

To get started with development, you'll need to have PHP 8.3+ and Composer installed on your machine.

1.  **Fork the repository** on GitHub.
2.  **Clone your fork** locally:
    ```bash
    git clone https://github.com/your-username/codeception-module-openapi-server-mock.git
    cd codeception-module-openapi-server-mock
    ```
3.  **Install dependencies**:
    ```bash
    composer install
    ```
4.  **Initialize GrumPHP**:
    GrumPHP is used to run quality checks on every commit. It should be initialized automatically, but you can run:
    ```bash
    vendor/bin/grumphp git:init
    ```

## 🧪 Running Tests

We use Codeception for our own internal testing. To run the tests, use:

```bash
composer test
```

This will run the acceptance tests which verify that the module correctly starts the mock server and interacts with it.

## 🔍 Quality Tools

We maintain high code quality standards. Before submitting a PR, ensure your code passes all checks:

- **Static Analysis**: `composer stan` (runs PHPStan at Level 8)
- **Coding Standard**: `composer cs:check` (verifies PSR-12/Symfony style)
- **Fixing Style**: `composer cs:fix` (automatically fixes most style issues)

## 📝 Pull Request Process

1.  **Create a new branch** for your feature or bugfix:
    ```bash
    git checkout -b feat/my-new-feature
    ```
2.  **Commit your changes**. GrumPHP will automatically run linting and tests. If any check fails, the commit will be blocked until fixed.
3.  **Ensure your commit messages follow conventional commits** (e.g., `feat: ...`, `fix: ...`).
4.  **Push to your fork** and **submit a Pull Request** to the `main` branch of the original repository.
5.  **Describe your changes** in detail in the PR description. Link any related issues.

## ⚖️ Code of Conduct

By participating in this project, you agree to abide by our Code of Conduct (based on the Contributor Covenant). Please be respectful and professional in all interactions.

## 📜 License

By contributing, you agree that your contributions will be licensed under the project's [MIT License](LICENSE).
