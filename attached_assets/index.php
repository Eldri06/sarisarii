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

// Get featured stories
$featured_stories = get_featured_stories(3);

// Get categories
$categories = get_categories();

// Page title and description
$page_title = SITE_NAME;
$page_description = SITE_DESCRIPTION;

// Process newsletter subscription
$subscription_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subscribe'])) {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    
    if (subscribe_newsletter($email)) {
        $subscription_message = "Thank you for subscribing!";
    } else {
        $subscription_message = "Subscription failed. Please try again.";
    }
}

// Include header
require_once '../includes/header.php';
?>

  <!-- Hero Section -->
  <section class="hero">
    <div class="overlay"></div>
    <div class="container">
      <div class="hero-content">
        <h1>Share Your <br> Local Stories</h1>
        <h2>Discover Your Community</h2>
        <p>A vibrant Filipino community platform for sharing personal narratives, hidden gems, local cuisine, events, and traditions from your neighborhood or hometown.</p>
        <div class="hero-buttons">
          <a href="discover.php" class="primary-btn">Start Exploring</a>
          <?php if (!$current_user): ?>
            <a href="register.php" class="secondary-btn">Get Started</a>
          <?php else: ?>
            <a href="create-story.php" class="secondary-btn">Share a Story</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

<!-- Featured Stories Section -->
<section class="featured" id="featured-section">
  <div class="container">
    <h2 class="section-title">Featured Stories</h2>
    <p class="section-subtitle">Discover community stories, local traditions, and hidden gems shared by fellow Filipinos.</p>

    <div class="stories-grid">
      <?php if (!isset($_SESSION['user'])): ?>
        <!-- Not logged in: show default curated stories -->
        <?php
          // These can be hardcoded or fetched from a public set
          $default_stories = [
            [
              'title' => 'The Hidden Bakery of San Juan',
              'category' => 'Food',
              'featured_image' => 'images/bakery.jpg',
              'author_name' => 'Eldrian Colinares',
              'author_image' => 'images/author1.jpg',
              'likes' => 126,
              'comments' => 24,
              'slug' => 'hidden-bakery-san-juan'
            ],
            [
              'title' => 'Afternoons at Luneta Park',
              'category' => 'City Life',
              'featured_image' => 'images/park.jpg',
              'author_name' => 'Jacob Demelino',
              'author_image' => 'images/author2.jpg',
              'likes' => 98,
              'comments' => 14,
              'slug' => 'afternoons-luneta-park'
            ],
            [
              'title' => 'The Flower Markets of Dangwa',
              'category' => 'Culture',
              'featured_image' => 'images/market.jpg',
              'author_name' => 'Gabriel Bitoy',
              'author_image' => 'images/author3.jpg',
              'likes' => 156,
              'comments' => 32,
              'slug' => 'flower-markets-dangwa'
            ],
          ];

          foreach ($default_stories as $story):
        ?>
          <div class="story-card">
            <div class="story-image">
              <img src="<?php echo $story['featured_image']; ?>" alt="<?php echo $story['title']; ?>">
              <span class="category"><?php echo $story['category']; ?></span>
            </div>
            <div class="story-content">
              <h3><a href="story.php?slug=<?php echo $story['slug']; ?>"><?php echo $story['title']; ?></a></h3>
              <div class="story-meta">
                <div class="author">
                  <img src="<?php echo $story['author_image']; ?>" alt="<?php echo $story['author_name']; ?>">
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

      <?php else: ?>
        <!-- Logged-in user view -->
        <?php if (empty($featured_stories)): ?>
          <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h3>No stories yet</h3>
            <p>Be the first to share your story with the community!</p>
            <a href="create-story.php" class="primary-btn">Share a Story</a>
          </div>
        <?php else: ?>
          <?php foreach ($featured_stories as $story): ?>
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
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <div class="view-more">
      <a href="discover.php" class="view-more-btn">View More Stories</a>
    </div>
  </div>
</section>

  <!-- Categories Section -->
  <!-- Categories Section -->
  <section class="categories">
    <div class="container">
      <h2 class="section-title">Discover by Category</h2>
      <p class="section-subtitle">Explore stories across different aspects of Filipino community life</p>

      <div class="categories-grid">
        <?php
        // Category data - can be from database later
        $categories = [
            [
                'icon' => 'fas fa-utensils',
                'title' => 'Local Eats',
                'description' => 'Discover hidden culinary gems and traditional recipes'
            ],
            [
                'icon' => 'fas fa-book-open',
                'title' => 'Personal Stories',
                'description' => 'Share meaningful experiences and memories from your community'
            ],
            [
                'icon' => 'fas fa-map-marker-alt',
                'title' => 'Hidden Spots',
                'description' => 'Explore lesser-known places with cultural and historical significance'
            ],
            [
                'icon' => 'fas fa-camera',
                'title' => 'Visual Tales',
                'description' => 'Photo essays that capture the spirit of Filipino culture'
            ],
            [
                'icon' => 'fas fa-calendar-alt',
                'title' => 'Local Events',
                'description' => 'Upcoming fiestas, markets, and community gatherings'
            ],
            [
                'icon' => 'fas fa-users',
                'title' => 'Community Heroes',
                'description' => 'Celebrating individuals making a difference'
            ]
        ];
        
        foreach ($categories as $category) :
        ?>
        <div class="category-card">
          <div class="category-icon">
            <i class="<?php echo $category['icon']; ?>"></i>
          </div>
          <h3><?php echo $category['title']; ?></h3>
          <p><?php echo $category['description']; ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Join Community Section -->
<section class="join-community">
  <div class="container">
    <div class="join-content">
      <h2 class="section-title">Join Our Growing Community</h2>
      <p>Connect with storytellers from across the Philippines and the diaspora. Share your own narratives, discover local traditions, and celebrate the vibrant tapestry of Filipino culture.</p>
      <div class="join-buttons">
        <?php if (!isset($_SESSION['user'])): ?>
          <a href="register.php" class="primary-btn">Sign Up</a>
        <?php else: ?>
          <a href="discover.php" class="primary-btn">Explore</a>
        <?php endif; ?>
        <a href="#featured-section" class="secondary-btn">Learn More</a>
      </div>
    </div>
    <div class="join-image">
      <img src="images/community.jpg" alt="Filipino Community Celebration">
    </div>
  </div>
</section>


  <!-- Newsletter Section -->
  <section class="newsletter">
    <div class="container">
      <h2 class="section-title">Subscribe to SariSari Stories</h2>
      <p>Stay updated with the latest stories, community events, and featured content delivered straight to your inbox.</p>
      <form class="newsletter-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit" name="subscribe" class="primary-btn">Subscribe</button>
      </form>
      <?php if (!empty($subscription_message)): ?>
        <p class="subscription-confirmation"><?php echo $subscription_message; ?></p>
      <?php endif; ?>
      <p class="privacy-note">We respect your privacy. Unsubscribe at any time.</p>
    </div>
  </section>

<?php
// Include footer
require_once '../includes/footer.php';
?>
