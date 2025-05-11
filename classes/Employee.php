<?php
/**
 * Employee Class
 * 
 * Handles employee management for the e-commerce system using JSON file storage
 */
class Employee {
    private $employees_file;
    private $employees;
    
    private $id;
    private $name;
    private $email;
    private $phone;
    private $position;
    private $department;
    private $hire_date;
    private $salary;
    private $status;
    private $created_at;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->employees_file = dirname(__DIR__) . '/data/employees.json';
        $this->loadEmployees();
        
        // Default values for new employees
        $this->status = 'active';
        $this->created_at = date('Y-m-d H:i:s');
        $this->hire_date = date('Y-m-d');
    }
    
    /**
     * Load employees from JSON file
     */
    private function loadEmployees() {
        if (file_exists($this->employees_file)) {
            $json_data = file_get_contents($this->employees_file);
            $this->employees = json_decode($json_data, true);
            
            // If the file is empty or invalid, initialize with empty array
            if (!is_array($this->employees)) {
                $this->employees = [];
            }
        } else {
            // Create the file with empty array if it doesn't exist
            $this->employees = [];
            $this->saveEmployees();
        }
    }
    
    /**
     * Save employees to JSON file
     */
    private function saveEmployees() {
        $json_data = json_encode($this->employees, JSON_PRETTY_PRINT);
        file_put_contents($this->employees_file, $json_data);
    }
    
    /**
     * Generate a unique ID for a new employee
     */
    private function generateId() {
        $maxId = 0;
        foreach ($this->employees as $employee) {
            if ($employee['id'] > $maxId) {
                $maxId = $employee['id'];
            }
        }
        return $maxId + 1;
    }
    
    /**
     * Create new employee
     * 
     * @param array $data Employee data
     * @return int|bool Employee ID on success, false on failure
     */
    public function create($data) {
        // Validate required fields
        if (empty($data['name']) || empty($data['email']) || empty($data['position'])) {
            return false;
        }
        
        // Set properties
        $this->id = $this->generateId();
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->phone = $data['phone'] ?? '';
        $this->position = $data['position'];
        $this->department = $data['department'] ?? 'General';
        $this->hire_date = $data['hire_date'] ?? date('Y-m-d');
        $this->salary = $data['salary'] ?? 0;
        
        // Create employee array
        $employee = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
            'department' => $this->department,
            'hire_date' => $this->hire_date,
            'salary' => $this->salary,
            'status' => $this->status,
            'created_at' => $this->created_at
        ];
        
        // Add to employees array
        $this->employees[] = $employee;
        
        // Save to file
        $this->saveEmployees();
        
        return $this->id;
    }
    
    /**
     * Get employee by ID
     * 
     * @param int $id Employee ID
     * @return bool Success or failure
     */
    public function read($id) {
        foreach ($this->employees as $employee) {
            if ($employee['id'] == $id) {
                // Set properties
                $this->id = $employee['id'];
                $this->name = $employee['name'];
                $this->email = $employee['email'];
                $this->phone = $employee['phone'];
                $this->position = $employee['position'];
                $this->department = $employee['department'];
                $this->hire_date = $employee['hire_date'];
                $this->salary = $employee['salary'];
                $this->status = $employee['status'];
                $this->created_at = $employee['created_at'];
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Update employee
     * 
     * @param array $data Employee data
     * @return bool Success or failure
     */
    public function update($data) {
        // Check if ID exists
        if (empty($this->id)) {
            return false;
        }
        
        // Find the employee index
        $index = null;
        foreach ($this->employees as $key => $employee) {
            if ($employee['id'] == $this->id) {
                $index = $key;
                break;
            }
        }
        
        // If employee not found, return false
        if ($index === null) {
            return false;
        }
        
        // Update properties if provided
        if (isset($data['name'])) $this->name = $data['name'];
        if (isset($data['email'])) $this->email = $data['email'];
        if (isset($data['phone'])) $this->phone = $data['phone'];
        if (isset($data['position'])) $this->position = $data['position'];
        if (isset($data['department'])) $this->department = $data['department'];
        if (isset($data['hire_date'])) $this->hire_date = $data['hire_date'];
        if (isset($data['salary'])) $this->salary = $data['salary'];
        if (isset($data['status'])) $this->status = $data['status'];
        
        // Update employee in array
        $this->employees[$index] = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
            'department' => $this->department,
            'hire_date' => $this->hire_date,
            'salary' => $this->salary,
            'status' => $this->status,
            'created_at' => $this->created_at
        ];
        
        // Save to file
        $this->saveEmployees();
        
        return true;
    }
    
    /**
     * Delete employee
     * 
     * @param int $id Employee ID
     * @return bool Success or failure
     */
    public function delete($id) {
        // Find the employee index
        $index = null;
        foreach ($this->employees as $key => $employee) {
            if ($employee['id'] == $id) {
                $index = $key;
                break;
            }
        }
        
        // If employee not found, return false
        if ($index === null) {
            return false;
        }
        
        // Remove from array
        array_splice($this->employees, $index, 1);
        
        // Save to file
        $this->saveEmployees();
        
        return true;
    }
    
    /**
     * Get all employees
     * 
     * @param string $status Filter by status
     * @param string $department Filter by department
     * @return array Array of employees
     */
    public function getAll($status = null, $department = null) {
        $filtered = $this->employees;
        
        // Filter by status
        if ($status !== null) {
            $temp = [];
            foreach ($filtered as $employee) {
                if ($employee['status'] == $status) {
                    $temp[] = $employee;
                }
            }
            $filtered = $temp;
        }
        
        // Filter by department
        if ($department !== null) {
            $temp = [];
            foreach ($filtered as $employee) {
                if ($employee['department'] == $department) {
                    $temp[] = $employee;
                }
            }
            $filtered = $temp;
        }
        
        return $filtered;
    }
    
    /**
     * Get employee data
     * 
     * @return array Employee data
     */
    public function getData() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
            'department' => $this->department,
            'hire_date' => $this->hire_date,
            'salary' => $this->salary,
            'status' => $this->status,
            'created_at' => $this->created_at
        ];
    }
    
    /**
     * Get employees by department
     * 
     * @param string $department Department name
     * @return array Employees in the specified department
     */
    public function getByDepartment($department) {
        $result = [];
        foreach ($this->employees as $employee) {
            if ($employee['department'] == $department) {
                $result[] = $employee;
            }
        }
        return $result;
    }
    
    /**
     * Get employees by position
     * 
     * @param string $position Position title
     * @return array Employees with the specified position
     */
    public function getByPosition($position) {
        $result = [];
        foreach ($this->employees as $employee) {
            if ($employee['position'] == $position) {
                $result[] = $employee;
            }
        }
        return $result;
    }
}