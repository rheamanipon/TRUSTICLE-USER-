<?php
/**
 * Activity Log Controller
 * 
 * Handles operations related to activity logs
 */

// Include necessary files
include_once '../../includes/db_connect.php';
include_once '../../includes/activity_logger.php';
include_once '../../includes/auth.php';

// Check if user is logged in and is an admin
checkAdminAuth();

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'delete_log':
            deleteActivityLog();
            break;
        default:
            sendResponse(false, 'Invalid action');
            break;
    }
} else {
    // Only POST requests are allowed
    sendResponse(false, 'Invalid request method');
}

/**
 * Delete an activity log entry
 */
function deleteActivityLog() {
    global $conn;
    
    // Check if log_id is set
    if (!isset($_POST['log_id'])) {
        sendResponse(false, 'Log ID is required');
        return;
    }
    
    $logId = mysqli_real_escape_string($conn, $_POST['log_id']);
    
    // Delete the log entry
    $query = "DELETE FROM activity_logs WHERE id = '$logId'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        // Log this action by the admin
        $adminId = $_SESSION['user_id'];
        logActivity($adminId, "Deleted activity log entry (ID: $logId)");
        
        sendResponse(true, 'Activity log deleted successfully');
    } else {
        sendResponse(false, 'Failed to delete activity log: ' . mysqli_error($conn));
    }
}

/**
 * Send JSON response
 * 
 * @param bool $success Whether the operation was successful
 * @param string $message Response message
 * @param array $data Additional data (optional)
 */
function sendResponse($success, $message, $data = []) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}
?> 