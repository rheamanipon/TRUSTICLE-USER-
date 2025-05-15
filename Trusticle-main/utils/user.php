<?php
/**
 * User utility functions
 * DRY principle implementation - centralizes user-related functionality
 */

/**
 * Log user activity
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @param string $action Action description
 * @return bool True on success, false on failure
 */
function logUserActivity($conn, $userId, $action) {
    $activityStmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
    $activityStmt->bind_param("is", $userId, $action);
    $result = $activityStmt->execute();
    $activityStmt->close();
    return $result;
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current user ID
 * 
 * @return int|null User ID if logged in, null otherwise
 */
function getCurrentUserId() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

/**
 * Check if user has specific role
 * 
 * @param string $role Role to check
 * @return bool True if user has role, false otherwise
 */
function userHasRole($role) {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Get user profile data
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @return array|null User data array or null if not found
 */
function getUserProfile($conn, $userId) {
    $stmt = $conn->prepare("SELECT id, username, email, first_name, last_name, birthdate, profile_image, bio, created_at 
                           FROM users 
                           WHERE id = ? AND is_active = TRUE AND is_deleted = FALSE");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $userData = $result->fetch_assoc();
        $stmt->close();
        return $userData;
    }
    
    $stmt->close();
    return null;
}

/**
 * Update user profile
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @param array $data Profile data to update
 * @return bool True on success, false on failure
 */
function updateUserProfile($conn, $userId, $data) {
    // Build update query dynamically based on provided data
    $allowedFields = ['first_name', 'last_name', 'email', 'birthdate', 'bio', 'profile_image'];
    $updates = [];
    $types = "i"; // First parameter is always user ID (integer)
    $values = [$userId];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "{$field} = ?";
            $values[] = $data[$field];
            $types .= "s"; // All these fields are treated as strings
        }
    }
    
    if (empty($updates)) {
        return false; // Nothing to update
    }
    
    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

/**
 * Update user password
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @param string $currentPassword Current password
 * @param string $newPassword New password
 * @return array Result with success status and message
 */
function updateUserPassword($conn, $userId, $currentPassword, $newPassword) {
    $result = ['success' => false, 'message' => ''];
    
    // Get current password hash
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $passwordResult = $stmt->get_result();
    $stmt->close();
    
    if ($passwordResult->num_rows !== 1) {
        $result['message'] = "User not found";
        return $result;
    }
    
    $userData = $passwordResult->fetch_assoc();
    
    // Verify current password
    if (!password_verify($currentPassword, $userData['password'])) {
        $result['message'] = "Current password is incorrect";
        return $result;
    }
    
    // Update to new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateStmt->bind_param("si", $hashedPassword, $userId);
    $updateResult = $updateStmt->execute();
    $updateStmt->close();
    
    if ($updateResult) {
        $result['success'] = true;
        $result['message'] = "Password updated successfully";
        // Log the password change
        logUserActivity($conn, $userId, "Password changed");
    } else {
        $result['message'] = "Failed to update password";
    }
    
    return $result;
}
?> 