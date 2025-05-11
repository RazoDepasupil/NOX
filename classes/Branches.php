<?php
/**
 * Branches Class
 * Handles store/branch locations management using JSON storage
 */
class Branches {
    private $id;
    private $company_id;
    private $name;
    private $address;
    private $city;
    private $state;
    private $zip;
    private $country;
    private $phone;
    private $email;
    private $manager_id; // Employee ID of branch manager
    private $status;     // active, inactive, etc.
    private $opening_hours;
    private $location_coordinates;
    private $is_headquarters;
    
    private $storage_file; // File path for JSON storage
    
    /**
     * Constructor
     * @param string $storage_file Path to JSON storage file
     */
    public function __construct($storage_file = 'data/branches.json') {
        $this->storage_file = $storage_file;
        
        // Ensure storage directory exists
        $dir = dirname($storage_file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Create storage file if it doesn't exist
        if (!file_exists($storage_file)) {
            file_put_contents($storage_file, json_encode(['branches' => []]));
        }
    }
    
    /**
     * Get all branches data
     * @return array All branches data
     */
    private function getAllBranchesData() {
        if (!file_exists($this->storage_file)) {
            return ['branches' => []];
        }
        
        $data = json_decode(file_get_contents($this->storage_file), true);
        if (!is_array($data) || !isset($data['branches'])) {
            $data = ['branches' => []];
        }
        
        return $data;
    }
    
    /**
     * Save all branches data
     * @param array $data Branches data
     * @return bool Success status
     */
    private function saveBranchesData($data) {
        try {
            file_put_contents($this->storage_file, json_encode($data, JSON_PRETTY_PRINT));
            return true;
        } catch (Exception $e) {
            error_log("Error saving branches data: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Load branch by ID
     * @param int $id Branch ID
     * @return bool Success status
     */
    public function load($id) {
        $data = $this->getAllBranchesData();
        
        foreach ($data['branches'] as $branch) {
            if ($branch['id'] == $id) {
                $this->id = $branch['id'];
                $this->company_id = $branch['company_id'];
                $this->name = $branch['name'];
                $this->address = $branch['address'];
                $this->city = $branch['city'];
                $this->state = $branch['state'];
                $this->zip = $branch['zip'];
                $this->country = $branch['country'];
                $this->phone = $branch['phone'];
                $this->email = $branch['email'];
                $this->manager_id = $branch['manager_id'];
                $this->status = $branch['status'];
                $this->opening_hours = $branch['opening_hours'];
                $this->location_coordinates = $branch['location_coordinates'];
                $this->is_headquarters = $branch['is_headquarters'];
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Save branch information
     * @return bool Success status
     */
    public function save() {
        $data = $this->getAllBranchesData();
        
        // Prepare branch data
        $branch_data = [
            'company_id' => $this->company_id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'manager_id' => $this->manager_id,
            'status' => $this->status,
            'opening_hours' => $this->opening_hours,
            'location_coordinates' => $this->location_coordinates,
            'is_headquarters' => $this->is_headquarters
        ];
        
        // Update existing or add new
        if ($this->id) {
            $branch_data['id'] = $this->id;
            $found = false;
            
            foreach ($data['branches'] as $key => $branch) {
                if ($branch['id'] == $this->id) {
                    $data['branches'][$key] = $branch_data;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                return false;
            }
        } else {
            // Generate new ID
            $max_id = 0;
            foreach ($data['branches'] as $branch) {
                if (isset($branch['id']) && $branch['id'] > $max_id) {
                    $max_id = $branch['id'];
                }
            }
            $this->id = $max_id + 1;
            $branch_data['id'] = $this->id;
            
            $data['branches'][] = $branch_data;
        }
        
        return $this->saveBranchesData($data);
    }
    
    /**
     * Set branch as headquarters
     * @return bool Success status
     */
    public function setAsHeadquarters() {
        if (!$this->id) return false;
        
        $data = $this->getAllBranchesData();
        
        // First, unset any existing headquarters for this company
        foreach ($data['branches'] as $key => $branch) {
            if ($branch['company_id'] == $this->company_id) {
                $data['branches'][$key]['is_headquarters'] = false;
            }
        }
        
        // Then set this branch as headquarters
        foreach ($data['branches'] as $key => $branch) {
            if ($branch['id'] == $this->id) {
                $data['branches'][$key]['is_headquarters'] = true;
                $this->is_headquarters = true;
                break;
            }
        }
        
        return $this->saveBranchesData($data);
    }
    
    /**
     * Get branch inventory summary
     * @return array Inventory summary
     */
    public function getInventorySummary() {
        $summary = [
            'total_items' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
            'value' => 0.00
        ];
        
        // Load inventory data
        $inventory_file = dirname($this->storage_file) . '/inventory.json';
        if (!file_exists($inventory_file)) {
            return $summary;
        }
        
        $inventory_data = json_decode(file_get_contents($inventory_file), true);
        if (!isset($inventory_data['inventory'])) {
            return $summary;
        }
        
        // Load products data for cost prices
        $products_file = dirname($this->storage_file) . '/products.json';
        $products_data = [];
        if (file_exists($products_file)) {
            $products_data = json_decode(file_get_contents($products_file), true);
            $products_data = isset($products_data['products']) ? $products_data['products'] : [];
            
            // Create a lookup array for easier access
            $products_lookup = [];
            foreach ($products_data as $product) {
                $products_lookup[$product['id']] = $product;
            }
            $products_data = $products_lookup;
        }
        
        // Process inventory
        foreach ($inventory_data['inventory'] as $item) {
            if ($item['branch_id'] == $this->id) {
                $summary['total_items']++;
                
                if ($item['quantity'] == 0) {
                    $summary['out_of_stock']++;
                } elseif ($item['quantity'] <= $item['reorder_level']) {
                    $summary['low_stock']++;
                }
                
                // Calculate value if product data is available
                if (isset($products_data[$item['product_id']])) {
                    $cost_price = $products_data[$item['product_id']]['cost_price'];
                    $summary['value'] += $item['quantity'] * $cost_price;
                }
            }
        }
        
        return $summary;
    }
    
    /**
     * Delete branch
     * @return bool Success status
     */
    public function delete() {
        if (!$this->id || $this->is_headquarters) {
            return false; // Can't delete if no ID or if it's headquarters
        }
        
        $data = $this->getAllBranchesData();
        
        foreach ($data['branches'] as $key => $branch) {
            if ($branch['id'] == $this->id) {
                unset($data['branches'][$key]);
                $data['branches'] = array_values($data['branches']); // Re-index array
                $this->id = null;
                return $this->saveBranchesData($data);
            }
        }
        
        return false;
    }
    
    /**
     * Get all branches
     * @param int $company_id Filter by company ID (optional)
     * @return array Branches list
     */
    public function getAllBranches($company_id = null) {
        $data = $this->getAllBranchesData();
        $branches = [];
        
        // Load employees data for manager names
        $employees_file = dirname($this->storage_file) . '/employees.json';
        $employees_data = [];
        if (file_exists($employees_file)) {
            $employees_data = json_decode(file_get_contents($employees_file), true);
            $employees_data = isset($employees_data['employees']) ? $employees_data['employees'] : [];
            
            // Create a lookup array for easier access
            $employees_lookup = [];
            foreach ($employees_data as $employee) {
                $employees_lookup[$employee['id']] = $employee;
            }
            $employees_data = $employees_lookup;
        }
        
        foreach ($data['branches'] as $branch) {
            if ($company_id === null || $branch['company_id'] == $company_id) {
                // Add manager name if available
                if (isset($branch['manager_id']) && isset($employees_data[$branch['manager_id']])) {
                    $employee = $employees_data[$branch['manager_id']];
                    $branch['first_name'] = $employee['first_name'];
                    $branch['last_name'] = $employee['last_name'];
                } else {
                    $branch['first_name'] = null;
                    $branch['last_name'] = null;
                }
                
                $branches[] = $branch;
            }
        }
        
        // Sort by headquarters first, then by name
        usort($branches, function($a, $b) {
            if ($a['is_headquarters'] && !$b['is_headquarters']) return -1;
            if (!$a['is_headquarters'] && $b['is_headquarters']) return 1;
            return strcmp($a['name'], $b['name']);
        });
        
        return $branches;
    }
    
    /**
     * Get branch sales for a period
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Sales data
     */
    public function getSales($start_date, $end_date) {
        $sales = [
            'total' => 0,
            'count' => 0,
            'average' => 0,
            'daily' => []
        ];
        
        // Load orders data
        $orders_file = dirname($this->storage_file) . '/orders.json';
        if (!file_exists($orders_file)) {
            return $sales;
        }
        
        $orders_data = json_decode(file_get_contents($orders_file), true);
        if (!isset($orders_data['orders'])) {
            return $sales;
        }
        
        $daily_sales = [];
        
        foreach ($orders_data['orders'] as $order) {
            if ($order['branch_id'] == $this->id) {
                $order_date = substr($order['order_date'], 0, 10); // Extract date part
                
                // Check if within date range
                if ($order_date >= $start_date && $order_date <= $end_date) {
                    $sales['total'] += $order['total_amount'];
                    $sales['count']++;
                    
                    // Add to daily sales
                    if (!isset($daily_sales[$order_date])) {
                        $daily_sales[$order_date] = [
                            'count' => 0,
                            'total' => 0
                        ];
                    }
                    
                    $daily_sales[$order_date]['count']++;
                    $daily_sales[$order_date]['total'] += $order['total_amount'];
                }
            }
        }
        
        // Calculate average
        $sales['average'] = $sales['count'] > 0 ? $sales['total'] / $sales['count'] : 0;
        
        // Sort daily sales by date
        ksort($daily_sales);
        $sales['daily'] = $daily_sales;
        
        return $sales;
    }
    
    // Getters and setters
    public function getId() { return $this->id; }
    
    public function getCompanyId() { return $this->company_id; }
    public function setCompanyId($company_id) { $this->company_id = $company_id; }
    
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
    
    public function getAddress() { return $this->address; }
    public function setAddress($address) { $this->address = $address; }
    
    public function getCity() { return $this->city; }
    public function setCity($city) { $this->city = $city; }
    
    public function getState() { return $this->state; }
    public function setState($state) { $this->state = $state; }
    
    public function getZip() { return $this->zip; }
    public function setZip($zip) { $this->zip = $zip; }
    
    public function getCountry() { return $this->country; }
    public function setCountry($country) { $this->country = $country; }
    
    public function getPhone() { return $this->phone; }
    public function setPhone($phone) { $this->phone = $phone; }
    
    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }
    
    public function getManagerId() { return $this->manager_id; }
    public function setManagerId($manager_id) { $this->manager_id = $manager_id; }
    
    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; }
    
    public function getOpeningHours() { return $this->opening_hours; }
    public function setOpeningHours($opening_hours) { $this->opening_hours = $opening_hours; }
    
    public function getLocationCoordinates() { return $this->location_coordinates; }
    public function setLocationCoordinates($location_coordinates) { $this->location_coordinates = $location_coordinates; }
    
    public function isHeadquarters() { return $this->is_headquarters; }
    public function setIsHeadquarters($is_headquarters) { $this->is_headquarters = $is_headquarters; }
    
    /**
     * Get full address
     * @param bool $include_country Whether to include country
     * @return string Full address
     */
    public function getFullAddress($include_country = true) {
        $address = $this->address;
        if ($this->city) $address .= ', ' . $this->city;
        if ($this->state) $address .= ', ' . $this->state;
        if ($this->zip) $address .= ' ' . $this->zip;
        if ($include_country && $this->country) $address .= ', ' . $this->country;
        
        return $address;
    }
}