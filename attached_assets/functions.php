<?php
/**
 * Helper functions for SariSari Stories
 */

// Include database connection
require_once 'db.php';

/**
 * Sanitize user input
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Get featured stories
 * 
 * @param int $limit Number of stories to retrieve
 * @return array Array of story data
 */
function get_featured_stories($limit = 3) {
    $sql = "SELECT s.*, c.name as category, c.slug as category_slug, 
                   u.username, u.full_name as author_name, u.profile_image as author_image,
                   (SELECT COUNT(*) FROM likes WHERE story_id = s.id) as likes,
                   (SELECT COUNT(*) FROM comments WHERE story_id = s.id) as comments
            FROM stories s
            JOIN users u ON s.user_id = u.id
            JOIN categories c ON s.category_id = c.id
            WHERE s.featured = 1 AND s.status = 'published'
            ORDER BY s.created_at DESC
            LIMIT ?";
    
    $stories = fetch_all($sql, [$limit]);
    
    // If no featured stories found, get recent stories
    if (empty($stories)) {
        $sql = "SELECT s.*, c.name as category, c.slug as category_slug, 
                       u.username, u.full_name as author_name, u.profile_image as author_image,
                       (SELECT COUNT(*) FROM likes WHERE story_id = s.id) as likes,
                       (SELECT COUNT(*) FROM comments WHERE story_id = s.id) as comments
                FROM stories s
                JOIN users u ON s.user_id = u.id
                JOIN categories c ON s.category_id = c.id
                WHERE s.status = 'published'
                ORDER BY s.created_at DESC
                LIMIT ?";
        
        $stories = fetch_all($sql, [$limit]);
    }
    
    return $stories;
}

/**
 * Get recent stories
 * 
 * @param int $limit Number of stories to retrieve
 * @param int $offset Offset for pagination
 * @return array Array of stories
 */
function get_recent_stories($limit = 10, $offset = 0) {
    $sql = "SELECT s.*, c.name as category, c.slug as category_slug, 
                   u.username, u.full_name as author_name, u.profile_image as author_image,
                   (SELECT COUNT(*) FROM likes WHERE story_id = s.id) as likes,
                   (SELECT COUNT(*) FROM comments WHERE story_id = s.id) as comments
            FROM stories s
            JOIN users u ON s.user_id = u.id
            JOIN categories c ON s.category_id = c.id
            WHERE s.status = 'published'
            ORDER BY s.created_at DESC
            LIMIT ? OFFSET ?";
    
    return fetch_all($sql, [$limit, $offset]);
}

/**
 * Get stories by category
 * 
 * @param int $category_id Category ID
 * @param int $limit Number of stories to retrieve
 * @return array Array of stories
 */
function get_stories_by_category($category_id, $limit = 10) {
    $sql = "SELECT s.*, c.name as category, c.slug as category_slug, 
                   u.username, u.full_name as author_name, u.profile_image as author_image,
                   (SELECT COUNT(*) FROM likes WHERE story_id = s.id) as likes,
                   (SELECT COUNT(*) FROM comments WHERE story_id = s.id) as comments
            FROM stories s
            JOIN users u ON s.user_id = u.id
            JOIN categories c ON s.category_id = c.id
            WHERE s.category_id = ? AND s.status = 'published'
            ORDER BY s.created_at DESC
            LIMIT ?";
    
    return fetch_all($sql, [$category_id, $limit]);
}

/**
 * Get stories by user
 * 
 * @param int $user_id User ID
 * @param int $limit Number of stories to retrieve
 * @return array Array of stories
 */
function get_stories_by_user($user_id, $limit = 10) {
    $sql = "SELECT s.*, c.name as category, c.slug as category_slug, 
                   u.username, u.full_name as author_name, u.profile_image as author_image,
                   (SELECT COUNT(*) FROM likes WHERE story_id = s.id) as likes,
                   (SELECT COUNT(*) FROM comments WHERE story_id = s.id) as comments
            FROM stories s
            JOIN users u ON s.user_id = u.id
            JOIN categories c ON s.category_id = c.id
            WHERE s.user_id = ? AND s.status = 'published'
            ORDER BY s.created_at DESC
            LIMIT ?";
    
    return fetch_all($sql, [$user_id, $limit]);
}

/**
 * Get a single story by ID or slug
 * 
 * @param mixed $id_or_slug Story ID or slug
 * @param bool $increment_views Whether to increment view count
 * @return array|false Story data or false if not found
 */
function get_story($id_or_slug, $increment_views = true) {
    $param_type = is_numeric($id_or_slug) ? 'id' : 'slug';
    
    $sql = "SELECT s.*, c.name as category, c.slug as category_slug, 
                   u.id as user_id, u.username, u.full_name as author_name, u.profile_image as author_image, u.bio as author_bio,
                   (SELECT COUNT(*) FROM likes WHERE story_id = s.id) as likes,
                   (SELECT COUNT(*) FROM comments WHERE story_id = s.id) as comments
            FROM stories s
            JOIN users u ON s.user_id = u.id
            JOIN categories c ON s.category_id = c.id
            WHERE s.{$param_type} = ? AND s.status = 'published'";
    
    $story = fetch_one($sql, [$id_or_slug]);
    
    if ($story && $increment_views) {
        // Increment view count
        update('stories', ['views' => $story['views'] + 1], "id = ?", [$story['id']]);
        $story['views']++;
    }
    
    return $story;
}

/**
 * Get categories
 * 
 * @return array Array of categories
 */
function get_categories() {
    $sql = "SELECT id, name, description, icon, slug FROM categories ORDER BY name ASC";
    return fetch_all($sql);
}

/**
 * Get category by ID or slug
 * 
 * @param mixed $id_or_slug Category ID or slug
 * @return array|false Category data or false if not found
 */
function get_category($id_or_slug) {
    $param_type = is_numeric($id_or_slug) ? 'id' : 'slug';
    
    $sql = "SELECT id, name, description, icon, slug FROM categories WHERE {$param_type} = ?";
    return fetch_one($sql, [$id_or_slug]);
}

/**
 * Get comments for a story
 * 
 * @param int $story_id Story ID
 * @return array Array of comments
 */
function get_comments($story_id) {
    $sql = "SELECT c.*, u.username, u.full_name, u.profile_image 
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.story_id = ?
            ORDER BY c.created_at DESC";
    
    return fetch_all($sql, [$story_id]);
}

/**
 * Add a comment to a story
 * 
 * @param int $story_id Story ID
 * @param int $user_id User ID
 * @param string $content Comment content
 * @return int|false Comment ID or false on failure
 */
function add_comment($story_id, $user_id, $content) {
    $data = [
        'story_id' => $story_id,
        'user_id' => $user_id,
        'content' => $content
    ];
    
    return insert('comments', $data);
}

/**
 * Toggle like on a story
 * 
 * @param int $story_id Story ID
 * @param int $user_id User ID
 * @return bool Success status
 */
function toggle_like($story_id, $user_id) {
    // Check if like already exists
    $sql = "SELECT id FROM likes WHERE story_id = ? AND user_id = ?";
    $like = fetch_one($sql, [$story_id, $user_id]);
    
    if ($like) {
        // Unlike
        return delete('likes', 'id = ?', [$like['id']]);
    } else {
        // Like
        $data = [
            'story_id' => $story_id,
            'user_id' => $user_id
        ];
        return insert('likes', $data) ? true : false;
    }
}

/**
 * Check if user has liked a story
 * 
 * @param int $story_id Story ID
 * @param int $user_id User ID
 * @return bool True if liked, false otherwise
 */
function user_has_liked($story_id, $user_id) {
    $sql = "SELECT COUNT(*) as count FROM likes WHERE story_id = ? AND user_id = ?";
    $result = fetch_one($sql, [$story_id, $user_id]);
    return $result && $result['count'] > 0;
}

/**
 * Format date in a user-friendly way
 * 
 * @param string $date Date string
 * @param string $format Format string for date
 * @return string Formatted date
 */
function format_date($date, $format = 'F j, Y') {
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Get time elapsed since given date
 * 
 * @param string $datetime Date/time string
 * @return string Time elapsed in human-readable format
 */
function time_elapsed_string($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!empty($string)) {
        return $string[key($string)] . ' ago';
    }
    
    return 'just now';
}

/**
 * Create a slug from a title
 * 
 * @param string $title Title to convert
 * @param string $table Table to check for slug uniqueness
 * @param int $id ID to exclude from uniqueness check (for updates)
 * @return string URL-friendly slug
 */
function create_slug($title, $table = null, $id = null) {
    // Replace non-alphanumeric characters with dashes
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $title);
    
    // Convert to lowercase
    $slug = strtolower($slug);
    
    // Remove leading/trailing dashes
    $slug = trim($slug, '-');
    
    // If table is provided, ensure slug is unique
    if ($table) {
        $original_slug = $slug;
        $count = 1;
        
        while (true) {
            $where = $id ? "slug = ? AND id != ?" : "slug = ?";
            $params = $id ? [$slug, $id] : [$slug];
            
            $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
            $result = fetch_one($sql, $params);
            
            if (!$result || $result['count'] == 0) {
                break;
            }
            
            $slug = $original_slug . '-' . $count;
            $count++;
        }
    }
    
    return $slug;
}

/**
 * Upload and process an image
 * 
 * @param array $file $_FILES array element
 * @param string $directory Directory to save in
 * @param int $max_size Maximum file size in bytes
 * @return string|false New filename or false on failure
 */
function upload_image($file, $directory = 'uploads', $max_size = 5242880) {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Verify file size
    if ($file['size'] > $max_size) {
        return false;
    }
    
    // Verify MIME type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    
    if (!in_array($mime_type, $allowed_types)) {
        return false;
    }
    
    // Create unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $extension;
    
    // Ensure upload directory exists
    $upload_dir = PUBLIC_PATH . $directory;
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Move uploaded file
    $destination = $upload_dir . '/' . $new_filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return false;
    }
    
    return '/uploads/stories/' . $new_filename;

}

/**
 * Subscribe a user to the newsletter
 * 
 * @param string $email User's email
 * @return bool Success status
 */
function subscribe_newsletter($email) {
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    try {
        $data = [
            'email' => $email,
            'status' => 'active'
        ];
        
        // Check if already subscribed
        $sql = "SELECT id, status FROM subscribers WHERE email = ?";
        $subscriber = fetch_one($sql, [$email]);
        
        if ($subscriber) {
            if ($subscriber['status'] == 'unsubscribed') {
                // Reactivate subscription
                return update('subscribers', ['status' => 'active'], "id = ?", [$subscriber['id']]);
            }
            return true; // Already subscribed and active
        }
        
        return insert('subscribers', $data) ? true : false;
    } catch (Exception $e) {
        error_log("Newsletter subscription failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Search stories
 * 
 * @param string $query Search query
 * @param int $limit Number of results to return
 * @return array Search results
 */
function search_stories($query, $limit = 10) {
    $search_term = '%' . $query . '%';
    
    $sql = "SELECT s.*, c.name as category, c.slug as category_slug, 
                   u.username, u.full_name as author_name, u.profile_image as author_image,
                   (SELECT COUNT(*) FROM likes WHERE story_id = s.id) as likes,
                   (SELECT COUNT(*) FROM comments WHERE story_id = s.id) as comments
            FROM stories s
            JOIN users u ON s.user_id = u.id
            JOIN categories c ON s.category_id = c.id
            WHERE (s.title LIKE ? OR s.content LIKE ?) AND s.status = 'published'
            ORDER BY s.created_at DESC
            LIMIT ?";
    
    return fetch_all($sql, [$search_term, $search_term, $limit]);
}

/**
 * Get user data by ID or username
 * 
 * @param mixed $id_or_username User ID or username
 * @return array|false User data or false if not found
 */
function get_user($id_or_username) {
    $param_type = is_numeric($id_or_username) ? 'id' : 'username';
    
    $sql = "SELECT id, username, email, full_name, bio, profile_image, created_at 
            FROM users 
            WHERE {$param_type} = ?";
    
    return fetch_one($sql, [$id_or_username]);
}

/**
 * Count stories by a user
 * 
 * @param int $user_id User ID
 * @return int Story count
 */
function count_user_stories($user_id) {
    $sql = "SELECT COUNT(*) as count FROM stories WHERE user_id = ? AND status = 'published'";
    $result = fetch_one($sql, [$user_id]);
    return $result ? $result['count'] : 0;
}

/**
 * Check if a username or email already exists
 * 
 * @param string $username Username to check
 * @param string $email Email to check
 * @param int $exclude_id User ID to exclude (for updates)
 * @return array|false Associative array with 'username' and 'email' booleans, or false on error
 */
function check_existing_user($username, $email, $exclude_id = null) {
    try {
        $result = ['username' => false, 'email' => false];
        
        // Check username
        $where = $exclude_id ? "username = ? AND id != ?" : "username = ?";
        $params = $exclude_id ? [$username, $exclude_id] : [$username];
        
        $sql = "SELECT COUNT(*) as count FROM users WHERE {$where}";
        $username_check = fetch_one($sql, $params);
        
        if ($username_check && $username_check['count'] > 0) {
            $result['username'] = true;
        }
        
        // Check email
        $where = $exclude_id ? "email = ? AND id != ?" : "email = ?";
        $params = $exclude_id ? [$email, $exclude_id] : [$email];
        
        $sql = "SELECT COUNT(*) as count FROM users WHERE {$where}";
        $email_check = fetch_one($sql, $params);
        
        if ($email_check && $email_check['count'] > 0) {
            $result['email'] = true;
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Error checking existing user: " . $e->getMessage());
        return false;
    }
}

/**
 * Get navigation menu items
 * 
 * @return array Menu items
 */
function get_navigation_items() {
    return [
        'Home' => '/',
        'Discover' => 'discover.php',
        'Featured' => 'discover.php?featured=1',
        'About' => 'about.php'
    ];
}

/**
 * Get footer links
 * 
 * @return array Footer link groups
 */
function get_footer_links() {
    return [
        'Company' => [
            'About' => 'about.php',
            'Community' => 'discover.php'
        ],
        'Helpful Links' => [
            'Contact' => 'contact.php',
            'FAQs' => 'faqs.php',
            'Community Guidelines' => 'guidelines.php'
        ],
        'Legal' => [
            'Privacy Policy' => 'privacy.php',
            'Terms & Conditions' => 'terms.php'
        ]
    ];
}
