<?php

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';


header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}


$email = isset($_POST['email']) ? sanitize($_POST['email']) : '';

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a valid email address'
    ]);
    exit;
}

// Subscribe to newsletter
$result = subscribe_newsletter($email);

if ($result === true) {
    echo json_encode([
        'success' => true,
        'message' => 'Subscribed successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $result
    ]);
}
