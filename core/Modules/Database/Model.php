<?php
namespace Nexus\Modules\Database;
use PDO;
use Nexus\Modules\Database\Database;
// app/Core/Model.php
abstract class Model {
    protected $table;
    protected $db;
    protected $fillable = [];
    protected $hidden = [];
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // public function find($id) {
    //     $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
    //     $result = $stmt->fetch(PDO::FETCH_OBJ);
    //     return $result ? $this->hideAttributes($result) : null;
    // }
    
    // public function all() {
    //     $stmt = $this->db->query("SELECT * FROM {$this->table}");
    //     $results = $stmt->fetchAll(PDO::FETCH_OBJ);
    //     return array_map([$this, 'hideAttributes'], $results);
    // }
    
    // public function create($data) {
    //     $data = $this->filterFillable($data);
    //     $data = $this->mutateAttributes($data);
    //     $fields = implode(', ', array_keys($data));
    //     $placeholders = ':' . implode(', :', array_keys($data));
        
    //     $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
    //     $this->db->query($sql, $data);
        
    //     return $this->db->lastInsertId();
    // }
    
    public function update($id, $data) {
        $data = $this->filterFillable($data);
        $data = $this->mutateAttributes($data);
        $set = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));
        $data[$this->primaryKey] = $id;
        
        $sql = "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = :{$this->primaryKey}";
        $this->db->query($sql, $data);
        
        return $this->find($id);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $this->db->query($sql, [$id]);
        
        return true;
    }
    
    public static function where($column, $operator, $value = null) {
        $instance = new static();
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $stmt = $instance->db->query("SELECT * FROM {$instance->table} WHERE $column $operator ?", [$value]);
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        return array_map([$instance, 'hideAttributes'], $results);
    }
    
    public static function find($id) {
        $instance = new static();
        $stmt = $instance->db->query("SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ?", [$id]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $instance->hideAttributes($result) : null;
    }
    
    public static function all() {
        $instance = new static();
        $stmt = $instance->db->query("SELECT * FROM {$instance->table}");
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        return array_map([$instance, 'hideAttributes'], $results);
    }
    
    public static function create($data) {
        $instance = new static();
        $data = $instance->filterFillable($data);
        $data = $instance->mutateAttributes($data);
        
        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$instance->table} ($fields) VALUES ($placeholders)";
        $instance->db->query($sql, $data);
        
        return $instance->db->lastInsertId();
    }
    
    protected function hideAttributes($object) {
        if (empty($this->hidden)) {
            return $object;
        }
        foreach ($this->hidden as $attr) {
            unset($object->$attr);
        }
        return $object;
    }
    
    protected function mutateAttributes($data) {
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key) . 'Attribute';
            if (method_exists($this, $method)) {
                $data[$key] = $this->$method($value);
            }
        }
        return $data;
    }
}