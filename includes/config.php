<?php
// Configuration settings for the e-commerce application

// Site information
define('SITE_NAME', 'ShopEasy');
define('SITE_URL', 'http://localhost/shop-easy');

// JSON data file paths
define('DATA_DIR', __DIR__ . '/../data/');
define('USERS_FILE', DATA_DIR . 'users.json');
define('PRODUCTS_FILE', DATA_DIR . 'products.json');
define('ORDERS_FILE', DATA_DIR . 'orders.json');
define('CART_FILE', DATA_DIR . 'cart.json');
define('INVENTORY_FILE', DATA_DIR . 'inventory.json');

// Session configuration
session_start();

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
?>