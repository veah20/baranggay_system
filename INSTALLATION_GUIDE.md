# BIRS Installation Guide

## Quick Start Guide

Follow these steps to install and run the Barangay Information and Reporting System.

### Step 1: Install XAMPP

1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP to `C:\xampp`
3. Run XAMPP Control Panel
4. Start **Apache** and **MySQL** modules

### Step 2: Setup the System Files

The system files are already in the correct location:
```
C:\xampp\htdocs\BarangayInformationResidentSystem\
```

### Step 3: Create the Database

1. Open your web browser
2. Go to: `http://localhost/phpmyadmin`
3. Click on **"New"** in the left sidebar
4. Database name: `birs_db`
5. Click **"Create"**

### Step 4: Import Database Schema

1. Click on the `birs_db` database you just created
2. Click on the **"Import"** tab at the top
3. Click **"Choose File"**
4. Navigate to: `C:\xampp\htdocs\BarangayInformationResidentSystem\database\birs_database.sql`
5. Click **"Go"** at the bottom
6. Wait for the success message

### Step 5: Access the System

1. Open your web browser
2. Go to: `http://localhost/BarangayInformationResidentSystem/`
3. You will be redirected to the login page

### Step 6: Login

Use these default credentials:
- **Username:** `admin`
- **Password:** `admin123`

### Step 7: Change Default Password (Recommended)

1. After logging in, go to **User Accounts**
2. Click **Edit** on the admin user
3. Enter a new secure password
4. Click **Update User**

## Verification Checklist

✅ XAMPP installed and running  
✅ Apache started (green in XAMPP)  
✅ MySQL started (green in XAMPP)  
✅ Database `birs_db` created  
✅ Database tables imported successfully  
✅ Can access login page  
✅ Can login with admin credentials  

## Common Issues & Solutions

### Issue: "Connection error" message
**Solution:** 
- Make sure MySQL is running in XAMPP
- Check if database name is exactly `birs_db`
- Verify database was imported successfully

### Issue: Blank page or errors
**Solution:**
- Check if all files are in the correct directory
- Ensure PHP version is 8.0 or higher
- Check XAMPP error logs in `C:\xampp\apache\logs\error.log`

### Issue: Cannot login
**Solution:**
- Verify database was imported (check if `users` table exists)
- Try username: `admin` password: `admin123` (case-sensitive)
- Clear browser cache and try again

### Issue: Charts not showing
**Solution:**
- Check internet connection (CDN libraries needed)
- Enable JavaScript in browser
- Try a different browser

## File Permissions

The system will automatically create these directories:
- `uploads/profiles/`
- `uploads/signatures/`
- `uploads/logos/`
- `uploads/documents/`

If you encounter upload errors, ensure these folders exist and are writable.

## Next Steps

After successful installation:

1. **Update System Settings**
   - Go to Settings
   - Update barangay name, municipality, province
   - Set certificate fees

2. **Add Users**
   - Go to User Accounts
   - Create accounts for staff members
   - Assign appropriate roles

3. **Add Residents**
   - Go to Residents
   - Start adding resident records

4. **Configure Officials**
   - Go to Barangay Officials
   - Update current officials information

## System Requirements Met

✅ Windows OS  
✅ XAMPP (Apache + MySQL + PHP)  
✅ Modern web browser  
✅ At least 100MB free disk space  

## Support

If you encounter any issues:
1. Check the troubleshooting section in README.md
2. Verify all installation steps were followed
3. Check XAMPP error logs
4. Ensure all required services are running

---

**Installation Complete!** You can now start using the Barangay Information and Reporting System.

---

# Deployment and Hosting Strategy - This Week (Dec 2-6, 2025)

## I. Deployment Timeline

### Monday, December 2 - Pre-Deployment Verification
- [ ] System integrity check (all tables, CRUD operations)
- [ ] Security audit (passwords, SQL injection, XSS protection)
- [ ] Performance testing (load testing, response times)
- [ ] Documentation preparation (user manual, admin guide)

### Tuesday, December 3 - Environment Preparation
- [ ] XAMPP configuration verification
- [ ] Database backup creation
- [ ] Application optimization
- [ ] Test data preparation (50-100 sample residents)

### Wednesday, December 4 - Staging & Testing
- [ ] Full system testing (all modules)
- [ ] User acceptance testing (all roles)
- [ ] Browser compatibility (Chrome, Firefox, Edge, Safari)
- [ ] Mobile responsiveness validation

### Thursday, December 5 - Final Preparation
- [ ] Demo scenario preparation
- [ ] Staff training session
- [ ] System health check
- [ ] Backup and recovery test

### Friday, December 6 - Live Demonstration
- [ ] Pre-demo setup (9:00 AM)
- [ ] Stakeholder demonstration (10:00 AM)
- [ ] Q&A and feedback (12:00 PM)
- [ ] Post-demo activities (2:00 PM)

---

## II. Current Hosting Setup (Local XAMPP)

### Access Information
- **URL:** `http://localhost/BarangayInformationResidentSystem/`
- **Server:** Apache (Port 80)
- **Database:** MySQL (Port 3306)
- **Database Name:** `birs_db`
- **PHP Version:** 8.0+

### System Architecture
```
Web Browser → Apache Server → PHP Application → MySQL Database
   (Client)      (Port 80)      (Business Logic)   (Data Storage)
```

---

## III. Pre-Demonstration Checklist

### Database
- [ ] All 16 tables created
- [ ] Foreign key relationships verified
- [ ] Sample data populated (50+ residents)
- [ ] Default admin account active
- [ ] Database backup created

### Application
- [ ] All PHP files in correct locations
- [ ] Configuration files updated
- [ ] File permissions set correctly (755 directories, 644 files)
- [ ] Upload directories writable
- [ ] Error logging enabled

### Security
- [ ] Default password changed (admin123 → strong password)
- [ ] SQL injection prevention verified
- [ ] XSS protection enabled
- [ ] Session timeout configured (30 minutes)
- [ ] Activity logging functional

### Testing
- [ ] Login functionality tested
- [ ] All CRUD operations tested
- [ ] Reports generation tested
- [ ] Certificate printing tested
- [ ] Role-based access verified

---

## IV. Deployment Steps

### Step 1: Start XAMPP Services
```
1. Open XAMPP Control Panel
2. Click "Start" for Apache (should show green)
3. Click "Start" for MySQL (should show green)
4. Verify both services are running
```

### Step 2: Verify Database Connection
```
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Login with root credentials
3. Verify birs_db database exists
4. Check all 16 tables are present
5. Verify data integrity
```

### Step 3: Access Application
```
1. Open web browser
2. Navigate to: http://localhost/BarangayInformationResidentSystem/
3. You should see login page
4. Login with: admin / [new password]
5. Verify dashboard loads
```

### Step 4: Run System Tests
```
1. Test user login/logout
2. Add new resident
3. Issue certificate
4. File blotter case
5. Generate report
6. Check activity logs
```

---

## V. Demo Scenario Script

### Introduction (2 minutes)
- Welcome stakeholders
- Brief overview of system purpose
- Explain key features

### Live Demonstration (15 minutes)

**1. Login & Dashboard (2 min)**
- Show login page
- Login with admin account
- Display dashboard with statistics

**2. Resident Management (3 min)**
- Add new resident
- Show resident list
- Edit resident information
- Demonstrate search functionality

**3. Certificate Issuance (3 min)**
- Navigate to Certificates
- Issue sample certificate
- Show certificate preview
- Demonstrate print functionality

**4. Blotter Management (2 min)**
- File new blotter case
- Show case list
- Update case status

**5. Reports (2 min)**
- Generate population report
- Show certificate statistics
- Display charts and graphs

**6. Livelihood Features (3 min)**
- Show skills registry
- Demonstrate training programs
- Show job opportunities
- Display job applications

### Q&A Session (10 minutes)
- Address questions
- Gather feedback
- Discuss next steps

---

## VI. Hardware Requirements

### Minimum
- Processor: Intel i5 or equivalent
- RAM: 4GB
- Storage: 500MB free space
- Network: Ethernet or WiFi

### Recommended
- Processor: Intel i7 or equivalent
- RAM: 8GB+
- Storage: 1GB free space
- Network: Gigabit Ethernet

---

## VII. Backup & Recovery

### Daily Backup
```bash
1. Export database to SQL file
   Location: C:\xampp\backups\
   Filename: birs_db_YYYY-MM-DD.sql

2. Backup application files
   Location: C:\xampp\backups\
   Filename: birs_app_YYYY-MM-DD.zip

3. Store offsite
   - USB drive
   - Cloud storage (Google Drive, OneDrive)
   - External hard drive
```

### Recovery Procedure
```
1. Stop Apache and MySQL
2. Restore database from backup
3. Restore application files
4. Restart services
5. Verify system functionality
```

---

## VIII. Troubleshooting

### Cannot Connect to Database
- Verify MySQL is running in XAMPP
- Check database credentials in config/database.php
- Verify database name is "birs_db"
- Restart MySQL service

### Page Not Found (404 Error)
- Verify project folder in C:\xampp\htdocs\
- Check .htaccess file exists
- Verify Apache mod_rewrite is enabled
- Restart Apache service

### Login Not Working
- Clear browser cache and cookies
- Verify admin account exists in database
- Check session directory is writable
- Check error logs for details

### Slow Performance
- Check database indexes
- Optimize MySQL queries
- Clear browser cache
- Monitor system resources (CPU, RAM)

---

## IX. Post-Demonstration Tasks

### Immediate (Day 1)
- [ ] Verify all systems operational
- [ ] Conduct final testing
- [ ] Document any issues
- [ ] Create backup
- [ ] Brief staff on usage

### Short-term (Week 1)
- [ ] Monitor system performance
- [ ] Gather user feedback
- [ ] Fix any reported issues
- [ ] Create additional user accounts
- [ ] Schedule training sessions

### Medium-term (Month 1)
- [ ] Analyze usage patterns
- [ ] Optimize performance
- [ ] Plan feature enhancements
- [ ] Establish backup schedule
- [ ] Document lessons learned

---

## X. Success Criteria

### Deployment Success
- ✅ System accessible to all authorized users
- ✅ All features functioning correctly
- ✅ Database integrity verified
- ✅ Performance meets requirements (< 2s load time)
- ✅ Security measures implemented
- ✅ Documentation complete
- ✅ Staff trained and ready
- ✅ Backup procedures established

### Demonstration Success
- ✅ All stakeholders can access system
- ✅ Live data entry demonstration successful
- ✅ Reports generate correctly
- ✅ No critical errors during demo
- ✅ Positive stakeholder feedback
- ✅ Questions answered satisfactorily
- ✅ Next steps clearly communicated

---

## XI. Contact Information

### Technical Support
- **System Administrator:** [Name]
- **Email:** [Email]
- **Phone:** [Phone]
- **Available:** Monday-Friday, 8:00 AM - 5:00 PM

### Emergency Support
- **On-call:** [Name]
- **Phone:** [Emergency Number]
- **Available:** 24/7 for critical issues

---

## XII. Future Hosting Options

### Option 1: Network Deployment (Post-Demo)
- Dedicated server machine
- Static IP address
- All staff access from office computers
- Access URL: `http://192.168.1.100/BarangayInformationResidentSystem/`

### Option 2: Cloud Hosting (Phase 2)

**Recommended Providers:**
1. **Heroku** - Free tier, easy setup, auto-scaling
2. **AWS** - Highly scalable, reliable, complex setup
3. **DigitalOcean** - Affordable ($5-20/month), simple setup
4. **Bluehost/SiteGround** - Shared hosting, easy setup

**Benefits of Cloud:**
- Accessible from anywhere
- Automatic backups
- High availability
- Professional support
- Scalable resources

---

**Deployment Status:** Ready for This Week  
**Last Updated:** December 2, 2025  
**Approved By:** [System Administrator]
