<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Get current page URL and pathname
        const currentUrl = window.location.href;
        const currentPath = window.location.pathname;
        
        // Keep submenu open on settings pages
        if (currentUrl.includes('settings.php')) {
            $('.submenu').addClass('keep-open');
            $('.settings-menu').addClass('active');
            
            // Highlight active settings page based on hash
            updateActiveSubmenuFromHash();
        }
        
        // Function to update active submenu based on hash
        function updateActiveSubmenuFromHash() {
            const hash = window.location.hash || '#edit-profile';
            
            // Remove active class from all submenu items first
            $('.submenu-item').removeClass('active');
            
            if (hash.includes('edit-profile')) {
                $('#edit-profile-link').addClass('active');
            } else if (hash.includes('account-security')) {
                $('#account-security-link').addClass('active');
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
            const href = $(this).attr('href');
            if(href && !href.startsWith('javascript:')) {
                window.location.href = href;
            }
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

    <div class="floating-button" id="openModalBtn">
    <i class="fas fa-plus"></i>
</div>
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
    });
</script>