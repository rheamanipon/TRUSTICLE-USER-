<?php
/**
 * Activity Logger
 * 
 * This file contains functions to log user activities
 * in the Trusticle system. It's used across the application
 * to record user actions.
 */

// Include database connection if not already included
require_once 'db_connect.php';

/**
 * Log user activity
 * 
 * @param int $user_id User ID
 * @param string $action Description of the action
 * @return bool Whether logging was successful
 */
function logActivity($user_id, $action) {
    global $conn;
    
    $user_id = mysqli_real_escape_string($conn, $user_id);
    $action = mysqli_real_escape_string($conn, $action);
    
    $query = "INSERT INTO activity_logs (user_id, action) VALUES ('$user_id', '$action')";
    return mysqli_query($conn, $query);
}

/**
 * Log user login
 * 
 * @param int $user_id User ID
 * @param string $username User's username
 * @return bool Whether logging was successful
 */
function logLogin($user_id, $username) {
    return logActivity($user_id, "User '$username' logged in");
}

/**
 * Log user registration
 * 
 * @param int $user_id User ID
 * @param string $email User's email
 * @return bool Whether logging was successful
 */
function logRegistration($user_id, $email) {
    return logActivity($user_id, "New user registered with email '$email'");
}

/**
 * Log article submission
 * 
 * @param int $user_id User ID
 * @param string $title Article title
 * @param int $article_id Article ID
 * @return bool Whether logging was successful
 */
function logArticleSubmission($user_id, $title, $article_id) {
    return logActivity($user_id, "Submitted article \"$title\" (ID: $article_id)");
}

/**
 * Log article update
 * 
 * @param int $user_id User ID
 * @param string $title Article title
 * @param int $article_id Article ID
 * @return bool Whether logging was successful
 */
function logArticleUpdate($user_id, $title, $article_id) {
    return logActivity($user_id, "Updated article \"$title\" (ID: $article_id)");
}

/**
 * Log article status change
 * 
 * @param int $user_id User ID
 * @param string $title Article title
 * @param string $status New status
 * @param int $article_id Article ID
 * @return bool Whether logging was successful
 */
function logArticleStatusChange($user_id, $title, $status, $article_id) {
    return logActivity($user_id, "Changed article \"$title\" (ID: $article_id) status to '$status'");
}

/**
 * Log article deletion
 * 
 * @param int $user_id User ID
 * @param string $title Article title
 * @param int $article_id Article ID
 * @return bool Whether logging was successful
 */
function logArticleDeletion($user_id, $title, $article_id) {
    return logActivity($user_id, "Deleted article \"$title\" (ID: $article_id)");
}

/**
 * Log comment submission
 * 
 * @param int $user_id User ID
 * @param int $article_id Article ID
 * @param int $comment_id Comment ID
 * @return bool Whether logging was successful
 */
function logCommentSubmission($user_id, $article_id, $comment_id) {
    return logActivity($user_id, "Added comment (ID: $comment_id) to article (ID: $article_id)");
}

/**
 * Log comment deletion
 * 
 * @param int $user_id User ID
 * @param int $article_id Article ID
 * @param int $comment_id Comment ID
 * @return bool Whether logging was successful
 */
function logCommentDeletion($user_id, $article_id, $comment_id) {
    return logActivity($user_id, "Deleted comment (ID: $comment_id) from article (ID: $article_id)");
}

/**
 * Log user account modification
 * 
 * @param int $user_id User ID
 * @param string $field Field that was modified
 * @return bool Whether logging was successful
 */
function logUserUpdate($user_id, $field) {
    return logActivity($user_id, "Updated user profile field: $field");
}

/**
 * Log user soft deletion
 * 
 * @param int $admin_id Admin user ID
 * @param int $user_id User ID being deleted
 * @param string $email User's email
 * @return bool Whether logging was successful
 */
function logUserSoftDelete($admin_id, $user_id, $email) {
    return logActivity($admin_id, "Soft deleted user '$email' (ID: $user_id)");
}

/**
 * Log category creation
 * 
 * @param int $user_id User ID
 * @param string $category_name Category name
 * @param int $category_id Category ID
 * @return bool Whether logging was successful
 */
function logCategoryCreation($user_id, $category_name, $category_id) {
    return logActivity($user_id, "Created new category '$category_name' (ID: $category_id)");
}

/**
 * Log category update
 * 
 * @param int $user_id User ID
 * @param string $category_name Category name
 * @param int $category_id Category ID
 * @return bool Whether logging was successful
 */
function logCategoryUpdate($user_id, $category_name, $category_id) {
    return logActivity($user_id, "Updated category '$category_name' (ID: $category_id)");
}

/**
 * Log category deletion
 * 
 * @param int $user_id User ID
 * @param string $category_name Category name
 * @param int $category_id Category ID
 * @return bool Whether logging was successful
 */
function logCategoryDeletion($user_id, $category_name, $category_id) {
    return logActivity($user_id, "Deleted category '$category_name' (ID: $category_id)");
}
?> 