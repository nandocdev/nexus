<?php
namespace Nexus\Modules\Http;

class Router {
    private $routes = [];
    private $namedRoutes = [];
    private $middlewareInstance;
    
    public function __construct(Middleware $middleware = null) {
        $this->middlewareInstance = $middleware ?? new Middleware();
        $this->registerDefaultMiddlewares();
    }
    
    private function registerDefaultMiddlewares() {
        // Registrar middlewares predefinidos
        $this->middlewareInstance->add('auth', Middleware::auth());
        $this->middlewareInstance->add('guest', Middleware::guest());
        $this->middlewareInstance->add('cors', Middleware::cors());
        $this->middlewareInstance->add('log', Middleware::log());
        $this->middlewareInstance->add('sanitize', Middleware::sanitize());
        
        // Registrar grupos de middlewares
        $this->middlewareInstance->group('web', ['log', 'sanitize']);
        $this->middlewareInstance->group('api', ['cors', 'throttle']);
    }
    
    public function add($method, $path, $handler, $name = null, $middleware = []) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'name' => $name,
            'middleware' => $middleware
        ];
        
        if ($name) {
            $this->namedRoutes[$name] = $path;
        }
    }
    
    public function addMiddleware($name, $middleware) {
        $this->middleware[$name] = $middleware;
    }
    
    public function addGlobalMiddleware($middleware) {
        $this->globalMiddleware[] = $middleware;
    }
    
    public function match($method = null, $path = null) {
        $method = $method ?? $_SERVER['REQUEST_METHOD'];
        $path = $path ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            
            $pattern = $this->buildPattern($route['path']);
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                return [
                    'handler' => $route['handler'],
                    'params' => $matches,
                    'middleware' => $route['middleware']
                ];
            }
        }
        
        return null;
    }
    
    public function runMiddleware($middleware, $params = []) {
        if (empty($middleware)) {
            return true;
        }

        // Procesar middlewares con parámetros
        $processedMiddleware = [];
        foreach ($middleware as $key => $mw) {
            if (is_array($mw)) {
                // Middleware con parámetros, ej: ['validate' => ['rules']]
                foreach ($mw as $mwName => $mwParams) {
                    $processedMiddleware[] = function($next) use ($mwName, $mwParams) {
                        return $this->executeMiddleware($mwName, $mwParams, $next);
                    };
                }
            } else {
                $processedMiddleware[] = $mw;
            }
        }

        try {
            $this->middlewareInstance->run($processedMiddleware, function() {
                return true;
            }, $params);
            return true;
        } catch (\Exception $e) {
            throw $e; // Re-lanzar la excepción en lugar de silenciarla
        }
    }

    private function executeMiddleware($name, $params, $next) {
        switch ($name) {
            case 'validate':
                return Middleware::validate($params)($next);
            default:
                throw new \InvalidArgumentException("Unknown parameterized middleware: {$name}");
        }
    }
    
    private function buildPattern($path) {
        return '#^' . preg_replace('/\{([^}]+)\}/', '([^/]+)', $path) . '$#';
    }
    
    /**
     * Get all registered routes
     * 
     * @return array
     */
    public function getRoutes() {
        return $this->routes;
    }
}