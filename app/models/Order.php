<?php
namespace App\Models;

use App\Core\Model;

/**
 * Order Model
 */
class Order extends Model
{
    /**
     * @var string
     */
    protected $table = 'orders';
    
    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'total_amount',
        'delivery_address',
        'delivery_date',
        'delivery_charge',
        'status',
        'notes'
    ];
    
    /**
     * Create an order from cart
     * 
     * @param int $userId
     * @param array $orderData
     * @return array
     */
    public function createFromCart($userId, $orderData)
    {
        // Get cart items
        $cartModel = new \App\Models\Cart();
        $cartItems = $cartModel->getCartItems($userId);
        
        if (empty($cartItems)) {
            return [
                'success' => false,
                'message' => 'Your cart is empty'
            ];
        }
        
        // Calculate total amount
        $totalAmount = 0;
        foreach ($cartItems as $item) {
            $totalAmount += $item['subtotal'];
        }
        
        // Add delivery charge if applicable
        $totalAmount += $orderData['delivery_charge'];
        
        // Create order
        $orderData['user_id'] = $userId;
        $orderData['total_amount'] = $totalAmount;
        $orderData['status'] = 'pending';
        
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Insert order
            $orderId = $this->create($orderData);
            
            // Insert order items
            foreach ($cartItems as $item) {
                $this->db->insert(
                    'order_items',
                    [
                        'order_id' => $orderId,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price']
                    ]
                );
            }
            
            // Clear cart
            $cartModel->clearCart($userId);
            
            // Commit transaction
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Order placed successfully',
                'order_id' => $orderId
            ];
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->rollback();
            
            return [
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get order details
     * 
     * @param int $orderId
     * @return array
     */
    public function getOrderDetails($orderId)
    {
        // Get order
        $order = $this->find($orderId);
        
        if (!$order) {
            return [
                'success' => false,
                'message' => 'Order not found'
            ];
        }
        
        // Get order items
        $items = $this->db->select(
            "SELECT oi.*, p.name, p.image_url, p.quantity_desc 
             FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.order_id = ?",
            [$orderId]
        );
        
        // Get user
        $user = $this->db->selectOne(
            "SELECT name, email, phone_number FROM users WHERE id = ?",
            [$order['user_id']]
        );
        
        return [
            'success' => true,
            'order' => $order,
            'items' => $items,
            'user' => $user
        ];
    }
    
    /**
     * Get user orders
     * 
     * @param int $userId
     * @return array
     */
    public function getUserOrders($userId)
    {
        return $this->db->select(
            "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );
    }
    
    /**
     * Update order status
     * 
     * @param int $orderId
     * @param string $status
     * @return array
     */
    public function updateStatus($orderId, $status)
    {
        // Valid statuses
        $validStatuses = ['pending', 'confirmed', 'on_delivery', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return [
                'success' => false,
                'message' => 'Invalid status'
            ];
        }
        
        // Update order
        $updated = $this->update($orderId, ['status' => $status]);
        
        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Failed to update order status'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Order status updated successfully'
        ];
    }
    
    /**
     * Calculate delivery charge
     * 
     * @param string $address
     * @param array $metroCities
     * @param array $charges
     * @return float
     */
    public function calculateDeliveryCharge($address, $metroCities, $charges)
    {
        foreach ($metroCities as $city) {
            if (stripos($address, $city) !== false) {
                return $charges['metro_manila'];
            }
        }
        
        return $charges['other_areas'];
    }
    
    /**
     * Get sales report by date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getSalesReport($startDate = null, $endDate = null)
    {
        $query = "SELECT o.id, o.created_at, o.total_amount, o.status, 
                         u.name as customer_name,
                         COUNT(oi.id) as total_items
                  FROM orders o
                  JOIN users u ON o.user_id = u.id
                  JOIN order_items oi ON o.id = oi.order_id
                  WHERE o.status = 'delivered'";
        
        $params = [];
        
        if ($startDate && $endDate) {
            $query .= " AND DATE(o.created_at) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        } elseif ($startDate) {
            $query .= " AND DATE(o.created_at) >= ?";
            $params[] = $startDate;
        } elseif ($endDate) {
            $query .= " AND DATE(o.created_at) <= ?";
            $params[] = $endDate;
        }
        
        $query .= " GROUP BY o.id, o.created_at, o.total_amount, o.status, u.name
                    ORDER BY o.created_at DESC";
        
        $orders = $this->db->select($query, $params);
        
        // Calculate total sales
        $totalSales = 0;
        foreach ($orders as $order) {
            $totalSales += $order['total_amount'];
        }
        
        // Get product sales
        $productQuery = "SELECT p.name, SUM(oi.quantity) as total_quantity, 
                                 SUM(oi.quantity * oi.unit_price) as total_revenue
                          FROM order_items oi
                          JOIN products p ON oi.product_id = p.id
                          JOIN orders o ON oi.order_id = o.id
                          WHERE o.status = 'delivered'";
        
        if ($startDate && $endDate) {
            $productQuery .= " AND DATE(o.created_at) BETWEEN ? AND ?";
        } elseif ($startDate) {
            $productQuery .= " AND DATE(o.created_at) >= ?";
        } elseif ($endDate) {
            $productQuery .= " AND DATE(o.created_at) <= ?";
        }
        
        $productQuery .= " GROUP BY p.name
                           ORDER BY total_revenue DESC";
        
        $productSales = $this->db->select($productQuery, $params);
        
        return [
            'orders' => $orders,
            'product_sales' => $productSales,
            'total_sales' => $totalSales,
            'order_count' => count($orders)
        ];
    }
}