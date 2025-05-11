<?php
/**
 * Registration page for SariSari Stories
 */

// Include header
$page_title = "Sign Up";
$page_description = "Join SariSari Stories, a vibrant Filipino community platform for sharing your stories.";

include_once 'includes/header.php';

// Check if user is already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

// Check if registration is enabled
if (!ENABLE_REGISTRATION) {
    header('Location: index.php');
    exit;
}

// Initialize variables
$username = '';
$email = '';
$full_name = '';
$error = '';
$success = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    
    // Validate form data
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Register user
        $result = register_user($username, $email, $password, $full_name);
        
        if (is_numeric($result)) {
            // Registration successful
            $success = 'Registration successful! You can now log in.';
            
            // Clear form data
            $username = '';
            $email = '';
            $full_name = '';
        } else {
            // Registration failed
            $error = $result;
        }
    }
}
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <h1>Join SariSari Stories</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <p>Please <a href="login.php">log in</a> to continue.</p>
                </div>
            <?php else: ?>
                <form action="register.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <small>Must be at least 8 characters long.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="primary-btn">Sign Up</button>
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Log In</a></p>
            </div>
        </div>
    </div>
</section>

