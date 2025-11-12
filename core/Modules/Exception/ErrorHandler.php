<?php
namespace Nexus\Modules\Exception;

use Nexus\Modules\Logging\Logger;

class ErrorHandler
{
    /**
     * Register the error handler.
     *
     * @return void
     */
    public static function register()
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handle PHP errors and convert them to exceptions.
     *
     * @param  int  $level
     * @param  string  $message
     * @param  string  $file
     * @param  int  $line
     * @return void
     *
     * @throws \ErrorException
     */
    public static function handleError($level, $message, $file = '', $line = 0)
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle uncaught exceptions.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public static function handleException(\Throwable $exception)
    {
        $handler = new Handler();
        $handler->report($exception);

        // If we're in CLI, render for console
        if (PHP_SAPI === 'cli') {
            $handler->renderForConsole($exception);
            exit(1);
        }

        // Otherwise, render HTTP response
        $response = $handler->render($exception);

        if (is_string($response)) {
            echo $response;
        }

        exit(1);
    }

    /**
     * Handle shutdown and catch fatal errors.
     *
     * @return void
     */
    public static function handleShutdown()
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $exception = new \ErrorException(
                $error['message'],
                $error['type'],
                0,
                $error['file'],
                $error['line']
            );

            self::handleException($exception);
        }
    }
}