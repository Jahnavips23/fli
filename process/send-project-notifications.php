<?php
/**
 * Process pending project update notifications
 * 
 * This script should be run via cron job to send pending notifications
 * Example cron: */5 * * * * php /path/to/send-project-notifications.php
 */

// Set script execution time limit
set_time_limit(300);

// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/email-helper.php';

// Log file
$log_file = __DIR__ . '/../logs/notification-' . date('Y-m-d') . '.log';

// Function to log messages
function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message" . PHP_EOL;
    
    // Create logs directory if it doesn't exist
    $logs_dir = dirname($log_file);
    if (!file_exists($logs_dir)) {
        mkdir($logs_dir, 0755, true);
    }
    
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Start processing
log_message("Starting notification processing");

try {
    // Get pending notifications
    $stmt = $db->prepare("
        SELECT pu.id
        FROM project_updates pu
        WHERE pu.notify_customer = 1 AND pu.notification_sent = 0
        ORDER BY pu.created_at ASC
        LIMIT 50
    ");
    $stmt->execute();
    $updates = $stmt->fetchAll();
    
    $total_updates = count($updates);
    log_message("Found $total_updates pending notifications");
    
    $success_count = 0;
    $error_count = 0;
    
    // Process each update
    foreach ($updates as $update) {
        $result = send_project_update_notification($update['id']);
        
        if ($result['success']) {
            log_message("Successfully sent notification for update ID: {$update['id']}");
            $success_count++;
        } else {
            log_message("Error sending notification for update ID: {$update['id']} - {$result['message']}");
            $error_count++;
        }
        
        // Add a small delay to prevent overwhelming the mail server
        usleep(500000); // 0.5 seconds
    }
    
    log_message("Completed processing. Success: $success_count, Errors: $error_count");
} catch (Exception $e) {
    log_message("Error: " . $e->getMessage());
}

log_message("Notification processing completed");
?>