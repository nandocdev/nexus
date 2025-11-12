<?php
namespace Nexus\Modules\Logging;
// app/Core/Logger.php
class Logger {
    private static $logFile = __DIR__ . '/../../../storage/logs/app.log';
    
    public static function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    public static function info($message) {
        self::log($message, 'INFO');
    }
    
    public static function warning($message) {
        self::log($message, 'WARNING');
    }
    
    public static function error($message) {
        self::log($message, 'ERROR');
    }
}