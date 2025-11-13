<?php
namespace Nexus\Modules\Database;
// app/Core/Migration.php
abstract class Migration {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    abstract public function up();
    abstract public function down();

    /**
     * Create a new table
     */
    protected function createTable($tableName, array $columns) {
        $columnDefinitions = [];
        $constraints = [];

        foreach ($columns as $columnName => $columnDefinition) {
            if (is_int($columnName)) {
                // This is a constraint (like FOREIGN KEY)
                $constraints[] = $columnDefinition;
            } else {
                // This is a column definition
                $columnDefinitions[] = "{$columnName} {$columnDefinition}";
            }
        }

        $allDefinitions = array_merge($columnDefinitions, $constraints);
        $columnsSql = implode(', ', $allDefinitions);
        $sql = "CREATE TABLE IF NOT EXISTS {$tableName} ({$columnsSql})";

        try {
            $this->db->query($sql);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to create table {$tableName}: " . $e->getMessage());
        }
    }

    /**
     * Drop a table
     */
    protected function dropTable($tableName) {
        $sql = "DROP TABLE IF EXISTS {$tableName}";

        try {
            $this->db->query($sql);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to drop table {$tableName}: " . $e->getMessage());
        }
    }

    /**
     * Add a column to an existing table
     */
    protected function addColumn($tableName, $columnName, $columnDefinition) {
        $sql = "ALTER TABLE {$tableName} ADD COLUMN {$columnName} {$columnDefinition}";

        try {
            $this->db->query($sql);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to add column {$columnName} to table {$tableName}: " . $e->getMessage());
        }
    }

    /**
     * Drop a column from a table
     */
    protected function dropColumn($tableName, $columnName) {
        $sql = "ALTER TABLE {$tableName} DROP COLUMN {$columnName}";

        try {
            $this->db->query($sql);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to drop column {$columnName} from table {$tableName}: " . $e->getMessage());
        }
    }

    /**
     * Create an index
     */
    protected function createIndex($tableName, $indexName, array $columns) {
        $columnsSql = implode(', ', $columns);
        $sql = "CREATE INDEX {$indexName} ON {$tableName} ({$columnsSql})";

        try {
            $this->db->query($sql);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to create index {$indexName}: " . $e->getMessage());
        }
    }

    /**
     * Drop an index
     */
    protected function dropIndex($tableName, $indexName) {
        $sql = "DROP INDEX {$indexName} ON {$tableName}";

        try {
            $this->db->query($sql);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to drop index {$indexName}: " . $e->getMessage());
        }
    }
}