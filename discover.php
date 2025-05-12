<?php
/**
 * Discover page for SariSari Stories
 */

// Include header
$page_title = "Discover Stories";
$page_description = "Explore all stories from the SariSari Stories community.";

include_once 'includes/header.php';

// Get categories for filter
$categories = get_categories();

// Process filter parameters
$category_slug = sanitize($_GET['category'] ?? '');
$sort_by = sanitize($_GET['sort'] ?? 'latest');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = POSTS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Build the query based on filters
$where_clause = "s.status = 'published'";
$params = [];
$order_clause = "";

// Add category filter if provided
if (!empty($category_slug)) {
    $where_clause .= " AND c.slug = ?";
    $params[] = $category_slug;
}

// Determine sort order
switch ($sort_by) {
    case 'popular':
        $order_clause = "s.views DESC";
        break;
    case 'likes':
        $order_clause = "(SELECT COUNT(*) FROM likes WHERE story_id = s.id) DESC";
        break;
    case 'comments':
        $order_clause = "(SELECT COUNT(*) FROM comments WHERE story_id = s.id) DESC";
        break;
    case 'oldest':
        $order_clause = "s.created_at ASC";
        break;
    case 'latest':
    default:
        $order_clause = "s.created_at DESC";
        break;
}

// Get the total count of stories matching the filter
$count_sql = "SELECT COUNT(*) as total FROM stories s
              JOIN categories c ON s.category_id = c.id
              WHERE $where_clause";
$count_result = fetch_one($count_sql, $params);
$total_stories = $count_result ? $count_result['total'] : 0;

// Calculate total pages
$total_pages = ceil($total_stories / $limit);

// Adjust page if out of range
$page = min($page, max(1, $total_pages));

// Get stories based on filter
$sql = "SELECT s.*, c.name as category, c.slug as category_slug, 
              u.username, u.full_name as author_name, u.profile_image as author_image,
              (SELECT COUNT(*) FROM likes WHERE story_id = s.id) as likes,
              (SELECT COUNT(*) FROM comments WHERE story_id = s.id) as comments
       FROM stories s
       JOIN users u ON s.user_id = u.id
       JOIN categories c ON s.category_id = c.id
       WHERE $where_clause
       ORDER BY $order_clause
       LIMIT ? OFFSET ?";

// Add limit and offset to params
$params[] = $limit;
$params[] = $offset;

// Execute query
$stories = fetch_all($sql, $params);

// Get selected category name for display
$selected_category = '';
if (!empty($category_slug)) {
    $category_info = get_category($category_slug);
    $selected_category = $category_info ? $category_info['name'] : '';
}
?>

<section class="discover-section">
    <div class="container">
        <div class="discover-header">
            <h1>Discover Stories</h1>
            <p>Explore the diverse collection of Filipino narratives shared by our community.</p>
        </div>
        
        <div class="discover-filters">
            <div class="category-filter">
                <label for="category-select">Category:</label>
                <select id="category-select" onchange="location = this.value;">
                    <option value="discover.php?sort=<?php echo $sort_by; ?>">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="discover.php?category=<?php echo urlencode($category['slug']); ?>&sort=<?php echo urlencode($sort_by); ?>" <?php echo $category_slug === $category['slug'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="sort-filter">
                <label for="sort-select">Sort by:</label>
                <select id="sort-select" onchange="location = this.value;">
                    <option value="discover.php<?php echo !empty($category_slug) ? '?category=' . urlencode($category_slug) : ''; ?>&sort=latest" <?php echo $sort_by === 'latest' ? 'selected' : ''; ?>>
                        Latest
                    </option>
                    <option value="discover.php<?php echo !empty($category_slug) ? '?category=' . urlencode($category_slug) : ''; ?>&sort=oldest" <?php echo $sort_by === 'oldest' ? 'selected' : ''; ?>>
                        Oldest
                    </option>
                    <option value="discover.php<?php echo !empty($category_slug) ? '?category=' . urlencode($category_slug) : ''; ?>&sort=popular" <?php echo $sort_by === 'popular' ? 'selected' : ''; ?>>
                        Most Viewed
                    </option>
                    <option value="discover.php<?php echo !empty($category_slug) ? '?category=' . urlencode($category_slug) : ''; ?>&sort=likes" <?php echo $sort_by === 'likes' ? 'selected' : ''; ?>>
                        Most Liked
                    </option>
                    <option value="discover.php<?php echo !empty($category_slug) ? '?category=' . urlencode($category_slug) : ''; ?>&sort=comments" <?php echo $sort_by === 'comments' ? 'selected' : ''; ?>>
                        Most Commented
                    </option>
                </select>
            </div>
        </div>
        
        <?php if (!empty($selected_category)): ?>
            <div class="filter-info">
                <p>Showing stories in: <strong><?php echo htmlspecialchars($selected_category); ?></strong> <a href="discover.php?sort=<?php echo urlencode($sort_by); ?>" class="clear-filter">(Clear)</a></p>
            </div>
        <?php endif; ?>
        
        <?php if (empty($stories)): ?>
            <div class="no-stories">
                <p>No stories found matching your criteria.</p>
            </div>
        <?php else: ?>
            <div class="stories-grid">
                <?php foreach ($stories as $story): ?>
                    <div class="story-card">
                        <div class="story-image">
                            <img src="<?php echo htmlspecialchars($story['/uploads/stories'] ?? 'images/default-story.jpg'); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>">
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
                        <a href="discover.php?<?php echo !empty($category_slug) ? 'category=' . urlencode($category_slug) . '&' : ''; ?>sort=<?php echo urlencode($sort_by); ?>&page=<?php echo $page - 1; ?>" class="pagination-link prev">&laquo; Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="discover.php?<?php echo !empty($category_slug) ? 'category=' . urlencode($category_slug) . '&' : ''; ?>sort=<?php echo urlencode($sort_by); ?>&page=<?php echo $i; ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="discover.php?<?php echo !empty($category_slug) ? 'category=' . urlencode($category_slug) . '&' : ''; ?>sort=<?php echo urlencode($sort_by); ?>&page=<?php echo $page + 1; ?>" class="pagination-link next">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
