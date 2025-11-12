<?php
namespace Nexus\Modules\Console\Commands;

use Nexus\Modules\Console\Command;

class ConfigCacheCommand extends Command
{
    protected function configure()
    {
        $this->signature = 'config:cache';
        $this->description = 'Cache configuration files for faster loading';
    }

    public function handle()
    {
        $this->info('Caching configuration files...');

        $configDir = __DIR__ . '/../../../../app/Config';
        $cacheFile = __DIR__ . '/../../../../storage/cache/config.php';

        if (!is_dir($configDir)) {
            $this->error('Config directory not found.');
            return;
        }

        // Crear directorio de cache si no existe
        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $config = [];

        // Cargar todos los archivos de configuración
        $files = glob($configDir . '/*.php');
        foreach ($files as $file) {
            $key = basename($file, '.php');
            $config[$key] = require $file;
        }

        // Cachear la configuración
        $content = "<?php\nreturn " . var_export($config, true) . ";\n";

        if (file_put_contents($cacheFile, $content)) {
            $this->info('Configuration cached successfully.');
        } else {
            $this->error('Failed to cache configuration.');
        }
    }
}