<?php
/**
 * Diagnostic Tool - Check System Status
 */

echo "<h1>🔍 Barangay System Diagnostic Report</h1>";
echo "<hr>";

// 1. PHP Version
echo "<h2>1. PHP Version</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// 2. Required Extensions
echo "<h2>2. Required Extensions</h2>";
$extensions = ['pdo', 'pdo_mysql', 'mysqli', 'curl', 'json', 'session'];
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? "✅ Loaded" : "❌ Missing";
    echo "<p><strong>$ext:</strong> $status</p>";
}

// 3. File Permissions
echo "<h2>3. File & Folder Permissions</h2>";
$directories = [
    'uploads' => __DIR__ . '/uploads',
    'config' => __DIR__ . '/config',
    'includes' => __DIR__ . '/includes',
    'database' => __DIR__ . '/database'
];

foreach ($directories as $name => $path) {
    $exists = file_exists($path) ? "✅ Exists" : "❌ Missing";
    $writable = is_writable($path) ? "✅ Writable" : "⚠️ Not Writable";
    echo "<p><strong>$name:</strong> $exists | $writable</p>";
}

// 4. Configuration Files
echo "<h2>4. Configuration Files</h2>";
$configs = [
    'config.php' => __DIR__ . '/config/config.php',
    'database.php' => __DIR__ . '/config/database.php'
];

foreach ($configs as $name => $path) {
    $exists = file_exists($path) ? "✅ Found" : "❌ Missing";
    echo "<p><strong>$name:</strong> $exists</p>";
}

// 5. Database Connection Test
echo "<h2>5. Database Connection</h2>";
try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<p>✅ <strong style='color: green;'>Database Connected Successfully!</strong></p>";
        
        // Test query
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "<p>Users in database: " . $result['count'] . "</p>";
    } else {
        echo "<p>❌ <strong style='color: red;'>Database Connection Failed</strong></p>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong style='color: red;'>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 6. Session Status
echo "<h2>6. Session Status</h2>";
echo "<p><strong>Session Status:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? "✅ Active" : "⚠️ Not Active") . "</p>";
echo "<p><strong>Session Save Path:</strong> " . session_save_path() . "</p>";

// 7. Key Files
echo "<h2>7. Key Files Status</h2>";
$files = [
    'login.php',
    'dashboard.php',
    'index.php',
    'config/config.php',
    'config/database.php',
    'includes/header.php',
    'includes/sidebar.php',
    'includes/footer.php',
    'includes/modal.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    $status = file_exists($path) ? "✅ Found" : "❌ Missing";
    echo "<p><strong>$file:</strong> $status</p>";
}

// 8. Error Log
echo "<h2>8. Recent Errors</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $lines = file_tail($error_log, 10);
    echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
    foreach ($lines as $line) {
        echo htmlspecialchars($line) . "\n";
    }
    echo "</pre>";
} else {
    echo "<p>⚠️ Error log not found or disabled</p>";
}

// Helper function to get last lines of file
function file_tail($filename, $lines = 10) {
    $handle = @fopen($filename, "r");
    if ($handle) {
        $lineno = -1;
        $output = array();
        while (fgets($handle)) {
            $lineno++;
        }
        rewind($handle);
        $pos = 0;
        $lineno = 0;
        $begin = ($lineno - $lines);
        while (($line = fgets($handle)) !== false) {
            if ($lineno >= $begin) {
                $output[] = $line;
            }
            $lineno++;
        }
        fclose($handle);
        return $output;
    }
    return array();
}

echo "<hr>";
echo "<p style='color: #666; font-size: 12px;'>Generated: " . date('Y-m-d H:i:s') . "</p>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background-color: #f9f9f9;
    }
    
    h1 {
        color: #333;
        border-bottom: 3px solid #667eea;
        padding-bottom: 10px;
    }
    
    h2 {
        color: #555;
        margin-top: 20px;
        border-left: 4px solid #667eea;
        padding-left: 10px;
    }
    
    p {
        line-height: 1.8;
        color: #666;
    }
    
    strong {
        color: #333;
    }
    
    pre {
        overflow-x: auto;
        font-size: 12px;
    }
    
    hr {
        border: none;
        border-top: 1px solid #ddd;
        margin: 30px 0;
    }
</style>
