<?php
namespace Nexus\Modules\Database;

use Closure;

/**
 * Base class for model relationships
 */
abstract class Relation
{
    protected $parent;
    protected $related;
    protected $foreignKey;
    protected $localKey;
    protected $query;

    public function __construct(Model $parent, $related, $foreignKey, $localKey)
    {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        $this->query = $related::query();
    }

    /**
     * Get the results of the relationship
     */
    abstract public function getResults();

    /**
     * Get the query builder for the relationship
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Add constraints to the query
     */
    abstract public function addConstraints();

    /**
     * Add eager loading constraints
     */
    abstract public function addEagerConstraints(array $models);

    /**
     * Match the eagerly loaded results to their parents
     */
    abstract public function match(array $models, $results, $relation);

    /**
     * Get the foreign key name
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Get the local key name
     */
    public function getLocalKey()
    {
        return $this->localKey;
    }
}

/**
 * Has One relationship
 */
class HasOne extends Relation
{
    public function getResults()
    {
        $this->addConstraints();
        return $this->query->first();
    }

    public function addConstraints()
    {
        $this->query->where($this->foreignKey, '=', $this->parent->{$this->localKey});
    }

    public function addEagerConstraints(array $models)
    {
        $keys = $this->getKeys($models, $this->localKey);
        $this->query->whereIn($this->foreignKey, $keys);
    }

    public function match(array $models, $results, $relation)
    {
        $foreign = $this->foreignKey;
        $local = $this->localKey;

        $dictionary = [];
        foreach ($results as $result) {
            $dictionary[$result->$foreign] = $result;
        }

        foreach ($models as $model) {
            if (isset($dictionary[$model->$local])) {
                $model->setRelation($relation, $dictionary[$model->$local]);
            }
        }
    }

    protected function getKeys(array $models, $key)
    {
        return array_unique(array_map(function ($model) use ($key) {
            return $model->$key;
        }, $models));
    }
}

/**
 * Has Many relationship
 */
class HasMany extends HasOne
{
    public function getResults()
    {
        $this->addConstraints();
        return $this->query->get();
    }

    public function match(array $models, $results, $relation)
    {
        $foreign = $this->foreignKey;
        $local = $this->localKey;

        $dictionary = [];
        foreach ($results as $result) {
            $dictionary[$result->$foreign][] = $result;
        }

        foreach ($models as $model) {
            if (isset($dictionary[$model->$local])) {
                $model->setRelation($relation, $dictionary[$model->$local]);
            } else {
                $model->setRelation($relation, []);
            }
        }
    }
}

/**
 * Belongs To relationship
 */
class BelongsTo extends Relation
{
    public function getResults()
    {
        $this->addConstraints();
        return $this->query->first();
    }

    public function addConstraints()
    {
        $this->query->where($this->localKey, '=', $this->parent->{$this->foreignKey});
    }

    public function addEagerConstraints(array $models)
    {
        $keys = $this->getKeys($models, $this->foreignKey);
        $this->query->whereIn($this->localKey, $keys);
    }

    public function match(array $models, $results, $relation)
    {
        $foreign = $this->foreignKey;
        $local = $this->localKey;

        $dictionary = [];
        foreach ($results as $result) {
            $dictionary[$result->$local] = $result;
        }

        foreach ($models as $model) {
            if (isset($dictionary[$model->$foreign])) {
                $model->setRelation($relation, $dictionary[$model->$foreign]);
            }
        }
    }

    protected function getKeys(array $models, $key)
    {
        return array_unique(array_map(function ($model) use ($key) {
            return $model->$key;
        }, $models));
    }
}

/**
 * Belongs To Many relationship (Many-to-Many)
 */
class BelongsToMany extends Relation
{
    protected $pivotTable;
    protected $pivotColumns = ['*'];
    protected $pivotWheres = [];

    public function __construct(Model $parent, $related, $pivotTable, $foreignKey, $relatedKey, $localKey = null)
    {
        $this->pivotTable = $pivotTable;
        parent::__construct($parent, $related, $foreignKey, $localKey ?: $parent->getKeyName());
        $this->relatedKey = $relatedKey;
    }

    public function getResults()
    {
        $this->addConstraints();
        return $this->query->get();
    }

    public function addConstraints()
    {
        $this->query->join($this->pivotTable, $this->related->getTable() . '.' . $this->related->getKeyName(), '=', $this->pivotTable . '.' . $this->relatedKey)
                    ->where($this->pivotTable . '.' . $this->foreignKey, '=', $this->parent->{$this->localKey});
    }

    public function addEagerConstraints(array $models)
    {
        $keys = $this->getKeys($models, $this->localKey);
        $this->query->join($this->pivotTable, $this->related->getTable() . '.' . $this->related->getKeyName(), '=', $this->pivotTable . '.' . $this->relatedKey)
                    ->whereIn($this->pivotTable . '.' . $this->foreignKey, $keys);
    }

    public function match(array $models, $results, $relation)
    {
        $foreign = $this->foreignKey;
        $local = $this->localKey;

        $dictionary = [];
        foreach ($results as $result) {
            $dictionary[$result->pivot->$foreign][] = $result;
        }

        foreach ($models as $model) {
            if (isset($dictionary[$model->$local])) {
                $model->setRelation($relation, $dictionary[$model->$local]);
            } else {
                $model->setRelation($relation, []);
            }
        }
    }

    protected function getKeys(array $models, $key)
    {
        return array_unique(array_map(function ($model) use ($key) {
            return $model->$key;
        }, $models));
    }
}