<?php
namespace Nexus\Modules\Logging;
// app/Core/Logger.php
class Logger {
    private static $logFile = __DIR__ . '/../../../storage/logs/app.log';
    private static $maxFileSize = 10485760; // 10MB

    public static function log($message, $level = 'INFO', array $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $contextString = empty($context) ? '' : ' ' . json_encode($context);
        $logMessage = "[$timestamp] [$level] $message{$contextString}" . PHP_EOL;

        self::rotateLogFileIfNeeded();
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    public static function info($message, array $context = []) {
        self::log($message, 'INFO', $context);
    }

    public static function warning($message, array $context = []) {
        self::log($message, 'WARNING', $context);
    }

    public static function error($message, array $context = []) {
        self::log($message, 'ERROR', $context);
    }

    public static function debug($message, array $context = []) {
        self::log($message, 'DEBUG', $context);
    }

    public static function critical($message, array $context = []) {
        self::log($message, 'CRITICAL', $context);
    }

    /**
     * Rotate log file if it exceeds maximum size.
     *
     * @return void
     */
    private static function rotateLogFileIfNeeded() {
        if (!file_exists(self::$logFile)) {
            return;
        }

        if (filesize(self::$logFile) > self::$maxFileSize) {
            $backupFile = self::$logFile . '.' . date('Y-m-d-H-i-s') . '.bak';
            rename(self::$logFile, $backupFile);

            // Keep only last 5 backup files
            self::cleanupOldBackups();
        }
    }

    /**
     * Clean up old backup files, keeping only the most recent ones.
     *
     * @return void
     */
    private static function cleanupOldBackups() {
        $logDir = dirname(self::$logFile);
        $backupFiles = glob($logDir . '/app.log.*.bak');

        if (count($backupFiles) > 5) {
            // Sort by modification time, newest first
            usort($backupFiles, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            // Remove older files
            $filesToDelete = array_slice($backupFiles, 5);
            foreach ($filesToDelete as $file) {
                unlink($file);
            }
        }
    }

    /**
     * Get all log entries for a specific date.
     *
     * @param  string  $date
     * @return array
     */
    public static function getLogsByDate($date) {
        if (!file_exists(self::$logFile)) {
            return [];
        }

        $logs = [];
        $lines = file(self::$logFile);

        foreach ($lines as $line) {
            if (strpos($line, "[$date") === 0) {
                $logs[] = trim($line);
            }
        }

        return $logs;
    }

    /**
     * Get recent log entries.
     *
     * @param  int  $limit
     * @return array
     */
    public static function getRecentLogs($limit = 100) {
        if (!file_exists(self::$logFile)) {
            return [];
        }

        $lines = file(self::$logFile);
        $logs = array_slice($lines, -$limit);

        return array_map('trim', $logs);
    }
}