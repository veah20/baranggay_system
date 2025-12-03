<?php
/**
 * System Configuration
 * Barangay Information and Reporting System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure Database class is loaded
require_once __DIR__ . '/database.php';

// Timezone
date_default_timezone_set('Asia/Manila');

// Base URL
define('BASE_URL', 'http://localhost/BarangayInformationResidentSystem/');

// Directory paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('ASSETS_PATH', ROOT_PATH . '/assets/');

// Upload directories
define('PROFILE_UPLOAD_PATH', UPLOAD_PATH . 'profiles/');
define('SIGNATURE_UPLOAD_PATH', UPLOAD_PATH . 'signatures/');
define('LOGO_UPLOAD_PATH', UPLOAD_PATH . 'logos/');
define('DOCUMENTS_UPLOAD_PATH', UPLOAD_PATH . 'documents/');

// Create upload directories if they don't exist
$directories = [
    UPLOAD_PATH,
    PROFILE_UPLOAD_PATH,
    SIGNATURE_UPLOAD_PATH,
    LOGO_UPLOAD_PATH,
    DOCUMENTS_UPLOAD_PATH
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// System settings
define('SYSTEM_NAME', 'Barangay Information and Reporting System');
define('SYSTEM_ACRONYM', 'BIRS');
define('SYSTEM_VERSION', '1.0.0');

// Pagination
define('RECORDS_PER_PAGE', 10);

// Password settings
define('MIN_PASSWORD_LENGTH', 6);

// File upload settings
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

// Security
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Check if session is valid
 */
function checkSession() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . 'login.php?timeout=1');
        exit();
    }
    
    $_SESSION['last_activity'] = time();
}

/**
 * Check user role
 */
function hasRole($roles) {
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    return isset($_SESSION['role']) && in_array($_SESSION['role'], $roles);
}

/**
 * Sanitize input
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Format date
 */
function formatDate($date, $format = 'F d, Y') {
    return date($format, strtotime($date));
}

/**
 * Calculate age
 */
function calculateAge($birthdate) {
    $birthDate = new DateTime($birthdate);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
    return $age;
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length);
}

/**
 * Upload file
 */
function uploadFile($file, $targetDir, $allowedTypes = ALLOWED_IMAGE_TYPES) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds maximum limit'];
    }
    
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Log activity
 */
function logActivity($conn, $user_id, $action, $module, $details = null) {
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $module, $details, $ip_address]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}
?>
