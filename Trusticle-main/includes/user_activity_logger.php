<?php
/**
 * User Activity Logger
 * 
 * This file contains hooks and integration points to ensure user activities
 * are logged automatically throughout the application.
 */

// Include necessary files
require_once 'activity_logger.php';

/**
 * Functions to integrate logging into common user operations
 */

/**
 * Hook for user login process
 * Call this function after successful user authentication
 */
function logUserLogin($user) {
    if (isset($user['id']) && isset($user['username'])) {
        logLogin($user['id'], $user['username']);
    }
}

/**
 * Hook for user registration process
 * Call this function after successful user registration
 */
function logUserRegistration($userId, $email) {
    logRegistration($userId, $email);
}

/**
 * Hook for user profile update
 * Call this function after user updates any profile information
 */
function logProfileUpdate($userId, $updatedFields) {
    foreach ($updatedFields as $field) {
        logUserUpdate($userId, $field);
    }
}

/**
 * Hook for user password change
 * Call this function after user changes password
 */
function logPasswordChange($userId) {
    logUserUpdate($userId, 'password');
}

/**
 * Hook for user soft deletion
 * Call this function when an admin soft deletes a user
 */
function logUserDeactivation($adminId, $userId, $email) {
    logUserSoftDelete($adminId, $userId, $email);
}

/**
 * Hook for user login failures (for security purposes)
 * Call this after failed login attempts
 */
function logLoginFailure($username) {
    // Using 0 as user ID for system actions since the user isn't authenticated
    logActivity(0, "Failed login attempt for username: '$username'");
}

/**
 * Hook for user session timeout/expiration
 */
function logSessionExpiration($userId) {
    logActivity($userId, "User session expired/timed out");
}

/**
 * Hook for user logout
 */
function logUserLogout($userId, $username) {
    logActivity($userId, "User '$username' logged out");
}
?> 