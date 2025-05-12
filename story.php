<?php
/**
 * Single story page for SariSari Stories
 */

// Include header
include_once 'includes/header.php';

// Get story slug from URL
$slug = sanitize($_GET['slug'] ?? '');

// If no slug provided, redirect to home
if (empty($slug)) {
    header('Location: index.php');
    exit;
}

// Get story data
$story = get_story($slug);

// If story not found, show error
if (!$story) {
    $page_title = "Story Not Found";
    $page_description = "The requested story was not found.";
    $error = "Story not found.";
} else {
    // Set page title and description based on story
    $page_title = $story['title'];
    $page_description = substr(strip_tags($story['content']), 0, 155);
    
    // Get comments for the story
    $comments = get_comments($story['id']);
    
    // Check if current user has liked the story
    $has_liked = is_logged_in() ? user_has_liked($story['id'], $current_user['id']) : false;
    
    // Get related stories from the same category
    $sql = "SELECT s.*, c.name as category, c.slug as category_slug, 
                   u.username, u.full_name as author_name, u.profile_image as author_image,
                   (SELECT COUNT(*) FROM likes WHERE story_id = s.id) as likes,
                   (SELECT COUNT(*) FROM comments WHERE story_id = s.id) as comments
            FROM stories s
            JOIN users u ON s.user_id = u.id
            JOIN categories c ON s.category_id = c.id
            WHERE s.category_id = ? AND s.id != ? AND s.status = 'published'
            ORDER BY s.created_at DESC
            LIMIT 3";
    $related_stories = fetch_all($sql, [$story['category_id'], $story['id']]);
}

// Set share URL
$share_url = SITE_URL . '/story.php?slug=' . urlencode($slug);
?>

<section class="story-section">
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
                <p><a href="index.php">Return to homepage</a></p>
            </div>
        <?php else: ?>
            <article class="story-article">
                <header class="story-header">
                    <div class="story-meta">
                        <a href="category.php?slug=<?php echo htmlspecialchars($story['category_slug']); ?>" class="category-link"><?php echo htmlspecialchars($story['category']); ?></a>
                        <span class="publish-date"><?php echo format_date($story['created_at']); ?></span>
                    </div>
                    <h1 class="story-title"><?php echo htmlspecialchars($story['title']); ?></h1>
                    <div class="author-info">
                        <img src="<?php echo htmlspecialchars($story['author_image'] ?? 'images/default-avatar.jpg'); ?>" alt="<?php echo htmlspecialchars($story['author_name']); ?>" class="author-image">
                        <div class="author-details">
                            <a href="profile.php?username=<?php echo htmlspecialchars($story['username']); ?>" class="author-name"><?php echo htmlspecialchars($story['author_name']); ?></a>
                            <?php if (!empty($story['author_bio'])): ?>
                                <p class="author-bio"><?php echo htmlspecialchars($story['author_bio']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </header>
                
                <?php if (!empty($story['featured_image'])): ?>
                    <div class="story-featured-image">
                    <img src="<?php echo htmlspecialchars($story['/uploads/stories'] ?? 'images/default-story.jpg'); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>">
                    </div>
                <?php endif; ?>
                
                <div class="story-content">
                    <?php echo $story['content']; ?>
                </div>
                
                <footer class="story-footer">
                    <div class="story-actions">
                        <button class="like-button <?php echo $has_liked ? 'liked' : ''; ?>" data-story-id="<?php echo $story['id']; ?>">
                            <i class="<?php echo $has_liked ? 'fas' : 'far'; ?> fa-heart"></i> 
                            <span class="like-count"><?php echo $story['likes']; ?></span>
                        </button>
                        <button class="comment-button">
                            <i class="far fa-comment"></i> 
                            <span class="comment-count"><?php echo $story['comments']; ?></span>
                        </button>
                        <div class="share-buttons">
                            <span>Share:</span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($share_url); ?>" target="_blank" class="share-button facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($share_url); ?>&text=<?php echo urlencode($story['title']); ?>" target="_blank" class="share-button twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                        </div>
                    </div>
                </footer>
            </article>
            
            <section class="comments-section">
                <h2>Comments (<?php echo count($comments); ?>)</h2>
                
                <?php if (is_logged_in()): ?>
                    <div class="comment-form-container">
                        <form id="comment-form" data-story-id="<?php echo $story['id']; ?>">
                            <div class="comment-form-header">
                                <img src="<?php echo htmlspecialchars($current_user['profile_image'] ?? 'images/default-avatar.jpg'); ?>" alt="<?php echo htmlspecialchars($current_user['username']); ?>" class="commenter-image">
                                <span class="commenter-name"><?php echo htmlspecialchars($current_user['full_name']); ?></span>
                            </div>
                            <div class="form-group">
                                <textarea id="comment-content" placeholder="Share your thoughts..." required></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="primary-btn">Post Comment</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="login-to-comment">
                        <p>Please <a href="login.php?redirect=<?php echo urlencode('story.php?slug=' . $slug); ?>">log in</a> to leave a comment.</p>
                    </div>
                <?php endif; ?>
                
                <div id="comments-list" class="comments-list">
                    <?php if (empty($comments)): ?>
                        <div class="no-comments">
                            <p>No comments yet. Be the first to share your thoughts!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <div class="comment-header">
                                    <img src="<?php echo htmlspecialchars($comment['profile_image'] ?? 'images/default-avatar.jpg'); ?>" alt="<?php echo htmlspecialchars($comment['username']); ?>" class="commenter-image">
                                    <div class="comment-meta">
                                        <a href="profile.php?username=<?php echo htmlspecialchars($comment['username']); ?>" class="commenter-name"><?php echo htmlspecialchars($comment['full_name']); ?></a>
                                        <span class="comment-date"><?php echo time_elapsed_string($comment['created_at']); ?></span>
                                    </div>
                                </div>
                                <div class="comment-content">
                                    <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            
            <?php if (!empty($related_stories)): ?>
                <section class="related-stories">
                    <h2>Related Stories</h2>
                    <div class="stories-grid">
                        <?php foreach ($related_stories as $related): ?>
                            <div class="story-card">
                                <div class="story-image">
                                    <img src="<?php echo htmlspecialchars($related['uploads/'] ?? 'images/default-story.jpg'); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">
                                    <a href="category.php?slug=<?php echo htmlspecialchars($related['category_slug']); ?>" class="category"><?php echo htmlspecialchars($related['category']); ?></a>
                                </div>
                                <div class="story-content">
                                    <h3><a href="story.php?slug=<?php echo htmlspecialchars($related['slug']); ?>"><?php echo htmlspecialchars($related['title']); ?></a></h3>
                                    <p><?php echo substr(strip_tags($related['content']), 0, 150) . '...'; ?></p>
                                    <div class="story-meta">
                                        <div class="author">
                                            <img src="<?php echo htmlspecialchars($related['author_image'] ?? 'images/default-avatar.jpg'); ?>" alt="<?php echo htmlspecialchars($related['author_name']); ?>">
                                            <span><?php echo htmlspecialchars($related['author_name']); ?></span>
                                        </div>
                                        <div class="stats">
                                            <span class="likes"><i class="far fa-heart"></i> <?php echo htmlspecialchars($related['likes']); ?></span>
                                            <span class="comments"><i class="far fa-comment"></i> <?php echo htmlspecialchars($related['comments']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
