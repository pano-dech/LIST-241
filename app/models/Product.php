<?php
namespace App\Models;

use App\Core\Model;

/**
 * Product Model
 */
class Product extends Model
{
    /**
     * @var string
     */
    protected $table = 'products';
    
    /**
     * @var array
     */
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'image_url',
        'price',
        'quantity_desc',
        'is_active'
    ];
    
    /**
     * Get active products
     * 
     * @return array
     */
    public function getActiveProducts()
    {
        return $this->db->select("SELECT * FROM active_products ORDER BY name");
    }
    
    /**
     * Get active products by category
     * 
     * @param int $categoryId
     * @return array
     */
    public function getProductsByCategory($categoryId)
    {
        return $this->db->select(
            "SELECT * FROM active_products WHERE category_id = ? ORDER BY name",
            [$categoryId]
        );
    }
    
    /**
     * Get hot deals
     * 
     * @param int $limit
     * @return array
     */
    public function getHotDeals($limit = 3)
    {
        return $this->db->select(
            "SELECT * FROM active_products WHERE category_id = 1 ORDER BY id DESC LIMIT ?",
            [$limit]
        );
    }
    
    /**
     * Search products
     * 
     * @param string $keyword
     * @return array
     */
    public function searchProducts($keyword)
    {
        $search = "%{$keyword}%";
        
        return $this->db->select(
            "SELECT * FROM active_products WHERE name LIKE ? OR description LIKE ? ORDER BY name",
            [$search, $search]
        );
    }
    
    /**
     * Get product with category
     * 
     * @param int $id
     * @return array|null
     */
    public function getProductWithCategory($id)
    {
        return $this->db->selectOne(
            "SELECT p.*, c.name as category_name 
             FROM products p 
             JOIN categories c ON p.category_id = c.id 
             WHERE p.id = ? AND p.is_active = TRUE",
            [$id]
        );
    }
    
    /**
     * Get all categories
     * 
     * @return array
     */
    public function getAllCategories()
    {
        return $this->db->select(
            "SELECT * FROM categories WHERE is_active = TRUE ORDER BY name"
        );
    }
}