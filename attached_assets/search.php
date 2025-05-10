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

// Initialize variables
$query = '';
$stories = [];
$has_searched = false;

// Process search query
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
    $query = sanitize($_GET['q']);
    
    if (strlen($query) >= 3) {
        // Search for stories
        $stories = search_stories($query);
        $has_searched = true;
    }
}

// Page title
$page_title = 'Search - ' . SITE_NAME;

// Include header
require_once '../includes/header.php';
?>

<div class="page-content">
  <div class="container">
    <div class="search-header">
      <h1 class="section-title">Search Stories</h1>
      <p class="section-subtitle">Find stories by title, content, or keywords</p>
      
      <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="search-form">
        <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Enter keywords..." minlength="3" required>
        <button type="submit"><i class="fas fa-search"></i></button>
      </form>
    </div>
    
    <?php if ($has_searched): ?>
      <div class="search-results">
        <?php if (empty($query)): ?>
          <p class="search-results-summary">Please enter at least 3 characters to search.</p>
        <?php elseif (empty($stories)): ?>
          <p class="search-results-summary">No results found for "<?php echo htmlspecialchars($query); ?>".</p>
          
          <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>No stories found</h3>
            <p>Try searching with different keywords or browse our categories.</p>
            <a href="discover.php" class="primary-btn">Browse Stories</a>
          </div>
        <?php else: ?>
          <p class="search-results-summary"><?php echo count($stories); ?> result<?php echo count($stories) !== 1 ? 's' : ''; ?> found for "<?php echo htmlspecialchars($query); ?>"</p>
          
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
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="search-tips">
        <h3>Search Tips</h3>
        <ul>
          <li>Use specific keywords to find relevant stories</li>
          <li>Search for locations, food names, events, or traditions</li>
          <li>Try both English and Filipino terms</li>
          <li>Minimum 3 characters required for search</li>
        </ul>
      </div>
      
      <div class="popular-categories">
        <h3>Browse by Popular Categories</h3>
        
        <div class="categories-grid">
          <?php foreach (get_categories() as $category): ?>
            <a href="discover.php?category=<?php echo $category['slug']; ?>" class="category-pill">
              <i class="<?php echo $category['icon']; ?>"></i>
              <span><?php echo $category['name']; ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
  /* Additional styles for the search page */
  .search-header {
    text-align: center;
    margin-bottom: 50px;
  }
  
  .search-form {
    max-width: 600px;
    margin: 30px auto 0;
    display: flex;
    box-shadow: var(--box-shadow);
    border-radius: var(--border-radius);
    overflow: hidden;
  }
  
  .search-form input {
    flex: 1;
    padding: 15px 20px;
    border: none;
    font-size: 1.1rem;
    border-radius: var(--border-radius) 0 0 var(--border-radius);
  }
  
  .search-form button {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 0 25px;
    font-size: 1.2rem;
    cursor: pointer;
    transition: var(--transition);
  }
  
  .search-form button:hover {
    background-color: var(--primary-dark);
  }
  
  .search-results-summary {
    margin-bottom: 30px;
    font-size: 1.1rem;
    color: #666;
  }
  
  .search-tips,
  .popular-categories {
    background-color: #fff;
    border-radius: var(--border-radius);
    padding: 30px;
    margin-bottom: 40px;
    box-shadow: var(--box-shadow);
  }
  
  .search-tips h3,
  .popular-categories h3 {
    margin-bottom: 20px;
    font-size: 1.5rem;
  }
  
  .search-tips ul {
    list-style-type: disc;
    padding-left: 20px;
  }
  
  .search-tips li {
    margin-bottom: 10px;
    color: #666;
  }
  
  .categories-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 20px;
  }
  
  .category-pill {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    background-color: var(--bg-light);
    border-radius: 50px;
    transition: var(--transition);
  }
  
  .category-pill:hover {
    background-color: var(--primary-color);
    color: white;
    transform: translateY(-3px);
  }
  
  .category-pill i {
    margin-right: 8px;
  }
</style>

<?php
// Include footer
require_once '../includes/footer.php';
?>
