<?php
namespace Nexus\Modules\Http;

use Closure;

class Middleware {
    private array $middlewares = [];
    private array $groups = [];

    /**
     * Registrar un middleware nombrado
     *
     * @param string $name
     * @param callable $middleware
     */
    public function add(string $name, callable $middleware): void {
        $this->middlewares[$name] = $middleware;
    }

    /**
     * Registrar un grupo de middlewares
     *
     * @param string $name
     * @param array $middlewares
     */
    public function group(string $name, array $middlewares): void {
        $this->groups[$name] = $middlewares;
    }

    /**
     * Ejecutar una lista de middlewares
     *
     * @param array $middlewareNames
     * @param callable $destination
     * @param array $params
     * @return mixed
     */
    public function run(array $middlewareNames, callable $destination, array $params = []) {
        $pipeline = $this->buildPipeline($middlewareNames, $destination);

        return $pipeline(...$params);
    }

    /**
     * Construir el pipeline de middlewares
     *
     * @param array $middlewareNames
     * @param callable $destination
     * @return Closure
     */
    private function buildPipeline(array $middlewareNames, callable $destination): Closure {
        $middlewares = $this->resolveMiddlewares($middlewareNames);

        // Crear el pipeline en orden inverso
        $pipeline = $destination;

        foreach (array_reverse($middlewares) as $middleware) {
            $pipeline = function (...$params) use ($middleware, $pipeline) {
                return $middleware($pipeline, ...$params);
            };
        }

        return $pipeline;
    }

    /**
     * Resolver los nombres de middlewares a callables
     *
     * @param array $middlewareNames
     * @return array
     */
    private function resolveMiddlewares(array $middlewareNames): array {
        $resolved = [];

        foreach ($middlewareNames as $name) {
            if (isset($this->groups[$name])) {
                // Es un grupo, resolver recursivamente
                $resolved = array_merge($resolved, $this->resolveMiddlewares($this->groups[$name]));
            } elseif (isset($this->middlewares[$name])) {
                // Es un middleware registrado
                $resolved[] = $this->middlewares[$name];
            } elseif (is_callable($name)) {
                // Es un callable directo
                $resolved[] = $name;
            } else {
                throw new \InvalidArgumentException("Middleware '{$name}' not found");
            }
        }

        return $resolved;
    }

    /**
     * Middlewares predefinidos comunes
     */

    /**
     * Middleware de autenticación
     */
    public static function auth(): callable {
        return function (callable $next, ...$params) {
            $auth = \Nexus\Modules\Auth\Auth::class;

            if (!$auth::check()) {
                if (self::isAjaxRequest()) {
                    http_response_code(401);
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Unauthorized']);
                    exit;
                } else {
                    header('Location: /login');
                    exit;
                }
            }

            return $next(...$params);
        };
    }

    /**
     * Middleware de invitado (solo para usuarios no autenticados)
     */
    public static function guest(): callable {
        return function (callable $next, ...$params) {
            $auth = \Nexus\Modules\Auth\Auth::class;

            if ($auth::check()) {
                header('Location: /');
                exit;
            }

            return $next(...$params);
        };
    }

    /**
     * Middleware CORS
     */
    public static function cors(array $options = []): callable {
        $defaults = [
            'allowed_origins' => ['*'],
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
            'max_age' => 86400,
        ];

        $config = array_merge($defaults, $options);

        return function (callable $next, ...$params) use ($config) {
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

            // Manejar preflight requests
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                if (in_array('*', $config['allowed_origins']) || in_array($origin, $config['allowed_origins'])) {
                    header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
                    header('Access-Control-Allow-Methods: ' . implode(', ', $config['allowed_methods']));
                    header('Access-Control-Allow-Headers: ' . implode(', ', $config['allowed_headers']));
                    header('Access-Control-Max-Age: ' . $config['max_age']);
                    exit;
                }
            }

            // Agregar headers CORS a la respuesta
            if (in_array('*', $config['allowed_origins']) || in_array($origin, $config['allowed_origins'])) {
                header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
                header('Access-Control-Allow-Credentials: true');
            }

            return $next(...$params);
        };
    }

    /**
     * Middleware de logging
     */
    public static function log(string $level = 'info'): callable {
        return function (callable $next, ...$params) {
            try {
                $logger = new \Nexus\Modules\Logging\Logger();

                // Log de entrada
                $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
                $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
                $logger::info("Request: {$method} {$uri}");

                $start = microtime(true);
                $result = $next(...$params);
                $duration = microtime(true) - $start;

                // Log de salida
                $logger::info("Response completed in " . round($duration * 1000, 2) . "ms");

                return $result;
            } catch (\Exception $e) {
                // Log del error pero continuar
                error_log("Logging middleware error: " . $e->getMessage());
                // Continuar sin logging
                return $next(...$params);
            }
        };
    }

    /**
     * Middleware de throttling/rate limiting
     */
    public static function throttle(int $maxRequests = 60, int $decayMinutes = 1): callable {
        return function (callable $next, ...$params) use ($maxRequests, $decayMinutes) {
            $key = self::getThrottleKey();
            $now = time();

            // Implementación simple de rate limiting (en producción usar Redis/memcached)
            $requests = $_SESSION['throttle'][$key]['requests'] ?? 0;
            $resetTime = $_SESSION['throttle'][$key]['reset'] ?? $now + ($decayMinutes * 60);

            if ($now > $resetTime) {
                $requests = 0;
                $resetTime = $now + ($decayMinutes * 60);
            }

            if ($requests >= $maxRequests) {
                http_response_code(429);
                header('Content-Type: application/json');
                header('Retry-After: ' . ($resetTime - $now));
                echo json_encode([
                    'error' => 'Too Many Requests',
                    'retry_after' => $resetTime - $now
                ]);
                exit;
            }

            $_SESSION['throttle'][$key] = [
                'requests' => $requests + 1,
                'reset' => $resetTime
            ];

            return $next(...$params);
        };
    }

    /**
     * Middleware de validación de entrada
     */
    public static function validate(array $rules): callable {
        return function (callable $next, ...$params) use ($rules) {
            $validator = new \Nexus\Modules\Validation\Validator($_POST, $rules);

            if (!$validator->validate()) {
                if (self::isAjaxRequest()) {
                    http_response_code(422);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'error' => 'Validation failed',
                        'errors' => $validator->errors()
                    ]);
                    exit;
                } else {
                    // Para requests normales, redirigir con errores
                    $_SESSION['validation_errors'] = $validator->errors();
                    $_SESSION['old_input'] = $_POST;
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit;
                }
            }

            return $next(...$params);
        };
    }

    /**
     * Middleware de sanitización de entrada
     */
    public static function sanitize(): callable {
        return function (callable $next, ...$params) {
            $_GET = self::sanitizeArray($_GET);
            $_POST = self::sanitizeArray($_POST);
            $_REQUEST = self::sanitizeArray($_REQUEST);

            return $next(...$params);
        };
    }

    /**
     * Utilidades auxiliares
     */

    private static function isAjaxRequest(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private static function getThrottleKey(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        return md5($ip . $uri);
    }

    private static function sanitizeArray(array $data): array {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                $data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        return $data;
    }

    /**
     * Obtener instancia del middleware (para uso con contenedor DI)
     */
    public static function getInstance(): self {
        return new self();
    }
}