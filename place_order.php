<?php
require_once 'includes/init.php';
// TEMPORARY DEBUG: Force user ID for testing
$_SESSION['user_id'] = '682020630116de4975d16f1a1a42f';
error_log('PLACE_ORDER.PHP USER ID: ' . ($_SESSION['user_id'] ?? 'NOT SET'));
require_once 'classes/ShoppingCart.php';
require_once 'classes/Orders.php';
require_once 'classes/Payment.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    setFlashMessage('warning', 'Please log in to complete your purchase.');
    header('Location: login.php?redirect=' . urlencode($_SERVER['HTTP_REFERER']));
    exit;
}

// Initialize cart
$cart = new ShoppingCart();
$items = $cart->getItems();
error_log('PLACE_ORDER.PHP ITEMS: ' . print_r($items, true));

// Check if cart is empty
if (empty($items)) {
    setFlashMessage('warning', 'Your cart is empty.');
    header('Location: cart.php');
    exit;
}

// Get cart details
$cart_details = $cart->viewCartDetails();

// Calculate totals
$subtotal = 0;
foreach ($cart_details['items'] as $item) {
    $subtotal += $item['itemTotal'];
}

// Validate subtotal is greater than zero
if ($subtotal <= 0) {
    setFlashMessage('danger', 'Invalid order total. Please add items to your cart.');
    header('Location: cart.php');
    exit;
}

// Set shipping cost (you can adjust this based on your needs)
$shipping_cost = 10.00;

// Calculate tax (assuming 10% tax rate)
$tax_rate = 0.10;
$tax = $subtotal * $tax_rate;

// Calculate total
$total = $subtotal + $shipping_cost + $tax;

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
        if (empty(trim($shipping_address))) {
            throw new Exception("Please enter a valid shipping address.");
        }
        
        // Get payment method
        $payment_method = sanitizeInput($_POST['payment_method']);
        
        // Get payment details based on method
        $payment_details = [];
        switch ($payment_method) {
            case 'credit_card':
                if (empty($_POST['card_number']) || empty($_POST['expiry_date']) || empty($_POST['cvv'])) {
                    throw new Exception("Please provide complete credit card information.");
                }
                
                // Basic card number validation
                $card_number = preg_replace('/\D/', '', $_POST['card_number']);
                if (strlen($card_number) < 13 || strlen($card_number) > 19) {
                    throw new Exception("Please enter a valid card number.");
                }
                
                $payment_details = [
                    'card_number' => sanitizeInput($_POST['card_number']),
                    'expiry_date' => sanitizeInput($_POST['expiry_date']),
                    'cvv' => sanitizeInput($_POST['cvv'])
                ];
                break;
            
            case 'paypal':
                // PayPal integration would go here
                $payment_details = ['method' => 'paypal'];
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

        // Create new order
        $order = new Orders();
        $order->setCustomerId($_SESSION['user_id']);
        
        // Set order details
        $order->setSubtotal($subtotal);
        $order->setShippingCost($shipping_cost);
        $order->setTax($tax);
        $order->setTotal($total);
        $order->setItems($cart_details['items']);
        
        // Add shipping address to order
        $order->setShippingAddress($shipping_address);
        
        // Place the order first
        $order_result = $order->placeOrder();
        if (!$order_result) {
            // Log error for debugging
            error_log("Order placement failed: " . print_r($order, true));
            throw new Exception("Failed to place order. Please try again.");
        }

        // Process payment
        $payment_result = $order->processPayment($payment_method, $payment_details);
        if (!$payment_result) {
            // If payment fails, cancel the order and log the error
            error_log("Payment processing failed: " . print_r($payment_details, true));
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
        header('Location: place_order.php');
        exit;
    }
}

include 'includes/templates/header.php';
?>

<div class="container mt-5 mb-5">
    <h2 class="mb-4">Place Your Order</h2>
    
    <?php displayFlashMessage(); ?>
    
    <div class="row">
        <!-- Order Form -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="place_order.php" method="POST" id="order-form">
                        <!-- Shipping Information -->
                        <h4 class="mb-3">Shipping Information</h4>
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Shipping Address</label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required></textarea>
                        </div>

                        <!-- Payment Information -->
                        <h4 class="mb-3 mt-4">Payment Information</h4>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                                <label class="form-check-label" for="credit_card">
                                    Credit Card
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                <label class="form-check-label" for="paypal">
                                    PayPal
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="gcash" value="gcash">
                                <label class="form-check-label" for="gcash">
                                    GCash
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod">
                                <label class="form-check-label" for="cod">
                                    Cash on Delivery
                                </label>
                            </div>
                        </div>

                        <!-- Credit Card Details -->
                        <div id="credit-card-details">
                            <div class="mb-3">
                                <label for="card_number" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" name="cvv" placeholder="123" required>
                                </div>
                            </div>
                        </div>

                        <!-- GCash Details -->
                        <div id="gcash-details" style="display: none;">
                            <div class="mb-3">
                                <label for="gcash_number" class="form-label">GCash Number</label>
                                <input type="text" class="form-control" id="gcash_number" name="gcash_number" placeholder="09XX XXX XXXX">
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Please ensure your GCash number is correct. You will receive a payment request after placing your order.
                            </div>
                        </div>

                        <!-- Cash on Delivery Details -->
                        <div id="cod-details" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                You will pay the exact amount when your order is delivered. Please have the exact amount ready.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mt-4">
                            Place Order
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-4">Order Summary</h4>
                    
                    <?php if (empty($cart_details['items'])): ?>
                    <div class="alert alert-warning">
                        Your cart is empty. <a href="products.php">Shop now</a>
                    </div>
                    <?php else: ?>
                    <!-- Items -->
                    <?php foreach ($cart_details['items'] as $item): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="mb-0"><?php echo htmlspecialchars($item['productName']); ?></h6>
                            <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                        </div>
                        <span>$<?php echo number_format($item['itemTotal'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    
                    <!-- Totals -->
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping</span>
                        <span>$<?php echo number_format($shipping_cost, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Total</strong>
                        <strong>$<?php echo number_format($total, 2); ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const creditCardDetails = document.getElementById('credit-card-details');
    const gcashDetails = document.getElementById('gcash-details');
    const codDetails = document.getElementById('cod-details');
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    
    // Toggle payment details based on payment method
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            // Hide all payment details first
            creditCardDetails.style.display = 'none';
            gcashDetails.style.display = 'none';
            codDetails.style.display = 'none';
            
            // Remove required attributes from all payment fields
            document.getElementById('card_number').required = false;
            document.getElementById('expiry_date').required = false;
            document.getElementById('cvv').required = false;
            document.getElementById('gcash_number').required = false;
            
            // Show and set required fields based on selected method
            switch(this.value) {
                case 'credit_card':
                    creditCardDetails.style.display = 'block';
                    document.getElementById('card_number').required = true;
                    document.getElementById('expiry_date').required = true;
                    document.getElementById('cvv').required = true;
                    break;
                case 'gcash':
                    gcashDetails.style.display = 'block';
                    document.getElementById('gcash_number').required = true;
                    break;
                case 'cod':
                    codDetails.style.display = 'block';
                    break;
            }
        });
    });
    
    // Format card number input
    const cardNumber = document.getElementById('card_number');
    cardNumber.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        let formattedValue = '';
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) {
                formattedValue += ' ';
            }
            formattedValue += value[i];
        }
        e.target.value = formattedValue;
    });
    
    // Format expiry date input
    const expiryDate = document.getElementById('expiry_date');
    expiryDate.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        e.target.value = value;
    });
    
    // Format CVV input
    const cvv = document.getElementById('cvv');
    cvv.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 3);
    });

    // Format GCash number input
    const gcashNumber = document.getElementById('gcash_number');
    gcashNumber.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        let formattedValue = '';
        for (let i = 0; i < value.length; i++) {
            if (i === 4 || i === 7) {
                formattedValue += ' ';
            }
            formattedValue += value[i];
        }
        e.target.value = formattedValue;
    });
});
</script>

<?php include 'includes/templates/footer.php'; ?>