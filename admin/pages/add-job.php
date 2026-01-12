<?php
require_once '../includes/config.php';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    header('Location: ' . ADMIN_URL . '/login.php');
    exit;
}

$page_title = "Add New Job";
include '../includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_job'])) {
    // Get form data
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $job_type = isset($_POST['job_type']) ? trim($_POST['job_type']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $requirements = isset($_POST['requirements']) ? trim($_POST['requirements']) : '';
    $responsibilities = isset($_POST['responsibilities']) ? trim($_POST['responsibilities']) : '';
    $salary_range = isset($_POST['salary_range']) ? trim($_POST['salary_range']) : '';
    $application_deadline = isset($_POST['application_deadline']) ? trim($_POST['application_deadline']) : '';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate required fields
    $errors = [];
    if (empty($title)) $errors[] = "Job title is required.";
    if (empty($department)) $errors[] = "Department is required.";
    if (empty($location)) $errors[] = "Location is required.";
    if (empty($job_type)) $errors[] = "Job type is required.";
    if (empty($description)) $errors[] = "Job description is required.";
    if (empty($requirements)) $errors[] = "Requirements are required.";
    if (empty($responsibilities)) $errors[] = "Responsibilities are required.";
    
    // If no errors, proceed with saving
    if (empty($errors)) {
        try {
            // Generate slug from title
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $title)));
            
            // Check if slug already exists
            $stmt = $db->prepare("SELECT COUNT(*) FROM job_listings WHERE slug = :slug");
            $stmt->bindParam(':slug', $slug);
            $stmt->execute();
            $slug_exists = (int)$stmt->fetchColumn() > 0;
            
            // If slug exists, append a unique identifier
            if ($slug_exists) {
                $slug .= '-' . time();
            }
            
            // Insert job listing
            $stmt = $db->prepare("
                INSERT INTO job_listings (
                    title, slug, department, location, job_type, description, 
                    requirements, responsibilities, salary_range, application_deadline, 
                    is_featured, is_active
                ) VALUES (
                    :title, :slug, :department, :location, :job_type, :description, 
                    :requirements, :responsibilities, :salary_range, :application_deadline, 
                    :is_featured, :is_active
                )
            ");
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':department', $department);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':job_type', $job_type);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':requirements', $requirements);
            $stmt->bindParam(':responsibilities', $responsibilities);
            $stmt->bindParam(':salary_range', $salary_range);
            $stmt->bindParam(':application_deadline', $application_deadline);
            $stmt->bindParam(':is_featured', $is_featured);
            $stmt->bindParam(':is_active', $is_active);
            
            $stmt->execute();
            
            $success_message = "Job listing created successfully.";
            
            // Clear form data after successful submission
            $title = $department = $location = $job_type = $description = $requirements = $responsibilities = $salary_range = $application_deadline = '';
            $is_featured = $is_active = 0;
        } catch (PDOException $e) {
            $error_message = "Error creating job listing: " . $e->getMessage();
        }
    } else {
        $error_message = "Please correct the following errors:<br>" . implode("<br>", $errors);
    }
}

// Get existing departments for dropdown
$departments = [];
try {
    $stmt = $db->prepare("SELECT DISTINCT department FROM job_listings ORDER BY department");
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Silently fail, will just show empty dropdown
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Add New Job</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="job-listings.php">Job Listings</a></li>
        <li class="breadcrumb-item active">Add New Job</li>
    </ol>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus-circle me-1"></i> Add New Job Listing
        </div>
        <div class="card-body">
            <form method="post" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="department" name="department" list="departments" value="<?php echo isset($department) ? htmlspecialchars($department) : ''; ?>" required>
                        <datalist id="departments">
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="location" name="location" value="<?php echo isset($location) ? htmlspecialchars($location) : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="job_type" class="form-label">Job Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="job_type" name="job_type" required>
                            <option value="" disabled <?php echo !isset($job_type) ? 'selected' : ''; ?>>Select Job Type</option>
                            <option value="Full-time" <?php echo (isset($job_type) && $job_type === 'Full-time') ? 'selected' : ''; ?>>Full-time</option>
                            <option value="Part-time" <?php echo (isset($job_type) && $job_type === 'Part-time') ? 'selected' : ''; ?>>Part-time</option>
                            <option value="Contract" <?php echo (isset($job_type) && $job_type === 'Contract') ? 'selected' : ''; ?>>Contract</option>
                            <option value="Remote" <?php echo (isset($job_type) && $job_type === 'Remote') ? 'selected' : ''; ?>>Remote</option>
                            <option value="Internship" <?php echo (isset($job_type) && $job_type === 'Internship') ? 'selected' : ''; ?>>Internship</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="salary_range" class="form-label">Salary Range</label>
                        <input type="text" class="form-control" id="salary_range" name="salary_range" value="<?php echo isset($salary_range) ? htmlspecialchars($salary_range) : ''; ?>" placeholder="e.g., £30,000 - £40,000">
                    </div>
                    <div class="col-md-6">
                        <label for="application_deadline" class="form-label">Application Deadline</label>
                        <input type="date" class="form-control" id="application_deadline" name="application_deadline" value="<?php echo isset($application_deadline) ? htmlspecialchars($application_deadline) : ''; ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Job Description <span class="text-danger">*</span></label>
                    <textarea class="form-control editor" id="description" name="description" rows="5" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="requirements" class="form-label">Requirements <span class="text-danger">*</span></label>
                    <textarea class="form-control editor" id="requirements" name="requirements" rows="5" required><?php echo isset($requirements) ? htmlspecialchars($requirements) : ''; ?></textarea>
                    <div class="form-text">Use HTML list format for better readability (e.g., &lt;ul&gt;&lt;li&gt;Requirement 1&lt;/li&gt;&lt;/ul&gt;)</div>
                </div>
                
                <div class="mb-3">
                    <label for="responsibilities" class="form-label">Responsibilities <span class="text-danger">*</span></label>
                    <textarea class="form-control editor" id="responsibilities" name="responsibilities" rows="5" required><?php echo isset($responsibilities) ? htmlspecialchars($responsibilities) : ''; ?></textarea>
                    <div class="form-text">Use HTML list format for better readability (e.g., &lt;ul&gt;&lt;li&gt;Responsibility 1&lt;/li&gt;&lt;/ul&gt;)</div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" <?php echo (isset($is_featured) && $is_featured) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_featured">Featured Job</label>
                        </div>
                        <div class="form-text">Featured jobs appear in the highlighted section on the careers page.</div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo (!isset($is_active) || $is_active) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Active Job</label>
                        </div>
                        <div class="form-text">Inactive jobs will not be displayed on the website.</div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="add_job" class="btn btn-primary">Add Job Listing</button>
                    <a href="job-listings.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize rich text editors
        $('.editor').each(function() {
            ClassicEditor
                .create(this)
                .catch(error => {
                    console.error(error);
                });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>