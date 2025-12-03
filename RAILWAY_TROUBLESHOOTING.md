# 🔧 Railway Deployment - Troubleshooting & Advanced Configuration

## 🆘 Common Issues & Solutions

### Issue 1: "Cannot connect to database"

**Symptoms:**
- Page shows "Database connection failed"
- Error: `PDOException: could not find driver`

**Solutions:**

1. **Verify Environment Variables**
   ```
   Railway Dashboard → Variables
   Check that all DB_ variables are set correctly
   ```

2. **Test Database Connection**
   ```php
   <?php
   $host = getenv('DB_HOST');
   $user = getenv('DB_USER');
   $pass = getenv('DB_PASSWORD');
   $db = getenv('DB_NAME');
   
   echo "DB_HOST: " . $host . "<br>";
   echo "DB_NAME: " . $db . "<br>";
   echo "DB_USER: " . $user . "<br>";
   ?>
   ```

3. **Verify Database Exists**
   ```sql
   SHOW DATABASES;
   -- Should show birs_db in the list
   ```

4. **Check User Permissions**
   ```sql
   SHOW GRANTS FOR 'your_user'@'your_host';
   ```

---

### Issue 2: "502 Bad Gateway Error"

**Symptoms:**
- Page shows 502 error
- App crashes after deployment

**Solutions:**

1. **Check Railway Logs**
   ```
   Railway Dashboard → Deployments → Logs
   Look for PHP error messages
   ```

2. **Verify Procfile**
   ```
   Content should be:
   web: php -S 0.0.0.0:${PORT:-8080}
   
   (No extra spaces or lines)
   ```

3. **Check PHP Syntax**
   ```powershell
   # Locally, verify PHP files have no syntax errors
   php -l config/config.php
   php -l config/database.php
   ```

4. **Restart Deployment**
   ```
   Railway Dashboard → Deployments → Click deployment → Restart
   ```

---

### Issue 3: "404 Not Found - File not found"

**Symptoms:**
- All pages show 404 error
- login.php not found
- CSS/JS files not loading

**Solutions:**

1. **Verify File Structure**
   ```
   Ensure all PHP files are in project root:
   - login.php
   - dashboard.php
   - config/
   - includes/
   - database/
   ```

2. **Check .gitignore**
   ```
   Ensure important files are NOT in .gitignore
   Verify git committed all files:
   git status
   ```

3. **Verify Git Push**
   ```powershell
   git log --oneline -5
   # Should show your commits
   
   git show HEAD --stat
   # Should show all files
   ```

4. **Redeploy**
   ```
   Railway Dashboard → Deployments → Deploy latest commit
   ```

---

### Issue 4: "Session not working / Login not persisting"

**Symptoms:**
- Can login but immediately logged out
- Session variables not persisting
- Redirected to login page constantly

**Solutions:**

1. **Check Session Directory**
   ```
   /tmp directory must exist and be writable
   ```

2. **Update config/config.php**
   ```php
   // Add this at the beginning of config.php:
   if (getenv('APP_ENV') === 'production') {
       ini_set('session.save_path', '/tmp');
       ini_set('session.cookie_secure', 1);
       ini_set('session.cookie_httponly', 1);
   }
   ```

3. **Test Session**
   ```php
   <?php
   session_start();
   $_SESSION['test'] = 'working';
   echo "Session path: " . session_save_path() . "<br>";
   echo "Session test: " . $_SESSION['test'];
   ?>
   ```

---

### Issue 5: "File uploads failing / Cannot upload files"

**Symptoms:**
- Upload button doesn't work
- Error: "Failed to move uploaded file"
- Upload directory permission errors

**Solutions:**

1. **Verify Upload Directories Exist**
   ```php
   <?php
   $dirs = [
       'uploads/',
       'uploads/profiles/',
       'uploads/signatures/',
       'uploads/logos/',
       'uploads/documents/'
   ];
   
   foreach ($dirs as $dir) {
       if (is_dir($dir)) {
           echo "✅ $dir exists<br>";
       } else {
           echo "❌ $dir missing<br>";
       }
   }
   ?>
   ```

2. **Create Missing Directories**
   ```php
   <?php
   $dirs = [
       'uploads/',
       'uploads/profiles/',
       'uploads/signatures/',
       'uploads/logos/',
       'uploads/documents/'
   ];
   
   foreach ($dirs as $dir) {
       if (!is_dir($dir)) {
           mkdir($dir, 0755, true);
       }
   }
   echo "Directories created!";
   ?>
   ```

3. **Check File Permissions**
   ```
   Use Railway's file manager or SSH
   Ensure uploads/ has 755 permissions
   ```

4. **Check File Size Limits**
   ```php
   // In config/config.php, ensure:
   define('MAX_FILE_SIZE', 5242880); // 5MB
   ```

---

### Issue 6: "PHP extensions missing"

**Symptoms:**
- Error: "Call to undefined function"
- PDO error for MySQL
- GD library not found (for images)

**Solutions:**

1. **Update railway.toml**
   ```toml
   [build.nixpacks]
   providers = ["php"]
   nixpacks = "{ pkgs = [\"php82\", \"php82Packages.composer\", \"php82Packages.pdo\", \"php82Packages.gd\"] }"
   ```

2. **Verify Extensions in config/config.php**
   ```php
   <?php
   echo "PHP Version: " . phpversion() . "<br>";
   echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? "✅" : "❌") . "<br>";
   echo "GD: " . (extension_loaded('gd') ? "✅" : "❌") . "<br>";
   ?>
   ```

---

## ⚙️ Advanced Configuration

### Custom Domain Setup

1. **Add Custom Domain in Railway**
   ```
   Railway Dashboard → Settings → Domains → Add Domain
   Add your custom domain (e.g., barangay.example.com)
   ```

2. **Update DNS Records**
   ```
   Point your domain's DNS to Railway's servers
   Follow Railway's instructions for your domain provider
   ```

3. **Update config/config.php**
   ```php
   if ($environment === 'production') {
       $domain = getenv('CUSTOM_DOMAIN') ?: 'your-app-name.railway.app';
       define('BASE_URL', 'https://' . $domain . '/');
   }
   ```

---

### Performance Optimization

#### 1. Enable Query Caching
```php
// In config/database.php
$options = [
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
];
```

#### 2. Optimize Database Queries
```sql
-- Add indexes for frequently searched columns
CREATE INDEX idx_residents_name ON residents(first_name, last_name);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_blotter_status ON blotter(status);
```

#### 3. Enable Output Compression
```php
// In includes/header.php
ob_start('ob_gzhandler');
```

#### 4. Minimize CSS/JS
```html
<!-- Use minified versions -->
<link rel="stylesheet" href="assets/css/style.min.css">
<script src="assets/js/app.min.js"></script>
```

---

### Security Hardening

#### 1. Set Security Headers
```php
// Add to config/config.php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
```

#### 2. Enable CSRF Protection
```php
// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verify in forms
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token validation failed');
}
```

#### 3. Rate Limiting
```php
// Simple rate limiting for login attempts
$ip = $_SERVER['REMOTE_ADDR'];
$attempts_key = 'login_attempts_' . $ip;

if (isset($_SESSION[$attempts_key]) && $_SESSION[$attempts_key] >= 5) {
    die('Too many login attempts. Try again later.');
}
```

#### 4. SQL Injection Prevention
```php
// Always use prepared statements
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

// Never use string concatenation
// Bad: "WHERE username = '$username'"
// Good: WHERE username = ? with execute([$username])
```

---

### Scaling & Resource Management

#### 1. Monitor Resource Usage
```
Railway Dashboard → Metrics
Check CPU, Memory, Network usage
```

#### 2. Upgrade Plan if Needed
```
If CPU > 80% or Memory > 85% consistently:
Railway Dashboard → Settings → Billing
Consider upgrading plan
```

#### 3. Database Optimization
```sql
-- Analyze tables for optimization
ANALYZE TABLE residents;
OPTIMIZE TABLE residents;

-- Check query performance
EXPLAIN SELECT * FROM residents WHERE status = 'active';
```

#### 4. Caching Strategy
```php
// Simple file-based caching
function cache_set($key, $value, $ttl = 3600) {
    file_put_contents("/tmp/cache_$key", json_encode($value));
}

function cache_get($key, $ttl = 3600) {
    $file = "/tmp/cache_$key";
    if (file_exists($file) && (time() - filemtime($file) < $ttl)) {
        return json_decode(file_get_contents($file), true);
    }
    return null;
}
```

---

### Monitoring & Alerts

#### 1. Error Logging
```php
// In config/config.php for production
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_errors.log');

// View logs
// Railway Dashboard → Deployments → Logs
```

#### 2. Set Up Notifications
```
Railway Dashboard → Settings → Notifications
Enable email alerts for:
- Failed deployments
- High CPU usage
- Memory warnings
```

#### 3. Create Health Check Endpoint
```php
<?php
// health.php - Check system health
header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => getenv('APP_ENV'),
    'php_version' => phpversion(),
    'database' => 'checking...'
];

try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->query("SELECT 1");
    $health['database'] = 'ok';
} catch (Exception $e) {
    $health['database'] = 'error';
    $health['status'] = 'error';
    http_response_code(500);
}

echo json_encode($health);
?>
```

---

## 📋 Deployment Validation Checklist

### Before Deployment
- [ ] All PHP files have been tested locally
- [ ] Database schema is correct
- [ ] `Procfile` exists and is correct
- [ ] `railway.toml` is configured
- [ ] `.gitignore` is set up
- [ ] No sensitive data in files
- [ ] All necessary environment variables identified

### After Deployment
- [ ] App is accessible at Railway URL
- [ ] Login page loads
- [ ] Can login with valid credentials
- [ ] Dashboard displays data
- [ ] Database connection verified
- [ ] Uploads directory exists
- [ ] File uploads work
- [ ] Reports generate
- [ ] No errors in logs
- [ ] Performance is acceptable

### Weekly Checks
- [ ] Database size is reasonable
- [ ] No PHP errors in logs
- [ ] Performance metrics normal
- [ ] Backups are current
- [ ] No security warnings

---

## 🚀 Rollback Plan

If deployment goes wrong:

### Immediate Rollback
```
1. Railway Dashboard → Deployments
2. Find your previous working deployment
3. Click deployment → Rollback
4. System reverts to previous version
```

### Manual Rollback via GitHub
```powershell
# Find the commit you want to revert to
git log --oneline

# Revert to previous commit
git revert <commit-hash>
git push origin main

# Railway auto-deploys the reverted version
```

---

## 📞 Support Resources

### Railway
- **Documentation**: https://docs.railway.app
- **API Reference**: https://api.railway.app
- **Discord Community**: https://railway.app/discord
- **Status Page**: https://status.railway.app

### PHP/Database
- **PHP Manual**: https://www.php.net/manual
- **MySQL Docs**: https://dev.mysql.com/doc
- **PDO Documentation**: https://www.php.net/manual/en/book.pdo.php

### Your System
- **Visit diagnostic page**: `/diagnostic.php`
- **Check logs**: Railway Dashboard → Logs
- **Review database**: phpMyAdmin on Railway MySQL

---

## 🎯 Success Metrics

✅ **Availability**: App is accessible 99.9% of the time  
✅ **Performance**: Page loads in < 2 seconds  
✅ **Database**: All queries execute in < 500ms  
✅ **Security**: No unresolved security warnings  
✅ **Reliability**: Automatic backups happening  
✅ **Monitoring**: Logs are being collected  

---

**Version**: 1.0  
**Last Updated**: December 3, 2025
