<?php
class ShoppingCart {
    private $cartID;
    private $userID;
    private $items = [];
    

    private $shippingID;
    private $shippingType;
    private $shippingCost;
    private $shippingRegionID;
    
    // Constructor
    public function __construct($userId = null) {
        $this->cartID = generateUniqueId();
        $this->userID = $userId ?? (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
        $this->loadCartItems();
    }
    
    // Load cart items for current user
    private function loadCartItems() {
        if (!$this->userID) {
            return;
        }
        
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
    }
    
    // Save cart items
    private function saveCartItems() {
        if (!$this->userID) {
            return false;
        }
        
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
    }
    
    // Add item to cart
    public function addCartItem($productId, $quantity = 1) {
        if (!$this->userID) {
            return false;
        }
        
        // Validate product
        $product = Product::getById($productId);
        if (!$product || $product->getStockQuantity() < $quantity) {
            return false;
        }
        
        // Check if product already in cart
        foreach ($this->items as &$item) {
            if ($item['productID'] === $productId) {
                $item['quantity'] += $quantity;
                return $this->saveCartItems();
            }
        }
        
        // Add new item to cart
        $this->items[] = [
            'cartItemID' => generateUniqueId(),
            'productID' => $productId,
            'quantity' => $quantity,
            'dateAdded' => date('Y-m-d H:i:s')
        ];
        
        return $this->saveCartItems();
    }
    
    // Update quantity of cart item
    public function updateQuantity($cartItemId, $quantity) {
        if (!$this->userID) {
            return false;
        }
        
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
    }
    
    // Remove item from cart
    public function removeCartItem($cartItemId) {
        if (!$this->userID) {
            return false;
        }
        
        foreach ($this->items as $key => $item) {
            if ($item['cartItemID'] === $cartItemId) {
                unset($this->items[$key]);
                $this->items = array_values($this->items); // Re-index array
                return $this->saveCartItems();
            }
        }
        
        return false;
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
                'total' => 0
            ];
        }
        
        $cartDetails = [
            'items' => [],
            'subtotal' => 0,
            'shipping' => [
                'type' => $this->shippingType,
                'cost' => $this->shippingCost
            ],
            'total' => 0
        ];
        
        $products = readJsonFile(PRODUCTS_FILE);
        
        foreach ($this->items as $item) {
            $productData = null;
            
            // Find product details
            foreach ($products as $product) {
                if ($product['productID'] === $item['productID']) {
                    $productData = $product;
                    break;
                }
            }
            
            if ($productData) {
                $itemTotal = $productData['unitCost'] * $item['quantity'];
                $cartDetails['subtotal'] += $itemTotal;
                
                $cartDetails['items'][] = [
                    'cartItemID' => $item['cartItemID'],
                    'productID' => $item['productID'],
                    'productName' => $productData['productName'],
                    'unitCost' => $productData['unitCost'],
                    'quantity' => $item['quantity'],
                    'itemTotal' => $itemTotal,
                    'imageUrl' => $productData['imageUrl'] ?? 'assets/images/product-placeholder.jpg',
                    'dateAdded' => $item['dateAdded']
                ];
            }
        }
        
        $cartDetails['total'] = $cartDetails['subtotal'] + $cartDetails['shipping']['cost'];
        
        return $cartDetails;
    }
    
    // Clear cart
    public function clearCart() {
        if (!$this->userID) {
            return false;
        }
        
        $this->items = [];
        return $this->saveCartItems();
    }
    
    // Checkout process
    public function checkout() {
        if (!$this->userID || empty($this->items)) {
            return false;
        }
        
        $cartDetails = $this->viewCartDetails();
        
        // Create new order
        $order = new Orders();
        $order->setCustomerId($this->userID);
        $order->setSubtotal($cartDetails['subtotal']);
        $order->setShippingCost($cartDetails['shipping']['cost']);
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
    }
    
    // Update shipping info
    public function updateShippingInfo($shippingType, $shippingRegionID = null) {
        if (!$this->userID) {
            return false;
        }
        
        $this->shippingID = generateUniqueId();
        $this->shippingType = $shippingType;
        $this->shippingRegionID = $shippingRegionID;
        
        // Calculate shipping cost based on type
        switch ($shippingType) {
            case 'standard':
                $this->shippingCost = 5.99;
                break;
            case 'express':
                $this->shippingCost = 14.99;
                break;
            case 'nextDay':
                $this->shippingCost = 24.99;
                break;
            case 'free':
            default:
                $this->shippingCost = 0;
                break;
        }
        
        return $this->saveCartItems();
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
}
?>