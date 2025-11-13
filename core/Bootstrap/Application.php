<?php
namespace Nexus\Bootstrap;

class Application {
    private Container $container;
    private array $providers = [];
    private static ?Application $instance = null;

    public function __construct() {
        self::$instance = $this;
        $this->container = new Container();
        $this->registerBaseProviders();
    }

    public static function getInstance(): ?Application {
        return self::$instance;
    }

    private function registerBaseProviders() {
        // Providers esenciales del framework
        $this->registerProvider(\Nexus\Modules\Config\ConfigServiceProvider::class);
        $this->registerProvider(\Nexus\Modules\Database\DatabaseServiceProvider::class);
        $this->registerProvider(\Nexus\Modules\Http\HttpServiceProvider::class);
        $this->registerProvider(\Nexus\Modules\Auth\AuthServiceProvider::class);
        $this->registerProvider(\Nexus\Modules\Validation\ValidationServiceProvider::class);
        $this->registerProvider(\Nexus\Modules\Logging\LoggingServiceProvider::class);
        $this->registerProvider(\Nexus\Modules\View\ViewServiceProvider::class);
        $this->registerProvider(\Nexus\Modules\Console\ConsoleServiceProvider::class);
        $this->registerProvider(\Nexus\Modules\Exception\ExceptionServiceProvider::class);
        $this->registerProvider(\Nexus\Modules\View\ViewServiceProvider::class);
    }

    public function registerProvider(string $providerClass): void {
        $provider = new $providerClass();
        $provider->setContainer($this->container);
        $provider->register();
        $this->providers[] = $provider;
    }

    public function boot(): void {
        foreach ($this->providers as $provider) {
            $provider->boot();
        }
    }

    public function getContainer(): Container {
        return $this->container;
    }

    public function make(string $abstract) {
        return $this->container->make($abstract);
    }
}