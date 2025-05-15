/**
 * Simple Notification System using jQuery
 */
const NotificationSystem = {
    /**
     * Show a notification
     * @param {string} message - The notification message 
     * @param {string} type - The notification type (success, error, info)
     * @param {number} duration - Duration to show in milliseconds
     */
    show: function(message, type = 'success', duration = 3000) {
        // Remove any existing notifications
        this.hideAll();
        
        // Create notification element using jQuery
        const notification = $('<div></div>')
            .addClass(`notification ${type}`)
            .text(message);
        
        // Add to DOM
        $('body').append(notification);
        
        // Trigger animation using jQuery's queue
        notification
            .delay(10)
            .queue(function(next) {
                $(this).addClass('show');
                next();
            })
            .delay(duration)
            .queue(function(next) {
                NotificationSystem.hide($(this));
                next();
            });
        
        return notification;
    },
    
    /**
     * Hide a specific notification
     * @param {jQuery} notification - The notification jQuery element to hide
     */
    hide: function(notification) {
        notification.removeClass('show');
        
        // Remove from DOM after animation completes using jQuery's delay
        notification
            .delay(300)
            .queue(function(next) {
                $(this).remove();
                next();
            });
    },
    
    /**
     * Hide all notifications
     */
    hideAll: function() {
        $('.notification').each(function() {
            NotificationSystem.hide($(this));
        });
    },
    
    /**
     * Show a success notification
     * @param {string} message - The notification message
     * @param {number} duration - Duration to show in milliseconds
     */
    success: function(message, duration = 3000) {
        return this.show(message, 'success', duration);
    },
    
    /**
     * Show an error notification
     * @param {string} message - The notification message
     * @param {number} duration - Duration to show in milliseconds
     */
    error: function(message, duration = 4000) {
        return this.show(message, 'error', duration);
    },
    
    /**
     * Show an info notification
     * @param {string} message - The notification message
     * @param {number} duration - Duration to show in milliseconds
     */
    info: function(message, duration = 3000) {
        return this.show(message, 'info', duration);
    }
};