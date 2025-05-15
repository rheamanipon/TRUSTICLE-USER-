<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tru/ticle - Articles</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Additional CSS files based on the page -->
    <?php
    // Get the current file name
    $current_file = basename($_SERVER['PHP_SELF']);
    
    // Include specific CSS files based on the current page
    if ($current_file === 'activity_log.php') {
        echo '<link rel="stylesheet" href="../assets/css/activity_log.css">';
    } elseif ($current_file === 'manage_article.php') {
        echo '<link rel="stylesheet" href="../assets/css/article.css">';
    } elseif ($current_file === 'settings.php') {
        echo '<link rel="stylesheet" href="../assets/css/settings.css">';
    } elseif ($current_file === 'dashboard.php') {
        echo '<link rel="stylesheet" href="../assets/css/dashboard.css">';
    } elseif ($current_file === 'analytics.php') {
        echo '<link rel="stylesheet" href="../assets/css/analytics.css">';
    } elseif ($current_file === 'user_management.php') {
        echo '<link rel="stylesheet" href="../assets/css/user_management.css">';
    }
    ?>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Chart.js for dashboard charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo-container" id="toggleSidebar">
            <div class="logo">
                <img src="../assets/images/logo.png" alt="Trusticle Logo" class="sidebar-logo">
            </div>
        </div>
        
        <div class="sidebar-menu">
            <a href="../view/dashboard.php" class="menu-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="../view/user_management.php" class="menu-item">
                <i class="fas fa-user"></i>
                <span>User Management</span>
            </a>
            <a href="../view/manage_article.php" class="menu-item">
                <i class="fas fa-file-alt"></i>
                <span>Manage Articles</span>
            </a>
            <a href="../view/analytics.php" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span>Analytics</span>
            </a>
            <a href="../view/activity_log.php" class="menu-item">
                <i class="fas fa-history"></i>
                <span>Activity Log</span>
            </a>
            <div class="menu-item settings-menu" id="settingsMenu">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
                <i class="fas fa-chevron-down settings-arrow"></i>
            </div>
            <!-- Settings dropdown menu items -->
            <div class="submenu" id="settingsSubmenu">
                <a href="../view/settings.php#edit-profile" class="submenu-item" id="edit-profile-link">
                    <i class="fas fa-user-edit"></i>
                    <span>Edit Profile</span>
                </a>
                <a href="../view/settings.php#account-security" class="submenu-item" id="account-security-link">
                    <i class="fas fa-shield-alt"></i>
                    <span>Account Security</span>
                </a>
            </div>
        </div>
        
        <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=Rhea+Manipon&background=0D8ABC&color=fff" alt="User" class="profile-image">
            <div class="user-info">
                <small>Rhea Manipon</small>
                <small class="user-subtitle">trusmapn@mail.com</small>
            </div>
            <div class="user-menu">
                <i class="fas fa-ellipsis-v"></i>
            </div>
            
            <!-- Dropdown menu inside user-profile for better positioning -->
            <div class="user-dropdown">
                <a href="../view/settings.php#edit-profile" class="dropdown-item">Edit Profile</a>
                <a href="../../auth/logout.php" class="dropdown-item logout-option">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>