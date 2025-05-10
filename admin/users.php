<?php
/**
 * Admin Users Management for SariSari Stories
 */

// Page title
$page_title = "Users Management";

// Include header
require_once 'includes/header.php';

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';
$user = [];
$users = [];

// Set table columns and sort options
$columns = [
    'id' => 'ID',
    'username' => 'Username',
    'email' => 'Email',
    'full_name' => 'Full Name',
    'created_at' => 'Date Joined',
    'is_admin' => 'Admin'
];

// Get sorting parameters
$sort_by = isset($_GET['sort']) && array_key_exists($_GET['sort'], $columns) ? $_GET['sort'] : 'id';
$sort_dir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'DESC';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$items_per_page = 10;

// Handle Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'edit' || $action === 'new') {
        // Get form data
        $user_data = [
            'username' => sanitize($_POST['username'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'full_name' => sanitize($_POST['full_name'] ?? ''),
            'bio' => sanitize($_POST['bio'] ?? ''),
            'is_admin' => isset($_POST['is_admin']) ? 1 : 0
        ];
        
        // Add password if set
        if (!empty($_POST['password'])) {
            $user_data['password'] = $_POST['password'];
        }
        
        if ($action === 'new') {
            // Create new user
            $result = register_user($user_data['username'], $user_data['email'], $_POST['password'], $user_data['full_name']);
            
            if (is_numeric($result)) {
                // If registration successful, set admin status if needed
                if ($user_data['is_admin']) {
                    update('users', ['is_admin' => 1], 'id = ?', [$result]);
                }
                
                $_SESSION['success_message'] = "User created successfully!";
                header('Location: users.php');
                exit;
            } else {
                $error = $result;
            }
        } else {
            // Update existing user
            $result = update_profile($user_id, $user_data);
            
            if ($result === true) {
                $_SESSION['success_message'] = "User updated successfully!";
                header('Location: users.php');
                exit;
            } else {
                $error = $result;
            }
        }
    } elseif ($action === 'delete' && $user_id) {
        // Delete user (only if not current user)
        if ($user_id != $_SESSION['user']['id']) {
            $result = delete('users', 'id = ?', [$user_id]);
            
            if ($result) {
                $_SESSION['success_message'] = "User deleted successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to delete user.";
            }
        } else {
            $_SESSION['error_message'] = "You cannot delete your own account.";
        }
        
        header('Location: users.php');
        exit;
    }
}

// Handle different actions
switch ($action) {
    case 'new':
        // New user form
        $user = [
            'id' => 0,
            'username' => '',
            'email' => '',
            'full_name' => '',
            'bio' => '',
            'is_admin' => 0
        ];
        break;
        
    case 'edit':
        // Get user data for editing
        if ($user_id) {
            $sql = "SELECT id, username, email, full_name, bio, is_admin, created_at FROM users WHERE id = ?";
            $user = fetch_one($sql, [$user_id]);
            
            if (!$user) {
                $_SESSION['error_message'] = "User not found.";
                header('Location: users.php');
                exit;
            }
        } else {
            header('Location: users.php');
            exit;
        }
        break;
        
    case 'delete':
        // Confirm delete form
        if ($user_id) {
            $sql = "SELECT id, username, email, full_name FROM users WHERE id = ?";
            $user = fetch_one($sql, [$user_id]);
            
            if (!$user) {
                $_SESSION['error_message'] = "User not found.";
                header('Location: users.php');
                exit;
            }
        } else {
            header('Location: users.php');
            exit;
        }
        break;
        
    default:
        // List all users with pagination
        $where = "";
        $params = [];
        
        // Add search condition if search term is provided
        if (!empty($search)) {
            $where = "WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ?";
            $search_term = "%{$search}%";
            $params = [$search_term, $search_term, $search_term];
        }
        
        // Count total users for pagination
        $count_sql = "SELECT COUNT(*) as total FROM users $where";
        $count_result = fetch_one($count_sql, $params);
        $total_users = $count_result ? $count_result['total'] : 0;
        
        // Calculate pagination
        $total_pages = ceil($total_users / $items_per_page);
        $offset = ($page - 1) * $items_per_page;
        
        // Get users with pagination and sorting
        $sql = "SELECT id, username, email, full_name, is_admin, created_at 
                FROM users 
                $where
                ORDER BY $sort_by $sort_dir 
                LIMIT ? OFFSET ?";
        
        $users = fetch_all($sql, array_merge($params, [$items_per_page, $offset]));
        break;
}
?>

<div class="admin-header-actions">
  <h1><?php echo $action === 'list' ? 'Users Management' : ($action === 'new' ? 'Add New User' : ($action === 'edit' ? 'Edit User' : 'Delete User')); ?></h1>
  
  <?php if ($action === 'list'): ?>
    <a href="users.php?action=new" class="admin-button">
      <i class="fas fa-plus"></i> Add New User
    </a>
  <?php else: ?>
    <a href="users.php" class="admin-button secondary">
      <i class="fas fa-arrow-left"></i> Back to Users
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
  <!-- Users Listing -->
  <div class="admin-filters">
    <form action="users.php" method="GET" class="search-form">
      <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
      <button type="submit" class="admin-button">Search</button>
      <?php if (!empty($search)): ?>
        <a href="users.php" class="admin-button secondary">Clear</a>
      <?php endif; ?>
    </form>
  </div>
  
  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <?php foreach ($columns as $column => $label): ?>
            <th>
              <a href="users.php?sort=<?php echo $column; ?>&dir=<?php echo ($sort_by === $column && $sort_dir === 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>" class="sort-link <?php echo $sort_by === $column ? 'active' : ''; ?>">
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
        <?php if (empty($users)): ?>
          <tr>
            <td colspan="<?php echo count($columns) + 1; ?>" class="no-results">No users found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($users as $user): ?>
            <tr>
              <td><?php echo $user['id']; ?></td>
              <td><?php echo htmlspecialchars($user['username']); ?></td>
              <td><?php echo htmlspecialchars($user['email']); ?></td>
              <td><?php echo htmlspecialchars($user['full_name']); ?></td>
              <td><?php echo format_date($user['created_at']); ?></td>
              <td><?php echo $user['is_admin'] ? '<span class="badge badge-success">Yes</span>' : '<span class="badge">No</span>'; ?></td>
              <td class="actions-cell">
                <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="admin-action-btn edit" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="admin-action-btn delete" title="Delete">
                  <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
  <?php if ($total_pages > 1): ?>
    <div class="admin-pagination">
      <?php if ($page > 1): ?>
        <a href="users.php?page=<?php echo ($page - 1); ?>&sort=<?php echo $sort_by; ?>&dir=<?php echo $sort_dir; ?>&search=<?php echo urlencode($search); ?>" class="admin-button pagination-btn">
          <i class="fas fa-chevron-left"></i> Previous
        </a>
      <?php endif; ?>
      
      <div class="pagination-info">
        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
      </div>
      
      <?php if ($page < $total_pages): ?>
        <a href="users.php?page=<?php echo ($page + 1); ?>&sort=<?php echo $sort_by; ?>&dir=<?php echo $sort_dir; ?>&search=<?php echo urlencode($search); ?>" class="admin-button pagination-btn">
          Next <i class="fas fa-chevron-right"></i>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
  
<?php elseif ($action === 'new' || $action === 'edit'): ?>
  <!-- User Form -->
  <div class="admin-form-container">
    <form action="users.php?action=<?php echo $action; ?><?php echo $user_id ? '&id=' . $user_id : ''; ?>" method="POST" class="admin-form">
      <div class="form-group">
        <label for="username">Username *</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
      </div>
      
      <div class="form-group">
        <label for="email">Email Address *</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
      </div>
      
      <div class="form-group">
        <label for="full_name">Full Name *</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
      </div>
      
      <div class="form-group">
        <label for="password"><?php echo $action === 'new' ? 'Password *' : 'Password (leave blank to keep current)'; ?></label>
        <input type="password" id="password" name="password" <?php echo $action === 'new' ? 'required' : ''; ?>>
        <?php if ($action === 'new'): ?>
          <p class="field-hint">Password must be at least 8 characters long.</p>
        <?php endif; ?>
      </div>
      
      <div class="form-group">
        <label for="bio">Bio</label>
        <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
      </div>
      
      <div class="form-group checkbox-group">
        <label class="checkbox-label">
          <input type="checkbox" name="is_admin" value="1" <?php echo isset($user['is_admin']) && $user['is_admin'] ? 'checked' : ''; ?>>
          <span>Administrator</span>
        </label>
        <p class="field-hint">Administrators have full access to manage all content on the site.</p>
      </div>
      
      <div class="form-actions">
        <button type="submit" class="admin-button">
          <?php echo $action === 'new' ? 'Create User' : 'Update User'; ?>
        </button>
        <a href="users.php" class="admin-button secondary">Cancel</a>
      </div>
    </form>
  </div>
  
<?php elseif ($action === 'delete'): ?>
  <!-- Delete Confirmation -->
  <div class="admin-confirm-delete">
    <h2>Are you sure you want to delete this user?</h2>
    <p>You are about to delete the user: <strong><?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</strong></p>
    <p class="warning">This action cannot be undone. All content created by this user will be deleted as well.</p>
    
    <form action="users.php?action=delete&id=<?php echo $user_id; ?>" method="POST">
      <div class="form-actions">
        <button type="submit" class="admin-button delete">Yes, Delete User</button>
        <a href="users.php" class="admin-button secondary">Cancel</a>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php
// Include footer
require_once 'includes/footer.php';
?>