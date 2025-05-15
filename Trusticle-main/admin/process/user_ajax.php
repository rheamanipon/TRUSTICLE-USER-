<?php
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the connection file
require_once '../../config/connection.php';
require_once '../../utils/validation.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Set content type header
header('Content-Type: application/json');

// Handle different AJAX requests
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'add_user':
        addUser($conn);
        break;
    case 'update_user':
        updateUser($conn);
        break;
    case 'update_role':
        updateRole($conn);
        break;
    case 'delete_user':
        deleteUser($conn);
        break;
    case 'get_users':
        getUsers($conn);
        break;
    case 'validate_password':
        validatePassword($conn);
        break;
    case 'validate_username':
        validateUsername($conn);
        break;
    case 'validate_email':
        validateEmail($conn);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

// Function to add a new user
function addUser($conn) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $dob = $_POST['dob'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    $errors = [];
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    
    // Validate username (alphanumeric and underscores only)
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores";
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($dob)) $errors[] = "Date of birth is required";
    if (empty($role)) $errors[] = "Role is required";
    
    // Password validation
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND is_deleted = 0");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Username already exists";
    }
    $stmt->close();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND is_deleted = 0");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    $stmt->close();
    
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, birthdate, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $first_name, $last_name, $username, $email, $dob, $hashed_password, $role);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            // Log the activity
            $action = "Created new user: $first_name $last_name";
            $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
            $admin_id = $_SESSION['user_id'];
            $stmt->bind_param("is", $admin_id, $action);
            $stmt->execute();
            
            // Success response
            echo json_encode([
                'status' => 'success', 
                'message' => "User added successfully"
            ]);
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => "Error adding user: " . $stmt->error
            ]);
        }
        $stmt->close();
    } else {
        // Return validation errors
        echo json_encode([
            'status' => 'error',
            'message' => implode("<br>", $errors)
        ]);
    }
}

// Function to update an existing user
function updateUser($conn) {
    $user_id = $_POST['user_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $dob = $_POST['dob'];
    $is_active = $_POST['is_active'];
    
    // Validate inputs
    $errors = [];
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    
    // Validate username (alphanumeric and underscores only)
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores";
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($dob)) $errors[] = "Date of birth is required";
    
    // Check if username already exists for other users
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ? AND is_deleted = 0");
    $stmt->bind_param("si", $username, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Username already exists";
    }
    $stmt->close();
    
    // Check if email already exists for other users
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ? AND is_deleted = 0");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    $stmt->close();
    
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ?, birthdate = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("sssssii", $first_name, $last_name, $username, $email, $dob, $is_active, $user_id);
        
        if ($stmt->execute()) {
            // Log the activity
            $action = "Updated user: $first_name $last_name (ID: $user_id)";
            $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
            $admin_id = $_SESSION['user_id'];
            $stmt->bind_param("is", $admin_id, $action);
            $stmt->execute();
            
            // Success response
            echo json_encode([
                'status' => 'success', 
                'message' => "User updated successfully"
            ]);
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => "Error updating user: " . $stmt->error
            ]);
        }
        $stmt->close();
    } else {
        // Return validation errors
        echo json_encode([
            'status' => 'error',
            'message' => implode("<br>", $errors)
        ]);
    }
}

// Function to update user role
function updateRole($conn) {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];
    
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $user_id);
    
    if ($stmt->execute()) {
        // Get user info for activity log
        $stmt_user = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $user_result = $stmt_user->get_result();
        $user_data = $user_result->fetch_assoc();
        
        // Log the activity
        $action = "Changed role of " . $user_data['first_name'] . " " . $user_data['last_name'] . " (ID: $user_id) to " . ucfirst($role);
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
        $admin_id = $_SESSION['user_id'];
        $stmt->bind_param("is", $admin_id, $action);
        $stmt->execute();
        
        // Success response
        echo json_encode([
            'status' => 'success', 
            'message' => "User role updated successfully"
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => "Error updating role: " . $stmt->error
        ]);
    }
    $stmt->close();
}

// Function to delete (soft delete) a user
function deleteUser($conn) {
    $user_id = $_POST['user_id'];
    
    // Get user info for activity log before deleting
    $stmt_user = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user_result = $stmt_user->get_result();
    $user_data = $result = $user_result->fetch_assoc();
    $stmt_user->close();
    
    if (!$user_data) {
        echo json_encode([
            'status' => 'error',
            'message' => "User not found"
        ]);
        return;
    }
    
    // Perform soft delete by setting is_deleted to 1 and is_active to 0
    $stmt = $conn->prepare("UPDATE users SET is_deleted = 1, is_active = 0 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        // Include activity logger
        require_once '../../includes/activity_logger.php';
        
        // Get admin ID from session
        $admin_id = $_SESSION['user_id'];
        
        // Log the soft delete action using the proper function
        logUserSoftDelete($admin_id, $user_id, $user_data['email']);
        
        // Success response
        echo json_encode([
            'status' => 'success', 
            'message' => "User deleted successfully"
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => "Error deleting user: " . $stmt->error
        ]);
    }
    $stmt->close();
}

// Function to get all users (with optional filtering)
function getUsers($conn) {
    $filter = isset($_POST['filter']) ? $_POST['filter'] : 'all';
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    
    // Base query
    $sql = "SELECT id, first_name, last_name, username, email, birthdate, role, is_active, created_at 
            FROM users 
            WHERE is_deleted = 0";
    
    // Add filtering if needed
    if ($filter !== 'all') {
        $sql .= " AND role = '$filter'";
    }
    
    // Add search if provided
    if (!empty($search)) {
        // Check if search is a date format
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $search)) {
            // If it's a date, search by created_at
            $date_search = date('Y-m-d', strtotime(str_replace('/', '-', $search)));
            $sql .= " AND DATE(created_at) = DATE(?)";
            
            // Add ordering
            $sql .= " ORDER BY id DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $date_search);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            // Regular search on other fields
            $search = '%' . $search . '%';
            $sql .= " AND (id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR username LIKE ?)";
            
            // Add ordering
            $sql .= " ORDER BY id DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $search, $search, $search, $search, $search);
            $stmt->execute();
            $result = $stmt->get_result();
        }
    } else {
        // No search term provided
        $sql .= " ORDER BY id DESC";
        $result = $conn->query($sql);
    }
    
    $users = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $fullName = $row['first_name'] . ' ' . $row['last_name'];
            $status = $row['is_active'] ? 'Active' : 'Inactive';
            $status_class = $row['is_active'] ? 'active-status' : 'inactive-status';
            $created_at = date('m/d/Y', strtotime($row['created_at']));
            
            $users[] = [
                'id' => $row['id'],
                'fullName' => htmlspecialchars($fullName),
                'email' => htmlspecialchars($row['email']),
                'username' => htmlspecialchars($row['username']),
                'created_at' => $created_at,
                'role' => $row['role'],
                'role_display' => ucfirst($row['role']),
                'status' => $status,
                'status_class' => $status_class,
                'first_name' => htmlspecialchars($row['first_name']),
                'last_name' => htmlspecialchars($row['last_name']),
                'birthdate' => $row['birthdate'],
                'is_active' => $row['is_active']
            ];
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $users
    ]);
}

// Function to validate password using validation.php
function validatePassword($conn) {
    if (!isset($_POST['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Password is required']);
        return;
    }
    
    $password = $_POST['password'];
    
    // Create validator with just the password
    $data = ['password' => $password];
    $validator = new Validator($data, $conn);
    
    // Validate using the same rules as registration
    $validator->required('password')
              ->minLength('password', 8)
              ->passwordStrength('password');
    
    if ($validator->fails()) {
        echo json_encode([
            'status' => 'error',
            'message' => implode("<br>", $validator->getErrors())
        ]);
    } else {
        echo json_encode(['status' => 'success']);
    }
}

// Function to validate username uniqueness
function validateUsername($conn) {
    if (!isset($_POST['username'])) {
        echo json_encode(['status' => 'error', 'message' => 'Username is required']);
        return;
    }
    
    $username = trim($_POST['username']);
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    // Validate username format (alphanumeric and underscores only)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo json_encode(['status' => 'error', 'message' => 'Username can only contain letters, numbers, and underscores']);
        return;
    }
    
    // Check minimum length
    if (strlen($username) < 4) {
        echo json_encode(['status' => 'error', 'message' => 'Username must be at least 4 characters long']);
        return;
    }
    
    // Check if username already exists
    if ($user_id > 0) {
        // For existing users (update case)
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ? AND is_deleted = 0");
        $stmt->bind_param("si", $username, $user_id);
    } else {
        // For new users (add case)
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND is_deleted = 0");
        $stmt->bind_param("s", $username);
    }
    
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
    } else {
        echo json_encode(['status' => 'success']);
    }
    
    $stmt->close();
}

// Function to validate email uniqueness
function validateEmail($conn) {
    if (!isset($_POST['email'])) {
        echo json_encode(['status' => 'error', 'message' => 'Email is required']);
        return;
    }
    
    $email = trim($_POST['email']);
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        return;
    }
    
    // Check if email already exists
    if ($user_id > 0) {
        // For existing users (update case)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ? AND is_deleted = 0");
        $stmt->bind_param("si", $email, $user_id);
    } else {
        // For new users (add case)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND is_deleted = 0");
        $stmt->bind_param("s", $email);
    }
    
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
    } else {
        echo json_encode(['status' => 'success']);
    }
    
    $stmt->close();
} 