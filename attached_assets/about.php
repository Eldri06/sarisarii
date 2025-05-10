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

// Page title
$page_title = 'About Us - ' . SITE_NAME;

// Include header
require_once '../includes/header.php';
?>

<div class="page-content">
  <div class="container">
    <div class="about-header">
      <h1 class="section-title">About SariSari Stories</h1>
      <p class="section-subtitle">Connecting communities through stories that celebrate Filipino culture and heritage.</p>
    </div>
    
    <div class="about-content">
      <div class="about-section">
        <div class="about-image">
          <img src="https://images.unsplash.com/photo-1455849318743-b2233052fcff?q=80&w=1469&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Filipino community sharing stories">
        </div>
        
        <div class="about-text">
          <h2>Our Story</h2>
          <p>SariSari Stories was born from a simple idea: that every neighborhood, every community, and every Filipino has unique stories worth sharing. Just like the traditional "sari-sari" store that serves as a gathering place for neighbors, we created a digital space where Filipinos can connect through storytelling.</p>
          <p>Founded in 2023, our platform aims to preserve cultural knowledge, celebrate local traditions, and provide a space for authentic voices to be heard. Through personal narratives, food stories, local guides, and community spotlights, we're building a living archive of Filipino experiences.</p>
        </div>
      </div>
      
      <div class="about-section reversed">
        <div class="about-image">
          <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Filipino cultural celebration">
        </div>
        
        <div class="about-text">
          <h2>Our Mission</h2>
          <p>We believe in the power of storytelling to preserve culture, build community, and create understanding. SariSari Stories is dedicated to:</p>
          <ul>
            <li>Creating a platform where Filipinos can share their authentic experiences</li>
            <li>Documenting and preserving local traditions and cultural practices</li>
            <li>Highlighting hidden gems and local businesses across the Philippines</li>
            <li>Connecting Filipino communities both within the Philippines and across the diaspora</li>
            <li>Celebrating the diversity and richness of Filipino culture</li>
          </ul>
        </div>
      </div>
      
      <div class="values-section">
        <h2 class="section-title">Our Values</h2>
        
        <div class="values-grid">
          <div class="value-card">
            <div class="value-icon">
              <i class="fas fa-users"></i>
            </div>
            <h3>Community</h3>
            <p>We foster a supportive environment where all voices are welcomed and respected.</p>
          </div>
          
          <div class="value-card">
            <div class="value-icon">
              <i class="fas fa-lightbulb"></i>
            </div>
            <h3>Authenticity</h3>
            <p>We celebrate genuine stories that reflect real experiences and perspectives.</p>
          </div>
          
          <div class="value-card">
            <div class="value-icon">
              <i class="fas fa-hands-helping"></i>
            </div>
            <h3>Preservation</h3>
            <p>We are committed to documenting and preserving cultural knowledge for future generations.</p>
          </div>
          
          <div class="value-card">
            <div class="value-icon">
              <i class="fas fa-globe-asia"></i>
            </div>
            <h3>Diversity</h3>
            <p>We honor the rich tapestry of experiences across different Filipino communities.</p>
          </div>
        </div>
      </div>
      
      <div class="how-it-works">
        <h2 class="section-title">How It Works</h2>
        <p class="section-subtitle">Joining our community and sharing your stories is easy!</p>
        
        <div class="steps-grid">
          <div class="step-card">
            <div class="step-number">1</div>
            <h3>Create an Account</h3>
            <p>Sign up for a free account to become part of our storytelling community.</p>
          </div>
          
          <div class="step-card">
            <div class="step-number">2</div>
            <h3>Browse Stories</h3>
            <p>Explore stories by category, region, or author to discover new perspectives.</p>
          </div>
          
          <div class="step-card">
            <div class="step-number">3</div>
            <h3>Share Your Story</h3>
            <p>Write about your neighborhood, local traditions, family recipes, or personal experiences.</p>
          </div>
          
          <div class="step-card">
            <div class="step-number">4</div>
            <h3>Connect</h3>
            <p>Engage with other storytellers through comments, likes, and shared experiences.</p>
          </div>
        </div>
      </div>
      
      <div class="join-community-about">
        <h2>Join Our Growing Community</h2>
        <p>Whether you're a passionate storyteller, a curious reader, or someone looking to reconnect with Filipino culture, there's a place for you at SariSari Stories.</p>
        
        <div class="cta-buttons">
          <?php if (!$current_user): ?>
            <a href="register.php" class="primary-btn">Sign Up Now</a>
            <a href="discover.php" class="a-secondary-btn">Explore Stories</a>
          <?php else: ?>
            <a href="create-story.php" class="primary-btn">Share Your Story</a>
            <a href="discover.php" class="a-secondary-btn" >Explore Stories</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  /* Additional styles for the about page */
  .about-header {
    text-align: center;
    margin-bottom: 50px;
  }
  
  .about-content {
    max-width: 1000px;
    margin: 0 auto;
  }
  
  .about-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    align-items: center;
    margin-bottom: 80px;
  }
  
  .about-section.reversed {
    direction: rtl;
  }
  
  .about-section.reversed .about-text {
    direction: ltr;
  }
  
  .about-image img {
    width: 100%;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
  }
  
  .about-text h2 {
    font-size: 2rem;
    margin-bottom: 20px;
    color: var(--text-dark);
  }
  
  .about-text p {
    margin-bottom: 20px;
    color: #555;
    line-height: 1.8;
  }
  
  .about-text ul {
    padding-left: 20px;
    margin-bottom: 20px;
  }
  
  .about-text li {
    margin-bottom: 10px;
    color: #555;
    line-height: 1.8;
  }
  
  .values-section {
    margin-bottom: 80px;
    text-align: center;
  }
  
  .values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 30px;
    margin-top: 40px;
  }
  
  .value-card {
    background-color: #fff;
    border-radius: var(--border-radius);
    padding: 30px;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
  }
  
  .value-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
  }
  
  .value-icon {
    width: 70px;
    height: 70px;
    background-color: var(--bg-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
  }
  
  .value-icon i {
    font-size: 2rem;
    color: var(--primary-color);
  }
  
  .value-card h3 {
    margin-bottom: 15px;
    font-size: 1.3rem;
  }
  
  .value-card p {
    color: #666;
    font-size: 0.95rem;
  }
  
  .how-it-works {
    margin-bottom: 80px;
    text-align: center;
  }
  
  .steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 30px;
    margin-top: 40px;
  }
  
  .step-card {
    background-color: #fff;
    border-radius: var(--border-radius);
    padding: 30px;
    box-shadow: var(--box-shadow);
    position: relative;
    transition: var(--transition);
  }
  
  .step-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
  }
  
  .step-number {
    width: 40px;
    height: 40px;
    background-color: var(--primary-color);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
    margin: 0 auto 20px;
  }
  
  .step-card h3 {
    margin-bottom: 15px;
    font-size: 1.3rem;
  }
  
  .step-card p {
    color: #666;
    font-size: 0.95rem;
  }
  
  .join-community-about {
    background-color: var(--bg-light);
    border-radius: var(--border-radius);
    padding: 50px;
    text-align: center;
    margin-bottom: 40px;
  }
  
  .join-community-about h2 {
    font-size: 2.2rem;
    margin-bottom: 20px;
  }
  
  .join-community-about p {
    margin-bottom: 30px;
    color: #555;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
  }
 
  .cta-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
  }
   .a-secondary-btn {
    background-color: transparent;
    color: black;
    border: 2px solid black;
    padding: 10px 25px;
    border-radius: var(--border-radius);
    font-weight: 500;
    transition: var(--transition);
    letter-spacing: 0.5px;
    display: inline-block;
    text-align: center;
  }
  
.a-secondary-btn:hover {
    background-color: white;
    transform: translateY(-2px);
    color: var(--bg-dark);
    box-shadow: 0 5px 15px rgba(255, 255, 255, 0.2);
  }
  
  

  
  
  
  @media (max-width: 992px) {
    .about-section {
      grid-template-columns: 1fr;
      gap: 30px;
    }
    
    .about-section.reversed {
      direction: ltr;
    }
    
    .about-image {
      order: 1;
    }
    
    .about-text {
      order: 2;
    }
  }
  
  @media (max-width: 576px) {
    .cta-buttons {
      flex-direction: column;
      gap: 15px;
    }
    
    .join-community-about {
      padding: 30px 20px;
    }
  }
</style>

<?php
// Include footer
require_once '../includes/footer.php';
?>
