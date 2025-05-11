<?php
require_once 'includes/init.php';
$pageTitle = 'Products';

// Get category filter
$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

// Create Product instance
$productObj = new Product();
$products = [];

// Get products based on filters
if ($search) {
    $products = Product::search($search);
} elseif ($category) {
    $products = Product::getByCategory($category);
} else {
    $products = Product::getAll();
}

// Map categories to their image filename prefixes
$categoryPrefixes = [
    'mens' => 'men',
    'womens' => 'women',
    'footwear' => 'footwear',
    'accessories' => 'accessories'
];

// Your image label mapping
$categoryImages = [
    "accessories1.png" => "Black Handbag",
    "accessories2.png" => "Beige Scarf",
    "accessories3.png" => "Black Cap",
    "accessories4.png" => "Sunglasses",
    "accessories5.png" => "Black Towels",
    "accessories6.png" => "Green Handkerchief",
    "accessories7.png" => "Bandana",
    "accessories8.png" => "Classic Cap",
    "footwear1.png" => "Sneakers",
    "footwear2.png" => "Green Slippers",
    "footwear3.png" => "Strappy Heels",
    "footwear4.png" => "Chunky Heels",
    "footwear5.png" => "White Flats",
    "footwear6.png" => "Suede Clogs",
    "footwear7.png" => "Crystal Heels",
    "men1.png" => "Black T-Shirt",
    "men2.png" => "Gray Jeans",
    "men3.png" => "Green Shorts",
    "men4.png" => "Polo Shirt",
    "men5.png" => "Brown Suit",
    "men6.png" => "Leather Jacket",
    "men7.png" => "Hooded Jacket",
    "men8.png" => "Varsity Jacket",
    "noxlogo.png" => "NOX Logo",
    "women1.png" => "Corset Dress",
    "women2.png" => "Floral Corset",
    "women3.png" => "Floral Skirt",
    "women4.png" => "Black T-Shirt (Women)",
    "women5.png" => "White Blouse",
    "women6.png" => "Pantsuit",
    "women8.png" => "Bow Dress"
];

// Get the selected category from the URL
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;

// Filter images by category if a category is selected
$filteredImages = $categoryImages;
if ($selectedCategory && isset($categoryPrefixes[$selectedCategory])) {
    $prefix = $categoryPrefixes[$selectedCategory];
    $filteredImages = array_filter($categoryImages, function($filename) use ($prefix) {
        return strpos($filename, $prefix) === 0;
    }, ARRAY_FILTER_USE_KEY);
}

include 'includes/templates/header.php';
?>

<div class="container mt-5">
    <!-- Category Title -->
    <?php if ($category): ?>
        <h1 class="mb-4"><?php echo CATEGORIES[$category] ?? 'Products'; ?></h1>
    <?php elseif ($search): ?>
        <h1 class="mb-4">Search Results for "<?php echo htmlspecialchars($search); ?>"</h1>
    <?php else: ?>
        <h1 class="mb-4">All Products</h1>
    <?php endif; ?>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Categories</h5>
                    <div class="list-group">
                        <a href="<?php echo SITE_URL; ?>/products.php" class="list-group-item list-group-item-action <?php echo !$category ? 'active' : ''; ?>">
                            All Products
                        </a>
                        <?php foreach (CATEGORIES as $key => $name): ?>
                            <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo urlencode($key); ?>" 
                               class="list-group-item list-group-item-action <?php echo $category === $key ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Combined Category Images and Products Grid -->
            <div class="row mb-4">
                <?php
                // Build a map of image filename to product for quick lookup
                $productImageMap = [];
                foreach ($products as $product) {
                    $imgPath = basename($product->getImageUrl());
                    $productImageMap[$imgPath] = $product;
                }
                foreach ($filteredImages as $img => $label):
                    $product = isset($productImageMap[$img]) ? $productImageMap[$img] : null;
                ?>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="card h-100 product-card">
                            <img src="assets/images/categories/<?php echo $img; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($label); ?>">
                            <div class="card-body text-center p-2">
                                <h6 class="card-title mb-1"><?php echo htmlspecialchars($label); ?></h6>
                                <?php if ($product): ?>
                                    <p class="card-text text-primary mb-1"><?php echo CURRENCY_SYMBOL . number_format($product->getUnitCost() ?? 0, 2); ?></p>
                                    <?php if ($product->getStockQuantity() > 0): ?>
                                        <span class="badge bg-success mb-1">In Stock</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger mb-1">Out of Stock</span>
                                    <?php endif; ?>
                                    <a href="<?php echo SITE_URL; ?>/product-details.php?id=<?php echo $product->getProductID(); ?>" class="btn btn-primary btn-sm w-100 mt-2">View Details</a>
                                <?php else: ?>
                                    <span class="badge bg-secondary mb-1">Not available</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.product-card {
    transition: transform 0.3s ease;
}

.product-card:hover {
    transform: translateY(-5px);
}

.card-img-top {
    height: 200px;
    object-fit: cover;
}

.list-group-item.active {
    background-color: #212529;
    border-color: #212529;
}
</style>

<?php include 'includes/templates/footer.php'; ?> 