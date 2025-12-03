<?php
require_once '../config/database.php';

$database = new Database();
$conn = $database->getConnection();

$session_id = $_GET['session_id'] ?? 0;

try {
    $stmt = $conn->prepare("
        SELECT tp.*, r.firstname, r.lastname, r.contact_number
        FROM training_participants tp
        JOIN residents r ON tp.resident_id = r.resident_id
        WHERE tp.session_id = ?
        ORDER BY r.lastname, r.firstname
    ");
    $stmt->execute([$session_id]);
    $participants = $stmt->fetchAll();
    
    if (count($participants) > 0) {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-sm'>";
        echo "<thead class='table-light'>";
        echo "<tr>";
        echo "<th>Participant</th>";
        echo "<th>Contact</th>";
        echo "<th>Attendance</th>";
        echo "<th>Completion</th>";
        echo "<th>Certificate</th>";
        echo "<th>Actions</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($participants as $p) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($p['firstname'] . ' ' . $p['lastname']) . "</td>";
            echo "<td>" . htmlspecialchars($p['contact_number'] ?? '-') . "</td>";
            echo "<td>";
            echo "<form method='POST' action='../livelihood_training.php' style='display:inline;'>";
            echo "<input type='hidden' name='action' value='update_participant'>";
            echo "<input type='hidden' name='participant_id' value='" . $p['participant_id'] . "'>";
            echo "<select class='form-select form-select-sm' name='attendance_status' onchange='this.form.submit()'>";
            echo "<option value='Present' " . ($p['attendance_status'] === 'Present' ? 'selected' : '') . ">Present</option>";
            echo "<option value='Absent' " . ($p['attendance_status'] === 'Absent' ? 'selected' : '') . ">Absent</option>";
            echo "<option value='Excused' " . ($p['attendance_status'] === 'Excused' ? 'selected' : '') . ">Excused</option>";
            echo "</select>";
            echo "</form>";
            echo "</td>";
            echo "<td>";
            echo "<form method='POST' action='../livelihood_training.php' style='display:inline;'>";
            echo "<input type='hidden' name='action' value='update_participant'>";
            echo "<input type='hidden' name='participant_id' value='" . $p['participant_id'] . "'>";
            echo "<select class='form-select form-select-sm' name='completion_status' onchange='this.form.submit()'>";
            echo "<option value='Not Started' " . ($p['completion_status'] === 'Not Started' ? 'selected' : '') . ">Not Started</option>";
            echo "<option value='In Progress' " . ($p['completion_status'] === 'In Progress' ? 'selected' : '') . ">In Progress</option>";
            echo "<option value='Completed' " . ($p['completion_status'] === 'Completed' ? 'selected' : '') . ">Completed</option>";
            echo "<option value='Dropped' " . ($p['completion_status'] === 'Dropped' ? 'selected' : '') . ">Dropped</option>";
            echo "</select>";
            echo "</form>";
            echo "</td>";
            echo "<td>";
            if ($p['certificate_issued'] === 'Yes') {
                echo "<span class='badge bg-success'>" . htmlspecialchars($p['certificate_number']) . "</span>";
            } else {
                echo "<span class='badge bg-secondary'>Not Issued</span>";
            }
            echo "</td>";
            echo "<td>";
            echo "<form method='POST' action='../livelihood_training.php' style='display:inline;'>";
            echo "<input type='hidden' name='action' value='delete_participant'>";
            echo "<input type='hidden' name='participant_id' value='" . $p['participant_id'] . "'>";
            echo "<button type='submit' class='btn btn-danger btn-sm' onclick='return confirm(\"Remove participant?\")'>";
            echo "<i class='fas fa-trash'></i>";
            echo "</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    } else {
        echo "<p class='text-muted text-center'>No participants enrolled yet</p>";
    }
} catch (PDOException $e) {
    echo "<p class='text-danger'>Error: " . $e->getMessage() . "</p>";
}
?>
