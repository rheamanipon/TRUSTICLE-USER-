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
            const formData = new FormData(this);
            
            // Basic validation
            let hasErrors = false;
            
            // First name validation
            if (!$("#first-name").val().trim()) {
                formUtils.showError($("#first-name"), "First name is required");
                hasErrors = true;
            }
            
            // Last name validation
            if (!$("#last-name").val().trim()) {
                formUtils.showError($("#last-name"), "Last name is required");
                hasErrors = true;
            }
            
            // Username validation
            const username = $("#username").val().trim();
            if (!username) {
                formUtils.showError($("#username"), "Username is required");
                hasErrors = true;
            } else {
                const lengthError = validators.minLength(username, 4, 'Username');
                if (lengthError) {
                    formUtils.showError($("#username"), lengthError);
                    hasErrors = true;
                } else {
                    const formatError = validators.usernameFormat(username);
                    if (formatError) {
                        formUtils.showError($("#username"), formatError);
                        hasErrors = true;
                    }
                }
            }
            
            // Email validation
            const email = $("#email").val().trim();
            if (!email) {
                formUtils.showError($("#email"), "Email is required");
                hasErrors = true;
            } else {
                const error = validators.email(email);
                if (error) {
                    formUtils.showError($("#email"), error);
                    hasErrors = true;
                }
            }
            
            // DOB validation
            const dob = $("#dob").val();
            if (!dob) {
                formUtils.showError($("#dob"), "Date of birth is required");
                hasErrors = true;
            } else {
                const error = validators.datePast(dob, 'Date of birth');
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
                url: "../../process/settings_process.php",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Update profile information across the page
                        updateProfileInfo(response.userData);
                        
                        // Show success message in dialog
                        showDialog('Success', 'Profile updated successfully!');
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
            
            if (value !== newPassword) {
                formUtils.showError($(this), "Passwords do not match");
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
            } else if (formData.new_password && formData.confirm_password !== formData.new_password) {
                formUtils.showError($("#confirm-password"), "Passwords do not match");
                hasErrors = true;
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
                        // Clear form fields on success
                        $("#current-password, #new-password, #confirm-password").val('');
                        
                        // Clear password strength indicator
                        $(".password-strength").remove();
                        
                        // Show success dialog
                        showDialog('Success', 'Password changed successfully!');
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

    // Tab navigation for security settings
    $(".security-tab").on("click", function(e) {
        e.preventDefault();
        
        // Update tab state
        $(".security-tab").removeClass("active").attr("aria-selected", "false").attr("tabindex", "-1");
        $(this).addClass("active").attr("aria-selected", "true").attr("tabindex", "0");
        
        // Update content visibility
        $(".security-content").attr("hidden", true).removeClass("active");
        const targetId = $(this).attr("href").split("/")[1];
        $("#" + targetId).attr("hidden", false).addClass("active");
    });

    // Account action buttons
    $("#deactivate-btn").on("click", function() {
        showConfirmDialog('Deactivate Account', 'Are you sure you want to deactivate your account? Your profile will be hidden until you log in again.', function() {
            // Perform the deactivation
            $.ajax({
                type: "POST",
                url: "../../process/settings_process.php",
                data: { form_type: "deactivate_account" },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Show info dialog and redirect
                        showDialog('Account Deactivated', 'Your account has been deactivated. You will be logged out now.', function() {
                            window.location.href = "../../auth/logout.php";
                        });
                    } else {
                        // Show error message
                        showDialog('Error', response.message || "Failed to deactivate account");
                    }
                },
                error: function() {
                    showDialog('Error', "An error occurred while processing your request");
                }
            });
        });
    });

    $("#delete-btn").on("click", function() {
        showConfirmDialog('Delete Account', 'Are you sure you want to delete your account? This action is irreversible and all your data will be permanently removed.', function() {
            // Perform the deletion
            $.ajax({
                type: "POST",
                url: "../../process/settings_process.php",
                data: { form_type: "delete_account" },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        // Show info dialog and redirect
                        showDialog('Account Deleted', 'Your account has been deleted. You will be redirected to the homepage.', function() {
                            window.location.href = "../../auth/logout.php";
                        });
                    } else {
                        // Show error message
                        showDialog('Error', response.message || "Failed to delete account");
                    }
                },
                error: function() {
                    showDialog('Error', "An error occurred while processing your request");
                }
            });
        });
    });

    // Profile image upload handling
    if ($(".profile-image").length) {
        $(".profile-image").on("click", function() {
            $("#profile-photo-input").click();
        });
        
        $("#profile-photo-input").on("change", function() {
            const file = this.files[0];
            if (file) {
                // Show loading state
                $(".profile-image").addClass("loading");
                
                // Show preview immediately
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#profile-preview').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
                
                // Create form data for upload
                const formData = new FormData();
                formData.append("form_type", "update_profile_photo");
                formData.append("profile_photo", file);
                
                // Show uploading message
                showDialog('Processing', 'Uploading your profile photo...');
                
                // Submit the image via AJAX
                $.ajax({
                    type: "POST",
                    url: "../../process/settings_process.php",
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    success: function(response) {
                        $(".profile-image").removeClass("loading");
                        
                        if (response.success) {
                            // Update profile preview
                            if (response.photo_url) {
                                updateProfileImage(response.photo_url);
                            }
                            
                            // Show success message
                            showDialog('Success', 'Profile photo updated successfully');
                        } else {
                            // Show error message
                            showDialog('Error', response.message || "Failed to upload profile photo");
                        }
                    },
                    error: function(xhr, status, error) {
                        $(".profile-image").removeClass("loading");
                        showDialog('Error', "An error occurred while uploading your profile photo");
                        console.error('Upload failed:', error);
                        console.error('Response text:', xhr.responseText);
                        console.error('Status:', status);
                    }
                });
            }
        });
    }

    // Function to update profile image across the site
    function updateProfileImage(url) {
        // Make sure the URL is correct - this is crucial!
        let imagePath = url;
        
        // If the URL is a relative path, ensure it points to the correct directory
        if (!url.startsWith('http') && !url.startsWith('/')) {
            // Extract the file name in case the URL already has a path prefix
            const fileName = url.split('/').pop();
            // Always use the main assets directory path
            imagePath = '../../assets/images/profiles/' + fileName;
        }
        
        console.log('Updating profile image with:', imagePath);
        
        // Add a timestamp to prevent caching
        const cacheBustUrl = imagePath + '?v=' + new Date().getTime();
        
        // Update all profile images with more specific selectors
        $('.profile-image img').attr('src', cacheBustUrl);
        $('.sidebar .profile-image img').attr('src', cacheBustUrl);
        $('.user-avatar').attr('src', cacheBustUrl);
        
        // Also target images by classes that might be in the sidebar
        $('.sidebar img.profile-photo').attr('src', cacheBustUrl);
        $('.sidebar img.user-photo').attr('src', cacheBustUrl);
        
        // Add a broader selector for any images in user-related containers
        $('.sidebar .user-info img').attr('src', cacheBustUrl);
        $('.user-profile img').attr('src', cacheBustUrl);
        
        // Force update of header profile images
        $('.header-user-image').attr('src', cacheBustUrl);
        $('#header-profile-image').attr('src', cacheBustUrl);
        
        // Store in sessionStorage to persist between page navigations
        sessionStorage.setItem('profileImageUrl', imagePath);
    }

    // Function to update profile information
    function updateProfileInfo(userData) {
        if (!userData) return;
        
        // Update text information
        $('.profile-info h3').text(userData.first_name + ' ' + userData.last_name);
        $('.profile-info p').text(userData.username);
        
        // Update sidebar text information
        $('.sidebar .user-info small:first-child').text(userData.first_name + ' ' + userData.last_name);
        $('.sidebar .user-info .user-subtitle').text(userData.email);
        
        // Only update image if we have a profile photo
        if (userData.profile_photo) {
            updateProfileImage(userData.profile_photo);
        }
    }

    // Generic dialog functions
    function showConfirmDialog(title, message, onConfirm) {
        const $confirmDialog = $('#confirm-dialog');
        $('#confirm-dialog-title').text(title);
        $('#confirm-dialog-desc').text(message);
        
        // Set up handlers
        $('#confirm-yes').off('click').on('click', function() {
            onConfirm();
            $confirmDialog[0].close();
        });
        
        $('#confirm-no').off('click').on('click', function() {
            $confirmDialog[0].close();
        });
        
        $confirmDialog[0].showModal();
    }
    
    function showDialog(title, message, onClose) {
        const $infoDialog = $('#info-dialog');
        $('#info-dialog-title').text(title);
        $('#info-dialog-desc').text(message);
        
        $('#info-ok').off('click').on('click', function() {
            $infoDialog[0].close();
            if (typeof onClose === 'function') {
                onClose();
            }
        });
        
        $infoDialog[0].showModal();
    }
    
    // Password visibility toggle
    window.togglePasswordVisibility = function(inputId, icon) {
        const input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
            $(icon).removeClass("fa-eye").addClass("fa-eye-slash");
        } else {
            input.type = "password";
            $(icon).removeClass("fa-eye-slash").addClass("fa-eye");
        }
    };
    
    // Handler for tab navigation via URL hash
    function handleHashNavigation() {
        if (window.location.hash) {
            const hash = window.location.hash.substring(1); // Remove the # symbol
            const parts = hash.split('/');
            
            if (parts.length > 0) {
                const tabName = parts[0];
                
                // Switch to the appropriate tab
                $(`.tab-link[href="#${tabName}"]`).click();
                
                // If there's a subtab path, handle it
                if (parts.length > 1 && tabName === "account-security") {
                    const securityTab = parts[1];
                    $(`.security-tab[href="#account-security/${securityTab}"]`).click();
                }
            }
        }
    }
    
    // Initialize tab navigation
    $(".tab-link").on("click", function(e) {
        e.preventDefault();
        
        // Update tab state
        $(".tab-link").removeClass("active").attr("aria-selected", "false");
        $(this).addClass("active").attr("aria-selected", "true");
        
        // Update tab panel visibility
        $(".tab-content").removeClass("active");
        const targetId = $(this).attr("href").substring(1); // Remove the # symbol
        $("#" + targetId).addClass("active");
        
        // Update URL hash without scrolling
        const currentScrollPosition = window.pageYOffset;
        window.location.hash = $(this).attr("href");
        window.scrollTo(0, currentScrollPosition);
    });
    
    // Handle initial hash navigation
    handleHashNavigation();
    
    // Listen for hash changes
    $(window).on("hashchange", handleHashNavigation);
    
    // Check for stored profile image URL and apply it
    const storedProfileImageUrl = sessionStorage.getItem('profileImageUrl');
    if (storedProfileImageUrl) {
        console.log('Found stored profile image URL:', storedProfileImageUrl);
        
        // Make sure the stored URL is using the correct path
        let correctedUrl = storedProfileImageUrl;
        if (!storedProfileImageUrl.startsWith('http') && !storedProfileImageUrl.startsWith('/')) {
            // Extract the filename in case the URL already has a path prefix
            const fileName = storedProfileImageUrl.split('/').pop();
            // Always use the main assets directory path
            correctedUrl = '../../assets/images/profiles/' + fileName;
        }
        
        // Update all profile images with the corrected URL
        updateProfileImage(correctedUrl);
    } else if ($("#profile-preview").length) {
        // If no stored image but we have a profile preview, store its source
        const initialProfileUrl = $("#profile-preview").attr('src');
        if (initialProfileUrl) {
            sessionStorage.setItem('profileImageUrl', initialProfileUrl);
        }
    }
}); 