<?php
$page_title = "System Settings";
require_once 'includes/header.php';
require_once 'config/database.php';

if (!hasRole(['Admin'])) {
    header('Location: dashboard.php');
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare("
            UPDATE system_settings SET 
            barangay_name=?, municipality=?, province=?, region=?, 
            contact_number=?, email=?, 
            clearance_fee=?, indigency_fee=?, residency_fee=?, business_permit_fee=?
            WHERE setting_id=1
        ");
        $stmt->execute([
            $_POST['barangay_name'], $_POST['municipality'], $_POST['province'], $_POST['region'],
            $_POST['contact_number'], $_POST['email'],
            $_POST['clearance_fee'], $_POST['indigency_fee'], $_POST['residency_fee'], $_POST['business_permit_fee']
        ]);
        
        logActivity($conn, $_SESSION['user_id'], 'Updated system settings', 'Settings');
        $success = 'Settings updated successfully!';
    } catch (PDOException $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

try {
    $stmt = $conn->query("SELECT * FROM system_settings LIMIT 1");
    $settings = $stmt->fetch();
    
    if (!$settings) {
        $conn->exec("INSERT INTO system_settings (barangay_name) VALUES ('Barangay Sample')");
        $stmt = $conn->query("SELECT * FROM system_settings LIMIT 1");
        $settings = $stmt->fetch();
    }
} catch (PDOException $e) {
    $error = 'Error fetching settings: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <h2 class="mb-4"><i class="fas fa-cog me-2"></i>System Settings</h2>
    
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
        <div class="card-header">
            <h5 class="mb-0">Barangay Information</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Barangay Name *</label>
                        <input type="text" class="form-control" name="barangay_name" value="<?php echo htmlspecialchars($settings['barangay_name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Municipality *</label>
                        <input type="text" class="form-control" name="municipality" value="<?php echo htmlspecialchars($settings['municipality']); ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Province *</label>
                        <input type="text" class="form-control" name="province" value="<?php echo htmlspecialchars($settings['province']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Region *</label>
                        <input type="text" class="form-control" name="region" value="<?php echo htmlspecialchars($settings['region']); ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact_number" value="<?php echo htmlspecialchars($settings['contact_number'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>">
                    </div>
                </div>
                
                <hr class="my-4">
                
                <h5 class="mb-3">Certificate Fees</h5>
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Barangay Clearance</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="clearance_fee" value="<?php echo $settings['clearance_fee']; ?>" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Certificate of Residency</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="residency_fee" value="<?php echo $settings['residency_fee']; ?>" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Certificate of Indigency</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="indigency_fee" value="<?php echo $settings['indigency_fee']; ?>" step="0.01" min="0">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Business Permit</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="business_permit_fee" value="<?php echo $settings['business_permit_fee']; ?>" step="0.01" min="0">
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
