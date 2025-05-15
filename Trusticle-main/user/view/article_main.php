<?php
// Include the database connection
require_once '../../config/connection.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

// Debugging information
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get categories for the dropdown
$categories = [];
$categoryQuery = "SELECT id, name FROM categories ORDER BY name";
$result = $conn->query($categoryQuery);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Include the header (which contains the sidebar)
include '../includes/header.php';
?>

<!-- Main Content -->
<link rel="stylesheet" href="../../assets/css/articles.css">
<link rel="stylesheet" href="../../assets/css/styles.css">
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Articles</h1>
    </div>

    <div class="articles-content">
            <div class="welcome-message">
                <h1>My Articles</h1>
                <p>Here's your articles.</p>
            </div>
    
    <!-- Hidden field to store current username for comments -->
    <span id="currentUsername" class="hidden"><?php echo htmlspecialchars($_SESSION['username']); ?></span>

    <div>
        <div class="search-container">
            <div class="search-box">
                <input type="text" class="search-input" placeholder="Search by title, content, category, or date...">
                <button class="search-button"><i class="fas fa-search"></i></button>
            </div>
            <div class="filter-wrapper">
                <button class="filter-button"><i class="fas fa-filter"></i></button>
                <div class="filter-dropdown">
                    <a href="#" class="filter-item active" data-status="all">All</a>
                    <a href="#" class="filter-item" data-status="pending">Pending</a>
                    <a href="#" class="filter-item" data-status="legit">Legit</a>
                    <a href="#" class="filter-item" data-status="fake">Fake</a>
                </div>
            </div>
        </div>
        
        <!-- Article listings container -->
        <div class="article-listings">
            <!-- Articles will be loaded here via AJAX -->
            <div class="loading">Loading articles...</div>
        </div>
        
        <a href="my_articles.php" class="see-all-button">See all my articles</a>
        
        <div class="section-divider"></div>
        
        <h2 class="subheader">Community Articles</h2>
        
        <!-- Community articles container -->
        <div class="community-articles-container">
            <div class="loading">Loading community articles...</div>
        </div>
        
        <a href="community_articles.php" class="see-all-button">See all community articles</a>
    </div>
</div>

<!-- Add the floating plus button at the bottom of the main-content div -->
<div class="floating-button" id="addArticleBtn">
    <i class="fas fa-plus"></i>
</div>

<!-- Article Details Modal -->
<div id="articleDetailsModal" class="modal-overlay hidden">
    <div class="modal article-details-modal">
        <div class="modal-header">
            <h2 class="modal-title" id="articleModalTitle">Article Title</h2>
            <div class="modal-header-actions">
                <button id="editArticleModalBtn" class="header-action-button edit-button" style="display:none;">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button id="deleteArticleModalBtn" class="header-action-button delete-button" style="display:none;">
                    <i class="fas fa-trash"></i> Delete
                </button>
                <button id="closeArticleDetailsBtn" class="close-button">&times;</button>
            </div>
        </div>
        
        <div class="article-modal-content">
            <div class="article-meta">
                <div id="articleModalAuthor"><i class="fas fa-user"></i> <span>Author</span></div>
                <div id="articleModalCategory"><i class="fas fa-folder"></i> <span>Category</span></div>
                <div id="articleModalDate"><i class="fas fa-calendar"></i> <span>Date</span></div>
            </div>
            
            <!-- Status Badge -->
            <div class="article-status-container">
                <div id="articleModalStatus" class="article-status status-pending">
                    Pending – Waiting for Admin Review
                </div>
            </div>

            <!-- Article Content with Highlighted Keywords -->
                <div class="content-section">
                    <h4 class="content-heading">Article Content</h4>
                    <div id="articleModalContent" class="article-content"></div>
                </div>
                
                <!-- Source Information -->
                <div id="articleModalSource" class="article-source"></div>
            
            <!-- Fakeness Meter -->
            <div class="article-fakeness-meter">
                <h3>Fakeness Score: <span id="articleModalScore">0</span>%</h3>
                <p>Based on the detection of <span id="articleModalKeywordCount">0</span> fake-related keywords</p>
                
                <!-- Prediction label based on fakeness score -->
                <div id="articlePredictionLabel" class="status-badge">Likely Legitimate</div>
                
                <div class="meter-bar">
                    <div id="articleModalScoreIndicator" class="meter-indicator" style="left: 0%;"></div>
                </div>
                <div class="meter-label">
                    <span>0-24% - Likely Legitimate</span>
                    <span>25-49% - Suspicious</span>
                    <span>50-100% - Likely Fake</span>
                </div>
            </div>
            
            <!-- Comments Section -->
            <div class="comments-section">
                <h4 class="content-heading">Comments</h4>
                <div id="articleComments" class="comments-container">
                    <!-- Comments will be loaded here -->
                </div>
                
                <div class="comment-form">
                    <textarea id="commentText" placeholder="Add your comment..."></textarea>
                    <button id="submitComment" class="comment-submit-btn">Post Comment</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Modify the article modal to get categories from the database
ob_start();
include '../includes/modal.php';
$modalContent = ob_get_clean();

// Replace the static category options with dynamic ones from database
$categoryOptions = '<option value="" disabled selected>Category</option>';
foreach ($categories as $category) {
    $categoryOptions .= '<option value="' . $category['id'] . '">' . htmlspecialchars($category['name']) . '</option>';
}

// Replace the category options in the modal content
$modalContent = preg_replace('/<option value="" disabled selected>Category<\\/option>.*?<\\/select>/s', 
    '<option value="" disabled selected>Category</option>' . $categoryOptions . '</select>', $modalContent);

echo $modalContent;
include '../includes/footer.php';
?>

<!-- Include jQuery if not already included -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Include jQuery UI for datepicker -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<!-- Include our notification system -->
<script src="../../assets/js/notification.js?v=<?php echo time(); ?>"></script>

<!-- Include our article.js file -->
<script src="../../assets/js/article.js?v=<?php echo time(); ?>"></script>

<!-- Confirmation Modal -->
<dialog id="confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="confirm-dialog-title" aria-describedby="confirm-dialog-desc">
    <h2 id="confirm-dialog-title">Confirm Action</h2>
    <p id="confirm-dialog-desc"></p>
    <div class="dialog-buttons">
        <button id="confirm-no" class="action-button">Cancel</button>
        <button id="confirm-yes" class="action-button">Confirm</button>
    </div>
</dialog>

<!-- Info Modal (for messages replacing alert) -->
<dialog id="info-dialog" role="alertdialog" aria-modal="true" aria-labelledby="info-dialog-title" aria-describedby="info-dialog-desc">
    <h2 id="info-dialog-title"></h2>
    <p id="info-dialog-desc"></p>
    <div class="dialog-buttons">
        <button id="info-ok" class="action-button">OK</button>
    </div>
</dialog>

</body>
</html>