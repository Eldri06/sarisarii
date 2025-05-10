<?php
/**
 * Admin Settings Management for SariSari Stories
 */

// Page title
$page_title = "Site Settings";

// Include header
require_once 'includes/header.php';

// Initialize variables
$message = '';
$error = '';
$settings = [];

// Define settings sections and fields
$settings_sections = [
    'general' => [
        'title' => 'General Settings',
        'fields' => [
            'site_name' => [
                'label' => 'Site Name',
                'type' => 'text',
                'value' => SITE_NAME,
                'required' => true
            ],
            'site_description' => [
                'label' => 'Site Description',
                'type' => 'textarea',
                'value' => SITE_DESCRIPTION,
                'required' => false
            ],
            'site_url' => [
                'label' => 'Site URL',
                'type' => 'text',
                'value' => SITE_URL,
                'required' => true
            ],
            'admin_email' => [
                'label' => 'Admin Email',
                'type' => 'email',
                'value' => ADMIN_EMAIL,
                'required' => true
            ],
            'contact_email' => [
                'label' => 'Contact Email',
                'type' => 'email',
                'value' => CONTACT_EMAIL,
                'required' => true
            ],
            'posts_per_page' => [
                'label' => 'Posts Per Page',
                'type' => 'number',
                'value' => POSTS_PER_PAGE,
                'required' => true,
                'min' => 1,
                'max' => 50
            ],
            'enable_registration' => [
                'label' => 'Enable User Registration',
                'type' => 'checkbox',
                'value' => ENABLE_REGISTRATION,
                'required' => false
            ]
        ]
    ],
    'appearance' => [
        'title' => 'Appearance Settings',
        'fields' => [
            'primary_color' => [
                'label' => 'Primary Color',
                'type' => 'color',
                'value' => '#3498db',
                'required' => false
            ],
            'secondary_color' => [
                'label' => 'Secondary Color',
                'type' => 'color',
                'value' => '#2ecc71',
                'required' => false
            ],
            'show_featured_section' => [
                'label' => 'Show Featured Stories Section',
                'type' => 'checkbox',
                'value' => true,
                'required' => false
            ],
            'show_categories_section' => [
                'label' => 'Show Categories Section',
                'type' => 'checkbox',
                'value' => true,
                'required' => false
            ],
            'show_newsletter_section' => [
                'label' => 'Show Newsletter Section',
                'type' => 'checkbox',
                'value' => true,
                'required' => false
            ]
        ]
    ],
    'advanced' => [
        'title' => 'Advanced Settings',
        'fields' => [
            'session_timeout' => [
                'label' => 'Session Timeout (seconds)',
                'type' => 'number',
                'value' => SESSION_TIMEOUT,
                'required' => true,
                'min' => 300,
                'max' => 86400
            ],
            'error_reporting' => [
                'label' => 'Enable Error Reporting',
                'type' => 'checkbox',
                'value' => (bool) ini_get('display_errors'),
                'required' => false
            ],
            'maintenance_mode' => [
                'label' => 'Maintenance Mode',
                'type' => 'checkbox',
                'value' => false,
                'required' => false
            ]
        ]
    ]
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_settings') {
        // In a real application, this would update a settings.php file or database table
        // For this demo, we'll just show a success message
        $_SESSION['success_message'] = "Settings updated successfully!";
        header('Location: settings.php');
        exit;
    } elseif ($action === 'clear_cache') {
        // In a real application, this would clear cached data
        $_SESSION['success_message'] = "Cache cleared successfully!";
        header('Location: settings.php');
        exit;
    } elseif ($action === 'backup_database') {
        // In a real application, this would create a database backup
        $_SESSION['success_message'] = "Database backup created successfully!";
        header('Location: settings.php');
        exit;
    }
}

// Check for active tab
$active_tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $settings_sections) 
    ? $_GET['tab'] : key($settings_sections);
?>

<div class="admin-header-actions">
  <h1>Site Settings</h1>
  
  <div class="action-buttons">
    <a href="#" class="admin-button" id="save-settings-btn">
      <i class="fas fa-save"></i> Save Settings
    </a>
  </div>
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

<div class="admin-settings">
  <div class="settings-tabs">
    <ul>
      <?php foreach ($settings_sections as $section_key => $section): ?>
        <li>
          <a href="settings.php?tab=<?php echo $section_key; ?>" class="<?php echo $active_tab === $section_key ? 'active' : ''; ?>">
            <?php echo $section['title']; ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  
  <div class="settings-content">
    <form id="settings-form" action="settings.php" method="POST" class="admin-form">
      <input type="hidden" name="action" value="update_settings">
      
      <?php foreach ($settings_sections as $section_key => $section): ?>
        <div class="settings-section <?php echo $active_tab === $section_key ? 'active' : ''; ?>" id="section-<?php echo $section_key; ?>">
          <h2><?php echo $section['title']; ?></h2>
          
          <?php foreach ($section['fields'] as $field_key => $field): ?>
            <div class="form-group">
              <?php if ($field['type'] === 'checkbox'): ?>
                <div class="checkbox-group">
                  <label class="checkbox-label">
                    <input type="checkbox" name="settings[<?php echo $field_key; ?>]" value="1" <?php echo $field['value'] ? 'checked' : ''; ?>>
                    <span><?php echo $field['label']; ?></span>
                  </label>
                </div>
              <?php else: ?>
                <label for="<?php echo $field_key; ?>"><?php echo $field['label']; ?></label>
                
                <?php if ($field['type'] === 'textarea'): ?>
                  <textarea id="<?php echo $field_key; ?>" name="settings[<?php echo $field_key; ?>]" rows="4" <?php echo $field['required'] ? 'required' : ''; ?>><?php echo htmlspecialchars($field['value']); ?></textarea>
                <?php else: ?>
                  <input type="<?php echo $field['type']; ?>" id="<?php echo $field_key; ?>" name="settings[<?php echo $field_key; ?>]" value="<?php echo htmlspecialchars($field['value']); ?>" <?php echo $field['required'] ? 'required' : ''; ?> <?php echo isset($field['min']) ? 'min="' . $field['min'] . '"' : ''; ?> <?php echo isset($field['max']) ? 'max="' . $field['max'] . '"' : ''; ?>>
                <?php endif; ?>
              <?php endif; ?>
              
              <?php if (isset($field['description'])): ?>
                <p class="field-hint"><?php echo $field['description']; ?></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
      
      <div class="form-actions">
        <button type="submit" class="admin-button">Save Settings</button>
        <button type="reset" class="admin-button secondary">Reset Changes</button>
      </div>
    </form>
    
    <?php if ($active_tab === 'advanced'): ?>
      <div class="maintenance-actions">
        <h3>Maintenance Actions</h3>
        <div class="action-cards">
          <div class="action-card">
            <div class="action-card-icon">
              <i class="fas fa-trash"></i>
            </div>
            <div class="action-card-content">
              <h4>Clear Cache</h4>
              <p>Clear all cached data to refresh the site content.</p>
              <form action="settings.php" method="POST">
                <input type="hidden" name="action" value="clear_cache">
                <button type="submit" class="admin-button small">Clear Cache</button>
              </form>
            </div>
          </div>
          
          <div class="action-card">
            <div class="action-card-icon">
              <i class="fas fa-database"></i>
            </div>
            <div class="action-card-content">
              <h4>Backup Database</h4>
              <p>Create a backup of the database for recovery purposes.</p>
              <form action="settings.php" method="POST">
                <input type="hidden" name="action" value="backup_database">
                <button type="submit" class="admin-button small">Create Backup</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
/* Settings Page Specific Styles */
.admin-settings {
  display: flex;
  gap: 20px;
  margin-bottom: 30px;
}

.settings-tabs {
  width: 250px;
  flex-shrink: 0;
}

.settings-tabs ul {
  list-style: none;
  padding: 0;
  margin: 0;
  background-color: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.settings-tabs ul li {
  border-bottom: 1px solid #eee;
}

.settings-tabs ul li:last-child {
  border-bottom: none;
}

.settings-tabs ul li a {
  display: block;
  padding: 15px 20px;
  color: #2c3e50;
  text-decoration: none;
  transition: all 0.3s;
}

.settings-tabs ul li a:hover {
  background-color: #f9f9f9;
}

.settings-tabs ul li a.active {
  background-color: #3498db;
  color: #fff;
  font-weight: 500;
}

.settings-content {
  flex-grow: 1;
  background-color: #fff;
  border-radius: 8px;
  padding: 25px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.settings-section {
  display: none;
}

.settings-section.active {
  display: block;
}

.settings-section h2 {
  margin-top: 0;
  margin-bottom: 20px;
  color: #2c3e50;
  font-size: 1.3rem;
  padding-bottom: 10px;
  border-bottom: 1px solid #eee;
}

.maintenance-actions {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.maintenance-actions h3 {
  color: #2c3e50;
  font-size: 1.2rem;
  margin-bottom: 20px;
}

.action-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.action-card {
  background-color: #f9f9f9;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
}

.action-card-icon {
  width: 50px;
  height: 50px;
  background-color: rgba(52, 152, 219, 0.1);
  color: #3498db;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.action-card:nth-child(2) .action-card-icon {
  background-color: rgba(46, 204, 113, 0.1);
  color: #2ecc71;
}

.action-card-content {
  flex-grow: 1;
}

.action-card-content h4 {
  margin: 0 0 5px 0;
  color: #2c3e50;
  font-size: 1.1rem;
}

.action-card-content p {
  margin: 0 0 15px 0;
  color: #7f8c8d;
  font-size: 0.9rem;
}

@media (max-width: 768px) {
  .admin-settings {
    flex-direction: column;
  }
  
  .settings-tabs {
    width: 100%;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Tab Navigation
  const tabLinks = document.querySelectorAll('.settings-tabs a');
  const sections = document.querySelectorAll('.settings-section');
  
  tabLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Remove active class from all tabs and sections
      tabLinks.forEach(tab => tab.classList.remove('active'));
      sections.forEach(section => section.classList.remove('active'));
      
      // Add active class to clicked tab
      this.classList.add('active');
      
      // Get section ID from href
      const href = this.getAttribute('href');
      const sectionId = href.split('=')[1];
      
      // Activate corresponding section
      document.getElementById('section-' + sectionId).classList.add('active');
      
      // Update URL without page reload
      history.pushState(null, '', href);
    });
  });
  
  // Save settings button
  const saveButton = document.getElementById('save-settings-btn');
  const settingsForm = document.getElementById('settings-form');
  
  if (saveButton && settingsForm) {
    saveButton.addEventListener('click', function(e) {
      e.preventDefault();
      settingsForm.submit();
    });
  }
});
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>