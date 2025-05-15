<?php
// Include the database connection
require_once '../config/connection.php';
require_once '../utils/profile_image.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get comments for an article
        if (isset($_GET['article_id'])) {
            $articleId = $conn->real_escape_string($_GET['article_id']);
            
            $query = "SELECT c.id, c.content, c.created_at, u.username, u.profile_photo, u.id as user_id, 
                      u.first_name, u.last_name 
                      FROM comments c 
                      JOIN users u ON c.user_id = u.id 
                      WHERE c.article_id = ? 
                      ORDER BY c.created_at DESC";
                      
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $articleId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $comments = [];
            while ($row = $result->fetch_assoc()) {
                // Get profile image (either user-uploaded or generated from initials)
                $profile_image = get_profile_image(
                    $row['profile_photo'],
                    $row['first_name'],
                    $row['last_name'],
                    40 // Smaller size for comments
                );
                
                $comments[] = [
                    'id' => $row['id'],
                    'content' => $row['content'],
                    'created_at' => $row['created_at'],
                    'username' => $row['username'],
                    'profile_image' => $profile_image,
                    'is_owner' => ($row['user_id'] == $_SESSION['user_id'])
                ];
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'comments' => $comments]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Article ID is required']);
        }
        break;
        
    case 'POST':
        // Add a new comment
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['article_id']) && isset($data['content']) && !empty($data['content'])) {
            $articleId = $conn->real_escape_string($data['article_id']);
            $content = $conn->real_escape_string($data['content']);
            $userId = $_SESSION['user_id'];
            
            $query = "INSERT INTO comments (article_id, user_id, content) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('iis', $articleId, $userId, $content);
            
            if ($stmt->execute()) {
                $commentId = $stmt->insert_id;
                
                // Get the newly created comment with user info
                $query = "SELECT c.id, c.content, c.created_at, u.username, u.profile_photo, u.id as user_id,
                          u.first_name, u.last_name
                          FROM comments c 
                          JOIN users u ON c.user_id = u.id 
                          WHERE c.id = ?";
                          
                $stmt = $conn->prepare($query);
                $stmt->bind_param('i', $commentId);
                $stmt->execute();
                $result = $stmt->get_result();
                $newComment = $result->fetch_assoc();
                
                // Get profile image
                $profile_image = get_profile_image(
                    $newComment['profile_photo'],
                    $newComment['first_name'],
                    $newComment['last_name'],
                    40 // Smaller size for comments
                );
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => 'Comment added successfully',
                    'comment' => [
                        'id' => $newComment['id'],
                        'content' => $newComment['content'],
                        'created_at' => $newComment['created_at'],
                        'username' => $newComment['username'],
                        'profile_image' => $profile_image,
                        'is_owner' => true
                    ]
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Article ID and comment content are required']);
        }
        break;
    
    case 'PUT':
        // Edit a comment
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['comment_id']) && isset($data['content']) && !empty($data['content'])) {
            $commentId = $conn->real_escape_string($data['comment_id']);
            $content = $conn->real_escape_string($data['content']);
            $userId = $_SESSION['user_id'];
            
            // Check if user owns the comment
            $checkQuery = "SELECT user_id FROM comments WHERE id = ?";
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param('i', $commentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $comment = $result->fetch_assoc();
            
            // Check if comment exists and user has permission
            if ($comment && $comment['user_id'] == $userId) {
                $updateQuery = "UPDATE comments SET content = ? WHERE id = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param('si', $content, $commentId);
                
                if ($stmt->execute()) {
                    // Get the updated comment
                    $query = "SELECT c.id, c.content, c.created_at, u.username, u.profile_photo, 
                              u.first_name, u.last_name
                              FROM comments c 
                              JOIN users u ON c.user_id = u.id 
                              WHERE c.id = ?";
                              
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('i', $commentId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $updatedComment = $result->fetch_assoc();
                    
                    // Get profile image
                    $profile_image = get_profile_image(
                        $updatedComment['profile_photo'],
                        $updatedComment['first_name'],
                        $updatedComment['last_name'],
                        40 // Smaller size for comments
                    );
                    
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Comment updated successfully',
                        'comment' => [
                            'id' => $updatedComment['id'],
                            'content' => $updatedComment['content'],
                            'created_at' => $updatedComment['created_at'],
                            'username' => $updatedComment['username'],
                            'profile_image' => $profile_image,
                            'is_owner' => true
                        ]
                    ]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Failed to update comment']);
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Not authorized to edit this comment']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Comment ID and content are required']);
        }
        break;
        
    case 'DELETE':
        // Delete a comment
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['comment_id'])) {
            $commentId = $conn->real_escape_string($data['comment_id']);
            $userId = $_SESSION['user_id'];
            
            // Check if user owns the comment or is admin
            $checkQuery = "SELECT user_id FROM comments WHERE id = ?";
            $stmt = $conn->prepare($checkQuery);
            $stmt->bind_param('i', $commentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $comment = $result->fetch_assoc();
            
            // Check if comment exists and user has permission
            if ($comment && ($comment['user_id'] == $userId || $_SESSION['role'] == 'admin')) {
                $deleteQuery = "DELETE FROM comments WHERE id = ?";
                $stmt = $conn->prepare($deleteQuery);
                $stmt->bind_param('i', $commentId);
                
                if ($stmt->execute()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Failed to delete comment']);
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Not authorized to delete this comment']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Comment ID is required']);
        }
        break;
        
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        break;
} 