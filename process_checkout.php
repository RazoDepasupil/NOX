<?php
require_once 'includes/init.php';
require_once 'classes/ShoppingCart.php';
require_once 'classes/Orders.php';
require_once 'classes/Payment.php';
require_once 'classes/Product.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    setFlashMessage('warning', 'Please log in to complete your purchase.');
    header('Location: login.php?redirect=' . urlencode($_SERVER['HTTP_REFERER']));
    exit;
}

// Initialize cart
$cart = new ShoppingCart();
$items = $cart->getItems();

// Check if cart is empty
if (empty($items)) {
    setFlashMessage('warning', 'Your cart is empty.');
    header('Location: cart.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['shipping_address', 'payment_method'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields.");
            }
        }

        // Get shipping address
        $shipping_address = sanitizeInput($_POST['shipping_address']);
        
        // Get payment method
        $payment_method = sanitizeInput($_POST['payment_method']);
        
        // Get payment details based on method
        $payment_details = [];
        switch ($payment_method) {
            case 'credit_card':
                if (empty($_POST['card_number']) || empty($_POST['expiry_date']) || empty($_POST['cvv'])) {
                    throw new Exception("Please provide complete credit card information.");
                }
                $payment_details = [
                    'card_number' => sanitizeInput($_POST['card_number']),
                    'expiry_date' => sanitizeInput($_POST['expiry_date']),
                    'cvv' => sanitizeInput($_POST['cvv'])
                ];
                break;
            
            case 'gcash':
                if (empty($_POST['gcash_number'])) {
                    throw new Exception("Please provide your GCash number.");
                }
                $gcash_number = sanitizeInput($_POST['gcash_number']);
                // Validate GCash number format (should be 11 digits starting with 09)
                if (!preg_match('/^09[0-9]{9}$/', str_replace(' ', '', $gcash_number))) {
                    throw new Exception("Please enter a valid GCash number (should start with 09 and be 11 digits).");
                }
                $payment_details = [
                    'method' => 'gcash',
                    'gcash_number' => $gcash_number
                ];
                break;

            case 'cod':
                $payment_details = [
                    'method' => 'cod',
                    'status' => 'pending' // Payment will be marked as pending until delivery
                ];
                break;
            
            default:
                throw new Exception("Invalid payment method selected.");
        }

        // Calculate cart totals
        $subtotal = 0;
        $orderItems = [];
        foreach ($items as $item) {
            if (!isset($item['productID'])) continue;
            $product = Product::getById($item['productID']);
            if ($product) {
                $itemTotal = $product->getUnitCost() * ($item['quantity'] ?? 1);
                $subtotal += $itemTotal;
                
                $orderItems[] = [
                    'productID' => $product->getProductID(),
                    'productName' => $product->getProductName(),
                    'quantity' => $item['quantity'],
                    'unitCost' => $product->getUnitCost(),
                    'itemTotal' => $itemTotal
                ];
            }
        }

        // Calculate shipping and tax
        $shippingCost = 100; // Fixed shipping cost for now
        $taxRate = 0.12; // 12% tax rate
        $tax = $subtotal * $taxRate;
        $total = $subtotal + $shippingCost + $tax;

        // Create new order
        $order = new Orders();
        $order->setCustomerId($_SESSION['user_id']);
        $order->setShippingAddress($shipping_address);
        $order->setSubtotal($subtotal);
        $order->setShippingCost($shippingCost);
        $order->setTax($tax);
        $order->setTotal($total);
        $order->setItems($orderItems);
        
        // Place the order
        if (!$order->placeOrder()) {
            throw new Exception("Failed to place order. Please try again.");
        }

        // Process payment
        $payment = new Payment();
        $paymentResult = $payment->processPayment([
            'order_id' => $order->getOrderID(),
            'amount' => $total,
            'payment_method' => $payment_method,
            'payment_details' => $payment_details
        ]);

        if (!$paymentResult) {
            // If payment fails, cancel the order
            $order->cancelOrder();
            throw new Exception("Payment processing failed. Please try again.");
        }

        // Clear cart after successful order
        $cart->clearCart();

        // Set success message and redirect
        $success_message = 'Your order has been placed successfully!';
        if ($payment_method === 'gcash') {
            $success_message .= ' You will receive a GCash payment request shortly.';
        } elseif ($payment_method === 'cod') {
            $success_message .= ' Please have the exact amount ready for payment upon delivery.';
        }
        setFlashMessage('success', $success_message);
        header('Location: order_confirmation.php?order_id=' . $order->getOrderID());
        exit;

    } catch (Exception $e) {
        setFlashMessage('danger', $e->getMessage());
        header('Location: checkout.php');
        exit;
    }
} else {
    // If not POST request, redirect to checkout page
    header('Location: checkout.php');
    exit;
}
?> 