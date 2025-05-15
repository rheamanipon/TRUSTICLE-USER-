<?php include_once '../includes/header.php'; ?>

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
                        <div class="filter-option" data-filter="real">Real</div>
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
                
                <button class="btn btn-primary">
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
                        <tr>
                            <td>0</td>
                            <td>Artificial Intelligence in Healthcare</td>
                            <td>Regina Manipon</td>
                            <td>Health</td>
                            <td>01/24/2025</td>
                            <td><a href="#" class="url-btn">url</a></td>
                            <td>Real</td>
                            <td class="status-pending">Pending</td>
                            <td><a href="#" class="view-btn" data-id="0"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>Artificial Intelligence in the Workplace</td>
                            <td>Jansdale Yusi</td>
                            <td>Sports</td>
                            <td>01/24/2025</td>
                            <td><a href="#" class="url-btn">https://missu.com</a></td>
                            <td>Real</td>
                            <td class="status-real">Real</td>
                            <td><a href="#" class="view-btn" data-id="1"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Artificial Intelligence in the Workplace</td>
                            <td>Regie Manipon</td>
                            <td>Sports</td>
                            <td>01/24/2025</td>
                            <td><a href="#" class="url-btn">url</a></td>
                            <td>Fake</td>
                            <td class="status-fake">Fake</td>
                            <td><a href="#" class="view-btn" data-id="2"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Artificial Intelligence in the Workplace</td>
                            <td>Rikki Garcia</td>
                            <td>Politics</td>
                            <td>01/24/2025</td>
                            <td><a href="#" class="url-btn">url</a></td>
                            <td>Real</td>
                            <td class="status-pending">Pending</td>
                            <td><a href="#" class="view-btn" data-id="3"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Artificial Intelligence in the Workplace</td>
                            <td>Jackie Manipon</td>
                            <td>Education</td>
                            <td>01/24/2025</td>
                            <td><a href="#" class="url-btn">url</a></td>
                            <td>Fake</td>
                            <td class="status-pending">Pending</td>
                            <td><a href="#" class="view-btn" data-id="4"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Artificial Intelligence in the Workplace</td>
                            <td>Lovely Punto</td>
                            <td>Technology</td>
                            <td>01/24/2025</td>
                            <td><a href="#" class="url-btn">url</a></td>
                            <td>Fake</td>
                            <td class="status-fake">Fake</td>
                            <td><a href="#" class="view-btn" data-id="5"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Artificial Intelligence in the Workplace</td>
                            <td>Josh Viray</td>
                            <td>Entertainment</td>
                            <td>01/24/2025</td>
                            <td><a href="#" class="url-btn">url</a></td>
                            <td>Real</td>
                            <td class="status-real">Real</td>
                            <td><a href="#" class="view-btn" data-id="6"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>Artificial Intelligence in the Workplace</td>
                            <td>Ani Garcia</td>
                            <td>Travel</td>
                            <td>01/24/2025</td>
                            <td><a href="#" class="url-btn">url</a></td>
                            <td>Fake</td>
                            <td class="status-pending">Pending</td>
                            <td><a href="#" class="view-btn" data-id="7"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>Artificial Intelligence in the Workplace</td>
                            <td>Reiner Manalang</td>
                            <td>Culture</td>
                            <td>01/24/2025</td>
                            <td><a href="#" class="url-btn">url</a></td>
                            <td>Fake</td>
                            <td class="status-pending">Pending</td>
                            <td><a href="#" class="view-btn" data-id="8"><i class="fas fa-eye"></i></a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Categories Tab Content -->
        <div id="categories" class="tab-content">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>0</td>
                            <td>Health</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>Education</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Sports</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Politics</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Entertainment</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Culture</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Travel</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>Technology</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="action-button-container">
                <button id="newCategoryBtn" class="btn btn-primary">+ New Category</button>
            </div>
        </div>
        
        <!-- Keywords Tab Content -->
        <div id="keywords" class="tab-content">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Keyword</th>
                            <th class="actions-column">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>0</td>
                            <td>Jandale</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>Baho</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Hindi</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Nailigo</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Yuck</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Kadire</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Joke</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>7</td>
                            <td>MWA</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>HEHE</td>
                            <td class="actions-cell">
                                <div class="action-icons-container">
                                    <i class="fas fa-edit action-icon"></i>
                                    <i class="fas fa-trash action-icon"></i>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="action-button-container">
                <button id="newKeywordBtn" class="btn btn-primary">+ New Keyword</button>
            </div>
        </div>
        
        <div class="pagination">
            <a href="#" class="prev"><i class="fas fa-chevron-left"></i> Previous</a>
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#">4</a>
            <a href="#">5</a>
            <a href="#" class="next">Next <i class="fas fa-chevron-right"></i></a>
        </div>
        
        <!-- Modal for New Category -->
        <div id="categoryModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Add New Category</h2> </br>
                <form id="categoryForm">
                    <div class="form-group">
                        <input type="text" id="categoryName" placeholder="Enter category name">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary add-btn">ADD</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Modal for New Keyword -->
        <div id="keywordModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Add New Keyword</h2> </br>
                <form id="keywordForm">
                    <div class="form-group">
                        <input type="text" id="keywordName" placeholder="Enter keyword">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary add-btn">ADD</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Modal for Article View -->
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
                        <p>Phasellus pellentesque, quam sed tempus tempus, dui magna semper urna, placerat tristique diam augue ut nunc. Phasellus pellentesque, quam sed tempus tempus, dui magna semper urna, placerat tristique diam augue ut nunc.</p>
                        <p>Phasellus pellentesque, quam sed tempus tempus, dui magna semper urna, placerat tristique diam augue ut nunc. Phasellus pellentesque, quam sed tempus tempus, dui magna semper urna, placerat tristique diam augue ut nunc.</p>
                        <p>Phasellus pellentesque, quam sed tempus tempus, dui magna semper urna, placerat tristique diam augue ut nunc. Phasellus pellentesque, quam sed tempus tempus, dui magna semper urna, placerat tristique diam augue ut nunc.</p>
                    </div>
                    <div class="article-sidebar">
                        <div class="result-indicator">
                            <div class="result-label">Result</div>
                            <div class="result-percentage">75%</div>
                            <div class="result-text">Real or Fake</div>
                        </div>
                        <div class="tagged-keywords">
                            <div class="keywords-label">Tagged Keywords</div>
                            <div class="keywords-tags">
                                <span class="keyword-tag">Keyword 1</span>
                                <span class="keyword-tag">Keyword 2</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="article-date">
                    <i class="far fa-calendar"></i> 01/24/2025
                </div>
                
                <div class="article-actions">
                    <button class="btn btn-real approve-btn">Approve</button>
                    <button class="btn btn-fake fake-btn">Mark as Fake</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>