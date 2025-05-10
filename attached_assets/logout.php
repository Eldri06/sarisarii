<?php
// Include configuration and functions
require_once '../includes/config.php';
require_once '../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Log out user
logout();

// Redirect to homepage
header('Location: index.php');
exit;
