<?php
namespace App\Core;

/**
 * Base Model Class
 * 
 * All models should extend this class
 */
class Model
{
    /**
     * @var Database
     */
    protected $db;
    
    /**
     * @var string
     */
    protected $table;
    
    /**
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array
     */
    protected $fillable = [];
    
    /**
     * Initialize the model
     */
    public function __construct()
    {
        // Load configuration
        $config = require_once __DIR__ . '/../../config/config.php';
        
        // Initialize database connection
        $this->db = new Database($config['db']);
    }
    
    /**
     * Find a record by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function find($id)
    {
        return $this->db->selectOne(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }
    
    /**
     * Get all records
     * 
     * @return array
     */
    public function all()
    {
        return $this->db->select("SELECT * FROM {$this->table}");
    }
    
    /**
     * Find records by a column value
     * 
     * @param string $column
     * @param mixed $value
     * @return array
     */
    public function findBy($column, $value)
    {
        return $this->db->select(
            "SELECT * FROM {$this->table} WHERE {$column} = ?",
            [$value]
        );
    }
    
    /**
     * Find one record by a column value
     * 
     * @param string $column
     * @param mixed $value
     * @return array|null
     */
    public function findOneBy($column, $value)
    {
        return $this->db->selectOne(
            "SELECT * FROM {$this->table} WHERE {$column} = ?",
            [$value]
        );
    }
    
    /**
     * Create a new record
     * 
     * @param array $data
     * @return int
     */
    public function create($data)
    {
        // Filter out non-fillable fields
        $fillableData = array_intersect_key($data, array_flip($this->fillable));
        
        return $this->db->insert($this->table, $fillableData);
    }
    
    /**
     * Update a record
     * 
     * @param int $id
     * @param array $data
     * @return int
     */
    public function update($id, $data)
    {
        // Filter out non-fillable fields
        $fillableData = array_intersect_key($data, array_flip($this->fillable));
        
        return $this->db->update(
            $this->table,
            $fillableData,
            "{$this->primaryKey} = ?",
            [$id]
        );
    }
    
    /**
     * Delete a record
     * 
     * @param int $id
     * @return int
     */
    public function delete($id)
    {
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = ?",
            [$id]
        );
    }
    
    /**
     * Count records
     * 
     * @param string $where
     * @param array $params
     * @return int
     */
    public function count($where = '', $params = [])
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($where)) {
            $query .= " WHERE {$where}";
        }
        
        $result = $this->db->selectOne($query, $params);
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Paginate records
     * 
     * @param int $page
     * @param int $perPage
     * @param string $orderBy
     * @param string $direction
     * @param string $where
     * @param array $params
     * @return array
     */
    public function paginate($page = 1, $perPage = 10, $orderBy = 'id', $direction = 'DESC', $where = '', $params = [])
    {
        $query = "SELECT * FROM {$this->table}";
        
        if (!empty($where)) {
            $query .= " WHERE {$where}";
        }
        
        $query .= " ORDER BY {$orderBy} {$direction} LIMIT ? OFFSET ?";
        
        $offset = ($page - 1) * $perPage;
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $items = $this->db->select($query, $params);
        
        $countQuery = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($where)) {
            $countQuery .= " WHERE {$where}";
        }
        
        $countResult = $this->db->selectOne($countQuery, array_slice($params, 0, -2));
        $total = $countResult['count'] ?? 0;
        
        $lastPage = ceil($total / $perPage);
        
        return [
            'items' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => $lastPage,
            'next_page' => $page < $lastPage ? $page + 1 : null,
            'prev_page' => $page > 1 ? $page - 1 : null,
        ];
    }
}