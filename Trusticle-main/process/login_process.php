<?php
session_start();
require_once "../config/connection.php";
require_once "../utils/validation.php";
require_once "../utils/user.php";

header('Content-Type: application/json');
$response = array();

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize validator with POST data
    $validator = new Validator($_POST, $conn);
    
    // Validate form data - fluent interface for better readability (KISS principle)
    $validator->required('username')
              ->usernameFormat('username')
              ->required('password');
    
    // If there are errors, return them
    if ($validator->fails()) {
        $response['success'] = false;
        $response['errors'] = $validator->getErrors();
        echo json_encode($response);
        exit();
    }
    
    // Get sanitized values
    $username = $validator->getValue('username');
    $password = $validator->getValue('password');
    
    try {
        // First, check if a user exists with this username
        $stmt = $conn->prepare("SELECT id, username, email, password, role, is_active, is_deleted FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username); // Check both username and email
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if user is deleted - don't allow login for deleted accounts
            if ($user['is_deleted'] == TRUE) {
                $response['success'] = false;
                $response['errors'] = ["This account no longer exists. You may register again with this username/email."];
                echo json_encode($response);
                exit();
            }
            
            // Check if user is active
            if ($user['is_active'] == FALSE) {
                // Instead of showing error, reactivate the account
                $reactivateStmt = $conn->prepare("UPDATE users SET is_active = TRUE WHERE id = ?");
                $reactivateStmt->bind_param("i", $user['id']);
                $reactivateStmt->execute();
                $reactivateStmt->close();
                
                // Log reactivation activity
                logUserActivity($conn, $user['id'], "Account reactivated by login");
                
                // Continue with login process
                // No need to show a specific message about reactivation as this is automatic
            }
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                // Log activity using our centralized function
                logUserActivity($conn, $user['id'], "User logged in");
                
                // Check if password needs rehash (in case hashing algorithm changes)
                if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $newHash, $user['id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
                
                $response['success'] = true;
                
                // Set redirect URL based on user role
                if ($user['role'] === 'admin') {
                    $response['redirect'] = "../admin/view/dashboard.php";
                } else {
                    $response['redirect'] = "../user/view/dashboard.php";
                }
                
            } else {
                $response['success'] = false;
                $response['errors'] = ["Incorrect password. Please try again."];
                
                // Optional: Log failed login attempts for security monitoring
                logUserActivity($conn, $user['id'], "Failed login attempt");
            }
        } else {
            $response['success'] = false;
            $response['errors'] = ["Account not found. Please sign up if you don't have an account."];
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $response['success'] = false;
        $response['errors'] = ["An error occurred: " . $e->getMessage()];
    }
    
} else {
    $response['success'] = false;
    $response['errors'] = ["Invalid request method"];
}

echo json_encode($response);
$conn->close();
?>
