<?php

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';


require_login();


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get current user
$current_user = get_current_user_data();
$user_id = $current_user['id'];

// Get categories for the dropdown
$categories = get_categories();

// Check
$is_edit = false;
$story_id = null;
$story = null;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $story_id = (int)$_GET['edit'];
    
    // Get story data
    $sql = "SELECT * FROM stories WHERE id = ? AND user_id = ?";
    $story = fetch_one($sql, [$story_id, $user_id]);
    
    if ($story) {
        $is_edit = true;
    } else {
        // Story not found 
        header('Location: profile.php');
        exit;
    }
}


$title = $is_edit ? $story['title'] : '';
$content = $is_edit ? $story['content'] : '';
$category_id = $is_edit ? $story['category_id'] : '';
$featured_image = $is_edit ? $story['featured_image'] : '';
$status = $is_edit ? $story['status'] : 'published';
$success_message = '';
$error_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $title = sanitize($_POST['title']);
    $content = $_POST['content']; 
    $category_id = (int)$_POST['category_id'];
    $status = $_POST['status'];
    
   
    $slug = create_slug($title, 'stories', $is_edit ? $story_id : null);
    
    // Validation
    if (empty($title) || empty($content) || empty($category_id)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Handle featured image upload
        $image_path = $featured_image;
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $new_image = upload_image($_FILES['featured_image'], 'uploads/stories');
            if ($new_image) {
                $image_path = $new_image;
            } else {
                $error_message = "Error uploading image. Please try again.";
            }
        }
        
        if (empty($error_message)) {
            // Prepare story data
            $story_data = [
                'title' => $title,
                'content' => $content,
                'category_id' => $category_id,
                'status' => $status,
                'slug' => $slug
            ];
            
            // Add image path if it exists
            if ($image_path) {
                $story_data['featured_image'] = $image_path;
            }
            
            if ($is_edit) {
                // Update existing story
                $result = update('stories', $story_data, "id = ? AND user_id = ?", [$story_id, $user_id]);
                
                if ($result !== false) {
                    $success_message = "Story updated successfully.";
                } else {
                    $error_message = "Error updating story. Please try again.";
                }
            } else {
                // Add user_id to story data
                $story_data['user_id'] = $user_id;
                
                // Insert new story
                $result = insert('stories', $story_data);
                
                if ($result) {
                    $story_id = $result;
                    $success_message = "Story created successfully.";
                    
                    // Clear form fields after successful submission
                    $title = '';
                    $content = '';
                    $category_id = '';
                    $featured_image = '';
                    $status = 'published';
                } else {
                    $error_message = "Error creating story. Please try again.";
                }
            }
        }
    }
}

// Page title
$page_title = $is_edit ? 'Edit Story - ' . SITE_NAME : 'Create Story - ' . SITE_NAME;

// styles head content API (TinyMCE)
$additional_head = <<<HTML
<script src="https://cdn.tiny.cloud/1/ezhv7stnj1p20njlyk17x9ax00ija0xu9j3e0wwa4ybxhe94/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
  tinymce.init({
    selector: '#content',
    height: 400,
    menubar: false,
    plugins: [
      'advlist autolink lists link image charmap print preview anchor',
      'searchreplace visualblocks code fullscreen',
      'insertdatetime media table paste code help wordcount'
    ],
    toolbar: 'undo redo | formatselect | bold italic backcolor | \
      alignleft aligncenter alignright alignjustify | \
      bullist numlist outdent indent | removeformat | help',
    content_style: 'body { font-family: "Poppins", sans-serif; font-size: 16px; }'
  });
</script>
HTML;

// Include header
require_once '../includes/header.php';
?>

<div class="page-content">
  <div class="container">
    <h1 class="section-title"><?php echo $is_edit ? 'Edit Your Story' : 'Share Your Story'; ?></h1>
    <p class="section-subtitle">Share your local experiences, hidden gems, and personal narratives with the community.</p>
    
    <?php if (!empty($success_message)): ?>
      <div class="alert success">
        <?php echo $success_message; ?>
        <?php if ($is_edit && isset($story_id)): ?>
          <p><a href="story.php?slug=<?php echo $slug; ?>">View your story</a> or <a href="profile.php">go to your profile</a>.</p>
        <?php elseif (isset($story_id)): ?>
          <p><a href="story.php?id=<?php echo $story_id; ?>">View your story</a> or <a href="create-story.php">create another story</a>.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
      <div class="alert error">
        <?php echo $error_message; ?>
      </div>
    <?php endif; ?>
    
    <div class="form-container" style="max-width: 800px;">
      <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . ($is_edit ? '?edit=' . $story_id : '')); ?>" enctype="multipart/form-data">
        <div class="form-group">
          <label for="title">Title *</label>
          <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
        </div>
        
        <div class="form-group">
          <label for="category_id">Category *</label>
          <select id="category_id" name="category_id" required>
            <option value="">Select a category</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                <?php echo $category['name']; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group">
          <label for="featured_image">Featured Image</label>
          <div class="file-upload">
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Drag your image here, or <span>browse</span></p>
            <small>Recommended size: 1200x800 pixels. Maximum file size: 5MB.</small>
            <input type="file" id="featured_image" name="featured_image" accept="image/*">
          </div>
          
          <?php if (!empty($featured_image)): ?>
            <div class="image-preview" id="image-preview">
              <img src="<?php echo $featured_image; ?>" alt="Featured image preview">
              <div class="remove-image" title="Remove image"><i class="fas fa-times"></i></div>
            </div>
          <?php else: ?>
            <div class="image-preview" id="image-preview"></div>
          <?php endif; ?>
        </div>
        
        <div class="form-group">
          <label for="content">Story Content *</label>
          <textarea id="content" name="content" required><?php echo htmlspecialchars($content); ?></textarea>
        </div>
        
        <div class="form-group">
          <label for="status">Status</label>
          <select id="status" name="status">
            <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
            <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
          </select>
          <small>Draft stories won't be visible to other users.</small>
        </div>
        
        <div class="form-group">
          <button type="submit" class="primary-btn"><?php echo $is_edit ? 'Update Story' : 'Publish Story'; ?></button>
          <a href="profile.php" class="secondary-btn">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
// Include footer
require_once '../includes/footer.php';
?>
