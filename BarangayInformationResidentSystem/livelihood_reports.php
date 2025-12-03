<?php
$page_title = "Livelihood & Skills Reports";
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$error = '';
$report_type = isset($_GET['type']) ? $_GET['type'] : 'skills_summary';

// Initialize all variables
$skills_summary = [];
$program_participation = [];
$training_completion = [];
$job_report = [];
$resident_skills = [];

try {
    // Skills Summary Report
    if ($report_type === 'skills_summary' || $report_type === 'all') {
        $stmt = $conn->query("
            SELECT s.skill_name, s.category, COUNT(DISTINCT rs.resident_id) as resident_count,
                   AVG(CASE WHEN rs.proficiency_level = 'Beginner' THEN 1 ELSE 0 END) * 100 as beginner_pct,
                   AVG(CASE WHEN rs.proficiency_level = 'Intermediate' THEN 1 ELSE 0 END) * 100 as intermediate_pct,
                   AVG(CASE WHEN rs.proficiency_level = 'Advanced' THEN 1 ELSE 0 END) * 100 as advanced_pct,
                   AVG(CASE WHEN rs.proficiency_level = 'Expert' THEN 1 ELSE 0 END) * 100 as expert_pct
            FROM skills s
            LEFT JOIN resident_skills rs ON s.skill_id = rs.skill_id
            GROUP BY s.skill_id
            ORDER BY resident_count DESC
        ");
        $skills_summary = $stmt->fetchAll();
    }
    
    // Program Participation Report
    if ($report_type === 'programs' || $report_type === 'all') {
        $stmt = $conn->query("
            SELECT lp.program_name, lp.program_type, lp.status,
                   COUNT(DISTINCT ts.session_id) as session_count,
                   COUNT(DISTINCT tp.participant_id) as participant_count,
                   SUM(CASE WHEN tp.completion_status = 'Completed' THEN 1 ELSE 0 END) as completed_count,
                   ROUND(SUM(CASE WHEN tp.completion_status = 'Completed' THEN 1 ELSE 0 END) / COUNT(DISTINCT tp.participant_id) * 100, 2) as completion_rate
            FROM livelihood_programs lp
            LEFT JOIN training_sessions ts ON lp.program_id = ts.program_id
            LEFT JOIN training_participants tp ON ts.session_id = tp.session_id
            GROUP BY lp.program_id
            ORDER BY lp.start_date DESC
        ");
        $program_participation = $stmt->fetchAll();
    }
    
    // Training Completion Report
    if ($report_type === 'training' || $report_type === 'all') {
        $stmt = $conn->query("
            SELECT ts.session_name, lp.program_name, ts.trainer_name, ts.session_date,
                   COUNT(DISTINCT tp.participant_id) as total_enrolled,
                   SUM(CASE WHEN tp.attendance_status = 'Present' THEN 1 ELSE 0 END) as present_count,
                   SUM(CASE WHEN tp.completion_status = 'Completed' THEN 1 ELSE 0 END) as completed_count,
                   SUM(CASE WHEN tp.certificate_issued = 'Yes' THEN 1 ELSE 0 END) as certified_count
            FROM training_sessions ts
            LEFT JOIN livelihood_programs lp ON ts.program_id = lp.program_id
            LEFT JOIN training_participants tp ON ts.session_id = tp.session_id
            GROUP BY ts.session_id
            ORDER BY ts.session_date DESC
        ");
        $training_completion = $stmt->fetchAll();
    }
    
    // Job Opportunities Report
    if ($report_type === 'jobs' || $report_type === 'all') {
        $stmt = $conn->query("
            SELECT jo.job_title, jo.employment_type, jo.status, jo.posted_date,
                   COUNT(DISTINCT ja.application_id) as application_count,
                   SUM(CASE WHEN ja.status = 'Hired' THEN 1 ELSE 0 END) as hired_count,
                   SUM(CASE WHEN ja.status = 'Shortlisted' THEN 1 ELSE 0 END) as shortlisted_count
            FROM job_opportunities jo
            LEFT JOIN job_applications ja ON jo.opportunity_id = ja.opportunity_id
            GROUP BY jo.opportunity_id
            ORDER BY jo.posted_date DESC
        ");
        $job_report = $stmt->fetchAll();
    }
    
    // Resident Skills Profile Report
    if ($report_type === 'resident_profile' || $report_type === 'all') {
        $stmt = $conn->query("
            SELECT r.resident_id, r.firstname, r.lastname, r.occupation, r.contact_number,
                   COUNT(DISTINCT rs.skill_id) as skill_count,
                   GROUP_CONCAT(DISTINCT s.skill_name ORDER BY s.skill_name SEPARATOR ', ') as skills,
                   COUNT(DISTINCT tp.participant_id) as training_attended,
                   SUM(CASE WHEN tp.certificate_issued = 'Yes' THEN 1 ELSE 0 END) as certificates_earned
            FROM residents r
            LEFT JOIN resident_skills rs ON r.resident_id = rs.resident_id
            LEFT JOIN skills s ON rs.skill_id = s.skill_id
            LEFT JOIN training_participants tp ON r.resident_id = tp.resident_id
            WHERE r.status = 'Active'
            GROUP BY r.resident_id
            HAVING skill_count > 0 OR training_attended > 0
            ORDER BY skill_count DESC, r.lastname, r.firstname
        ");
        $resident_skills = $stmt->fetchAll();
    }
    
} catch (PDOException $e) {
    $error = 'Error generating report: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-bar me-2"></i>Livelihood & Skills Reports</h2>
        <div>
            <a href="livelihood_reports.php?type=all" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-print me-1"></i>Print All Reports
            </a>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Report Selection -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <a href="?type=skills_summary" class="btn btn-outline-primary w-100 mb-2 <?php echo $report_type === 'skills_summary' ? 'active' : ''; ?>">
                        <i class="fas fa-star me-1"></i>Skills Summary
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="?type=programs" class="btn btn-outline-primary w-100 mb-2 <?php echo $report_type === 'programs' ? 'active' : ''; ?>">
                        <i class="fas fa-graduation-cap me-1"></i>Programs
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="?type=training" class="btn btn-outline-primary w-100 mb-2 <?php echo $report_type === 'training' ? 'active' : ''; ?>">
                        <i class="fas fa-chalkboard me-1"></i>Training
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="?type=jobs" class="btn btn-outline-primary w-100 mb-2 <?php echo $report_type === 'jobs' ? 'active' : ''; ?>">
                        <i class="fas fa-briefcase me-1"></i>Jobs
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="?type=resident_profile" class="btn btn-outline-primary w-100 mb-2 <?php echo $report_type === 'resident_profile' ? 'active' : ''; ?>">
                        <i class="fas fa-users me-1"></i>Residents
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="?type=all" class="btn btn-outline-primary w-100 mb-2 <?php echo $report_type === 'all' ? 'active' : ''; ?>">
                        <i class="fas fa-list me-1"></i>All Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Skills Summary Report -->
    <?php if (($report_type === 'skills_summary' || $report_type === 'all') && count($skills_summary) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Skills Summary Report</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Skill Name</th>
                            <th>Category</th>
                            <th>Residents with Skill</th>
                            <th>Proficiency Distribution</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($skills_summary as $skill): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($skill['skill_name']); ?></strong></td>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars($skill['category']); ?></span></td>
                            <td><?php echo $skill['resident_count']; ?></td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <?php if ($skill['beginner_pct'] > 0): ?>
                                    <div class="progress-bar bg-danger" style="width: <?php echo $skill['beginner_pct']; ?>%" title="Beginner"></div>
                                    <?php endif; ?>
                                    <?php if ($skill['intermediate_pct'] > 0): ?>
                                    <div class="progress-bar bg-warning" style="width: <?php echo $skill['intermediate_pct']; ?>%" title="Intermediate"></div>
                                    <?php endif; ?>
                                    <?php if ($skill['advanced_pct'] > 0): ?>
                                    <div class="progress-bar bg-success" style="width: <?php echo $skill['advanced_pct']; ?>%" title="Advanced"></div>
                                    <?php endif; ?>
                                    <?php if ($skill['expert_pct'] > 0): ?>
                                    <div class="progress-bar bg-primary" style="width: <?php echo $skill['expert_pct']; ?>%" title="Expert"></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Program Participation Report -->
    <?php if (($report_type === 'programs' || $report_type === 'all') && count($program_participation) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Program Participation Report</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Program Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Sessions</th>
                            <th>Participants</th>
                            <th>Completed</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($program_participation as $prog): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($prog['program_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($prog['program_type']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($prog['status']); ?></span></td>
                            <td><?php echo $prog['session_count']; ?></td>
                            <td><?php echo $prog['participant_count']; ?></td>
                            <td><?php echo $prog['completed_count'] ?? 0; ?></td>
                            <td>
                                <?php 
                                $rate = $prog['completion_rate'] ?? 0;
                                $color = $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                                ?>
                                <span class="badge bg-<?php echo $color; ?>"><?php echo number_format($rate, 1); ?>%</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Training Completion Report -->
    <?php if (($report_type === 'training' || $report_type === 'all') && count($training_completion) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chalkboard me-2"></i>Training Completion Report</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Session Name</th>
                            <th>Program</th>
                            <th>Trainer</th>
                            <th>Date</th>
                            <th>Enrolled</th>
                            <th>Present</th>
                            <th>Completed</th>
                            <th>Certified</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($training_completion as $train): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($train['session_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($train['program_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($train['trainer_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($train['session_date'])); ?></td>
                            <td><?php echo $train['total_enrolled']; ?></td>
                            <td><?php echo $train['present_count'] ?? 0; ?></td>
                            <td><?php echo $train['completed_count'] ?? 0; ?></td>
                            <td><?php echo $train['certified_count'] ?? 0; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Job Opportunities Report -->
    <?php if (($report_type === 'jobs' || $report_type === 'all') && count($job_report) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Job Opportunities Report</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Job Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Posted Date</th>
                            <th>Applications</th>
                            <th>Shortlisted</th>
                            <th>Hired</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($job_report as $job): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($job['job_title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($job['employment_type']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($job['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($job['posted_date'])); ?></td>
                            <td><?php echo $job['application_count']; ?></td>
                            <td><?php echo $job['shortlisted_count'] ?? 0; ?></td>
                            <td><?php echo $job['hired_count'] ?? 0; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Resident Skills Profile Report -->
    <?php if (($report_type === 'resident_profile' || $report_type === 'all') && count($resident_skills) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Resident Skills Profile Report</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Resident Name</th>
                            <th>Occupation</th>
                            <th>Contact</th>
                            <th>Skills</th>
                            <th>Skill Count</th>
                            <th>Training Attended</th>
                            <th>Certificates</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resident_skills as $resident): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($resident['firstname'] . ' ' . $resident['lastname']); ?></strong></td>
                            <td><?php echo htmlspecialchars($resident['occupation'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($resident['contact_number'] ?? '-'); ?></td>
                            <td>
                                <?php 
                                if ($resident['skills']) {
                                    $skills_array = explode(', ', $resident['skills']);
                                    foreach (array_slice($skills_array, 0, 3) as $skill) {
                                        echo "<span class='badge bg-info me-1'>" . htmlspecialchars($skill) . "</span>";
                                    }
                                    if (count($skills_array) > 3) {
                                        echo "<span class='badge bg-secondary'>+" . (count($skills_array) - 3) . " more</span>";
                                    }
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                            <td><span class="badge bg-primary"><?php echo $resident['skill_count']; ?></span></td>
                            <td><?php echo $resident['training_attended'] ?? 0; ?></td>
                            <td><?php echo $resident['certificates_earned'] ?? 0; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
