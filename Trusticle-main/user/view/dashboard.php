<?php
session_start();
require_once "../../utils/user.php";
require_once "../../config/connection.php";

// Check if user is logged in
if (!isLoggedIn()) {
    // Redirect to login page if not logged in
    header("Location: ../../auth/login.php");
    exit();
}

// Get user info
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Get user first name
$stmt = $conn->prepare("SELECT first_name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$first_name = $username; // Default to username if first_name not found
if ($row = $result->fetch_assoc()) {
    $first_name = $row['first_name'];
}
$stmt->close();

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
    $total_articles = $row['total'] ?? 0;
    $pending_articles = $row['pending'] ?? 0;
    $legit_articles = $row['legit'] ?? 0;
    $fake_articles = $row['fake'] ?? 0;
}
$stmt->close();

// Get recent articles
$stmt = $conn->prepare("SELECT 
    a.id, 
    a.title, 
    u.username as author, 
    c.name as category, 
    a.created_at as date_published, 
    a.status
FROM articles a
LEFT JOIN users u ON a.user_id = u.id
LEFT JOIN categories c ON a.category_id = c.id
WHERE a.user_id = ? AND a.is_visible = 1
ORDER BY a.created_at DESC
LIMIT 5");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_articles = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Trusticle</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../includes/header.php';?>
        <!-- Main Content Area -->
        <div class="main-content">

            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <div class="welcome-message">
                    <h1>Welcome, <?php echo htmlspecialchars($first_name); ?>!</h1>
                    <p>Here's what's happening with your articles today.</p>
                </div>

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
                
                <!-- Recent Articles Table -->
                <div class="articles-card">
                    <div class="articles-card-header">
                        <div class="articles-card-title">My Recent Articles</div>
                    </div>
                    <table class="articles-table">
                        <thead>
                            <tr>
                                <th>Title</th>
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
                                        <td><?php echo htmlspecialchars($article['category']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($article['date_published'])); ?></td>
                                        <td>
                                            <?php if ($article['status'] == 'pending'): ?>
                                                <span class="result-pending">Pending</span>
                                            <?php elseif ($article['status'] == 'legit'): ?>
                                                <span class="result-real">Real</span>
                                            <?php elseif ($article['status'] == 'fake'): ?>
                                                <span class="result-fake">Fake</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center;">No articles found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    // Improved dashboard card navigation
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
                
                // Log navigation info for debugging
                console.log('NAVIGATION: Dashboard card clicked for filter: ' + filter);
                console.log('NAVIGATION: Redirecting to my_articles.php?filter=' + filter);
                
                // Create the target URL
                const targetUrl = 'my_articles.php?filter=' + filter;
                
                // Use direct window location navigation
                window.location.href = targetUrl;
                
                // Prevent default action if somehow an <a> tag is still involved
                e.preventDefault();
            });
        });
    });
</script>
</html>
