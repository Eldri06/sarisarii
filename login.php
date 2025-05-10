<?php
/**
 * Login page for SariSari Stories
 */

// Include header
$page_title = "Login";
$page_description = "Log in to your SariSari Stories account to share your stories, like and comment on others' tales.";

include_once 'includes/header.php';

// Check if user is already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

// Initialize variables
$username_email = '';
$error = '';
$success = '';

// Check for redirect parameter
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

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
            // Login successful
            start_session($result);
            
            // Redirect to the requested page
            header('Location: ' . $redirect);
            exit;
        } else {
            // Login failed
            $error = $result;
        }
    }
}
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <h1>Log In to SariSari Stories</h1>
            
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
            
            <form action="login.php?redirect=<?php echo urlencode($redirect); ?>" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username_email">Username or Email</label>
                    <input type="text" id="username_email" name="username_email" value="<?php echo htmlspecialchars($username_email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="primary-btn">Log In</button>
                </div>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Sign Up</a></p>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
