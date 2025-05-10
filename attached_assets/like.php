<?php
// Include configuration and functions
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set header to return JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode([
        'success' => false,
        'message' => 'login_required'
    ]);
    exit;
}

// Get current user
$current_user = get_current_user_data();
$user_id = $current_user['id'];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get story ID from request
$story_id = isset($_POST['story_id']) ? (int)$_POST['story_id'] : 0;

if (empty($story_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Story ID is required'
    ]);
    exit;
}

// Check if story exists
$sql = "SELECT id FROM stories WHERE id = ? AND status = 'published'";
$story = fetch_one($sql, [$story_id]);

if (!$story) {
    echo json_encode([
        'success' => false,
        'message' => 'Story not found'
    ]);
    exit;
}

// Toggle like
$result = toggle_like($story_id, $user_id);

if ($result === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to toggle like'
    ]);
    exit;
}

// Get updated like count
$sql = "SELECT COUNT(*) as count FROM likes WHERE story_id = ?";
$like_count = fetch_one($sql, [$story_id]);
$likes = $like_count ? $like_count['count'] : 0;

// Check if user has liked the story after the toggle
$liked = user_has_liked($story_id, $user_id);

// Return success response
echo json_encode([
    'success' => true,
    'likes' => $likes,
    'liked' => $liked
]);
