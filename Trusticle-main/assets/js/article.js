$(document).ready(function() {
    // Global variables
    let currentArticleId = null;
    let articleOperation = 'create'; // 'create' or 'update'
    let lastSearchTimeout = null;
    
    // Disable jQuery UI datepicker tooltips
    $.datepicker._generateHTML = function(inst) {
        var orig = $.datepicker._generateHTML;
        return function() {
            var html = orig.apply(this, arguments);
            html = html.replace(/title="[^"]*"/g, '');
            return html;
        };
    }();
    
    // Initialize datepicker with tooltip removal
    $('input[name="date"]').datepicker({
        dateFormat: 'mm/dd/yy',
        changeMonth: true,
        changeYear: true,
        yearRange: '2000:+0',
        maxDate: '+0d',
        showAnim: 'fadeIn',
        beforeShow: function(input, inst) {
            inst.dpDiv.css({
                marginTop: '10px',
                zIndex: 2000
            });
        },
        onSelect: function(dateText, inst) {
            // Remove tooltips after date selection
            $('.ui-datepicker-calendar th, .ui-datepicker-calendar td, .ui-datepicker-calendar a').removeAttr('title');
        }
    }).on('focus', function() {
        $(this).blur();
    });
    
    // Remove tooltips when modal is shown
    $('#modalOverlay').on('shown', function() {
        $('.ui-datepicker-calendar th, .ui-datepicker-calendar td, .ui-datepicker-calendar a').removeAttr('title');
    });
    
    // Remove tooltips when datepicker is shown
    $(document).on('click', 'input[name="date"]', function() {
        setTimeout(function() {
            $('.ui-datepicker-calendar th, .ui-datepicker-calendar td, .ui-datepicker-calendar a').removeAttr('title');
        }, 0);
    });
    
    // Remove tooltips when month/year changes
    $(document).on('click', '.ui-datepicker-prev, .ui-datepicker-next, .ui-datepicker-month, .ui-datepicker-year', function() {
        setTimeout(function() {
            $('.ui-datepicker-calendar th, .ui-datepicker-calendar td, .ui-datepicker-calendar a').removeAttr('title');
        }, 0);
    });
    
    // Remove tooltips from datepicker cells
    $(document).on('mouseover', '.ui-datepicker-calendar th, .ui-datepicker-calendar td, .ui-datepicker-calendar a', function() {
        $(this).removeAttr('title');
    });
    
    // Remove tooltips from entire datepicker
    $(document).on('mouseover', '.ui-datepicker', function() {
        $('.ui-datepicker-calendar th, .ui-datepicker-calendar td, .ui-datepicker-calendar a').removeAttr('title');
    });
    
    // Disable jQuery UI tooltips globally
    $.fn.tooltip = function() {
        return this;
    };
    
    // Remove tooltips from all elements
    $(document).on('mouseover', '*', function() {
        $(this).removeAttr('title');
    });
    
    // Load articles on page load - detect what page we're on
    const isCommunityPage = window.location.pathname.includes('/view/community_articles.php');
    const isMyArticlesPage = window.location.pathname.includes('/view/my_articles.php');
    const isMainPage = window.location.pathname.includes('/view/article_main.php');
    
    console.log('Page detection:', {
        isCommunityPage: isCommunityPage,
        isMyArticlesPage: isMyArticlesPage,
        isMainPage: isMainPage,
        path: window.location.pathname
    });
    
    // Check for filter in URL before loading articles
    const urlParams = new URLSearchParams(window.location.search);
    const filterParam = urlParams.get('filter');
    
    // Remove the exclusion for my_articles.php page - load articles on all pages
    console.log('Auto-loading articles with default parameters');
    loadArticles();
    
    // If on main page, also load community articles
    if (isMainPage) {
        console.log('Loading community articles for main page');
        loadCommunityArticles();
    }
    
    // Filter articles by status
    $('.filter-item').on('click', function(e) {
        e.preventDefault();
        const status = $(this).data('status') || 'all';
        const search = $('.search-input').val().trim();
        
        console.log('Filter clicked:', {
            status: status,
            search: search
        });
        
        // Check if we're on the main page
        const isMainPage = window.location.pathname.includes('/view/article_main.php');
        const isCommunityPage = window.location.pathname.includes('/view/community_articles.php');
        
        // Update UI to show selected filter
        $('.filter-item').removeClass('active');
        $(this).addClass('active');
        
        // Update articles with filter
        loadArticles(status, search);
        
        // If on main page, also update community articles
        if (isMainPage) {
            loadCommunityArticles(search, status);
        }
        
        // Close the dropdown
        $('.filter-dropdown').removeClass('show');
    });
    
    // Real-time search functionality
    $('.search-input').on('input', function() {
        // Clear any existing timeout
        if (lastSearchTimeout) {
            clearTimeout(lastSearchTimeout);
        }
        
        // Set a new timeout to prevent too many requests
        lastSearchTimeout = setTimeout(function() {
            const searchText = $('.search-input').val().trim();
            const status = $('.filter-item.active').data('status') || 'all';
            
            console.log('Search input handler:', {
                searchText: searchText,
                status: status,
                pathname: window.location.pathname
            });
            
            // Reset pagination when searching
            $('#pagination').empty();
            
            // Check if we're on the main page
            const isMainPage = window.location.pathname.includes('/view/article_main.php');
            const isCommunityPage = window.location.pathname.includes('/view/community_articles.php');
            
            // Load articles with search text
            loadArticles(status, searchText, 1);
            
            // If on main page, also update community articles
            if (isMainPage) {
                loadCommunityArticles(searchText, status);
            }
        }, 300); // 300ms delay for typing
    });
    
    // Search button click
    $('.search-button').on('click', function() {
        const searchText = $('.search-input').val().trim();
        const status = $('.filter-item.active').data('status') || 'all';
        
        console.log('Search button clicked:', {
            searchText: searchText,
            status: status
        });
        
        // Check if we're on the main page
        const isMainPage = window.location.pathname.includes('/view/article_main.php');
        const isCommunityPage = window.location.pathname.includes('/view/community_articles.php');
        
        // Load articles with search text
        loadArticles(status, searchText, 1);
        
        // If on main page, also update community articles
        if (isMainPage) {
            loadCommunityArticles(searchText, status);
        }
    });
    
    // Enter key on search input
    $('.search-input').on('keypress', function(e) {
        if (e.which === 13) {
            const searchText = $(this).val().trim();
            const status = $('.filter-item.active').data('status') || 'all';
            
            console.log('Enter key pressed in search:', {
                searchText: searchText,
                status: status
            });
            
            // Check if we're on the main page
            const isMainPage = window.location.pathname.includes('/view/article_main.php');
            const isCommunityPage = window.location.pathname.includes('/view/community_articles.php');
            
            // Load articles with search text
            loadArticles(status, searchText, 1);
            
            // If on main page, also update community articles
            if (isMainPage) {
                loadCommunityArticles(searchText, status);
            }
        }
    });
    
    // Toggle filter dropdown
    $('.filter-button').on('click', function() {
        $('.filter-dropdown').toggleClass('show');
    });
    
    // Close dropdowns when clicking elsewhere
    $(document).on('click', function(e) {
        // Close filter dropdown if clicking outside it
        if (!$(e.target).closest('.filter-wrapper').length) {
            $('.filter-dropdown').removeClass('show');
        }
        
        // Close article menu dropdowns
        if (!$(e.target).hasClass('fa-ellipsis-h') && !$(e.target).closest('.dropdown-menu').length) {
            $('.dropdown-menu').removeClass('show');
        }
    });
    
    // Modal close and cancel buttons
    $('#closeModalBtn, #cancelBtn').on('click', function() {
        $('#modalOverlay').addClass('hidden');
        resetArticleForm();
    });
    
    // Close article details modal
    $('#closeArticleDetailsBtn').on('click', function() {
        $('#articleDetailsModal').addClass('hidden');
        
        // Check if we should restore a previous filter state
        const savedFilter = sessionStorage.getItem('lastArticleFilter');
        if (savedFilter) {
            console.log("Restoring previous filter state:", savedFilter);
            
            // If we're on the my_articles page, ensure the filter is applied when returning
            if (window.location.pathname.includes('/view/my_articles.php')) {
                // Update the active filter in the UI
                $('.filter-item').removeClass('active');
                $(`.filter-item[data-status="${savedFilter}"]`).addClass('active');
                
                // Check if the URL needs updating
                const urlParams = new URLSearchParams(window.location.search);
                const currentFilter = urlParams.get('filter');
                
                if (currentFilter !== savedFilter) {
                    // Update URL if needed
                    const newUrl = new URL(window.location.href);
                    newUrl.searchParams.set('filter', savedFilter);
                    window.history.pushState({}, '', newUrl.toString());
                    
                    // Reload articles with the saved filter
                    loadArticles(savedFilter, '', 1);
                }
            }
        }
    });
    
    // Toggle article menu
    $(document).on('click', '.article-menu i', function(e) {
        e.stopPropagation();
        $(this).siblings('.dropdown-menu').toggleClass('show');
    });
    
    // Custom datepicker for article modal
    function initArticleDatepicker() {
        $('input[name="date"]').datepicker('destroy').datepicker({
            dateFormat: 'mm/dd/yy',
            changeMonth: true,
            changeYear: true,
            yearRange: '2000:+0',
            maxDate: '+0d',
            showAnim: 'fadeIn',
            beforeShow: function(input, inst) {
                inst.dpDiv.css({
                    marginTop: '10px',
                    zIndex: 2000
                });
            }
        }).on('focus', function() {
            $(this).blur();
        });

        // Remove all tooltips from the datepicker
        $('.ui-datepicker').find('th, td, a').removeAttr('title');
    }

    // Initialize datepicker when modal is shown
    $('#modalOverlay').on('shown', function() {
        initArticleDatepicker();
    });

    // Reinitialize datepicker when month/year changes
    $(document).on('click', '.ui-datepicker-prev, .ui-datepicker-next, .ui-datepicker-month, .ui-datepicker-year', function() {
        setTimeout(initArticleDatepicker, 0);
    });

    // Show modal for adding a new article
    $('#addArticleBtn').on('click', function() {
        articleOperation = 'create';
        resetArticleForm();
        $('.modal-title').text('Post an Article');
        $('#modalOverlay').removeClass('hidden');
        initArticleDatepicker();
    });
    
    // Handle edit article
    $(document).on('click', '.edit-article', function(e) {
        e.preventDefault();
        const articleId = $(this).data('id');
        articleOperation = 'update';
        currentArticleId = articleId;
        
        // Get article data and populate form
        $.ajax({
            url: '../../process/article_process.php',
            type: 'POST',
            data: {
                action: 'get_single',
                article_id: articleId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const article = response.article;
                    
                    // Set form title
                    $('.modal-title').text('Edit Article');
                    
                    // Populate form
                    $('input[name="title"]').val(article.title);
                    $('select[name="category"]').val(article.category_id);
                    $('input[name="source_url"]').val(article.source_url);
                    
                    // Format date from YYYY-MM-DD to MM/DD/YYYY for datepicker
                    if (article.date_published) {
                        const dateParts = article.date_published.split('-');
                        const formattedDate = `${dateParts[1]}/${dateParts[2]}/${dateParts[0]}`;
                        $('input[name="date"]').datepicker('setDate', formattedDate);
                    } else {
                        $('input[name="date"]').datepicker('setDate', null);
                    }
                    
                    $('textarea[name="content"]').val(article.content);
                    
                    // Show modal
                    $('#modalOverlay').removeClass('hidden');
                    initArticleDatepicker();
                    
                    // Add article ID to form
                    $('form.modal-form').data('article-id', article.id);
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Error loading article data', 'error');
            }
        });
    });
    
    // Handle delete article
    $(document).on('click', '.delete-article', function(e) {
        e.preventDefault();
        const articleId = $(this).data('id');
        
        showConfirmDialog(
            'Delete Article',
            'Are you sure you want to delete this article? This action cannot be undone.',
            function() {
                $.ajax({
                    url: '../../process/article_process.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        article_id: articleId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification(response.message, 'success');
                            loadArticles(); // Reload articles
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('Error deleting article', 'error');
                    }
                });
            }
        );
    });
    
    // View article details
    $(document).on('click', '.read-more, .article-card', function(e) {
        e.preventDefault();
        if ($(e.target).hasClass('edit-article') || $(e.target).hasClass('delete-article') || 
            $(e.target).closest('.article-menu').length) {
            return; // Don't open article if clicking on edit/delete or menu
        }
        
        const articleId = $(this).data('id');
        currentArticleId = articleId; // Set the current article ID
        
        // Store current filter state before opening article details
        const currentUrlParams = new URLSearchParams(window.location.search);
        const currentFilter = currentUrlParams.get('filter');
        if (currentFilter) {
            // Save to sessionStorage so it can be restored when returning to articles
            sessionStorage.setItem('lastArticleFilter', currentFilter);
            console.log("Saved current filter state:", currentFilter);
        }
        
        // Show loading state in modal
        $('#articleModalTitle').text('Loading...');
        $('#articleModalContent').html('<div class="loading">Loading article content...</div>');
        $('#articleDetailsModal').removeClass('hidden');
        
        // Make AJAX request to get article details
        $.ajax({
            url: '../../process/article_process.php',
            type: 'POST',
            data: {
                action: 'get_article_details',
                article_id: articleId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const article = response.article;
                    
                    // Update article title
                    $('#articleModalTitle').text(article.title);
                    
                    // Update article metadata
                    $('#articleModalAuthor span').text(article.username || 'Unknown');
                    $('#articleModalCategory span').text(article.category_name);
                    
                    // Format date
                    if (article.date_published) {
                        const date = new Date(article.date_published);
                        const options = { year: 'numeric', month: 'long', day: 'numeric' };
                        $('#articleModalDate span').text(date.toLocaleDateString('en-US', options));
                    } else {
                        $('#articleModalDate span').text('N/A');
                    }
                    
                    // Update status badge
                    const statusClass = article.status === 'legit' ? 'status-legit' : 
                                       (article.status === 'fake' ? 'status-fake' : 'status-pending');
                    
                    const statusText = article.status === 'pending' ? 'Pending – Waiting for Admin Review' : 
                                      capitalizeFirstLetter(article.status);
                    
                    $('#articleModalStatus').attr('class', 'article-status ' + statusClass).text(statusText);
                    
                    // Update fakeness score and keyword information
                    $('#articleModalScore').text(article.detection_score);
                    $('#articleModalKeywordCount').text(response.keywords.match_count);
                    $('#articleModalScoreIndicator').css('left', article.detection_score + '%').attr('data-score', article.detection_score + '%');
                    
                    // Update the prediction label based on fakeness score
                    let predictionClass = '';
                    let predictionText = '';
                    
                    // Determine the prediction based on score ranges
                    if (article.detection_score) {
                        if (article.detection_score < 25) {
                            predictionClass = 'status-legit';
                            predictionText = 'Likely Legitimate';
                        } else if (article.detection_score < 50) {
                            predictionClass = 'status-suspicious';
                            predictionText = 'Suspicious';
                        } else {
                            predictionClass = 'status-fake';
                            predictionText = 'Likely Fake';
                        }
                    } else {
                        // Default if no score is available
                        predictionClass = 'status-legit';
                        predictionText = 'Likely Legitimate';
                    }
                    
                    $('#articlePredictionLabel').attr('class', 'status-badge ' + predictionClass).text(predictionText);
                    
                    // Update content without tooltips
                    let content = response.keywords.highlighted_content;
                    content = content.replace(/title="[^"]*"/g, ''); // Remove all title attributes
                    $('#articleModalContent').html(content);
                    
                    // Display source URL if available
                    if (article.source_url) {
                        $('#articleModalSource').html(`
                            <div class="source-info">
                                <h4>Source:</h4>
                                <a href="${article.source_url}" target="_blank" class="source-link">
                                    ${article.source_url}
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        `);
                    } else {
                        $('#articleModalSource').empty();
                    }
                    
                    // Set edit/delete buttons visibility based on ownership
                    if (article.is_owner) {
                        $('#editArticleModalBtn, #deleteArticleModalBtn').show();
                        $('#editArticleModalBtn').data('id', article.id);
                        $('#deleteArticleModalBtn').data('id', article.id);
                    } else {
                        $('#editArticleModalBtn, #deleteArticleModalBtn').hide();
                    }
                    
                    // Load comments for this article
                    loadComments(articleId);
                } else {
                    $('#articleModalContent').html('<div class="error">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#articleModalContent').html('<div class="error">Error loading article content</div>');
            }
        });
    });
    
    // Handle edit button in article details modal
    $('#editArticleModalBtn').on('click', function() {
        const articleId = $(this).data('id');
        
        // Hide the details modal
        $('#articleDetailsModal').addClass('hidden');
        
        // Trigger the edit function
        $('.edit-article[data-id="' + articleId + '"]').trigger('click');
    });
    
    // Handle delete button in article details modal
    $('#deleteArticleModalBtn').on('click', function() {
        const articleId = $(this).data('id');
        
        // Hide the details modal
        $('#articleDetailsModal').addClass('hidden');
        
        // Show delete confirmation dialog
        showConfirmDialog(
            'Delete Article',
            'Are you sure you want to delete this article? This action cannot be undone.',
            function() {
                $.ajax({
                    url: '../../process/article_process.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        article_id: articleId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification(response.message, 'success');
                            loadArticles(); // Reload articles
                        } else {
                            showNotification(response.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('Error deleting article', 'error');
                    }
                });
            }
        );
    });
    
    // Function to load articles with pagination - defined in global scope so it can be called from other scripts
    function loadArticles(status = 'all', search = '', page = 1) {
        console.log("loadArticles called with status:", status, "search:", search, "page:", page);
        
        const container = $('.article-listings');
        container.html('<div class="loading">Loading articles...</div>');
        
        // Validate status parameter to ensure it's one of the allowed values
        const allowedStatuses = ['all', 'pending', 'legit', 'fake'];
        if (!allowedStatuses.includes(status)) {
            console.error('Invalid status parameter:', status);
            status = 'all'; // Default to 'all' if invalid
        }
        
        // Log the actual status being used for debugging
        console.log('Using validated status parameter:', status);
        
        // Determine which endpoint to use based on the current page
        const isCommunityPage = window.location.pathname.includes('/view/community_articles.php');
        const isMyArticlesPage = window.location.pathname.includes('/view/my_articles.php');
        
        // Choose the appropriate action based on the page
        let action;
        if (isCommunityPage) {
            action = 'read_community';
        } else if (isMyArticlesPage) {
            action = 'read_paginated';
        } else {
            action = 'read';
        }
        
        // Debug information
        console.log('Loading articles with params:', {
            action: action,
            status: status,
            search: search,
            page: page,
            isCommunityPage: isCommunityPage,
            isMyArticlesPage: isMyArticlesPage
        });
        
        // Create request data object
        const requestData = {
            action: action,
            status: status,
            search: search,
            page: page,
            limit: 10 // Always use 10 items per page for full listings
        };
        
        // Set a timeout to detect stuck requests
        const requestTimeout = setTimeout(function() {
            console.warn('Articles request taking too long');
            container.html('<div class="error">Request timeout. Try refreshing the page.</div>');
        }, 10000); // 10 second timeout
        
        // Use fetch API instead of jQuery AJAX for better error handling
        fetch('../../process/article_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(requestData)
        })
        .then(response => {
            clearTimeout(requestTimeout);
            
            // Check if the response is valid JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // If not JSON, get the text and log it for debugging
                return response.text().then(text => {
                    console.error('Server returned non-JSON response:', text);
                    throw new Error('Server returned non-JSON response');
                });
            }
        })
        .then(data => {
            console.log('Articles response:', data);
            
            if (data && data.success === true) {
                if (data.articles && data.articles.length > 0) {
                    // Log status of returned articles for debugging
                    const statusCounts = data.articles.reduce((counts, article) => {
                        counts[article.status] = (counts[article.status] || 0) + 1;
                        return counts;
                    }, {});
                    
                    console.log('Article filter results:', {
                        requestedStatus: status,
                        totalReturned: data.articles.length,
                        statusBreakdown: statusCounts
                    });
                    
                    // If we requested a specific status but got mixed results, warn about it
                    if (status !== 'all' && Object.keys(statusCounts).length > 1) {
                        console.warn('Warning: Received mixed statuses despite filtering for:', status);
                    }
                    
                    // Apply client-side filtering for specific statuses
                    let articlesToDisplay;
                    
                    if (status !== 'all') {
                        // Strict client-side filtering - only show articles matching the exact status
                        articlesToDisplay = data.articles.filter(article => article.status === status);
                        console.log(`Client-side filtering: reduced from ${data.articles.length} to ${articlesToDisplay.length} articles with status "${status}"`);
                        
                        // If we got no results after filtering, show a message
                        if (articlesToDisplay.length === 0) {
                            container.html(`<div class="no-articles">No "${status}" articles found.</div>`);
                            return;
                        }
                    } else {
                        // For 'all' status, show all articles
                        articlesToDisplay = data.articles;
                    }
                    
                    // Display the filtered articles
                    displayArticles(articlesToDisplay);
                    
                    // Check if we have pagination data and need to display pagination
                    // For my_articles.php and community_articles.php pages
                    const needsPagination = window.location.pathname.includes('/view/my_articles.php') || 
                                           window.location.pathname.includes('/view/community_articles.php');
                    
                    if (data.total_pages && needsPagination) {
                        displayPagination(data.current_page, data.total_pages);
                    } else if (data.total_pages && action === 'read_paginated') {
                        // Fallback for any other page using the paginated endpoint
                        displayPagination(data.current_page, data.total_pages);
                    }
                } else {
                    container.html('<div class="no-articles">No articles found.</div>');
                }
            } else {
                const errorMsg = data && data.message ? data.message : 'Unknown error';
                container.html('<div class="error">Error: ' + errorMsg + '</div>');
                console.error('Error loading articles:', data);
            }
        })
        .catch(error => {
            clearTimeout(requestTimeout);
            console.error('Fetch Error:', error);
            container.html('<div class="error">Error loading articles. Please check the console for details and try again.</div>');
        });
    }
    
    // Display articles in the UI - Global function that can be called from any page
    function displayArticles(articles) {
        let html = '';
        
        articles.forEach(function(article) {
            // Format the date
            let formattedDate = article.time_ago || 'No date';
            if (article.date_published && !article.time_ago) {
                const date = new Date(article.date_published);
                const options = { year: 'numeric', month: 'short', day: 'numeric' };
                formattedDate = date.toLocaleDateString('en-US', options);
            }
            
            // Determine status class
            const statusClass = article.status === 'legit' ? 'status-legit' : 
                              (article.status === 'fake' ? 'status-fake' : 'status-pending');
            
            // Show edit/delete menu only if user owns the article
            const menuHtml = article.is_owner !== false ? `
                    <div class="article-menu">
                        <i class="fas fa-ellipsis-h"></i>
                        <div class="dropdown-menu">
                            <a href="#" class="edit-article" data-id="${article.id}">Edit</a>
                            <a href="#" class="delete-article" data-id="${article.id}">Delete</a>
                        </div>
                    </div>
            ` : '';
            
            // Show author name for community articles
            const authorHtml = article.username && article.is_owner === false ? 
                `<p class="article-author">By ${article.username}</p>` : '';
            
            html += `
                <div class="article-card" data-id="${article.id}">
                    ${menuHtml}
                    
                    <h3 class="article-title">${article.title}</h3>
                    ${authorHtml}
                    <p class="article-excerpt">${article.excerpt}</p>
                    <a href="#" class="read-more" data-id="${article.id}">Read More...</a>
                    
                    <div class="article-footer">
                        <div class="article-stats">
                            <div class="stat-item">
                                <i class="far fa-folder"></i>
                                <span>${article.category_name || 'Uncategorized'}</span>
                            </div>
                            <div class="stat-item">
                                <i class="far fa-clock"></i>
                                <span>${formattedDate}</span>
                            </div>
                        </div>
                        
                        <div class="status-badge ${statusClass}">
                            ${capitalizeFirstLetter(article.status)}
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('.article-listings').html(html);
    }
    
    // Helper function to capitalize first letter - global helper
    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    
    // Reset article form
    function resetArticleForm() {
        $('form.modal-form')[0].reset();
        $('input[name="date"]').datepicker('setDate', null);
    }
    
    // Notification function
    function showNotification(message, type = 'error') {
        // Remove any existing notifications
        $('.notification').remove();
        
        // Create new notification
        const notification = $(`<div class="notification ${type}">${message}</div>`);
        $('body').append(notification);
        
        // Force a reflow to ensure the animation works
        notification[0].offsetHeight;
        
        // Add the show class to trigger animation
        notification.addClass('show');
        
        // Remove notification after delay using jQuery's delay and queue
        notification
            .delay(type === 'success' ? 3000 : 5000)
            .queue(function(next) {
                $(this).removeClass('show');
                next();
            })
            .delay(300)
            .queue(function(next) {
                $(this).remove();
                next();
            });
    }
    
    // Function to load comments for an article
    function loadComments(articleId) {
        // Clear existing comments
        $('#articleComments').html('<div class="loading">Loading comments...</div>');
        
        // Fetch comments from the API
        $.ajax({
            url: '../../process/comments.php?article_id=' + articleId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayComments(response.comments);
                } else {
                    $('#articleComments').html('<div class="error">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#articleComments').html('<div class="error">Failed to load comments. Please try again.</div>');
            }
        });
    }
    
    // Function to display comments
    function displayComments(comments) {
        if (comments.length === 0) {
            $('#articleComments').html('<div class="no-comments">No comments yet. Be the first to comment!</div>');
            return;
        }
        
        let html = '';
        comments.forEach(function(comment) {
            const formattedDate = formatCommentDate(comment.created_at);
            const isCurrentUser = comment.is_owner;
            
            html += `
                <div class="comment-item" data-comment-id="${comment.id}">
                    <div class="comment-header">
                        <div class="comment-user">
                            <img src="${comment.profile_image}" alt="${comment.username}">
                            <span class="comment-username">${comment.username}</span>
                        </div>
                        <span class="comment-time">${formattedDate}</span>
                    </div>
                    <div class="comment-content">${comment.content}</div>
                    ${isCurrentUser ? `
                    <div class="comment-actions">
                        <button class="edit-comment" data-comment-id="${comment.id}">Edit</button>
                        <button class="delete-comment" data-comment-id="${comment.id}">Delete</button>
                    </div>
                    <div class="edit-comment-form" style="display:none;">
                        <textarea class="edit-comment-text">${comment.content}</textarea>
                        <div class="edit-comment-buttons">
                            <button class="save-edit-btn" data-comment-id="${comment.id}">Save</button>
                            <button class="cancel-edit-btn">Cancel</button>
                        </div>
                    </div>` : ''}
                </div>
            `;
        });
        
        $('#articleComments').html(html);
        
        // Add event listener for delete buttons
        $('.delete-comment').on('click', function() {
            const commentId = $(this).data('comment-id');
            deleteComment(commentId);
        });
        
        // Add event listener for edit buttons
        $('.edit-comment').on('click', function() {
            const commentId = $(this).data('comment-id');
            const commentItem = $(this).closest('.comment-item');
            
            // Hide the comment content and actions
            commentItem.find('.comment-content, .comment-actions').hide();
            
            // Show the edit form
            commentItem.find('.edit-comment-form').show();
        });
        
        // Cancel edit
        $('.cancel-edit-btn').on('click', function() {
            const commentItem = $(this).closest('.comment-item');
            
            // Show the comment content and actions
            commentItem.find('.comment-content, .comment-actions').show();
            
            // Hide the edit form
            commentItem.find('.edit-comment-form').hide();
        });
        
        // Save edit
        $('.save-edit-btn').on('click', function() {
            const commentId = $(this).data('comment-id');
            const commentItem = $(this).closest('.comment-item');
            const newContent = commentItem.find('.edit-comment-text').val().trim();
            
            if (!newContent) {
                showNotification('Comment cannot be empty', 'error');
                return;
            }
            
            editComment(commentId, newContent);
        });
    }
    
    // Function to format comment date
    function formatCommentDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffSec = Math.floor(diffMs / 1000);
        const diffMin = Math.floor(diffSec / 60);
        const diffHour = Math.floor(diffMin / 60);
        const diffDay = Math.floor(diffHour / 24);
        
        if (diffSec < 60) {
            return 'just now';
        } else if (diffMin < 60) {
            return diffMin + ' minute' + (diffMin > 1 ? 's' : '') + ' ago';
        } else if (diffHour < 24) {
            return diffHour + ' hour' + (diffHour > 1 ? 's' : '') + ' ago';
        } else if (diffDay < 7) {
            return diffDay + ' day' + (diffDay > 1 ? 's' : '') + ' ago';
        } else {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return date.toLocaleDateString(undefined, options);
        }
    }
    
    // Function to add a new comment
    function addComment() {
        const content = $('#commentText').val().trim();
        
        if (!content) {
            showNotification('Please enter a comment', 'error');
            return;
        }
        
        // Submit comment via API
        $.ajax({
            url: '../../process/comments.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                article_id: currentArticleId,
                content: content
            }),
            success: function(response) {
                if (response.success) {
                    // Clear the textarea
                    $('#commentText').val('');
                    
                    // Refresh comments to show the new one
                    loadComments(currentArticleId);
                    showNotification('Comment added successfully', 'success');
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Failed to add comment. Please try again.', 'error');
            }
        });
    }
    
    // Function to delete a comment
    function deleteComment(commentId) {
        $.ajax({
            url: '../../process/comments.php',
            type: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify({
                comment_id: commentId
            }),
            success: function(response) {
                if (response.success) {
                    // Remove the comment from the UI
                    $(`[data-comment-id="${commentId}"]`).fadeOut(300, function() {
                        $(this).remove();
                        
                        // If no comments left, show the "no comments" message
                        if ($('#articleComments .comment-item').length === 0) {
                            $('#articleComments').html('<div class="no-comments">No comments yet. Be the first to comment!</div>');
                        }
                    });
                    
                    showNotification('Comment deleted successfully', 'success');
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Failed to delete comment. Please try again.', 'error');
            }
        });
    }
    
    // Function to edit a comment
    function editComment(commentId, content) {
        $.ajax({
            url: '../../process/comments.php',
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({
                comment_id: commentId,
                content: content
            }),
            success: function(response) {
                if (response.success) {
                    // Update the comment content in the UI
                    const commentItem = $(`[data-comment-id="${commentId}"]`);
                    commentItem.find('.comment-content').text(content).show();
                    commentItem.find('.comment-actions').show();
                    commentItem.find('.edit-comment-form').hide();
                    
                    showNotification('Comment updated successfully', 'success');
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('Failed to update comment. Please try again.', 'error');
            }
        });
    }
    
    // Submit comment event handler
    $(document).on('click', '#submitComment', function() {
        addComment();
    });
    
    // Function to load community articles
    function loadCommunityArticles(search = '', status = 'all', page = 1, limit = 3) {
        // Find the community articles container
        const $communityContainer = $('.community-articles-container');
        
        // Show loading state
        $communityContainer.html('<div class="loading">Loading community articles...</div>');
        
        // Debug information
        console.log('Loading community articles with params:', {
            search: search,
            status: status,
            page: page,
            limit: limit
        });
        
        // Create request data object
        const requestData = {
            action: 'read_community',
            status: status,
            search: search,
            page: page,
            limit: limit
        };
        
        // Set a timeout to detect stuck requests
        const requestTimeout = setTimeout(function() {
            console.warn('Community articles request taking too long');
            $communityContainer.html('<div class="error">Request timeout. Try refreshing the page.</div>');
        }, 10000); // 10 second timeout
        
        // Use fetch API instead of jQuery AJAX for better error handling
        fetch('../../process/article_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(requestData)
        })
        .then(response => {
            clearTimeout(requestTimeout);
            console.log('Community articles response status:', response.status);
            
            // Check if the response is valid JSON
            const contentType = response.headers.get('content-type');
            console.log('Community articles response content type:', contentType);
            
            if (contentType && contentType.includes('application/json')) {
                return response.json().catch(err => {
                    console.error('Error parsing JSON:', err);
                    return response.text().then(text => {
                        console.error('Raw response that failed JSON parsing:', text);
                        throw new Error('Failed to parse JSON response');
                    });
                });
            } else {
                // If not JSON, get the text and log it for debugging
                return response.text().then(text => {
                    console.error('Server returned non-JSON response for community articles:', text);
                    throw new Error('Server returned non-JSON response');
                });
            }
        })
        .then(data => {
            console.log('Community articles response data:', data);
            
            if (!data) {
                $communityContainer.html('<div class="error">Invalid server response</div>');
                return;
            }
            
            if (data.success === true) {
                if (data.articles && data.articles.length > 0) {
                    displayCommunityArticlesPreview(data.articles, $communityContainer);
                } else {
                    $communityContainer.html('<div class="no-articles">No community articles found.</div>');
                }
            } else {
                const errorMsg = data.message ? data.message : 'Unknown error';
                $communityContainer.html('<div class="error">Error: ' + errorMsg + '</div>');
                console.error('Error loading community articles:', data);
            }
        })
        .catch(error => {
            clearTimeout(requestTimeout);
            console.error('Community articles fetch error:', error);
            $communityContainer.html('<div class="error">Error loading community articles. Please check the console for details and try again.</div>');
        });
    }
    
    function displayCommunityArticlesPreview(articles, container) {
        let html = '';
        
        articles.forEach(function(article) {
            // Format the date
            let formattedDate = article.time_ago || 'No date';
            
            // Determine status class
            const statusClass = article.status === 'legit' ? 'status-legit' : 
                              (article.status === 'fake' ? 'status-fake' : 'status-pending');
            
            html += `
                <div class="article-card" data-id="${article.id}">
                    <h3 class="article-title">${article.title}</h3>
                    <p class="article-author">By ${article.username || 'Unknown'}</p>
                    <p class="article-excerpt">${article.excerpt}</p>
                    <a href="#" class="read-more" data-id="${article.id}">Read More...</a>
                    
                    <div class="article-footer">
                        <div class="article-stats">
                            <div class="stat-item">
                                <i class="far fa-folder"></i>
                                <span>${article.category_name || 'Uncategorized'}</span>
                            </div>
                            <div class="stat-item">
                                <i class="far fa-clock"></i>
                                <span>${formattedDate}</span>
                            </div>
                        </div>
                        
                        <div class="status-badge ${statusClass}">
                            ${capitalizeFirstLetter(article.status)}
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.html(html);
    }
    
    // Form submit handler
    $('.modal-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const isEdit = articleOperation === 'update';
        
        // Add the action to formData
        formData.append('action', isEdit ? 'update' : 'create');
        
        // If editing, add the article ID
        if (isEdit && currentArticleId) {
            formData.append('article_id', currentArticleId);
        }
        
        // Show confirmation dialog with different messages based on operation
        showConfirmDialog(
            isEdit ? 'Update Article' : 'Create New Article',
            isEdit ? 
                'Are you sure you want to update this article? Your changes will be saved immediately.' : 
                'Are you sure you want to create this article? It will be submitted for review.',
            function() {
                $.ajax({
                    url: '../../process/article_process.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        try {
                            // Parse response if it's a string
                            const result = typeof response === 'string' ? JSON.parse(response) : response;
                            
                            if (result.success) {
                                // Show success notification with different messages
                                showNotification(
                                    isEdit ? 
                                        'Article has been updated successfully. Status will be updated after review.' : 
                                        'Article has been created and is waiting for review.',
                                    'success'
                                );
                                
                                // Close the modal
                                $('#modalOverlay').addClass('hidden');
                                
                                // Reset the form
                                resetArticleForm();
                                
                                // Reload articles immediately
                                loadArticles();
                                
                                // If on community page, reload community articles too
                                if (window.location.pathname.includes('/view/article_main.php')) {
                                    loadCommunityArticles();
                                }
                            } else {
                                // Show error notification with the specific error message
                                showNotification(result.message || 'Unable to process your request. Please try again.', 'error');
                            }
                        } catch (error) {
                            console.error('Error parsing response:', error);
                            showNotification('Unable to process the server response. Please try again.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        console.error('Response:', xhr.responseText);
                        showNotification('Unable to connect to the server. Please check your connection and try again.', 'error');
                    }
                });
            }
        );
    });

    // Confirmation dialog logic
    const $confirmDialog = $('#confirm-dialog');
    const $confirmTitle = $('#confirm-dialog-title');
    const $confirmDesc = $('#confirm-dialog-desc');
    const $confirmYes = $('#confirm-yes');
    const $confirmNo = $('#confirm-no');

    function showConfirmDialog(title, message, onConfirm) {
        $confirmTitle.text(title);
        $confirmDesc.html(`<i class="fas fa-exclamation-triangle delete-warning-icon"></i> ${message}`);
        
        // Set appropriate button text based on the action
        if (title.includes('Delete')) {
            $confirmYes.text('Delete');
        } else if (title.includes('Update')) {
            $confirmYes.text('Update');
        } else if (title.includes('Create')) {
            $confirmYes.text('Create');
        } else {
            $confirmYes.text('Confirm');
        }

        function yesHandler() {
            onConfirm();
            $confirmDialog[0].close();
            cleanup();
        }

        function noHandler() {
            $confirmDialog[0].close();
            cleanup();
        }

        function cleanup() {
            $confirmYes.off('click', yesHandler);
            $confirmNo.off('click', noHandler);
        }

        $confirmYes.on('click', yesHandler);
        $confirmNo.on('click', noHandler);

        $confirmDialog[0].showModal();
    }

    // Info dialog for replacement of alert
    const $infoDialog = $('#info-dialog');
    const $infoTitle = $('#info-dialog-title');
    const $infoDesc = $('#info-dialog-desc');
    const $infoOk = $('#info-ok');

    function showInfoDialog(title, message) {
        $infoTitle.text(title);
        $infoDesc.text(message);

        function okHandler() {
            $infoDialog[0].close();
            $infoOk.off('click', okHandler);
        }

        $infoOk.on('click', okHandler);
        $infoDialog[0].showModal();
    }

    // Trap focus inside dialogs while open
    function trapFocus($dialog) {
        $dialog.on('keydown', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                $dialog[0].close();
            }
            else if (e.key === 'Tab') {
                const focusable = $dialog.find('button:visible').toArray();
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (e.shiftKey) {
                    if (document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    }
                } else {
                    if (document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            }
        });
    }

    trapFocus($confirmDialog);
    trapFocus($infoDialog);

    // Function to highlight keywords in content
    function highlightKeywords(content, keywords) {
        if (!keywords || !keywords.length) return content;
        
        let highlightedContent = content;
        keywords.forEach(keyword => {
            const regex = new RegExp(keyword, 'gi');
            highlightedContent = highlightedContent.replace(regex, match => 
                `<span class="highlighted-keyword">${match}</span>`
            );
        });
        
        return highlightedContent;
    }

    // Remove tooltips from datepicker in article modal
    $('#modalOverlay').on('click', 'input[name="date"]', function() {
        var $datepicker = $('.ui-datepicker');
        if ($datepicker.length) {
            $datepicker.find('th, td, a').removeAttr('title');
        }
    });

    // Remove tooltips when datepicker is shown in modal
    $('#modalOverlay').on('click', '.ui-datepicker-prev, .ui-datepicker-next, .ui-datepicker-month, .ui-datepicker-year', function() {
        var $datepicker = $('.ui-datepicker');
        if ($datepicker.length) {
            $datepicker.find('th, td, a').removeAttr('title');
        }
    });

    // Remove tooltips on hover in modal datepicker
    $('#modalOverlay').on('mouseover', '.ui-datepicker th, .ui-datepicker td, .ui-datepicker a', function() {
        $(this).removeAttr('title');
    });

    // Function to display pagination - also global
    function displayPagination(currentPage, totalPages) {
        const pagination = $('#pagination');
        pagination.empty();
        
        if (totalPages <= 1) {
            return;
        }
        
        let html = '';
        
        // Previous button
        html += `<a href="#" class="prev ${currentPage === 1 ? 'disabled' : ''}" data-page="${currentPage - 1}">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>`;
        
        // Page numbers
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        if (startPage > 1) {
            html += `<a href="#" data-page="1">1</a>`;
            if (startPage > 2) {
                html += `<span class="pagination-ellipsis">...</span>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<a href="#" class="${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</a>`;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<span class="pagination-ellipsis">...</span>`;
            }
            html += `<a href="#" data-page="${totalPages}">${totalPages}</a>`;
        }
        
        // Next button
        html += `<a href="#" class="next ${currentPage === totalPages ? 'disabled' : ''}" data-page="${currentPage + 1}">
                    Next <i class="fas fa-chevron-right"></i>
                </a>`;
        
        pagination.html(html);
        
        // Add click handlers for pagination
        pagination.find('a').on('click', function(e) {
            e.preventDefault();
            if (!$(this).hasClass('disabled')) {
                const page = $(this).data('page');
                const status = $('.filter-item.active').data('status') || 'all';
                const search = $('.search-input').val().trim();
                
                loadArticles(status, search, page);
                
                // Scroll to top of article listings
                $('.article-listings')[0].scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
});