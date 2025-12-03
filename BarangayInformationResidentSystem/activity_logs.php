<?php
$page_title = "Activity Logs";
require_once 'includes/header.php';
require_once 'config/database.php';

if (!hasRole(['Admin'])) {
    header('Location: dashboard.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

try {
    $stmt = $conn->query("
        SELECT l.*, u.username, u.fullname 
        FROM activity_logs l
        JOIN users u ON l.user_id = u.user_id
        ORDER BY l.created_at DESC
        LIMIT 500
    ");
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching logs: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <h2 class="mb-4"><i class="fas fa-history me-2"></i>Activity Logs</h2>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover data-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo formatDate($log['created_at'], 'M d, Y h:i A'); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($log['fullname']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($log['username']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($log['action']); ?></td>
                                <td><span class="badge bg-info"><?php echo $log['module']; ?></span></td>
                                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
