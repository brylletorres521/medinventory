<?php
session_start();
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Medical Inventory/login.php");
    exit();
}

require_once '../config/db.php';

// Get date range for reports
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

// Inventory Summary Report
$inventory_summary_query = "SELECT 
    COUNT(*) as total_items,
    SUM(quantity) as total_quantity,
    SUM(quantity * unit_price) as total_value
    FROM inventory";
$inventory_summary = $conn->query($inventory_summary_query)->fetch_assoc();

// Low Stock Report
$low_stock_query = "SELECT i.*, m.name, m.generic_name, c.name as category_name
    FROM inventory i 
    JOIN medicines m ON i.medicine_id = m.id 
    LEFT JOIN categories c ON m.category_id = c.id 
    WHERE i.quantity <= 10 
    ORDER BY i.quantity ASC";
$low_stock_result = $conn->query($low_stock_query);

// Expiring Medicines Report
$expiring_query = "SELECT i.*, m.name, m.generic_name, c.name as category_name
    FROM inventory i 
    JOIN medicines m ON i.medicine_id = m.id 
    LEFT JOIN categories c ON m.category_id = c.id 
    WHERE i.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
    ORDER BY i.expiry_date ASC";
$expiring_result = $conn->query($expiring_query);

// Transaction Summary Report
$transaction_summary_query = "SELECT 
    transaction_type,
    COUNT(*) as count,
    SUM(quantity) as total_quantity,
    SUM(total_amount) as total_amount
    FROM inventory_transactions 
    WHERE DATE(transaction_date) BETWEEN '$date_from' AND '$date_to'
    GROUP BY transaction_type";
$transaction_summary_result = $conn->query($transaction_summary_query);

// Top Selling Medicines
$top_selling_query = "SELECT 
    m.name, m.generic_name,
    SUM(t.quantity) as total_sold,
    SUM(t.total_amount) as total_revenue
    FROM inventory_transactions t
    JOIN inventory i ON t.inventory_id = i.id
    JOIN medicines m ON i.medicine_id = m.id
    WHERE t.transaction_type = 'sale' 
    AND DATE(t.transaction_date) BETWEEN '$date_from' AND '$date_to'
    GROUP BY m.id
    ORDER BY total_sold DESC
    LIMIT 10";
$top_selling_result = $conn->query($top_selling_query);

// Category-wise Inventory
$category_inventory_query = "SELECT 
    c.name as category_name,
    COUNT(DISTINCT i.medicine_id) as medicine_count,
    SUM(i.quantity) as total_quantity,
    SUM(i.quantity * i.unit_price) as total_value
    FROM inventory i
    JOIN medicines m ON i.medicine_id = m.id
    LEFT JOIN categories c ON m.category_id = c.id
    GROUP BY c.id
    ORDER BY total_value DESC";
$category_inventory_result = $conn->query($category_inventory_query);

include '../includes/header.php';
?>

<h1 class="h3 mb-4">Reports & Analytics</h1>

<!-- Date Range Filter -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="m-0"><i class="fas fa-calendar mr-2"></i>Date Range</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Date From</label>
                    <input type="date" class="form-control" name="date_from" value="<?php echo $date_from; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Date To</label>
                    <input type="date" class="form-control" name="date_to" value="<?php echo $date_to; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-1"></i>Generate Reports
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Inventory Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Inventory Items
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($inventory_summary['total_items']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Quantity
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo number_format($inventory_summary['total_quantity']); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-pills fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Inventory Value
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            $<?php echo number_format($inventory_summary['total_value'], 2); ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Low Stock Items
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php echo $low_stock_result->num_rows; ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Summary -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="m-0"><i class="fas fa-chart-bar mr-2"></i>Transaction Summary (<?php echo date('M d', strtotime($date_from)); ?> - <?php echo date('M d', strtotime($date_to)); ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Transaction Type</th>
                                <th>Count</th>
                                <th>Quantity</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $transaction_summary_result->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="badge badge-<?php echo $row['transaction_type'] == 'purchase' ? 'success' : ($row['transaction_type'] == 'sale' ? 'primary' : 'info'); ?>"><?php echo ucfirst($row['transaction_type']); ?></span></td>
                                    <td><?php echo $row['count']; ?></td>
                                    <td><?php echo number_format($row['total_quantity']); ?></td>
                                    <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="m-0"><i class="fas fa-chart-pie mr-2"></i>Category-wise Inventory</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Medicines</th>
                                <th>Quantity</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $category_inventory_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['category_name'] ?: 'Uncategorized'); ?></td>
                                    <td><?php echo $row['medicine_count']; ?></td>
                                    <td><?php echo number_format($row['total_quantity']); ?></td>
                                    <td>$<?php echo number_format($row['total_value'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Alert -->
<div class="card mb-4">
    <div class="card-header bg-warning text-white">
        <h5 class="m-0"><i class="fas fa-exclamation-triangle mr-2"></i>Low Stock Alert (≤10 units)</h5>
    </div>
    <div class="card-body">
        <?php if ($low_stock_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Category</th>
                            <th>Batch Number</th>
                            <th>Current Stock</th>
                            <th>Unit Price</th>
                            <th>Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $low_stock_result->fetch_assoc()): ?>
                            <tr class="<?php echo $row['quantity'] <= 5 ? 'table-danger' : 'table-warning'; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['generic_name']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['category_name'] ?: 'Uncategorized'); ?></td>
                                <td><?php echo htmlspecialchars($row['batch_number']); ?></td>
                                <td><span class="badge badge-<?php echo $row['quantity'] <= 5 ? 'danger' : 'warning'; ?>"><?php echo $row['quantity']; ?></span></td>
                                <td>$<?php echo number_format($row['unit_price'], 2); ?></td>
                                <td>$<?php echo number_format($row['quantity'] * $row['unit_price'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-2"></i> No low stock items found.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Expiring Medicines -->
<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <h5 class="m-0"><i class="fas fa-clock mr-2"></i>Expiring Medicines (Next 6 Months)</h5>
    </div>
    <div class="card-body">
        <?php if ($expiring_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Category</th>
                            <th>Batch Number</th>
                            <th>Quantity</th>
                            <th>Expiry Date</th>
                            <th>Days Left</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $expiring_result->fetch_assoc()): 
                            $expiry_date = new DateTime($row['expiry_date']);
                            $today = new DateTime();
                            $days_left = $today->diff($expiry_date)->days;
                            $row_class = $days_left <= 30 ? 'table-danger' : ($days_left <= 90 ? 'table-warning' : '');
                        ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['generic_name']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['category_name'] ?: 'Uncategorized'); ?></td>
                                <td><?php echo htmlspecialchars($row['batch_number']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['expiry_date'])); ?></td>
                                <td><span class="badge badge-<?php echo $days_left <= 30 ? 'danger' : ($days_left <= 90 ? 'warning' : 'info'); ?>"><?php echo $days_left; ?> days</span></td>
                                <td>$<?php echo number_format($row['quantity'] * $row['unit_price'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-2"></i> No medicines are expiring in the next 6 months.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Top Selling Medicines -->
<div class="card">
    <div class="card-header">
        <h5 class="m-0"><i class="fas fa-trophy mr-2"></i>Top Selling Medicines (<?php echo date('M d', strtotime($date_from)); ?> - <?php echo date('M d', strtotime($date_to)); ?>)</h5>
    </div>
    <div class="card-body">
        <?php if ($top_selling_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Medicine</th>
                            <th>Generic Name</th>
                            <th>Units Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        while ($row = $top_selling_result->fetch_assoc()): 
                        ?>
                            <tr>
                                <td>
                                    <?php if ($rank <= 3): ?>
                                        <span class="badge badge-<?php echo $rank == 1 ? 'warning' : ($rank == 2 ? 'secondary' : 'danger'); ?>"><?php echo $rank; ?></span>
                                    <?php else: ?>
                                        <?php echo $rank; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($row['generic_name']); ?></td>
                                <td><?php echo number_format($row['total_sold']); ?></td>
                                <td><strong>$<?php echo number_format($row['total_revenue'], 2); ?></strong></td>
                            </tr>
                        <?php 
                        $rank++;
                        endwhile; 
                        ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i> No sales data available for the selected period.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 