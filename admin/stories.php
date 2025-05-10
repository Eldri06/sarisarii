<?php
/**
 * Admin Stories Management for SariSari Stories
 */

// Page title
$page_title = "Stories Management";

// Include header
require_once 'includes/header.php';

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$story_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';
$story = [];
$stories = [];
$categories = get_categories();

// Set table columns and sort options
$columns = [
    'id' => 'ID',
    'title' => 'Title',
    'category_name' => 'Category',
    'username' => 'Author',
    'created_at' => 'Date',
    'views' => 'Views',
    'featured' => 'Featured'
];

// Get sorting parameters
$sort_by = isset($_GET['sort']) && array_key_exists($_GET['sort'], $columns) ? $_GET['sort'] : 'id';
$sort_dir = isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC']) ? strtoupper($_GET['dir']) : 'DESC';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$items_per_page = 10;

// Handle Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'edit' || $action === 'new') {
        // Get form data
        $title = sanitize($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $category_id = (int)($_POST['category_id'] ?? 0);
        $featured = isset($_POST['featured']) ? 1 : 0;
        $status = sanitize($_POST['status'] ?? 'published');
        
        // Validate data
        if (empty($title) || empty($content) || $category_id <= 0) {
            $error = "Please fill in all required fields.";
        } else {
            // Create story data array
            $story_data = [
                'title' => $title,
                'content' => $content,
                'category_id' => $category_id,
                'featured' => $featured,
                'status' => $status
            ];
            
            // Create slug from title
            if ($action === 'new') {
                $story_data['slug'] = create_slug($title, 'stories');
                $story_data['user_id'] = $_SESSION['user']['id']; // Author is the current admin
            } else {
                $story_data['slug'] = create_slug($title, 'stories', $story_id);
            }
            
            // Handle image upload if provided
            if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                $image_path = upload_image($_FILES['featured_image'], 'uploads/stories');
                
                if ($image_path) {
                    $story_data['featured_image'] = $image_path;
                }
            }
            
            if ($action === 'new') {
                // Insert new story
                $story_id = insert('stories', $story_data);
                
                if ($story_id) {
                    $_SESSION['success_message'] = "Story created successfully!";
                    header('Location: stories.php');
                    exit;
                } else {
                    $error = "Failed to create story. Please try again.";
                }
            } else {
                // Update existing story
                $result = update('stories', $story_data, 'id = ?', [$story_id]);
                
                if ($result) {
                    $_SESSION['success_message'] = "Story updated successfully!";
                    header('Location: stories.php');
                    exit;
                } else {
                    $error = "Failed to update story. Please try again.";
                }
            }
        }
    } elseif ($action === 'delete' && $story_id) {
        // Delete story
        $result = delete('stories', 'id = ?', [$story_id]);
        
        if ($result) {
            $_SESSION['success_message'] = "Story deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete story.";
        }
        
        header('Location: stories.php');
        exit;
    } elseif ($action === 'toggle_featured' && $story_id) {
        // Toggle featured status
        $story = fetch_one("SELECT featured FROM stories WHERE id = ?", [$story_id]);
        
        if ($story) {
            $new_status = $story['featured'] ? 0 : 1;
            update('stories', ['featured' => $new_status], 'id = ?', [$story_id]);
            
            $_SESSION['success_message'] = $new_status ? "Story marked as featured." : "Story removed from featured list.";
        } else {
            $_SESSION['error_message'] = "Story not found.";
        }
        
        header('Location: stories.php');
        exit;
    }
}

// Handle different actions
switch ($action) {
    case 'new':
        // New story form
        $story = [
            'id' => 0,
            'title' => '',
            'content' => '',
            'category_id' => 0,
            'featured' => 0,
            'status' => 'published'
        ];
        break;
        
    case 'edit':
        // Get story data for editing
        if ($story_id) {
            $sql = "SELECT s.*, c.name as category_name 
                    FROM stories s
                    JOIN categories c ON s.category_id = c.id
                    WHERE s.id = ?";
            $story = fetch_one($sql, [$story_id]);
            
            if (!$story) {
                $_SESSION['error_message'] = "Story not found.";
                header('Location: stories.php');
                exit;
            }
        } else {
            header('Location: stories.php');
            exit;
        }
        break;
        
    case 'delete':
        // Confirm delete form
        if ($story_id) {
            $sql = "SELECT s.id, s.title, s.created_at, u.username 
                    FROM stories s
                    JOIN users u ON s.user_id = u.id
                    WHERE s.id = ?";
            $story = fetch_one($sql, [$story_id]);
            
            if (!$story) {
                $_SESSION['error_message'] = "Story not found.";
                header('Location: stories.php');
                exit;
            }
        } else {
            header('Location: stories.php');
            exit;
        }
        break;
        
    default:
        // List all stories with pagination
        $where = [];
        $params = [];
        
        // Add search condition if search term is provided
        if (!empty($search)) {
            $where[] = "(s.title LIKE ? OR s.content LIKE ?)";
            $search_term = "%{$search}%";
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        // Add category filter if provided
        if ($category_filter > 0) {
            $where[] = "s.category_id = ?";
            $params[] = $category_filter;
        }
        
        // Build where clause
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Count total stories for pagination
        $count_sql = "SELECT COUNT(*) as total 
                      FROM stories s
                      $where_clause";
        $count_result = fetch_one($count_sql, $params);
        $total_stories = $count_result ? $count_result['total'] : 0;
        
        // Calculate pagination
        $total_pages = ceil($total_stories / $items_per_page);
        $offset = ($page - 1) * $items_per_page;
        
        // Get stories with pagination and sorting
        $sql = "SELECT s.id, s.title, s.slug, s.views, s.featured, s.status, s.created_at,
                       c.name as category_name, u.username 
                FROM stories s
                JOIN categories c ON s.category_id = c.id
                JOIN users u ON s.user_id = u.id
                $where_clause
                ORDER BY " . ($sort_by === 'category_name' || $sort_by === 'username' ? $sort_by : "s." . $sort_by) . " $sort_dir 
                LIMIT ? OFFSET ?";
        
        $stories = fetch_all($sql, array_merge($params, [$items_per_page, $offset]));
        break;
}
?>

<div class="admin-header-actions">
  <h1><?php echo $action === 'list' ? 'Stories Management' : ($action === 'new' ? 'Add New Story' : ($action === 'edit' ? 'Edit Story' : 'Delete Story')); ?></h1>
  
  <?php if ($action === 'list'): ?>
    <a href="stories.php?action=new" class="admin-button">
      <i class="fas fa-plus"></i> Add New Story
    </a>
  <?php else: ?>
    <a href="stories.php" class="admin-button secondary">
      <i class="fas fa-arrow-left"></i> Back to Stories
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
  <!-- Stories Listing -->
  <div class="admin-filters">
    <form action="stories.php" method="GET" class="search-form">
      <div class="filter-group">
        <input type="text" name="search" placeholder="Search stories..." value="<?php echo htmlspecialchars($search); ?>">
      </div>
      
      <div class="filter-group">
        <select name="category">
          <option value="0">All Categories</option>
          <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($category['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="filter-group">
        <button type="submit" class="admin-button">Apply Filters</button>
        <?php if (!empty($search) || $category_filter > 0): ?>
          <a href="stories.php" class="admin-button secondary">Clear Filters</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
  
  <div class="admin-table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <?php foreach ($columns as $column => $label): ?>
            <th>
              <a href="stories.php?sort=<?php echo $column; ?>&dir=<?php echo ($sort_by === $column && $sort_dir === 'ASC') ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>" class="sort-link <?php echo $sort_by === $column ? 'active' : ''; ?>">
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
        <?php if (empty($stories)): ?>
          <tr>
            <td colspan="<?php echo count($columns) + 1; ?>" class="no-results">No stories found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($stories as $story): ?>
            <tr>
              <td><?php echo $story['id']; ?></td>
              <td>
                <a href="../story.php?slug=<?php echo htmlspecialchars($story['slug']); ?>" target="_blank" class="title-link">
                  <?php echo htmlspecialchars($story['title']); ?>
                </a>
                <?php if ($story['status'] !== 'published'): ?>
                  <span class="badge"><?php echo ucfirst($story['status']); ?></span>
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($story['category_name']); ?></td>
              <td><?php echo htmlspecialchars($story['username']); ?></td>
              <td><?php echo format_date($story['created_at']); ?></td>
              <td><?php echo $story['views']; ?></td>
              <td><?php echo $story['featured'] ? '<span class="badge badge-success">Yes</span>' : 'No'; ?></td>
              <td class="actions-cell">
                <a href="stories.php?action=edit&id=<?php echo $story['id']; ?>" class="admin-action-btn edit" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <a href="stories.php?action=toggle_featured&id=<?php echo $story['id']; ?>" class="admin-action-btn <?php echo $story['featured'] ? 'unfeatured' : 'featured'; ?>" title="<?php echo $story['featured'] ? 'Remove from Featured' : 'Add to Featured'; ?>">
                  <i class="fas fa-<?php echo $story['featured'] ? 'star' : 'star'; ?>"></i>
                </a>
                <a href="stories.php?action=delete&id=<?php echo $story['id']; ?>" class="admin-action-btn delete" title="Delete">
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
        <a href="stories.php?page=<?php echo ($page - 1); ?>&sort=<?php echo $sort_by; ?>&dir=<?php echo $sort_dir; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>" class="admin-button pagination-btn">
          <i class="fas fa-chevron-left"></i> Previous
        </a>
      <?php endif; ?>
      
      <div class="pagination-info">
        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
      </div>
      
      <?php if ($page < $total_pages): ?>
        <a href="stories.php?page=<?php echo ($page + 1); ?>&sort=<?php echo $sort_by; ?>&dir=<?php echo $sort_dir; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>" class="admin-button pagination-btn">
          Next <i class="fas fa-chevron-right"></i>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
  
<?php elseif ($action === 'new' || $action === 'edit'): ?>
  <!-- Story Form -->
  <div class="admin-form-container">
    <form action="stories.php?action=<?php echo $action; ?><?php echo $story_id ? '&id=' . $story_id : ''; ?>" method="POST" class="admin-form" enctype="multipart/form-data">
      <div class="form-group">
        <label for="title">Title *</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($story['title'] ?? ''); ?>" required>
      </div>
      
      <div class="form-group">
        <label for="category_id">Category *</label>
        <select id="category_id" name="category_id" required>
          <option value="">Select a Category</option>
          <?php foreach ($categories as $category): ?>
            <option value="<?php echo $category['id']; ?>" <?php echo isset($story['category_id']) && $story['category_id'] == $category['id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($category['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="form-group">
        <label for="content">Content *</label>
        <textarea id="content" name="content" rows="15" required><?php echo htmlspecialchars($story['content'] ?? ''); ?></textarea>
        <p class="field-hint">You can use HTML tags for formatting.</p>
      </div>
      
      <div class="form-group">
        <label for="featured_image">Featured Image</label>
        <?php if (isset($story['featured_image']) && !empty($story['featured_image'])): ?>
          <div class="current-image">
            <img src="<?php echo htmlspecialchars('../' . $story['featured_image']); ?>" alt="Current featured image">
            <p>Current image</p>
          </div>
        <?php endif; ?>
        <input type="file" id="featured_image" name="featured_image" accept="image/*">
        <p class="field-hint">Maximum file size: 5MB. Supported formats: JPEG, PNG, GIF.</p>
      </div>
      
      <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="published" <?php echo isset($story['status']) && $story['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
          <option value="draft" <?php echo isset($story['status']) && $story['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
        </select>
      </div>
      
      <div class="form-group checkbox-group">
        <label class="checkbox-label">
          <input type="checkbox" name="featured" value="1" <?php echo isset($story['featured']) && $story['featured'] ? 'checked' : ''; ?>>
          <span>Feature this story</span>
        </label>
        <p class="field-hint">Featured stories appear in the featured section on the homepage.</p>
      </div>
      
      <div class="form-actions">
        <button type="submit" class="admin-button">
          <?php echo $action === 'new' ? 'Create Story' : 'Update Story'; ?>
        </button>
        <a href="stories.php" class="admin-button secondary">Cancel</a>
      </div>
    </form>
  </div>
  
<?php elseif ($action === 'delete'): ?>
  <!-- Delete Confirmation -->
  <div class="admin-confirm-delete">
    <h2>Are you sure you want to delete this story?</h2>
    <p>You are about to delete: <strong><?php echo htmlspecialchars($story['title']); ?></strong></p>
    <p class="warning">This action cannot be undone. All comments and likes associated with this story will be deleted as well.</p>
    
    <form action="stories.php?action=delete&id=<?php echo $story_id; ?>" method="POST">
      <div class="form-actions">
        <button type="submit" class="admin-button delete">Yes, Delete Story</button>
        <a href="stories.php" class="admin-button secondary">Cancel</a>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php
// Additional scripts for story editor
if ($action === 'new' || $action === 'edit'): 
    $additional_scripts = '<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js"></script>
    <script>
      tinymce.init({
        selector: "#content",
        height: 400,
        plugins: "autolink lists link image charmap preview hr anchor searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking table directionality emoticons paste textpattern help",
        toolbar: "undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help",
        content_style: "body { font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; }"
      });
    </script>';
endif;

// Include footer
require_once 'includes/footer.php';
?>