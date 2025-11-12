<?php
namespace Nexus\Modules\Http;

use Nexus\Bootstrap\ServiceProvider;

class HttpServiceProvider extends ServiceProvider {
    public function register() {
        $this->container->bind('middleware', function() {
            return new Middleware();
        });

        $this->container->bind('router', function() {
            $middleware = $this->container->make('middleware');
            return new Router($middleware);
        });

        $this->container->bind('controller', function() {
            return new Controller();
        });
    }
}