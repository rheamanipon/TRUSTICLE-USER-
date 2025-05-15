<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trusticle Login</title>
  <!-- Google Fonts - Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="login.css">
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
    <div class="login-card">
      <!-- Left Panel - Login Form -->
      <div class="left-panel">
        <div class="logo">
          <div class="logo">
                <img src="../user/assets/images/logo2.png" alt="Trusticle Logo" class="login-logo">
                </div>
        </div>
        
        <h2 class="login-heading">LOGIN</h2>
        
        <form id="loginForm" class="login-form">
          <div id="loginError" class="form-error-message" style="display: none;"></div>
          
          <div class="input-field">
            <i class="fas fa-user"></i>
            <input type="text" id="username" name="username" placeholder="Email or Username" required>
          </div>
          
          <div class="input-field">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <i class="fas fa-eye password-toggle" onclick="togglePasswordVisibility('password', this)"></i>
          </div>
          
          <div class="forgot-password">
            <a href="#">Forgot your password?</a>
          </div>
          
          <button type="submit" class="login-btn">Login Now</button>
        </form>
        
        <div class="login-divider">
          <span>Login with Others</span>
        </div>
        
        <div class="social-logins">
          <button class="social-btn google">
            <i class="fab fa-google"></i>
            <span>Google</span>
          </button>
          
          <button class="social-btn facebook">
            <i class="fab fa-facebook-f"></i>
            <span>Facebook</span>
          </button>
        </div>
        
        <div class="signup-prompt">
          <p>Don't you have an account? <a href="registration.php">Sign up</a></p>
        </div>
      </div>
      
      <!-- Right Panel - Image and Text -->
      <div class="right-panel">
        <div class="overlay">
          <div class="message">
            <h2>Empowering You to Stop the Spread of Misinformation.</h2>
          </div>
        </div>
      </div>
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
</body>
</html>