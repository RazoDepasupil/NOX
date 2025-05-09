<?php
class Orders {
    protected $orderID;
    protected $customer;
    protected $items = [];
    protected $subtotal;
    protected $shippingCost;
    protected $total;
    protected $orderDate;
    protected $status;
    protected $paymentStatus;
    protected $paymentMethod;
    
    // Constructor
    public function __construct($orderData = null) {
        if ($orderData) {
            $this->orderID = $orderData['orderID'] ?? generateUniqueId();
            $this->customer = $orderData['customer'] ?? null;
            $this->items = $orderData['items'] ?? [];
            $this->subtotal = $orderData['subtotal'] ?? 0;
            $this->shippingCost = $orderData['shippingCost'] ?? 0;
            $this->total = $orderData['total'] ?? 0;
            $this->orderDate = $orderData['orderDate'] ?? date('Y-m-d H:i:s');
            $this->status = $orderData['status'] ?? 'pending';
            $this->paymentStatus = $orderData['paymentStatus'] ?? 'pending';
            $this->paymentMethod = $orderData['paymentMethod'] ?? null;
        } else {
            $this->orderID = generateUniqueId();
            $this->orderDate = date('Y-m-d H:i:s');
            $this->status = 'pending';
            $this->paymentStatus = 'pending';
        }
    }
    
    // Place an order
    public function placeOrder() {
        return $this->save();
    }
    
    // Save order data
    public function save() {
        $orders = readJsonFile(ORDERS_FILE);
        $found = false;
        
        // Check if order already exists
        foreach ($orders as $key => $order) {
            if ($order['orderID'] === $this->orderID) {
                $orders[$key] = $this->toArray();
                $found = true;
                break;
            }
        }
        
        // Add new order if not found
        if (!$found) {
            $orders[] = $this->toArray();
        }
        
        return writeJsonFile(ORDERS_FILE, $orders);
    }
    
    // Load order by ID
    public static function getById($orderId) {
        $orders = readJsonFile(ORDERS_FILE);
        
        foreach ($orders as $order) {
            if ($order['orderID'] === $orderId) {
                return new Orders($order);
            }
        }
        
        return null;
    }
    
    // Get all orders
    public static function getAll() {
        $orders = readJsonFile(ORDERS_FILE);
        $orderObjects = [];
        
        foreach ($orders as $order) {
            $orderObjects[] = new Orders($order);
        }
        
        return $orderObjects;
    }
    
    // Get orders by customer ID
    public static function getByCustomer($customerId) {
        $orders = readJsonFile(ORDERS_FILE);
        $customerOrders = [];
        
        foreach ($orders as $order) {
            if ($order['customer'] === $customerId) {
                $customerOrders[] = new Orders($order);
            }
        }
        
        return $customerOrders;
    }
    
    // Update order status
    public function updateStatus($status) {
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $this->status = $status;
        return $this->save();
    }
    
    // Update payment status
    public function updatePaymentStatus($paymentStatus) {
        $validStatuses = ['pending', 'paid', 'failed', 'refunded'];
        
        if (!in_array($paymentStatus, $validStatuses)) {
            return false;
        }
        
        $this->paymentStatus = $paymentStatus;
        return $this->save();
    }
    
    // Process payment for order
    public function processPayment($paymentMethod, $paymentDetails) {
        $this->paymentMethod = $paymentMethod;
        
        // Create payment
        $payment = new Payment();
        $payment->setOrderID($this->orderID);
        $payment->setAmount($this->total);
        $payment->setPaymentMethod($paymentMethod);
        
        if ($payment->processPayment($paymentDetails)) {
            $this->paymentStatus = 'paid';
            $this->save();
            return true;
        }
        
        $this->paymentStatus = 'failed';
        $this->save();
        return false;
    }
    
    // Cancel order
    public function cancelOrder() {
        if ($this->status === 'shipped' || $this->status === 'delivered') {
            return false;
        }
        
        $this->status = 'cancelled';
        
        // Restore inventory
        if (!empty($this->items)) {
            foreach ($this->items as $item) {
                $product = Product::getById($item['productID']);
                if ($product) {
                    $product->setStockQuantity($product->getStockQuantity() + $item['quantity']);
                    $product->save();
                }
            }
        }
        
        // Refund payment if paid
        if ($this->paymentStatus === 'paid') {
            $payment = Payment::getByOrderId($this->orderID);
            if ($payment) {
                $payment->refundPayment();
            }
            $this->paymentStatus = 'refunded';
        }
        
        return $this->save();
    }
    
    // Convert order object to array
    public function toArray() {
        return [
            'orderID' => $this->orderID,
            'customer' => $this->customer,
            'items' => $this->items,
            'subtotal' => $this->subtotal,
            'shippingCost' => $this->shippingCost,
            'total' => $this->total,
            'orderDate' => $this->orderDate,
            'status' => $this->status,
            'paymentStatus' => $this->paymentStatus,
            'paymentMethod' => $this->paymentMethod
        ];
    }
    
    // Getters and setters
    public function getOrderID() {
        return $this->orderID;
    }
    
    public function getCustomerId() {
        return $this->customer;
    }
    
    public function setCustomerId($customerId) {
        $this->customer = $customerId;
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
    
    public function getOrderDate() {
        return $this->orderDate;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function getPaymentStatus() {
        return $this->paymentStatus;
    }
    
    public function getPaymentMethod() {
        return $this->paymentMethod;
    }
    
    public function setPaymentMethod($paymentMethod) {
        $this->paymentMethod = $paymentMethod;
    }
}
?>