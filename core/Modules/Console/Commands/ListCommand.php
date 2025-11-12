<?php
namespace Nexus\Modules\Console\Commands;

use Nexus\Modules\Console\Command;

class ListCommand extends Command
{
    protected function configure()
    {
        $this->signature = 'list';
        $this->description = 'List all available commands';
    }

    public function handle()
    {
        $this->line('Nexus Framework ' . $this->getVersion());
        $this->line('');
        $this->line('Usage:');
        $this->line('  php nexus <command> [options] [arguments]');
        $this->line('');
        $this->line('Available commands:');

        // Obtener comandos del kernel
        $kernel = new \Nexus\Modules\Console\Kernel(null);
        $kernel->registerCustomCommands(); // Registrar comandos personalizados
        $commands = $kernel->getCommands();

        $commandList = [];
        foreach ($commands as $signature => $command) {
            $commandList[$signature] = $command->description;
        }

        $maxLength = max(array_map('strlen', array_keys($commandList)));

        foreach ($commandList as $signature => $description) {
            $padding = str_repeat(' ', $maxLength - strlen($signature) + 2);
            $this->line("  {$signature}{$padding}{$description}");
        }
    }

    protected function getVersion()
    {
        return 'v1.0.0';
    }
}