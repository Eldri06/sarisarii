<?php
// Include configuration and functions
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}


$username_email = '';
$error = '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $username_email = sanitize($_POST['username_email']);
    $password = $_POST['password'];
    
    // validation sa db
    if (empty($username_email) || empty($password)) {
        $error = 'All fields are required';
    } else {
        // Attempt to login user
        $result = login_user($username_email, $password);
        
        if (is_array($result)) {
            // Success , get user data 
            start_session($result);
            
            // Redirect sa index.php 
            $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '/';
            header("Location: index.php?redirect=" . urlencode($redirect));
            exit;
        } else {
            // Error msg
            $error = $result;
        }
    }
}


$page_title = 'Login - ' . SITE_NAME;

// bg for login page
$additional_head = <<<HTML
<style>
    body {
    background-image: url('https://images.unsplash.com/photo-1669554017518-45d0337356f2?q=80&w=1632&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&fbclid=IwZXh0bgNhZW0CMTEAAR7EkTCyoJl45dnNSSqM29mJADIB2MZK2rtUENzlVc4Re1QuIAta58IUndKjLQ_aem_1QH-HjkmCmede-BO5e-M5g');
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
    background-attachment: fixed;
}
</style>
HTML;


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $page_title; ?></title>
  <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php echo $additional_head; ?>
</head>
<body>
  <div class="auth-page">
    <div class="container">
      <div class="auth-container">
        <div class="auth-form">
          <div class="logo" style="justify-content: center; margin-bottom: 20px;">
            <a href="index.php">
              <span>SariSari Stories</span>
            </a>
          </div>
          
          <h2>Login to Your Account</h2>
          
          <?php if (!empty($error)): ?>
            <div class="error" style="text-align: center; margin-bottom: 15px; color: #e74c3c;">
              <?php echo $error; ?>
            </div>
          <?php endif; ?>
          
          <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
            
            <div class="form-group">
              <label for="username_email">Username or Email</label>
              <input type="text" id="username_email" name="username_email" value="<?php echo htmlspecialchars($username_email); ?>" required>
            </div>
            
            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
              <button type="submit" class="primary-btn" style="width: 100%;">Login</button>
            </div>
            
            <div class="auth-links">
              <p>Don't have an account? <a href="register.php<?php echo !empty($redirect) && $redirect != '/' ? '?redirect=' . urlencode($redirect) : ''; ?>">Register</a></p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  
  <script src="/js/main.js"></script>
</body>
</html>
