<?php
namespace Nexus\Modules\Config;

use Exception;
// app/Core/Env.php
class Env {
    private static $loaded = false;
    private static $cache = [];
    
    public static function load($filePath = null) {
        if (self::$loaded) return;
        
        if ($filePath === null) {
            $filePath = __DIR__ . '/../../../.env';
        }
        
        if (!file_exists($filePath)) {
            throw new Exception(".env file not found: {$filePath}");
        }
        
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) continue;
            
            // Separar key y value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remover comillas
                $value = self::parseValue($value);
                
                // Guardar en cache y en $_ENV
                self::$cache[$key] = $value;
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
        
        self::$loaded = true;
    }
    
    private static function parseValue($value) {
        // Remover comillas simples y dobles
        if (preg_match('/^\"(.*)\"$/', $value, $matches) || 
            preg_match('/^\'(.*)\'$/', $value, $matches)) {
            return $matches[1];
        }
        
        // Convertir booleanos
        $lowerValue = strtolower($value);
        if ($lowerValue === 'true') return true;
        if ($lowerValue === 'false') return false;
        if ($lowerValue === 'null') return null;
        
        // Convertir números
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }
        
        return $value;
    }
    
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        // Buscar en cache primero
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        
        // Buscar en $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        // Buscar en $_SERVER
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        
        // Buscar en getenv() como fallback
        $value = getenv($key);
        if ($value !== false) {
            return self::parseValue($value);
        }
        
        return $default;
    }
    
    public static function all() {
        if (!self::$loaded) {
            self::load();
        }
        return self::$cache;
    }
}