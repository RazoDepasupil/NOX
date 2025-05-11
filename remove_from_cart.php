<?php
require_once 'includes/init.php';
require_once 'classes/ShoppingCart.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = "Please log in to modify your cart.";
    header('Location: login.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartItemId = $_POST['cart_item_id'] ?? '';

    if (empty($cartItemId)) {
        $_SESSION['error'] = "Invalid remove request.";
        header('Location: cart.php');
        exit;
    }

    $cart = new ShoppingCart();
    
    // Remove the item
    if ($cart->removeCartItem($cartItemId)) {
        $_SESSION['success'] = "Item removed from cart successfully.";
    } else {
        $_SESSION['error'] = "Failed to remove item from cart.";
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
}

header('Location: cart.php');
exit; 