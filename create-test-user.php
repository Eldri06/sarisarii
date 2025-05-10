<?php
/**
 * Create Test User for SariSari Stories
 * 
 * This script creates a test user account with predefined credentials.
 * This is for testing purposes only and should be removed in production.
 */

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Default test user credentials
$test_user = [
    'username' => 'testuser',
    'email' => 'testuser@example.com',
    'password' => 'password123',
    'full_name' => 'Test User'
];

// Try to create the test user
try {
    // Check if the test user already exists
    $existing = check_existing_user($test_user['username'], $test_user['email']);
    
    if ($existing['username'] || $existing['email']) {
        echo "<h2>Test user already exists</h2>";
        echo "<p>A user with this username or email already exists. You can use the following credentials to log in:</p>";
    } else {
        // Register the test user
        $user_id = register_user(
            $test_user['username'], 
            $test_user['email'], 
            $test_user['password'], 
            $test_user['full_name']
        );
        
        if (is_numeric($user_id)) {
            echo "<h2>Test User Created Successfully</h2>";
            echo "<p>Your test user has been created!</p>";
        } else {
            echo "<h2>Error</h2>";
            echo "<p>Failed to create test user: $user_id</p>";
            exit;
        }
    }
    
    // Display login credentials
    echo "<ul>";
    echo "<li><strong>Username:</strong> " . $test_user['username'] . "</li>";
    echo "<li><strong>Password:</strong> " . $test_user['password'] . "</li>";
    echo "</ul>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    
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
    <title>Create Test User - SariSari Stories</title>
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
        <strong>Security Warning:</strong> Delete this file after creating the test user! 
        This script creates a user with predictable credentials and should not be kept on production servers.
    </div>
    
    <p><a href="index.php">Return to Homepage</a></p>
</body>
</html>