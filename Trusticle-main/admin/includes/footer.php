<!-- Custom JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="../assets/js/sidebar.js" defer></script>
<?php
// Get the current file name
$current_file = basename($_SERVER['PHP_SELF']);

// Include specific JS files based on the current page
if ($current_file === 'activity_log.php') {
    echo '<script src="../assets/js/activity_log.js" defer></script>';
} elseif ($current_file === 'manage_article.php') {
    echo '<script src="../assets/js/article.js" defer></script>';
} elseif ($current_file === 'user_management.php') {
    echo '<script src="../assets/js/user_management.js" defer></script>';
} elseif ($current_file === 'settings.php') {
    echo '<script src="../assets/js/settings.js" defer></script>';
} elseif ($current_file === 'analytics.php') {
    echo '<script src="../assets/js/analytics.js" defer></script>';
} elseif ($current_file === 'dashboard.php') {
    echo '<script src="../assets/js/dashboard.js" defer></script>';
}

?>

<script>
    $(document).ready(function() {
        // Get current page URL and pathname
        const currentUrl = window.location.href;
        const currentPath = window.location.pathname;
        
        // First check if we have a stored active submenu item
        const storedActiveItem = localStorage.getItem('activeSubmenuItem');
        
        // Keep submenu open on settings pages
        if (currentUrl.includes('settings.php')) {
            $('.submenu').addClass('keep-open');
            $('.settings-menu').addClass('active');
            
            // Apply stored active item if it exists and we're on a settings page
            if (storedActiveItem) {
                $('.submenu-item').removeClass('active');
                $(`.submenu-item[href="${storedActiveItem}"]`).addClass('active');
            } else {
                // Otherwise highlight based on hash
                updateActiveSubmenuFromHash();
            }
        }
        
        // Function to update active submenu based on hash
        function updateActiveSubmenuFromHash() {
            const hash = window.location.hash || '#edit-profile';
            
            // Remove active class from all submenu items first
            $('.submenu-item').removeClass('active');
            
            if (hash.includes('edit-profile')) {
                $('.submenu-item[href$="#edit-profile"]').addClass('active');
            } else if (hash.includes('account-security')) {
                $('.submenu-item[href$="#account-security"]').addClass('active');
            }
        }
        
        // Listen for hash changes to update active submenu
        $(window).on('hashchange', function() {
            if (window.location.pathname.includes('settings.php')) {
                updateActiveSubmenuFromHash();
            }
        });
        
        // Settings dropdown
        $('.settings-menu').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).toggleClass('active');
            
            // If on a settings page, don't allow closing the submenu
            if (!currentUrl.includes('settings.php')) {
                $('.submenu').toggleClass('keep-open');
            }
        });
        
        // User profile dropdown (three dots menu)
        $('.user-menu').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Show the dropdown
            $('.user-dropdown').show();
        });
        
        // Handle navigation for settings dropdown
        $('.submenu-item').on('click', function(e) {
            // Remove active class from all submenu items
            $('.submenu-item').removeClass('active');
            // Add active class to clicked item
            $(this).addClass('active');
            
            const href = $(this).attr('href');
            if(href && !href.startsWith('javascript:')) {
                window.location.href = href;
            }
        });
        
        // Handle navigation for user dropdown
        $('.dropdown-item').on('click', function(e) {
            e.preventDefault();
            
            const $this = $(this);
            const href = $this.attr('href');
            
            // If edit profile is clicked, also activate the corresponding submenu item
            if (href && href.includes('edit-profile')) {
                // Remove active class from all submenu items
                $('.submenu-item').removeClass('active');
                
                // Add active class to edit profile submenu item
                $('.submenu-item[href$="#edit-profile"]').addClass('active');
                
                // Open the settings submenu
                $('.submenu').addClass('keep-open');
                $('.settings-menu').addClass('active');
                
                // Store in localStorage to persist through navigation
                localStorage.setItem('activeSubmenuItem', $('.submenu-item[href$="#edit-profile"]').attr('href'));
                
                // Apply a longer delay for Edit Profile to make the highlighting more visible
                setTimeout(function() {
                    if(href && !href.startsWith('javascript:')) {
                        window.location.href = href;
                    }
                }, 350); // Increased from 150ms to 350ms
                return; // Exit early to avoid the other navigation code
            }
            
            // For other dropdown items, use a shorter delay
            setTimeout(function() {
                if(href && !href.startsWith('javascript:')) {
                    window.location.href = href;
                }
            }, 150);
        });
        
        // Close dropdowns when clicking outside
        $(document).on('click', function(e) {
            if(!$(e.target).closest('.settings-menu, .submenu').length && !currentUrl.includes('settings.php')) {
                $('.settings-menu').removeClass('active');
                $('.submenu').removeClass('keep-open');
            }
            if(!$(e.target).closest('.user-profile, .user-menu, .user-dropdown').length) {
                $('.user-dropdown').hide();
            }
            if(!$(e.target).closest('.filter-button, .filter-dropdown').length) {
                $('.filter-dropdown').removeClass('active');
            }
        });

        // Floating action button
        $('.floating-button').click(function() {
            $('#modalOverlay').removeClass('hidden');
        });
        
        // Filter dropdown
        $('.filter-button').click(function(e) {
            e.preventDefault();
            $('.filter-dropdown').toggleClass('active');
            e.stopPropagation();
        });
        
        // Open create article modal
        $('#openModalBtn').click(function() {
            $('#modalOverlay').removeClass('hidden');
        });
        
        // Close create article modal
        $('#closeModalBtn, #cancelBtn').click(function() {
            $('#modalOverlay').addClass('hidden');
        });
        
        // Open article view modal
        $('.article-card').click(function(e) {
            if (!$(e.target).closest('.article-menu').length) {
                const title = $(this).find('.article-title').text();
                const excerpt = $(this).find('.article-excerpt').text();
                
                $('#articleModalTitle').text(title);
                $('#articleModalCategory').text('Technology');
                $('#articleModalSource').text('example.com');
                $('#articleModalDate').text('05/07/2023');
                $('#articleModalContent').text(excerpt + ' Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.');
                
                $('#articleModalOverlay').removeClass('hidden');
            }
        });
        
        // Close article view modal
        $('#closeArticleModalBtn, #closeArticleBtn').click(function() {
            $('#articleModalOverlay').addClass('hidden');
        });
        
        // Filter articles
        $('.filter-item').click(function() {
            const status = $(this).data('status');
            if (status === 'all') {
                $('.article-card').show();
            } else {
                $('.article-card').hide();
                $(`.article-card:has(.status-${status})`).show();
            }
            $('.filter-dropdown').removeClass('active');
        });

        // Clear the stored submenu state when navigating to a non-settings page
        $('.menu-item:not(.settings-menu)').on('click', function() {
            const href = $(this).attr('href');
            if (href && !href.includes('settings.php')) {
                localStorage.removeItem('activeSubmenuItem');
            }
        });

        // Also clear when clicking the logo or other navigation elements
        $('.logo-container').on('click', function() {
            localStorage.removeItem('activeSubmenuItem');
        });
    });
</script>
</body>
</html>