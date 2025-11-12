<?php
namespace Nexus\Modules\Console\Commands;

use Nexus\Modules\Console\Command;

class MakeModelCommand extends Command
{
    protected function configure()
    {
        $this->signature = 'make:model';
        $this->description = 'Create a new model class';
    }

    public function handle()
    {
        $name = $this->argument(0);

        if (!$name) {
            $this->error('Model name is required.');
            $this->line('Usage: php nexus make:model <ModelName>');
            return;
        }

        $modelName = ucfirst($name);
        $filePath = __DIR__ . "/../../../../app/Models/{$modelName}.php";

        if (file_exists($filePath)) {
            $this->error("Model {$modelName} already exists!");
            return;
        }

        // Crear el directorio si no existe
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Template del modelo
        $template = $this->getModelTemplate($modelName);

        // Crear el archivo
        if (file_put_contents($filePath, $template)) {
            $this->info("Model created successfully: {$filePath}");
        } else {
            $this->error("Failed to create model file.");
        }
    }

    protected function getModelTemplate($modelName)
    {
        return "<?php
namespace App\Models;

use Nexus\Modules\Database\Model;

class {$modelName} extends Model
{
    protected \$table = '" . strtolower($modelName) . "s' . \";
    protected \$fillable = [];
    protected \$hidden = [];
    protected \$casts = [];

    // Define relationships here
    // public function relationshipName()
    // {
    //     return \$this->belongsTo(RelationshipModel::class);
    // }
}
";
    }
}