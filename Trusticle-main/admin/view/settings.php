<?php
// Start the session
session_start();

// Database connection
require_once '../../config/connection.php';
// Include profile image utility
require_once '../../utils/profile_image.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

// Get user data from database
$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $userData = $result->fetch_assoc();
    
    // Generate profile image URL
    $profileImage = get_profile_image($userData['profile_photo'], $userData['first_name'], $userData['last_name'], 150);
    
    // Ensure correct path for profile images
    if (!empty($userData['profile_photo']) && $userData['profile_photo'] !== 'default.jpg') {
        // Always use the main assets path for profile images
        $profileImage = '../../assets/images/profiles/' . $userData['profile_photo'];
    }
} else {
    // Handle error - user not found
    header("Location: ../../auth/logout.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Settings Dashboard</title>
  <!-- Font Awesome MUST be loaded before any other styles -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <!-- Main admin styles from admin/assets -->
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <!-- Admin settings specific styles -->
  <link rel="stylesheet" href="../assets/css/settings.css" />
  <!-- Global validation styles -->
  <link rel="stylesheet" href="../../assets/css/validation.css" />
  <?php
  // Get the current file name
  $current_file = basename($_SERVER['PHP_SELF']);
  ?>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    /* Override specific styles only, remove all duplicated styles */
    #edit-profile .profile-image {
      width: 150px;
      height: 150px;
    }
    
    /* Make sure sidebar profile image is not affected */
    .sidebar .user-profile .profile-image {
      width: 35px !important;
      height: 35px !important;
      border-radius: 50% !important;
      margin-right: 10px !important;
      border: none !important;
    }
    
    /* Password visibility toggle styling */
    .settings-content .form-group {
      position: relative;
    }
    
    /* Admin-specific password toggle styling */
    .settings-content .password-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(30%);
      cursor: pointer;
      color: #666;
      z-index: 10;
      transition: color 0.2s ease-in-out;
    }
    
    .settings-content .password-toggle:hover {
      color: #0056b3;
    }
    
    /* Password strength indicator position fix */
    .password-strength {
      position: absolute;
      right: 40px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 12px;
      padding: 2px 8px;
      border-radius: 3px;
      color: white;
      z-index: 10;
    }
    
    .password-strength.weak { background-color: #f44336; }
    .password-strength.medium { background-color: #ff9800; }
    .password-strength.strong { background-color: #4CAF50; }
    
    /* Delete Account button styling */
    #delete-btn {
      background-color: #dc3545;
      border-color: #dc3545;
      color: white;
      width: 200px;
      margin-top: 10px;
    }
    
    #delete-btn:hover {
      background-color: #c82333;
      border-color: #bd2130;
    }
    
    /* Make deactivate button consistent width */
    #deactivate-btn {
      width: 200px;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <?php include '../includes/header.php';?>
    <!-- Main Content Area -->
    <main class="main-content" id="main-content" tabindex="-1">
      <div class="settings-content" style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <h1>Settings</h1>
        
        <!-- Edit Profile Tab -->
        <section class="tab-content active" id="edit-profile" role="tabpanel" aria-labelledby="tab-edit-profile">
          <div class="content-card">
            <h2>Personal Information</h2>
            <div class="profile-section">
              <div class="profile-image">
                <img id="profile-preview" src="<?php echo $profileImage; ?>" alt="Profile picture of <?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?>" />
                <div class="profile-image-overlay">
                  <i class="fas fa-pencil-alt"></i>
                  <span>Edit Photo</span>
                </div>
              </div>
              <div class="profile-info">
                <h3><?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?></h3>
                <p><?php echo htmlspecialchars($userData['username']); ?></p>
              </div>
            </div>
            <form class="settings-form" id="edit-profile-form" action="../../process/settings_process.php" method="post" enctype="multipart/form-data" novalidate>
              <div id="profile-error" class="form-error-message" style="display: none;"></div>
              <input type="hidden" name="form_type" value="update_profile">
              <input type="file" id="profile-photo-input" name="profile_photo" accept="image/*" style="display: none;">
              <div class="form-group">
                <label for="first-name">First Name</label>
                <input id="first-name" name="first_name" type="text" value="<?php echo htmlspecialchars($userData['first_name']); ?>" required />
              </div>
              <div class="form-group">
                <label for="last-name">Last Name</label>
                <input id="last-name" name="last_name" type="text" value="<?php echo htmlspecialchars($userData['last_name']); ?>" required />
              </div>
              <div class="form-group">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" value="<?php echo htmlspecialchars($userData['username']); ?>" required />
              </div>
              <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input id="dob" name="dob" type="date" value="<?php echo $userData['birthdate']; ?>" required />
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required />
              </div>
              <button type="submit" class="save-button">SAVE CHANGES</button>
            </form>
          </div>
        </section>

        <!-- Account Security Tab -->
        <section class="tab-content" id="account-security" role="tabpanel" aria-labelledby="tab-account-security" hidden>
          <div class="content-card">
            <h2>Account Security</h2>

            <nav class="security-tabs" role="tablist" aria-label="Account security tabs">
              <a
                href="#account-security/change-password"
                class="security-tab active"
                id="tab-change-password"
                role="tab"
                aria-selected="true"
                aria-controls="change-password"
                tabindex="0"
                >Change Password</a
              >
              <a
                href="#account-security/delete-account"
                class="security-tab"
                id="tab-delete-account"
                role="tab"
                aria-selected="false"
                aria-controls="delete-account"
                tabindex="-1"
                >Delete/Deactivate Account</a
              >
            </nav>

            <div class="security-content active" id="change-password" role="tabpanel" aria-labelledby="tab-change-password">
              <form class="settings-form" id="change-password-form" action="../../process/settings_process.php" method="post" novalidate>
                <div id="password-error" class="form-error-message" style="display: none;"></div>
                <input type="hidden" name="form_type" value="change_password">
                <div class="form-group">
                  <label for="current-password">Current Password</label>
                  <input id="current-password" name="current_password" type="password" placeholder="Enter current password" required />
                  <i class="fas fa-eye password-toggle" onclick="togglePasswordVisibility('current-password', this)"></i>
                </div>
                <div class="form-group">
                  <label for="new-password">New Password</label>
                  <input id="new-password" name="new_password" type="password" placeholder="Enter new password" required />
                  <i class="fas fa-eye password-toggle" onclick="togglePasswordVisibility('new-password', this)"></i>
                </div>
                <div class="form-group">
                  <label for="confirm-password">Confirm Password</label>
                  <input
                    id="confirm-password"
                    name="confirm_password"
                    type="password"
                    placeholder="Confirm new password"
                    required
                  />
                  <i class="fas fa-eye password-toggle" onclick="togglePasswordVisibility('confirm-password', this)"></i>
                </div>
                <button type="submit" class="save-button">SAVE CHANGES</button>
              </form>
            </div>

            <div class="security-content" id="delete-account" role="tabpanel" aria-labelledby="tab-delete-account" hidden>
              <div class="account-actions">
                <div class="action-section">
                  <h3>Deactivate Your Account</h3>
                  <p>
                    Deactivating your account will temporarily remove your profile and content from view.
                    You can reactivate your account at any time by simply logging back in with your username and password.
                  </p>
                  <button type="button" class="action-button" id="deactivate-btn">Deactivate Account</button>
                </div>
                <div class="action-section">
                  <h3>Delete Your Account</h3>
                  <p>
                    This will hide your account and all your content from view.
                    Your data will be kept in our system, but you won't be able to log in.
                    If you wish to use this account name again, you'll need to register with the same username or email,
                    but you will not have access to your previous articles or content.
                  </p>
                  <button type="button" class="action-button" id="delete-btn">Delete Account</button>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- Confirmation Modal -->
  <dialog id="confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="confirm-dialog-title" aria-describedby="confirm-dialog-desc">
    <h2 id="confirm-dialog-title"></h2>
    <p id="confirm-dialog-desc"></p>
    <div class="dialog-buttons">
      <button id="confirm-yes" class="action-button">Yes</button>
      <button id="confirm-no" class="action-button">No</button>
    </div>
  </dialog>

  <!-- Info Modal (for messages replacing alert) -->
  <dialog id="info-dialog" role="alertdialog" aria-modal="true" aria-labelledby="info-dialog-title" aria-describedby="info-dialog-desc">
    <h2 id="info-dialog-title"></h2>
    <p id="info-dialog-desc"></p>
    <div class="dialog-buttons">
      <button id="info-ok" class="action-button">OK</button>
    </div>
  </dialog>

  <!-- Include validation utilities -->
  <script src="../../assets/js/validation.js"></script>
  <script src="../../assets/js/settings.js"></script>
  
  <script>
    // Add a utility function to the global scope for updating profile images
    function updateProfileImageSrc(url) {
      console.log('Global updateProfileImageSrc called with:', url);
      
      // Process the URL to ensure correct path
      let imagePath = url;
      
      // If the URL is a relative path, ensure it points to the correct directory
      if (!url.startsWith('http') && !url.startsWith('/')) {
        // Extract only the filename portion from the URL
        const fileName = url.split('/').pop();
        
        // Always use the main assets path for profile images
        imagePath = '../../assets/images/profiles/' + fileName;
      }
      
      // Add a timestamp to prevent caching
      const cacheBustUrl = imagePath + '?v=' + new Date().getTime();
      
      console.log('Using profile image path:', imagePath);
      
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
      
      console.log('Updated all profile images with:', cacheBustUrl);
    }
    
    $(document).ready(function() {
      // Initialize profile image from database on page load
      // This ensures we start with the correct database image
      const initialProfileImage = '<?php echo $profileImage; ?>';
      if (initialProfileImage) {
        updateProfileImageSrc(initialProfileImage);
      }
      
      // Dump all image elements for debugging
      console.log('Found profile images:', $('.profile-image img').length);
      console.log('Found sidebar profile images:', $('.sidebar .profile-image img').length);
      
      // Handle clicking on profile image to change photo
      $('.profile-image-overlay').on('click', function() {
        console.log('Profile image clicked');
        $('#profile-photo-input').click();
      });
      
      // Handle profile image upload handling
      $('#profile-photo-input').on('change', function(e) {
        if (this.files && this.files[0]) {
          var file = this.files[0];
          
          // Show preview immediately
          var reader = new FileReader();
          reader.onload = function(e) {
            $('#profile-preview').attr('src', e.target.result);
          };
          reader.readAsDataURL(file);
          
          // Upload file
          var formData = new FormData();
          formData.append('form_type', 'update_profile_photo');
          formData.append('profile_photo', file);
          
          // Show uploading message
          showInfoDialog('Processing', 'Uploading your profile photo...');
          
          // Store the photo data for later use
          window.lastUploadedPhoto = {
            file: file,
            formData: formData
          };
          
          uploadProfilePhoto(formData);
        }
      });
      
      // Add a function to retry the upload if needed
      function uploadProfilePhoto(formData) {
        console.log('Starting photo upload...');
        
        $.ajax({
          url: '../../process/settings_process.php',
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function(response) {
            console.log('Upload response:', response);
            
            if (response.success) {
              // Get the photo URL from the response
              var photoUrl = response.photo_url;
              console.log('New photo URL from server:', photoUrl);
              
              // Save URL for future reference
              window.lastPhotoUrl = photoUrl;
              
              // Extract the filename, regardless of the path returned by the server
              const fileName = photoUrl.split('/').pop();
              
              // Always use the correct path for admin section
              const correctedUrl = '../../assets/images/profiles/' + fileName;
              console.log('Corrected photo URL:', correctedUrl);
              
              // Update all profile images using our utility function
              updateProfileImageSrc(correctedUrl);
              
              // Show success message
              showInfoDialog('Success', 'Profile photo updated successfully');
              
              // Trigger an event for other parts of the application
              document.dispatchEvent(new CustomEvent('profileImageUpdated', { 
                detail: { url: correctedUrl } 
              }));
            } else {
              $('#profile-error').text(response.message || 'An error occurred').show();
              console.error('Upload error:', response.message);
            }
          },
          error: function(xhr, status, error) {
            console.error('Upload failed:', error);
            console.error('Response text:', xhr.responseText);
            console.error('Status:', status);
            console.error('XHR:', xhr);
            $('#profile-error').text('Error uploading image: ' + error).show();
            
            // Try to parse the response to see if there's PHP error output
            if (xhr.responseText && xhr.responseText.indexOf('<') === 0) {
              $('#profile-error').text('Server error - please check your PHP configuration').show();
            }
          }
        });
      }
      
      // Listen for profile image updates from other parts of the application
      document.addEventListener('profileImageUpdated', function(e) {
        if (e.detail && e.detail.url) {
          updateProfileImageSrc(e.detail.url);
        }
      });
      
      // Handle hash and tabs
      function handleHash() {
        const hash = window.location.hash || '#edit-profile';
        const parts = hash.slice(1).split('/'); // remove #
        const mainTab = parts[0];
        const subTab = parts[1] || null;

        // Hide all tab content first
        $('.tab-content').removeClass('active').attr('hidden', true);
        
        // Show the selected tab content
        $('#' + mainTab).addClass('active').removeAttr('hidden');

        // Update dropdown menu if it exists
        $('.dropdown-menu li').removeClass('active');
        $(`.dropdown-menu li[data-tab="${mainTab}"]`).addClass('active');
        
        // Update sidebar menu items
        $('.submenu-item').removeClass('active');
        $(`.submenu-item[href*="#${mainTab}"]`).addClass('active');

        if (mainTab === 'account-security') {
          if (subTab === 'delete-account' || subTab === 'delete') {
            changeSecurityTab('delete-account');
          } else {
            changeSecurityTab('change-password');
          }
        }
      }

      // Security tab switch
      function changeSecurityTab(tabId) {
        $('.security-tab').removeClass('active').attr({
          'aria-selected': 'false',
          tabindex: '-1'
        });
        $(`.security-tab[href="#account-security/${tabId}"]`).addClass('active').attr({
          'aria-selected': 'true',
          tabindex: '0'
        }).focus();

        $('.security-content').removeClass('active').attr('hidden', true);
        $('#' + tabId).addClass('active').removeAttr('hidden');
      }

      $(window).on('hashchange', handleHash);
      handleHash();

      // Handle profile form submission via AJAX
      $('#edit-profile-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
          type: "POST",
          url: "../../process/settings_process.php",
          data: formData,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function(response) {
            if (response.success) {
              // Update profile information across the page
              updateProfileInfo(response.userData);
              
              // Only show the modal success message, not the top notification
              showInfoDialog('Success', 'Profile updated successfully');
              
              // Hide any previous error messages
              $('#profile-error').hide();
            } else {
              // Show error message in the top notification
              $('#profile-error').text(response.message || 'An error occurred').show();
            }
          },
          error: function() {
            $('#profile-error').text('An unexpected error occurred').show();
          }
        });
      });
      
      // Function to update all profile information elements on the page
      function updateProfileInfo(userData) {
        // Update text information
        $('.profile-info h3').text(userData.first_name + ' ' + userData.last_name);
        $('.profile-info p').text(userData.username);
        
        // Update sidebar text information
        $('.sidebar .user-info small:first-child').text(userData.first_name + ' ' + userData.last_name);
        $('.sidebar .user-info .user-subtitle').text(userData.email);
        
        // Only update image if we have a profile photo
        if (userData.profile_photo) {
          const photoUrl = '../../assets/images/profiles/' + userData.profile_photo;
          updateProfileImageSrc(photoUrl);
        }
        
        // Update other header/UI elements if they exist
        if ($('.user-info-header').length) {
          $('.user-info-header .user-name').text(userData.first_name + ' ' + userData.last_name);
        }
        
        if ($('.sidebar-username').length) {
          $('.sidebar-username').text(userData.username);
        }
        
        if ($('.header-user-name').length) {
          $('.header-user-name').text(userData.first_name);
        }
      }

      // Confirmation dialog logic
      const $confirmDialog = $('#confirm-dialog');
      const $confirmTitle = $('#confirm-dialog-title');
      const $confirmDesc = $('#confirm-dialog-desc');
      const $confirmYes = $('#confirm-yes');
      const $confirmNo = $('#confirm-no');

      function showConfirmDialog(title, message, onConfirm) {
        $confirmTitle.text(title);
        $confirmDesc.text(message);

        function yesHandler() {
          onConfirm();
          $confirmDialog[0].close();
          cleanup();
        }

        function noHandler() {
          $confirmDialog[0].close();
          cleanup();
        }

        function cleanup() {
          $confirmYes.off('click', yesHandler);
          $confirmNo.off('click', noHandler);
        }

        $confirmYes.on('click', yesHandler);
        $confirmNo.on('click', noHandler);

        $confirmDialog[0].showModal();
      }

      // Info dialog for replacement of alert
      const $infoDialog = $('#info-dialog');
      const $infoTitle = $('#info-dialog-title');
      const $infoDesc = $('#info-dialog-desc');
      const $infoOk = $('#info-ok');

      function showInfoDialog(title, message) {
        $infoTitle.text(title);
        $infoDesc.text(message);

        function okHandler() {
          $infoDialog[0].close();
          $infoOk.off('click', okHandler);
        }

        $infoOk.on('click', okHandler);
        $infoDialog[0].showModal();
      }
      
      // Password Change Form Validation & Submission
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
        $submitButton.prop('disabled', true).text('Processing...');
        
        // Submit the form using AJAX
        $.ajax({
          type: "POST",
          url: "../../process/settings_process.php",
          data: formData,
          dataType: "json",
          success: function(response) {
            if (response.success) {
              // Clear the form
              $("#change-password-form")[0].reset();
              
              // Clear all validation messages and indicators
              formUtils.clearErrors();
              $(".password-strength").remove();
              $("#password-error").hide();
              
              // Show success message
              showInfoDialog('Success', 'Password changed successfully!');
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
            $submitButton.prop('disabled', false).text('SAVE CHANGES');
          },
          error: function(xhr, status, error) {
            $("#password-error").text("An error occurred: " + error).show();
            $submitButton.prop('disabled', false).text('SAVE CHANGES');
          }
        });
      });

      $('#deactivate-btn').on('click', function() {
        showConfirmDialog(
          'Confirm Account Deactivation',
          'Are you sure you want to deactivate your account? You can reactivate it later by logging in.',
          function() {
            // Send ajax request to deactivate account
            $.ajax({
              type: "POST",
              url: "../../process/settings_process.php",
              data: { form_type: "deactivate_account" },
              dataType: "json",
              success: function(response) {
                if (response.success) {
                  window.location.href = "../../auth/login.php";
                } else {
                  showInfoDialog('Error', response.message || 'An error occurred');
                }
              },
              error: function() {
                showInfoDialog('Error', 'An unexpected error occurred');
              }
            });
          }
        );
      });

      $('#delete-btn').on('click', function() {
        showConfirmDialog(
          'Confirm Account Deletion',
          'Are you sure you want to delete your account? Your account will be hidden but your data will be kept in our system. If you register again, you will not have access to your previous content.',
          function() {
            // Send ajax request to delete account
            $.ajax({
              type: "POST",
              url: "../../process/settings_process.php",
              data: { form_type: "delete_account" },
              dataType: "json",
              success: function(response) {
                if (response.success) {
                  window.location.href = "../../auth/login.php";
                } else {
                  showInfoDialog('Error', response.message || 'An error occurred');
                }
              },
              error: function() {
                showInfoDialog('Error', 'An unexpected error occurred');
              }
            });
          }
        );
      });

      // Trap focus inside dialogs while open
      function trapFocus($dialog) {
        $dialog.on('keydown', function(e) {
          if (e.key === 'Escape') {
            e.preventDefault();
            $dialog[0].close();
          }
          else if (e.key === 'Tab') {
            const focusable = $dialog.find('button:visible').toArray();
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (e.shiftKey) {
              if (document.activeElement === first) {
                e.preventDefault();
                last.focus();
              }
            } else {
              if (document.activeElement === last) {
                e.preventDefault();
                first.focus();
              }
            }
          }
        });
      }
      trapFocus($confirmDialog);
      trapFocus($infoDialog);

      // Check for stored profile image URL and apply it
      const storedProfileImageUrl = sessionStorage.getItem('profileImageUrl');
      if (storedProfileImageUrl) {
        console.log('Found stored profile image URL:', storedProfileImageUrl);
        updateProfileImageSrc(storedProfileImageUrl);
      }
    });
    
    // Password visibility toggle function
    function togglePasswordVisibility(inputId, icon) {
      const input = document.getElementById(inputId);
      
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }
  </script>
  
  <!-- Include the footer for proper script loading -->
  <?php include '../includes/footer.php'; ?>
</body>
</html>
