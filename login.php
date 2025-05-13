<?php

$page_title = "Login";
$page_description = "Log in to your SariSari Stories account to share your stories, like and comment on others' tales.";

// Background style
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


include_once 'includes/header.php';


if (isset($additional_head)) echo $additional_head;


if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

// Initialize variables
$username_email = '';
$error = '';
$success = '';

// Handle redirect parameter
$redirect = $_GET['redirect'] ?? 'index.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_email = sanitize($_POST['username_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username_email) || empty($password)) {
        $error = 'Please enter both username/email and password.';
    } else {
        $result = login_user($username_email, $password);
        if (is_array($result)) {
            start_session($result);
            header('Location: ' . $redirect);
            exit;
        } else {
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
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
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


