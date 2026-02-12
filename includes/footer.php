</main>

<!-- Newsletter Section -->
<section class="newsletter-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card border-0 shadow">
                    <div class="card-body p-5">
                        <div class="row align-items-center">
                            <div class="col-lg-6 mb-4 mb-lg-0">
                                <h3 class="fw-bold text-primary">Subscribe to Our Newsletter</h3>
                                <p class="text-muted mb-0">Stay updated with our latest news, resources, and educational
                                    content.</p>
                            </div>
                            <div class="col-lg-6">
                                <form id="newsletter-form" action="<?php echo SITE_URL; ?>/process/subscribe.php"
                                    method="post" class="row g-3">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="name" placeholder="Your Name"
                                            required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="email" class="form-control" name="email" placeholder="Your Email"
                                            required>
                                    </div>
                                    <div class="col-12">
                                        <select class="form-select" name="type" required>
                                            <option value="" selected disabled>I am a...</option>
                                            <option value="parent">Parent</option>
                                            <option value="school_staff">School Staff</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary w-100 btn-rect">Subscribe Now</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer text-white pt-5 pb-3">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 mb-4">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo-white.png" alt="<?php echo SITE_NAME; ?>"
                    class="mb-4" height="50">
                <p>Flione Innovation & Technologies Pvt. Ltd. — empowering education with robotics, digital classrooms,
                    and interactive learning. We provide professional IT and STEM-based solutions for schools,
                    educators, and institutions.</p>
                <div class="social-icons mt-3">
                    <a href="<?php echo get_setting('facebook_url') ?: 'javascript:void(0)'; ?>" class="text-white me-3"
                        <?php echo get_setting('facebook_url') ? 'target="_blank"' : ''; ?>><i
                            class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="<?php echo get_setting('twitter_url') ?: 'javascript:void(0)'; ?>" class="text-white me-3"
                        <?php echo get_setting('twitter_url') ? 'target="_blank"' : ''; ?>><i
                            class="fab fa-twitter fa-lg"></i></a>
                    <a href="<?php echo get_setting('linkedin_url') ?: 'javascript:void(0)'; ?>" class="text-white me-3"
                        <?php echo get_setting('linkedin_url') ? 'target="_blank"' : ''; ?>><i
                            class="fab fa-linkedin-in fa-lg"></i></a>
                    <a href="<?php echo get_setting('instagram_url') ?: 'javascript:void(0)'; ?>" class="text-white"
                        <?php echo get_setting('instagram_url') ? 'target="_blank"' : ''; ?>><i
                            class="fab fa-instagram fa-lg"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="mb-4 fw-bold">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo SITE_URL; ?>" class="text-white text-decoration-none">Home</a>
                    </li>
                    <li class="mb-2"><a href="<?php echo SITE_URL; ?>/about.php"
                            class="text-white text-decoration-none">About Us</a></li>
                    <li class="mb-2"><a href="<?php echo SITE_URL; ?>/for-schools.php"
                            class="text-white text-decoration-none">For Schools</a></li>
                    <li class="mb-2"><a href="<?php echo SITE_URL; ?>/for-kids.php"
                            class="text-white text-decoration-none">For Kids</a></li>
                    <li class="mb-2"><a href="<?php echo SITE_URL; ?>/blog.php"
                            class="text-white text-decoration-none">Blog</a></li>
                    <li class="mb-2"><a href="<?php echo SITE_URL; ?>/downloads.php"
                            class="text-white text-decoration-none">Downloads</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/track.php" class="text-white text-decoration-none"><i
                                class="fas fa-search-location me-1"></i> Track Project/Ticket</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-6">
                <h5 class="mb-4 fw-bold">Contact Us</h5>
                <ul class="list-unstyled">
                    <li class="mb-3"><i class="fas fa-map-marker-alt me-2"></i> #34, Basement Floor,Dr.Arunachalam
                        Road,BEML layout 5th stage, Rajarajeshwari Nagar, Bengaluru - 560098, Karnataka, India</li>
                    <li class="mb-3"><i class="fas fa-phone me-2"></i> <?php echo get_setting('contact_phone'); ?></li>
                    <li class="mb-3"><i class="fas fa-envelope me-2"></i>
                        <?php echo get_setting('contact_email') ?: 'contact@flionetech.com'; ?></li>
                    <li><i class="fas fa-clock me-2"></i> Mon - Fri: 10:00 AM - 5:00 PM</li>
                </ul>
            </div>
        </div>
        <hr class="mt-4 mb-4 bg-secondary">
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0"><?php echo get_setting('footer_text'); ?></p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="mb-0">
                    <a href="#" class="text-white text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-white text-decoration-none">Terms of Service</a>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<a href="#" class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></a>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- AOS Animation Library -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Custom JS -->
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
<?php if (basename($_SERVER['PHP_SELF'], '.php') === 'index'): ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/counter.js"></script>
<?php endif; ?>
<script src="<?php echo SITE_URL; ?>/assets/js/about-scroll.js?v=<?php echo time(); ?>"></script>

<script>
    // Initialize AOS
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });

    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('#backToTop').fadeIn();
        } else {
            $('#backToTop').fadeOut();
        }
    });

    $('#backToTop').click(function (e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, 800);
        return false;
    });

    // Newsletter form submission
    $('#newsletter-form').submit(function (e) {
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#newsletter-form').html('<div class="alert alert-success">' + response.message + '</div>');
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert('An error occurred. Please try again later.');
            }
        });
    });
</script>
</body>

</html>