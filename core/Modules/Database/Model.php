<?php
namespace Nexus\Modules\Database;
use PDO;
use Nexus\Modules\Database\Database;
use Nexus\Modules\Database\QueryBuilder;
use Nexus\Modules\Database\HasOne;
use Nexus\Modules\Database\HasMany;
use Nexus\Modules\Database\BelongsTo;
use Nexus\Modules\Database\BelongsToMany;
use Nexus\Modules\Database\EagerLoader;

// Include relationship classes
require_once __DIR__ . '/Relations.php';
// app/Core/Model.php
abstract class Model {
    protected $db;
    protected $fillable = [];
    protected $hidden = [];
    protected $primaryKey = 'id';
    protected $timestamps = true;
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $attributes = [];
    protected $relations = [];
    protected $touches = [];
    protected $softDelete = false;
    protected $exists = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    public function __construct(array $attributes = []) {
        $this->db = Database::getInstance();
        
        if (!empty($attributes)) {
            $this->fill($attributes);
        }
    }
       

    /**
     * Fill the model with an array of attributes
     */
    public function fill(array $attributes) {
        foreach ($this->filterFillable($attributes) as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Set a given attribute on the model
     */

    /**
     * Get an attribute from the model
     */
    public function getAttribute($key) {
        // Check for accessor
        $method = 'get' . ucfirst($key) . 'Attribute';
        if (method_exists($this, $method)) {
            return $this->$method($this->attributes[$key] ?? null);
        }

        return $this->attributes[$key] ?? null;
    }

    /**
     * Dynamically retrieve attributes on the model
     */
    public function __get($key) {
        // Check if it's a relationship
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        // Check if it's a relationship method
        if (method_exists($this, $key)) {
            $relation = $this->$key();
            if ($relation instanceof Relation) {
                return $this->relations[$key] = $relation->getResults();
            }
        }

        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model
     */
    public function __set($key, $value) {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists
     */
    public function __isset($key) {
        return isset($this->attributes[$key]);
    }

    /**
     * Unset an attribute on the model
     */
    public function __unset($key) {
        unset($this->attributes[$key]);
    }

    /**
     * Convert the model instance to an array
     */
    public function toArray() {
        $attributes = $this->attributes;

        // Add relations
        foreach ($this->relations as $key => $relation) {
            if ($relation instanceof Model) {
                $attributes[$key] = $relation->toArray();
            } elseif (is_array($relation)) {
                $attributes[$key] = array_map(function ($item) {
                    return $item instanceof Model ? $item->toArray() : $item;
                }, $relation);
            } else {
                $attributes[$key] = $relation;
            }
        }

        // Hide hidden attributes
        foreach ($this->hidden as $hidden) {
            unset($attributes[$hidden]);
        }

        return $attributes;
    }

    /**
     * Convert the model to JSON
     */
    public function toJson($options = 0) {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get the table name
     */
    public function getTable() {
        return $this->table ?? strtolower(class_basename(static::class)) . 's';
    }

    /**
     * Get the primary key name
     */
    public function getKeyName() {
        return $this->primaryKey;
    }

    /**
     * Get the primary key value
     */
    public function getKey() {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Set the primary key value
     */
    public function setKey($value) {
        $this->setAttribute($this->getKeyName(), $value);
        return $this;
    }

    /**
     * Get the query builder instance
     */
    public static function query() {
        $instance = new static();
        return new QueryBuilder($instance->db, $instance->getTable());
    }

    /**
     * Execute a raw SQL query
     */
    public static function raw($sql, array $bindings = []) {
        $instance = new static();
        return $instance->db->query($sql, $bindings);
    }

    /**
     * Find a model by its primary key
     */
    // public static function find($id) {
    //     $instance = new static();
    //     $query = static::query();

    //     if ($instance->softDelete) {
    //         $query->whereNull($instance->getTable() . '.' . static::DELETED_AT);
    //     }

    //     $result = $query->where($instance->getKeyName(), $id)->first();

    //     if ($result) {
    //         $model = new static();
    //         $model->attributes = (array) $result;
    //         $model->exists = true;
    //         return $model->hideAttributes($model);
    //     }

    //     return null;
    // }

    /**
     * Find models by primary keys
     */
    public static function findMany(array $ids) {
        $instance = new static();
        $query = static::query();

        if ($instance->softDelete) {
            $query->whereNull($instance->getTable() . '.' . static::DELETED_AT);
        }

        $results = $query->whereIn($instance->getKeyName(), $ids)->get();

        $models = [];
        foreach ($results as $result) {
            $model = new static();
            $model->attributes = (array) $result;
            $model->exists = true;
            $models[] = $model->hideAttributes($model);
        }

        return $models;
    }




    /**
     * Set an attribute value
     */
    public function setAttribute($key, $value)
    {
        // Apply mutator if exists
        $method = 'set' . ucfirst($key) . 'Attribute';
        if (method_exists($this, $method)) {
            $value = $this->$method($value);
        }

        $this->attributes[$key] = $value;
        return $this;
    }

   

    /**
     * Create a new model instance
     */
    public static function create(array $attributes = []) {
        $instance = new static();
        $instance->fill($attributes);

        // Set timestamps
        if ($instance->timestamps) {
            $now = date($instance->dateFormat);
            $instance->setAttribute(static::CREATED_AT, $now);
            $instance->setAttribute(static::UPDATED_AT, $now);
        }

        $data = $instance->attributes;
        $id = static::query()->insertGetId($data);

        $instance->setKey($id);
        $instance->exists = true;

        return $instance;
    }

    /**
     * Save the model to the database
     */
    public function save() {
        if ($this->exists) {
            return $this->update($this->attributes);
        } else {
            $id = static::create($this->attributes)->getKey();
            $this->setKey($id);
            $this->exists = true;
            return true;
        }
    }

    /**
     * Update the model in the database
     */
    public function update(array $attributes = []) {
        if (!$this->exists) {
            return false;
        }

        $this->fill($attributes);

        // Set updated timestamp
        if ($this->timestamps) {
            $this->setAttribute(static::UPDATED_AT, date($this->dateFormat));
        }

        $data = $this->attributes;
        $keyName = $this->getKeyName();
        $keyValue = $this->getKey();

        unset($data[$keyName]); // Don't update the primary key

        $query = static::query()->where($keyName, $keyValue);
        $affected = $query->update($data);

        // Touch related models
        $this->touchOwners();

        return $affected > 0;
    }

    /**
     * Delete the model from the database
     */
    public function delete() {
        if (!$this->exists) {
            return false;
        }

        $keyName = $this->getKeyName();
        $keyValue = $this->getKey();

        if ($this->softDelete) {
            // Soft delete
            $this->setAttribute(static::DELETED_AT, date($this->dateFormat));
            $query = static::query()->where($keyName, $keyValue);
            $affected = $query->update([static::DELETED_AT => $this->getAttribute(static::DELETED_AT)]);
        } else {
            // Hard delete
            $query = static::query()->where($keyName, $keyValue);
            $affected = $query->delete();
        }

        if ($affected > 0) {
            $this->exists = false;
        }

        return $affected > 0;
    }

    /**
     * Restore a soft deleted model
     */
    public function restore() {
        if (!$this->softDelete || !$this->exists) {
            return false;
        }

        $this->setAttribute(static::DELETED_AT, null);
        $keyName = $this->getKeyName();
        $keyValue = $this->getKey();

        $query = static::query()->where($keyName, $keyValue);
        $affected = $query->update([static::DELETED_AT => null]);

        return $affected > 0;
    }

    /**
     * Force delete a soft deleted model
     */
    public function forceDelete() {
        if (!$this->softDelete) {
            return $this->delete();
        }

        $this->softDelete = false;
        $result = $this->delete();
        $this->softDelete = true;

        return $result;
    }

    /**
     * Get trashed (soft deleted) models
     */
    public static function onlyTrashed() {
        $instance = new static();
        if (!$instance->softDelete) {
            return [];
        }

        $results = static::query()
            ->whereNotNull($instance->getTable() . '.' . static::DELETED_AT)
            ->get();

        $models = [];
        foreach ($results as $result) {
            $model = new static();
            $model->attributes = (array) $result;
            $model->exists = true;
            $models[] = $model->hideAttributes($model);
        }

        return $models;
    }

    /**
     * Get models with trashed
     */
    public static function withTrashed() {
        $instance = new static();
        if (!$instance->softDelete) {
            return static::all();
        }

        $results = static::query()->get();

        $models = [];
        foreach ($results as $result) {
            $model = new static();
            $model->attributes = (array) $result;
            $model->exists = true;
            $models[] = $model->hideAttributes($model);
        }

        return $models;
    }

    /**
     * Define a one-to-one relationship
     */
    public function hasOne($related, $foreignKey = null, $localKey = null) {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->getKeyName();

        $instance = new $related;
        return new HasOne($this, $instance, $foreignKey, $localKey);
    }

    /**
     * Define a one-to-many relationship
     */
    public function hasMany($related, $foreignKey = null, $localKey = null) {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->getKeyName();

        $instance = new $related;
        return new HasMany($this, $instance, $foreignKey, $localKey);
    }

    /**
     * Define an inverse one-to-one or many relationship
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null) {
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $ownerKey = $ownerKey ?: (new $related)->getKeyName();

        $instance = new $related;
        return new BelongsTo($this, $instance, $foreignKey, $ownerKey);
    }

    /**
     * Define a many-to-many relationship
     */
    public function belongsToMany($related, $pivotTable = null, $foreignKey = null, $relatedKey = null, $localKey = null) {
        $pivotTable = $pivotTable ?: $this->getPivotTableName($related);
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $relatedKey = $relatedKey ?: (new $related)->getForeignKey();
        $localKey = $localKey ?: $this->getKeyName();

        $instance = new $related;
        return new BelongsToMany($this, $instance, $pivotTable, $foreignKey, $relatedKey, $localKey);
    }

    /**
     * Get the relationship value
     */
    public function getRelation($name) {
        return $this->relations[$name] ?? null;
    }

    /**
     * Set the relationship value
     */
    public function setRelation($name, $value) {
        $this->relations[$name] = $value;
        return $this;
    }

    /**
     * Load relationships eagerly
     */
    public function load($relations) {
        $relations = is_string($relations) ? func_get_args() : $relations;

        $query = new EagerLoader($this);
        $query->load($this, $relations);

        return $this;
    }

    /**
     * Load relationship with constraints
     */
    public function loadMissing($relations) {
        $relations = is_string($relations) ? func_get_args() : $relations;

        $query = new EagerLoader($this);
        $query->loadMissing($this, $relations);

        return $this;
    }

    /**
     * Get foreign key name
     */
    public function getForeignKey() {
        return strtolower(basename(str_replace('\\', '/', get_class($this)))) . '_id';
    }

    /**
     * Get pivot table name
     */
    protected function getPivotTableName($related) {
        $tables = [strtolower(basename(str_replace('\\', '/', get_class($this)))), strtolower(basename(str_replace('\\', '/', $related)))];
        sort($tables);
        return implode('_', $tables);
    }

    /**
     * Touch the owning relations
     */
    public function touchOwners() {
        foreach ($this->touches as $relation) {
            $this->$relation()->touch();
        }
    }

    /**
     * Update the model's update timestamp
     */
    public function touch() {
        if (!$this->timestamps) {
            return false;
        }

        $this->setAttribute(static::UPDATED_AT, date($this->dateFormat));
        return $this->save();
    }

    /**
     * Filter fillable attributes
     */
    // protected function filterFillable($data) {
    //     if (empty($this->fillable)) {
    //         return $data;
    //     }
    //     return array_intersect_key($data, array_flip($this->fillable));
    // }

    /**
     * Hide attributes from array/json output
     */
    protected function hideAttributes($model) {
        if (empty($this->hidden)) {
            return $model;
        }

        if (is_object($model)) {
            foreach ($this->hidden as $hidden) {
                unset($model->$hidden);
            }
        } elseif (is_array($model)) {
            foreach ($this->hidden as $hidden) {
                unset($model[$hidden]);
            }
        }

        return $model;
    }

    /**
     * Handle dynamic method calls for scopes
     */
    public static function __callStatic($method, $parameters) {
        $instance = new static();

        if (method_exists($instance, 'scope' . ucfirst($method))) {
            $scopeMethod = 'scope' . ucfirst($method);
            $query = static::query();

            array_unshift($parameters, $query);
            $result = call_user_func_array([$instance, $scopeMethod], $parameters);

            if ($result instanceof QueryBuilder) {
                return $result;
            }

            return $instance;
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    /**
     * Handle dynamic method calls for relationships
     */
    public function __call($method, $parameters) {
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $parameters);
        }

        // Check if it's a relationship method
        $relationMethod = 'get' . ucfirst($method) . 'Relation';
        if (method_exists($this, $relationMethod)) {
            $relation = $this->$relationMethod();
            if ($relation instanceof Relation) {
                return $relation->getResults();
            }
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    
    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    // public function update($id, $data) {
    //     $data = $this->filterFillable($data);
    //     $data = $this->mutateAttributes($data);
    //     $set = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));
    //     $data[$this->primaryKey] = $id;
        
    //     $sql = "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = :{$this->primaryKey}";
    //     $this->db->query($sql, $data);
        
    //     return $this->find($id);
    // }
    
    // public function delete($id) {
    //     $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
    //     $this->db->query($sql, [$id]);
        
    //     return true;
    // }
    
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
        
        if ($result) {
            $model = new static();
            $model->attributes = (array) $result;
            $model->exists = true;
            return $model;
        }
        
        return null;
    }
    
    public static function all() {
        $instance = new static();
        $stmt = $instance->db->query("SELECT * FROM {$instance->table}");
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $models = [];
        foreach ($results as $result) {
            $model = new static();
            $model->attributes = (array) $result;
            $model->exists = true;
            $models[] = $model;
        }
        
        return $models;
    }
    
    // public static function create($data) {
    //     $instance = new static();
    //     $data = $instance->filterFillable($data);
    //     $data = $instance->mutateAttributes($data);
        
    //     $fields = implode(', ', array_keys($data));
    //     $placeholders = ':' . implode(', :', array_keys($data));
        
    //     $sql = "INSERT INTO {$instance->table} ($fields) VALUES ($placeholders)";
    //     $instance->db->query($sql, $data);
        
    //     return $instance->db->lastInsertId();
    // }
    
    // protected function hideAttributes($object) {
    //     if (empty($this->hidden)) {
    //         return $object;
    //     }
    //     foreach ($this->hidden as $attr) {
    //         unset($object->$attr);
    //     }
    //     return $object;
    // }
    
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