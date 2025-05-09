<?php
require_once 'includes/init.php';
require_once 'classes/Orders.php';
require_once 'classes/ShoppingCart.php';

$cart = new ShoppingCart();
$items = $cart->getItems();

include 'includes/templates/header.php';
include 'includes/templates/navbar.php';
?>
<div class="container mt-5">
    <h2>Checkout</h2>
    <?php if (!empty($items)): ?>
        <form method="post" action="process_checkout.php">
            <div class="form-group">
                <label for="address">Shipping Address</label>
                <input type="text" name="address" id="address" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Place Order</button>
        </form>
    <?php else: ?>
        <p>No items in cart.</p>
    <?php endif; ?>
</div>
<?php include 'includes/templates/footer.php'; ?>