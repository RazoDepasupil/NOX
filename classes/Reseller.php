<?php
class Reseller {
    protected $resellerID;
    protected $companyName;
    protected $contactPerson;
    protected $email;
    protected $phone;
    protected $address;
    protected $taxID;
    protected $discountRate;
    protected $status;
    protected $products = [];
    protected $orders = [];
    protected $paymentHistory = [];
    protected $createdAt;
    protected $updatedAt;
    
    // Constructor
    public function __construct($resellerData = null) {
        if ($resellerData) {
            $this->resellerID = $resellerData['resellerID'] ?? generateUniqueId();
            $this->companyName = $resellerData['companyName'] ?? '';
            $this->contactPerson = $resellerData['contactPerson'] ?? '';
            $this->email = $resellerData['email'] ?? '';
            $this->phone = $resellerData['phone'] ?? '';
            $this->address = $resellerData['address'] ?? '';
            $this->taxID = $resellerData['taxID'] ?? '';
            $this->discountRate = $resellerData['discountRate'] ?? 0;
            $this->status = $resellerData['status'] ?? 'active';
            $this->products = $resellerData['products'] ?? [];
            $this->orders = $resellerData['orders'] ?? [];
            $this->paymentHistory = $resellerData['paymentHistory'] ?? [];
            $this->createdAt = $resellerData['createdAt'] ?? date('Y-m-d H:i:s');
            $this->updatedAt = $resellerData['updatedAt'] ?? date('Y-m-d H:i:s');
        } else {
            $this->resellerID = generateUniqueId();
            $this->status = 'active';
            $this->createdAt = date('Y-m-d H:i:s');
            $this->updatedAt = date('Y-m-d H:i:s');
        }
    }
    
    // Save reseller data
    public function save() {
        $resellers = readJsonFile(RESELLERS_FILE);
        $found = false;
        
        // Check if reseller already exists
        foreach ($resellers as $key => $reseller) {
            if ($reseller['resellerID'] === $this->resellerID) {
                $resellers[$key] = $this->toArray();
                $found = true;
                break;
            }
        }
        
        // Add new reseller if not found
        if (!$found) {
            $resellers[] = $this->toArray();
        }
        
        return writeJsonFile(RESELLERS_FILE, $resellers);
    }
    
    // Delete reseller
    public function delete() {
        $resellers = readJsonFile(RESELLERS_FILE);
        
        foreach ($resellers as $key => $reseller) {
            if ($reseller['resellerID'] === $this->resellerID) {
                unset($resellers[$key]);
                break;
            }
        }
        
        $resellers = array_values($resellers); // Re-index array
        return writeJsonFile(RESELLERS_FILE, $resellers);
    }
    
    // Add product to reseller's catalog
    public function addProduct($productID, $resellerPrice) {
        if (!isset($this->products[$productID])) {
            $this->products[$productID] = [
                'productID' => $productID,
                'resellerPrice' => $resellerPrice,
                'addedAt' => date('Y-m-d H:i:s')
            ];
            $this->updatedAt = date('Y-m-d H:i:s');
            return $this->save();
        }
        return false;
    }
    
    // Remove product from reseller's catalog
    public function removeProduct($productID) {
        if (isset($this->products[$productID])) {
            unset($this->products[$productID]);
            $this->updatedAt = date('Y-m-d H:i:s');
            return $this->save();
        }
        return false;
    }
    
    // Add order to reseller's history
    public function addOrder($orderID) {
        if (!in_array($orderID, $this->orders)) {
            $this->orders[] = $orderID;
            $this->updatedAt = date('Y-m-d H:i:s');
            return $this->save();
        }
        return false;
    }
    
    // Add payment to history
    public function addPayment($amount, $paymentMethod, $reference) {
        $payment = [
            'amount' => $amount,
            'paymentMethod' => $paymentMethod,
            'reference' => $reference,
            'date' => date('Y-m-d H:i:s')
        ];
        
        $this->paymentHistory[] = $payment;
        $this->updatedAt = date('Y-m-d H:i:s');
        return $this->save();
    }
    
    // Calculate total sales
    public function calculateTotalSales() {
        $total = 0;
        $orders = readJsonFile(ORDERS_FILE);
        
        foreach ($this->orders as $orderID) {
            foreach ($orders as $order) {
                if ($order['orderID'] === $orderID) {
                    $total += $order['totalAmount'];
                    break;
                }
            }
        }
        
        return $total;
    }
    
    // Get reseller's products
    public function getProducts() {
        $products = [];
        $allProducts = readJsonFile(PRODUCTS_FILE);
        
        foreach ($this->products as $productID => $resellerProduct) {
            foreach ($allProducts as $product) {
                if ($product['productID'] === $productID) {
                    $products[] = array_merge($product, [
                        'resellerPrice' => $resellerProduct['resellerPrice']
                    ]);
                    break;
                }
            }
        }
        
        return $products;
    }
    
    // Get reseller's orders
    public function getOrders() {
        $orders = [];
        $allOrders = readJsonFile(ORDERS_FILE);
        
        foreach ($this->orders as $orderID) {
            foreach ($allOrders as $order) {
                if ($order['orderID'] === $orderID) {
                    $orders[] = $order;
                    break;
                }
            }
        }
        
        return $orders;
    }
    
    // Load reseller by ID
    public static function getById($resellerId) {
        $resellers = readJsonFile(RESELLERS_FILE);
        
        foreach ($resellers as $reseller) {
            if ($reseller['resellerID'] === $resellerId) {
                return new Reseller($reseller);
            }
        }
        
        return null;
    }
    
    // Get all resellers
    public static function getAll() {
        $resellers = readJsonFile(RESELLERS_FILE);
        $resellerObjects = [];
        
        foreach ($resellers as $reseller) {
            $resellerObjects[] = new Reseller($reseller);
        }
        
        return $resellerObjects;
    }
    
    // Get resellers by status
    public static function getByStatus($status) {
        $resellers = readJsonFile(RESELLERS_FILE);
        $resellerObjects = [];
        
        foreach ($resellers as $reseller) {
            if ($reseller['status'] === $status) {
                $resellerObjects[] = new Reseller($reseller);
            }
        }
        
        return $resellerObjects;
    }
    
    // Search resellers
    public static function search($keyword) {
        $resellers = readJsonFile(RESELLERS_FILE);
        $resellerObjects = [];
        
        foreach ($resellers as $reseller) {
            if (stripos($reseller['companyName'], $keyword) !== false || 
                stripos($reseller['contactPerson'], $keyword) !== false ||
                stripos($reseller['email'], $keyword) !== false) {
                $resellerObjects[] = new Reseller($reseller);
            }
        }
        
        return $resellerObjects;
    }
    
    // Convert reseller object to array
    public function toArray() {
        return [
            'resellerID' => $this->resellerID,
            'companyName' => $this->companyName,
            'contactPerson' => $this->contactPerson,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'taxID' => $this->taxID,
            'discountRate' => $this->discountRate,
            'status' => $this->status,
            'products' => $this->products,
            'orders' => $this->orders,
            'paymentHistory' => $this->paymentHistory,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
    
    // Getters and setters
    public function getResellerID() {
        return $this->resellerID;
    }
    
    public function getCompanyName() {
        return $this->companyName;
    }
    
    public function setCompanyName($companyName) {
        $this->companyName = $companyName;
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    public function getContactPerson() {
        return $this->contactPerson;
    }
    
    public function setContactPerson($contactPerson) {
        $this->contactPerson = $contactPerson;
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function setEmail($email) {
        $this->email = $email;
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    public function getPhone() {
        return $this->phone;
    }
    
    public function setPhone($phone) {
        $this->phone = $phone;
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    public function getAddress() {
        return $this->address;
    }
    
    public function setAddress($address) {
        $this->address = $address;
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    public function getTaxID() {
        return $this->taxID;
    }
    
    public function setTaxID($taxID) {
        $this->taxID = $taxID;
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    public function getDiscountRate() {
        return $this->discountRate;
    }
    
    public function setDiscountRate($discountRate) {
        $this->discountRate = $discountRate;
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function setStatus($status) {
        $this->status = $status;
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    public function getCreatedAt() {
        return $this->createdAt;
    }
    
    public function getUpdatedAt() {
        return $this->updatedAt;
    }
}
?>
