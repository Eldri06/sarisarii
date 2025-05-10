<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Get current user if logged in
$current_user = get_current_user_data();

// Determine active page
$current_page = basename($_SERVER['PHP_SELF']);
$nav_items = get_navigation_items();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
  <meta name="description" content="<?php echo isset($page_description) ? $page_description : SITE_DESCRIPTION; ?>">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <?php if (isset($additional_head)) echo $additional_head; ?>
</head>
<body>
  <!-- Header Section -->
  <header id="main-header">
    <div class="container">
      <div class="logo">
        <a href="index.php"> 
          <span>SariSari Stories</span>
        </a>
      </div>
      <nav>
        <ul>
          <?php foreach ($nav_items as $title => $url): 
            // Determine if this nav item should be marked as active
            $active_class = '';
            
            if (strpos($url, '#') !== false) {
                // If URL contains an anchor, check just the filename
                $file_part = explode('#', $url)[0];
                if ($file_part == $current_page) {
                    $active_class = 'active';
                }
            } else if ($url == $current_page) {
                $active_class = 'active';
            }
          ?>
            <li><a href="<?php echo $url; ?>" class="<?php echo $active_class; ?>"><?php echo $title; ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>
      <div class="header-actions">
        <a href="search.php" class="search-btn"><i class="fas fa-search"></i></a>
        <?php if ($current_user): ?>
          <div class="user-menu">
            <a href="#" class="user-menu-trigger">
              <img src="<?php echo !empty($current_user['profile_image']) ? $current_user['profile_image'] : 'images/default-avatar.jpg'; ?>" alt="<?php echo $current_user['username']; ?>" class="avatar-small">
              <span><?php echo $current_user['username']; ?></span>
            </a>
            <div class="user-dropdown">
              <ul>
                <li><a href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="create-story.php"><i class="fas fa-edit"></i> Create Story</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
              </ul>
            </div>
          </div>
        <?php else: ?>
          <div class="auth-buttons">
            <a href="login.php" class="login-btn">Login</a>
            <a href="create-story.php" class="share-story-btn">Share a Story</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </header>
