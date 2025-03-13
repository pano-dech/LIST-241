<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Database Class
 * 
 * Handles database connection and provides methods for executing queries
 */
class Database
{
    /**
     * @var PDO
     */
    private $connection;
    
    /**
     * @var array
     */
    private $config;
    
    /**
     * Initialize the database connection
     * 
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }
    
    /**
     * Establish the database connection
     */
    private function connect()
    {
        $dsn = "mysql:host={$this->config['host']};dbname={$this->config['name']};charset={$this->config['charset']}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $this->connection = new PDO($dsn, $this->config['user'], $this->config['pass'], $options);
        } catch (PDOException $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }
    
    /**
     * Get the PDO connection instance
     * 
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * Execute a query with parameters
     * 
     * @param string $query
     * @param array $params
     * @return \PDOStatement
     */
    public function query($query, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Query Error: " . $e->getMessage());
        }
    }
    
    /**
     * Execute a SELECT query and return all rows
     * 
     * @param string $query
     * @param array $params
     * @return array
     */
    public function select($query, $params = [])
    {
        $stmt = $this->query($query, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Execute a SELECT query and return a single row
     * 
     * @param string $query
     * @param array $params
     * @return array|null
     */
    public function selectOne($query, $params = [])
    {
        $stmt = $this->query($query, $params);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Insert a record and return the last insert ID
     * 
     * @param string $table
     * @param array $data
     * @return int
     */
    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($query, array_values($data));
        return $this->connection->lastInsertId();
    }
    
    /**
     * Update records in a table
     * 
     * @param string $table
     * @param array $data
     * @param string $where
     * @param array $params
     * @return int
     */
    public function update($table, $data, $where, $params = [])
    {
        $setClauses = [];
        
        foreach ($data as $column => $value) {
            $setClauses[] = "{$column} = ?";
        }
        
        $setClause = implode(', ', $setClauses);
        
        $query = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $stmt = $this->query($query, array_merge(array_values($data), $params));
        return $stmt->rowCount();
    }
    
    /**
     * Delete records from a table
     * 
     * @param string $table
     * @param string $where
     * @param array $params
     * @return int
     */
    public function delete($table, $where, $params = [])
    {
        $query = "DELETE FROM {$table} WHERE {$where}";
        
        $stmt = $this->query($query, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Begin a transaction
     */
    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }
    
    /**
     * Commit a transaction
     */
    public function commit()
    {
        $this->connection->commit();
    }
    
    /**
     * Rollback a transaction
     */
    public function rollback()
    {
        $this->connection->rollBack();
    }
}