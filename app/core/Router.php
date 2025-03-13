<?php
namespace App\Core;

/**
 * Router Class
 * 
 * Handles routing of requests to appropriate controllers
 */
class Router
{
    /**
     * @var array
     */
    private $routes = [];
    
    /**
     * Add a route
     * 
     * @param string $route
     * @param string $controller
     * @param string $method
     */
    public function add($route, $controller, $method = 'GET')
    {
        $this->routes[] = [
            'route' => $route,
            'controller' => $controller,
            'method' => $method
        ];
    }
    
    /**
     * Dispatch the request to the appropriate controller
     * 
     * @param string $uri
     * @param string $requestMethod
     */
    public function dispatch($uri, $requestMethod)
    {
        // Default route
        if ($uri === '' || $uri === '/') {
            $uri = '/';
        }
        
        foreach ($this->routes as $route) {
            // Skip if HTTP method doesn't match
            if ($route['method'] !== $requestMethod) {
                continue;
            }
            
            // Use regex to match route with parameters
            $pattern = $this->convertRouteToRegex($route['route']);
            
            if (preg_match($pattern, $uri, $matches)) {
                // Remove the full match from the matches array
                array_shift($matches);
                
                // Load the controller and call the action
                $this->loadController($route['controller'], $matches);
                return;
            }
        }
        
        // No route found, display 404 error
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 Page Not Found</h1>";
        exit;
    }
    
    /**
     * Convert a route to a regex pattern
     * 
     * @param string $route
     * @return string
     */
    private function convertRouteToRegex($route)
    {
        $pattern = str_replace('/', '\/', $route);
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Load a controller and call the specified action
     * 
     * @param string $controller
     * @param array $params
     */
    private function loadController($controller, $params = [])
    {
        // Parse controller and action from string
        list($controllerName, $actionName) = explode('@', $controller);
        
        // Add namespace to controller name
        $controllerClass = "App\\Controllers\\{$controllerName}";
        
        // Check if controller class exists
        if (!class_exists($controllerClass)) {
            header("HTTP/1.0 500 Internal Server Error");
            echo "<h1>Controller {$controllerName} not found</h1>";
            exit;
        }
        
        // Create controller instance
        $controllerInstance = new $controllerClass();
        
        // Check if action method exists
        if (!method_exists($controllerInstance, $actionName)) {
            header("HTTP/1.0 500 Internal Server Error");
            echo "<h1>Action {$actionName} not found in controller {$controllerName}</h1>";
            exit;
        }
        
        // Call the action with parameters
        call_user_func_array([$controllerInstance, $actionName], $params);
    }
}