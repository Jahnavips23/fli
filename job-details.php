<?php
require_once 'includes/config.php';
$page_title = "Job Details - FLIONE";
$meta_description = "View details and apply for job opportunities at FLIONE.";
$meta_keywords = "FLIONE careers, job application, employment, job details";

// Get job details
$job = null;
$related_jobs = [];

if (isset($_GET['slug']) && !empty($_GET['slug'])) {
    $slug = $_GET['slug'];
    
    try {
        // Get job details
        $stmt = $db->prepare("SELECT * FROM job_listings WHERE slug = :slug AND is_active = 1");
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        $job = $stmt->fetch();
        
        if ($job) {
            // Update page title and meta
            $page_title = $job['title'] . " - FLIONE Careers";
            $meta_description = "Apply for the " . $job['title'] . " position at FLIONE. " . substr(strip_tags($job['description']), 0, 150) . "...";
            
            // Get related jobs from the same department
            $stmt = $db->prepare("SELECT * FROM job_listings WHERE department = :department AND id != :id AND is_active = 1 ORDER BY created_at DESC LIMIT 3");
            $stmt->bindParam(':department', $job['department']);
            $stmt->bindParam(':id', $job['id']);
            $stmt->execute();
            $related_jobs = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log("Error fetching job details: " . $e->getMessage());
    }
}

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="page-title"><?php echo $job ? $job['title'] : 'Job Details'; ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/careers.php">Careers</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $job ? $job['title'] : 'Job Details'; ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<?php if ($job): ?>
<!-- Job Details Section -->
<section class="job-details-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="job-details-card bg-white p-4 rounded shadow-sm mb-4">
                    <div class="job-header mb-4">
                        <div class="job-meta mb-3">
                            <span class="job-department me-3"><i class="fas fa-briefcase me-1"></i> <?php echo $job['department']; ?></span>
                            <span class="job-location me-3"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $job['location']; ?></span>
                            <span class="job-type"><i class="fas fa-clock me-1"></i> <?php echo $job['job_type']; ?></span>
                        </div>
                        <?php if (!empty($job['salary_range'])): ?>
                            <div class="job-salary mb-3">
                                <strong>Salary Range:</strong> <?php echo $job['salary_range']; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($job['application_deadline'])): ?>
                            <div class="job-deadline mb-3">
                                <strong>Application Deadline:</strong> <?php echo date('F d, Y', strtotime($job['application_deadline'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="job-description mb-4">
                        <h3>Job Description</h3>
                        <div class="description-content">
                            <?php echo $job['description']; ?>
                        </div>
                    </div>
                    
                    <div class="job-responsibilities mb-4">
                        <h3>Responsibilities</h3>
                        <div class="responsibilities-content">
                            <?php echo $job['responsibilities']; ?>
                        </div>
                    </div>
                    
                    <div class="job-requirements mb-4">
                        <h3>Requirements</h3>
                        <div class="requirements-content">
                            <?php echo $job['requirements']; ?>
                        </div>
                    </div>
                    
                    <div class="job-apply text-center mt-5">
                        <a href="#apply-form" class="btn btn-primary btn-lg">Apply Now</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="job-sidebar">
                    <div class="sidebar-widget bg-white p-4 rounded shadow-sm mb-4">
                        <h4 class="widget-title">Quick Apply</h4>
                        <p>Ready to join our team? Use the button below to submit your application.</p>
                        <a href="#apply-form" class="btn btn-primary w-100">Apply for this Position</a>
                    </div>
                    
                    <div class="sidebar-widget bg-white p-4 rounded shadow-sm mb-4">
                        <h4 class="widget-title">Share This Job</h4>
                        <div class="social-share mt-3">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/job-details.php?slug=' . $job['slug']); ?>" target="_blank" class="btn btn-outline-primary me-2"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode('Check out this job opportunity at FLIONE: ' . $job['title']); ?>&url=<?php echo urlencode(SITE_URL . '/job-details.php?slug=' . $job['slug']); ?>" target="_blank" class="btn btn-outline-primary me-2"><i class="fab fa-twitter"></i></a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(SITE_URL . '/job-details.php?slug=' . $job['slug']); ?>" target="_blank" class="btn btn-outline-primary me-2"><i class="fab fa-linkedin-in"></i></a>
                            <a href="mailto:?subject=<?php echo urlencode('Job Opportunity at FLIONE: ' . $job['title']); ?>&body=<?php echo urlencode('I thought you might be interested in this job opportunity at FLIONE: ' . $job['title'] . '. Learn more at: ' . SITE_URL . '/job-details.php?slug=' . $job['slug']); ?>" class="btn btn-outline-primary"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                    
                    <?php if (!empty($related_jobs)): ?>
                    <div class="sidebar-widget bg-white p-4 rounded shadow-sm">
                        <h4 class="widget-title">Similar Jobs</h4>
                        <div class="related-jobs mt-3">
                            <?php foreach ($related_jobs as $related_job): ?>
                                <div class="related-job-item mb-3 pb-3 border-bottom">
                                    <h5 class="mb-2"><a href="<?php echo SITE_URL; ?>/job-details.php?slug=<?php echo $related_job['slug']; ?>"><?php echo $related_job['title']; ?></a></h5>
                                    <div class="job-meta small">
                                        <span class="job-location me-3"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $related_job['location']; ?></span>
                                        <span class="job-type"><i class="fas fa-clock me-1"></i> <?php echo $related_job['job_type']; ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Application Form Section -->
<section id="apply-form" class="application-form-section py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="application-form-card bg-white p-4 rounded shadow-sm">
                    <h2 class="text-center mb-4">Apply for <?php echo $job['title']; ?></h2>
                    
                    <form id="job-application-form" action="process/submit-application.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                        <input type="hidden" name="job_title" value="<?php echo $job['title']; ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="resume" class="form-label">Resume/CV (PDF, DOC, DOCX) *</label>
                            <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                            <div class="form-text">Maximum file size: 5MB</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cover_letter" class="form-label">Cover Letter</label>
                            <textarea class="form-control" id="cover_letter" name="cover_letter" rows="5" placeholder="Tell us why you're interested in this position and what makes you a great fit."></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="privacy_policy" name="privacy_policy" required>
                                <label class="form-check-label" for="privacy_policy">
                                    I agree to the <a href="#" target="_blank">Privacy Policy</a> and consent to the processing of my personal data for recruitment purposes.
                                </label>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">Submit Application</button>
                        </div>
                    </form>
                    
                    <div id="application-response" class="mt-4" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- JavaScript for form submission -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const applicationForm = document.getElementById('job-application-form');
    const responseDiv = document.getElementById('application-response');
    
    applicationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Validate file size
        const resumeFile = document.getElementById('resume').files[0];
        if (resumeFile && resumeFile.size > 5 * 1024 * 1024) { // 5MB
            alert('Resume file size exceeds the 5MB limit. Please upload a smaller file.');
            return;
        }
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            responseDiv.style.display = 'block';
            
            if (data.success) {
                responseDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                applicationForm.reset();
            } else {
                responseDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
            
            // Scroll to response
            responseDiv.scrollIntoView({ behavior: 'smooth' });
        })
        .catch(error => {
            responseDiv.style.display = 'block';
            responseDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again later.</div>';
            console.error('Error:', error);
        });
    });
});
</script>

<?php else: ?>
<!-- Job Not Found Section -->
<section class="job-not-found py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="alert alert-warning">
                    <h3>Job Not Found</h3>
                    <p>The job you're looking for is no longer available or doesn't exist.</p>
                    <a href="<?php echo SITE_URL; ?>/careers.php" class="btn btn-primary mt-3">View All Jobs</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>