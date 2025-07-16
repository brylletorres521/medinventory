<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /Medical Inventory/login.php");
    exit();
}

require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

// Fetch all transactions for the user
$query = "SELECT t.id, t.transaction_type, t.quantity, t.transaction_date, m.name, m.generic_name
          FROM inventory_transactions t
          JOIN inventory i ON t.inventory_id = i.id
          JOIN medicines m ON i.medicine_id = m.id
          WHERE t.user_id = $user_id
          ORDER BY t.transaction_date DESC";
$result = $conn->query($query);

$conn->close();

include '../includes/header.php';
?>

<h1 class="h3 mb-4">My Transactions</h1>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="m-0"><i class="fas fa-exchange-alt mr-2"></i>All Transactions</h5>
    </div>
    <div class="card-body">
        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Medicine</th>
                            <th>Generic Name</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Date</th>
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
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['generic_name']); ?></td>
                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(htmlspecialchars($row['transaction_type'])); ?></span></td>
                                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($row['transaction_date']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i> No transactions found.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 