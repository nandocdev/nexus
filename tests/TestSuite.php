<?php
// tests/TestSuite.php
require_once __DIR__ . '/../core/Bootstrap/Autoloader.php';
\Nexus\Bootstrap\Autoloader::register();

// Load environment
\Nexus\Modules\Config\Env::load();

// Test Config
echo "Testing Config...\n";
\Nexus\Modules\Config\Config::load('app');
$name = \Nexus\Modules\Config\Config::get('name');
assert($name === 'Mi Proyecto', "Config test failed");
echo "Config test passed\n";

// Test Validator
echo "Testing Validator...\n";
$validator = new \Nexus\Modules\Validation\Validator(['name' => '', 'email' => 'invalid'], [
    'name' => 'required',
    'email' => 'email'
]);
assert(!$validator->validate(), "Validator should fail");
assert(count($validator->errors()) === 2, "Should have 2 errors");
echo "Validator test passed\n";

// Test Router
echo "Testing Router...\n";
$router = new \Nexus\Modules\Http\Router();
$router->add('GET', '/test/{id}', 'TestController@show', 'test.show');
$route = $router->match('GET', '/test/123');
assert($route['handler'] === 'TestController@show', "Route handler incorrect");
assert($route['params'][0] === '123', "Route param incorrect");
echo "Router test passed\n";

echo "All tests passed!\n";