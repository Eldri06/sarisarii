<?php
/**
 * About page for SariSari Stories
 */

// Include header
$page_title = "About SariSari Stories";
$page_description = "Learn about SariSari Stories, a vibrant Filipino community platform for sharing personal narratives, hidden gems, local cuisine, events, and traditions.";

include_once 'includes/header.php';
?>

<section class="about-hero">
    <div class="container">
        <div class="about-hero-content">
            <h1>About SariSari Stories</h1>
            <h2>Our Story</h2>
            <p>SariSari Stories is a vibrant community platform dedicated to preserving and celebrating Filipino culture through storytelling. Just as a traditional "sari-sari" store offers a variety of everyday goods, our platform houses a diverse collection of personal narratives, hidden local gems, culinary treasures, and community traditions from all corners of the Philippines.</p>
        </div>
    </div>
</section>

<section class="about-mission">
    <div class="container">
        <div class="mission-content">
            <div class="mission-text">
                <h2>Our Mission</h2>
                <p>We believe every Filipino story deserves to be told and preserved. Our mission is to create a space where individuals can share their authentic experiences, discover new perspectives, and connect through the power of storytelling.</p>
                <p>In a rapidly changing world, SariSari Stories aims to be a digital repository of Filipino culture and daily life, creating a lasting legacy for future generations while fostering community and understanding in the present.</p>
            </div>
            <div class="mission-values">
                <div class="value-card">
                    <i class="fas fa-heart"></i>
                    <h3>Authenticity</h3>
                    <p>We value real stories from real people, highlighting the genuine Filipino experience in all its diversity.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-users"></i>
                    <h3>Community</h3>
                    <p>We foster connections between Filipinos worldwide through shared experiences and cultural pride.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-book"></i>
                    <h3>Preservation</h3>
                    <p>We document and preserve traditions, recipes, and stories that might otherwise be lost to time.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="about-how-it-works">
    <div class="container">
        <h2>How It Works</h2>
        <div class="steps-container">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Create an Account</h3>
                <p>Sign up to become part of our community. It's quick, easy, and free.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Share Your Story</h3>
                <p>Write about your experiences, family recipes, hidden local spots, or community traditions.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Connect & Discover</h3>
                <p>Engage with other stories through likes and comments, and discover new perspectives from the community.</p>
            </div>
        </div>
    </div>
</section>

<section class="about-categories">
    <div class="container">
        <h2>Story Categories</h2>
        <p class="section-subtitle">Our platform features various categories to help organize and discover stories:</p>
        
        <div class="categories-grid">
            <?php 
            $categories = get_categories();
            foreach ($categories as $category): 
            ?>
                <div class="category-card">
                    <div class="category-icon">
                        <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
                    </div>
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="about-community">
    <div class="container">
        <div class="cta-box">
            <div class="cta-content">
                <h2>Join Our Community</h2>
                <p>Ready to share your story or discover narratives from fellow Filipinos? <br> Join SariSari Stories today and become part of our growing community.</p>
                <div class="cta-buttons">
                    <a href="register.php" class="primary-btn">Sign Up</a>
                    <a href="discover.php" class="a-secondary-btn">Explore Stories</a>
                </div>
            </div>
        </div>
    </div>
</section>
<hr>

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
