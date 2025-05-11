<?php
class Product {
    protected $productID;
    protected $productName;
    protected $serialNumber;
    protected $stockQuantity;
    protected $type;
    protected $unitCost;
    protected $description;
    protected $imageUrl;
    protected $orderDetails = [];
    protected $purchases = [];
    protected $shoppingCart = [];
    protected $featured;
    protected $created_at;
    protected $minStockLevel;
    protected $maxStockLevel;
    protected $details;
    
    // Constructor
    public function __construct($productData = null) {
        if ($productData) {
            $this->productID = $productData['productID'] ?? generateUniqueId();
            $this->productName = $productData['productName'] ?? '';
            $this->serialNumber = $productData['serialNumber'] ?? $this->generateSerialNumber();
            $this->stockQuantity = $productData['stockQuantity'] ?? 0;
            $this->type = $productData['type'] ?? '';
            $this->unitCost = $productData['unitCost'] ?? 0;
            $this->description = $productData['description'] ?? '';
            $this->imageUrl = $productData['imageUrl'] ?? 'assets/images/product-placeholder.jpg';
            $this->orderDetails = $productData['orderDetails'] ?? [];
            $this->purchases = $productData['purchases'] ?? [];
            $this->shoppingCart = $productData['shoppingCart'] ?? [];
            $this->featured = $productData['featured'] ?? false;
            $this->created_at = $productData['created_at'] ?? date('Y-m-d H:i:s');
            $this->minStockLevel = $productData['minStockLevel'] ?? 5;
            $this->maxStockLevel = $productData['maxStockLevel'] ?? 100;
            $this->details = $productData['details'] ?? [];
        } else {
            $this->productID = generateUniqueId();
            $this->productName = '';
            $this->serialNumber = $this->generateSerialNumber();
            $this->stockQuantity = 0;
            $this->type = '';
            $this->unitCost = 0;
            $this->description = '';
            $this->imageUrl = 'assets/images/product-placeholder.jpg';
            $this->orderDetails = [];
            $this->purchases = [];
            $this->shoppingCart = [];
            $this->featured = false;
            $this->created_at = date('Y-m-d H:i:s');
            $this->minStockLevel = 5;
            $this->maxStockLevel = 100;
            $this->details = [];
        }
    }
    
    // Generate a unique serial number
    private function generateSerialNumber() {
        return strtoupper(substr(md5(time() . rand()), 0, 12));
    }
    
    // Save product data
    public function save() {
        try {
            $products = readJsonFile(PRODUCTS_FILE);
            $found = false;
            
            // Check if product already exists
            foreach ($products as $key => $product) {
                if ($product['productID'] === $this->productID) {
                    $products[$key] = $this->toArray();
                    $found = true;
                    break;
                }
            }
            
            // Add new product if not found
            if (!$found) {
                $products[] = $this->toArray();
            }
            
            return writeJsonFile(PRODUCTS_FILE, $products);
        } catch (Exception $e) {
            error_log("Error saving product: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete product
    public function delete() {
        try {
            $products = readJsonFile(PRODUCTS_FILE);
            
            foreach ($products as $key => $product) {
                if ($product['productID'] === $this->productID) {
                    unset($products[$key]);
                    break;
                }
            }
            
            $products = array_values($products); // Re-index array
            return writeJsonFile(PRODUCTS_FILE, $products);
        } catch (Exception $e) {
            error_log("Error deleting product: " . $e->getMessage());
            return false;
        }
    }
    
    // Sell product
    public function sell($quantity) {
        if (!is_numeric($quantity) || $quantity < 1) {
            return false;
        }
        
        try {
            if ($this->stockQuantity < $quantity) {
                return false;
            }
            
            $this->stockQuantity -= $quantity;
            
            // Check if stock is below minimum level
            if ($this->stockQuantity <= $this->minStockLevel) {
                // Log low stock warning
                error_log("Low stock warning: Product {$this->productName} (ID: {$this->productID}) is below minimum stock level");
            }
            
            return $this->save();
        } catch (Exception $e) {
            error_log("Error selling product: " . $e->getMessage());
            return false;
        }
    }
    
    // Restock product
    public function restock($quantity) {
        if (!is_numeric($quantity) || $quantity < 1) {
            return false;
        }
        
        try {
            $newQuantity = $this->stockQuantity + $quantity;
            
            // Check if new quantity exceeds maximum stock level
            if ($newQuantity > $this->maxStockLevel) {
                return false;
            }
            
            $this->stockQuantity = $newQuantity;
            return $this->save();
        } catch (Exception $e) {
            error_log("Error restocking product: " . $e->getMessage());
            return false;
        }
    }
    
    // Update stock quantity
    public function updateStock($quantity) {
        if (!is_numeric($quantity) || $quantity < 0) {
            return false;
        }
        
        try {
            if ($quantity > $this->maxStockLevel) {
                return false;
            }
            
            $this->stockQuantity = $quantity;
            return $this->save();
        } catch (Exception $e) {
            error_log("Error updating stock: " . $e->getMessage());
            return false;
        }
    }
    
    // Apply discount
    public function applyDiscount($discountPercentage) {
        if (!is_numeric($discountPercentage) || $discountPercentage < 0 || $discountPercentage > 100) {
            return false;
        }
        
        try {
            $originalCost = $this->unitCost;
            $discountAmount = $originalCost * ($discountPercentage / 100);
            $this->unitCost = $originalCost - $discountAmount;
            
            return $this->save();
        } catch (Exception $e) {
            error_log("Error applying discount: " . $e->getMessage());
            return false;
        }
    }
    
    // Add to order details
    public function addToOrderDetails(OrderDetails $orderDetail) {
        $this->orderDetails[] = $orderDetail->getOrderId();
    }
    
    // Add to purchases
    public function addToPurchases(Purchases $purchase) {
        $this->purchases[] = $purchase->getPurchaseID();
    }
    
    // Add to shopping cart
    public function addToShoppingCart(ShoppingCart $cart) {
        $this->shoppingCart[] = $cart->getCartID();
    }
    
    // Load product by ID
    public static function getById($productId) {
        $data = readJsonFile(PRODUCTS_FILE);
        $products = $data['products'] ?? [];
        
        // Convert productId to string for comparison
        $productId = (string)$productId;
        
        foreach ($products as $product) {
            if (isset($product['productID']) && (string)$product['productID'] === $productId) {
                return new Product($product);
            }
        }
        
        return null;
    }
    
    // Get all products
    public static function getAll() {
        $data = readJsonFile(PRODUCTS_FILE);
        $products = $data['products'] ?? [];
        $productObjects = [];
        
        foreach ($products as $product) {
            $productObjects[] = new Product($product);
        }
        
        return $productObjects;
    }
    
    // Get products by category
    public static function getByCategory($category) {
        $data = readJsonFile(PRODUCTS_FILE);
        $products = $data['products'] ?? [];
        $productObjects = [];
        
        foreach ($products as $product) {
            if (isset($product['type']) && $product['type'] === $category) {
                $productObjects[] = new Product($product);
            }
        }
        
        return $productObjects;
    }
    
    // Search products
    public static function search($keyword) {
        $data = readJsonFile(PRODUCTS_FILE);
        $products = $data['products'] ?? [];
        $productObjects = [];
        
        foreach ($products as $product) {
            if (stripos($product['productName'], $keyword) !== false || 
                stripos($product['description'], $keyword) !== false) {
                $productObjects[] = new Product($product);
            }
        }
        
        return $productObjects;
    }
    
    // Convert product object to array
    public function toArray() {
        return [
            'productID' => $this->productID,
            'productName' => $this->productName,
            'serialNumber' => $this->serialNumber,
            'stockQuantity' => $this->stockQuantity,
            'type' => $this->type,
            'unitCost' => $this->unitCost,
            'description' => $this->description,
            'imageUrl' => $this->imageUrl,
            'orderDetails' => $this->orderDetails,
            'purchases' => $this->purchases,
            'shoppingCart' => $this->shoppingCart,
            'featured' => $this->featured,
            'created_at' => $this->created_at,
            'minStockLevel' => $this->minStockLevel,
            'maxStockLevel' => $this->maxStockLevel,
            'details' => $this->details
        ];
    }
    
    // Getters and setters
    public function getProductID() {
        return $this->productID;
    }
    
    public function getProductName() {
        return $this->productName;
    }
    
    public function setProductName($productName) {
        $this->productName = $productName;
    }
    
    public function getSerialNumber() {
        return $this->serialNumber;
    }
    
    public function getStockQuantity() {
        return $this->stockQuantity;
    }
    
    public function setStockQuantity($stockQuantity) {
        $this->stockQuantity = $stockQuantity;
    }
    
    public function getType() {
        return $this->type;
    }
    
    public function setType($type) {
        $this->type = $type;
    }
    
    public function getUnitCost() {
        return $this->unitCost;
    }
    
    public function setUnitCost($unitCost) {
        $this->unitCost = $unitCost;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function setDescription($description) {
        $this->description = $description;
    }
    
    public function getImageUrl() {
        return $this->imageUrl;
    }
    
    public function setImageUrl($imageUrl) {
        $this->imageUrl = $imageUrl;
    }

    /**
     * Get featured status
     * @return bool Featured status
     */
    public function isFeatured() {
        return $this->featured;
    }

    /**
     * Set featured status
     * @param bool $featured Featured status
     */
    public function setFeatured($featured) {
        $this->featured = (bool)$featured;
    }

    /**
     * Get creation date
     * @return string Creation date
     */
    public function getCreatedAt() {
        return $this->created_at;
    }

    /**
     * Set creation date
     * @param string $created_at Creation date
     */
    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }

    /**
     * Get featured products
     * 
     * @param int $limit Number of products to return
     * @return array Array of Product objects
     */
    public static function getFeaturedProducts($limit = 4) {
        $products = readJsonFile(PRODUCTS_FILE);
        $productObjects = [];
        $featured = array_filter($products, function($product) {
            return isset($product['featured']) && $product['featured'] === true;
        });

        // If no featured products, return latest products
        if (empty($featured)) {
            // Sort by newest first (assuming products have a created_at field)
            usort($products, function($a, $b) {
                $dateA = $a['created_at'] ?? '0';
                $dateB = $b['created_at'] ?? '0';
                return strcmp($dateB, $dateA);
            });
            $featured = $products;
        }

        // Get limited number of products
        $featured = array_slice($featured, 0, $limit);

        foreach ($featured as $product) {
            $productObjects[] = new Product($product);
        }

        return $productObjects;
    }

    public function getMinStockLevel() {
        return $this->minStockLevel;
    }
    
    public function setMinStockLevel($level) {
        if (is_numeric($level) && $level >= 0) {
            $this->minStockLevel = $level;
            return true;
        }
        return false;
    }
    
    public function getMaxStockLevel() {
        return $this->maxStockLevel;
    }
    
    public function setMaxStockLevel($level) {
        if (is_numeric($level) && $level > $this->minStockLevel) {
            $this->maxStockLevel = $level;
            return true;
        }
        return false;
    }

    public function getDetails() {
        return $this->details;
    }
    
    public function setDetails($details) {
        $this->details = $details;
    }
}
?>