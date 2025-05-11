<?php
// Include initialization file
require_once __DIR__ . '/../init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <header class="bg-dark text-white p-3">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <!-- Logo (far left) -->
                    <div class="col-auto">
                        <a href="<?php echo SITE_URL; ?>/index.php" class="text-white text-decoration-none logo-container d-flex align-items-center">
                            <img src="<?php echo SITE_URL; ?>/assets/images/categories/noxlogo.png" alt="NOX Logo" class="logo-img">
                            <h1 class="logo-text d-none d-md-inline ms-2 mb-0">NOX</h1>
                        </a>
                    </div>
                    <!-- Search Bar (left) -->
                    <div class="col">
                        <form class="search-form flex-grow-1 me-2" action="<?php echo SITE_URL; ?>/products.php" method="GET">
                            <input class="form-control search-input" type="search" placeholder="Search for products..." name="search" aria-label="Search">
                            <button class="btn btn-outline-light search-btn" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    <!-- Cart (left, after search) -->
                    <div class="col-auto">
                        <a href="<?php echo SITE_URL; ?>/cart.php" class="btn btn-outline-light cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if (isLoggedIn()): ?>
                                <span class="badge bg-danger cart-count"><?php echo getCartCount(); ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    <!-- Login/Register or Account (far right) -->
                    <div class="col-auto ms-auto d-flex align-items-center">
                        <?php if (isLoggedIn()): ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-light dropdown-toggle account-btn" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user"></i> Account
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end futuristic-dropdown" aria-labelledby="userDropdown">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php">
                                            <i class="fas fa-user-circle me-2"></i> Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo SITE_URL; ?>/orders.php">
                                            <i class="fas fa-shopping-bag me-2"></i> My Orders
                                        </a>
                                    </li>
                                    <?php if (isAdmin()): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/">
                                                <i class="fas fa-cog me-2"></i> Admin Panel
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/logout.php">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-outline-light me-2 btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i> Login
                            </a>
                            <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-light btn-register">
                                <i class="fas fa-user-plus me-2"></i> Register
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>
        
        <?php include __DIR__ . '/navbar.php'; ?>
        
        <main class="container py-4">
            <?php displayFlashMessage(); ?>