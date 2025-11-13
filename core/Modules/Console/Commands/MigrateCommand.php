<?php
namespace Nexus\Modules\Console\Commands;

use Nexus\Modules\Console\Command;
use Nexus\Modules\Database\Migrator;

class MigrateCommand extends Command {
    protected function configure() {
        $this->signature = 'migrate {--rollback : Rollback the last batch of migrations} {--status : Show migration status}';
        $this->description = 'Run database migrations';
    }

    public function handle() {
        $migrator = new Migrator();

        if ($this->option('status')) {
            return $this->showStatus($migrator);
        }

        if ($this->option('rollback')) {
            return $this->rollbackMigrations($migrator);
        }

        return $this->runMigrations($migrator);
    }

    protected function runMigrations(Migrator $migrator) {
        $this->info('Running database migrations...');

        $result = $migrator->run();

        if ($result['status'] === 'success') {
            if ($result['count'] === 0) {
                $this->info($result['message']);
            } else {
                $this->success($result['message']);
            }
            return 0;
        } else {
            $this->error($result['message']);
            foreach ($result['errors'] as $error) {
                $this->error("  - {$error}");
            }
            return 1;
        }
    }

    protected function rollbackMigrations(Migrator $migrator) {
        $this->info('Rolling back last batch of migrations...');

        $result = $migrator->rollback();

        if ($result['status'] === 'success') {
            if ($result['count'] === 0) {
                $this->info($result['message']);
            } else {
                $this->success($result['message']);
            }
            return 0;
        } else {
            $this->error($result['message']);
            foreach ($result['errors'] as $error) {
                $this->error("  - {$error}");
            }
            return 1;
        }
    }

    protected function showStatus(Migrator $migrator) {
        $this->info('Migration Status:');
        $this->line('');

        $status = $migrator->status();

        if (empty($status)) {
            $this->warning('No migration files found.');
            return 0;
        }

        $executedCount = 0;
        $pendingCount = 0;

        foreach ($status as $migration) {
            $statusIcon = $migration['status'] === 'executed' ? '✅' : '⏳';
            $statusText = $migration['status'] === 'executed' ? '<fg=green>Executed</>' : '<fg=yellow>Pending</>';

            $this->line("{$statusIcon} {$migration['migration']} - {$statusText}");

            if ($migration['status'] === 'executed') {
                $executedCount++;
            } else {
                $pendingCount++;
            }
        }

        $this->line('');
        $this->info("Total: {$executedCount} executed, {$pendingCount} pending");

        return 0;
    }
}