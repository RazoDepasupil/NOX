<?php
require_once 'includes/init.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method.');
    redirect('register.php');
}

// Get and sanitize input
$name = sanitizeInput($_POST['name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($name) || empty($email) || empty($password)) {
    setFlashMessage('error', 'Please fill in all fields.');
    redirect('register.php');
}

if (!validateEmail($email)) {
    setFlashMessage('error', 'Please enter a valid email address.');
    redirect('register.php');
}

if (strlen($password) < 8) {
    setFlashMessage('error', 'Password must be at least 8 characters long.');
    redirect('register.php');
}

// Create customer object
$customer = new Customer([
    'username' => $name,
    'email' => $email,
    'password' => $password,
    'role' => 'customer'
]);

// Attempt registration
if ($customer->register()) {
    // Registration successful
    setFlashMessage('success', 'Registration successful! Please login.');
    redirect('login.php');
} else {
    // Registration failed
    setFlashMessage('error', 'Email address already exists.');
    redirect('register.php');
} 