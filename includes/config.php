<?php
// Configuration settings for Nox clothing brand

// Site information
if (!defined('SITE_NAME')) define('SITE_NAME', 'NOX');
if (!defined('SITE_URL')) define('SITE_URL', '/NOX-main');
if (!defined('BRAND_DESCRIPTION')) define('BRAND_DESCRIPTION', 'Premium clothing brand offering diverse products including footwear, accessories, men\'s wear and women\'s wear');

// Product categories
if (!defined('CATEGORIES')) {
    define('CATEGORIES', [
        'mens' => 'Men\'s Wear',
        'womens' => 'Women\'s Wear',
        'footwear' => 'Footwear',
        'accessories' => 'Accessories'
    ]);
}

// JSON data file paths
if (!defined('DATA_DIR')) define('DATA_DIR', __DIR__ . '/../data/');
if (!defined('USERS_FILE')) define('USERS_FILE', DATA_DIR . 'users.json');
if (!defined('PRODUCTS_FILE')) define('PRODUCTS_FILE', DATA_DIR . 'products.json');
if (!defined('ORDERS_FILE')) define('ORDERS_FILE', DATA_DIR . 'orders.json');
if (!defined('CART_FILE')) define('CART_FILE', DATA_DIR . 'cart.json');
if (!defined('INVENTORY_FILE')) define('INVENTORY_FILE', DATA_DIR . 'inventory.json');
if (!defined('PAYMENTS_FILE')) define('PAYMENTS_FILE', DATA_DIR . 'payments.json');
if (!defined('RESELLERS_FILE')) define('RESELLERS_FILE', DATA_DIR . 'resellers.json');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Helper functions for JSON data handling
function readJsonFile($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }
    $jsonData = file_get_contents($filePath);
    return json_decode($jsonData, true) ?: [];
}

function writeJsonFile($filePath, $data) {
    if (!is_dir(dirname($filePath))) {
        mkdir(dirname($filePath), 0755, true);
    }
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($filePath, $jsonData);
}

// Currency settings
if (!defined('CURRENCY')) define('CURRENCY', 'USD');
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', '$');

// Shipping settings
if (!defined('SHIPPING_METHODS')) {
    define('SHIPPING_METHODS', [
        'standard' => [
            'name' => 'Standard Shipping',
            'cost' => 5.99,
            'days' => '3-5 business days'
        ],
        'express' => [
            'name' => 'Express Shipping',
            'cost' => 14.99,
            'days' => '1-2 business days'
        ],
        'next_day' => [
            'name' => 'Next Day Delivery',
            'cost' => 24.99,
            'days' => 'Next business day'
        ]
    ]);
}

// Tax settings
if (!defined('DEFAULT_TAX_RATE')) define('DEFAULT_TAX_RATE', 0.08); // 8% tax rate
?>