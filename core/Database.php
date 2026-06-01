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
        return $this;
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
        // Apply the 2-arg shorthand before passing to the builder
        if ($column !== null && func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $pdo = $this->pdo;
        $table = $this->table;
        return new class($pdo, $table, $column, $operator, $value) {
            protected $pdo;
            protected $table;
            protected $wheres = [];

            public function __construct($pdo, $table, $column, $operator, $value)
            {
                $this->pdo = $pdo;
                $this->table = $table;
                if ($column !== null) {
                    $this->wheres[] = [$column, $operator, $value];
                }
            }

            public function where($column, $operator = null, $value = null)
            {
                if ($column === null) {
                    return $this;
                }

                if (func_num_args() === 2) {
                    $value = $operator;
                    $operator = '=';
                }
                $this->wheres[] = [$column, $operator, $value];
                return $this;
            }

            public function first()
            {
                if (empty($this->wheres)) {
                    $sql = "SELECT * FROM {$this->table} LIMIT 1";
                } else {
                    $whereClauses = [];
                    $params = [];
                    foreach ($this->wheres as $where) {
                        $whereClauses[] = "{$where[0]} {$where[1]} ?";
                        $params[] = $where[2];
                    }
                    $whereSql = implode(' AND ', $whereClauses);
                    $sql = "SELECT * FROM {$this->table} WHERE {$whereSql} LIMIT 1";
                }

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params ?? []);

                return $stmt->fetch();
            }

            public function get()
            {
                // Similar to first but without limit
                if (empty($this->wheres)) {
                    $sql = "SELECT * FROM {$this->table}";
                } else {
                    $whereClauses = [];
                    $params = [];
                    foreach ($this->wheres as $where) {
                        $whereClauses[] = "{$where[0]} {$where[1]} ?";
                        $params[] = $where[2];
                    }
                    $whereSql = implode(' AND ', $whereClauses);
                    $sql = "SELECT * FROM {$this->table} WHERE {$whereSql}";
                }

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params ?? []);

                return $stmt->fetchAll();
            }

            public function update($data)
            {
                if (empty($this->wheres)) {
                    throw new \Exception('Update requires a where clause to prevent accidental mass updates.');
                }

                $sets = [];
                $params = [];
                foreach ($data as $key => $value) {
                    $sets[] = "{$key} = ?";
                    $params[] = $value;
                }

                $whereClauses = [];
                foreach ($this->wheres as $where) {
                    $whereClauses[] = "{$where[0]} {$where[1]} ?";
                    $params[] = $where[2];
                }

                $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE " . implode(' AND ', $whereClauses);
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute($params);
            }

            public function delete()
            {
                if (empty($this->wheres)) {
                    throw new \Exception('Delete requires a where clause to prevent accidental mass deletions.');
                }

                $whereClauses = [];
                $params = [];
                foreach ($this->wheres as $where) {
                    $whereClauses[] = "{$where[0]} {$where[1]} ?";
                    $params[] = $where[2];
                }

                $sql = "DELETE FROM {$this->table} WHERE " . implode(' AND ', $whereClauses);
                $stmt = $this->pdo->prepare($sql);
                return $stmt->execute($params);
            }

            public function value($column)
            {
                $result = $this->first();
                return $result->{$column} ?? null;
            }
        };
    }

    public function first()
    {
        return $this->where()->first();
    }

    public function get()
    {
        return $this->where()->get();
    }

    public function update($data)
    {
        return $this->where()->update($data);
    }

    public function delete()
    {
        return $this->where()->delete();
    }

    public function value($column)
    {
        return $this->where()->value($column);
    }
}
