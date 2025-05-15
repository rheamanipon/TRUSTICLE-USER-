$(document).ready(function() {
    // Setup error clearing on input focus
    formUtils.setupErrorClearingEvents();
    
    // Common function to show notification
    function showNotification(message, type = 'error') {
        const notification = $(`<div class="${type}-notification">${message}</div>`);
        $('body').append(notification);
        
        // Add the show class after a small delay to trigger animation
        notification
            .delay(100)
            .queue(function(next) {
                $(this).addClass('show');
                next();
            });
        
        // Remove notification after delay
        notification
            .delay(type === 'success' ? 1500 : 3000)
            .queue(function(next) {
                $(this).removeClass('show');
                next();
            })
            .delay(300)
            .queue(function(next) {
                $(this).remove();
                next();
            });
    }
    
    // Common function to handle form submission state
    function handleFormSubmission($form, $submitButton, isSubmitting) {
        if (isSubmitting) {
            $submitButton.data('original-text', $submitButton.text());
            $submitButton.prop('disabled', true).text('Processing...');
        } else {
            const originalText = $submitButton.data('original-text') || 'Sign Up';
            $submitButton.prop('disabled', false).text(originalText);
        }
    }
    
    // Login Form Handling
    if ($("#loginForm").length) {
        // Real-time validation for login form
        $("#loginForm #username").on("blur", function() {
            const value = $(this).val().trim();
            if (!value) {
                formUtils.showError($(this), "Email or username is required");
            } else if (value.includes('@')) {
                // If it looks like an email, validate email format
                const error = validators.email(value);
                if (error) {
                    formUtils.showError($(this), error);
                }
            }
        });
        
        $("#loginForm #password").on("blur", function() {
            const value = $(this).val().trim();
            if (!value) {
                formUtils.showError($(this), "Password is required");
            }
        });
        
        // Login form submission
        $("#loginForm").on("submit", function(e) {
            e.preventDefault();
            
            // Reset previous errors
            formUtils.clearErrors();
            $("#loginError").hide();
            
            // Show loading state
            const $submitButton = $(this).find('button[type="submit"]');
            handleFormSubmission($(this), $submitButton, true);
            
            // Get form data
            const formData = {
                username: $("#username").val().trim(),
                password: $("#password").val().trim()
            };
            
            // Basic validation
            if (!formData.username) {
                formUtils.showError($("#username"), "Email or username is required");
                handleFormSubmission($(this), $submitButton, false);
                return;
            }
            
            if (!formData.password) {
                formUtils.showError($("#password"), "Password is required");
                handleFormSubmission($(this), $submitButton, false);
                return;
            }
            
            // If it looks like an email, validate email format
            if (formData.username.includes('@')) {
                const error = validators.email(formData.username);
                if (error) {
                    formUtils.showError($("#username"), error);
                    handleFormSubmission($(this), $submitButton, false);
                    return;
                }
            }
            
            console.log("Submitting login form:", formData);
            
            // Submit the form using AJAX
            $.ajax({
                type: "POST",
                url: "../process/login_process.php",
                data: formData,
                dataType: "json",
                success: function(response) {
                    console.log("Login response:", response);
                    if (response.success) {
                        showNotification("Successfully logged in!", "success");
                        
                        // Use jQuery's delay instead of setTimeout
                        $(document).delay(1500).queue(function(next) {
                            window.location.href = response.redirect;
                            next();
                        });
                    } else {
                        if (response.errors && response.errors.length > 0) {
                            showNotification(response.errors.join("<br>"));
                        }
                        handleFormSubmission($("#loginForm"), $submitButton, false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Login error:", xhr.responseText, status, error);
                    showNotification("An error occurred: " + error);
                    handleFormSubmission($("#loginForm"), $submitButton, false);
                }
            });
        });
    }
    
    // Registration Form Handling
    if ($("#registerForm").length) {
        // Setup password strength indicator
        $("#password").on("input", function() {
            formUtils.showPasswordStrength($(this));
        });
        
        // Real-time field validation
        function validateField(fieldId, value, fieldName) {
            const $field = $(`#${fieldId}`);
            formUtils.clearFieldError($field);

            // Required field validation
            const requiredError = validators.required(value, fieldName);
            if (requiredError) {
                formUtils.showError($field, requiredError);
                return false;
            }

            // Field-specific validations
            switch (fieldId) {
                case 'username':
                    if (value) {
                        const lengthError = validators.minLength(value, 4, 'Username');
                        if (lengthError) {
                            formUtils.showError($field, lengthError);
                            return false;
                        }
                        const formatError = validators.usernameFormat(value);
                        if (formatError) {
                            formUtils.showError($field, formatError);
                            return false;
                        }
                    }
                    break;

                case 'email':
                    if (value) {
                        const emailError = validators.email(value);
                        if (emailError) {
                            formUtils.showError($field, emailError);
                            return false;
                        }
                    }
                    break;

                case 'birthdate':
                    if (value) {
                        const dateError = validators.datePast(value, 'Birthdate');
                        if (dateError) {
                            formUtils.showError($field, dateError);
                            return false;
                        }
                    }
                    break;

                case 'password':
                    if (value) {
                        const lengthError = validators.minLength(value, 6, 'Password');
                        if (lengthError) {
                            formUtils.showError($field, lengthError);
                            return false;
                        }
                        const strengthError = validators.passwordStrength(value);
                        if (strengthError) {
                            formUtils.showError($field, strengthError);
                            return false;
                        }
                    }
                    break;

                case 'confirmPassword':
                    if (value && $('#password').val()) {
                        const matchError = validators.passwordMatch($('#password').val(), value);
                        if (matchError) {
                            formUtils.showError($field, matchError);
                            return false;
                        }
                    }
                    break;
            }

            formUtils.markFieldValid($field);
            return true;
        }

        // Add real-time validation to fields
        const fields = [
            { id: 'firstName', name: 'First name' },
            { id: 'lastName', name: 'Last name' },
            { id: 'username', name: 'Username' },
            { id: 'email', name: 'Email' },
            { id: 'birthdate', name: 'Birthdate' },
            { id: 'password', name: 'Password' },
            { id: 'confirmPassword', name: 'Confirm password' }
        ];

        // Setup validation events for each field
        fields.forEach(field => {
            const $field = $(`#${field.id}`);
            
            // Validate on input (typing)
            $field.on('input', function() {
                validateField(field.id, $(this).val().trim(), field.name);
            });

            // Validate on blur (when field loses focus)
            $field.on('blur', function() {
                validateField(field.id, $(this).val().trim(), field.name);
            });

            // Validate on paste
            $field.on('paste', function() {
                setTimeout(() => {
                    validateField(field.id, $(this).val().trim(), field.name);
                }, 0);
            });

            // Validate on initial load if field has a value
            if ($field.val().trim()) {
                validateField(field.id, $field.val().trim(), field.name);
            }
        });

        // Special handling for confirm password
        $('#password').on('input blur paste', function() {
            if ($('#confirmPassword').val()) {
                validateField('confirmPassword', $('#confirmPassword').val(), 'Confirm password');
            }
        });
        
        // Registration form validation
        function validateRegistrationForm(data) {
            const errors = [];
            
            // Check required fields with corresponding elements
            const requiredFields = [
                { name: 'firstName', label: 'First name', element: $("#firstName") },
                { name: 'lastName', label: 'Last name', element: $("#lastName") },
                { name: 'username', label: 'Username', element: $("#username") },
                { name: 'email', label: 'Email', element: $("#email") },
                { name: 'birthdate', label: 'Birthdate', element: $("#birthdate") },
                { name: 'password', label: 'Password', element: $("#password") },
                { name: 'confirmPassword', label: 'Confirm password', element: $("#confirmPassword") }
            ];
            
            // Check all required fields
            requiredFields.forEach(field => {
                const error = validators.required(data[field.name], field.label);
                if (error) {
                    errors.push({ message: error, element: field.element });
                }
            });
            
            // Additional validations if initial requirements are met
            if (data.username && !errors.find(e => e.element.attr('id') === 'username')) {
                const lengthError = validators.minLength(data.username, 4, 'Username');
                if (lengthError) {
                    errors.push({ message: lengthError, element: $("#username") });
                }
                
                const formatError = validators.usernameFormat(data.username);
                if (formatError) {
                    errors.push({ message: formatError, element: $("#username") });
                }
            }
            
            if (data.email && !errors.find(e => e.element.attr('id') === 'email')) {
                const error = validators.email(data.email);
                if (error) {
                    errors.push({ message: error, element: $("#email") });
                }
            }
            
            if (data.birthdate && !errors.find(e => e.element.attr('id') === 'birthdate')) {
                const error = validators.datePast(data.birthdate, 'Birthdate');
                if (error) {
                    errors.push({ message: error, element: $("#birthdate") });
                }
            }
            
            if (data.password && !errors.find(e => e.element.attr('id') === 'password')) {
                const lengthError = validators.minLength(data.password, 6, 'Password');
                if (lengthError) {
                    errors.push({ message: lengthError, element: $("#password") });
                    return errors;
                }
                
                const strengthError = validators.passwordStrength(data.password);
                if (strengthError) {
                    errors.push({ message: strengthError, element: $("#password") });
                }
            }
            
            if (data.password && data.confirmPassword) {
                const error = validators.passwordMatch(data.password, data.confirmPassword);
                if (error) {
                    errors.push({ message: error, element: $("#confirmPassword") });
                }
            }
            
            return errors;
        }
        
        // Registration form submission
        $("#registerForm").on("submit", function(e) {
            e.preventDefault();
            
            // Reset previous errors
            formUtils.clearErrors();
            
            // Show loading state
            const $submitButton = $(this).find('button[type="submit"]');
            const originalText = $submitButton.text();
            handleFormSubmission($(this), $submitButton, true);
            
            // Get form data
            const formData = {
                firstName: $("#firstName").val().trim(),
                lastName: $("#lastName").val().trim(),
                username: $("#username").val().trim(),
                email: $("#email").val().trim(),
                birthdate: $("#birthdate").val(),
                password: $("#password").val(),
                confirmPassword: $("#confirmPassword").val()
            };
            
            // Validate form data
            const errors = validateRegistrationForm(formData);
            
            if (errors.length > 0) {
                // Display validation errors
                errors.forEach(error => {
                    formUtils.showError(error.element, error.message);
                });
                handleFormSubmission($(this), $submitButton, false);
                return;
            }
            
            // Submit the form using AJAX
            $.ajax({
                type: "POST",
                url: "../process/registration_process.php",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Show success modal
                        $("#successModal").css("display", "flex");
                        $(".container").addClass("blur-background");
                        // Reset button state since we're showing the success modal
                        handleFormSubmission($("#registerForm"), $submitButton, false);
                    } else {
                        // Display server-side errors
                        if (response.errors && response.errors.length > 0) {
                            showNotification(response.errors.join("<br>"));
                        }
                        handleFormSubmission($("#registerForm"), $submitButton, false);
                    }
                },
                error: function(xhr, status, error) {
                    showNotification("An error occurred during registration. Please try again.");
                    handleFormSubmission($("#registerForm"), $submitButton, false);
                }
            });
        });
        
        // Login button in success modal redirects to login page
        $(".login-btn").on("click", function() {
            window.location.href = "login.php";
        });
    }
    
    // Handle window resize - reposition error messages
    $(window).on('resize', function() {
        $('.error-message').each(function() {
            const $input = $(this).prev('input, select, textarea');
            if ($input.length) {
                $(this).css({
                    'top': $input.position().top + $input.outerHeight() + 2,
                    'left': $input.position().left
                });
            }
        });
    });
}); 