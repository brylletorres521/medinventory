<?php
session_start();
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Medical Inventory/login.php");
    exit();
}

require_once '../config/db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $inventory_id = (int)$_POST['inventory_id'];
                $transaction_type = $conn->real_escape_string($_POST['transaction_type']);
                $quantity = (int)$_POST['quantity'];
                $unit_price = (float)$_POST['unit_price'];
                $total_amount = $quantity * $unit_price;
                $notes = $conn->real_escape_string($_POST['notes']);
                $user_id = $_SESSION['user_id'];
                
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // Insert transaction record
                    $query = "INSERT INTO inventory_transactions (inventory_id, transaction_type, quantity, unit_price, total_amount, notes, user_id) 
                             VALUES ($inventory_id, '$transaction_type', $quantity, $unit_price, $total_amount, '$notes', $user_id)";
                    $conn->query($query);
                    
                    // Update inventory quantity
                    if ($transaction_type == 'purchase' || $transaction_type == 'return') {
                        $update_query = "UPDATE inventory SET quantity = quantity + $quantity WHERE id = $inventory_id";
                    } else {
                        $update_query = "UPDATE inventory SET quantity = quantity - $quantity WHERE id = $inventory_id";
                    }
                    $conn->query($update_query);
                    
                    $conn->commit();
                    $success_message = "Transaction added successfully!";
                } catch (Exception $e) {
                    $conn->rollback();
                    $error_message = "Error adding transaction: " . $e->getMessage();
                }
                break;
        }
    }
}

// Build filter query
$where_conditions = [];
$params = [];

if (isset($_GET['transaction_type']) && !empty($_GET['transaction_type'])) {
    $where_conditions[] = "t.transaction_type = '" . $conn->real_escape_string($_GET['transaction_type']) . "'";
}

if (isset($_GET['medicine_id']) && !empty($_GET['medicine_id'])) {
    $where_conditions[] = "i.medicine_id = " . (int)$_GET['medicine_id'];
}

if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $where_conditions[] = "DATE(t.transaction_date) >= '" . $conn->real_escape_string($_GET['date_from']) . "'";
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $where_conditions[] = "DATE(t.transaction_date) <= '" . $conn->real_escape_string($_GET['date_to']) . "'";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get transactions with filters
$query = "SELECT t.*, m.name as medicine_name, m.generic_name, i.batch_number, u.username 
          FROM inventory_transactions t 
          JOIN inventory i ON t.inventory_id = i.id 
          JOIN medicines m ON i.medicine_id = m.id 
          JOIN users u ON t.user_id = u.id 
          $where_clause 
          ORDER BY t.transaction_date DESC";
$result = $conn->query($query);

// Get medicines for filter dropdown
$medicines_query = "SELECT id, name FROM medicines ORDER BY name";
$medicines_result = $conn->query($medicines_query);

// Get inventory items for transaction form
$inventory_query = "SELECT i.id, m.name, i.batch_number, i.quantity, i.selling_price 
                   FROM inventory i 
                   JOIN medicines m ON i.medicine_id = m.id 
                   WHERE i.quantity > 0 
                   ORDER BY m.name, i.batch_number";
$inventory_result = $conn->query($inventory_query);

include '../includes/header.php';
?>

<h1 class="h3 mb-4">Transactions Management</h1>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success_message; ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_message; ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="m-0"><i class="fas fa-filter mr-2"></i>Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label>Transaction Type</label>
                    <select class="form-control" name="transaction_type">
                        <option value="">All Types</option>
                        <option value="purchase" <?php echo (isset($_GET['transaction_type']) && $_GET['transaction_type'] == 'purchase') ? 'selected' : ''; ?>>Purchase</option>
                        <option value="sale" <?php echo (isset($_GET['transaction_type']) && $_GET['transaction_type'] == 'sale') ? 'selected' : ''; ?>>Sale</option>
                        <option value="return" <?php echo (isset($_GET['transaction_type']) && $_GET['transaction_type'] == 'return') ? 'selected' : ''; ?>>Return</option>
                        <option value="adjustment" <?php echo (isset($_GET['transaction_type']) && $_GET['transaction_type'] == 'adjustment') ? 'selected' : ''; ?>>Adjustment</option>
                        <option value="expired" <?php echo (isset($_GET['transaction_type']) && $_GET['transaction_type'] == 'expired') ? 'selected' : ''; ?>>Expired</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Medicine</label>
                    <select class="form-control" name="medicine_id">
                        <option value="">All Medicines</option>
                        <?php while ($med = $medicines_result->fetch_assoc()): ?>
                            <option value="<?php echo $med['id']; ?>" <?php echo (isset($_GET['medicine_id']) && $_GET['medicine_id'] == $med['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($med['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Date From</label>
                    <input type="date" class="form-control" name="date_from" value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Date To</label>
                    <input type="date" class="form-control" name="date_to" value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-search mr-1"></i>Filter
                        </button>
                        <a href="transactions.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times mr-1"></i>Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-exchange-alt mr-2"></i>Transaction History</h5>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addTransactionModal">
            <i class="fas fa-plus mr-1"></i>Add Transaction
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Medicine</th>
                        <th>Batch</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total Amount</th>
                        <th>User</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): 
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
                            <td><?php echo date('M d, Y H:i', strtotime($row['transaction_date'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['medicine_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($row['generic_name']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['batch_number']); ?></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(htmlspecialchars($row['transaction_type'])); ?></span></td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td>$<?php echo number_format($row['unit_price'], 2); ?></td>
                            <td><strong>$<?php echo number_format($row['total_amount'], 2); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['notes']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Transaction</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Inventory Item *</label>
                                <select class="form-control" name="inventory_id" id="inventory_id" required>
                                    <option value="">Select Inventory Item</option>
                                    <?php while ($inv = $inventory_result->fetch_assoc()): ?>
                                        <option value="<?php echo $inv['id']; ?>" data-price="<?php echo $inv['selling_price']; ?>">
                                            <?php echo htmlspecialchars($inv['name'] . ' - Batch: ' . $inv['batch_number'] . ' (Qty: ' . $inv['quantity'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Transaction Type *</label>
                                <select class="form-control" name="transaction_type" required>
                                    <option value="">Select Type</option>
                                    <option value="purchase">Purchase</option>
                                    <option value="sale">Sale</option>
                                    <option value="return">Return</option>
                                    <option value="adjustment">Adjustment</option>
                                    <option value="expired">Expired</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Quantity *</label>
                                <input type="number" class="form-control" name="quantity" id="quantity" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Unit Price *</label>
                                <input type="number" class="form-control" name="unit_price" id="unit_price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Total Amount</label>
                                <input type="text" class="form-control" id="total_amount" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Optional notes about this transaction"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Calculate total amount when quantity or unit price changes
document.getElementById('quantity').addEventListener('input', calculateTotal);
document.getElementById('unit_price').addEventListener('input', calculateTotal);

// Set unit price when inventory item is selected
document.getElementById('inventory_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const unitPrice = selectedOption.getAttribute('data-price');
    if (unitPrice) {
        document.getElementById('unit_price').value = unitPrice;
        calculateTotal();
    }
});

function calculateTotal() {
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
    const total = quantity * unitPrice;
    document.getElementById('total_amount').value = '$' + total.toFixed(2);
}
</script>

<?php include '../includes/footer.php'; ?> 