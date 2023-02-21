<?php

namespace DMT\Laravel\Import\Reader\Providers;

use Closure;
use DMT\Import\Reader\Handlers\Factories\CallbackHandlerFactory;
use DMT\Import\Reader\Handlers\HandlerFactory;
use DMT\Import\Reader\ReaderBuilder;
use DMT\Laravel\Import\Reader\Contracts\ErrorHandler;
use Illuminate\Support\ServiceProvider;

class ImportReaderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/reader.php' => config_path('reader.php')
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/reader.php', 'reader');

        $this->app->bind(ReaderBuilder::class, function () {
            $builder = new ReaderBuilder($this->app->make(HandlerFactory::class));

            foreach (config('reader.sanitizers') as $configKey => $sanitizerClassName) {
                $builder->addSanitizer($configKey, $sanitizerClassName);
            }

            return $builder;
        });

        $this->app->bind(HandlerFactory::class, function () {
            $factory = new HandlerFactory();

            foreach (config('reader.handler_callbacks', []) as $handler => $callback) {
                if ($callback instanceof Closure) {
                    $callback = new CallbackHandlerFactory($callback);
                }
                $factory->addInitializeHandlerFactory($handler, $callback);
            }

            return $factory;
        });

        if (config('reader.error_handler')) {
            $this->app->bind(ErrorHandler::class, config('reader.error_handler'));
            $this->app->make(ErrorHandler::class)->register();
        }
    }
}
