<?php
$page_title = "Barangay Officials";
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
                $stmt = $conn->prepare("
                    INSERT INTO officials (resident_id, name, position, committee, term_start, term_end, status, contact_number, email) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['resident_id'] ?: null, $_POST['name'], $_POST['position'], $_POST['committee'],
                    $_POST['term_start'], $_POST['term_end'], $_POST['status'], $_POST['contact_number'], $_POST['email']
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Added official: ' . $_POST['name'], 'Officials');
                $success = 'Official added successfully!';
                
            } elseif ($_POST['action'] === 'edit') {
                $stmt = $conn->prepare("
                    UPDATE officials SET resident_id=?, name=?, position=?, committee=?, term_start=?, term_end=?, 
                    status=?, contact_number=?, email=? WHERE official_id=?
                ");
                $stmt->execute([
                    $_POST['resident_id'] ?: null, $_POST['name'], $_POST['position'], $_POST['committee'],
                    $_POST['term_start'], $_POST['term_end'], $_POST['status'], $_POST['contact_number'], 
                    $_POST['email'], $_POST['official_id']
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Updated official: ' . $_POST['name'], 'Officials');
                $success = 'Official updated successfully!';
                
            } elseif ($_POST['action'] === 'delete') {
                $stmt = $conn->prepare("DELETE FROM officials WHERE official_id=?");
                $stmt->execute([$_POST['official_id']]);
                
                logActivity($conn, $_SESSION['user_id'], 'Deleted official ID: ' . $_POST['official_id'], 'Officials');
                $success = 'Official deleted successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Fetch all officials
try {
    $stmt = $conn->query("SELECT * FROM officials ORDER BY 
        CASE position 
            WHEN 'Barangay Captain' THEN 1 
            WHEN 'Kagawad' THEN 2 
            WHEN 'SK Chairman' THEN 3 
            WHEN 'Barangay Secretary' THEN 4 
            WHEN 'Barangay Treasurer' THEN 5 
            ELSE 6 
        END, name");
    $officials = $stmt->fetchAll();
    
    // Fetch residents for dropdown
    $stmt = $conn->query("SELECT resident_id, CONCAT(firstname, ' ', lastname) as name FROM residents WHERE status = 'Active' ORDER BY lastname, firstname");
    $residents = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching officials: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-user-tie me-2"></i>Barangay Officials</h2>
        <?php if (hasRole(['Admin'])): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOfficialModal">
            <i class="fas fa-plus me-2"></i>Add Official
        </button>
        <?php endif; ?>
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
    
    <!-- Current Officials -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Current Officials</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php 
                $current_officials = array_filter($officials, function($o) { return $o['status'] == 'Current'; });
                foreach ($current_officials as $official): 
                ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-user-circle" style="font-size: 60px; color: #667eea;"></i>
                                </div>
                                <h5 class="mb-1"><?php echo htmlspecialchars($official['name']); ?></h5>
                                <p class="text-muted mb-2"><?php echo htmlspecialchars($official['position']); ?></p>
                                <?php if ($official['committee']): ?>
                                    <p class="small mb-2"><strong>Committee:</strong> <?php echo htmlspecialchars($official['committee']); ?></p>
                                <?php endif; ?>
                                <p class="small mb-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo formatDate($official['term_start'], 'M Y'); ?> - <?php echo formatDate($official['term_end'], 'M Y'); ?>
                                </p>
                                <?php if ($official['contact_number']): ?>
                                    <p class="small mb-1"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($official['contact_number']); ?></p>
                                <?php endif; ?>
                                <?php if ($official['email']): ?>
                                    <p class="small mb-2"><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($official['email']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (hasRole(['Admin'])): ?>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-warning" onclick='editOfficial(<?php echo json_encode($official); ?>)'>
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="official_id" value="<?php echo $official['official_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Past Officials -->
    <?php 
    $past_officials = array_filter($officials, function($o) { return $o['status'] == 'Past'; });
    if (count($past_officials) > 0): 
    ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Past Officials</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Committee</th>
                            <th>Term</th>
                            <?php if (hasRole(['Admin'])): ?>
                            <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($past_officials as $official): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($official['name']); ?></td>
                                <td><?php echo htmlspecialchars($official['position']); ?></td>
                                <td><?php echo htmlspecialchars($official['committee'] ?: 'N/A'); ?></td>
                                <td><?php echo formatDate($official['term_start'], 'M Y'); ?> - <?php echo formatDate($official['term_end'], 'M Y'); ?></td>
                                <?php if (hasRole(['Admin'])): ?>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick='editOfficial(<?php echo json_encode($official); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="official_id" value="<?php echo $official['official_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add Official Modal -->
<div class="modal fade" id="addOfficialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add Official</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label">Link to Resident (Optional)</label>
                        <select class="form-select" name="resident_id">
                            <option value="">-- Not linked --</option>
                            <?php foreach ($residents as $resident): ?>
                                <option value="<?php echo $resident['resident_id']; ?>">
                                    <?php echo htmlspecialchars($resident['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Position *</label>
                        <select class="form-select" name="position" required>
                            <option value="">Select Position</option>
                            <option value="Barangay Captain">Barangay Captain</option>
                            <option value="Kagawad">Kagawad</option>
                            <option value="SK Chairman">SK Chairman</option>
                            <option value="Barangay Secretary">Barangay Secretary</option>
                            <option value="Barangay Treasurer">Barangay Treasurer</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Committee</label>
                        <input type="text" class="form-control" name="committee" placeholder="e.g., Health and Sanitation">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Term Start *</label>
                            <input type="date" class="form-control" name="term_start" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Term End *</label>
                            <input type="date" class="form-control" name="term_end" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-select" name="status" required>
                            <option value="Current">Current</option>
                            <option value="Past">Past</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact_number">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Official</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Official Modal -->
<div class="modal fade" id="editOfficialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Official</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="official_id" id="edit_official_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Link to Resident (Optional)</label>
                        <select class="form-select" name="resident_id" id="edit_resident_id">
                            <option value="">-- Not linked --</option>
                            <?php foreach ($residents as $resident): ?>
                                <option value="<?php echo $resident['resident_id']; ?>">
                                    <?php echo htmlspecialchars($resident['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Position *</label>
                        <select class="form-select" name="position" id="edit_position" required>
                            <option value="Barangay Captain">Barangay Captain</option>
                            <option value="Kagawad">Kagawad</option>
                            <option value="SK Chairman">SK Chairman</option>
                            <option value="Barangay Secretary">Barangay Secretary</option>
                            <option value="Barangay Treasurer">Barangay Treasurer</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Committee</label>
                        <input type="text" class="form-control" name="committee" id="edit_committee">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Term Start *</label>
                            <input type="date" class="form-control" name="term_start" id="edit_term_start" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Term End *</label>
                            <input type="date" class="form-control" name="term_end" id="edit_term_end" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select class="form-select" name="status" id="edit_status" required>
                            <option value="Current">Current</option>
                            <option value="Past">Past</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact_number" id="edit_contact_number">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Official</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editOfficial(official) {
    document.getElementById('edit_official_id').value = official.official_id;
    document.getElementById('edit_resident_id').value = official.resident_id || '';
    document.getElementById('edit_name').value = official.name;
    document.getElementById('edit_position').value = official.position;
    document.getElementById('edit_committee').value = official.committee || '';
    document.getElementById('edit_term_start').value = official.term_start;
    document.getElementById('edit_term_end').value = official.term_end;
    document.getElementById('edit_status').value = official.status;
    document.getElementById('edit_contact_number').value = official.contact_number || '';
    document.getElementById('edit_email').value = official.email || '';
    
    new bootstrap.Modal(document.getElementById('editOfficialModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>
