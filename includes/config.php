<?php
/**
 * Configuration file for SariSari Stories
 */

// Database configuration
define('DB_SERVER', 'localhost');     // 'localhost' for XAMPP
define('DB_PORT', '3307');            // Default MySQL port in XAMPP
define('DB_USERNAME', 'root');        // Default MySQL username in XAMPP
define('DB_PASSWORD', '');            // Default MySQL password in XAMPP (blank)
define('DB_NAME', 'sarisari_stories'); // Database name
define('DB_TYPE', 'mysql');           // Using MySQL in XAMPP

// Site configuration
define('SITE_NAME', 'SariSari Stories');
define('SITE_DESCRIPTION', 'A vibrant Filipino community platform for sharing personal narratives, hidden gems, local cuisine, events, and traditions.');
define('SITE_URL', 'http://localhost/sarisarii'); 

// Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('PUBLIC_PATH', ROOT_PATH . 'public/');
define('UPLOADS_PATH', PUBLIC_PATH . 'images/stories/');
define('ADMIN_PATH', ROOT_PATH . 'admin/');

// Email Configuration
define('ADMIN_EMAIL', 'admin@sarisari-stories.com');
define('CONTACT_EMAIL', 'contact@sarisari-stories.com');

// Other settings
define('POSTS_PER_PAGE', 9);
define('ENABLE_REGISTRATION', true);

// Session timeout in seconds
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>