$(document).ready(function() {
    // Cache DOM elements - improves performance and readability
    const $settingsMenu = $('#settingsMenu');
    const $settingsSubmenu = $('#settingsSubmenu');
    const $settingsArrow = $settingsMenu.find('.settings-arrow');
    const $userMenu = $('.user-menu');
    const $userDropdown = $('.user-dropdown');
    const $submenuItems = $('.submenu-item');
    const $userDropdownItems = $('.dropdown-item');
    
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
    
    function navigateTo(href, delay = 10) {
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
        
        // Create URL objects for comparison
        const currentUrl = new URL(currentPath + currentHash, window.location.origin);
        const targetUrl = new URL(href, window.location.origin);
        
        // Check if paths match or if target path is part of current path
        return currentUrl.pathname === targetUrl.pathname || 
               currentPath.includes(targetUrl.pathname.split('/').pop());
    }

    function isSubmenuPage() {
        return $submenuItems.toArray().some(item => 
            isUrlMatch($(item).attr('href'))
        );
    }
    
    function isEditProfilePage() {
        const currentPath = window.location.pathname;
        const currentHash = window.location.hash;
        return currentPath.includes('/view/edit-profile') || currentHash.includes('edit-profile');
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
        
        // Set active state for the clicked submenu item
        setActiveState($submenuItems, $this);
        openSettingsSubmenu();
        
        // If this is the account-security tab, ensure it's highlighted
        if (href && href.includes('account-security')) {
            $('.submenu-item').removeClass(ACTIVE_CLASS);
            $(`.submenu-item[href*="account-security"]`).addClass(ACTIVE_CLASS);
        }
        
        navigateTo(href);
    }
    
    function handleUserMenuClick(e) {
        e.stopPropagation();
        $userDropdown.toggleClass(ACTIVE_CLASS);
    }
    
    function handleUserDropdownItemClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const href = $(this).attr('href');
        
        // Always close the dropdown menu when an item is clicked
        $userDropdown.removeClass(ACTIVE_CLASS);
        
        // If edit profile is clicked, also activate the corresponding submenu item
        if (href && href.includes('edit-profile')) {
            activateEditProfileSubmenu();
        }
        
        navigateTo(href);
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
    
    function initActiveStates() {
        // Check submenu items for active state
        let hasActiveSubmenu = false;
        
        // Check if we're on a settings page with a hash
        const currentHash = window.location.hash;
        
        // If we have a hash that includes account-security, activate that tab
        if (currentHash && currentHash.includes('account-security')) {
            const $accountSecurityItem = $submenuItems.filter(function() {
                return $(this).attr('href').includes('account-security');
            });
            
            if ($accountSecurityItem.length) {
                setActiveState($submenuItems, $accountSecurityItem);
                openSettingsSubmenu();
                hasActiveSubmenu = true;
            }
        } 
        // Otherwise check each submenu item normally
        else {
            $submenuItems.each(function() {
                const href = $(this).attr('href');
                if (isUrlMatch(href)) {
                    setActiveState($submenuItems, $(this));
                    openSettingsSubmenu();
                    hasActiveSubmenu = true;
                }
            });
            
            // Check user dropdown items for active state
            $userDropdownItems.each(function() {
                const href = $(this).attr('href');
                if (isUrlMatch(href) && href && href.includes('edit-profile')) {
                    activateEditProfileSubmenu();
                }
            });
            
            // Ensure edit-profile pages have their submenu item highlighted
            // Only do this if we're not on the account-security page
            if (isEditProfilePage() && !currentHash.includes('account-security')) {
                activateEditProfileSubmenu();
            }
        }
        
        // Handle page load - check if we should open/close submenu
        if (!hasActiveSubmenu && !isSubmenuPage()) {
            closeSettingsSubmenu();
        }
    }
    
    /**
     * Initialize event listeners
     */
    function setupEventListeners() {
        // Settings menu click
        $settingsMenu.on('click', handleSettingsClick);
        
        // Submenu item click
        $submenuItems.on('click', handleSubmenuItemClick);
        
        // User menu click
        $userMenu.on('click', handleUserMenuClick);
        
        // User dropdown item click
        $userDropdownItems.on('click', handleUserDropdownItemClick);
        
        // Document click - close menus
        $(document).on('click', handleDocumentClick);
    }
    
    /**
     * Initialization
     */
    function init() {
        setupEventListeners();
        initActiveStates();
    }
    
    // Profile image initialization from sessionStorage
    function initProfileImage() {
        const storedProfileImageUrl = sessionStorage.getItem('profileImageUrl');
        if (storedProfileImageUrl) {
            console.log('Sidebar init - found stored profile image URL:', storedProfileImageUrl);
            
            // Add timestamp to prevent caching
            const cacheBustUrl = storedProfileImageUrl + '?v=' + new Date().getTime();
            
            // Update sidebar profile image
            $('.sidebar .profile-image').attr('src', cacheBustUrl);
        }
    }
    
    // Initialize profile image when document is ready
    initProfileImage();
    
    // Start the app
    init();
});