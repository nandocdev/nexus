<?php
namespace Nexus\Modules\Database;

use Nexus\Bootstrap\ServiceProvider;
use Nexus\Modules\Config\Config;

class DatabaseServiceProvider extends ServiceProvider {
    public function register() {
        // Cargar configuración de base de datos
        Config::load('database');

        $this->container->bind('database', function () {
            return Database::getInstance();
        });
    }
}