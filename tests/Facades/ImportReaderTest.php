<?php

namespace DMT\Test\Laravel\Import\Reader\Facades;

use DMT\Import\Reader\Decorators\Csv\ColumnMappingDecorator;
use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use DMT\Laravel\Import\Reader\Facades\ImportReader;
use DMT\Laravel\Import\Reader\Providers\ImportReaderServiceProvider;
use DMT\Test\Laravel\Import\Reader\Fixtures\Number;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Orchestra\Testbench\TestCase;

class ImportReaderTest extends TestCase
{
    protected function getApplicationProviders($app)
    {
        return [
            FilesystemServiceProvider::class,
            ImportReaderServiceProvider::class,
        ];
    }

    public function testReadFile()
    {
        $expected = [
            (object)['number' => 1, 'text' => 'one'],
            (object)['number' => 2, 'text' => 'two'],
            (object)['number' => 3, 'text' => 'three'],
        ];

        $reader = ImportReader::build(__DIR__ . '/../files/test.json', ['path' => '.']);

        foreach ($reader->read() as $item) {
            $this->assertContainsEquals($item, $expected);
        }
    }

    public function testReadWithDecorator()
    {
        $decorator = $this->getMockBuilder(DecoratorInterface::class)
            ->onlyMethods(['decorate'])
            ->getMockForAbstractClass();

        $decorator
            ->expects($this->exactly(3))
            ->method('decorate')
            ->willReturnCallback(
                function (\ArrayObject $currentRow) {
                    settype($currentRow['number'], 'int');
                    return $currentRow;
                }
            );

        $expected = [
            new \ArrayObject(['number' => 1, 'text' => 'one']),
            new \ArrayObject(['number' => 2, 'text' => 'two']),
            new \ArrayObject(['number' => 3, 'text' => 'three']),
        ];

        $config = [
            'trim' => [],
            'delimiter' => ';',
        ];

        $reader = ImportReader::build(__DIR__ . '/../files/test.csv', $config)
            ->addDecorator(new ColumnMappingDecorator(['number', 'text']))
            ->addDecorator($decorator);

        foreach ($reader->read(1) as $item) {
            $this->assertContainsEquals($item, $expected);
        }
    }

    public function testReadFileIntoArrays()
    {
        $expected = [
            ['number' => '1', 'text' => 'one'],
            ['number' => '2', 'text' => 'two'],
            ['number' => '3', 'text' => 'three'],
        ];

        $reader = ImportReader::buildToArrayReader(__DIR__ . '/../files/test.csv', [
            'trim' => [],
            'delimiter' => ';',
            'mapping' => ['number', 'text'],
        ]);

        /** skip reoccurring header */
        $filter = fn($currentRow) => array_keys($currentRow) <> array_values($currentRow);

        foreach ($reader->read(0, $filter) as $item) {
            $this->assertContains($item, $expected);
        }
    }

    public function testReadFileIntoObjects()
    {
        $expected = [
            new Number(1, 'one'),
            new Number(2, 'two'),
            new Number(3, 'three'),
        ];

        $reader = ImportReader::buildToObjectReader(__DIR__ . '/../files/test.xml', [
            'class' => Number::class,
            'mapping' => [
                '/numbers/text/@number' => 'number',
                '/numbers/text' => 'text',
            ],
        ]);

        foreach ($reader->read() as $item) {
            $this->assertContainsEquals($item, $expected);
        }
    }

    /**
     * @dataProvider provideHandlerFile
     *
     * @param string $file
     * @param string $expectedHandler
     */
    public function testCreateHandler(string $file, string $expectedHandler)
    {
        $this->assertInstanceOf($expectedHandler, ImportReader::createHandler($file, []));
    }

    public function provideHandlerFile(): iterable
    {
        return [
            [__DIR__ . '/../files/test.csv', CsvReaderHandler::class],
            [__DIR__ . '/../files/test.json', JsonReaderHandler::class],
            [__DIR__ . '/../files/test.xml', XmlReaderHandler::class],
        ];
    }
}
