# 🚂 Railway MySQL Setup Guide (Option A - Easiest)

Complete guide to set up and deploy with Railway's managed MySQL service.

---

## 📋 Why Railway MySQL?

✅ **Auto-managed** - No server configuration needed  
✅ **Auto-backups** - Automatic daily backups  
✅ **Zero maintenance** - Railway handles updates  
✅ **Secure** - SSL connections built-in  
✅ **Scalable** - Grows with your needs  
✅ **Simple** - One-click setup  

---

## 🎯 Complete Step-by-Step Deployment

### **Phase 1: Prepare Your GitHub Repository**

#### Step 1.1: Initialize Git & Push to GitHub

```powershell
# Navigate to project directory
cd C:\xampp\htdocs\BarangayInformationResidentSystem

# Initialize git (if not already done)
git init

# Add all files
git add .

# Commit
git commit -m "Initial commit: BIRS with Railway deployment ready"

# Add GitHub remote (replace YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/barangay-information-system.git

# Set main branch
git branch -M main

# Push to GitHub
git push -u origin main
```

**Expected Output:**
```
Enumerating objects: 150, done.
Counting objects: 100%
...
Branch 'main' set up to track 'origin/main'.
```

✅ **Status:** Code is now on GitHub

---

### **Phase 2: Create Railway Project with MySQL**

#### Step 2.1: Create Railway Project

1. **Go to railway.app**
   - If you don't have account, click "Sign Up"
   - Sign up with GitHub (easiest)

2. **Click "Create Project"**
   - Select: **"Deploy from GitHub"**
   - Search and select: `barangay-information-system`
   - Click **"Deploy"**

✅ **Status:** Railway project created with app service

#### Step 2.2: Add MySQL Database Service

1. **In Railway Dashboard:**
   - Look for **"+ New"** button (top right)
   - Click it and select **"Database"** → **"MySQL"**

2. **Wait for MySQL to Initialize**
   - Railway automatically creates MySQL instance
   - You'll see progress: "Provisioning MySQL..."
   - This takes 30-60 seconds

3. **MySQL Service is Ready**
   - You'll see in dashboard: `mysql-prod` service
   - Status should be "Running" (green checkmark)

✅ **Status:** MySQL database is live and running

---

### **Phase 3: Get Database Credentials**

#### Step 3.1: Retrieve MySQL Connection Details

1. **Click on the MySQL service** (in dashboard)
2. **Go to "Variables" tab**
3. **You'll see automatic variables:**
   ```
   MYSQLHOST
   MYSQLPASSWORD
   MYSQLPORT
   MYSQLUSER
   MYSQL_URL (full connection string)
   MYSQLDATABASE
   ```

4. **Copy these values** (you'll need them)

**Example:**
```
MYSQLHOST: prod-mysql-server-1.railway.app
MYSQLUSER: root
MYSQLPASSWORD: abcd1234efgh5678
MYSQLDATABASE: railway
MYSQLPORT: 3306
```

✅ **Status:** You have database credentials

---

### **Phase 4: Link App to MySQL & Set Environment Variables**

#### Step 4.1: Connect App Service to MySQL

1. **In Railway Dashboard, click on your Web App** (not MySQL)
2. **Go to "Variables" tab**
3. **Add these environment variables:**

| Variable | Value | Example |
|----------|-------|---------|
| `DB_HOST` | MYSQLHOST value | `prod-mysql-server-1.railway.app` |
| `DB_USER` | MYSQLUSER value | `root` |
| `DB_PASSWORD` | MYSQLPASSWORD value | `abcd1234efgh5678` |
| `DB_NAME` | MYSQLDATABASE value | `railway` |
| `APP_ENV` | `production` | `production` |
| `PORT` | `8080` | `8080` |

**How to add variables:**
1. Click **"+ Add Variable"**
2. Enter name (e.g., `DB_HOST`)
3. Enter value from MySQL service
4. Click checkmark ✅
5. Repeat for all 6 variables

✅ **Status:** App is configured to use Railway MySQL

---

### **Phase 5: Import Your Database Schema**

#### Step 5.1: Connect to Railway MySQL

You have two options:

**Option A: Using Railway Web Interface (Easiest)**

1. Click MySQL service in dashboard
2. Click **"Data Browser"** tab
3. Click **"Import SQL"**
4. Upload: `database/birs_database.sql`
5. Click **"Import"**

**Option B: Using MySQL Client (Command Line)**

```powershell
# Install MySQL client if needed
# Download from: https://dev.mysql.com/downloads/mysql/

# Connect to Railway MySQL
mysql -h prod-mysql-server-1.railway.app -P 3306 -u root -p

# When prompted, enter password

# Then in MySQL prompt:
CREATE DATABASE IF NOT EXISTS railway;
USE railway;
SOURCE C:\xampp\htdocs\BarangayInformationResidentSystem\database\birs_database.sql;
EXIT;
```

✅ **Status:** Database schema is imported into Railway MySQL

---

### **Phase 6: Verify Database Import**

#### Step 6.1: Check Tables Were Created

1. In Railway Dashboard, click MySQL service
2. Click **"Data Browser"**
3. You should see tables:
   - ✅ `users`
   - ✅ `residents`
   - ✅ `households`
   - ✅ `certificates`
   - ✅ `blotter`
   - ✅ `announcements`
   - ✅ `livelihood_jobs`
   - ✅ `activity_logs`
   - (and others)

4. Click `users` table → see default admin account

✅ **Status:** All database tables are in place

---

### **Phase 7: Deploy Your Application**

#### Step 7.1: Trigger Deployment

1. **In Railway Dashboard, go to "Deployments" tab**
2. You should see a pending deployment (from GitHub push)
3. Click **"Deploy"** button to start

**What happens:**
```
Git Push Detected
    ⬇️
Railpack Detects PHP (from composer.json)
    ⬇️
Installs PHP 8.2
    ⬇️
Runs: bash start.sh
    ⬇️
Creates Upload Directories
    ⬇️
Starts PHP Server on Port 8080
    ⬇️
App is LIVE ✅
```

#### Step 7.2: Monitor Deployment

1. Click the deployment in progress
2. Watch **"Build Logs"** tab
   - Should see: "PHP 8.2 installed ✅"
   - Should see: "Build completed successfully ✅"

3. Watch **"Deploy Logs"** tab
   - Should see: "bash start.sh executed ✅"
   - Should see: "PHP Server started on port 8080 ✅"

**Expected Time:** 3-5 minutes

✅ **Status:** Application is deployed and running

---

### **Phase 8: Get Your Live URL & Test**

#### Step 8.1: Find Your Application URL

1. In Railway Dashboard, click Web App service
2. Look for **"View Deployment"** link
3. Or find the domain in **"Domains"** tab
4. Copy your URL (looks like): `https://barangay-information-system-production.railway.app`

#### Step 8.2: Test Your Deployment

1. **Open URL in browser**
   ```
   https://your-railway-url.railway.app
   ```

2. **You should see the login page**
   - If you see an error, check logs first

3. **Login with default credentials:**
   ```
   Username: admin
   Password: admin123
   ```

4. **Dashboard should load with no database errors** ✅

#### Step 8.3: Comprehensive Testing

✅ **Test all features:**
- [ ] Login/Logout works
- [ ] Dashboard loads
- [ ] Add new resident
- [ ] Issue certificate
- [ ] Generate report
- [ ] Upload profile picture
- [ ] Check activity logs

✅ **Status:** Application is live and working!

---

## 🔧 Complete Configuration Reference

### Your Current Database Config (Already Supports Railway)

**File:** `config/database.php`

```php
// Already configured to read from environment variables:
$this->host = getenv('DB_HOST') ?: "localhost";
$this->db_name = getenv('DB_NAME') ?: "birs_db";
$this->username = getenv('DB_USER') ?: "root";
$this->password = getenv('DB_PASSWORD') ?: "";
```

✅ **Perfect** - No changes needed!

### Required Files Already in Place

| File | Status | Purpose |
|------|--------|---------|
| `composer.json` | ✅ Exists | Tells Railway: "This is PHP" |
| `railway.json` | ✅ Exists | Railway config |
| `Procfile` | ✅ Exists | Startup command |
| `start.sh` | ✅ Exists | PHP server launcher |
| `config/database.php` | ✅ Exists | Uses env variables |
| `config/config.php` | ✅ Exists | App configuration |

---

## 📊 Architecture Diagram

```
┌─────────────────────────────────────────────────────┐
│         Your Browser / Client                       │
└────────────────────┬────────────────────────────────┘
                     │ HTTPS Request
                     ⬇️
┌─────────────────────────────────────────────────────┐
│  Railway Web App Service                            │
│  ├── PHP 8.2 Runtime                               │
│  ├── Your Application Files                         │
│  ├── Port: 8080                                    │
│  └── Domain: your-app.railway.app                  │
└─────────────────┬──────────────────────────────────┘
                  │ TCP Connection
                  ⬇️
┌─────────────────────────────────────────────────────┐
│  Railway MySQL Database Service                     │
│  ├── Host: prod-mysql-server-1.railway.app         │
│  ├── Port: 3306                                    │
│  ├── Database: railway                             │
│  ├── SSL Encrypted Connection                      │
│  └── Automated Backups                             │
└─────────────────────────────────────────────────────┘
```

---

## 🚨 Troubleshooting

### Issue: "Cannot connect to database"

**Check:**
1. MySQL service is running (green in dashboard)
2. Environment variables are correct in Web App
3. Database exists: `railway`
4. Correct credentials copied

**Fix:**
```powershell
# Verify locally first
mysql -h localhost -u root -p birs_db

# Then try with Railway credentials
mysql -h prod-mysql-server-1.railway.app -u root -p
```

---

### Issue: "500 Internal Server Error"

**Check:**
1. Deployment logs in Railway dashboard
2. PHP errors: Look in "Logs" tab
3. File permissions: Check `uploads/` directory

**View logs:**
```
Railway Dashboard 
  ⬇️ Click Web App 
  ⬇️ Deployments tab 
  ⬇️ Click latest deployment 
  ⬇️ View "Deploy Logs"
```

---

### Issue: "Application not starting"

**Check:**
1. `Procfile` exists and readable
2. `start.sh` exists and executable
3. `composer.json` exists
4. No syntax errors in PHP files

**Common cause:** Missing `composer.json`
**Fix:** Ensure `composer.json` is committed to GitHub

---

### Issue: "Database tables not found"

**Check:**
1. Database import completed successfully
2. Correct database selected (`railway`)
3. Tables visible in Data Browser

**Fix:**
```powershell
# Re-import database
mysql -h prod-mysql-server-1.railway.app -u root -p railway < database/birs_database.sql
```

---

## 📈 After Deployment Checklist

- [ ] Application is accessible via Railway URL
- [ ] Login works with admin/admin123
- [ ] Dashboard loads without errors
- [ ] Database connection verified
- [ ] Can add new resident
- [ ] Can upload files
- [ ] Activity logs are recorded
- [ ] All features working

---

## 🔐 Security Best Practices

### 1. Change Default Admin Password (Immediately!)

After first login:
1. Go to **Settings** → **User Accounts**
2. Click **Edit** on admin user
3. Change password from `admin123` to strong password
4. Save changes

### 2. Enable HTTPS

Railway provides automatic HTTPS:
- ✅ SSL certificate auto-generated
- ✅ HTTPS enforced automatically
- ✅ No configuration needed

### 3. Rotate MySQL Password (Optional)

To change database password:
1. In Railway MySQL dashboard
2. Click **Settings**
3. Change root password
4. Update `DB_PASSWORD` variable in Web App
5. Redeploy

### 4. Database Backups

Railway MySQL auto-backups:
- ✅ Daily backups created
- ✅ 30-day retention
- ✅ One-click restore available

---

## 💰 Pricing Information

### Railway Free Tier
- **$5 credit per month**
- MySQL database included
- 512MB RAM for app
- Plenty for small/medium use

### Typical Monthly Cost
```
Web App (100 hours): ~$3.00
MySQL Database:      ~$10.00
─────────────────────────────
Total:               ~$13.00 (with $5 free credit)
```

---

## 🎯 Summary: What You Just Did

1. ✅ Created Railway project
2. ✅ Added MySQL database service
3. ✅ Connected app to database
4. ✅ Imported database schema
5. ✅ Deployed PHP application
6. ✅ Application is live and accessible

**Your system is now:**
- 🌍 **Globally accessible** via Railway URL
- 🔒 **Secure** with HTTPS
- 💾 **Backed up** automatically
- 📈 **Scalable** on demand
- ✅ **Production ready**

---

## 📚 Next Steps

### Immediate
1. Test all functionality on live site
2. Share URL with stakeholders
3. Gather feedback

### Short-term (Week 1)
1. Monitor application performance
2. Check logs for errors
3. Train staff on system
4. Document access procedures

### Medium-term (Month 1)
1. Set up domain name (optional)
2. Configure email notifications
3. Schedule regular backups
4. Plan feature enhancements

---

## 🔗 Useful Links

| Resource | URL |
|----------|-----|
| Railway Dashboard | https://railway.app/dashboard |
| Railway Docs | https://docs.railway.app |
| PHP on Railway | https://docs.railway.app/guides/php |
| MySQL on Railway | https://docs.railway.app/databases/mysql |
| Support | support@railway.app |

---

## 📞 Quick Support

### Before contacting support:
1. Check Railway logs
2. Verify environment variables
3. Confirm database connection
4. Review this guide's troubleshooting

### If issues persist:
- Railway Support: https://railway.app/support
- GitHub Issues: Report in your repo
- Community: Railway Discord

---

## ✨ Congratulations! 🎉

Your **Barangay Information and Reporting System** is now deployed on Railway with managed MySQL!

**Your system is:**
- ✅ Live 24/7
- ✅ Auto-backed up
- ✅ Globally accessible
- ✅ Secure with HTTPS
- ✅ Production ready

**Next:** Share your live URL with stakeholders!

---

**Version:** 1.0  
**Last Updated:** December 3, 2025  
**Status:** ✅ PRODUCTION READY

---

## 🚀 Quick Reference Card

```
DEPLOYMENT SUMMARY
═══════════════════════════════════════════════════════

1. GITHUB
   Repository: barangay-information-system
   Branch: main
   Status: ✅ Pushed

2. RAILWAY WEB APP
   Language: PHP 8.2
   Port: 8080
   Domain: your-app.railway.app
   Status: ✅ Deployed

3. RAILWAY MySQL
   Host: prod-mysql-server-1.railway.app
   Port: 3306
   Database: railway
   Backups: ✅ Automatic
   Status: ✅ Running

4. APPLICATION
   URL: https://your-app.railway.app
   Login: admin / admin123
   Status: ✅ Live

5. ACCESS LEVEL
   📊 Dashboard: ✅ Working
   👥 Residents: ✅ Working
   📋 Reports: ✅ Working
   🏠 Households: ✅ Working
   ⚙️ Settings: ✅ Working

═══════════════════════════════════════════════════════
```

