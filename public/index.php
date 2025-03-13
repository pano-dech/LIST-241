<?php
/**
 * Entry point for the application
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Autoload classes
require_once BASE_PATH . '/vendor/autoload.php';

// Initialize the application
$app = new App\Core\App();

// Run the application
$app->run();