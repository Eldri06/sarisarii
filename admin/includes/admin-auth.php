<?php
/**
 * Admin authentication functions for SariSari Stories
 */

// Include main auth file and other dependencies
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

/**
 * Check if current user is an admin
 * 
 * @return bool True if user is an admin, false otherwise
 */
function is_admin() {
    // Ensure session is started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
        // Get current user
        $user_id = $_SESSION['user']['id'];
        
        // Check if user has admin role
        $sql = "SELECT is_admin FROM users WHERE id = ?";
        $result = fetch_one($sql, [$user_id]);
        
        return $result && isset($result['is_admin']) && $result['is_admin'] == 1;
    }
    
    return false;
}

/**
 * Require admin privileges to access a page
 * 
 * @param string $redirect_url URL to redirect to if not an admin
 * @return void
 */
function require_admin($redirect_url = '../login.php') {
    if (!is_admin()) {
        // Set error message
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['error_message'] = "You don't have permission to access this page.";
        
        // Redirect to login page
        header("Location: $redirect_url");
        exit;
    }
}

/**
 * Get admin dashboard statistics
 * 
 * @return array Statistics for the admin dashboard
 */
function get_admin_stats() {
    $stats = [
        'total_users' => 0,
        'total_stories' => 0,
        'total_comments' => 0,
        'total_likes' => 0,
        'total_categories' => 0,
        'total_subscribers' => 0,
        'recent_users' => [],
        'recent_stories' => []
    ];
    
    // Get total users
    $result = fetch_one("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $stats['total_users'] = $result['count'];
    }
    
    // Get total stories
    $result = fetch_one("SELECT COUNT(*) as count FROM stories");
    if ($result) {
        $stats['total_stories'] = $result['count'];
    }
    
    // Get total comments
    $result = fetch_one("SELECT COUNT(*) as count FROM comments");
    if ($result) {
        $stats['total_comments'] = $result['count'];
    }
    
    // Get total likes
    $result = fetch_one("SELECT COUNT(*) as count FROM likes");
    if ($result) {
        $stats['total_likes'] = $result['count'];
    }
    
    // Get total categories
    $result = fetch_one("SELECT COUNT(*) as count FROM categories");
    if ($result) {
        $stats['total_categories'] = $result['count'];
    }
    
    // Get total subscribers
    $result = fetch_one("SELECT COUNT(*) as count FROM subscribers");
    if ($result) {
        $stats['total_subscribers'] = $result['count'];
    }
    
    // Get recent users
    $sql = "SELECT id, username, email, full_name, created_at FROM users ORDER BY created_at DESC LIMIT 5";
    $result = fetch_all($sql);
    if ($result) {
        $stats['recent_users'] = $result;
    }
    
    // Get recent stories
    $sql = "SELECT s.id, s.title, s.created_at, u.username 
            FROM stories s 
            JOIN users u ON s.user_id = u.id 
            ORDER BY s.created_at DESC LIMIT 5";
    $result = fetch_all($sql);
    if ($result) {
        $stats['recent_stories'] = $result;
    }
    
    return $stats;
}