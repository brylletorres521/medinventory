<?php
// Display all PHP errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Medical Inventory System - Fix Passwords</h1>";

try {
    // Database configuration
    $username = "root";
    $password = "";
    $database = "medical_inventory";
    
    // Connect using localhost (socket) first
    $conn = @new mysqli("localhost", $username, $password);
    
    // If that fails, try explicit TCP connection on port 3306
    if ($conn->connect_error) {
        $conn = @new mysqli("127.0.0.1", $username, $password, "", 3306);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
    }
    
    echo "<div style='color:green;'>✓ Connected to MySQL successfully</div>";
    
    // Select the database
    $conn->select_db($database);
    echo "<div style='color:green;'>✓ Selected database: $database</div>";
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 0) {
        throw new Exception("Users table does not exist. Please run setup.php first.");
    }
    
    // Generate password hashes
    $admin_password = 'admin123';
    $user_password = 'user123';
    
    $admin_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    $user_hash = password_hash($user_password, PASSWORD_DEFAULT);
    
    echo "<div>Generated password hashes:</div>";
    echo "<div>Admin: " . $admin_hash . "</div>";
    echo "<div>User: " . $user_hash . "</div>";
    
    // Update admin password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->bind_param("s", $admin_hash);
    if ($stmt->execute()) {
        echo "<div style='color:green;'>✓ Admin password updated successfully</div>";
    } else {
        echo "<div style='color:red;'>✗ Failed to update admin password: " . $stmt->error . "</div>";
    }
    $stmt->close();
    
    // Update user password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'user'");
    $stmt->bind_param("s", $user_hash);
    if ($stmt->execute()) {
        echo "<div style='color:green;'>✓ User password updated successfully</div>";
    } else {
        echo "<div style='color:red;'>✗ Failed to update user password: " . $stmt->error . "</div>";
    }
    $stmt->close();
    
    echo "<div style='margin-top:20px; padding:15px; background-color:#dff0d8; border:1px solid #d6e9c6; color:#3c763d;'>";
    echo "<h3>Passwords Reset Successfully!</h3>";
    echo "<p>You can now log in with the following credentials:</p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> Username: admin, Password: admin123</li>";
    echo "<li><strong>User:</strong> Username: user, Password: user123</li>";
    echo "</ul>";
    echo "<a href='login.php' style='display:inline-block; margin-top:10px; padding:10px 15px; background-color:#5cb85c; color:white; text-decoration:none; border-radius:4px;'>Go to Login Page</a>";
    echo "</div>";
    
    // Option to create additional test users
    echo "<h2>Create Additional Test User</h2>";
    echo "<form method='post' action='fix_passwords.php'>";
    echo "<div style='margin-bottom:10px;'>";
    echo "<label for='new_username'>Username: </label>";
    echo "<input type='text' name='new_username' id='new_username' required>";
    echo "</div>";
    echo "<div style='margin-bottom:10px;'>";
    echo "<label for='new_password'>Password: </label>";
    echo "<input type='password' name='new_password' id='new_password' required>";
    echo "</div>";
    echo "<div style='margin-bottom:10px;'>";
    echo "<label for='new_role'>Role: </label>";
    echo "<select name='new_role' id='new_role'>";
    echo "<option value='user'>User</option>";
    echo "<option value='admin'>Admin</option>";
    echo "</select>";
    echo "</div>";
    echo "<button type='submit' name='create_user' style='padding:5px 10px; background-color:#5bc0de; color:white; border:none; border-radius:4px;'>Create User</button>";
    echo "</form>";
    
    // Process new user creation
    if (isset($_POST['create_user'])) {
        $new_username = trim($_POST['new_username']);
        $new_password = trim($_POST['new_password']);
        $new_role = $_POST['new_role'];
        
        if (!empty($new_username) && !empty($new_password)) {
            // Check if username already exists
            $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check->bind_param("s", $new_username);
            $check->execute();
            $result = $check->get_result();
            
            if ($result->num_rows > 0) {
                echo "<div style='color:red; margin-top:10px;'>✗ Username already exists</div>";
            } else {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $insert = $conn->prepare("INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
                $full_name = $new_username;
                $email = $new_username . "@example.com";
                $insert->bind_param("sssss", $new_username, $new_hash, $full_name, $email, $new_role);
                
                if ($insert->execute()) {
                    echo "<div style='color:green; margin-top:10px;'>✓ New user created successfully</div>";
                } else {
                    echo "<div style='color:red; margin-top:10px;'>✗ Failed to create new user: " . $insert->error . "</div>";
                }
                $insert->close();
            }
            $check->close();
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div style='color:red; font-weight:bold;'>Error: " . $e->getMessage() . "</div>";
    echo "<div>Please check your MySQL connection settings and try again.</div>";
}

echo "<div style='margin-top:30px;'>";
echo "<a href='index.php' style='margin-right:20px;'>Go to Homepage</a>";
echo "<a href='setup.php' style='margin-right:20px;'>Run Setup Script</a>";
echo "<a href='check.php'>Run Diagnostics</a>";
echo "</div>";
?> 