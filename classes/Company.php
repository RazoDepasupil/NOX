<?php
/**
 * Company Class
 * Handles company profile management and settings
 */
class Company {
    private $db;
    private $id;
    private $name;
    private $address;
    private $phone;
    private $email;
    private $website;
    private $tax_id;
    private $logo;
    private $settings;
    
    /**
     * Constructor
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->db = $db;
        $this->settings = [];
        // Load default company if it exists
        $this->loadDefaultCompany();
    }
    
    /**
     * Load default company information
     */
    private function loadDefaultCompany() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM companies WHERE is_default = 1 LIMIT 1");
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->address = $row['address'];
                $this->phone = $row['phone'];
                $this->email = $row['email'];
                $this->website = $row['website'];
                $this->tax_id = $row['tax_id'];
                $this->logo = $row['logo'];
                
                // Load company settings
                $this->loadSettings();
            }
        } catch (PDOException $e) {
            error_log("Error loading company: " . $e->getMessage());
        }
    }
    
    /**
     * Load a specific company by ID
     * @param int $id Company ID
     * @return bool Success status
     */
    public function load($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM companies WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->address = $row['address'];
                $this->phone = $row['phone'];
                $this->email = $row['email'];
                $this->website = $row['website'];
                $this->tax_id = $row['tax_id'];
                $this->logo = $row['logo'];
                
                // Load company settings
                $this->loadSettings();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error loading company: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Load company settings
     */
    private function loadSettings() {
        try {
            $stmt = $this->db->prepare("SELECT setting_key, setting_value FROM company_settings WHERE company_id = :company_id");
            $stmt->bindParam(':company_id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (PDOException $e) {
            error_log("Error loading company settings: " . $e->getMessage());
        }
    }
    
    /**
     * Save company information
     * @return bool Success status
     */
    public function save() {
        try {
            // If ID exists, update; otherwise insert
            if ($this->id) {
                $stmt = $this->db->prepare("UPDATE companies SET 
                    name = :name, 
                    address = :address, 
                    phone = :phone, 
                    email = :email, 
                    website = :website, 
                    tax_id = :tax_id, 
                    logo = :logo 
                    WHERE id = :id");
                $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            } else {
                $stmt = $this->db->prepare("INSERT INTO companies 
                    (name, address, phone, email, website, tax_id, logo, is_default) 
                    VALUES (:name, :address, :phone, :email, :website, :tax_id, :logo, 0)");
            }
            
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':address', $this->address);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':website', $this->website);
            $stmt->bindParam(':tax_id', $this->tax_id);
            $stmt->bindParam(':logo', $this->logo);
            
            if ($stmt->execute()) {
                if (!$this->id) {
                    $this->id = $this->db->lastInsertId();
                }
                $this->saveSettings();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error saving company: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Save company settings
     */
    private function saveSettings() {
        if (!$this->id) return;
        
        try {
            // First delete existing settings
            $stmt = $this->db->prepare("DELETE FROM company_settings WHERE company_id = :company_id");
            $stmt->bindParam(':company_id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Then insert new settings
            $stmt = $this->db->prepare("INSERT INTO company_settings (company_id, setting_key, setting_value) VALUES (:company_id, :key, :value)");
            
            foreach ($this->settings as $key => $value) {
                $stmt->bindParam(':company_id', $this->id, PDO::PARAM_INT);
                $stmt->bindParam(':key', $key);
                $stmt->bindParam(':value', $value);
                $stmt->execute();
            }
        } catch (PDOException $e) {
            error_log("Error saving company settings: " . $e->getMessage());
        }
    }
    
    /**
     * Set a company setting
     * @param string $key Setting key
     * @param mixed $value Setting value
     */
    public function setSetting($key, $value) {
        $this->settings[$key] = $value;
    }
    
    /**
     * Get a company setting
     * @param string $key Setting key
     * @param mixed $default Default value if setting not found
     * @return mixed Setting value
     */
    public function getSetting($key, $default = null) {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }
    
    /**
     * Set company as default
     * @return bool Success status
     */
    public function setAsDefault() {
        if (!$this->id) return false;
        
        try {
            // First, unset all defaults
            $stmt = $this->db->prepare("UPDATE companies SET is_default = 0");
            $stmt->execute();
            
            // Then set this as default
            $stmt = $this->db->prepare("UPDATE companies SET is_default = 1 WHERE id = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error setting company as default: " . $e->getMessage());
            return false;
        }
    }
    
    // Getters and setters
    public function getId() { return $this->id; }
    
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
    
    public function getAddress() { return $this->address; }
    public function setAddress($address) { $this->address = $address; }
    
    public function getPhone() { return $this->phone; }
    public function setPhone($phone) { $this->phone = $phone; }
    
    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }
    
    public function getWebsite() { return $this->website; }
    public function setWebsite($website) { $this->website = $website; }
    
    public function getTaxId() { return $this->tax_id; }
    public function setTaxId($tax_id) { $this->tax_id = $tax_id; }
    
    public function getLogo() { return $this->logo; }
    public function setLogo($logo) { $this->logo = $logo; }
    
    /**
     * Get all company settings
     * @return array Settings array
     */
    public function getAllSettings() {
        return $this->settings;
    }
    
    /**
     * Get all companies
     * @return array Companies list
     */
    public function getAllCompanies() {
        $companies = [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM companies ORDER BY name");
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $companies[] = $row;
            }
        } catch (PDOException $e) {
            error_log("Error getting companies: " . $e->getMessage());
        }
        return $companies;
    }
    
    /**
     * Delete company
     * @return bool Success status
     */
    public function delete() {
        if (!$this->id || $this->isDefault()) {
            return false; // Can't delete if no ID or if it's the default company
        }
        
        try {
            // Delete company settings first
            $stmt = $this->db->prepare("DELETE FROM company_settings WHERE company_id = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Then delete the company
            $stmt = $this->db->prepare("DELETE FROM companies WHERE id = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $this->id = null;
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error deleting company: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if company is the default one
     * @return bool Is default
     */
    public function isDefault() {
        if (!$this->id) return false;
        
        try {
            $stmt = $this->db->prepare("SELECT is_default FROM companies WHERE id = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return (bool)$row['is_default'];
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error checking if company is default: " . $e->getMessage());
            return false;
        }
    }
}
?>