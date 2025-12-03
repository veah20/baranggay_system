<?php
$page_title = "Residents Management";
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
                    INSERT INTO residents (firstname, middlename, lastname, suffix, gender, birthdate, birthplace, 
                    civil_status, nationality, religion, occupation, contact_number, email, purok, street, 
                    is_voter, is_pwd, is_senior, is_4ps, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $is_senior = (calculateAge($_POST['birthdate']) >= 60) ? 'Yes' : 'No';
                
                $stmt->execute([
                    $_POST['firstname'], $_POST['middlename'], $_POST['lastname'], $_POST['suffix'],
                    $_POST['gender'], $_POST['birthdate'], $_POST['birthplace'], $_POST['civil_status'],
                    $_POST['nationality'], $_POST['religion'], $_POST['occupation'], $_POST['contact_number'],
                    $_POST['email'], $_POST['purok'], $_POST['street'], $_POST['is_voter'],
                    $_POST['is_pwd'], $is_senior, $_POST['is_4ps'], 'Active'
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Added new resident: ' . $_POST['firstname'] . ' ' . $_POST['lastname'], 'Residents');
                $success = 'Resident added successfully!';
                
            } elseif ($_POST['action'] === 'edit') {
                $stmt = $conn->prepare("
                    UPDATE residents SET firstname=?, middlename=?, lastname=?, suffix=?, gender=?, birthdate=?, 
                    birthplace=?, civil_status=?, nationality=?, religion=?, occupation=?, contact_number=?, 
                    email=?, purok=?, street=?, is_voter=?, is_pwd=?, is_senior=?, is_4ps=?, status=?
                    WHERE resident_id=?
                ");
                
                $is_senior = (calculateAge($_POST['birthdate']) >= 60) ? 'Yes' : 'No';
                
                $stmt->execute([
                    $_POST['firstname'], $_POST['middlename'], $_POST['lastname'], $_POST['suffix'],
                    $_POST['gender'], $_POST['birthdate'], $_POST['birthplace'], $_POST['civil_status'],
                    $_POST['nationality'], $_POST['religion'], $_POST['occupation'], $_POST['contact_number'],
                    $_POST['email'], $_POST['purok'], $_POST['street'], $_POST['is_voter'],
                    $_POST['is_pwd'], $is_senior, $_POST['is_4ps'], $_POST['status'], $_POST['resident_id']
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Updated resident: ' . $_POST['firstname'] . ' ' . $_POST['lastname'], 'Residents');
                $success = 'Resident updated successfully!';
                
            } elseif ($_POST['action'] === 'delete') {
                $stmt = $conn->prepare("DELETE FROM residents WHERE resident_id=?");
                $stmt->execute([$_POST['resident_id']]);
                
                logActivity($conn, $_SESSION['user_id'], 'Deleted resident ID: ' . $_POST['resident_id'], 'Residents');
                $success = 'Resident deleted successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Fetch all residents
try {
    $stmt = $conn->query("SELECT * FROM residents ORDER BY lastname, firstname");
    $residents = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching residents: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users me-2"></i>Residents Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResidentModal">
            <i class="fas fa-plus me-2"></i>Add New Resident
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
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Age</th>
                            <th>Purok</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($residents as $resident): ?>
                            <tr>
                                <td><?php echo $resident['resident_id']; ?></td>
                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars($resident['firstname'] . ' ' . 
                                            ($resident['middlename'] ? substr($resident['middlename'], 0, 1) . '. ' : '') . 
                                            $resident['lastname'] . 
                                            ($resident['suffix'] ? ' ' . $resident['suffix'] : '')); ?>
                                    </strong>
                                </td>
                                <td><?php echo $resident['gender']; ?></td>
                                <td><?php echo calculateAge($resident['birthdate']); ?></td>
                                <td><?php echo htmlspecialchars($resident['purok']); ?></td>
                                <td><?php echo htmlspecialchars($resident['contact_number'] ?: 'N/A'); ?></td>
                                <td>
                                    <?php
                                    $badge = 'success';
                                    if ($resident['status'] == 'Deceased') $badge = 'dark';
                                    elseif ($resident['status'] == 'Moved Out') $badge = 'warning';
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>"><?php echo $resident['status']; ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewResident(<?php echo htmlspecialchars(json_encode($resident)); ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="editResident(<?php echo htmlspecialchars(json_encode($resident)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="resident_id" value="<?php echo $resident['resident_id']; ?>">
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

<!-- Add Resident Modal -->
<div class="modal fade" id="addResidentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Resident</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name *</label>
                            <input type="text" class="form-control" name="firstname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="middlename">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name *</label>
                            <input type="text" class="form-control" name="lastname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Suffix</label>
                            <input type="text" class="form-control" name="suffix" placeholder="Jr., Sr., III">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gender *</label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Birthdate *</label>
                            <input type="date" class="form-control" name="birthdate" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Birthplace</label>
                            <input type="text" class="form-control" name="birthplace">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Civil Status *</label>
                            <select class="form-select" name="civil_status" required>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                                <option value="Divorced">Divorced</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nationality</label>
                            <input type="text" class="form-control" name="nationality" value="Filipino">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Religion</label>
                            <input type="text" class="form-control" name="religion">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Occupation</label>
                            <input type="text" class="form-control" name="occupation">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-control" name="contact_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purok *</label>
                            <input type="text" class="form-control" name="purok" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Street</label>
                            <input type="text" class="form-control" name="street">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Registered Voter?</label>
                            <select class="form-select" name="is_voter">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">PWD?</label>
                            <select class="form-select" name="is_pwd">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">4Ps Member?</label>
                            <select class="form-select" name="is_4ps">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Resident</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Resident Modal -->
<div class="modal fade" id="editResidentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Resident</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editResidentForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="resident_id" id="edit_resident_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name *</label>
                            <input type="text" class="form-control" name="firstname" id="edit_firstname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="middlename" id="edit_middlename">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name *</label>
                            <input type="text" class="form-control" name="lastname" id="edit_lastname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Suffix</label>
                            <input type="text" class="form-control" name="suffix" id="edit_suffix">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gender *</label>
                            <select class="form-select" name="gender" id="edit_gender" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Birthdate *</label>
                            <input type="date" class="form-control" name="birthdate" id="edit_birthdate" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Birthplace</label>
                            <input type="text" class="form-control" name="birthplace" id="edit_birthplace">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Civil Status *</label>
                            <select class="form-select" name="civil_status" id="edit_civil_status" required>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                                <option value="Divorced">Divorced</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nationality</label>
                            <input type="text" class="form-control" name="nationality" id="edit_nationality">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Religion</label>
                            <input type="text" class="form-control" name="religion" id="edit_religion">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Occupation</label>
                            <input type="text" class="form-control" name="occupation" id="edit_occupation">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-control" name="contact_number" id="edit_contact_number">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purok *</label>
                            <input type="text" class="form-control" name="purok" id="edit_purok" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Street</label>
                            <input type="text" class="form-control" name="street" id="edit_street">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Registered Voter?</label>
                            <select class="form-select" name="is_voter" id="edit_is_voter">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">PWD?</label>
                            <select class="form-select" name="is_pwd" id="edit_is_pwd">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">4Ps Member?</label>
                            <select class="form-select" name="is_4ps" id="edit_is_4ps">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="Active">Active</option>
                                <option value="Deceased">Deceased</option>
                                <option value="Moved Out">Moved Out</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Resident</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Resident Modal -->
<div class="modal fade" id="viewResidentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user me-2"></i>Resident Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewResidentContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function editResident(resident) {
    document.getElementById('edit_resident_id').value = resident.resident_id;
    document.getElementById('edit_firstname').value = resident.firstname;
    document.getElementById('edit_middlename').value = resident.middlename || '';
    document.getElementById('edit_lastname').value = resident.lastname;
    document.getElementById('edit_suffix').value = resident.suffix || '';
    document.getElementById('edit_gender').value = resident.gender;
    document.getElementById('edit_birthdate').value = resident.birthdate;
    document.getElementById('edit_birthplace').value = resident.birthplace || '';
    document.getElementById('edit_civil_status').value = resident.civil_status;
    document.getElementById('edit_nationality').value = resident.nationality || '';
    document.getElementById('edit_religion').value = resident.religion || '';
    document.getElementById('edit_occupation').value = resident.occupation || '';
    document.getElementById('edit_contact_number').value = resident.contact_number || '';
    document.getElementById('edit_email').value = resident.email || '';
    document.getElementById('edit_purok').value = resident.purok;
    document.getElementById('edit_street').value = resident.street || '';
    document.getElementById('edit_is_voter').value = resident.is_voter;
    document.getElementById('edit_is_pwd').value = resident.is_pwd;
    document.getElementById('edit_is_4ps').value = resident.is_4ps;
    document.getElementById('edit_status').value = resident.status;
    
    new bootstrap.Modal(document.getElementById('editResidentModal')).show();
}

function viewResident(resident) {
    const age = calculateAge(resident.birthdate);
    const content = `
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><th>Full Name:</th><td>${resident.firstname} ${resident.middlename || ''} ${resident.lastname} ${resident.suffix || ''}</td></tr>
                    <tr><th>Gender:</th><td>${resident.gender}</td></tr>
                    <tr><th>Birthdate:</th><td>${resident.birthdate} (${age} years old)</td></tr>
                    <tr><th>Birthplace:</th><td>${resident.birthplace || 'N/A'}</td></tr>
                    <tr><th>Civil Status:</th><td>${resident.civil_status}</td></tr>
                    <tr><th>Nationality:</th><td>${resident.nationality || 'N/A'}</td></tr>
                    <tr><th>Religion:</th><td>${resident.religion || 'N/A'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><th>Occupation:</th><td>${resident.occupation || 'N/A'}</td></tr>
                    <tr><th>Contact Number:</th><td>${resident.contact_number || 'N/A'}</td></tr>
                    <tr><th>Email:</th><td>${resident.email || 'N/A'}</td></tr>
                    <tr><th>Address:</th><td>${resident.street || ''} Purok ${resident.purok}</td></tr>
                    <tr><th>Registered Voter:</th><td>${resident.is_voter}</td></tr>
                    <tr><th>PWD:</th><td>${resident.is_pwd}</td></tr>
                    <tr><th>Senior Citizen:</th><td>${resident.is_senior}</td></tr>
                    <tr><th>4Ps Member:</th><td>${resident.is_4ps}</td></tr>
                    <tr><th>Status:</th><td><span class="badge bg-success">${resident.status}</span></td></tr>
                </table>
            </div>
        </div>
    `;
    
    document.getElementById('viewResidentContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('viewResidentModal')).show();
}

function calculateAge(birthdate) {
    const today = new Date();
    const birth = new Date(birthdate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
}
</script>

<?php include 'includes/footer.php'; ?>
