<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include admin authentication
require_once 'admin-auth.php';

// Require admin privileges
require_admin();

// Get current user
$current_user = $_SESSION['user'];

// Current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? $page_title . ' - Admin Dashboard' : 'Admin Dashboard'; ?></title>
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="css/admin-styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php if (isset($additional_head)) echo $additional_head; ?>
</head>
<body class="admin-interface">
  <!-- Admin Header -->
  <header id="admin-header">
    <div class="container">
      <div class="admin-logo">
        <a href="index.php">
          <span>SariSari Stories</span>
          <span class="admin-badge">Admin</span>
        </a>
      </div>
      <div class="admin-user-info">
        <div class="admin-user-menu">
          <a href="#" class="admin-user-trigger">
            <img src="<?php echo !empty($current_user['profile_image']) ? '../' . $current_user['profile_image'] : '../images/default-avatar.jpg'; ?>" alt="<?php echo $current_user['username']; ?>" class="avatar-small">
            <span><?php echo $current_user['username']; ?></span>
            <i class="fas fa-chevron-down"></i>
          </a>
          <div class="admin-user-dropdown">
            <ul>
              <li><a href="../profile.php"><i class="fas fa-user"></i> My Profile</a></li>
              <li><a href="../index.php"><i class="fas fa-home"></i> Visit Site</a></li>
              <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </header>
  
  <!-- Admin Content Wrapper -->
  <div class="admin-wrapper">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
      <nav class="admin-nav">
        <ul>
          <li>
            <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
              <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
          </li>
          <li>
            <a href="users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
              <i class="fas fa-users"></i> Users
            </a>
          </li>
          <li>
            <a href="stories.php" class="<?php echo $current_page == 'stories.php' ? 'active' : ''; ?>">
              <i class="fas fa-book"></i> Stories
            </a>
          </li>
          <li>
            <a href="categories.php" class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
              <i class="fas fa-tags"></i> Categories
            </a>
          </li>
          <li>
            <a href="comments.php" class="<?php echo $current_page == 'comments.php' ? 'active' : ''; ?>">
              <i class="fas fa-comments"></i> Comments
            </a>
          </li>
          <li>
            <a href="subscribers.php" class="<?php echo $current_page == 'subscribers.php' ? 'active' : ''; ?>">
              <i class="fas fa-envelope"></i> Subscribers
            </a>
          </li>
          <li>
            <a href="settings.php" class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
              <i class="fas fa-cog"></i> Settings
            </a>
          </li>
        </ul>
      </nav>
    </aside>
    
    <!-- Main Content Area -->
    <main class="admin-content">
      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
          <?php 
          echo $_SESSION['success_message']; 
          unset($_SESSION['success_message']);
          ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
          <?php 
          echo $_SESSION['error_message']; 
          unset($_SESSION['error_message']);
          ?>
        </div>
      <?php endif; ?>