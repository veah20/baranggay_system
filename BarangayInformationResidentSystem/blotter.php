<?php
$page_title = "Blotter Records";
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'add') {
                $case_number = 'BLT-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                $stmt = $conn->prepare("
                    INSERT INTO blotter (case_number, complainant_id, complainant_name, complainant_address, 
                    respondent_id, respondent_name, respondent_address, incident_type, incident_date, incident_time, 
                    incident_location, details, status, filed_by, date_filed) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $case_number, $_POST['complainant_id'] ?: null, $_POST['complainant_name'], $_POST['complainant_address'],
                    $_POST['respondent_id'] ?: null, $_POST['respondent_name'], $_POST['respondent_address'],
                    $_POST['incident_type'], $_POST['incident_date'], $_POST['incident_time'],
                    $_POST['incident_location'], $_POST['details'], 'Pending', $_SESSION['user_id'], $_POST['date_filed']
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Filed blotter case: ' . $case_number, 'Blotter');
                $success = 'Blotter case filed successfully! Case Number: ' . $case_number;
                
            } elseif ($_POST['action'] === 'update_status') {
                $stmt = $conn->prepare("UPDATE blotter SET status=?, resolution=? WHERE blotter_id=?");
                $stmt->execute([$_POST['status'], $_POST['resolution'], $_POST['blotter_id']]);
                
                logActivity($conn, $_SESSION['user_id'], 'Updated blotter case ID: ' . $_POST['blotter_id'], 'Blotter');
                $success = 'Blotter status updated successfully!';
                
            } elseif ($_POST['action'] === 'delete') {
                $stmt = $conn->prepare("DELETE FROM blotter WHERE blotter_id=?");
                $stmt->execute([$_POST['blotter_id']]);
                
                logActivity($conn, $_SESSION['user_id'], 'Deleted blotter case ID: ' . $_POST['blotter_id'], 'Blotter');
                $success = 'Blotter case deleted successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Fetch all blotter cases
try {
    $stmt = $conn->query("
        SELECT b.*, 
               u.fullname as filed_by_name
        FROM blotter b
        JOIN users u ON b.filed_by = u.user_id
        ORDER BY b.date_filed DESC, b.blotter_id DESC
    ");
    $blotter_cases = $stmt->fetchAll();
    
    // Fetch residents for dropdown
    $stmt = $conn->query("SELECT resident_id, CONCAT(firstname, ' ', lastname) as name FROM residents WHERE status = 'Active' ORDER BY lastname, firstname");
    $residents = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching blotter cases: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-clipboard-list me-2"></i>Blotter Records</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBlotterModal">
            <i class="fas fa-plus me-2"></i>File New Case
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
                            <th>Case #</th>
                            <th>Incident Type</th>
                            <th>Complainant</th>
                            <th>Respondent</th>
                            <th>Date Filed</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blotter_cases as $blotter): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($blotter['case_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($blotter['incident_type']); ?></td>
                                <td><?php echo htmlspecialchars($blotter['complainant_name']); ?></td>
                                <td><?php echo htmlspecialchars($blotter['respondent_name']); ?></td>
                                <td><?php echo formatDate($blotter['date_filed']); ?></td>
                                <td>
                                    <?php
                                    $badge = 'secondary';
                                    switch($blotter['status']) {
                                        case 'Pending': $badge = 'warning'; break;
                                        case 'Under Investigation': $badge = 'info'; break;
                                        case 'Resolved': $badge = 'success'; break;
                                        case 'Dismissed': $badge = 'secondary'; break;
                                        case 'Referred to Higher Authority': $badge = 'danger'; break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>"><?php echo $blotter['status']; ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick='viewBlotter(<?php echo json_encode($blotter); ?>)'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick='updateStatus(<?php echo json_encode($blotter); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="blotter_id" value="<?php echo $blotter['blotter_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Blotter Modal -->
<div class="modal fade" id="addBlotterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clipboard-list me-2"></i>File New Blotter Case</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <h6 class="mb-3">Complainant Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Resident (Optional)</label>
                            <select class="form-select" name="complainant_id" id="complainant_id" onchange="fillComplainantInfo()">
                                <option value="">-- Not in resident list --</option>
                                <?php foreach ($residents as $resident): ?>
                                    <option value="<?php echo $resident['resident_id']; ?>" data-name="<?php echo htmlspecialchars($resident['name']); ?>">
                                        <?php echo htmlspecialchars($resident['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Complainant Name *</label>
                            <input type="text" class="form-control" name="complainant_name" id="complainant_name" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Complainant Address *</label>
                        <input type="text" class="form-control" name="complainant_address" required>
                    </div>
                    
                    <h6 class="mb-3 mt-4">Respondent Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Resident (Optional)</label>
                            <select class="form-select" name="respondent_id" id="respondent_id" onchange="fillRespondentInfo()">
                                <option value="">-- Not in resident list --</option>
                                <?php foreach ($residents as $resident): ?>
                                    <option value="<?php echo $resident['resident_id']; ?>" data-name="<?php echo htmlspecialchars($resident['name']); ?>">
                                        <?php echo htmlspecialchars($resident['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Respondent Name *</label>
                            <input type="text" class="form-control" name="respondent_name" id="respondent_name" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Respondent Address *</label>
                        <input type="text" class="form-control" name="respondent_address" required>
                    </div>
                    
                    <h6 class="mb-3 mt-4">Incident Details</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Incident Type *</label>
                            <input type="text" class="form-control" name="incident_type" placeholder="e.g., Noise Complaint, Dispute, etc." required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Incident Location *</label>
                            <input type="text" class="form-control" name="incident_location" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Incident Date *</label>
                            <input type="date" class="form-control" name="incident_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Incident Time</label>
                            <input type="time" class="form-control" name="incident_time">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Details/Description *</label>
                        <textarea class="form-control" name="details" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Date Filed *</label>
                        <input type="date" class="form-control" name="date_filed" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">File Case</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Update Case Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="blotter_id" id="update_blotter_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Case Number</label>
                        <input type="text" class="form-control" id="update_case_number" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-select" name="status" id="update_status" required>
                            <option value="Pending">Pending</option>
                            <option value="Under Investigation">Under Investigation</option>
                            <option value="Resolved">Resolved</option>
                            <option value="Dismissed">Dismissed</option>
                            <option value="Referred to Higher Authority">Referred to Higher Authority</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Resolution/Remarks</label>
                        <textarea class="form-control" name="resolution" id="update_resolution" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Blotter Modal -->
<div class="modal fade" id="viewBlotterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clipboard-list me-2"></i>Blotter Case Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewBlotterContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function fillComplainantInfo() {
    const select = document.getElementById('complainant_id');
    const nameInput = document.getElementById('complainant_name');
    const selectedOption = select.options[select.selectedIndex];
    
    if (select.value) {
        nameInput.value = selectedOption.getAttribute('data-name');
    } else {
        nameInput.value = '';
    }
}

function fillRespondentInfo() {
    const select = document.getElementById('respondent_id');
    const nameInput = document.getElementById('respondent_name');
    const selectedOption = select.options[select.selectedIndex];
    
    if (select.value) {
        nameInput.value = selectedOption.getAttribute('data-name');
    } else {
        nameInput.value = '';
    }
}

function updateStatus(blotter) {
    document.getElementById('update_blotter_id').value = blotter.blotter_id;
    document.getElementById('update_case_number').value = blotter.case_number;
    document.getElementById('update_status').value = blotter.status;
    document.getElementById('update_resolution').value = blotter.resolution || '';
    
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}

function viewBlotter(blotter) {
    const content = `
        <div class="row">
            <div class="col-md-12">
                <h6>Case Information</h6>
                <table class="table table-borderless">
                    <tr><th width="200">Case Number:</th><td>${blotter.case_number}</td></tr>
                    <tr><th>Incident Type:</th><td>${blotter.incident_type}</td></tr>
                    <tr><th>Status:</th><td><span class="badge bg-info">${blotter.status}</span></td></tr>
                    <tr><th>Date Filed:</th><td>${blotter.date_filed}</td></tr>
                    <tr><th>Filed By:</th><td>${blotter.filed_by_name}</td></tr>
                </table>
                
                <h6 class="mt-4">Complainant</h6>
                <table class="table table-borderless">
                    <tr><th width="200">Name:</th><td>${blotter.complainant_name}</td></tr>
                    <tr><th>Address:</th><td>${blotter.complainant_address || 'N/A'}</td></tr>
                </table>
                
                <h6 class="mt-4">Respondent</h6>
                <table class="table table-borderless">
                    <tr><th width="200">Name:</th><td>${blotter.respondent_name}</td></tr>
                    <tr><th>Address:</th><td>${blotter.respondent_address || 'N/A'}</td></tr>
                </table>
                
                <h6 class="mt-4">Incident Details</h6>
                <table class="table table-borderless">
                    <tr><th width="200">Date:</th><td>${blotter.incident_date}</td></tr>
                    <tr><th>Time:</th><td>${blotter.incident_time || 'N/A'}</td></tr>
                    <tr><th>Location:</th><td>${blotter.incident_location}</td></tr>
                    <tr><th>Details:</th><td>${blotter.details}</td></tr>
                </table>
                
                ${blotter.resolution ? `
                <h6 class="mt-4">Resolution</h6>
                <p>${blotter.resolution}</p>
                ` : ''}
            </div>
        </div>
    `;
    
    document.getElementById('viewBlotterContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('viewBlotterModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>
