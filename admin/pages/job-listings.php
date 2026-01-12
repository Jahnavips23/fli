<?php
require_once '../includes/config.php';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    header('Location: ' . ADMIN_URL . '/login.php');
    exit;
}

$page_title = "Job Listings";
include '../includes/header.php';

// Get job listings
$jobs = [];
try {
    $stmt = $db->prepare("SELECT * FROM job_listings ORDER BY created_at DESC");
    $stmt->execute();
    $jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error fetching job listings: " . $e->getMessage();
}

// Handle job status toggle
if (isset($_POST['toggle_status']) && isset($_POST['job_id'])) {
    $job_id = (int)$_POST['job_id'];
    $new_status = $_POST['new_status'] === '1' ? 1 : 0;
    
    try {
        $stmt = $db->prepare("UPDATE job_listings SET is_active = :status WHERE id = :id");
        $stmt->bindParam(':status', $new_status);
        $stmt->bindParam(':id', $job_id);
        $stmt->execute();
        
        $success_message = "Job status updated successfully.";
        
        // Refresh job listings
        $stmt = $db->prepare("SELECT * FROM job_listings ORDER BY created_at DESC");
        $stmt->execute();
        $jobs = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = "Error updating job status: " . $e->getMessage();
    }
}

// Handle job deletion
if (isset($_POST['delete_job']) && isset($_POST['job_id'])) {
    $job_id = (int)$_POST['job_id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM job_listings WHERE id = :id");
        $stmt->bindParam(':id', $job_id);
        $stmt->execute();
        
        $success_message = "Job listing deleted successfully.";
        
        // Refresh job listings
        $stmt = $db->prepare("SELECT * FROM job_listings ORDER BY created_at DESC");
        $stmt->execute();
        $jobs = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_message = "Error deleting job listing: " . $e->getMessage();
    }
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Job Listings</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Job Listings</li>
    </ol>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div><i class="fas fa-briefcase me-1"></i> Manage Job Listings</div>
            <a href="add-job.php" class="btn btn-primary btn-sm">Add New Job</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="jobsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Applications</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($jobs)): ?>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td><?php echo $job['title']; ?></td>
                                    <td><?php echo $job['department']; ?></td>
                                    <td><?php echo $job['location']; ?></td>
                                    <td><?php echo $job['job_type']; ?></td>
                                    <td>
                                        <?php echo !empty($job['application_deadline']) ? date('M d, Y', strtotime($job['application_deadline'])) : 'No deadline'; ?>
                                    </td>
                                    <td>
                                        <form method="post" class="status-toggle-form">
                                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                            <input type="hidden" name="new_status" value="<?php echo $job['is_active'] ? '0' : '1'; ?>">
                                            <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $job['is_active'] ? 'btn-success' : 'btn-secondary'; ?>">
                                                <?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($job['is_featured']): ?>
                                            <span class="badge bg-warning text-dark">Featured</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">Standard</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        // Get application count
                                        $stmt = $db->prepare("SELECT COUNT(*) FROM job_applications WHERE job_id = :job_id");
                                        $stmt->bindParam(':job_id', $job['id']);
                                        $stmt->execute();
                                        $application_count = $stmt->fetchColumn();
                                        ?>
                                        <a href="job-applications.php?job_id=<?php echo $job['id']; ?>" class="btn btn-info btn-sm">
                                            <?php echo $application_count; ?> Applications
                                        </a>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="edit-job.php?id=<?php echo $job['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                            <a href="<?php echo SITE_URL; ?>/job-details.php?slug=<?php echo $job['slug']; ?>" target="_blank" class="btn btn-info btn-sm">View</a>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteJobModal<?php echo $job['id']; ?>">Delete</button>
                                        </div>
                                        
                                        <!-- Delete Confirmation Modal -->
                                        <div class="modal fade" id="deleteJobModal<?php echo $job['id']; ?>" tabindex="-1" aria-labelledby="deleteJobModalLabel<?php echo $job['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteJobModalLabel<?php echo $job['id']; ?>">Confirm Deletion</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Are you sure you want to delete the job listing: <strong><?php echo $job['title']; ?></strong>?</p>
                                                        <p class="text-danger">This action cannot be undone and will also delete all associated job applications.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <form method="post">
                                                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                                            <button type="submit" name="delete_job" class="btn btn-danger">Delete Job</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No job listings found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#jobsTable').DataTable({
            order: [[4, 'asc']], // Sort by deadline
            responsive: true
        });
        
        // Confirm status toggle
        $('.status-toggle-form').on('submit', function(e) {
            if (!confirm('Are you sure you want to change the status of this job?')) {
                e.preventDefault();
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>