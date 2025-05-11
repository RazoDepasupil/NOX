<?php
/**
 * OrderDetails Class
 * 
 * Manages order details for the e-commerce system using JSON file storage
 */
class OrderDetails {
    private $orders_file;
    private $products_file;
    private $orders;
    private $products;
    
    private $id;
    private $order_id;
    private $product_id;
    private $quantity;
    private $price;
    private $subtotal;
    private $discount;
    private $tax;
    private $total;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->orders_file = dirname(__DIR__) . '/data/orders.json';
        $this->products_file = dirname(__DIR__) . '/data/products.json';
        $this->loadOrders();
        $this->loadProducts();
    }
    
    /**
     * Load orders from JSON file
     */
    private function loadOrders() {
        if (file_exists($this->orders_file)) {
            $json_data = file_get_contents($this->orders_file);
            $this->orders = json_decode($json_data, true);
            
            // If the file is empty or invalid, initialize with empty array
            if (!is_array($this->orders)) {
                $this->orders = [];
            }
        } else {
            // Create the file with empty array if it doesn't exist
            $this->orders = [];
            $this->saveOrders();
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
     * Save orders to JSON file
     */
    private function saveOrders() {
        $json_data = json_encode($this->orders, JSON_PRETTY_PRINT);
        file_put_contents($this->orders_file, $json_data);
    }
    
    /**
     * Generate a unique ID for a new order detail
     * 
     * @param int $order_id Order ID
     * @return int New detail ID
     */
    private function generateDetailId($order_id) {
        $maxId = 0;
        
        foreach ($this->orders as $key => $order) {
            if ($order['id'] == $order_id && isset($order['details'])) {
                foreach ($order['details'] as $detail) {
                    if ($detail['id'] > $maxId) {
                        $maxId = $detail['id'];
                    }
                }
            }
        }
        
        return $maxId + 1;
    }
    
    /**
     * Add a product to an order
     * 
     * @param int $order_id Order ID
     * @param int $product_id Product ID
     * @param int $quantity Quantity
     * @param float $discount Discount amount (optional)
     * @param float $tax Tax amount (optional)
     * @return int|bool Detail ID on success, false on failure
     */
    public function addProductToOrder($order_id, $product_id, $quantity, $discount = 0, $tax = 0) {
        // Find order index
        $orderIndex = null;
        foreach ($this->orders as $key => $order) {
            if ($order['id'] == $order_id) {
                $orderIndex = $key;
                break;
            }
        }
        
        // If order not found, return false
        if ($orderIndex === null) {
            return false;
        }
        
        // Find product and get price
        $productPrice = null;
        $productName = '';
        foreach ($this->products as $product) {
            if ($product['id'] == $product_id) {
                $productPrice = $product['price'];
                $productName = $product['name'];
                break;
            }
        }
        
        // If product not found, return false
        if ($productPrice === null) {
            return false;
        }
        
        // Check if product already exists in the order
        $detailExists = false;
        if (isset($this->orders[$orderIndex]['details'])) {
            foreach ($this->orders[$orderIndex]['details'] as $key => $detail) {
                if ($detail['product_id'] == $product_id) {
                    // Update quantity
                    $this->orders[$orderIndex]['details'][$key]['quantity'] += $quantity;
                    
                    // Recalculate totals
                    $subtotal = $this->orders[$orderIndex]['details'][$key]['quantity'] * $productPrice;
                    $this->orders[$orderIndex]['details'][$key]['subtotal'] = $subtotal;
                    $this->orders[$orderIndex]['details'][$key]['discount'] = $discount;
                    $this->orders[$orderIndex]['details'][$key]['tax'] = $tax;
                    $this->orders[$orderIndex]['details'][$key]['total'] = $subtotal - $discount + $tax;
                    
                    $detailExists = true;
                    $this->id = $detail['id'];
                    break;
                }
            }
        } else {
            // Initialize details array if it doesn't exist
            $this->orders[$orderIndex]['details'] = [];
        }
        
        if (!$detailExists) {
            // Generate new detail ID
            $detailId = $this->generateDetailId($order_id);
            
            // Calculate totals
            $subtotal = $quantity * $productPrice;
            $total = $subtotal - $discount + $tax;
            
            // Create new detail
            $detail = [
                'id' => $detailId,
                'product_id' => $product_id,
                'product_name' => $productName,
                'quantity' => $quantity,
                'price' => $productPrice,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total
            ];
            
            // Add to order
            $this->orders[$orderIndex]['details'][] = $detail;
            $this->id = $detailId;
        }
        
        // Update order totals
        $this->updateOrderTotals($order_id);
        
        // Save orders
        $this->saveOrders();
        
        return $this->id;
    }
    
    /**
     * Remove a product from an order
     * 
     * @param int $order_id Order ID
     * @param int $product_id Product ID
     * @return bool Success or failure
     */
    public function removeProductFromOrder($order_id, $product_id) {
        // Find order index
        $orderIndex = null;
        foreach ($this->orders as $key => $order) {
            if ($order['id'] == $order_id) {
                $orderIndex = $key;
                break;
            }
        }
        
        // If order not found, return false
        if ($orderIndex === null) {
            return false;
        }
        
        // Find detail index
        $detailIndex = null;
        if (isset($this->orders[$orderIndex]['details'])) {
            foreach ($this->orders[$orderIndex]['details'] as $key => $detail) {
                if ($detail['product_id'] == $product_id) {
                    $detailIndex = $key;
                    break;
                }
            }
        }
        
        // If detail not found, return false
        if ($detailIndex === null) {
            return false;
        }
        
        // Remove detail
        array_splice($this->orders[$orderIndex]['details'], $detailIndex, 1);
        
        // Update order totals
        $this->updateOrderTotals($order_id);
        
        // Save orders
        $this->saveOrders();
        
        return true;
    }
    
    /**
     * Update order totals
     * 
     * @param int $order_id Order ID
     * @return bool Success or failure
     */
    public function updateOrderTotals($order_id) {
        // Find order index
        $orderIndex = null;
        foreach ($this->orders as $key => $order) {
            if ($order['id'] == $order_id) {
                $orderIndex = $key;
                break;
            }
        }
        
        // If order not found, return false
        if ($orderIndex === null) {
            return false;
        }
        
        // Calculate totals
        $subtotal = 0;
        $discount = 0;
        $tax = 0;
        $total = 0;
        
        if (isset($this->orders[$orderIndex]['details'])) {
            foreach ($this->orders[$orderIndex]['details'] as $detail) {
                $subtotal += $detail['subtotal'];
                $discount += $detail['discount'];
                $tax += $detail['tax'];
                $total += $detail['total'];
            }
        }
        
        // Update order totals
        $this->orders[$orderIndex]['subtotal'] = $subtotal;
        $this->orders[$orderIndex]['discount'] = $discount;
        $this->orders[$orderIndex]['tax'] = $tax;
        $this->orders[$orderIndex]['total'] = $total;
        
        // Update order items count
        $itemCount = 0;
        if (isset($this->orders[$orderIndex]['details'])) {
            foreach ($this->orders[$orderIndex]['details'] as $detail) {
                $itemCount += $detail['quantity'];
            }
        }
        $this->orders[$orderIndex]['item_count'] = $itemCount;
        
        return true;
    }
    
    /**
     * Update product quantity in an order
     * 
     * @param int $order_id Order ID
     * @param int $product_id Product ID
     * @param int $quantity New quantity
     * @return bool Success or failure
     */
    public function updateProductQuantity($order_id, $product_id, $quantity) {
        // Find order index
        $orderIndex = null;
        foreach ($this->orders as $key => $order) {
            if ($order['id'] == $order_id) {
                $orderIndex = $key;
                break;
            }
        }
        
        // If order not found, return false
        if ($orderIndex === null) {
            return false;
        }
        
        // Find detail index and current price
        $detailIndex = null;
        $price = 0;
        if (isset($this->orders[$orderIndex]['details'])) {
            foreach ($this->orders[$orderIndex]['details'] as $key => $detail) {
                if ($detail['product_id'] == $product_id) {
                    $detailIndex = $key;
                    $price = $detail['price'];
                    break;
                }
            }
        }
        
        // If detail not found, return false
        if ($detailIndex === null) {
            return false;
        }
        
        // If quantity is 0 or less, remove the product
        if ($quantity <= 0) {
            return $this->removeProductFromOrder($order_id, $product_id);
        }
        
        // Update quantity
        $this->orders[$orderIndex]['details'][$detailIndex]['quantity'] = $quantity;
        
        // Recalculate totals
        $subtotal = $quantity * $price;
        $discount = $this->orders[$orderIndex]['details'][$detailIndex]['discount'];
        $tax = $this->orders[$orderIndex]['details'][$detailIndex]['tax'];
        $total = $subtotal - $discount + $tax;
        
        $this->orders[$orderIndex]['details'][$detailIndex]['subtotal'] = $subtotal;
        $this->orders[$orderIndex]['details'][$detailIndex]['total'] = $total;
        
        // Update order totals
        $this->updateOrderTotals($order_id);
        
        // Save orders
        $this->saveOrders();
        
        return true;
    }
    
    /**
     * Get order details
     * 
     * @param int $order_id Order ID
     * @return array|bool Order details or false if not found
     */
    public function getOrderDetails($order_id) {
        foreach ($this->orders as $order) {
            if ($order['id'] == $order_id) {
                return isset($order['details']) ? $order['details'] : [];
            }
        }
        
        return false;
    }
    
    /**
     * Get order detail by ID
     * 
     * @param int $order_id Order ID
     * @param int $detail_id Detail ID
     * @return array|bool Order detail or false if not found
     */
    public function getDetail($order_id, $detail_id) {
        foreach ($this->orders as $order) {
            if ($order['id'] == $order_id && isset($order['details'])) {
                foreach ($order['details'] as $detail) {
                    if ($detail['id'] == $detail_id) {
                        return $detail;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if a product exists in an order
     * 
     * @param int $order_id Order ID
     * @param int $product_id Product ID
     * @return bool True if exists, false otherwise
     */
    public function productExists($order_id, $product_id) {
        foreach ($this->orders as $order) {
            if ($order['id'] == $order_id && isset($order['details'])) {
                foreach ($order['details'] as $detail) {
                    if ($detail['product_id'] == $product_id) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get product quantity in an order
     * 
     * @param int $order_id Order ID
     * @param int $product_id Product ID
     * @return int|bool Quantity or false if not found
     */
    public function getProductQuantity($order_id, $product_id) {
        foreach ($this->orders as $order) {
            if ($order['id'] == $order_id && isset($order['details'])) {
                foreach ($order['details'] as $detail) {
                    if ($detail['product_id'] == $product_id) {
                        return $detail['quantity'];
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get order ID
     * 
     * @return int Order ID
     */
    public function getOrderId() {
        return $this->order_id;
    }
}