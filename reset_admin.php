<?php
// Reset Admin Password Script - Delete this file after use
require_once 'config/database.php';

echo "<h2>Reset Admin Password</h2>";

if (isset($_POST['reset'])) {
    $database = new Database();
    $conn = $database->getConnection();
    
    $new_password = 'admin123';
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->execute([$hashed_password]);
        
        echo "<div style='padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
        echo "✅ <strong>Password Reset Successful!</strong><br><br>";
        echo "You can now login with:<br>";
        echo "Username: <strong>admin</strong><br>";
        echo "Password: <strong>admin123</strong><br><br>";
        echo "<a href='login.php' style='color: #155724; font-weight: bold;'>Go to Login Page</a>";
        echo "</div>";
        
        // Verify it works
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = 'admin'");
        $stmt->execute();
        $user = $stmt->fetch();
        
        if (password_verify($new_password, $user['password'])) {
            echo "<p style='color: green;'>✅ Password verification test: PASSED</p>";
        } else {
            echo "<p style='color: red;'>❌ Password verification test: FAILED</p>";
        }
        
    } catch (PDOException $e) {
        echo "<div style='padding: 15px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "❌ Error: " . $e->getMessage();
        echo "</div>";
    }
} else {
    ?>
    <div style="max-width: 500px; margin: 50px auto; padding: 30px; border: 2px solid #667eea; border-radius: 10px; background: #f8f9fa;">
        <p>This will reset the admin password to: <strong>admin123</strong></p>
        <form method="POST">
            <button type="submit" name="reset" style="width: 100%; padding: 15px; background: #667eea; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; font-weight: bold;">
                Reset Admin Password
            </button>
        </form>
        <p style="margin-top: 20px; font-size: 12px; color: #666;">
            <strong>Note:</strong> Delete this file (reset_admin.php) after resetting the password for security.
        </p>
    </div>
    <?php
}
?>

<style>
    body { 
        font-family: Arial, sans-serif; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        margin: 0;
        padding: 20px;
    }
    h2 { 
        text-align: center; 
        color: white;
        margin-top: 50px;
    }
    button:hover {
        background: #5568d3 !important;
    }
</style>
