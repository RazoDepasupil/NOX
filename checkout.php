<?php
require_once 'includes/init.php';
require_once 'classes/ShoppingCart.php';
require_once 'classes/Product.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    setFlashMessage('warning', 'Please log in to complete your purchase.');
    header('Location: login.php?redirect=' . urlencode($_SERVER['HTTP_REFERER']));
    exit;
}

$cart = new ShoppingCart();
$items = $cart->getItems();
$cartTotal = 0;

// Calculate cart total
foreach ($items as $item) {
    if (!isset($item['productID'])) continue;
    $product = Product::getById($item['productID']);
    if ($product) {
        $cartTotal += $product->getUnitCost() * ($item['quantity'] ?? 1);
    }
}

// Check if cart is empty
if (empty($items)) {
    setFlashMessage('warning', 'Your cart is empty.');
    header('Location: cart.php');
    exit;
}

$pageTitle = 'Checkout';
include 'includes/templates/header.php';
?>

<div class="container mt-5 mb-5">
    <h2 class="mb-4">Checkout</h2>
    
    <?php if (empty($items)): ?>
        <div class="alert alert-info">
            <i class="fas fa-shopping-cart me-2"></i>Your cart is empty.
            <a href="products.php" class="alert-link">Continue shopping</a>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Order Summary -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($items as $item):
                            if (!isset($item['productID'])) continue;
                            $product = Product::getById($item['productID']);
                            if ($product):
                                $itemTotal = $product->getUnitCost() * ($item['quantity'] ?? 1);
                        ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo htmlspecialchars($product->getProductName()); ?> x <?php echo $item['quantity']; ?></span>
                                <span>$<?php echo number_format($itemTotal, 2); ?></span>
                            </div>
                        <?php endif; endforeach; ?>
                        
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Subtotal:</strong>
                            <strong>$<?php echo number_format($cartTotal, 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>Calculated at next step</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Tax:</span>
                            <span>Calculated at next step</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Checkout Form -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Shipping & Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="process_checkout.php" method="POST" id="checkoutForm">
                            <!-- Shipping Information -->
                            <h6 class="mb-3">Shipping Address</h6>
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">Full Address</label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required></textarea>
                            </div>
                            
                            <!-- Payment Method -->
                            <h6 class="mb-3 mt-4">Payment Method</h6>
                            <div class="mb-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                    <label class="form-check-label" for="cod">
                                        Cash on Delivery (COD)
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="gcash" value="gcash">
                                    <label class="form-check-label" for="gcash">
                                        GCash
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card">
                                    <label class="form-check-label" for="credit_card">
                                        Credit Card
                                    </label>
                                </div>
                            </div>
                            
                            <!-- GCash Number (hidden by default) -->
                            <div id="gcashFields" class="mb-3" style="display: none;">
                                <label for="gcash_number" class="form-label">GCash Number</label>
                                <input type="tel" class="form-control" id="gcash_number" name="gcash_number" placeholder="09XXXXXXXXX">
                            </div>
                            
                            <!-- Credit Card Fields (hidden by default) -->
                            <div id="creditCardFields" class="mb-3" style="display: none;">
                                <div class="mb-3">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="card_number" name="card_number" placeholder="XXXX XXXX XXXX XXXX">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="expiry_date" class="form-label">Expiry Date</label>
                                        <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" name="cvv" placeholder="XXX">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-lock me-2"></i>Place Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const gcashFields = document.getElementById('gcashFields');
    const creditCardFields = document.getElementById('creditCardFields');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            // Hide all payment fields
            gcashFields.style.display = 'none';
            creditCardFields.style.display = 'none';
            
            // Show relevant fields
            if (this.value === 'gcash') {
                gcashFields.style.display = 'block';
            } else if (this.value === 'credit_card') {
                creditCardFields.style.display = 'block';
            }
        });
    });
    
    // Form validation
    const form = document.getElementById('checkoutForm');
    form.addEventListener('submit', function(e) {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        if (selectedMethod === 'gcash') {
            const gcashNumber = document.getElementById('gcash_number').value;
            if (!gcashNumber || !/^09[0-9]{9}$/.test(gcashNumber.replace(/\s/g, ''))) {
                e.preventDefault();
                alert('Please enter a valid GCash number (should start with 09 and be 11 digits).');
            }
        } else if (selectedMethod === 'credit_card') {
            const cardNumber = document.getElementById('card_number').value;
            const expiryDate = document.getElementById('expiry_date').value;
            const cvv = document.getElementById('cvv').value;
            
            if (!cardNumber || !expiryDate || !cvv) {
                e.preventDefault();
                alert('Please fill in all credit card details.');
            }
        }
    });
});
</script>

<?php include 'includes/templates/footer.php'; ?> 