<?php
/**
 * Validation utility functions
 * DRY principle implementation - centralizes validation logic
 */

class Validator {
    private $errors = [];
    private $data = [];
    private $conn = null;
    
    /**
     * Constructor - initializes validator with data to validate
     * 
     * @param array $data Form data to validate
     * @param mysqli $conn Database connection for unique checks
     */
    public function __construct($data, $conn = null) {
        $this->data = $data;
        $this->conn = $conn;
    }
    
    /**
     * Check if field is required
     * 
     * @param string $field Field name
     * @param string $label Field label for error message
     * @return Validator
     */
    public function required($field, $label = null) {
        $label = $label ?? ucfirst($field);
        
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[] = "{$label} is required";
        }
        
        return $this;
    }
    
    /**
     * Check if field meets minimum length
     * 
     * @param string $field Field name
     * @param int $length Required minimum length
     * @param string $label Field label for error message
     * @return Validator
     */
    public function minLength($field, $length, $label = null) {
        $label = $label ?? ucfirst($field);
        
        if (isset($this->data[$field]) && strlen(trim($this->data[$field])) < $length) {
            $this->errors[] = "{$label} must be at least {$length} characters";
        }
        
        return $this;
    }
    
    /**
     * Check if field is a valid email
     * 
     * @param string $field Field name
     * @param string $label Field label for error message
     * @return Validator
     */
    public function email($field, $label = null) {
        $label = $label ?? ucfirst($field);
        
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Invalid email format";
        }
        
        return $this;
    }
    
    /**
     * Check if passwords match
     * 
     * @param string $field1 First password field
     * @param string $field2 Confirm password field
     * @return Validator
     */
    public function passwordsMatch($field1, $field2) {
        if (isset($this->data[$field1]) && isset($this->data[$field2]) 
            && $this->data[$field1] !== $this->data[$field2]) {
            $this->errors[] = "Passwords do not match";
        }
        
        return $this;
    }
    
    /**
     * Check password strength
     * 
     * @param string $field Password field
     * @param string $label Field label for error message
     * @return Validator
     */
    public function passwordStrength($field, $label = null) {
        $label = $label ?? ucfirst($field);
        
        if (!isset($this->data[$field]) || empty($this->data[$field])) {
            return $this; // Skip empty passwords (required check will catch this)
        }
        
        $password = $this->data[$field];
        $minLength = 8;
        
        if (strlen($password) < $minLength) {
            $this->errors[] = "{$label} must be at least {$minLength} characters";
            return $this;
        }
        
        $strength = 0;
        
        // Check for lowercase letters
        if (preg_match('/[a-z]/', $password)) $strength++;
        
        // Check for uppercase letters
        if (preg_match('/[A-Z]/', $password)) $strength++;
        
        // Check for numbers
        if (preg_match('/[0-9]/', $password)) $strength++;
        
        // Check for special characters
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $strength++;
        
        if ($strength < 2) {
            $this->errors[] = "{$label} must contain at least 2 of the following: lowercase letters, uppercase letters, numbers, and special characters";
        }
        
        return $this;
    }
    
    /**
     * Check if username format is valid
     * 
     * @param string $field Username field
     * @param string $label Field label for error message
     * @return Validator
     */
    public function usernameFormat($field, $label = null) {
        $label = $label ?? ucfirst($field);
        
        if (!isset($this->data[$field]) || empty($this->data[$field])) {
            return $this; // Skip empty usernames (required check will catch this)
        }
        
        $username = $this->data[$field];
        
        if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $username)) {
            $this->errors[] = "{$label} can only contain letters, numbers, underscores, and periods";
        }
        
        return $this;
    }
    
    /**
     * Check if date is in the past
     * 
     * @param string $field Date field
     * @param string $label Field label for error message
     * @return Validator
     */
    public function datePast($field, $label = null) {
        $label = $label ?? ucfirst($field);
        
        if (!isset($this->data[$field]) || empty($this->data[$field])) {
            return $this; // Skip empty dates (required check will catch this)
        }
        
        $date = $this->data[$field];
        $selectedDate = strtotime($date);
        $today = strtotime('today');
        
        if ($selectedDate > $today) {
            $this->errors[] = "{$label} must be a date in the past";
        }
        
        return $this;
    }
    
    /**
     * Check if field value is unique in database
     * 
     * @param string $field Field name
     * @param string $table Database table name
     * @param string $label Field label for error message
     * @param string $idField Optional ID field to exclude in update situations
     * @param int $idValue Optional ID value to exclude in update situations
     * @return Validator
     */
    public function unique($field, $table, $label = null, $idField = null, $idValue = null) {
        if (!$this->conn) {
            return $this;  // Skip if no database connection
        }
        
        $label = $label ?? ucfirst($field);
        $value = $this->data[$field] ?? '';
        
        if (empty($value)) {
            return $this;  // Skip empty values
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$field} = ?";
        $params = [$value];
        $types = "s";
        
        // Add exclusion condition for updates
        if ($idField !== null && $idValue !== null) {
            $sql .= " AND {$idField} != ?";
            $params[] = $idValue;
            $types .= "i";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row['count'] > 0) {
            $this->errors[] = "{$label} is already taken";
        }
        
        return $this;
    }
    
    /**
     * Check if field value is unique or belongs to a deleted account
     * 
     * @param string $field Field name to check uniqueness of
     * @param string $table Database table name
     * @param string $label Field label for error message
     * @return Validator
     */
    public function uniqueOrDeleted($field, $table, $label = null) {
        if (!$this->conn) {
            return $this;  // Skip if no database connection
        }
        
        $label = $label ?? ucfirst($field);
        $value = $this->data[$field] ?? '';
        
        if (empty($value)) {
            return $this;  // Skip empty values
        }
        
        // Check if the field exists in a non-deleted user account
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$field} = ? AND is_deleted = FALSE";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row['count'] > 0) {
            $this->errors[] = "{$label} is already taken";
        }
        
        return $this;
    }
    
    /**
     * Get existing but deleted user by field value
     *
     * @param string $field Field name to check
     * @param mixed $value Field value to find
     * @return array|null User data if found and deleted, null otherwise
     */
    public function getDeletedUser($field, $value) {
        if (!$this->conn) {
            return null;  // Skip if no database connection
        }
        
        // Check if user exists and is deleted
        $sql = "SELECT * FROM users WHERE {$field} = ? AND is_deleted = TRUE";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $stmt->close();
            return $user;
        }
        
        $stmt->close();
        return null;
    }
    
    /**
     * Get sanitized value
     * 
     * @param string $field Field name
     * @param mixed $default Default value if field is empty
     * @return mixed Sanitized value
     */
    public function getValue($field, $default = '') {
        $value = $this->data[$field] ?? $default;
        
        if (is_string($value)) {
            return trim($value);
        }
        
        return $value;
    }
    
    /**
     * Check if validation passed
     * 
     * @return bool True if no errors, false otherwise
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     * 
     * @return bool True if has errors, false otherwise
     */
    public function fails() {
        return !empty($this->errors);
    }
    
    /**
     * Get validation errors
     * 
     * @return array Array of error messages
     */
    public function getErrors() {
        return $this->errors;
    }
}
?> 