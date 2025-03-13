<?php
namespace App\Core;

/**
 * View Class
 * 
 * Handles rendering of views
 */
class View
{
    /**
     * @var array
     */
    private $data = [];
    
    /**
     * @var string
     */
    private $layoutFile = 'layouts/default.php';
    
    /**
     * Set a value to be used in the view
     * 
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }
    
    /**
     * Get a value from the view data
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
    
    /**
     * Set the layout file
     * 
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->layoutFile = "layouts/{$layout}.php";
    }
    
    /**
     * Render a view
     * 
     * @param string $view
     */
    public function render($view)
    {
        // Get the view file path
        $viewFile = __DIR__ . "/../views/{$view}.php";
        
        // Check if view file exists
        if (!file_exists($viewFile)) {
            die("View {$view} not found");
        }
        
        // Extract data for use in view
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        include $viewFile;
        
        // Get the view content
        $content = ob_get_clean();
        
        // Set the content to be used in the layout
        $this->set('content', $content);
        
        // Get the layout file path
        $layoutFile = __DIR__ . "/../views/{$this->layoutFile}";
        
        // Check if layout file exists
        if (!file_exists($layoutFile)) {
            die("Layout {$this->layoutFile} not found");
        }
        
        // Include the layout file
        include $layoutFile;
    }
    
    /**
     * Render a partial view
     * 
     * @param string $partial
     * @param array $data
     * @return string
     */
    public function renderPartial($partial, $data = [])
    {
        // Get the partial file path
        $partialFile = __DIR__ . "/../views/{$partial}.php";
        
        // Check if partial file exists
        if (!file_exists($partialFile)) {
            die("Partial {$partial} not found");
        }
        
        // Extract data for use in partial
        extract(array_merge($this->data, $data));
        
        // Start output buffering
        ob_start();
        
        // Include the partial file
        include $partialFile;
        
        // Return the partial content
        return ob_get_clean();
    }
    
    /**
     * Escape HTML
     * 
     * @param string $value
     * @return string
     */
    public function e($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Format a price
     * 
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return number_format($price, 2) . ' PHP';
    }
    
    /**
     * Format a date
     * 
     * @param string $date
     * @param string $format
     * @return string
     */
    public function formatDate($date, $format = 'M d, Y')
    {
        return date($format, strtotime($date));
    }
}