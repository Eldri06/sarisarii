<?php

require_once 'db.php';

/**
 * Register a new user
 * 
 * @param string $username Username
 * @param string $email Email address
 * @param string $password Password
 * @param string $full_name Full name
 * @return int|string User ID on success, error msg on failure
 */
function register_user($username, $email, $password, $full_name) {
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        return "All fields are required";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }
    
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters long";
    }
    
    // Check for existing username or email
    $existing = check_existing_user($username, $email);
    
    if ($existing['username']) {
        return "Username already taken";
    }
    
    if ($existing['email']) {
        return "Email already registered";
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare user data
    $user_data = [
        'username' => $username,
        'email' => $email,
        'password' => $hashed_password,
        'full_name' => $full_name
    ];
    
    // Insert into database
    $user_id = insert('users', $user_data);
    
    if (!$user_id) {
        return "Registration failed. Please try again.";
    }
    
    return $user_id;
}

/**
 * Check if username or email already exists
 * 
 * @param string $username Username to check
 * @param string $email Email to check
 * @param int $exclude_user_id User ID to exclude (for updates)
 * @return array Results with username and email keys
 */
function check_existing_user($username = '', $email = '', $exclude_user_id = null) {
    $result = [
        'username' => false,
        'email' => false
    ];
    
    // Check username if provided
    if (!empty($username)) {
        $where = $exclude_user_id ? "username = ? AND id != ?" : "username = ?";
        $params = $exclude_user_id ? [$username, $exclude_user_id] : [$username];
        
        $user = fetch_one("SELECT id FROM users WHERE $where", $params);
        $result['username'] = !empty($user);
    }
    
    // Check email if provided
    if (!empty($email)) {
        $where = $exclude_user_id ? "email = ? AND id != ?" : "email = ?";
        $params = $exclude_user_id ? [$email, $exclude_user_id] : [$email];
        
        $user = fetch_one("SELECT id FROM users WHERE $where", $params);
        $result['email'] = !empty($user);
    }
    
    return $result;
}

/**
 * Authenticate a user
 * 
 * @param string $username_or_email Username or email
 * @param string $password Password
 * @return array|string User data on success, error message on failure
 */
function login_user($username_or_email, $password) {
    // Validate input
    if (empty($username_or_email) || empty($password)) {
        return "All fields are required";
    }
    
    // Determine if input is username or email
    $field = filter_var($username_or_email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    
    // Get user from database
    $sql = "SELECT id, username, email, password, full_name, profile_image, is_admin FROM users WHERE {$field} = ?";
    $user = fetch_one($sql, [$username_or_email]);
    
    if (!$user) {
        return "Invalid credentials";
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        return "Invalid credentials";
    }
    
    // Remove password from user data before returning
    unset($user['password']);
    
    return $user;
}

/**
 * Start a user session
 * 
 * @param array $user User data
 * @return void
 */
function start_session($user) {
    // Ensure session is started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Store user data in session
    $_SESSION['user'] = $user;
    $_SESSION['logged_in'] = true;
    $_SESSION['last_activity'] = time();
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function is_logged_in() {
    // Ensure session is started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in and session hasn't expired
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
        // Check for session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
            // Session expired, log out
            logout();
            return false;
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    return false;
}

/**
 * Get current user data
 * 
 * @param bool $refresh Whether to refresh user data from database
 * @return array|false User data or false if not logged in
 */
function get_current_user_data($refresh = false) {
    if (!is_logged_in()) {
        return false;
    }
    
    if ($refresh && isset($_SESSION['user']['id'])) {
        // Refresh user data from database
        $user = get_user($_SESSION['user']['id']);
        
        if ($user) {
            $_SESSION['user'] = $user;
        }
    }
    
    return $_SESSION['user'];
}

/**
 * Get user data by ID
 * 
 * @param int $user_id User ID
 * @return array|false User data or false if not found
 */
function get_user($user_id) {
    $sql = "SELECT id, username, email, full_name, bio, profile_image, created_at FROM users WHERE id = ?";
    return fetch_one($sql, [$user_id]);
}

/**
 * Update user profile
 * 
 * @param int $user_id User ID
 * @param array $data Data to update
 * @return bool|string True on success, error message on failure
 */
function update_profile($user_id, $data) {
    // Validate user ID
    if (!$user_id) {
        return "Invalid user ID";
    }
    
    $update_data = [];
    
    // Username update
    if (isset($data['username']) && !empty($data['username'])) {
        // Check if username already exists (excluding current user)
        $existing = check_existing_user($data['username'], '', $user_id);
        
        if ($existing['username']) {
            return "Username already taken";
        }
        
        $update_data['username'] = $data['username'];
    }
    
    // Email update
    if (isset($data['email']) && !empty($data['email'])) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format";
        }
        
        // Check if email already exists (excluding current user)
        $existing = check_existing_user('', $data['email'], $user_id);
        
        if ($existing['email']) {
            return "Email already registered";
        }
        
        $update_data['email'] = $data['email'];
    }
    
    // Full name update
    if (isset($data['full_name']) && !empty($data['full_name'])) {
        $update_data['full_name'] = $data['full_name'];
    }
    
    // Bio update
    if (isset($data['bio'])) {
        $update_data['bio'] = $data['bio'];
    }
    
    // Password update
    if (isset($data['password']) && !empty($data['password'])) {
        if (strlen($data['password']) < 8) {
            return "Password must be at least 8 characters long";
        }
        
        $update_data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    // Profile image update
    if (isset($data['profile_image']) && !empty($data['profile_image'])) {
        $update_data['profile_image'] = $data['profile_image'];
    }
    
    // Check if there's anything to update
    if (empty($update_data)) {
        return "No changes made";
    }
    
    // Update user in database
    $result = update('users', $update_data, "id = ?", [$user_id]);
    
    if (!$result) {
        return "Update failed. Please try again.";
    }
    
    // If the current user is being updated, refresh session data
    if (is_logged_in() && $_SESSION['user']['id'] == $user_id) {
        get_current_user_data(true);
    }
    
    return true;
}

/**
 * Log out the current user
 * 
 * @return void
 */
function logout() {
    // Ensure session is started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Clear all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
}

/**
 * Require user to be logged in to access a page
 * 
 * @param string $redirect_url URL to redirect to if not logged in
 * @return void
 */
function require_login($redirect_url = 'login.php') {
    if (!is_logged_in()) {
        header("Location: login.php?redirect=" . urlencode($redirect_url));
        exit;
    }
}
