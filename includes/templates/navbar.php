<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav">
              <li class="nav-item">
                  <a class="nav-link" href="<?php echo SITE_URL; ?>/">Home</a>
              </li>
              <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                      Categories
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                      <?php
                      $products = readJsonFile(PRODUCTS_FILE);
                      $categories = [];
                      
                      foreach ($products as $product) {
                          if (isset($product['type']) && !in_array($product['type'], $categories)) {
                              $categories[] = $product['type'];
                          }
                      }
                      
                      foreach ($categories as $category) {
                          echo '<li><a class="dropdown-item" href="' . SITE_URL . '/products.php?category=' . urlencode($category) . '">' . htmlspecialchars($category) . '</a></li>';
                      }
                      
                      if (empty($categories)) {
                          echo '<li><a class="dropdown-item" href="#">No categories available</a></li>';
                      }
                      ?>
                  </ul>
              </li>
              <li class="nav-item">
                  <a class="nav-link" href="<?php echo SITE_URL; ?>/products.php">All Products</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link" href="<?php echo SITE_URL; ?>/about.php">About Us</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link" href="<?php echo SITE_URL; ?>/contact.php">Contact</a>
              </li>
          </ul>
      </div>
  </div>
</nav>