<?php include_once '../includes/header.php'; ?>

<!-- Start session if not already started -->
<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection if not already included
if (!isset($conn)) {
    require_once "../../config/connection.php";
}

// Get admin's first name
$user_id = $_SESSION['user_id'];
$first_name = "Admin"; // Default if first_name not found

$stmt = $conn->prepare("SELECT first_name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $first_name = $row['first_name'];
}
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
                <h1>Welcome, <?php echo htmlspecialchars($first_name); ?>!</h1>
                <p>Monitor and manage all articles and user activities.</p>
            </div>
            
            <!-- Stats Grid - 4 Cards -->
            <div class="stats-grid">
                <!-- Total Users Card -->
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Users</div>
                        <div class="stat-value">0</div>
                    </div>
                </div>
                
                <!-- Total Submitted Articles Card -->
                <div class="stat-card yellow">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Submitted Articles</div>
                        <div class="stat-value">0</div>
                    </div>
                </div>
                
                <!-- Total Approved Articles Card -->
                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Approved Articles</div>
                        <div class="stat-value">0</div>
                    </div>
                </div>
                
                <!-- Total Pending Articles Card -->
                <div class="stat-card red">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-title">Total Pending Articles</div>
                        <div class="stat-value">0</div>
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
                        <tr>
                            <td>Gobyerno di umano bumibili ng mga gamot para COVID-19</td>
                            <td>John Doe</td>
                            <td>Politics</td>
                            <td>May 1, 2023</td>
                            <td><span class="result-fake">Fake</span></td>
                        </tr>
                        <tr>
                            <td>Pagbaba ng presyo ng gasolina, inaasahan sa susunod na linggo</td>
                            <td>Jane Smith</td>
                            <td>Economy</td>
                            <td>May 3, 2023</td>
                            <td><span class="result-real">Real</span></td>
                        </tr>
                        <tr>
                            <td>Bagong bakuna laban sa COVID-19, aprubado na ng FDA</td>
                            <td>Robert Johnson</td>
                            <td>Health</td>
                            <td>May 5, 2023</td>
                            <td><span class="result-real">Real</span></td>
                        </tr>
                        <tr>
                            <td>Aliens, dumating na sa Pilipinas ayon sa isang viral video</td>
                            <td>Maria Garcia</td>
                            <td>Technology</td>
                            <td>May 7, 2023</td>
                            <td><span class="result-fake">Fake</span></td>
                        </tr>
                        <tr>
                            <td>Bagong programang pabahay, inilunsad ng DSWD</td>
                            <td>Alex Williams</td>
                            <td>Society</td>
                            <td>May 9, 2023</td>
                            <td><span class="result-pending">Pending</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>