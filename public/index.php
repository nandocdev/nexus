<?php
// public/index.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../core/helpers.php';

use Nexus\Modules\Http\Request;
use Nexus\Modules\Http\Response;


// Cargar configuración
try {
    \Nexus\Modules\Config\Config::load('database');
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit;
}

// Inicializar aplicación
$app = new \Nexus\Bootstrap\Application();
$app->boot();

// Crear instancia de Request
$request = Request::capture();

// Obtener servicios del contenedor
$router = $app->make('router');
$middleware = $app->make('middleware');

// Registrar middlewares adicionales personalizados
$middleware->add('admin', function ($next) {
    // Middleware personalizado para administradores
    session_start();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo "Access denied";
        exit;
    }
    return $next();
});

$middleware->add('api', function ($next) {
    // Middleware para API
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    return $next();
});

// Cargar rutas desde archivo dedicado
require_once __DIR__ . '/../router/web.php';

// Manejar la solicitud
$route = $router->match($request->method(), $request->path());

if ($route) {
    try {
        if (!$router->runMiddleware($route['middleware'], $route['params'])) {
            // Middleware failed, response already sent
            exit;
        }

        $handler = $route['handler'];

        if (is_callable($handler)) {
            // Handler is a closure or callable
            $result = call_user_func_array($handler, array_merge([$request], $route['params']));
            if ($result instanceof Response) {
                $result->send();
            } elseif (is_string($result)) {
                echo $result;
            }
        } else {
            // Handler is a controller string
            list($controller, $method) = explode('@', $handler);
            $controllerClass = "App\\Controllers\\{$controller}";

            if (class_exists($controllerClass)) {
                $instance = new $controllerClass();
                $result = call_user_func_array([$instance, $method], array_merge([$request], $route['params']));
                if ($result instanceof Response) {
                    $result->send();
                } elseif (is_string($result)) {
                    echo $result;
                }
            } else {
                throw new \Nexus\Modules\Exception\RouteNotFoundException(
                    "Controller '{$controller}' not found",
                    $request->method(),
                    $request->path()
                );
            }
        }
    } catch (\Nexus\Modules\Exception\HttpException $e) {
        // HTTP exceptions are handled by the exception handler
        throw $e;
    } catch (\Exception $e) {
        // Convert other exceptions to HTTP exceptions
        throw new \Nexus\Modules\Exception\HttpException(500, $e->getMessage(), $e);
    }
} else {
    throw new \Nexus\Modules\Exception\RouteNotFoundException(
        'The requested resource was not found',
        $request->method(),
        $request->path()
    );
}