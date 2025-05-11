<?php
require_once 'includes/init.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method.');
    redirect('login.php');
}

// Get and sanitize input
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    setFlashMessage('error', 'Please fill in all fields.');
    redirect('login.php');
}

if (!validateEmail($email)) {
    setFlashMessage('error', 'Please enter a valid email address.');
    redirect('login.php');
}

// Create user object and attempt login
$user = new User([
    'email' => $email,
    'password' => $password
]);

if ($user->login()) {
    // Login successful
    setFlashMessage('success', 'Welcome back!');
    
    // Redirect based on user role
    if (isAdmin()) {
        redirect('admin/index.php');
    } else {
        redirect('index.php');
    }
} else {
    // Login failed
    setFlashMessage('error', 'Invalid email or password.');
    redirect('login.php');
} 