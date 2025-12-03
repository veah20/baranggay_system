# 🚀 Railway Deployment - Quick Reference (No Docker)

## 📋 Pre-Deployment Checklist

```powershell
# 1. Verify files are created
Test-Path "Procfile"
Test-Path "railway.toml"
Test-Path ".gitignore"
Test-Path "config/config.php"
Test-Path "config/database.php"
```

## 🔧 Setup Steps (One-Time)

### Step 1: Create GitHub Repository
```
1. Go to github.com/new
2. Repository name: barangay-information-system
3. Click "Create repository"
```

### Step 2: Initialize Local Git
```powershell
cd C:\xampp\htdocs\BarangayInformationResidentSystem

# Create .gitkeep files for upload directories
New-Item -Path "uploads/profiles/.gitkeep" -Force
New-Item -Path "uploads/signatures/.gitkeep" -Force
New-Item -Path "uploads/logos/.gitkeep" -Force
New-Item -Path "uploads/documents/.gitkeep" -Force
New-Item -Path "logs/.gitkeep" -Force

# Initialize git
git init
git add .
git commit -m "Initial commit: BIRS ready for Railway"

# Add remote (replace YOUR_USERNAME)
git remote add origin https://github.com/YOUR_USERNAME/barangay-information-system.git
git branch -M main
git push -u origin main
```

### Step 3: Create Railway Project
```
1. Go to railway.app
2. Click "Create Project"
3. Select "Deploy from GitHub"
4. Select your repository
5. Click "Deploy"
```

### Step 4: Configure Environment Variables on Railway
```
APP_ENV=production
DB_HOST=[your_db_host]
DB_PORT=3306
DB_NAME=birs_db
DB_USER=[your_db_user]
DB_PASSWORD=[your_db_password]
PORT=8080
RAILWAY_DOMAIN=[your-app-name].railway.app
```

### Step 5: Set Up Database
```sql
-- Create database
CREATE DATABASE birs_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import schema
-- Run: database/birs_database.sql via phpMyAdmin or MySQL CLI
```

## 📤 Push Updates to Railway

```powershell
# Make your changes locally
git add .
git commit -m "Your change description"
git push origin main

# Railway auto-deploys! No additional steps needed.
```

## 🔗 Environment Variables Reference

### Database Variables
| Variable | Value | Example |
|----------|-------|---------|
| DB_HOST | Database hostname | localhost or your_host.com |
| DB_PORT | Database port | 3306 |
| DB_NAME | Database name | birs_db |
| DB_USER | Database username | root |
| DB_PASSWORD | Database password | your_secure_password |

### Application Variables
| Variable | Value | Example |
|----------|-------|---------|
| APP_ENV | Environment | production |
| PORT | Server port | 8080 |
| RAILWAY_DOMAIN | Your Railway domain | your-app-name.railway.app |
| SESSION_TIMEOUT | Session timeout (seconds) | 3600 |

## 🧪 Testing Deployment

```powershell
# After deployment completes:

1. Visit: https://your-app-name.railway.app
2. You should see the login page
3. Login with admin credentials
4. Test core features:
   - Add new resident
   - Issue certificate
   - Generate report
```

## 📊 Monitoring

### View Logs
```
Railway Dashboard → Deployments → [Your Deployment] → Logs
```

### Check Metrics
```
Railway Dashboard → Metrics (CPU, Memory, Network)
```

## 🆘 Troubleshooting

### "Cannot connect to database"
- Verify DB_ variables are set correctly
- Check database exists: `SHOW DATABASES;`
- Verify user has access to database

### "404 - File not found"
- Verify all files committed to Git
- Check Procfile exists
- Restart deployment in Railway

### "502 Bad Gateway"
- Check Rails logs for PHP errors
- Verify Procfile format is correct
- Ensure PHP version compatibility

### "Uploads not working"
- Verify uploads/ directory exists
- Check permissions (should be 755)
- Verify write access to uploads/

## 🔐 Security

### Important Security Steps

1. **Change Default Admin Password**
   - Login with admin/admin123
   - Go to User Accounts
   - Change password to something strong

2. **Set Secure Environment Variables**
   - Use strong database password (20+ chars)
   - Never commit .env files
   - Rotate credentials periodically

3. **Enable HTTPS**
   - Railway provides free HTTPS
   - All traffic automatically redirected

## 📝 Files Created

### New Files Added to Project
- `Procfile` - Railway startup command
- `railway.toml` - Railway configuration
- `.gitignore` - Git ignore rules
- `RAILWAY_NO_DOCKER_GUIDE.md` - Detailed guide

### Updated Files
- `config/config.php` - Added production support
- `config/database.php` - Added environment variable support

## 🚀 Deployment Timeline

| Phase | Time | Status |
|-------|------|--------|
| GitHub setup | 5 min | Manual |
| Git initialization | 5 min | Manual |
| Code push | 2 min | Automated |
| Railway deployment | 3-5 min | Automated |
| Database setup | 5 min | Manual |
| Testing | 10 min | Manual |
| **Total** | **~30 min** | ✅ Live |

## 📞 Useful Links

- **Railway Docs**: https://docs.railway.app
- **PHP on Railway**: https://docs.railway.app/guides/php
- **GitHub Help**: https://github.com/help
- **Railway Status**: https://status.railway.app

## 💡 Pro Tips

1. **Enable GitHub integration** - Auto-deploy on push
2. **Set up monitoring** - Get alerts on errors
3. **Regular backups** - Export database weekly
4. **Check logs regularly** - Catch issues early
5. **Test locally first** - Before pushing to GitHub

## 🎯 Success Indicators

✅ App is live at your Railway domain  
✅ Login page loads successfully  
✅ Can login with admin credentials  
✅ Dashboard displays data  
✅ Database connection verified  
✅ File uploads working  
✅ Reports generating  

---

**Quick Start**: Create configs → Push to GitHub → Set variables → Deploy → Done! 🎉

**Need help?** Check the detailed guide: `RAILWAY_NO_DOCKER_GUIDE.md`
