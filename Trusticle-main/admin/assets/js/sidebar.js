$(document).ready(function() {
    // Cache DOM elements - improves performance and readability
    const $settingsMenu = $('#settingsMenu');
    const $settingsSubmenu = $('#settingsSubmenu');
    const $settingsArrow = $settingsMenu.find('.settings-arrow');
    const $userMenu = $('.user-menu');
    const $userDropdown = $('.user-dropdown');
    const $submenuItems = $('.submenu-item');
    const $userDropdownItems = $('.dropdown-item');
    const $menuItems = $('.menu-item:not(.settings-menu)');
    const $securityTabs = $('.security-tab');
    const $securityContents = $('.security-content');
    const $settingsTabs = $('.settings-tab');
    const $tabContents = $('.tab-content');
    
    // Constants - makes code easier to maintain (KISS principle)
    const STORAGE_KEY = 'settingsSubmenuOpen';
    const TRANSITION_STYLE = 'transform 0.3s ease';
    const ACTIVE_CLASS = 'active';
    const KEEP_OPEN_CLASS = 'keep-open';
    
    /**
     * Submenu state management - localStorage
     */
    function storeSubmenuState(isOpen) {
        localStorage.setItem(STORAGE_KEY, isOpen ? 'true' : 'false');
    }
    
    function getSubmenuState() {
        return localStorage.getItem(STORAGE_KEY) === 'true';
    }
    
    /**
     * Menu state management functions
     */
    function openSettingsSubmenu() {
        $settingsMenu.addClass(ACTIVE_CLASS);
        $settingsSubmenu.addClass(KEEP_OPEN_CLASS);
        $settingsArrow.css({
            'transition': TRANSITION_STYLE,
            'transform': 'rotate(180deg)'
        });
        storeSubmenuState(true);
    }
    
    function closeSettingsSubmenu() {
        $settingsMenu.removeClass(ACTIVE_CLASS);
        $settingsSubmenu.removeClass(KEEP_OPEN_CLASS);
        $settingsArrow.css({
            'transition': TRANSITION_STYLE,
            'transform': 'rotate(0deg)'
        });
        storeSubmenuState(false);
    }
    
    function toggleSettingsSubmenu() {
        const isActive = $settingsMenu.hasClass(ACTIVE_CLASS);
        
        // Close all other dropdowns first
        $userDropdown.removeClass(ACTIVE_CLASS);
        
        // Toggle settings menu
        isActive ? closeSettingsSubmenu() : openSettingsSubmenu();
    }
    
    /**
     * Navigation helpers
     */
    function setActiveState($items, $clickedItem) {
        $items.removeClass(ACTIVE_CLASS);
        $clickedItem.addClass(ACTIVE_CLASS);
    }
    
    function navigateTo(href, delay = 200) {
        if (!href) return;
        
        // Use setTimeout to ensure DOM state is updated before navigation
        setTimeout(function() {
            window.location.href = href;
        }, delay);
    }
    
    /**
     * URL and path matching functions
     */
    function isUrlMatch(href) {
        if (!href) return false;
        
        const currentPath = window.location.pathname;
        const currentHash = window.location.hash;
        
        // Direct hash comparison for settings tabs
        if (href.includes('#') && currentHash) {
            const hrefHash = href.substring(href.indexOf('#'));
            return currentHash.startsWith(hrefHash);
        }
        
        // For normal page links (non-hash), extract the filename for comparison
        // This handles admin/view/settings.php vs settings.php#edit-profile
        const hrefFilename = href.split('/').pop().split('#')[0];
        const currentFilename = currentPath.split('/').pop().split('#')[0];
        
        return currentFilename === hrefFilename;
    }
    
    function isSubmenuPage() {
        return $submenuItems.toArray().some(item => 
            isUrlMatch($(item).attr('href'))
        );
    }
    
    function isEditProfilePage() {
        const currentHash = window.location.hash;
        return currentHash.includes('edit-profile') || currentHash === '' || !currentHash;
    }
    
    function isAccountSecurityPage() {
        const currentHash = window.location.hash;
        return currentHash.includes('account-security');
    }
    
    /**
     * Event handlers
     */
    function handleSettingsClick(e) {
        e.stopPropagation();
        toggleSettingsSubmenu();
    }
    
    function handleSubmenuItemClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $this = $(this);
        const href = $this.attr('href');
        
        // First apply the active state immediately
        setActiveState($submenuItems, $this);
        openSettingsSubmenu();
        
        // Store current active item in localStorage to persist through page refresh
        localStorage.setItem('activeSubmenuItem', $this.attr('href'));
        
        // Force visual rendering before navigation
        $(document).ready(function() {
            // Ensure the active state is applied visually
            $this.addClass(ACTIVE_CLASS);
            
            // Delay navigation more significantly to ensure CSS transition completes
            setTimeout(() => {
                navigateTo(href, 50);
            }, 200);
        });
    }
    
    function handleMenuItemClick(e) {
        const $this = $(this);
        
        // Skip if this is a dropdown toggle or not a navigation link
        if ($this.hasClass('settings-menu') || !$this.attr('href')) {
            return;
        }
        
        // Set this menu item as active
        setActiveState($menuItems, $this);
    }
    
    function handleUserMenuClick(e) {
        e.stopPropagation();
        $userDropdown.toggleClass(ACTIVE_CLASS);
    }
    
    function handleUserDropdownItemClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $this = $(this);
        const href = $this.attr('href');
        
        // Always close the dropdown menu when an item is clicked
        $userDropdown.removeClass(ACTIVE_CLASS);
        
        // If edit profile is clicked, also activate the corresponding submenu item
        if (href && href.includes('edit-profile')) {
            const $editProfileSubmenu = $submenuItems.filter(function() {
                return $(this).attr('href').includes('edit-profile');
            });
            
            if ($editProfileSubmenu.length) {
                // First apply active state
                setActiveState($submenuItems, $editProfileSubmenu);
                openSettingsSubmenu();
                
                // Store in localStorage to persist through navigation
                localStorage.setItem('activeSubmenuItem', $editProfileSubmenu.attr('href'));
                
                // Ensure CSS transition completes before navigation with a longer delay
                setTimeout(() => {
                    navigateTo(href, 50);
                }, 350); // Increased from 200ms to 350ms
                return; // Exit early since we're handling navigation with delay
            }
        }
        
        // For other links, navigate after a short delay
        setTimeout(() => {
            navigateTo(href, 50);
        }, 150);
    }
    
    function handleDocumentClick(e) {
        // Don't close if clicking on a submenu item
        if ($(e.target).closest('.submenu-item').length) {
            return;
        }
        
        // Don't close settings submenu if it's a submenu page
        if (!$(e.target).closest($settingsMenu).length && 
            !$(e.target).closest($settingsSubmenu).length && 
            !isSubmenuPage()) {
            closeSettingsSubmenu();
        }

        // Close user dropdown
        if (!$(e.target).closest($userMenu).length && 
            !$(e.target).closest($userDropdown).length) {
            $userDropdown.removeClass(ACTIVE_CLASS);
        }
    }
    
    // Handle main settings tabs
    $settingsTabs.on('click', function(e) {
        e.preventDefault();
        
        const target = $(this).attr('href').substring(1); // Remove the #
        
        // Update tab state
        $settingsTabs.removeClass('active').attr({
            'aria-selected': 'false'
        });
        
        $(this).addClass('active').attr({
            'aria-selected': 'true'
        });
        
        // Update content state
        $tabContents.removeClass('active').attr('hidden', 'true');
        $('#' + target).addClass('active').removeAttr('hidden');
        
        // Update URL hash without page jump
        const scrollPos = window.scrollY;
        window.location.hash = $(this).attr('href');
        setTimeout(() => window.scrollTo(0, scrollPos), 0);
    });
    
    /**
     * Helper functions
     */
    function activateEditProfileSubmenu() {
        const $editProfileSubmenu = $submenuItems.filter(function() {
            return $(this).attr('href').includes('edit-profile');
        });
        
        if ($editProfileSubmenu.length) {
            setActiveState($submenuItems, $editProfileSubmenu);
            openSettingsSubmenu();
        }
    }
    
    // Initialize profile image handling
    function initProfileImage() {
        const storedImageUrl = sessionStorage.getItem('profileImageUrl');
        if (storedImageUrl) {
            console.log('Found stored profile image URL:', storedImageUrl);
            
            // Format the URL correctly to ensure it points to main assets
            let imagePath = storedImageUrl;
            
            // If it's a relative path, ensure it points to the correct directory
            if (!storedImageUrl.startsWith('http') && !storedImageUrl.startsWith('/')) {
                // Extract the filename
                const fileName = storedImageUrl.split('/').pop();
                
                // Use the correct path based on context - check if in admin view or includes
                const currentPath = window.location.pathname;
                if (currentPath.includes('/admin/view/')) {
                    // In admin view folder, need to go up two levels
                    imagePath = '../../assets/images/profiles/' + fileName;
                } else if (currentPath.includes('/admin/includes/')) {
                    // In admin includes folder
                    imagePath = '../../assets/images/profiles/' + fileName;
                } else {
                    // Default fallback
                    imagePath = '../../assets/images/profiles/' + fileName;
                }
            }
            
            // Add cache buster
            const cacheBustUrl = imagePath + '?v=' + new Date().getTime();
            console.log('Updating sidebar profile image with:', cacheBustUrl);
            
            // Update all sidebar profile images
            $('.sidebar .profile-image').attr('src', cacheBustUrl);
        }
    }
    
    // Handle tabs in settings page
    $securityTabs.on('click', function(e) {
        e.preventDefault();
        
        // Update tab state
        $securityTabs.removeClass('active').attr({
            'aria-selected': 'false',
            'tabindex': '-1'
        });
        
        $(this).addClass('active').attr({
            'aria-selected': 'true', 
            'tabindex': '0'
        });
        
        // Update content state
        const target = $(this).attr('href').split('/')[1];
        $securityContents.removeClass('active').attr('hidden', 'true');
        $('#' + target).addClass('active').removeAttr('hidden');
        
        // Update URL hash without page jump
        const scrollPos = window.scrollY;
        window.location.hash = $(this).attr('href');
        setTimeout(() => window.scrollTo(0, scrollPos), 0);
    });
    
    // Check if we need to activate a specific settings tab based on hash
    function checkSettingsHash() {
        // Always start by checking if we're on the settings page first
        const isSettingsPage = window.location.pathname.includes('settings.php');
        if (!isSettingsPage) return;
        
        const hash = window.location.hash.substring(1) || 'edit-profile'; // default to edit-profile if no hash
        
        // Handle main settings tabs
        if (hash.startsWith('edit-profile')) {
            $settingsTabs.filter('[href="#edit-profile"]').click();
        } else if (hash.startsWith('account-security')) {
            $settingsTabs.filter('[href="#account-security"]').click();
            
            // Handle security sub-tabs
            const parts = hash.split('/');
            if (parts.length > 1) {
                const securityTab = parts[1];
                $(`.security-tab[href="#account-security/${securityTab}"]`).click();
            }
        } else {
            // Default to edit-profile if hash doesn't match any known tabs
            $settingsTabs.filter('[href="#edit-profile"]').click();
        }
    }
    
    // Initialize active states based on URL
    function initActiveStates() {
        // First check if we're on a settings page with security tabs
        checkSettingsHash();
        
        // Check regular menu items
        $menuItems.each(function() {
            const href = $(this).attr('href');
            if (isUrlMatch(href)) {
                setActiveState($menuItems, $(this));
            }
        });
        
        // Check if we're on a settings page
        let hasActiveSubmenu = false;
        
        // Check for stored active submenu item
        const storedActiveItem = localStorage.getItem('activeSubmenuItem');
        if (storedActiveItem) {
            const $activeItem = $submenuItems.filter(function() {
                return $(this).attr('href') === storedActiveItem;
            });
            
            if ($activeItem.length) {
                setActiveState($submenuItems, $activeItem);
                openSettingsSubmenu();
                hasActiveSubmenu = true;
            }
        }
        
        // Check if we have a hash with account-security
        if (isAccountSecurityPage()) {
            const $accountSecurityItem = $submenuItems.filter(function() {
                return $(this).attr('href').includes('account-security');
            });
            
            if ($accountSecurityItem.length) {
                setActiveState($submenuItems, $accountSecurityItem);
                openSettingsSubmenu();
                hasActiveSubmenu = true;
            }
        } 
        // Otherwise check for edit-profile or other submenu items
        else if (isSettingsPage()) {
        $submenuItems.each(function() {
            const href = $(this).attr('href');
            if (isUrlMatch(href)) {
                setActiveState($submenuItems, $(this));
                openSettingsSubmenu();
                hasActiveSubmenu = true;
            }
        });
        
            // Check user dropdown items for edit profile links
        $userDropdownItems.each(function() {
            const href = $(this).attr('href');
            if (isUrlMatch(href) && href && href.includes('edit-profile')) {
                activateEditProfileSubmenu();
            }
        });
        
            // Check if we're on edit profile page
        if (isEditProfilePage()) {
            activateEditProfileSubmenu();
            }
        }
        
        // Close submenu if it shouldn't be open
        if (!hasActiveSubmenu && !isSubmenuPage()) {
            closeSettingsSubmenu();
        }
    }
    
    // Check if we're on the settings page
    function isSettingsPage() {
        return window.location.pathname.includes('settings.php');
    }
    
    /**
     * Initialize event listeners
     */
    function setupEventListeners() {
        // Settings menu functionality
        $settingsMenu.on('click', handleSettingsClick);
        
        // Handle submenu item clicks
        $submenuItems.on('click', handleSubmenuItemClick);
        
        // Handle regular menu item clicks
        $menuItems.on('click', handleMenuItemClick);
        
        // User dropdown menu functionality
        $userMenu.on('click', handleUserMenuClick);
        
        // Handle user dropdown item clicks
        $userDropdownItems.on('click', handleUserDropdownItemClick);
        
        // Close dropdowns when clicking outside
        $(document).on('click', handleDocumentClick);
    }
    
    // Call init functions
        setupEventListeners();
        initActiveStates();
    initProfileImage();
});