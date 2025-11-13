<?php
namespace Nexus\Modules\Http;

use Nexus\Bootstrap\ServiceProvider;

class HttpServiceProvider extends ServiceProvider {
    public function register() {
        $this->container->bind('middleware', function () {
            return new Middleware();
        });

        $this->container->bind('router', function () {
            $middleware = $this->container->make('middleware');
            return new Router($middleware);
        });

        $this->container->bind('controller', function () {
            return new Controller();
        });

        $this->container->bind('request', function () {
            return Request::capture();
        });

        $this->container->bind('response', function () {
            return new Response();
        });

        $this->container->bind(RequestInterface::class, function () {
            return Request::capture();
        });

        $this->container->bind(ResponseInterface::class, function () {
            return new Response();
        });

        // Register new HTTP abstractions
        $this->container->bind('rate_limiter', function () {
            return new RateLimitMiddleware();
        });
    }
}