<?php
$page_title = "User Accounts";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

if (!hasRole(['Admin'])) {
    header('Location: dashboard.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'add') {
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, fullname, role, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['username'], $hashed_password, $_POST['fullname'], $_POST['role'], $_POST['status']]);
                logActivity($conn, $_SESSION['user_id'], 'Created user account: ' . $_POST['username'], 'Users');
                $success = 'User account created successfully!';
            } elseif ($_POST['action'] === 'edit') {
                if (!empty($_POST['password'])) {
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET username=?, password=?, fullname=?, role=?, status=? WHERE user_id=?");
                    $stmt->execute([$_POST['username'], $hashed_password, $_POST['fullname'], $_POST['role'], $_POST['status'], $_POST['user_id']]);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET username=?, fullname=?, role=?, status=? WHERE user_id=?");
                    $stmt->execute([$_POST['username'], $_POST['fullname'], $_POST['role'], $_POST['status'], $_POST['user_id']]);
                }
                logActivity($conn, $_SESSION['user_id'], 'Updated user account: ' . $_POST['username'], 'Users');
                $success = 'User account updated successfully!';
            } elseif ($_POST['action'] === 'delete') {
                if ($_POST['user_id'] == $_SESSION['user_id']) {
                    $error = 'You cannot delete your own account!';
                } else {
                    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
                    $stmt->execute([$_POST['user_id']]);
                    logActivity($conn, $_SESSION['user_id'], 'Deleted user account ID: ' . $_POST['user_id'], 'Users');
                    $success = 'User account deleted successfully!';
                }
            }
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

try {
    $stmt = $conn->query("SELECT * FROM users ORDER BY user_id");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching users: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-user-cog me-2"></i>User Accounts</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-2"></i>Add User
        </button>
    </div>
    
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
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                <td><span class="badge bg-primary"><?php echo $user['role']; ?></span></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['status'] == 'Active' ? 'success' : 'secondary'; ?>">
                                        <?php echo $user['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $user['last_login'] ? formatDate($user['last_login'], 'M d, Y h:i A') : 'Never'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick='editUser(<?php echo json_encode($user); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add User Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="fullname" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select class="form-select" name="role" required>
                            <option value="Secretary">Secretary</option>
                            <option value="Barangay Captain">Barangay Captain</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-select" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit User Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-control" name="username" id="edit_username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" name="password" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="fullname" id="edit_fullname" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select class="form-select" name="role" id="edit_role" required>
                            <option value="Secretary">Secretary</option>
                            <option value="Barangay Captain">Barangay Captain</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-select" name="status" id="edit_status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(user) {
    document.getElementById('edit_user_id').value = user.user_id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_fullname').value = user.fullname;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('edit_status').value = user.status;
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>
