<?php
namespace Nexus\Modules\Config;

use Nexus\Bootstrap\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider {
    public function register() {
        // La configuración se carga automáticamente en Config::init()
        // No se necesita registro adicional en el contenedor
    }
}