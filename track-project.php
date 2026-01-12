<?php
require_once 'includes/config.php';

// Get the order ID if provided
$order_id = isset($_GET['id']) ? trim($_GET['id']) : '';

// Redirect to the unified tracking page
if (!empty($order_id)) {
    header("Location: track.php?id=" . urlencode($order_id) . "&type=project");
    exit;
} else {
    header("Location: track.php");
    exit;
}

// The code below is kept for reference but will not be executed
// Initialize variables
$error_message = '';
$project = null;
$updates = [];

// If order ID is provided, fetch project details
if (!empty($order_id)) {
    try {
        $stmt = $db->prepare("
            SELECT p.*, s.name as status_name, s.color as status_color 
            FROM projects p
            JOIN project_statuses s ON p.status_id = s.id
            WHERE p.order_id = :order_id
        ");
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        $project = $stmt->fetch();
        
        if ($project) {
            // Get project updates
            $stmt = $db->prepare("
                SELECT pu.*, s.name as status_name, s.color as status_color
                FROM project_updates pu
                JOIN project_statuses s ON pu.status_id = s.id
                WHERE pu.project_id = :project_id
                ORDER BY pu.created_at DESC
            ");
            $stmt->bindParam(':project_id', $project['id']);
            $stmt->execute();
            $updates = $stmt->fetchAll();
        } else {
            $error_message = "No project found with the provided Order ID.";
        }
    } catch (PDOException $e) {
        $error_message = "Error retrieving project information.";
    }
}

// Page title
$page_title = "Track Your Project";
include 'includes/header.php';
?>

<section class="track-project-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold">Track Your Project</h1>
                    <p class="lead">Enter your Order ID to check the status of your project</p>
                </div>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($project)): ?>
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form action="" method="get" class="row g-3 justify-content-center">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control form-control-lg" name="id" placeholder="Enter your Order ID" value="<?php echo $order_id; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">Track</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Project Details -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="h4 mb-0">Project Details</h2>
                                <a href="track-project.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-search"></i> Track Another Project
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <h3 class="h5 mb-3"><?php echo $project['title']; ?></h3>
                                    <p class="mb-1"><strong>Order ID:</strong> <?php echo $project['order_id']; ?></p>
                                    <p class="mb-1"><strong>Customer:</strong> <?php echo $project['customer_name']; ?></p>
                                    <p class="mb-3"><strong>Created:</strong> <?php echo date('F j, Y', strtotime($project['created_at'])); ?></p>
                                    
                                    <?php if (!empty($project['description'])): ?>
                                        <p><strong>Description:</strong><br><?php echo nl2br($project['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <div class="status-card p-4 text-center h-100 d-flex flex-column justify-content-center" style="background-color: rgba(<?php echo hexToRgb($project['status_color']); ?>, 0.1); border: 1px solid <?php echo $project['status_color']; ?>; border-radius: 8px;">
                                        <h4 class="text-uppercase mb-3">Current Status</h4>
                                        <div class="status-badge mb-3">
                                            <span class="badge fs-5 px-4 py-2" style="background-color: <?php echo $project['status_color']; ?>; color: #fff;">
                                                <?php echo $project['status_name']; ?>
                                            </span>
                                        </div>
                                        <p class="mb-0">Last Updated: <?php echo date('F j, Y, g:i a', strtotime($project['last_update'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Project Timeline -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h2 class="h4 mb-0">Project Timeline</h2>
                        </div>
                        <div class="card-body p-4">
                            <?php if (!empty($updates)): ?>
                                <div class="timeline">
                                    <?php foreach ($updates as $update): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-marker" style="background-color: <?php echo $update['status_color']; ?>;"></div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <span class="badge" style="background-color: <?php echo $update['status_color']; ?>; color: #fff;">
                                                        <?php echo $update['status_name']; ?>
                                                    </span>
                                                    <span class="timeline-date">
                                                        <?php echo date('F j, Y, g:i a', strtotime($update['created_at'])); ?>
                                                    </span>
                                                </div>
                                                <div class="timeline-body">
                                                    <?php echo nl2br($update['comments']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <p>No updates have been recorded for this project yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-5">
                    <p>If you have any questions about your project, please contact us at <a href="mailto:<?php echo get_setting('contact_email'); ?>"><?php echo get_setting('contact_email'); ?></a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Timeline styles */
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
    display: flex;
}

.timeline-marker {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    margin-right: 15px;
    margin-top: 5px;
    flex-shrink: 0;
}

.timeline-content {
    background-color: #f8f9fa;
    border-radius: 4px;
    padding: 15px;
    flex-grow: 1;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.timeline-date {
    color: #6c757d;
    font-size: 0.9rem;
}

.timeline-body {
    margin-bottom: 10px;
}
</style>

<?php
// Helper function to convert hex color to RGB
function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    
    return "$r, $g, $b";
}

include 'includes/footer.php';
?>