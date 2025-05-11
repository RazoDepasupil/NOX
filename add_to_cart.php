<?php
require_once 'includes/init.php';
require_once 'classes/ShoppingCart.php';
require_once 'classes/Product.php';

// Debug log to track execution
error_log('add_to_cart.php started');

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log('User not logged in, redirecting to login');
    setFlashMessage('warning', 'Please log in to add items to your cart.');
    header('Location: login.php?redirect=' . urlencode($_SERVER['HTTP_REFERER']));
    exit;
}

// Check if the cart file exists, if not create it with empty array
if (!file_exists(CART_FILE)) {
    error_log('Creating new cart file');
    file_put_contents(CART_FILE, json_encode(['carts' => [], 'shipping' => []]), LOCK_EX);
    chmod(CART_FILE, 0666); // Set proper permissions
}

// Check if the file is writable and log the result
if (is_writable(CART_FILE)) {
    error_log('CART FILE IS WRITABLE');
} else {
    error_log('CART FILE IS NOT WRITABLE');
    // Try to fix permissions if possible
    chmod(CART_FILE, 0666);
    
    // Check again after trying to fix
    if (!is_writable(CART_FILE)) {
        setFlashMessage('danger', 'System error: Cart storage is not writable.');
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? $_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $size = isset($_POST['size']) ? $_POST['size'] : null;
    
    error_log('Add to cart: productId=' . $productId . ', quantity=' . $quantity . ', size=' . $size);
    
    if ($productId <= 0) {
        error_log('Invalid product ID: ' . $productId);
        setFlashMessage('danger', 'Invalid product selected.');
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    if ($quantity <= 0) {
        error_log('Invalid quantity: ' . $quantity);
        setFlashMessage('danger', 'Please select a valid quantity.');
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    try {
        $product = Product::getById($productId);
        if (!$product) {
            error_log('Product not found: ' . $productId);
            setFlashMessage('danger', 'Selected product not found.');
            header('Location: products.php');
            exit;
        }
        
        // Check stock availability
        if ($product->getStockQuantity() < $quantity) {
            error_log('Insufficient stock: requested=' . $quantity . ', available=' . $product->getStockQuantity());
            setFlashMessage('warning', 'Sorry, we only have ' . $product->getStockQuantity() . ' units in stock.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        
        $cart = new ShoppingCart();
        
        if ($cart->addCartItem($productId, $quantity, $size)) {
            // Redirect back to product page with success message
            setFlashMessage('success', 'Product added to cart successfully!');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        } else {
            error_log('Add to cart failed for productId=' . $productId . ', quantity=' . $quantity . ', size=' . $size);
            // Redirect back with error message
            setFlashMessage('danger', 'Failed to add product to cart. Please try again.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    } catch (Exception $e) {
        error_log('Cart exception: ' . $e->getMessage());
        setFlashMessage('danger', 'An error occurred: ' . $e->getMessage());
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

// If we get here, something went wrong
setFlashMessage('danger', 'Invalid request.');
header('Location: products.php');
exit;