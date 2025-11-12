<?php
namespace Nexus\Modules\Database;

use Nexus\Bootstrap\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider {
    public function register() {
        $this->container->bind('database', function() {
            return Database::getInstance();
        });
    }
}