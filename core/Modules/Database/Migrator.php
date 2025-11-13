<?php
namespace Nexus\Modules\Database;

use Exception;
use PDO;

class Migrator {
    protected $db;
    protected $migrationPath;
    protected $migrationTable = 'migrations';

    public function __construct($migrationPath = null) {
        $this->db = Database::getInstance();
        $this->migrationPath = $migrationPath ?: __DIR__ . '/../../../app/Migrations';
        $this->ensureMigrationTableExists();
    }

    /**
     * Ensure the migrations table exists
     */
    protected function ensureMigrationTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $this->db->query($sql);
    }

    /**
     * Get all migration files
     */
    protected function getMigrationFiles() {
        $files = [];
        if (is_dir($this->migrationPath)) {
            $iterator = new \DirectoryIterator($this->migrationPath);
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $file->getFilename();
                }
            }
        }
        sort($files);
        return $files;
    }

    /**
     * Get executed migrations
     */
    protected function getExecutedMigrations() {
        $stmt = $this->db->query("SELECT migration FROM {$this->migrationTable} ORDER BY id");
        $executed = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $executed[] = $row['migration'];
        }
        return $executed;
    }

    /**
     * Get pending migrations
     */
    protected function getPendingMigrations() {
        $allFiles = $this->getMigrationFiles();
        $executed = $this->getExecutedMigrations();

        return array_filter($allFiles, function ($file) use ($executed) {
            return !in_array($file, $executed);
        });
    }

    /**
     * Run pending migrations
     */
    public function run() {
        $pending = $this->getPendingMigrations();

        if (empty($pending)) {
            return ['status' => 'success', 'message' => 'No pending migrations.', 'count' => 0];
        }

        $batch = $this->getNextBatchNumber();
        $executed = 0;
        $errors = [];

        foreach ($pending as $migrationFile) {
            try {
                $this->runMigration($migrationFile, $batch);
                $executed++;
            } catch (Exception $e) {
                $errors[] = "Migration {$migrationFile} failed: " . $e->getMessage();
                break; // Stop on first error
            }
        }

        if (!empty($errors)) {
            return [
                'status' => 'error',
                'message' => 'Migration failed.',
                'errors' => $errors,
                'executed' => $executed
            ];
        }

        return [
            'status' => 'success',
            'message' => "Successfully executed {$executed} migrations.",
            'count' => $executed
        ];
    }

    /**
     * Run a single migration
     */
    protected function runMigration($migrationFile, $batch) {
        $className = $this->getMigrationClassName($migrationFile);
        $filePath = $this->migrationPath . '/' . $migrationFile;

        if (!file_exists($filePath)) {
            throw new Exception("Migration file not found: {$migrationFile}");
        }

        require_once $filePath;

        if (!class_exists($className)) {
            throw new Exception("Migration class not found: {$className}");
        }

        $migration = new $className();

        if (!$migration instanceof Migration) {
            throw new Exception("Migration class must extend Migration base class");
        }

        // Run the migration
        $migration->up();

        // Record the migration
        $this->recordMigration($migrationFile, $batch);
    }

    /**
     * Record a migration as executed
     */
    protected function recordMigration($migrationFile, $batch) {
        $sql = "INSERT INTO {$this->migrationTable} (migration, batch) VALUES (?, ?)";
        $this->db->query($sql, [$migrationFile, $batch]);
    }

    /**
     * Rollback last batch of migrations
     */
    public function rollback($steps = 1) {
        $lastBatch = $this->getLastBatchNumber();

        if (!$lastBatch) {
            return ['status' => 'success', 'message' => 'No migrations to rollback.', 'count' => 0];
        }

        $migrations = $this->getMigrationsByBatch($lastBatch);
        $rolledBack = 0;
        $errors = [];

        foreach (array_reverse($migrations) as $migration) {
            try {
                $this->rollbackMigration($migration['migration']);
                $this->removeMigrationRecord($migration['migration']);
                $rolledBack++;
            } catch (Exception $e) {
                $errors[] = "Rollback of {$migration['migration']} failed: " . $e->getMessage();
                break;
            }
        }

        if (!empty($errors)) {
            return [
                'status' => 'error',
                'message' => 'Rollback failed.',
                'errors' => $errors,
                'rolled_back' => $rolledBack
            ];
        }

        return [
            'status' => 'success',
            'message' => "Successfully rolled back {$rolledBack} migrations.",
            'count' => $rolledBack
        ];
    }

    /**
     * Rollback a single migration
     */
    protected function rollbackMigration($migrationFile) {
        $className = $this->getMigrationClassName($migrationFile);
        $filePath = $this->migrationPath . '/' . $migrationFile;

        if (!file_exists($filePath)) {
            throw new Exception("Migration file not found: {$migrationFile}");
        }

        require_once $filePath;

        if (!class_exists($className)) {
            throw new Exception("Migration class not found: {$className}");
        }

        $migration = new $className();

        if (!$migration instanceof Migration) {
            throw new Exception("Migration class must extend Migration base class");
        }

        $migration->down();
    }

    /**
     * Remove migration record
     */
    protected function removeMigrationRecord($migrationFile) {
        $sql = "DELETE FROM {$this->migrationTable} WHERE migration = ?";
        $this->db->query($sql, [$migrationFile]);
    }

    /**
     * Get next batch number
     */
    protected function getNextBatchNumber() {
        $stmt = $this->db->query("SELECT MAX(batch) as max_batch FROM {$this->migrationTable}");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['max_batch'] ?? 0) + 1;
    }

    /**
     * Get last batch number
     */
    protected function getLastBatchNumber() {
        $stmt = $this->db->query("SELECT MAX(batch) as max_batch FROM {$this->migrationTable}");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['max_batch'] ?? null;
    }

    /**
     * Get migrations by batch
     */
    protected function getMigrationsByBatch($batch) {
        $stmt = $this->db->query("SELECT * FROM {$this->migrationTable} WHERE batch = ? ORDER BY id", [$batch]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get migration class name from file
     */
    protected function getMigrationClassName($migrationFile) {
        // Remove .php extension and convert to class name
        $className = str_replace('.php', '', $migrationFile);

        // Handle timestamped migrations
        if (preg_match('/^\d{4}_\d{2}_\d{2}_\d{6}_(.+)$/', $className, $matches)) {
            $className = $matches[1];
        }

        // Convert snake_case to PascalCase
        $className = str_replace('_', ' ', $className);
        $className = ucwords($className);
        $className = str_replace(' ', '', $className);

        return 'App\\Migrations\\' . $className . 'Migration';
    }

    /**
     * Get migration status
     */
    public function status() {
        $allFiles = $this->getMigrationFiles();
        $executed = $this->getExecutedMigrations();

        $status = [];
        foreach ($allFiles as $file) {
            $status[] = [
                'migration' => $file,
                'status' => in_array($file, $executed) ? 'executed' : 'pending'
            ];
        }

        return $status;
    }
}