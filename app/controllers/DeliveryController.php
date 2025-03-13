<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;

/**
 * Delivery Controller
 */
class DeliveryController extends Controller
{
    /**
     * Display delivery form
     */
    public function index()
    {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return;
        }
        
        $userId = $this->session->get('user_id');
        
        // Get user data
        $user = $this->db->selectOne(
            "SELECT name, email, phone_number, address FROM users WHERE id = ?",
            [$userId]
        );
        
        // Get minimum delivery date
        $minDeliveryDate = date('Y-m-d', strtotime('+' . $this->config['orders']['minimum_delivery_days'] . ' days'));
        
        $this->render('delivery/index', [
            'title' => 'Delivery - Tummy Pillow Bakery',
            'user' => $user,
            'minDeliveryDate' => $minDeliveryDate,
            'metroCities' => $this->config['orders']['metro_manila_cities'],
            'deliveryCharges' => $this->config['orders']['delivery_charge']
        ]);
    }
    
    /**
     * Process delivery order
     */
    public function process()
    {
        // Check if request is POST
        if (!$this->isPost()) {
            $this->redirect('/delivery');
            return;
        }
        
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            return;
        }
        
        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->redirect('/delivery');
            return;
        }
        
        // Get form data
        $deliveryAddress = $this->post('delivery_address');
        $deliveryDate = $this->post('delivery_date');
        $notes = $this->post('notes', '');
        
        // Validate form data
        if (empty($deliveryAddress) || empty($deliveryDate)) {
            $this->setFlash('error', 'Please enter delivery address and date');
            $this->redirect('/delivery');
            return;
        }
        
        // Validate delivery date
        $minDeliveryDate = date('Y-m-d', strtotime('+' . $this->config['orders']['minimum_delivery_days'] . ' days'));
        
        if (strtotime($deliveryDate) < strtotime($minDeliveryDate)) {
            $this->setFlash('error', 'Delivery date must be at least ' . $this->config['orders']['minimum_delivery_days'] . ' days from today');
            $this->redirect('/delivery');
            return;
        }
        
        // Calculate delivery charge
        $orderModel = new Order();
        $deliveryCharge = $orderModel->calculateDeliveryCharge(
            $deliveryAddress,
            $this->config['orders']['metro_manila_cities'],
            $this->config['orders']['delivery_charge']
        );
        
        // Prepare order data
        $orderData = [
            'delivery_address' => $deliveryAddress,
            'delivery_date' => $deliveryDate,
            'delivery_charge' => $deliveryCharge,
            'notes' => $notes
        ];
        
        // Create order
        $userId = $this->session->get('user_id');
        $result = $orderModel->createFromCart($userId, $orderData);
        
        if (!$result['success']) {
            $this->setFlash('error', $result['message']);
            $this->redirect('/delivery');
            return;
        }
        
        // Set success message
        $this->setFlash('success', 'Your order has been placed successfully!');
        
        // Redirect to order confirmation
        $this->redirect('/profile');
    }
    
    /**
     * Calculate delivery charge via AJAX
     */
    public function calculateCharge()
    {
        // Check if request is POST and AJAX
        if (!$this->isPost() || !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
            $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        
        // Get address
        $address = $this->post('address');
        
        if (empty($address)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['success' => false, 'message' => 'Please enter delivery address']);
            exit;
        }
        
        // Calculate delivery charge
        $orderModel = new Order();
        $deliveryCharge = $orderModel->calculateDeliveryCharge(
            $address,
            $this->config['orders']['metro_manila_cities'],
            $this->config['orders']['delivery_charge']
        );
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'delivery_charge' => $deliveryCharge,
            'formatted_charge' => number_format($deliveryCharge, 2) . ' PHP'
        ]);
        exit;
    }
}