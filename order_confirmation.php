<?php
require_once 'includes/init.php';
require_once 'classes/Orders.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    setFlashMessage('warning', 'Please log in to view your order.');
    header('Location: login.php?redirect=' . urlencode($_SERVER['HTTP_REFERER']));
    exit;
}

// Check if order ID is provided
if (!isset($_GET['order_id'])) {
    setFlashMessage('danger', 'Invalid order reference.');
    header('Location: orders.php');
    exit;
}

// Get order details
$order = Orders::getById($_GET['order_id']);

// Check if order exists and belongs to current user
if (!$order || $order->getCustomerId() !== $_SESSION['user_id']) {
    setFlashMessage('danger', 'Order not found.');
    header('Location: orders.php');
    exit;
}

include 'includes/templates/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="card-title mb-4">Order Confirmed!</h2>
                    <p class="lead mb-4">Thank you for your purchase. Your order has been successfully placed.</p>
                    <p class="text-muted mb-4">Order Reference: #<?php echo $order->getOrderID(); ?></p>
                </div>
            </div>

            <!-- Order Details -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h4 class="card-title mb-4">Order Details</h4>
                    
                    <!-- Order Items -->
                    <div class="mb-4">
                        <?php foreach ($order->getItems() as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($item['productName']); ?></h6>
                                <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                            </div>
                            <span>$<?php echo number_format($item['itemTotal'], 2); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr>
                    
                    <!-- Order Summary -->
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($order->getSubtotal(), 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping</span>
                        <span>$<?php echo number_format($order->getShippingCost(), 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax</span>
                        <span>$<?php echo number_format($order->getTax(), 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Total</strong>
                        <strong>$<?php echo number_format($order->getTotal(), 2); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Order Status -->
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h4 class="card-title mb-4">Order Status</h4>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Status</h6>
                            <span class="badge bg-<?php echo getStatusBadgeClass($order->getStatus()); ?>">
                                <?php echo ucfirst($order->getStatus()); ?>
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-1">Payment Status</h6>
                            <span class="badge bg-<?php echo getPaymentStatusBadgeClass($order->getPaymentStatus()); ?>">
                                <?php echo ucfirst($order->getPaymentStatus()); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center mt-4">
                <a href="orders.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-list me-2"></i>View All Orders
                </a>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/templates/footer.php'; ?> 