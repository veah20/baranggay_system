# Barangay Information and Reporting System (BIRS)

A comprehensive web-based system for managing barangay records, generating certificates, and handling barangay operations efficiently.

## Features

### Core Modules
- **Resident Management** - Add, update, delete, and search resident information
- **Household Management** - Group residents under household heads
- **Certificate Issuance** - Generate printable certificates (Clearance, Indigency, Residency, Business Permit)
- **Blotter/Incident Reporting** - Record and monitor incidents within the barangay
- **Barangay Officials Management** - Manage current and past officials
- **Announcements** - Post public announcements and events
- **Reports & Analytics** - Generate population and certificate statistics
- **User Management** - Role-based access control (Admin, Secretary, Barangay Captain)
- **System Settings** - Configure barangay information and certificate fees
- **Activity Logs** - Track all system activities

### Key Features
- Responsive dashboard with real-time statistics
- Advanced search and filtering
- Printable certificates with official format
- Data visualization with charts
- Secure authentication with password hashing
- Activity logging for audit trail
- Role-based permissions

## System Requirements

- **Web Server**: Apache (XAMPP/WAMP/LAMP)
- **PHP**: Version 8.0 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Browser**: Modern web browser (Chrome, Firefox, Edge)

## Installation

### 1. Setup Database

1. Start XAMPP and run Apache and MySQL
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create a new database named `birs_db`
4. Import the SQL file:
   - Click on the `birs_db` database
   - Go to "Import" tab
   - Choose file: `database/birs_database.sql`
   - Click "Go" to import

### 2. Configure Database Connection

The database configuration is already set in `config/database.php`:
```php
private $host = "localhost";
private $db_name = "birs_db";
private $username = "root";
private $password = "";
```

If your MySQL has a different configuration, update these values accordingly.

### 3. Access the System

1. Open your web browser
2. Navigate to: `http://localhost/BarangayInformationResidentSystem/`
3. You will be redirected to the login page

### 4. Default Login Credentials

**Username:** admin  
**Password:** admin123

**Important:** Change the default password after first login!

## Directory Structure

```
BarangayInformationResidentSystem/
├── config/
│   ├── config.php          # System configuration
│   └── database.php        # Database connection
├── database/
│   └── birs_database.sql   # Database schema
├── includes/
│   ├── header.php          # Page header
│   ├── sidebar.php         # Navigation sidebar
│   └── footer.php          # Page footer
├── uploads/                # File uploads directory
│   ├── profiles/
│   ├── signatures/
│   ├── logos/
│   └── documents/
├── login.php               # Login page
├── logout.php              # Logout handler
├── dashboard.php           # Main dashboard
├── residents.php           # Resident management
├── households.php          # Household management
├── certificates.php        # Certificate issuance
├── print_certificate.php   # Certificate printing
├── blotter.php             # Blotter records
├── officials.php           # Officials management
├── announcements.php       # Announcements
├── reports.php             # Reports & analytics
├── users.php               # User management
├── settings.php            # System settings
├── activity_logs.php       # Activity logs
└── README.md               # This file
```

## User Roles & Permissions

### Admin
- Full access to all modules
- User management
- System settings
- Activity logs
- All CRUD operations

### Barangay Captain
- View all records
- Issue certificates
- Manage blotter cases
- View reports

### Secretary
- Manage residents and households
- Issue certificates
- File blotter cases
- Post announcements

## Usage Guide

### Managing Residents

1. Go to **Residents** from the sidebar
2. Click **Add New Resident**
3. Fill in the resident information
4. Click **Save Resident**
5. Use the search and filter features to find residents

### Issuing Certificates

1. Go to **Certificates** from the sidebar
2. Click **Issue Certificate**
3. Select the resident
4. Choose certificate type
5. Enter purpose and payment details
6. Click **Issue Certificate**
7. Click **Print** to generate the certificate

### Filing Blotter Cases

1. Go to **Blotter Records**
2. Click **File New Case**
3. Enter complainant and respondent information
4. Provide incident details
5. Click **File Case**
6. Update case status as needed

### Generating Reports

1. Go to **Reports & Analytics**
2. Select the year to view
3. View population statistics
4. Check certificate and blotter statistics
5. Click **Print Report** to print

## Security Features

- Password hashing using bcrypt
- Session management with timeout
- Role-based access control
- SQL injection prevention using prepared statements
- XSS protection with input sanitization
- Activity logging for audit trail

## Troubleshooting

### Cannot connect to database
- Ensure MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Verify database name is `birs_db`

### Login not working
- Clear browser cache and cookies
- Check if database was imported correctly
- Verify default admin account exists

### Charts not displaying
- Ensure JavaScript is enabled in browser
- Check browser console for errors
- Verify Chart.js CDN is accessible

### Upload directory errors
- Check folder permissions (should be writable)
- Ensure `uploads/` directory exists
- Verify PHP upload settings in php.ini

## Customization

### Change Barangay Information
1. Login as Admin
2. Go to **System Settings**
3. Update barangay name, municipality, province
4. Set certificate fees
5. Click **Save Settings**

### Add New User
1. Login as Admin
2. Go to **User Accounts**
3. Click **Add User**
4. Enter user details and assign role
5. Click **Create User**

## Support & Maintenance

### Database Backup
Regularly backup your database:
1. Open phpMyAdmin
2. Select `birs_db`
3. Click "Export"
4. Choose "Quick" export method
5. Click "Go" to download

### System Updates
- Keep PHP and MySQL updated
- Regularly backup database
- Monitor activity logs for suspicious activities
- Change passwords periodically

## Technical Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP 8+
- **Database**: MySQL/MariaDB
- **Libraries**: 
  - Bootstrap 5.3.0
  - Font Awesome 6.4.0
  - jQuery 3.7.0
  - DataTables 1.13.4
  - Chart.js 4.3.0

## License

This system is developed for barangay use. All rights reserved.

## Version

**Version:** 1.0.0  
**Release Date:** October 2025

## Credits

Developed for efficient barangay management and operations.

---

For technical support or questions, contact your system administrator.
