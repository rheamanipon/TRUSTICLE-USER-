<?php 
// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize notification message variables
$notification = [
    'type' => '',
    'message' => ''
];

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../../login.php");
    exit;
}

// Include the connection file if not already included
if (!isset($conn)) {
    require_once '../../config/connection.php';
}

// Handle Export - IMPORTANT: Do this before including any output-generating files
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    // Query to get all users
    $export_query = "SELECT id, first_name, last_name, username, email, birthdate, role, is_active, created_at 
                    FROM users 
                    WHERE is_deleted = 0 
                    ORDER BY id DESC";
    $export_result = $conn->query($export_query);
    
    if ($export_result && $export_result->num_rows > 0) {
        // Set headers for CSV download
        $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Create a file pointer
        $output = fopen('php://output', 'w');
        
        // Set column headers
        fputcsv($output, ['ID', 'First Name', 'Last Name', 'Username', 'Email', 'Birthdate', 'Role', 'Status', 'Date Registered']);
        
        // Output each row of the data
        while ($row = $export_result->fetch_assoc()) {
            $status = $row['is_active'] ? 'Active' : 'Inactive';
            $csv_row = [
                $row['id'],
                $row['first_name'],
                $row['last_name'],
                $row['username'],
                $row['email'],
                $row['birthdate'],
                ucfirst($row['role']),
                $status,
                date('m/d/Y', strtotime($row['created_at']))
            ];
            fputcsv($output, $csv_row);
        }
        
        // Close the file pointer
        fclose($output);
        exit;
    } else {
        // Set error notification if no results
        $notification = [
            'type' => 'error',
            'message' => "No data available to export"
        ];
    }
}

// Now include the header file that outputs HTML
include_once '../includes/header.php'; 
?>
<!-- Include validation JS utilities -->
<script src="../../assets/js/validation.js"></script>

<div class="container">
    <!-- Sidebar is included in the header.php file -->
    <div class="content-area">
        <div class="page-header">
            <h1 class="page-title">User Management</h1>
        </div>
        
        <!-- Notification container -->
        <div id="notification-container"></div>
        
        <div class="action-bar">
            <div class="search-container">
                <input type="text" id="search-input" class="search-input" placeholder="Search by id, name, email or date (mm/dd/yyyy)...">
                <button class="search-icon"><i class="fas fa-search"></i></button>
            </div>
            <div class="actions-container">
                <div class="filter-container">
                    <button id="filter-btn" class="btn btn-outline">
                        <i class="fas fa-filter"></i> <span>All</span>
                    </button>
                    <div id="filter-dropdown" class="filter-dropdown">
                        <div class="filter-option" data-filter="all">All</div>
                        <div class="filter-option" data-filter="admin">Admin</div>
                        <div class="filter-option" data-filter="user">User</div>
                    </div>
                </div>
                <button id="add-user-btn" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New User
                </button>
                <a href="user_management.php?export=csv" class="btn btn-primary">
                    <i class="fas fa-download"></i> Export
                </a>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Date Registered</th>
                        <th>Role</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">
                    <!-- User data will be loaded here via AJAX -->
                    <tr>
                        <td colspan="8" class="text-center">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="pagination">
            <a href="#" class="prev"><i class="fas fa-chevron-left"></i> Previous</a>
            <span id="pagination-numbers">
                <!-- Page numbers will be dynamically inserted here -->
            </span>
            <a href="#" class="next">Next <i class="fas fa-chevron-right"></i></a>
        </div>
    </div>
</div>

<!-- Modal for Adding Users -->
<div id="user-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add New User</h2>
        <br/>
        <form id="user-form">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" id="first-name" name="first_name" placeholder="First Name" required>
                </div>
                <div class="form-group">
                    <input type="text" id="last-name" name="last_name" placeholder="Last Name" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" id="username" name="username" placeholder="Username" required pattern="^[a-zA-Z0-9_]+$" title="Username can only contain letters, numbers, and underscores">
                </div>
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder="Email" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="date" id="dob" name="dob" placeholder="Date of Birth" required>
                </div>
                <div class="form-group">
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <div class="password-field-container">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <button type="button" class="toggle-password" tabindex="-1"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <div class="password-field-container">
                        <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm Password" required>
                        <button type="button" class="toggle-password" tabindex="-1"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="add-btn">Add User</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Editing Users -->
<div id="edit-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit User</h2>
        <br/>
        <form id="edit-form">
            <input type="hidden" id="edit-user-id" name="user_id">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" id="edit-first-name" name="first_name" placeholder="First Name" required>
                </div>
                <div class="form-group">
                    <input type="text" id="edit-last-name" name="last_name" placeholder="Last Name" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" id="edit-username" name="username" placeholder="Username" required pattern="^[a-zA-Z0-9_]+$" title="Username can only contain letters, numbers, and underscores">
                </div>
                <div class="form-group">
                    <input type="email" id="edit-email" name="email" placeholder="Email" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="date" id="edit-dob" name="dob" placeholder="Date of Birth" required>
                </div>
                <div class="form-group">
                    <select id="edit-active" name="is_active" class="active-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="add-btn">Update User</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Role Change -->
<div id="role-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Change User Role</h2>
        <br/>
        <form id="role-form">
            <input type="hidden" id="role-user-id" name="user_id">
            <div class="form-group">
                <p id="role-user-name" class="user-info-text"></p>
            </div>
            <div class="form-group">
                <select id="role-change" name="role" class="role-select">
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="add-btn">Update Role</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Delete Confirmation -->
<div id="delete-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Delete User</h2>
        <br/>
        <p>Are you sure you want to delete this user? This action cannot be undone.</p>
        <form id="delete-form">
            <input type="hidden" id="delete-user-id" name="user_id">
            <div class="form-group">
                <button type="submit" class="add-btn delete-action">Delete User</button>
                <button type="button" class="add-btn cancel-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Include JavaScript for AJAX operations -->
<script>
$(document).ready(function() {
    // Display notification if set by the PHP
    <?php if (!empty($notification['type']) && !empty($notification['message'])): ?>
        showNotification('<?php echo $notification['type']; ?>', '<?php echo addslashes($notification['message']); ?>');
    <?php endif; ?>
    
    // Function to show notifications
    function showNotification(type, message) {
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        const notificationHtml = `
            <div class="notification ${type}">
                <i class="fas ${iconClass}"></i>
                <span>${message}</span>
            </div>
        `;
        
        $('#notification-container').html(notificationHtml);
        
        // Auto hide after 5 seconds
        setTimeout(function() {
            $('.notification').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Handle password visibility toggle
    $('.toggle-password').on('click', function() {
        const passwordField = $(this).siblings('input');
        const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        
        // Change the eye icon
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });
    
    // Define form utility functions if not already defined
    if (typeof formUtils === 'undefined') {
        window.formUtils = {
            showError: function($element, message) {
                // Remove existing error message if any
                this.clearFieldError($element);
                
                // Add error class to element
                $element.addClass('input-error');
                
                // Add error message after element
                const errorMessage = $('<div class="error-message"><i class="fas fa-exclamation-circle"></i> ' + message + '</div>');
                $element.after(errorMessage);
            },
            
            clearFieldError: function($element) {
                // Remove error class and message
                $element.removeClass('input-error input-valid');
                const nextEl = $element.next();
                if (nextEl.hasClass('error-message') || nextEl.hasClass('success-message')) {
                    nextEl.remove();
                }
            },
            
            markFieldValid: function($element) {
                $element.addClass('input-valid');
            },
            
            clearErrors: function() {
                $('.error-message, .success-message').remove();
                $('input, select').removeClass('input-error input-valid');
            },
            
            showPasswordStrength: function($element) {
                // Remove existing strength indicator
                $element.parent().find('.password-strength').remove();
                
                const password = $element.val();
                if (!password) return;
                
                let strength = 0;
                let strengthClass = '';
                let strengthText = '';
                
                // Check for lowercase letters
                if (password.match(/[a-z]/)) strength++;
                
                // Check for uppercase letters
                if (password.match(/[A-Z]/)) strength++;
                
                // Check for numbers
                if (password.match(/[0-9]/)) strength++;
                
                // Check for special characters
                if (password.match(/[^a-zA-Z0-9]/)) strength++;
                
                // Check length
                if (password.length < 8) {
                    strengthClass = 'weak';
                    strengthText = 'Weak (too short)';
                } else if (strength === 1) {
                    strengthClass = 'weak';
                    strengthText = 'Weak';
                } else if (strength === 2) {
                    strengthClass = 'medium';
                    strengthText = 'Medium';
                } else if (strength >= 3) {
                    strengthClass = 'strong';
                    strengthText = 'Strong';
                }
                
                const strengthIndicator = $('<div class="password-strength ' + strengthClass + '">' + strengthText + '</div>');
                $element.after(strengthIndicator);
            },
            
            setupErrorClearingEvents: function() {
                // Clear errors when focusing on an input
                $('input, select').on('focus', function() {
                    formUtils.clearFieldError($(this));
                });
            }
        };
    }
    
    // Add user form field validation
    function setupUserFormValidation() {
        // First name validation
        $('#first-name').on('blur', function() {
            const value = $(this).val().trim();
            const error = validators.required(value, 'First name');
            if (error) {
                formUtils.showError($(this), error);
            }
        });
        
        // Last name validation
        $('#last-name').on('blur', function() {
            const value = $(this).val().trim();
            const error = validators.required(value, 'Last name');
            if (error) {
                formUtils.showError($(this), error);
            }
        });
        
        // Username validation
        $('#username').on('blur', function() {
            const value = $(this).val().trim();
            
            if (!value) {
                formUtils.showError($(this), 'Username is required');
                return;
            }
            
            if (value.length < 4) {
                formUtils.showError($(this), 'Username must be at least 4 characters long');
                return;
            }
            
            const formatError = validators.usernameFormat(value);
            if (formatError) {
                formUtils.showError($(this), formatError);
                return;
            }
            
            // Check for duplicates with server-side validation
            $.ajax({
                url: '../process/user_ajax.php',
                type: 'POST',
                data: {
                    action: 'validate_username',
                    username: value
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'error') {
                        formUtils.showError($('#username'), response.message);
                    } else {
                        formUtils.clearFieldError($('#username'));
                        formUtils.markFieldValid($('#username'));
                        // Show a small checkmark or success message
                        const successMessage = $('<div class="success-message"><i class="fas fa-check-circle"></i> Username available</div>');
                        $('#username').after(successMessage);
                        setTimeout(function() {
                            successMessage.fadeOut('slow', function() {
                                $(this).remove();
                            });
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);
                }
            });
        });
        
        // Email validation
        $('#email').on('blur', function() {
            const value = $(this).val().trim();
            
            if (!value) {
                formUtils.showError($(this), 'Email is required');
                return;
            }
            
            const emailError = validators.email(value);
            if (emailError) {
                formUtils.showError($(this), emailError);
                return;
            }
            
            // Check for duplicates with server-side validation
            $.ajax({
                url: '../process/user_ajax.php',
                type: 'POST',
                data: {
                    action: 'validate_email',
                    email: value
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'error') {
                        formUtils.showError($('#email'), response.message);
                    } else {
                        formUtils.clearFieldError($('#email'));
                        formUtils.markFieldValid($('#email'));
                        // Show a small checkmark or success message
                        const successMessage = $('<div class="success-message"><i class="fas fa-check-circle"></i> Email available</div>');
                        $('#email').after(successMessage);
                        setTimeout(function() {
                            successMessage.fadeOut('slow', function() {
                                $(this).remove();
                            });
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);
                }
            });
        });
        
        // Date of birth validation
        $('#dob').on('blur', function() {
            const value = $(this).val().trim();
            
            if (!value) {
                formUtils.showError($(this), 'Date of birth is required');
                return;
            }
            
            const dateError = validators.datePast(value, 'Date of birth');
            if (dateError) {
                formUtils.showError($(this), dateError);
            }
        });
        
        // Role validation
        $('#role').on('blur', function() {
            const value = $(this).val();
            
            if (!value) {
                formUtils.showError($(this), 'Role is required');
            }
        });
        
        // Password validation with strength indicator
        $('#password').on('blur', function() {
            const value = $(this).val().trim();
            
            if (!value) {
                formUtils.showError($(this), 'Password is required');
                return;
            }
            
            // Use server-side validation with validation.php
            $.ajax({
                url: '../process/user_ajax.php',
                type: 'POST',
                data: {
                    action: 'validate_password',
                    password: value
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'error') {
                        formUtils.showError($('#password'), response.message);
                    } else {
                        formUtils.clearFieldError($('#password'));
                        formUtils.markFieldValid($('#password'));
                        // Show a small checkmark or success message
                        const successMessage = $('<div class="success-message"><i class="fas fa-check-circle"></i> Password meets requirements</div>');
                        $('#password').after(successMessage);
                        setTimeout(function() {
                            successMessage.fadeOut('slow', function() {
                                $(this).remove();
                            });
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);
                }
            });
        });
        
        // Show password strength on input
        $('#password').on('input', function() {
            formUtils.showPasswordStrength($(this));
        });
        
        // Confirm password validation
        $('#confirm-password').on('blur', function() {
            const value = $(this).val().trim();
            const password = $('#password').val().trim();
            
            if (!value) {
                formUtils.showError($(this), 'Confirm password is required');
                return;
            }
            
            if (password && password !== value) {
                formUtils.showError($(this), 'Passwords do not match');
            } else if (password && password === value) {
                // Show success message for password match
                formUtils.clearFieldError($('#confirm-password'));
                formUtils.markFieldValid($('#confirm-password'));
                // Only show success message if not already shown
                if (!$('#confirm-password').next('.success-message').length) {
                    const successMessage = $('<div class="success-message"><i class="fas fa-check-circle"></i> Passwords match</div>');
                    $('#confirm-password').after(successMessage);
                    setTimeout(function() {
                        successMessage.fadeOut('slow', function() {
                            $(this).remove();
                        });
                    }, 3000);
                }
            }
        });
    }
    
    // Setup form field validation for edit form as well
    function setupEditFormValidation() {
        // First name validation
        $('#edit-first-name').on('blur', function() {
            const value = $(this).val().trim();
            const error = validators.required(value, 'First name');
            if (error) {
                formUtils.showError($(this), error);
            }
        });
        
        // Last name validation
        $('#edit-last-name').on('blur', function() {
            const value = $(this).val().trim();
            const error = validators.required(value, 'Last name');
            if (error) {
                formUtils.showError($(this), error);
            }
        });
        
        // Username validation
        $('#edit-username').on('blur', function() {
            const value = $(this).val().trim();
            
            if (!value) {
                formUtils.showError($(this), 'Username is required');
                return;
            }
            
            if (value.length < 4) {
                formUtils.showError($(this), 'Username must be at least 4 characters long');
                return;
            }
            
            const formatError = validators.usernameFormat(value);
            if (formatError) {
                formUtils.showError($(this), formatError);
                return;
            }
            
            // Check for duplicates with server-side validation (in edit form)
            $.ajax({
                url: '../process/user_ajax.php',
                type: 'POST',
                data: {
                    action: 'validate_username',
                    username: value,
                    user_id: $('#edit-user-id').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'error') {
                        formUtils.showError($('#edit-username'), response.message);
                    } else {
                        formUtils.clearFieldError($('#edit-username'));
                        formUtils.markFieldValid($('#edit-username'));
                        // Show a small checkmark or success message
                        const successMessage = $('<div class="success-message"><i class="fas fa-check-circle"></i> Username available</div>');
                        $('#edit-username').after(successMessage);
                        setTimeout(function() {
                            successMessage.fadeOut('slow', function() {
                                $(this).remove();
                            });
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);
                }
            });
        });
        
        // Email validation
        $('#edit-email').on('blur', function() {
            const value = $(this).val().trim();
            
            if (!value) {
                formUtils.showError($(this), 'Email is required');
                return;
            }
            
            const emailError = validators.email(value);
            if (emailError) {
                formUtils.showError($(this), emailError);
                return;
            }
            
            // Check for duplicates with server-side validation
            $.ajax({
                url: '../process/user_ajax.php',
                type: 'POST',
                data: {
                    action: 'validate_email',
                    email: value,
                    user_id: $('#edit-user-id').val()
                },
                dataType: 'json',
                async: false,  // Make this synchronous to wait for validation
                success: function(response) {
                    if (response.status === 'error') {
                        formUtils.showError($('#edit-email'), response.message);
                        showNotification('error', response.message);
                        hasErrors = true;
                    } else {
                        formUtils.clearFieldError($('#edit-email'));
                        formUtils.markFieldValid($('#edit-email'));
                        // Show a small checkmark or success message
                        const successMessage = $('<div class="success-message"><i class="fas fa-check-circle"></i> Email available</div>');
                        $('#edit-email').after(successMessage);
                        setTimeout(function() {
                            successMessage.fadeOut('slow', function() {
                                $(this).remove();
                            });
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);
                    showNotification('error', 'Server error: ' + error);
                    hasErrors = true;
                }
            });
        });
        
        // Date of birth validation
        $('#edit-dob').on('blur', function() {
            const value = $(this).val().trim();
            
            if (!value) {
                formUtils.showError($(this), 'Date of birth is required');
                return;
            }
            
            const dateError = validators.datePast(value, 'Date of birth');
            if (dateError) {
                formUtils.showError($(this), dateError);
            }
        });
    }
    
    // Call the setup function for validation
    setupUserFormValidation();
    setupEditFormValidation();
    
    // Setup error clearing on input focus
    formUtils.setupErrorClearingEvents();
    
    // Pagination variables
    const itemsPerPage = 10;
    let currentPage = 1;
    let totalPages = 1;
    let allUsers = [];
    let filteredUsers = [];
    
    // Function to load users via AJAX
    function loadUsers(filter = 'all', search = '') {
        $.ajax({
            url: '../process/user_ajax.php',
            type: 'POST',
            data: {
                action: 'get_users',
                filter: filter,
                search: search
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    allUsers = response.data;
                    filteredUsers = [...allUsers];
                    renderUsers();
                } else {
                    showNotification('error', response.message || 'Error loading users');
                }
            },
            error: function(xhr, status, error) {
                showNotification('error', 'Server error: ' + error);
                console.error(xhr, status, error);
            }
        });
    }
    
    // Function to render users with pagination
    function renderUsers() {
        const $tableBody = $('#users-table-body');
        $tableBody.empty();
        
        totalPages = Math.max(1, Math.ceil(filteredUsers.length / itemsPerPage));
        
        // Ensure current page is valid
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }
        
        // Calculate start and end indices for current page
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, filteredUsers.length);
        
        // Check if there are users to display
        if (filteredUsers.length === 0) {
            $tableBody.html('<tr><td colspan="8" class="text-center">No users found</td></tr>');
            updatePaginationUI();
            return;
        }
        
        // Generate table rows for current page
        for (let i = startIndex; i < endIndex; i++) {
            const user = filteredUsers[i];
            const row = `
                <tr>
                    <td>${user.id}</td>
                    <td>${user.fullName}</td>
                    <td>${user.email}</td>
                    <td>${user.username}</td>
                    <td>${user.created_at}</td>
                    <td data-role="${user.role}">
                        <span class="role-badge role-${user.role}">${user.role_display}</span>
                    </td>
                    <td class="${user.status_class}">${user.status}</td>
                    <td>
                        <div class="action-icons">
                            <i class="fas fa-edit action-icon edit-icon" 
                               data-id="${user.id}" 
                               data-firstname="${user.first_name}"
                               data-lastname="${user.last_name}"
                               data-username="${user.username}"
                               data-email="${user.email}"
                               data-dob="${user.birthdate}"
                               data-active="${user.is_active}"></i>
                            <i class="fas fa-trash action-icon delete-icon" data-id="${user.id}"></i>
                            <i class="fas fa-user-cog action-icon role-icon" 
                               data-id="${user.id}" 
                               data-name="${user.fullName}"
                               data-role="${user.role}"></i>
                        </div>
                    </td>
                </tr>
            `;
            $tableBody.append(row);
        }
        
        // Update pagination UI
        updatePaginationUI();
    }
    
    // Update pagination numbers and states
    function updatePaginationUI() {
        const $pagination = $('.pagination');
        const $pageNumbers = $('#pagination-numbers');
        
        // Clear existing page numbers
        $pageNumbers.empty();
        
        // Determine range of pages to show
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, startPage + 4);
        
        // Add page numbers
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === currentPage ? 'active' : '';
            $pageNumbers.append(`<a href="#" class="${activeClass}" data-page="${i}">${i}</a>`);
        }
        
        // Update previous and next buttons state
        $('.pagination .prev').toggleClass('disabled', currentPage === 1);
        $('.pagination .next').toggleClass('disabled', currentPage === totalPages || totalPages === 0);
    }
    
    // Function to filter users on the client side
    function filterUsers(filterValue) {
        if (filterValue === 'all') {
            filteredUsers = [...allUsers];
        } else {
            filteredUsers = allUsers.filter(user => user.role === filterValue);
        }
        
        currentPage = 1;
        renderUsers();
        
        // Save filter preference in sessionStorage
        sessionStorage.setItem('userManagementFilter', filterValue);
    }
    
    // Search function on the client side
    function handleSearch() {
        const searchTerm = $('#search-input').val().toLowerCase();
        
        if (searchTerm === '') {
            // If search is cleared, reapply filter only
            const currentFilter = $('#filter-btn span').text().trim().toLowerCase();
            if (currentFilter === 'all') {
                filteredUsers = [...allUsers];
            } else {
                filteredUsers = allUsers.filter(user => user.role === currentFilter);
            }
        } else {
            // Apply both search and filter
            const currentFilter = $('#filter-btn span').text().trim().toLowerCase();
            
            // Check if the search term is a date format (mm/dd/yyyy)
            const isDateSearch = /^\d{1,2}\/\d{1,2}\/\d{4}$/.test(searchTerm);
            
            filteredUsers = allUsers.filter(user => {
                const matchesFilter = currentFilter === 'all' || user.role === currentFilter;
                
                let matchesSearch;
                
                if (isDateSearch) {
                    // If searching for a date, check the created_at field
                    matchesSearch = user.created_at.toLowerCase().includes(searchTerm);
                } else {
                    // Otherwise check all other fields
                    matchesSearch = 
                        user.id.toString().includes(searchTerm) ||
                        user.fullName.toLowerCase().includes(searchTerm) ||
                        user.email.toLowerCase().includes(searchTerm) ||
                        user.username.toLowerCase().includes(searchTerm);
                }
                
                return matchesSearch && matchesFilter;
            });
        }
        
        currentPage = 1;
        renderUsers();
        
        // Save search term in sessionStorage
        sessionStorage.setItem('userManagementSearch', searchTerm);
    }
    
    // Initial load of users
    loadUsers();
    
    // Load saved filter and search if available
    if (sessionStorage.getItem('userManagementFilter')) {
        const savedFilter = sessionStorage.getItem('userManagementFilter');
        $('#filter-btn span').text(savedFilter.charAt(0).toUpperCase() + savedFilter.slice(1));
        
        // We'll reapply the filter after users are loaded
        $(document).ajaxComplete(function(event, xhr, settings) {
            if (settings.url === '../process/user_ajax.php' && settings.data.includes('action=get_users')) {
                filterUsers(savedFilter);
                // Remove this handler after first execution
                $(document).off('ajaxComplete', arguments.callee);
            }
        });
    }
    
    if (sessionStorage.getItem('userManagementSearch')) {
        const savedSearch = sessionStorage.getItem('userManagementSearch');
        $('#search-input').val(savedSearch);
        
        // We'll reapply the search after users are loaded
        $(document).ajaxComplete(function(event, xhr, settings) {
            if (settings.url === '../process/user_ajax.php' && settings.data.includes('action=get_users')) {
                handleSearch();
                // Remove this handler after first execution
                $(document).off('ajaxComplete', arguments.callee);
            }
        });
    }
    
    // Handle pagination clicks
    $('.pagination').on('click', 'a:not(.disabled)', function(e) {
        e.preventDefault();
        
        if ($(this).hasClass('prev')) {
            currentPage--;
        } else if ($(this).hasClass('next')) {
            currentPage++;
        } else {
            currentPage = parseInt($(this).data('page'));
        }
        
        renderUsers();
        
        // Save current page in sessionStorage
        sessionStorage.setItem('userManagementPage', currentPage);
    });
    
    // Restore saved page if available
    if (sessionStorage.getItem('userManagementPage')) {
        currentPage = parseInt(sessionStorage.getItem('userManagementPage'));
        
        // We'll reapply the page after users are loaded
        $(document).ajaxComplete(function(event, xhr, settings) {
            if (settings.url === '../process/user_ajax.php' && settings.data.includes('action=get_users')) {
                renderUsers();
                // Remove this handler after first execution
                $(document).off('ajaxComplete', arguments.callee);
            }
        });
    }
    
    // Search input handling
    $('#search-input').on('keyup', function() {
        handleSearch();
    });
    
    // Show filter dropdown
    $('#filter-btn').click(function(e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent the document click handler from immediately closing it
        $('#filter-dropdown').toggle(); // Use toggle() instead of toggleClass('show')
    });
    
    // Close filter dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.filter-container').length) {
            $('#filter-dropdown').hide(); // Use hide() instead of removeClass('show')
        }
    });
    
    // Handle filter options click
    $('.filter-option').on('click', function() {
        const filterValue = $(this).data('filter');
        $('#filter-btn span').text($(this).text());
        $('#filter-dropdown').hide(); // Use hide() instead of removeClass('show')
        filterUsers(filterValue);
    });
    
    // Show add user modal
    $('#add-user-btn').click(function() {
        // Clear previous form inputs and errors
        $('#user-form')[0].reset();
        formUtils.clearErrors();
        $('.error-message, .success-message').remove();
        $('input').removeClass('input-error input-valid');
        $('#user-modal').css('display', 'block');
    });
    
    // Prevent modals from closing when clicking outside
    $('.modal').on('click', function(e) {
        if (e.target === this) {
            e.stopPropagation();
            // Never close the modal when clicking outside
            const $modalContent = $(this).find('.modal-content');
            $modalContent.css('animation', 'shake 0.5s');
            setTimeout(function() {
                $modalContent.css('animation', '');
            }, 500);
        }
    });
    
    // Close modals when clicking on X or cancel button only
    $('.close, .cancel-btn').click(function() {
        $('.modal').css('display', 'none');
    });
    
    // Show edit modal with user data
    $(document).on('click', '.edit-icon', function() {
        // Clear previous errors
        formUtils.clearErrors();
        $('.error-message, .success-message').remove();
        $('input').removeClass('input-error input-valid');
        
        const userId = $(this).data('id');
        const firstName = $(this).data('firstname');
        const lastName = $(this).data('lastname');
        const username = $(this).data('username');
        const email = $(this).data('email');
        const dob = $(this).data('dob');
        const isActive = $(this).data('active');
        
        $('#edit-user-id').val(userId);
        $('#edit-first-name').val(firstName);
        $('#edit-last-name').val(lastName);
        $('#edit-username').val(username);
        $('#edit-email').val(email);
        $('#edit-dob').val(dob);
        $('#edit-active').val(isActive);
        
        $('#edit-modal').css('display', 'block');
    });
    
    // Show role change modal
    $(document).on('click', '.role-icon', function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        const role = $(this).data('role');
        
        $('#role-user-id').val(userId);
        $('#role-user-name').text(userName);
        $('#role-change').val(role);
        
        $('#role-modal').css('display', 'block');
    });
    
    // Show delete confirmation modal
    $(document).on('click', '.delete-icon', function() {
        const userId = $(this).data('id');
        $('#delete-user-id').val(userId);
        $('#delete-modal').css('display', 'block');
    });
    
    // Handle Add User form submission with validation
    $('#user-form').submit(function(e) {
        e.preventDefault();
        
        // Clear previous errors
        formUtils.clearErrors();
        
        // Get form data
        const formData = new FormData(this);
        const data = {
            first_name: $('#first-name').val().trim(),
            last_name: $('#last-name').val().trim(),
            username: $('#username').val().trim(),
            email: $('#email').val().trim(),
            dob: $('#dob').val().trim(),
            role: $('#role').val(),
            password: $('#password').val(),
            confirm_password: $('#confirm-password').val()
        };
        
        // Validate form data
        let hasErrors = false;
        
        // Check required fields
        const requiredFields = [
            { name: 'first_name', label: 'First name', element: $('#first-name') },
            { name: 'last_name', label: 'Last name', element: $('#last-name') },
            { name: 'username', label: 'Username', element: $('#username') },
            { name: 'email', label: 'Email', element: $('#email') },
            { name: 'dob', label: 'Date of birth', element: $('#dob') },
            { name: 'role', label: 'Role', element: $('#role') },
            { name: 'password', label: 'Password', element: $('#password') },
            { name: 'confirm_password', label: 'Confirm password', element: $('#confirm-password') }
        ];
        
        requiredFields.forEach(field => {
            if (!data[field.name]) {
                formUtils.showError(field.element, `${field.label} is required`);
                hasErrors = true;
            }
        });
        
        // Additional validations if initial requirements are met
        if (data.username && !hasErrors) {
            if (data.username.length < 4) {
                formUtils.showError($('#username'), 'Username must be at least 4 characters long');
                hasErrors = true;
            } else {
                const formatError = validators.usernameFormat(data.username);
                if (formatError) {
                    formUtils.showError($('#username'), formatError);
                    hasErrors = true;
                }
            }
        }
        
        if (data.email && !hasErrors) {
            const emailError = validators.email(data.email);
            if (emailError) {
                formUtils.showError($('#email'), emailError);
                hasErrors = true;
            }
        }
        
        if (data.dob && !hasErrors) {
            const dateError = validators.datePast(data.dob, 'Date of birth');
            if (dateError) {
                formUtils.showError($('#dob'), dateError);
                hasErrors = true;
            }
        }
        
        // Check if passwords match
        if (data.password && data.confirm_password && data.password !== data.confirm_password) {
            formUtils.showError($('#confirm-password'), 'Passwords do not match');
            hasErrors = true;
        } else if (data.password && data.confirm_password && data.password === data.confirm_password) {
            // Show success message for password match
            formUtils.clearFieldError($('#confirm-password'));
            formUtils.markFieldValid($('#confirm-password'));
            // Only show success message if not already shown
            if (!$('#confirm-password').next('.success-message').length) {
                const successMessage = $('<div class="success-message"><i class="fas fa-check-circle"></i> Passwords match</div>');
                $('#confirm-password').after(successMessage);
                setTimeout(function() {
                    successMessage.fadeOut('slow', function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        }
        
        if (hasErrors) {
            // Show notification with validation error summary
            let errorMessages = [];
            $('.error-message').each(function() {
                errorMessages.push($(this).text());
            });
            
            if (errorMessages.length > 0) {
                showNotification('error', 'Please fix the following errors:<br>' + errorMessages.join('<br>'));
            }
            return false;
        }
        
        // If basic validation passes, validate password with server-side rules
        if (data.password) {
            $.ajax({
                url: '../process/user_ajax.php',
                type: 'POST',
                data: {
                    action: 'validate_password',
                    password: data.password
                },
                dataType: 'json',
                async: false,  // Make this synchronous to wait for validation
                success: function(response) {
                    if (response.status === 'error') {
                        formUtils.showError($('#password'), response.message);
                        showNotification('error', response.message);
                        hasErrors = true;
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);
                    showNotification('error', 'Server error: ' + error);
                    hasErrors = true;
                }
            });
            
            if (hasErrors) {
                return false;
            }
        }
        
        // Validate username uniqueness
        if (data.username && !hasErrors) {
            $.ajax({
                url: '../process/user_ajax.php',
                type: 'POST',
                data: {
                    action: 'validate_username',
                    username: data.username
                },
                dataType: 'json',
                async: false,  // Make this synchronous to wait for validation
                success: function(response) {
                    if (response.status === 'error') {
                        formUtils.showError($('#username'), response.message);
                        showNotification('error', response.message);
                        hasErrors = true;
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);
                    showNotification('error', 'Server error: ' + error);
                    hasErrors = true;
                }
            });
            
            if (hasErrors) {
                return false;
            }
        }
        
        // Validate email uniqueness
        if (data.email && !hasErrors) {
            $.ajax({
                url: '../process/user_ajax.php',
                type: 'POST',
                data: {
                    action: 'validate_email',
                    email: data.email
                },
                dataType: 'json',
                async: false,  // Make this synchronous to wait for validation
                success: function(response) {
                    if (response.status === 'error') {
                        formUtils.showError($('#email'), response.message);
                        showNotification('error', response.message);
                        hasErrors = true;
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);
                    showNotification('error', 'Server error: ' + error);
                    hasErrors = true;
                }
            });
            
            if (hasErrors) {
                return false;
            }
        }
        
        // If validation passes, proceed with form submission
        formData.append('action', 'add_user');
        
        $.ajax({
            url: '../process/user_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Close modal only on success
                    $('#user-modal').css('display', 'none');
                    
                    // Show success message
                    showNotification('success', response.message);
                    
                    // Reload users
                    loadUsers();
                    
                    // Reset form
                    $('#user-form')[0].reset();
                } else {
                    // Show notification error but keep modal open
                    showNotification('error', response.message);
                    
                    // If we have specific field errors, show them in the form
                    if (response.field_errors) {
                        $.each(response.field_errors, function(field, error) {
                            formUtils.showError($('#' + field), error);
                        });
                    }
                }
            },
            error: function(xhr, status, error) {
                // Show error notification but keep modal open
                showNotification('error', 'Server error: ' + error);
                console.error(xhr, status, error);
            }
        });
    });
    
    // Handle Edit User form submission with validation
    $('#edit-form').submit(function(e) {
        e.preventDefault();
        
        // Clear previous errors
        formUtils.clearErrors();
        
        // Get form data
        const formData = new FormData(this);
        const data = {
            user_id: $('#edit-user-id').val(),
            first_name: $('#edit-first-name').val().trim(),
            last_name: $('#edit-last-name').val().trim(),
            username: $('#edit-username').val().trim(),
            email: $('#edit-email').val().trim(),
            dob: $('#edit-dob').val().trim(),
            is_active: $('#edit-active').val()
        };
        
        // Validate form data
        let hasErrors = false;
        
        // Check required fields
        const requiredFields = [
            { name: 'first_name', label: 'First name', element: $('#edit-first-name') },
            { name: 'last_name', label: 'Last name', element: $('#edit-last-name') },
            { name: 'username', label: 'Username', element: $('#edit-username') },
            { name: 'email', label: 'Email', element: $('#edit-email') },
            { name: 'dob', label: 'Date of birth', element: $('#edit-dob') }
        ];
        
        requiredFields.forEach(field => {
            if (!data[field.name]) {
                formUtils.showError(field.element, `${field.label} is required`);
                hasErrors = true;
            }
        });
        
        // Additional validations if initial requirements are met
        if (data.username && !hasErrors) {
            if (data.username.length < 4) {
                formUtils.showError($('#edit-username'), 'Username must be at least 4 characters long');
                hasErrors = true;
            } else {
                const formatError = validators.usernameFormat(data.username);
                if (formatError) {
                    formUtils.showError($('#edit-username'), formatError);
                    hasErrors = true;
                }
            }
        }
        
        if (data.email && !hasErrors) {
            const emailError = validators.email(data.email);
            if (emailError) {
                formUtils.showError($('#edit-email'), emailError);
                hasErrors = true;
            }
        }
        
        if (data.dob && !hasErrors) {
            const dateError = validators.datePast(data.dob, 'Date of birth');
            if (dateError) {
                formUtils.showError($('#edit-dob'), dateError);
                hasErrors = true;
            }
        }
        
        if (hasErrors) {
            // Show notification with validation error summary
            let errorMessages = [];
            $('.error-message').each(function() {
                errorMessages.push($(this).text());
            });
            
            if (errorMessages.length > 0) {
                showNotification('error', 'Please fix the following errors:<br>' + errorMessages.join('<br>'));
            }
            return false;
        }
        
        // Validate username uniqueness
        if (data.username && !hasErrors) {
            $.ajax({
                url: '../process/user_ajax.php',
                type: 'POST',
                data: {
                    action: 'validate_username',
                    username: data.username,
                    user_id: data.user_id
                },
                dataType: 'json',
                async: false,  // Make this synchronous to wait for validation
                success: function(response) {
                    if (response.status === 'error') {
                        formUtils.showError($('#edit-username'), response.message);
                        showNotification('error', response.message);
                        hasErrors = true;
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);
                    showNotification('error', 'Server error: ' + error);
                    hasErrors = true;
                }
            });
            
            if (hasErrors) {
                return false;
            }
        }
        
        // Validate email uniqueness
        if (data.email && !hasErrors) {
            $.ajax({
                url: '../process/user_ajax.php',
                type: 'POST',
                data: {
                    action: 'validate_email',
                    email: data.email,
                    user_id: data.user_id
                },
                dataType: 'json',
                async: false,  // Make this synchronous to wait for validation
                success: function(response) {
                    if (response.status === 'error') {
                        formUtils.showError($('#edit-email'), response.message);
                        showNotification('error', response.message);
                        hasErrors = true;
                    } else {
                        formUtils.clearFieldError($('#edit-email'));
                        formUtils.markFieldValid($('#edit-email'));
                        // Show a small checkmark or success message
                        const successMessage = $('<div class="success-message"><i class="fas fa-check-circle"></i> Email available</div>');
                        $('#edit-email').after(successMessage);
                        setTimeout(function() {
                            successMessage.fadeOut('slow', function() {
                                $(this).remove();
                            });
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);
                    showNotification('error', 'Server error: ' + error);
                    hasErrors = true;
                }
            });
            
            if (hasErrors) {
                return false;
            }
        }
        
        // If validation passes, proceed with form submission
        formData.append('action', 'update_user');
        
        $.ajax({
            url: '../process/user_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Close modal only on success
                    $('#edit-modal').css('display', 'none');
                    
                    // Show success message
                    showNotification('success', response.message);
                    
                    // Reload users
                    loadUsers();
                } else {
                    // Show notification error but keep modal open
                    showNotification('error', response.message);
                    
                    // If we have specific field errors, show them in the form
                    if (response.field_errors) {
                        $.each(response.field_errors, function(field, error) {
                            formUtils.showError($('#edit-' + field), error);
                        });
                    }
                }
            },
            error: function(xhr, status, error) {
                // Show error notification but keep modal open
                showNotification('error', 'Server error: ' + error);
                console.error(xhr, status, error);
            }
        });
    });
    
    // Handle Role Change form submission
    $('#role-form').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'update_role');
        
        $.ajax({
            url: '../process/user_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Close modal only on success
                    $('#role-modal').css('display', 'none');
                    
                    // Show success message
                    showNotification('success', response.message);
                    
                    // Reload users
                    loadUsers();
                } else {
                    // Show error but keep modal open
                    showNotification('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                // Show error but keep modal open
                showNotification('error', 'Server error: ' + error);
                console.error(xhr, status, error);
            }
        });
    });
    
    // Handle Delete User form submission
    $('#delete-form').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'delete_user');
        
        $.ajax({
            url: '../process/user_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Close modal only on success
                    $('#delete-modal').css('display', 'none');
                    
                    // Show success message
                    showNotification('success', response.message);
                    
                    // Reload users
                    loadUsers();
                } else {
                    // Show error but keep modal open
                    showNotification('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                // Show error but keep modal open
                showNotification('error', 'Server error: ' + error);
                console.error(xhr, status, error);
            }
        });
    });
    
    // Add CSS for notifications if not already in stylesheet
    if (!$('style#notification-styles').length) {
        $('head').append(`
            <style id="notification-styles">
                #notification-container {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    max-width: 350px;
                }
                .notification {
                    padding: 15px;
                    margin-bottom: 10px;
                    border-radius: 4px;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                    display: flex;
                    align-items: center;
                    animation: slide-in 0.3s ease-out;
                }
                .notification.success {
                    background-color: #d4edda;
                    color: #155724;
                    border-left: 4px solid #28a745;
                }
                .notification.error {
                    background-color: #f8d7da;
                    color: #721c24;
                    border-left: 4px solid #dc3545;
                }
                .notification i {
                    margin-right: 10px;
                    font-size: 20px;
                }
                .notification span {
                    flex-grow: 1;
                }
                .close-notification {
                    background: none;
                    border: none;
                    color: inherit;
                    cursor: pointer;
                }
                .validation-errors {
                    margin-bottom: 15px;
                }
                .alert {
                    padding: 10px 15px;
                    border-radius: 4px;
                    margin-bottom: 15px;
                }
                .alert-danger {
                    background-color: #f8d7da;
                    color: #721c24;
                    border: 1px solid #f5c6cb;
                }
                .alert-success {
                    background-color: #d4edda;
                    color: #155724;
                    border: 1px solid #c3e6cb;
                }
                @keyframes slide-in {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                .text-center {
                    text-align: center;
                }
                
                /* Password strength indicator styles */
                .password-strength {
                    position: absolute;
                    padding: 5px 10px;
                    border-radius: 3px;
                    font-size: 12px;
                    z-index: 100;
                }
                .password-strength.weak {
                    background-color: #f8d7da;
                    color: #721c24;
                }
                .password-strength.medium {
                    background-color: #fff3cd;
                    color: #856404;
                }
                .password-strength.strong {
                    background-color: #d4edda;
                    color: #155724;
                }
                
                /* Error message styles */
                .error-message {
                    color: #dc3545;
                    font-size: 12px;
                    margin-top: 5px;
                    position: absolute;
                    z-index: 100;
                }
                .input-error {
                    border-color: #dc3545 !important;
                }
                .input-valid {
                    border-color: #28a745 !important;
                }
                
                /* Password field container and toggle button */
                .password-field-container {
                    position: relative;
                    width: 100%;
                }
                
                .password-field-container input {
                    padding-right: 40px;
                    width: 100%;
                }
                
                .toggle-password {
                    position: absolute;
                    right: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    background: none;
                    border: none;
                    cursor: pointer;
                    padding: 0;
                    color: #666;
                    font-size: 14px;
                }
                
                .toggle-password:focus {
                    outline: none;
                }
                
                .toggle-password:hover {
                    color: #333;
                }
                
                /* Success message styles */
                .success-message {
                    color: #28a745;
                    font-size: 12px;
                    margin-top: 5px;
                    position: absolute;
                    z-index: 100;
                }
                
                .success-message i {
                    margin-right: 5px;
                }
            </style>
        `);
    }
    
    // Add a shake animation to the CSS
    if (!$('style#animation-styles').length) {
        $('head').append(`
            <style id="animation-styles">
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                    20%, 40%, 60%, 80% { transform: translateX(5px); }
                }
            </style>
        `);
    }
});
</script>

<style>
/* Additional styles for the delete modal buttons */
.form-group {
    display: flex;
    gap: 10px;
}

.delete-action {
    background-color: #dc3545 !important;
    color: white !important;
}

.delete-action:hover {
    background-color: #bd2130 !important;
}

.form-group .add-btn {
    flex: 1;
    margin-top: 30px;
}
</style>

<?php include_once '../includes/footer.php'; ?>