$(document).ready(function() {
    // Filter dropdown toggle
    const $filterBtn = $('#filter-btn');
    const $filterDropdown = $('#filter-dropdown');
    const $filterContainer = $('#filter-container');
    
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
        const $tableRows = $('.tab-content.active tbody tr');
        
        if (filterValue === 'all') {
            $tableRows.show();
            return;
        }
        
        $tableRows.each(function() {
            const $statusCell = $(this).find('td.status-' + filterValue);
            if ($statusCell.length || filterValue === 'all') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }
    
    // Tab Dropdown Menu functionality
    const $tabDropdownBtn = $('#tab-dropdown-btn');
    const $tabDropdownMenu = $('#tab-dropdown-menu');
    const $currentTabText = $('#current-tab-text');
    
    if ($tabDropdownBtn.length && $tabDropdownMenu.length) {
        $tabDropdownBtn.on('click', function() {
            $tabDropdownMenu.toggleClass('show');
        });
        
        // Close the dropdown when clicking outside
        $(document).on('click', function(event) {
            if (!$(event.target).closest('.tab-dropdown-container').length) {
                $tabDropdownMenu.removeClass('show');
            }
        });
        
        // Tab options
        $('.tab-dropdown-item').on('click', function() {
            const tabValue = $(this).data('tab');
            
            // Hide all tab content
            $('.tab-content').removeClass('active');
            
            // Show selected tab content
            $('#' + tabValue).addClass('active');
            
            // Update dropdown button text
            $currentTabText.text($(this).text());
            
            // Hide dropdown menu
            $tabDropdownMenu.removeClass('show');
            
            // Show or hide filter container based on selected tab
            if (tabValue === 'articles') {
                $filterContainer.show();
            } else {
                $filterContainer.hide();
            }
        });
    }
    
    // Initially hide filter container if not on articles tab
    const currentTabValue = $currentTabText.text().toLowerCase();
    if (currentTabValue !== 'articles' && $filterContainer.length) {
        $filterContainer.hide();
    }
    
    // Search functionality
    const $searchInput = $('#search-input');
    if ($searchInput.length) {
        $searchInput.on('keyup', function() {
            const searchValue = $(this).val().toLowerCase();
            const $activeTable = $('.tab-content.active table');
            
            if ($activeTable.length) {
                const $tableRows = $activeTable.find('tbody tr');
                $tableRows.each(function() {
                    const text = $(this).text().toLowerCase();
                    if (text.includes(searchValue)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
    }
    
    // Article View Modal functionality
    const $articleViewModal = $('#articleViewModal');
    const $articleViewBtns = $('.view-btn');
    const $articleViewClose = $articleViewModal.find('.close');
    
    // Store the current article row being viewed
    let $currentArticleRow = null;
    
    if ($articleViewBtns.length > 0 && $articleViewModal.length) {
        $articleViewBtns.on('click', function(e) {
            e.preventDefault();
            const articleId = $(this).data('id');
            
            // Here you would fetch the article data based on the ID
            // For now, we'll just show the modal with sample data
            
            // Update the modal with relevant article data
            // This is just a placeholder - in a real implementation,
            // you'd update this with actual data from your backend
            const articleTitle = $(this).closest('tr').find('td:nth-child(2)').text();
            const articleAuthor = $(this).closest('tr').find('td:nth-child(3)').text();
            const articleCategory = $(this).closest('tr').find('td:nth-child(4)').text();
            const articleDate = $(this).closest('tr').find('td:nth-child(5)').text();
            const articleResult = $(this).closest('tr').find('td:nth-child(7)').text();
            const articleStatus = $(this).closest('tr').find('td:nth-child(8)').text();
            
            // Update modal content
            $articleViewModal.find('.article-title').text(articleTitle);
            $articleViewModal.find('.article-author').text(articleAuthor);
            $articleViewModal.find('.article-date').html(`<i class="far fa-calendar"></i> ${articleDate}`);
            
            // Set appropriate status class and text
            const $statusElement = $articleViewModal.find('.article-status');
            $statusElement.text(articleStatus);
            $statusElement.removeClass().addClass('article-status');
            
            if (articleStatus.toLowerCase().includes('pending')) {
                $statusElement.addClass('status-pending-label');
            } else if (articleStatus.toLowerCase().includes('legit')) {
                $statusElement.addClass('status-legit-label');
            } else if (articleStatus.toLowerCase().includes('fake')) {
                $statusElement.addClass('status-fake-label');
            }
            
            // Set result percentage (placeholder logic)
            let percentage = '75%';
            if (articleResult.toLowerCase() === 'legit') {
                percentage = '85%';
            } else if (articleResult.toLowerCase() === 'fake') {
                percentage = '20%';
            }
            
            $articleViewModal.find('.result-percentage').text(percentage);
            $articleViewModal.find('.result-text').text(articleResult);
            
            // Show the modal
            $articleViewModal.show();
            
            // Store reference to current article row
            $currentArticleRow = $(this).closest('tr');
        });
        
        // Close Article View modal
        $articleViewClose.on('click', function() {
            $articleViewModal.hide();
        });
        
        // Close modal when clicking outside
        $(window).on('click', function(event) {
            if (event.target === $articleViewModal[0]) {
                $articleViewModal.hide();
            }
        });
        
        // Handle approve and fake buttons
        const $approveBtn = $articleViewModal.find('.approve-btn');
        const $fakeBtn = $articleViewModal.find('.fake-btn');
        
        if ($approveBtn.length) {
            $approveBtn.on('click', function() {
                // Update modal status
                const $statusElement = $articleViewModal.find('.article-status');
                $statusElement.text('Legit');
                $statusElement.removeClass().addClass('article-status status-legit-label');
                
                // Update table row if available
                if ($currentArticleRow) {
                    // Update result cell
                    const $resultCell = $currentArticleRow.find('td:nth-child(7)');
                    if ($resultCell.length) $resultCell.text('Legit');
                    
                    // Update status cell
                    const $statusCell = $currentArticleRow.find('td:nth-child(8)');
                    if ($statusCell.length) {
                        $statusCell.text('Legit');
                        $statusCell.removeClass().addClass('status-legit');
                    }
                }
                
                // Close the modal after a short delay
                setTimeout(() => {
                    $articleViewModal.hide();
                }, 500);
            });
        }
        
        if ($fakeBtn.length) {
            $fakeBtn.on('click', function() {
                // Update modal status
                const $statusElement = $articleViewModal.find('.article-status');
                $statusElement.text('Fake');
                $statusElement.removeClass().addClass('article-status status-fake-label');
                
                // Update table row if available
                if ($currentArticleRow) {
                    // Update result cell
                    const $resultCell = $currentArticleRow.find('td:nth-child(7)');
                    if ($resultCell.length) $resultCell.text('Fake');
                    
                    // Update status cell
                    const $statusCell = $currentArticleRow.find('td:nth-child(8)');
                    if ($statusCell.length) {
                        $statusCell.text('Fake');
                        $statusCell.removeClass().addClass('status-fake');
                    }
                }
                
                // Close the modal after a short delay
                setTimeout(() => {
                    $articleViewModal.hide();
                }, 500);
            });
        }
    }
    
    // Modal functionality for Category
    const $categoryModal = $('#categoryModal');
    const $newCategoryBtn = $('#newCategoryBtn');
    const $categoryClose = $categoryModal.find('.close');
    
    if ($newCategoryBtn.length && $categoryModal.length) {
        $newCategoryBtn.on('click', function() {
            $categoryModal.show();
        });
        
        $categoryClose.on('click', function() {
            $categoryModal.hide();
        });
    }
    
    // Modal functionality for Keyword
    const $keywordModal = $('#keywordModal');
    const $newKeywordBtn = $('#newKeywordBtn');
    const $keywordClose = $keywordModal.find('.close');
    
    if ($newKeywordBtn.length && $keywordModal.length) {
        $newKeywordBtn.on('click', function() {
            $keywordModal.show();
        });
        
        $keywordClose.on('click', function() {
            $keywordModal.hide();
        });
    }
    
    // Close modals when clicking outside
    $(window).on('click', function(event) {
        if (event.target === $categoryModal[0]) {
            $categoryModal.hide();
        }
        if (event.target === $keywordModal[0]) {
            $keywordModal.hide();
        }
    });
    
    // Form submission (prevent default for demo)
    const $categoryForm = $('#categoryForm');
    if ($categoryForm.length) {
        $categoryForm.on('submit', function(e) {
            e.preventDefault();
            // Here you would add code to handle the actual submission
            $categoryModal.hide();
        });
    }
    
    const $keywordForm = $('#keywordForm');
    if ($keywordForm.length) {
        $keywordForm.on('submit', function(e) {
            e.preventDefault();
            // Here you would add code to handle the actual submission
            $keywordModal.hide();
        });
    }
    
    // Pagination
    const $paginationLinks = $('.pagination a:not(.prev):not(.next)');
    if ($paginationLinks.length > 0) {
        $paginationLinks.on('click', function(e) {
            e.preventDefault();
            $('.pagination a').removeClass('active');
            $(this).addClass('active');
            
            // Here would go actual pagination logic if needed
        });
    }
});
