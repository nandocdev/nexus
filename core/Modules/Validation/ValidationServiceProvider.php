<?php
namespace Nexus\Modules\Validation;

use Nexus\Bootstrap\ServiceProvider;
use Nexus\Modules\Validation\Validator;

class ValidationServiceProvider extends ServiceProvider {
    public function register() {
        $this->container->bind('validator', function($container) {
            return new Validator($container);
        });
    }
}
