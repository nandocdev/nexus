<?php
namespace Nexus\Modules\Auth;

use Nexus\Bootstrap\ServiceProvider;

class AuthServiceProvider extends ServiceProvider {
    public function register() {
        // El modelo de usuario ya está configurado en la clase Auth
        // No se necesita registro adicional en el contenedor
    }
}