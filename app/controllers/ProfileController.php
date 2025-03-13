<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Order;

/**
 * Profile Controller
 */
class ProfileController extends Controller
{
    /**
     * Display user profile
     */
    public function index()
    {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return;
        }
        
        $userId = $this->session->get('user_id');
        
        // Get user data
        $userModel = new User();
        $user = $userModel->find($userId);
        
        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/logout');
            return;
        }
        
        // Remove sensitive data
        unset($user['password_hash']);
        
        // Get user orders
        $orderModel = new Order();
        $orders = $orderModel->getUserOrders($userId);
        
        // Group orders by status
        $ordersByStatus = [
            'pending' => [],
            'confirmed' => [],
            'on_delivery' => [],
            'delivered' => [],
            'cancelled' => []
        ];
        
        foreach ($orders as $order) {
            $ordersByStatus[$order['status']][] = $order;
        }
        
        $this->render('profile/index', [
            'title' => 'Your Profile - Tummy Pillow Bakery',
            'user' => $user,
            'orders' => $orders,
            'ordersByStatus' => $ordersByStatus
        ]);
    }
    
    /**
     * Update user profile
     */
    public function update()
    {
        // Check if request is POST
        if (!$this->isPost()) {
            $this->redirect('/profile');
            return;
        }
        
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return;
        }
        
        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->redirect('/profile');
            return;
        }
        
        // Get form data
        $name = $this->post('name');
        $phone_number = $this->post('phone_number');
        $address = $this->post('address');
        $password = $this->post('password');
        $confirm_password = $this->post('confirm_password');
        
        // Validate form data
        if (empty($name) || empty($phone_number) || empty($address)) {
            $this->setFlash('error', 'Please fill in all required fields');
            $this->redirect('/profile');
            return;
        }
        
        // Check if password fields match
        if (!empty($password) && $password !== $confirm_password) {
            $this->setFlash('error', 'Passwords do not match');
            $this->redirect('/profile');
            return;
        }
        
        // Prepare user data
        $userId = $this->session->get('user_id');
        $userData = [
            'name' => $name,
            'phone_number' => $phone_number,
            'address' => $address
        ];
        
        // Add password if provided
        if (!empty($password)) {
            $userData['password'] = $password;
        }
        
        // Update user
        $userModel = new User();
        $result = $userModel->updateProfile($userId, $userData);
        
        if (!$result['success']) {
            $this->setFlash('error', $result['message']);
            $this->redirect('/profile');
            return;
        }
        
        // Set success message
        $this->setFlash('success', 'Profile updated successfully');
        $this->redirect('/profile');
    }
    
    /**
     * View order details
     * 
     * @param int $orderId
     */
    public function viewOrder($orderId)
    {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return;
        }
        
        $userId = $this->session->get('user_id');
        
        // Get order details
        $orderModel = new Order();
        $result = $orderModel->getOrderDetails($orderId);
        
        if (!$result['success']) {
            $this->setFlash('error', $result['message']);
            $this->redirect('/profile');
            return;
        }
        
        // Check if order belongs to user
        if ($result['order']['user_id'] != $userId) {
            $this->setFlash('error', 'You do not have permission to view this order');
            $this->redirect('/profile');
            return;
        }
        
        $this->render('profile/order', [
            'title' => 'Order #' . $orderId . ' - Tummy Pillow Bakery',
            'order' => $result['order'],
            'items' => $result['items']
        ]);
    }
    
    /**
     * Cancel order
     */
    public function cancelOrder()
    {
        // Check if request is POST
        if (!$this->isPost()) {
            $this->redirect('/profile');
            return;
        }
        
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return;
        }
        
        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->redirect('/profile');
            return;
        }
        
        // Get order ID
        $orderId = (int) $this->post('order_id');
        
        // Get order details
        $orderModel = new Order();
        $result = $orderModel->getOrderDetails($orderId);
        
        if (!$result['success']) {
            $this->setFlash('error', $result['message']);
            $this->redirect('/profile');
            return;
        }
        
        // Check if order belongs to user
        $userId = $this->session->get('user_id');
        
        if ($result['order']['user_id'] != $userId) {
            $this->setFlash('error', 'You do not have permission to cancel this order');
            $this->redirect('/profile');
            return;
        }
        
        // Check if order can be cancelled
        if ($result['order']['status'] !== 'pending' && $result['order']['status'] !== 'confirmed') {
            $this->setFlash('error', 'This order cannot be cancelled');
            $this->redirect('/profile');
            return;
        }
        
        // Cancel order
        $updateResult = $orderModel->updateStatus($orderId, 'cancelled');
        
        if (!$updateResult['success']) {
            $this->setFlash('error', $updateResult['message']);
            $this->redirect('/profile');
            return;
        }
        
        // Set success message
        $this->setFlash('success', 'Order cancelled successfully');
        $this->redirect('/profile');
    }
}