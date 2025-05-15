<?php include_once '../includes/header.php'; ?>

<style>
    /* Fix for action buttons */
    .action-icons-container {
        display: flex;
        gap: 10px;
        justify-content: center;
    }
    
    .view-btn, .delete-article {
        color: #555;
        text-decoration: none;
        transition: color 0.2s;
    }
    
    .view-btn:hover, .delete-article:hover {
        color: #007bff;
    }
</style>

<div class="container">
    <!-- Sidebar is included in the header.php file -->
    <div class="content-area">
        <div class="page-header">
            <h1 class="page-title">Manage Article</h1>
        </div>
        
        <div class="action-bar">
            <div class="search-container">
                <input type="text" id="search-input" class="search-input" placeholder="Search by id, name or category...">
                <button class="search-icon"><i class="fas fa-search"></i></button>
            </div>
            <div class="actions-container">
                <!-- Filter container - will only appear when Articles is selected -->
                <div id="filter-container" class="filter-container">
                    <button id="filter-btn" class="btn btn-outline">
                        <i class="fas fa-filter"></i> <span>All</span>
                    </button>
                    <div id="filter-dropdown" class="filter-dropdown">
                        <div class="filter-option" data-filter="all">All</div>
                        <div class="filter-option" data-filter="pending">Pending</div>
                        <div class="filter-option" data-filter="reviewed">Reviewed</div>
                        <div class="filter-option" data-filter="legit">Legit</div>
                        <div class="filter-option" data-filter="fake">Fake</div>
                    </div>
                </div>
                
                <!-- Tab Dropdown Menu -->
                <div class="tab-dropdown-container">
                    <button id="tab-dropdown-btn" class="btn btn-outline">
                        <span id="current-tab-text">Articles</span> <i class="fas fa-caret-down"></i>
                    </button>
                    <div id="tab-dropdown-menu" class="tab-dropdown-menu">
                        <div class="tab-dropdown-item" data-tab="articles">Articles</div>
                        <div class="tab-dropdown-item" data-tab="categories">Categories</div>
                        <div class="tab-dropdown-item" data-tab="keywords">Keywords</div>
                    </div>
                </div>
                
                <button id="export-btn" class="btn btn-primary">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
        
        <!-- Articles Tab Content -->
        <div id="articles" class="tab-content active">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Date Published</th>
                            <th>Source</th>
                            <th>Result</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Database connection
                        include_once '../../config/connection.php';
                        
                        // Query to fetch articles where is_visible = 1
                        $query = "SELECT a.*, c.name as category_name, u.username as author 
                                 FROM articles a 
                                 LEFT JOIN categories c ON a.category_id = c.id
                                 LEFT JOIN users u ON a.user_id = u.id
                                 WHERE a.is_visible = 1
                                 ORDER BY a.id DESC";
                        $result = mysqli_query($conn, $query);
                        
                        // Check if there are any articles
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $status_class = '';
                                if ($row['status'] == 'pending') {
                                    $status_class = 'status-pending';
                                } elseif ($row['status'] == 'legit') {
                                    $status_class = 'status-legit';
                                } elseif ($row['status'] == 'fake') {
                                    $status_class = 'status-fake';
                                }
                                
                                // Format the date if it exists
                                $date_published = !empty($row['date_published']) ? date('m/d/Y', strtotime($row['date_published'])) : '';
                                
                                // Determine result text based on detection_score
                                if ($row['detection_score'] >= 70) {
                                    $result_text = 'Likely Legit';
                                } elseif ($row['detection_score'] >= 30) {
                                    $result_text = 'Suspicious';
                                } else {
                                    $result_text = 'Likely Fake';
                                }
                                
                                // Format source URL for display
                                $source_url = !empty($row['source_url']) ? $row['source_url'] : 'url';
                                $display_url = $source_url;
                                
                                echo '<tr>';
                                echo '<td>' . $row['id'] . '</td>';
                                echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['author']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['category_name']) . '</td>';
                                echo '<td>' . $date_published . '</td>';
                                echo '<td><a href="' . htmlspecialchars($source_url) . '" class="url-btn" target="_blank">' . htmlspecialchars($display_url) . '</a></td>';
                                echo '<td>' . $result_text . '</td>';
                                echo '<td class="' . $status_class . '">' . ucfirst($row['status']) . '</td>';
                                echo '<td>
                                        <div class="action-icons-container">
                                            <a href="#" class="view-btn" data-id="' . $row['id'] . '"><i class="fas fa-eye"></i></a>
                                            <a href="#" class="delete-article" data-id="' . $row['id'] . '"><i class="fas fa-trash"></i></a>
                                        </div>
                                     </td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="9">No articles found</td></tr>';
                        }
                        
                        // Close the database connection
                        mysqli_close($conn);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Categories Tab Content -->
        <div id="categories" class="tab-content">
            <div class="action-button-container">
                <button id="newCategoryBtn" class="btn btn-primary">+ New Category</button>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categories-table-body">
                        <!-- Categories will be loaded dynamically -->
                        <tr>
                            <td colspan="3" class="text-center">Loading categories...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Keywords Tab Content -->
        <div id="keywords" class="tab-content">
            <div class="action-button-container">
                <button id="newKeywordBtn" class="btn btn-primary">+ New Keyword</button>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Keyword</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="keywords-table-body">
                        <!-- Keywords will be loaded dynamically -->
                        <tr>
                            <td colspan="3" class="text-center">Loading keywords...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="pagination">
            <a href="#" class="prev"><i class="fas fa-chevron-left"></i> Previous</a>
            <span id="pagination-numbers">
                <a href="#" class="active" data-page="1">1</a>
                <a href="#" data-page="2">2</a>
                <a href="#" data-page="3">3</a>
                <a href="#" data-page="4">4</a>
                <a href="#" data-page="5">5</a>
            </span>
            <a href="#" class="next">Next <i class="fas fa-chevron-right"></i></a>
        </div>
        
        <!-- Modal for New Category -->
        <div id="categoryModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="categoryModalTitle">Add New Category</h2> </br>
                <form id="categoryForm">
                    <input type="hidden" id="categoryId" value="">
                    <div class="form-group">
                        <input type="text" id="categoryName" placeholder="Enter category name" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-add add-btn" id="categorySubmitBtn">ADD</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Modal for New Keyword -->
        <div id="keywordModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="keywordModalTitle">Add New Keyword</h2> </br>
                <form id="keywordForm">
                    <input type="hidden" id="keywordId" value="">
                    <div class="form-group">
                        <input type="text" id="keywordName" placeholder="Enter keyword" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-add add-btn" id="keywordSubmitBtn">ADD</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Confirmation Modal for Delete Category -->
        <div id="confirmDeleteCategoryModal" class="modal">
            <div class="modal-content">
                <span class="close-confirm">&times;</span>
                <h3>Confirm Deletion</h3>
                <p>Are you sure you want to delete this category?</p>
                <div class="confirm-actions">
                    <button id="confirmDeleteCategoryBtn" class="btn btn-fake">Yes, Delete</button>
                    <button id="cancelDeleteCategoryBtn" class="btn btn-outline">Cancel</button>
                </div>
                <input type="hidden" id="deleteCategoryId">
            </div>
        </div>
        
        <!-- Confirmation Modal for Delete Keyword -->
        <div id="confirmDeleteKeywordModal" class="modal">
            <div class="modal-content">
                <span class="close-confirm">&times;</span>
                <h3>Confirm Deletion</h3>
                <p>Are you sure you want to delete this keyword?</p>
                <div class="confirm-actions">
                    <button id="confirmDeleteKeywordBtn" class="btn btn-fake">Yes, Delete</button>
                    <button id="cancelDeleteKeywordBtn" class="btn btn-outline">Cancel</button>
                </div>
                <input type="hidden" id="deleteKeywordId">
            </div>
        </div>
        
        <!-- Article View Modal -->
        <div id="articleViewModal" class="modal article-view-modal">
            <div class="article-modal-content">
                <div class="article-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h2 class="article-title">Article Title</h2>
                    <span class="article-status status-pending-label">Pending</span>
                </div>
                <span class="close">&times;</span>
                </div>
                <div class="article-author">Author</div>
                
                <div class="article-content-container">
                    <div class="article-content">
                        <div id="article-content-text"></div>
                    </div>
                    <div class="article-sidebar">
                        <div class="result-indicator">
                            <div class="result-label">Result</div>
                            <div class="result-percentage">75%</div>
                            <div class="result-text" id="result-verdict">Legit or Fake</div>
                            <div class="keyword-count" id="keyword-count">Based on the detection of 0 fake-related keywords</div>
                        </div>
                    </div>
                </div>
                
                <div class="article-date">
                    <i class="far fa-calendar"></i> <span id="article-date">01/24/2025</span>
                </div>
                
                <div class="article-actions">
                    <button class="btn btn-legit approve-btn">Mark as Legit</button>
                    <button class="btn btn-fake fake-btn">Mark as Fake</button>
                </div>
            </div>
        </div>
        
        <!-- Confirmation Modal for Marking as Legit -->
        <div id="confirmLegitModal" class="modal">
            <div class="modal-content">
                <span class="close-confirm">&times;</span>
                <h3>Confirm Action</h3>
                <p>Are you sure you want to mark this article as legitimate?</p>
                <div class="confirm-actions">
                    <button id="confirmLegitBtn" class="btn btn-legit">Yes, Mark as Legit</button>
                    <button id="cancelLegitBtn" class="btn btn-outline">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal for Marking as Fake -->
        <div id="confirmFakeModal" class="modal">
            <div class="modal-content">
                <span class="close-confirm">&times;</span>
                <h3>Confirm Action</h3>
                <p>Are you sure you want to mark this article as fake?</p>
                <div class="confirm-actions">
                    <button id="confirmFakeBtn" class="btn btn-fake">Yes, Mark as Fake</button>
                    <button id="cancelFakeBtn" class="btn btn-outline">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal for Delete Article -->
        <div id="confirmDeleteArticleModal" class="modal">
            <div class="modal-content">
                <span class="close-confirm">&times;</span>
                <h3>Confirm Action</h3>
                <p>Are you sure you want to hide this article from public view? It will still be in the database but not visible to users.</p>
                <div class="confirm-actions">
                    <button id="confirmDeleteArticleBtn" class="btn btn-fake">Yes, Hide Article</button>
                    <button id="cancelDeleteArticleBtn" class="btn btn-outline">Cancel</button>
                </div>
                <input type="hidden" id="deleteArticleId">
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript for handling article view modal
$(document).ready(function() {
    var currentPage = 1;
    var totalPages = 1;
    var statusFilter = 'all';
    var searchTerm = '';
    
    // Check if we have a filter parameter in the URL
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }
    
    // Attach handlers to the initial PHP-generated table
    attachViewButtonHandlers();
    
    // Check for filter parameter
    var filterParam = getUrlParameter('filter');
    
    // Load articles first
    loadArticles(1, 'all', '');
    
    // Apply filter from URL if present
    if (filterParam) {
        // A small delay to make sure articles are loaded first
        setTimeout(function() {
            // Find the filter option that matches the parameter
            var $filterOption = $('.filter-option[data-filter="' + filterParam + '"]');
            if ($filterOption.length) {
                // Update the filter text and variable
                statusFilter = filterParam;
                $('#filter-btn span').text($filterOption.text());
                
                // Apply the filter
                loadArticles(1, statusFilter, searchTerm);
            }
        }, 300);
    }
    
    // Function to load articles
    function loadArticles(page, status, search) {
        const itemsPerPage = 10; // Setting explicit limit of 10 items per page
        
        $.ajax({
            url: '../../process/article_process.php',
            type: 'POST',
            data: {
                action: 'get_admin_articles',
                page: page,
                status: status,
                search: search,
                limit: itemsPerPage // Send items per page to backend
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    // Update pagination info
                    currentPage = response.current_page;
                    totalPages = response.total_pages;
                    
                    // Update articles table
                    updateArticlesTable(response.articles);
                    
                    // Update pagination UI
                    updatePagination();
                } else {
                    showArticleLoadError(response);
                }
            },
            error: function() {
                showArticleLoadError(null);
            }
        });
    }
    
    // Function to update the articles table
    function updateArticlesTable(articles) {
        var tableBody = $('#articles table tbody');
        tableBody.empty();
        
        if(articles.length === 0) {
            tableBody.append('<tr><td colspan="9">No articles found</td></tr>');
            return;
        }
        
        $.each(articles, function(index, article) {
            var statusClass = '';
            if(article.status === 'pending') {
                statusClass = 'status-pending';
            } else if(article.status === 'legit') {
                statusClass = 'status-legit';
            } else if(article.status === 'fake') {
                statusClass = 'status-fake';
            }
            
            // Determine result text based on detection_score
            var resultText = '';
            if(article.detection_score < 25) {
                resultText = 'Likely Legit';
            } else if(article.detection_score < 50) {
                resultText = 'Suspicious';
            } else {
                resultText = 'Likely Fake';
            }
            
            var row = '<tr>' +
                '<td>' + article.id + '</td>' +
                '<td>' + article.title + '</td>' +
                '<td>' + article.author + '</td>' +
                '<td>' + article.category_name + '</td>' +
                '<td>' + article.date_published + '</td>' +
                '<td><a href="' + article.source_url + '" class="url-btn" target="_blank">' + article.source_url + '</a></td>' +
                '<td>' + resultText + '</td>' +
                '<td class="' + statusClass + '">' + article.status.charAt(0).toUpperCase() + article.status.slice(1) + '</td>' +
                '<td>' +
                '<div class="action-icons-container">' +
                '<a href="#" class="view-btn" data-id="' + article.id + '"><i class="fas fa-eye"></i></a>' +
                '<a href="#" class="delete-article" data-id="' + article.id + '"><i class="fas fa-trash"></i></a>' +
                '</div>' +
                '</td>' +
                '</tr>';
            
            tableBody.append(row);
        });
        
        // Reattach view button click handlers
        attachViewButtonHandlers();
    }
    
    // Function to update pagination UI
    function updatePagination() {
        var paginationNumbers = $('#pagination-numbers');
        paginationNumbers.empty();
        
        // Determine range of pages to show
        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, startPage + 4);
        startPage = Math.max(1, endPage - 4); // Adjust start if end is too small
        
        // Add page numbers
        for(var i = startPage; i <= endPage; i++) {
            var activeClass = i === currentPage ? 'active' : '';
            paginationNumbers.append('<a href="#" class="' + activeClass + '" data-page="' + i + '">' + i + '</a>');
        }
        
        // Update previous and next buttons
        $('.pagination .prev').toggleClass('disabled', currentPage === 1);
        $('.pagination .next').toggleClass('disabled', currentPage === totalPages);
        
        // Attach click handlers to pagination links
        $('.pagination a[data-page]').click(function(e) {
            e.preventDefault();
            var page = parseInt($(this).data('page'));
            loadArticles(page, statusFilter, searchTerm);
        });
        
        // Previous button handler
        $('.pagination .prev').click(function(e) {
            e.preventDefault();
            if(currentPage > 1) {
                loadArticles(currentPage - 1, statusFilter, searchTerm);
            }
        });
        
        // Next button handler
        $('.pagination .next').click(function(e) {
            e.preventDefault();
            if(currentPage < totalPages) {
                loadArticles(currentPage + 1, statusFilter, searchTerm);
            }
        });
    }
    
    // Handle search input
    $('#search-input').on('keyup', function(e) {
        var currentTab = $('#current-tab-text').text().toLowerCase();
        
        if(e.keyCode === 13) { // Enter key
            searchTerm = $(this).val().trim();
            
            // Reset to first page with new search term
            if(currentTab === 'articles') {
                loadArticles(1, statusFilter, searchTerm);
            } else if(currentTab === 'categories') {
                loadCategories(1, searchTerm);
            } else if(currentTab === 'keywords') {
                loadKeywords(1, searchTerm);
            }
        } else if(currentTab === 'keywords') {
            // For keywords tab, add dynamic search as user types with 300ms delay
            clearTimeout($.data(this, 'timer'));
            var searchString = $(this).val().trim();
            var wait = setTimeout(function() {
                searchTerm = searchString;
                loadKeywords(1, searchTerm);
            }, 300);
            $(this).data('timer', wait);
        }
    });
    
    // Handle search button click
    $('.search-icon').click(function() {
        searchTerm = $('#search-input').val().trim();
        var currentTab = $('#current-tab-text').text().toLowerCase();
        
        // Reset to first page with new search term
        if(currentTab === 'articles') {
            loadArticles(1, statusFilter, searchTerm);
        } else if(currentTab === 'categories') {
            loadCategories(1, searchTerm);
        } else if(currentTab === 'keywords') {
            loadKeywords(1, searchTerm);
        }
    });
    
    // Handle filter dropdown
    $('#filter-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent the document click handler from immediately closing it
        $('#filter-dropdown').toggle(); // Using toggle() instead of toggleClass('show')
    });
    
    // Close filter dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#filter-container').length) {
            $('#filter-dropdown').hide(); // Using hide() instead of removeClass('show')
        }
    });
    
    // Handle filter options click
    $('.filter-option').on('click', function() {
        statusFilter = $(this).data('filter');
        $('#filter-btn span').text($(this).text());
        $('#filter-dropdown').hide(); // Using hide() instead of removeClass('show')
        
        // Reset to first page when filter changes
        loadArticles(1, statusFilter, searchTerm);
    });
    
    // Handle export button
    $('#export-btn').click(function() {
        // Get current tab
        var currentTab = $('#current-tab-text').text().toLowerCase();
        
        // Get filter criteria if on articles tab
        var exportData = {
            action: 'export_' + currentTab,
        };
        
        // Add additional parameters for articles
        if (currentTab === 'articles') {
            exportData.status = statusFilter;
            exportData.search = searchTerm;
        }
        
        // Create form to submit for download
        var form = $('<form>', {
            'method': 'POST',
            'action': '../../process/article_process.php',
            'target': '_blank'
        });
        
        // Add parameters
        for (var key in exportData) {
            form.append($('<input>', {
                'name': key,
                'value': exportData[key],
                'type': 'hidden'
            }));
        }
        
        // Append form to body, submit, and remove
        $('body').append(form);
        form.submit();
        form.remove();
    });
    
    // Handle tab dropdown click
    $('#tab-dropdown-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent the document click handler from immediately closing it
        $('#tab-dropdown-menu').toggle(); // Using toggle() instead of toggleClass('show')
    });
    
    // Close tab dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.tab-dropdown-container').length) {
            $('#tab-dropdown-menu').hide(); // Using hide() instead of removeClass('show')
        }
    });
    
    // Tab switching
    $('.tab-dropdown-item').click(function() {
        var tabId = $(this).data('tab');
        
        // Update dropdown text
        $('#current-tab-text').text($(this).text());
        
        // Hide all tabs and show selected one
        $('.tab-content').removeClass('active');
        $('#' + tabId).addClass('active');
        
        // Hide filter container if not on articles tab
        if(tabId === 'articles') {
            $('#filter-container').show();
            loadArticles(1, statusFilter, searchTerm);
        } else {
            $('#filter-container').hide();
            
            // Load categories or keywords with pagination reset to page 1
            if(tabId === 'categories') {
                loadCategories(1, searchTerm);
            } else if(tabId === 'keywords') {
                loadKeywords(1, searchTerm);
            }
        }
        
        // Hide dropdown menu
        $('#tab-dropdown-menu').hide(); // Using hide() instead of removeClass('show')
    });
    
    // Function to load categories with search support
    function loadCategories(page = 1, search = '') {
        search = search || $('#search-input').val().trim();
        
        $.ajax({
            url: '../../process/article_process.php',
            type: 'POST',
            data: {
                action: 'get_categories',
                page: page,
                limit: 10, // 10 items per page
                search: search
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    displayCategories(response.categories);
                    
                    // Update the pagination for categories
                    updatePaginationForTab('categories', response.current_page, response.total_pages);
                } else {
                    showCategoryLoadError(response);
                }
            },
            error: function() {
                showCategoryLoadError(null);
            }
        });
    }
    
    // Function to load keywords with search support
    function loadKeywords(page = 1, search = '') {
        search = search || $('#search-input').val().trim();
        
        $.ajax({
            url: '../../process/article_process.php',
            type: 'POST',
            data: {
                action: 'get_keywords',
                page: page,
                limit: 10, // 10 items per page
                search: search
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    displayKeywords(response.keywords);
                    
                    // Update the pagination for keywords
                    updatePaginationForTab('keywords', response.current_page, response.total_pages);
                } else {
                    showKeywordLoadError(response);
                }
            },
            error: function() {
                showKeywordLoadError(null);
            }
        });
    }

    // Function to update pagination for each tab
    function updatePaginationForTab(tabId, currentPage, totalPages) {
        // Only update pagination if we're on the requested tab
        if (!$('#' + tabId).hasClass('active')) {
            return;
        }
        
        var paginationNumbers = $('#pagination-numbers');
        paginationNumbers.empty();
        
        // Determine range of pages to show
        var startPage = Math.max(1, currentPage - 2);
        var endPage = Math.min(totalPages, startPage + 4);
        startPage = Math.max(1, endPage - 4); // Adjust start if end is too small
        
        // Add page numbers
        for(var i = startPage; i <= endPage; i++) {
            var activeClass = i === currentPage ? 'active' : '';
            paginationNumbers.append('<a href="#" class="' + activeClass + '" data-page="' + i + '" data-target="' + tabId + '">' + i + '</a>');
        }
        
        // Update previous and next buttons
        $('.pagination .prev').toggleClass('disabled', currentPage === 1);
        $('.pagination .next').toggleClass('disabled', currentPage === totalPages);
        
        // Store current tab and page in data attributes
        $('.pagination').data('current-tab', tabId);
        $('.pagination').data('current-page', currentPage);
    }

    // Update pagination click handler
    $(document).on('click', '.pagination a[data-page]', function(e) {
        e.preventDefault();
        
        var page = parseInt($(this).data('page'));
        var targetTab = $(this).data('target') || $('.pagination').data('current-tab') || 'articles';
        
        // Different loading function based on which tab is active
        if(targetTab === 'articles') {
            loadArticles(page, statusFilter, searchTerm);
        } else if(targetTab === 'categories') {
            loadCategories(page, searchTerm);
        } else if(targetTab === 'keywords') {
            loadKeywords(page, searchTerm);
        }
    });
    
    // Update previous button handler
    $('.pagination .prev').on('click', function(e) {
        e.preventDefault();
        if($(this).hasClass('disabled')) return;
        
        var currentPage = parseInt($('.pagination').data('current-page')) || 1;
        var targetTab = $('.pagination').data('current-tab') || 'articles';
        
        if(currentPage > 1) {
            if(targetTab === 'articles') {
                loadArticles(currentPage - 1, statusFilter, searchTerm);
            } else if(targetTab === 'categories') {
                loadCategories(currentPage - 1, searchTerm);
            } else if(targetTab === 'keywords') {
                loadKeywords(currentPage - 1, searchTerm);
            }
        }
    });
    
    // Update next button handler
    $('.pagination .next').on('click', function(e) {
        e.preventDefault();
        if($(this).hasClass('disabled')) return;
        
        var currentPage = parseInt($('.pagination').data('current-page')) || 1;
        var targetTab = $('.pagination').data('current-tab') || 'articles';
        
        if(targetTab === 'articles') {
            loadArticles(currentPage + 1, statusFilter, searchTerm);
        } else if(targetTab === 'categories') {
            loadCategories(currentPage + 1, searchTerm);
        } else if(targetTab === 'keywords') {
            loadKeywords(currentPage + 1, searchTerm);
        }
    });
    
    function displayCategories(categories) {
        var tbody = $('#categories-table-body');
        tbody.empty();
        
        if(categories.length === 0) {
            tbody.append('<tr><td colspan="3" class="text-center">No categories found</td></tr>');
            return;
        }
        
        $.each(categories, function(index, category) {
            var row = '<tr>' +
                '<td>' + category.id + '</td>' +
                '<td>' + category.name + '</td>' +
                '<td class="actions-cell">' +
                '<div class="action-icons-container">' +
                '<i class="fas fa-edit action-icon edit-category" data-id="' + category.id + '" data-name="' + category.name + '"></i>' +
                '<i class="fas fa-trash action-icon delete-category" data-id="' + category.id + '"></i>' +
                '</div>' +
                '</td>' +
                '</tr>';
            
            tbody.append(row);
        });
        
        // Attach event handlers to newly created elements
        attachCategoryEventHandlers();
    }
    
    function displayKeywords(keywords) {
        var tbody = $('#keywords-table-body');
        tbody.empty();
        
        if(keywords.length === 0) {
            tbody.append('<tr><td colspan="3" class="text-center">No keywords found</td></tr>');
            return;
        }
        
        $.each(keywords, function(index, keyword) {
            var row = '<tr>' +
                '<td>' + keyword.id + '</td>' +
                '<td>' + keyword.keyword + '</td>' +
                '<td class="actions-cell">' +
                '<div class="action-icons-container">' +
                '<i class="fas fa-edit action-icon edit-keyword" data-id="' + keyword.id + '" data-keyword="' + keyword.keyword + '"></i>' +
                '<i class="fas fa-trash action-icon delete-keyword" data-id="' + keyword.id + '"></i>' +
                '</div>' +
                '</td>' +
                '</tr>';
            
            tbody.append(row);
        });
        
        // Attach event handlers to newly created elements
        attachKeywordEventHandlers();
    }
    
    // Event handlers for categories
    function attachCategoryEventHandlers() {
        // Edit category
        $('.edit-category').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            // Set modal to edit mode
            $('#categoryModalTitle').text('Edit Category');
            $('#categoryId').val(id);
            $('#categoryName').val(name);
            $('#categorySubmitBtn').text('UPDATE');
            
            // Show modal
            $('#categoryModal').fadeIn();
        });
        
        // Delete category confirmation
        $('.delete-category').click(function() {
            var id = $(this).data('id');
            $('#deleteCategoryId').val(id);
            $('#confirmDeleteCategoryModal').fadeIn();
        });
    }
    
    // Event handlers for keywords
    function attachKeywordEventHandlers() {
        // Edit keyword
        $('.edit-keyword').click(function() {
            var id = $(this).data('id');
            var keyword = $(this).data('keyword');
            
            // Set modal to edit mode
            $('#keywordModalTitle').text('Edit Keyword');
            $('#keywordId').val(id);
            $('#keywordName').val(keyword);
            $('#keywordSubmitBtn').text('UPDATE');
            
            // Show modal
            $('#keywordModal').fadeIn();
        });
        
        // Delete keyword confirmation
        $('.delete-keyword').click(function() {
            var id = $(this).data('id');
            $('#deleteKeywordId').val(id);
            $('#confirmDeleteKeywordModal').fadeIn();
        });
    }
    
    // New category button
    $('#newCategoryBtn').click(function() {
        // Reset modal to add mode
        $('#categoryModalTitle').text('Add New Category');
        $('#categoryId').val('');
        $('#categoryName').val('');
        $('#categorySubmitBtn').text('ADD');
        
        // Show modal
        $('#categoryModal').fadeIn();
    });
    
    // New keyword button
    $('#newKeywordBtn').click(function() {
        // Reset modal to add mode
        $('#keywordModalTitle').text('Add New Keyword');
        $('#keywordId').val('');
        $('#keywordName').val('');
        $('#keywordSubmitBtn').text('ADD');
        
        // Show modal
        $('#keywordModal').fadeIn();
    });
    
    // Category form submit
    $('#categoryForm').submit(function(e) {
        e.preventDefault();
        
        var id = $('#categoryId').val();
        var name = $('#categoryName').val().trim();
        
        if(name === '') {
            NotificationSystem.error('Please enter a category name');
            return;
        }
        
        // Determine if this is an add or update operation
        var action = id ? 'update_category' : 'add_category';
        
        $.ajax({
            url: '../../process/article_process.php',
            type: 'POST',
            data: {
                action: action,
                id: id,
                name: name
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    // Close modal
                    $('#categoryModal').fadeOut();
                    
                    // Reload categories
                    loadCategories();
                    
                    // Show success message
                    var message = id ? 'Category updated successfully!' : 'Category added successfully!';
                    NotificationSystem.success(message);
                } else {
                    NotificationSystem.error('Error: ' + response.message);
                }
            },
            error: function() {
                NotificationSystem.error('Error processing request');
            }
        });
    });
    
    // Keyword form submit
    $('#keywordForm').submit(function(e) {
        e.preventDefault();
        
        var id = $('#keywordId').val();
        var keyword = $('#keywordName').val().trim();
        
        if(keyword === '') {
            NotificationSystem.error('Please enter a keyword');
            return;
        }
        
        // Determine if this is an add or update operation
        var action = id ? 'update_keyword' : 'add_keyword';
        
        $.ajax({
            url: '../../process/article_process.php',
            type: 'POST',
            data: {
                action: action,
                id: id,
                keyword: keyword
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    // Close modal
                    $('#keywordModal').fadeOut();
                    
                    // Reload keywords
                    loadKeywords();
                    
                    // Show success message
                    var message = id ? 'Keyword updated successfully!' : 'Keyword added successfully!';
                    NotificationSystem.success(message);
                } else {
                    NotificationSystem.error('Error: ' + response.message);
                }
            },
            error: function() {
                NotificationSystem.error('Error processing request');
            }
        });
    });
    
    // Close modals when X is clicked
    $('.close').click(function() {
        $(this).closest('.modal').fadeOut();
    });
    
    // Close modals when clicking outside
    $(window).click(function(e) {
        if($(e.target).hasClass('modal')) {
            $('.modal').fadeOut();
        }
    });
    
    // Handle delete category confirmation
    $('#confirmDeleteCategoryBtn').click(function() {
        var id = $('#deleteCategoryId').val();
        
        $.ajax({
            url: '../../process/article_process.php',
            type: 'POST',
            data: {
                action: 'delete_category',
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    // Close modal
                    $('#confirmDeleteCategoryModal').fadeOut();
                    
                    // Reload categories
                    loadCategories();
                    
                    // Show success message
                    NotificationSystem.success('Category deleted successfully!');
                } else {
                    NotificationSystem.error('Error: ' + response.message);
                }
            },
            error: function() {
                NotificationSystem.error('Error processing delete request');
            }
        });
    });
    
    // Handle delete keyword confirmation
    $('#confirmDeleteKeywordBtn').click(function() {
        var id = $('#deleteKeywordId').val();
        
        $.ajax({
            url: '../../process/article_process.php',
            type: 'POST',
            data: {
                action: 'delete_keyword',
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    // Close modal
                    $('#confirmDeleteKeywordModal').fadeOut();
                    
                    // Reload keywords
                    loadKeywords();
                    
                    // Show success message
                    NotificationSystem.success('Keyword deleted successfully!');
                } else {
                    NotificationSystem.error('Error: ' + response.message);
                }
            },
            error: function() {
                NotificationSystem.error('Error processing delete request');
            }
        });
    });
    
    // Cancel delete buttons
    $('#cancelDeleteCategoryBtn, #cancelDeleteKeywordBtn, .close-confirm').click(function() {
        $(this).closest('.modal').fadeOut();
    });
    
    // Function to attach view button handlers
    function attachViewButtonHandlers() {
        // When view button is clicked
        $('.view-btn').on('click', function(e) {
            e.preventDefault();
            var articleId = $(this).data('id');
            
            // Fetch article details via AJAX
            $.ajax({
                url: '../../process/article_process.php',
                type: 'POST',
                data: {
                    action: 'get_admin_article',
                    article_id: articleId
                },
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        var article = response.data;
                        
                        // Update modal with article details
                        $('.article-title').text(article.title);
                        $('.article-author').text(article.author);
                        $('#article-date').text(article.date_published);
                        
                        // Set status class and text
                        var statusClass = '';
                        if(article.status === 'pending') {
                            statusClass = 'status-pending-label';
                            statusText = 'Pending';
                        } else if(article.status === 'legit') {
                            statusClass = 'status-legit-label';
                            statusText = 'Legit';
                        } else if(article.status === 'fake') {
                            statusClass = 'status-fake-label';
                            statusText = 'Fake';
                        }
                        
                        $('.article-status').removeClass('status-pending-label status-legit-label status-fake-label').addClass(statusClass).text(statusText);
                        
                        // Update score and verdict
                        $('.result-percentage').text(Math.round(article.detection_score) + '%');
                        
                        var resultVerdict = '';
                        if(article.detection_score < 25) {
                            resultVerdict = 'Likely Legit';
                        } else if(article.detection_score < 50) {
                            resultVerdict = 'Suspicious';
                        } else {
                            resultVerdict = 'Likely Fake';
                        }
                        
                        $('#result-verdict').text(resultVerdict);
                        
                        // Process article content - highlight fake keywords
                        var content = article.content;
                        var highlightedContent = content;
                        
                        if(article.fake_keywords && article.fake_keywords.length > 0) {
                            // Sort keywords by length (longest first) to avoid nested replacements
                            article.fake_keywords.sort(function(a, b) {
                                return b.length - a.length;
                            });
                            
                            // We'll use a placeholder approach to prevent nested highlighting
                            var placeholders = [];
                            var count = 0;
                            
                            // First pass: replace keywords with placeholders
                            article.fake_keywords.forEach(function(keyword) {
                                var escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                                var regex = new RegExp('\\b(' + escapedKeyword + ')\\b', 'gi');
                                
                                highlightedContent = highlightedContent.replace(regex, function(match) {
                                    var placeholder = '___KEYWORD_PLACEHOLDER_' + count + '___';
                                    placeholders.push({
                                        placeholder: placeholder,
                                        original: match
                                    });
                                    count++;
                                    return placeholder;
                                });
                            });
                            
                            // Second pass: replace placeholders with highlighted spans
                            placeholders.forEach(function(item) {
                                highlightedContent = highlightedContent.replace(
                                    item.placeholder, 
                                    '<span class="highlighted-keyword">' + item.original + '</span>'
                                );
                            });
                            
                            // Update the keyword count
                            $('#keyword-count').text('Based on the detection of ' + article.fake_keywords.length + ' fake-related keywords');
                        } else {
                            $('#keyword-count').text('Based on the detection of 0 fake-related keywords');
                        }
                        
                        // Set the content with highlighted keywords
                        $('#article-content-text').html(highlightedContent);
                        
                        // Always show both buttons with consistent labels
                        $('.approve-btn').show().text('Mark as Legit');
                        $('.fake-btn').show().text('Mark as Fake');
                        
                        // Store the article ID for the action buttons
                        $('.approve-btn, .fake-btn').data('article-id', article.id);
                        
                        // Show the modal
                        $('#articleViewModal').fadeIn();
                    } else {
                        NotificationSystem.error('Error: ' + response.message);
                    }
                },
                error: function() {
                    NotificationSystem.error('Error: Could not fetch article details');
                }
            });
        });

        // Add event handler for delete article button
        $('.delete-article').on('click', function(e) {
            e.preventDefault();
            var articleId = $(this).data('id');
            $('#deleteArticleId').val(articleId);
            $('#confirmDeleteArticleModal').fadeIn();
        });
    }
    
    // Handle Mark as Legit button click
    $('.approve-btn').click(function() {
        var articleId = $(this).data('article-id');
        // Show confirmation modal instead of immediate action
        $('#confirmLegitBtn').data('article-id', articleId);
        $('#confirmLegitModal').fadeIn();
    });
    
    // Handle Mark as Fake button click
    $('.fake-btn').click(function() {
        var articleId = $(this).data('article-id');
        // Show confirmation modal instead of immediate action
        $('#confirmFakeBtn').data('article-id', articleId);
        $('#confirmFakeModal').fadeIn();
    });
    
    // Handle confirm legit button
    $('#confirmLegitBtn').click(function() {
        var articleId = $(this).data('article-id');
        updateArticleStatus(articleId, 'legit');
        $('#confirmLegitModal').fadeOut();
    });
    
    // Handle confirm fake button
    $('#confirmFakeBtn').click(function() {
        var articleId = $(this).data('article-id');
        updateArticleStatus(articleId, 'fake');
        $('#confirmFakeModal').fadeOut();
    });
    
    // Handle cancel buttons and close icons for confirmation modals
    $('#cancelLegitBtn, #cancelFakeBtn, .close-confirm').click(function() {
        $('#confirmLegitModal, #confirmFakeModal').fadeOut();
    });
    
    // Close confirmation modals when clicking outside
    $(window).click(function(e) {
        if($(e.target).is('#confirmLegitModal')) {
            $('#confirmLegitModal').fadeOut();
        } else if($(e.target).is('#confirmFakeModal')) {
            $('#confirmFakeModal').fadeOut();
        }
    });
    
    // Function to update article status
    function updateArticleStatus(articleId, status) {
        $.ajax({
            url: '../../process/article_process.php',
            type: 'POST',
            data: {
                action: 'update_article_status',
                article_id: articleId,
                status: status
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    var statusText = status === 'legit' ? 'legitimate' : 'fake';
                    NotificationSystem.success('Article marked as ' + statusText + ' successfully!');
                    // Close the modal
                    $('#articleViewModal').fadeOut();
                    // Reload current page of articles
                    loadArticles(currentPage, statusFilter, searchTerm);
                } else {
                    NotificationSystem.error('Error: ' + response.message);
                }
            },
            error: function() {
                NotificationSystem.error('Error: Could not update article status');
            }
        });
    }

    // Error handling for AJAX requests
    function handleAjaxError(message) {
        NotificationSystem.error('Error: ' + message);
    }

    // Error loading articles
    function showArticleLoadError(response) {
        if (response && response.message) {
            NotificationSystem.error('Error loading articles: ' + response.message);
        } else {
            NotificationSystem.error('Error: Could not load articles');
        }
    }

    // Error loading categories
    function showCategoryLoadError(response) {
        if (response && response.message) {
            NotificationSystem.error('Error loading categories: ' + response.message);
        } else {
            NotificationSystem.error('Error: Could not load categories');
        }
    }

    // Error loading keywords
    function showKeywordLoadError(response) {
        if (response && response.message) {
            NotificationSystem.error('Error loading keywords: ' + response.message);
        } else {
            NotificationSystem.error('Error: Could not load keywords');
        }
    }

    // Handle confirm delete article button
    $('#confirmDeleteArticleBtn').click(function() {
        var articleId = $('#deleteArticleId').val();
        deleteArticle(articleId);
        $('#confirmDeleteArticleModal').fadeOut();
    });

    // Update the cancel buttons click handler to include the new delete modal
    $('#cancelLegitBtn, #cancelFakeBtn, #cancelDeleteArticleBtn, .close-confirm').click(function() {
        $('#confirmLegitModal, #confirmFakeModal, #confirmDeleteArticleModal').fadeOut();
    });

    // Update the window click handler to include the new delete modal
    $(window).click(function(e) {
        if($(e.target).is('#confirmLegitModal')) {
            $('#confirmLegitModal').fadeOut();
        } else if($(e.target).is('#confirmFakeModal')) {
            $('#confirmFakeModal').fadeOut();
        } else if($(e.target).is('#confirmDeleteArticleModal')) {
            $('#confirmDeleteArticleModal').fadeOut();
        }
    });

    // Add delete article function
    function deleteArticle(articleId) {
        $.ajax({
            url: '../../process/article_process.php',
            type: 'POST',
            data: {
                action: 'delete',
                article_id: articleId,
                soft_delete: true  // Explicitly indicate this is a soft delete
            },
            dataType: 'json',
            success: function(response) {
                console.log('Delete article response:', response);
                // Check both possible success indicators
                if(response.status === 'success' || response.success === true) {
                    NotificationSystem.success('Article successfully hidden from public view');
                    // Reload current page of articles
                    loadArticles(currentPage, statusFilter, searchTerm);
                } else {
                    NotificationSystem.error('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Delete article error:', {xhr, status, error});
                NotificationSystem.error('Error: Could not remove the article');
            }
        });
    }
});
</script>

<!-- Include our notification system -->
<script src="../../assets/js/notification.js?v=<?php echo time(); ?>"></script>


<?php include_once '../includes/footer.php'; ?>