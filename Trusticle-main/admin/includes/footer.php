<!-- Custom JavaScript -->
<script src="../assets/js/sidebar.js" defer></script>
<?php
// Get the current file name
$current_file = basename($_SERVER['PHP_SELF']);

// Include specific JS files based on the current page
if ($current_file === 'activity_log.php') {
    echo '<script src="../assets/js/activity_log.js" defer></script>';
} elseif ($current_file === 'manage_article.php') {
    echo '<script src="../assets/js/article.js" defer></script>';
} elseif ($current_file === 'user_management.php') {
    echo '<script src="../assets/js/user_management.js" defer></script>';
} elseif ($current_file === 'settings.php') {
    echo '<script src="../assets/js/settings.js" defer></script>';
} elseif ($current_file === 'analytics.php') {
    echo '<script src="../assets/js/analytics.js" defer></script>';
} elseif ($current_file === 'dashboard.php') {
    echo '<script src="../assets/js/dashboard.js" defer></script>';
}

?>
</body>
</html>