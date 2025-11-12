<?php
namespace Nexus\Modules\Logging;

use Nexus\Bootstrap\ServiceProvider;

class LoggingServiceProvider extends ServiceProvider {
    public function register() {
        $this->container->bind('logger', function() {
            return new Logger();
        });
    }
}