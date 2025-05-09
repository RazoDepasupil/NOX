<?php
require_once 'includes/init.php';
require_once 'classes/ShoppingCart.php';

$cart = new ShoppingCart();
$items = $cart->getItems();

include 'includes/templates/header.php';
include 'includes/templates/navbar.php';
?>
<div class="container mt-5">
    <h2>Your Cart</h2>
    <?php if (!empty($items)): ?>
        <table class="table table-bordered">
            <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>
<?php include 'includes/templates/footer.php'; ?>