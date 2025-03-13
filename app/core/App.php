<?php
namespace App\Core;

/**
 * Main Application Class
 * 
 * This class bootstraps the application and handles the request lifecycle
 */
class App
{
    /**
     * @var Router
     */
    private $router;
    
    /**
     * @var array
     */
    private $config;
    
    /**
     * @var Database
     */
    private $db;
    
    /**
     * @var Session
     */
    private $session;
    
    /**
     * Initialize the application
     */
    public function __construct()
    {
        // Load configuration
        $this->config = require_once __DIR__ . '/../../config/config.php';
        
        // Initialize database connection
        $this->db = new Database($this->config['db']);
        
        // Initialize session
        $this->session = new Session();
        
        // Initialize router
        $this->router = new Router();
        
        // Set up routes
        $this->setupRoutes();
    }
    
    /**
     * Set up application routes
     */
    private function setupRoutes()
    {
        // Home routes
        $this->router->add('/', 'HomeController@index');
        $this->router->add('/about', 'HomeController@about');
        
        // Authentication routes
        $this->router->add('/login', 'UserController@login');
        $this->router->add('/login/auth', 'UserController@authenticate', 'POST');
        $this->router->add('/register', 'UserController@register');
        $this->router->add('/register/create', 'UserController@store', 'POST');
        $this->router->add('/logout', 'UserController@logout');
        
        // Menu routes
        $this->router->add('/menu', 'MenuController@index');
        $this->router->add('/menu/category/([0-9]+)', 'MenuController@category');
        $this->router->add('/menu/product/([0-9]+)', 'MenuController@product');
        
        // Cart routes
        $this->router->add('/cart', 'CartController@index');
        $this->router->add('/cart/add', 'CartController@add', 'POST');
        $this->router->add('/cart/update', 'CartController@update', 'POST');
        $this->router->add('/cart/remove', 'CartController@remove', 'POST');
        $this->router->add('/cart/clear', 'CartController@clear', 'POST');
        
        // Checkout and delivery routes
        $this->router->add('/cart/checkout', 'CartController@checkout');
        $this->router->add('/delivery', 'DeliveryController@index');
        $this->router->add('/delivery/process', 'DeliveryController@process', 'POST');
        
        // User profile routes
        $this->router->add('/profile', 'ProfileController@index');
        $this->router->add('/profile/update', 'ProfileController@update', 'POST');
        
        // Admin routes
        $this->router->add('/admin/orders', 'OrderController@index');
        $this->router->add('/admin/orders/update', 'OrderController@update', 'POST');
        $this->router->add('/admin/reports', 'OrderController@reports');
    }
    
    /**
     * Run the application
     */
    public function run()
    {
        // Get the current URI and HTTP method
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Parse the URI
        $parsedUri = parse_url($uri, PHP_URL_PATH);
        
        // Dispatch the request to the appropriate controller
        $this->router->dispatch($parsedUri, $method);
    }
    
    /**
     * Get the database connection
     * 
     * @return Database
     */
    public function getDb()
    {
        return $this->db;
    }
    
    /**
     * Get the session instance
     * 
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }
    
    /**
     * Get the configuration
     * 
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}