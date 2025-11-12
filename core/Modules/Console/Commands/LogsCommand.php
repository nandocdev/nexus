<?php
namespace Nexus\Modules\Console\Commands;

use Nexus\Modules\Console\Command;
use Nexus\Modules\Logging\Logger;

class LogsCommand extends Command
{
    protected function configure()
    {
        $this->signature = 'logs';
        $this->description = 'Display application logs';
    }

    public function handle()
    {
        $limit = $this->option('limit') ?: 50;
        $level = $this->option('level');

        $logs = Logger::getRecentLogs($limit);

        if (empty($logs)) {
            $this->info('No logs found.');
            return;
        }

        $this->line('Recent application logs:');
        $this->line('');

        foreach ($logs as $log) {
            // Filter by level if specified
            if ($level && !preg_match("/\[$level\]/", $log)) {
                continue;
            }

            // Color code based on log level
            if (preg_match('/\[ERROR\]/', $log)) {
                $this->error($log);
            } elseif (preg_match('/\[WARNING\]/', $log)) {
                $this->warning($log);
            } elseif (preg_match('/\[INFO\]/', $log)) {
                $this->info($log);
            } elseif (preg_match('/\[DEBUG\]/', $log)) {
                $this->line("\033[36m{$log}\033[0m"); // Cyan for debug
            } else {
                $this->line($log);
            }
        }
    }

    protected function configureOptions()
    {
        $this->addOption('limit', 'l', 'Maximum number of logs to display', '50');
        $this->addOption('level', null, 'Filter logs by level (INFO, WARNING, ERROR, DEBUG)', null);
    }
}