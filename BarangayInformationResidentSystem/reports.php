<?php
$page_title = "Reports & Analytics";
require_once 'includes/header.php';
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Get filter parameters
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');

try {
    // Population statistics
    $stmt = $conn->query("SELECT COUNT(*) as total FROM residents WHERE status = 'Active'");
    $total_population = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM residents WHERE status = 'Active' AND gender = 'Male'");
    $male_count = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM residents WHERE status = 'Active' AND gender = 'Female'");
    $female_count = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM residents WHERE status = 'Active' AND is_voter = 'Yes'");
    $voters_count = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM residents WHERE status = 'Active' AND is_pwd = 'Yes'");
    $pwd_count = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM residents WHERE status = 'Active' AND is_senior = 'Yes'");
    $senior_count = $stmt->fetch()['total'];
    
    // Certificate statistics
    $stmt = $conn->prepare("SELECT cert_type, COUNT(*) as count FROM certificates WHERE YEAR(date_issued) = ? GROUP BY cert_type");
    $stmt->execute([$year]);
    $cert_stats = $stmt->fetchAll();
    
    // Blotter statistics
    $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM blotter WHERE YEAR(date_filed) = ? GROUP BY status");
    $stmt->execute([$year]);
    $blotter_stats = $stmt->fetchAll();
    
    // Monthly certificate trend
    $stmt = $conn->prepare("SELECT MONTH(date_issued) as month, COUNT(*) as count FROM certificates WHERE YEAR(date_issued) = ? GROUP BY MONTH(date_issued) ORDER BY month");
    $stmt->execute([$year]);
    $monthly_certs = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error fetching data: ' . $e->getMessage();
}
?>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h2>
        <div>
            <button class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print Report
            </button>
        </div>
    </div>
    
    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <select class="form-select" name="year" onchange="this.form.submit()">
                        <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Population Statistics -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Population Statistics</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2 mb-3">
                    <div class="text-center">
                        <h3 class="mb-0" style="color: #667eea;"><?php echo number_format($total_population); ?></h3>
                        <small class="text-muted">Total Population</small>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="text-center">
                        <h3 class="mb-0" style="color: #4facfe;"><?php echo number_format($male_count); ?></h3>
                        <small class="text-muted">Male</small>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="text-center">
                        <h3 class="mb-0" style="color: #f5576c;"><?php echo number_format($female_count); ?></h3>
                        <small class="text-muted">Female</small>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="text-center">
                        <h3 class="mb-0" style="color: #43e97b;"><?php echo number_format($voters_count); ?></h3>
                        <small class="text-muted">Voters</small>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="text-center">
                        <h3 class="mb-0" style="color: #fa8231;"><?php echo number_format($pwd_count); ?></h3>
                        <small class="text-muted">PWD</small>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="text-center">
                        <h3 class="mb-0" style="color: #a55eea;"><?php echo number_format($senior_count); ?></h3>
                        <small class="text-muted">Senior Citizens</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Certificate Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Certificates Issued (<?php echo $year; ?>)</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Certificate Type</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_certs = 0;
                            foreach ($cert_stats as $stat): 
                                $total_certs += $stat['count'];
                            ?>
                                <tr>
                                    <td><?php echo $stat['cert_type']; ?></td>
                                    <td class="text-end"><strong><?php echo number_format($stat['count']); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-primary">
                                <td><strong>Total</strong></td>
                                <td class="text-end"><strong><?php echo number_format($total_certs); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Blotter Cases (<?php echo $year; ?>)</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_blotter = 0;
                            foreach ($blotter_stats as $stat): 
                                $total_blotter += $stat['count'];
                            ?>
                                <tr>
                                    <td><?php echo $stat['status']; ?></td>
                                    <td class="text-end"><strong><?php echo number_format($stat['count']); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-primary">
                                <td><strong>Total</strong></td>
                                <td class="text-end"><strong><?php echo number_format($total_blotter); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Trend -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Certificate Trend (<?php echo $year; ?>)</h5>
        </div>
        <div class="card-body">
            <canvas id="monthlyTrendChart" height="80"></canvas>
        </div>
    </div>
</div>

<script>
// Monthly trend chart
const monthlyData = <?php 
    $months = array_fill(1, 12, 0);
    foreach ($monthly_certs as $mc) {
        $months[$mc['month']] = $mc['count'];
    }
    echo json_encode(array_values($months));
?>;

const monthlyCtx = document.getElementById('monthlyTrendChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Certificates Issued',
            data: monthlyData,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
