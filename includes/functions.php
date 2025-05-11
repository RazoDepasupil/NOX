<?php
// Helper functions for the e-commerce application

// Generate a unique ID
function generateUniqueId() {
    return uniqid() . bin2hex(random_bytes(8));
}

// Format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email address
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Redirect to another page
function redirect($url) {
    header("Location: $url");
    exit;
}

// Display flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Display flash message and clear it
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_message']['type'];
        $message = $_SESSION['flash_message']['message'];
        
        echo "<div class='alert alert-$type' role='alert'>$message</div>";
        
        unset($_SESSION['flash_message']);
    }
}

// Check if user has admin privileges
function isAdmin() {
    $user = getCurrentUser();
    return $user && isset($user['role']) && $user['role'] === 'admin';
}

// Check if a product is in stock
function isInStock($productId) {
    $products = readJsonFile(PRODUCTS_FILE);
    
    foreach ($products as $product) {
        if ($product['productID'] === $productId) {
            return $product['stockQuantity'] > 0;
        }
    }
    
    return false;
}

// Get cart count for current user
function getCartCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    $userId = $_SESSION['user_id'];
    $carts = readJsonFile(CART_FILE);
    
    $count = 0;
    if (isset($carts[$userId])) {
        foreach ($carts[$userId] as $item) {
            $count += $item['quantity'];
        }
    }
    
    return $count;
}

// Calculate subtotal for cart
function calculateCartSubtotal() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    $userId = $_SESSION['user_id'];
    $carts = readJsonFile(CART_FILE);
    $products = readJsonFile(PRODUCTS_FILE);
    
    $subtotal = 0;
    if (isset($carts[$userId])) {
        foreach ($carts[$userId] as $item) {
            foreach ($products as $product) {
                if ($product['productID'] === $item['productID']) {
                    $subtotal += $product['unitCost'] * $item['quantity'];
                    break;
                }
            }
        }
    }
    
    return $subtotal;
}
?>