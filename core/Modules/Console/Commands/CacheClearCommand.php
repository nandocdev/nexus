<?php
namespace Nexus\Modules\Console\Commands;

use Nexus\Modules\Console\Command;

class CacheClearCommand extends Command
{
    protected function configure()
    {
        $this->signature = 'cache:clear';
        $this->description = 'Clear application cache';
    }

    public function handle()
    {
        $this->info('Clearing application cache...');

        $cacheDir = __DIR__ . '/../../../../storage/cache';

        if (!is_dir($cacheDir)) {
            $this->warning('Cache directory does not exist.');
            return;
        }

        $files = glob($cacheDir . '/*');
        $count = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }

        $this->info("Cache cleared successfully. {$count} files removed.");
    }
}