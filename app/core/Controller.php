<?php
namespace App\Core;

/**
 * Base Controller Class
 * 
 * All controllers should extend this class
 */
class Controller
{
    /**
     * @var View
     */
    protected $view;
    
    /**
     * @var Database
     */
    protected $db;
    
    /**
     * @var Session
     */
    protected $session;
    
    /**
     * @var array
     */
    protected $config;
    
    /**
     * Initialize the controller
     */
    public function __construct()
    {
        // Load configuration
        $this->config = require_once __DIR__ . '/../../config/config.php';
        
        // Initialize database connection
        $this->db = new Database($this->config['db']);
        
        // Initialize session
        $this->session = new Session();
        
        // Initialize view
        $this->view = new View();
        
        // Set default view data
        $this->view->set('site_name', $this->config['site']['name']);
        $this->view->set('current_year', date('Y'));
        $this->view->set('csrf_token', $this->session->getCsrfToken());
        
        // Check if user is logged in and pass user data to the view
        if ($this->session->has('user_id')) {
            $userId = $this->session->get('user_id');
            $user = $this->db->selectOne("SELECT id, name, email, role FROM users WHERE id = ?", [$userId]);
            
            if ($user) {
                $this->view->set('user', $user);
                $this->view->set('is_logged_in', true);
                $this->view->set('is_admin', $user['role'] === 'admin');
            } else {
                $this->session->remove('user_id');
                $this->view->set('is_logged_in', false);
                $this->view->set('is_admin', false);
            }
        } else {
            $this->view->set('is_logged_in', false);
            $this->view->set('is_admin', false);
        }
        
        // Load cart count if user is logged in
        if ($this->session->has('user_id')) {
            $userId = $this->session->get('user_id');
            $cartCount = $this->getCartCount($userId);
            $this->view->set('cart_count', $cartCount);
        } else {
            $this->view->set('cart_count', 0);
        }
    }
    
    /**
     * Get the cart count for a user
     * 
     * @param int $userId
     * @return int
     */
    protected function getCartCount($userId)
    {
        $query = "SELECT SUM(ci.quantity) as count 
                 FROM carts c 
                 JOIN cart_items ci ON c.id = ci.cart_id 
                 WHERE c.user_id = ?";
        
        $result = $this->db->selectOne($query, [$userId]);
        
        return $result['count'] ?? 0;
    }
    
    /**
     * Render a view
     * 
     * @param string $view
     * @param array $data
     */
    protected function render($view, $data = [])
    {
        // Set view data
        foreach ($data as $key => $value) {
            $this->view->set($key, $value);
        }
        
        // Render the view
        $this->view->render($view);
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $url
     */
    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Set a flash message
     * 
     * @param string $type
     * @param string $message
     */
    protected function setFlash($type, $message)
    {
        $this->session->setFlash($type, $message);
    }
    
    /**
     * Check if the current request is POST
     * 
     * @return bool
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Get a POST value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Get a GET value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Validate CSRF token
     * 
     * @return bool
     */
    protected function validateCsrf()
    {
        $token = $this->post('csrf_token');
        
        if (!$token || !$this->session->validateCsrfToken($token)) {
            $this->setFlash('error', 'Invalid CSRF token');
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if user is logged in
     * 
     * @param bool $redirect
     * @return bool
     */
    protected function isLoggedIn($redirect = true)
    {
        if (!$this->session->has('user_id')) {
            if ($redirect) {
                $this->setFlash('error', 'Please log in to access this page');
                $this->redirect('/login');
            }
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if user is admin
     * 
     * @param bool $redirect
     * @return bool
     */
    protected function isAdmin($redirect = true)
    {
        if (!$this->isLoggedIn($redirect)) {
            return false;
        }
        
        $userId = $this->session->get('user_id');
        $user = $this->db->selectOne("SELECT role FROM users WHERE id = ?", [$userId]);
        
        if (!$user || $user['role'] !== 'admin') {
            if ($redirect) {
                $this->setFlash('error', 'You do not have permission to access this page');
                $this->redirect('/');
            }
            
            return false;
        }
        
        return true;
    }
}