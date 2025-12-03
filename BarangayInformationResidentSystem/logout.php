<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (isLoggedIn()) {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Log activity
    logActivity($conn, $_SESSION['user_id'], 'User logged out', 'Authentication');
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header('Location: login.php?logout=1');
exit();
?>
