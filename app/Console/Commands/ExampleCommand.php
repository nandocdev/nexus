<?php
namespace App\Console\Commands;

use Nexus\Modules\Console\Command;

class ExampleCommand extends Command
{
    protected function configure()
    {
        $this->signature = 'example';
        $this->description = 'An example custom command';
    }

    public function handle()
    {
        $this->info('Hello from a custom command!');

        $name = $this->ask('What is your name?', 'World');
        $this->line("Hello, {$name}!");

        if ($this->confirm('Do you want to see a warning?', false)) {
            $this->warning('This is a warning message!');
        }

        $this->info('Command completed successfully!');
    }
}