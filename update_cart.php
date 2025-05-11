<?php
require_once 'includes/init.php';
require_once 'classes/ShoppingCart.php';
require_once 'classes/Product.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = "Please log in to update your cart.";
    header('Location: login.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartItemId = $_POST['cart_item_id'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 0);

    if (empty($cartItemId) || $quantity < 1) {
        $_SESSION['error'] = "Invalid update request.";
        header('Location: cart.php');
        exit;
    }

    $cart = new ShoppingCart();
    
    // Get the cart item to check product stock
    $items = $cart->getItems();
    $item = null;
    foreach ($items as $cartItem) {
        if ($cartItem['cartItemID'] === $cartItemId) {
            $item = $cartItem;
            break;
        }
    }

    if (!$item) {
        $_SESSION['error'] = "Cart item not found.";
        header('Location: cart.php');
        exit;
    }

    // Check product stock
    $product = Product::getById($item['productID']);
    if (!$product) {
        $_SESSION['error'] = "Product not found.";
        header('Location: cart.php');
        exit;
    }

    if ($quantity > $product->getStockQuantity()) {
        $_SESSION['error'] = "Not enough stock available. Only " . $product->getStockQuantity() . " items left.";
        header('Location: cart.php');
        exit;
    }

    // Update the quantity
    if ($cart->updateQuantity($cartItemId, $quantity)) {
        $_SESSION['success'] = "Cart updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update cart.";
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
}

header('Location: cart.php');
exit; 