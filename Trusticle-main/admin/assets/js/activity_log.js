$(document).ready(function() {
    // Filter dropdown toggle
    const $filterBtn = $('#filter-btn');
    const $filterDropdown = $('#filter-dropdown');
    
    if ($filterBtn.length && $filterDropdown.length) {
        $filterBtn.on('click', function() {
            $filterDropdown.toggleClass('show');
        });
        
        // Close the dropdown when clicking outside
        $(document).on('click', function(event) {
            if (!$(event.target).closest('.filter-container').length) {
                $filterDropdown.removeClass('show');
            }
        });
        
        // Filter options
        $('.filter-option').on('click', function() {
            const filterValue = $(this).data('filter');
            filterTable(filterValue);
            
            // Update button text
            $filterBtn.find('span').text($(this).text());
            $filterDropdown.removeClass('show');
        });
    }
    
    // Function to filter table rows
    function filterTable(filterValue) {
        $('.table-container tbody tr').each(function() {
            const $roleCell = $(this).find('td:nth-child(3)');
            if ($roleCell.length) {
                const role = $roleCell.text().trim().toLowerCase();
                
                if (filterValue === 'all' || role === filterValue.toLowerCase()) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            }
        });
    }
    
    // Search functionality
    const $searchInput = $('#search-input');
    if ($searchInput.length) {
        $searchInput.on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            const $tableRows = $('.table-container tbody tr');
            
            $tableRows.each(function() {
                const text = $(this).text().toLowerCase();
                // Get current filter state
                const currentFilter = $('#filter-btn span').text().trim().toLowerCase();
                const $roleCell = $(this).find('td:nth-child(3)');
                const role = $roleCell.length ? $roleCell.text().trim().toLowerCase() : '';
                
                // Check if matches both search term and current filter
                const matchesSearch = text.includes(searchTerm);
                const matchesFilter = currentFilter === 'all' || role === currentFilter;
                
                if (matchesSearch && matchesFilter) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    }
    
    // Delete activity log entry
    $(document).on('click', '.delete-log', function() {
        if (confirm('Are you sure you want to delete this activity log entry?')) {
            const logId = $(this).data('id');
            
            $.ajax({
                url: '../controllers/activity_log_controller.php',
                type: 'POST',
                data: {
                    action: 'delete_log',
                    log_id: logId
                },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        // Remove the row from the table
                        $('.table-container tbody tr').each(function() {
                            if ($(this).find('td:first').text() == logId) {
                                $(this).remove();
                            }
                        });
                    } else {
                        alert('Error: ' + result.message);
                    }
                },
                error: function() {
                    alert('An error occurred while trying to delete the log entry.');
                }
            });
        }
    });
    
    // Tab switching for settings page
    const $tabs = $('.tab');
    if ($tabs.length > 0) {
        $tabs.on('click', function() {
            // Remove active class from all tabs and content
            $('.tab').removeClass('active');
            $('.tab-content').removeClass('active');
            
            // Add active class to clicked tab
            $(this).addClass('active');
            
            // Show corresponding content
            const contentId = $(this).data('tab');
            $('#' + contentId).addClass('active');
        });
    }
    
    // Password visibility toggle
    const $passwordToggles = $('.password-toggle');
    if ($passwordToggles.length > 0) {
        $passwordToggles.on('click', function() {
            const $passwordField = $(this).prev();
            const type = $passwordField.attr('type');
            
            if (type === 'password') {
                $passwordField.attr('type', 'text');
                $(this).html('<i class="fas fa-eye-slash"></i>');
            } else {
                $passwordField.attr('type', 'password');
                $(this).html('<i class="fas fa-eye"></i>');
            }
        });
    }
    
    // Pagination
    const $paginationBtns = $('.pagination-btn');
    if ($paginationBtns.length > 0) {
        $paginationBtns.on('click', function() {
            $('.pagination-btn').removeClass('active');
            $(this).addClass('active');
            
            // Here would go actual pagination logic if needed
        });
    }
});