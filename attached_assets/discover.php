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

// Determine what to display: featured or category or all
$is_featured = isset($_GET['featured']) && $_GET['featured'] == 1;
$category_slug = isset($_GET['category']) ? $_GET['category'] : null;
$category = null;

// For pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = POSTS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Get stories based on filters
if ($is_featured) {
    // Get featured stories
    $stories = get_featured_stories($limit);
    $page_title = 'Featured Stories - ' . SITE_NAME;
    $page_heading = 'Featured Stories';
    $page_subheading = 'Discover selected stories that showcase the best of our community.';
} elseif ($category_slug) {
    // Get category info
    $category = get_category($category_slug);
    
    if (!$category) {
        // Category not found, redirect to discover page
        header('Location: /discover.php');
        exit;
    }
    
    // Get stories by category
    $stories = get_stories_by_category($category['id'], $limit);
    $page_title = $category['name'] . ' Stories - ' . SITE_NAME;
    $page_heading = $category['name'] . ' Stories';
    $page_subheading = $category['description'];
} else {
    // Get recent stories
    $stories = get_recent_stories($limit, $offset);
    $page_title = 'Discover Stories - ' . SITE_NAME;
    $page_heading = 'Discover Stories';
    $page_subheading = 'Explore stories from across the Philippines, sharing experiences and local discoveries.';
}

// Get all categories for the sidebar
$categories = get_categories();

// Include header
require_once '../includes/header.php';
?>

<div class="page-content">
  <div class="container">
    <h1 class="section-title"><?php echo $page_heading; ?></h1>
    <p class="section-subtitle"><?php echo $page_subheading; ?></p>
    
    <div class="discover-layout">
      <div class="stories-container">
        <?php if (empty($stories)): ?>
          <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h3>No stories found</h3>
            <?php if ($category): ?>
              <p>There are no stories in this category yet.</p>
            <?php else: ?>
              <p>Be the first to share your story with the community!</p>
            <?php endif; ?>
            <a href="create-story.php" class="primary-btn">Share a Story</a>
          </div>
        <?php else: ?>
          <div class="stories-grid">
            <?php foreach ($stories as $story): ?>
              <div class="story-card">
                <div class="story-image">
                  <img src="<?php echo !empty($story['featured_image']) ? $story['featured_image'] : 'https://images.unsplash.com/photo-1580674684081-7617fbf3d745?w=800&auto=format&fit=crop'; ?>" alt="<?php echo $story['title']; ?>">
                  <span class="category"><?php echo $story['category']; ?></span>
                </div>
                <div class="story-content">
                  <h3><a href="story.php?slug=<?php echo $story['slug']; ?>"><?php echo $story['title']; ?></a></h3>
                  <div class="story-meta">
                    <div class="author">
                      <img src="<?php echo !empty($story['author_image']) ? $story['author_image'] : 'images/default-avatar.jpg'; ?>" alt="<?php echo $story['author_name']; ?>">
                      <span><?php echo $story['author_name']; ?></span>
                    </div>
                    <div class="story-stats">
                      <span><i class="fas fa-heart"></i> <?php echo $story['likes']; ?></span>
                      <span><i class="fas fa-comment"></i> <?php echo $story['comments']; ?></span>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          
          <?php if (!$is_featured && !$category_slug): ?>
            <!-- Pagination -->
            <div class="pagination">
              <?php 
              // Calculate total pages - in a real application, you would get the total count from the database
              $total_pages = ceil(count_stories_total() / $limit);
              
              if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="pagination-item">Previous</a>
              <?php endif; ?>
              
              <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="pagination-item <?php echo $i == $page ? 'active' : ''; ?>">
                  <?php echo $i; ?>
                </a>
              <?php endfor; ?>
              
              <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="pagination-item">Next</a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
      
      <div class="sidebar">
        <div class="sidebar-section">
          <h3>Categories</h3>
          <ul class="category-list">
            <?php foreach ($categories as $cat): ?>
              <li>
                <a href="discover.php?category=<?php echo $cat['slug']; ?>" class="<?php echo ($category_slug === $cat['slug']) ? 'active' : ''; ?>">
                  <i class="<?php echo $cat['icon']; ?>"></i>
                  <span><?php echo $cat['name']; ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        
        <div class="sidebar-section">
          <h3>Quick Links</h3>
          <ul class="quick-links">
            <li>
              <a href="discover.php" class="<?php echo (!$is_featured && !$category_slug) ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Recent Stories
              </a>
            </li>
            <li>
              <a href="discover.php?featured=1" class="<?php echo $is_featured ? 'active' : ''; ?>">
                <i class="fas fa-star"></i> Featured Stories
              </a>
            </li>
            <?php if ($current_user): ?>
              <li>
                <a href="create-story.php">
                  <i class="fas fa-edit"></i> Share Your Story
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </div>
        
        <div class="sidebar-cta">
          <h3>Share Your Experience</h3>
          <p>Have a story about your neighborhood, a hidden gem, or local tradition?</p>
          <a href="create-story.php" class="primary-btn">Share a Story</a>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  /* Additional styles for the discover page */
  .discover-layout {
    display: grid;
    grid-template-columns: 3fr 1fr;
    gap: 40px;
  }
  
  .sidebar {
    position: sticky;
    top: 100px;
    height: fit-content;
  }
  
  .sidebar-section {
    background-color: #fff;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 25px;
    margin-bottom: 30px;
  }
  
  .sidebar-section h3 {
    margin-bottom: 20px;
    font-size: 1.3rem;
  }
  
  .category-list li,
  .quick-links li {
    margin-bottom: 12px;
  }
  
  .category-list a,
  .quick-links a {
    display: flex;
    align-items: center;
    padding: 8px 10px;
    border-radius: var(--border-radius);
    transition: var(--transition);
  }
  
  .category-list a:hover,
  .quick-links a:hover,
  .category-list a.active,
  .quick-links a.active {
    background-color: var(--bg-light);
    color: var(--primary-color);
  }
  
  .category-list i,
  .quick-links i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
    color: var(--primary-color);
  }
  
  .sidebar-cta {
    background-color: var(--bg-light);
    border-radius: var(--border-radius);
    padding: 25px;
    text-align: center;
  }
  
  .sidebar-cta h3 {
    margin-bottom: 15px;
    font-size: 1.3rem;
  }
  
  .sidebar-cta p {
    margin-bottom: 20px;
    color: #666;
  }
  
  .pagination {
    display: flex;
    justify-content: center;
    margin-top: 40px;
  }
  
  .pagination-item {
    display: inline-block;
    padding: 8px 16px;
    margin: 0 5px;
    border-radius: var(--border-radius);
    border: 1px solid #ddd;
    transition: var(--transition);
  }
  
  .pagination-item:hover {
    background-color: var(--bg-light);
  }
  
  .pagination-item.active {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
  }
  
  @media (max-width: 992px) {
    .discover-layout {
      grid-template-columns: 1fr;
    }
    
    .sidebar {
      margin-top: 40px;
      position: static;
    }
  }
</style>

<?php
// Include footer
require_once '../includes/footer.php';

// Helper function to get total number of stories (for pagination)
function count_stories_total() {
    $sql = "SELECT COUNT(*) as count FROM stories WHERE status = 'published'";
    $result = fetch_one($sql);
    return $result ? $result['count'] : 0;
}
?>
