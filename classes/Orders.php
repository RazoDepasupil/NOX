<?php
class Orders {
    private $orderID;
    private $customerID;
    private $orderDate;
    private $status;
    private $paymentStatus;
    private $paymentMethod;
    private $shippingAddress;
    private $items = [];
    private $subtotal;
    private $tax;
    private $shippingCost;
    private $total;
    
    public function __construct() {
        $this->orderDate = date('Y-m-d H:i:s');
        $this->status = 'pending';
        $this->paymentStatus = 'pending';
        $this->orderID = uniqid('ORD');
    }
    
    public function getOrderID() {
        return $this->orderID;
    }
    
    public function getCustomerId() {
        return $this->customerID;
    }
    
    public function setCustomerId($customerID) {
        $this->customerID = $customerID;
    }
    
    public function getOrderDate() {
        return $this->orderDate;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function getPaymentStatus() {
        return $this->paymentStatus;
    }
    
    public function getItems() {
        return $this->items;
    }
    
    public function setItems($items) {
        $this->items = $items;
    }
    
    public function getSubtotal() {
        return $this->subtotal;
    }
    
    public function setSubtotal($subtotal) {
        $this->subtotal = $subtotal;
    }
    
    public function getTax() {
        return $this->tax;
    }
    
    public function setTax($tax) {
        $this->tax = $tax;
    }
    
    public function getShippingCost() {
        return $this->shippingCost;
    }
    
    public function setShippingCost($shippingCost) {
        $this->shippingCost = $shippingCost;
    }
    
    public function getTotal() {
        return $this->total;
    }
    
    public function setTotal($total) {
        $this->total = $total;
    }
    
    public function setShippingAddress($address) {
        $this->shippingAddress = $address;
    }
    
    public function getShippingAddress() {
        return $this->shippingAddress;
    }
    
    /**
     * Place the order
     * 
     * @return bool True if successful, false otherwise
     */
    public function placeOrder() {
        try {
            // Basic validation
            if (empty($this->customerID) || $this->subtotal <= 0 || empty($this->items)) {
                error_log("Order validation failed: " . 
                    "CustomerID: {$this->customerID}, " . 
                    "Subtotal: {$this->subtotal}, " . 
                    "Items: " . (empty($this->items) ? "empty" : count($this->items))
                );
                return false;
            }
            
            // Create order data
            $orderData = [
                'orderID' => $this->orderID,
                'customerID' => $this->customerID,
                'orderDate' => $this->orderDate,
                'status' => $this->status,
                'paymentStatus' => $this->paymentStatus,
                'paymentMethod' => $this->paymentMethod,
                'shippingAddress' => $this->shippingAddress,
                'subtotal' => $this->subtotal,
                'tax' => $this->tax,
                'shippingCost' => $this->shippingCost,
                'total' => $this->total,
                'items' => $this->items
            ];
            
            // Read existing orders
            $orders = readJsonFile(ORDERS_FILE);
            if (!is_array($orders)) {
                $orders = [];
            }
            
            // Add new order
            $orders[] = $orderData;
            
            // Save orders
            if (!writeJsonFile(ORDERS_FILE, $orders)) {
                throw new Exception("Failed to save order");
            }
            
            // Update product inventory
            $products = readJsonFile(PRODUCTS_FILE);
            if (!is_array($products)) {
                $products = [];
            }
            
            foreach ($this->items as $item) {
                foreach ($products as &$product) {
                    if ($product['productID'] == $item['productID']) {
                        $product['stock_quantity'] -= $item['quantity'];
                        break;
                    }
                }
            }
            writeJsonFile(PRODUCTS_FILE, $products);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error placing order: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process payment for the order
     * 
     * @param string $method Payment method
     * @param array $details Payment details
     * @return bool True if successful, false otherwise
     */
    public function processPayment($method, $details) {
        try {
            // Validate order ID
            if (empty($this->orderID)) {
                return false;
            }
            
            // Set payment method
            $this->paymentMethod = $method;
            
            // Process based on payment method
            $paymentStatus = 'pending';
            
            switch ($method) {
                case 'credit_card':
                    // In a real application, you would integrate with a payment gateway here
                    $paymentStatus = 'paid';
                    break;
                
                case 'gcash':
                    // GCash integration would go here
                    $paymentStatus = 'pending';
                    break;
                
                case 'cod':
                    // Cash on Delivery - payment will be collected on delivery
                    $paymentStatus = 'pending';
                    break;
                
                default:
                    return false;
            }
            
            // Update order payment status
            $orders = readJsonFile(ORDERS_FILE);
            if (!is_array($orders)) {
                $orders = [];
            }
            
            foreach ($orders as &$order) {
                if ($order['orderID'] == $this->orderID) {
                    $order['paymentMethod'] = $method;
                    $order['paymentStatus'] = $paymentStatus;
                    $this->paymentStatus = $paymentStatus;
                    break;
                }
            }
            
            // Save updated orders
            if (!writeJsonFile(ORDERS_FILE, $orders)) {
                throw new Exception("Failed to update payment status");
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error processing payment: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cancel the order
     * 
     * @return bool True if successful, false otherwise
     */
    public function cancelOrder() {
        try {
            // Validate order ID
            if (empty($this->orderID)) {
                return false;
            }
            
            // Update order status
            $orders = readJsonFile(ORDERS_FILE);
            if (!is_array($orders)) {
                $orders = [];
            }
            
            foreach ($orders as &$order) {
                if ($order['orderID'] == $this->orderID) {
                    $order['status'] = 'cancelled';
                    $this->status = 'cancelled';
                    
                    // Restore product inventory
                    $products = readJsonFile(PRODUCTS_FILE);
                    if (!is_array($products)) {
                        $products = [];
                    }
                    
                    foreach ($order['items'] as $item) {
                        foreach ($products as &$product) {
                            if ($product['productID'] == $item['productID']) {
                                $product['stock_quantity'] += $item['quantity'];
                                break;
                            }
                        }
                    }
                    writeJsonFile(PRODUCTS_FILE, $products);
                    break;
                }
            }
            
            // Save updated orders
            if (!writeJsonFile(ORDERS_FILE, $orders)) {
                throw new Exception("Failed to cancel order");
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error cancelling order: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get order by ID
     * 
     * @param string $orderID Order ID
     * @return Orders|null Order object or null if not found
     */
    public static function getById($orderID) {
        try {
            $orders = readJsonFile(ORDERS_FILE);
            if (!is_array($orders)) {
                return null;
            }
            
            foreach ($orders as $orderData) {
                if (isset($orderData['orderID']) && $orderData['orderID'] == $orderID) {
                    $order = new self();
                    $order->orderID = $orderData['orderID'];
                    $order->customerID = $orderData['customerID'];
                    $order->orderDate = $orderData['orderDate'];
                    $order->status = $orderData['status'];
                    $order->paymentStatus = $orderData['paymentStatus'];
                    $order->paymentMethod = $orderData['paymentMethod'];
                    $order->shippingAddress = $orderData['shippingAddress'];
                    $order->subtotal = $orderData['subtotal'];
                    $order->tax = $orderData['tax'];
                    $order->shippingCost = $orderData['shippingCost'];
                    $order->total = $orderData['total'];
                    $order->items = $orderData['items'];
                    return $order;
                }
            }
            return null;
        } catch (Exception $e) {
            error_log("Error getting order by ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all orders for a customer
     * 
     * @param int $customerID Customer ID
     * @return array Orders array
     */
    public static function getByCustomer($customerID) {
        try {
            $orders = readJsonFile(ORDERS_FILE);
            if (!is_array($orders)) {
                return [];
            }
            
            $customerOrders = [];
            foreach ($orders as $orderData) {
                if (isset($orderData['customerID']) && $orderData['customerID'] == $customerID) {
                    $order = new self();
                    $order->orderID = $orderData['orderID'];
                    $order->customerID = $orderData['customerID'];
                    $order->orderDate = $orderData['orderDate'];
                    $order->status = $orderData['status'];
                    $order->paymentStatus = $orderData['paymentStatus'];
                    $order->paymentMethod = $orderData['paymentMethod'];
                    $order->shippingAddress = $orderData['shippingAddress'];
                    $order->subtotal = $orderData['subtotal'];
                    $order->tax = $orderData['tax'];
                    $order->shippingCost = $orderData['shippingCost'];
                    $order->total = $orderData['total'];
                    $order->items = $orderData['items'];
                    $customerOrders[] = $order;
                }
            }
            
            return $customerOrders;
        } catch (Exception $e) {
            error_log("Error getting orders by customer: " . $e->getMessage());
            return [];
        }
    }
}