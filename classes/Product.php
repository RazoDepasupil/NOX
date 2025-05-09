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
        } else {
            $this->productID = generateUniqueId();
            $this->serialNumber = $this->generateSerialNumber();
            $this->imageUrl = 'assets/images/product-placeholder.jpg';
        }
    }
    
    // Generate a unique serial number
    private function generateSerialNumber() {
        return strtoupper(substr(md5(time() . rand()), 0, 12));
    }
    
    // Save product data
    public function save() {
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
    }
    
    // Delete product
    public function delete() {
        $products = readJsonFile(PRODUCTS_FILE);
        
        foreach ($products as $key => $product) {
            if ($product['productID'] === $this->productID) {
                unset($products[$key]);
                break;
            }
        }
        
        $products = array_values($products); // Re-index array
        return writeJsonFile(PRODUCTS_FILE, $products);
    }
    
    // Sell product
    public function sell($quantity) {
        if ($this->stockQuantity < $quantity) {
            return false;
        }
        
        $this->stockQuantity -= $quantity;
        return $this->save();
    }
    
    // Get product details
    public function getProductDetails() {
        return $this->toArray();
    }
    
    // Update stock quantity
    public function updateStock($quantity) {
        $this->stockQuantity = $quantity;
        return $this->save();
    }
    
    // Apply discount
    public function applyDiscount($discountPercentage) {
        if ($discountPercentage < 0 || $discountPercentage > 100) {
            return false;
        }
        
        $originalCost = $this->unitCost;
        $discountAmount = $originalCost * ($discountPercentage / 100);
        $this->unitCost = $originalCost - $discountAmount;
        
        return $this->save();
    }
    
    // Add to order details
    public function addToOrderDetails(OrderDetails $orderDetail) {
        $this->orderDetails[] = $orderDetail->getOrderID();
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
        $products = readJsonFile(PRODUCTS_FILE);
        
        foreach ($products as $product) {
            if ($product['productID'] === $productId) {
                return new Product($product);
            }
        }
        
        return null;
    }
    
    // Get all products
    public static function getAll() {
        $products = readJsonFile(PRODUCTS_FILE);
        $productObjects = [];
        
        foreach ($products as $product) {
            $productObjects[] = new Product($product);
        }
        
        return $productObjects;
    }
    
    // Get products by category
    public static function getByCategory($category) {
        $products = readJsonFile(PRODUCTS_FILE);
        $productObjects = [];
        
        foreach ($products as $product) {
            if ($product['type'] === $category) {
                $productObjects[] = new Product($product);
            }
        }
        
        return $productObjects;
    }
    
    // Search products
    public static function search($keyword) {
        $products = readJsonFile(PRODUCTS_FILE);
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
            'shoppingCart' => $this->shoppingCart
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
}
?>