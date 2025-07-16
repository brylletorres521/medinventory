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
                $description = $conn->real_escape_string($_POST['description']);
                
                // Check if category already exists
                $check_query = "SELECT id FROM categories WHERE name = '$name'";
                $check_result = $conn->query($check_query);
                
                if ($check_result->num_rows > 0) {
                    $error_message = "Category already exists!";
                } else {
                    $query = "INSERT INTO categories (name, description) VALUES ('$name', '$description')";
                    if ($conn->query($query)) {
                        $success_message = "Category added successfully!";
                    } else {
                        $error_message = "Error adding category: " . $conn->error;
                    }
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $name = $conn->real_escape_string($_POST['name']);
                $description = $conn->real_escape_string($_POST['description']);
                
                // Check if category name already exists for other categories
                $check_query = "SELECT id FROM categories WHERE name = '$name' AND id != $id";
                $check_result = $conn->query($check_query);
                
                if ($check_result->num_rows > 0) {
                    $error_message = "Category name already exists!";
                } else {
                    $query = "UPDATE categories SET name='$name', description='$description' WHERE id=$id";
                    if ($conn->query($query)) {
                        $success_message = "Category updated successfully!";
                    } else {
                        $error_message = "Error updating category: " . $conn->error;
                    }
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                // Check if category is being used by medicines
                $check_query = "SELECT COUNT(*) as count FROM medicines WHERE category_id = $id";
                $check_result = $conn->query($check_query);
                $count = $check_result->fetch_assoc()['count'];
                
                if ($count > 0) {
                    $error_message = "Cannot delete category. It is being used by $count medicine(s).";
                } else {
                    $query = "DELETE FROM categories WHERE id=$id";
                    if ($conn->query($query)) {
                        $success_message = "Category deleted successfully!";
                    } else {
                        $error_message = "Error deleting category: " . $conn->error;
                    }
                }
                break;
        }
    }
}

// Get categories with medicine count
$query = "SELECT c.*, COUNT(m.id) as medicine_count 
          FROM categories c 
          LEFT JOIN medicines m ON c.id = m.category_id 
          GROUP BY c.id 
          ORDER BY c.name";
$result = $conn->query($query);

include '../includes/header.php';
?>

<h1 class="h3 mb-4">Categories Management</h1>

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
        <h5 class="m-0"><i class="fas fa-list mr-2"></i>Categories List</h5>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addCategoryModal">
            <i class="fas fa-plus mr-1"></i>Add Category
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Medicines Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $row['medicine_count'] > 0 ? 'primary' : 'secondary'; ?>">
                                    <?php echo $row['medicine_count']; ?> medicine(s)
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editCategory(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($row['medicine_count'] == 0): ?>
                                    <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Category Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Category Name *</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this category? This action cannot be undone.</p>
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
function editCategory(id) {
    // Fetch category data and populate modal
    fetch(`get_category.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_description').value = data.description;
            $('#editCategoryModal').modal('show');
        });
}

function deleteCategory(id) {
    document.getElementById('delete_id').value = id;
    $('#deleteCategoryModal').modal('show');
}
</script>

<?php include '../includes/footer.php'; ?> 