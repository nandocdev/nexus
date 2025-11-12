<?php
namespace Nexus\Modules\Console\Commands;

use Nexus\Modules\Console\Command;

class MigrateStatusCommand extends Command
{
    protected function configure()
    {
        $this->signature = 'migrate:status';
        $this->description = 'Show migration status';
    }

    public function handle()
    {
        $this->info('Migration Status');
        $this->line('================');

        $this->warning('Migration system not yet implemented.');
        $this->line('This command will show the status of all migrations.');
    }
}