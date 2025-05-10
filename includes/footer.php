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
      <p>&copy; <?php echo date('Y'); ?> SariSari Stories. All rights reserved. 
        <a href="admin/login.php" class="admin-link">Admin Dashboard</a>
      </p>
    </div>
    
    <style>
      .admin-link {
        display: inline-block;
        margin-left: 15px;
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
        padding: 3px 10px;
        border-radius: 4px;
        background-color: rgba(255, 255, 255, 0.1);
        transition: background-color 0.3s, color 0.3s;
      }
      
      .admin-link:hover {
        background-color: rgba(255, 255, 255, 0.2);
        color: #fff;
      }
    </style>
  </div>
</footer>

<script src="js/main.js"></script>
<?php if (isset($additional_scripts)) echo $additional_scripts; ?>
</body>
</html>
