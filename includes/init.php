<?php
// Initialize the application

// Include configuration
require_once __DIR__ . '/config.php';

// Load helper functions
require_once __DIR__ . '/functions.php';

// Autoload classes
spl_autoload_register(function ($className) {
    $classFile = __DIR__ . '/../classes/' . $className . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// Initialize data directory and files if they don't exist
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// Initialize JSON data files with default data if they don't exist
$dataFiles = [
    USERS_FILE => ['customers' => [], 'employees' => [], 'resellers' => []],
    PRODUCTS_FILE => [],
    ORDERS_FILE => [],
    CART_FILE => [],
    INVENTORY_FILE => []
];

foreach ($dataFiles as $file => $defaultData) {
    if (!file_exists($file)) {
        writeJsonFile($file, $defaultData);
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $users = readJsonFile(USERS_FILE);
    $userId = $_SESSION['user_id'];
    
    foreach ($users as $type => $userList) {
        foreach ($userList as $user) {
            if ($user['userID'] == $userId) {
                return $user;
            }
        }
    }
    
    return null;
}
?>