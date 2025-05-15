<?php
// Require the profile image utility
require_once __DIR__ . '/../../utils/profile_image.php';

// Make sure the session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
$user_first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : '';
$user_last_name = isset($_SESSION['last_name']) ? $_SESSION['last_name'] : '';
$user_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$user_profile_photo = isset($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'default.jpg';

// Generate profile image
$profile_image = get_profile_image($user_profile_photo, $user_first_name, $user_last_name, 45);

// Get the current file name to highlight active page
$current_file = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tru/ticle - Articles</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
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
            <a href="dashboard.php" class="menu-item <?php echo ($current_file === 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="article_main.php" class="menu-item <?php echo ($current_file === 'article_main.php') ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span>Article</span>
            </a>
            <a href="analytics.php" class="menu-item <?php echo ($current_file === 'analytics.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Analytics</span>
            </a>
            <div class="menu-item settings-menu <?php echo ($current_file === 'settings.php') ? 'active' : ''; ?>" id="settingsMenu">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
                <i class="fas fa-chevron-down settings-arrow"></i>
            </div>
            <!-- Settings dropdown menu items -->
            <div class="submenu" id="settingsSubmenu">
                <a href="settings.php#edit-profile" class="submenu-item">
                    <i class="fas fa-user-edit"></i>
                    <span>Edit Profile</span>
                </a>
                <a href="settings.php#account-security" class="submenu-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Account Security</span>
                </a>
            </div>
            <a href="about.php" class="menu-item <?php echo ($current_file === 'about.php') ? 'active' : ''; ?>">
                <i class="fas fa-info-circle"></i>
                <span>About Us</span>
            </a>
        </div>
        
        <div class="user-profile">
            <img src="<?php echo $profile_image; ?>" alt="User" class="profile-image">
            <div class="user-info">
                <small><?php echo htmlspecialchars($user_first_name . ' ' . $user_last_name); ?></small>
                <small class="user-subtitle"><?php echo htmlspecialchars($user_email); ?></small>
            </div>
            <div class="user-menu">
                <i class="fas fa-ellipsis-v"></i>
            </div>
            
            <!-- Dropdown menu inside user-profile for better positioning -->
            <div class="user-dropdown">
                <a href="settings.php#edit-profile" class="dropdown-item">Edit Profile</a>
                <a href="../../auth/logout.php" class="dropdown-item logout-option">Logout</a>
            </div>
        </div>
    </div>
    <!-- Custom JavaScript -->
    <script src="../../assets/js/sidebar.js" defer></script>
</body>
</html>