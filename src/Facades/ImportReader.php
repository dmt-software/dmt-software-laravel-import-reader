<?php

namespace DMT\Laravel\Import\Reader\Facades;

use DMT\Import\Reader\Handlers\HandlerInterface;
use DMT\Import\Reader\Reader;
use DMT\Import\Reader\ReaderBuilder;
use DMT\Import\Reader\ToArrayReader;
use DMT\Import\Reader\ToObjectReader;
use Illuminate\Support\Facades\Facade;

/**
 * Class ImportReader
 *
 * @method Reader build(string $file, array $options)
 * @method ToArrayReader buildToArrayReader(string $file, array $options)
 * @method ToObjectReader buildToObjectReader(string $file, array $options)
 * @method HandlerInterface createHandler(string $file, array $options)
 */
class ImportReader extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ReaderBuilder::class;
    }
}
