<?php
$page_title = "Certificates Management";
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'issue') {
        try {
            $cert_number = 'CERT-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            
            $stmt = $conn->prepare("
                INSERT INTO certificates (cert_number, resident_id, cert_type, purpose, amount_paid, or_number, issued_by, date_issued) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $cert_number, $_POST['resident_id'], $_POST['cert_type'], $_POST['purpose'],
                $_POST['amount_paid'], $_POST['or_number'], $_SESSION['user_id'], $_POST['date_issued']
            ]);
            
            $cert_id = $conn->lastInsertId();
            
            logActivity($conn, $_SESSION['user_id'], 'Issued certificate: ' . $cert_number, 'Certificates');
            $success = 'Certificate issued successfully! Certificate Number: ' . $cert_number;
            
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Fetch all certificates
try {
    $stmt = $conn->query("
        SELECT c.*, CONCAT(r.firstname, ' ', r.lastname) as resident_name, u.fullname as issued_by_name
        FROM certificates c
        JOIN residents r ON c.resident_id = r.resident_id
        JOIN users u ON c.issued_by = u.user_id
        ORDER BY c.date_issued DESC, c.cert_id DESC
    ");
    $certificates = $stmt->fetchAll();
    
    // Fetch residents for dropdown
    $stmt = $conn->query("SELECT resident_id, CONCAT(firstname, ' ', lastname) as name FROM residents WHERE status = 'Active' ORDER BY lastname, firstname");
    $residents = $stmt->fetchAll();
    
    // Fetch system settings for fees
    $stmt = $conn->query("SELECT * FROM system_settings LIMIT 1");
    $settings = $stmt->fetch();
} catch (PDOException $e) {
    $error = 'Error fetching certificates: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-certificate me-2"></i>Certificates Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#issueCertificateModal">
            <i class="fas fa-plus me-2"></i>Issue Certificate
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
                            <th>Certificate #</th>
                            <th>Resident</th>
                            <th>Type</th>
                            <th>Purpose</th>
                            <th>Amount</th>
                            <th>Date Issued</th>
                            <th>Issued By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certificates as $cert): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cert['cert_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cert['resident_name']); ?></td>
                                <td><span class="badge bg-info"><?php echo $cert['cert_type']; ?></span></td>
                                <td><?php echo htmlspecialchars(substr($cert['purpose'], 0, 30)) . (strlen($cert['purpose']) > 30 ? '...' : ''); ?></td>
                                <td>₱<?php echo number_format($cert['amount_paid'], 2); ?></td>
                                <td><?php echo formatDate($cert['date_issued']); ?></td>
                                <td><?php echo htmlspecialchars($cert['issued_by_name']); ?></td>
                                <td>
                                    <a href="print_certificate.php?id=<?php echo $cert['cert_id']; ?>" class="btn btn-sm btn-success" target="_blank">
                                        <i class="fas fa-print"></i> Print
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Issue Certificate Modal -->
<div class="modal fade" id="issueCertificateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-certificate me-2"></i>Issue Certificate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="issue">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Resident *</label>
                            <select class="form-select" name="resident_id" id="resident_id" required>
                                <option value="">Select Resident</option>
                                <?php foreach ($residents as $resident): ?>
                                    <option value="<?php echo $resident['resident_id']; ?>">
                                        <?php echo htmlspecialchars($resident['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Certificate Type *</label>
                            <select class="form-select" name="cert_type" id="cert_type" required onchange="updateFee()">
                                <option value="">Select Type</option>
                                <option value="Barangay Clearance">Barangay Clearance</option>
                                <option value="Certificate of Residency">Certificate of Residency</option>
                                <option value="Certificate of Indigency">Certificate of Indigency</option>
                                <option value="Business Permit">Business Permit</option>
                                <option value="Good Moral">Good Moral</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Purpose *</label>
                        <textarea class="form-control" name="purpose" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Amount Paid *</label>
                            <input type="number" class="form-control" name="amount_paid" id="amount_paid" step="0.01" min="0" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">OR Number</label>
                            <input type="text" class="form-control" name="or_number">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date Issued *</label>
                            <input type="date" class="form-control" name="date_issued" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Issue Certificate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const fees = {
    'Barangay Clearance': <?php echo $settings['clearance_fee'] ?? 50; ?>,
    'Certificate of Residency': <?php echo $settings['residency_fee'] ?? 30; ?>,
    'Certificate of Indigency': <?php echo $settings['indigency_fee'] ?? 0; ?>,
    'Business Permit': <?php echo $settings['business_permit_fee'] ?? 500; ?>,
    'Good Moral': 30,
    'Other': 0
};

function updateFee() {
    const certType = document.getElementById('cert_type').value;
    const amountField = document.getElementById('amount_paid');
    
    if (certType && fees[certType] !== undefined) {
        amountField.value = fees[certType];
    }
}
</script>

<?php include 'includes/footer.php'; ?>
