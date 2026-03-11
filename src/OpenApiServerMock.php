<?php

declare(strict_types=1);

namespace WebProject\Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Module;
use Codeception\Module\PhpBrowser;
use Codeception\Module\REST;
use function dirname;
use function fclose;
use function file_exists;
use function fsockopen;
use function is_dir;
use function realpath;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;
use function time;
use function usleep;
use WebProject\PhpOpenApiMockServer\Middleware\MockMiddleware\OpenApiMockMiddleware;

/**
 * Class OpenApiServerMock.
 *
 * Provides actions to control the PHP OpenAPI Mock Server.
 */
class OpenApiServerMock extends Module implements DependsOnModule
{
    /**
     * @var array<string, mixed>
     */
    protected array $config = [
        'path'         => null,
        'port'         => 8080,
        'host'         => 'localhost',
        'spec'         => 'data/openapi.yaml',
        'startServer'  => true,
        'waitTimeout'  => 5,
        'stopOnFinish' => true,
    ];

    protected ?Process $process = null;

    public function _initialize(): void
    {
        if (null === $this->config['path']) {
            $this->config['path'] = $this->autoDetectPath();
        }

        if ($this->config['startServer']) {
            $this->validatePath((string) $this->config['path']);
            $this->validateSpec((string) $this->config['spec']);
        }
    }

    private function validateSpec(string $spec): void
    {
        if (empty($spec) || !file_exists($spec)) {
            throw new ModuleConfigException($this, "OpenAPI specification file not found at '{$spec}'.");
        }
    }

    private function autoDetectPath(): ?string
    {
        // Try to find via class reflection (most reliable)
        try {
            $reflection = new ReflectionClass(OpenApiMockMiddleware::class);
            $fileName   = $reflection->getFileName();
            if ($fileName) {
                $path = realpath(dirname($fileName, 4));

                return false === $path ? null : $path;
            }
        } catch (Throwable) {
            // Class not found, fallback to directory traversal
        }

        // Fallback: Sibling in vendor
        $siblingPath = dirname(__DIR__, 2) . '/php-openapi-mock-server';
        if (is_dir($siblingPath)) {
            return $siblingPath;
        }

        return null;
    }

    private function validatePath(string $path): void
    {
        if (empty($path) || !is_dir($path)) {
            throw new ModuleConfigException($this, "Mock server path not found. Provide 'path' in config or ensure 'webproject-xyz/php-openapi-mock-server' is installed.");
        }

        if (!file_exists($path . '/bin/openapi-mock-server') && !file_exists($path . '/public/index.php')) {
            throw new ModuleConfigException($this, "Path '{$path}' is not a valid php-openapi-mock-server directory.");
        }
    }

    /**
     * @return array<int, string>
     */
    public function _depends(): array
    {
        return [];
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function _beforeSuite(array $settings = []): void
    {
        if ($this->config['startServer']) {
            $this->startMockServer();
        }
    }

    public function _afterSuite(): void
    {
        if ($this->config['stopOnFinish'] && $this->process?->isRunning()) {
            $this->process->stop();
        }
    }

    protected function startMockServer(): void
    {
        if ($this->isPortInUse()) {
            throw new RuntimeException("Port {$this->config['port']} is already in use. Cannot start mock server.");
        }

        $binPath = $this->config['path'] . '/bin/openapi-mock-server';
        if (!file_exists($binPath)) {
            $binPath = $this->config['path'] . '/public/index.php';
        }

        $phpBinary = (new PhpExecutableFinder())->find();
        if (!$phpBinary) {
            throw new RuntimeException('PHP executable not found.');
        }

        $specPath = (string) $this->config['spec'];
        if (file_exists($specPath)) {
            $specPath = (string) realpath($specPath);
        }

        $command = [$phpBinary, '-S', "{$this->config['host']}:{$this->config['port']}", $binPath];
        $env     = ['OPENAPI_SPEC' => $specPath];

        $this->process = new Process($command, (string) $this->config['path'], $env);
        $this->process->start();

        if (!$this->waitForServer()) {
            $errorOutput = $this->process->getErrorOutput();
            throw new RuntimeException("Mock server failed to start. Error: {$errorOutput}");
        }
    }

    private function isPortInUse(): bool
    {
        $fp = @fsockopen($this->config['host'], (int) $this->config['port'], $errno, $errstr, 0.1);
        if ($fp) {
            fclose($fp);

            return true;
        }

        return false;
    }

    private function waitForServer(): bool
    {
        $start = time();
        while (time() - $start < (int) $this->config['waitTimeout']) {
            $fp = @fsockopen($this->config['host'], (int) $this->config['port'], $errno, $errstr, 0.1);
            if ($fp) {
                fclose($fp);

                return true;
            }
            if (null === $this->process || !$this->process->isRunning()) {
                return false;
            }
            usleep(100000);
        }

        return false;
    }

    /**
     * Set the expected status code for the next mock response.
     */
    public function haveOpenApiMockStatusCode(int $code): void
    {
        $this->setHttpHeader(OpenApiMockMiddleware::HEADER_OPENAPI_MOCK_STATUSCODE, (string) $code);
    }

    /**
     * Set the expected example name for the next mock response.
     */
    public function haveOpenApiMockExample(string $example): void
    {
        $this->setHttpHeader(OpenApiMockMiddleware::HEADER_OPENAPI_MOCK_EXAMPLE, $example);
    }

    /**
     * Enable or disable the mock server for the next request.
     */
    public function setOpenApiMockActive(bool $active): void
    {
        $this->setHttpHeader(OpenApiMockMiddleware::HEADER_OPENAPI_MOCK_ACTIVE, $active ? 'true' : 'false');
    }

    public function getOpenApiMockServerUrl(): string
    {
        return "http://{$this->config['host']}:{$this->config['port']}";
    }

    protected function setHttpHeader(string $name, string $value): void
    {
        if ($this->hasModule('REST')) {
            /** @var REST $rest */
            $rest = $this->getModule('REST');
            $rest->haveHttpHeader($name, $value);
        }

        if ($this->hasModule('PhpBrowser')) {
            /** @var PhpBrowser $phpBrowser */
            $phpBrowser = $this->getModule('PhpBrowser');
            $phpBrowser->setHeader($name, $value);
        }
    }
}
