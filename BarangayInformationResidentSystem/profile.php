<?php
$page_title = "My Profile";
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_profile'])) {
            $stmt = $conn->prepare("UPDATE users SET fullname=?, username=? WHERE user_id=?");
            $stmt->execute([$_POST['fullname'], $_POST['username'], $_SESSION['user_id']]);
            
            $_SESSION['fullname'] = $_POST['fullname'];
            $_SESSION['username'] = $_POST['username'];
            
            logActivity($conn, $_SESSION['user_id'], 'Updated profile information', 'Profile');
            $success = 'Profile updated successfully!';
        } elseif (isset($_POST['change_password'])) {
            $stmt = $conn->prepare("SELECT password FROM users WHERE user_id=?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (password_verify($_POST['current_password'], $user['password'])) {
                if ($_POST['new_password'] === $_POST['confirm_password']) {
                    $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
                    $stmt->execute([$hashed, $_SESSION['user_id']]);
                    
                    logActivity($conn, $_SESSION['user_id'], 'Changed password', 'Profile');
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'New passwords do not match!';
                }
            } else {
                $error = 'Current password is incorrect!';
            }
        }
    } catch (PDOException $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id=?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $error = 'Error fetching profile: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <h2 class="mb-4"><i class="fas fa-user me-2"></i>My Profile</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Profile Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="text-center mb-4">
                            <div class="user-avatar mx-auto" style="width: 80px; height: 80px; font-size: 32px;">
                                <?php echo strtoupper(substr($user['fullname'], 0, 1)); ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?php echo $user['role']; ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Last Login</label>
                            <input type="text" class="form-control" value="<?php echo $user['last_login'] ? formatDate($user['last_login'], 'M d, Y h:i A') : 'Never'; ?>" readonly>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required minlength="6">
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password" required minlength="6">
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
