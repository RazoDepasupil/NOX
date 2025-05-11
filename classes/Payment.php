<?php
/**
 * Payment Class
 * 
 * Manages payment processing for the e-commerce system using JSON file storage
 */
class Payment {
    private $orders_file;
    private $payments_file;
    private $orders;
    private $payments;
    
    private $id;
    private $order_id;
    private $amount;
    private $payment_method;
    private $transaction_id;
    private $status;
    private $date;
    private $notes;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->orders_file = dirname(__DIR__) . '/data/orders.json';
        $this->payments_file = dirname(__DIR__) . '/data/payments.json';
        $this->loadOrders();
        $this->loadPayments();
        
        // Default values
        $this->date = date('Y-m-d H:i:s');
        $this->status = 'pending';
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
            file_put_contents($this->orders_file, json_encode($this->orders, JSON_PRETTY_PRINT));
        }
    }
    
    /**
     * Load payments from JSON file
     */
    private function loadPayments() {
        if (file_exists($this->payments_file)) {
            $json_data = file_get_contents($this->payments_file);
            $this->payments = json_decode($json_data, true);
            
            // If the file is empty or invalid, initialize with empty array
            if (!is_array($this->payments)) {
                $this->payments = [];
            }
        } else {
            // Create the file with empty array if it doesn't exist
            $this->payments = [];
            $this->savePayments();
        }
    }
    
    /**
     * Save payments to JSON file
     */
    private function savePayments() {
        $json_data = json_encode($this->payments, JSON_PRETTY_PRINT);
        file_put_contents($this->payments_file, $json_data);
    }
    
    /**
     * Save orders to JSON file
     */
    private function saveOrders() {
        $json_data = json_encode($this->orders, JSON_PRETTY_PRINT);
        file_put_contents($this->orders_file, $json_data);
    }
    
    /**
     * Generate a unique ID for a new payment
     */
    private function generateId() {
        $maxId = 0;
        foreach ($this->payments as $payment) {
            if ($payment['id'] > $maxId) {
                $maxId = $payment['id'];
            }
        }
        return $maxId + 1;
    }
    
    /**
     * Process a payment
     * 
     * @param array $data Payment data
     * @return int|bool Payment ID on success, false on failure
     */
    public function processPayment($data) {
        // Validate required fields
        if (empty($data['order_id']) || empty($data['amount']) || empty($data['payment_method'])) {
            return false;
        }
        
        // Check if order exists
        $order_exists = false;
        $order_index = null;
        foreach ($this->orders as $key => $order) {
            if ($order['orderID'] == $data['order_id']) {
                $order_exists = true;
                $order_index = $key;
                break;
            }
        }
        
        if (!$order_exists) {
            return false;
        }
        
        // Set properties
        $this->id = $this->generateId();
        $this->order_id = $data['order_id'];
        $this->amount = $data['amount'];
        $this->payment_method = $data['payment_method'];
        $this->transaction_id = $data['transaction_id'] ?? '';
        $this->status = $data['status'] ?? 'pending';
        $this->notes = $data['notes'] ?? '';
        
        // Create payment array
        $payment = [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'status' => $this->status,
            'date' => $this->date,
            'notes' => $this->notes
        ];
        
        // Add to payments array
        $this->payments[] = $payment;
        
        // Save to file
        $this->savePayments();
        
        // Update order payment status
        if ($this->status === 'completed') {
            $this->updateOrderPaymentStatus($this->order_id, 'paid');
        } else {
            $this->updateOrderPaymentStatus($this->order_id, 'pending');
        }
        
        return $this->id;
    }
    
    /**
     * Update payment status
     * 
     * @param int $payment_id Payment ID
     * @param string $status New status
     * @return bool Success or failure
     */
    public function updateStatus($payment_id, $status) {
        // Find payment index
        $index = null;
        foreach ($this->payments as $key => $payment) {
            if ($payment['id'] == $payment_id) {
                $index = $key;
                break;
            }
        }
        
        // If payment not found, return false
        if ($index === null) {
            return false;
        }
        
        // Update status
        $this->payments[$index]['status'] = $status;
        
        // Save to file
        $this->savePayments();
        
        // Update order payment status if necessary
        if ($status == 'completed') {
            $this->updateOrderPaymentStatus($this->payments[$index]['order_id'], 'paid');
        } else if ($status == 'failed') {
            $this->updateOrderPaymentStatus($this->payments[$index]['order_id'], 'unpaid');
        }
        
        return true;
    }
    
    /**
     * Update order payment status
     * 
     * @param int $order_id Order ID
     * @param string $status New payment status
     * @return bool Success or failure
     */
    private function updateOrderPaymentStatus($order_id, $status) {
        // Find order index
        $index = null;
        foreach ($this->orders as $key => $order) {
            if ($order['id'] == $order_id) {
                $index = $key;
                break;
            }
        }
        
        // If order not found, return false
        if ($index === null) {
            return false;
        }
        
        // Update status
        $this->orders[$index]['payment_status'] = $status;
        
        // Save to file
        $this->saveOrders();
        
        return true;
    }
    
    /**
     * Get payment by ID
     * 
     * @param int $id Payment ID
     * @return array|bool Payment data or false if not found
     */
    public function getPayment($id) {
        foreach ($this->payments as $payment) {
            if ($payment['id'] == $id) {
                return $payment;
            }
        }
        
        return false;
    }
    
    /**
     * Get all payments for an order
     * 
     * @param int $order_id Order ID
     * @return array Order payments
     */
    public function getOrderPayments($order_id) {
        $result = [];
        
        foreach ($this->payments as $payment) {
            if ($payment['order_id'] == $order_id) {
                $result[] = $payment;
            }
        }
        
        return $result;
    }
    
    /**
     * Get total paid amount for an order
     * 
     * @param int $order_id Order ID
     * @return float Total paid amount
     */
    public function getTotalPaid($order_id) {
        $total = 0;
        
        foreach ($this->payments as $payment) {
            if ($payment['order_id'] == $order_id && $payment['status'] == 'completed') {
                $total += $payment['amount'];
            }
        }
        
        return $total;
    }
    
    /**
     * Check if an order is fully paid
     * 
     * @param int $order_id Order ID
     * @return bool True if fully paid, false otherwise
     */
    public function isFullyPaid($order_id) {
        // Get order total
        $order_total = 0;
        foreach ($this->orders as $order) {
            if ($order['id'] == $order_id) {
                $order_total = $order['total'];
                break;
            }
        }
        
        // Get total paid
        $total_paid = $this->getTotalPaid($order_id);
        
        // Check if fully paid
        return $total_paid >= $order_total;
    }
    
    /**
     * Get payments by status
     * 
     * @param string $status Payment status
     * @return array Payments with the specified status
     */
    public function getPaymentsByStatus($status) {
        $result = [];
        
        foreach ($this->payments as $payment) {
            if ($payment['status'] == $status) {
                $result[] = $payment;
            }
        }
        
        return $result;
    }
    
    /**
     * Get payments by date range
     * 
     * @param string $start_date Start date (Y-m-d)
     * @param string $end_date End date (Y-m-d)
     * @return array Payments in the specified date range
     */
    public function getPaymentsByDateRange($start_date, $end_date) {
        $result = [];
        
        foreach ($this->payments as $payment) {
            $payment_date = substr($payment['date'], 0, 10); // Extract just the date part
            
            if ($payment_date >= $start_date && $payment_date <= $end_date) {
                $result[] = $payment;
            }
        }
        
        return $result;
    }
    
    /**
     * Get payments by payment method
     * 
     * @param string $method Payment method
     * @return array Payments with the specified method
     */
    public function getPaymentsByMethod($method) {
        $result = [];
        
        foreach ($this->payments as $payment) {
            if ($payment['payment_method'] == $method) {
                $result[] = $payment;
            }
        }
        
        return $result;
    }
    
    /**
     * Refund a payment
     * 
     * @param int $payment_id Payment ID
     * @param float $amount Refund amount (optional, defaults to full payment)
     * @param string $reason Refund reason (optional)
     * @return bool Success or failure
     */
    public function refundPayment($payment_id, $amount = null, $reason = '') {
        // Find payment
        $payment = null;
        foreach ($this->payments as $p) {
            if ($p['id'] == $payment_id) {
                $payment = $p;
                break;
            }
        }
        
        // If payment not found or not completed, return false
        if ($payment === null || $payment['status'] !== 'completed') {
            return false;
        }
        
        // Set refund amount to full payment if not specified
        if ($amount === null) {
            $amount = $payment['amount'];
        }
        
        // Create refund record
        $refund_data = [
            'order_id' => $payment['order_id'],
            'amount' => -$amount, // Negative amount for refund
            'payment_method' => $payment['payment_method'],
            'transaction_id' => 'refund_' . $payment['transaction_id'],
            'status' => 'completed',
            'notes' => 'Refund for payment #' . $payment_id . ($reason ? ': ' . $reason : '')
        ];
        
        // Process refund as a new payment record
        return $this->processPayment($refund_data) ? true : false;
    }
}