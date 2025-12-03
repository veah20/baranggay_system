<?php
$page_title = "Job Opportunities & Matching";
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$success = '';
$error = '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'opportunities';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            // Add Job Opportunity
            if ($_POST['action'] === 'add_opportunity') {
                $stmt = $conn->prepare("
                    INSERT INTO job_opportunities (job_title, description, required_skills, employer_name, employer_contact, location, salary_range, employment_type, posted_date, deadline_date, status, posted_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $_POST['job_title'],
                    $_POST['description'],
                    $_POST['required_skills'],
                    $_POST['employer_name'],
                    $_POST['employer_contact'] ?? null,
                    $_POST['location'] ?? null,
                    $_POST['salary_range'] ?? null,
                    $_POST['employment_type'],
                    date('Y-m-d'),
                    $_POST['deadline_date'] ?? null,
                    'Open',
                    $_SESSION['user_id']
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Posted job opportunity: ' . $_POST['job_title'], 'Jobs');
                $success = 'Job opportunity posted successfully!';
            }
            
            // Update Job Opportunity
            elseif ($_POST['action'] === 'edit_opportunity') {
                $stmt = $conn->prepare("
                    UPDATE job_opportunities 
                    SET job_title = ?, description = ?, required_skills = ?, employer_name = ?, employer_contact = ?, 
                        location = ?, salary_range = ?, employment_type = ?, deadline_date = ?, status = ?
                    WHERE opportunity_id = ?
                ");
                
                $stmt->execute([
                    $_POST['job_title'],
                    $_POST['description'],
                    $_POST['required_skills'],
                    $_POST['employer_name'],
                    $_POST['employer_contact'] ?? null,
                    $_POST['location'] ?? null,
                    $_POST['salary_range'] ?? null,
                    $_POST['employment_type'],
                    $_POST['deadline_date'] ?? null,
                    $_POST['status'],
                    $_POST['opportunity_id']
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Updated job opportunity: ' . $_POST['job_title'], 'Jobs');
                $success = 'Job opportunity updated successfully!';
            }
            
            // Apply for Job
            elseif ($_POST['action'] === 'apply_job') {
                $stmt = $conn->prepare("
                    INSERT INTO job_applications (opportunity_id, resident_id, application_date, status)
                    VALUES (?, ?, ?, 'Applied')
                ");
                
                $stmt->execute([
                    $_POST['opportunity_id'],
                    $_POST['resident_id'],
                    date('Y-m-d')
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Resident applied for job opportunity', 'Jobs');
                $success = 'Application submitted successfully!';
            }
            
            // Update Application Status
            elseif ($_POST['action'] === 'update_application') {
                $stmt = $conn->prepare("
                    UPDATE job_applications 
                    SET status = ?, reviewed_by = ?, review_date = ?, notes = ?
                    WHERE application_id = ?
                ");
                
                $stmt->execute([
                    $_POST['status'],
                    $_SESSION['user_id'],
                    date('Y-m-d'),
                    $_POST['notes'] ?? null,
                    $_POST['application_id']
                ]);
                
                logActivity($conn, $_SESSION['user_id'], 'Updated job application status', 'Jobs');
                $success = 'Application status updated successfully!';
            }
        }
    } catch (PDOException $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Fetch data
$opportunities = [];
$applications = [];
$residents = [];

try {
    $stmt = $conn->query("SELECT resident_id, CONCAT(firstname, ' ', lastname) as fullname FROM residents WHERE status = 'Active' ORDER BY lastname, firstname");
    $residents = $stmt->fetchAll();
    
    $stmt = $conn->query("
        SELECT jo.*, u.fullname as posted_by_name,
               COUNT(DISTINCT ja.application_id) as application_count
        FROM job_opportunities jo
        LEFT JOIN users u ON jo.posted_by = u.user_id
        LEFT JOIN job_applications ja ON jo.opportunity_id = ja.opportunity_id
        GROUP BY jo.opportunity_id
        ORDER BY jo.posted_date DESC
    ");
    $opportunities = $stmt->fetchAll();
    
    $stmt = $conn->query("
        SELECT ja.*, jo.job_title, r.firstname, r.lastname, r.contact_number, r.email
        FROM job_applications ja
        JOIN job_opportunities jo ON ja.opportunity_id = jo.opportunity_id
        JOIN residents r ON ja.resident_id = r.resident_id
        ORDER BY ja.application_date DESC
    ");
    $applications = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching data: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-briefcase me-2"></i>Job Opportunities & Matching</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOpportunityModal">
            <i class="fas fa-plus me-2"></i>Post Job Opportunity
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
    
    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'opportunities' ? 'active' : ''; ?>" href="?tab=opportunities">
                <i class="fas fa-briefcase me-2"></i>Job Opportunities
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $tab === 'applications' ? 'active' : ''; ?>" href="?tab=applications">
                <i class="fas fa-file-alt me-2"></i>Applications
            </a>
        </li>
    </ul>
    
    <!-- Job Opportunities Tab -->
    <?php if ($tab === 'opportunities'): ?>
    <div class="tab-pane active">
        <div class="row">
            <?php
            foreach ($opportunities as $opp) {
                $status_colors = ['Open' => 'success', 'Closed' => 'danger', 'On Hold' => 'warning'];
                echo "<div class='col-md-6 mb-3'>";
                echo "<div class='card'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>" . htmlspecialchars($opp['job_title']) . "</h5>";
                echo "<p class='card-text text-muted'>" . htmlspecialchars(substr($opp['description'], 0, 100)) . "...</p>";
                echo "<div class='mb-2'>";
                echo "<span class='badge bg-" . $status_colors[$opp['status']] . "'>" . $opp['status'] . "</span>";
                echo "<span class='badge bg-secondary'>" . $opp['employment_type'] . "</span>";
                echo "</div>";
                echo "<small class='text-muted'>";
                echo "<i class='fas fa-building me-1'></i>" . htmlspecialchars($opp['employer_name']) . "<br>";
                if ($opp['location']) {
                    echo "<i class='fas fa-map-marker-alt me-1'></i>" . htmlspecialchars($opp['location']) . "<br>";
                }
                if ($opp['salary_range']) {
                    echo "<i class='fas fa-money-bill me-1'></i>" . htmlspecialchars($opp['salary_range']) . "<br>";
                }
                echo "<i class='fas fa-calendar me-1'></i>Posted: " . date('M d, Y', strtotime($opp['posted_date'])) . "<br>";
                if ($opp['deadline_date']) {
                    echo "<i class='fas fa-hourglass-end me-1'></i>Deadline: " . date('M d, Y', strtotime($opp['deadline_date'])) . "<br>";
                }
                echo "<i class='fas fa-file-alt me-1'></i>Applications: " . $opp['application_count'];
                echo "</small>";
                echo "<div class='mt-3'>";
                echo "<button class='btn btn-sm btn-outline-primary' data-bs-toggle='modal' data-bs-target='#viewOpportunityModal' onclick='viewOpportunity(" . $opp['opportunity_id'] . ")'>";
                echo "<i class='fas fa-eye me-1'></i>View Details";
                echo "</button>";
                echo "<button class='btn btn-sm btn-outline-info' data-bs-toggle='modal' data-bs-target='#applicationsModal' onclick='loadApplications(" . $opp['opportunity_id'] . ")'>";
                echo "<i class='fas fa-users me-1'></i>View Applications";
                echo "</button>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Applications Tab -->
    <?php if ($tab === 'applications'): ?>
    <div class="tab-pane active">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Job Title</th>
                        <th>Applicant</th>
                        <th>Contact</th>
                        <th>Application Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($applications as $app) {
                        $status_colors = ['Applied' => 'info', 'Under Review' => 'warning', 'Shortlisted' => 'primary', 'Rejected' => 'danger', 'Hired' => 'success', 'Withdrawn' => 'secondary'];
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($app['job_title']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($app['firstname'] . ' ' . $app['lastname']) . "</td>";
                        echo "<td>";
                        if ($app['contact_number']) {
                            echo "<i class='fas fa-phone me-1'></i>" . htmlspecialchars($app['contact_number']) . "<br>";
                        }
                        if ($app['email']) {
                            echo "<i class='fas fa-envelope me-1'></i>" . htmlspecialchars($app['email']);
                        }
                        echo "</td>";
                        echo "<td>" . date('M d, Y', strtotime($app['application_date'])) . "</td>";
                        echo "<td><span class='badge bg-" . $status_colors[$app['status']] . "'>" . $app['status'] . "</span></td>";
                        echo "<td>";
                        echo "<button class='btn btn-sm btn-outline-primary' data-bs-toggle='modal' data-bs-target='#updateApplicationModal' onclick='updateApplication(" . $app['application_id'] . ", \"" . $app['status'] . "\")'>";
                        echo "<i class='fas fa-edit me-1'></i>Update";
                        echo "</button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add Opportunity Modal -->
<div class="modal fade" id="addOpportunityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Post Job Opportunity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_opportunity">
                    
                    <div class="mb-3">
                        <label class="form-label">Job Title</label>
                        <input type="text" class="form-control" name="job_title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Required Skills</label>
                        <textarea class="form-control" name="required_skills" rows="3" placeholder="List required skills, one per line" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employer Name</label>
                            <input type="text" class="form-control" name="employer_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employer Contact</label>
                            <input type="text" class="form-control" name="employer_contact">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Salary Range</label>
                            <input type="text" class="form-control" name="salary_range" placeholder="e.g., 15,000 - 20,000">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employment Type</label>
                            <select class="form-select" name="employment_type" required>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contract">Contract</option>
                                <option value="Temporary">Temporary</option>
                                <option value="Freelance">Freelance</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Application Deadline</label>
                            <input type="date" class="form-control" name="deadline_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Post Opportunity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Application Modal -->
<div class="modal fade" id="updateApplicationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Application Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_application">
                    <input type="hidden" name="application_id" id="updateAppId">
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="Applied">Applied</option>
                            <option value="Under Review">Under Review</option>
                            <option value="Shortlisted">Shortlisted</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Hired">Hired</option>
                            <option value="Withdrawn">Withdrawn</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
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

<script>
function updateApplication(appId, currentStatus) {
    document.getElementById('updateAppId').value = appId;
}

function loadApplications(opportunityId) {
    // Load applications for specific opportunity
}

function viewOpportunity(opportunityId) {
    // View opportunity details
}
</script>

<?php include 'includes/footer.php'; ?>
