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
                $username = $conn->real_escape_string($_POST['username']);
                $email = $conn->real_escape_string($_POST['email']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $role = $conn->real_escape_string($_POST['role']);
                $full_name = $conn->real_escape_string($_POST['full_name']);
                $phone = $conn->real_escape_string($_POST['phone']);
                
                // Check if username already exists
                $check_query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
                $check_result = $conn->query($check_query);
                
                if ($check_result->num_rows > 0) {
                    $error_message = "Username or email already exists!";
                } else {
                    $query = "INSERT INTO users (username, email, password, role, full_name, phone, created_at) 
                             VALUES ('$username', '$email', '$password', '$role', '$full_name', '$phone', NOW())";
                    if ($conn->query($query)) {
                        $success_message = "User added successfully!";
                    } else {
                        $error_message = "Error adding user: " . $conn->error;
                    }
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $username = $conn->real_escape_string($_POST['username']);
                $email = $conn->real_escape_string($_POST['email']);
                $role = $conn->real_escape_string($_POST['role']);
                $full_name = $conn->real_escape_string($_POST['full_name']);
                $phone = $conn->real_escape_string($_POST['phone']);
                
                // Check if username/email already exists for other users
                $check_query = "SELECT id FROM users WHERE (username = '$username' OR email = '$email') AND id != $id";
                $check_result = $conn->query($check_query);
                
                if ($check_result->num_rows > 0) {
                    $error_message = "Username or email already exists!";
                } else {
                    $query = "UPDATE users SET username='$username', email='$email', role='$role', 
                             full_name='$full_name', phone='$phone' WHERE id=$id";
                    if ($conn->query($query)) {
                        $success_message = "User updated successfully!";
                    } else {
                        $error_message = "Error updating user: " . $conn->error;
                    }
                }
                break;
                
            case 'change_password':
                $id = (int)$_POST['id'];
                $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                
                $query = "UPDATE users SET password='$new_password' WHERE id=$id";
                if ($conn->query($query)) {
                    $success_message = "Password changed successfully!";
                } else {
                    $error_message = "Error changing password: " . $conn->error;
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                // Prevent admin from deleting themselves
                if ($id == $_SESSION['user_id']) {
                    $error_message = "You cannot delete your own account!";
                } else {
                    $query = "DELETE FROM users WHERE id=$id";
                    if ($conn->query($query)) {
                        $success_message = "User deleted successfully!";
                    } else {
                        $error_message = "Error deleting user: " . $conn->error;
                    }
                }
                break;
        }
    }
}

// Get users list
$query = "SELECT id, username, email, role, full_name, phone, created_at, last_login 
          FROM users ORDER BY created_at DESC";
$result = $conn->query($query);

include '../includes/header.php';
?>

<h1 class="h3 mb-4">Users Management</h1>

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
        <h5 class="m-0"><i class="fas fa-users mr-2"></i>Users List</h5>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addUserModal">
            <i class="fas fa-plus mr-1"></i>Add User
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Created</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                                <?php if ($row['id'] == $_SESSION['user_id']): ?>
                                    <span class="badge badge-info">You</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $row['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($row['role'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php echo $row['last_login'] ? date('M d, Y H:i', strtotime($row['last_login'])) : 'Never'; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editUser(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="changePassword(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-key"></i>
                                </button>
                                <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $row['id']; ?>)">
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
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
                                <label>Username *</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" class="form-control" name="full_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" class="form-control" name="phone">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password *</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Role *</label>
                                <select class="form-control" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
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
                                <label>Username *</label>
                                <input type="text" class="form-control" name="username" id="edit_username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" class="form-control" name="email" id="edit_email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" class="form-control" name="full_name" id="edit_full_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" class="form-control" name="phone" id="edit_phone">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Role *</label>
                        <select class="form-control" name="role" id="edit_role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="id" id="password_id">
                    <div class="form-group">
                        <label>New Password *</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password *</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
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
function editUser(id) {
    // Fetch user data and populate modal
    fetch(`get_user.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_full_name').value = data.full_name;
            document.getElementById('edit_phone').value = data.phone;
            document.getElementById('edit_role').value = data.role;
            $('#editUserModal').modal('show');
        });
}

function changePassword(id) {
    document.getElementById('password_id').value = id;
    $('#changePasswordModal').modal('show');
}

function deleteUser(id) {
    document.getElementById('delete_id').value = id;
    $('#deleteUserModal').modal('show');
}
</script>

<?php include '../includes/footer.php'; ?> 