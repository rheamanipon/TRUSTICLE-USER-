<?php include_once '../includes/header.php'; ?>
<?php
require_once "../../config/connection.php";

// Get user statistics - exclude inactive and deleted users
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE is_active = 1 AND is_deleted = 0");
$stmt->execute();
$users_result = $stmt->get_result();
$total_users = 0;
if ($row = $users_result->fetch_assoc()) {
    $total_users = (int)($row['total'] ?? 0);
}
$stmt->close();

// Get article statistics
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'legit' THEN 1 ELSE 0 END) as legit,
    SUM(CASE WHEN status = 'fake' THEN 1 ELSE 0 END) as fake
FROM articles 
WHERE is_visible = 1");

$stmt->execute();
$result = $stmt->get_result();

$total_articles = 0;
$pending_articles = 0;
$legit_articles = 0;
$fake_articles = 0;

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
    $fakeArticlesQuery = $conn->prepare("SELECT content FROM articles WHERE status = 'fake' AND is_visible = 1");
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
?>

<div class="container">
    <!-- Sidebar is included in the header.php file -->
    <div class="content-area">
        <div class="page-header">
            <h1 class="page-title">Analytics</h1>
        </div>
        
        <div class="analytics-content">
            <div class="welcome-message">
                <h1>Analytics Overview</h1>
                <p>Monitor article distribution and keyword trends.</p>
            </div>
            
            <!-- Stats Grid - 4 Cards -->
            <div class="stats-grid">
                <!-- Total Users Card -->
                <div class="stat-card blue" onclick="window.location.href='user_management.php'">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Users</div>
                        <div class="stat-value"><?php echo $total_users; ?></div>
                    </div>
                </div>
                
                <!-- Total Submitted Articles Card -->
                <div class="stat-card yellow" data-filter="all">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Submitted Articles</div>
                        <div class="stat-value"><?php echo $total_articles; ?></div>
                    </div>
                </div>
                
                <!-- Total Reviewed Articles Card -->
                <div class="stat-card green" data-filter="reviewed">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Reviewed Articles</div>
                        <div class="stat-value"><?php echo $legit_articles + $fake_articles; ?></div>
                    </div>
                </div>
                
                <!-- Total Pending Articles Card -->
                <div class="stat-card red" data-filter="pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Pending Articles</div>
                        <div class="stat-value"><?php echo $pending_articles; ?></div>
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

<!-- Include Chart.js before our own scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Make article count data available to JavaScript
    window.articleData = {
        pending: <?php echo $pending_articles; ?>,
        legit: <?php echo $legit_articles; ?>,
        fake: <?php echo $fake_articles; ?>,
        total: <?php echo $total_articles; ?>
    };
    
    // Make keyword data available to JavaScript
    window.keywordData = {
        labels: <?php echo json_encode(array_keys($topKeywords)); ?>,
        values: <?php echo json_encode(array_values($topKeywords)); ?>
    };
</script>
<script src="../assets/js/analytics.js"></script>

<style>
    .stat-card {
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
</style>

<script>
    // Handle card clicks to navigate to manage_article.php with filter
    document.addEventListener('DOMContentLoaded', function() {
        // Get all article stat cards (except users card)
        const articleCards = document.querySelectorAll('.stat-card[data-filter]');
        
        // Add click event to each card
        articleCards.forEach(card => {
            card.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                window.location.href = 'manage_article.php?filter=' + filter;
            });
        });
    });
</script>

<?php include_once '../includes/footer.php'; ?>