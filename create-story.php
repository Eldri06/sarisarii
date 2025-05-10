<?php
/**
 * Create story page for SariSari Stories
 */

// Include header
$page_title = "Create Story";
$page_description = "Share your Filipino story with the SariSari Stories community.";

// Add TinyMCE to the head
$additional_head = '
<script src="https://cdn.tiny.cloud/1/ezhv7stnj1p20njlyk17x9ax00ija0xu9j3e0wwa4ybxhe94/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: "#content",
        height: 400,
        plugins: "link image lists table code autolink advlist media emoticons fullscreen preview",
        toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | table | code",
        menubar: false,
        image_advtab: true,
        branding: false,
        statusbar: false,
        setup: function(editor) {
            editor.on("change", function() {
                editor.save(); // This ensures content is saved to the textarea
            });
        }
    });
</script>';

include_once 'includes/header.php';

// Redirect to login if not logged in
if (!is_logged_in()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['PHP_SELF']));
    exit;
}

// Get categories for the form
$categories = get_categories();

// Initialize variables
$title = '';
$content = '';
$category_id = '';
$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = sanitize($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $category_id = intval($_POST['category_id'] ?? 0);
    
    // Validate form data
    if (empty($title) || empty($content) || empty($category_id)) {
        $error = 'All fields are required.';
    } else {
        // Create a slug for the story
        $slug = create_slug($title, 'stories');
        
        // Prepare story data
        $story_data = [
            'title' => $title,
            'content' => $content,
            'category_id' => $category_id,
            'user_id' => $current_user['id'],
            'slug' => $slug,
            'status' => 'published'
        ];
        
        // Handle featured image upload
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $featured_image = upload_image($_FILES['featured_image'], 'uploads/stories');
            
            if ($featured_image) {
                $story_data['featured_image'] = $featured_image;
            } else {
                $error = 'Failed to upload featured image. Please ensure it is a valid image file (JPG, PNG, GIF) under 5MB.';
            }
        }
        
        // Insert story if no errors
        if (empty($error)) {
            $story_id = insert('stories', $story_data);
            
            if ($story_id) {
                // Story created successfully
                $success = 'Your story has been published!';
                
                // Clear form data
                $title = '';
                $content = '';
                $category_id = '';
                
                // Redirect to the new story
                header('Location: story.php?slug=' . $slug);
                exit;
            } else {
                $error = 'Failed to publish your story. Please try again.';
            }
        }
    }
}
?>

<section class="create-story-section">
    <div class="container">
        <div class="create-story-container">
            <h1>Share Your Story</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form action="create-story.php" method="POST" enctype="multipart/form-data" class="create-story-form">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="featured_image">Featured Image</label>
                    <input type="file" id="featured_image" name="featured_image" accept="image/*">
                    <div id="image-preview"></div>
                    <small>Max file size: 5MB. Supported formats: JPG, PNG, GIF.</small>
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" required><?php echo htmlspecialchars($content); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="primary-btn">Publish Story</button>
                    <a href="profile.php" class="secondary-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
