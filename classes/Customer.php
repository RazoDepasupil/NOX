<?php
class Customer extends User {
    private $customerID;
    protected $purchaseHistory;
    protected $loyaltyStatus;
    protected $orders = [];
    protected $purchases = [];
    
    public function __construct($userData = null) {
        parent::__construct($userData);
        
        if ($userData) {
            $this->customerID = $userData['customerID'] ?? $this->userID;
            $this->purchaseHistory = $userData['purchaseHistory'] ?? [];
            $this->loyaltyStatus = $userData['loyaltyStatus'] ?? 'regular';
            $this->orders = $userData['orders'] ?? [];
            $this->purchases = $userData['purchases'] ?? [];
        } else {
            $this->customerID = $this->userID;
            $this->purchaseHistory = [];
            $this->loyaltyStatus = 'regular';
            $this->orders = [];
            $this->purchases = [];
        }
        
        $this->role = 'customer';
    }
    
    public function register() {
        // Validate input data
        if (empty($this->username) || empty($this->email) || empty($this->password)) {
            return false;
        }
        
        // Check if email already exists
        $users = readJsonFile(USERS_FILE);
        
        foreach ($users as $userType => $userList) {
            foreach ($userList as $user) {
                if ($user['email'] === $this->email) {
                    return false;
                }
            }
        }
        
        
        // Hash password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Prepare user data for saving
        $userData = $this->toArray();
        
        // Add user to the JSON file
        $users['customers'][] = $userData;
        writeJsonFile(USERS_FILE, $users);
        
        return true;
    }
    
    // Browse products
    public function browse($category = null, $search = null) {
        $products = readJsonFile(PRODUCTS_FILE);
        $results = [];
        
        foreach ($products as $product) {
            $matchCategory = $category === null || $product['type'] === $category;
            $matchSearch = $search === null || stripos($product['productName'], $search) !== false;
            
            if ($matchCategory && $matchSearch) {
                $results[] = $product;
            }
        }
        
        return $results;
    }
    
    // Purchase a product
    public function purchase($productID, $quantity) {
        // Check if product exists and has enough stock
        $products = readJsonFile(PRODUCTS_FILE);
        $productIndex = -1;
        
        foreach ($products as $index => $product) {
            if ($product['productID'] === $productID) {
                $productIndex = $index;
                break;
            }
        }
        
        if ($productIndex === -1 || $products[$productIndex]['stockQuantity'] < $quantity) {
            return false;
        }
        
        // Update product stock
        $products[$productIndex]['stockQuantity'] -= $quantity;
        writeJsonFile(PRODUCTS_FILE, $products);
        
        // Add to purchase history
        $purchase = [
            'purchaseID' => generateUniqueId(),
            'productID' => $productID,
            'productName' => $products[$productIndex]['productName'],
            'quantity' => $quantity,
            'unitCost' => $products[$productIndex]['unitCost'],
            'total' => $quantity * $products[$productIndex]['unitCost'],
            'date' => date('Y-m-d H:i:s')
        ];
        
        // Update purchase history
        $this->purchaseHistory[] = $purchase;
        $this->updateProfile();
        
        return true;
    }
    
    // Verify product authenticity
    public function verifyProduct($serialNumber) {
        $products = readJsonFile(PRODUCTS_FILE);
        
        foreach ($products as $product) {
            if (isset($product['serialNumber']) && $product['serialNumber'] === $serialNumber) {
                return [
                    'verified' => true,
                    'product' => $product
                ];
            }
        }
        
        return [
            'verified' => false
        ];
    }
    
    // Add order
    public function addOrder(Orders $order) {
        $this->orders[] = $order->getOrderID();
        $this->updateProfile();
    }
    
    // Add purchase
    public function addPurchase(Purchases $purchase) {
        $this->purchases[] = $purchase->getPurchaseID();
        $this->updateProfile();
    }
    
    // Get customer orders
    public function getOrders() {
        $ordersData = readJsonFile(ORDERS_FILE);
        $customerOrders = [];
        
        foreach ($ordersData as $order) {
            if ($order['customer'] === $this->customerID) {
                $customerOrders[] = $order;
            }
        }
        
        return $customerOrders;
    }
    
    // Convert customer object to array
    public function toArray() {
        $userData = parent::toArray();
        
        return array_merge($userData, [
            'customerID' => $this->customerID,
            'purchaseHistory' => $this->purchaseHistory,
            'loyaltyStatus' => $this->loyaltyStatus,
            'orders' => $this->orders,
            'purchases' => $this->purchases
        ]);
    }
    
    // Getters and setters
    public function getCustomerID() {
        return $this->customerID;
    }
    
    public function getPurchaseHistory() {
        return $this->purchaseHistory;
    }
    
    public function getLoyaltyStatus() {
        return $this->loyaltyStatus;
    }
    
    public function setLoyaltyStatus($loyaltyStatus) {
        $this->loyaltyStatus = $loyaltyStatus;
    }
}