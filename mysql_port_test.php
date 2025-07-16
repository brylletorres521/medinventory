<?php
// Display all PHP errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>MySQL Port Detection</h1>";

// Common MySQL ports to try
$ports_to_try = [3306, 8111, 8889, 3307, 3308, 8080, 8090];
$host = "127.0.0.1";
$username = "root";
$password = "";

echo "<h2>Testing MySQL Connection on Different Ports</h2>";

$successful_port = null;

foreach ($ports_to_try as $port) {
    echo "<div>Testing port $port... ";
    $start_time = microtime(true);
    
    try {
        $conn = @new mysqli($host, $username, $password, "", $port);
        
        if (!$conn->connect_error) {
            $successful_port = $port;
            echo "<span style='color:green;'>SUCCESS! Connected to MySQL on port $port</span>";
            echo "<br>MySQL version: " . $conn->server_info;
            $conn->close();
            echo "</div>";
            break;
        } else {
            echo "<span style='color:red;'>Failed: " . $conn->connect_error . "</span>";
        }
    } catch (Exception $e) {
        echo "<span style='color:red;'>Failed: " . $e->getMessage() . "</span>";
    }
    
    $time_taken = round((microtime(true) - $start_time) * 1000, 2);
    echo " (took $time_taken ms)</div>";
}

if ($successful_port) {
    echo "<div style='margin-top:20px; padding:15px; background-color:#dff0d8; border:1px solid #d6e9c6; color:#3c763d;'>";
    echo "<h3>Success! MySQL is running on port $successful_port</h3>";
    echo "<p>Please update your configuration files to use this port:</p>";
    echo "<ol>";
    echo "<li>Edit <strong>config/db.php</strong> and change the port to $successful_port</li>";
    echo "<li>Edit <strong>check.php</strong> and change the port to $successful_port</li>";
    echo "<li>Edit <strong>setup.php</strong> and change the port to $successful_port</li>";
    echo "</ol>";
    echo "<a href='setup.php' style='display:inline-block; margin-top:10px; padding:10px 15px; background-color:#5cb85c; color:white; text-decoration:none; border-radius:4px;'>Run Setup Script</a>";
    echo "</div>";
} else {
    echo "<div style='margin-top:20px; padding:15px; background-color:#f2dede; border:1px solid #ebccd1; color:#a94442;'>";
    echo "<h3>MySQL Connection Failed on All Tested Ports</h3>";
    echo "<p>Possible issues:</p>";
    echo "<ol>";
    echo "<li>MySQL is not running. Start MySQL in XAMPP Control Panel.</li>";
    echo "<li>MySQL is running on a non-standard port not in our test list.</li>";
    echo "<li>MySQL might be configured to only accept local socket connections.</li>";
    echo "<li>Firewall might be blocking connections to MySQL.</li>";
    echo "</ol>";
    echo "</div>";
    
    // Try socket connection as a last resort
    echo "<h2>Trying Socket Connection</h2>";
    try {
        $conn = @new mysqli("localhost", $username, $password);
        
        if (!$conn->connect_error) {
            echo "<div style='color:green;'>SUCCESS! Connected to MySQL using socket connection</div>";
            echo "<div>MySQL version: " . $conn->server_info . "</div>";
            
            echo "<div style='margin-top:20px; padding:15px; background-color:#dff0d8; border:1px solid #d6e9c6; color:#3c763d;'>";
            echo "<h3>Socket Connection Successful</h3>";
            echo "<p>Please update your configuration files to use 'localhost' instead of '127.0.0.1':</p>";
            echo "<ol>";
            echo "<li>Edit <strong>config/db.php</strong> and change the host to 'localhost' and remove the port</li>";
            echo "<li>Edit <strong>check.php</strong> and change the host to 'localhost' and remove the port</li>";
            echo "<li>Edit <strong>setup.php</strong> and change the host to 'localhost' and remove the port</li>";
            echo "</ol>";
            echo "<a href='setup.php' style='display:inline-block; margin-top:10px; padding:10px 15px; background-color:#5cb85c; color:white; text-decoration:none; border-radius:4px;'>Run Setup Script</a>";
            echo "</div>";
            
            $conn->close();
        } else {
            echo "<div style='color:red;'>Socket connection failed: " . $conn->connect_error . "</div>";
        }
    } catch (Exception $e) {
        echo "<div style='color:red;'>Socket connection failed: " . $e->getMessage() . "</div>";
    }
}
?> 