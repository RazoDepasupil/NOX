<?php
require_once 'includes/init.php';
require_once 'classes/ShoppingCart.php';
require_once 'classes/Product.php';

$cart = new ShoppingCart();
$items = $cart->getItems();
$cartTotal = 0;

include 'includes/templates/header.php';
?>
<style>
.cart-table th, .cart-table td {
    vertical-align: middle;
    text-align: center;
}
.cart-table th:first-child, .cart-table td:first-child {
    text-align: left;
}
.cart-product-img {
    width: 64px;
    height: 64px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #eee;
    background: #fff;
}
.cart-product-name {
    font-weight: 500;
    font-size: 1.1rem;
    margin-bottom: 0.2rem;
}
.cart-product-sku {
    font-size: 0.85rem;
    color: #888;
}
.cart-size-badge {
    background: #f1f3f4;
    color: #222;
    border-radius: 6px;
    padding: 0.25em 0.7em;
    font-size: 0.95em;
    font-weight: 500;
}
.quantity-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.quantity-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1px solid #ddd;
    background: #fff;
    color: #222;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, border 0.2s;
}
.quantity-btn:hover {
    background: #f8f9fa;
    border-color: #bbb;
}
.quantity-input {
    width: 48px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 0.25rem 0.5rem;
    font-size: 1rem;
}
.remove-btn {
    border: none;
    background: none;
    color: #dc3545;
    font-size: 1.2rem;
    padding: 0.4rem 0.7rem;
    border-radius: 50%;
    transition: background 0.2s;
}
.remove-btn:hover {
    background: #ffeaea;
    color: #b71c1c;
}
.cart-total-row td {
    font-size: 1.1rem;
    font-weight: 600;
    background: #f8f9fa;
}
.cart-empty {
    text-align: center;
    padding: 3rem 0;
    color: #888;
}
.cart-empty i {
    font-size: 3rem;
    color: #e0e0e0;
    margin-bottom: 1rem;
}
@media (max-width: 600px) {
    .cart-table th, .cart-table td {
        font-size: 0.95rem;
        padding: 0.5rem;
    }
    .cart-product-img {
        width: 48px;
        height: 48px;
    }
}
/* Hide number input arrows for all browsers */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
input[type=number] {
  -moz-appearance: textfield; /* Firefox */
}
</style>
<div class="container mt-5 mb-5">
    <h2 class="mb-4">Your Shopping Cart</h2>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php if (isset($_SESSION['debug']) && is_array($_SESSION['debug'])): ?>
            <div class="alert alert-warning">
                <h5>Debug Information:</h5>
                <pre><?php print_r($_SESSION['debug']); unset($_SESSION['debug']); ?></pre>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (!empty($items)): ?>
        <div class="table-responsive">
            <table class="table cart-table align-middle shadow-sm">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:220px;">Product</th>
                        <th>Size</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item):
                    if (!isset($item['productID'])) continue;
                    $product = Product::getById($item['productID']);
                    if ($product):
                        $itemTotal = $product->getUnitCost() * ($item['quantity'] ?? 1);
                        $cartTotal += $itemTotal;
                ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?php echo htmlspecialchars($product->getImageUrl() ?? 'assets/images/product-placeholder.jpg'); ?>" class="cart-product-img" alt="<?php echo htmlspecialchars($product->getProductName() ?? 'Product'); ?>">
                                <div>
                                    <div class="cart-product-name"><?php echo htmlspecialchars($product->getProductName() ?? 'Product'); ?></div>
                                    <div class="cart-product-sku">SKU: <?php echo htmlspecialchars($product->getProductID() ?? 'N/A'); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="cart-size-badge"><?php echo htmlspecialchars($item['size'] ?? 'N/A'); ?></span></td>
                        <td>
                            <form action="update_cart.php" method="POST" class="quantity-group">
                                <input type="hidden" name="cart_item_id" value="<?php echo htmlspecialchars($item['cartItemID'] ?? ''); ?>">
                                <button type="button" class="quantity-btn" onclick="updateQuantity(this, -1)"><i class="fas fa-minus"></i></button>
                                <input type="number" name="quantity" value="<?php echo htmlspecialchars($item['quantity'] ?? 1); ?>" min="1" max="<?php echo htmlspecialchars($product->getStockQuantity() ?? 1); ?>" class="quantity-input" onchange="this.form.submit()">
                                <button type="button" class="quantity-btn" onclick="updateQuantity(this, 1)"><i class="fas fa-plus"></i></button>
                            </form>
                        </td>
                        <td>$<?php echo number_format($product->getUnitCost() ?? 0, 2); ?></td>
                        <td><strong>$<?php echo number_format($itemTotal, 2); ?></strong></td>
                        <td>
                            <form action="remove_from_cart.php" method="POST" onsubmit="return confirm('Remove this item from your cart?');">
                                <input type="hidden" name="cart_item_id" value="<?php echo htmlspecialchars($item['cartItemID'] ?? ''); ?>">
                                <button type="submit" class="remove-btn" title="Remove"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endif; endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="cart-total-row">
                        <td colspan="4" class="text-end">Total:</td>
                        <td colspan="2" class="text-start">$<?php echo number_format($cartTotal, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 gap-2">
            <a href="products.php" class="btn btn-outline-secondary px-4 py-2">
                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
            </a>
            <a href="checkout.php" class="btn btn-success px-4 py-2">
                <i class="fas fa-shopping-cart me-2"></i>Proceed to Checkout
            </a>
        </div>
    <?php else: ?>
        <div class="cart-empty">
            <i class="fas fa-shopping-cart"></i>
            <h4 class="mt-3">Your cart is empty</h4>
            <p class="text-muted">Looks like you haven't added any items yet.</p>
            <a href="products.php" class="btn btn-primary mt-3"><i class="fas fa-shopping-bag me-2"></i>Start Shopping</a>
        </div>
    <?php endif; ?>
</div>
<script>
function updateQuantity(button, change) {
    const input = button.parentElement.querySelector('input[type="number"]');
    const currentValue = parseInt(input.value);
    const newValue = currentValue + change;
    const max = parseInt(input.getAttribute('max'));
    const min = parseInt(input.getAttribute('min'));
    if (newValue >= min && newValue <= max) {
        input.value = newValue;
        input.form.submit();
    }
}
</script>
<?php include 'includes/templates/footer.php'; ?>