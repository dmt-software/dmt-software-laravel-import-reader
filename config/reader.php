<?php

use DMT\Laravel\Import\Reader\Exceptions\ReaderErrorHandler;

return [

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
    | Here you can add a factory that initiate a custom handler. These handlers
    | must implement the HandlerFactoryInterface or contain a Closure that can
    | initiate the handler.
    |
    | Example:
    |   SomeHandler => new SomeHandlerFactory(),
    |   MyHandler::class => fn (
    |      string|resource $source,
    |      array $config,
    |      array $sanitizers
    |   ) => new MyHandler($source, $sanitizers),
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
   | Empty this entry if you don't want to override the error handling during
   | reader usage.
   |
   */

    'error_handler' => ReaderErrorHandler::class,

];
