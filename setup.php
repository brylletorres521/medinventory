<?php
// Display all PHP errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Medical Inventory System - Database Setup</h1>";

try {
    // Database configuration
    $username = "root";
    $password = "";
    $database = "medical_inventory";
    
    echo "<div>Attempting to connect to MySQL...</div>";
    
    // Try socket connection first
    $conn = @new mysqli("localhost", $username, $password);
    $connection_method = "socket";
    
    // If socket fails, try TCP connection on port 3306
    if ($conn->connect_error) {
        echo "<div>Socket connection failed. Trying TCP on port 3306...</div>";
        $conn = @new mysqli("127.0.0.1", $username, $password, "", 3306);
        
        if (!$conn->connect_error) {
            $connection_method = "TCP on port 3306";
        } else {
            throw new Exception("Could not connect to MySQL: " . $conn->connect_error);
        }
    }
    
    echo "<div style='color:green;'>✓ Connected to MySQL successfully using $connection_method</div>";
    
    // Create database if not exists
    $sql = "CREATE DATABASE IF NOT EXISTS $database";
    if ($conn->query($sql) === TRUE) {
        echo "<div style='color:green;'>✓ Database created or already exists</div>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db($database);
    echo "<div style='color:green;'>✓ Selected database: $database</div>";
    
    // Read SQL file
    $sql_file = file_get_contents('database.sql');
    
    if (!$sql_file) {
        throw new Exception("Could not read database.sql file");
    }
    
    echo "<div style='color:blue;'>Reading SQL file... (" . strlen($sql_file) . " bytes)</div>";
    
    // Split SQL file into individual statements
    $statements = explode(';', $sql_file);
    $total = count($statements);
    $success = 0;
    
    echo "<div>Executing $total SQL statements...</div>";
    echo "<div style='height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin: 10px 0; background-color: #f8f9fa;'>";
    
    // Execute each statement
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            // Skip database creation statements as we've already handled them
            if (strpos($statement, 'CREATE DATABASE') !== false || 
                strpos($statement, 'USE medical_inventory') !== false) {
                echo "<div>Skipping: " . htmlspecialchars(substr($statement, 0, 50)) . "...</div>";
                $success++;
                continue;
            }
            
            // Execute the statement
            if ($conn->query($statement) === TRUE) {
                echo "<div style='color:green;'>✓ Success: " . htmlspecialchars(substr($statement, 0, 50)) . "...</div>";
                $success++;
            } else {
                echo "<div style='color:red;'>✗ Error: " . $conn->error . " in statement: " . htmlspecialchars(substr($statement, 0, 50)) . "...</div>";
            }
        }
    }
    
    echo "</div>";
    
    echo "<h2>Setup Result</h2>";
    echo "<div>Successfully executed $success out of $total statements</div>";
    
    // Check if tables were created
    $tables = ['users', 'categories', 'suppliers', 'medicines', 'inventory', 'inventory_transactions'];
    $missing_tables = [];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows == 0) {
            $missing_tables[] = $table;
        }
    }
    
    if (empty($missing_tables)) {
        echo "<div style='color:green; font-weight:bold; margin-top:20px;'>✓ All tables created successfully!</div>";
        echo "<div style='color:green;'>The database setup is complete. You can now <a href='login.php'>login</a> to the system.</div>";
        echo "<div style='margin-top:10px;'><strong>Admin credentials:</strong> Username: admin, Password: admin123</div>";
        echo "<div><strong>User credentials:</strong> Username: user, Password: user123</div>";
    } else {
        echo "<div style='color:red; font-weight:bold; margin-top:20px;'>✗ Some tables are missing: " . implode(", ", $missing_tables) . "</div>";
        echo "<div>Please try again or import the database.sql file manually using phpMyAdmin.</div>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div style='color:red; font-weight:bold;'>Error: " . $e->getMessage() . "</div>";
    echo "<div>Please check your MySQL connection settings and try again.</div>";
    echo "<div style='margin-top:20px;'>";
    echo "<a href='mysql_port_test.php' class='btn' style='display:inline-block; margin-right:10px; padding:10px 15px; background-color:#5bc0de; color:white; text-decoration:none; border-radius:4px;'>Run MySQL Port Test</a>";
    echo "</div>";
}

echo "<div style='margin-top:30px;'>";
echo "<a href='index.php' style='margin-right:20px;'>Go to Homepage</a>";
echo "<a href='check.php'>Run Diagnostics</a>";
echo "</div>";
?> 