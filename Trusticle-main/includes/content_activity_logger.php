<?php
/**
 * Content Activity Logger
 * 
 * This file contains hooks and integration points to ensure content-related
 * activities are logged automatically throughout the application.
 */

// Include necessary files
require_once 'activity_logger.php';

/**
 * Functions to integrate logging into article operations
 */

/**
 * Hook for article submission
 * Call this function after an article is successfully submitted
 */
function logNewArticle($userId, $title, $articleId) {
    logArticleSubmission($userId, $title, $articleId);
}

/**
 * Hook for article update
 * Call this function after an article is successfully updated
 */
function logArticleEdited($userId, $title, $articleId) {
    logArticleUpdate($userId, $title, $articleId);
}

/**
 * Hook for article approval process
 * Call this function when an admin approves/rejects an article
 */
function logArticleReview($adminId, $title, $status, $articleId) {
    logArticleStatusChange($adminId, $title, $status, $articleId);
}

/**
 * Hook for article deletion
 * Call this function when an article is deleted
 */
function logArticleRemoval($userId, $title, $articleId) {
    logArticleDeletion($userId, $title, $articleId);
}

/**
 * Hook for article visibility change
 */
function logArticleVisibilityChange($userId, $title, $visibility, $articleId) {
    $visibilityText = $visibility ? 'visible' : 'hidden';
    logActivity($userId, "Changed article \"$title\" (ID: $articleId) visibility to $visibilityText");
}

/**
 * Functions to integrate logging into comment operations
 */

/**
 * Hook for comment submission
 * Call this function after a comment is successfully posted
 */
function logNewComment($userId, $articleId, $commentId) {
    logCommentSubmission($userId, $articleId, $commentId);
}

/**
 * Hook for comment deletion
 * Call this function when a comment is deleted
 */
function logCommentRemoval($userId, $articleId, $commentId) {
    logCommentDeletion($userId, $articleId, $commentId);
}

/**
 * Functions to integrate logging into category operations
 */

/**
 * Hook for category creation
 * Call this function after a category is created
 */
function logNewCategory($userId, $categoryName, $categoryId) {
    logCategoryCreation($userId, $categoryName, $categoryId);
}

/**
 * Hook for category update
 * Call this function after a category is updated
 */
function logCategoryChange($userId, $categoryName, $categoryId) {
    logCategoryUpdate($userId, $categoryName, $categoryId);
}

/**
 * Hook for category deletion
 * Call this function when a category is deleted
 */
function logCategoryRemoval($userId, $categoryName, $categoryId) {
    logCategoryDeletion($userId, $categoryName, $categoryId);
}

/**
 * Hook for when a user views an article (optional, high volume)
 */
function logArticleView($userId, $articleId, $title) {
    // This could generate a lot of logs, so it might be used selectively
    logActivity($userId, "Viewed article \"$title\" (ID: $articleId)");
}
?> 