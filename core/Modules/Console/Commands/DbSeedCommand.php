<?php
namespace Nexus\Modules\Console\Commands;

use Nexus\Modules\Console\Command;

class DbSeedCommand extends Command
{
    protected function configure()
    {
        $this->signature = 'db:seed';
        $this->description = 'Run database seeders';
    }

    public function handle()
    {
        $this->info('Running database seeders...');

        // Aquí iría la lógica real de seeders
        // Por ahora, solo mostramos un mensaje

        $this->warning('Seeder system not yet implemented.');
        $this->line('This command will run all database seeders.');
        $this->line('');
        $this->line('To implement:');
        $this->line('1. Create Seeder class in core/Modules/Database/');
        $this->line('2. Scan app/Seeders/ directory');
        $this->line('3. Execute seeders in order');
    }
}