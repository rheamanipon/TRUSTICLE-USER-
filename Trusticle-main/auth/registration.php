<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trusticle Registration</title>
  <!-- Google Fonts - Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="registration.css">
  <link rel="stylesheet" href="../assets/css/validation.css">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    /* Hide default password toggle */
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
      display: none;
    }
    input[type="password"]::-webkit-credentials-auto-fill-button {
      display: none !important;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="register-card">
      <!-- Left Panel - Registration Form -->
      <div class="form-panel">
        <h1 class="welcome-heading">WELCOME!</h1>
        
        <form id="registerForm">
          <div class="name-fields-container">
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" id="firstName" name="firstName" placeholder="First Name" required>
            </div>

            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" id="lastName" name="lastName" placeholder="Last Name" required>
            </div>
          </div>

          <div class="input-field">
            <i class="fas fa-user"></i>
            <input type="text" id="username" name="username" placeholder="Username" required>
            <div class="validation-tooltip">
              <i class="fas fa-question-circle"></i>
              <span class="tooltip-text">Username must be at least 4 characters and can only contain letters, numbers, underscores, and periods.</span>
            </div>
          </div>

          <div class="input-field">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" placeholder="Email" required>
          </div>

          <div class="input-field">
            <i class="fas fa-calendar-alt"></i>
            <input type="date" id="birthdate" name="birthdate" placeholder="Birthdate" required>
          </div>

          <div class="input-field">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <div class="validation-tooltip">
              <i class="fas fa-question-circle"></i>
              <span class="tooltip-text">Password must be at least 8 characters long and include 3 of the following: lowercase letters, uppercase letters, numbers, and special characters.</span>
            </div>
            <i class="fas fa-eye password-toggle" onclick="togglePasswordVisibility('password', this)"></i>
          </div>

          <div class="input-field">
            <i class="fas fa-lock"></i>
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
            <i class="fas fa-eye password-toggle" onclick="togglePasswordVisibility('confirmPassword', this)"></i>
          </div>

          <div class="terms-checkbox">
            <input type="checkbox" id="terms" required>
            <label for="terms">By creating an account, you agree to the T&C</label>
          </div>

          <button type="submit" class="signup-btn">Sign Up</button>
        </form>

        <div class="social-signup">
          <p>or sign up with</p>
          <div class="social-icons">
            <a href="#" class="social-icon google"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v5/icons/google.svg" alt="Google"></a>
            <a href="#" class="social-icon facebook"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v5/icons/facebook.svg" alt="Facebook"></a>
          </div>
        </div>

        <div class="signin-prompt">
          <p>Already have an account? <a href="login.php">Sign in</a></p>
        </div>
      </div>
      
      <!-- Right Panel - Logo and About -->
      <div class="logo-panel">
        <div class="logo-container">
          <div class="logo">
              <div class="logo">
                    <img src="../user/assets/images/logo.png" alt="Trusticle Logo" class="login-logo">
                    </div>
          </div> </br>
          <button class="about-btn">About Us!</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Success Modal -->
  <div id="successModal" class="modal">
    <div class="modal-content">
      <div class="success-icon">
        <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="40" cy="40" r="39" stroke="#8CE99A" stroke-width="2"/>
          <path d="M24 40L36 52L56 28" stroke="#8CE99A" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <h2 class="success-heading">Account Completed Successfully!</h2>
      <p class="success-message">Congratulations! You can now log in to your account.</p>
      <button class="login-btn">Go to Login</button>
    </div>
  </div>

  <!-- Include validation utilities -->
  <script src="../assets/js/validation.js"></script>
  <!-- Include auth.js -->
  <script src="auth.js"></script>
  
  <!-- Password visibility toggle script -->
  <script>
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
  
  <!-- Datepicker fix script -->
  <script>
    $(document).ready(function() {
      // Fix for datepicker not closing when clicking outside
      $(document).on('click', function(e) {
        // If the click is not on the datepicker and not on the datepicker input
        if (!$(e.target).closest('input[type=date]').length && !$(e.target).hasClass('calendar-dropdown')) {
          // Force close any open datepicker by removing focus
          $('input[type=date]').blur();
        }
      });
      
      // Additional check to ensure the datepicker closes when focusing another element
      $('input:not([type=date]), button, a').on('focus', function() {
        $('input[type=date]').blur();
      });
    });
  </script>
</body>
</html>