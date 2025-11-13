<?php
namespace Nexus\Modules\Config;

// app/Core/Config.php (versión simplificada)
class Config {
    private static $config = [];

    public static function load($file) {
        $path = __DIR__ . "/../../../app/Config/{$file}.php";
        if (file_exists($path)) {
            self::$config[$file] = require $path;
        }
    }

    public static function get($key, $default = null) {
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