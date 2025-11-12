<?php
namespace Nexus\Modules\Database;

use PDO;
use Closure;

/**
 * Advanced Query Builder for Nexus ORM
 */
class QueryBuilder
{
    protected $connection;
    protected $table;
    protected $columns = ['*'];
    protected $wheres = [];
    protected $joins = [];
    protected $orders = [];
    protected $groups = [];
    protected $havings = [];
    protected $limit;
    protected $offset;
    protected $bindings = [];

    public function __construct($connection, $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * Set the columns to select
     */
    public function select($columns = ['*'])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    /**
     * Add a where clause
     */
    public function where($column, $operator = null, $value = null, $boolean = 'AND')
    {
        if ($column instanceof Closure) {
            return $this->whereNested($column, $boolean);
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];

        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Add an or where clause
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Add a where in clause
     */
    public function whereIn($column, array $values, $boolean = 'AND', $not = false)
    {
        $type = $not ? 'not_in' : 'in';
        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];

        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    /**
     * Add a where not in clause
     */
    public function whereNotIn($column, array $values, $boolean = 'AND')
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add a where null clause
     */
    public function whereNull($column, $boolean = 'AND', $not = false)
    {
        $type = $not ? 'not_null' : 'null';
        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'boolean' => $boolean
        ];

        return $this;
    }

    /**
     * Add a where not null clause
     */
    public function whereNotNull($column, $boolean = 'AND')
    {
        return $this->whereNull($column, $boolean, true);
    }

    /**
     * Add a where between clause
     */
    public function whereBetween($column, array $values, $boolean = 'AND', $not = false)
    {
        $type = $not ? 'not_between' : 'between';
        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];

        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    /**
     * Add a where not between clause
     */
    public function whereNotBetween($column, array $values, $boolean = 'AND')
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    /**
     * Add a nested where clause
     */
    public function whereNested(Closure $callback, $boolean = 'AND')
    {
        $query = new static($this->connection, $this->table);
        $callback($query);

        $this->wheres[] = [
            'type' => 'nested',
            'query' => $query,
            'boolean' => $boolean
        ];

        $this->bindings = array_merge($this->bindings, $query->getBindings());

        return $this;
    }

    /**
     * Add a join clause
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'INNER')
    {
        if ($first instanceof Closure) {
            $this->joins[] = [
                'type' => $type,
                'table' => $table,
                'callback' => $first
            ];
        } else {
            $this->joins[] = [
                'type' => $type,
                'table' => $table,
                'first' => $first,
                'operator' => $operator,
                'second' => $second
            ];
        }

        return $this;
    }

    /**
     * Add a left join clause
     */
    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Add a right join clause
     */
    public function rightJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    /**
     * Add an order by clause
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];

        return $this;
    }

    /**
     * Add a group by clause
     */
    public function groupBy($columns)
    {
        $this->groups = array_merge($this->groups, (array) $columns);
        return $this;
    }

    /**
     * Add a having clause
     */
    public function having($column, $operator = null, $value = null, $boolean = 'AND')
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->havings[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];

        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Set the limit
     */
    public function limit($value)
    {
        $this->limit = $value;
        return $this;
    }

    /**
     * Set the offset
     */
    public function offset($value)
    {
        $this->offset = $value;
        return $this;
    }

    /**
     * Execute a raw SQL query
     */
    public function raw($sql, array $bindings = [])
    {
        $this->bindings = array_merge($this->bindings, $bindings);
        return $this->connection->query($sql, $this->bindings);
    }

    /**
     * Get the results
     */
    public function get()
    {
        $sql = $this->buildSelect();
        $stmt = $this->connection->query($sql, $this->bindings);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get the first result
     */
    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return count($results) > 0 ? $results[0] : null;
    }

    /**
     * Get results as array
     */
    public function toArray()
    {
        return json_decode(json_encode($this->get()), true);
    }

    /**
     * Count the results
     */
    public function count()
    {
        $originalColumns = $this->columns;
        $this->columns = ['COUNT(*) as count'];
        $result = $this->first();
        $this->columns = $originalColumns;

        return (int) $result->count;
    }

    /**
     * Check if records exist
     */
    public function exists()
    {
        return $this->count() > 0;
    }

    /**
     * Insert records
     */
    public function insert(array $values)
    {
        if (empty($values)) {
            return false;
        }

        $sql = $this->buildInsert($values);
        $stmt = $this->connection->query($sql, $this->getInsertBindings($values));
        return $stmt->rowCount() > 0;
    }

    /**
     * Insert and get ID
     */
    public function insertGetId(array $values)
    {
        $this->insert($values);
        return $this->connection->lastInsertId();
    }

    /**
     * Update records
     */
    public function update(array $values)
    {
        $sql = $this->buildUpdate($values);
        $stmt = $this->connection->query($sql, array_merge($this->bindings, array_values($values)));
        return $stmt->rowCount();
    }

    /**
     * Delete records
     */
    public function delete()
    {
        $sql = $this->buildDelete();
        $stmt = $this->connection->query($sql, $this->bindings);
        return $stmt->rowCount();
    }

    /**
     * Get the query bindings
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Build the SELECT SQL
     */
    protected function buildSelect()
    {
        $sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table;

        if (!empty($this->joins)) {
            $sql .= ' ' . $this->buildJoins();
        }

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
        }

        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groups);
        }

        if (!empty($this->havings)) {
            $sql .= ' HAVING ' . $this->buildHavings();
        }

        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ' . $this->buildOrders();
        }

        if ($this->limit) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    /**
     * Build the WHERE clause
     */
    protected function buildWheres()
    {
        $wheres = [];

        foreach ($this->wheres as $index => $where) {
            $boolean = $index === 0 ? '' : $where['boolean'];
            switch ($where['type']) {
                case 'basic':
                    $wheres[] = $boolean . ' ' . $where['column'] . ' ' . $where['operator'] . ' ?';
                    break;
                case 'in':
                    $placeholders = str_repeat('?,', count($where['values']) - 1) . '?';
                    $wheres[] = $boolean . ' ' . $where['column'] . ' IN (' . $placeholders . ')';
                    break;
                case 'not_in':
                    $placeholders = str_repeat('?,', count($where['values']) - 1) . '?';
                    $wheres[] = $boolean . ' ' . $where['column'] . ' NOT IN (' . $placeholders . ')';
                    break;
                case 'null':
                    $wheres[] = $boolean . ' ' . $where['column'] . ' IS NULL';
                    break;
                case 'not_null':
                    $wheres[] = $boolean . ' ' . $where['column'] . ' IS NOT NULL';
                    break;
                case 'between':
                    $wheres[] = $boolean . ' ' . $where['column'] . ' BETWEEN ? AND ?';
                    break;
                case 'not_between':
                    $wheres[] = $boolean . ' ' . $where['column'] . ' NOT BETWEEN ? AND ?';
                    break;
                case 'nested':
                    $wheres[] = $boolean . ' (' . $where['query']->buildWheres() . ')';
                    break;
            }
        }

        return ltrim(implode(' ', $wheres));
    }

    /**
     * Build the JOIN clause
     */
    protected function buildJoins()
    {
        $joins = [];

        foreach ($this->joins as $join) {
            if (isset($join['callback'])) {
                // Advanced join with callback - simplified for now
                $joins[] = $join['type'] . ' JOIN ' . $join['table'];
            } else {
                $joins[] = $join['type'] . ' JOIN ' . $join['table'] . ' ON ' . $join['first'] . ' ' . $join['operator'] . ' ' . $join['second'];
            }
        }

        return implode(' ', $joins);
    }

    /**
     * Build the ORDER BY clause
     */
    protected function buildOrders()
    {
        $orders = [];

        foreach ($this->orders as $order) {
            $orders[] = $order['column'] . ' ' . $order['direction'];
        }

        return implode(', ', $orders);
    }

    /**
     * Build the HAVING clause
     */
    protected function buildHavings()
    {
        $havings = [];

        foreach ($this->havings as $index => $having) {
            $boolean = $index === 0 ? '' : $having['boolean'];
            $havings[] = $boolean . ' ' . $having['column'] . ' ' . $having['operator'] . ' ?';
        }

        return ltrim(implode(' ', $havings));
    }

    /**
     * Build INSERT SQL
     */
    protected function buildInsert(array $values)
    {
        $columns = array_keys($values);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';

        return 'INSERT INTO ' . $this->table . ' (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')';
    }

    /**
     * Build UPDATE SQL
     */
    protected function buildUpdate(array $values)
    {
        $sets = [];

        foreach (array_keys($values) as $column) {
            $sets[] = $column . ' = ?';
        }

        $sql = 'UPDATE ' . $this->table . ' SET ' . implode(', ', $sets);

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
        }

        return $sql;
    }

    /**
     * Build DELETE SQL
     */
    protected function buildDelete()
    {
        $sql = 'DELETE FROM ' . $this->table;

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
        }

        return $sql;
    }

    /**
     * Get bindings for INSERT
     */
    protected function getInsertBindings(array $values)
    {
        return array_values($values);
    }
}