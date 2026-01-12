<?php
require_once 'includes/config.php';
$page_title = "Downloads";

// Handle error messages
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'file_not_found':
            $error_message = 'The requested file could not be found. Please contact support.';
            break;
        case 'server_error':
            $error_message = 'An error occurred while processing your download request. Please try again later.';
            break;
        default:
            $error_message = 'An unknown error occurred. Please try again later.';
    }
}

// Get all active downloads
$downloads = [];
try {
    $stmt = $db->prepare("
        SELECT * FROM downloads 
        WHERE active = 1 
        ORDER BY category, title
    ");
    $stmt->execute();
    $downloads = $stmt->fetchAll();
} catch (PDOException $e) {
    // Handle error silently
}

// Group downloads by category
$downloads_by_category = [];
foreach ($downloads as $download) {
    $category = $download['category'];
    if (!isset($downloads_by_category[$category])) {
        $downloads_by_category[$category] = [];
    }
    $downloads_by_category[$category][] = $download;
}

// Get platform categories
$platforms = [
    'Windows' => [
        'icon' => 'fab fa-windows',
        'color' => 'primary'
    ],
    'macOS' => [
        'icon' => 'fab fa-apple',
        'color' => 'dark'
    ],
    'Android' => [
        'icon' => 'fab fa-android',
        'color' => 'success'
    ],
    'iOS' => [
        'icon' => 'fab fa-app-store-ios',
        'color' => 'info'
    ],
    'Linux' => [
        'icon' => 'fab fa-linux',
        'color' => 'warning'
    ],
    'Documentation' => [
        'icon' => 'fas fa-file-pdf',
        'color' => 'danger'
    ],
    'Other' => [
        'icon' => 'fas fa-download',
        'color' => 'secondary'
    ]
];

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="display-4">Downloads</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Downloads</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Downloads Section -->
<section class="downloads-section py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title">Download Our Applications</h2>
                <div class="title-border mx-auto"></div>
                <p class="lead">Access our educational software and resources for schools and students. Our applications are designed to enhance learning experiences and make education more interactive and engaging.</p>
                
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger mt-4">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Platform Tabs -->
        <div class="row mb-4">
            <div class="col-12">
                <ul class="nav nav-pills nav-justified mb-4" id="downloadTabs" role="tablist">
                    <?php 
                    $first_tab = true;
                    foreach ($platforms as $platform => $info): 
                        if (isset($downloads_by_category[$platform]) && count($downloads_by_category[$platform]) > 0):
                    ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $first_tab ? 'active' : ''; ?>" 
                                id="<?php echo strtolower(str_replace(' ', '-', $platform)); ?>-tab" 
                                data-bs-toggle="pill" 
                                data-bs-target="#<?php echo strtolower(str_replace(' ', '-', $platform)); ?>" 
                                type="button" 
                                role="tab" 
                                aria-controls="<?php echo strtolower(str_replace(' ', '-', $platform)); ?>" 
                                aria-selected="<?php echo $first_tab ? 'true' : 'false'; ?>">
                            <i class="<?php echo $info['icon']; ?> me-2"></i> <?php echo $platform; ?>
                        </button>
                    </li>
                    <?php 
                        $first_tab = false;
                        endif; 
                    endforeach; 
                    ?>
                </ul>
                
                <div class="tab-content" id="downloadTabsContent">
                    <?php 
                    $first_tab = true;
                    foreach ($platforms as $platform => $info): 
                        if (isset($downloads_by_category[$platform]) && count($downloads_by_category[$platform]) > 0):
                    ?>
                    <div class="tab-pane fade <?php echo $first_tab ? 'show active' : ''; ?>" 
                         id="<?php echo strtolower(str_replace(' ', '-', $platform)); ?>" 
                         role="tabpanel" 
                         aria-labelledby="<?php echo strtolower(str_replace(' ', '-', $platform)); ?>-tab">
                        
                        <div class="row">
                            <?php foreach ($downloads_by_category[$platform] as $download): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 download-card">
                                    <div class="card-header bg-<?php echo $info['color']; ?> text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="<?php echo $info['icon']; ?> me-2"></i> <?php echo htmlspecialchars($download['title']); ?>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text"><?php echo htmlspecialchars($download['description']); ?></p>
                                        <div class="download-meta">
                                            <p class="mb-1"><strong>Size:</strong> <?php echo format_file_size($download['file_size']); ?></p>
                                            <p class="mb-1"><strong>Added:</strong> <?php echo date('M d, Y', strtotime($download['created_at'])); ?></p>
                                            <?php if (!empty($download['version'])): ?>
                                            <p class="mb-1"><strong>Version:</strong> <?php echo htmlspecialchars($download['version']); ?></p>
                                            <?php endif; ?>
                                            <p class="mb-1"><strong>Downloads:</strong> <?php echo number_format($download['download_count']); ?></p>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-light">
                                        <a href="<?php echo SITE_URL . '/downloads.php?id=' . $download['id']; ?>" class="btn btn-<?php echo $info['color']; ?> w-100">
                                            <i class="fas fa-download me-2"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php 
                        $first_tab = false;
                        endif; 
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>
        
        <!-- No Downloads Message -->
        <?php if (empty($downloads)): ?>
        <div class="row">
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    <h4 class="alert-heading">No Downloads Available</h4>
                    <p>We're currently updating our download section. Please check back soon for new resources and applications.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Download Instructions -->
<section class="download-instructions py-5 bg-light">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title">Installation Instructions</h2>
                <div class="title-border mx-auto"></div>
                <p class="lead">Follow these simple steps to install our applications on your device.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="instruction-icon mb-3">
                            <i class="fas fa-download fa-3x text-primary"></i>
                        </div>
                        <h4>Step 1: Download</h4>
                        <p>Click the download button for your platform and save the file to your computer or device.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="instruction-icon mb-3">
                            <i class="fas fa-file-archive fa-3x text-primary"></i>
                        </div>
                        <h4>Step 2: Extract/Install</h4>
                        <p>Open the downloaded file and follow the installation instructions. For mobile apps, install directly from the app store.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="instruction-icon mb-3">
                            <i class="fas fa-laptop fa-3x text-primary"></i>
                        </div>
                        <h4>Step 3: Launch</h4>
                        <p>Once installed, open the application and start exploring our educational resources and tools.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- System Requirements -->
<section class="system-requirements py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title">System Requirements</h2>
                <div class="title-border mx-auto"></div>
                <p class="lead">Make sure your device meets these minimum requirements for optimal performance.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fab fa-windows me-2"></i> Windows</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>OS:</strong> Windows 10 or later</li>
                            <li class="list-group-item"><strong>Processor:</strong> 1.6 GHz or faster</li>
                            <li class="list-group-item"><strong>Memory:</strong> 4 GB RAM</li>
                            <li class="list-group-item"><strong>Storage:</strong> 500 MB available space</li>
                            <li class="list-group-item"><strong>Graphics:</strong> DirectX 9 or later with WDDM 1.0 driver</li>
                            <li class="list-group-item"><strong>Display:</strong> 1280 x 720 screen resolution</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0"><i class="fab fa-apple me-2"></i> macOS</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>OS:</strong> macOS 10.14 Mojave or later</li>
                            <li class="list-group-item"><strong>Processor:</strong> Intel Core i5 or later</li>
                            <li class="list-group-item"><strong>Memory:</strong> 4 GB RAM</li>
                            <li class="list-group-item"><strong>Storage:</strong> 500 MB available space</li>
                            <li class="list-group-item"><strong>Graphics:</strong> Intel HD Graphics 4000 or later</li>
                            <li class="list-group-item"><strong>Display:</strong> 1280 x 720 screen resolution</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fab fa-android me-2"></i> Android</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>OS:</strong> Android 8.0 or later</li>
                            <li class="list-group-item"><strong>Processor:</strong> Quad-core 1.2 GHz or faster</li>
                            <li class="list-group-item"><strong>Memory:</strong> 2 GB RAM</li>
                            <li class="list-group-item"><strong>Storage:</strong> 100 MB available space</li>
                            <li class="list-group-item"><strong>Display:</strong> 720 x 1280 screen resolution</li>
                            <li class="list-group-item"><strong>Internet:</strong> Required for some features</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fab fa-app-store-ios me-2"></i> iOS</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>OS:</strong> iOS 13.0 or later</li>
                            <li class="list-group-item"><strong>Device:</strong> iPhone 6s or later, iPad (5th generation) or later</li>
                            <li class="list-group-item"><strong>Memory:</strong> 2 GB RAM</li>
                            <li class="list-group-item"><strong>Storage:</strong> 100 MB available space</li>
                            <li class="list-group-item"><strong>Display:</strong> 1334 x 750 screen resolution</li>
                            <li class="list-group-item"><strong>Internet:</strong> Required for some features</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Support Section -->
<section class="support-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="section-title">Need Help?</h2>
                <div class="title-border mx-auto"></div>
                <p class="lead mb-4">If you're having trouble with downloads or installation, our support team is here to help.</p>
                <a href="<?php echo SITE_URL; ?>/about.php#contact" class="btn btn-primary btn-lg">Contact Support</a>
            </div>
        </div>
    </div>
</section>

<?php
// Helper function to format file size
function format_file_size($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}

include 'includes/footer.php';
?>