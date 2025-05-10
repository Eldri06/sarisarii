<?php
/**
 * Admin Comments Management for SariSari Stories
 */

// Page title
$page_title = "Comments Management";

// Include header
require_once 'includes/header.php';

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$comment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';
$comment = [];
$comments = [];

// Set table columns and sort options
$columns = [
    'id' => 'ID',
    'content' => 'Content',
    'story_title' => 'Story',
    'username' => 'User',
    'created_at' => 'Date'
];

// Get sorting parameters
$sort_by = isset($_GET['sort']) && array_key_exists($_GET['sort'], $columns) ? $_GET['sort'] : 'created_at';
$sort_dir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'DESC';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$items_per_page = 15;

// Handle Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$story_filter = isset($_GET['story_id']) ? (int)$_GET['story_id'] : 0;
$user_filter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'edit' && $comment_id) {
        // Get form data
        $content = sanitize($_POST['content'] ?? '');
        
        // Validate data
        if (empty($content)) {
            $error = "Comment content is required.";
        } else {
            // Update comment
            $result = update('comments', ['content' => $content], 'id = ?', [$comment_id]);
            
            if ($result) {
                $_SESSION['success_message'] = "Comment updated successfully!";
                header('Location: comments.php');
                exit;
            } else {
                $error = "Failed to update comment. Please try again.";
            }
        }
    } elseif ($action === 'delete' && $comment_id) {
        // Delete comment
        $result = delete('comments', 'id = ?', [$comment_id]);
        
        if ($result) {
            $_SESSION['success_message'] = "Comment deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete comment.";
        }
        
        header('Location: comments.php');
        exit;
    } elseif ($action === 'delete_selected' && isset($_POST['selected_comments'])) {
        // Delete multiple comments
        $selected = $_POST['selected_comments'] ?? [];
        $deleted = 0;
        
        foreach ($selected as $c_id) {
            $result = delete('comments', 'id = ?', [(int)$c_id]);
            if ($result) {
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $_SESSION['success_message'] = "$deleted comment(s) deleted successfully!";
        } else {
            $_SESSION['error_message'] = "No comments were deleted.";
        }
        
        header('Location: comments.php');
        exit;
    }
}

// Handle different actions
switch ($action) {
    case 'edit':
        // Get comment data for editing
        if ($comment_id) {
            $sql = "SELECT c.*, s.title as story_title, s.id as story_id, u.username 
                    FROM comments c
                    JOIN stories s ON c.story_id = s.id
                    JOIN users u ON c.user_id = u.id
                    WHERE c.id = ?";
            $comment = fetch_one($sql, [$comment_id]);
            
            if (!$comment) {
                $_SESSION['error_message'] = "Comment not found.";
                header('Location: comments.php');
                exit;
            }
        } else {
            header('Location: comments.php');
            exit;
        }
        break;
        
    case 'delete':
        // Confirm delete form
        if ($comment_id) {
            $sql = "SELECT c.*, s.title as story_title, u.username 
                    FROM comments c
                    JOIN stories s ON c.story_id = s.id
                    JOIN users u ON c.user_id = u.id
                    WHERE c.id = ?";
            $comment = fetch_one($sql, [$comment_id]);
            
            if (!$comment) {
                $_SESSION['error_message'] = "Comment not found.";
                header('Location: comments.php');
                exit;
            }
        } else {
            header('Location: comments.php');
            exit;
        }
        break;
        
    default:
        // List all comments with pagination
        $where = [];
        $params = [];
        
        // Add search condition if search term is provided
        if (!empty($search)) {
            $where[] = "(c.content LIKE ? OR s.title LIKE ? OR u.username LIKE ?)";
            $search_term = "%{$search}%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        // Add story filter if provided
        if ($story_filter > 0) {
            $where[] = "c.story_id = ?";
            $params[] = $story_filter;
        }
        
        // Add user filter if provided
        if ($user_filter > 0) {
            $where[] = "c.user_id = ?";
            $params[] = $user_filter;
        }
        
        // Build where clause
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Count total comments for pagination
        $count_sql = "SELECT COUNT(*) as total 
                      FROM comments c
                      JOIN stories s ON c.story_id = s.id
                      JOIN users u ON c.user_id = u.id
                      $where_clause";
        $count_result = fetch_one($count_sql, $params);
        $total_comments = $count_result ? $count_result['total'] : 0;
        
        // Calculate pagination
        $total_pages = ceil($total_comments / $items_per_page);
        $offset = ($page - 1) * $items_per_page;
        
        // Get comments with pagination and sorting
        $sql = "SELECT c.id, c.content, c.created_at, s.id as story_id, s.title as story_title, 
                       u.id as user_id, u.username 
                FROM comments c
                JOIN stories s ON c.story_id = s.id
                JOIN users u ON c.user_id = u.id
                $where_clause
                ORDER BY " . ($sort_by === 'story_title' || $sort_by === 'username' ? $sort_by : "c." . $sort_by) . " $sort_dir 
                LIMIT ? OFFSET ?";
        
        $comments = fetch_all($sql, array_merge($params, [$items_per_page, $offset]));
        
        // Get stories for filter dropdown
        $stories = fetch_all("SELECT id, title FROM stories ORDER BY title ASC");
        
        // Get users for filter dropdown
        $users = fetch_all("SELECT id, username FROM users ORDER BY username ASC");
        break;
}
?>

<div class="admin-header-actions">
  <h1><?php echo $action === 'list' ? 'Comments Management' : ($action === 'edit' ? 'Edit Comment' : 'Delete Comment'); ?></h1>
  
  <?php if ($action !== 'list'): ?>
    <a href="comments.php" class="admin-button secondary">
      <i class="fas fa-arrow-left"></i> Back to Comments
    </a>
  <?php endif; ?>
</div>

<?php if (!empty($error)): ?>
  <div class="alert alert-error">
    <?php echo $error; ?>
  </div>
<?php endif; ?>

<?php if (!empty($message)): ?>
  <div class="alert alert-success">
    <?php echo $message; ?>
  </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
  <!-- Comments Listing -->
  <div class="admin-filters">
    <form action="comments.php" method="GET" class="search-form">
      <div class="filter-group">
        <input type="text" name="search" placeholder="Search comments..." value="<?php echo htmlspecialchars($search); ?>">
      </div>
      
      <div class="filter-group">
        <select name="story_id">
          <option value="0">All Stories</option>
          <?php foreach ($stories as $story): ?>
            <option value="<?php echo $story['id']; ?>" <?php echo $story_filter == $story['id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($story['title']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="filter-group">
        <select name="user_id">
          <option value="0">All Users</option>
          <?php foreach ($users as $user): ?>
            <option value="<?php echo $user['id']; ?>" <?php echo $user_filter == $user['id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($user['username']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="filter-group">
        <button type="submit" class="admin-button">Apply Filters</button>
        <?php if (!empty($search) || $story_filter > 0 || $user_filter > 0): ?>
          <a href="comments.php" class="admin-button secondary">Clear Filters</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
  
  <form action="comments.php?action=delete_selected" method="POST">
    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th class="checkbox-column">
              <input type="checkbox" id="select-all">
            </th>
            <?php foreach ($columns as $column => $label): ?>
              <th>
                <a href="comments.php?sort=<?php echo $column; ?>&dir=<?php echo ($sort_by === $column && $sort_dir === 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&story_id=<?php echo $story_filter; ?>&user_id=<?php echo $user_filter; ?>" class="sort-link <?php echo $sort_by === $column ? 'active' : ''; ?>">
                  <?php echo $label; ?>
                  <?php if ($sort_by === $column): ?>
                    <i class="fas fa-chevron-<?php echo $sort_dir === 'ASC' ? 'up' : 'down'; ?>"></i>
                  <?php endif; ?>
                </a>
              </th>
            <?php endforeach; ?>
            <th class="actions-column">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($comments)): ?>
            <tr>
              <td colspan="<?php echo count($columns) + 2; ?>" class="no-results">No comments found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($comments as $comment): ?>
              <tr>
                <td>
                  <input type="checkbox" name="selected_comments[]" value="<?php echo $comment['id']; ?>" class="comment-checkbox">
                </td>
                <td><?php echo $comment['id']; ?></td>
                <td>
                  <div class="comment-content">
                    <?php echo htmlspecialchars(substr($comment['content'], 0, 150)) . (strlen($comment['content']) > 150 ? '...' : ''); ?>
                  </div>
                </td>
                <td>
                  <a href="../story.php?id=<?php echo $comment['story_id']; ?>" target="_blank">
                    <?php echo htmlspecialchars($comment['story_title']); ?>
                  </a>
                </td>
                <td>
                  <a href="users.php?action=edit&id=<?php echo $comment['user_id']; ?>">
                    <?php echo htmlspecialchars($comment['username']); ?>
                  </a>
                </td>
                <td><?php echo format_date($comment['created_at']); ?></td>
                <td class="actions-cell">
                  <a href="comments.php?action=edit&id=<?php echo $comment['id']; ?>" class="admin-action-btn edit" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="comments.php?action=delete&id=<?php echo $comment['id']; ?>" class="admin-action-btn delete" title="Delete">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <div class="bulk-actions">
      <button type="submit" class="admin-button danger" id="delete-selected" disabled>
        <i class="fas fa-trash"></i> Delete Selected
      </button>
      <span id="selected-count">0 comments selected</span>
    </div>
  </form>
  
  <?php if ($total_pages > 1): ?>
    <div class="admin-pagination">
      <?php if ($page > 1): ?>
        <a href="comments.php?page=<?php echo ($page - 1); ?>&sort=<?php echo $sort_by; ?>&dir=<?php echo $sort_dir; ?>&search=<?php echo urlencode($search); ?>&story_id=<?php echo $story_filter; ?>&user_id=<?php echo $user_filter; ?>" class="admin-button pagination-btn">
          <i class="fas fa-chevron-left"></i> Previous
        </a>
      <?php endif; ?>
      
      <div class="pagination-info">
        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
      </div>
      
      <?php if ($page < $total_pages): ?>
        <a href="comments.php?page=<?php echo ($page + 1); ?>&sort=<?php echo $sort_by; ?>&dir=<?php echo $sort_dir; ?>&search=<?php echo urlencode($search); ?>&story_id=<?php echo $story_filter; ?>&user_id=<?php echo $user_filter; ?>" class="admin-button pagination-btn">
          Next <i class="fas fa-chevron-right"></i>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
  
<?php elseif ($action === 'edit'): ?>
  <!-- Comment Edit Form -->
  <div class="admin-form-container">
    <div class="comment-info">
      <p><strong>Story:</strong> <a href="../story.php?id=<?php echo $comment['story_id']; ?>" target="_blank"><?php echo htmlspecialchars($comment['story_title']); ?></a></p>
      <p><strong>User:</strong> <?php echo htmlspecialchars($comment['username']); ?></p>
      <p><strong>Date:</strong> <?php echo format_date($comment['created_at']); ?></p>
    </div>
    
    <form action="comments.php?action=edit&id=<?php echo $comment_id; ?>" method="POST" class="admin-form">
      <div class="form-group">
        <label for="content">Comment Content *</label>
        <textarea id="content" name="content" rows="6" required><?php echo htmlspecialchars($comment['content']); ?></textarea>
      </div>
      
      <div class="form-actions">
        <button type="submit" class="admin-button">Update Comment</button>
        <a href="comments.php" class="admin-button secondary">Cancel</a>
      </div>
    </form>
  </div>
  
<?php elseif ($action === 'delete'): ?>
  <!-- Delete Confirmation -->
  <div class="admin-confirm-delete">
    <h2>Are you sure you want to delete this comment?</h2>
    <div class="comment-info">
      <p><strong>Story:</strong> <?php echo htmlspecialchars($comment['story_title']); ?></p>
      <p><strong>User:</strong> <?php echo htmlspecialchars($comment['username']); ?></p>
      <p><strong>Date:</strong> <?php echo format_date($comment['created_at']); ?></p>
      <p><strong>Content:</strong> <?php echo htmlspecialchars($comment['content']); ?></p>
    </div>
    <p class="warning">This action cannot be undone.</p>
    
    <form action="comments.php?action=delete&id=<?php echo $comment_id; ?>" method="POST">
      <div class="form-actions">
        <button type="submit" class="admin-button delete">Yes, Delete Comment</button>
        <a href="comments.php" class="admin-button secondary">Cancel</a>
      </div>
    </form>
  </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Handle select all checkbox
  const selectAllCheckbox = document.getElementById('select-all');
  const commentCheckboxes = document.querySelectorAll('.comment-checkbox');
  const deleteSelectedButton = document.getElementById('delete-selected');
  const selectedCountSpan = document.getElementById('selected-count');
  
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      const isChecked = this.checked;
      
      commentCheckboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
      });
      
      updateSelectedCount();
    });
  }
  
  // Handle individual checkboxes
  commentCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
  });
  
  function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.comment-checkbox:checked').length;
    
    if (selectedCountSpan) {
      selectedCountSpan.textContent = selectedCount + ' comment' + (selectedCount === 1 ? '' : 's') + ' selected';
    }
    
    if (deleteSelectedButton) {
      deleteSelectedButton.disabled = selectedCount === 0;
    }
  }
});
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>