<?php
/**
 * Configuration file for SariSari Stories
 */

// Database configuration
define('DB_SERVER', 'ep-steep-recipe-a5be1i68.us-east-2.aws.neon.tech');     
define('DB_PORT', '5432');            
define('DB_USERNAME', 'neondb_owner');        
define('DB_PASSWORD', 'npg_O4srxhnEejm8');           
define('DB_NAME', 'neondb');        
define('DB_TYPE', 'pgsql');     
define('DB_SSL_MODE', 'require');      

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
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
