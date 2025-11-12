<?php
namespace Nexus\Modules\Config;

// app/Core/Config.php (versión mejorada)
class Config {
    private static $config = [];
    private static $envLoaded = false;
    
    public static function init() {
        if (!self::$envLoaded) {
            // Cargar variables de entorno
            Env::load();
            self::$envLoaded = true;
            
            // Validar variables requeridas
            self::validateEnvironment();
        }
    }
    
    public static function load($file) {
        self::init();
        
        $path = __DIR__ . "/../Config/{$file}.php";
        if (file_exists($path)) {
            $config = require $path;
            
            // Reemplazar placeholders con variables de entorno
            $config = self::replaceEnvPlaceholders($config);
            
            self::$config = array_merge(self::$config, $config);
        }
    }
    
    private static function replaceEnvPlaceholders($config) {
        array_walk_recursive($config, function (&$value) {
            if (is_string($value) && preg_match('/\$\{([^}]+)\}/', $value, $matches)) {
                $envValue = Env::get($matches[1]);
                $value = str_replace($matches[0], $envValue, $value);
            }
        });
        
        return $config;
    }
    
    private static function validateEnvironment() {
        $required = ['DB_HOST', 'DB_NAME', 'DB_USER'];
        EnvValidator::validateRequired($required);
        
        $rules = [
            'DB_PORT' => 'int',
            'DB_HOST' => 'string',
            'APP_DEBUG' => 'bool'
        ];
        EnvValidator::validateTypes($rules);
    }
    
    public static function get($key, $default = null) {
        // Primero buscar en variables de entorno (prefijo APP_)
        $envKey = 'APP_' . strtoupper(str_replace('.', '_', $key));
        $envValue = Env::get($envKey);
        
        if ($envValue !== null) {
            return $envValue;
        }
        
        // Luego buscar en configuración normal
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}