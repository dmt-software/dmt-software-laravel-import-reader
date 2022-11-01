<?php

use DMT\Laravel\Import\Reader\Exceptions\ReaderErrorHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | File extensions
    |--------------------------------------------------------------------------
    |
    | Here you may specify which reader handler will be used based on the file
    | extension.
    |
    | The extensions csv, json and xml are mapped to their handlers by default.
    |
    */

    'extensions' => [],

    /*
    |--------------------------------------------------------------------------
    | Sanitizers
    |--------------------------------------------------------------------------
    |
    | Here you may specify sanitizers to apply to the raw value of an item from
    | the reader, e.g. custom => MyCustomSanitizer::class.
    |
    | By default a trim and encoding sanitizer are configured.
    |
    */

    'sanitizers' => [],

    /*
    |--------------------------------------------------------------------------
    | Custom reader handlers
    |--------------------------------------------------------------------------
    |
    | By default custom readers will use the PHP internal SplFileObject to read
    | through a file line by line. This can be overridden by mapping a custom
    | reader to a Closure that returns its own reader handler.
    |
    | Example:
    |   MyReader => function ($file, $config, $sanitizers): HandlerInterface {}
    |
    */

    'handler_callbacks' => [],

    /*
   |--------------------------------------------------------------------------
   | Error handler class
   |--------------------------------------------------------------------------
   |
   | Register a class to handle the errors that are triggered by the reader
   | during iteration while the reader continues reading.
   |
   | When empty no handler is registered and, depending on the server config,
   | the reader breaks of iteration.
   |
   | By default the ReaderErrorHandler is used to ensure the errors are logged
   | without stopping the reader.
   |
   */

    'error_handler' => ReaderErrorHandler::class,

];