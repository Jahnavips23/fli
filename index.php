<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get carousel slides
$carousel_slides = get_carousel_slides();

// Get recent blog posts
$recent_posts = get_recent_blog_posts(3);
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Carousel -->
<section class="hero-carousel">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($carousel_slides as $index => $slide): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?php echo $index; ?>" <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?>
                    aria-label="Slide <?php echo $index + 1; ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($carousel_slides as $index => $slide): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>"
                    style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('<?php echo SITE_URL . '/' . $slide['image_path']; ?>') center center / cover no-repeat;">
                    <div class="carousel-caption text-center">
                        <h2 class="animate__animated animate__fadeInDown"><?php echo $slide['title']; ?></h2>
                        <p class="animate__animated animate__fadeInUp"><?php echo $slide['description']; ?></p>
                        <?php if (!empty($slide['button_text']) && !empty($slide['button_link'])): ?>
                            <a href="<?php echo $slide['button_link']; ?>"
                                class="btn btn-primary animate__animated animate__fadeInUp"><?php echo $slide['button_text']; ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>

<!-- Features Section -->
<section class="features-section section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="section-title text-center">
                    <h2><i class="fas fa-star text-warning me-2"></i>Why Choose Us</h2>
                    <p>We provide innovative IT solutions tailored to meet the unique needs of businesses, schools, and
                        educational institutions.</p>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <!-- Innovative Solutions -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <div class="interactive-card">
                    <img src="<?php echo SITE_URL; ?>/assets/images/features/innovative-solutions.png"
                        alt="Innovative Solutions" class="card-bg-img">
                    <div class="card-overlay"></div>
                    <div class="card-content">
                        <div class="card-icon mb-2">
                            <i class="fas fa-desktop"></i>
                        </div>
                        <h4>Innovative Solutions</h4>
                        <p>We leverage the latest technologies to deliver cutting-edge IT solutions that drive growth
                            and efficiency.</p>
                    </div>
                </div>
            </div>

            <!-- Educational Expertise -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="interactive-card">
                    <img src="<?php echo SITE_URL; ?>/assets/images/features/educational-exp.jpg"
                        alt="Educational Expertise" class="card-bg-img">
                    <div class="card-overlay"></div>
                    <div class="card-content">
                        <div class="card-icon mb-2">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h4>Educational Expertise</h4>
                        <p>Our specialized knowledge in educational technology helps schools enhance learning
                            experiences.</p>
                    </div>
                </div>
            </div>

            <!-- Kid-Friendly Technology -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="interactive-card">
                    <img src="<?php echo SITE_URL; ?>/assets/images/features/kids-friendly-tech.jpg"
                        alt="Kid-Friendly Technology" class="card-bg-img">
                    <div class="card-overlay"></div>
                    <div class="card-content">
                        <div class="card-icon mb-2">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h4>Kid-Friendly Technology</h4>
                        <p>We create safe, engaging, and educational technology resources specifically designed for
                            children.</p>
                    </div>
                </div>
            </div>

            <!-- 6 Days a week Support -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="interactive-card">
                    <img src="<?php echo SITE_URL; ?>/assets/images/carousel/1752243240_3d-abstract-dots-lines-connected.jpg"
                        alt="Support" class="card-bg-img">
                    <div class="card-overlay"></div>
                    <div class="card-content">
                        <div class="card-icon mb-2">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>6 Days a week Support</h4>
                        <p>Our dedicated support team is available around the clock to assist you with any technical
                            issues.</p>
                    </div>
                </div>
            </div>

            <!-- Enhanced Security -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="interactive-card">
                    <img src="<?php echo SITE_URL; ?>/assets/images/features/Enhanced-Security.png"
                        alt="Enhanced Security" class="card-bg-img">
                    <div class="card-overlay"></div>
                    <div class="card-content">
                        <div class="card-icon mb-2">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Enhanced Security</h4>
                        <p>We implement robust security measures to protect your data and systems from potential
                            threats.</p>
                    </div>
                </div>
            </div>

            <!-- Performance Optimization -->
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="interactive-card">
                    <img src="<?php echo SITE_URL; ?>/assets/images/features/performance-optimization.png"
                        alt="Performance Optimization" class="card-bg-img">
                    <div class="card-overlay"></div>
                    <div class="card-content">
                        <div class="card-icon mb-2">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Performance Optimization</h4>
                        <p>Our solutions are designed to optimize performance and maximize efficiency for your business.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about-section section-padding bg-light-blue position-relative overflow-hidden">
    <!-- Floating Background Shapes -->
    <div class="anim-shape shape-1"></div>
    <div class="anim-shape shape-2"></div>
    <div class="anim-shape shape-3"></div>

    <div class="container position-relative z-index-1">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                <div class="about-img-wrapper item-tilt">
                    <div class="img-backdrop"></div>
                    <img src="<?php echo SITE_URL; ?>/assets/images/about-img.png" alt="About Flione IT"
                        class="img-fluid rounded shadow-lg position-relative tilt-element">
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="about-content ps-lg-4">
                    <h2 class="mb-4 fw-bold"><i class="fas fa-info-circle text-primary me-2"></i>About Flione IT</h2>
                    <p class="lead text-primary mb-4 position-relative d-inline-block shimmer-text">Innovating Education
                        & Technology</p>
                    <p>Flione IT is a leading provider of innovative technology solutions for educational institutions,
                        and individuals. With years of experience and a team of skilled professionals, we deliver
                        cutting-edge IT services tailored to meet the unique needs of our clients.</p>
                    <p>Our mission is to empower organizations and individuals through technology, helping them achieve
                        their goals and stay ahead in today's digital world.</p>
                    <ul class="list-unstyled mt-4 about-feature-list">
                        <li class="mb-2" style="--d: 0.1s">Comprehensive IT solutions for businesses</li>
                        <li class="mb-2" style="--d: 0.2s">Specialized educational technology</li>
                        <li class="mb-2" style="--d: 0.3s">Kid-friendly resources that make learning fun</li>
                        <li class="mb-2" style="--d: 0.4s">Dedicated support team 6 Days a week</li>
                        <li class="mb-2" style="--d: 0.5s">Customized solutions tailored to your needs</li>
                    </ul>
                    <a href="<?php echo SITE_URL; ?>/about.php"
                        class="btn btn-primary mt-3 btn-lg rounded-pill shadow-sm">Learn More About Us</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Get services
$services = [];
try {
    $stmt = $db->prepare("SELECT * FROM services WHERE active = 1 ORDER BY display_order ASC");
    $stmt->execute();
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching services: " . $e->getMessage());
}
?>

<!-- Services Section -->
<section class="services-section section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="section-title text-center">
                    <h2><i class="fas fa-shapes text-success me-2"></i>Our Services</h2>
                    <p>We offer a wide range of educational technology solutions to help schools and institutions thrive
                        in the digital age.</p>
                </div>
            </div>
        </div>
        <div class="position-relative">
            <!-- Scroll Button -->
            <button id="scrollRightBtn" class="scroll-btn d-none d-md-flex">
                <i class="fas fa-chevron-right"></i>
            </button>

            <div class="services-scroll-container" id="servicesContainer">
                <?php if (!empty($services)): ?>
                    <?php foreach ($services as $index => $service): ?>
                        <div class="service-item" data-aos="fade-left" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="service-card">
                                <div class="service-icon">
                                    <i class="<?php echo $service['icon']; ?>"></i>
                                </div>
                                <h4><?php echo $service['title']; ?></h4>
                                <p><?php echo $service['description']; ?></p>
                                <?php if (!empty($service['image'])): ?>
                                    <div class="service-image mt-3">
                                        <img src="<?php echo SITE_URL . '/' . $service['image']; ?>"
                                            alt="<?php echo $service['title']; ?>" class="img-fluid rounded">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback services if none in database -->
                    <div class="service-item" data-aos="fade-left">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <h4>Smart Classroom Solutions</h4>
                            <p>Integrated hardware and software systems designed to create engaging, interactive learning
                                environments.</p>
                        </div>
                    </div>
                    <div class="service-item" data-aos="fade-left" data-aos-delay="100">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-school"></i>
                            </div>
                            <h4>School Management Systems</h4>
                            <p>Comprehensive digital platforms that streamline administrative tasks and improve
                                communication.</p>
                        </div>
                    </div>
                    <div class="service-item" data-aos="fade-left" data-aos-delay="200">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4>Learning Analytics</h4>
                            <p>Data-driven tools that help educators understand student progress and personalize
                                instruction.</p>
                        </div>
                    </div>
                    <div class="service-item" data-aos="fade-left" data-aos-delay="300">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-desktop"></i>
                            </div>
                            <h4>Digital Labs</h4>
                            <p>State-of-the-art computer labs with latest hardware and software for practical learning.</p>
                        </div>
                    </div>
                    <div class="service-item" data-aos="fade-left" data-aos-delay="400">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-robot"></i>
                            </div>
                            <h4>Robotics & AI</h4>
                            <p>Hands-on robotics kits and AI curriculum to prepare students for future technologies.</p>
                        </div>
                    </div>
                    <div class="service-item" data-aos="fade-left" data-aos-delay="500">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="fas fa-book-reader"></i>
                            </div>
                            <h4>E-Library Solutions</h4>
                            <p>Digital library management systems with access to thousands of educational resources.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const container = document.getElementById('servicesContainer');
                const scrollBtn = document.getElementById('scrollRightBtn');
                let scrollAmount = 0;
                let autoScrollSpeed = 2; // Speed of auto scroll
                let isHovered = false;
                let animationId;

                // Clone items for infinite scroll
                const items = Array.from(container.children);
                items.forEach(item => {
                    const clone = item.cloneNode(true);
                    container.appendChild(clone);
                });

                function autoScroll() {
                    if (!isHovered) {
                        container.scrollLeft += autoScrollSpeed;
                        // Seamless Loop: If scrolled past half (original width), reset to 0
                        // Use scrollWidth / 2 because we duplicated the content exactly once
                        if (container.scrollLeft >= container.scrollWidth / 2) { container.scrollLeft = 0; }
                    }
                    animationId = requestAnimationFrame(autoScroll);
                }

                // Start auto scroll
                animationId = requestAnimationFrame(autoScroll);

                // Pause on hover over container OR button
                function pauseScroll() {
                    isHovered = true;
                }

                function resumeScroll() {
                    isHovered = false;
                }

                container.addEventListener('mouseenter', pauseScroll);
                container.addEventListener('mouseleave', resumeScroll);
                scrollBtn.addEventListener('mouseenter', pauseScroll);
                scrollBtn.addEventListener('mouseleave', resumeScroll);

                // Manual scroll button
                scrollBtn.addEventListener('click', () => {
                    // isHovered is already true due to mouseenter
                    const scrollDistance = 350; // Width of card + margin
                    container.scrollBy({
                        left: scrollDistance,
                        behavior: 'smooth'
                    });
                });
            });
        </script>

        <div class="text-center mt-4" data-aos="fade-up">
            <a href="<?php echo SITE_URL; ?>/about.php#services" class="btn btn-outline-primary">Learn More About Our
                Services</a>
        </div>
    </div>
</section>

<?php
// Get counters from database
$counters = [];
try {
    $stmt = $db->prepare("SELECT * FROM counters WHERE active = 1 ORDER BY display_order ASC");
    $stmt->execute();
    $counters = $stmt->fetchAll();

    // If no counters found in database, use default ones
    if (empty($counters)) {
        $counters = [
            [
                'id' => 1,
                'title' => 'Client Retention',
                'value' => 95,
                'icon' => 'fas fa-user-check',
                'display_order' => 1,
                'active' => 1
            ],
            [
                'id' => 2,
                'title' => 'Students Reached',
                'value' => 50000,
                'icon' => 'fas fa-user-graduate',
                'display_order' => 2,
                'active' => 1
            ],
            [
                'id' => 3,
                'title' => 'Years of Experience',
                'value' => 15,
                'icon' => 'fas fa-calendar-alt',
                'display_order' => 3,
                'active' => 1
            ],
            [
                'id' => 4,
                'title' => 'Schools Reached',
                'value' => 500,
                'icon' => 'fas fa-school',
                'display_order' => 4,
                'active' => 1
            ]
        ];
    }
} catch (PDOException $e) {
    // Log error and use default counters
    error_log("Error fetching counters: " . $e->getMessage());
    $counters = [
        [
            'id' => 1,
            'title' => 'Client Retention',
            'value' => 95,
            'icon' => 'fas fa-user-check',
            'display_order' => 1,
            'active' => 1
        ],
        [
            'id' => 2,
            'title' => 'Students Reached',
            'value' => 50000,
            'icon' => 'fas fa-user-graduate',
            'display_order' => 2,
            'active' => 1
        ],
        [
            'id' => 3,
            'title' => 'Years of Experience',
            'value' => 15,
            'icon' => 'fas fa-calendar-alt',
            'display_order' => 3,
            'active' => 1
        ],
        [
            'id' => 4,
            'title' => 'Schools Reached',
            'value' => 500,
            'icon' => 'fas fa-school',
            'display_order' => 4,
            'active' => 1
        ]
    ];
}
?>

<!-- Counter Section -->
<section class="counter-section">
    <div class="container">
        <div class="row">
            <?php if (!empty($counters)): ?>
                <?php foreach ($counters as $index => $counter): ?>
                    <div class="col-lg-3 col-md-6 mb-4 mb-lg-0" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="counter-box">
                            <div class="counter-icon">
                                <i class="<?php echo $counter['icon']; ?>"></i>
                            </div>
                            <div class="counter-number" data-count="<?php echo $counter['value']; ?>">0</div>
                            <div class="counter-text"><?php echo $counter['title']; ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback counters if none in database -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0" data-aos="fade-up">
                    <div class="counter-box">
                        <div class="counter-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="counter-number" data-count="95">0</div>
                        <div class="counter-text">Client Retention</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0" data-aos="fade-up" data-aos-delay="100">
                    <div class="counter-box">
                        <div class="counter-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="counter-number" data-count="50000">0</div>
                        <div class="counter-text">Students Reached</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0" data-aos="fade-up" data-aos-delay="200">
                    <div class="counter-box">
                        <div class="counter-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="counter-number" data-count="15">0</div>
                        <div class="counter-text">Years of Experience</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="counter-box">
                        <div class="counter-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <div class="counter-number" data-count="500">0</div>
                        <div class="counter-text">Schools Reached</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Get testimonials from database (limit to 3)
$testimonials = get_testimonials(3);
?>

<!-- Testimonial Section -->
<section class="testimonials-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="section-title mb-5">
                    <h2 class="title">What Our Clients Say</h2>
                    <div class="title-border mx-auto"></div>
                    <p class="mt-3">Hear from schools and educators who have partnered with FLIONE</p>
                </div>
            </div>
        </div>

        <?php if (!empty($testimonials)): ?>
            <div class="testimonial-carousel">
                <div class="row">
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="testimonial-card h-100 bg-white p-4 rounded shadow-sm">
                                <div class="testimonial-rating mb-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i
                                            class="fas fa-star <?php echo ($i <= $testimonial['rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="testimonial-text mb-4">
                                    <p class="fst-italic">"<?php echo $testimonial['content']; ?>"</p>
                                </div>
                                <div class="testimonial-author d-flex align-items-center">
                                    <?php if (!empty($testimonial['image'])): ?>
                                        <div class="author-image me-3">
                                            <img src="<?php echo SITE_URL . '/' . $testimonial['image']; ?>"
                                                alt="<?php echo $testimonial['name']; ?>" class="rounded-circle" width="60"
                                                height="60">
                                        </div>
                                    <?php endif; ?>
                                    <div class="author-info">
                                        <h5 class="mb-0"><?php echo $testimonial['name']; ?></h5>
                                        <p class="mb-0 text-muted"><?php echo $testimonial['position']; ?>,
                                            <?php echo $testimonial['organization']; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Sample testimonials if none in database -->
            <div class="testimonial-carousel">
                <div class="row">
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="testimonial-card h-100 bg-white p-4 rounded shadow-sm">
                            <div class="testimonial-rating mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <div class="testimonial-text mb-4">
                                <p class="fst-italic">"FLIONE has transformed our school's technology infrastructure. Our
                                    students are more engaged, and our teachers have the tools they need to deliver
                                    exceptional education."</p>
                            </div>
                            <div class="testimonial-author d-flex align-items-center">
                                <div class="author-image me-3">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/testimonials/principal.jpg"
                                        alt="Sarah Johnson" class="rounded-circle" width="60" height="60">
                                </div>
                                <div class="author-info">
                                    <h5 class="mb-0">Sarah Johnson</h5>
                                    <p class="mb-0 text-muted">Principal, Oakridge Academy</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="testimonial-card h-100 bg-white p-4 rounded shadow-sm">
                            <div class="testimonial-rating mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <div class="testimonial-text mb-4">
                                <p class="fst-italic">"The implementation process was seamless, and the ongoing support has
                                    been exceptional. FLIONE truly understands the unique challenges schools face with
                                    technology integration."</p>
                            </div>
                            <div class="testimonial-author d-flex align-items-center">
                                <div class="author-image me-3">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/testimonials/it-director.jpg"
                                        alt="Michael Chen" class="rounded-circle" width="60" height="60">
                                </div>
                                <div class="author-info">
                                    <h5 class="mb-0">Michael Chen</h5>
                                    <p class="mb-0 text-muted">IT Director, Westfield Schools</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="testimonial-card h-100 bg-white p-4 rounded shadow-sm">
                            <div class="testimonial-rating mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <div class="testimonial-text mb-4">
                                <p class="fst-italic">"As a teacher, I appreciate how FLIONE's solutions are designed with
                                    the classroom in mind. The technology enhances my teaching without getting in the way."
                                </p>
                            </div>
                            <div class="testimonial-author d-flex align-items-center">
                                <div class="author-image me-3">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/testimonials/teacher.jpg"
                                        alt="Emily Rodriguez" class="rounded-circle" width="60" height="60">
                                </div>
                                <div class="author-info">
                                    <h5 class="mb-0">Emily Rodriguez</h5>
                                    <p class="mb-0 text-muted">Science Teacher, Greenwood High</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-9 mb-4 mb-lg-0" data-aos="fade-right">
                <h3 class="fw-bold mb-2">Ready to transform your technology experience?</h3>
                <p class="mb-0">Contact us today to discuss how we can help you achieve your technology goals.</p>
            </div>
            <div class="col-lg-3 text-lg-end" data-aos="fade-left">
                <a href="<?php echo SITE_URL; ?>/about.php#contact" class="btn btn-light btn-lg">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<!-- Blog Section -->
<section class="blog-section section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="section-title text-center">
                    <h2><i class="fas fa-newspaper text-info me-2"></i>Latest Blog Posts</h2>
                    <p>Stay updated with our latest news, insights, and educational content.</p>
                </div>
            </div>
        </div>
        <div class="row">
            <?php if (!empty($recent_posts)): ?>
                <?php foreach ($recent_posts as $index => $post): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="blog-card">
                            <div class="blog-img">
                                <?php if (!empty($post['featured_image'])): ?>
                                    <img src="<?php echo SITE_URL . '/' . $post['featured_image']; ?>"
                                        alt="<?php echo $post['title']; ?>">
                                <?php else: ?>
                                    <img src="<?php echo SITE_URL; ?>/assets/images/blog/blog-default.jpg"
                                        alt="<?php echo $post['title']; ?>">
                                <?php endif; ?>
                                <?php if (!empty($post['category_name'])): ?>
                                    <span class="blog-category"><?php echo $post['category_name']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="blog-content">
                                <div class="blog-date">
                                    <i class="far fa-calendar-alt me-2"></i><?php echo format_date($post['created_at']); ?>
                                    <?php if (!empty($post['author_name'])): ?>
                                        <span class="ms-3"><i
                                                class="far fa-user me-2"></i><?php echo $post['author_name']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <h4><?php echo $post['title']; ?></h4>
                                <p><?php echo !empty($post['excerpt']) ? $post['excerpt'] : create_excerpt($post['content']); ?>
                                </p>
                                <a href="<?php echo SITE_URL; ?>/blog-post.php?slug=<?php echo $post['slug']; ?>"
                                    class="blog-link">Read More <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>No blog posts available at the moment. Check back soon!</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-4" data-aos="fade-up">
            <a href="<?php echo SITE_URL; ?>/blog.php" class="btn btn-outline-primary">View All Blog Posts</a>
        </div>
    </div>
</section>

<!-- Clients Section -->
<section class="clients-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="section-title text-center">
                    <h2><i class="fas fa-handshake text-secondary me-2"></i>Our Clients</h2>
                    <p>We collaborate with leading organizations to deliver the best solutions.</p>
                </div>
            </div>
        </div>
        <div class="partners-scroll-container" id="partnersContainer">
            <div class="partner-item">
                <img src="<?php echo SITE_URL; ?>/assets/images/clients/ksvp_logo.jpg" alt="KSVP Client" class="img-fluid">
            </div>
            <div class="partner-item">
                <img src="<?php echo SITE_URL; ?>/assets/images/clients/jnana-deepthi.jpg" alt="Jnana Deepthi English School Client" class="img-fluid">
            </div>
            <div class="partner-item">
                <img src="<?php echo SITE_URL; ?>/assets/images/clients/vivek_international.jpg" alt="Vivek International Public School" class="img-fluid">
            </div>
            <div class="partner-item">
                <img src="<?php echo SITE_URL; ?>/assets/images/clients/martin_luther.jpg" alt="Martin Luther School" class="img-fluid">
            </div>
            <div class="partner-item">
                <img src="<?php echo SITE_URL; ?>/assets/images/clients/phoenix_english.jpg" alt="Phoenix English Medium School" class="img-fluid">
            </div>
            <div class="partner-item">
                <img src="<?php echo SITE_URL; ?>/assets/images/clients/royal_public.jpg" alt="Royal Public School" class="img-fluid">
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const partnersContainer = document.getElementById('partnersContainer');
                let partnersScrollSpeed = 1; // Slightly slower for partners
                let isPartnersHovered = false;
                let partnersAnimationId;

                // Clone items for infinite scroll
                const items = Array.from(partnersContainer.children);
                items.forEach(item => {
                    const clone = item.cloneNode(true);
                    partnersContainer.appendChild(clone);
                });

                function autoScrollPartners() {
                    if (!isPartnersHovered) {
                        partnersContainer.scrollLeft += partnersScrollSpeed;
                        if (partnersContainer.scrollLeft >= partnersContainer.scrollWidth / 2) {
                            partnersContainer.scrollLeft = 0;
                        }
                    }
                    partnersAnimationId = requestAnimationFrame(autoScrollPartners);
                }

                // Start auto scroll
                partnersAnimationId = requestAnimationFrame(autoScrollPartners);

                // Pause on hover
                partnersContainer.addEventListener('mouseenter', () => { isPartnersHovered = true; });
                partnersContainer.addEventListener('mouseleave', () => { isPartnersHovered = false; });
            });
        </script>
    </div>
</section>

<?php include 'includes/footer.php'; ?>