<?php
// Check if we're on Live Server (port 5500)
if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '5500') {
    // Redirect to the correct XAMPP URL
    header("Location: http://localhost/Medical%20Inventory/");
    exit();
}

// Continue with normal processing
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
} 
// User is logged in, redirect to appropriate dashboard based on role
elseif ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit();
} 
else {
    header("Location: user/dashboard.php");
    exit();
}
?> 