<?php
namespace Nexus\Modules\Console;

use Nexus\Modules\Console\Commands\Command;
class Kernel
{
    protected $commands = [];
    protected $container;

    public function __construct($container = null)
    {
        $this->container = $container;
        $this->registerBuiltInCommands();
        // No registrar comandos personalizados aquí, se hace en handle()
    }

    protected function registerCommands()
    {
        // Registrar comandos built-in del sistema
        $this->registerBuiltInCommands();

        // Registrar comandos personalizados del desarrollador
        $this->registerCustomCommands();
    }

    protected function registerBuiltInCommands()
    {
        // Comandos del sistema
        $this->commands['list'] = new \Nexus\Modules\Console\Commands\ListCommand();
        $this->commands['migrate'] = new \Nexus\Modules\Console\Commands\MigrateCommand();
        $this->commands['migrate:status'] = new \Nexus\Modules\Console\Commands\MigrateStatusCommand();
        $this->commands['make:model'] = new \Nexus\Modules\Console\Commands\MakeModelCommand();
        $this->commands['make:controller'] = new \Nexus\Modules\Console\Commands\MakeControllerCommand();
        $this->commands['make:migration'] = new \Nexus\Modules\Console\Commands\MakeMigrationCommand();
        $this->commands['db:seed'] = new \Nexus\Modules\Console\Commands\DbSeedCommand();
        $this->commands['cache:clear'] = new \Nexus\Modules\Console\Commands\CacheClearCommand();
        $this->commands['config:cache'] = new \Nexus\Modules\Console\Commands\ConfigCacheCommand();
        $this->commands['route:list'] = new \Nexus\Modules\Console\Commands\RouteListCommand();
        $this->commands['test'] = new \Nexus\Modules\Console\Commands\TestCommand();
    }

    public function registerCustomCommands()
    {
        // Buscar comandos personalizados en app/Console/Commands/
        $commandsPath = __DIR__ . '/../../../app/Console/Commands';

        if (!is_dir($commandsPath)) {
            return;
        }

        $files = glob($commandsPath . '/*.php');

        foreach ($files as $file) {
            $className = basename($file, '.php');
            $fullClassName = "App\\Console\\Commands\\{$className}";

            if (class_exists($fullClassName)) {
                $command = new $fullClassName();
                if ($command instanceof \Nexus\Modules\Console\Command) {
                    $this->commands[$command->signature] = $command;
                }
            }
        }
    }

    public function handle($argv)
    {
        // Los comandos personalizados ya se registraron en el script nexus

        $commandName = $argv[1] ?? 'list';
        $args = array_slice($argv, 2);

        if (!isset($this->commands[$commandName])) {
            $this->error("Command '{$commandName}' not found.");
            $this->line("");
            $this->line("Run 'php nexus list' to see available commands.");
            exit(1);
        }

        $command = $this->commands[$commandName];

        try {
            // Parse arguments and options
            $this->parseArguments($command, $args);

            // Execute the command
            $command->handle();
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            exit(1);
        }
    }

    protected function parseArguments($command, array $args)
    {
        $parsedArgs = [];
        $parsedOptions = [];

        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                // Es una opción
                $option = substr($arg, 2);
                if (strpos($option, '=') !== false) {
                    list($key, $value) = explode('=', $option, 2);
                    $parsedOptions[$key] = $value;
                } else {
                    $parsedOptions[$option] = true;
                }
            } else {
                // Es un argumento
                $parsedArgs[] = $arg;
            }
        }

        // Asignar argumentos y opciones al comando
        $command->arguments = $parsedArgs;
        $command->options = $parsedOptions;
    }

    public function getCommands()
    {
        return $this->commands;
    }

    protected function error($message)
    {
        echo "\033[31m" . $message . "\033[0m" . PHP_EOL;
    }

    protected function line($message)
    {
        echo $message . PHP_EOL;
    }
}