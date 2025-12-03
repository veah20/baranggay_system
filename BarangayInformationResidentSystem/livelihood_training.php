<?php
$page_title = "Training Sessions Management";
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            // Add Training Session
            if ($_POST['action'] === 'add_session') {
                $stmt = $conn->prepare("
                    INSERT INTO training_sessions (program_id, session_name, skill_id, trainer_name, session_date, session_time, duration_hours, location, max_participants, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $_POST['program_id'],
                    $_POST['session_name'],
                    $_POST['skill_id'] ?? null,
                    $_POST['trainer_name'],
                    $_POST['session_date'],
                    $_POST['session_time'] ?? null,
                    $_POST['duration_hours'] ?? null,
                    $_POST['location'] ?? null,
                    $_POST['max_participants'] ?? null,
                    'Scheduled'
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Created training session: ' . $_POST['session_name'], 'Training');
                $success = 'Training session created successfully!';
            }
            
            // Enroll Participant
            elseif ($_POST['action'] === 'enroll_participant') {
                $stmt = $conn->prepare("
                    INSERT INTO training_participants (session_id, resident_id, enrolled_date, completion_status)
                    VALUES (?, ?, ?, 'Not Started')
                ");
                
                $stmt->execute([
                    $_POST['session_id'],
                    $_POST['resident_id'],
                    date('Y-m-d')
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Enrolled resident in training session', 'Training');
                $success = 'Resident enrolled successfully!';
            }
            
            // Update Participant Status
            elseif ($_POST['action'] === 'update_participant') {
                $stmt = $conn->prepare("
                    UPDATE training_participants 
                    SET completion_status = ?, attendance_status = ?, certificate_issued = ?, certificate_number = ?, completion_date = ?
                    WHERE participant_id = ?
                ");
                
                $completion_date = ($_POST['completion_status'] === 'Completed') ? date('Y-m-d') : null;
                
                $stmt->execute([
                    $_POST['completion_status'],
                    $_POST['attendance_status'] ?? 'Present',
                    $_POST['certificate_issued'] ?? 'No',
                    $_POST['certificate_number'] ?? null,
                    $completion_date,
                    $_POST['participant_id']
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Updated training participant status', 'Training');
                $success = 'Participant status updated successfully!';
            }
            
            // Delete Participant
            elseif ($_POST['action'] === 'delete_participant') {
                $stmt = $conn->prepare("DELETE FROM training_participants WHERE participant_id = ?");
                $stmt->execute([$_POST['participant_id']]);
                
                logActivity($conn, $_SESSION['user_id'], 'Removed participant from training', 'Training');
                $success = 'Participant removed successfully!';
            }
        }
    } catch (PDOException $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Fetch data
$programs = [];
$sessions = [];
$residents = [];

try {
    $stmt = $conn->query("SELECT * FROM livelihood_programs WHERE status IN ('Planning', 'Ongoing') ORDER BY start_date DESC");
    $programs = $stmt->fetchAll();
    
    $stmt = $conn->query("SELECT resident_id, CONCAT(firstname, ' ', lastname) as fullname FROM residents WHERE status = 'Active' ORDER BY lastname, firstname");
    $residents = $stmt->fetchAll();
    
    $stmt = $conn->query("
        SELECT ts.*, lp.program_name, s.skill_name,
               COUNT(DISTINCT tp.participant_id) as enrolled_count
        FROM training_sessions ts
        LEFT JOIN livelihood_programs lp ON ts.program_id = lp.program_id
        LEFT JOIN skills s ON ts.skill_id = s.skill_id
        LEFT JOIN training_participants tp ON ts.session_id = tp.session_id
        GROUP BY ts.session_id
        ORDER BY ts.session_date DESC
    ");
    $sessions = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching data: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chalkboard me-2"></i>Training Sessions Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSessionModal">
            <i class="fas fa-plus me-2"></i>Add Training Session
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
    
    <!-- Training Sessions List -->
    <div class="row">
        <?php
        foreach ($sessions as $session) {
            $status_colors = ['Scheduled' => 'info', 'Ongoing' => 'warning', 'Completed' => 'success', 'Cancelled' => 'danger'];
            echo "<div class='col-md-6 mb-3'>";
            echo "<div class='card'>";
            echo "<div class='card-body'>";
            echo "<h5 class='card-title'>" . htmlspecialchars($session['session_name']) . "</h5>";
            echo "<p class='card-text text-muted'>";
            echo "<i class='fas fa-graduation-cap me-1'></i>" . htmlspecialchars($session['program_name']) . "<br>";
            if ($session['skill_name']) {
                echo "<i class='fas fa-star me-1'></i>" . htmlspecialchars($session['skill_name']) . "<br>";
            }
            echo "<i class='fas fa-user-tie me-1'></i>Trainer: " . htmlspecialchars($session['trainer_name']) . "<br>";
            echo "<i class='fas fa-calendar me-1'></i>" . date('M d, Y', strtotime($session['session_date']));
            if ($session['session_time']) {
                echo " at " . date('H:i', strtotime($session['session_time']));
            }
            echo "<br>";
            if ($session['duration_hours']) {
                echo "<i class='fas fa-clock me-1'></i>" . $session['duration_hours'] . " hours<br>";
            }
            echo "<i class='fas fa-users me-1'></i>Enrolled: " . $session['enrolled_count'];
            if ($session['max_participants']) {
                echo " / " . $session['max_participants'];
            }
            echo "</p>";
            echo "<div class='mb-2'>";
            echo "<span class='badge bg-" . $status_colors[$session['status']] . "'>" . $session['status'] . "</span>";
            echo "</div>";
            echo "<div>";
            echo "<button class='btn btn-sm btn-outline-primary' data-bs-toggle='modal' data-bs-target='#enrollModal' onclick='setSessionId(" . $session['session_id'] . ")'>";
            echo "<i class='fas fa-user-plus me-1'></i>Enroll Participant";
            echo "</button>";
            echo "<button class='btn btn-sm btn-outline-info' data-bs-toggle='modal' data-bs-target='#participantsModal' onclick='loadParticipants(" . $session['session_id'] . ")'>";
            echo "<i class='fas fa-list me-1'></i>View Participants";
            echo "</button>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
        ?>
    </div>
</div>

<!-- Add Session Modal -->
<div class="modal fade" id="addSessionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Training Session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_session">
                    
                    <div class="mb-3">
                        <label class="form-label">Program</label>
                        <select class="form-select" name="program_id" required>
                            <option value="">Select Program</option>
                            <?php foreach ($programs as $program): ?>
                                <option value="<?php echo $program['program_id']; ?>">
                                    <?php echo htmlspecialchars($program['program_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Session Name</label>
                        <input type="text" class="form-control" name="session_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Related Skill (Optional)</label>
                        <select class="form-select" name="skill_id">
                            <option value="">Select Skill</option>
                            <?php
                            try {
                                $stmt = $conn->query("SELECT * FROM skills WHERE status = 'Active' ORDER BY category, skill_name");
                                $skills = $stmt->fetchAll();
                                foreach ($skills as $skill) {
                                    echo "<option value='" . $skill['skill_id'] . "'>" . htmlspecialchars($skill['skill_name'] . ' (' . $skill['category'] . ')') . "</option>";
                                }
                            } catch (PDOException $e) {}
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Trainer Name</label>
                        <input type="text" class="form-control" name="trainer_name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Session Date</label>
                            <input type="date" class="form-control" name="session_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Session Time</label>
                            <input type="time" class="form-control" name="session_time">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Duration (Hours)</label>
                            <input type="number" class="form-control" name="duration_hours" min="0" step="0.5">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Max Participants</label>
                            <input type="number" class="form-control" name="max_participants" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" placeholder="e.g., Barangay Hall">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Enroll Participant Modal -->
<div class="modal fade" id="enrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enroll Participant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="enroll_participant">
                    <input type="hidden" name="session_id" id="enrollSessionId">
                    
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Enroll</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Participants Modal -->
<div class="modal fade" id="participantsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Training Participants</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="participantsContent">Loading...</div>
            </div>
        </div>
    </div>
</div>

<script>
function setSessionId(sessionId) {
    document.getElementById('enrollSessionId').value = sessionId;
}

function loadParticipants(sessionId) {
    fetch('api/get_participants.php?session_id=' + sessionId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('participantsContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('participantsContent').innerHTML = 'Error loading participants';
        });
}
</script>

<?php include 'includes/footer.php'; ?>
