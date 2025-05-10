<?php
/**
 * Admin Subscribers Management for SariSari Stories
 */

// Page title
$page_title = "Newsletter Subscribers";

// Include header
require_once 'includes/header.php';

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$subscriber_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';
$subscriber = [];
$subscribers = [];

// Set table columns and sort options
$columns = [
    'id' => 'ID',
    'email' => 'Email',
    'status' => 'Status',
    'created_at' => 'Date Subscribed'
];

// Get sorting parameters
$sort_by = isset($_GET['sort']) && array_key_exists($_GET['sort'], $columns) ? $_GET['sort'] : 'created_at';
$sort_dir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'DESC';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$items_per_page = 15;

// Handle Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'new') {
        // Get form data
        $email = sanitize($_POST['email'] ?? '');
        $status = sanitize($_POST['status'] ?? 'active');
        
        // Validate data
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            // Check if email already exists
            $existing = fetch_one("SELECT id FROM subscribers WHERE email = ?", [$email]);
            
            if ($existing) {
                // Update status if subscriber exists
                $result = update('subscribers', ['status' => $status], 'id = ?', [$existing['id']]);
                $_SESSION['success_message'] = "Email already exists. Status has been updated.";
                header('Location: subscribers.php');
                exit;
            } else {
                // Insert new subscriber
                $result = insert('subscribers', [
                    'email' => $email,
                    'status' => $status
                ]);
                
                if ($result) {
                    $_SESSION['success_message'] = "Subscriber added successfully!";
                    header('Location: subscribers.php');
                    exit;
                } else {
                    $error = "Failed to add subscriber. Please try again.";
                }
            }
        }
    } elseif ($action === 'edit' && $subscriber_id) {
        // Get form data
        $email = sanitize($_POST['email'] ?? '');
        $status = sanitize($_POST['status'] ?? 'active');
        
        // Validate data
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            // Check if email already exists for another subscriber
            $existing = fetch_one("SELECT id FROM subscribers WHERE email = ? AND id != ?", [$email, $subscriber_id]);
            
            if ($existing) {
                $error = "This email is already subscribed.";
            } else {
                // Update subscriber
                $result = update('subscribers', [
                    'email' => $email,
                    'status' => $status
                ], 'id = ?', [$subscriber_id]);
                
                if ($result) {
                    $_SESSION['success_message'] = "Subscriber updated successfully!";
                    header('Location: subscribers.php');
                    exit;
                } else {
                    $error = "Failed to update subscriber. Please try again.";
                }
            }
        }
    } elseif ($action === 'delete' && $subscriber_id) {
        // Delete subscriber
        $result = delete('subscribers', 'id = ?', [$subscriber_id]);
        
        if ($result) {
            $_SESSION['success_message'] = "Subscriber deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete subscriber.";
        }
        
        header('Location: subscribers.php');
        exit;
    } elseif ($action === 'export') {
        // Export subscribers to CSV
        $where = "";
        $params = [];
        
        if (!empty($status_filter)) {
            $where = "WHERE status = ?";
            $params = [$status_filter];
        }
        
        $sql = "SELECT email, status, created_at FROM subscribers $where ORDER BY email ASC";
        $all_subscribers = fetch_all($sql, $params);
        
        if (!empty($all_subscribers)) {
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="subscribers_' . date('Y-m-d') . '.csv"');
            
            // Open output stream
            $output = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($output, ['Email', 'Status', 'Date Subscribed']);
            
            // Add data rows
            foreach ($all_subscribers as $subscriber) {
                fputcsv($output, $subscriber);
            }
            
            // Close the stream
            fclose($output);
            exit;
        } else {
            $_SESSION['error_message'] = "No subscribers found to export.";
            header('Location: subscribers.php');
            exit;
        }
    } elseif ($action === 'delete_selected' && isset($_POST['selected_subscribers'])) {
        // Delete multiple subscribers
        $selected = $_POST['selected_subscribers'] ?? [];
        $deleted = 0;
        
        foreach ($selected as $s_id) {
            $result = delete('subscribers', 'id = ?', [(int)$s_id]);
            if ($result) {
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            $_SESSION['success_message'] = "$deleted subscriber(s) deleted successfully!";
        } else {
            $_SESSION['error_message'] = "No subscribers were deleted.";
        }
        
        header('Location: subscribers.php');
        exit;
    }
}

// Handle different actions
switch ($action) {
    case 'new':
        // New subscriber form
        $subscriber = [
            'id' => 0,
            'email' => '',
            'status' => 'active'
        ];
        break;
        
    case 'edit':
        // Get subscriber data for editing
        if ($subscriber_id) {
            $sql = "SELECT id, email, status, created_at FROM subscribers WHERE id = ?";
            $subscriber = fetch_one($sql, [$subscriber_id]);
            
            if (!$subscriber) {
                $_SESSION['error_message'] = "Subscriber not found.";
                header('Location: subscribers.php');
                exit;
            }
        } else {
            header('Location: subscribers.php');
            exit;
        }
        break;
        
    case 'delete':
        // Confirm delete form
        if ($subscriber_id) {
            $sql = "SELECT id, email, status, created_at FROM subscribers WHERE id = ?";
            $subscriber = fetch_one($sql, [$subscriber_id]);
            
            if (!$subscriber) {
                $_SESSION['error_message'] = "Subscriber not found.";
                header('Location: subscribers.php');
                exit;
            }
        } else {
            header('Location: subscribers.php');
            exit;
        }
        break;
        
    default:
        // List all subscribers with pagination
        $where = [];
        $params = [];
        
        // Add search condition if search term is provided
        if (!empty($search)) {
            $where[] = "email LIKE ?";
            $search_term = "%{$search}%";
            $params[] = $search_term;
        }
        
        // Add status filter if provided
        if (!empty($status_filter)) {
            $where[] = "status = ?";
            $params[] = $status_filter;
        }
        
        // Build where clause
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Count total subscribers for pagination
        $count_sql = "SELECT COUNT(*) as total FROM subscribers $where_clause";
        $count_result = fetch_one($count_sql, $params);
        $total_subscribers = $count_result ? $count_result['total'] : 0;
        
        // Calculate pagination
        $total_pages = ceil($total_subscribers / $items_per_page);
        $offset = ($page - 1) * $items_per_page;
        
        // Get subscribers with pagination and sorting
        $sql = "SELECT id, email, status, created_at 
                FROM subscribers 
                $where_clause
                ORDER BY $sort_by $sort_dir 
                LIMIT ? OFFSET ?";
        
        $subscribers = fetch_all($sql, array_merge($params, [$items_per_page, $offset]));
        
        // Get subscriber statistics
        $stats = [
            'total' => fetch_one("SELECT COUNT(*) as count FROM subscribers")['count'] ?? 0,
            'active' => fetch_one("SELECT COUNT(*) as count FROM subscribers WHERE status = 'active'")['count'] ?? 0,
            'unsubscribed' => fetch_one("SELECT COUNT(*) as count FROM subscribers WHERE status = 'unsubscribed'")['count'] ?? 0
        ];
        break;
}
?>

<div class="admin-header-actions">
  <h1><?php echo $action === 'list' ? 'Newsletter Subscribers' : ($action === 'new' ? 'Add New Subscriber' : ($action === 'edit' ? 'Edit Subscriber' : 'Delete Subscriber')); ?></h1>
  
  <?php if ($action === 'list'): ?>
    <div class="action-buttons">
      <a href="subscribers.php?action=new" class="admin-button">
        <i class="fas fa-plus"></i> Add Subscriber
      </a>
      <a href="subscribers.php?action=export<?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?>" class="admin-button secondary">
        <i class="fas fa-download"></i> Export CSV
      </a>
    </div>
  <?php else: ?>
    <a href="subscribers.php" class="admin-button secondary">
      <i class="fas fa-arrow-left"></i> Back to Subscribers
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
  <!-- Subscriber Stats -->
  <div class="stats-cards">
    <div class="stat-card">
      <div class="stat-icon">
        <i class="fas fa-users"></i>
      </div>
      <div class="stat-details">
        <h3>Total Subscribers</h3>
        <p class="stat-number"><?php echo number_format($stats['total']); ?></p>
      </div>
    </div>
    
    <div class="stat-card">
      <div class="stat-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="stat-details">
        <h3>Active</h3>
        <p class="stat-number"><?php echo number_format($stats['active']); ?></p>
      </div>
      <a href="subscribers.php?status=active" class="stat-link">View</a>
    </div>
    
    <div class="stat-card">
      <div class="stat-icon">
        <i class="fas fa-times-circle"></i>
      </div>
      <div class="stat-details">
        <h3>Unsubscribed</h3>
        <p class="stat-number"><?php echo number_format($stats['unsubscribed']); ?></p>
      </div>
      <a href="subscribers.php?status=unsubscribed" class="stat-link">View</a>
    </div>
  </div>

  <!-- Subscribers Listing -->
  <div class="admin-filters">
    <form action="subscribers.php" method="GET" class="search-form">
      <div class="filter-group">
        <input type="text" name="search" placeholder="Search email..." value="<?php echo htmlspecialchars($search); ?>">
      </div>
      
      <div class="filter-group">
        <select name="status">
          <option value="">All Statuses</option>
          <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
          <option value="unsubscribed" <?php echo $status_filter === 'unsubscribed' ? 'selected' : ''; ?>>Unsubscribed</option>
        </select>
      </div>
      
      <div class="filter-group">
        <button type="submit" class="admin-button">Apply Filters</button>
        <?php if (!empty($search) || !empty($status_filter)): ?>
          <a href="subscribers.php" class="admin-button secondary">Clear Filters</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
  
  <form action="subscribers.php?action=delete_selected" method="POST">
    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th class="checkbox-column">
              <input type="checkbox" id="select-all">
            </th>
            <?php foreach ($columns as $column => $label): ?>
              <th>
                <a href="subscribers.php?sort=<?php echo $column; ?>&dir=<?php echo ($sort_by === $column && $sort_dir === 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" class="sort-link <?php echo $sort_by === $column ? 'active' : ''; ?>">
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
          <?php if (empty($subscribers)): ?>
            <tr>
              <td colspan="<?php echo count($columns) + 2; ?>" class="no-results">No subscribers found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($subscribers as $subscriber): ?>
              <tr>
                <td>
                  <input type="checkbox" name="selected_subscribers[]" value="<?php echo $subscriber['id']; ?>" class="subscriber-checkbox">
                </td>
                <td><?php echo $subscriber['id']; ?></td>
                <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                <td>
                  <span class="badge <?php echo $subscriber['status'] === 'active' ? 'badge-success' : ''; ?>">
                    <?php echo ucfirst($subscriber['status']); ?>
                  </span>
                </td>
                <td><?php echo format_date($subscriber['created_at']); ?></td>
                <td class="actions-cell">
                  <a href="subscribers.php?action=edit&id=<?php echo $subscriber['id']; ?>" class="admin-action-btn edit" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="subscribers.php?action=delete&id=<?php echo $subscriber['id']; ?>" class="admin-action-btn delete" title="Delete">
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
      <span id="selected-count">0 subscribers selected</span>
    </div>
  </form>
  
  <?php if ($total_pages > 1): ?>
    <div class="admin-pagination">
      <?php if ($page > 1): ?>
        <a href="subscribers.php?page=<?php echo ($page - 1); ?>&sort=<?php echo $sort_by; ?>&dir=<?php echo $sort_dir; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" class="admin-button pagination-btn">
          <i class="fas fa-chevron-left"></i> Previous
        </a>
      <?php endif; ?>
      
      <div class="pagination-info">
        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
      </div>
      
      <?php if ($page < $total_pages): ?>
        <a href="subscribers.php?page=<?php echo ($page + 1); ?>&sort=<?php echo $sort_by; ?>&dir=<?php echo $sort_dir; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status_filter; ?>" class="admin-button pagination-btn">
          Next <i class="fas fa-chevron-right"></i>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
  
<?php elseif ($action === 'new' || $action === 'edit'): ?>
  <!-- Subscriber Form -->
  <div class="admin-form-container">
    <form action="subscribers.php?action=<?php echo $action; ?><?php echo $subscriber_id ? '&id=' . $subscriber_id : ''; ?>" method="POST" class="admin-form">
      <div class="form-group">
        <label for="email">Email Address *</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($subscriber['email']); ?>" required>
      </div>
      
      <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="active" <?php echo isset($subscriber['status']) && $subscriber['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
          <option value="unsubscribed" <?php echo isset($subscriber['status']) && $subscriber['status'] === 'unsubscribed' ? 'selected' : ''; ?>>Unsubscribed</option>
        </select>
      </div>
      
      <?php if ($action === 'edit'): ?>
        <div class="form-group">
          <label>Date Subscribed</label>
          <div class="field-text"><?php echo format_date($subscriber['created_at']); ?></div>
        </div>
      <?php endif; ?>
      
      <div class="form-actions">
        <button type="submit" class="admin-button">
          <?php echo $action === 'new' ? 'Add Subscriber' : 'Update Subscriber'; ?>
        </button>
        <a href="subscribers.php" class="admin-button secondary">Cancel</a>
      </div>
    </form>
  </div>
  
<?php elseif ($action === 'delete'): ?>
  <!-- Delete Confirmation -->
  <div class="admin-confirm-delete">
    <h2>Are you sure you want to delete this subscriber?</h2>
    <p>You are about to delete: <strong><?php echo htmlspecialchars($subscriber['email']); ?></strong></p>
    <p class="warning">This action cannot be undone.</p>
    
    <form action="subscribers.php?action=delete&id=<?php echo $subscriber_id; ?>" method="POST">
      <div class="form-actions">
        <button type="submit" class="admin-button delete">Yes, Delete Subscriber</button>
        <a href="subscribers.php" class="admin-button secondary">Cancel</a>
      </div>
    </form>
  </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Handle select all checkbox
  const selectAllCheckbox = document.getElementById('select-all');
  const subscriberCheckboxes = document.querySelectorAll('.subscriber-checkbox');
  const deleteSelectedButton = document.getElementById('delete-selected');
  const selectedCountSpan = document.getElementById('selected-count');
  
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      const isChecked = this.checked;
      
      subscriberCheckboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
      });
      
      updateSelectedCount();
    });
  }
  
  // Handle individual checkboxes
  subscriberCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
  });
  
  function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.subscriber-checkbox:checked').length;
    
    if (selectedCountSpan) {
      selectedCountSpan.textContent = selectedCount + ' subscriber' + (selectedCount === 1 ? '' : 's') + ' selected';
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