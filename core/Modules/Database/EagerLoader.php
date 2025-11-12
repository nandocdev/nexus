<?php
namespace Nexus\Modules\Database;

/**
 * Eager loading functionality for relationships
 */
class EagerLoader
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Load relationships for the given models
     */
    public function load($models, array $relations)
    {
        $models = is_array($models) ? $models : [$models];

        foreach ($relations as $name => $constraints) {
            if (is_numeric($name)) {
                $name = $constraints;
                $constraints = null;
            }

            $this->loadRelation($models, $name, $constraints);
        }
    }

    /**
     * Load missing relationships
     */
    public function loadMissing($models, array $relations)
    {
        $models = is_array($models) ? $models : [$models];

        foreach ($relations as $name => $constraints) {
            if (is_numeric($name)) {
                $name = $constraints;
                $constraints = null;
            }

            $this->loadRelation($models, $name, $constraints, true);
        }
    }

    /**
     * Load a specific relationship
     */
    protected function loadRelation(array $models, $name, $constraints = null, $missing = false)
    {
        $relation = $this->getRelationInstance($models[0], $name);

        if ($missing && $models[0]->getRelation($name) !== null) {
            return;
        }

        if ($constraints) {
            $constraints($relation->getQuery());
        }

        $relation->addEagerConstraints($models);
        $results = $relation->getQuery()->get();

        $relation->match($models, $results, $name);
    }

    /**
     * Get relation instance from model
     */
    protected function getRelationInstance($model, $name)
    {
        $method = 'get' . ucfirst($name) . 'Relation';

        // First try the explicit relation method
        if (method_exists($model, $method)) {
            return $model->$method();
        }

        // Then try calling the method directly (for dynamic relationships)
        try {
            $relation = $model->$name();
            if ($relation instanceof Relation) {
                return $relation;
            }
        } catch (\Exception $e) {
            // Method doesn't exist
        }

        throw new \InvalidArgumentException("Relationship {$name} does not exist on model " . get_class($model));
    }
}