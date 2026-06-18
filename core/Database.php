<?php
/**
 * Database Connection Class
 * Simplified PDO wrapper
 */

namespace Core;

use PDO;
use PDOException;

class Database
{
    protected $pdo;
    protected $table = '';

    public function __construct($config)
    {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function table($table)
    {
        $this->table = $table;
        return new class($this->pdo, $table, null, null, null) {
            protected $pdo;
            protected $table;
            protected $wheres = [];
            protected $orderCol = null;
            protected $orderDir = 'ASC';
            protected $limitCount = 0;

            public function __construct($pdo, $table, $column, $operator, $value)
            {
                $this->pdo = $pdo;
                $this->table = $table;
            }

            public function where($column, $operator = null, $value = null)
            {
                if ($column === null) return $this;
                if (func_num_args() === 2) { $value = $operator; $operator = '='; }
                $this->wheres[] = [$column, $operator, $value];
                return $this;
            }

            public function orderBy($column, $direction = 'ASC')
            {
                $this->orderCol = $column;
                $this->orderDir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                return $this;
            }

            public function limit($limit)
            {
                $this->limitCount = (int)$limit;
                return $this;
            }

            public function get()
            {
                if (empty($this->wheres)) {
                    $sql = "SELECT * FROM {$this->table}";
                } else {
                    $clauses = [];
                    $params = [];
                    foreach ($this->wheres as $w) {
                        $clauses[] = "{$w[0]} {$w[1]} ?";
                        $params[] = $w[2];
                    }
                    $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $clauses);
                }
                if ($this->orderCol) $sql .= " ORDER BY {$this->orderCol} {$this->orderDir}";
                if ($this->limitCount > 0) $sql .= " LIMIT {$this->limitCount}";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params ?? []);
                return $stmt->fetchAll();
            }

            public function first()
            {
                $this->limitCount = 1;
                $rows = $this->get();
                return $rows[0] ?? null;
            }

            public function delete()
            {
                $clauses = [];
                $params = [];
                foreach ($this->wheres as $w) {
                    $clauses[] = "{$w[0]} {$w[1]} ?";
                    $params[] = $w[2];
                }
                $sql = "DELETE FROM {$this->table} WHERE " . implode(' AND ', $clauses);
                return $this->pdo->prepare($sql)->execute($params);
            }

            public function update($data)
            {
                $sets = [];
                $params = [];
                foreach ($data as $col => $val) {
                    $sets[] = "{$col} = ?";
                    $params[] = $val;
                }
                $clauses = [];
                foreach ($this->wheres as $w) {
                    $clauses[] = "{$w[0]} {$w[1]} ?";
                    $params[] = $w[2];
                }
                $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE " . implode(' AND ', $clauses);
                return $this->pdo->prepare($sql)->execute($params);
            }

            public function value($column)
            {
                $result = $this->first();
                return $result->{$column} ?? null;
            }
        };
    }

    public function insertGetId($data)
    {
        $columns = '`' . implode('`, `', array_keys($data)) . '`';
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();

        return (int)$this->pdo->lastInsertId();
    }

    public function where($column = null, $operator = null, $value = null)
    {
        if (func_num_args() === 2) { $value = $operator; $operator = '='; }
        $builder = $this->table($this->table);
        if ($column !== null) return $builder->where($column, $operator, $value);
        return $builder;
    }

    public function first()
    {
        return $this->table($this->table)->first();
    }

    public function get()
    {
        return $this->table($this->table)->get();
    }

    public function update($data)
    {
        return $this->table($this->table)->update($data);
    }

    public function delete()
    {
        return $this->table($this->table)->delete();
    }

    public function value($column)
    {
        return $this->table($this->table)->value($column);
    }

    public function orderBy($column, $direction = 'ASC')
    {
        return $this->table($this->table)->orderBy($column, $direction);
    }

    public function limit($limit)
    {
        return $this->table($this->table)->limit($limit);
    }
}
