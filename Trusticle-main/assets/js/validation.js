/**
 * Validation utility functions
 * DRY principle implementation - centralizes validation logic
 */

// Field validation functions
const validators = {
    // Check if field is not empty
    required: (value, fieldName) => {
        if (!value || value.trim() === '') {
            return `${fieldName} is required`;
        }
        return null;
    },
    
    // Check minimum length
    minLength: (value, length, fieldName) => {
        if (value && value.length < length) {
            return `${fieldName} must be at least ${length} characters`;
        }
        return null;
    },
    
    // Email validation
    email: (value) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (value && !emailRegex.test(value)) {
            return "Invalid email format";
        }
        return null;
    },
    
    // Password match validation
    passwordMatch: (password, confirmPassword) => {
        if (password !== confirmPassword) {
            return "Passwords do not match";
        }
        return null;
    },
    
    // Password strength validation
    passwordStrength: (password) => {
        if (!password || password.length < 8) {
            return "Password must be at least 8 characters";
        }
        
        let strength = 0;
        
        // Check for lowercase letters
        if (/[a-z]/.test(password)) strength++;
        
        // Check for uppercase letters
        if (/[A-Z]/.test(password)) strength++;
        
        // Check for numbers
        if (/[0-9]/.test(password)) strength++;
        
        // Check for special characters
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        if (strength < 3) {
            return "Password must contain at least 3 of the following: lowercase letters, uppercase letters, numbers, and special characters";
        }
        
        return null;
    },
    
    // Username format
    usernameFormat: (username) => {
        const usernameRegex = /^[a-zA-Z0-9_\.]+$/;
        if (username && !usernameRegex.test(username)) {
            return "Username can only contain letters, numbers, underscores, and periods";
        }
        return null;
    },
    
    // Date validation (must be in the past)
    datePast: (date, fieldName) => {
        if (!date) return null;
        
        const selectedDate = new Date(date);
        const today = new Date();
        
        if (selectedDate > today) {
            return `${fieldName} must be a date in the past`;
        }
        return null;
    }
};

// Form error display functions
const formUtils = {
    // Display field error
    showError: (element, message) => {
        // Remove any existing error for this element
        formUtils.clearFieldError(element);
        
        // Add new error
        const errorElement = $('<div class="error-message">' + message + '</div>');
        element.after(errorElement);
        element.addClass("input-error");
        
        // Position the error message below the element
        const elementPosition = element.position();
        const elementHeight = element.outerHeight();
        
        errorElement.css({
            'top': elementPosition.top + elementHeight + 2,
            'left': elementPosition.left
        });
        
        // Ensure it doesn't disappear after a short time
        errorElement
            .delay(100)
            .queue(function(next) {
                if ($(this).length) {
                    const newPosition = element.position();
                    $(this).css({
                        'top': newPosition.top + elementHeight + 2,
                        'left': newPosition.left
                    });
                }
                next();
            });
    },
    
    // Display form-level error
    showFormError: (form, message) => {
        // Remove any existing form error
        form.find('.form-error-message').remove();
        
        // Add new error
        const errorElement = $('<div class="form-error-message">' + message + '</div>');
        form.prepend(errorElement);
        
        // Scroll to the error message
        $('html, body').animate({
            scrollTop: errorElement.offset().top - 20
        }, 200);
    },
    
    // Clear errors for a specific field
    clearFieldError: (element) => {
        element.removeClass("input-error");
        element.next(".error-message").remove();
    },
    
    // Clear all errors in a form
    clearErrors: () => {
        $(".error-message, .form-error-message").remove();
        $(".input-error").removeClass("input-error");
    },
    
    // Setup error clearing on input focus
    setupErrorClearingEvents: () => {
        $(document).on("focus", "input, select, textarea", function() {
            formUtils.clearFieldError($(this));
        });
        
        // Also clear on input change for password fields
        $(document).on("input", "input[type='password']", function() {
            formUtils.clearFieldError($(this));
        });
    },
    
    // Show password strength indicator
    showPasswordStrength: (passwordField) => {
        const password = passwordField.val();
        let strength = 0;
        let status = "";
        
        // Remove existing strength indicator
        passwordField.parent().find('.password-strength').remove();
        
        if (!password) return;
        
        // Criteria for password strength
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        // Determine status text and color
        switch (strength) {
            case 0:
            case 1:
                status = '<div class="password-strength weak">Weak Password</div>';
                break;
            case 2:
            case 3:
                status = '<div class="password-strength medium">Medium Password</div>';
                break;
            case 4:
            case 5:
                status = '<div class="password-strength strong">Strong Password</div>';
                break;
        }
        
        // Append strength indicator after password field
        const strengthIndicator = $(status);
        passwordField.after(strengthIndicator);
        
        // Position the strength indicator
        const fieldPosition = passwordField.position();
        const fieldHeight = passwordField.outerHeight();
        
        strengthIndicator.css({
            'top': fieldPosition.top + fieldHeight + 2,
            'right': 10
        });

        // Fade out the strength indicator after 3 seconds
        strengthIndicator
            .delay(3000)
            .fadeOut(500);
    },
    
    // Mark a field as valid when it passes validation
    markFieldValid: (element) => {
        element.removeClass("input-error");
        element.addClass("input-valid");
    }
};

// Export to global scope
window.validators = validators;
window.formUtils = formUtils; 