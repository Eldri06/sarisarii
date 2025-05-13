<?php

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';


header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode([
        'success' => false,
        'message' => 'login_required'
    ]);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}


$story_id = isset($_POST['story_id']) ? intval($_POST['story_id']) : 0;

// Validate story ID
if (empty($story_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Story ID is required'
    ]);
    exit;
}

// Get current user data
$user = get_current_user_data();

// Verify story exists
$story = fetch_one("SELECT id FROM stories WHERE id = ? AND status = 'published'", [$story_id]);

if (!$story) {
    echo json_encode([
        'success' => false,
        'message' => 'Story not found'
    ]);
    exit;
}

// Check if user has already liked the story
$has_liked = user_has_liked($story_id, $user['id']);

// Toggle like
$toggle_result = toggle_like($story_id, $user['id']);

if ($toggle_result === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to toggle like'
    ]);
    exit;
}

// Get updated likes count
$likes_count = fetch_one("SELECT COUNT(*) as count FROM likes WHERE story_id = ?", [$story_id]);
$likes = $likes_count ? $likes_count['count'] : 0;

// Return success response
echo json_encode([
    'success' => true,
    'liked' => !$has_liked, // Toggled state
    'likes' => $likes,
    'message' => !$has_liked ? 'Story liked' : 'Story unliked'
]);
