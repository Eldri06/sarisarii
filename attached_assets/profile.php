<?php
// Include configuration and functions
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Require login to access this page
require_login();

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get current user
$current_user = get_current_user_data(true);
$user_id = $current_user['id'];

// Check if profile belongs to current user or viewing another user
$username = isset($_GET['username']) ? $_GET['username'] : $current_user['username'];
$is_own_profile = ($current_user['username'] === $username);

// Get user data
if ($is_own_profile) {
    $user = $current_user;
} else {
    $user = get_user($username);
    if (!$user) {
        // User not found, redirect to home
        header('Location: /');
        exit;
    }
}

// Initialize variables
$success_message = '';
$error_message = '';

// Process profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_own_profile) {
    // Check what form was submitted
    if (isset($_POST['update_profile'])) {
        // Profile update form
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $bio = sanitize($_POST['bio']);
        
        // Handle profile image upload
        $profile_image = $user['profile_image'];
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $new_image = upload_image($_FILES['profile_image'], 'uploads/profile');
            if ($new_image) {
                $profile_image = $new_image;
            } else {
                $error_message = "Error uploading image. Please try again.";
            }
        }
        
        // Prepare update data
        $update_data = [
            'full_name' => $full_name,
            'email' => $email,
            'bio' => $bio,
            'profile_image' => $profile_image
        ];
        
        // Update profile
        $result = update_profile($user_id, $update_data);
        
        if ($result === true) {
            $success_message = "Profile updated successfully.";
            
            // Refresh user data
            $current_user = get_current_user_data(true);
            $user = $current_user;
        } else {
            $error_message = $result;
        }
    } elseif (isset($_POST['update_password'])) {
        // Password update form
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "All fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $error_message = "Password must be at least 8 characters long.";
        } else {
            // Verify current password
            $sql = "SELECT password FROM users WHERE id = ?";
            $user_pwd = fetch_one($sql, [$user_id]);
            
            if ($user_pwd && password_verify($current_password, $user_pwd['password'])) {
                // Update password
                $update_data = [
                    'password' => $new_password
                ];
                
                $result = update_profile($user_id, $update_data);
                
                if ($result === true) {
                    $success_message = "Password updated successfully.";
                } else {
                    $error_message = $result;
                }
            } else {
                $error_message = "Current password is incorrect.";
            }
        }
    }
}

// Get user's stories
$user_stories = get_stories_by_user($user['id']);

// Count user's stories
$story_count = count_user_stories($user['id']);

// Page title
$page_title = $is_own_profile ? 'My Profile - ' . SITE_NAME : $user['full_name'] . ' - ' . SITE_NAME;

// Additional scripts
$additional_scripts = <<<HTML
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Profile image preview
    const fileInput = document.getElementById('profile_image');
    if (fileInput) {
      fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
          const reader = new FileReader();
          const preview = document.querySelector('.profile-image-preview');
          
          reader.addEventListener('load', function() {
            preview.src = this.result;
          });
          
          reader.readAsDataURL(file);
        }
      });
    }
  });
</script>
HTML;

// Include header
require_once '../includes/header.php';
?>

<div class="page-content">
  <div class="container">
    <?php if (!empty($success_message)): ?>
      <div class="alert success">
        <?php echo $success_message; ?>
      </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
      <div class="alert error">
        <?php echo $error_message; ?>
      </div>
    <?php endif; ?>
    
    <div class="profile-header">
      <img src="<?php echo !empty($user['profile_image']) ? $user['profile_image'] : 'images/default-avatar.jpg'; ?>" alt="<?php echo $user['full_name']; ?>" class="profile-image">
      
      <div class="profile-info">
        <h1><?php echo $user['full_name']; ?></h1>
        <p class="username">@<?php echo $user['username']; ?></p>
        
        <?php if (!empty($user['bio'])): ?>
          <p><?php echo $user['bio']; ?></p>
        <?php endif; ?>
        
        <div class="profile-stats">
          <div class="stat-item">
            <span class="stat-value"><?php echo $story_count; ?></span>
            <span class="stat-label">Stories</span>
          </div>
          
          <div class="stat-item">
            <span class="stat-value"><?php echo format_date($user['created_at'], 'M Y'); ?></span>
            <span class="stat-label">Joined</span>
          </div>
        </div>
      </div>
    </div>
    
    <div class="profile-tabs">
      <ul>
        <li><a href="#stories" class="active">Stories</a></li>
        <?php if ($is_own_profile): ?>
        <li><a href="#edit-profile">Edit Profile</a></li>
        <li><a href="#change-password">Change Password</a></li>
        <?php endif; ?>
      </ul>
    </div>
    
    <div id="stories" class="tab-content active">
      <?php if (empty($user_stories)): ?>
        <div class="empty-state">
          <i class="fas fa-book-open"></i>
          <h3>No stories yet</h3>
          <?php if ($is_own_profile): ?>
            <p>You haven't shared any stories yet. Share your first story now!</p>
            <a href="create-story.php" class="primary-btn">Share a Story</a>
          <?php else: ?>
            <p><?php echo $user['full_name']; ?> hasn't shared any stories yet.</p>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="stories-grid">
          <?php foreach ($user_stories as $story): ?>
            <div class="story-card">
              <div class="story-image">
                <img src="<?php echo !empty($story['featured_image']) ? $story['featured_image'] : 'https://images.unsplash.com/photo-1580674684081-7617fbf3d745?w=800&auto=format&fit=crop'; ?>" alt="<?php echo $story['title']; ?>">
                <span class="category"><?php echo $story['category']; ?></span>
              </div>
              <div class="story-content">
                <h3><a href="story.php?slug=<?php echo $story['slug']; ?>"><?php echo $story['title']; ?></a></h3>
                <div class="story-meta">
                  <div class="author">
                    <img src="<?php echo !empty($story['author_image']) ? $story['author_image'] : 'images/default-avatar.png'; ?>" alt="<?php echo $story['author_name']; ?>">
                    <span><?php echo $story['author_name']; ?></span>
                  </div>
                  <div class="story-stats">
                    <span><i class="fas fa-heart"></i> <?php echo $story['likes']; ?></span>
                    <span><i class="fas fa-comment"></i> <?php echo $story['comments']; ?></span>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    
    <?php if ($is_own_profile): ?>
    <div id="edit-profile" class="tab-content">
      <div class="form-container">
        <h2>Edit Profile</h2>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
          <div class="form-group">
            <label for="profile_image">Profile Image</label>
            <div class="profile-image-upload">
              <img src="<?php echo !empty($user['profile_image']) ? $user['profile_image'] : 'images/default-avatar.png'; ?>" alt="Profile Preview" class="profile-image-preview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 15px;">
              <input type="file" id="profile_image" name="profile_image" accept="image/*">
              <small>Maximum file size: 5MB. Recommended size: 400x400 pixels.</small>
            </div>
          </div>
          
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" value="<?php echo $user['username']; ?>" disabled>
            <small>Username cannot be changed.</small>
          </div>
          
          <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
          </div>
          
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
          </div>
          
          <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="4"><?php echo isset($user['bio']) ? $user['bio'] : ''; ?></textarea>
            <small>Tell us about yourself in a few sentences.</small>
          </div>
          
          <div class="form-group">
            <button type="submit" name="update_profile" class="primary-btn">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
    
    <div id="change-password" class="tab-content">
      <div class="form-container">
        <h2>Change Password</h2>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
          <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required>
          </div>
          
          <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required>
            <small>At least 8 characters long</small>
          </div>
          
          <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
          </div>
          
          <div class="form-group">
            <button type="submit" name="update_password" class="primary-btn">Update Password</button>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>
