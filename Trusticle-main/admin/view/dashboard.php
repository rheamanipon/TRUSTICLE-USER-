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

// Get recent articles (limit to 5)
$stmt = $conn->prepare("SELECT 
    a.title, 
    u.username as author, 
    c.name as category, 
    a.date_published, 
    a.status
FROM articles a
LEFT JOIN users u ON a.user_id = u.id
LEFT JOIN categories c ON a.category_id = c.id
WHERE a.is_visible = 1
ORDER BY a.created_at DESC
LIMIT 5");

$stmt->execute();
$recent_articles = $stmt->get_result();
$stmt->close();
?>

<div class="container">
    <!-- Sidebar is included in the header.php file -->
    <div class="content-area">
        <div class="page-header">
            <h1 class="page-title">Dashboard</h1>
        </div>
        
        <div class="dashboard-content">
            <div class="welcome-message">
                <h1>Welcome, <?php echo htmlspecialchars($user_first_name); ?>!</h1>
                <p>Monitor and manage all articles and user activities.</p>
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
                
                <!-- Total Approved Articles Card -->
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
            
            <!-- Recent Articles Table -->
            <div class="articles-card">
                <div class="articles-card-header">
                    <div class="articles-card-title">Recent Articles</div>
                </div>
                <table class="articles-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Date Published</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_articles->num_rows > 0): ?>
                            <?php while ($article = $recent_articles->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($article['title']); ?></td>
                                    <td><?php echo htmlspecialchars($article['author']); ?></td>
                                    <td><?php echo htmlspecialchars($article['category']); ?></td>
                                    <td><?php echo $article['date_published'] ? date('M j, Y', strtotime($article['date_published'])) : 'N/A'; ?></td>
                                    <td>
                                        <?php if ($article['status'] == 'pending'): ?>
                                            <span class="result-pending">Pending</span>
                                        <?php elseif ($article['status'] == 'legit'): ?>
                                            <span class="result-legit">Legit</span>
                                        <?php elseif ($article['status'] == 'fake'): ?>
                                            <span class="result-fake">Fake</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No articles found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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