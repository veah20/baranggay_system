<div class="sidebar" id="sidebar" role="navigation" aria-label="Main navigation">
    <div class="sidebar-brand">
        <i class="fas fa-building" aria-hidden="true"></i>
        <h2><?php echo SYSTEM_ACRONYM; ?></h2>
        <small>v<?php echo SYSTEM_VERSION; ?></small>
    </div>
    
    <nav class="sidebar-menu" aria-label="Sidebar menu">
        <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-home" aria-hidden="true"></i>
            <span>Dashboard</span>
        </a>
        
        <div class="menu-label">Records Management</div>
        
        <a href="residents.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'residents.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'residents.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-users" aria-hidden="true"></i>
            <span>Residents</span>
        </a>
        
        <a href="households.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'households.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'households.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-home" aria-hidden="true"></i>
            <span>Households</span>
        </a>
        
        <div class="menu-label">Documents</div>
        
        <a href="certificates.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'certificates.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'certificates.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-certificate" aria-hidden="true"></i>
            <span>Certificates</span>
        </a>
        
        <div class="menu-label">Incident Management</div>
        
        <a href="blotter.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'blotter.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'blotter.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-clipboard-list" aria-hidden="true"></i>
            <span>Blotter Records</span>
        </a>
        
        <div class="menu-label">Information</div>
        
        <a href="officials.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'officials.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'officials.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-user-tie" aria-hidden="true"></i>
            <span>Barangay Officials</span>
        </a>
        
        <a href="announcements.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-bullhorn" aria-hidden="true"></i>
            <span>Announcements</span>
        </a>
        
        <div class="menu-label">Economic Development</div>
        
        <a href="livelihood.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'livelihood.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'livelihood.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-briefcase" aria-hidden="true"></i>
            <span>Livelihood & Skills</span>
        </a>
        
        <a href="livelihood_training.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'livelihood_training.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'livelihood_training.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-chalkboard" aria-hidden="true"></i>
            <span>Training Sessions</span>
        </a>
        
        <a href="livelihood_jobs.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'livelihood_jobs.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'livelihood_jobs.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-briefcase" aria-hidden="true"></i>
            <span>Job Opportunities</span>
        </a>
        
        <a href="livelihood_reports.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'livelihood_reports.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'livelihood_reports.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-chart-line" aria-hidden="true"></i>
            <span>Livelihood Reports</span>
        </a>
        
        <div class="menu-label">Reports</div>
        
        <a href="reports.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-chart-bar" aria-hidden="true"></i>
            <span>Analytics & Reports</span>
        </a>
        
        <?php if (hasRole(['Admin'])): ?>
        <div class="menu-label">System</div>
        
        <a href="users.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-user-cog" aria-hidden="true"></i>
            <span>User Accounts</span>
        </a>
        
        <a href="settings.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-cog" aria-hidden="true"></i>
            <span>System Settings</span>
        </a>
        
        <a href="activity_logs.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'activity_logs.php' ? 'active' : ''; ?>" aria-current="<?php echo basename($_SERVER['PHP_SELF']) == 'activity_logs.php' ? 'page' : 'false'; ?>">
            <i class="fas fa-history" aria-hidden="true"></i>
            <span>Activity Logs</span>
        </a>
        <?php endif; ?>
    </nav>
</div>

<header class="main-header" role="banner">
    <button class="btn btn-link d-md-none" id="sidebarToggle" aria-label="Toggle navigation menu" aria-expanded="false">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>
    
    <div class="ms-auto user-dropdown">
        <div class="dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-label="User menu">
                <div class="user-avatar" title="<?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?>">
                    <?php echo strtoupper(substr($_SESSION['fullname'] ?? 'U', 0, 1)); ?>
                </div>
                <div class="ms-2 d-none d-md-block">
                    <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?></div>
                    <div style="font-size: 12px; color: #6c757d;"><?php echo htmlspecialchars($_SESSION['role'] ?? 'User'); ?></div>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2" aria-hidden="true"></i>My Profile</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2" aria-hidden="true"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2" aria-hidden="true"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</header>
