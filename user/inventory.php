<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Medical Inventory/login.php");
    exit();
}

require_once '../config/db.php';

// Get all categories for filter
$categories_query = "SELECT id, name FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

// Get all suppliers for filter
$suppliers_query = "SELECT id, name FROM suppliers ORDER BY name";
$suppliers_result = $conn->query($suppliers_query);

// Initialize filter variables
$filter_name = isset($_GET['name']) ? $_GET['name'] : '';
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$filter_supplier = isset($_GET['supplier']) ? $_GET['supplier'] : '';
$filter_expiry = isset($_GET['expiry']) ? $_GET['expiry'] : '';
$filter_batch = isset($_GET['batch']) ? $_GET['batch'] : '';
$filter_location = isset($_GET['location']) ? $_GET['location'] : '';

// Build query based on filters
$query = "SELECT i.id, i.batch_number, i.quantity, i.selling_price, 
          i.expiry_date, i.manufacturing_date, 
          m.name AS medicine_name, m.generic_name, m.storage_location, m.unit,
          c.name AS category_name, s.name AS supplier_name
          FROM inventory i
          JOIN medicines m ON i.medicine_id = m.id
          LEFT JOIN categories c ON m.category_id = c.id
          LEFT JOIN suppliers s ON m.supplier_id = s.id
          WHERE 1=1";

$params = array();
$types = "";

if (!empty($filter_name)) {
    $query .= " AND (m.name LIKE ? OR m.generic_name LIKE ?)";
    $search_term = "%{$filter_name}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

if (!empty($filter_category)) {
    $query .= " AND m.category_id = ?";
    $params[] = $filter_category;
    $types .= "i";
}

if (!empty($filter_supplier)) {
    $query .= " AND m.supplier_id = ?";
    $params[] = $filter_supplier;
    $types .= "i";
}

if (!empty($filter_batch)) {
    $query .= " AND i.batch_number LIKE ?";
    $params[] = "%{$filter_batch}%";
    $types .= "s";
}

if (!empty($filter_location)) {
    $query .= " AND m.storage_location LIKE ?";
    $params[] = "%{$filter_location}%";
    $types .= "s";
}

// Handle expiry date filter
if (!empty($filter_expiry)) {
    switch ($filter_expiry) {
        case 'expired':
            $query .= " AND i.expiry_date < CURDATE()";
            break;
        case '1month':
            $query .= " AND i.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
            break;
        case '3months':
            $query .= " AND i.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 MONTH)";
            break;
        case '6months':
            $query .= " AND i.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 MONTH)";
            break;
        case 'valid':
            $query .= " AND i.expiry_date > CURDATE()";
            break;
    }
}

$query .= " ORDER BY i.expiry_date ASC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Include header
include '../includes/header.php';
?>

<h1 class="h3 mb-4">Inventory</h1>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="m-0"><i class="fas fa-filter mr-2"></i>Filter Inventory</h5>
    </div>
    <div class="card-body">
        <form method="get" action="" class="row">
            <div class="form-group col-md-4">
                <label for="name">Medicine Name/Generic</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($filter_name); ?>">
            </div>
            
            <div class="form-group col-md-4">
                <label for="category">Category</label>
                <select class="form-control" id="category" name="category">
                    <option value="">All Categories</option>
                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo ($filter_category == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group col-md-4">
                <label for="supplier">Supplier</label>
                <select class="form-control" id="supplier" name="supplier">
                    <option value="">All Suppliers</option>
                    <?php while ($supplier = $suppliers_result->fetch_assoc()): ?>
                        <option value="<?php echo $supplier['id']; ?>" <?php echo ($filter_supplier == $supplier['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($supplier['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group col-md-4">
                <label for="batch">Batch Number</label>
                <input type="text" class="form-control" id="batch" name="batch" value="<?php echo htmlspecialchars($filter_batch); ?>">
            </div>
            
            <div class="form-group col-md-4">
                <label for="location">Storage Location</label>
                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($filter_location); ?>">
            </div>
            
            <div class="form-group col-md-4">
                <label for="expiry">Expiry Status</label>
                <select class="form-control" id="expiry" name="expiry">
                    <option value="">All Items</option>
                    <option value="expired" <?php echo ($filter_expiry == 'expired') ? 'selected' : ''; ?>>Expired</option>
                    <option value="1month" <?php echo ($filter_expiry == '1month') ? 'selected' : ''; ?>>Expiring in 1 Month</option>
                    <option value="3months" <?php echo ($filter_expiry == '3months') ? 'selected' : ''; ?>>Expiring in 3 Months</option>
                    <option value="6months" <?php echo ($filter_expiry == '6months') ? 'selected' : ''; ?>>Expiring in 6 Months</option>
                    <option value="valid" <?php echo ($filter_expiry == 'valid') ? 'selected' : ''; ?>>Valid (Not Expired)</option>
                </select>
            </div>
            
            <div class="col-12 text-right">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-1"></i> Apply Filters
                </button>
                <a href="inventory.php" class="btn btn-secondary">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Inventory Table -->
<div class="card">
    <div class="card-body">
        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover datatable">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Batch</th>
                            <th>Quantity</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Location</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): 
                            $expiry_date = new DateTime($row['expiry_date']);
                            $today = new DateTime();
                            $is_expired = $expiry_date < $today;
                            $days_left = $today->diff($expiry_date)->days;
                            
                            $status_class = '';
                            $status_text = '';
                            
                            if ($is_expired) {
                                $status_class = 'badge-danger';
                                $status_text = 'Expired';
                            } elseif ($days_left <= 30) {
                                $status_class = 'badge-danger';
                                $status_text = 'Expires in ' . $days_left . ' days';
                            } elseif ($days_left <= 90) {
                                $status_class = 'badge-warning';
                                $status_text = 'Expires in ' . $days_left . ' days';
                            } elseif ($days_left <= 180) {
                                $status_class = 'badge-info';
                                $status_text = 'Expires in ' . $days_left . ' days';
                            } else {
                                $status_class = 'badge-success';
                                $status_text = 'Valid';
                            }
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['medicine_name']); ?></strong>
                                    <?php if (!empty($row['generic_name'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($row['generic_name']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['batch_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity']) . ' ' . htmlspecialchars($row['unit']); ?></td>
                                <td><?php echo htmlspecialchars($row['category_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['supplier_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['storage_location']); ?></td>
                                <td><?php echo htmlspecialchars($row['expiry_date']); ?></td>
                                <td><span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                <td>
                                    <a href="view_inventory.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info btn-action" data-toggle="tooltip" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="add_transaction.php?inventory_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success btn-action" data-toggle="tooltip" title="Add Transaction">
                                        <i class="fas fa-exchange-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i> No inventory items found matching your criteria.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 