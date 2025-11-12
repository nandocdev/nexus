<?php
namespace Nexus\Modules\Exception;

use Nexus\Bootstrap\ServiceProvider;

class ExceptionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container->bind('exception.handler', function () {
            return new Handler();
        });

        $this->container->bind('error.handler', function () {
            return new ErrorHandler();
        });
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register error handlers only for web requests, not CLI
        if (PHP_SAPI !== 'cli') {
            ErrorHandler::register();
        }

        // Set custom exception handler for the application
        $handler = $this->container->make('exception.handler');

        // Override the default exception handler
        set_exception_handler(function ($exception) use ($handler) {
            $handler->report($exception);

            if (PHP_SAPI === 'cli') {
                $handler->renderForConsole($exception);
                exit(1);
            }

            $response = $handler->render($exception);
            if (is_string($response)) {
                echo $response;
            }
            // Don't exit in web context - let the script continue
        });
    }
}