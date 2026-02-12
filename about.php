<?php
require_once 'includes/config.php';
$page_title = "About Us - FLIONE";
$meta_description = "Learn about FLIONE - Future-ready Learning Infrastructure Optimized for Next-gen Education. Our mission, vision, and commitment to transforming education.";
$meta_keywords = "FLIONE, educational technology, school infrastructure, learning ecosystem, edtech";
include 'includes/header.php';
?>

<!-- Custom CSS for the new About page -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/about-new.css?v=<?php echo time(); ?>_2">

<?php
// Get testimonials
$testimonials = get_testimonials();
?>

<!-- Hero Section -->
<section class="about-hero">
    <div class="container about-hero-content">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="hero-title">Transforming Education Through Technology</h1>
                <p class="hero-desc">At FLIONE, we're building the future of education with innovative, child-centric
                    technology solutions that empower schools and inspire young minds.</p>
                <a href="#contact" class="btn btn-light btn-lg hero-btn">Get In Touch</a>
            </div>
        </div>
    </div>
    <a href="#about" class="scroll-down">
        <i class="fas fa-chevron-down"></i>
    </a>
</section>

<!-- About Us Section -->
<section id="about" class="about-section position-relative overflow-hidden">
    <!-- Alternate Background: Tech Grid -->
    <div class="tech-grid-bg"></div>

    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="about-image-wrapper item-tilt" data-aos="fade-right" data-aos-duration="1000">
                    <div class="img-backdrop"></div>
                    <img src="<?php echo SITE_URL; ?>/assets/images/about/about-main.jpg" alt="FLIONE Team"
                        class="img-fluid rounded shadow-lg position-relative tilt-element">
                    <div class="experience-badge">
                        <span class="years">5+</span>
                        <span class="text">Years of<br>Experience</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                <div class="about-content">
                    <div class="section-title">
                        <h2>Who We Are</h2>
                    </div>
                    <p class="lead">At FLIONE (Future-ready Learning Infrastructure Optimized for Next-gen Education),
                        we're on a mission to transform the way schools operate and students learn.</p>
                    <p>We design smart, scalable, and child-centric educational technologies that empower institutions
                        to deliver inspired, future-ready education.</p>
                    <p>Founded with a deep passion for bridging the gap between innovation and impact, FLIONE blends
                        hardware, software, and thoughtful design to build learning ecosystems that are modern,
                        reliable, and truly learner-focused.</p>

                    <div class="row mt-4">
                        <div class="col-md-6 mb-3">
                            <div class="feature-box" data-aos="fade-up" data-aos-duration="800" data-aos-delay="100">
                                <div class="icon-box">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                <h5 class="mb-0">Innovative Solutions</h5>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="feature-box" data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
                                <div class="icon-box">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h5 class="mb-0">Child-Centric Approach</h5>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="feature-box" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
                                <div class="icon-box">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <h5 class="mb-0">Scalable Technology</h5>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="feature-box" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                                <div class="icon-box">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <h5 class="mb-0">Educational Expertise</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>




<!-- Our Team Section -->
<section class="team-section position-relative overflow-hidden">
    <!-- Floating Background Shapes -->
    <div class="anim-shape shape-1"></div>
    <div class="anim-shape shape-2"></div>

    <div class="container position-relative z-index-1">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up">
                <div class="section-title mb-5">
                    <h2 class="title">Our Team</h2>
                    <div class="title-border mx-auto"></div>
                    <p class="mt-3">Meet the dedicated professionals behind FLIONE</p>
                </div>
            </div>
        </div>
        <div class="team-carousel-wrapper position-relative">
            <!-- Custom Navigation Button -->
            <div class="swiper-button-next-custom">
                <i class="fas fa-arrow-right"></i>
            </div>

            <div class="swiper teamSwiper">
                <div class="swiper-wrapper">
                    <!-- Team Member New (Founder) -->
                    <div class="swiper-slide">
                        <div class="team-card-wrapper">
                            <div class="team-card">
                                <div class="team-header">
                                    <div class="team-image-circle">
                                        <img src="<?php echo SITE_URL; ?>/assets/images/team/founder.png"
                                            alt="Srinidhi Chilakamarri K">
                                        <div class="team-social-overlay">
                                            <a href="https://www.linkedin.com/in/srinidhi-chilakamarri-k-?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app"
                                                class="social-icon" target="blank"><i class="fab fa-linkedin-in"></i></a>
                                            <a href="https://www.instagram.com/srinidhichilakamarrik?igsh=MXJuc2I5azJhZXBmZA==" class="social-icon" target="blank"><i class="fab fa-instagram"></i></a>
                                            <a href="https://mail.google.com/mail/?view=cm&to=reach.flione@gmail.com" class="social-icon" target="blank"><i class="fas fa-envelope"></i></a>
                                        </div>
                                    </div>
                                    <h4 class="mt-3 mb-1">Srinidhi Chilakamarri K</h4>
                                    <p class="team-role">Founder and CEO</p>
                                </div>
                                <div class="team-bio-box">
                                    <p>Srinidhi leads the company’s mission to transform education through purposeful
                                        technology. With experience in school ERP systems, RFID automation, robotics labs,
                                        and digital learning solutions.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Team Member 1 -->
                    <div class="swiper-slide">
                        <div class="team-card-wrapper">
                            <div class="team-card">
                                <div class="team-header">
                                    <div class="team-image-circle">
                                        <img src="<?php echo SITE_URL; ?>/assets/images/team/person1.png" alt="Suchitha">
                                        <div class="team-social-overlay">
                                            <a href="https://www.linkedin.com/in/suchitha-hs/" class="social-icon" target="_blank"><i
                                                    class="fab fa-linkedin-in"></i></a>
                                            <a href="https://www.instagram.com/suchitha27?igsh=dDZ3enAwYzJwYXc5" class="social-icon" target="_blank"><i class="fab fa-instagram"></i></a>
                                            <a href="https://mail.google.com/mail/?view=cm&to=parisuchi27@gmail.com" class="social-icon" target="_blank"><i class="fas fa-envelope"></i></a>
                                        </div>
                                    </div>
                                    <h4 class="mt-3 mb-1">Suchitha</h4>
                                    <p class="team-role">Project Manager</p>
                                </div>
                                <div class="team-bio-box">
                                    <p>Leads the development team while designing, developing, and maintaining scalable web
                                        applications using PHP, Python, and SQL. Oversees backend architecture.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Team Member 2 -->
                    <div class="swiper-slide">
                        <div class="team-card-wrapper">
                            <div class="team-card">
                                <div class="team-header">
                                    <div class="team-image-circle">
                                        <img src="<?php echo SITE_URL; ?>/assets/images/team/sumant.png" alt="Sumant">
                                        <div class="team-social-overlay">
                                            <a href="https://www.linkedin.com/in/sumant-bhat-b57867293?utm_source=share_via&utm_content=profile&utm_medium=member_ios" class="social-icon" target="blank"><i class="fab fa-linkedin-in"></i></a>
                                            <a href="https://www.instagram.com/sumantbhat_?igsh=aGp6bHR4NXppemlp" class="social-icon" target="blank"><i class="fab fa-instagram"></i></a>
                                            <a href="https://mail.google.com/mail/?view=cm&to=sumantsbhatta@gmail.com" class="social-icon" target="blank"><i class="fas fa-envelope"></i></a>
                                        </div>
                                    </div>
                                    <h4 class="mt-3 mb-1">Sumant</h4>
                                    <p class="team-role">Flutter Developer</p>
                                </div>
                                <div class="team-bio-box">
                                    <p>Driving technical innovation, Sumant ensures FLIONE stays ahead of the curve,
                                        delivering robust and scalable solutions for mobile platforms.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DUPLICATE SLIDES FOR LOOPING -->
                    <!-- Team Member New (Founder) -->
                    <div class="swiper-slide">
                        <div class="team-card-wrapper">
                            <div class="team-card">
                                <div class="team-header">
                                    <div class="team-image-circle">
                                        <img src="<?php echo SITE_URL; ?>/assets/images/team/founder.png"
                                            alt="Srinidhi Chilakamarri K">
                                        <div class="team-social-overlay">
                                            <a href="https://www.linkedin.com/in/srinidhi-chilakamarri-k-?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app"
                                                class="social-icon" target="blank"><i class="fab fa-linkedin-in"></i></a>
                                            <a href="https://www.instagram.com/srinidhichilakamarrik?igsh=MXJuc2I5azJhZXBmZA==" class="social-icon" target="blank"><i class="fab fa-instagram"></i></a>
                                            <a href="https://mail.google.com/mail/?view=cm&to=reach.flione@gmail.com" class="social-icon" target="blank"><i class="fas fa-envelope"></i></a>
                                        </div>
                                    </div>
                                    <h4 class="mt-3 mb-1">Srinidhi Chilakamarri K</h4>
                                    <p class="team-role">Founder and CEO</p>
                                </div>
                                <div class="team-bio-box">
                                    <p>Srinidhi leads the company’s mission to transform education through purposeful
                                        technology. With experience in school ERP systems, RFID automation, robotics labs,
                                        and digital learning solutions.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Team Member 1 -->
                    <div class="swiper-slide">
                        <div class="team-card-wrapper">
                            <div class="team-card">
                                <div class="team-header">
                                    <div class="team-image-circle">
                                        <img src="<?php echo SITE_URL; ?>/assets/images/team/person1.png" alt="Suchitha">
                                        <div class="team-social-overlay">
                                            <a href="https://www.linkedin.com/in/suchitha-hs/" class="social-icon" target="_blank"><i
                                                    class="fab fa-linkedin-in"></i></a>
                                            <a href="https://www.instagram.com/suchitha27?igsh=dDZ3enAwYzJwYXc5" class="social-icon" target="_blank"><i class="fab fa-instagram"></i></a>
                                            <a href="https://mail.google.com/mail/?view=cm&to=parisuchi27@gmail.com" class="social-icon" target="_blank"><i class="fas fa-envelope"></i></a>
                                        </div>
                                    </div>
                                    <h4 class="mt-3 mb-1">Suchitha</h4>
                                    <p class="team-role">Project Manager</p>
                                </div>
                                <div class="team-bio-box">
                                    <p>Leads the development team while designing, developing, and maintaining scalable web
                                        applications using PHP, Python, and SQL. Oversees backend architecture.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Team Member 2 -->
                    <div class="swiper-slide">
                        <div class="team-card-wrapper">
                            <div class="team-card">
                                <div class="team-header">
                                    <div class="team-image-circle">
                                        <img src="<?php echo SITE_URL; ?>/assets/images/team/sumant.png" alt="Sumant">
                                        <div class="team-social-overlay">
                                            <a href="https://www.linkedin.com/in/sumant-bhat-b57867293?utm_source=share_via&utm_content=profile&utm_medium=member_ios" class="social-icon" target="blank"><i class="fab fa-linkedin-in"></i></a>
                                            <a href="https://www.instagram.com/sumantbhat_?igsh=aGp6bHR4NXppemlp" class="social-icon" target="blank"><i class="fab fa-instagram"></i></a>
                                            <a href="https://mail.google.com/mail/?view=cm&to=sumantsbhatta@gmail.com" class="social-icon" target="blank"><i class="fas fa-envelope"></i></a>
                                        </div>
                                    </div>
                                    <h4 class="mt-3 mb-1">Sumant</h4>
                                    <p class="team-role">Flutter Developer</p>
                                </div>
                                <div class="team-bio-box">
                                    <p>Driving technical innovation, Sumant ensures FLIONE stays ahead of the curve,
                                        delivering robust and scalable solutions for mobile platforms.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Initialize Swiper -->
    <script>
        var teamSwiper = new Swiper(".teamSwiper", {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            speed: 800,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            navigation: {
                nextEl: ".swiper-button-next-custom",
            },
            breakpoints: {
                640: {
                    slidesPerView: 1,
                    spaceBetween: 20,
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 30,
                },
            },
        });
    </script>
</section>

<!-- Vision & Mission Section -->
<section class="vision-mission-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up" data-aos-duration="1000">
                <div class="section-title mb-5">
                    <h2 class="title">Our Vision & Mission</h2>
                    <div class="title-border mx-auto"></div>
                    <p class="mt-3">Guiding principles that drive our innovation and impact</p>
                </div>
            </div>
        </div>

        <div class="vision-mission-tabs">
            <div class="vm-tab-nav" data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
                <button class="vm-tab-btn active" data-tab="vision">Our Vision</button>
                <button class="vm-tab-btn" data-tab="mission">Our Mission</button>
            </div>

            <div class="vm-tab-content active" id="vision" data-aos="fade-up" data-aos-duration="800"
                data-aos-delay="300">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="vm-card">
                            <div class="vm-card-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h3>Our Vision</h3>
                            <p>To transform education by building future-ready learning ecosystems that empower every
                                child to thrive through inspired, technology-driven experiences.We envision a world
                                where learning goes beyond classrooms, fostering creativity, critical thinking, and
                                lifelong curiosity. By integrating innovation, accessibility, and modern digital tools,
                                we aim to create inclusive educational environments that prepare students to adapt,
                                grow, and succeed in an ever-evolving global landscape.</p>
                            <img src="<?php echo SITE_URL; ?>/assets/images/about/vision.png" alt="FLIONE Vision"
                                class="img-fluid rounded">
                        </div>
                    </div>
                </div>
            </div>

            <div class="vm-tab-content" id="mission" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="vm-card">
                            <div class="vm-card-icon">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <h3>Our Mission</h3>
                            <p>To design and deliver innovative educational technologies that optimize school
                                infrastructure, engage students, and enable educators to create nurturing, next-gen
                                learning environments. We strive to bridge the gap between traditional education and
                                modern skill requirements by providing engaging platforms, personalized learning
                                experiences, and continuous support for students, educators, and institutions. Through
                                collaboration and innovation, we are committed to nurturing confident learners who are
                                equipped for the challenges of tomorrow.</p>
                            <img src="<?php echo SITE_URL; ?>/assets/images/about/mission.jpg" alt="FLIONE Mission"
                                class="img-fluid rounded">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Values Section -->
<section class="values-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up" data-aos-duration="1000">
                <div class="section-title mb-5">
                    <h2 class="title">Our Core Values</h2>
                    <div class="title-border mx-auto"></div>
                    <p class="mt-3">The principles that guide everything we do at FLIONE</p>
                </div>
            </div>
        </div>

        <div class="values-grid">
            <div class="value-card" data-aos="fade-up" data-aos-duration="800" data-aos-delay="100">
                <div class="value-icon">
                    <i class="fas fa-child"></i>
                </div>
                <h4>Child-First Thinking</h4>
                <p>We place children at the center of every design decision, ensuring our solutions enhance their
                    learning journey.</p>
            </div>

            <div class="value-card" data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
                <div class="value-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h4>Innovation</h4>
                <p>We constantly push boundaries to create solutions that address both current needs and future
                    challenges.</p>
            </div>

            <div class="value-card" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
                <div class="value-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h4>Partnership</h4>
                <p>We believe in collaborative relationships with schools, educators, and communities to create lasting
                    impact.</p>
            </div>

            <div class="value-card" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                <div class="value-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h4>Reliability</h4>
                <p>We build robust, dependable systems that schools can count on day after day, year after year.</p>
            </div>

            <div class="value-card" data-aos="fade-up" data-aos-duration="800" data-aos-delay="500">
                <div class="value-icon">
                    <i class="fas fa-universal-access"></i>
                </div>
                <h4>Accessibility</h4>
                <p>We design inclusive solutions that work for all learners, regardless of background or ability.</p>
            </div>

            <div class="value-card" data-aos="fade-up" data-aos-duration="800" data-aos-delay="600">
                <div class="value-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <h4>Sustainability</h4>
                <p>We create solutions with longevity in mind, both in terms of environmental impact and long-term
                    educational value.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up" data-aos-duration="1000">
                <div class="section-title mb-5">
                    <h2 class="title">What Our Clients Say</h2>
                    <div class="title-border mx-auto"></div>
                    <p class="mt-3">Hear from schools and educators who have partnered with FLIONE</p>
                </div>
            </div>
        </div>

        <div class="testimonial-slider" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
            <div class="row">
                <?php if (!empty($testimonials)): ?>
                    <?php foreach ($testimonials as $testimonial): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="testimonial-card">
                                <div class="testimonial-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i
                                            class="fas fa-star <?php echo ($i <= $testimonial['rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="testimonial-text">
                                    <p>"<?php echo $testimonial['content']; ?>"</p>
                                </div>
                                <div class="testimonial-author">
                                    <?php if (!empty($testimonial['image'])): ?>
                                        <div class="author-image">
                                            <img src="<?php echo SITE_URL . '/' . $testimonial['image']; ?>"
                                                alt="<?php echo $testimonial['name']; ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div class="author-info">
                                        <h5><?php echo $testimonial['name']; ?></h5>
                                        <p><?php echo $testimonial['position']; ?>, <?php echo $testimonial['organization']; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Sample testimonials if none in database -->
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="testimonial-card">
                            <div class="testimonial-rating">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <div class="testimonial-text">
                                <p>"FLIONE has completely transformed how our students engage with technology. The solutions
                                    are intuitive, reliable, and truly enhance the learning experience."</p>
                            </div>
                            <div class="testimonial-author">
                                <div class="author-image">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/testimonials/testimonial-1.jpg"
                                        alt="Sarah Johnson">
                                </div>
                                <div class="author-info">
                                    <h5>Sarah Johnson</h5>
                                    <p>Principal, Oakridge Academy</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="testimonial-card">
                            <div class="testimonial-rating">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <div class="testimonial-text">
                                <p>"The implementation process was seamless, and the ongoing support has been exceptional.
                                    FLIONE truly understands the unique challenges schools face with technology
                                    integration."</p>
                            </div>
                            <div class="testimonial-author">
                                <div class="author-image">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/testimonials/testimonial-2.jpg"
                                        alt="Michael Chen">
                                </div>
                                <div class="author-info">
                                    <h5>Michael Chen</h5>
                                    <p>IT Director, Westfield Schools</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="testimonial-card">
                            <div class="testimonial-rating">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <div class="testimonial-text">
                                <p>"As a teacher, I appreciate how FLIONE's solutions are designed with the classroom in
                                    mind. The technology enhances my teaching without getting in the way."</p>
                            </div>
                            <div class="testimonial-author">
                                <div class="author-image">
                                    <img src="<?php echo SITE_URL; ?>/assets/images/testimonials/testimonial-3.jpg"
                                        alt="Emily Rodriguez">
                                </div>
                                <div class="author-info">
                                    <h5>Emily Rodriguez</h5>
                                    <p>Science Teacher, Greenwood High</p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Contact Us Section -->
<section id="contact" class="contact-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center" data-aos="fade-up" data-aos-duration="1000">
                <div class="section-title mb-5">
                    <h2 class="title">Get In Touch</h2>
                    <div class="title-border mx-auto"></div>
                    <p class="mt-3">Have questions about how FLIONE can help your school? We'd love to hear from you!
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5 mb-4 mb-lg-0" data-aos="fade-right" data-aos-duration="1000">
                <div class="contact-info">
                    <h4>Contact Information</h4>

                    <div class="info-item">
                        <div class="icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="text">
                            <h5>Our Location</h5>
                            <p>34, Dr. Arunachalam Road, BEML Layout, 5th stage, Shri Rajarejashwari Nagar<br>Bangalore
                                -560098</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="text">
                            <h5>Call Us</h5>
                            <p>+91 8296817008</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="text">
                            <h5>Email Us</h5>
                            <p>reach@flioneit.com</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="text">
                            <h5>Working Hours</h5>
                            <p>Monday - Friday: 10:00 AM - 5:00 PM</p>
                        </div>
                    </div>

                    <div class="social-links mt-4">
                        <h5>Follow Us</h5>
                        <div class="d-flex mt-2">
                            <a href="#" class="social-link me-2"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link me-2"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link me-2"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                <div class="contact-form">
                    <h4>Send Us a Message</h4>

                    <?php if (isset($_SESSION['contact_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['contact_success']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['contact_success']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['contact_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['contact_error']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['contact_error']); ?>
                    <?php endif; ?>

                    <form id="contact-form" action="<?php echo SITE_URL; ?>/process-contact.php" method="post">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Your Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">I am a:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="contact_type" id="school_admin"
                                    value="school_admin" checked>
                                <label class="form-check-label" for="school_admin">School Administrator</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="contact_type" id="teacher"
                                    value="teacher">
                                <label class="form-check-label" for="teacher">Teacher</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="contact_type" id="parent"
                                    value="parent">
                                <label class="form-check-label" for="parent">Parent</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="contact_type" id="other"
                                    value="other">
                                <label class="form-check-label" for="other">Other</label>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter" value="1">
                            <label class="form-check-label" for="newsletter">Subscribe to our newsletter</label>
                        </div>
                        <div class="mb-3">
                            <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section">
    <div class="container-fluid p-0">
        <div class="map-container">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3888.920246781666!2d77.52556427572256!3d12.912847316162411!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xa842319c705e6b73%3A0x24bddcc162f8da19!2sFlione%20Innovation%20%26%20technologies%20Private%20Limited!5e0!3m2!1sen!2sin!4v1755862403571!5m2!1sen!2sin"
                width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
</section>

<!-- JavaScript for Vision & Mission Tabs -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tab functionality
        const tabBtns = document.querySelectorAll('.vm-tab-btn');
        const tabContents = document.querySelectorAll('.vm-tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const tabId = this.getAttribute('data-tab');

                // Remove active class from all buttons and contents
                tabBtns.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                // Add active class to current button and content
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);

                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Initialize AOS animations
        AOS.init({
            once: true,
            offset: 100,
            duration: 800
        });
    });
</script>

<?php include 'includes/footer.php'; ?>