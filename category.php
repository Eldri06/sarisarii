<?php
/**
 * Category page for SariSari Stories
 */

// Include header
include_once 'includes/header.php';

// Get category slug from URL
$slug = sanitize($_GET['slug'] ?? '');

// If no slug provided, redirect to discover page
if (empty($slug)) {
    header('Location: discover.php');
    exit;
}

// Get category data
$category = get_category($slug);

// If category not found, show error
if (!$category) {
    $page_title = "Category Not Found";
    $page_description = "The requested category was not found.";
    $error = "Category not found.";
} else {
    // Set page title and description
    $page_title = $category['name'];
    $page_description = $category['description'];
    
    // Pagination setup
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = POSTS_PER_PAGE;
    $offset = ($page - 1) * $limit;
    
    // Get stories in this category
    $sql = "SELECT s.*, c.name as category, c.slug as category_slug, 
                  u.username, u.full_name as author_name, u.profile_image as author_image,
                  (SELECT COUNT(*) FROM likes WHERE story_id = s.id) as likes,
                  (SELECT COUNT(*) FROM comments WHERE story_id = s.id) as comments
           FROM stories s
           JOIN users u ON s.user_id = u.id
           JOIN categories c ON s.category_id = c.id
           WHERE s.category_id = ? AND s.status = 'published'
           ORDER BY s.created_at DESC
           LIMIT ? OFFSET ?";
    $stories = fetch_all($sql, [$category['id'], $limit, $offset]);
    
    // Get total stories count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM stories WHERE category_id = ? AND status = 'published'";
    $count_result = fetch_one($count_sql, [$category['id']]);
    $total_stories = $count_result ? $count_result['total'] : 0;
    
    // Calculate total pages
    $total_pages = ceil($total_stories / $limit);
}
?>

<section class="category-section">
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
                <p><a href="discover.php">View all categories</a></p>
            </div>
        <?php else: ?>
            <div class="category-header">
                <div class="category-icon">
                    <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
                </div>
                <div class="category-info">
                    <h1><?php echo htmlspecialchars($category['name']); ?></h1>
                    <p><?php echo htmlspecialchars($category['description']); ?></p>
                </div>
            </div>
            
            <?php if (empty($stories)): ?>
                <div class="no-stories">
                    <p>No stories in this category yet.</p>
                    <a href="create-story.php" class="primary-btn">Be the first to share a story</a>
                </div>
            <?php else: ?>
                <div class="stories-grid">
                    <?php foreach ($stories as $story): ?>
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
                
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="category.php?slug=<?php echo urlencode($slug); ?>&page=<?php echo $page - 1; ?>" class="pagination-link prev">&laquo; Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="category.php?slug=<?php echo urlencode($slug); ?>&page=<?php echo $i; ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="category.php?slug=<?php echo urlencode($slug); ?>&page=<?php echo $page + 1; ?>" class="pagination-link next">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="category-footer">
                <h3>Explore Other Categories</h3>
                <div class="categories-list">
                    <?php 
                    $other_categories = fetch_all("SELECT name, slug, icon FROM categories WHERE id != ? ORDER BY name ASC", [$category['id']]);
                    foreach ($other_categories as $cat): 
                    ?>
                        <a href="category.php?slug=<?php echo htmlspecialchars($cat['slug']); ?>" class="category-pill">
                            <i class="<?php echo htmlspecialchars($cat['icon']); ?>"></i>
                            <span><?php echo htmlspecialchars($cat['name']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
