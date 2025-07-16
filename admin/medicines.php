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
                $name = $conn->real_escape_string($_POST['name']);
                $generic_name = $conn->real_escape_string($_POST['generic_name']);
                $description = $conn->real_escape_string($_POST['description']);
                $dosage_form = $conn->real_escape_string($_POST['dosage_form']);
                $strength = $conn->real_escape_string($_POST['strength']);
                $category_id = (int)$_POST['category_id'];
                $unit_price = (float)$_POST['unit_price'];
                
                $query = "INSERT INTO medicines (name, generic_name, description, dosage_form, strength, category_id, unit_price) 
                         VALUES ('$name', '$generic_name', '$description', '$dosage_form', '$strength', $category_id, $unit_price)";
                if ($conn->query($query)) {
                    $success_message = "Medicine added successfully!";
                } else {
                    $error_message = "Error adding medicine: " . $conn->error;
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $name = $conn->real_escape_string($_POST['name']);
                $generic_name = $conn->real_escape_string($_POST['generic_name']);
                $description = $conn->real_escape_string($_POST['description']);
                $dosage_form = $conn->real_escape_string($_POST['dosage_form']);
                $strength = $conn->real_escape_string($_POST['strength']);
                $category_id = (int)$_POST['category_id'];
                $unit_price = (float)$_POST['unit_price'];
                
                $query = "UPDATE medicines SET name='$name', generic_name='$generic_name', description='$description', 
                         dosage_form='$dosage_form', strength='$strength', category_id=$category_id, unit_price=$unit_price 
                         WHERE id=$id";
                if ($conn->query($query)) {
                    $success_message = "Medicine updated successfully!";
                } else {
                    $error_message = "Error updating medicine: " . $conn->error;
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $query = "DELETE FROM medicines WHERE id=$id";
                if ($conn->query($query)) {
                    $success_message = "Medicine deleted successfully!";
                } else {
                    $error_message = "Error deleting medicine: " . $conn->error;
                }
                break;
        }
    }
}

// Get categories for dropdown
$categories_query = "SELECT id, name FROM categories ORDER BY name";
$categories_result = $conn->query($categories_query);

// Get medicines with category names
$query = "SELECT m.*, c.name as category_name 
          FROM medicines m 
          LEFT JOIN categories c ON m.category_id = c.id 
          ORDER BY m.name";
$result = $conn->query($query);

include '../includes/header.php';
?>

<h1 class="h3 mb-4">Medicines Management</h1>

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

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="m-0"><i class="fas fa-pills mr-2"></i>Medicines List</h5>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addMedicineModal">
            <i class="fas fa-plus mr-1"></i>Add Medicine
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Generic Name</th>
                        <th>Category</th>
                        <th>Dosage Form</th>
                        <th>Strength</th>
                        <th>Unit Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['generic_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['dosage_form']); ?></td>
                            <td><?php echo htmlspecialchars($row['strength']); ?></td>
                            <td>$<?php echo number_format($row['unit_price'], 2); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editMedicine(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteMedicine(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Medicine Modal -->
<div class="modal fade" id="addMedicineModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Medicine</h5>
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
                                <label>Medicine Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Generic Name *</label>
                                <input type="text" class="form-control" name="generic_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Dosage Form *</label>
                                <select class="form-control" name="dosage_form" required>
                                    <option value="">Select Form</option>
                                    <option value="Tablet">Tablet</option>
                                    <option value="Capsule">Capsule</option>
                                    <option value="Syrup">Syrup</option>
                                    <option value="Injection">Injection</option>
                                    <option value="Cream">Cream</option>
                                    <option value="Ointment">Ointment</option>
                                    <option value="Drops">Drops</option>
                                    <option value="Inhaler">Inhaler</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Strength</label>
                                <input type="text" class="form-control" name="strength" placeholder="e.g., 500mg">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Category *</label>
                                <select class="form-control" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Unit Price *</label>
                        <input type="number" class="form-control" name="unit_price" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Medicine</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Medicine Modal -->
<div class="modal fade" id="editMedicineModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Medicine</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Medicine Name *</label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Generic Name *</label>
                                <input type="text" class="form-control" name="generic_name" id="edit_generic_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Dosage Form *</label>
                                <select class="form-control" name="dosage_form" id="edit_dosage_form" required>
                                    <option value="">Select Form</option>
                                    <option value="Tablet">Tablet</option>
                                    <option value="Capsule">Capsule</option>
                                    <option value="Syrup">Syrup</option>
                                    <option value="Injection">Injection</option>
                                    <option value="Cream">Cream</option>
                                    <option value="Ointment">Ointment</option>
                                    <option value="Drops">Drops</option>
                                    <option value="Inhaler">Inhaler</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Strength</label>
                                <input type="text" class="form-control" name="strength" id="edit_strength" placeholder="e.g., 500mg">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Category *</label>
                                <select class="form-control" name="category_id" id="edit_category_id" required>
                                    <option value="">Select Category</option>
                                    <?php 
                                    $categories_result->data_seek(0);
                                    while ($cat = $categories_result->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Unit Price *</label>
                        <input type="number" class="form-control" name="unit_price" id="edit_unit_price" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Medicine</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteMedicineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this medicine? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editMedicine(id) {
    // Fetch medicine data and populate modal
    fetch(`get_medicine.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_generic_name').value = data.generic_name;
            document.getElementById('edit_description').value = data.description;
            document.getElementById('edit_dosage_form').value = data.dosage_form;
            document.getElementById('edit_strength').value = data.strength;
            document.getElementById('edit_category_id').value = data.category_id;
            document.getElementById('edit_unit_price').value = data.unit_price;
            $('#editMedicineModal').modal('show');
        });
}

function deleteMedicine(id) {
    document.getElementById('delete_id').value = id;
    $('#deleteMedicineModal').modal('show');
}
</script>

<?php include '../includes/footer.php'; ?> 