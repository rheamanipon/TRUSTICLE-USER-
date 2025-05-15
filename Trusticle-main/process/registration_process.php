<?php
session_start();
require_once "../config/connection.php";
require_once "../utils/validation.php";
require_once "../utils/user.php";

header('Content-Type: application/json');
$response = array();

// Check if it's an AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize validator with POST data
    $validator = new Validator($_POST, $conn);
    
    // Validate form data using fluent interface for better readability (KISS principle)
    $validator->required('firstName', 'First name')
              ->required('lastName', 'Last name')
              ->required('username')
              ->minLength('username', 4)
              ->usernameFormat('username')
              ->required('email')
              ->email('email')
              ->required('birthdate', 'Birthdate')
              ->datePast('birthdate', 'Birthdate')
              ->required('password')
              ->minLength('password', 6)
              ->passwordStrength('password')
              ->passwordsMatch('password', 'confirmPassword')
              ->uniqueOrDeleted('username', 'users')
              ->uniqueOrDeleted('email', 'users');
    
    // If there are errors, return them
    if ($validator->fails()) {
        $response['success'] = false;
        $response['errors'] = $validator->getErrors();
        echo json_encode($response);
        exit();
    }
    
    // All validations passed, get sanitized values
    $firstName = $validator->getValue('firstName');
    $lastName = $validator->getValue('lastName');
    $username = $validator->getValue('username');
    $email = $validator->getValue('email');
    $birthdate = $validator->getValue('birthdate');
    $password = $validator->getValue('password');
    
    try {
        // Begin transaction for data integrity
        $conn->begin_transaction();
        
        // Check if there's a deleted user with this username or email
        $deletedUserByUsername = $validator->getDeletedUser('username', $username);
        $deletedUserByEmail = $validator->getDeletedUser('email', $email);
        
        // Hash password for secure storage
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        if ($deletedUserByUsername || $deletedUserByEmail) {
            // We found a deleted user account, reactivate it
            $userId = $deletedUserByUsername ? $deletedUserByUsername['id'] : $deletedUserByEmail['id'];
            
            // Begin transaction
            $conn->begin_transaction();
            
            // Update the deleted user with new information
            $stmt = $conn->prepare("UPDATE users SET 
                username = ?, 
                email = ?, 
                password = ?, 
                first_name = ?, 
                last_name = ?, 
                birthdate = ?,
                is_deleted = FALSE,
                is_active = TRUE,
                updated_at = NOW()
                WHERE id = ?");
                
            $stmt->bind_param("ssssssi", $username, $email, $hashedPassword, $firstName, $lastName, $birthdate, $userId);
            
            if ($stmt->execute()) {
                // Log activity for user reactivation
                logUserActivity($conn, $userId, "User account reactivated");
                
                // Commit transaction
                $conn->commit();
                
                $response['success'] = true;
                $response['message'] = "Account reactivated successfully! Please login. Note that previously created content will not be available.";
            } else {
                // Rollback transaction if something fails
                $conn->rollback();
                $response['success'] = false;
                $response['errors'] = ["Reactivation failed: " . $conn->error];
            }
            
        } else {
            // No deleted user found, create a new account
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, birthdate) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $email, $hashedPassword, $firstName, $lastName, $birthdate);
            
            if ($stmt->execute()) {
                $userId = $conn->insert_id;
                
                // Log activity for new user registration
                logUserActivity($conn, $userId, "User registered");
                
                // Commit transaction
                $conn->commit();
                
                $response['success'] = true;
                $response['message'] = "Registration successful! Please login.";
            } else {
                // Rollback transaction if something fails
                $conn->rollback();
                $response['success'] = false;
                $response['errors'] = ["Registration failed: " . $conn->error];
            }
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        // Rollback transaction on exception
        $conn->rollback();
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
