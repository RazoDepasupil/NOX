<?php
class User {
    protected $userID;
    protected $username;
    protected $email;
    protected $password;
    protected $birthday; // Changed from age to birthday
    protected $gender;
    protected $sex;
    protected $address;
    protected $role;
    
    // Constructor
    public function __construct($userData = null) {
        if ($userData) {
            $this->userID = $userData['userID'] ?? generateUniqueId();
            $this->username = $userData['username'] ?? '';
            $this->email = $userData['email'] ?? '';
            $this->password = $userData['password'] ?? '';
            $this->birthday = $userData['birthday'] ?? '';
            $this->gender = $userData['gender'] ?? '';
            $this->sex = $userData['sex'] ?? '';
            $this->address = $userData['address'] ?? '';
            $this->role = $userData['role'] ?? 'customer';
        } else {
            $this->userID = generateUniqueId();
            $this->role = 'customer';
        }
    }
    
    // Register a new user
    public function register() {
        // Validate input data
        if (empty($this->username) || empty($this->email) || empty($this->password)) {
            return false;
        }
        
        // Check if email already exists
        $users = readJsonFile(USERS_FILE);
        
        foreach ($users as $userType => $userList) {
            foreach ($userList as $user) {
                if ($user['email'] === $this->email) {
                    return false;
                }
            }
        }
        
        // Hash password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Prepare user data for saving
        $userData = $this->toArray();
        
        // Add user to the JSON file
        $users['customers'][] = $userData;
        writeJsonFile(USERS_FILE, $users);
        
        return true;
    }
    
    // Login user
    public function login() {
        // Validate input data
        if (empty($this->email) || empty($this->password)) {
            return false;
        }
        
        // Get users data
        $users = readJsonFile(USERS_FILE);
        
        // Check user credentials
        foreach ($users as $userType => $userList) {
            foreach ($userList as $user) {
                if ($user['email'] === $this->email && password_verify($this->password, $user['password'])) {
                    // Set session data
                    $_SESSION['user_id'] = $user['userID'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'] ?? 'customer';
                    
                    return true;
                }
            }
        }
        
        return false;
    }
    
    // Logout user
    public function logout() {
        // Clear session data
        session_unset();
        session_destroy();
        
        return true;
    }
    
    // Update user profile
    public function updateProfile() {
        // Validate input data
        if (empty($this->userID)) {
            return false;
        }
        
        // Get users data
        $users = readJsonFile(USERS_FILE);
        $updated = false;
        
        // Find and update user data
        foreach ($users as $userType => &$userList) {
            foreach ($userList as $key => $user) {
                if ($user['userID'] === $this->userID) {
                    // Don't update password if it's empty
                    if (empty($this->password)) {
                        $this->password = $user['password'];
                    } else {
                        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
                    }
                    
                    // Update user data
                    $userList[$key] = $this->toArray();
                    $updated = true;
                    break 2;
                }
            }
        }
        
        if ($updated) {
            writeJsonFile(USERS_FILE, $users);
            return true;
        }
        
        return false;
    }
    
    // Convert user object to array
    public function toArray() {
        return [
            'userID' => $this->userID,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'birthday' => $this->birthday,
            'gender' => $this->gender,
            'sex' => $this->sex,
            'address' => $this->address,
            'role' => $this->role
        ];
    }
    
    // Getters and setters
    public function getUserID() {
        return $this->userID;
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function setUsername($username) {
        $this->username = $username;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function setEmail($email) {
        $this->email = $email;
    }
    
    public function setPassword($password) {
        $this->password = $password;
    }
    
    public function getBirthday() {
        return $this->birthday;
    }
    
    public function setBirthday($birthday) {
        $this->birthday = $birthday;
    }
    
    public function getGender() {
        return $this->gender;
    }
    
    public function setGender($gender) {
        $this->gender = $gender;
    }
    
    public function getSex() {
        return $this->sex;
    }
    
    public function setSex($sex) {
        $this->sex = $sex;
    }
    
    public function getAddress() {
        return $this->address;
    }
    
    public function setAddress($address) {
        $this->address = $address;
    }
    
    public function getRole() {
        return $this->role;
    }
    
    public function setRole($role) {
        $this->role = $role;
    }
}
?>