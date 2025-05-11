<?php
require_once 'includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlashMessage('error', 'Invalid request method.');
    redirect('contact.php');
}

// Get and sanitize input
$name = sanitizeInput($_POST['name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$subject = sanitizeInput($_POST['subject'] ?? '');
$message = sanitizeInput($_POST['message'] ?? '');

// Validate input
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    setFlashMessage('error', 'Please fill in all fields.');
    redirect('contact.php');
}

if (!validateEmail($email)) {
    setFlashMessage('error', 'Please enter a valid email address.');
    redirect('contact.php');
}

// In a real application, you would send an email here
// For now, we'll just show a success message
setFlashMessage('success', 'Thank you for your message! We will get back to you soon.');
redirect('contact.php'); 