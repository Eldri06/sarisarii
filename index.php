<?php
/**
 * Home page for SariSari Stories
 */

// Page title and description
$page_title = "Home";
$page_description = "A vibrant Filipino community platform for sharing personal narratives, hidden gems, local cuisine, events, and traditions.";

// Include header
include_once 'includes/header.php';

// Get featured stories for hero section
$hero_story = get_featured_stories(1)[0] ?? null;

// Get featured stories for featured section
$featured_stories = get_featured_stories(6);

// Get categories
$categories = get_categories();
?>

<!-- Hero Section -->
<section class="hero">
  <div class="overlay"></div>
  <div class="container">
    <div class="hero-content">
      <h1>Share Your Filipino Story</h1>
      <h2>Discover Tales from Every Corner</h2>
      <p>Join our vibrant community of storytellers preserving and celebrating Filipino culture, traditions, and daily life through personal narratives, hidden gems, local cuisine, and more.</p>
      <div class="hero-buttons">
        <a href="discover.php" class="primary-btn">Explore Stories</a>
        <a href="create-story.php" class="secondary-btn">Share Your Story</a>
      </div>
    </div>
  </div>
</section>

<!-- Featured Stories Section -->
<section class="featured" id="featured-section">
  <div class="container">
    <h2 class="section-title">Featured Stories</h2>
    <p class="section-subtitle">Discover the most compelling narratives from our community</p>
    
    <div class="stories-grid">
      <?php if (!empty($featured_stories)): ?>
        <?php foreach ($featured_stories as $story): ?>
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
      <?php else: ?>
        <div class="no-stories">
          <p>No featured stories yet. Be the first to <a href="create-story.php">share your story</a>!</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Categories Section -->
<section class="categories">
  <div class="container">
    <h2 class="section-title">Explore Categories</h2>
    <p class="section-subtitle">Find stories that match your interests</p>
    
    <div class="categories-grid">
      <?php foreach ($categories as $category): ?>
        <a href="category.php?slug=<?php echo htmlspecialchars($category['slug']); ?>" class="category-card">
          <div class="category-icon">
            <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
          </div>
          <h3><?php echo htmlspecialchars($category['name']); ?></h3>
          <p><?php echo htmlspecialchars($category['description']); ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Community Spotlight -->
<section class="community-spotlight">
  <div class="container">
    <h2 class="section-title">Join Our Community</h2>
    <p class="section-subtitle">Connect with fellow storytellers and explore together</p>
    
    <div class="cta-box">
      <div class="cta-content">
        <h3>Ready to Share Your Story?</h3>
        <p>Whether it's a cherished family recipe, a hidden spot in your hometown, or a personal narrative that reflects Filipino culture, your story matters. Join our community of storytellers today!</p>
        <div class="cta-buttons">
          <a href="register.php" class="primary-btn">Sign Up Now</a>
          <a href="about.php" class="secondary-btn">Learn More</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter">
  <div class="container">
    <div class="newsletter-box">
      <div class="newsletter-content">
        <h3>Subscribe to Our Newsletter</h3>
        <p>Stay updated with the latest stories, community events, and more.</p>
        <form class="newsletter-form">
          <input type="email" name="email" placeholder="Your email address" required>
          <button type="submit" class="primary-btn">Subscribe</button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
