<?php
require_once 'includes/init.php';
require_once 'classes/Product.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = new Product();
$details = $product->getProductById($id);

include 'includes/templates/header.php';
include 'includes/templates/navbar.php';
?>
<div class="container mt-5">
    <?php if ($details): ?>
        <div class="row">
            <div class="col-md-6">
                <img src="assets/images/<?php echo htmlspecialchars($details['image']); ?>" class="img-fluid" alt="">
            </div>
            <div class="col-md-6">
                <h2><?php echo htmlspecialchars($details['name']); ?></h2>
                <p>$<?php echo number_format($details['price'], 2); ?></p>
                <p><?php echo htmlspecialchars($details['description']); ?></p>
                <a href="cart.php?action=add&id=<?php echo $details['id']; ?>" class="btn btn-primary">Add to Cart</a>
            </div>
        </div>
    <?php else: ?>
        <p>Product not found.</p>
    <?php endif; ?>
</div>
<?php include 'includes/templates/footer.php'; ?>