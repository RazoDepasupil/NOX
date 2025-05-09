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
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <a href="<?php echo SITE_URL; ?>" class="text-white text-decoration-none">
                            <h1><?php echo SITE_NAME; ?></h1>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <form class="d-flex" action="<?php echo SITE_URL; ?>/products.php" method="GET">
                            <input class="form-control me-2" type="search" placeholder="Search for products..." name="search" aria-label="Search">
                            <button class="btn btn-outline-light" type="submit">Search</button>
                        </form>
                    </div>
                    <div class="col-md-3 d-flex justify-content-end">
                        <a href="<?php echo SITE_URL; ?>/cart.php" class="btn btn-outline-light me-2">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if (isLoggedIn()): ?>
                                <span class="badge bg-danger"><?php echo getCartCount(); ?></span>
                            <?php endif; ?>
                        </a>
                        <?php if (isLoggedIn()): ?>
                            <div class="dropdown">
                                <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user"></i> Account
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/orders.php">My Orders</a></li>
                                    <?php if (isAdmin()): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/">Admin Panel</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-outline-light me-2">Login</a>
                            <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-light">Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>
        
        <?php include __DIR__ . '/navbar.php'; ?>
        
        <main class="container py-4">
            <?php displayFlashMessage(); ?>