<?php
namespace Nexus\Modules\Exception;

use Nexus\Modules\Logging\Logger;
use Throwable;

abstract class ExceptionHandler
{
    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        Logger::error($exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
        Logger::error('Stack trace: ' . $exception->getTraceAsString());
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Throwable  $exception
     * @return mixed
     */
    abstract public function render(Throwable $exception);

    /**
     * Render an exception to the console.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function renderForConsole(Throwable $exception)
    {
        Logger::error('Exception: ' . $exception->getMessage());
        Logger::error('File: ' . $exception->getFile() . ':' . $exception->getLine());
        Logger::error('Stack trace:');
        Logger::error($exception->getTraceAsString());
    }
}