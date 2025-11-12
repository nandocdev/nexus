<?php
namespace Nexus\Modules\Config;

use Exception;
// app/Core/EnvValidator.php
class EnvValidator {
    public static function validateRequired($keys) {
        $missing = [];
        
        foreach ($keys as $key) {
            if (Env::get($key) === null) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception(
                "Missing required environment variables: " . implode(', ', $missing)
            );
        }
    }
    
    public static function validateTypes($rules) {
        foreach ($rules as $key => $type) {
            $value = Env::get($key);
            
            if ($value === null) continue;
            
            switch ($type) {
                case 'string':
                    if (!is_string($value)) {
                        throw new Exception("Env variable {$key} must be a string");
                    }
                    break;
                    
                case 'int':
                case 'integer':
                    if (!is_numeric($value)) {
                        throw new Exception("Env variable {$key} must be an integer");
                    }
                    break;
                    
                case 'bool':
                case 'boolean':
                    if (!is_bool($value) && !in_array(strtolower($value), ['true', 'false', '1', '0'])) {
                        throw new Exception("Env variable {$key} must be a boolean");
                    }
                    break;
                    
                case 'array':
                    if (!is_array($value) && !self::isJsonArray($value)) {
                        throw new Exception("Env variable {$key} must be an array or JSON string");
                    }
                    break;
            }
        }
    }
    
    private static function isJsonArray($value) {
        if (!is_string($value)) return false;
        
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE && is_array($decoded);
    }
}