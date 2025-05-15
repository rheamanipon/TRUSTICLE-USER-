<?php
// Include database connection
require_once '../config/connection.php';
require_once '../utils/FakeNewsDetector.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define common date format patterns to include in search queries
$date_format_patterns = "
    OR DATE_FORMAT(a.date_published, '%Y-%m-%d') LIKE ? 
    OR DATE_FORMAT(a.date_published, '%b %d, %Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%M %d, %Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%d/%m/%Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%m/%d/%Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%d-%m-%Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%b %Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%M %Y') LIKE ?
";

// Helper function to add date parameters to an array of params
function addDateParameters(&$params, $search) {
    $search_param = "%$search%";
    $date_search = "$search%"; // For year patterns
    
    // Add date format parameters
    $params[] = $date_search;  // date Y-m-d LIKE ?
    $params[] = $search_param; // date b d, Y LIKE ?
    $params[] = $search_param; // date M d, Y LIKE ?
    $params[] = $search_param; // date d/m/Y LIKE ?
    $params[] = $search_param; // date m/d/Y LIKE ?
    $params[] = $search_param; // date d-m-Y LIKE ?
    $params[] = $date_search;  // date Y LIKE ?
    $params[] = $search_param; // date b Y LIKE ?
    $params[] = $search_param; // date M Y LIKE ?
}

// Make sure we return JSON for all responses, even errors
header('Content-Type: application/json');

// Error handling to ensure valid JSON is always returned
function handleFatalErrors() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo json_encode([
            'success' => false,
            'message' => 'Fatal server error: ' . $error['message'],
            'error_details' => $error
        ]);
        exit;
    }
}
register_shutdown_function('handleFatalErrors');

// Set error handling to catch warnings and notices
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error: [$errno] $errstr in $errfile on line $errline");
    return false; // Let PHP's internal error handler run as well
});

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

// Get the requested action
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Debug info
error_log("Article process request: action=$action, user=$user_id");
error_log("POST data: " . print_r($_POST, true));

try {
    switch ($action) {
        case 'create':
            createArticle($conn, $user_id);
            break;
        
        case 'read':
            getArticles($conn, $user_id);
            break;
            
        case 'read_paginated':
            getPaginatedArticles($conn, $user_id);
            break;
            
        case 'read_community':
            getCommunityArticles($conn, $user_id);
            break;
            
        case 'get_single':
            getSingleArticle($conn);
            break;
            
        case 'get_article_details':
            getArticleDetails($conn);
            break;
            
        case 'update':
            updateArticle($conn, $user_id);
            break;
            
        case 'delete':
            deleteArticle($conn, $user_id);
            break;
            
        case 'get_admin_article':
            getAdminArticleDetails($conn);
            break;
            
        case 'update_article_status':
            updateArticleStatus($conn, $user_id);
            break;
            
        case 'get_admin_articles':
            getAdminArticles($conn);
            break;
            
        case 'export_articles':
            exportArticles($conn);
            break;
            
        case 'export_categories':
            exportCategories($conn);
            break;
            
        case 'export_keywords':
            exportKeywords($conn);
            break;
            
        case 'get_categories':
            getCategories($conn);
            break;
            
        case 'add_category':
            addCategory($conn);
            break;
            
        case 'update_category':
            updateCategory($conn);
            break;
            
        case 'delete_category':
            deleteCategory($conn);
            break;
            
        case 'get_keywords':
            getKeywords($conn);
            break;
            
        case 'add_keyword':
            addKeyword($conn);
            break;
            
        case 'update_keyword':
            updateKeyword($conn);
            break;
            
        case 'delete_keyword':
            deleteKeyword($conn);
            break;
            
        default:
            $response['message'] = 'Invalid action';
            echo json_encode($response);
            break;
    }
} catch (Exception $e) {
    // Capture any exceptions and return as JSON error
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
    exit;
}

function createArticle($conn, $user_id) {
    global $response;
    
    // Validate input
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $category_id = isset($_POST['category']) ? (int)$_POST['category'] : 0;
    $source_url = isset($_POST['source_url']) ? trim($_POST['source_url']) : '';
    $date_published = isset($_POST['date']) ? date('Y-m-d', strtotime($_POST['date'])) : date('Y-m-d');
    
    if (empty($title) || empty($content) || $category_id <= 0) {
        $response['message'] = 'Required fields are missing';
        echo json_encode($response);
        return;
    }
    
    // Create excerpt from content (first 150 chars)
    $excerpt = substr($content, 0, 150) . (strlen($content) > 150 ? '...' : '');
    
    // Analyze content for fake news indicators
    $detector = new FakeNewsDetector($conn);
    $analysis = $detector->analyzeArticle($content);
    
    // Always set initial status to 'pending'
    $status = 'pending';
    $detection_score = $analysis['score'];
    
    // Insert into database with detection results
    $stmt = $conn->prepare("INSERT INTO articles (user_id, title, content, excerpt, source_url, date_published, category_id, status, detection_score) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("isssssisd", $user_id, $title, $content, $excerpt, $source_url, $date_published, $category_id, $status, $detection_score);
    
    if ($stmt->execute()) {
        $article_id = $stmt->insert_id;
        $response['success'] = true;
        $response['message'] = 'Article created successfully';
        $response['article_id'] = $article_id;
        
        // Include analysis results in the response
        $response['analysis'] = [
            'score' => $detection_score,
            'prediction' => $analysis['prediction'],
            'match_count' => $analysis['match_count']
        ];
    } else {
        $response['message'] = 'Error creating article: ' . $stmt->error;
    }
    
    $stmt->close();
    echo json_encode($response);
}

function getArticles($conn, $user_id) {
    global $response, $date_format_patterns;
    
    // Get filter parameters if any
    $status = isset($_POST['status']) && $_POST['status'] != 'all' ? $_POST['status'] : null;
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    
    try {
        // Build data query - only show articles created after most recent account reactivation
        $dataQuery = "SELECT a.*, c.name as category_name, u.username,
                CASE
                    WHEN a.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN CONCAT(CEILING(TIMESTAMPDIFF(MINUTE, a.created_at, NOW())), ' min ago')
                    WHEN a.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN CONCAT(CEILING(TIMESTAMPDIFF(HOUR, a.created_at, NOW())), ' hours ago')
                    ELSE DATE_FORMAT(a.created_at, '%b %d, %Y')
                END as time_ago
                FROM articles a 
                JOIN categories c ON a.category_id = c.id 
                JOIN users u ON a.user_id = u.id
                WHERE a.user_id = ? 
                AND a.is_visible = 1
                AND (a.created_at > (
                    SELECT COALESCE(MAX(al.timestamp), '1900-01-01') 
                    FROM activity_logs al 
                    WHERE al.user_id = ? AND al.action = 'User account reactivated'
                ))";
        
        // Add filter conditions
        if ($status !== null) {
            $dataQuery .= " AND a.status = ?";
        }
        
        if (!empty($search)) {
            // Enhanced search across all relevant fields
            $dataQuery .= " AND (
                a.title LIKE ? 
                OR a.content LIKE ? 
                OR c.name LIKE ?
                $date_format_patterns
            )";
        }
        
        $dataQuery .= " ORDER BY a.created_at DESC";
        
        // Prepare and execute the query
        $dataStmt = $conn->prepare($dataQuery);
        if (!$dataStmt) {
            $response['success'] = false;
            $response['message'] = 'Error preparing query: ' . $conn->error;
            echo json_encode($response);
            return;
        }
        
        // Bind parameters using dynamic approach
        try {
            // Create arrays to hold all parameters for bind_param
            $types = '';  // String containing parameter types
            $params = []; // Array to hold parameter values
            
            // First parameter is always user_id (integer), now we need it twice
            $types .= 'ii';
            $params[] = $user_id;
            $params[] = $user_id; // For the subquery
            
            // Add status parameter if used
            if ($status !== null) {
                $types .= 's';
                $params[] = $status;
            }
            
            // Add search parameters if used
            if (!empty($search)) {
                // Add base search parameters - 3 string parameters for title, content, category
                $types .= 'sss';
                $search_param = "%$search%";
                
                // Add all search parameters
                $params[] = $search_param; // title LIKE ?
                $params[] = $search_param; // content LIKE ?
                $params[] = $search_param; // category LIKE ?
                
                // Add 9 date format parameters
                $types .= str_repeat('s', 9);
                addDateParameters($params, $search);
            }
            
            // Log for debugging
            error_log("Parameter types in getArticles: $types");
            error_log("Parameter count in getArticles: " . count($params));
            
            // Execute bind_param with constructed parameter list
            $dataStmt->bind_param($types, ...$params);
            
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = 'Error binding parameters: ' . $e->getMessage();
            error_log("Parameter binding error in getArticles: " . $e->getMessage());
            echo json_encode($response);
            return;
        }
        
        // Execute query
        if (!$dataStmt->execute()) {
            $response['success'] = false;
            $response['message'] = 'Error executing query: ' . $dataStmt->error;
            echo json_encode($response);
            $dataStmt->close();
            return;
        }
        
        $result = $dataStmt->get_result();
        $articles = [];
        
        while ($row = $result->fetch_assoc()) {
            // Add flag to indicate ownership
            $row['is_owner'] = true;
            $articles[] = $row;
        }
        
        $response['success'] = true;
        $response['articles'] = $articles;
        $dataStmt->close();
        
        echo json_encode($response);
    } catch (Exception $e) {
        // Log the error
        error_log("Exception in getArticles: " . $e->getMessage());
        
        // Return a proper JSON response
        echo json_encode([
            'success' => false,
            'message' => 'Error processing articles: ' . $e->getMessage()
        ]);
    }
}

function getSingleArticle($conn) {
    global $response;
    
    $article_id = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;
    
    if ($article_id <= 0) {
        $response['message'] = 'Invalid article ID';
        echo json_encode($response);
        return;
    }
    
    $stmt = $conn->prepare("SELECT a.*, c.name as category_name 
                           FROM articles a 
                           JOIN categories c ON a.category_id = c.id
                           JOIN users u ON a.user_id = u.id
                           WHERE a.id = ? AND a.is_visible = 1 AND u.is_active = 1");
    
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $response['success'] = true;
        $response['article'] = $row;
    } else {
        $response['message'] = 'Article not found';
    }
    
    $stmt->close();
    echo json_encode($response);
}

function getArticleDetails($conn) {
    global $response;
    
    $article_id = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;
    $current_user_id = $_SESSION['user_id']; // Get current user ID
    
    if ($article_id <= 0) {
        $response['message'] = 'Invalid article ID';
        echo json_encode($response);
        return;
    }
    
    // Get article with user info and check if user is deleted or deactivated
    $stmt = $conn->prepare("SELECT a.*, c.name as category_name, u.username, u.is_deleted, u.is_active
                           FROM articles a 
                           JOIN categories c ON a.category_id = c.id
                           JOIN users u ON a.user_id = u.id
                           WHERE a.id = ? AND a.is_visible = 1");
    
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Check if the article belongs to a deleted or deactivated user and the current user is not the owner
        if (($row['is_deleted'] == 1 || $row['is_active'] == 0) && $row['user_id'] != $current_user_id) {
            $response['success'] = false;
            $response['message'] = 'This article is no longer available';
            echo json_encode($response);
            $stmt->close();
            return;
        }
        
        // Get the article details
        $article = $row;
        
        // Add is_owner flag to check if current user is the article owner
        $article['is_owner'] = ($article['user_id'] == $current_user_id);
        
        // Use the detector to analyze and highlight content
        $detector = new FakeNewsDetector($conn);
        $analysis = $detector->analyzeArticle($article['content']);
        
        // Get highlighted content with fake keywords
        $highlightedContent = $detector->highlightKeywords($article['content'], $analysis['matches']);
        
        $response['success'] = true;
        $response['article'] = $article;
        $response['keywords'] = [
            'matches' => $analysis['matches'],
            'match_count' => $analysis['match_count'],
            'highlighted_content' => $highlightedContent
        ];
    } else {
        $response['message'] = 'Article not found';
        $response['success'] = false;
    }
    
    $stmt->close();
    echo json_encode($response);
}

function updateArticle($conn, $user_id) {
    global $response;
    
    $article_id = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $category_id = isset($_POST['category']) ? (int)$_POST['category'] : 0;
    $source_url = isset($_POST['source_url']) ? trim($_POST['source_url']) : '';
    $date_published = isset($_POST['date']) ? date('Y-m-d', strtotime($_POST['date'])) : null;
    
    if ($article_id <= 0 || empty($title) || empty($content) || $category_id <= 0) {
        $response['message'] = 'Required fields are missing';
        echo json_encode($response);
        return;
    }
    
    // Create excerpt from content
    $excerpt = substr($content, 0, 150) . (strlen($content) > 150 ? '...' : '');
    
    // Verify ownership
    $stmt = $conn->prepare("SELECT user_id FROM articles WHERE id = ?");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['user_id'] != $user_id) {
            $response['message'] = 'You do not have permission to edit this article';
            echo json_encode($response);
            $stmt->close();
            return;
        }
    } else {
        $response['message'] = 'Article not found';
        echo json_encode($response);
        $stmt->close();
        return;
    }
    $stmt->close();
    
    // Update article and set status back to 'pending' regardless of previous status
    $status = 'pending'; // Reset status to pending
    
    // Re-analyze article content for fake news score
    $detector = new FakeNewsDetector($conn);
    $analysis = $detector->analyzeArticle($content);
    $detection_score = $analysis['score'];
    
    $stmt = $conn->prepare("UPDATE articles 
                           SET title = ?, content = ?, excerpt = ?, source_url = ?, 
                               date_published = ?, category_id = ?, status = ?, detection_score = ? 
                           WHERE id = ? AND user_id = ?");
    
    $stmt->bind_param("sssssisdii", $title, $content, $excerpt, $source_url, $date_published, $category_id, $status, $detection_score, $article_id, $user_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Article updated successfully - Status set to pending for admin review';
    } else {
        $response['message'] = 'Error updating article: ' . $stmt->error;
    }
    
    $stmt->close();
    echo json_encode($response);
}

function deleteArticle($conn, $user_id) {
    global $response;
    
    $article_id = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;
    
    if ($article_id <= 0) {
        $response['message'] = 'Invalid article ID';
        echo json_encode($response);
        return;
    }
    
    // Check if user is admin
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $is_admin = ($row && $row['role'] === 'admin');
    $stmt->close();
    
    // Get article info for logging
    $stmt = $conn->prepare("SELECT title, user_id FROM articles WHERE id = ?");
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $article_data = $result->fetch_assoc();
    $stmt->close();
    
    if (!$article_data) {
        $response['message'] = 'Article not found';
        echo json_encode($response);
        return;
    }
    
    $article_title = $article_data['title'];
    $article_owner_id = $article_data['user_id'];
    
    if (!$is_admin && $article_owner_id != $user_id) {
        $response['message'] = 'You do not have permission to delete this article';
        echo json_encode($response);
        return;
    }
    
    // Include activity logger
    require_once '../includes/activity_logger.php';
    
    // Update article to set is_visible = 0 instead of actually deleting it
    if ($is_admin) {
        // Admin user can remove any article without ownership check
        $stmt = $conn->prepare("UPDATE articles SET is_visible = 0 WHERE id = ?");
        $stmt->bind_param("i", $article_id);
    } else {
        // Regular user - only their own articles
        $stmt = $conn->prepare("UPDATE articles SET is_visible = 0 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $article_id, $user_id);
    }
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Article deleted successfully';
        $response['status'] = 'success'; // Match the response format used in other functions
        
        // Log the soft delete action
        logArticleDeletion($user_id, $article_title, $article_id);
    } else {
        $response['message'] = 'Error deleting article: ' . $stmt->error;
        $response['status'] = 'error';
    }
    
    $stmt->close();
    echo json_encode($response);
}

function getPaginatedArticles($conn, $user_id) {
    global $response, $date_format_patterns;
    
    // Get filter parameters if any
    $status = isset($_POST['status']) && $_POST['status'] != 'all' ? $_POST['status'] : null;
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = 10; // Number of articles per page
    
    // Calculate offset
    $offset = ($page - 1) * $limit;
    
    try {
        // Build base query for counting total - only include articles created after account reactivation
        $countQuery = "SELECT COUNT(*) as total FROM articles a 
                      JOIN categories c ON a.category_id = c.id 
                      JOIN users u ON a.user_id = u.id
                      WHERE a.user_id = ?
                      AND a.is_visible = 1
                      AND (a.created_at > (
                          SELECT COALESCE(MAX(al.timestamp), '1900-01-01') 
                          FROM activity_logs al 
                          WHERE al.user_id = ? AND al.action = 'User account reactivated'
                      ))";
        
        // Build data query - only include articles created after account reactivation
        $dataQuery = "SELECT a.*, c.name as category_name, u.username,
                  CASE
                    WHEN a.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN CONCAT(CEILING(TIMESTAMPDIFF(MINUTE, a.created_at, NOW())), ' min ago')
                    WHEN a.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN CONCAT(CEILING(TIMESTAMPDIFF(HOUR, a.created_at, NOW())), ' hours ago')
                    ELSE DATE_FORMAT(a.created_at, '%b %d, %Y')
                  END as time_ago
                  FROM articles a 
                  JOIN categories c ON a.category_id = c.id 
                  JOIN users u ON a.user_id = u.id
                  WHERE a.user_id = ?
                  AND a.is_visible = 1
                  AND (a.created_at > (
                      SELECT COALESCE(MAX(al.timestamp), '1900-01-01') 
                      FROM activity_logs al 
                      WHERE al.user_id = ? AND al.action = 'User account reactivated'
                  ))";
        
        // Add debugging for SQL query and params
        if ($status !== null) {
            error_log("Filtering by status: $status");
        }
        
        // Explicitly check if status param is legit, pending, or fake - add more specific filtering
        if ($status === 'legit' || $status === 'pending' || $status === 'fake') {
            // Make sure we're filtering EXACTLY by this status
            $countQuery .= " AND a.status = ?";
            $dataQuery .= " AND a.status = ?";
            
            // Log that we're applying strict filtering
            error_log("Applying strict status filtering for: $status");
        } else if ($status !== null) {
            // For other non-null status values, use the default filtering
            $countQuery .= " AND a.status = ?";
            $dataQuery .= " AND a.status = ?";
        }
        
        if (!empty($search)) {
            // Enhanced search across all relevant fields
            $countQuery .= " AND (
                a.title LIKE ? 
                OR a.content LIKE ? 
                OR c.name LIKE ?
                $date_format_patterns
            )";
            $dataQuery .= " AND (
                a.title LIKE ? 
                OR a.content LIKE ? 
                OR c.name LIKE ?
                $date_format_patterns
            )";
        }
        
        // Add sorting and limits
        $dataQuery .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
        
        // Debug the constructed queries
        error_log("Count query: $countQuery");
        error_log("Data query: $dataQuery");
        error_log("Parameter counts in count query: " . substr_count($countQuery, '?'));
        error_log("Parameter counts in data query: " . substr_count($dataQuery, '?'));
        
        // Prepare and execute count query
        $countStmt = $conn->prepare($countQuery);
        if (!$countStmt) {
            $response['success'] = false;
            $response['message'] = 'Error preparing count query: ' . $conn->error;
            echo json_encode($response);
            return;
        }
        
        // Bind parameters for count query using dynamic approach
        try {
            // Create arrays to hold all parameters for bind_param
            $types = '';  // String containing parameter types
            $params = []; // Array to hold parameter values
            
            // First parameter is always user_id (integer)
            $types .= 'ii';
            $params[] = $user_id;
            $params[] = $user_id; // For the subquery
            
            // Add status parameter if used
            if ($status !== null) {
                $types .= 's';
                $params[] = $status;
            }
            
            // Add search parameters if used
            if (!empty($search)) {
                // Add base search parameters - 3 string parameters for title, content, category
                $types .= 'sss';
                $search_param = "%$search%";
                
                // Add all search parameters
                $params[] = $search_param; // title LIKE ?
                $params[] = $search_param; // content LIKE ?
                $params[] = $search_param; // category LIKE ?
                
                // Add 9 date format parameters
                $types .= str_repeat('s', 9);
                addDateParameters($params, $search);
            }
            
            // Log for debugging
            error_log("Count parameter types: $types");
            error_log("Count parameter count: " . count($params));
            
            // Execute bind_param with constructed parameter list
            $countStmt->bind_param($types, ...$params);
            
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = 'Error binding count parameters: ' . $e->getMessage();
            error_log("Count parameter binding error: " . $e->getMessage());
            echo json_encode($response);
            return;
        }
        
        if (!$countStmt->execute()) {
            $response['success'] = false;
            $response['message'] = 'Error executing count query: ' . $countStmt->error;
            echo json_encode($response);
            $countStmt->close();
            return;
        }
        
        $countResult = $countStmt->get_result();
        $totalRow = $countResult->fetch_assoc();
        $total = $totalRow['total'];
        $countStmt->close();
        
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // Adjust current page if needed
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }
        
        // Prepare data query
        $dataStmt = $conn->prepare($dataQuery);
        if (!$dataStmt) {
            $response['success'] = false;
            $response['message'] = 'Error preparing data query: ' . $conn->error;
            echo json_encode($response);
            return;
        }
        
        // Bind parameters for data query - this is a critical section that needs to be fixed
        try {
            // Create arrays to hold all parameters for bind_param
            $types = '';  // String containing parameter types
            $params = []; // Array to hold parameter values
            
            // First parameter is always user_id (integer)
            $types .= 'ii';
            $params[] = $user_id;
            $params[] = $user_id; // For the subquery
            
            // Add status parameter if used
            if ($status !== null) {
                $types .= 's';
                $params[] = $status;
            }
            
            // Add search parameters if used
            if (!empty($search)) {
                // Add base search parameters - 3 string parameters for title, content, category
                $types .= 'sss';
                $search_param = "%$search%";
                
                // Add all search parameters
                $params[] = $search_param; // title LIKE ?
                $params[] = $search_param; // content LIKE ?
                $params[] = $search_param; // category LIKE ?
                
                // Add 9 date format parameters
                $types .= str_repeat('s', 9);
                addDateParameters($params, $search);
            }
            
            // Finally add the limit and offset parameters (always integers)
            $types .= 'ii';
            $params[] = $limit;
            $params[] = $offset;
            
            // Log for debugging
            error_log("Data parameter types: $types");
            error_log("Data parameter count: " . count($params));
            
            // Execute bind_param with constructed parameter list
            $dataStmt->bind_param($types, ...$params);
            
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = 'Error binding parameters: ' . $e->getMessage();
            error_log("Parameter binding error: " . $e->getMessage());
            echo json_encode($response);
            return;
        }
        
        if (!$dataStmt->execute()) {
            $response['success'] = false;
            $response['message'] = 'Error executing data query: ' . $dataStmt->error;
            echo json_encode($response);
            $dataStmt->close();
            return;
        }
        
        $result = $dataStmt->get_result();
        $articles = [];
        
        while ($row = $result->fetch_assoc()) {
            // Add flag to indicate ownership
            $row['is_owner'] = true;
            $articles[] = $row;
        }
        
        $response['success'] = true;
        $response['articles'] = $articles;
        $response['total'] = $total;
        $response['current_page'] = $page;
        $response['total_pages'] = $totalPages;
        
        $dataStmt->close();
        
        echo json_encode($response);
    } catch (Exception $e) {
        // Log the error
        error_log("Exception in getPaginatedArticles: " . $e->getMessage());
        
        // Return a proper JSON response
        echo json_encode([
            'success' => false,
            'message' => 'Error processing paginated articles: ' . $e->getMessage()
        ]);
    }
}

function getCommunityArticles($conn, $user_id) {
    global $response, $date_format_patterns;
    
    // Debugging information
    error_log("getCommunityArticles called with user_id: $user_id");
    error_log("POST data: " . print_r($_POST, true));
    
    // Get filter parameters if any
    $status = isset($_POST['status']) && $_POST['status'] != 'all' ? $_POST['status'] : null;
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10; // Number of articles per page
    
    // More debugging
    error_log("Processed parameters: status=$status, search=$search, page=$page, limit=$limit");
    
    // Calculate offset
    $offset = ($page - 1) * $limit;
    
    try {
        // Build base query for counting total - exclude current user's articles and articles from deleted users
        $countQuery = "SELECT COUNT(*) as total FROM articles a 
                      JOIN categories c ON a.category_id = c.id 
                      JOIN users u ON a.user_id = u.id
                      WHERE a.user_id != ? AND u.is_deleted = 0 AND u.is_active = 1 AND a.is_visible = 1";
        
        // Build data query - exclude current user's articles and articles from deleted users
        $dataQuery = "SELECT a.*, c.name as category_name, u.username,
                  CASE
                    WHEN a.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN CONCAT(CEILING(TIMESTAMPDIFF(MINUTE, a.created_at, NOW())), ' min ago')
                    WHEN a.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN CONCAT(CEILING(TIMESTAMPDIFF(HOUR, a.created_at, NOW())), ' hours ago')
                    ELSE DATE_FORMAT(a.created_at, '%b %d, %Y')
                  END as time_ago
                  FROM articles a 
                  JOIN categories c ON a.category_id = c.id 
                  JOIN users u ON a.user_id = u.id
                  WHERE a.user_id != ? AND u.is_deleted = 0 AND u.is_active = 1 AND a.is_visible = 1";
        
        // Add filter conditions to both queries
        if ($status !== null) {
            $countQuery .= " AND a.status = ?";
            $dataQuery .= " AND a.status = ?";
        }
        
        if (!empty($search)) {
            // Enhanced search across all relevant fields
            $countQuery .= " AND (
                a.title LIKE ? 
                OR a.content LIKE ? 
                OR c.name LIKE ?
                OR u.username LIKE ?
                $date_format_patterns
            )";
            $dataQuery .= " AND (
                a.title LIKE ? 
                OR a.content LIKE ? 
                OR c.name LIKE ?
                OR u.username LIKE ?
                $date_format_patterns
            )";
        }
        
        // Add sorting and limits
        $dataQuery .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
        
        // Debug the constructed queries
        error_log("Count query: $countQuery");
        error_log("Data query: $dataQuery");
        error_log("Parameter counts in count query: " . substr_count($countQuery, '?'));
        error_log("Parameter counts in data query: " . substr_count($dataQuery, '?'));
        
        // Prepare and execute count query
        $countStmt = $conn->prepare($countQuery);
        if (!$countStmt) {
            $response['success'] = false;
            $response['message'] = 'Error preparing count query: ' . $conn->error;
            echo json_encode($response);
            return;
        }
        
        // Bind parameters for count query using dynamic approach
        try {
            // Create arrays to hold all parameters for bind_param
            $types = '';  // String containing parameter types
            $params = []; // Array to hold parameter values
            
            // First parameter is always user_id (integer)
            $types .= 'i';
            $params[] = $user_id;
            
            // Add status parameter if used
            if ($status !== null) {
                $types .= 's';
                $params[] = $status;
            }
            
            // Add search parameters if used
            if (!empty($search)) {
                // Add base search parameters - 4 string parameters for title, content, category, username
                $types .= 'ssss';
                $search_param = "%$search%";
                
                // Add base search parameters
                $params[] = $search_param; // title LIKE ?
                $params[] = $search_param; // content LIKE ?
                $params[] = $search_param; // category LIKE ?
                $params[] = $search_param; // username LIKE ?
                
                // Add 9 date format parameters
                $types .= str_repeat('s', 9);
                addDateParameters($params, $search);
            }
            
            // Log for debugging
            error_log("Count parameter types: $types");
            error_log("Count parameter count: " . count($params));
            
            // Execute bind_param with constructed parameter list
            $countStmt->bind_param($types, ...$params);
            
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = 'Error binding count parameters: ' . $e->getMessage();
            error_log("Count parameter binding error: " . $e->getMessage());
            echo json_encode($response);
            return;
        }
        
        if (!$countStmt->execute()) {
            $response['success'] = false;
            $response['message'] = 'Error executing count query: ' . $countStmt->error;
            echo json_encode($response);
            $countStmt->close();
            return;
        }
        
        $countResult = $countStmt->get_result();
        $totalRow = $countResult->fetch_assoc();
        $total = $totalRow['total'];
        $countStmt->close();
        
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // Adjust current page if needed
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }
        
        // Prepare data query
        $dataStmt = $conn->prepare($dataQuery);
        if (!$dataStmt) {
            $response['success'] = false;
            $response['message'] = 'Error preparing data query: ' . $conn->error;
            echo json_encode($response);
            return;
        }
        
        // Bind parameters for data query - this is a critical section that needs to be fixed
        try {
            // Create arrays to hold all parameters for bind_param
            $types = '';  // String containing parameter types
            $params = []; // Array to hold parameter values
            
            // First parameter is always user_id (integer)
            $types .= 'i';
            $params[] = $user_id;
            
            // Add status parameter if used
            if ($status !== null) {
                $types .= 's';
                $params[] = $status;
            }
            
            // Add search parameters if used
            if (!empty($search)) {
                // Add base search parameters - 4 string parameters for title, content, category, username
                $types .= 'ssss';
                $search_param = "%$search%";
                
                // Add base search parameters
                $params[] = $search_param; // title LIKE ?
                $params[] = $search_param; // content LIKE ?
                $params[] = $search_param; // category LIKE ?
                $params[] = $search_param; // username LIKE ?
                
                // Add 9 date format parameters
                $types .= str_repeat('s', 9);
                addDateParameters($params, $search);
            }
            
            // Finally add the limit and offset parameters (always integers)
            $types .= 'ii';
            $params[] = $limit;
            $params[] = $offset;
            
            // Log for debugging
            error_log("Data parameter types: $types");
            error_log("Data parameter count: " . count($params));
            
            // Execute bind_param with constructed parameter list
            $dataStmt->bind_param($types, ...$params);
            
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = 'Error binding parameters: ' . $e->getMessage();
            error_log("Parameter binding error: " . $e->getMessage());
            echo json_encode($response);
            return;
        }
        
        if (!$dataStmt->execute()) {
            $response['success'] = false;
            $response['message'] = 'Error executing data query: ' . $dataStmt->error;
            echo json_encode($response);
            $dataStmt->close();
            return;
        }
        
        $result = $dataStmt->get_result();
        $articles = [];
        
        while ($row = $result->fetch_assoc()) {
            // Mark that this article is not owned by the current user
            $row['is_owner'] = false;
            $articles[] = $row;
        }
        
        $response['success'] = true;
        $response['articles'] = $articles;
        $response['total'] = $total;
        $response['current_page'] = $page;
        $response['total_pages'] = $totalPages;
        
        $dataStmt->close();
        
        echo json_encode($response);
    } catch (Exception $e) {
        // Log the error
        error_log("Exception in getCommunityArticles: " . $e->getMessage());
        
        // Return a proper JSON response
        echo json_encode([
            'success' => false,
            'message' => 'Error processing community articles: ' . $e->getMessage()
        ]);
    }
}

function getAdminArticleDetails($conn) {
    global $response;
    
    // Validate article_id
    if (!isset($_POST['article_id']) || empty($_POST['article_id'])) {
        $response = [
            'status' => 'error',
            'message' => 'Article ID is required'
        ];
        echo json_encode($response);
        return;
    }
    
    $article_id = mysqli_real_escape_string($conn, $_POST['article_id']);
    
    // Get article details
    $query = "SELECT a.*, c.name as category_name, u.username as author
              FROM articles a
              LEFT JOIN categories c ON a.category_id = c.id
              LEFT JOIN users u ON a.user_id = u.id
              WHERE a.id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $article = $result->fetch_assoc();
        
        // Format date for display
        if (!empty($article['date_published'])) {
            $article['date_published'] = date('m/d/Y', strtotime($article['date_published']));
        }
        
        // Get fake keywords that appear in the article
        $fake_keywords = array();
        $keyword_query = "SELECT keyword FROM fake_keywords";
        $keyword_result = mysqli_query($conn, $keyword_query);
        
        if ($keyword_result && mysqli_num_rows($keyword_result) > 0) {
            while ($keyword_row = mysqli_fetch_assoc($keyword_result)) {
                $keyword = $keyword_row['keyword'];
                // Check if keyword appears in the article content
                if (stripos($article['content'], $keyword) !== false) {
                    $fake_keywords[] = $keyword;
                }
            }
        }
        
        // Add fake keywords to article data
        $article['fake_keywords'] = $fake_keywords;
        
        // Set response
        $response = [
            'status' => 'success',
            'message' => 'Article details retrieved successfully',
            'data' => $article
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Article not found'
        ];
    }
    
    $stmt->close();
    echo json_encode($response);
}

function updateArticleStatus($conn, $user_id) {
    global $response;
    
    // Check if required parameters are set
    if (!isset($_POST['article_id']) || empty($_POST['article_id']) || 
        !isset($_POST['status']) || empty($_POST['status'])) {
        
        $response = [
            'status' => 'error',
            'message' => 'Article ID and status are required'
        ];
        echo json_encode($response);
        return;
    }
    
    $article_id = mysqli_real_escape_string($conn, $_POST['article_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Validate status value
    if ($status !== 'legit' && $status !== 'fake' && $status !== 'pending') {
        $response = [
            'status' => 'error',
            'message' => 'Invalid status value'
        ];
        echo json_encode($response);
        return;
    }
    
    // Update article status
    $stmt = $conn->prepare("UPDATE articles SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $article_id);
    
    if ($stmt->execute()) {
        // Log the action
        $action = "Updated article #$article_id status to $status";
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
        $log_stmt->bind_param("is", $user_id, $action);
        $log_stmt->execute();
        
        $response = [
            'status' => 'success',
            'message' => 'Article status updated successfully'
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Failed to update article status: ' . $stmt->error
        ];
    }
    
    $stmt->close();
    echo json_encode($response);
}

function getAdminArticles($conn) {
    global $response, $date_format_patterns;
    
    // Get pagination parameters
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = 10; // Number of articles per page
    $status = isset($_POST['status']) && $_POST['status'] != 'all' ? $_POST['status'] : null;
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    
    // Calculate offset
    $offset = ($page - 1) * $limit;
    
    try {
        // Build count query - only include visible articles
        $countQuery = "SELECT COUNT(*) as total FROM articles a 
                      LEFT JOIN categories c ON a.category_id = c.id
                      LEFT JOIN users u ON a.user_id = u.id
                      WHERE a.is_visible = 1 AND u.is_active = 1";
        
        // Build data query - only include visible articles
        $dataQuery = "SELECT a.*, c.name as category_name, u.username as author
                     FROM articles a 
                     LEFT JOIN categories c ON a.category_id = c.id
                     LEFT JOIN users u ON a.user_id = u.id
                     WHERE a.is_visible = 1 AND u.is_active = 1";
        
        // Handle special case for "reviewed" status (show both legit and fake)
        if ($status === 'reviewed') {
            $countQuery .= " AND (a.status = 'legit' OR a.status = 'fake')";
            $dataQuery .= " AND (a.status = 'legit' OR a.status = 'fake')";
        }
        // Handle regular status filtering
        elseif ($status !== null) {
            $countQuery .= " AND a.status = ?";
            $dataQuery .= " AND a.status = ?";
        }
        
        if (!empty($search)) {
            $countQuery .= " AND (a.id LIKE ? OR a.title LIKE ? OR u.username LIKE ? OR c.name LIKE ?)";
            $dataQuery .= " AND (a.id LIKE ? OR a.title LIKE ? OR u.username LIKE ? OR c.name LIKE ?)";
        }
        
        // Add sorting and limits
        $dataQuery .= " ORDER BY a.id DESC LIMIT ? OFFSET ?";
        
        // Prepare and execute count query
        $countStmt = $conn->prepare($countQuery);
        
        if (!$countStmt) {
            $response = [
                'status' => 'error',
                'message' => 'Error preparing count query: ' . $conn->error
            ];
            echo json_encode($response);
            return;
        }
        
        // Bind parameters for count query - special handling for reviewed filter
        if ($status !== null && $status !== 'reviewed' && !empty($search)) {
            $search_param = "%$search%";
            $countStmt->bind_param("sssss", $status, $search_param, $search_param, $search_param, $search_param);
        } elseif ($status !== null && $status !== 'reviewed') {
            $countStmt->bind_param("s", $status);
        } elseif (!empty($search)) {
            $search_param = "%$search%";
            $countStmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
        }
        
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRow = $countResult->fetch_assoc();
        $total = $totalRow['total'];
        $countStmt->close();
        
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // Adjust current page if needed
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }
        
        // Prepare and execute data query
        $dataStmt = $conn->prepare($dataQuery);
        
        if (!$dataStmt) {
            $response = [
                'status' => 'error',
                'message' => 'Error preparing data query: ' . $conn->error
            ];
            echo json_encode($response);
            return;
        }
        
        // Bind parameters for data query - special handling for reviewed filter
        if ($status !== null && $status !== 'reviewed' && !empty($search)) {
            $search_param = "%$search%";
            $dataStmt->bind_param("sssssii", $status, $search_param, $search_param, $search_param, $search_param, $limit, $offset);
        } elseif ($status !== null && $status !== 'reviewed') {
            $dataStmt->bind_param("sii", $status, $limit, $offset);
        } elseif (!empty($search)) {
            $search_param = "%$search%";
            $dataStmt->bind_param("ssssii", $search_param, $search_param, $search_param, $search_param, $limit, $offset);
        } else {
            $dataStmt->bind_param("ii", $limit, $offset);
        }
        
        $dataStmt->execute();
        $result = $dataStmt->get_result();
        
        $articles = [];
        while ($row = $result->fetch_assoc()) {
            // Format date for display
            if (!empty($row['date_published'])) {
                $row['date_published'] = date('m/d/Y', strtotime($row['date_published']));
            }
            $articles[] = $row;
        }
        
        $dataStmt->close();
        
        // Prepare response
        $response = [
            'status' => 'success',
            'articles' => $articles,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        $response = [
            'status' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ];
        echo json_encode($response);
    }
}

function exportArticles($conn) {
    // Get filter parameters if any
    $status = isset($_POST['status']) && $_POST['status'] != 'all' ? $_POST['status'] : null;
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    
    try {
        // Build query to get all matching articles (no pagination)
        $query = "SELECT a.id, a.title, u.username as author, c.name as category, 
                 a.date_published, a.source_url, a.detection_score, a.status, 
                 a.created_at, a.updated_at
                 FROM articles a 
                 LEFT JOIN categories c ON a.category_id = c.id
                 LEFT JOIN users u ON a.user_id = u.id
                 WHERE a.is_visible = 1 AND u.is_active = 1";
        
        // Handle special case for "reviewed" status (show both legit and fake)
        if ($status === 'reviewed') {
            $query .= " AND (a.status = 'legit' OR a.status = 'fake')";
        }
        // Handle regular status filtering
        elseif ($status !== null) {
            $query .= " AND a.status = ?";
        }
        
        if (!empty($search)) {
            $query .= " AND (a.id LIKE ? OR a.title LIKE ? OR u.username LIKE ? OR c.name LIKE ?)";
        }
        
        $query .= " ORDER BY a.id DESC";
        
        // Prepare and execute query
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            // Return error as JSON if someone tries to access via AJAX
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Error preparing export query: ' . $conn->error
            ]);
            return;
        }
        
        // Bind parameters - special handling for reviewed filter
        if ($status !== null && $status !== 'reviewed' && !empty($search)) {
            $search_param = "%$search%";
            $stmt->bind_param("sssss", $status, $search_param, $search_param, $search_param, $search_param);
        } elseif ($status !== null && $status !== 'reviewed') {
            $stmt->bind_param("s", $status);
        } elseif (!empty($search)) {
            $search_param = "%$search%";
            $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Set headers for CSV download
        $filename = 'articles_export_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add header row
        fputcsv($output, [
            'ID', 
            'Title', 
            'Author', 
            'Category', 
            'Date Published',
            'Source URL', 
            'Detection Score', 
            'Result',
            'Status', 
            'Created Date', 
            'Last Updated'
        ]);
        
        // Add data rows
        while ($row = $result->fetch_assoc()) {
            // Format dates
            $date_published = !empty($row['date_published']) ? date('m/d/Y', strtotime($row['date_published'])) : '';
            $created_at = date('m/d/Y H:i:s', strtotime($row['created_at']));
            $updated_at = date('m/d/Y H:i:s', strtotime($row['updated_at']));
            
            // Determine result based on detection score
            if ($row['detection_score'] < 25) {
                $result_text = 'Likely Legit';
            } elseif ($row['detection_score'] < 50) {
                $result_text = 'Suspicious';
            } else {
                $result_text = 'Likely Fake';
            }
            
            // Capitalize status
            $status = ucfirst($row['status']);
            
            // Write row to CSV
            fputcsv($output, [
                $row['id'],
                $row['title'],
                $row['author'],
                $row['category'],
                $date_published,
                $row['source_url'],
                $row['detection_score'] . '%',
                $result_text,
                $status,
                $created_at,
                $updated_at
            ]);
        }
        
        // Close statement and output
        $stmt->close();
        fclose($output);
        exit; // Important: stop execution after sending the file
        
    } catch (Exception $e) {
        // Return error as JSON
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Error exporting articles: ' . $e->getMessage()
        ]);
    }
}

function getCategories($conn) {
    try {
        // Get pagination parameters
        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10; // Number of categories per page
        $search = isset($_POST['search']) ? trim($_POST['search']) : '';
        
        // Calculate offset
        $offset = ($page - 1) * $limit;
        
        // Build count query
        $countQuery = "SELECT COUNT(*) as total FROM categories";
        
        // Add search condition if search parameter is provided
        if (!empty($search)) {
            $countQuery .= " WHERE name LIKE ?";
        }
        
        $countStmt = $conn->prepare($countQuery);
        
        if (!$countStmt) {
            throw new Exception('Error preparing count query: ' . $conn->error);
        }
        
        // Bind search parameter if used
        if (!empty($search)) {
            $search_param = "%$search%";
            $countStmt->bind_param("s", $search_param);
        }
        
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRow = $countResult->fetch_assoc();
        $total = $totalRow['total'];
        $countStmt->close();
        
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // Adjust current page if needed
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }
        
        // Build data query
        $dataQuery = "SELECT * FROM categories";
        
        // Add search condition if search parameter is provided
        if (!empty($search)) {
            $dataQuery .= " WHERE name LIKE ?";
        }
        
        $dataQuery .= " ORDER BY id DESC LIMIT ? OFFSET ?";
        
        $dataStmt = $conn->prepare($dataQuery);
        
        if (!$dataStmt) {
            throw new Exception('Error preparing data query: ' . $conn->error);
        }
        
        // Bind parameters
        if (!empty($search)) {
            $search_param = "%$search%";
            $dataStmt->bind_param("sii", $search_param, $limit, $offset);
        } else {
            $dataStmt->bind_param("ii", $limit, $offset);
        }
        
        $dataStmt->execute();
        $result = $dataStmt->get_result();
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        $dataStmt->close();
        
        echo json_encode([
            'status' => 'success',
            'categories' => $categories,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error loading categories: ' . $e->getMessage()
        ]);
    }
}

function addCategory($conn) {
    // Validate input
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    
    if (empty($name)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Category name is required'
        ]);
        return;
    }
    
    try {
        // Check if category already exists
        $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Category already exists'
            ]);
            return;
        }
        
        // Insert new category
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Category added successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error adding category: ' . $stmt->error
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error adding category: ' . $e->getMessage()
        ]);
    }
}

function updateCategory($conn) {
    // Validate input
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    
    if ($id <= 0 || empty($name)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid input parameters'
        ]);
        return;
    }
    
    try {
        // Check if category exists
        $stmt = $conn->prepare("SELECT id FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Category not found'
            ]);
            return;
        }
        
        // Check if name already exists for another category
        $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Another category with this name already exists'
            ]);
            return;
        }
        
        // Update the category
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Category updated successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error updating category: ' . $stmt->error
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error updating category: ' . $e->getMessage()
        ]);
    }
}

function deleteCategory($conn) {
    // Validate input
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid category ID'
        ]);
        return;
    }
    
    try {
        // Check if there are any articles using this category
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM articles WHERE category_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Cannot delete category - it is being used by ' . $row['count'] . ' article(s)'
            ]);
            return;
        }
        
        // Delete the category
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Category deleted successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error deleting category: ' . $stmt->error
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error deleting category: ' . $e->getMessage()
        ]);
    }
}

function getKeywords($conn) {
    try {
        // Get pagination parameters
        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10; // Number of keywords per page
        $search = isset($_POST['search']) ? trim($_POST['search']) : '';
        
        // Calculate offset
        $offset = ($page - 1) * $limit;
        
        // Build count query
        $countQuery = "SELECT COUNT(*) as total FROM fake_keywords";
        
        // Add search condition if search parameter is provided
        if (!empty($search)) {
            $countQuery .= " WHERE keyword LIKE ?";
        }
        
        $countStmt = $conn->prepare($countQuery);
        
        if (!$countStmt) {
            throw new Exception('Error preparing count query: ' . $conn->error);
        }
        
        // Bind search parameter if used
        if (!empty($search)) {
            $search_param = "%$search%";
            $countStmt->bind_param("s", $search_param);
        }
        
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $totalRow = $countResult->fetch_assoc();
        $total = $totalRow['total'];
        $countStmt->close();
        
        // Calculate total pages
        $totalPages = ceil($total / $limit);
        
        // Adjust current page if needed
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }
        
        // Build data query
        $dataQuery = "SELECT * FROM fake_keywords";
        
        // Add search condition if search parameter is provided
        if (!empty($search)) {
            $dataQuery .= " WHERE keyword LIKE ?";
        }
        
        $dataQuery .= " ORDER BY id DESC LIMIT ? OFFSET ?";
        
        $dataStmt = $conn->prepare($dataQuery);
        
        if (!$dataStmt) {
            throw new Exception('Error preparing data query: ' . $conn->error);
        }
        
        // Bind parameters
        if (!empty($search)) {
            $search_param = "%$search%";
            $dataStmt->bind_param("sii", $search_param, $limit, $offset);
        } else {
            $dataStmt->bind_param("ii", $limit, $offset);
        }
        
        $dataStmt->execute();
        $result = $dataStmt->get_result();
        
        $keywords = [];
        while ($row = $result->fetch_assoc()) {
            $keywords[] = $row;
        }
        
        $dataStmt->close();
        
        echo json_encode([
            'status' => 'success',
            'keywords' => $keywords,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error loading keywords: ' . $e->getMessage()
        ]);
    }
}

function addKeyword($conn) {
    // Validate input
    $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';
    
    if (empty($keyword)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Keyword is required'
        ]);
        return;
    }
    
    try {
        // Check if keyword already exists
        $stmt = $conn->prepare("SELECT id FROM fake_keywords WHERE keyword = ?");
        $stmt->bind_param("s", $keyword);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Keyword already exists'
            ]);
            return;
        }
        
        // Insert new keyword
        $stmt = $conn->prepare("INSERT INTO fake_keywords (keyword) VALUES (?)");
        $stmt->bind_param("s", $keyword);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Keyword added successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error adding keyword: ' . $stmt->error
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error adding keyword: ' . $e->getMessage()
        ]);
    }
}

function updateKeyword($conn) {
    // Validate input
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';
    
    if ($id <= 0 || empty($keyword)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid input parameters'
        ]);
        return;
    }
    
    try {
        // Check if keyword exists
        $stmt = $conn->prepare("SELECT id FROM fake_keywords WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Keyword not found'
            ]);
            return;
        }
        
        // Check if keyword already exists for another entry
        $stmt = $conn->prepare("SELECT id FROM fake_keywords WHERE keyword = ? AND id != ?");
        $stmt->bind_param("si", $keyword, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Another entry with this keyword already exists'
            ]);
            return;
        }
        
        // Update the keyword
        $stmt = $conn->prepare("UPDATE fake_keywords SET keyword = ? WHERE id = ?");
        $stmt->bind_param("si", $keyword, $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Keyword updated successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error updating keyword: ' . $stmt->error
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error updating keyword: ' . $e->getMessage()
        ]);
    }
}

function deleteKeyword($conn) {
    // Validate input
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid keyword ID'
        ]);
        return;
    }
    
    try {
        // Delete the keyword
        $stmt = $conn->prepare("DELETE FROM fake_keywords WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Keyword deleted successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error deleting keyword: ' . $stmt->error
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error deleting keyword: ' . $e->getMessage()
        ]);
    }
}

function exportCategories($conn) {
    try {
        // Build query to get all categories
        $query = "SELECT id, name, created_at FROM categories ORDER BY id";
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            // Return error as JSON if someone tries to access via AJAX
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Error exporting categories: ' . $conn->error
            ]);
            return;
        }
        
        // Set headers for CSV download
        $filename = 'categories_export_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add header row
        fputcsv($output, [
            'ID', 
            'Category Name',
            'Created Date'
        ]);
        
        // Add data rows
        while ($row = mysqli_fetch_assoc($result)) {
            // Format date
            $created_at = date('m/d/Y H:i:s', strtotime($row['created_at']));
            
            // Write row to CSV
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $created_at
            ]);
        }
        
        // Close output
        fclose($output);
        exit; // Important: stop execution after sending the file
        
    } catch (Exception $e) {
        // Return error as JSON
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Error exporting categories: ' . $e->getMessage()
        ]);
    }
}

function exportKeywords($conn) {
    try {
        // Build query to get all keywords
        $query = "SELECT * FROM fake_keywords ORDER BY id";
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            // Return error as JSON if someone tries to access via AJAX
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Error exporting keywords: ' . $conn->error
            ]);
            return;
        }
        
        // Set headers for CSV download
        $filename = 'keywords_export_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add header row
        fputcsv($output, [
            'ID', 
            'Keyword'
        ]);
        
        // Add data rows
        while ($row = mysqli_fetch_assoc($result)) {
            // Write row to CSV
            fputcsv($output, [
                $row['id'],
                $row['keyword']
            ]);
        }
        
        // Close output
        fclose($output);
        exit; // Important: stop execution after sending the file
        
    } catch (Exception $e) {
        // Return error as JSON
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Error exporting keywords: ' . $e->getMessage()
        ]);
    }
}
?> 