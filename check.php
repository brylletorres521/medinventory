<?php
// Display all PHP errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Medical Inventory System - Server Check</h1>";

// Check PHP version
echo "<h2>PHP Version</h2>";
echo "Current PHP version: " . phpversion();
echo "<br>Required: PHP 7.4 or higher";
echo "<br>Status: " . (version_compare(phpversion(), '7.4.0') >= 0 ? '<span style="color:green">✓ OK</span>' : '<span style="color:red">✗ Upgrade needed</span>');

// Check MySQL connection
echo "<h2>Database Connection</h2>";
try {
    $host = "127.0.0.1";
    $port = 3306; // Updated to default port
    $username = "root";
    $password = "";
    $database = "medical_inventory";
    
    // Try connecting with socket first
    $conn = @new mysqli("localhost", $username, $password);
    $connection_method = "socket";
    
    // If socket fails, try TCP connection
    if ($conn->connect_error) {
        $conn = @new mysqli($host, $username, $password, "", $port);
        if (!$conn->connect_error) {
            $connection_method = "TCP on port 3306";
        } else {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
    }
    
    echo "MySQL connection: <span style=\"color:green\">✓ Connected successfully using $connection_method</span>";
    echo "<br>MySQL version: " . $conn->server_info;
    
    // Check if database exists
    $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'");
    if ($result && $result->num_rows > 0) {
        echo "<br>Database '$database': <span style=\"color:green\">✓ Exists</span>";
        
        // Select the database
        $conn->select_db($database);
        
        // Check if tables exist
        echo "<h3>Database Tables</h3>";
        $tables = ['users', 'categories', 'suppliers', 'medicines', 'inventory', 'inventory_transactions'];
        $missing_tables = [];
        
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows == 0) {
                $missing_tables[] = $table;
            }
        }
        
        if (empty($missing_tables)) {
            echo "All required tables exist: <span style=\"color:green\">✓ OK</span>";
        } else {
            echo "Missing tables: <span style=\"color:red\">✗ " . implode(", ", $missing_tables) . "</span>";
            echo "<br>Please import the database.sql file to create all required tables.";
            echo "<br><a href=\"database.sql\" download>Download database.sql</a> and import it using phpMyAdmin.";
            echo "<br>Or <a href=\"setup.php\">run the setup script</a> to create the tables automatically.";
        }
    } else {
        echo "<br>Database '$database': <span style=\"color:red\">✗ Does not exist</span>";
        echo "<br>Please create the database and import the database.sql file.";
        echo "<br>Or <a href=\"setup.php\">run the setup script</a> to create the database automatically.";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "MySQL connection: <span style=\"color:red\">✗ Failed</span>";
    echo "<br>Error: " . $e->getMessage();
    echo "<br><br>Possible solutions:";
    echo "<ul>";
    echo "<li>Make sure MySQL service is running in XAMPP</li>";
    echo "<li>Verify MySQL is using port 3306 in XAMPP Control Panel</li>";
    echo "<li>Check that the database 'medical_inventory' exists</li>";
    echo "<li>Verify the database credentials in config/db.php</li>";
    echo "</ul>";
}

// Check file permissions
echo "<h2>File Permissions</h2>";
$writable_dirs = ['.', 'admin', 'user', 'config', 'includes', 'assets'];
$not_writable = [];

foreach ($writable_dirs as $dir) {
    if (!is_writable($dir)) {
        $not_writable[] = $dir;
    }
}

if (empty($not_writable)) {
    echo "All directories are writable: <span style=\"color:green\">✓ OK</span>";
} else {
    echo "Non-writable directories: <span style=\"color:red\">✗ " . implode(", ", $not_writable) . "</span>";
    echo "<br>Please check permissions on these directories.";
}

// Check required PHP extensions
echo "<h2>PHP Extensions</h2>";
$required_extensions = ['mysqli', 'pdo', 'pdo_mysql', 'json', 'session'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (empty($missing_extensions)) {
    echo "All required PHP extensions are loaded: <span style=\"color:green\">✓ OK</span>";
} else {
    echo "Missing PHP extensions: <span style=\"color:red\">✗ " . implode(", ", $missing_extensions) . "</span>";
    echo "<br>Please enable these extensions in your PHP configuration.";
}

// Server information
echo "<h2>Server Information</h2>";
echo "Server software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Server name: " . $_SERVER['SERVER_NAME'] . "<br>";
echo "Server port: " . $_SERVER['SERVER_PORT'] . "<br>";
echo "Current script: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";

// Navigation links
echo "<h2>Navigation</h2>";
echo "<ul>";
echo "<li><a href=\"index.php\">Go to Application</a></li>";
echo "<li><a href=\"http://localhost/phpmyadmin\" target=\"_blank\">Open phpMyAdmin</a></li>";
echo "<li><a href=\"setup.php\">Run Setup Script</a></li>";
echo "<li><a href=\"database.sql\" download>Download Database SQL</a></li>";
echo "</ul>";

// Troubleshooting
echo "<h2>Troubleshooting Tips</h2>";
echo "<ol>";
echo "<li>Make sure both Apache and MySQL services are running in XAMPP</li>";
echo "<li>Verify MySQL is using port 3306 in XAMPP Control Panel</li>";
echo "<li>Import the database.sql file using phpMyAdmin or run the setup.php script</li>";
echo "<li>Check that the database credentials in config/db.php match your setup</li>";
echo "<li>Ensure all files are in the correct directory: C:\\xampp\\htdocs\\Medical Inventory\\</li>";
echo "<li>Try accessing the application directly at <a href=\"http://localhost/Medical%20Inventory/\">http://localhost/Medical%20Inventory/</a></li>";
echo "</ol>";
?> 