<?php
/**
 * Inventory Class
 * 
 * Manages inventory for the e-commerce system using JSON file storage
 */
class Inventory {
    private $inventory_file;
    private $products_file;
    private $inventory;
    private $products;
    private $log_file;
    private $backup_dir;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->inventory_file = dirname(__DIR__) . '/data/inventory.json';
        $this->products_file = dirname(__DIR__) . '/data/products.json';
        $this->log_file = dirname(__DIR__) . '/logs/inventory.log';
        $this->backup_dir = dirname(__DIR__) . '/data/backups/';
        
        // Create necessary directories
        $this->createDirectories();
        
        $this->loadInventory();
        $this->loadProducts();
    }
    
    /**
     * Create necessary directories if they don't exist
     */
    private function createDirectories() {
        $dirs = [
            dirname($this->inventory_file),
            dirname($this->products_file),
            dirname($this->log_file),
            $this->backup_dir
        ];
        
        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Log an error message
     * 
     * @param string $message Error message
     */
    private function logError($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] ERROR: $message\n";
        file_put_contents($this->log_file, $log_message, FILE_APPEND);
    }
    
    /**
     * Create a backup of inventory data
     */
    private function createBackup() {
        $timestamp = date('Y-m-d_H-i-s');
        $backup_file = $this->backup_dir . "inventory_backup_{$timestamp}.json";
        
        if (file_exists($this->inventory_file)) {
            copy($this->inventory_file, $backup_file);
        }
    }
    
    /**
     * Load inventory from JSON file
     */
    private function loadInventory() {
        if (file_exists($this->inventory_file)) {
            $json_data = file_get_contents($this->inventory_file);
            $this->inventory = json_decode($json_data, true);
            
            // If the file is empty or invalid, initialize with empty array
            if (!is_array($this->inventory)) {
                $this->inventory = [];
            }
        } else {
            // Create the file with empty array if it doesn't exist
            $this->inventory = [];
            $this->saveInventory();
        }
    }
    
    /**
     * Load products from JSON file
     */
    private function loadProducts() {
        if (file_exists($this->products_file)) {
            $json_data = file_get_contents($this->products_file);
            $this->products = json_decode($json_data, true);
            
            // If the file is empty or invalid, initialize with empty array
            if (!is_array($this->products)) {
                $this->products = [];
            }
        } else {
            // Create the file with empty array if it doesn't exist
            $this->products = [];
            file_put_contents($this->products_file, json_encode($this->products, JSON_PRETTY_PRINT));
        }
    }
    
    /**
     * Save inventory to JSON file
     */
    private function saveInventory() {
        $json_data = json_encode($this->inventory, JSON_PRETTY_PRINT);
        file_put_contents($this->inventory_file, $json_data);
    }
    
    /**
     * Update inventory for a product with validation
     * 
     * @param int $product_id Product ID
     * @param int $quantity Quantity to add (positive) or remove (negative)
     * @param string $location Location in warehouse
     * @param string $note Note about the update
     * @return bool Success or failure
     */
    public function updateStock($product_id, $quantity, $location = null, $note = '') {
        // Validate inputs
        if (!is_numeric($product_id) || $product_id <= 0) {
            $this->logError("Invalid product ID: $product_id");
            return false;
        }
        
        if (!is_numeric($quantity)) {
            $this->logError("Invalid quantity: $quantity");
            return false;
        }
        
        // Check if product exists
        $product_exists = false;
        foreach ($this->products as $product) {
            if ($product['id'] == $product_id) {
                $product_exists = true;
                break;
            }
        }
        
        if (!$product_exists) {
            $this->logError("Product not found: $product_id");
            return false;
        }
        
        // Create backup before update
        $this->createBackup();
        
        // Find if product already exists in inventory
        $found = false;
        foreach ($this->inventory as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $found = true;
                
                // Update quantity
                $this->inventory[$key]['quantity'] += $quantity;
                
                // Update location if provided
                if ($location !== null) {
                    $this->inventory[$key]['location'] = $location;
                }
                
                // Add movement record
                $this->inventory[$key]['movements'][] = [
                    'date' => date('Y-m-d H:i:s'),
                    'quantity' => $quantity,
                    'new_total' => $this->inventory[$key]['quantity'],
                    'note' => $note
                ];
                
                break;
            }
        }
        
        if (!$found) {
            // Add new inventory item
            $this->inventory[] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'location' => $location ?? 'Default',
                'last_updated' => date('Y-m-d H:i:s'),
                'movements' => [
                    [
                        'date' => date('Y-m-d H:i:s'),
                        'quantity' => $quantity,
                        'new_total' => $quantity,
                        'note' => $note
                    ]
                ]
            ];
        } else {
            // Update last_updated timestamp
            foreach ($this->inventory as $key => $item) {
                if ($item['product_id'] == $product_id) {
                    $this->inventory[$key]['last_updated'] = date('Y-m-d H:i:s');
                    break;
                }
            }
        }
        
        // Save inventory
        $this->saveInventory();
        
        return true;
    }
    
    /**
     * Batch update inventory for multiple products
     * 
     * @param array $updates Array of updates [product_id => quantity]
     * @param string $location Location in warehouse
     * @param string $note Note about the update
     * @return array Results of each update
     */
    public function batchUpdateStock($updates, $location = null, $note = '') {
        $results = [];
        
        foreach ($updates as $product_id => $quantity) {
            $results[$product_id] = $this->updateStock($product_id, $quantity, $location, $note);
        }
        
        return $results;
    }
    
    /**
     * Get inventory for a product
     * 
     * @param int $product_id Product ID
     * @return array|bool Inventory data or false if not found
     */
    public function getProductInventory($product_id) {
        foreach ($this->inventory as $item) {
            if ($item['product_id'] == $product_id) {
                return $item;
            }
        }
        
        return false;
    }
    
    /**
     * Get current stock quantity for a product
     * 
     * @param int $product_id Product ID
     * @return int|bool Quantity or false if product not found
     */
    public function getStockQuantity($product_id) {
        foreach ($this->inventory as $item) {
            if ($item['product_id'] == $product_id) {
                return $item['quantity'];
            }
        }
        
        return false;
    }
    
    /**
     * Check if a product is in stock (quantity > 0)
     * 
     * @param int $product_id Product ID
     * @return bool True if in stock, false otherwise
     */
    public function isInStock($product_id) {
        $quantity = $this->getStockQuantity($product_id);
        
        if ($quantity === false || $quantity <= 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if there's enough stock for a product
     * 
     * @param int $product_id Product ID
     * @param int $quantity Quantity needed
     * @return bool True if enough stock, false otherwise
     */
    public function checkStock($product_id, $quantity) {
        $current_quantity = $this->getStockQuantity($product_id);
        
        if ($current_quantity === false || $current_quantity < $quantity) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get low stock products (below threshold)
     * 
     * @param int $threshold Low stock threshold
     * @return array Products with low stock
     */
    public function getLowStock($threshold = 5) {
        $low_stock = [];
        
        foreach ($this->inventory as $item) {
            if ($item['quantity'] <= $threshold) {
                // Get product details
                $product = null;
                foreach ($this->products as $p) {
                    if ($p['id'] == $item['product_id']) {
                        $product = $p;
                        break;
                    }
                }
                
                if ($product) {
                    $low_stock[] = [
                        'product_id' => $item['product_id'],
                        'product_name' => $product['name'],
                        'quantity' => $item['quantity'],
                        'location' => $item['location']
                    ];
                }
            }
        }
        
        return $low_stock;
    }
    
    /**
     * Get out of stock products (quantity = 0)
     * 
     * @return array Products out of stock
     */
    public function getOutOfStock() {
        return $this->getLowStock(0);
    }
    
    /**
     * Get all inventory with product details
     * 
     * @return array Inventory with product details
     */
    public function getAllInventory() {
        $result = [];
        
        foreach ($this->inventory as $item) {
            // Get product details
            $product = null;
            foreach ($this->products as $p) {
                if ($p['id'] == $item['product_id']) {
                    $product = $p;
                    break;
                }
            }
            
            if ($product) {
                $result[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $product['name'],
                    'quantity' => $item['quantity'],
                    'location' => $item['location'],
                    'last_updated' => $item['last_updated']
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Get inventory movement history for a product
     * 
     * @param int $product_id Product ID
     * @return array|bool Movement history or false if not found
     */
    public function getMovementHistory($product_id) {
        foreach ($this->inventory as $item) {
            if ($item['product_id'] == $product_id) {
                return $item['movements'];
            }
        }
        
        return false;
    }
    
    /**
     * Get total inventory value
     * 
     * @return float Total value
     */
    public function getTotalValue() {
        $total = 0;
        
        foreach ($this->inventory as $item) {
            // Get product price
            $price = 0;
            foreach ($this->products as $product) {
                if ($product['id'] == $item['product_id']) {
                    $price = $product['price'];
                    break;
                }
            }
            
            $total += $item['quantity'] * $price;
        }
        
        return $total;
    }
    
    /**
     * Get inventory value for a specific product
     * 
     * @param int $product_id Product ID
     * @return float|bool Product inventory value or false if not found
     */
    public function getProductValue($product_id) {
        // Get product quantity
        $quantity = $this->getStockQuantity($product_id);
        
        if ($quantity === false) {
            return false;
        }
        
        // Get product price
        $price = 0;
        foreach ($this->products as $product) {
            if ($product['id'] == $product_id) {
                $price = $product['price'];
                break;
            }
        }
        
        return $quantity * $price;
    }
    
    /**
     * Search inventory by product name or ID
     * 
     * @param string $query Search query
     * @return array Matching inventory items
     */
    public function searchInventory($query) {
        $results = [];
        $query = strtolower($query);
        
        foreach ($this->inventory as $item) {
            // Get product details
            $product = null;
            foreach ($this->products as $p) {
                if ($p['id'] == $item['product_id']) {
                    $product = $p;
                    break;
                }
            }
            
            if ($product && (
                strpos(strtolower($product['name']), $query) !== false ||
                strpos((string)$item['product_id'], $query) !== false
            )) {
                $results[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $product['name'],
                    'quantity' => $item['quantity'],
                    'location' => $item['location'],
                    'last_updated' => $item['last_updated']
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Get inventory alerts for low stock
     * 
     * @param int $threshold Low stock threshold
     * @return array Alerts for low stock items
     */
    public function getInventoryAlerts($threshold = 5) {
        $alerts = [];
        
        foreach ($this->inventory as $item) {
            if ($item['quantity'] <= $threshold) {
                // Get product details
                $product = null;
                foreach ($this->products as $p) {
                    if ($p['id'] == $item['product_id']) {
                        $product = $p;
                        break;
                    }
                }
                
                if ($product) {
                    $alerts[] = [
                        'product_id' => $item['product_id'],
                        'product_name' => $product['name'],
                        'current_quantity' => $item['quantity'],
                        'threshold' => $threshold,
                        'location' => $item['location'],
                        'last_updated' => $item['last_updated']
                    ];
                }
            }
        }
        
        return $alerts;
    }
    
    /**
     * Get inventory statistics
     * 
     * @return array Inventory statistics
     */
    public function getInventoryStats() {
        $stats = [
            'total_products' => count($this->inventory),
            'total_value' => $this->getTotalValue(),
            'low_stock_items' => count($this->getLowStock()),
            'out_of_stock_items' => count($this->getOutOfStock()),
            'total_quantity' => 0
        ];
        
        foreach ($this->inventory as $item) {
            $stats['total_quantity'] += $item['quantity'];
        }
        
        return $stats;
    }
}