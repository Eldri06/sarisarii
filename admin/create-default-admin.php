<?php
/**
 * Create Default Admin User for SariSari Stories
 * 
 * This script creates a default admin user with predefined credentials.
 * This is for testing purposes only and should be removed in production.
 */

// Include necessary files
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Default admin credentials
$default_admin = [
    'username' => 'admin',
    'email' => 'admin@sarisari-stories.com',
    'password' => 'admin123456',
    'full_name' => 'Administrator'
];

// Try to create the admin user
try {
    // Check if an admin already exists
    $admin_check = fetch_one("SELECT COUNT(*) as count FROM users WHERE is_admin = TRUE");
    
    if ($admin_check && $admin_check['count'] > 0) {
        echo "<h2>An admin user already exists</h2>";
        echo "<p>You can use the existing admin account to log in.</p>";
        echo "<p>If you've forgotten the password, you can manually create a new admin through the database.</p>";
    } else {
        // Register the admin user
        $user_id = register_user(
            $default_admin['username'], 
            $default_admin['email'], 
            $default_admin['password'], 
            $default_admin['full_name']
        );
        
        if (is_numeric($user_id)) {
            // Update the user to be an admin
            $result = update('users', ['is_admin' => TRUE], 'id = ?', [$user_id]);
            
            if ($result) {
                echo "<h2>Default Admin Created Successfully</h2>";
                echo "<p>Use the following credentials to log in:</p>";
                echo "<ul>";
                echo "<li><strong>Username:</strong> " . $default_admin['username'] . "</li>";
                echo "<li><strong>Password:</strong> " . $default_admin['password'] . "</li>";
                echo "</ul>";
                echo "<p><a href='login.php'>Go to Admin Login</a></p>";
            } else {
                echo "<h2>Error</h2>";
                echo "<p>Failed to set admin privileges.</p>";
            }
        } else {
            // Registration failed
            echo "<h2>Error</h2>";
            echo "<p>Failed to create admin user: $user_id</p>";
        }
    }
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>An error occurred: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Default Admin - SariSari Stories</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            color: #2c3e50;
        }
        ul {
            background: #f8f9fa;
            padding: 15px 25px;
            border-radius: 5px;
        }
        .warning {
            background: #fff3cd;
            padding: 10px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            background: #3498db;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="warning">
        <strong>Security Warning:</strong> Delete this file after creating the admin user! 
        This script creates a default admin with predictable credentials and should not be kept on production servers.
    </div>
    
    <p><a href="../index.php">Return to Homepage</a></p>
</body>
</html>