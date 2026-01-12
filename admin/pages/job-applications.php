<?php
require_once '../includes/config.php';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    header('Location: ' . ADMIN_URL . '/login.php');
    exit;
}

$page_title = "Job Applications";
include '../includes/header.php';

// Get job ID if provided
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
$job = null;

// If job ID is provided, get job details
if ($job_id) {
    try {
        $stmt = $db->prepare("SELECT * FROM job_listings WHERE id = :id");
        $stmt->bindParam(':id', $job_id);
        $stmt->execute();
        $job = $stmt->fetch();
    } catch (PDOException $e) {
        $error_message = "Error fetching job details: " . $e->getMessage();
    }
}

// Handle application status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $application_id = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;
    $new_status = isset($_POST['new_status']) ? $_POST['new_status'] : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    
    if ($application_id && !empty($new_status)) {
        try {
            $stmt = $db->prepare("UPDATE job_applications SET status = :status, notes = :notes, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $stmt->bindParam(':status', $new_status);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':id', $application_id);
            $stmt->execute();
            
            $success_message = "Application status updated successfully.";
        } catch (PDOException $e) {
            $error_message = "Error updating application status: " . $e->getMessage();
        }
    }
}

// Handle application deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_application'])) {
    $application_id = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;
    
    if ($application_id) {
        try {
            // Get resume path before deleting
            $stmt = $db->prepare("SELECT resume_path FROM job_applications WHERE id = :id");
            $stmt->bindParam(':id', $application_id);
            $stmt->execute();
            $resume_path = $stmt->fetchColumn();
            
            // Delete application
            $stmt = $db->prepare("DELETE FROM job_applications WHERE id = :id");
            $stmt->bindParam(':id', $application_id);
            $stmt->execute();
            
            // Delete resume file if exists
            if ($resume_path && file_exists('../' . $resume_path)) {
                unlink('../' . $resume_path);
            }
            
            $success_message = "Application deleted successfully.";
        } catch (PDOException $e) {
            $error_message = "Error deleting application: " . $e->getMessage();
        }
    }
}

// Get applications
$applications = [];
try {
    if ($job_id) {
        // Get applications for specific job
        $stmt = $db->prepare("
            SELECT a.*, j.title as job_title 
            FROM job_applications a 
            JOIN job_listings j ON a.job_id = j.id 
            WHERE a.job_id = :job_id 
            ORDER BY a.created_at DESC
        ");
        $stmt->bindParam(':job_id', $job_id);
    } else {
        // Get all applications
        $stmt = $db->prepare("
            SELECT a.*, j.title as job_title 
            FROM job_applications a 
            JOIN job_listings j ON a.job_id = j.id 
            ORDER BY a.created_at DESC
        ");
    }
    
    $stmt->execute();
    $applications = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error fetching applications: " . $e->getMessage();
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">
        <?php echo $job ? 'Applications for ' . htmlspecialchars($job['title']) : 'All Job Applications'; ?>
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=job-listings">Job Listings</a></li>
        <li class="breadcrumb-item active">
            <?php echo $job ? 'Applications for ' . htmlspecialchars($job['title']) : 'All Applications'; ?>
        </li>
    </ol>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-file-alt me-1"></i> 
                <?php echo $job ? 'Applications for ' . htmlspecialchars($job['title']) : 'All Job Applications'; ?>
            </div>
            <?php if ($job): ?>
                <a href="index.php?page=job-listings" class="btn btn-primary btn-sm">Back to Job Listings</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (!empty($applications)): ?>
                <div class="table-responsive">
                    <table id="applicationsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <?php if (!$job): ?><th>Job Position</th><?php endif; ?>
                                <th>Contact</th>
                                <th>Date Applied</th>
                                <th>Status</th>
                                <th>Resume</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $application): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></td>
                                    <?php if (!$job): ?>
                                        <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($application['email']); ?>"><?php echo htmlspecialchars($application['email']); ?></a><br>
                                        <?php echo htmlspecialchars($application['phone']); ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($application['created_at'])); ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            switch($application['status']) {
                                                case 'New': echo 'bg-primary'; break;
                                                case 'Under Review': echo 'bg-info'; break;
                                                case 'Shortlisted': echo 'bg-warning text-dark'; break;
                                                case 'Interviewed': echo 'bg-secondary'; break;
                                                case 'Offered': echo 'bg-success'; break;
                                                case 'Hired': echo 'bg-success'; break;
                                                case 'Rejected': echo 'bg-danger'; break;
                                                default: echo 'bg-secondary';
                                            }
                                        ?>">
                                            <?php echo htmlspecialchars($application['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo SITE_URL . '/' . htmlspecialchars($application['resume_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-file-download"></i> Download
                                        </a>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewApplicationModal<?php echo $application['id']; ?>">
                                                View
                                            </button>
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#updateStatusModal<?php echo $application['id']; ?>">
                                                Update Status
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteApplicationModal<?php echo $application['id']; ?>">
                                                Delete
                                            </button>
                                        </div>
                                        
                                        <!-- View Application Modal -->
                                        <div class="modal fade" id="viewApplicationModal<?php echo $application['id']; ?>" tabindex="-1" aria-labelledby="viewApplicationModalLabel<?php echo $application['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="viewApplicationModalLabel<?php echo $application['id']; ?>">
                                                            Application Details
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <h6>Applicant Information</h6>
                                                                <p><strong>Name:</strong> <?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></p>
                                                                <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($application['email']); ?>"><?php echo htmlspecialchars($application['email']); ?></a></p>
                                                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($application['phone']); ?></p>
                                                                <p><strong>Applied On:</strong> <?php echo date('F d, Y', strtotime($application['created_at'])); ?></p>
                                                                <p><strong>Status:</strong> <?php echo htmlspecialchars($application['status']); ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6>Job Information</h6>
                                                                <p><strong>Position:</strong> <?php echo htmlspecialchars($application['job_title']); ?></p>
                                                                <p><strong>Resume:</strong> <a href="<?php echo SITE_URL . '/' . htmlspecialchars($application['resume_path']); ?>" target="_blank">Download Resume</a></p>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if (!empty($application['cover_letter'])): ?>
                                                            <div class="mb-3">
                                                                <h6>Cover Letter</h6>
                                                                <div class="border p-3 bg-light">
                                                                    <?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($application['notes'])): ?>
                                                            <div class="mb-3">
                                                                <h6>Notes</h6>
                                                                <div class="border p-3 bg-light">
                                                                    <?php echo nl2br(htmlspecialchars($application['notes'])); ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal<?php echo $application['id']; ?>" data-bs-dismiss="modal">
                                                            Update Status
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Update Status Modal -->
                                        <div class="modal fade" id="updateStatusModal<?php echo $application['id']; ?>" tabindex="-1" aria-labelledby="updateStatusModalLabel<?php echo $application['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="updateStatusModalLabel<?php echo $application['id']; ?>">
                                                            Update Application Status
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="post">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="new_status<?php echo $application['id']; ?>" class="form-label">Status</label>
                                                                <select class="form-select" id="new_status<?php echo $application['id']; ?>" name="new_status" required>
                                                                    <option value="New" <?php echo $application['status'] === 'New' ? 'selected' : ''; ?>>New</option>
                                                                    <option value="Under Review" <?php echo $application['status'] === 'Under Review' ? 'selected' : ''; ?>>Under Review</option>
                                                                    <option value="Shortlisted" <?php echo $application['status'] === 'Shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                                                                    <option value="Interviewed" <?php echo $application['status'] === 'Interviewed' ? 'selected' : ''; ?>>Interviewed</option>
                                                                    <option value="Offered" <?php echo $application['status'] === 'Offered' ? 'selected' : ''; ?>>Offered</option>
                                                                    <option value="Hired" <?php echo $application['status'] === 'Hired' ? 'selected' : ''; ?>>Hired</option>
                                                                    <option value="Rejected" <?php echo $application['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="notes<?php echo $application['id']; ?>" class="form-label">Notes</label>
                                                                <textarea class="form-control" id="notes<?php echo $application['id']; ?>" name="notes" rows="4"><?php echo htmlspecialchars($application['notes']); ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Delete Application Modal -->
                                        <div class="modal fade" id="deleteApplicationModal<?php echo $application['id']; ?>" tabindex="-1" aria-labelledby="deleteApplicationModalLabel<?php echo $application['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteApplicationModalLabel<?php echo $application['id']; ?>">
                                                            Confirm Deletion
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete the application from <strong><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></strong>?</p>
                                                        <p class="text-danger">This action cannot be undone and will permanently delete the application and resume file.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form method="post">
                                                            <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                                            <button type="submit" name="delete_application" class="btn btn-danger">Delete Application</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <?php echo $job ? 'No applications found for this job position.' : 'No job applications found.'; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#applicationsTable').DataTable({
            order: [[3, 'desc']], // Sort by date applied (newest first)
            responsive: true
        });
    });
</script>

<?php include '../includes/footer.php'; ?>