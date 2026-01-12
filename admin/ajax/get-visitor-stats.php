<?php
require_once '../includes/config.php';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Get period from request
$period = isset($_GET['period']) ? $_GET['period'] : 'year';

// Validate period
if (!in_array($period, ['week', 'month', 'year'])) {
    $period = 'year';
}

// Get visitor stats using our function that returns hardcoded data
$visitor_stats = get_visitor_stats($period);

// Add a small delay to simulate database query
usleep(300000); // 300ms delay

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'period' => $period,
    'labels' => $visitor_stats['labels'],
    'data' => $visitor_stats['data']
]);
?>