<?php
namespace Nexus\Modules\Console\Commands;

use Nexus\Modules\Console\Command;

class TestCommand extends Command
{
    protected function configure()
    {
        $this->signature = 'test';
        $this->description = 'Run the test suite';
    }

    public function handle()
    {
        $this->info('Running test suite...');
        $this->line('');

        $testFile = __DIR__ . '/../../../../tests/TestSuite.php';

        if (!file_exists($testFile)) {
            $this->error('Test suite not found at: ' . $testFile);
            return;
        }

        // Ejecutar las pruebas
        ob_start();
        require_once $testFile;
        $output = ob_get_clean();

        echo $output;

        // Verificar si las pruebas pasaron
        if (strpos($output, 'All tests passed!') !== false) {
            $this->info('✓ All tests passed!');
        } else {
            $this->error('✗ Some tests failed!');
        }
    }
}