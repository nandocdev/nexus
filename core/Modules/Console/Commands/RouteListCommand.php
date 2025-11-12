<?php
namespace Nexus\Modules\Console\Commands;

use Nexus\Modules\Console\Command;

class RouteListCommand extends Command
{
    protected function configure()
    {
        $this->signature = 'route:list';
        $this->description = 'List all registered routes';
    }

    public function handle()
    {
        $this->info('Registered Routes');
        $this->line('================');

        // En un escenario real, esto vendría del Router registrado en el contenedor
        // Por ahora, simulamos cargando las rutas
        $routes = $this->getRoutes();

        if (empty($routes)) {
            $this->warning('No routes registered.');
            return;
        }

        $this->line('');
        $this->line('Method    Path                    Handler                    Middleware');
        $this->line('------    ----                    -------                    ----------');

        foreach ($routes as $route) {
            $method = str_pad($route['method'], 8);
            $path = str_pad($route['path'], 23);
            $handler = str_pad($route['handler'], 25);
            $middleware = implode(', ', $route['middleware'] ?: ['none']);

            $this->line("{$method} {$path} {$handler} {$middleware}");
        }

        $this->line('');
        $this->info('Total routes: ' . count($routes));
    }

    protected function getRoutes()
    {
        // Simular la carga de rutas desde router/web.php
        // En una implementación real, esto vendría del Router service
        $routes = [];

        // Intentar incluir el archivo de rutas para capturar las rutas registradas
        $routesFile = __DIR__ . '/../../../../router/web.php';

        if (file_exists($routesFile)) {
            // Aquí podríamos implementar un sistema para capturar las rutas
            // Por ahora, devolveremos un array vacío con un mensaje
            $this->warning('Route listing from file not yet implemented.');
            $this->line('Routes are loaded dynamically and not easily accessible from CLI.');
            return [];
        }

        return $routes;
    }
}