<?php
/**
 * Admin Login page for SariSari Stories
 */

// Start the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once 'includes/admin-auth.php';

// Check if user is already logged in as admin
if (is_admin()) {
    header('Location: index.php');
    exit;
}

// Initialize variables
$username_email = '';
$error = '';
$success = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username_email = sanitize($_POST['username_email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate form data
    if (empty($username_email) || empty($password)) {
        $error = 'Please enter both username/email and password.';
    } else {
        // Attempt to log in
        $result = login_user($username_email, $password);
        
        if (is_array($result)) {
            // Login successful, now check if user is an admin
            $user_id = $result['id'];
            $sql = "SELECT is_admin FROM users WHERE id = ?";
            $admin_check = fetch_one($sql, [$user_id]);
            
            if ($admin_check && isset($admin_check['is_admin']) && $admin_check['is_admin'] == 1) {
                // User is an admin, start session
                start_session($result);
                
                // Redirect to admin dashboard
                header('Location: index.php');
                exit;
            } else {
                // Not an admin
                $error = "You don't have permission to access the admin area.";
            }
        } else {
            // Login failed
            $error = $result;
        }
    }
}

// Set the current year for copyright
$current_year = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - SariSari Stories</title>
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="css/admin-styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background-color: #f5f5f5;
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    .admin-login-container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .admin-login-box {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
      padding: 30px;
    }
    
    .admin-login-logo {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .admin-login-logo span {
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem;
      font-weight: 700;
      color: #2c3e50;
    }
    
    .admin-login-logo .admin-badge {
      background-color: #e74c3c;
      color: #fff;
      padding: 3px 10px;
      border-radius: 10px;
      font-size: 0.7rem;
      margin-left: 10px;
      text-transform: uppercase;
      font-family: 'Poppins', sans-serif;
    }
    
    .admin-login-title {
      text-align: center;
      margin-bottom: 20px;
    }
    
    .admin-login-title h1 {
      color: #2c3e50;
      font-size: 1.5rem;
      margin: 0;
    }
    
    .admin-login-form .form-group {
      margin-bottom: 20px;
    }
    
    .admin-login-form label {
      display: block;
      margin-bottom: 8px;
      color: #2c3e50;
      font-weight: 500;
    }
    
    .admin-login-form input {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 0.95rem;
      transition: border-color 0.3s;
    }
    
    .admin-login-form input:focus {
      border-color: #3498db;
      outline: none;
    }
    
    .admin-login-form button {
      width: 100%;
      padding: 12px;
      background-color: #3498db;
      color: #fff;
      border: none;
      border-radius: 4px;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    
    .admin-login-form button:hover {
      background-color: #2980b9;
    }
    
    .admin-login-links {
      text-align: center;
      margin-top: 20px;
      font-size: 0.9rem;
    }
    
    .admin-login-links a {
      color: #3498db;
      text-decoration: none;
    }
    
    .admin-login-links a:hover {
      text-decoration: underline;
    }
    
    .admin-login-footer {
      text-align: center;
      padding: 15px;
      color: #7f8c8d;
      font-size: 0.8rem;
      background-color: #2c3e50;
      color: #ecf0f1;
    }
    
    .alert {
      padding: 12px;
      border-radius: 4px;
      margin-bottom: 20px;
      text-align: center;
    }
    
    .alert-error {
      background-color: #fdedeb;
      color: #c0392b;
      border-left: 4px solid #e74c3c;
    }
    
    .alert-success {
      background-color: #d5f5e3;
      color: #27ae60;
      border-left: 4px solid #2ecc71;
    }
  </style>
</head>
<body>
  <div class="admin-login-container">
    <div class="admin-login-box">
      <div class="admin-login-logo">
        <span>SariSari Stories<span class="admin-badge">Admin</span></span>
      </div>
      
      <div class="admin-login-title">
        <h1>Admin Login</h1>
      </div>
      
      <?php if (!empty($error)): ?>
        <div class="alert alert-error">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>
      
      <?php if (!empty($success)): ?>
        <div class="alert alert-success">
          <?php echo $success; ?>
        </div>
      <?php endif; ?>
      
      <form action="login.php" method="POST" class="admin-login-form">
        <div class="form-group">
          <label for="username_email">Username or Email</label>
          <input type="text" id="username_email" name="username_email" value="<?php echo htmlspecialchars($username_email); ?>" required>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
          <button type="submit">Log In</button>
        </div>
      </form>
      
      <div class="admin-login-links">
        <a href="../index.php">Return to Site</a>
      </div>
    </div>
  </div>
  
  <div class="admin-login-footer">
    <p>&copy; <?php echo $current_year; ?> SariSari Stories. All rights reserved.</p>
  </div>

  <script src="../js/main.js"></script>
</body>
</html>