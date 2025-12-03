# 🚂 Railway Deployment Guide (No Docker)

**Complete step-by-step guide to deploy your Barangay Information System to Railway without Docker.**

---

## 📋 Prerequisites

Before starting, ensure you have:
- ✅ GitHub account (create at github.com)
- ✅ Railway account (create at railway.app)
- ✅ Git installed on your computer
- ✅ Your BIRS project files ready
- ✅ Command line familiarity

---

## 🚀 Quick Start (7 Steps)

### **Step 1: Prepare Your Project Locally**

Navigate to your project directory:

```powershell
cd C:\xampp\htdocs\BarangayInformationResidentSystem
```

### **Step 2: Create .gitignore File**

Create a `.gitignore` file to exclude unnecessary files:

```
# Environment
.env
.env.local
.env.production

# Uploads (keep directory but ignore files)
uploads/*
!uploads/.gitkeep
!uploads/profiles/.gitkeep
!uploads/signatures/.gitkeep
!uploads/logos/.gitkeep
!uploads/documents/.gitkeep

# System
.DS_Store
Thumbs.db
*.log
logs/*
!logs/.gitkeep

# IDE
.vscode/
.idea/
*.sublime-project
*.sublime-workspace

# PHP
*.phar
composer.lock

# Database backups
*.sql.bak
database/backups/
```

Create the `.gitkeep` files to preserve directory structure:

```powershell
# Create .gitkeep files for upload directories
New-Item -Path "uploads/.gitkeep" -Force
New-Item -Path "uploads/profiles/.gitkeep" -Force
New-Item -Path "uploads/signatures/.gitkeep" -Force
New-Item -Path "uploads/logos/.gitkeep" -Force
New-Item -Path "uploads/documents/.gitkeep" -Force
New-Item -Path "logs/.gitkeep" -Force
```

### **Step 3: Create Procfile**

Create a `Procfile` in the project root directory:

```
web: php -S 0.0.0.0:${PORT:-8080}
```

This tells Railway to run PHP built-in server on the specified port.

### **Step 4: Create railway.toml Configuration**

Create `railway.toml` in the project root:

```toml
[build]
builder = "nixpacks"

[build.nixpacks]
providers = ["php", "mysql"]
nixpacks = "{ pkgs = [\"php82\", \"php82Packages.composer\"] }"

[deploy]
startCommand = "php -S 0.0.0.0:${PORT:-8080}"
restartPolicyMaxRetries = 3
restartPolicyWindowMs = 60000

[[services]]
name = "web"
startCommand = "php -S 0.0.0.0:${PORT:-8080}"

[environments.production]
PORT = "8080"
APP_ENV = "production"
```

### **Step 5: Update Configuration Files**

#### **Update config/database.php for Production**

Replace your current `database.php` with production-ready version:

```php
<?php
/**
 * Database Configuration
 * Supports both local development and Railway production
 */

if (!class_exists('Database')) {
    class Database {
        private $host;
        private $db_name;
        private $username;
        private $password;
        private $port;
        public $conn;

        public function __construct() {
            // Use environment variables if available (Railway)
            // Fall back to local configuration
            $this->host = getenv('DB_HOST') ?: (getenv('MYSQL_HOST') ?: 'localhost');
            $this->port = getenv('DB_PORT') ?: (getenv('MYSQL_PORT') ?: 3306);
            $this->db_name = getenv('DB_NAME') ?: (getenv('MYSQL_DATABASE') ?: 'birs_db');
            $this->username = getenv('DB_USER') ?: (getenv('MYSQL_USER') ?: 'root');
            $this->password = getenv('DB_PASSWORD') ?: (getenv('MYSQL_PASSWORD') ?: '');
        }

        /**
         * Get database connection
         */
        public function getConnection() {
            if ($this->conn !== null) {
                return $this->conn;
            }

            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];
                
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                
                $this->conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                $this->conn->exec("SET CHARACTER SET utf8mb4");
                
                // Log successful connection in production
                if (getenv('APP_ENV') === 'production') {
                    error_log("[DB] Connected to {$this->db_name} on {$this->host}:{$this->port}");
                }
                
            } catch(PDOException $exception) {
                $error_msg = "Database Connection Error: " . $exception->getMessage();
                error_log($error_msg);
                
                if (getenv('APP_ENV') === 'production') {
                    die("Database connection failed. Contact administrator.");
                } else {
                    die("Database connection failed. Error: " . htmlspecialchars($exception->getMessage()));
                }
            }

            return $this->conn;
        }

        public function closeConnection() {
            $this->conn = null;
        }

        public function __destruct() {
            $this->closeConnection();
        }
    }
}
?>
```

#### **Update config/config.php for Production**

Modify your `config.php` to support Railway environment:

```php
<?php
/**
 * System Configuration
 * Barangay Information and Reporting System
 * Supports Local Development and Railway Production
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Session configuration for production
    if (getenv('APP_ENV') === 'production') {
        ini_set('session.cookie_secure', 1);      // Only HTTPS
        ini_set('session.cookie_httponly', 1);    // No JavaScript access
        ini_set('session.cookie_samesite', 'Strict');
        session_name('BIRS_SESSION');
    }
    session_start();
}

// Ensure Database class is loaded
require_once __DIR__ . '/database.php';

// Detect environment
$environment = getenv('APP_ENV') ?: 'development';

// Timezone
date_default_timezone_set('Asia/Manila');

// Base URL - detect if running on Railway
if ($environment === 'production') {
    // Railway provides RAILWAY_DOMAIN
    $railway_domain = getenv('RAILWAY_DOMAIN');
    if ($railway_domain) {
        define('BASE_URL', 'https://' . $railway_domain . '/');
    } else {
        define('BASE_URL', 'http://localhost:8080/');
    }
} else {
    define('BASE_URL', 'http://localhost/BarangayInformationResidentSystem/');
}

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
        @mkdir($dir, 0755, true);
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

// Error reporting
if ($environment === 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', '/tmp/php_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

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
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, module, details, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $module, $details, $ip_address]);
        return true;
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}
?>
```

### **Step 6: Initialize Git and Push to GitHub**

```powershell
# Navigate to project directory
cd C:\xampp\htdocs\BarangayInformationResidentSystem

# Initialize git
git init

# Add all files
git add .

# Commit
git commit -m "Initial commit: BIRS ready for Railway deployment"

# Add GitHub remote (replace YOUR_USERNAME and REPO_NAME)
git remote add origin https://github.com/YOUR_USERNAME/barangay-information-system.git

# Rename branch to main
git branch -M main

# Push to GitHub
git push -u origin main
```

### **Step 7: Deploy to Railway**

1. Go to **railway.app** and log in
2. Click **"Create Project"**
3. Select **"Deploy from GitHub"**
4. Authorize Railway to access your GitHub
5. Select your repository: `barangay-information-system`
6. Click **"Deploy"**

---

## ⚙️ Configure Railway Environment Variables

Once deployed, add environment variables to Railway:

1. Go to Railway Dashboard
2. Click your project
3. Click **"Variables"** tab
4. Add these variables:

### **Database Configuration (Option A: Use Railway MySQL)**

```
DB_HOST=your_mysql_container_host
DB_PORT=3306
DB_NAME=birs_db
DB_USER=root
DB_PASSWORD=your_secure_password
```

### **Database Configuration (Option B: Use External MySQL)**

If using external provider (InfinityFree, Bluehost, etc.):

```
DB_HOST=your_external_host.com
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASSWORD=your_database_password
```

### **Application Configuration**

```
APP_ENV=production
RAILWAY_DOMAIN=your-app-name.railway.app
PORT=8080
SESSION_TIMEOUT=3600
MAX_FILE_SIZE=5242880
RECORDS_PER_PAGE=10
```

---

## 🗄️ Database Setup on Railway

### **Option 1: Use Railway's MySQL Plugin**

1. In Railway Dashboard, click **"+ New"**
2. Select **"MySQL"**
3. Railway automatically provisions MySQL
4. Copy connection credentials
5. Create empty database:

```sql
CREATE DATABASE birs_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

6. Import your schema:

```bash
mysql -h your_host -u user -p birs_db < database/birs_database.sql
```

### **Option 2: Use External MySQL Provider**

1. Keep your existing MySQL provider
2. Add connection details to Railway Variables
3. No additional setup needed

---

## 📂 Final Project Structure

Verify your project has this structure:

```
barangay-information-system/
├── config/
│   ├── config.php          (UPDATED)
│   ├── database.php        (UPDATED)
├── database/
│   ├── birs_database.sql
│   ├── complete_birs_database.sql
├── includes/
│   ├── header.php
│   ├── sidebar.php
│   ├── footer.php
│   ├── modal.php
├── api/
│   ├── get_participants.php
├── uploads/
│   ├── profiles/
│   ├── signatures/
│   ├── logos/
│   ├── documents/
├── logs/
├── .gitignore              (NEW)
├── Procfile                (NEW)
├── railway.toml            (NEW)
├── login.php
├── dashboard.php
├── index.php
├── residents.php
├── certificates.php
├── README.md
└── [other PHP files]
```

---

## 🚀 Deployment Checklist

Before deploying:

- [ ] All PHP files are in place
- [ ] `Procfile` created with correct command
- [ ] `railway.toml` configured
- [ ] `.gitignore` created
- [ ] `config/config.php` updated for production
- [ ] `config/database.php` updated for production
- [ ] Local testing completed successfully
- [ ] Git repository initialized and committed
- [ ] GitHub repository created
- [ ] Code pushed to GitHub main branch
- [ ] Railway project created
- [ ] Environment variables configured
- [ ] Database setup on Railway
- [ ] Database imported successfully

---

## ✅ Testing After Deployment

### **1. Check if Site is Accessible**

Visit: `https://your-app-name.railway.app`

You should see your login page.

### **2. Test Database Connection**

Create a test file at root: `test_db.php`

```php
<?php
require_once 'config/config.php';

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo "✅ Database connected successfully!<br>";
    
    $stmt = $conn->query("SELECT COUNT(*) as count FROM residents");
    $result = $stmt->fetch();
    echo "Total residents: " . $result['count'];
} else {
    echo "❌ Database connection failed!";
}
?>
```

Visit: `https://your-app-name.railway.app/test_db.php`

Delete this file after testing.

### **3. Test Login**

- Go to your app URL
- Login with: `admin` / `admin123` (or your configured credentials)
- Should see dashboard

### **4. Test Upload Functionality**

- Try adding a new resident with profile picture
- Verify image uploads successfully

### **5. Check Logs**

In Railway Dashboard:
- Click **Deployments**
- Click your deployment
- Click **Logs** tab
- Look for any errors

---

## 🔍 View Logs and Debug

### **Real-time Logs in Railway**

```
Railway Dashboard → Your Project → Deployments → Logs
```

### **Access PHP Error Logs**

Connect via SSH and check:

```bash
cat /tmp/php_errors.log
```

### **Common Issues & Solutions**

#### **"404 Not Found"**
- Verify all PHP files are committed
- Check file structure matches project root
- Ensure Procfile exists

#### **"Cannot connect to database"**
- Verify DATABASE variables are set
- Check database exists and is accessible
- Verify credentials are correct
- Test with `test_db.php`

#### **"502 Bad Gateway"**
- Check Railway logs for PHP errors
- Verify Procfile syntax
- Ensure port 8080 is used
- Restart deployment

#### **"Session not persisting"**
- Check `/tmp` directory exists
- Verify session.save_path setting
- Check file permissions

---

## 🔐 Security Best Practices

1. **Change Default Password**
   - After first login, change admin password
   - Use strong passwords (12+ characters)

2. **Enable HTTPS**
   - Railway provides free HTTPS
   - Always use HTTPS in production

3. **Secure Environment Variables**
   - Never commit `.env` files
   - Use Railway's variable management
   - Rotate credentials regularly

4. **Database Security**
   - Use strong database password
   - Restrict IP access if possible
   - Regular backups

5. **File Permissions**
   - Keep uploads directory protected
   - Don't allow script execution in uploads
   - Regular security audits

---

## 📊 Monitoring & Performance

### **Check Performance**

1. Railway Dashboard → Deployments → Metrics
2. Monitor CPU, Memory, Network usage
3. Check response times

### **Scale if Needed**

If performance degrades:
1. Upgrade Railway plan
2. Optimize database queries
3. Add caching layer
4. Enable query optimization

---

## 🔄 Updates & Redeployment

### **To Update Your App**

```powershell
# Make changes locally
git add .
git commit -m "Update: description of changes"
git push origin main
```

Railway automatically redeploys on push to main branch!

### **Monitor Redeployment**

1. Go to Railway Dashboard
2. Watch deployment progress
3. Check logs for errors
4. Verify app still works

---

## 💾 Database Backups

### **Create Manual Backup**

```bash
mysqldump -h your_host -u user -p database_name > backup_YYYY-MM-DD.sql
```

### **Restore from Backup**

```bash
mysql -h your_host -u user -p database_name < backup_YYYY-MM-DD.sql
```

### **Scheduled Backups**

Set up automated backups using:
- Railway's backup features
- Cron jobs
- External backup services

---

## 🆘 Getting Help

### **Railway Support**
- Docs: https://docs.railway.app
- Status: https://status.railway.app
- Discord: Railway Community

### **System Support**
- Check `diagnostic.php` for system info
- Review Railway logs
- Verify all configurations

---

## 📝 Post-Deployment Tasks

### **Immediate**
- [ ] Verify site is accessible
- [ ] Test all major features
- [ ] Create admin account backup
- [ ] Set up monitoring

### **First Week**
- [ ] Monitor logs daily
- [ ] Gather user feedback
- [ ] Fix any issues
- [ ] Optimize performance

### **Ongoing**
- [ ] Weekly backups
- [ ] Monthly security checks
- [ ] Update documentation
- [ ] Monitor usage metrics

---

## 🎉 Success!

Your Barangay Information System is now live on Railway! 

### You have achieved:
- ✅ Application deployed to production
- ✅ Database connected and accessible
- ✅ Auto-deployment from GitHub enabled
- ✅ Secure HTTPS connection
- ✅ Environment variables configured
- ✅ Monitoring and logging active

### Next Steps:
1. Share your app URL with stakeholders
2. Train users on system usage
3. Monitor performance
4. Plan feature enhancements

---

**Last Updated:** December 3, 2025  
**Version:** 2.0 (No Docker)  
**Status:** Ready for Deployment
