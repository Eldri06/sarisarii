<?php
// Include configuration and functions
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get current user if logged in
$current_user = get_current_user_data();

// Check if we have a story ID or slug
if (isset($_GET['id'])) {
    $story_param = $_GET['id'];
} elseif (isset($_GET['slug'])) {
    $story_param = $_GET['slug'];
} else {
    // No story ID or slug provided, redirect to discover page
    header('Location: /discover.php');
    exit;
}

// Get story data
$story = get_story($story_param);

if (!$story) {
    // Story not found or not published, redirect to discover page
    header('Location: /discover.php');
    exit;
}

// Get story author
$author = get_user($story['user_id']);

// Get story comments
$comments = get_comments($story['id']);

// Check if user has liked the story
$has_liked = $current_user ? user_has_liked($story['id'], $current_user['id']) : false;

// Get related stories (same category)
$related_stories = get_stories_by_category($story['category_id'], 3);

// Process comment form submission
$comment_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment']) && $current_user) {
    $comment_content = sanitize($_POST['comment_content']);
    
    if (!empty($comment_content)) {
        $result = add_comment($story['id'], $current_user['id'], $comment_content);
        
        if ($result) {
            // Comment added successfully, refresh page to show the new comment
            header("Location: " . $_SERVER['REQUEST_URI'] . "#comments");
            exit;
        } else {
            $comment_message = "Error adding comment. Please try again.";
        }
    } else {
        $comment_message = "Comment cannot be empty.";
    }
}

// Page title and description
$page_title = $story['title'] . ' - ' . SITE_NAME;
$page_description = substr(strip_tags($story['content']), 0, 160) . '...';

// Additional head content for social sharing meta tags
$additional_head = <<<HTML
<!-- Open Graph / Facebook -->
<meta property="og:type" content="article">
<meta property="og:url" content="{$_SERVER['REQUEST_URI']}">
<meta property="og:title" content="{$story['title']}">
<meta property="og:description" content="{$page_description}">
<meta property="og:image" content="{$story['featured_image']}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{$_SERVER['REQUEST_URI']}">
<meta property="twitter:title" content="{$story['title']}">
<meta property="twitter:description" content="{$page_description}">
<meta property="twitter:image" content="{$story['featured_image']}">
HTML;

// Include header
require_once '../includes/header.php';
?>

<div class="page-content">
  <div class="container">
    <div class="story-detail">
      <div class="story-detail-header">
        <a href="discover.php?category=<?php echo $story['category_slug']; ?>" class="category-link">
          <span class="category"><?php echo $story['category']; ?></span>
        </a>
        
        <h1><?php echo $story['title']; ?></h1>
        
        <div class="story-meta-info">
          <div class="author-info">
            <a href="profile.php?username=<?php echo $story['username']; ?>">
              <img src="<?php echo !empty($story['author_image']) ? $story['author_image'] : 'images/default-avatar.jpg'; ?>" alt="<?php echo $story['author_name']; ?>" class="author-img-small">
              <span><?php echo $story['author_name']; ?></span>
            </a>
          </div>
          
          <div class="post-date">
            <span><i class="far fa-calendar-alt"></i> <?php echo format_date($story['created_at']); ?></span>
            <span><i class="far fa-eye"></i> <?php echo $story['views']; ?> views</span>
          </div>
        </div>
      </div>
      
      <?php if (!empty($story['featured_image'])): ?>
        <div class="story-detail-image">
          <img src="<?php echo $story['featured_image']; ?>" alt="<?php echo $story['title']; ?>">
        </div>
      <?php endif; ?>
      
      <div class="story-content-body">
        <?php echo $story['content']; ?>
      </div>
      
      <div class="story-actions">
        <button class="like-button <?php echo $has_liked ? 'liked' : ''; ?>" data-story-id="<?php echo $story['id']; ?>">
          <i class="<?php echo $has_liked ? 'fas' : 'far'; ?> fa-heart"></i>
          <span class="like-count"><?php echo $story['likes']; ?></span> likes
        </button>
        
        <div class="share-buttons">
          <span>Share:</span>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" target="_blank" class="share-facebook">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($story['title']); ?>" target="_blank" class="share-twitter">
            <i class="fab fa-twitter"></i>
          </a>
        </div>
      </div>
      
      <div class="author-box">
        <img src="<?php echo !empty($author['profile_image']) ? $author['profile_image'] : 'images/default-avatar.jpg'; ?>" alt="<?php echo $author['full_name']; ?>" class="author-box-image">
        
        <div class="author-box-info">
          <h3><a href="profile.php?username=<?php echo $author['username']; ?>"><?php echo $author['full_name']; ?></a></h3>
          <?php if (!empty($author['bio'])): ?>
            <p><?php echo $author['bio']; ?></p>
          <?php endif; ?>
          <a href="profile.php?username=<?php echo $author['username']; ?>" class="view-more-btn">View all stories</a>
        </div>
      </div>
      
      <div class="comments-section" id="comments">
        <h3><?php echo count($comments); ?> Comments</h3>
        
        <?php if ($current_user): ?>
          <div class="comment-form">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] . '#comments'); ?>" id="comment-form" data-story-id="<?php echo $story['id']; ?>">
              <div class="form-group">
                <textarea id="comment-content" name="comment_content" placeholder="Write your comment..." required></textarea>
                <?php if (!empty($comment_message)): ?>
                  <div class="error"><?php echo $comment_message; ?></div>
                <?php endif; ?>
              </div>
              
              <div class="form-group">
                <button type="submit" name="submit_comment" class="primary-btn">Post Comment</button>
              </div>
            </form>
          </div>
        <?php else: ?>
          <div class="login-to-comment">
            <p>Please <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">login</a> to leave a comment.</p>
          </div>
        <?php endif; ?>
        
        <div class="comment-list" id="comments-list">
          <?php if (empty($comments)): ?>
            <div class="empty-state">
              <p>No comments yet. Be the first to comment!</p>
            </div>
          <?php else: ?>
            <?php foreach ($comments as $comment): ?>
              <div class="comment">
                <img src="<?php echo !empty($comment['profile_image']) ? $comment['profile_image'] : 'images/default-avatar.jpg'; ?>" alt="<?php echo $comment['full_name']; ?>" class="comment-avatar">
                
                <div class="comment-content">
                  <div class="comment-header">
                    <span class="comment-name">
                      <a href="profile.php?username=<?php echo $comment['username']; ?>"><?php echo $comment['full_name']; ?></a>
                    </span>
                    <span class="comment-date"><?php echo time_elapsed_string($comment['created_at']); ?></span>
                  </div>
                  
                  <div class="comment-body">
                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
      
      <?php if (!empty($related_stories)): ?>
        <div class="related-stories">
          <h3>Related Stories</h3>
          
          <div class="stories-grid">
            <?php foreach ($related_stories as $related_story): ?>
              <?php if ($related_story['id'] != $story['id']): ?>
                <div class="story-card">
                  <div class="story-image">
                    <img src="<?php echo !empty($related_story['featured_image']) ? $related_story['featured_image'] : 'https://images.unsplash.com/photo-1580674684081-7617fbf3d745?w=800&auto=format&fit=crop'; ?>" alt="<?php echo $related_story['title']; ?>">
                    <span class="category"><?php echo $related_story['category']; ?></span>
                  </div>
                  <div class="story-content">
                    <h3><a href="story.php?slug=<?php echo $related_story['slug']; ?>"><?php echo $related_story['title']; ?></a></h3>
                    <div class="story-meta">
                      <div class="author">
                        <img src="<?php echo !empty($related_story['author_image']) ? $related_story['author_image'] : 'images/default-avatar.jpg'; ?>" alt="<?php echo $related_story['author_name']; ?>">
                        <span><?php echo $related_story['author_name']; ?></span>
                      </div>
                      <div class="story-stats">
                        <span><i class="fas fa-heart"></i> <?php echo $related_story['likes']; ?></span>
                        <span><i class="fas fa-comment"></i> <?php echo $related_story['comments']; ?></span>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>
