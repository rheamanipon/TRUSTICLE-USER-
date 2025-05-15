<?php 
include_once '../includes/header.php';
require_once "../../config/connection.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user article statistics
$total_articles = 0;
$pending_articles = 0;
$legit_articles = 0;
$fake_articles = 0;

// Get article counts
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'legit' THEN 1 ELSE 0 END) as legit,
    SUM(CASE WHEN status = 'fake' THEN 1 ELSE 0 END) as fake
FROM articles 
WHERE user_id = ? AND is_visible = 1");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $total_articles = (int)($row['total'] ?? 0);
    $pending_articles = (int)($row['pending'] ?? 0);
    $legit_articles = (int)($row['legit'] ?? 0);
    $fake_articles = (int)($row['fake'] ?? 0);
}
$stmt->close();

// Get top keywords from fake_keywords table with occurrence counts
$topKeywords = [];

// First, get all keywords from the fake_keywords table
$keywordsQuery = $conn->query("SELECT id, keyword FROM fake_keywords ORDER BY id");
$keywords = [];
while ($keywordRow = $keywordsQuery->fetch_assoc()) {
    $keywords[$keywordRow['id']] = $keywordRow['keyword'];
}

// If we have keywords, let's count their occurrences in fake articles
if (!empty($keywords)) {
    // Get content of fake articles
    $fakeArticlesQuery = $conn->prepare("SELECT content FROM articles WHERE user_id = ? AND status = 'fake' AND is_visible = 1");
    $fakeArticlesQuery->bind_param("i", $user_id);
    $fakeArticlesQuery->execute();
    $fakeArticlesResult = $fakeArticlesQuery->get_result();

    // Initialize count for each keyword
    $keywordCounts = array_fill_keys($keywords, 0);
    
    // Check each article for keyword occurrences
    while ($articleRow = $fakeArticlesResult->fetch_assoc()) {
        $content = $articleRow['content'];
        $content = strtolower($content); // Convert to lowercase for case-insensitive matching
        
        foreach ($keywords as $id => $keyword) {
            // Count occurrences of the keyword phrase in the content
            $keywordLower = strtolower($keyword);
            $count = substr_count($content, $keywordLower);
            if ($count > 0) {
                $keywordCounts[$keyword] += $count;
            }
        }
    }
    $fakeArticlesQuery->close();
    
    // Sort by count (highest first) and take top 8
    arsort($keywordCounts);
    $topKeywords = array_slice($keywordCounts, 0, 8, true);
}

// If we have less than 8 keywords with counts, add the remaining ones with 0 counts
if (count($topKeywords) < 8) {
    $remainingKeywords = array_diff($keywords, array_keys($topKeywords));
    $remainingKeywords = array_slice($remainingKeywords, 0, 8 - count($topKeywords), true);
    foreach ($remainingKeywords as $keyword) {
        $topKeywords[$keyword] = 0;
    }
}

// If still less than 8 (meaning we don't have enough keywords), add placeholders
if (count($topKeywords) < 8) {
    $placeholders = ['No keywords found', 'Add keywords', 'In database', 'To see', 'Trend analysis', 'For fake', 'News articles', 'Detection'];
    $i = count($topKeywords);
    while ($i < 8) {
        $topKeywords[$placeholders[$i]] = 0;
        $i++;
    }
}

// Debug output (will be hidden in the HTML source)
// echo "<!-- Debug: " . json_encode(['total' => $total_articles, 'pending' => $pending_articles, 'legit' => $legit_articles, 'fake' => $fake_articles]) . " -->";
?>
<link rel="stylesheet" href="../../assets/css/analytics.css">
<div class="dashboard-container">
    <!-- Main Content Area -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Analytics</h1>
        </div>
        
        <div class="analytics-content">
            <div class="welcome-message">
                <h1>Analytics Overview</h1>
                <p>Monitor article distribution and keyword trends.</p>
            </div>
            
            <!-- Stats Grid - 4 Cards using dashboard styling -->
            <div class="stat-cards-container">
                <!-- Total Articles Card -->
                <div class="stat-card blue" data-filter="all">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Articles Submitted</div>
                        <div class="stat-value"><?php echo $total_articles; ?></div>
                    </div>
                </div>
                
                <!-- Pending Articles Card -->
                <div class="stat-card yellow" data-filter="pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Pending Articles</div>
                        <div class="stat-value"><?php echo $pending_articles; ?></div>
                    </div>
                </div>
                
                <!-- Legit Articles Card -->
                <div class="stat-card green" data-filter="legit">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Legit Articles</div>
                        <div class="stat-value"><?php echo $legit_articles; ?></div>
                    </div>
                </div>
                
                <!-- Fake Articles Card -->
                <div class="stat-card red" data-filter="fake">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Fake Articles</div>
                        <div class="stat-value"><?php echo $fake_articles; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Grid - Donut and Line Charts -->
            <div class="chart-grid">
                <!-- Donut Chart - Article Distribution -->
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Article Distribution</div>
                    </div>
                    <div class="donut-container">
                        <canvas id="donutChart"></canvas>
                    </div>
                </div>
                
                <!-- Line Chart - Top Fake Keywords -->
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title">Top Fake Keywords</div>
                    </div>
                    <div class="line-chart-container">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Make article count data available to JavaScript as a global variable
    window.articleData = {
        pending: <?php echo intval($pending_articles); ?>,
        legit: <?php echo intval($legit_articles); ?>,
        fake: <?php echo intval($fake_articles); ?>,
        total: <?php echo intval($total_articles); ?>
    };
    
    // Make keyword data available to JavaScript
    window.keywordData = {
        labels: <?php echo json_encode(array_keys($topKeywords)); ?>,
        values: <?php echo json_encode(array_values($topKeywords)); ?>
    };
    
    console.log("Article data:", window.articleData); // Debug data
    console.log("Keyword data:", window.keywordData); // Debug keyword data
</script>
<script src="../../assets/js/analytics.js"></script>
<script>
    // Improved dashboard card navigation - same as in dashboard.php
    document.addEventListener('DOMContentLoaded', function() {
        // Get all stat cards
        const cards = document.querySelectorAll('.stat-card');
        
        // Process all cards
        cards.forEach(card => {
            // Make sure cards are visibly clickable
            card.style.cursor = 'pointer';
            
            // Add click event with direct navigation
            card.addEventListener('click', function(e) {
                // Get the filter value directly from the data attribute
                const filter = this.getAttribute('data-filter');
                
                // Create the target URL
                const targetUrl = 'my_articles.php?filter=' + filter;
                
                // Use direct window location navigation
                window.location.href = targetUrl;
                
                // Prevent default action if somehow an <a> tag is still involved
                e.preventDefault();
            });
        });
        
        // Check sidebar state and update main content accordingly
        const sidebar = document.getElementById('sidebar');
        if (sidebar.classList.contains('collapsed')) {
            document.querySelector('.main-content').classList.add('expanded');
        }
        
        // Listen for sidebar toggle
        document.addEventListener('sidebarToggled', function(e) {
            document.querySelector('.main-content').classList.toggle('expanded');
        });
    });
</script>
<?php include_once '../includes/footer.php'; ?>