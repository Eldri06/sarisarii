<?php
/**
 * User profile page for SariSari Stories
 */

// Include header
$page_title = "User Profile";
$page_description = "View and manage your SariSari Stories profile, stories, and interactions.";

include_once 'includes/header.php';

// Get profile username from URL
$username = sanitize($_GET['username'] ?? '');

// If no username provided and user is logged in, use current user
if (empty($username) && is_logged_in()) {
    $username = $current_user['username'];
}

// If no username provided and user is not logged in, redirect to login
if (empty($username)) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['PHP_SELF']));
    exit;
}

// Get user data
$sql = "SELECT id, username, full_name, bio, profile_image, created_at FROM users WHERE username = ?";
$profile_user = fetch_one($sql, [$username]);

// If user not found, show error
if (!$profile_user) {
    $error = "User not found.";
}

// Get user's stories
$user_stories = [];
if ($profile_user) {
    $user_stories = get_stories_by_user($profile_user['id']);
}

// Check if logged-in user is viewing their own profile
$is_own_profile = is_logged_in() && $current_user['id'] == $profile_user['id'];

// Get user's liked stories
$liked_stories = [];
if ($profile_user) {
    $sql = "SELECT s.*, c.name as category, c.slug as category_slug, 
                   u.username, u.full_name as author_name, u.profile_image as author_image,
                   (SELECT COUNT(*) FROM likes WHERE story_id = s.id) as likes,
                   (SELECT COUNT(*) FROM comments WHERE story_id = s.id) as comments
            FROM likes l
            JOIN stories s ON l.story_id = s.id
            JOIN users u ON s.user_id = u.id
            JOIN categories c ON s.category_id = c.id
            WHERE l.user_id = ? AND s.status = 'published'
            ORDER BY l.created_at DESC
            LIMIT 10";
    $liked_stories = fetch_all($sql, [$profile_user['id']]);
}

// Get additional scripts for the profile page
$additional_scripts = '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize active tab
    const activeTab = document.querySelector(".profile-tabs a.active");
    if (activeTab) {
        const tabId = activeTab.getAttribute("href");
        document.querySelector(tabId).classList.add("active");
    }
});
</script>';
?>

<section class="profile-section">
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php else: ?>
            <div class="profile-header">
                <div class="profile-image">
                    <img src="<?php echo htmlspecialchars($profile_user['profile_image'] ?? 'images/default-avatar.jpg'); ?>" alt="<?php echo htmlspecialchars($profile_user['full_name']); ?>">
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($profile_user['full_name']); ?></h1>
                    <p class="username">@<?php echo htmlspecialchars($profile_user['username']); ?></p>
                    <?php if (!empty($profile_user['bio'])): ?>
                        <p class="bio"><?php echo htmlspecialchars($profile_user['bio']); ?></p>
                    <?php endif; ?>
                    <p class="member-since">Member since <?php echo format_date($profile_user['created_at']); ?></p>
                    
                    <?php if ($is_own_profile): ?>
                        <a href="edit-profile.php" class="secondary-btn">Edit Profile</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="profile-content">
                <div class="profile-tabs">
                    <a href="#stories" class="active">Stories</a>
                    <?php if ($is_own_profile): ?>
                        <a href="#liked">Liked Stories</a>
                    <?php endif; ?>
                </div>
                
                <div id="stories" class="tab-content">
                    <h2>Stories by <?php echo htmlspecialchars($profile_user['full_name']); ?></h2>
                    
                    <?php if (empty($user_stories)): ?>
                        <div class="no-content">
                            <p>No stories yet.</p>
                            <?php if ($is_own_profile): ?>
                                <a href="create-story.php" class="primary-btn">Create Your First Story</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="stories-grid">
                            <?php foreach ($user_stories as $story): ?>
                                <div class="story-card">
                                    <div class="story-image">
                                        <img src="<?php echo htmlspecialchars($story['featured_image'] ?? 'images/default-story.jpg'); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>">
                                        <a href="category.php?slug=<?php echo htmlspecialchars($story['category_slug']); ?>" class="category"><?php echo htmlspecialchars($story['category']); ?></a>
                                    </div>
                                    <div class="story-content">
                                        <h3><a href="story.php?slug=<?php echo htmlspecialchars($story['slug']); ?>"><?php echo htmlspecialchars($story['title']); ?></a></h3>
                                        <p><?php echo substr(strip_tags($story['content']), 0, 150) . '...'; ?></p>
                                        <div class="story-meta">
                                            <div class="stats">
                                                <span class="likes"><i class="far fa-heart"></i> <?php echo htmlspecialchars($story['likes']); ?></span>
                                                <span class="comments"><i class="far fa-comment"></i> <?php echo htmlspecialchars($story['comments']); ?></span>
                                                <span class="date"><?php echo time_elapsed_string($story['created_at']); ?></span>
                                            </div>
                                            <?php if ($is_own_profile): ?>
                                                <div class="story-actions">
                                                    <a href="edit-story.php?id=<?php echo $story['id']; ?>" class="edit-btn"><i class="fas fa-edit"></i></a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($is_own_profile): ?>
                    <div id="liked" class="tab-content">
                        <h2>Stories You've Liked</h2>
                        
                        <?php if (empty($liked_stories)): ?>
                            <div class="no-content">
                                <p>You haven't liked any stories yet.</p>
                                <a href="discover.php" class="primary-btn">Discover Stories</a>
                            </div>
                        <?php else: ?>
                            <div class="stories-grid">
                                <?php foreach ($liked_stories as $story): ?>
                                    <div class="story-card">
                                        <div class="story-image">
                                            <img src="<?php echo htmlspecialchars($story['featured_image'] ?? 'images/default-story.jpg'); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>">
                                            <a href="category.php?slug=<?php echo htmlspecialchars($story['category_slug']); ?>" class="category"><?php echo htmlspecialchars($story['category']); ?></a>
                                        </div>
                                        <div class="story-content">
                                            <h3><a href="story.php?slug=<?php echo htmlspecialchars($story['slug']); ?>"><?php echo htmlspecialchars($story['title']); ?></a></h3>
                                            <p><?php echo substr(strip_tags($story['content']), 0, 150) . '...'; ?></p>
                                            <div class="story-meta">
                                                <div class="author">
                                                    <img src="<?php echo htmlspecialchars($story['author_image'] ?? 'images/default-avatar.jpg'); ?>" alt="<?php echo htmlspecialchars($story['author_name']); ?>">
                                                    <span><?php echo htmlspecialchars($story['author_name']); ?></span>
                                                </div>
                                                <div class="stats">
                                                    <span class="likes"><i class="far fa-heart"></i> <?php echo htmlspecialchars($story['likes']); ?></span>
                                                    <span class="comments"><i class="far fa-comment"></i> <?php echo htmlspecialchars($story['comments']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
