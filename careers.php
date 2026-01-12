<?php
require_once 'includes/config.php';
$page_title = "Careers - FLIONE";
$meta_description = "Join the FLIONE team and help transform educational technology. Explore our current job openings and apply today.";
$meta_keywords = "FLIONE careers, jobs, employment, educational technology jobs, edtech careers";
include 'includes/header.php';

// Get job listings
$jobs = [];
$featured_jobs = [];
try {
    // Get all active jobs
    $stmt = $db->prepare("SELECT * FROM job_listings WHERE is_active = 1 ORDER BY created_at DESC");
    $stmt->execute();
    $jobs = $stmt->fetchAll();
    
    // Get featured jobs
    $stmt = $db->prepare("SELECT * FROM job_listings WHERE is_active = 1 AND is_featured = 1 ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $featured_jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching job listings: " . $e->getMessage());
}

// Group jobs by department
$departments = [];
foreach ($jobs as $job) {
    if (!isset($departments[$job['department']])) {
        $departments[$job['department']] = [];
    }
    $departments[$job['department']][] = $job;
}
?>

<!-- Page Header -->
<div class="page-header bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="page-title">Careers at FLIONE</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Careers</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Careers Intro Section -->
<section class="careers-intro py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="section-title mb-4">
                    <h2 class="title">Join Our Team</h2>
                    <div class="title-border"></div>
                </div>
                <p class="lead">At FLIONE, we're passionate about transforming education through innovative technology.</p>
                <p>We're looking for talented individuals who share our vision and want to make a real difference in how students learn and how educators teach. Join us in creating the future of educational technology.</p>
                <p>As part of the FLIONE team, you'll work in a collaborative, creative environment where your ideas are valued and your growth is supported.</p>
                <div class="mt-4">
                    <a href="#current-openings" class="btn btn-primary me-3">View Open Positions</a>
                    <a href="#why-flione" class="btn btn-outline-primary">Why Work With Us</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="careers-image position-relative">
                    <img src="<?php echo SITE_URL; ?>/assets/images/careers/team-working.jpg" alt="FLIONE Team" class="img-fluid rounded shadow-lg">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Jobs Section -->
<?php if (!empty($featured_jobs)): ?>
<section class="featured-jobs py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="section-title mb-5">
                    <h2 class="title">Featured Opportunities</h2>
                    <div class="title-border mx-auto"></div>
                    <p class="mt-3">Explore our highlighted positions that need to be filled urgently</p>
                </div>
            </div>
        </div>
        <div class="row">
            <?php foreach ($featured_jobs as $job): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="job-card h-100 bg-white rounded shadow-sm p-4">
                        <div class="featured-badge">Featured</div>
                        <h3 class="job-title h5 mb-3"><?php echo $job['title']; ?></h3>
                        <div class="job-meta mb-3">
                            <span class="job-department me-3"><i class="fas fa-briefcase me-1"></i> <?php echo $job['department']; ?></span>
                            <span class="job-location"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $job['location']; ?></span>
                        </div>
                        <div class="job-type mb-3">
                            <span class="badge bg-primary"><?php echo $job['job_type']; ?></span>
                        </div>
                        <div class="job-excerpt mb-3">
                            <?php echo substr(strip_tags($job['description']), 0, 120) . '...'; ?>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/job-details.php?slug=<?php echo $job['slug']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Why Work With Us Section -->
<section id="why-flione" class="why-work-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="section-title mb-5">
                    <h2 class="title">Why Work With Us</h2>
                    <div class="title-border mx-auto"></div>
                    <p class="mt-3">Join a team that values innovation, collaboration, and making a difference</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="benefit-card text-center h-100 p-4 bg-white rounded shadow-sm">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-lightbulb fa-3x text-primary"></i>
                    </div>
                    <h4>Innovative Environment</h4>
                    <p>Work on cutting-edge educational technology that's transforming how students learn and teachers teach.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="benefit-card text-center h-100 p-4 bg-white rounded shadow-sm">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-users fa-3x text-primary"></i>
                    </div>
                    <h4>Collaborative Culture</h4>
                    <p>Join a diverse team of professionals who value collaboration, open communication, and shared success.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="benefit-card text-center h-100 p-4 bg-white rounded shadow-sm">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-chart-line fa-3x text-primary"></i>
                    </div>
                    <h4>Growth Opportunities</h4>
                    <p>Develop your skills and advance your career with our commitment to professional development and growth.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="benefit-card text-center h-100 p-4 bg-white rounded shadow-sm">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-balance-scale fa-3x text-primary"></i>
                    </div>
                    <h4>Work-Life Balance</h4>
                    <p>Enjoy flexible working arrangements and policies designed to help you maintain a healthy work-life balance.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="benefit-card text-center h-100 p-4 bg-white rounded shadow-sm">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-hand-holding-heart fa-3x text-primary"></i>
                    </div>
                    <h4>Competitive Benefits</h4>
                    <p>Access comprehensive benefits including health insurance, retirement plans, and paid time off.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="benefit-card text-center h-100 p-4 bg-white rounded shadow-sm">
                    <div class="benefit-icon mb-3">
                        <i class="fas fa-globe fa-3x text-primary"></i>
                    </div>
                    <h4>Make an Impact</h4>
                    <p>Contribute to meaningful work that positively impacts education and helps shape the future of learning.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Current Openings Section -->
<section id="current-openings" class="current-openings py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="section-title mb-5">
                    <h2 class="title">Current Openings</h2>
                    <div class="title-border mx-auto"></div>
                    <p class="mt-3">Explore our available positions and find your perfect role</p>
                </div>
            </div>
        </div>
        
        <?php if (!empty($departments)): ?>
            <div class="job-filters mb-4">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <select id="department-filter" class="form-select">
                            <option value="all">All Departments</option>
                            <?php foreach (array_keys($departments) as $dept): ?>
                                <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select id="job-type-filter" class="form-select">
                            <option value="all">All Job Types</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                            <option value="Remote">Remote</option>
                            <option value="Internship">Internship</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="job-listings">
                <?php foreach ($departments as $department => $dept_jobs): ?>
                    <div class="department-section mb-4" data-department="<?php echo $department; ?>">
                        <h3 class="department-title mb-3"><?php echo $department; ?></h3>
                        <div class="row">
                            <?php foreach ($dept_jobs as $job): ?>
                                <div class="col-12 mb-3 job-item" data-job-type="<?php echo $job['job_type']; ?>">
                                    <div class="job-list-card bg-white rounded shadow-sm p-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h4 class="job-title mb-2"><?php echo $job['title']; ?></h4>
                                                <div class="job-meta">
                                                    <span class="job-location me-3"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $job['location']; ?></span>
                                                    <span class="job-type"><i class="fas fa-briefcase me-1"></i> <?php echo $job['job_type']; ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="job-salary mb-2 mb-md-0">
                                                    <?php if (!empty($job['salary_range'])): ?>
                                                        <i class="fas fa-money-bill-wave me-1"></i> <?php echo $job['salary_range']; ?>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($job['application_deadline'])): ?>
                                                    <div class="job-deadline">
                                                        <i class="fas fa-calendar-alt me-1"></i> Deadline: <?php echo date('M d, Y', strtotime($job['application_deadline'])); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-2 text-md-end mt-3 mt-md-0">
                                                <a href="<?php echo SITE_URL; ?>/job-details.php?slug=<?php echo $job['slug']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <p>We don't have any open positions at the moment. Please check back later or send your resume to <a href="mailto:careers@flioneit.com">careers@flioneit.com</a> for future opportunities.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Testimonials Section -->
<section class="employee-testimonials py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="section-title mb-5">
                    <h2 class="title">What Our Team Says</h2>
                    <div class="title-border mx-auto"></div>
                    <p class="mt-3">Hear from the people who make FLIONE great</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="testimonial-card h-100 bg-white p-4 rounded shadow-sm">
                    <div class="testimonial-text mb-4">
                        <p class="fst-italic">"Working at FLIONE has been the most rewarding experience of my career. I love being part of a team that's making such a positive impact on education, and the collaborative culture makes every day exciting."</p>
                    </div>
                    <div class="testimonial-author d-flex align-items-center">
                        <div class="testimonial-author-image">
                            <img src="<?php echo SITE_URL; ?>/assets/images/careers/employee-1.jpg" alt="Sarah Johnson" class="rounded-circle">
                        </div>
                        <div class="testimonial-author-info ms-3">
                            <h5 class="mb-0">Sarah Johnson</h5>
                            <p class="mb-0 text-muted">Educational Technology Specialist</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="testimonial-card h-100 bg-white p-4 rounded shadow-sm">
                    <div class="testimonial-text mb-4">
                        <p class="fst-italic">"The growth opportunities at FLIONE are incredible. In just two years, I've expanded my skills, taken on new responsibilities, and been supported every step of the way. It's a place where your potential is truly valued."</p>
                    </div>
                    <div class="testimonial-author d-flex align-items-center">
                        <div class="testimonial-author-image">
                            <img src="<?php echo SITE_URL; ?>/assets/images/careers/employee-2.jpg" alt="David Chen" class="rounded-circle">
                        </div>
                        <div class="testimonial-author-info ms-3">
                            <h5 class="mb-0">David Chen</h5>
                            <p class="mb-0 text-muted">Software Developer</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="testimonial-card h-100 bg-white p-4 rounded shadow-sm">
                    <div class="testimonial-text mb-4">
                        <p class="fst-italic">"What I appreciate most about FLIONE is the work-life balance. The flexible working arrangements allow me to be productive while still having time for my family and personal interests. It's a company that truly cares about its employees."</p>
                    </div>
                    <div class="testimonial-author d-flex align-items-center">
                        <div class="testimonial-author-image">
                            <img src="<?php echo SITE_URL; ?>/assets/images/careers/employee-3.jpg" alt="Michelle Patel" class="rounded-circle">
                        </div>
                        <div class="testimonial-author-info ms-3">
                            <h5 class="mb-0">Michelle Patel</h5>
                            <p class="mb-0 text-muted">Curriculum Development Specialist</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Application Process Section -->
<section class="application-process py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="section-title mb-5">
                    <h2 class="title">Our Application Process</h2>
                    <div class="title-border mx-auto"></div>
                    <p class="mt-3">What to expect when you apply for a position at FLIONE</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="process-step text-center">
                    <div class="process-icon mb-3">
                        <span class="step-number">1</span>
                        <i class="fas fa-file-alt fa-2x text-primary"></i>
                    </div>
                    <h4>Application</h4>
                    <p>Submit your resume and cover letter through our online application system.</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="process-step text-center">
                    <div class="process-icon mb-3">
                        <span class="step-number">2</span>
                        <i class="fas fa-phone-alt fa-2x text-primary"></i>
                    </div>
                    <h4>Initial Screening</h4>
                    <p>If your application matches our requirements, we'll contact you for an initial phone interview.</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="process-step text-center">
                    <div class="process-icon mb-3">
                        <span class="step-number">3</span>
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                    <h4>Interviews</h4>
                    <p>Selected candidates will be invited for in-person or virtual interviews with the team.</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="process-step text-center">
                    <div class="process-icon mb-3">
                        <span class="step-number">4</span>
                        <i class="fas fa-handshake fa-2x text-primary"></i>
                    </div>
                    <h4>Offer</h4>
                    <p>Successful candidates will receive a job offer and welcome to the FLIONE team!</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="careers-contact py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="section-title mb-4">
                    <h2 class="title">Have Questions?</h2>
                    <div class="title-border mx-auto"></div>
                </div>
                <p class="mb-4">If you have any questions about our open positions or the application process, please don't hesitate to contact us.</p>
                <div class="contact-info">
                    <p><i class="fas fa-envelope me-2"></i> <a href="mailto:careers@flioneit.com">careers@flioneit.com</a></p>
                    <p><i class="fas fa-phone me-2"></i> <?php echo get_setting('contact_phone'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- JavaScript for filtering jobs -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Department filter
    document.getElementById('department-filter').addEventListener('change', filterJobs);
    
    // Job type filter
    document.getElementById('job-type-filter').addEventListener('change', filterJobs);
    
    function filterJobs() {
        const departmentFilter = document.getElementById('department-filter').value;
        const jobTypeFilter = document.getElementById('job-type-filter').value;
        
        // Department sections
        const departmentSections = document.querySelectorAll('.department-section');
        departmentSections.forEach(section => {
            const department = section.getAttribute('data-department');
            if (departmentFilter === 'all' || departmentFilter === department) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
        
        // Job items
        const jobItems = document.querySelectorAll('.job-item');
        jobItems.forEach(item => {
            const jobType = item.getAttribute('data-job-type');
            const parentDepartment = item.closest('.department-section');
            
            if (parentDepartment.style.display === 'none') {
                item.style.display = 'none';
                return;
            }
            
            if (jobTypeFilter === 'all' || jobTypeFilter === jobType) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
        
        // Check if any jobs are visible in each department
        departmentSections.forEach(section => {
            if (section.style.display !== 'none') {
                const visibleJobs = section.querySelectorAll('.job-item[style="display: block;"]');
                if (visibleJobs.length === 0) {
                    section.style.display = 'none';
                }
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>