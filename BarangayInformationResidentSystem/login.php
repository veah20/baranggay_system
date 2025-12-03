<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$timeout_message = '';

if (isset($_GET['timeout'])) {
    $timeout_message = 'Your session has expired. Please login again.';
}

if (isset($_GET['logout'])) {
    $timeout_message = 'You have been logged out successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $database = new Database();
        $conn = $database->getConnection();
        
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'Active'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // Update last login
                $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                $updateStmt->execute([$user['user_id']]);
                
                // Log activity
                logActivity($conn, $user['user_id'], 'User logged in', 'Authentication');
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to <?php echo SYSTEM_ACRONYM; ?> - <?php echo SYSTEM_NAME; ?>">
    <title>Login - <?php echo SYSTEM_ACRONYM; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16px;
            line-height: 1.5;
        }
        
        /* Focus visible for better keyboard navigation */
        *:focus-visible {
            outline: 3px solid white;
            outline-offset: 2px;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header i {
            font-size: 50px;
            margin-bottom: 15px;
        }
        .login-header h1 {
            margin: 0;
            font-weight: 600;
            font-size: 28px;
        }
        .login-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .login-body {
            padding: 40px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 2px solid #ddd;
            font-size: 16px;
        }
        .form-control:focus {
            border-color: #2a5298;
            box-shadow: 0 0 0 0.2rem rgba(42, 82, 152, 0.25);
        }
        .form-control:disabled,
        .form-control[readonly] {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .input-group-text {
            background: #f8f9fa;
            border-radius: 8px 0 0 8px;
            border: 2px solid #ddd;
            border-right: none;
            color: #2a5298;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 8px 8px 0;
        }
        .btn-login {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: transform 0.2s;
            font-size: 16px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(42, 82, 152, 0.4);
            color: white;
        }
        .btn-login:focus-visible {
            outline: 3px solid #2a5298;
            outline-offset: 2px;
            transform: none;
        }
        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            border: 1px solid;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <header class="login-header">
                <i class="fas fa-building" aria-hidden="true"></i>
                <h1><?php echo SYSTEM_ACRONYM; ?></h1>
                <p><?php echo SYSTEM_NAME; ?></p>
            </header>
            <main class="login-body">
                <?php if ($timeout_message): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2" aria-hidden="true"></i><?php echo htmlspecialchars($timeout_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2" aria-hidden="true"></i><?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" novalidate>
                    <fieldset>
                        <legend style="display: none;">Login Form</legend>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span aria-label="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user" aria-hidden="true"></i>
                                </span>
                                <input 
                                    type="text" 
                                    id="username"
                                    class="form-control" 
                                    name="username" 
                                    placeholder="Enter username" 
                                    required 
                                    autofocus
                                    aria-describedby="username-help"
                                    autocomplete="username">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password <span aria-label="required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock" aria-hidden="true"></i>
                                </span>
                                <input 
                                    type="password" 
                                    id="password"
                                    class="form-control" 
                                    name="password" 
                                    placeholder="Enter password" 
                                    required
                                    aria-describedby="password-help"
                                    autocomplete="current-password">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-login">
                            <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>Login
                        </button>
                    </fieldset>
                </form>
                
                <div class="mt-4 text-center text-muted">
                </div>
            </main>
        </div>
        
        <div class="text-center mt-3 text-white">
            <small>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(SYSTEM_ACRONYM); ?>. All rights reserved.</small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
