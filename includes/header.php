<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    // Dynamic SEO setup
    $page_title = isset($page_title)
        ? $page_title
        : SITE_NAME . " | Robotics Labs, Interactive Flat Panels & Computer Lab Setup in South India";

    $page_description = isset($page_description)
        ? $page_description
        : "Flione Innovation & Technologies Pvt Ltd (Flione IT) – A leading educational technology company in Bangalore providing interactive flat panels, robotics lab setups, and computer lab installations for schools across Karnataka, Kerala, Tamil Nadu, Andhra Pradesh, and Telangana.";

    $page_keywords = "interactive flat panels Karnataka, interactive flat panel distributors Bangalore, robotics lab setup Karnataka, computer lab setup Karnataka, school IT provider Bangalore, robotics kits for schools, STEM education kits South India, smart classroom solutions Bangalore, Flione IT Rajarajeshwari Nagar, educational technology company Bangalore, robotics education setup Tamil Nadu, robotics lab supplier Andhra Pradesh, robotics kits Telangana, Flione Innovation Technologies Bangalore South";
    ?>

    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($page_keywords); ?>">
    <meta name="robots" content="index, follow">
    <meta name="revisit-after" content="3 days">
    <meta name="author" content="Flione Innovation and Technologies Pvt Ltd, Bangalore">
    <meta name="geo.region" content="IN-KA">
    <meta name="geo.placename" content="Rajarajeshwari Nagar, Bangalore">
    <meta name="geo.position" content="12.913030322616823, 77.5281606537633">
    <meta name="ICBM" content="12.913030322616823, 77.5281606537633">

    <!-- Extra OG Tag for Product SEO -->
    <meta property="og:title"
        content="Interactive White Digital Board and Interactive LED Panel OEM Manufacturer | Bengaluru">
    <meta property="og:site_name" content="Flione IT">
    <meta property="og:locale" content="en_IN">
    <meta property="og:region" content="Karnataka">
    <meta property="og:country-name" content="India">

    <!-- Open Graph / Facebook -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:image" content="<?php echo SITE_URL; ?>/assets/images/logo/logo.png">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    <meta property="og:type" content="website">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL; ?>/assets/images/logo/logo.png">


    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/images/logo/logo.png" type="image/png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/products.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/kids.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/blog.css">
    <?php if (basename($_SERVER['PHP_SELF'], '.php') === 'about'): ?>
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/about.css">
    <?php endif; ?>
    <?php if (in_array(basename($_SERVER['PHP_SELF'], '.php'), ['careers', 'job-details'])): ?>
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/careers.css">
    <?php endif; ?>
    <?php if (basename($_SERVER['PHP_SELF'], '.php') === 'downloads'): ?>
        <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/downloads.css">
    <?php endif; ?>
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar bg-dark text-white py-2">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <span><i class="fas fa-envelope me-2"></i><?php echo SITE_EMAIL; ?></span>
                    <span class="ms-3"><i
                            class="fas fa-phone me-2"></i><?php echo get_setting('contact_phone'); ?></span>
                </div>
                <div class="col-md-6 text-end">
                    <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white me-2"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header sticky-top">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo/logo.png" alt="<?php echo SITE_NAME; ?>"
                        height="50">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_page('index') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_page('about') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/about.php">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_page('for-schools') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/for-schools.php">For Schools</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_page('for-kids') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/for-kids.php">For Kids</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_page('blog') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/blog.php">Blog</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_page('downloads') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/downloads.php">Downloads</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_page('careers') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/careers.php">Careers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_page('csr') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/csr.php">CSR</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_page('track') ? 'active' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/track.php">
                                <i class="fas fa-search-location me-1"></i> Track
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white ms-2 px-4 btn-contact-cta"
                                href="<?php echo SITE_URL; ?>/about.php#contact">Contact Us</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>