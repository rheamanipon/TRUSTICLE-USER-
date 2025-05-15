<?php
// Require the profile image utility
require_once __DIR__ . '/../../utils/profile_image.php';

// Make sure the session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

// Get user info from database if not in session but user_id is set
if (isset($_SESSION['user_id']) && (!isset($_SESSION['first_name']) || !isset($_SESSION['last_name']) || !isset($_SESSION['email']))) {
    require_once __DIR__ . '/../../config/connection.php';
    $stmt = $conn->prepare("SELECT first_name, last_name, email, profile_photo FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $_SESSION['first_name'] = $row['first_name'];
        $_SESSION['last_name'] = $row['last_name'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['profile_photo'] = $row['profile_photo'];
    }
    $stmt->close();
}

// Get user info from session
$user_first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Admin';
$user_last_name = isset($_SESSION['last_name']) ? $_SESSION['last_name'] : 'User';
$user_email = isset($_SESSION['email']) ? $_SESSION['email'] : 'admin@example.com';
$user_profile_photo = isset($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.jpg';

// Generate profile image
$profile_image = get_profile_image($user_profile_photo, $user_first_name, $user_last_name, 45);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trusticle - Admin</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add Inter font for consistency with settings page -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Admin styles -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    
    <?php
    // Get the current file name
    $current_file = basename($_SERVER['PHP_SELF']);
    
    // Include specific CSS files based on the current page
    if ($current_file === 'activity_log.php') {
        echo '<link rel="stylesheet" href="../assets/css/activity_log.css">';
    } elseif ($current_file === 'manage_article.php') {
        echo '<link rel="stylesheet" href="../assets/css/article.css">';
    } elseif ($current_file === 'user_management.php') {
        echo '<link rel="stylesheet" href="../assets/css/user_management.css">';
    } elseif ($current_file === 'settings.php') {
        echo '<link rel="stylesheet" href="../assets/css/settings.css">';
    } elseif ($current_file === 'analytics.php') {
        echo '<link rel="stylesheet" href="../assets/css/analytics.css">';
    } elseif ($current_file === 'dashboard.php') {
        echo '<link rel="stylesheet" href="../assets/css/dashboard.css">';
    }
    ?>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo-container">
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
                <a href="../view/settings.php#edit-profile" class="submenu-item">
                    <i class="fas fa-user-edit"></i>
                    <span>Edit Profile</span>
                </a>
                <a href="../view/settings.php#account-security" class="submenu-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Account Security</span>
                </a>
            </div>
        </div>
        
        <div class="user-profile">
            <img src="<?php echo $profile_image; ?>" alt="User" class="profile-image" id="header-profile-image">
            <div class="user-info">
                <small><?php echo htmlspecialchars($user_first_name . ' ' . $user_last_name); ?></small>
                <small class="user-subtitle"><?php echo htmlspecialchars($user_email); ?></small>
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
    <!-- Custom JavaScript -->
    <script src="../assets/js/sidebar.js" defer></script>
</body>
</html>