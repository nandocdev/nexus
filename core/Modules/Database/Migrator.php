<?php
namespace Nexus\Modules\Database;

class Migrator {
    private $db;
    private $migrationsTable = 'migrations';
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->createMigrationsTable();
    }
    
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->db->query($sql);
    }
    
    public function run($migrations) {
        $executed = $this->getExecutedMigrations();
        
        foreach ($migrations as $migration) {
            $className = get_class($migration);
            if (!in_array($className, $executed)) {
                $migration->up();
                $this->recordMigration($className);
                Logger::info("Migration executed: $className");
            }
        }
    }
    
    public function rollback($migrations, $steps = 1) {
        $executed = $this->getExecutedMigrations();
        $toRollback = array_slice(array_reverse($executed), 0, $steps);
        
        foreach ($toRollback as $className) {
            foreach ($migrations as $migration) {
                if (get_class($migration) === $className) {
                    $migration->down();
                    $this->removeMigration($className);
                    Logger::info("Migration rolled back: $className");
                    break;
                }
            }
        }
    }
    
    private function getExecutedMigrations() {
        $stmt = $this->db->query("SELECT migration FROM {$this->migrationsTable} ORDER BY executed_at ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    private function recordMigration($migration) {
        $this->db->query("INSERT INTO {$this->migrationsTable} (migration) VALUES (?)", [$migration]);
    }
    
    private function removeMigration($migration) {
        $this->db->query("DELETE FROM {$this->migrationsTable} WHERE migration = ?", [$migration]);
    }
}