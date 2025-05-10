<?php
/**
 * Admin Dashboard for SariSari Stories
 */

// Page title
$page_title = "Dashboard";

// Include header
require_once 'includes/header.php';

// Get admin statistics
$stats = get_admin_stats();
?>

<div class="dashboard-header">
  <h1>Admin Dashboard</h1>
  <p>Welcome back, <?php echo $current_user['full_name']; ?>! Here's an overview of your site.</p>
</div>

<!-- Stats Summary Cards -->
<section class="stats-cards">
  <div class="stat-card">
    <div class="stat-icon">
      <i class="fas fa-users"></i>
    </div>
    <div class="stat-details">
      <h3>Users</h3>
      <p class="stat-number"><?php echo number_format($stats['total_users']); ?></p>
    </div>
    <a href="users.php" class="stat-link">View All</a>
  </div>
  
  <div class="stat-card">
    <div class="stat-icon">
      <i class="fas fa-book"></i>
    </div>
    <div class="stat-details">
      <h3>Stories</h3>
      <p class="stat-number"><?php echo number_format($stats['total_stories']); ?></p>
    </div>
    <a href="stories.php" class="stat-link">View All</a>
  </div>
  
  <div class="stat-card">
    <div class="stat-icon">
      <i class="fas fa-comments"></i>
    </div>
    <div class="stat-details">
      <h3>Comments</h3>
      <p class="stat-number"><?php echo number_format($stats['total_comments']); ?></p>
    </div>
    <a href="comments.php" class="stat-link">View All</a>
  </div>
  
  <div class="stat-card">
    <div class="stat-icon">
      <i class="fas fa-heart"></i>
    </div>
    <div class="stat-details">
      <h3>Likes</h3>
      <p class="stat-number"><?php echo number_format($stats['total_likes']); ?></p>
    </div>
    <a href="#" class="stat-link">View Stats</a>
  </div>
</section>

<!-- Recent Content Section -->
<section class="dashboard-section">
  <div class="dashboard-section-header">
    <h2>Recent Activity</h2>
  </div>
  
  <div class="dashboard-grid">
    <!-- Recent Users -->
    <div class="dashboard-card">
      <div class="dashboard-card-header">
        <h3>Recent Users</h3>
        <a href="users.php" class="view-all-link">View All</a>
      </div>
      <div class="dashboard-card-body">
        <?php if (!empty($stats['recent_users'])): ?>
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>User</th>
                <th>Email</th>
                <th>Date Joined</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($stats['recent_users'] as $user): ?>
                <tr>
                  <td>
                    <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="user-link">
                      <?php echo htmlspecialchars($user['username']); ?>
                    </a>
                  </td>
                  <td><?php echo htmlspecialchars($user['email']); ?></td>
                  <td><?php echo format_date($user['created_at']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="no-results">No users found.</p>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Recent Stories -->
    <div class="dashboard-card">
      <div class="dashboard-card-header">
        <h3>Recent Stories</h3>
        <a href="stories.php" class="view-all-link">View All</a>
      </div>
      <div class="dashboard-card-body">
        <?php if (!empty($stats['recent_stories'])): ?>
          <table class="dashboard-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($stats['recent_stories'] as $story): ?>
                <tr>
                  <td>
                    <a href="stories.php?action=edit&id=<?php echo $story['id']; ?>" class="story-link">
                      <?php echo htmlspecialchars($story['title']); ?>
                    </a>
                  </td>
                  <td><?php echo htmlspecialchars($story['username']); ?></td>
                  <td><?php echo format_date($story['created_at']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="no-results">No stories found.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Quick Actions Section -->
<section class="dashboard-section">
  <div class="dashboard-section-header">
    <h2>Quick Actions</h2>
  </div>
  
  <div class="quick-actions">
    <a href="stories.php?action=new" class="quick-action-btn">
      <i class="fas fa-plus"></i>
      <span>Add Story</span>
    </a>
    <a href="users.php?action=new" class="quick-action-btn">
      <i class="fas fa-user-plus"></i>
      <span>Add User</span>
    </a>
    <a href="categories.php?action=new" class="quick-action-btn">
      <i class="fas fa-folder-plus"></i>
      <span>Add Category</span>
    </a>
    <a href="../index.php" class="quick-action-btn">
      <i class="fas fa-external-link-alt"></i>
      <span>View Site</span>
    </a>
  </div>
</section>

<?php
// Include footer
require_once 'includes/footer.php';
?>