<?php
require_once 'includes/init.php';
require_once 'classes/Product.php';

$productId = isset($_GET['id']) ? $_GET['id'] : null;
$product = $productId ? Product::getById($productId) : null;

if (!$product) {
    setFlashMessage('danger', 'Product not found.');
    header('Location: products.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product->getProductName()); ?> - NOX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/templates/header.php'; ?>

    <div class="container my-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product->getProductName()); ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo htmlspecialchars($product->getImageUrl()); ?>" 
                     alt="<?php echo htmlspecialchars($product->getProductName()); ?>" 
                     class="img-fluid rounded product-image">
            </div>
            <div class="col-md-6">
                <h1 class="mb-3"><?php echo htmlspecialchars($product->getProductName()); ?></h1>
                
                <div class="mb-4">
                    <h3 class="text-primary">$<?php echo number_format($product->getUnitCost(), 2); ?></h3>
                    <div class="stock-status mb-3">
                        <?php if ($product->getStockQuantity() > 0): ?>
                            <span class="badge bg-success">In Stock (<?php echo $product->getStockQuantity(); ?> available)</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="product-description mb-4">
                    <h4>Description</h4>
                    <p><?php echo nl2br(htmlspecialchars($product->getDescription())); ?></p>
                </div>

                <?php $details = $product->getDetails(); ?>
                <?php if ($details): ?>
                <div class="product-specifications mb-4">
                    <h4>Specifications</h4>
                    <div class="row">
                        <?php foreach ($details as $key => $value): ?>
                            <div class="col-md-6 mb-2">
                                <strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</strong>
                                <?php if (is_array($value)): ?>
                                    <?php echo implode(', ', $value); ?>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($value); ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($product->getStockQuantity() > 0): ?>
                <form action="add_to_cart.php" method="POST" class="mb-4">
                    <input type="hidden" name="product_id" value="<?php echo $product->getProductID(); ?>">
                    <div class="row align-items-center">
                        <?php if (isset($details['sizes']) && is_array($details['sizes']) && count($details['sizes']) > 0): ?>
                        <div class="col-md-4 mb-2">
                            <div class="input-group">
                                <label class="input-group-text" for="size">Size</label>
                                <select class="form-select" id="size" name="size" required>
                                    <option value="" disabled selected>Select size</option>
                                    <?php foreach ($details['sizes'] as $size): ?>
                                        <option value="<?php echo htmlspecialchars($size); ?>"><?php echo htmlspecialchars($size); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-4">
                            <div class="input-group">
                                <label class="input-group-text" for="quantity">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       value="1" min="1" max="<?php echo $product->getStockQuantity(); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </form>
                <?php endif; ?>

                <div class="product-meta">
                    <p class="text-muted">
                        <small>Product ID: <?php echo htmlspecialchars($product->getSerialNumber()); ?></small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/templates/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>