<?php
/**
 * Comment handler for SariSari Stories
 */

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Set content type to JSON
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

// Get form data
$story_id = isset($_POST['story_id']) ? intval($_POST['story_id']) : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

// Validate input
if (empty($story_id) || empty($content)) {
    echo json_encode([
        'success' => false,
        'message' => 'Story ID and comment content are required'
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

// Add comment
$comment_id = add_comment($story_id, $user['id'], $content);

if (!$comment_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add comment'
    ]);
    exit;
}

// Get the comment data with user details for display
$comment = fetch_one("
    SELECT c.*, u.username, u.full_name, u.profile_image 
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = ?
", [$comment_id]);

// Generate HTML for the new comment
$html = '
<div class="comment-header">
    <img src="' . htmlspecialchars($comment['profile_image'] ?? 'images/default-avatar.jpg') . '" alt="' . htmlspecialchars($comment['username']) . '" class="commenter-image">
    <div class="comment-meta">
        <a href="profile.php?username=' . htmlspecialchars($comment['username']) . '" class="commenter-name">' . htmlspecialchars($comment['full_name']) . '</a>
        <span class="comment-date">just now</span>
    </div>
</div>
<div class="comment-content">
    <p>' . htmlspecialchars($comment['content']) . '</p>
</div>';

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Comment added successfully',
    'html' => $html,
    'comment' => [
        'id' => $comment_id,
        'content' => $content,
        'username' => $user['username'],
        'full_name' => $user['full_name'],
        'profile_image' => $user['profile_image'],
        'created_at' => date('Y-m-d H:i:s')
    ]
]);
