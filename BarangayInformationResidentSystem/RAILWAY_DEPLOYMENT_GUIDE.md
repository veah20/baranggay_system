# 🚂 Railway Deployment Guide

Complete step-by-step guide to deploy your Barangay Information and Reporting System to Railway.

---

## 📋 Prerequisites

Before you start, make sure you have:
- ✅ GitHub account (create one at github.com if needed)
- ✅ Railway account (create one at railway.app)
- ✅ Git installed on your computer
- ✅ Your BIRS project files ready

---

## 🚀 Quick Start (5 Steps)

### **Step 1: Create GitHub Repository**

1. Go to **github.com** and log in
2. Click **"+"** icon → **"New repository"**
3. Fill in:
   - **Repository name**: `barangay-information-system`
   - **Description**: Barangay Information and Reporting System
   - **Public** or **Private** (your choice)
4. Click **"Create repository"**

### **Step 2: Push Code to GitHub**

**On Windows (Command Prompt):**

```bash
cd C:\xampp\htdocs\BarangayInformationResidentSystem

# Initialize git
git init

# Add all files
git add .

# Commit
git commit -m "Initial commit: Barangay Information System"

# Add remote (replace YOUR_USERNAME and REPO_NAME)
git remote add origin https://github.com/YOUR_USERNAME/barangay-information-system.git

# Push to GitHub
git branch -M main
git push -u origin main
```

### **Step 3: Create Railway Project**

1. Go to **railway.app**
2. Log in or create account
3. Click **"Create Project"** → **"Deploy from GitHub"**
4. Select your repository: `barangay-information-system`
5. Click **"Deploy"**

### **Step 4: Configure Railway Settings**

**In Railway Dashboard:**

1. Click on **"Variables"** tab
2. Add these environment variables:

```
DB_HOST=your_database_host
DB_NAME=birs_db
DB_USER=your_db_user
DB_PASSWORD=your_db_password
APP_ENV=production
```

### **Step 5: Deploy**

1. Railway auto-deploys from GitHub
2. Wait for deployment to complete
3. Click **"View Deployment"** to see your live site

---

## 📝 Detailed Setup Guide

### **Part 1: Create Procfile**

Create a file named `Procfile` in your project root:

```
web: php -S 0.0.0.0:${PORT:-8080} -t public
```

Or if you want to use Apache:

```
web: vendor/bin/heroku-php-apache2 public/
```

### **Part 2: Create Railway Configuration**

Create `railway.json` in project root:

```json
{
  "name": "Barangay Information System",
  "description": "BIRS - Barangay Information and Reporting System",
  "buildPacks": ["php"],
  "variables": {
    "DB_HOST": "your_mysql_host",
    "DB_NAME": "birs_db",
    "DB_USER": "root",
    "DB_PASSWORD": "your_password"
  }
}
```

### **Part 3: Update config/database.php for Production**

```php
<?php
/**
 * Database Configuration - Production (Railway)
 */

if (!class_exists('Database')) {
    class Database {
        private $host;
        private $db_name;
        private $username;
        private $password;
        public $conn;

        public function __construct() {
            // Use environment variables if available (Railway)
            $this->host = getenv('DB_HOST') ?: 'localhost';
            $this->db_name = getenv('DB_NAME') ?: 'birs_db';
            $this->username = getenv('DB_USER') ?: 'root';
            $this->password = getenv('DB_PASSWORD') ?: '';
        }

        /**
         * Get database connection
         */
        public function getConnection() {
            if ($this->conn !== null) {
                return $this->conn;
            }

            try {
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];
                
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                
                $this->conn->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
                $this->conn->exec("SET CHARACTER SET utf8mb4");
                
            } catch(PDOException $exception) {
                error_log("Database Connection Error: " . $exception->getMessage());
                die("Database connection failed. Error: " . htmlspecialchars($exception->getMessage()));
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

### **Part 4: Update config/config.php for Production**

```php
<?php
// Detect environment
$env = getenv('APP_ENV') ?: 'development';

// Base URL
if ($env === 'production') {
    define('BASE_URL', getenv('RAILWAY_URL') ?: 'http://localhost/BarangayInformationResidentSystem/');
} else {
    define('BASE_URL', 'http://localhost/BarangayInformationResidentSystem/');
}

// Rest of config...
?>
```

---

## 🗄️ Database Setup on Railway

### **Option 1: Use Railway MySQL**

1. In Railway Dashboard, click **"+ New"**
2. Select **"MySQL"**
3. Railway creates MySQL instance automatically
4. Get credentials from **"Variables"** section
5. Import your database:
   ```bash
   mysql -h your_host -u user -p database_name < database/birs_database.sql
   ```

### **Option 2: Use External MySQL (InfinityFree, etc.)**

1. Use existing MySQL credentials
2. In Railway, add variables:
   - `DB_HOST`: your_host
   - `DB_NAME`: your_db
   - `DB_USER`: your_user
   - `DB_PASSWORD`: your_password

---

## 📂 Project Structure for Railway

```
barangay-information-system/
├── config/
│   ├── config.php
│   ├── database.php
│   └── database.infinityfree.php
├── includes/
│   ├── header.php
│   ├── sidebar.php
│   ├── footer.php
│   └── modal.php
├── database/
│   ├── birs_database.sql
│   └── complete_birs_database.sql
├── uploads/
├── .gitignore
├── Procfile
├── railway.json
├── railway.toml
├── login.php
├── dashboard.php
├── index.php
├── README.md
└── [other files]
```

### **Create .gitignore**

```
# Environment
.env
.env.local

# Uploads
uploads/*
!uploads/.gitkeep

# System
.DS_Store
Thumbs.db
*.log

# IDE
.vscode/
.idea/
*.sublime-project

# Database backups
*.sql.bak
database/backups/
```

---

## 🔐 Security Configuration

### **Update config/config.php for Security**

```php
<?php
// Security settings
error_reporting(E_ALL);

// In production, don't display errors
if (getenv('APP_ENV') === 'production') {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    ini_set('display_errors', 1);
}

// Set secure session cookie
ini_set('session.cookie_secure', 1);      // Only HTTPS
ini_set('session.cookie_httponly', 1);    // No JavaScript access
ini_set('session.cookie_samesite', 'Strict');

// HTTPS enforcement
if (getenv('APP_ENV') === 'production' && empty($_SERVER['HTTPS'])) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}
?>
```

---

## 📝 railway.toml Configuration

Create `railway.toml`:

```toml
[build]
provider = "nixpacks"

[build.nixpacks]
providers = ["php"]

[[services]]
name = "web"
builder = "nixpacks"
startCommand = "php -S 0.0.0.0:${PORT:-8080} -t ."

[[services.variables]]
name = "PORT"
value = "8080"

[[services.variables]]
name = "APP_ENV"
value = "production"
```

---

## 🚀 Deployment Commands

**First time deployment:**

```bash
# Navigate to project
cd path/to/barangay-information-system

# Initialize git
git init
git add .
git commit -m "Initial deployment to Railway"
git remote add origin https://github.com/YOUR_USERNAME/barangay-information-system.git
git branch -M main
git push -u origin main
```

**Update deployment:**

```bash
# Make changes locally
git add .
git commit -m "Update feature/fix"
git push origin main
```

Railway automatically deploys on every push to main branch!

---

## ✅ Testing After Deployment

1. **Check if site is accessible:**
   ```
   https://your-railway-url.railway.app
   ```

2. **Verify database connection:**
   - Visit `/diagnostic.php`
   - Check all statuses

3. **Test login:**
   - Default credentials (if you imported sample data):
     - Username: admin
     - Password: admin123

4. **Check logs in Railway:**
   - Dashboard → **Deployments** → **Logs**

---

## 🐛 Troubleshooting

### **"Cannot connect to database"**
- Check DB credentials in Railway Variables
- Verify database exists and is accessible
- Check firewall/security settings

### **"500 Internal Server Error"**
- Check Railway logs for error details
- Verify PHP extensions are installed
- Check file permissions

### **"Session not working"**
- Add `/tmp` directory for session storage
- Check session.save_path setting

### **"Cannot upload files"**
- Create `uploads` folder
- Set proper permissions: `chmod 755 uploads`
- Check disk space

---

## 🎯 Environment Variables

Required variables on Railway:

```
DB_HOST=mysql_host
DB_NAME=birs_db
DB_USER=db_user
DB_PASSWORD=db_password
APP_ENV=production
RAILWAY_URL=https://your-app.railway.app
```

Optional variables:

```
SESSION_TIMEOUT=3600
MAX_FILE_SIZE=5242880
RECORDS_PER_PAGE=10
```

---

## 📊 Monitoring & Logs

### **View Logs**
1. Railway Dashboard → **Deployments**
2. Click deployment → **Logs** tab
3. Search for errors

### **Set Up Error Notifications**
1. Railway Dashboard → **Settings**
2. Enable **Notifications**
3. Choose email or Slack

---

## 💾 Database Backups

### **Manual Backup**
```bash
# Export database
mysqldump -h your_host -u user -p database_name > backup.sql

# Import backup
mysql -h your_host -u user -p database_name < backup.sql
```

### **Scheduled Backups**
Use Railway's built-in backup feature or external services like:
- MySQL backup scripts
- AWS Backup
- Google Cloud Backup

---

## 🔄 Continuous Integration

### **Auto-deploy on GitHub Push**

Railway automatically deploys when you:
1. Push to main branch
2. Create a release
3. Update repository

No additional CI/CD setup needed!

---

## 📞 Support & Resources

- **Railway Docs**: https://docs.railway.app
- **PHP on Railway**: https://docs.railway.app/guides/php
- **MySQL on Railway**: https://docs.railway.app/databases/mysql
- **GitHub**: https://github.com/help

---

## ✨ Quick Reference

| Task | Command |
|------|---------|
| Push to GitHub | `git push origin main` |
| Check logs | Railway Dashboard → Logs |
| Update database | phpMyAdmin or MySQL CLI |
| View live site | `https://your-app.railway.app` |
| Redeploy | Push to GitHub |

---

## 🎉 You're Deployed!

Once everything is working:
1. ✅ Your site is live on Railway
2. ✅ Auto-deploys from GitHub
3. ✅ Database connected
4. ✅ Files uploaded properly

**Congratulations! Your Barangay Information System is now in production!** 🚀

---

**Last Updated**: December 3, 2025
**Version**: 1.0
