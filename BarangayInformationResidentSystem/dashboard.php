<?php
session_start();
$page_title = "Dashboard";
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Get statistics
try {
    // Total residents
    $stmt = $conn->query("SELECT COUNT(*) as total FROM residents WHERE status = 'Active'");
    $total_residents = $stmt->fetch()['total'];
    
    // Total households
    $stmt = $conn->query("SELECT COUNT(*) as total FROM households");
    $total_households = $stmt->fetch()['total'];
    
    // Total certificates issued this month
    $stmt = $conn->query("SELECT COUNT(*) as total FROM certificates WHERE MONTH(date_issued) = MONTH(CURRENT_DATE()) AND YEAR(date_issued) = YEAR(CURRENT_DATE())");
    $total_certificates = $stmt->fetch()['total'];
    
    // Total blotter cases this month
    $stmt = $conn->query("SELECT COUNT(*) as total FROM blotter WHERE MONTH(date_filed) = MONTH(CURRENT_DATE()) AND YEAR(date_filed) = YEAR(CURRENT_DATE())");
    $total_blotter = $stmt->fetch()['total'];
    
    // Gender distribution
    $stmt = $conn->query("SELECT gender, COUNT(*) as count FROM residents WHERE status = 'Active' GROUP BY gender");
    $gender_data = $stmt->fetchAll();
    
    // Age distribution
    $stmt = $conn->query("
        SELECT 
            CASE 
                WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) < 18 THEN 'Minor (0-17)'
                WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 35 THEN 'Young Adult (18-35)'
                WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 36 AND 59 THEN 'Adult (36-59)'
                ELSE 'Senior (60+)'
            END as age_group,
            COUNT(*) as count
        FROM residents 
        WHERE status = 'Active'
        GROUP BY age_group
        ORDER BY age_group
    ");
    $age_data = $stmt->fetchAll();
    
    // Certificate types this year
    $stmt = $conn->query("
        SELECT cert_type, COUNT(*) as count 
        FROM certificates 
        WHERE YEAR(date_issued) = YEAR(CURRENT_DATE())
        GROUP BY cert_type
        ORDER BY count DESC
    ");
    $cert_types = $stmt->fetchAll();
    
    // Recent certificates
    $stmt = $conn->query("
        SELECT c.*, CONCAT(r.firstname, ' ', r.lastname) as resident_name
        FROM certificates c
        JOIN residents r ON c.resident_id = r.resident_id
        ORDER BY c.date_issued DESC
        LIMIT 5
    ");
    $recent_certificates = $stmt->fetchAll();
    
    // Recent blotter cases
    $stmt = $conn->query("
        SELECT * FROM blotter
        ORDER BY date_filed DESC
        LIMIT 5
    ");
    $recent_blotter = $stmt->fetchAll();
    
    // Population by purok
    $stmt = $conn->query("
        SELECT purok, COUNT(*) as count 
        FROM residents 
        WHERE status = 'Active'
        GROUP BY purok
        ORDER BY purok
    ");
    $purok_data = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error fetching data: " . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<main class="main-content" id="main-content" role="main">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-0">Dashboard</h1>
            <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?>!</p>
        </div>
        <div class="text-muted">
            <i class="fas fa-calendar-alt me-2" aria-hidden="true"></i><?php echo date('F d, Y'); ?>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <section aria-label="Dashboard statistics">
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h3 aria-label="Total residents"><?php echo number_format($total_residents); ?></h3>
                    <p>Total Residents</p>
                    <i class="fas fa-users" aria-hidden="true"></i>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h3 aria-label="Total households"><?php echo number_format($total_households); ?></h3>
                    <p>Total Households</p>
                    <i class="fas fa-home" aria-hidden="true"></i>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h3 aria-label="Certificates issued this month"><?php echo number_format($total_certificates); ?></h3>
                    <p>Certificates (This Month)</p>
                    <i class="fas fa-certificate" aria-hidden="true"></i>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <h3 aria-label="Blotter cases this month"><?php echo number_format($total_blotter); ?></h3>
                    <p>Blotter Cases (This Month)</p>
                    <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Charts Row -->
    <section aria-label="Analytics charts">
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-pie me-2" aria-hidden="true"></i>Gender Distribution
                    </div>
                    <div class="card-body">
                        <canvas id="genderChart" height="200" role="img" aria-label="Gender distribution chart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar me-2" aria-hidden="true"></i>Age Distribution
                    </div>
                    <div class="card-body">
                        <canvas id="ageChart" height="200" role="img" aria-label="Age distribution chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-line me-2" aria-hidden="true"></i>Certificate Types (This Year)
                    </div>
                    <div class="card-body">
                        <canvas id="certChart" height="200" role="img" aria-label="Certificate types chart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-map-marker-alt me-2" aria-hidden="true"></i>Population by Purok
                    </div>
                    <div class="card-body">
                        <canvas id="purokChart" height="200" role="img" aria-label="Population by purok chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Recent Activities -->
    <section aria-label="Recent activities">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-certificate me-2" aria-hidden="true"></i>Recent Certificates</span>
                        <a href="certificates.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" role="table" aria-label="Recent certificates">
                                <thead>
                                    <tr>
                                        <th scope="col">Resident</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recent_certificates) > 0): ?>
                                        <?php foreach ($recent_certificates as $cert): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cert['resident_name']); ?></td>
                                                <td><span class="badge bg-info"><?php echo htmlspecialchars($cert['cert_type']); ?></span></td>
                                                <td><?php echo formatDate($cert['date_issued']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No recent certificates</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-clipboard-list me-2" aria-hidden="true"></i>Recent Blotter Cases</span>
                        <a href="blotter.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" role="table" aria-label="Recent blotter cases">
                                <thead>
                                    <tr>
                                        <th scope="col">Case #</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recent_blotter) > 0): ?>
                                        <?php foreach ($recent_blotter as $blotter): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($blotter['case_number']); ?></td>
                                                <td><?php echo htmlspecialchars($blotter['incident_type']); ?></td>
                                                <td>
                                                    <?php
                                                    $badge_class = 'secondary';
                                                    switch($blotter['status']) {
                                                        case 'Pending': $badge_class = 'warning'; break;
                                                        case 'Under Investigation': $badge_class = 'info'; break;
                                                        case 'Resolved': $badge_class = 'success'; break;
                                                        case 'Dismissed': $badge_class = 'secondary'; break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_class; ?>"><?php echo htmlspecialchars($blotter['status']); ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No recent blotter cases</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
// Gender Chart
const genderCtx = document.getElementById('genderChart').getContext('2d');
new Chart(genderCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($gender_data, 'gender')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($gender_data, 'count')); ?>,
            backgroundColor: ['#667eea', '#f5576c']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Age Chart
const ageCtx = document.getElementById('ageChart').getContext('2d');
new Chart(ageCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($age_data, 'age_group')); ?>,
        datasets: [{
            label: 'Population',
            data: <?php echo json_encode(array_column($age_data, 'count')); ?>,
            backgroundColor: '#667eea'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Certificate Chart
const certCtx = document.getElementById('certChart').getContext('2d');
new Chart(certCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($cert_types, 'cert_type')); ?>,
        datasets: [{
            label: 'Certificates Issued',
            data: <?php echo json_encode(array_column($cert_types, 'count')); ?>,
            backgroundColor: '#4facfe'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                beginAtZero: true
            }
        }
    }
});

// Purok Chart
const purokCtx = document.getElementById('purokChart').getContext('2d');
new Chart(purokCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($purok_data, 'purok')); ?>,
        datasets: [{
            label: 'Residents',
            data: <?php echo json_encode(array_column($purok_data, 'count')); ?>,
            backgroundColor: '#43e97b'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
