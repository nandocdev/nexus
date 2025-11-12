<?php
namespace Nexus\Modules\Database;
use PDO;
use PDOException;
use Exception;
use Nexus\Modules\Config\Config;
// app/Core/Database.php
class Database {
    private $pdo;
    private static $instance = null;
    
    private function __construct() {
        $config = Config::get('database');
        if (!is_array($config)) {
            // Si no es array, reconstruir desde valores individuales
            $config = [
                'driver' => Config::get('driver'),
                'host' => Config::get('host'),
                'port' => Config::get('port'),
                'database' => Config::get('database'),
                'username' => Config::get('username'),
                'password' => Config::get('password'),
                'options' => Config::get('options', [])
            ];
        }
        
        $driver = $config['driver'] ?? 'mysql';
        
        try {
            switch ($driver) {
                case 'mysql':
                    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8";
                    break;
                case 'pgsql':
                    $dsn = "pgsql:host={$config['host']};dbname={$config['database']}";
                    break;
                case 'sqlite':
                    $dsn = "sqlite:{$config['database']}";
                    break;
                default:
                    throw new Exception("Unsupported database driver: $driver");
            }
            
            $this->pdo = new PDO($dsn, $config['username'] ?? null, $config['password'] ?? null, $config['options'] ?? []);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // Logger::error("Database query failed: " . $e->getMessage() . " SQL: $sql");
            throw new Exception("Database query failed: " . $e->getMessage() . " SQL: $sql");
        }
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    public function raw($sql, $params = []) {
        return $this->query($sql, $params);
    }
}