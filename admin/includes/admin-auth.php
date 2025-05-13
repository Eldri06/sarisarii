<?php


require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/db.php';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';



 // @return bool True if user is an admin, false otherwise
 
function is_admin() {
    
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
 * 
 * 
 * @param string 
 * @return void
 */
function require_admin($redirect_url = '../login.php') {
    if (!is_admin()) {
        // Set error message
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['error_message'] = "You don't have permission to access this page.";
        
        
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