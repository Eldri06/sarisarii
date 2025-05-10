<?php
/**
 * Create Admin User for SariSari Stories
 * 
 * This script is used to create the first admin user.
 * After creating the first admin, this file should be deleted for security.
 */

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if form was submitted
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    $setup_key = $_POST['setup_key'] ?? '';
    
    // Very basic setup key validation (in a real app, use a more secure method)
    $valid_setup_key = 'sarisari_setup_2023';
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif ($setup_key !== $valid_setup_key) {
        $error = "Invalid setup key";
    } else {
        // Check if an admin already exists
        $admin_check = fetch_one("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
        
        if ($admin_check && $admin_check['count'] > 0) {
            $error = "An admin user already exists. This script can only be used to create the first admin.";
        } else {
            // Register user
            $user_id = register_user($username, $email, $password, $full_name);
            
            if (is_numeric($user_id)) {
                // Update the user to be an admin
                $result = update('users', ['is_admin' => 1], 'id = ?', [$user_id]);
                
                if ($result) {
                    $message = "Admin user created successfully! You can now log in using your credentials.";
                    $message .= "<br><strong>Important:</strong> Delete this file (create-admin.php) immediately for security reasons.";
                } else {
                    $error = "Failed to set admin privileges. Please try again.";
                }
            } else {
                // Registration failed
                $error = $user_id; // Error message returned from register_user function
            }
        }
    }
}

// Check if an admin already exists
$admin_exists = false;
$admin_check = fetch_one("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
if ($admin_check && $admin_check['count'] > 0) {
    $admin_exists = true;
}

// Set the current year for copyright
$current_year = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Admin User - SariSari Stories</title>
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
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    .admin-setup-container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .admin-setup-box {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 500px;
      padding: 30px;
    }
    
    .admin-setup-logo {
      text-align: center;
      margin-bottom: 30px;
    }
    
    .admin-setup-logo span {
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem;
      font-weight: 700;
      color: #2c3e50;
    }
    
    .admin-setup-logo .admin-badge {
      background-color: #e74c3c;
      color: #fff;
      padding: 3px 10px;
      border-radius: 10px;
      font-size: 0.7rem;
      margin-left: 10px;
      text-transform: uppercase;
      font-family: 'Poppins', sans-serif;
    }
    
    .admin-setup-title {
      text-align: center;
      margin-bottom: 20px;
    }
    
    .admin-setup-title h1 {
      color: #2c3e50;
      font-size: 1.5rem;
      margin: 0 0 10px 0;
    }
    
    .admin-setup-title p {
      color: #7f8c8d;
      font-size: 0.9rem;
      margin: 0;
    }
    
    .admin-setup-form .form-group {
      margin-bottom: 20px;
    }
    
    .admin-setup-form label {
      display: block;
      margin-bottom: 8px;
      color: #2c3e50;
      font-weight: 500;
    }
    
    .admin-setup-form input {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 0.95rem;
      transition: border-color 0.3s;
    }
    
    .admin-setup-form input:focus {
      border-color: #3498db;
      outline: none;
    }
    
    .form-hint {
      font-size: 0.8rem;
      color: #7f8c8d;
      margin-top: 5px;
    }
    
    .admin-setup-form button {
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
    
    .admin-setup-form button:hover {
      background-color: #2980b9;
    }
    
    .admin-setup-links {
      text-align: center;
      margin-top: 20px;
      font-size: 0.9rem;
    }
    
    .admin-setup-links a {
      color: #3498db;
      text-decoration: none;
    }
    
    .admin-setup-links a:hover {
      text-decoration: underline;
    }
    
    .admin-setup-footer {
      text-align: center;
      padding: 15px;
      font-size: 0.8rem;
      background-color: #2c3e50;
      color: #ecf0f1;
    }
    
    .alert {
      padding: 15px;
      border-radius: 4px;
      margin-bottom: 20px;
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
    
    .alert-warning {
      background-color: #fef9e7;
      color: #f39c12;
      border-left: 4px solid #f1c40f;
    }
  </style>
</head>
<body>
  <div class="admin-setup-container">
    <div class="admin-setup-box">
      <div class="admin-setup-logo">
        <span>SariSari Stories<span class="admin-badge">Admin</span></span>
      </div>
      
      <div class="admin-setup-title">
        <h1>Create Admin User</h1>
        <p>Set up the first administrator account for your site</p>
      </div>
      
      <?php if ($admin_exists): ?>
        <div class="alert alert-warning">
          <p><strong>Notice:</strong> An administrator account already exists.</p>
          <p>This script is only for the initial setup. Please log in using the admin credentials or contact the site administrator if you need assistance.</p>
          <div class="admin-setup-links">
            <a href="login.php">Go to Admin Login</a> | 
            <a href="../index.php">Return to Site</a>
          </div>
        </div>
      <?php else: ?>
        <?php if (!empty($error)): ?>
          <div class="alert alert-error">
            <?php echo $error; ?>
          </div>
        <?php endif; ?>
        
        <?php if (!empty($message)): ?>
          <div class="alert alert-success">
            <?php echo $message; ?>
            <div class="admin-setup-links">
              <a href="login.php">Go to Admin Login</a>
            </div>
          </div>
        <?php else: ?>
          <form action="create-admin.php" method="POST" class="admin-setup-form">
            <div class="form-group">
              <label for="username">Username *</label>
              <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
              <label for="email">Email Address *</label>
              <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
              <label for="full_name">Full Name *</label>
              <input type="text" id="full_name" name="full_name" required>
            </div>
            
            <div class="form-group">
              <label for="password">Password *</label>
              <input type="password" id="password" name="password" required>
              <p class="form-hint">Password must be at least 8 characters long</p>
            </div>
            
            <div class="form-group">
              <label for="confirm_password">Confirm Password *</label>
              <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
              <label for="setup_key">Setup Key *</label>
              <input type="text" id="setup_key" name="setup_key" required>
              <p class="form-hint">Enter the setup key provided by the site owner</p>
            </div>
            
            <div class="form-group">
              <button type="submit">Create Admin Account</button>
            </div>
          </form>
          
          <div class="admin-setup-links">
            <a href="../index.php">Return to Site</a>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
  
  <div class="admin-setup-footer">
    <p>&copy; <?php echo $current_year; ?> SariSari Stories. All rights reserved.</p>
  </div>

  <script src="../js/main.js"></script>
</body>
</html>