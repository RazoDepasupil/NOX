<?php
/**
 * Branches Class
 * Handles store/branch locations management
 */
class Branches {
    private $db;
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
    
    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Load branch by ID
     * @param int $id Branch ID
     * @return bool Success status
     */
    public function load($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM branches WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->id = $row['id'];
                $this->company_id = $row['company_id'];
                $this->name = $row['name'];
                $this->address = $row['address'];
                $this->city = $row['city'];
                $this->state = $row['state'];
                $this->zip = $row['zip'];
                $this->country = $row['country'];
                $this->phone = $row['phone'];
                $this->email = $row['email'];
                $this->manager_id = $row['manager_id'];
                $this->status = $row['status'];
                $this->opening_hours = $row['opening_hours'];
                $this->location_coordinates = $row['location_coordinates'];
                $this->is_headquarters = $row['is_headquarters'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error loading branch: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Save branch information
     * @return bool Success status
     */
    public function save() {
        try {
            // If ID exists, update; otherwise insert
            if ($this->id) {
                $stmt = $this->db->prepare("UPDATE branches SET 
                    company_id = :company_id,
                    name = :name,
                    address = :address,
                    city = :city,
                    state = :state,
                    zip = :zip,
                    country = :country,
                    phone = :phone,
                    email = :email,
                    manager_id = :manager_id,
                    status = :status,
                    opening_hours = :opening_hours,
                    location_coordinates = :location_coordinates,
                    is_headquarters = :is_headquarters
                    WHERE id = :id");
                $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            } else {
                $stmt = $this->db->prepare("INSERT INTO branches 
                    (company_id, name, address, city, state, zip, country, phone, email, manager_id, status, opening_hours, location_coordinates, is_headquarters) 
                    VALUES (:company_id, :name, :address, :city, :state, :zip, :country, :phone, :email, :manager_id, :status, :opening_hours, :location_coordinates, :is_headquarters)");
            }
            
            $stmt->bindParam(':company_id', $this->company_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':address', $this->address);
            $stmt->bindParam(':city', $this->city);
            $stmt->bindParam(':state', $this->state);
            $stmt->bindParam(':zip', $this->zip);
            $stmt->bindParam(':country', $this->country);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':manager_id', $this->manager_id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':opening_hours', $this->opening_hours);
            $stmt->bindParam(':location_coordinates', $this->location_coordinates);
            $stmt->bindParam(':is_headquarters', $this->is_headquarters, PDO::PARAM_BOOL);
            
            if ($stmt->execute()) {
                if (!$this->id) {
                    $this->id = $this->db->lastInsertId();
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error saving branch: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set branch as headquarters
     * @return bool Success status
     */
    public function setAsHeadquarters() {
        if (!$this->id) return false;
        
        try {
            // First, unset any existing headquarters for this company
            $stmt = $this->db->prepare("UPDATE branches SET is_headquarters = 0 WHERE company_id = :company_id");
            $stmt->bindParam(':company_id', $this->company_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Then set this branch as headquarters
            $stmt = $this->db->prepare("UPDATE branches SET is_headquarters = 1 WHERE id = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $this->is_headquarters = true;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error setting branch as headquarters: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get branch inventory status
     * @return array Inventory summary
     */
    public function getInventorySummary() {
        $summary = [
            'total_items' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
            'value' => 0.00
        ];
        
        try {
            // Get total items count
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM inventory WHERE branch_id = :branch_id");
            $stmt->bindParam(':branch_id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $summary['total_items'] = (int)$row['count'];
            }
            
            // Get low stock count
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM inventory 
                WHERE branch_id = :branch_id AND quantity <= reorder_level AND quantity > 0");
            $stmt->bindParam(':branch_id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $summary['low_stock'] = (int)$row['count'];
            }
            
            // Get out of stock count
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM inventory 
                WHERE branch_id = :branch_id AND quantity = 0");
            $stmt->bindParam(':branch_id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $summary['out_of_stock'] = (int)$row['count'];
            }
            
            // Get total inventory value
            $stmt = $this->db->prepare("SELECT SUM(quantity * cost_price) as total_value 
                FROM inventory i 
                JOIN products p ON i.product_id = p.id 
                WHERE i.branch_id = :branch_id");
            $stmt->bindParam(':branch_id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $summary['value'] = (float)$row['total_value'];
            }
        } catch (PDOException $e) {
            error_log("Error getting branch inventory summary: " . $e->getMessage());
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
        
        try {
            $stmt = $this->db->prepare("DELETE FROM branches WHERE id = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $this->id = null;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error deleting branch: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all branches
     * @param int $company_id Filter by company ID (optional)
     * @return array Branches list
     */
    public function getAllBranches($company_id = null) {
        $branches = [];
        try {
            $sql = "SELECT b.*, e.first_name, e.last_name 
                FROM branches b 
                LEFT JOIN employees e ON b.manager_id = e.id 
                WHERE 1=1";
            
            $params = [];
            if ($company_id !== null) {
                $sql .= " AND b.company_id = :company_id";
                $params[':company_id'] = $company_id;
            }
            
            $sql .= " ORDER BY b.is_headquarters DESC, b.name";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $branches[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error getting branches: " . $e->getMessage());
        }
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
        
        try {
            // Get total sales and count
            $stmt = $this->db->prepare("SELECT 
                COUNT(*) as order_count, 
                SUM(total_amount) as total_sales 
                FROM orders 
                WHERE branch_id = :branch_id 
                AND order_date BETWEEN :start_date AND :end_date");
                
            $stmt->bindParam(':branch_id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $sales['total'] = (float)$row['total_sales'];
                $sales['count'] = (int)$row['order_count'];
                $sales['average'] = $sales['count'] > 0 ? $sales['total'] / $sales['count'] : 0;
            }
            
            // Get daily sales
            $stmt = $this->db->prepare("SELECT 
                DATE(order_date) as sale_date, 
                COUNT(*) as order_count, 
                SUM(total_amount) as daily_total 
                FROM orders 
                WHERE branch_id = :branch_id 
                AND order_date BETWEEN :start_date AND :end_date 
                GROUP BY DATE(order_date) 
                ORDER BY DATE(order_date)");
                
            $stmt->bindParam(':branch_id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $sales['daily'][$row['sale_date']] = [
                    'count' => (int)$row['order_count'],
                    'total' => (float)$row['daily_total']
                ];
            }
        } catch (PDOException $e) {
            error_log("Error getting branch sales: " . $e->getMessage());
        }
        
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