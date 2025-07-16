<?php
session_start();
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Medical Inventory/login.php");
    exit();
}

require_once '../config/db.php';

// Get total medicines count
$query = "SELECT COUNT(*) as total FROM medicines";
$result = $conn->query($query);
$total_medicines = $result->fetch_assoc()['total'];

// Get total inventory items count
$query = "SELECT COUNT(*) as total FROM inventory";
$result = $conn->query($query);
$total_inventory = $result->fetch_assoc()['total'];

// Get total categories count
$query = "SELECT COUNT(*) as total FROM categories";
$result = $conn->query($query);
$total_categories = $result->fetch_assoc()['total'];

// Get total suppliers count
$query = "SELECT COUNT(*) as total FROM suppliers";
$result = $conn->query($query);
$total_suppliers = $result->fetch_assoc()['total'];

// Get medicines expiring in 3 months
$query = "SELECT i.id, m.name, m.generic_name, i.batch_number, i.quantity, i.expiry_date 
          FROM inventory i 
          JOIN medicines m ON i.medicine_id = m.id 
          WHERE i.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 MONTH) 
          ORDER BY i.expiry_date ASC";
$expiring_result = $conn->query($query);

// Get recent transactions
$query = "SELECT t.id, t.transaction_type, t.quantity, t.transaction_date, m.name, u.username 
          FROM inventory_transactions t 
          JOIN inventory i ON t.inventory_id = i.id 
          JOIN medicines m ON i.medicine_id = m.id 
          JOIN users u ON t.user_id = u.id 
          ORDER BY t.transaction_date DESC LIMIT 5";
$transactions_result = $conn->query($query);

$conn->close();

// Include header
include '../includes/header.php';
?>

<h1 class="h3 mb-4">Admin Dashboard</h1>

<!-- Dashboard Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="dashboard-icon">
                    <i class="fas fa-pills"></i>
                </div>
                <div class="dashboard-number"><?php echo $total_medicines; ?></div>
                <div class="dashboard-label">Total Medicines</div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="dashboard-icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div class="dashboard-number"><?php echo $total_inventory; ?></div>
                <div class="dashboard-label">Inventory Items</div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="dashboard-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="dashboard-number"><?php echo $total_categories; ?></div>
                <div class="dashboard-label">Categories</div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="dashboard-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="dashboard-number"><?php echo $total_suppliers; ?></div>
                <div class="dashboard-label">Suppliers</div>
            </div>
        </div>
    </div>
</div>

<!-- Expiring Medicines Alert -->
<div class="card mb-4">
    <div class="card-header bg-warning text-white">
        <h5 class="m-0"><i class="fas fa-exclamation-triangle mr-2"></i>Medicines Expiring in 3 Months</h5>
    </div>
    <div class="card-body">
        <?php if ($expiring_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover datatable">
                    <thead>
                        <tr>
                            <th>Medicine Name</th>
                            <th>Generic Name</th>
                            <th>Batch Number</th>
                            <th>Quantity</th>
                            <th>Expiry Date</th>
                            <th>Days Left</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $expiring_result->fetch_assoc()): 
                            $expiry_date = new DateTime($row['expiry_date']);
                            $today = new DateTime();
                            $days_left = $today->diff($expiry_date)->days;
                            $row_class = $days_left <= 30 ? 'table-danger' : ($days_left <= 60 ? 'table-warning' : '');
                        ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['generic_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['batch_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($row['expiry_date']); ?></td>
                                <td><?php echo $days_left; ?> days</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-2"></i> No medicines are expiring in the next 3 months.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="m-0"><i class="fas fa-exchange-alt mr-2"></i>Recent Transactions</h5>
    </div>
    <div class="card-body">
        <?php if ($transactions_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Medicine</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Date</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $transactions_result->fetch_assoc()): 
                            $badge_class = '';
                            switch ($row['transaction_type']) {
                                case 'purchase':
                                    $badge_class = 'badge-success';
                                    break;
                                case 'sale':
                                    $badge_class = 'badge-primary';
                                    break;
                                case 'return':
                                    $badge_class = 'badge-info';
                                    break;
                                case 'adjustment':
                                    $badge_class = 'badge-warning';
                                    break;
                                case 'expired':
                                    $badge_class = 'badge-danger';
                                    break;
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(htmlspecialchars($row['transaction_type'])); ?></span></td>
                                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($row['transaction_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-right mt-3">
                <a href="transactions.php" class="btn btn-sm btn-outline-primary">View All Transactions</a>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i> No recent transactions found.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 