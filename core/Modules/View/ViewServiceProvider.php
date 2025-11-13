<?php
namespace Nexus\Modules\View;

use Nexus\Bootstrap\ServiceProvider;

class ViewServiceProvider extends ServiceProvider {
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->registerFilesystem();
        $this->registerViewFinder();
        $this->registerEngine();
        $this->registerFactory();
    }

    /**
     * Register the filesystem implementation.
     *
     * @return void
     */
    public function registerFilesystem() {
        $this->container->bind('view.filesystem', function () {
            return new Filesystem();
        });
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder() {
        $this->container->bind('view.finder', function ($container) {
            $paths = [__DIR__ . '/../../../app/Views'];

            return new ViewFinder(
                $container->make('view.filesystem'),
                $paths,
                ['php', 'phtml']
            );
        });
    }

    /**
     * Register the engine implementation.
     *
     * @return void
     */
    public function registerEngine() {
        $this->container->bind('view.engine', function () {
            return new Engines\PhpEngine();
        });
    }

    /**
     * Register the view factory implementation.
     *
     * @return void
     */
    public function registerFactory() {
        $this->container->bind('view', function ($container) {
            $factory = new ViewFactory(
                $container->make('view.engine'),
                $container->make('view.finder')
            );

            // Initialize shared data
            $factory->share('app_name', 'Nexus Framework');
            $factory->share('app_version', '1.0.0');

            // Share helper functions
            $factory->share('url', [ViewHelpers::class, 'url']);
            $factory->share('asset', [ViewHelpers::class, 'asset']);
            $factory->share('route', [ViewHelpers::class, 'route']);
            $factory->share('csrf', [ViewHelpers::class, 'csrf']);
            $factory->share('method', [ViewHelpers::class, 'method']);
            $factory->share('select', [ViewHelpers::class, 'select']);
            $factory->share('checkbox', [ViewHelpers::class, 'checkbox']);
            $factory->share('radio', [ViewHelpers::class, 'radio']);

            return $factory;
        });
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        // No additional bootstrapping needed
    }
}