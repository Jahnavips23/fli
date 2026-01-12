<?php
require_once 'config.php';
require_admin_login();

$current_admin = get_current_admin();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo get_admin_page_title(); ?> | <?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/images/logo/logo.png" type="image/png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <!-- Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="<?php echo ADMIN_ASSETS; ?>/css/admin-style.css">
    
    <!-- Custom Inline CSS -->
    <style>
        /* Fix for analytics chart */
        #visitorChart {
            max-height: 300px !important;
            height: 300px !important;
        }
        .card-body {
            overflow: hidden;
        }
        
        /* Chart loading indicator */
        .chart-loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }
    </style>
</head>
<body>
    <!-- Admin Wrapper -->
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-header">
                <a href="<?php echo ADMIN_URL; ?>/index.php">
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo/logo.png" alt="<?php echo SITE_NAME; ?>" height="40">
                </a>
                <button type="button" id="sidebarCollapse" class="btn btn-sm d-md-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="sidebar-user">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle fa-3x"></i>
                    </div>
                    <div class="user-details">
                        <h6><?php echo $current_admin['username']; ?></h6>
                        <span>Administrator</span>
                    </div>
                </div>
            </div>
            
            <ul class="list-unstyled sidebar-menu">
                <li class="<?php echo $current_page === 'index' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/index.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="menu-header">Content Management</li>
                <li class="<?php echo $current_page === 'carousel' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/carousel.php">
                        <i class="fas fa-images"></i> Carousel Slides
                    </a>
                </li>
                <li class="<?php echo in_array($current_page, ['blog-posts', 'blog-categories']) ? 'active' : ''; ?>">
                    <a href="#blogSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-blog"></i> Blog
                    </a>
                    <ul class="collapse list-unstyled <?php echo in_array($current_page, ['blog-posts', 'blog-categories']) ? 'show' : ''; ?>" id="blogSubmenu">
                        <li class="<?php echo $current_page === 'blog-posts' ? 'active' : ''; ?>">
                            <a href="<?php echo ADMIN_URL; ?>/pages/blog-posts.php">
                                <i class="fas fa-file-alt"></i> Posts
                            </a>
                        </li>
                        <li class="<?php echo $current_page === 'blog-categories' ? 'active' : ''; ?>">
                            <a href="<?php echo ADMIN_URL; ?>/pages/blog-categories.php">
                                <i class="fas fa-folder"></i> Categories
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="<?php echo $current_page === 'downloads' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/downloads.php">
                        <i class="fas fa-download"></i> Downloads
                    </a>
                </li>
                <li class="<?php echo $current_page === 'testimonials' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/testimonials.php">
                        <i class="fas fa-quote-right"></i> Testimonials
                    </a>
                </li>
                <li class="<?php echo $current_page === 'services' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/services.php">
                        <i class="fas fa-cogs"></i> Services
                    </a>
                </li>
                <li class="<?php echo $current_page === 'counters' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/counters.php">
                        <i class="fas fa-sort-numeric-up"></i> Counters
                    </a>
                </li>
                <li class="<?php echo $current_page === 'products' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/products.php">
                        <i class="fas fa-box"></i> Products
                    </a>
                </li>
                <li class="<?php echo $current_page === 'success-stories' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/success-stories.php">
                        <i class="fas fa-award"></i> Success Stories
                    </a>
                </li>
                <li class="<?php echo $current_page === 'product-enquiries' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/product-enquiries.php">
                        <i class="fas fa-question-circle"></i> Product Enquiries
                    </a>
                </li>
                <li class="<?php echo $current_page === 'kids-programs' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/kids-programs.php">
                        <i class="fas fa-child"></i> Kids Programs
                    </a>
                </li>
                <li class="<?php echo $current_page === 'program-registrations' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/program-registrations.php">
                        <i class="fas fa-clipboard-list"></i> Program Registrations
                    </a>
                </li>
                <li class="<?php echo $current_page === 'kids-products' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/kids-products.php">
                        <i class="fas fa-robot"></i> Kids Products
                    </a>
                </li>
                <li class="<?php echo $current_page === 'product-inquiries' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/product-inquiries.php">
                        <i class="fas fa-question-circle"></i> Product Inquiries
                    </a>
                </li>
                <li class="<?php echo in_array($current_page, ['projects', 'project-statuses']) ? 'active' : ''; ?>">
                    <a href="#projectsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-tasks"></i> Project Tracking
                    </a>
                    <ul class="collapse list-unstyled <?php echo in_array($current_page, ['projects', 'project-statuses']) ? 'show' : ''; ?>" id="projectsSubmenu">
                        <li class="<?php echo $current_page === 'projects' ? 'active' : ''; ?>">
                            <a href="<?php echo ADMIN_URL; ?>/pages/projects.php">
                                <i class="fas fa-project-diagram"></i> Projects
                            </a>
                        </li>
                        <li class="<?php echo $current_page === 'project-statuses' ? 'active' : ''; ?>">
                            <a href="<?php echo ADMIN_URL; ?>/pages/project-statuses.php">
                                <i class="fas fa-tags"></i> Status Options
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="<?php echo in_array($current_page, ['job-listings', 'add-job', 'edit-job', 'job-applications']) ? 'active' : ''; ?>">
                    <a href="#careersSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-briefcase"></i> Careers
                    </a>
                    <ul class="collapse list-unstyled <?php echo in_array($current_page, ['job-listings', 'add-job', 'edit-job', 'job-applications']) ? 'show' : ''; ?>" id="careersSubmenu">
                        <li class="<?php echo in_array($current_page, ['job-listings', 'add-job', 'edit-job']) ? 'active' : ''; ?>">
                            <a href="<?php echo ADMIN_URL; ?>/pages/job-listings.php">
                                <i class="fas fa-list"></i> Job Listings
                            </a>
                        </li>
                        <li class="<?php echo $current_page === 'job-applications' ? 'active' : ''; ?>">
                            <a href="<?php echo ADMIN_URL; ?>/pages/job-applications.php">
                                <i class="fas fa-file-alt"></i> Applications
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-header">Communication</li>
                <li class="<?php echo $current_page === 'contact-messages' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/contact-messages.php">
                        <i class="fas fa-inbox"></i> Contact Messages
                        <?php 
                        // Get unread messages count
                        $unread_count = 0;
                        try {
                            $stmt = $db->prepare("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
                            $stmt->execute();
                            $result = $stmt->fetch();
                            $unread_count = $result['count'];
                        } catch (PDOException $e) {
                            // Silently fail
                        }
                        if ($unread_count > 0): 
                        ?>
                        <span class="badge bg-danger rounded-pill ms-2"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="<?php echo $current_page === 'subscribers' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/subscribers.php">
                        <i class="fas fa-envelope"></i> Subscribers
                    </a>
                </li>
                
                <li class="menu-header">Customer Management</li>
                <li class="<?php echo $current_page === 'client-inquiries' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/client-inquiries.php">
                        <i class="fas fa-user-plus"></i> Client Inquiries
                        <?php 
                        // Get new inquiries count
                        $new_inquiries = 0;
                        try {
                            $stmt = $db->prepare("SELECT COUNT(*) as count FROM client_inquiries WHERE status = 'new'");
                            $stmt->execute();
                            $result = $stmt->fetch();
                            $new_inquiries = $result['count'];
                        } catch (PDOException $e) {
                            // Silently fail
                        }
                        if ($new_inquiries > 0): 
                        ?>
                        <span class="badge bg-danger rounded-pill ms-2"><?php echo $new_inquiries; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="<?php echo in_array($current_page, ['email-templates', 'email-attachments']) ? 'active' : ''; ?>">
                    <a href="#emailSettingsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-envelope"></i> Email Settings
                    </a>
                    <ul class="collapse list-unstyled <?php echo in_array($current_page, ['email-templates', 'email-attachments']) ? 'show' : ''; ?>" id="emailSettingsSubmenu">
                        <li class="<?php echo $current_page === 'email-templates' ? 'active' : ''; ?>">
                            <a href="<?php echo ADMIN_URL; ?>/pages/email-templates.php">
                                <i class="fas fa-file-alt"></i> Email Templates
                            </a>
                        </li>
                        <li class="<?php echo $current_page === 'email-attachments' ? 'active' : ''; ?>">
                            <a href="<?php echo ADMIN_URL; ?>/pages/email-attachments.php">
                                <i class="fas fa-paperclip"></i> Attachments
                            </a>
                        </li>
                    </ul>
                </li>
                
                <li class="menu-header">Customer Support</li>
                <li class="<?php echo $current_page === 'customer-tickets' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/customer-tickets.php">
                        <i class="fas fa-ticket-alt"></i> Support Tickets
                        <?php 
                        // Get open tickets count
                        $open_tickets = 0;
                        try {
                            $stmt = $db->prepare("
                                SELECT COUNT(*) as count 
                                FROM customer_tickets t
                                JOIN ticket_statuses s ON t.status_id = s.id
                                WHERE s.is_closed = 0
                            ");
                            $stmt->execute();
                            $result = $stmt->fetch();
                            $open_tickets = $result['count'];
                        } catch (PDOException $e) {
                            // Silently fail
                        }
                        if ($open_tickets > 0): 
                        ?>
                        <span class="badge bg-danger rounded-pill ms-2"><?php echo $open_tickets; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="<?php echo in_array($current_page, ['ticket-categories', 'ticket-priorities', 'ticket-statuses']) ? 'active' : ''; ?>">
                    <a href="#ticketSettingsSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <i class="fas fa-cog"></i> Ticket Settings
                    </a>
                    <ul class="collapse list-unstyled <?php echo in_array($current_page, ['ticket-categories', 'ticket-priorities', 'ticket-statuses']) ? 'show' : ''; ?>" id="ticketSettingsSubmenu">
                        <li class="<?php echo $current_page === 'ticket-categories' ? 'active' : ''; ?>">
                            <a href="<?php echo ADMIN_URL; ?>/pages/ticket-categories.php">
                                <i class="fas fa-folder"></i> Categories
                            </a>
                        </li>
                        <li class="<?php echo $current_page === 'ticket-priorities' ? 'active' : ''; ?>">
                            <a href="<?php echo ADMIN_URL; ?>/pages/ticket-priorities.php">
                                <i class="fas fa-flag"></i> Priorities
                            </a>
                        </li>
                        <li class="<?php echo $current_page === 'ticket-statuses' ? 'active' : ''; ?>">
                            <a href="<?php echo ADMIN_URL; ?>/pages/ticket-statuses.php">
                                <i class="fas fa-tags"></i> Statuses
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-header">User Management</li>
                <li class="<?php echo $current_page === 'users' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li class="menu-header">Settings</li>
                <li class="<?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/settings.php">
                        <i class="fas fa-cog"></i> Site Settings
                    </a>
                </li>
                <li class="<?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                    <a href="<?php echo ADMIN_URL; ?>/pages/profile.php">
                        <i class="fas fa-user-cog"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="<?php echo ADMIN_URL; ?>/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="<?php echo SITE_URL; ?>" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Website
                </a>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div id="content" class="content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapseDesktop" class="btn">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="d-flex ms-auto">

                        <div class="dropdown">
                            <button class="btn d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-2"></i>
                                <span class="d-none d-md-inline"><?php echo $current_admin['username']; ?></span>
                                <i class="fas fa-chevron-down ms-2"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/pages/profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/pages/settings.php">Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Page Content -->
            <div class="container-fluid py-4">
                <div class="page-header mb-4">
                    <div class="row align-items-center">
                        <div class="col">
                            <h1 class="page-title"><?php echo get_admin_page_title(); ?></h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/index.php">Dashboard</a></li>
                                    <?php if ($current_page !== 'index'): ?>
                                        <li class="breadcrumb-item active" aria-current="page"><?php echo get_admin_page_title(); ?></li>
                                    <?php endif; ?>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                
                <?php display_alert(''); ?>