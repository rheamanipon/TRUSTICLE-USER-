$(document).ready(function() {
    // Setup error clearing on input focus
    formUtils.setupErrorClearingEvents();
    
    // Common function to show notification
    function showNotification(message, type = 'error') {
        const notification = $(`<div class="${type}-notification">${message}</div>`);
        $('body').append(notification);
        
        // Add the show class after a small delay to trigger animation
        setTimeout(() => {
            notification.addClass('show');
        }, 100);
        
        // Remove notification after delay
        setTimeout(() => {
            notification.removeClass('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, type === 'success' ? 1500 : 3000);
    }
    
    // Common function to handle form submission state
    function handleFormSubmission($form, $submitButton, isSubmitting) {
        if (isSubmitting) {
            $submitButton.data('original-text', $submitButton.text());
            $submitButton.prop('disabled', true).text('Processing...');
        } else {
            const originalText = $submitButton.data('original-text') || 'Save Changes';
            $submitButton.prop('disabled', false).text(originalText);
        }
    }

    // Setup password strength indicator using the global formUtils
    $("#new-password").on("input", function() {
        formUtils.showPasswordStrength($(this));
    });

    // Profile Edit Form Validation & Submission
    if ($("#edit-profile-form").length) {
        // Field-specific validations
        $("#first-name, #last-name").on("blur", function() {
            const value = $(this).val().trim();
            const fieldName = $(this).attr('id') === 'first-name' ? 'First name' : 'Last name';
            
            if (!value) {
                formUtils.showError($(this), `${fieldName} is required`);
            }
        });
        
        $("#username").on("blur", function() {
            const value = $(this).val().trim();
            
            if (!value) {
                formUtils.showError($(this), "Username is required");
                return;
            }
            
            const lengthError = validators.minLength(value, 4, 'Username');
            if (lengthError) {
                formUtils.showError($(this), lengthError);
                return;
            }
            
            const formatError = validators.usernameFormat(value);
            if (formatError) {
                formUtils.showError($(this), formatError);
            }
        });
        
        $("#email").on("blur", function() {
            const value = $(this).val().trim();
            
            if (!value) {
                formUtils.showError($(this), "Email is required");
                return;
            }
            
            const error = validators.email(value);
            if (error) {
                formUtils.showError($(this), error);
            }
        });
        
        $("#dob").on("blur", function() {
            const value = $(this).val();
            
            if (!value) {
                formUtils.showError($(this), "Date of birth is required");
                return;
            }
            
            const error = validators.datePast(value, 'Date of birth');
            if (error) {
                formUtils.showError($(this), error);
            }
        });
        
        // Form submission
        $("#edit-profile-form").on("submit", function(e) {
            e.preventDefault();
            
            // Reset previous errors
            formUtils.clearErrors();
            $("#profile-error").hide();
            
            // Get form data
            const formData = {
                form_type: "update_profile",
                first_name: $("#first-name").val().trim(),
                last_name: $("#last-name").val().trim(),
                username: $("#username").val().trim(),
                email: $("#email").val().trim(),
                dob: $("#dob").val()
            };
            
            // Basic validation
            let hasErrors = false;
            
            // First name validation
            if (!formData.first_name) {
                formUtils.showError($("#first-name"), "First name is required");
                hasErrors = true;
            }
            
            // Last name validation
            if (!formData.last_name) {
                formUtils.showError($("#last-name"), "Last name is required");
                hasErrors = true;
            }
            
            // Username validation
            if (!formData.username) {
                formUtils.showError($("#username"), "Username is required");
                hasErrors = true;
            } else {
                const lengthError = validators.minLength(formData.username, 4, 'Username');
                if (lengthError) {
                    formUtils.showError($("#username"), lengthError);
                    hasErrors = true;
                } else {
                    const formatError = validators.usernameFormat(formData.username);
                    if (formatError) {
                        formUtils.showError($("#username"), formatError);
                        hasErrors = true;
                    }
                }
            }
            
            // Email validation
            if (!formData.email) {
                formUtils.showError($("#email"), "Email is required");
                hasErrors = true;
            } else {
                const error = validators.email(formData.email);
                if (error) {
                    formUtils.showError($("#email"), error);
                    hasErrors = true;
                }
            }
            
            // DOB validation
            if (!formData.dob) {
                formUtils.showError($("#dob"), "Date of birth is required");
                hasErrors = true;
            } else {
                const error = validators.datePast(formData.dob, 'Date of birth');
                if (error) {
                    formUtils.showError($("#dob"), error);
                    hasErrors = true;
                }
            }
            
            if (hasErrors) {
                return;
            }
            
            // Show loading state
            const $submitButton = $(this).find('button[type="submit"]');
            handleFormSubmission($(this), $submitButton, true);
            
            // Submit the form using AJAX
            $.ajax({
                type: "POST",
                url: "process/settings_process.php",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Remove this line to prevent the green notification from showing
                        // showNotification("Profile updated successfully!", "success");
                    } else {
                        if (response.message) {
                            $("#profile-error").text(response.message).show();
                        } else if (response.errors && response.errors.length > 0) {
                            response.errors.forEach(error => {
                                if (error.field && $(`#${error.field}`).length) {
                                    formUtils.showError($(`#${error.field}`), error.message);
                                } else {
                                    $("#profile-error").append(`<p>${error.message}</p>`).show();
                                }
                            });
                        } else {
                            $("#profile-error").text("An unknown error occurred").show();
                        }
                    }
                    handleFormSubmission($("#edit-profile-form"), $submitButton, false);
                },
                error: function(xhr, status, error) {
                    $("#profile-error").text("An error occurred: " + error).show();
                    handleFormSubmission($("#edit-profile-form"), $submitButton, false);
                }
            });
        });
    }

    // Password Change Form Validation & Submission
    if ($("#change-password-form").length) {
        // Field-specific validations
        $("#current-password").on("blur", function() {
            const value = $(this).val().trim();
            
            if (!value) {
                formUtils.showError($(this), "Current password is required");
            }
        });
        
        $("#new-password").on("blur", function() {
            const value = $(this).val().trim();
            
            if (!value) {
                formUtils.showError($(this), "New password is required");
                return;
            }
            
            const lengthError = validators.minLength(value, 8, 'Password');
            if (lengthError) {
                formUtils.showError($(this), lengthError);
                return;
            }
            
            const strengthError = validators.passwordStrength(value);
            if (strengthError) {
                formUtils.showError($(this), strengthError);
            }
        });
        
        $("#confirm-password").on("blur", function() {
            const value = $(this).val().trim();
            const newPassword = $("#new-password").val().trim();
            
            if (!value) {
                formUtils.showError($(this), "Confirm password is required");
                return;
            }
            
            const error = validators.passwordMatch(newPassword, value);
            if (error) {
                formUtils.showError($(this), error);
            }
        });
        
        // Form submission
        $("#change-password-form").on("submit", function(e) {
            e.preventDefault();
            
            // Reset previous errors
            formUtils.clearErrors();
            $("#password-error").hide();
            
            // Get form data
            const formData = {
                form_type: "change_password",
                current_password: $("#current-password").val().trim(),
                new_password: $("#new-password").val().trim(),
                confirm_password: $("#confirm-password").val().trim()
            };
            
            // Basic validation
            let hasErrors = false;
            
            // Current password validation
            if (!formData.current_password) {
                formUtils.showError($("#current-password"), "Current password is required");
                hasErrors = true;
            }
            
            // New password validation
            if (!formData.new_password) {
                formUtils.showError($("#new-password"), "New password is required");
                hasErrors = true;
            } else {
                const lengthError = validators.minLength(formData.new_password, 8, 'Password');
                if (lengthError) {
                    formUtils.showError($("#new-password"), lengthError);
                    hasErrors = true;
                } else {
                    const strengthError = validators.passwordStrength(formData.new_password);
                    if (strengthError) {
                        formUtils.showError($("#new-password"), strengthError);
                        hasErrors = true;
                    }
                }
            }
            
            // Confirm password validation
            if (!formData.confirm_password) {
                formUtils.showError($("#confirm-password"), "Confirm password is required");
                hasErrors = true;
            } else if (formData.new_password) {
                const error = validators.passwordMatch(formData.new_password, formData.confirm_password);
                if (error) {
                    formUtils.showError($("#confirm-password"), error);
                    hasErrors = true;
                }
            }
            
            if (hasErrors) {
                return;
            }
            
            // Show loading state
            const $submitButton = $(this).find('button[type="submit"]');
            handleFormSubmission($(this), $submitButton, true);
            
            // Submit the form using AJAX
            $.ajax({
                type: "POST",
                url: "../../process/settings_process.php",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        showNotification("Password changed successfully!", "success");
                        // Clear the form
                        $("#change-password-form")[0].reset();
                    } else {
                        if (response.message) {
                            $("#password-error").text(response.message).show();
                        } else if (response.errors && response.errors.length > 0) {
                            response.errors.forEach(error => {
                                if (error.field && $(`#${error.field}`).length) {
                                    formUtils.showError($(`#${error.field}`), error.message);
                                } else {
                                    $("#password-error").append(`<p>${error.message}</p>`).show();
                                }
                            });
                        } else {
                            $("#password-error").text("An unknown error occurred").show();
                        }
                    }
                    handleFormSubmission($("#change-password-form"), $submitButton, false);
                },
                error: function(xhr, status, error) {
                    $("#password-error").text("An error occurred: " + error).show();
                    handleFormSubmission($("#change-password-form"), $submitButton, false);
                }
            });
        });
    }
}); 