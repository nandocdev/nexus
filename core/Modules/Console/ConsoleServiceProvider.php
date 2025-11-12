<?php
namespace Nexus\Modules\Console;

use Nexus\Bootstrap\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->container->bind('console.kernel', function() {
            return new Kernel($this->container);
        });
    }

    public function boot()
    {
        // El kernel se inicializa cuando se ejecuta desde CLI
    }
}