<?php

$page_title = "Search Stories";
$page_description = "Search for stories in SariSari Stories.";

include_once 'includes/header.php';


$query = sanitize($_GET['q'] ?? '');

// Initialize variables
$stories = [];
$total_results = 0;

// Process search if query provided
if (!empty($query)) {
    // Pagination setup
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = POSTS_PER_PAGE;
    $offset = ($page - 1) * $limit;
    
    // Search query
    $search_terms = '%' . $query . '%';
    
    // Get stories matching search query
    $sql = "SELECT s.*, c.name as category, c.slug as category_slug, 
                  u.username, u.full_name as author_name, u.profile_image as author_image,
                  (SELECT COUNT(*) FROM likes WHERE story_id = s.id) as likes,
                  (SELECT COUNT(*) FROM comments WHERE story_id = s.id) as comments
           FROM stories s
           JOIN users u ON s.user_id = u.id
           JOIN categories c ON s.category_id = c.id
           WHERE (s.title LIKE ? OR s.content LIKE ? OR u.full_name LIKE ? OR c.name LIKE ?) AND s.status = 'published'
           ORDER BY 
               CASE WHEN s.title LIKE ? THEN 1
                    WHEN c.name LIKE ? THEN 2
                    WHEN u.full_name LIKE ? THEN 3
                    ELSE 4
               END,
               s.created_at DESC
           LIMIT ? OFFSET ?";
    
    $stories = fetch_all($sql, [
        $search_terms, $search_terms, $search_terms, $search_terms,
        $search_terms, $search_terms, $search_terms,
        $limit, $offset
    ]);
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM stories s
                 JOIN users u ON s.user_id = u.id
                 JOIN categories c ON s.category_id = c.id
                 WHERE (s.title LIKE ? OR s.content LIKE ? OR u.full_name LIKE ? OR c.name LIKE ?) AND s.status = 'published'";
    $count_result = fetch_one($count_sql, [$search_terms, $search_terms, $search_terms, $search_terms]);
    $total_results = $count_result ? $count_result['total'] : 0;
    

    $total_pages = ceil($total_results / $limit);
}
?>

<section class="search-section">
    <div class="container">
        <div class="search-header">
            <h1>Search Stories</h1>
            <form action="search.php" method="GET" class="search-form">
                <div class="search-input-group">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search for stories, authors, or categories..." required>
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <?php if (!empty($query)): ?>
            <div class="search-results-info">
                <h2>Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>
                <p><?php echo $total_results; ?> result<?php echo $total_results !== 1 ? 's' : ''; ?> found</p>
            </div>
            
            <?php if (empty($stories)): ?>
                <div class="no-results">
                    <p>No stories found matching your search.</p>
                    <p>Try different keywords or <a href="discover.php">browse all stories</a>.</p>
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
                            <a href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $page - 1; ?>" class="pagination-link prev">&laquo; Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <a href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $page + 1; ?>" class="pagination-link next">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <div class="search-suggestions">
                <h3>Popular Categories</h3>
                <div class="categories-list">
                    <?php 
                    $popular_categories = fetch_all("
                        SELECT c.name, c.slug, c.icon, COUNT(s.id) as story_count
                        FROM categories c
                        LEFT JOIN stories s ON c.id = s.category_id
                        GROUP BY c.id
                        ORDER BY story_count DESC
                        LIMIT 6
                    ");
                    
                    foreach ($popular_categories as $cat): 
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

include_once 'includes/footer.php';
?>
