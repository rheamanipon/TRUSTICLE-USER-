<?php
/**
 * Authentication Utilities
 * 
 * Functions for handling user authentication, session management,
 * and access control.
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * 
 * @return bool Whether user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is an admin
 * 
 * @return bool Whether user is an admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirect to login page if not logged in
 */
function checkAuth() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Redirect to login page if not logged in as admin
 */
function checkAdminAuth() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
    
    if (!isAdmin()) {
        header('Location: /unauthorized.php');
        exit;
    }
}

/**
 * Log out the current user
 */
function logoutUser() {
    // Destroy the session
    session_destroy();
    
    // Unset all $_SESSION variables
    $_SESSION = array();
    
    // If using session cookies, delete the cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}
?> 