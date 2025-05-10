<?php
/**
 * Configuration file for SariSari Stories
 *
 */

// Database configuration mysql
define('DB_SERVER', 'localhost');     
define('DB_PORT', '3307');            
define('DB_USERNAME', 'root');        
define('DB_PASSWORD', '');           
define('DB_NAME', 'sarisari');        
define('DB_TYPE', 'mysql');           

// Site configuration
define('SITE_NAME', 'SariSari Stories');
define('SITE_DESCRIPTION', 'A vibrant Filipino community platform for sharing personal narratives, hidden gems, local cuisine, events, and traditions.');
define('SITE_URL', 'http://localhost/sarisari'); 

// Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('PUBLIC_PATH', ROOT_PATH . 'public/');
define('UPLOADS_PATH', PUBLIC_PATH . 'uploads/');

// Email Configuration
define('ADMIN_EMAIL', 'admin@sarisari-stories.com');
define('CONTACT_EMAIL', 'contact@sarisari-stories.com');

// Other settings
define('POSTS_PER_PAGE', 9);
define('ENABLE_REGISTRATION', true);

// Session timeout in seconds
define('SESSION_TIMEOUT', 1800); // 30 minutes 1800 second i think

// Error reporting 
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>