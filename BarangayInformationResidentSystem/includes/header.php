<?php
require_once __DIR__ . '/../config/config.php';
checkSession();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo SYSTEM_NAME; ?> - Barangay Information and Resident System">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SYSTEM_ACRONYM; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        /* Skip to main content link for keyboard navigation */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: #1e3c72;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            z-index: 100;
            font-weight: 600;
        }
        
        .skip-link:focus {
            top: 0;
        }
        
        /* Focus visible for better keyboard navigation */
        *:focus-visible {
            outline: 3px solid #1e3c72;
            outline-offset: 2px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            font-size: 16px;
            line-height: 1.5;
            color: #333;
        }
        
        /* Main Header - Now Secondary */
        .main-header {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            height: var(--header-height);
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 20px;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            overflow-y: auto;
            z-index: 1001;
            transition: all 0.3s;
        }
        
        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand h2 {
            margin: 10px 0 0 0;
            font-weight: 600;
            font-size: 18px;
        }
        
        .sidebar-brand small {
            display: block;
        }
        
        .sidebar-brand i {
            font-size: 40px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu .menu-item {
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .sidebar-menu .menu-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-menu .menu-item.active {
            background: rgba(255,255,255,0.2);
            color: white;
            border-left: 4px solid white;
        }
        
        .sidebar-menu .menu-item i {
            width: 30px;
            font-size: 18px;
        }
        
        .sidebar-menu .menu-label {
            font-size: 11px;
            color: rgba(255,255,255,0.5);
            padding: 15px 20px 5px 20px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 30px;
            min-height: calc(100vh - var(--header-height));
        }
        
        /* Show sidebar on desktop */
        @media (min-width: 769px) {
            .sidebar {
                left: 0 !important;
            }
            
            .main-header,
            .main-content {
                margin-left: var(--sidebar-width);
            }
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        /* Stats Cards */
        .stats-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 50px;
            opacity: 0.3;
        }
        
        .stats-card h3 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
        }
        
        .stats-card p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        
        /* Buttons */
        .btn {
            border-radius: 6px;
            padding: 8px 16px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        /* Tables */
        .table {
            background: white;
        }
        
        /* Badges */
        .badge {
            padding: 5px 10px;
            font-weight: 500;
        }
        
        /* User dropdown */
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }
        
        /* Improve button focus for accessibility */
        .btn:focus-visible,
        .nav-link:focus-visible,
        .dropdown-toggle:focus-visible,
        .menu-item:focus-visible {
            outline: 2px solid #1e3c72;
            outline-offset: 2px;
        }
        
        /* Ensure sufficient color contrast for text-muted */
        .text-muted {
            color: #555 !important;
        }
        
        /* Improve heading hierarchy */
        h1, h2, h3, h4, h5, h6 {
            color: #333;
        }
        
        /* Better button contrast and states */
        .btn {
            font-weight: 500;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        /* Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                left: -var(--sidebar-width);
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-header,
            .main-content {
                margin-left: 0;
                left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Skip to main content link for keyboard navigation -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
