<?php
/**
 * Web Routes
 *
 * Aquí se definen todas las rutas de la aplicación web.
 * Las rutas se organizan por funcionalidad y tipo de middleware.
 */

// =====================================================================================
// RUTAS WEB PÚBLICAS
// =====================================================================================

/**
 * Ruta de inicio - Página principal
 */
$router->add('GET', '/', 'HomeController@index', 'home', ['web']);

/**
 * Rutas de autenticación
 */
$router->add('GET', '/login', 'AuthController@loginForm', 'login', ['web', 'guest']);
$router->add('POST', '/login', 'AuthController@login', 'login.post', ['web', 'guest']);
$router->add('POST', '/logout', 'AuthController@logout', 'logout', ['web']);

// =====================================================================================
// RUTAS PROTEGIDAS (REQUIEREN AUTENTICACIÓN)
// =====================================================================================

/**
 * Rutas de gestión de usuarios
 */
$router->add('GET', '/users', 'UserController@index', 'users.index', ['web', 'auth']);
$router->add('GET', '/users/create', 'UserController@create', 'users.create', ['web', 'auth']);
$router->add('POST', '/users', 'UserController@store', 'users.store', [
    'web',
    'auth',
    'validate' => [
        'name' => 'required|min:2|max:255',
        'email' => 'required|email',
        'password' => 'required|min:6'
    ]
]);
$router->add('GET', '/users/{id}', 'UserController@show', 'users.show', ['web', 'auth']);
$router->add('GET', '/users/{id}/edit', 'UserController@edit', 'users.edit', ['web', 'auth']);
$router->add('PUT', '/users/{id}', 'UserController@update', 'users.update', [
    'web',
    'auth',
    'validate' => [
        'name' => 'required|min:2|max:255',
        'email' => 'required|email'
    ]
]);
$router->add('DELETE', '/users/{id}', 'UserController@delete', 'users.delete', ['web', 'auth']);

// =====================================================================================
// RUTAS DE API
// =====================================================================================

/**
 * API de usuarios (JSON responses)
 */
$router->add('GET', '/api/users', 'ApiController@index', 'api.users', ['api', 'auth']);
$router->add('POST', '/api/users', 'ApiController@store', 'api.users.store', [
    'api',
    'auth',
    'validate' => [
        'name' => 'required|min:2|max:255',
        'email' => 'required|email',
        'password' => 'required|min:6'
    ]
]);
$router->add('GET', '/api/users/{id}', 'ApiController@show', 'api.users.show', ['api', 'auth']);
$router->add('PUT', '/api/users/{id}', 'ApiController@update', 'api.users.update', ['api', 'auth']);
$router->add('DELETE', '/api/users/{id}', 'ApiController@delete', 'api.users.delete', ['api', 'auth']);

// =====================================================================================
// RUTAS DE ADMINISTRACIÓN (REQUIEREN ROL ADMIN)
// =====================================================================================

/**
 * Rutas administrativas - Ejemplo
 * Descomentear cuando se implemente el sistema de roles
 */
// $router->add('GET', '/admin', 'AdminController@index', 'admin.dashboard', ['web', 'auth', 'admin']);
// $router->add('GET', '/admin/users', 'AdminController@users', 'admin.users', ['web', 'auth', 'admin']);

// =====================================================================================
// RUTAS DE RECURSOS ESTÁTICOS Y UTILIDADES
// =====================================================================================

/**
 * Health check - Para monitoreo
 */
$router->add('GET', '/health', function() {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'ok',
        'timestamp' => date('c'),
        'version' => '1.0.0'
    ]);
}, 'health', ['cors']);

/**
 * Ruta de prueba - Para desarrollo
 */
$router->add('GET', '/test', function() {
    echo "<h1>Test Route</h1>";
    echo "<p>Router funcionando correctamente</p>";
    echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";
}, 'test', ['web']);

/**
 * Ruta de prueba de errores
 */
$router->add('GET', '/test-error', function() {
    throw new \Exception('This is a test error for demonstration purposes');
}, 'test.error', ['web']);

/**
 * Ruta de prueba de validación
 */
$router->add('POST', '/test-validation', function() {
    validate($_POST, [
        'name' => 'required|min:3',
        'email' => 'required|email',
    ]);

    echo json_encode(['success' => true, 'message' => 'Validation passed']);
}, 'test.validation', ['api']);

// =====================================================================================
// RUTAS DE ERROR (Fallback)
// =====================================================================================

/**
 * Ruta catch-all para páginas 404
 * NOTA: Esta debe ser la última ruta registrada
 */
// $router->add('GET', '{any}', 'ErrorController@notFound', '404', ['web']);

