<?php
/**
 * Main configuration file
 */

// Load environment variables if .env file exists
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Site configuration
return [
    'site' => [
        'name' => $_ENV['SITE_NAME'] ?? 'Tummy Pillow Bakery',
        'url' => $_ENV['SITE_URL'] ?? 'http://localhost:8000',
        'email' => $_ENV['SITE_EMAIL'] ?? 'info@tummypillow.com',
    ],
    
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'tummy_pillow_db',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
    ],
    
    'security' => [
        'csrf_token_name' => 'csrf_token',
        'csrf_time_limit' => 3600, // 1 hour
        'password_min_length' => 8,
    ],
    
    'orders' => [
        'minimum_delivery_days' => 4,
        'metro_manila_cities' => [
            'Caloocan', 'Las Pinas', 'Makati', 'Malabon', 'Mandaluyong', 'Manila', 
            'Marikina', 'Muntinlupa', 'Navotas', 'Paranaque', 'Pasay', 'Pasig', 
            'Pateros', 'Quezon City', 'San Juan', 'Taguig', 'Valenzuela'
        ],
        'delivery_charge' => [
            'metro_manila' => 0,
            'other_areas' => 100
        ]
    ],
    
    'pagination' => [
        'items_per_page' => 10
    ]
];