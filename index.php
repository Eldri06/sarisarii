<?php

$page_title = "Home";
$page_description = "A vibrant Filipino community platform for sharing personal narratives, hidden gems, local cuisine, events, and traditions.";


include_once 'includes/header.php';


$hero_story = get_featured_stories(1)[0] ?? null;

$featured_stories = get_featured_stories(6);


$categories = get_categories();
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
        <a href="discover.php" class="primary-btn">Explore Stories</a>
        <a href="create-story.php" class="secondary-btn">Get Started</a>
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
                <img src="<?php echo htmlspecialchars($story['images/'] ?? 'https://images.unsplash.com/photo-1581216340441-d47cad9210a4?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'); ?>" alt="<?php echo htmlspecialchars($story['title']); ?>">
                <a href="category.php?slug=<?php echo htmlspecialchars($story['category_slug'] ?? '#'); ?>" class="category"><?php echo htmlspecialchars($story['category']); ?></a>
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
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <div class="view-more">
      <a href="discover.php" class="view-more-btn">View More Stories</a>
    </div>
  </div>
</section>

<!-- Categories Section -->
<section class="categories">
  <div class="container">
    <h2 class="section-title">Explore Categories</h2>
    <p class="section-subtitle">Find stories that match your interests</p>

    <div class="categories-grid">
      <?php
        // Merge or define categories array if not already set
        if (!isset($categories) || empty($categories)) {
          $categories = [
            [
              'icon' => 'fas fa-utensils',
              'slug' => 'local-eats',
              'name' => 'Local Eats',
              'description' => 'Discover hidden culinary gems and traditional recipes'
            ],
            [
              'icon' => 'fas fa-book-open',
              'slug' => 'personal-stories',
              'name' => 'Personal Stories',
              'description' => 'Share meaningful experiences and memories from your community'
            ],
            [
              'icon' => 'fas fa-map-marker-alt',
              'slug' => 'hidden-spots',
              'name' => 'Hidden Spots',
              'description' => 'Explore lesser-known places with cultural and historical significance'
            ],
            [
              'icon' => 'fas fa-camera',
              'slug' => 'visual-tales',
              'name' => 'Visual Tales',
              'description' => 'Photo essays that capture the spirit of Filipino culture'
            ],
            [
              'icon' => 'fas fa-calendar-alt',
              'slug' => 'local-events',
              'name' => 'Local Events',
              'description' => 'Upcoming fiestas, markets, and community gatherings'
            ],
            [
              'icon' => 'fas fa-users',
              'slug' => 'community-heroes',
              'name' => 'Community Heroes',
              'description' => 'Celebrating individuals making a difference'
            ]
          ];
        }

        foreach ($categories as $category):
      ?>
        <a href="category.php?slug=<?php echo htmlspecialchars($category['slug'] ?? '#'); ?>" class="category-card">
          <div class="category-icon">
            <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
          </div>
          <h3><?php echo htmlspecialchars($category['name'] ?? $category['title']); ?></h3>
          <p><?php echo htmlspecialchars($category['description']); ?></p>
        </a>
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
    <div class="newsletter-box">
      <div class="newsletter-content">
        <h3>Subscribe to SariSari Stories</h3>
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
