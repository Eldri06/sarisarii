<?php
/**
 * Logout script for SariSari Stories
 */

// Include auth functions
require_once 'includes/auth.php';

// Logout the user
logout();

// Redirect to home page
header('Location: index.php');
exit;
