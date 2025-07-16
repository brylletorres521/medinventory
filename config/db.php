<?php
// Database configuration - Using default port 3306
$username = "root";
$password = "";
$database = "medical_inventory";

// Create connection with error handling
try {
    // Connect using localhost (socket) first
    $conn = @new mysqli("localhost", $username, $password);
    
    // If that fails, try explicit TCP connection on port 3306
    if ($conn->connect_error) {
        $conn = @new mysqli("127.0.0.1", $username, $password, "", 3306);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
    }
    
    // Check if database exists, if not create it
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'");
    if (!$result || $result->num_rows == 0) {
        // Database doesn't exist, create it
        $sql = "CREATE DATABASE IF NOT EXISTS $database";
        if ($conn->query($sql) === TRUE) {
            echo "<script>console.log('Database created successfully');</script>";
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }
    }
    
    // Select the database
    $conn->select_db($database);
    
} catch (Exception $e) {
    die("
        <div style='margin: 50px auto; max-width: 600px; text-align: center; font-family: Arial, sans-serif;'>
            <h2 style='color: #d9534f;'>Database Connection Error</h2>
            <p>Could not connect to the MySQL server. Please make sure MySQL is running.</p>
            <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin-top: 20px; text-align: left;'>
                <strong>Error details:</strong> " . $e->getMessage() . "
            </div>
            <div style='margin-top: 20px; background-color: #f8f9fa; border: 1px solid #ddd; padding: 15px; border-radius: 5px; text-align: left;'>
                <h3>Troubleshooting steps:</h3>
                <ol style='text-align: left;'>
                    <li>Make sure XAMPP is running (check MySQL service)</li>
                    <li>Open XAMPP Control Panel and click 'Start' next to MySQL</li>
                    <li>Verify MySQL is running on port 3306</li>
                    <li>If MySQL won't start, check XAMPP error logs</li>
                    <li>Try restarting XAMPP completely</li>
                </ol>
                <p><a href='mysql_port_test.php' style='color: #007bff;'>Run MySQL Port Test</a> | <a href='check.php' style='color: #007bff;'>Run Diagnostics</a></p>
            </div>
        </div>
    ");
}
?> 