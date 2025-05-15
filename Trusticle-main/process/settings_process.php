<?php
// Start the session
session_start();

// Include database connection
require_once '../config/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You are not logged in']);
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'];

// Function to log user activity
function logUserActivity($userId, $action, $conn) {
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $action);
    $stmt->execute();
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize response array
    $response = ['success' => false];
    
    // Check form type
    if (isset($_POST['form_type'])) {
        $formType = $_POST['form_type'];
        
        // Handle profile update
        if ($formType === 'update_profile') {
            // Validate required fields
            $requiredFields = ['first_name', 'last_name', 'username', 'email', 'dob'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    $errors[] = ['field' => str_replace('_', '-', $field), 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'];
                }
            }
            
            // Validate email format
            if (isset($_POST['email']) && !empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = ['field' => 'email', 'message' => 'Invalid email format'];
            }
            
            // Validate username format
            if (isset($_POST['username']) && !empty($_POST['username'])) {
                if (strlen($_POST['username']) < 4) {
                    $errors[] = ['field' => 'username', 'message' => 'Username must be at least 4 characters'];
                } elseif (!preg_match('/^[a-zA-Z0-9_\.]+$/', $_POST['username'])) {
                    $errors[] = ['field' => 'username', 'message' => 'Username can only contain letters, numbers, underscores, and periods'];
                }
            }
            
            // Validate date format
            if (isset($_POST['dob']) && !empty($_POST['dob'])) {
                $dob = $_POST['dob'];
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob) || !strtotime($dob)) {
                    $errors[] = ['field' => 'dob', 'message' => 'Invalid date format. Use YYYY-MM-DD format.'];
                } else {
                    // Check if date is in the past
                    $dobDate = new DateTime($dob);
                    $today = new DateTime();
                    if ($dobDate > $today) {
                        $errors[] = ['field' => 'dob', 'message' => 'Date of birth must be in the past'];
                    }
                }
            }
            
            // Check if email or username already exists (for another user)
            if (empty($errors)) {
                $email = trim($_POST['email']);
                $username = trim($_POST['username']);
                
                // Check email
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->bind_param("si", $email, $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $errors[] = ['field' => 'email', 'message' => 'Email is already in use by another account'];
                }
                
                // Check username
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $stmt->bind_param("si", $username, $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $errors[] = ['field' => 'username', 'message' => 'Username is already taken'];
                }
            }
            
            // If no errors, update profile
            if (empty($errors)) {
                $firstName = trim($_POST['first_name']);
                $lastName = trim($_POST['last_name']);
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $dob = $_POST['dob'];
                
                $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ?, birthdate = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $firstName, $lastName, $username, $email, $dob, $userId);
                
                if ($stmt->execute()) {
                    // Log the activity
                    logUserActivity($userId, "Updated profile information", $conn);
                    
                    // Update session variables to reflect changes immediately
                    $_SESSION['first_name'] = $firstName;
                    $_SESSION['last_name'] = $lastName;
                    $_SESSION['email'] = $email;
                    
                    // Get updated user data to send back to client
                    $userData = [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'username' => $username,
                        'email' => $email,
                        'birthdate' => $dob
                    ];
                    
                    // Check if user has a profile photo
                    $photoQuery = $conn->prepare("SELECT profile_photo FROM users WHERE id = ?");
                    $photoQuery->bind_param("i", $userId);
                    $photoQuery->execute();
                    $photoResult = $photoQuery->get_result();
                    
                    if ($photoResult->num_rows === 1) {
                        $photoData = $photoResult->fetch_assoc();
                        if (!empty($photoData['profile_photo'])) {
                            $userData['profile_photo'] = $photoData['profile_photo'];
                            // Update session profile photo
                            $_SESSION['profile_photo'] = $photoData['profile_photo'];
                        }
                    }
                    
                    $response['success'] = true;
                    $response['message'] = 'Profile updated successfully';
                    $response['userData'] = $userData;
                } else {
                    $response['message'] = 'Failed to update profile: ' . $conn->error;
                }
            } else {
                $response['errors'] = $errors;
            }
        }
        // Handle password change
        elseif ($formType === 'change_password') {
            // Validate required fields
            $requiredFields = ['current_password', 'new_password', 'confirm_password'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    $fieldName = str_replace(['_', '-'], ' ', $field);
                    $fieldId = str_replace('_', '-', $field);
                    $errors[] = ['field' => $fieldId, 'message' => ucfirst($fieldName) . ' is required'];
                }
            }
            
            // Continue validation if required fields are present
            if (empty($errors)) {
                $currentPassword = trim($_POST['current_password']);
                $newPassword = trim($_POST['new_password']);
                $confirmPassword = trim($_POST['confirm_password']);
                
                // Verify current password
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    
                    // Check if password is valid using password_verify or a fallback for older passwords
                    $validPassword = false;
                    
                    // First try standard password_verify
                    if (password_verify($currentPassword, $user['password'])) {
                        $validPassword = true;
                    } 
                    // Fallback for older passwords that might use different hashing
                    elseif ($user['password'] === md5($currentPassword)) {
                        $validPassword = true;
                    }
                    
                    if (!$validPassword) {
                        $errors[] = ['field' => 'current-password', 'message' => 'Current password is incorrect'];
                    }
                } else {
                    $response['message'] = 'User not found';
                    echo json_encode($response);
                    exit;
                }
                
                // Validate new password
                if (strlen($newPassword) < 8) {
                    $errors[] = ['field' => 'new-password', 'message' => 'Password must be at least 8 characters'];
                } else {
                    // Check password strength (at least 3 of: lowercase, uppercase, numbers, special chars)
                    $strength = 0;
                    if (preg_match('/[a-z]/', $newPassword)) $strength++;
                    if (preg_match('/[A-Z]/', $newPassword)) $strength++;
                    if (preg_match('/[0-9]/', $newPassword)) $strength++;
                    if (preg_match('/[^a-zA-Z0-9]/', $newPassword)) $strength++;
                    
                    if ($strength < 3) {
                        $errors[] = ['field' => 'new-password', 'message' => 'Password must contain at least 3 of the following: lowercase letters, uppercase letters, numbers, and special characters'];
                    }
                }
                
                // Check if passwords match
                if ($newPassword !== $confirmPassword) {
                    $errors[] = ['field' => 'confirm-password', 'message' => 'Passwords do not match'];
                }
            }
            
            // If no errors, update password
            if (empty($errors)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashedPassword, $userId);
                
                if ($stmt->execute()) {
                    // Log the activity
                    logUserActivity($userId, "Changed account password", $conn);
                    
                    $response['success'] = true;
                    $response['message'] = 'Password changed successfully';
                } else {
                    $response['message'] = 'Failed to update password: ' . $conn->error;
                }
            } else {
                $response['errors'] = $errors;
            }
        }
        // Handle account deactivation
        elseif ($formType === 'deactivate_account') {
            try {
                $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
                $stmt->bind_param("i", $userId);
                
                if ($stmt->execute()) {
                    // Log the activity
                    logUserActivity($userId, "Deactivated account", $conn);
                    
                    // Destroy session
                    session_unset();
                    session_destroy();
                    
                    $response['success'] = true;
                    $response['message'] = 'Account deactivated successfully';
                } else {
                    $response['message'] = 'Failed to deactivate account: ' . $conn->error;
                }
            } catch (Exception $e) {
                $response['message'] = 'An error occurred: ' . $e->getMessage();
            }
        }
        // Handle account deletion
        elseif ($formType === 'delete_account') {
            try {
                // Include activity logger
                require_once '../includes/activity_logger.php';
                
                // Get user email for logging
                $emailStmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
                $emailStmt->bind_param("i", $userId);
                $emailStmt->execute();
                $emailResult = $emailStmt->get_result();
                $userData = $emailResult->fetch_assoc();
                $userEmail = $userData['email'];
                $emailStmt->close();
                
                // Start a transaction to ensure data consistency
                $conn->begin_transaction();
                
                // First, mark all the user's articles as not visible
                $articleStmt = $conn->prepare("UPDATE articles SET is_visible = 0 WHERE user_id = ?");
                $articleStmt->bind_param("i", $userId);
                $articleStmt->execute();
                
                // Then soft delete the account - mark as deleted and inactive
                $userStmt = $conn->prepare("UPDATE users SET is_deleted = 1, is_active = 0 WHERE id = ?");
                $userStmt->bind_param("i", $userId);
                
                if ($userStmt->execute()) {
                    // Log the activity - user soft deleted their own account
                    logUserSoftDelete($userId, $userId, $userEmail);
                    
                    // Commit the transaction
                    $conn->commit();
                    
                    // Destroy session
                    session_unset();
                    session_destroy();
                    
                    $response['success'] = true;
                    $response['message'] = 'Account deleted successfully';
                } else {
                    // Rollback transaction if user update fails
                    $conn->rollback();
                    $response['message'] = 'Failed to delete account: ' . $conn->error;
                }
            } catch (Exception $e) {
                // Rollback transaction on any error
                $conn->rollback();
                $response['message'] = 'An error occurred: ' . $e->getMessage();
            }
        }
        // Handle profile photo update
        elseif ($formType === 'update_profile_photo') {
            // Make sure we're sending a JSON response
            header('Content-Type: application/json');
            
            // Check if file was uploaded
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_photo']['name'];
                $filesize = $_FILES['profile_photo']['size'];
                $fileTmp = $_FILES['profile_photo']['tmp_name'];
                
                // Get file extension
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                // Check if file format is allowed
                if (!in_array($ext, $allowed)) {
                    $response['message'] = 'Invalid file format. Allowed formats: JPG, JPEG, PNG, GIF';
                    echo json_encode($response);
                    exit;
                }
                
                // Check file size (max 5MB)
                if ($filesize > 5 * 1024 * 1024) {
                    $response['message'] = 'File size too large. Maximum size is 5MB';
                    echo json_encode($response);
                    exit;
                }
                
                try {
                    // Generate unique filename
                    $newFilename = 'user_' . $userId . '_' . time() . '.' . $ext;
                    $uploadDir = __DIR__ . '/../assets/images/profiles/';
                    $uploadPath = $uploadDir . $newFilename;
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($uploadDir)) {
                        if (!mkdir($uploadDir, 0755, true)) {
                            throw new Exception("Failed to create upload directory");
                        }
                    }
                    
                    // Check if directory is writable
                    if (!is_writable($uploadDir)) {
                        throw new Exception("Upload directory is not writable");
                    }
                    
                    // Move the uploaded file
                    if (move_uploaded_file($fileTmp, $uploadPath)) {
                        // Also copy to user/assets/images/profiles for direct URL access
                        $userAssetsDir = __DIR__ . '/../user/assets/images/profiles/';
                        if (!file_exists($userAssetsDir)) {
                            if (!mkdir($userAssetsDir, 0755, true)) {
                                error_log("Failed to create user assets directory - continuing anyway");
                            }
                        }
                        
                        // Try to copy the file to user/assets directory
                        if (is_writable($userAssetsDir)) {
                            copy($uploadPath, $userAssetsDir . $newFilename);
                        }
                        
                        // Update user profile in database
                        $stmt = $conn->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                        $stmt->bind_param("si", $newFilename, $userId);
                        
                        if ($stmt->execute()) {
                            // Update session variable
                            $_SESSION['profile_photo'] = $newFilename;
                            
                            // Log the activity
                            logUserActivity($userId, "Updated profile photo", $conn);
                            
                            // Generate proper URL for use in the client
                            // Since the client is in the view/ directory, use the correct relative path
                            $photoUrl = '../assets/images/profiles/' . $newFilename;
                            
                            $response['success'] = true;
                            $response['message'] = 'Profile photo updated successfully';
                            $response['photo_url'] = $photoUrl;
                            $response['timestamp'] = time(); // Add timestamp for cache busting
                        } else {
                            throw new Exception('Failed to update profile photo in database: ' . $conn->error);
                        }
                    } else {
                        throw new Exception('Failed to upload file to ' . $uploadPath);
                    }
                } catch (Exception $e) {
                    // Log the error
                    error_log("Profile upload error: " . $e->getMessage());
                    $response['message'] = 'Error processing image: ' . $e->getMessage();
                }
            } else {
                $uploadError = $_FILES['profile_photo']['error'] ?? 'No file uploaded';
                $response['message'] = 'No image file was uploaded or an error occurred: ' . $uploadError;
            }
        }
        else {
            $response['message'] = 'Invalid form type';
        }
    } else {
        $response['message'] = 'Missing form type';
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // If not POST request, redirect to settings page
    header('Location: ../view/settings.php');
    exit;
}
?>