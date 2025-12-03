<?php
$page_title = "Livelihood & Skills Registry";
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'skills';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            // Add/Edit Resident Skill
            if ($_POST['action'] === 'add_skill') {
                $stmt = $conn->prepare("
                    INSERT INTO resident_skills (resident_id, skill_id, proficiency_level, years_of_experience, certification, notes, added_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    proficiency_level = VALUES(proficiency_level),
                    years_of_experience = VALUES(years_of_experience),
                    certification = VALUES(certification),
                    notes = VALUES(notes),
                    updated_at = NOW()
                ");
                
                $stmt->execute([
                    $_POST['resident_id'],
                    $_POST['skill_id'],
                    $_POST['proficiency_level'],
                    $_POST['years_of_experience'],
                    $_POST['certification'] ?? null,
                    $_POST['notes'] ?? null,
                    $_SESSION['user_id']
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Added/Updated resident skill', 'Livelihood');
                $success = 'Skill added successfully!';
            }
            
            // Delete Resident Skill
            elseif ($_POST['action'] === 'delete_skill') {
                $stmt = $conn->prepare("DELETE FROM resident_skills WHERE resident_skill_id = ?");
                $stmt->execute([$_POST['resident_skill_id']]);
                
                logActivity($conn, $_SESSION['user_id'], 'Deleted resident skill', 'Livelihood');
                $success = 'Skill deleted successfully!';
            }
            
            // Add Livelihood Program
            elseif ($_POST['action'] === 'add_program') {
                $stmt = $conn->prepare("
                    INSERT INTO livelihood_programs (program_name, description, program_type, start_date, end_date, location, target_beneficiaries, budget, coordinator_id, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $_POST['program_name'],
                    $_POST['description'],
                    $_POST['program_type'],
                    $_POST['start_date'],
                    $_POST['end_date'] ?? null,
                    $_POST['location'] ?? null,
                    $_POST['target_beneficiaries'] ?? null,
                    $_POST['budget'] ?? null,
                    $_SESSION['user_id'],
                    $_POST['status']
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Created livelihood program: ' . $_POST['program_name'], 'Livelihood');
                $success = 'Program created successfully!';
            }
            
            // Update Livelihood Program
            elseif ($_POST['action'] === 'edit_program') {
                $stmt = $conn->prepare("
                    UPDATE livelihood_programs 
                    SET program_name = ?, description = ?, program_type = ?, start_date = ?, end_date = ?, 
                        location = ?, target_beneficiaries = ?, budget = ?, status = ?
                    WHERE program_id = ?
                ");
                
                $stmt->execute([
                    $_POST['program_name'],
                    $_POST['description'],
                    $_POST['program_type'],
                    $_POST['start_date'],
                    $_POST['end_date'] ?? null,
                    $_POST['location'] ?? null,
                    $_POST['target_beneficiaries'] ?? null,
                    $_POST['budget'] ?? null,
                    $_POST['status'],
                    $_POST['program_id']
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Updated livelihood program: ' . $_POST['program_name'], 'Livelihood');
                $success = 'Program updated successfully!';
            }
            
            // Delete Program
            elseif ($_POST['action'] === 'delete_program') {
                $stmt = $conn->prepare("DELETE FROM livelihood_programs WHERE program_id = ?");
                $stmt->execute([$_POST['program_id']]);
                
                logActivity($conn, $_SESSION['user_id'], 'Deleted livelihood program', 'Livelihood');
                $success = 'Program deleted successfully!';
            }
        }
    } catch (PDOException $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Fetch data based on tab
$skills = [];
$programs = [];
$residents = [];

try {
    // Fetch all residents
    $stmt = $conn->query("SELECT resident_id, CONCAT(firstname, ' ', lastname) as fullname FROM residents WHERE status = 'Active' ORDER BY lastname, firstname");
    $residents = $stmt->fetchAll();
    
    // Fetch all skills
    $stmt = $conn->query("SELECT * FROM skills WHERE status = 'Active' ORDER BY category, skill_name");
    $skills = $stmt->fetchAll();
    
    // Fetch all programs
    $stmt = $conn->query("
        SELECT lp.*, u.fullname as coordinator_name, 
               COUNT(DISTINCT ts.session_id) as session_count,
               COUNT(DISTINCT tp.participant_id) as participant_count
        FROM livelihood_programs lp
        LEFT JOIN users u ON lp.coordinator_id = u.user_id
        LEFT JOIN training_sessions ts ON lp.program_id = ts.program_id
        LEFT JOIN training_participants tp ON ts.session_id = tp.session_id
        GROUP BY lp.program_id
        ORDER BY lp.start_date DESC
    ");
    $programs = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching data: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-briefcase me-2"></i>Livelihood & Skills Registry</h2>
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
    
    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'skills' ? 'active' : ''; ?>" href="?tab=skills">
                <i class="fas fa-star me-2"></i>Resident Skills
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'programs' ? 'active' : ''; ?>" href="?tab=programs">
                <i class="fas fa-graduation-cap me-2"></i>Livelihood Programs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'training' ? 'active' : ''; ?>" href="livelihood_training.php">
                <i class="fas fa-chalkboard me-2"></i>Training Sessions
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'jobs' ? 'active' : ''; ?>" href="livelihood_jobs.php">
                <i class="fas fa-briefcase me-2"></i>Job Opportunities
            </a>
        </li>
    </ul>
    
    <!-- Resident Skills Tab -->
    <?php if ($tab === 'skills'): ?>
    <div class="tab-pane active">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Resident Skills Management</h4>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSkillModal">
                <i class="fas fa-plus me-2"></i>Add Skill to Resident
            </button>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Resident Name</th>
                        <th>Skill</th>
                        <th>Category</th>
                        <th>Proficiency</th>
                        <th>Experience (Years)</th>
                        <th>Certification</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt = $conn->query("
                            SELECT rs.*, r.firstname, r.lastname, s.skill_name, s.category
                            FROM resident_skills rs
                            JOIN residents r ON rs.resident_id = r.resident_id
                            JOIN skills s ON rs.skill_id = s.skill_id
                            ORDER BY r.lastname, r.firstname, s.skill_name
                        ");
                        $resident_skills = $stmt->fetchAll();
                        
                        if (count($resident_skills) > 0) {
                            foreach ($resident_skills as $rs) {
                                echo "<tr>";
                                echo "<td><strong>" . htmlspecialchars($rs['firstname'] . ' ' . $rs['lastname']) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($rs['skill_name']) . "</td>";
                                echo "<td><span class='badge bg-info'>" . htmlspecialchars($rs['category']) . "</span></td>";
                                echo "<td>";
                                $proficiency_colors = ['Beginner' => 'danger', 'Intermediate' => 'warning', 'Advanced' => 'success', 'Expert' => 'primary'];
                                echo "<span class='badge bg-" . $proficiency_colors[$rs['proficiency_level']] . "'>" . $rs['proficiency_level'] . "</span>";
                                echo "</td>";
                                echo "<td>" . $rs['years_of_experience'] . "</td>";
                                echo "<td>" . ($rs['certification'] ? htmlspecialchars($rs['certification']) : '-') . "</td>";
                                echo "<td>";
                                echo "<form method='POST' style='display:inline;'>";
                                echo "<input type='hidden' name='action' value='delete_skill'>";
                                echo "<input type='hidden' name='resident_skill_id' value='" . $rs['resident_skill_id'] . "'>";
                                echo "<button type='submit' class='btn btn-danger btn-sm' onclick='return confirm(\"Delete this skill?\")'>";
                                echo "<i class='fas fa-trash'></i>";
                                echo "</button>";
                                echo "</form>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center text-muted'>No skills recorded yet</td></tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='7' class='text-danger'>Error: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Livelihood Programs Tab -->
    <?php if ($tab === 'programs'): ?>
    <div class="tab-pane active">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Livelihood Programs</h4>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                <i class="fas fa-plus me-2"></i>Add Program
            </button>
        </div>
        
        <div class="row">
            <?php
            foreach ($programs as $program) {
                $status_colors = ['Planning' => 'secondary', 'Ongoing' => 'info', 'Completed' => 'success', 'Cancelled' => 'danger'];
                echo "<div class='col-md-6 mb-3'>";
                echo "<div class='card'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>" . htmlspecialchars($program['program_name']) . "</h5>";
                echo "<p class='card-text text-muted'>" . htmlspecialchars(substr($program['description'], 0, 100)) . "...</p>";
                echo "<div class='mb-2'>";
                echo "<span class='badge bg-" . $status_colors[$program['status']] . "'>" . $program['status'] . "</span>";
                echo "<span class='badge bg-secondary'>" . $program['program_type'] . "</span>";
                echo "</div>";
                echo "<small class='text-muted'>";
                echo "<i class='fas fa-calendar me-1'></i>" . date('M d, Y', strtotime($program['start_date']));
                if ($program['end_date']) {
                    echo " - " . date('M d, Y', strtotime($program['end_date']));
                }
                echo "<br>";
                echo "<i class='fas fa-user me-1'></i>Coordinator: " . htmlspecialchars($program['coordinator_name']) . "<br>";
                echo "<i class='fas fa-graduation-cap me-1'></i>Sessions: " . $program['session_count'] . " | Participants: " . $program['participant_count'];
                echo "</small>";
                echo "<div class='mt-3'>";
                echo "<button class='btn btn-sm btn-outline-primary' data-bs-toggle='modal' data-bs-target='#editProgramModal' onclick='editProgram(" . $program['program_id'] . ")'>";
                echo "<i class='fas fa-edit me-1'></i>Edit";
                echo "</button>";
                echo "<form method='POST' style='display:inline;'>";
                echo "<input type='hidden' name='action' value='delete_program'>";
                echo "<input type='hidden' name='program_id' value='" . $program['program_id'] . "'>";
                echo "<button type='submit' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"Delete this program?\")'>";
                echo "<i class='fas fa-trash me-1'></i>Delete";
                echo "</button>";
                echo "</form>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add Skill Modal -->
<div class="modal fade" id="addSkillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Skill to Resident</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_skill">
                    
                    <div class="mb-3">
                        <label class="form-label">Resident</label>
                        <select class="form-select" name="resident_id" required>
                            <option value="">Select Resident</option>
                            <?php foreach ($residents as $resident): ?>
                                <option value="<?php echo $resident['resident_id']; ?>">
                                    <?php echo htmlspecialchars($resident['fullname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Skill</label>
                        <select class="form-select" name="skill_id" required>
                            <option value="">Select Skill</option>
                            <?php foreach ($skills as $skill): ?>
                                <option value="<?php echo $skill['skill_id']; ?>">
                                    <?php echo htmlspecialchars($skill['skill_name'] . ' (' . $skill['category'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Proficiency Level</label>
                        <select class="form-select" name="proficiency_level" required>
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                            <option value="Expert">Expert</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Years of Experience</label>
                        <input type="number" class="form-control" name="years_of_experience" min="0" value="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Certification</label>
                        <input type="text" class="form-control" name="certification" placeholder="e.g., TESDA Certificate">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Skill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Program Modal -->
<div class="modal fade" id="addProgramModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Livelihood Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_program">
                    
                    <div class="mb-3">
                        <label class="form-label">Program Name</label>
                        <input type="text" class="form-control" name="program_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Program Type</label>
                            <select class="form-select" name="program_type" required>
                                <option value="Training">Training</option>
                                <option value="Livelihood">Livelihood</option>
                                <option value="Microfinance">Microfinance</option>
                                <option value="Skills Development">Skills Development</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="Planning">Planning</option>
                                <option value="Ongoing">Ongoing</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" placeholder="e.g., Barangay Hall">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target Beneficiaries</label>
                            <input type="number" class="form-control" name="target_beneficiaries" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Budget</label>
                            <input type="number" class="form-control" name="budget" step="0.01" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
