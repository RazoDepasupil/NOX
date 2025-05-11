<?php
require_once 'includes/init.php';
require_once 'classes/Orders.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'Please login to view your orders.');
    redirect('login.php');
}

$pageTitle = 'My Orders';
include 'includes/templates/header.php';

// Get user's orders
$orders = Orders::getByCustomer($_SESSION['user_id']);
?>

<div class="container mt-5">
    <h1 class="mb-4">My Orders</h1>
    
    <?php if (!empty($orders)): ?>
        <div class="row">
            <?php foreach ($orders as $order): ?>
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Order #<?php echo htmlspecialchars($order->getOrderID()); ?></h5>
                                <small class="text-muted">Placed on <?php echo date('F j, Y', strtotime($order->getOrderDate())); ?></small>
                            </div>
                            <div>
                                <span class="badge bg-<?php echo getStatusBadgeClass($order->getStatus()); ?>">
                                    <?php echo ucfirst($order->getStatus()); ?>
                                </span>
                                <span class="badge bg-<?php echo getPaymentStatusBadgeClass($order->getPaymentStatus()); ?> ms-2">
                                    <?php echo ucfirst($order->getPaymentStatus()); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6>Items:</h6>
                                    <ul class="list-unstyled">
                                        <?php foreach ($order->getItems() as $item): ?>
                                            <li class="mb-2">
                                                <?php echo htmlspecialchars($item['productName']); ?> 
                                                <span class="text-muted">
                                                    (<?php echo $item['quantity']; ?> × <?php echo CURRENCY_SYMBOL . number_format($item['unitCost'], 2); ?>)
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <p class="mb-1">
                                        <span class="text-muted">Subtotal:</span> 
                                        <?php echo CURRENCY_SYMBOL . number_format($order->getSubtotal(), 2); ?>
                                    </p>
                                    <p class="mb-1">
                                        <span class="text-muted">Shipping:</span> 
                                        <?php echo CURRENCY_SYMBOL . number_format($order->getShippingCost(), 2); ?>
                                    </p>
                                    <h5 class="mt-2">
                                        Total: <?php echo CURRENCY_SYMBOL . number_format($order->getTotal(), 2); ?>
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <?php if ($order->getStatus() === 'pending'): ?>
                            <div class="card-footer text-end">
                                <form action="cancel_order.php" method="post" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?php echo $order->getOrderID(); ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this order?')">
                                        Cancel Order
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> You haven't placed any orders yet.
            <a href="<?php echo SITE_URL; ?>/products.php" class="alert-link">Start shopping</a>
        </div>
    <?php endif; ?>
</div>

<?php
// Helper functions for badge colors
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getPaymentStatusBadgeClass($status) {
    switch ($status) {
        case 'paid':
            return 'success';
        case 'pending':
            return 'warning';
        case 'failed':
            return 'danger';
        case 'refunded':
            return 'info';
        default:
            return 'secondary';
    }
}

include 'includes/templates/footer.php';
?>