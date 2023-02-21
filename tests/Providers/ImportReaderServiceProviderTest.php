<?php

namespace DMT\Test\Laravel\Import\Reader\Providers;

use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\HandlerFactory;
use DMT\Import\Reader\Handlers\HandlerInterface;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;
use DMT\Import\Reader\ReaderBuilder;
use DMT\Laravel\Import\Reader\Contracts\ErrorHandler;
use DMT\Laravel\Import\Reader\Exceptions\ReaderErrorHandler;
use DMT\Laravel\Import\Reader\Facades\ImportReader;
use DMT\Laravel\Import\Reader\Providers\ImportReaderServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Support\Facades\App;
use Mockery;
use Orchestra\Testbench\TestCase;

class ImportReaderServiceProviderTest extends TestCase
{
    protected function getApplicationProviders($app)
    {
        return [
            FilesystemServiceProvider::class,
        ];
    }

    public function testRegistration()
    {
        $serviceProvider = App::register(ImportReaderServiceProvider::class);
        $this->assertInstanceOf(ImportReaderServiceProvider::class, $serviceProvider);
        $this->assertInstanceOf(ReaderErrorHandler::class, App::get(ErrorHandler::class));
        $this->assertInstanceOf(ReaderBuilder::class, App::get(ReaderBuilder::class));
        $this->assertInstanceOf(HandlerFactory::class, App::get(HandlerFactory::class));
    }

    public function testConfigureSanitizers()
    {
        $appendSanitizer = new class implements SanitizerInterface {
            public function sanitize($row) {
                return substr($row, 0, -1) . ', "append": true}';
            }
        };

        $this->configureImportReaderServiceProvider([
            'sanitizers' => ['dummy' => get_class($appendSanitizer)]
        ]);

        $reader = ImportReader::build(__DIR__ . '/../files/test.json', [
            'path' => '.',
            'dummy' => [],
        ]);

        foreach ($reader->read() as $row) {
            $this->assertTrue($row->append);
        }
    }

    public function testConfigureReaderHandler()
    {
        $this->configureImportReaderServiceProvider([
            'handler_callbacks' => ['CustomHandler' => function () {
                return Mockery::namedMock('CustomHandler', HandlerInterface::class);
            }]
        ]);

        $this->assertInstanceOf(
            HandlerInterface::class,
            ImportReader::createHandler(__FILE__, ['handler' => 'CustomHandler'])
        );
    }

    public function testConfigureErrorHandler()
    {
        $defaultErrorHandler = ReaderErrorHandler::class;

        $this->configureImportReaderServiceProvider(['error_handler' => null]);

        [$handler] = set_error_handler(function () {});

        $this->assertNotInstanceOf($defaultErrorHandler, $handler);

        restore_error_handler();
    }

    protected function configureImportReaderServiceProvider(array $config = []): void
    {
        $config = array_replace(include __DIR__ . '/../../config/reader.php', $config);

        $serviceProvider = Mockery::mock(ImportReaderServiceProvider::class, [$this->app])->makePartial();
        $serviceProvider->shouldAllowMockingProtectedMethods();
        $serviceProvider->shouldReceive('mergeConfigFrom')
            ->andReturnUsing(
                function () use ($config) {
                    $this->app->make('config')->set('reader', $config);
                }
            );
        $serviceProvider->register();
    }
}
