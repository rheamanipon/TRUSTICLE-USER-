<?php
/**
 * Log Operations Helper
 * 
 * This file should be included in all pages that handle operations
 * which need to be logged in the activity_logs table.
 */

// Include activity logger if not already included
require_once 'activity_logger.php';

/**
 * Log actions for user operations
 */

// For login.php - Log user login
function processLoginLogging($userId, $username) {
    logLogin($userId, $username);
}

// For register.php - Log user registration
function processRegistrationLogging($userId, $email) {
    logRegistration($userId, $email);
}

/**
 * Log actions for article operations
 */

// For article submission
function processArticleSubmission($userId, $title, $articleId) {
    logArticleSubmission($userId, $title, $articleId);
}

// For article update
function processArticleUpdate($userId, $title, $articleId) {
    logArticleUpdate($userId, $title, $articleId);
}

// For article status change (approve, reject, mark as fake/legit)
function processArticleStatusChange($userId, $title, $status, $articleId) {
    logArticleStatusChange($userId, $title, $status, $articleId);
}

// For article deletion
function processArticleDeletion($userId, $title, $articleId) {
    logArticleDeletion($userId, $title, $articleId);
}

/**
 * Log actions for comment operations
 */

// For comment submission
function processCommentSubmission($userId, $articleId, $commentId) {
    logCommentSubmission($userId, $articleId, $commentId);
}

// For comment deletion
function processCommentDeletion($userId, $articleId, $commentId) {
    logCommentDeletion($userId, $articleId, $commentId);
}

/**
 * Log actions for user account operations
 */

// For user profile update
function processUserUpdate($userId, $field) {
    logUserUpdate($userId, $field);
}

// For user soft deletion
function processUserSoftDelete($adminId, $userId, $email) {
    logUserSoftDelete($adminId, $userId, $email);
}

/**
 * Log actions for category operations
 */

// For category creation
function processCategoryCreation($userId, $categoryName, $categoryId) {
    logCategoryCreation($userId, $categoryName, $categoryId);
}

// For category update
function processCategoryUpdate($userId, $categoryName, $categoryId) {
    logCategoryUpdate($userId, $categoryName, $categoryId);
}

// For category deletion
function processCategoryDeletion($userId, $categoryName, $categoryId) {
    logCategoryDeletion($userId, $categoryName, $categoryId);
}

/**
 * Helper function to include in operations files
 * 
 * This ensures that all operations are properly logged
 */
function ensureLoggingIncluded() {
    // Just a placeholder function to include this file
    return true;
}
?> 