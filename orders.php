<?php
require_once 'includes/init.php';
require_once 'classes/Orders.php';

$order = new Orders();
$orders = $order->getUserOrders();

include 'includes/templates/header.php';
include 'includes/templates/navbar.php';
?>
<div class="container mt-5">
    <h2>Your Orders</h2>
    <?php if (!empty($orders)): ?>
        <ul class="list-group">
            <?php foreach ($orders as $o): ?>
                <li class="list-group-item">
                    Order #<?php echo $o['id']; ?> - <?php echo $o['status']; ?> - $<?php echo number_format($o['total'], 2); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>
</div>
<?php include 'includes/templates/footer.php'; ?>