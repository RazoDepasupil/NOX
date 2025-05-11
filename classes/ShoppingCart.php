<?php
require_once __DIR__ . '/../includes/init.php';

class ShoppingCart {
    private $cartID;
    private $userID;
    private $items = [];
    private $shippingID;
    private $shippingType;
    private $shippingCost;
    private $shippingRegionID;
    private $taxRate;
    
    // Constructor
    public function __construct($userId = null) {
        if (!session_id()) {
            session_start();
        }
        
        $this->cartID = generateUniqueId();
        $this->userID = $userId ?? (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
        $this->taxRate = DEFAULT_TAX_RATE;
        $this->loadCartItems();
    }
    
    // Load cart items for current user
    private function loadCartItems() {
        if (!$this->userID) {
            return;
        }
        
        try {
            $carts = readJsonFile(CART_FILE);
            
            if (isset($carts[$this->userID])) {
                $this->items = $carts[$this->userID];
                
                // Load shipping info if available
                if (isset($carts['shipping'][$this->userID])) {
                    $shipping = $carts['shipping'][$this->userID];
                    $this->shippingID = $shipping['shippingID'] ?? null;
                    $this->shippingType = $shipping['shippingType'] ?? null;
                    $this->shippingCost = $shipping['shippingCost'] ?? 0;
                    $this->shippingRegionID = $shipping['shippingRegionID'] ?? null;
                }
            }
        } catch (Exception $e) {
            error_log("Error loading cart items: " . $e->getMessage());
            $this->items = [];
        }
    }
    
    // Save cart items
    private function saveCartItems() {
        if (!$this->userID) {
            return false;
        }
        
        try {
            $carts = readJsonFile(CART_FILE);
            $carts[$this->userID] = $this->items;
            
            // Save shipping info
            if (!isset($carts['shipping'])) {
                $carts['shipping'] = [];
            }
            
            $carts['shipping'][$this->userID] = [
                'shippingID' => $this->shippingID,
                'shippingType' => $this->shippingType,
                'shippingCost' => $this->shippingCost,
                'shippingRegionID' => $this->shippingRegionID
            ];
            
            return writeJsonFile(CART_FILE, $carts);
        } catch (Exception $e) {
            error_log("Error saving cart items: " . $e->getMessage());
            return false;
        }
    }
    
    // Add item to cart
    public function addCartItem($productId, $quantity = 1, $size = null) {
        error_log('ShoppingCart::addCartItem called with productId=' . $productId . ', quantity=' . $quantity . ', size=' . $size);
        if (!$this->userID) {
            error_log('No user ID in session');
            return false;
        }
        
        if (!is_numeric($quantity) || $quantity < 1) {
            error_log('Invalid quantity');
            return false;
        }
        
        try {
            // Validate product
            $product = Product::getById($productId);
            if (!$product) {
                error_log('Product not found for ID: ' . $productId);
                return false;
            }
            
            // Calculate total quantity in cart for this product+size
            $currentQty = 0;
            foreach ($this->items as $item) {
                if ($item['productID'] === $productId && ($item['size'] ?? null) === $size) {
                    $currentQty += $item['quantity'];
                }
            }
            
            // Check if adding would exceed stock
            if ($product->getStockQuantity() < $currentQty + $quantity) {
                error_log('Not enough stock: requested=' . ($currentQty + $quantity) . ', available=' . $product->getStockQuantity());
                return false;
            }
            
            // Check if product with same size already in cart
            foreach ($this->items as &$item) {
                if ($item['productID'] === $productId && ($item['size'] ?? null) === $size) {
                    $item['quantity'] += $quantity;
                    return $this->saveCartItems();
                }
            }
            
            // Add new item to cart
            $this->items[] = [
                'cartItemID' => generateUniqueId(),
                'productID' => $productId,
                'quantity' => $quantity,
                'size' => $size,
                'dateAdded' => date('Y-m-d H:i:s')
            ];
            
            return $this->saveCartItems();
        } catch (Exception $e) {
            error_log("Error adding item to cart: " . $e->getMessage());
            return false;
        }
    }
    
    // Update quantity of cart item
    public function updateQuantity($cartItemId, $quantity) {
        if (!$this->userID || !is_numeric($quantity) || $quantity < 1) {
            return false;
        }
        
        try {
            foreach ($this->items as &$item) {
                if ($item['cartItemID'] === $cartItemId) {
                    // Get product to check stock
                    $product = Product::getById($item['productID']);
                    if (!$product || $product->getStockQuantity() < $quantity) {
                        return false;
                    }
                    
                    $item['quantity'] = $quantity;
                    return $this->saveCartItems();
                }
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error updating cart quantity: " . $e->getMessage());
            return false;
        }
    }
    
    // Remove item from cart
    public function removeCartItem($cartItemId) {
        if (!$this->userID) {
            return false;
        }
        
        try {
            foreach ($this->items as $key => $item) {
                if ($item['cartItemID'] === $cartItemId) {
                    unset($this->items[$key]);
                    $this->items = array_values($this->items); // Re-index array
                    return $this->saveCartItems();
                }
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error removing cart item: " . $e->getMessage());
            return false;
        }
    }
    
    // Get cart details
    public function viewCartDetails() {
        if (!$this->userID) {
            return [
                'items' => [],
                'subtotal' => 0,
                'shipping' => [
                    'type' => null,
                    'cost' => 0
                ],
                'tax' => 0,
                'total' => 0
            ];
        }
        
        try {
            $cartDetails = [
                'items' => [],
                'subtotal' => 0,
                'shipping' => [
                    'type' => $this->shippingType,
                    'cost' => $this->shippingCost
                ],
                'tax' => 0,
                'total' => 0
            ];
            
            $products = readJsonFile(PRODUCTS_FILE);
            
            foreach ($this->items as $item) {
                // Skip invalid items
                if (!isset($item['productID']) || !isset($item['quantity'])) {
                    error_log("Invalid cart item found: " . json_encode($item));
                    continue;
                }
                
                $productData = null;
                
                // Find product details
                foreach ($products as $product) {
                    if (isset($product['productID']) && $product['productID'] === $item['productID']) {
                        $productData = $product;
                        break;
                    }
                }
                
                if ($productData && isset($productData['unitCost'])) {
                    $itemTotal = $productData['unitCost'] * $item['quantity'];
                    $cartDetails['subtotal'] += $itemTotal;
                    
                    $cartDetails['items'][] = [
                        'cartItemID' => $item['cartItemID'] ?? generateUniqueId(),
                        'productID' => $item['productID'],
                        'productName' => $productData['productName'] ?? 'Unknown Product',
                        'unitCost' => $productData['unitCost'],
                        'quantity' => $item['quantity'],
                        'itemTotal' => $itemTotal,
                        'imageUrl' => $productData['imageUrl'] ?? 'assets/images/product-placeholder.jpg',
                        'dateAdded' => $item['dateAdded'] ?? date('Y-m-d H:i:s')
                    ];
                } else {
                    error_log("Product not found or invalid for productID: " . $item['productID']);
                }
            }
            
            // Calculate tax
            $cartDetails['tax'] = $cartDetails['subtotal'] * $this->taxRate;
            
            // Calculate total
            $cartDetails['total'] = $cartDetails['subtotal'] + $cartDetails['shipping']['cost'] + $cartDetails['tax'];
            
            return $cartDetails;
        } catch (Exception $e) {
            error_log("Error viewing cart details: " . $e->getMessage());
            return [
                'items' => [],
                'subtotal' => 0,
                'shipping' => ['type' => null, 'cost' => 0],
                'tax' => 0,
                'total' => 0
            ];
        }
    }
    
    // Clear cart
    public function clearCart() {
        if (!$this->userID) {
            return false;
        }
        
        try {
            $this->items = [];
            return $this->saveCartItems();
        } catch (Exception $e) {
            error_log("Error clearing cart: " . $e->getMessage());
            return false;
        }
    }
    
    // Checkout process
    public function checkout() {
        if (!$this->userID || empty($this->items)) {
            return false;
        }
        
        try {
            $cartDetails = $this->viewCartDetails();
            
            // Create new order
            $order = new Orders();
            $order->setCustomerId($this->userID);
            $order->setSubtotal($cartDetails['subtotal']);
            $order->setShippingCost($cartDetails['shipping']['cost']);
            $order->setTax($cartDetails['tax']);
            $order->setTotal($cartDetails['total']);
            
            $orderItemsData = [];
            foreach ($cartDetails['items'] as $item) {
                $orderItemsData[] = [
                    'productID' => $item['productID'],
                    'productName' => $item['productName'],
                    'quantity' => $item['quantity'],
                    'unitCost' => $item['unitCost'],
                    'itemTotal' => $item['itemTotal']
                ];
                
                // Update product stock
                $product = Product::getById($item['productID']);
                if ($product) {
                    $product->sell($item['quantity']);
                }
            }
            
            $order->setItems($orderItemsData);
            
            if ($order->save()) {
                // Clear cart after successful order
                return $this->clearCart();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error during checkout: " . $e->getMessage());
            return false;
        }
    }
    
    // Update shipping info
    public function updateShippingInfo($shippingType, $shippingRegionID = null) {
        if (!$this->userID) {
            return false;
        }
        
        try {
            $this->shippingID = generateUniqueId();
            $this->shippingType = $shippingType;
            $this->shippingRegionID = $shippingRegionID;
            
            // Calculate shipping cost based on type
            if (isset(SHIPPING_METHODS[$shippingType])) {
                $this->shippingCost = SHIPPING_METHODS[$shippingType]['cost'];
            } else {
                $this->shippingCost = 0;
            }
            
            return $this->saveCartItems();
        } catch (Exception $e) {
            error_log("Error updating shipping info: " . $e->getMessage());
            return false;
        }
    }
    
    // Getters
    public function getCartID() {
        return $this->cartID;
    }
    
    public function getUserID() {
        return $this->userID;
    }
    
    public function getItems() {
        return $this->items;
    }
    
    public function getItemCount() {
        $count = 0;
        foreach ($this->items as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
    
    public function getShippingType() {
        return $this->shippingType;
    }
    
    public function getShippingCost() {
        return $this->shippingCost;
    }
    
    public function getTaxRate() {
        return $this->taxRate;
    }
    
    public function setTaxRate($rate) {
        if (is_numeric($rate) && $rate >= 0) {
            $this->taxRate = $rate;
            return true;
        }
        return false;
    }
}
?>