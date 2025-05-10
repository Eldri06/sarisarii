<?php
/**
 * Admin Categories Management for SariSari Stories
 */

// Page title
$page_title = "Categories Management";

// Include header
require_once 'includes/header.php';

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';
$category = [];
$categories = [];

// Available Font Awesome icons for categories
$available_icons = [
    'fas fa-utensils' => 'Utensils',
    'fas fa-book-open' => 'Book Open',
    'fas fa-map-marker-alt' => 'Map Marker',
    'fas fa-camera' => 'Camera',
    'fas fa-calendar-alt' => 'Calendar',
    'fas fa-users' => 'Users',
    'fas fa-landmark' => 'Landmark',
    'fas fa-music' => 'Music',
    'fas fa-palette' => 'Palette',
    'fas fa-shopping-bag' => 'Shopping Bag',
    'fas fa-hands-helping' => 'Helping Hands',
    'fas fa-leaf' => 'Leaf',
    'fas fa-heart' => 'Heart',
    'fas fa-graduation-cap' => 'Graduation Cap'
];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'edit' || $action === 'new') {
        // Get form data
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $icon = sanitize($_POST['icon'] ?? '');
        
        // Validate data
        if (empty($name)) {
            $error = "Category name is required.";
        } else {
            // Create category data array
            $category_data = [
                'name' => $name,
                'description' => $description,
                'icon' => $icon
            ];
            
            // Create slug from name
            if ($action === 'new') {
                $category_data['slug'] = create_slug($name, 'categories');
            } else {
                $category_data['slug'] = create_slug($name, 'categories', $category_id);
            }
            
            if ($action === 'new') {
                // Insert new category
                $category_id = insert('categories', $category_data);
                
                if ($category_id) {
                    $_SESSION['success_message'] = "Category created successfully!";
                    header('Location: categories.php');
                    exit;
                } else {
                    $error = "Failed to create category. Please try again.";
                }
            } else {
                // Update existing category
                $result = update('categories', $category_data, 'id = ?', [$category_id]);
                
                if ($result) {
                    $_SESSION['success_message'] = "Category updated successfully!";
                    header('Location: categories.php');
                    exit;
                } else {
                    $error = "Failed to update category. Please try again.";
                }
            }
        }
    } elseif ($action === 'delete' && $category_id) {
        // Check if there are stories in this category
        $story_count = fetch_one("SELECT COUNT(*) as count FROM stories WHERE category_id = ?", [$category_id]);
        
        if ($story_count && $story_count['count'] > 0) {
            $_SESSION['error_message'] = "Cannot delete category. There are stories assigned to this category.";
            header('Location: categories.php');
            exit;
        }
        
        // Delete category
        $result = delete('categories', 'id = ?', [$category_id]);
        
        if ($result) {
            $_SESSION['success_message'] = "Category deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to delete category.";
        }
        
        header('Location: categories.php');
        exit;
    }
}

// Handle different actions
switch ($action) {
    case 'new':
        // New category form
        $category = [
            'id' => 0,
            'name' => '',
            'description' => '',
            'icon' => 'fas fa-folder',
            'slug' => ''
        ];
        break;
        
    case 'edit':
        // Get category data for editing
        if ($category_id) {
            $sql = "SELECT id, name, description, icon, slug FROM categories WHERE id = ?";
            $category = fetch_one($sql, [$category_id]);
            
            if (!$category) {
                $_SESSION['error_message'] = "Category not found.";
                header('Location: categories.php');
                exit;
            }
        } else {
            header('Location: categories.php');
            exit;
        }
        break;
        
    case 'delete':
        // Confirm delete form
        if ($category_id) {
            $sql = "SELECT id, name, description FROM categories WHERE id = ?";
            $category = fetch_one($sql, [$category_id]);
            
            if (!$category) {
                $_SESSION['error_message'] = "Category not found.";
                header('Location: categories.php');
                exit;
            }
            
            // Get number of stories in this category
            $story_count = fetch_one("SELECT COUNT(*) as count FROM stories WHERE category_id = ?", [$category_id]);
            
            if ($story_count && $story_count['count'] > 0) {
                $_SESSION['error_message'] = "Cannot delete category. There are " . $story_count['count'] . " stories assigned to this category.";
                header('Location: categories.php');
                exit;
            }
        } else {
            header('Location: categories.php');
            exit;
        }
        break;
        
    default:
        // List all categories
        $sql = "SELECT c.*, (SELECT COUNT(*) FROM stories WHERE category_id = c.id) as story_count 
                FROM categories c 
                ORDER BY name ASC";
        $categories = fetch_all($sql);
        break;
}
?>

<div class="admin-header-actions">
  <h1><?php echo $action === 'list' ? 'Categories Management' : ($action === 'new' ? 'Add New Category' : ($action === 'edit' ? 'Edit Category' : 'Delete Category')); ?></h1>
  
  <?php if ($action === 'list'): ?>
    <a href="categories.php?action=new" class="admin-button">
      <i class="fas fa-plus"></i> Add New Category
    </a>
  <?php else: ?>
    <a href="categories.php" class="admin-button secondary">
      <i class="fas fa-arrow-left"></i> Back to Categories
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
  <!-- Categories Listing -->
  <div class="categories-grid admin-categories">
    <?php if (empty($categories)): ?>
      <div class="no-results">No categories found.</div>
    <?php else: ?>
      <?php foreach ($categories as $cat): ?>
        <div class="category-card admin-category-card">
          <div class="category-header">
            <div class="category-icon">
              <i class="<?php echo htmlspecialchars($cat['icon']); ?>"></i>
            </div>
            <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
          </div>
          
          <div class="category-description">
            <p><?php echo htmlspecialchars($cat['description']); ?></p>
          </div>
          
          <div class="category-meta">
            <div class="story-count">
              <i class="fas fa-book"></i> <?php echo $cat['story_count']; ?> stories
            </div>
            <div class="category-slug">
              <span>Slug: <?php echo htmlspecialchars($cat['slug']); ?></span>
            </div>
          </div>
          
          <div class="category-actions">
            <a href="categories.php?action=edit&id=<?php echo $cat['id']; ?>" class="admin-button small">
              <i class="fas fa-edit"></i> Edit
            </a>
            <?php if ($cat['story_count'] == 0): ?>
              <a href="categories.php?action=delete&id=<?php echo $cat['id']; ?>" class="admin-button small danger">
                <i class="fas fa-trash"></i> Delete
              </a>
            <?php else: ?>
              <button class="admin-button small danger" disabled title="Cannot delete categories with stories">
                <i class="fas fa-trash"></i> Delete
              </button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  
<?php elseif ($action === 'new' || $action === 'edit'): ?>
  <!-- Category Form -->
  <div class="admin-form-container">
    <form action="categories.php?action=<?php echo $action; ?><?php echo $category_id ? '&id=' . $category_id : ''; ?>" method="POST" class="admin-form">
      <div class="form-group">
        <label for="name">Category Name *</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
      </div>
      
      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($category['description']); ?></textarea>
      </div>
      
      <div class="form-group">
        <label>Icon</label>
        <div class="icon-selection">
          <?php foreach ($available_icons as $icon_class => $icon_name): ?>
            <label class="icon-option <?php echo $category['icon'] === $icon_class ? 'selected' : ''; ?>">
              <input type="radio" name="icon" value="<?php echo $icon_class; ?>" <?php echo $category['icon'] === $icon_class ? 'checked' : ''; ?>>
              <i class="<?php echo $icon_class; ?>"></i>
              <span><?php echo $icon_name; ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      
      <div class="form-group">
        <label>Preview</label>
        <div class="icon-preview">
          <div class="preview-icon">
            <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
          </div>
          <div class="preview-name">
            <?php echo htmlspecialchars($category['name'] ?: 'Category Name'); ?>
          </div>
        </div>
      </div>
      
      <div class="form-actions">
        <button type="submit" class="admin-button">
          <?php echo $action === 'new' ? 'Create Category' : 'Update Category'; ?>
        </button>
        <a href="categories.php" class="admin-button secondary">Cancel</a>
      </div>
    </form>
  </div>
  
<?php elseif ($action === 'delete'): ?>
  <!-- Delete Confirmation -->
  <div class="admin-confirm-delete">
    <h2>Are you sure you want to delete this category?</h2>
    <p>You are about to delete: <strong><?php echo htmlspecialchars($category['name']); ?></strong></p>
    <p class="warning">This action cannot be undone.</p>
    
    <form action="categories.php?action=delete&id=<?php echo $category_id; ?>" method="POST">
      <div class="form-actions">
        <button type="submit" class="admin-button delete">Yes, Delete Category</button>
        <a href="categories.php" class="admin-button secondary">Cancel</a>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php if ($action === 'new' || $action === 'edit'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Update preview when icon selection changes
  const iconInputs = document.querySelectorAll('input[name="icon"]');
  const previewIcon = document.querySelector('.preview-icon i');
  
  iconInputs.forEach(input => {
    input.addEventListener('change', function() {
      if (this.checked) {
        previewIcon.className = this.value;
        
        // Update selected class
        document.querySelectorAll('.icon-option').forEach(option => {
          option.classList.remove('selected');
        });
        this.closest('.icon-option').classList.add('selected');
      }
    });
  });
  
  // Update preview when name changes
  const nameInput = document.getElementById('name');
  const previewName = document.querySelector('.preview-name');
  
  nameInput.addEventListener('input', function() {
    previewName.textContent = this.value || 'Category Name';
  });
});
</script>
<?php endif; ?>

<?php
// Include footer
require_once 'includes/footer.php';
?>