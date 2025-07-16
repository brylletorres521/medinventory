<?php
session_start();
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

require_once '../config/db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "SELECT * FROM suppliers WHERE id = $id";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $supplier = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($supplier);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Supplier not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID parameter required']);
}

$conn->close();
?> 