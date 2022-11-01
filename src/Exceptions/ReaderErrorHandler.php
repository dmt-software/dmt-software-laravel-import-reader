<?php

namespace DMT\Laravel\Import\Reader\Exceptions;

use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Exceptions\DecoratorException;
use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Laravel\Import\Reader\Contracts\ErrorHandler;
use Error;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReaderErrorHandler implements ErrorHandler
{
    /** @var \Illuminate\Contracts\Debug\ExceptionHandler */
    private ExceptionHandler $exceptionHandler;

    /** @var callable|null */
    private $errorHandler = null;

    public function __construct(ExceptionHandler $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
    }

    public function register(): void
    {
        set_error_handler([$this, 'toException']);

        if ($this->exceptionHandler instanceof Handler) {
            $this->exceptionHandler->reportable(function (ExceptionInterface $exception) {
                Log::warning($exception->getMessage());
            })->stop();
        }
    }

    public function toException($code, $message, $file, $line, $context)
    {
        $this->fixTypeHintNoticeForPhp7($code, $message);

        if ($this->logDecoratorError($context['exception'] ?? null, $context['position'] ?? 0)) {
            return false;
        }

        return call_user_func($this->errorHandler, $code, $message, $file, $line, $context);
    }

    /**
     * @param \Throwable|null $exception
     * @param int $atPosition
     * @return bool
     */
    private function logDecoratorError(?Throwable $exception, int $atPosition): bool
    {
        if (!$exception instanceof DecoratorException) {
            return false;
        }

        try {
            $this->exceptionHandler->report(
                new DecoratorException(sprintf('Row %d: %s', $atPosition, $exception->getMessage()), 0, $exception)
            );

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Fixes PHP 7 notices when a type hinted property is set with an invalid value by a Decorator
     * that are considered (Type)Errors in PHP 8.
     *
     * @param int $code
     * @param string $message
     * @return void
     */
    private function fixTypeHintNoticeForPhp7(int $code, string $message): void
    {
        if (PHP_MAJOR_VERSION < 8) {
            $mightBeDecorator = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3)[2]['object'] ?? null;
            if ($code === E_NOTICE && $mightBeDecorator instanceof DecoratorInterface) {
                throw new Error($message, E_ERROR);
            }
        }
    }
}