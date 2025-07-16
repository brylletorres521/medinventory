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
                $contact_person = $conn->real_escape_string($_POST['contact_person']);
                $email = $conn->real_escape_string($_POST['email']);
                $phone = $conn->real_escape_string($_POST['phone']);
                $address = $conn->real_escape_string($_POST['address']);
                $city = $conn->real_escape_string($_POST['city']);
                $state = $conn->real_escape_string($_POST['state']);
                $zip_code = $conn->real_escape_string($_POST['zip_code']);
                $country = $conn->real_escape_string($_POST['country']);
                
                $query = "INSERT INTO suppliers (name, contact_person, email, phone, address, city, state, zip_code, country) 
                         VALUES ('$name', '$contact_person', '$email', '$phone', '$address', '$city', '$state', '$zip_code', '$country')";
                if ($conn->query($query)) {
                    $success_message = "Supplier added successfully!";
                } else {
                    $error_message = "Error adding supplier: " . $conn->error;
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $name = $conn->real_escape_string($_POST['name']);
                $contact_person = $conn->real_escape_string($_POST['contact_person']);
                $email = $conn->real_escape_string($_POST['email']);
                $phone = $conn->real_escape_string($_POST['phone']);
                $address = $conn->real_escape_string($_POST['address']);
                $city = $conn->real_escape_string($_POST['city']);
                $state = $conn->real_escape_string($_POST['state']);
                $zip_code = $conn->real_escape_string($_POST['zip_code']);
                $country = $conn->real_escape_string($_POST['country']);
                
                $query = "UPDATE suppliers SET name='$name', contact_person='$contact_person', email='$email', 
                         phone='$phone', address='$address', city='$city', state='$state', zip_code='$zip_code', 
                         country='$country' WHERE id=$id";
                if ($conn->query($query)) {
                    $success_message = "Supplier updated successfully!";
                } else {
                    $error_message = "Error updating supplier: " . $conn->error;
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $query = "DELETE FROM suppliers WHERE id=$id";
                if ($conn->query($query)) {
                    $success_message = "Supplier deleted successfully!";
                } else {
                    $error_message = "Error deleting supplier: " . $conn->error;
                }
                break;
        }
    }
}

// Get suppliers list
$query = "SELECT * FROM suppliers ORDER BY name";
$result = $conn->query($query);

include '../includes/header.php';
?>

<h1 class="h3 mb-4">Suppliers Management</h1>

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
        <h5 class="m-0"><i class="fas fa-truck mr-2"></i>Suppliers List</h5>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addSupplierModal">
            <i class="fas fa-plus mr-1"></i>Add Supplier
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Country</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact_person']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['city']); ?></td>
                            <td><?php echo htmlspecialchars($row['country']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editSupplier(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteSupplier(<?php echo $row['id']; ?>)">
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

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Supplier</h5>
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
                                <label>Supplier Name *</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Person *</label>
                                <input type="text" class="form-control" name="contact_person" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone *</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" class="form-control" name="city">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>State/Province</label>
                                <input type="text" class="form-control" name="state">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>ZIP/Postal Code</label>
                                <input type="text" class="form-control" name="zip_code">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" class="form-control" name="country">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier</h5>
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
                                <label>Supplier Name *</label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Person *</label>
                                <input type="text" class="form-control" name="contact_person" id="edit_contact_person" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" class="form-control" name="email" id="edit_email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone *</label>
                                <input type="tel" class="form-control" name="phone" id="edit_phone" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" class="form-control" name="city" id="edit_city">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>State/Province</label>
                                <input type="text" class="form-control" name="state" id="edit_state">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>ZIP/Postal Code</label>
                                <input type="text" class="form-control" name="zip_code" id="edit_zip_code">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" class="form-control" name="country" id="edit_country">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this supplier? This action cannot be undone.</p>
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
function editSupplier(id) {
    // Fetch supplier data and populate modal
    fetch(`get_supplier.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_contact_person').value = data.contact_person;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_phone').value = data.phone;
            document.getElementById('edit_address').value = data.address;
            document.getElementById('edit_city').value = data.city;
            document.getElementById('edit_state').value = data.state;
            document.getElementById('edit_zip_code').value = data.zip_code;
            document.getElementById('edit_country').value = data.country;
            $('#editSupplierModal').modal('show');
        });
}

function deleteSupplier(id) {
    document.getElementById('delete_id').value = id;
    $('#deleteSupplierModal').modal('show');
}
</script>

<?php include '../includes/footer.php'; ?> 