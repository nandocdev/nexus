<?php
namespace Nexus\Modules\Console\Commands;

use Nexus\Modules\Console\Command;

class MakeControllerCommand extends Command
{
    protected function configure()
    {
        $this->signature = 'make:controller';
        $this->description = 'Create a new controller class';
    }

    public function handle()
    {
        $name = $this->argument(0);

        if (!$name) {
            $this->error('Controller name is required.');
            $this->line('Usage: php nexus make:controller <ControllerName>');
            return;
        }

        $controllerName = ucfirst($name) . 'Controller';
        $filePath = __DIR__ . "/../../../../app/Controllers/{$controllerName}.php";

        if (file_exists($filePath)) {
            $this->error("Controller {$controllerName} already exists!");
            return;
        }

        // Crear el directorio si no existe
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Template del controlador
        $template = $this->getControllerTemplate($controllerName, $name);

        // Crear el archivo
        if (file_put_contents($filePath, $template)) {
            $this->info("Controller created successfully: {$filePath}");
        } else {
            $this->error("Failed to create controller file.");
        }
    }

    protected function getControllerTemplate($controllerName, $baseName)
    {
        $modelName = ucfirst($baseName);

        return "<?php
namespace App\Controllers;

use Nexus\Modules\Http\Controller;
use App\Models\\{$modelName};

class {$controllerName} extends Controller
{
    public function index()
    {
        \$items = {$modelName}::all();
        \$this->view('" . strtolower($baseName) . "s/index', [
            'items' => \$items,
            'layout' => 'layouts/app'
        ]);
    }

    public function create()
    {
        \$this->view('" . strtolower($baseName) . "s/create', [
            'layout' => 'layouts/app'
        ]);
    }

    public function store()
    {
        // Validar y crear nuevo {$baseName}
        // \$data = \$_POST;
        // \$item = {$modelName}::create(\$data);

        \$this->redirect('/" . strtolower($baseName) . "s');
    }

    public function show(\$id)
    {
        \$item = {$modelName}::find(\$id);

        if (!\$item) {
            \$this->view('errors/404', [
                'message' => '{$modelName} not found',
                'layout' => 'layouts/app'
            ]);
            return;
        }

        \$this->view('" . strtolower($baseName) . "s/show', [
            'item' => \$item,
            'layout' => 'layouts/app'
        ]);
    }

    public function edit(\$id)
    {
        \$item = {$modelName}::find(\$id);

        if (!\$item) {
            \$this->view('errors/404', [
                'message' => '{$modelName} not found',
                'layout' => 'layouts/app'
            ]);
            return;
        }

        \$this->view('" . strtolower($baseName) . "s/edit', [
            'item' => \$item,
            'layout' => 'layouts/app'
        ]);
    }

    public function update(\$id)
    {
        \$item = {$modelName}::find(\$id);

        if (!\$item) {
            \$this->view('errors/404', [
                'message' => '{$modelName} not found',
                'layout' => 'layouts/app'
            ]);
            return;
        }

        // Validar y actualizar {$baseName}
        // \$data = \$_POST;
        // \$item->update(\$data);

        \$this->redirect('/" . strtolower($baseName) . "s');
    }

    public function delete(\$id)
    {
        \$item = {$modelName}::find(\$id);

        if (!\$item) {
            \$this->view('errors/404', [
                'message' => '{$modelName} not found',
                'layout' => 'layouts/app'
            ]);
            return;
        }

        // \$item->delete();
        \$this->redirect('/" . strtolower($baseName) . "s');
    }
}
";
    }
}