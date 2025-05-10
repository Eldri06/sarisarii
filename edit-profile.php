<?php
/**
 * Edit profile page for SariSari Stories
 */

// Include header
$page_title = "Edit Profile";
$page_description = "Update your SariSari Stories profile information.";

include_once 'includes/header.php';

// Require login to access this page
require_login('edit-profile.php');

// Initialize variables
$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $bio = sanitize($_POST['bio'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Prepare update data
    $update_data = [
        'full_name' => $full_name,
        'email' => $email,
        'username' => $username,
        'bio' => $bio
    ];
    
    // Handle password update if provided
    if (!empty($new_password)) {
        // Verify current password
        if (empty($current_password)) {
            $error = 'Current password is required to set a new password.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } else {
            // Verify current password
            $user = fetch_one("SELECT password FROM users WHERE id = ?", [$current_user['id']]);
            
            if (!$user || !password_verify($current_password, $user['password'])) {
                $error = 'Current password is incorrect.';
            } else {
                $update_data['password'] = $new_password;
            }
        }
    }
    
    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $profile_image = upload_image($_FILES['profile_image'], 'uploads/profiles');
        
        if ($profile_image) {
            $update_data['profile_image'] = $profile_image;
        } else {
            $error = 'Failed to upload profile image. Please ensure it is a valid image file (JPG, PNG, GIF) under 5MB.';
        }
    }
    
    // Update profile if no errors
    if (empty($error)) {
        $result = update_profile($current_user['id'], $update_data);
        
        if ($result === true) {
            $success = 'Profile updated successfully.';
            
            // Refresh user data
            $current_user = get_current_user_data(true);
        } else {
            $error = $result;
        }
    }
}
?>

<section class="edit-profile-section">
    <div class="container">
        <div class="edit-profile-container">
            <h1>Edit Profile</h1>
            
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
            
            <form action="edit-profile.php" method="POST" enctype="multipart/form-data" class="edit-profile-form">
                <div class="form-group">
                    <label for="profile_image">Profile Image</label>
                    <div class="profile-image-upload">
                        <div class="current-image">
                            <img src="<?php echo htmlspecialchars($current_user['profile_image'] ?? 'images/default-avatar.jpg'); ?>" alt="Profile Image">
                        </div>
                        <div class="upload-controls">
                            <input type="file" id="profile_image" name="profile_image" accept="image/*">
                            <small>Max file size: 5MB. Supported formats: JPG, PNG, GIF.</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($current_user['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($current_user['username']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($current_user['bio'] ?? ''); ?></textarea>
                </div>
                
                <div class="password-section">
                    <h3>Change Password</h3>
                    <p>Leave blank if you don't want to change your password.</p>
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                        <small>Must be at least 8 characters long.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="primary-btn">Save Changes</button>
                    <a href="profile.php" class="secondary-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
