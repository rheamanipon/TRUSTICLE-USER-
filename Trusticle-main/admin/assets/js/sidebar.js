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
        return currentPath.includes('edit-profile') || currentHash.includes('edit-profile');
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
        
        setActiveState($submenuItems, $this);
        openSettingsSubmenu();
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
        if (isEditProfilePage()) {
            activateEditProfileSubmenu();
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
        // Settings menu functionality
        $settingsMenu.on('click', handleSettingsClick);
        
        // Handle submenu item clicks
        $submenuItems.on('click', handleSubmenuItemClick);
        
        // User dropdown menu functionality
        $userMenu.on('click', handleUserMenuClick);
        
        // Handle user dropdown item clicks
        $userDropdownItems.on('click', handleUserDropdownItemClick);
        
        // Close dropdowns when clicking outside
        $(document).on('click', handleDocumentClick);
    }
    
    /**
     * Initialization
     */
    function init() {
        setupEventListeners();
        initActiveStates();
    }
    
    // Start the app
    init();
});