/**
 * Admin Dashboard JavaScript for SariSari Stories
 */

document.addEventListener('DOMContentLoaded', function() {
  // Mobile menu toggle
  const menuToggleBtn = document.querySelector('.menu-toggle');
  const adminSidebar = document.querySelector('.admin-sidebar');
  
  if (menuToggleBtn && adminSidebar) {
    menuToggleBtn.addEventListener('click', function() {
      adminSidebar.classList.toggle('open');
    });
  }
  
  // Handle dropdown menus
  const userTrigger = document.querySelector('.admin-user-trigger');
  const userDropdown = document.querySelector('.admin-user-dropdown');
  
  if (userTrigger && userDropdown) {
    userTrigger.addEventListener('click', function(e) {
      e.preventDefault();
      userDropdown.style.display = userDropdown.style.display === 'block' ? 'none' : 'block';
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!userTrigger.contains(e.target) && !userDropdown.contains(e.target)) {
        userDropdown.style.display = 'none';
      }
    });
  }
  
  // Alert dismissal
  const alerts = document.querySelectorAll('.alert');
  
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      setTimeout(() => {
        alert.style.display = 'none';
      }, 500);
    }, 5000);
  });
  
  // Bulk selection for tables
  const selectAllCheckbox = document.getElementById('select-all');
  if (selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('table input[type="checkbox"]');
    
    selectAllCheckbox.addEventListener('change', function() {
      checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
      });
      
      updateBulkActions();
    });
    
    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('change', updateBulkActions);
    });
  }
  
  function updateBulkActions() {
    const selectedItems = document.querySelectorAll('table tbody input[type="checkbox"]:checked');
    const bulkActionButtons = document.querySelectorAll('.bulk-actions button');
    const selectedCount = document.getElementById('selected-count');
    
    if (bulkActionButtons.length) {
      if (selectedItems.length > 0) {
        bulkActionButtons.forEach(button => {
          button.disabled = false;
        });
      } else {
        bulkActionButtons.forEach(button => {
          button.disabled = true;
        });
      }
    }
    
    if (selectedCount) {
      const itemType = selectedCount.dataset.itemType || 'item';
      selectedCount.textContent = `${selectedItems.length} ${itemType}${selectedItems.length !== 1 ? 's' : ''} selected`;
    }
  }
  
  // Form validation
  const adminForms = document.querySelectorAll('.admin-form');
  
  adminForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      const requiredFields = form.querySelectorAll('[required]');
      let isValid = true;
      
      requiredFields.forEach(field => {
        if (!field.value.trim()) {
          isValid = false;
          field.classList.add('error');
          
          // Create error message if it doesn't exist
          const errorElement = field.parentNode.querySelector('.field-error');
          if (!errorElement) {
            const error = document.createElement('p');
            error.classList.add('field-error');
            error.textContent = 'This field is required';
            field.parentNode.appendChild(error);
          }
        } else {
          field.classList.remove('error');
          const errorElement = field.parentNode.querySelector('.field-error');
          if (errorElement) {
            errorElement.remove();
          }
        }
      });
      
      if (!isValid) {
        e.preventDefault();
        
        // Scroll to first error
        const firstError = form.querySelector('.error');
        if (firstError) {
          firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstError.focus();
        }
      }
    });
  });
  
  // Confirm delete actions
  const deleteButtons = document.querySelectorAll('.admin-action-btn.delete');
  
  deleteButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        e.preventDefault();
      }
    });
  });
  
  // Handle image upload preview
  const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
  
  imageInputs.forEach(input => {
    input.addEventListener('change', function() {
      const preview = document.getElementById(`${this.id}-preview`);
      
      if (preview) {
        if (this.files && this.files[0]) {
          const reader = new FileReader();
          
          reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
          };
          
          reader.readAsDataURL(this.files[0]);
        } else {
          preview.style.display = 'none';
        }
      }
    });
  });
  
  // Category icon selection preview
  const iconOptions = document.querySelectorAll('.icon-option');
  const previewIcon = document.querySelector('.preview-icon i');
  
  if (iconOptions.length && previewIcon) {
    iconOptions.forEach(option => {
      option.addEventListener('click', function() {
        const input = this.querySelector('input');
        const iconClass = input.value;
        
        // Update input checked state
        if (!input.checked) {
          input.checked = true;
        }
        
        // Update selected class
        iconOptions.forEach(opt => opt.classList.remove('selected'));
        this.classList.add('selected');
        
        // Update preview
        previewIcon.className = iconClass;
      });
    });
  }
  
  // Handle tabs in admin interfaces
  const tabLinks = document.querySelectorAll('.tab-link');
  
  if (tabLinks.length) {
    tabLinks.forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href').substring(1);
        const targetTab = document.getElementById(targetId);
        
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
          tab.classList.remove('active');
        });
        
        // Deactivate all links
        tabLinks.forEach(tabLink => {
          tabLink.classList.remove('active');
        });
        
        // Activate clicked tab and link
        targetTab.classList.add('active');
        this.classList.add('active');
      });
    });
    
    // Activate first tab by default
    tabLinks[0].click();
  }
  
  // Toggle password visibility
  const togglePasswordButtons = document.querySelectorAll('.toggle-password');
  
  togglePasswordButtons.forEach(button => {
    button.addEventListener('click', function() {
      const passwordField = document.getElementById(this.dataset.target);
      
      if (passwordField) {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        // Toggle icon
        this.innerHTML = type === 'password' ? 
          '<i class="fas fa-eye"></i>' : 
          '<i class="fas fa-eye-slash"></i>';
      }
    });
  });
});