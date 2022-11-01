<?php

namespace DMT\Laravel\Import\Reader\Facades;

use DMT\Import\Reader\ReaderBuilder;
use Illuminate\Support\Facades\Facade;

class ImportReader extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ReaderBuilder::class;
    }
}
