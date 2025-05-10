/**
 * Main JavaScript file for SariSari Stories
 */



document.addEventListener('DOMContentLoaded', function() {
    // transparent header on scroll
    const header = document.getElementById('main-header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // User dropdown menu
    const userMenuTrigger = document.querySelector('.user-menu-trigger');
    if (userMenuTrigger) {
        userMenuTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = document.querySelector('.user-dropdown');
            dropdown.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.querySelector('.user-dropdown');
            const trigger = document.querySelector('.user-menu-trigger');
            
            if (dropdown && !dropdown.contains(e.target) && !trigger.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    }
    
    // Profile tabs functionality
    const profileTabs = document.querySelectorAll('.profile-tabs a');
    if (profileTabs.length > 0) {
        profileTabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all tabs and content
                document.querySelectorAll('.profile-tabs a').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
            
                this.classList.add('active');
                
                
                const tabId = this.getAttribute('href');
                document.querySelector(tabId).classList.add('active');
            });
        });
    }
    
    // File upload preview
    const fileInput = document.getElementById('featured_image');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                const preview = document.getElementById('image-preview');
                
                reader.addEventListener('load', function() {
                    // Create preview
                    preview.innerHTML = `
                        <img src="${this.result}" alt="Image preview">
                        <span class="remove-image" title="Remove image"><i class="fas fa-times"></i></span>
                    `;
                    
                    // Add remove functionality
                    const removeButton = document.querySelector('.remove-image');
                    if (removeButton) {
                        removeButton.addEventListener('click', function() {
                            fileInput.value = '';
                            preview.innerHTML = '';
                        });
                    }
                });
                
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Like button functionality
    const likeButtons = document.querySelectorAll('.like-button');
    if (likeButtons.length > 0) {
        likeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const storyId = this.getAttribute('data-story-id');
                const likeCount = this.querySelector('.like-count');
                
                // Send AJAX request to toggle like
                fetch('/like.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `story_id=${storyId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update like count and button state
                        likeCount.textContent = data.likes;
                        
                        if (data.liked) {
                            this.classList.add('liked');
                        } else {
                            this.classList.remove('liked');
                        }
                    } else {
                        // User not logged in or other error
                        if (data.message === 'login_required') {
                            window.location.href = '/login.php?redirect=' + encodeURIComponent(window.location.href);
                        } else {
                            alert('Error: ' + data.message);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    }
    
    // Comment form submission
    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const storyId = this.getAttribute('data-story-id');
            const content = document.getElementById('comment-content').value;
            const commentsList = document.getElementById('comments-list');
            
            if (!content.trim()) {
                alert('Please enter a comment');
                return;
            }
            
            // Send AJAX request to add comment
            fetch('comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `story_id=${storyId}&content=${encodeURIComponent(content)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add new comment to the list
                    const newComment = document.createElement('div');
                    newComment.className = 'comment';
                    newComment.innerHTML = data.html;
                    
                    commentsList.insertBefore(newComment, commentsList.firstChild);
                    
                    // Clear form
                    document.getElementById('comment-content').value = '';
                    
                    // Update comment count
                    const commentCount = document.querySelector('.comment-count');
                    if (commentCount) {
                        commentCount.textContent = parseInt(commentCount.textContent) + 1;
                    }
                } else {
                    // User not logged in or other error
                    if (data.message === 'login_required') {
                        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                    } else {
                        alert('Error: ' + data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
    
    // Newsletter subscription
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = this.querySelector('input[name="email"]').value;
            
            if (!email.trim()) {
                alert('Please enter your email address');
                return;
            }
            
            // Send AJAX request to subscribe
            fetch('subscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `email=${encodeURIComponent(email)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const confirmation = document.createElement('p');
                    confirmation.className = 'subscription-confirmation';
                    confirmation.textContent = 'Thank you for subscribing!';
                    
                    this.parentNode.insertBefore(confirmation, this.nextSibling);
                    
                    // Clear form
                    this.querySelector('input[name="email"]').value = '';
                    
                    // Remove message after a few seconds
                    setTimeout(() => {
                        confirmation.remove();
                    }, 5000);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
});
