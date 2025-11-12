<?php
namespace Nexus\Modules\Console\Commands;

use Nexus\Modules\Console\Command;

class MakeMigrationCommand extends Command
{
    protected function configure()
    {
        $this->signature = 'make:migration';
        $this->description = 'Create a new migration file';
    }

    public function handle()
    {
        $name = $this->argument(0);

        if (!$name) {
            $this->error('Migration name is required.');
            $this->line('Usage: php nexus make:migration <migration_name>');
            return;
        }

        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_{$name}.php";
        $filePath = __DIR__ . "/../../../../app/Migrations/{$fileName}";

        // Crear el directorio si no existe
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Template de migración
        $template = $this->getMigrationTemplate($name);

        // Crear el archivo
        if (file_put_contents($filePath, $template)) {
            $this->info("Migration created successfully: {$filePath}");
        } else {
            $this->error("Failed to create migration file.");
        }
    }

    protected function getMigrationTemplate($name)
    {
        $className = $this->toCamelCase($name) . 'Migration';

        return "<?php
namespace App\Migrations;

use Nexus\Modules\Database\Migration;

class {$className} extends Migration
{
    public function up()
    {
        // Define the migration logic here
        // Example:
        // \$this->createTable('table_name', [
        //     'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        //     'name' => 'VARCHAR(255) NOT NULL',
        //     'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
        // ]);
    }

    public function down()
    {
        // Define the rollback logic here
        // Example:
        // \$this->dropTable('table_name');
    }
}
";
    }

    protected function toCamelCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
}