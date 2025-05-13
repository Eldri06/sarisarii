<?php

$page_title = "Edit Story";
$page_description = "Edit your story on SariSari Stories.";

// Add TinyMCE 
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

require_login('login.php');

// Get story ID from URL
$story_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if story exists and belongs to the current user
$sql = "SELECT s.*, c.id as category_id FROM stories s 
        JOIN categories c ON s.category_id = c.id 
        WHERE s.id = ? AND s.user_id = ?";
$story = fetch_one($sql, [$story_id, $current_user['id']]);

// If story not found or doesn't belong to current user, show error
if (!$story) {
    header('Location: profile.php');
    exit;
}

// Get categories for the form
$categories = get_categories();

// Initialize variables
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
        // Create a slug for the story if title changed
        $slug = $title !== $story['title'] ? create_slug($title, 'stories', $story_id) : $story['slug'];
        
        // Prepare story data
        $story_data = [
            'title' => $title,
            'content' => $content,
            'category_id' => $category_id,
            'slug' => $slug,
            'updated_at' => date('Y-m-d H:i:s')
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
        
        // Update story if no errors
        if (empty($error)) {
            $result = update('stories', $story_data, 'id = ?', [$story_id]);
            
            if ($result) {
                // Story updated successfully
                $success = 'Your story has been updated!';
                
                // Redirect to the updated story
                header('Location: story.php?slug=' . $slug);
                exit;
            } else {
                $error = 'Failed to update your story. Please try again.';
            }
        }
    }
}
?>

<section class="edit-story-section">
    <div class="container">
        <div class="edit-story-container">
            <h1>Edit Your Story</h1>
            
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
            
            <form action="edit-story.php?id=<?php echo $story_id; ?>" method="POST" enctype="multipart/form-data" class="edit-story-form">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($story['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $story['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="featured_image">Featured Image</label>
                    <div class="current-featured-image">
                        <?php if (!empty($story['featured_image'])): ?>
                            <img src="<?php echo htmlspecialchars($story['featured_image']); ?>" alt="Featured Image">
                        <?php else: ?>
                            <p>No featured image</p>
                        <?php endif; ?>
                    </div>
                    <input type="file" id="featured_image" name="featured_image" accept="image/*">
                    <div id="image-preview"></div>
                    <small>Max file size: 5MB. Supported formats: JPG, PNG, GIF.</small>
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" required><?php echo htmlspecialchars($story['content']); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="primary-btn">Update Story</button>
                    <a href="story.php?slug=<?php echo htmlspecialchars($story['slug']); ?>" class="secondary-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php

include_once 'includes/footer.php';
?>
