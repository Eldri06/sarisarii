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

// Get story ID and comment content from request
$story_id = isset($_POST['story_id']) ? (int)$_POST['story_id'] : 0;
$content = isset($_POST['content']) ? sanitize($_POST['content']) : '';

if (empty($story_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Story ID is required'
    ]);
    exit;
}

if (empty($content)) {
    echo json_encode([
        'success' => false,
        'message' => 'Comment content is required'
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

// Add comment
$comment_id = add_comment($story_id, $user_id, $content);

if (!$comment_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add comment'
    ]);
    exit;
}

// Get the new comment data
$sql = "SELECT c.*, u.username, u.full_name, u.profile_image 
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.id = ?";
$comment = fetch_one($sql, [$comment_id]);

if (!$comment) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve comment'
    ]);
    exit;
}

// Create HTML for the new comment
$profile_image = !empty($comment['profile_image']) ? $comment['profile_image'] : 'images/default-avatar.jpg';
$html = <<<HTML
<img src="{$profile_image}" alt="{$comment['full_name']}" class="comment-avatar">
<div class="comment-content">
  <div class="comment-header">
    <span class="comment-name">
      <a href="profile.php?username={$comment['username']}">{$comment['full_name']}</a>
    </span>
    <span class="comment-date">just now</span>
  </div>
  <div class="comment-body">
    {$comment['content']}
  </div>
</div>
HTML;

// Return success response
echo json_encode([
    'success' => true,
    'html' => $html,
    'comment_id' => $comment_id
]);
