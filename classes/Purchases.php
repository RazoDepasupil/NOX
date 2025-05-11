<?php
class Purchases {
    protected $purchaseID;
    protected $productID;
    protected $quantity;
    protected $unitCost;
    protected $totalCost;
    protected $purchaseDate;
    protected $supplier;
    protected $status;
    protected $notes;
    protected $product;
    
    // Constructor
    public function __construct($purchaseData = null) {
        if ($purchaseData) {
            $this->purchaseID = $purchaseData['purchaseID'] ?? generateUniqueId();
            $this->productID = $purchaseData['productID'] ?? '';
            $this->quantity = $purchaseData['quantity'] ?? 0;
            $this->unitCost = $purchaseData['unitCost'] ?? 0;
            $this->totalCost = $purchaseData['totalCost'] ?? 0;
            $this->purchaseDate = $purchaseData['purchaseDate'] ?? date('Y-m-d H:i:s');
            $this->supplier = $purchaseData['supplier'] ?? '';
            $this->status = $purchaseData['status'] ?? 'pending';
            $this->notes = $purchaseData['notes'] ?? '';
            
            // Load associated product
            if ($this->productID) {
                $this->product = Product::getById($this->productID);
            }
        } else {
            $this->purchaseID = generateUniqueId();
            $this->purchaseDate = date('Y-m-d H:i:s');
            $this->status = 'pending';
        }
    }
    
    // Save purchase data
    public function save() {
        $purchases = readJsonFile(PURCHASES_FILE);
        $found = false;
        
        // Check if purchase already exists
        foreach ($purchases as $key => $purchase) {
            if ($purchase['purchaseID'] === $this->purchaseID) {
                $purchases[$key] = $this->toArray();
                $found = true;
                break;
            }
        }
        
        // Add new purchase if not found
        if (!$found) {
            $purchases[] = $this->toArray();
        }
        
        // Update product stock if purchase is completed
        if ($this->status === 'completed' && $this->product) {
            $this->product->setStockQuantity($this->product->getStockQuantity() + $this->quantity);
            $this->product->save();
        }
        
        return writeJsonFile(PURCHASES_FILE, $purchases);
    }
    
    // Delete purchase
    public function delete() {
        $purchases = readJsonFile(PURCHASES_FILE);
        
        foreach ($purchases as $key => $purchase) {
            if ($purchase['purchaseID'] === $this->purchaseID) {
                unset($purchases[$key]);
                break;
            }
        }
        
        $purchases = array_values($purchases); // Re-index array
        return writeJsonFile(PURCHASES_FILE, $purchases);
    }
    
    // Complete purchase
    public function complete() {
        if ($this->status !== 'completed') {
            $this->status = 'completed';
            $this->save();
            return true;
        }
        return false;
    }
    
    // Cancel purchase
    public function cancel() {
        if ($this->status !== 'cancelled') {
            $this->status = 'cancelled';
            $this->save();
            return true;
        }
        return false;
    }
    
    // Calculate total cost
    public function calculateTotalCost() {
        $this->totalCost = $this->quantity * $this->unitCost;
        return $this->totalCost;
    }
    
    // Get purchase details
    public function getPurchaseDetails() {
        return $this->toArray();
    }
    
    // Load purchase by ID
    public static function getById($purchaseId) {
        $purchases = readJsonFile(PURCHASES_FILE);
        
        foreach ($purchases as $purchase) {
            if ($purchase['purchaseID'] === $purchaseId) {
                return new Purchases($purchase);
            }
        }
        
        return null;
    }
    
    // Get all purchases
    public static function getAll() {
        $purchases = readJsonFile(PURCHASES_FILE);
        $purchaseObjects = [];
        
        foreach ($purchases as $purchase) {
            $purchaseObjects[] = new Purchases($purchase);
        }
        
        return $purchaseObjects;
    }
    
    // Get purchases by status
    public static function getByStatus($status) {
        $purchases = readJsonFile(PURCHASES_FILE);
        $purchaseObjects = [];
        
        foreach ($purchases as $purchase) {
            if ($purchase['status'] === $status) {
                $purchaseObjects[] = new Purchases($purchase);
            }
        }
        
        return $purchaseObjects;
    }
    
    // Get purchases by date range
    public static function getByDateRange($startDate, $endDate) {
        $purchases = readJsonFile(PURCHASES_FILE);
        $purchaseObjects = [];
        
        foreach ($purchases as $purchase) {
            $purchaseDate = strtotime($purchase['purchaseDate']);
            if ($purchaseDate >= strtotime($startDate) && $purchaseDate <= strtotime($endDate)) {
                $purchaseObjects[] = new Purchases($purchase);
            }
        }
        
        return $purchaseObjects;
    }
    
    // Convert purchase object to array
    public function toArray() {
        return [
            'purchaseID' => $this->purchaseID,
            'productID' => $this->productID,
            'quantity' => $this->quantity,
            'unitCost' => $this->unitCost,
            'totalCost' => $this->totalCost,
            'purchaseDate' => $this->purchaseDate,
            'supplier' => $this->supplier,
            'status' => $this->status,
            'notes' => $this->notes
        ];
    }
    
    // Getters and setters
    public function getPurchaseID() {
        return $this->purchaseID;
    }
    
    public function getProductID() {
        return $this->productID;
    }
    
    public function setProductID($productID) {
        $this->productID = $productID;
        $this->product = Product::getById($productID);
    }
    
    public function getQuantity() {
        return $this->quantity;
    }
    
    public function setQuantity($quantity) {
        $this->quantity = $quantity;
        $this->calculateTotalCost();
    }
    
    public function getUnitCost() {
        return $this->unitCost;
    }
    
    public function setUnitCost($unitCost) {
        $this->unitCost = $unitCost;
        $this->calculateTotalCost();
    }
    
    public function getTotalCost() {
        return $this->totalCost;
    }
    
    public function getPurchaseDate() {
        return $this->purchaseDate;
    }
    
    public function setPurchaseDate($purchaseDate) {
        $this->purchaseDate = $purchaseDate;
    }
    
    public function getSupplier() {
        return $this->supplier;
    }
    
    public function setSupplier($supplier) {
        $this->supplier = $supplier;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function setStatus($status) {
        $this->status = $status;
    }
    
    public function getNotes() {
        return $this->notes;
    }
    
    public function setNotes($notes) {
        $this->notes = $notes;
    }
    
    public function getProduct() {
        return $this->product;
    }
}
?>
