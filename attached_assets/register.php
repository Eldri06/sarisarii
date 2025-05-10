<?php

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

// Initialize variables
$username = '';
$email = '';
$full_name = '';
$error = '';
$success = false;
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/';

// main bckend process regis 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // get fmorm inouts
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // validation
    if (empty($username) || empty($email) || empty($full_name) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Attempt to register user
        $result = register_user($username, $email, $password, $full_name);
        
        if (is_numeric($result)) {
            // Success - get user data and start session
            $user = get_user($result);
            if ($user) {
                start_session($user);
                
                // Redirect to requested page or home redirect na pa index.php
                $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '/';
                header("Location: index.php?redirect=" . urlencode($redirect));
                exit;
            } else {
                $success = true; // Registration successful but auto-login failed
            }
        } else {
            // Error - display message
            $error = $result;
        }
    }
}

// Page title
$page_title = 'Register - ' . SITE_NAME;

// for bacgkround cover sa regis 
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
        <div class="auth-form" style="max-width: 800px;">
          <div class="logo" style="justify-content: center; margin-bottom: 20px;">
            <a href="/">
              <span>SariSari Stories</span>
            </a>
          </div>
          
          <h2>Create an Account</h2>
          
          <?php if (!empty($error)): ?>
            <div class="error" style="text-align: center; margin-bottom: 15px; color: #e74c3c;">
              <?php echo $error; ?>
            </div>
          <?php endif; ?>
          
          <?php if ($success): ?>
            <div class="success" style="text-align: center; margin-bottom: 15px; color: #27ae60;">
              Registration successful! You can now <a href="/login.php">login</a>.
            </div>
          <?php else: ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
              <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
              
              <div class="form-row">
                <div class="form-group">
                  <label for="username">Username</label>
                  <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
                
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
              </div>
              
              <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
              </div>
              
              <div class="form-row">
                <div class="form-group">
                  <label for="password">Password</label>
                  <input type="password" id="password" name="password" required>
                  <small>At least 8 characters</small>
                </div>
                
                <div class="form-group">
                  <label for="confirm_password">Confirm Password</label>
                  <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
              </div>
              
              <div class="form-group">
                <button type="submit" class="primary-btn" style="width: 100%;">Register</button>
              </div>
              
              <div class="auth-links">
                <p>Already have an account? <a href="login.php<?php echo !empty($redirect) && $redirect != '/' ? '?redirect=' . urlencode($redirect) : ''; ?>">Login</a></p>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  
  <script src="js/main.js"></script>
</body>
</html>
