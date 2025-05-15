<?php
// Common date format patterns to include in search queries
$date_format_patterns = <<<SQL
    OR DATE_FORMAT(a.date_published, '%Y-%m-%d') LIKE ? 
    OR DATE_FORMAT(a.date_published, '%b %d, %Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%M %d, %Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%d/%m/%Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%m/%d/%Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%d-%m-%Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%b %Y') LIKE ?
    OR DATE_FORMAT(a.date_published, '%M %Y') LIKE ?
SQL;

// Helper function to add date parameters to an array of params
function addDateParameters(&$params, $search) {
    $search_param = "%$search%";
    $date_search = "$search%"; // For year patterns
    
    // Add date format parameters
    $params[] = $date_search;  // date Y-m-d LIKE ?
    $params[] = $search_param; // date b d, Y LIKE ?
    $params[] = $search_param; // date M d, Y LIKE ?
    $params[] = $search_param; // date d/m/Y LIKE ?
    $params[] = $search_param; // date m/d/Y LIKE ?
    $params[] = $search_param; // date d-m-Y LIKE ?
    $params[] = $date_search;  // date Y LIKE ?
    $params[] = $search_param; // date b Y LIKE ?
    $params[] = $search_param; // date M Y LIKE ?
}
?> 