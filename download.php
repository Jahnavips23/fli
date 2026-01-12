<?php
require_once 'includes/config.php';

// Get download ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    // Invalid ID, redirect to downloads page
    header('Location: ' . SITE_URL . '/downloads.php');
    exit;
}

try {
    // Get download information
    $stmt = $db->prepare("SELECT * FROM downloads WHERE id = :id AND active = 1");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $download = $stmt->fetch();
    
    if (!$download) {
        // Download not found or not active, redirect to downloads page
        header('Location: ' . SITE_URL . '/downloads.php');
        exit;
    }
    
    // Update download count
    $stmt = $db->prepare("UPDATE downloads SET download_count = download_count + 1 WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    // Get file path
    $file_path = ROOT_PATH . $download['file_path'];
    
    // Check if file exists
    if (!file_exists($file_path)) {
        // File not found, redirect to downloads page with error
        header('Location: ' . SITE_URL . '/downloads.php?error=file_not_found');
        exit;
    }
    
    // Set headers for download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($download['file_path']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    
    // Clear output buffer if it's active
    if (ob_get_level()) {
        ob_clean();
    }
    flush();
    
    // Read file and output to browser
    readfile($file_path);
    exit;
} catch (PDOException $e) {
    // Error occurred, redirect to downloads page with error
    header('Location: ' . SITE_URL . '/downloads.php?error=server_error');
    exit;
}
?>