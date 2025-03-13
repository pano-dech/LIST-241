<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;

/**
 * Order Controller
 * 
 * Handles admin-side order management
 */
class OrderController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        // Set admin layout
        $this->view->setLayout('admin');
    }
    
    /**
     * Display order management page
     */
    public function index()
    {
        // Check if user is admin
        if (!$this->isAdmin()) {
            return;
        }
        
        // Get filter parameters
        $status = $this->get('status');
        $startDate = $this->get('start_date');
        $endDate = $this->get('end_date');
        $search = $this->get('search');
        $page = (int) $this->get('page', 1);
        
        // Build filter conditions
        $where = '';
        $params = [];
        
        if (!empty($status)) {
            $where .= "status = ?";
            $params[] = $status;
        }
        
        if (!empty($startDate)) {
            if (!empty($where)) {
                $where .= " AND ";
            }
            $where .= "DATE(created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            if (!empty($where)) {
                $where .= " AND ";
            }
            $where .= "DATE(created_at) <= ?";
            $params[] = $endDate;
        }
        
        if (!empty($search)) {
            // Get orders by search term
            $searchTerm = "%{$search}%";
            
            if (!empty($where)) {
                $where .= " AND ";
            }
            
            $where .= "(id LIKE ? OR delivery_address LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            
            // Also search by user
            $userIds = $this->db->select(
                "SELECT id FROM users WHERE name LIKE ? OR email LIKE ?",
                [$searchTerm, $searchTerm]
            );
            
            if (!empty($userIds)) {
                $userIdsList = array_column($userIds, 'id');
                $userIdsStr = implode(',', $userIdsList);
                
                $where .= " OR user_id IN ({$userIdsStr})";
            }
        }
        
        // Get orders
        $orderModel = new Order();
        $perPage = $this->config['pagination']['items_per_page'];
        $pagination = $orderModel->paginate($page, $perPage, 'id', 'DESC', $where, $params);
        
        // Get order details (user info, items)
        foreach ($pagination['items'] as &$order) {
            // Get user
            $user = $this->db->selectOne(
                "SELECT name, email, phone_number FROM users WHERE id = ?",
                [$order['user_id']]
            );
            
            $order['user'] = $user;
            
            // Get items count
            $itemCount = $this->db->selectOne(
                "SELECT COUNT(*) as count FROM order_items WHERE order_id = ?",
                [$order['id']]
            );
            
            $order['item_count'] = $itemCount['count'] ?? 0;
        }
        
        $this->render('orders/manage', [
            'title' => 'Order Management - Tummy Pillow Bakery',
            'orders' => $pagination['items'],
            'pagination' => $pagination,
            'filters' => [
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'search' => $search
            ]
        ]);
    }
    
    /**
     * View order details
     * 
     * @param int $orderId
     */
    public function view($orderId)
    {
        // Check if user is admin
        if (!$this->isAdmin()) {
            return;
        }
        
        // Get order details
        $orderModel = new Order();
        $result = $orderModel->getOrderDetails($orderId);
        
        if (!$result['success']) {
            $this->setFlash('error', $result['message']);
            $this->redirect('/admin/orders');
            return;
        }
        
        $this->render('orders/view', [
            'title' => 'Order #' . $orderId . ' - Tummy Pillow Bakery',
            'order' => $result['order'],
            'items' => $result['items'],
            'user' => $result['user']
        ]);
    }
    
    /**
     * Update order status
     */
    public function update()
    {
        // Check if request is POST
        if (!$this->isPost()) {
            $this->redirect('/admin/orders');
            return;
        }
        
        // Check if user is admin
        if (!$this->isAdmin()) {
            return;
        }
        
        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->redirect('/admin/orders');
            return;
        }
        
        // Get form data
        $orderId = (int) $this->post('order_id');
        $status = $this->post('status');
        
        // Validate form data
        if ($orderId <= 0 || empty($status)) {
            $this->setFlash('error', 'Invalid order or status');
            $this->redirect('/admin/orders');
            return;
        }
        
        // Update order status
        $orderModel = new Order();
        $result = $orderModel->updateStatus($orderId, $status);
        
        if (!$result['success']) {
            $this->setFlash('error', $result['message']);
            $this->redirect('/admin/orders/view/' . $orderId);
            return;
        }
        
        // Set success message
        $this->setFlash('success', 'Order status updated successfully');
        $this->redirect('/admin/orders/view/' . $orderId);
    }
    
    /**
     * Bulk update orders
     */
    public function bulkUpdate()
    {
        // Check if request is POST
        if (!$this->isPost()) {
            $this->redirect('/admin/orders');
            return;
        }
        
        // Check if user is admin
        if (!$this->isAdmin()) {
            return;
        }
        
        // Validate CSRF token
        if (!$this->validateCsrf()) {
            $this->redirect('/admin/orders');
            return;
        }
        
        // Get form data
        $orderIds = $this->post('order_ids', []);
        $status = $this->post('status');
        
        // Validate form data
        if (empty($orderIds) || empty($status)) {
            $this->setFlash('error', 'No orders selected or invalid status');
            $this->redirect('/admin/orders');
            return;
        }
        
        // Update order status for each order
        $orderModel = new Order();
        $success = 0;
        $failed = 0;
        
        foreach ($orderIds as $orderId) {
            $result = $orderModel->updateStatus($orderId, $status);
            
            if ($result['success']) {
                $success++;
            } else {
                $failed++;
            }
        }
        
        // Set message
        if ($failed > 0) {
            $this->setFlash('warning', "Updated {$success} orders, failed to update {$failed} orders");
        } else {
            $this->setFlash('success', "Updated {$success} orders successfully");
        }
        
        $this->redirect('/admin/orders');
    }
    
    /**
     * Display sales report
     */
    public function reports()
    {
        // Check if user is admin
        if (!$this->isAdmin()) {
            return;
        }
        
        // Get filter parameters
        $startDate = $this->get('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $this->get('end_date', date('Y-m-d'));
        
        // Get sales report
        $orderModel = new Order();
        $report = $orderModel->getSalesReport($startDate, $endDate);
        
        $this->render('orders/reports', [
            'title' => 'Sales Report - Tummy Pillow Bakery',
            'report' => $report,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }
}