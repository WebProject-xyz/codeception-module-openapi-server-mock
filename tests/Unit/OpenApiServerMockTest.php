<?php

declare(strict_types=1);

namespace WebProject\Codeception\Module\Tests\Unit;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use Codeception\Module\PhpBrowser;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use function in_array;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use WebProject\Codeception\Module\OpenApiServerMock;
use WebProject\Codeception\Module\Tests\Support\UnitTester;
use WebProject\PhpOpenApiMockServer\Middleware\MockMiddleware\OpenApiMockMiddleware;

class OpenApiServerMockTest extends Unit
{
    protected UnitTester $tester;

    private OpenApiServerMock $module;

    /**
     * @var ModuleContainer&MockObject
     */
    private $moduleContainer;

    protected function _before(): void
    {
        $this->moduleContainer = $this->createMock(ModuleContainer::class);
        $this->module          = new OpenApiServerMock($this->moduleContainer);
    }

    public function testGetOpenApiMockServerUrl(): void
    {
        // Act
        $url = $this->module->getOpenApiMockServerUrl();

        // Assert
        self::assertSame('http://localhost:8080', $url);
    }

    public function testAutoDetectPath(): void
    {
        // Arrange
        $reflection = new ReflectionClass($this->module);
        $method     = $reflection->getMethod('autoDetectPath');
        $method->setAccessible(true);

        // Act
        $path = $method->invoke($this->module);

        // Assert
        self::assertNotNull($path);
        self::assertDirectoryExists($path);
        self::assertFileExists($path . '/bin/openapi-mock-server');
    }

    public function testValidatePathSuccess(): void
    {
        // Arrange
        $reflection = new ReflectionClass($this->module);
        $method     = $reflection->getMethod('validatePath');
        $method->setAccessible(true);
        $path = $reflection->getMethod('autoDetectPath')->invoke($this->module);

        // Act & Assert (should not throw exception)
        $method->invoke($this->module, $path);
    }

    public function testValidatePathFailure(): void
    {
        // Arrange
        $reflection = new ReflectionClass($this->module);
        $method     = $reflection->getMethod('validatePath');
        $method->setAccessible(true);

        // Assert
        $this->expectException(ModuleConfigException::class);

        // Act
        $method->invoke($this->module, '/non/existent/path');
    }

    public function testValidateSpecFailure(): void
    {
        // Arrange
        $reflection = new ReflectionClass($this->module);
        $method     = $reflection->getMethod('validateSpec');
        $method->setAccessible(true);

        // Assert
        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessage('OpenAPI specification file not found');

        // Act
        $method->invoke($this->module, 'non-existent-spec.yaml');
    }

    public function testHaveOpenApiMockStatusCode(): void
    {
        // Arrange
        $rest       = $this->createMock(REST::class);
        $phpBrowser = $this->createMock(PhpBrowser::class);

        $this->moduleContainer->expects(self::any())->method('hasModule')->willReturnCallback(static function ($name) {
            return in_array($name, ['REST', 'PhpBrowser'], true);
        });

        $this->moduleContainer->expects(self::any())->method('getModule')->willReturnCallback(static function ($name) use ($rest, $phpBrowser) {
            return 'REST' === $name ? $rest : $phpBrowser;
        });

        $rest->expects(self::once())
            ->method('haveHttpHeader')
            ->with(OpenApiMockMiddleware::HEADER_OPENAPI_MOCK_STATUSCODE, '404');

        $phpBrowser->expects(self::once())
            ->method('setHeader')
            ->with(OpenApiMockMiddleware::HEADER_OPENAPI_MOCK_STATUSCODE, '404');

        // Act
        $this->module->haveOpenApiMockStatusCode(404);
    }

    public function testHaveOpenApiMockExample(): void
    {
        // Arrange
        $rest = $this->createMock(REST::class);
        $this->moduleContainer->expects(self::any())->method('hasModule')->willReturnMap([['REST', true], ['PhpBrowser', false]]);
        $this->moduleContainer->expects(self::any())->method('getModule')->with('REST')->willReturn($rest);

        $rest->expects(self::once())
            ->method('haveHttpHeader')
            ->with(OpenApiMockMiddleware::HEADER_OPENAPI_MOCK_EXAMPLE, 'my-example');

        // Act
        $this->module->haveOpenApiMockExample('my-example');
    }

    public function testSetOpenApiMockActive(): void
    {
        // Arrange
        $rest = $this->createMock(REST::class);
        $this->moduleContainer->expects(self::any())->method('hasModule')->willReturnMap([['REST', true], ['PhpBrowser', false]]);
        $this->moduleContainer->expects(self::any())->method('getModule')->with('REST')->willReturn($rest);

        $rest->expects(self::once())
            ->method('haveHttpHeader')
            ->with(OpenApiMockMiddleware::HEADER_OPENAPI_MOCK_ACTIVE, 'true');

        // Act
        $this->module->setOpenApiMockActive(true);
    }

    public function testInitializeWithDefaults(): void
    {
        // Arrange
        $reflection = new ReflectionClass($this->module);
        $configProp = $reflection->getProperty('config');
        $configProp->setAccessible(true);
        $config         = $configProp->getValue($this->module);
        $config['spec'] = 'tests/Support/Data/openapi.yaml';
        $configProp->setValue($this->module, $config);

        // Act
        $this->module->_initialize();

        // Assert
        $config = $configProp->getValue($this->module);

        self::assertNotNull($config['path']);
        self::assertDirectoryExists($config['path']);
    }
}
