<?php
// Start the session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include DB connection first, before any output
include_once '../../includes/db_connect.php';

// Get the filter value if set
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filter_condition = '';
if ($filter !== 'all') {
    $filter_condition = "AND u.role = '" . mysqli_real_escape_string($conn, $filter) . "'";
}

// Handle export functionality BEFORE any HTML output
if(isset($_POST['export'])) {
    // For export, we want all records, not just the current page
    // Apply filter if one is set
    $export_query = "SELECT a.id, a.action, a.timestamp, u.first_name, u.last_name, u.role 
                    FROM activity_logs a 
                    JOIN users u ON a.user_id = u.id 
                    WHERE 1=1 $filter_condition
                    ORDER BY a.timestamp DESC";
    $export_result = mysqli_query($conn, $export_query);
    
    // Set headers for CSV download
    $filename = 'activity_log_export_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, ['ID', 'Name', 'Role', 'Activity', 'Time']);
    
    // Function to clean text for CSV export
    function cleanForCSV($text) {
        // Remove HTML tags
        $text = strip_tags($text);
        // Remove any PHP code or script tags that might be present
        $text = preg_replace('/<\?(.*?)\?>/s', '', $text);
        // Normalize quotes and apostrophes
        $text = str_replace(
            array('"', '"', "'", "'", '&quot;', '&apos;'), 
            array('"', '"', "'", "'", '"', "'"), 
            $text
        );
        return trim($text);
    }
    
    // Add data rows
    while($row = mysqli_fetch_assoc($export_result)) {
        $csvRow = [
            cleanForCSV($row['id']),
            cleanForCSV($row['first_name'] . ' ' . $row['last_name']),
            cleanForCSV($row['role']),
            cleanForCSV($row['action']),
            date('Y-m-d h:i A', strtotime($row['timestamp']))
        ];
        fputcsv($output, $csvRow);
    }
    
    fclose($output);
    exit();
}

// Now include the header file that outputs HTML
include_once '../includes/header.php';

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Count total records for pagination with filter applied
$count_query = "SELECT COUNT(*) as total FROM activity_logs a JOIN users u ON a.user_id = u.id WHERE 1=1 $filter_condition";
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch activity logs with user information with pagination and filter
$query = "SELECT a.id, a.action, a.timestamp, u.first_name, u.last_name, u.role 
          FROM activity_logs a 
          JOIN users u ON a.user_id = u.id 
          WHERE 1=1 $filter_condition
          ORDER BY a.timestamp DESC
          LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $query);
?>

<div class="container">
    <!-- Sidebar is included in the header.php file -->
    <div class="content-area">
        <div class="page-header">
            <h1 class="page-title">Activity Log</h1>
        </div>

        <div class="action-bar activity-action-bar">
            <div class="search-container">
                <input type="text" id="search-input" class="search-input" placeholder="Search for user, activity...">
                <button class="search-icon"><i class="fas fa-search"></i></button>
            </div>
            <div class="actions-container">
                <div class="filter-container">
                    <button id="filter-btn" class="btn btn-outline">
                        <i class="fas fa-filter"></i> <span><?php echo ucfirst($filter); ?></span>
                    </button>
                    <div id="filter-dropdown" class="filter-dropdown">
                        <div class="filter-option <?php echo $filter === 'all' ? 'active' : ''; ?>" data-filter="all">All</div>
                        <div class="filter-option <?php echo $filter === 'admin' ? 'active' : ''; ?>" data-filter="admin">Admin</div>
                        <div class="filter-option <?php echo $filter === 'user' ? 'active' : ''; ?>" data-filter="user">User</div>
                    </div>
                </div>
                <form method="post">
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    <button type="submit" name="export" class="btn btn-primary">
                        <i class="fas fa-download"></i> Export
                    </button>
                </form>
            </div>
        </div>

        <div class="table-container">
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Activity</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                            echo "<td><span class='role-badge role-" . strtolower($row['role']) . "'>" . ucfirst($row['role']) . "</span></td>";
                            echo "<td>" . $row['action'] . "</td>";
                            echo "<td>" . date('h:i A', strtotime($row['timestamp'])) . "</td>";
                            echo "<td><i class='fas fa-trash action-icon delete-log' data-id='" . $row['id'] . "'></i></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No activity logs found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?php if($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>" class="prev"><i class="fas fa-chevron-left"></i> Previous</a>
            <?php else: ?>
                <span class="prev disabled"><i class="fas fa-chevron-left"></i> Previous</span>
            <?php endif; ?>
            
            <?php
            // Calculate range of page numbers to display
            $range = 2; // Display 2 pages before and after current page
            $start_page = max(1, $page - $range);
            $end_page = min($total_pages, $page + $range);
            
            for($i = $start_page; $i <= $end_page; $i++): ?>
                <?php if($i == $page): ?>
                    <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>" class="active"><?php echo $i; ?></a>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if($end_page < $total_pages): ?>
                <a href="#">...</a>
                <a href="?page=<?php echo $total_pages; ?>&filter=<?php echo $filter; ?>"><?php echo $total_pages; ?></a>
            <?php endif; ?>
            
            <?php if($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>" class="next">Next <i class="fas fa-chevron-right"></i></a>
            <?php else: ?>
                <span class="next disabled">Next <i class="fas fa-chevron-right"></i></span>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Show filter dropdown
    $('#filter-btn').click(function(e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent the document click handler from immediately closing it
        $('#filter-dropdown').toggle(); // Use toggle() instead of toggleClass('show')
    });
    
    // Close filter dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.filter-container').length) {
            $('#filter-dropdown').hide(); // Use hide() instead of removeClass('show')
        }
    });
    
    // Handle filter options click
    $('.filter-option').on('click', function() {
        const filterValue = $(this).data('filter');
        window.location.href = '?filter=' + filterValue;
    });
});
</script>

<?php include_once '../includes/footer.php'; ?>