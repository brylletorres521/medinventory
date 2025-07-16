<?php
session_start();
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Medical Inventory/login.php");
    exit();
}

require_once '../config/db.php';

// Get current user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($query);
$user = $result->fetch_assoc();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $full_name = $conn->real_escape_string($_POST['full_name']);
                $email = $conn->real_escape_string($_POST['email']);
                $phone = $conn->real_escape_string($_POST['phone']);
                
                // Check if email already exists for other users
                $check_query = "SELECT id FROM users WHERE email = '$email' AND id != $user_id";
                $check_result = $conn->query($check_query);
                
                if ($check_result->num_rows > 0) {
                    $error_message = "Email already exists!";
                } else {
                    $query = "UPDATE users SET full_name='$full_name', email='$email', phone='$phone' WHERE id=$user_id";
                    if ($conn->query($query)) {
                        $success_message = "Profile updated successfully!";
                        // Refresh user data
                        $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
                        $user = $result->fetch_assoc();
                    } else {
                        $error_message = "Error updating profile: " . $conn->error;
                    }
                }
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                // Verify current password
                if (!password_verify($current_password, $user['password'])) {
                    $error_message = "Current password is incorrect!";
                } elseif ($new_password !== $confirm_password) {
                    $error_message = "New passwords do not match!";
                } elseif (strlen($new_password) < 6) {
                    $error_message = "Password must be at least 6 characters long!";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $query = "UPDATE users SET password='$hashed_password' WHERE id=$user_id";
                    if ($conn->query($query)) {
                        $success_message = "Password changed successfully!";
                    } else {
                        $error_message = "Error changing password: " . $conn->error;
                    }
                }
                break;
        }
    }
}

include '../includes/header.php';
?>

<h1 class="h3 mb-4">My Profile</h1>

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

<div class="row">
    <!-- Profile Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="m-0"><i class="fas fa-user mr-2"></i>Profile Information</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                <small class="text-muted">Username cannot be changed</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Role</label>
                                <input type="text" class="form-control" value="<?php echo ucfirst(htmlspecialchars($user['role'])); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Account Statistics -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="m-0"><i class="fas fa-info-circle mr-2"></i>Account Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 mb-0 text-primary"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                            <small class="text-muted">Member Since</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="h4 mb-0 text-success">
                                <?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never'; ?>
                            </div>
                            <small class="text-muted">Last Login</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#changePasswordModal">
                        <i class="fas fa-key mr-1"></i>Change Password
                    </button>
                </div>
            </div>
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
                    <div class="form-group">
                        <label>Current Password *</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password *</label>
                        <input type="password" class="form-control" name="new_password" required>
                        <small class="text-muted">Password must be at least 6 characters long</small>
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

<?php include '../includes/footer.php'; ?> 