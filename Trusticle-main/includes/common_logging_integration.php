<?php
/**
 * Common Logging Integration
 * 
 * This file provides instructions and examples for integrating activity logging 
 * into common operations throughout the application.
 * 
 * HOW TO USE:
 * 1. Include the appropriate logger file(s) in your PHP operation file
 * 2. Call the relevant logging function after the operation is successful
 * 
 * IMPORTANT: This is a documentation/example file. You should NOT include this file
 * in your actual code. Instead, include the specific logger files you need.
 */

// EXAMPLE: How to integrate logging into login.php
/*
// At the top of your login.php file:
include_once 'includes/user_activity_logger.php';

// After successful login:
$user = [
    'id' => $row['id'],
    'username' => $row['username']
];
logUserLogin($user);
*/

// EXAMPLE: How to integrate logging into registration.php
/*
// At the top of your registration.php file:
include_once 'includes/user_activity_logger.php';

// After successful registration and getting the inserted ID:
$userId = mysqli_insert_id($conn);
logUserRegistration($userId, $email);
*/

// EXAMPLE: How to integrate logging into article_submission.php
/*
// At the top of your article_submission.php file:
include_once 'includes/content_activity_logger.php';

// After successful article submission and getting the inserted ID:
$articleId = mysqli_insert_id($conn);
logNewArticle($_SESSION['user_id'], $title, $articleId);
*/

// EXAMPLE: How to integrate logging into article_approval.php
/*
// At the top of your article_approval.php file:
include_once 'includes/content_activity_logger.php';

// After admin approves/rejects an article:
logArticleReview($_SESSION['user_id'], $title, $status, $articleId);
*/

// EXAMPLE: How to integrate logging into comment_submission.php
/*
// At the top of your comment_submission.php file:
include_once 'includes/content_activity_logger.php';

// After successful comment submission and getting the inserted ID:
$commentId = mysqli_insert_id($conn);
logNewComment($_SESSION['user_id'], $articleId, $commentId);
*/

// EXAMPLE: How to integrate logging into user_deletion.php
/*
// At the top of your user_deletion.php file:
include_once 'includes/user_activity_logger.php';

// After admin soft deletes a user:
logUserDeactivation($_SESSION['user_id'], $userId, $userEmail);
*/

/**
 * Where to integrate activity logging:
 * 
 * 1. User Authentication
 *    - Login success (logUserLogin)
 *    - Login failure (logLoginFailure)
 *    - Registration (logUserRegistration)
 *    - Logout (logUserLogout)
 * 
 * 2. User Management
 *    - Profile updates (logProfileUpdate)
 *    - Password changes (logPasswordChange)
 *    - Account deactivation/soft deletion (logUserDeactivation)
 * 
 * 3. Article Management
 *    - Article submission (logNewArticle)
 *    - Article updates (logArticleEdited)
 *    - Article approval/rejection (logArticleReview)
 *    - Article deletion (logArticleRemoval)
 * 
 * 4. Comment Management
 *    - Comment submission (logNewComment)
 *    - Comment deletion (logCommentRemoval)
 * 
 * 5. Category Management
 *    - Category creation (logNewCategory)
 *    - Category updates (logCategoryChange)
 *    - Category deletion (logCategoryRemoval)
 */

// This file is for documentation only - do not include it elsewhere
?> 