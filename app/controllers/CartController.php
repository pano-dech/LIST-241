<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;

/**
 * Cart Controller
 */
class CartController extends Controller
{
    /**
     * Display cart
     */
    public function index()
    {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return;
        }
        
        $userId = $this->session->get('user_id');
        $cartModel = new Cart();
        
        // Get cart items
        $cartItems = $cartModel->getCartItems($userId);
        $cartTotal = $cartModel->getCartTotal($userId);
        
        $this->render('cart/index', [
            'title' => 'Your Cart - Tummy Pillow Bakery',
            'cartItems' => $cartItems,
            'cartTotal' => $cartTotal
        ]);
    }
    
    /**
     * Add item to cart
     */
    public function add()
    {
        // Check if request is POST
        if (!$this->isPost()) {
            $this->redirect('/menu');
            return;
        }
        
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return;
        }
        
        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->redirect('/menu');
            return;
        }
        
        // Get form data
        $productId = (int) $this->post('product_id');
        $quantity = (int) $this->post('quantity', 1);
        $returnUrl = $this->post('return_url', '/cart');
        
        // Validate form data
        if ($productId <= 0 || $quantity <= 0) {
            $this->setFlash('error', 'Invalid product or quantity');
            $this->redirect($returnUrl);
            return;
        }
        
        // Add to cart
        $userId = $this->session->get('user_id');
        $cartModel = new Cart();
        $result = $cartModel->addItem($userId, $productId, $quantity);
        
        if (!$result['success']) {
            $this->setFlash('error', $result['message']);
        } else {
            $this->setFlash('success', $result['message']);
        }
        
        // Redirect back
        $this->redirect($returnUrl);
    }
    
    /**
     * Update cart item quantity
     */
    public function update()
    {
        // Check if request is POST and AJAX
        if (!$this->isPost() || !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        
        // Check if user is logged in
        if (!$this->session->has('user_id')) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'Please log in']);
            exit;
        }
        
        // Get data
        $itemId = (int) $this->post('item_id');
        $quantity = (int) $this->post('quantity');
        
        // Validate data
        if ($itemId <= 0 || $quantity < 0) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['success' => false, 'message' => 'Invalid item or quantity']);
            exit;
        }
        
        // Update quantity
        $userId = $this->session->get('user_id');
        $cartModel = new Cart();
        $result = $cartModel->updateItemQuantity($userId, $itemId, $quantity);
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    
    /**
     * Remove item from cart
     */
    public function remove()
    {
        // Check if request is POST
        if (!$this->isPost()) {
            $this->redirect('/cart');
            return;
        }
        
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return;
        }
        
        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->redirect('/cart');
            return;
        }
        
        // Get item ID
        $itemId = (int) $this->post('item_id');
        
        // Validate item ID
        if ($itemId <= 0) {
            $this->setFlash('error', 'Invalid item');
            $this->redirect('/cart');
            return;
        }
        
        // Remove from cart
        $userId = $this->session->get('user_id');
        $cartModel = new Cart();
        $result = $cartModel->updateItemQuantity($userId, $itemId, 0);
        
        if (!$result['success']) {
            $this->setFlash('error', $result['message']);
        } else {
            $this->setFlash('success', 'Item removed from cart');
        }
        
        // Redirect back to cart
        $this->redirect('/cart');
    }
    
    /**
     * Clear cart
     */
    public function clear()
    {
        // Check if request is POST
        if (!$this->isPost()) {
            $this->redirect('/cart');
            return;
        }
        
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return;
        }
        
        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->redirect('/cart');
            return;
        }
        
        // Clear cart
        $userId = $this->session->get('user_id');
        $cartModel = new Cart();
        $result = $cartModel->clearCart($userId);
        
        if (!$result['success']) {
            $this->setFlash('error', $result['message']);
        } else {
            $this->setFlash('success', 'Cart cleared successfully');
        }
        
        // Redirect back to cart
        $this->redirect('/cart');
    }
    
    /**
     * Checkout page
     */
    public function checkout()
    {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return;
        }
        
        $userId = $this->session->get('user_id');
        $cartModel = new Cart();
        
        // Get cart items
        $cartItems = $cartModel->getCartItems($userId);
        $cartTotal = $cartModel->getCartTotal($userId);
        
        // Check if cart is empty
        if (empty($cartItems)) {
            $this->setFlash('error', 'Your cart is empty');
            $this->redirect('/cart');
            return;
        }
        
        // Get user info
        $user = $this->db->selectOne(
            "SELECT name, email, phone_number, address FROM users WHERE id = ?",
            [$userId]
        );
        
        // Get minimum delivery date
        $minDeliveryDate = date('Y-m-d', strtotime('+' . $this->config['orders']['minimum_delivery_days'] . ' days'));
        
        $this->render('cart/checkout', [
            'title' => 'Checkout - Tummy Pillow Bakery',
            'cartItems' => $cartItems,
            'cartTotal' => $cartTotal,
            'user' => $user,
            'minDeliveryDate' => $minDeliveryDate,
            'metroCities' => $this->config['orders']['metro_manila_cities'],
            'deliveryCharges' => $this->config['orders']['delivery_charge']
        ]);
    }
}