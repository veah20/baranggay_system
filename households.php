<?php
$page_title = "Households Management";
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
                $household_number = 'HH-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                $stmt = $conn->prepare("
                    INSERT INTO households (household_number, head_resident_id, address, purok, income_level, house_type, number_of_members) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $household_number, $_POST['head_resident_id'], $_POST['address'], 
                    $_POST['purok'], $_POST['income_level'], $_POST['house_type'], $_POST['number_of_members']
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Added new household: ' . $household_number, 'Households');
                $success = 'Household added successfully!';
                
            } elseif ($_POST['action'] === 'edit') {
                $stmt = $conn->prepare("
                    UPDATE households SET head_resident_id=?, address=?, purok=?, income_level=?, house_type=?, number_of_members=?
                    WHERE household_id=?
                ");
                $stmt->execute([
                    $_POST['head_resident_id'], $_POST['address'], $_POST['purok'], 
                    $_POST['income_level'], $_POST['house_type'], $_POST['number_of_members'], $_POST['household_id']
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Updated household ID: ' . $_POST['household_id'], 'Households');
                $success = 'Household updated successfully!';
                
            } elseif ($_POST['action'] === 'delete') {
                $stmt = $conn->prepare("DELETE FROM households WHERE household_id=?");
                $stmt->execute([$_POST['household_id']]);
                
                logActivity($conn, $_SESSION['user_id'], 'Deleted household ID: ' . $_POST['household_id'], 'Households');
                $success = 'Household deleted successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

// Fetch all households
try {
    $stmt = $conn->query("
        SELECT h.*, CONCAT(r.firstname, ' ', r.lastname) as head_name
        FROM households h
        LEFT JOIN residents r ON h.head_resident_id = r.resident_id
        ORDER BY h.household_number
    ");
    $households = $stmt->fetchAll();
    
    // Fetch residents for dropdown
    $stmt = $conn->query("SELECT resident_id, CONCAT(firstname, ' ', lastname) as name FROM residents WHERE status = 'Active' ORDER BY lastname, firstname");
    $residents = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching households: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-home me-2"></i>Households Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHouseholdModal">
            <i class="fas fa-plus me-2"></i>Add New Household
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
                            <th>Household #</th>
                            <th>Household Head</th>
                            <th>Address</th>
                            <th>Purok</th>
                            <th>Members</th>
                            <th>Income Level</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($households as $household): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($household['household_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($household['head_name']); ?></td>
                                <td><?php echo htmlspecialchars($household['address']); ?></td>
                                <td><?php echo htmlspecialchars($household['purok']); ?></td>
                                <td><?php echo $household['number_of_members']; ?></td>
                                <td>
                                    <?php
                                    $badge = 'secondary';
                                    if ($household['income_level'] == 'Low') $badge = 'warning';
                                    elseif ($household['income_level'] == 'Middle') $badge = 'info';
                                    elseif ($household['income_level'] == 'High') $badge = 'success';
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>"><?php echo $household['income_level']; ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick='editHousehold(<?php echo json_encode($household); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="household_id" value="<?php echo $household['household_id']; ?>">
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

<!-- Add Household Modal -->
<div class="modal fade" id="addHouseholdModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-home me-2"></i>Add New Household</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label">Household Head *</label>
                        <select class="form-select" name="head_resident_id" required>
                            <option value="">Select Resident</option>
                            <?php foreach ($residents as $resident): ?>
                                <option value="<?php echo $resident['resident_id']; ?>">
                                    <?php echo htmlspecialchars($resident['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Address *</label>
                        <input type="text" class="form-control" name="address" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Purok *</label>
                        <input type="text" class="form-control" name="purok" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Income Level *</label>
                        <select class="form-select" name="income_level" required>
                            <option value="Low">Low</option>
                            <option value="Middle">Middle</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">House Type *</label>
                        <select class="form-select" name="house_type" required>
                            <option value="Owned">Owned</option>
                            <option value="Rented">Rented</option>
                            <option value="Shared">Shared</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Number of Members *</label>
                        <input type="number" class="form-control" name="number_of_members" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Household</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Household Modal -->
<div class="modal fade" id="editHouseholdModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Household</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="household_id" id="edit_household_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Household Head *</label>
                        <select class="form-select" name="head_resident_id" id="edit_head_resident_id" required>
                            <?php foreach ($residents as $resident): ?>
                                <option value="<?php echo $resident['resident_id']; ?>">
                                    <?php echo htmlspecialchars($resident['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Address *</label>
                        <input type="text" class="form-control" name="address" id="edit_address" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Purok *</label>
                        <input type="text" class="form-control" name="purok" id="edit_purok" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Income Level *</label>
                        <select class="form-select" name="income_level" id="edit_income_level" required>
                            <option value="Low">Low</option>
                            <option value="Middle">Middle</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">House Type *</label>
                        <select class="form-select" name="house_type" id="edit_house_type" required>
                            <option value="Owned">Owned</option>
                            <option value="Rented">Rented</option>
                            <option value="Shared">Shared</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Number of Members *</label>
                        <input type="number" class="form-control" name="number_of_members" id="edit_number_of_members" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Household</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editHousehold(household) {
    document.getElementById('edit_household_id').value = household.household_id;
    document.getElementById('edit_head_resident_id').value = household.head_resident_id;
    document.getElementById('edit_address').value = household.address;
    document.getElementById('edit_purok').value = household.purok;
    document.getElementById('edit_income_level').value = household.income_level;
    document.getElementById('edit_house_type').value = household.house_type;
    document.getElementById('edit_number_of_members').value = household.number_of_members;
    
    new bootstrap.Modal(document.getElementById('editHouseholdModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>
