<?php

define('DB_SERVER', 'localhost');     // 'localhost' for XAMPP
define('DB_PORT', '3307');            // Default MySQL port in XAMPP
define('DB_USERNAME', 'root');        // Default MySQL username in XAMPP
define('DB_PASSWORD', '');            // Default MySQL password in XAMPP (blank)
define('DB_NAME', 'sarisari_stories'); // Database name
define('DB_TYPE', 'mysql');           // Using MySQL in XAMPP


define('SITE_NAME', 'SariSari Stories');
define('SITE_DESCRIPTION', 'A vibrant Filipino community platform for sharing personal narratives, hidden gems, local cuisine, events, and traditions.');
define('SITE_URL', 'http://localhost/sarisarii'); 


define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('PUBLIC_PATH', ROOT_PATH . 'public/');
define('UPLOADS_PATH', PUBLIC_PATH . 'images/stories/');
define('ADMIN_PATH', ROOT_PATH . 'admin/');


define('ADMIN_EMAIL', 'admin@sarisari-stories.com');
define('CONTACT_EMAIL', 'contact@sarisari-stories.com');


define('POSTS_PER_PAGE', 9);
define('ENABLE_REGISTRATION', true);


define('SESSION_TIMEOUT', 1800); // 30 minutes


error_reporting(E_ALL);
ini_set('display_errors', 1);
?>