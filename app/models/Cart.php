<?php
namespace App\Models;

use App\Core\Model;

/**
 * Cart Model
 */
class Cart extends Model
{
    /**
     * @var string
     */
    protected $table = 'carts';
    
    /**
     * @var array
     */
    protected $fillable = [
        'user_id'
    ];
    
    /**
     * Get or create a cart for user
     * 
     * @param int $userId
     * @return int
     */
    public function getOrCreateCart($userId)
    {
        // Check if user already has a cart
        $cart = $this->db->selectOne(
            "SELECT * FROM carts WHERE user_id = ?",
            [$userId]
        );
        
        if ($cart) {
            return $cart['id'];
        }
        
        // Create a new cart
        return $this->create(['user_id' => $userId]);
    }
    
    /**
     * Get cart items for a user
     * 
     * @param int $userId
     * @return array
     */
    public function getCartItems($userId)
    {
        return $this->db->select(
            "SELECT ci.id, ci.quantity, p.id as product_id, p.name, p.image_url, 
                    p.price, p.quantity_desc, (p.price * ci.quantity) as subtotal 
             FROM carts c 
             JOIN cart_items ci ON c.id = ci.cart_id 
             JOIN products p ON ci.product_id = p.id 
             WHERE c.user_id = ? 
             ORDER BY ci.id DESC",
            [$userId]
        );
    }
    
    /**
     * Calculate cart total
     * 
     * @param int $userId
     * @return float
     */
    public function getCartTotal($userId)
    {
        $result = $this->db->selectOne(
            "SELECT SUM(p.price * ci.quantity) as total 
             FROM carts c 
             JOIN cart_items ci ON c.id = ci.cart_id 
             JOIN products p ON ci.product_id = p.id 
             WHERE c.user_id = ?",
            [$userId]
        );
        
        return $result['total'] ?? 0;
    }
    
    /**
     * Calculate cart count
     * 
     * @param int $userId
     * @return int
     */
    public function getCartCount($userId)
    {
        $result = $this->db->selectOne(
            "SELECT SUM(ci.quantity) as count 
             FROM carts c 
             JOIN cart_items ci ON c.id = ci.cart_id 
             WHERE c.user_id = ?",
            [$userId]
        );
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Add an item to the cart
     * 
     * @param int $userId
     * @param int $productId
     * @param int $quantity
     * @return array
     */
    public function addItem($userId, $productId, $quantity = 1)
    {
        // Get or create cart
        $cartId = $this->getOrCreateCart($userId);
        
        // Check if product exists and is active
        $product = $this->db->selectOne(
            "SELECT * FROM products WHERE id = ? AND is_active = TRUE",
            [$productId]
        );
        
        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found or unavailable'
            ];
        }
        
        // Check if product is already in cart
        $cartItem = $this->db->selectOne(
            "SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ?",
            [$cartId, $productId]
        );
        
        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem['quantity'] + $quantity;
            
            $this->db->update(
                'cart_items',
                ['quantity' => $newQuantity],
                'id = ?',
                [$cartItem['id']]
            );
            
            return [
                'success' => true,
                'message' => 'Cart updated successfully',
                'cart_count' => $this->getCartCount($userId),
                'cart_total' => $this->getCartTotal($userId)
            ];
        }
        
        // Add new item to cart
        $this->db->insert(
            'cart_items',
            [
                'cart_id' => $cartId,
                'product_id' => $productId,
                'quantity' => $quantity
            ]
        );
        
        return [
            'success' => true,
            'message' => 'Product added to cart',
            'cart_count' => $this->getCartCount($userId),
            'cart_total' => $this->getCartTotal($userId)
        ];
    }
    
    /**
     * Update cart item quantity
     * 
     * @param int $userId
     * @param int $cartItemId
     * @param int $quantity
     * @return array
     */
    public function updateItemQuantity($userId, $cartItemId, $quantity)
    {
        // Validate quantity
        if ($quantity < 0) {
            return [
                'success' => false,
                'message' => 'Invalid quantity'
            ];
        }
        
        // Get cart ID
        $cart = $this->db->selectOne(
            "SELECT id FROM carts WHERE user_id = ?",
            [$userId]
        );
        
        if (!$cart) {
            return [
                'success' => false,
                'message' => 'Cart not found'
            ];
        }
        
        // Check if cart item exists and belongs to user
        $cartItem = $this->db->selectOne(
            "SELECT ci.*, p.price 
             FROM cart_items ci 
             JOIN products p ON ci.product_id = p.id 
             WHERE ci.id = ? AND ci.cart_id = ?",
            [$cartItemId, $cart['id']]
        );
        
        if (!$cartItem) {
            return [
                'success' => false,
                'message' => 'Cart item not found'
            ];
        }
        
        if ($quantity === 0) {
            // Remove item from cart
            $this->db->delete(
                'cart_items',
                'id = ?',
                [$cartItemId]
            );
            
            return [
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_count' => $this->getCartCount($userId),
                'cart_total' => $this->getCartTotal($userId),
                'new_quantity' => 0,
                'subtotal' => 0
            ];
        }
        
        // Update quantity
        $this->db->update(
            'cart_items',
            ['quantity' => $quantity],
            'id = ?',
            [$cartItemId]
        );
        
        // Calculate subtotal
        $subtotal = $cartItem['price'] * $quantity;
        
        return [
            'success' => true,
            'message' => 'Cart updated successfully',
            'cart_count' => $this->getCartCount($userId),
            'cart_total' => $this->getCartTotal($userId),
            'new_quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
    
    /**
     * Clear cart
     * 
     * @param int $userId
     * @return array
     */
    public function clearCart($userId)
    {
        // Get cart ID
        $cart = $this->db->selectOne(
            "SELECT id FROM carts WHERE user_id = ?",
            [$userId]
        );
        
        if (!$cart) {
            return [
                'success' => false,
                'message' => 'Cart not found'
            ];
        }
        
        // Delete all cart items
        $this->db->delete(
            'cart_items',
            'cart_id = ?',
            [$cart['id']]
        );
        
        return [
            'success' => true,
            'message' => 'Cart cleared successfully',
            'cart_count' => 0,
            'cart_total' => 0
        ];
    }
}