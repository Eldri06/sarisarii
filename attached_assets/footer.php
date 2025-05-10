<?php
// Get footer links
$footer_links = get_footer_links();
?>
<footer>
  <div class="container">
    <div class="footer-top">
      <div class="footer-logo">
        <span>SariSari Stories</span>
        <p>A vibrant community platform for sharing personal stories, hidden gems, local eats, events, and traditions from your neighborhood or hometown.</p>
        <div class="social-links">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
        </div>
      </div>
      
      <div class="footer-links">
        <?php foreach ($footer_links as $group_title => $links) : ?>
        <div class="link-group">
          <h3><?php echo $group_title; ?></h3>
          <ul>
            <?php foreach ($links as $link_title => $url) : ?>
            <li><a href="<?php echo $url; ?>"><?php echo $link_title; ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    
    <div class="footer-bottom">
      <p>&copy; <?php echo date('Y'); ?> SariSari Stories. All rights reserved.</p>
    </div>
  </div>
</footer>
<script>
  // Script to handle header transparency on scroll
  window.addEventListener('scroll', function() {
    const header = document.getElementById('main-header');
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });
  
  // User dropdown menu
  const userMenuTrigger = document.querySelector('.user-menu-trigger');
  if (userMenuTrigger) {
    userMenuTrigger.addEventListener('click', function(e) {
      e.preventDefault();
      const dropdown = document.querySelector('.user-dropdown');
      dropdown.classList.toggle('active');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      const dropdown = document.querySelector('.user-dropdown');
      const trigger = document.querySelector('.user-menu-trigger');
      
      if (dropdown && !dropdown.contains(e.target) && !trigger.contains(e.target)) {
        dropdown.classList.remove('active');
      }
    });
  }
</script>
<?php if (isset($additional_scripts)) echo $additional_scripts; ?>
</body>
</html>
