<?php
require_once 'includes/init.php';
include 'includes/templates/header.php';
?>
<div class="container-fluid p-0">
    <!-- Hero Section -->
    <div class="hero-section text-center py-5" style="background-color: #000; color: #fff;">
        <div class="logo-container">
            <img src="assets/images/categories/noxlogo.png" alt="NOX Logo" class="logo-img">
            <h1 class="logo-text">NOX</h1>
        </div>
        <p class="lead">Premium Clothing & Lifestyle</p>
        <a href="<?php echo SITE_URL; ?>/products.php" class="btn btn-light btn-lg mt-3">Shop Now</a>
    </div>

    <!-- Categories Section -->
    <section class="categories-section py-5">
        <div class="container">
            <h2 class="text-center mb-4">Shop by Category</h2>
            <div class="row">
                <!-- Men's Category -->
                <div class="col-md-3 mb-4">
                    <div class="card category-card">
                        <div class="category-images">
                            <img src="assets/images/categories/men1.png" class="category-img active" alt="Men's Fashion 1">
                            <img src="assets/images/categories/men2.png" class="category-img" alt="Men's Fashion 2">
                            <img src="assets/images/categories/men3.png" class="category-img" alt="Men's Fashion 3">
                            <img src="assets/images/categories/men4.png" class="category-img" alt="Men's Fashion 4">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Men's Fashion</h5>
                            <a href="<?php echo SITE_URL; ?>/products.php?category=mens" class="btn btn-primary">Shop Now</a>
                        </div>
                    </div>
                </div>
                <!-- Women's Category -->
                <div class="col-md-3 mb-4">
                    <div class="card category-card">
                        <div class="category-images">
                            <img src="assets/images/categories/women1.png" class="category-img active" alt="Women's Fashion 1">
                            <img src="assets/images/categories/women2.png" class="category-img" alt="Women's Fashion 2">
                            <img src="assets/images/categories/women3.png" class="category-img" alt="Women's Fashion 3">
                            <img src="assets/images/categories/women4.png" class="category-img" alt="Women's Fashion 4">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Women's Fashion</h5>
                            <a href="<?php echo SITE_URL; ?>/products.php?category=womens" class="btn btn-primary">Shop Now</a>
                        </div>
                    </div>
                </div>
                <!-- Footwear Category -->
                <div class="col-md-3 mb-4">
                    <div class="card category-card">
                        <div class="category-images">
                            <img src="assets/images/categories/footwear1.png" class="category-img active" alt="Footwear 1">
                            <img src="assets/images/categories/footwear2.png" class="category-img" alt="Footwear 2">
                            <img src="assets/images/categories/footwear3.png" class="category-img" alt="Footwear 3">
                            <img src="assets/images/categories/footwear4.png" class="category-img" alt="Footwear 4">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Footwear</h5>
                            <a href="<?php echo SITE_URL; ?>/products.php?category=footwear" class="btn btn-primary">Shop Now</a>
                        </div>
                    </div>
                </div>
                <!-- Accessories Category -->
                <div class="col-md-3 mb-4">
                    <div class="card category-card">
                        <div class="category-images">
                            <img src="assets/images/categories/accessories1.png" class="category-img active" alt="Accessories 1">
                            <img src="assets/images/categories/accessories2.png" class="category-img" alt="Accessories 2">
                            <img src="assets/images/categories/accessories3.png" class="category-img" alt="Accessories 3">
                            <img src="assets/images/categories/accessories4.png" class="category-img" alt="Accessories 4">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Accessories</h5>
                            <a href="<?php echo SITE_URL; ?>/products.php?category=accessories" class="btn btn-primary">Shop Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Brand Story -->
    <div class="container mt-5 mb-5">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2>Our Story</h2>
                <p>NOX is more than just a clothing brand - it's a lifestyle. We create premium apparel that combines style, comfort, and quality. From casual wear to statement pieces, our collections are designed for those who appreciate the finer things in life.</p>
                <a href="<?php echo SITE_URL; ?>/about.php" class="btn btn-outline-dark">Learn More</a>
            </div>
            <div class="col-md-6">
                <img src="<?php echo SITE_URL; ?>/assets/images/brand-story.jpg" class="img-fluid" alt="NOX Brand Story">
            </div>
        </div>
    </div>
</div>

<style>
.hero-section {
    min-height: 60vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo SITE_URL; ?>/assets/images/hero-bg.jpg');
    background-size: cover;
    background-position: center;
}

.category-card {
    position: relative;
    height: 400px;
    overflow: hidden;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.category-images {
    height: 100%;
    width: 100%;
}

.category-img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 0.5s ease;
}

.category-img.active {
    opacity: 1;
}

.category-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    color: white;
    text-align: center;
    padding: 20px;
}

.category-title {
    font-size: 2rem;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.product-card {
    transition: transform 0.3s;
}

.product-card:hover {
    transform: translateY(-5px);
}

.card-img-top {
    height: 300px;
    object-fit: cover;
}
</style>

<script>
// Image rotation for categories
document.addEventListener('DOMContentLoaded', function() {
    const categories = ['men', 'women', 'footwear', 'accessories'];
    
    categories.forEach(category => {
        let currentImage = 1;
        const images = document.querySelectorAll(`[src*="${category}"]`);
        
        setInterval(() => {
            images.forEach(img => img.classList.remove('active'));
            currentImage = (currentImage % 4) + 1;
            const activeImage = document.querySelector(`[src*="${category}${currentImage}"]`);
            if (activeImage) {
                activeImage.classList.add('active');
            }
        }, 3000);
    });
});
</script>
<?php include 'includes/templates/footer.php'; ?>