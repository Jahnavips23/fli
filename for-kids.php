<?php
require_once 'includes/config.php';
$page_title = 'For Kids';
$page_description = 'Discover our exciting range of technology programs designed specifically for children to learn, create, and have fun.';

// Get kids programs
$programs = [];
try {
    $stmt = $db->prepare("SELECT * FROM kids_programs WHERE active = 1 ORDER BY display_order ASC, created_at DESC");
    $stmt->execute();
    $programs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching kids programs: " . $e->getMessage());
}

// Get kids products
$products = [];
try {
    $stmt = $db->prepare("SELECT * FROM kids_products WHERE active = 1 ORDER BY display_order ASC, created_at DESC");
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching kids products: " . $e->getMessage());
}

// Get gallery images
$gallery_items = [];
try {
    $stmt = $db->prepare("
        SELECT g.*, p.title as program_title 
        FROM program_gallery g
        JOIN kids_programs p ON g.program_id = p.id
        WHERE g.active = 1
        ORDER BY g.display_order ASC, g.created_at DESC
    ");
    $stmt->execute();
    $gallery_items = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching gallery: " . $e->getMessage());
}

// Process program registration form
$form_submitted = false;
$form_errors = [];
$form_success = false;

// Process product inquiry form
$inquiry_submitted = false;
$inquiry_errors = [];
$inquiry_success = false;

// Process product inquiry form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    $inquiry_submitted = true;
    
    // Get form data
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Validate form data
    if ($product_id <= 0) {
        $inquiry_errors['product_id'] = 'Invalid product selection';
    }
    
    if (empty($name)) {
        $inquiry_errors['name'] = 'Please enter your name';
    }
    
    if (empty($email)) {
        $inquiry_errors['email'] = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $inquiry_errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($message)) {
        $inquiry_errors['message'] = 'Please enter your message';
    }
    
    // If no errors, save to database
    if (empty($inquiry_errors)) {
        try {
            // Check if product exists
            $stmt = $db->prepare("SELECT id, title FROM kids_products WHERE id = :id AND active = 1");
            $stmt->execute(['id' => $product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                $inquiry_errors['product_id'] = 'Selected product is not available';
            } else {
                // Insert inquiry
                $stmt = $db->prepare("
                    INSERT INTO kids_product_inquiries (
                        product_id, name, email, phone, message
                    )
                    VALUES (
                        :product_id, :name, :email, :phone, :message
                    )
                ");
                $stmt->execute([
                    'product_id' => $product_id,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'message' => $message
                ]);
                
                $inquiry_success = true;
                
                // Clear form data after successful submission
                $name = $email = $phone = $message = '';
            }
        } catch (PDOException $e) {
            error_log("Error saving product inquiry: " . $e->getMessage());
            $inquiry_errors['general'] = 'An error occurred while submitting your inquiry. Please try again later.';
        }
    }
}

// Process program registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_registration'])) {
    $form_submitted = true;
    
    // Get form data
    $program_id = isset($_POST['program_id']) ? (int)$_POST['program_id'] : 0;
    $child_name = isset($_POST['child_name']) ? trim($_POST['child_name']) : '';
    $child_age = isset($_POST['child_age']) ? (int)$_POST['child_age'] : 0;
    $parent_name = isset($_POST['parent_name']) ? trim($_POST['parent_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $special_requirements = isset($_POST['special_requirements']) ? trim($_POST['special_requirements']) : '';
    
    // Validate form data
    if ($program_id <= 0) {
        $form_errors['program_id'] = 'Please select a program';
    }
    
    if (empty($child_name)) {
        $form_errors['child_name'] = 'Please enter your child\'s name';
    }
    
    if ($child_age <= 0 || $child_age > 18) {
        $form_errors['child_age'] = 'Please enter a valid age (1-18)';
    }
    
    if (empty($parent_name)) {
        $form_errors['parent_name'] = 'Please enter parent/guardian name';
    }
    
    if (empty($email)) {
        $form_errors['email'] = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($phone)) {
        $form_errors['phone'] = 'Please enter your phone number';
    }
    
    // If no errors, save to database
    if (empty($form_errors)) {
        try {
            // Check if program exists and has space
            $stmt = $db->prepare("
                SELECT id, title, max_participants, current_participants 
                FROM kids_programs 
                WHERE id = :id AND active = 1
            ");
            $stmt->execute(['id' => $program_id]);
            $program = $stmt->fetch();
            
            if (!$program) {
                $form_errors['program_id'] = 'Selected program is not available';
            } elseif ($program['max_participants'] > 0 && $program['current_participants'] >= $program['max_participants']) {
                $form_errors['program_id'] = 'This program is currently full. Your registration will be added to the waitlist.';
                $waitlisted = true;
            }
            
            if (empty($form_errors) || isset($waitlisted)) {
                // Insert registration
                $stmt = $db->prepare("
                    INSERT INTO program_registrations (
                        program_id, child_name, child_age, parent_name, 
                        email, phone, address, special_requirements, status
                    )
                    VALUES (
                        :program_id, :child_name, :child_age, :parent_name, 
                        :email, :phone, :address, :special_requirements, :status
                    )
                ");
                $stmt->execute([
                    'program_id' => $program_id,
                    'child_name' => $child_name,
                    'child_age' => $child_age,
                    'parent_name' => $parent_name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'special_requirements' => $special_requirements,
                    'status' => isset($waitlisted) ? 'waitlisted' : 'new'
                ]);
                
                // Update current participants count
                if (!isset($waitlisted)) {
                    $stmt = $db->prepare("
                        UPDATE kids_programs 
                        SET current_participants = current_participants + 1 
                        WHERE id = :id
                    ");
                    $stmt->execute(['id' => $program_id]);
                }
                
                $form_success = true;
                
                // Clear form data after successful submission
                $program_id = $child_name = $parent_name = $email = $phone = $address = $special_requirements = '';
                $child_age = 0;
            }
        } catch (PDOException $e) {
            error_log("Error saving program registration: " . $e->getMessage());
            $form_errors['general'] = 'An error occurred while submitting your registration. Please try again later.';
        }
    }
}

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center">
                <h1 class="fw-bold">For Kids</h1>
                <p class="lead">Fun and educational technology programs designed specifically for young minds</p>
            </div>
        </div>
    </div>
</section>

<!-- Introduction Section -->
<section class="section-padding">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0 kids-intro-content" data-aos="fade-right">
                <h2 class="section-title">Inspiring the Next Generation of Innovators</h2>
                <p>At Flione IT, we believe that technology education should be fun, engaging, and accessible to children of all ages. Our specially designed programs introduce kids to coding, robotics, digital art, and more in a supportive and creative environment.</p>
                <p>Led by experienced instructors who are passionate about technology and education, our programs help children develop critical thinking, problem-solving skills, and digital literacy while fostering creativity and collaboration.</p>
                <div class="mt-4 d-flex gap-3">
                    <a href="#programs" class="btn btn-primary btn-rect text-nowrap">Explore Our Programs</a>
                    <a href="#products" class="btn btn-success btn-rect text-nowrap">Shop Educational Products</a>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <img src="<?php echo SITE_URL; ?>/assets/images/kids-learning.jpg" alt="Kids Learning Technology" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Programs Section -->
<section id="programs" class="section-padding bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h2 class="section-title">Our Programs for Kids</h2>
                <p class="section-description">Discover our range of exciting technology programs designed specifically for children</p>
            </div>
        </div>
        
        <div class="row mt-5">
            <?php if (!empty($programs)): ?>
                <?php foreach ($programs as $index => $program): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="card h-100 program-card">
                            <?php if (!empty($program['image'])): ?>
                                <img src="<?php echo SITE_URL . '/' . $program['image']; ?>" class="card-img-top" alt="<?php echo $program['title']; ?>">
                            <?php else: ?>
                                <div class="card-img-top bg-light text-center py-5">
                                    <i class="fas fa-child fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="program-age-badge">
                                    <?php echo $program['age_range']; ?>
                                </div>
                                <h5 class="card-title"><?php echo $program['title']; ?></h5>
                                <p class="card-text"><?php echo $program['short_description']; ?></p>
                                
                                <div class="program-details mt-3">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted d-block"><i class="fas fa-clock me-1"></i> <?php echo $program['duration']; ?></small>
                                        </div>
                                        <div class="col-6 text-end">
                                            <?php if ($program['price'] > 0): ?>
                                                <small class="text-primary fw-bold">₹<?php echo number_format($program['price'], 2); ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">Contact for pricing</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <a href="#" class="btn btn-sm btn-outline-primary view-program-details" data-bs-toggle="modal" data-bs-target="#programModal<?php echo $program['id']; ?>">View Details</a>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#registerModal" onclick="selectProgram(<?php echo $program['id']; ?>)">Register</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Program Modal -->
                    <div class="modal fade" id="programModal<?php echo $program['id']; ?>" tabindex="-1" aria-labelledby="programModalLabel<?php echo $program['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="programModalLabel<?php echo $program['id']; ?>"><?php echo $program['title']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-4 mb-md-0">
                                            <?php if (!empty($program['image'])): ?>
                                                <img src="<?php echo SITE_URL . '/' . $program['image']; ?>" class="img-fluid rounded" alt="<?php echo $program['title']; ?>">
                                            <?php else: ?>
                                                <div class="bg-light text-center py-5 rounded">
                                                    <i class="fas fa-child fa-5x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="program-details mt-3">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <p class="mb-1"><strong>Age Range:</strong></p>
                                                        <p class="text-primary"><?php echo $program['age_range']; ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <p class="mb-1"><strong>Duration:</strong></p>
                                                        <p><?php echo $program['duration']; ?></p>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($program['schedule'])): ?>
                                                    <p class="mb-1"><strong>Schedule:</strong></p>
                                                    <p><?php echo $program['schedule']; ?></p>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($program['location'])): ?>
                                                    <p class="mb-1"><strong>Location:</strong></p>
                                                    <p><?php echo $program['location']; ?></p>
                                                <?php endif; ?>
                                                
                                                <?php if ($program['price'] > 0): ?>
                                                    <p class="mb-1"><strong>Price:</strong></p>
                                                    <p class="text-primary fw-bold">₹<?php echo number_format($program['price'], 2); ?></p>
                                                <?php endif; ?>
                                                
                                                <?php if ($program['start_date'] && $program['end_date']): ?>
                                                    <p class="mb-1"><strong>Dates:</strong></p>
                                                    <p><?php echo date('F j, Y', strtotime($program['start_date'])); ?> - <?php echo date('F j, Y', strtotime($program['end_date'])); ?></p>
                                                <?php endif; ?>
                                                
                                                <?php if ($program['max_participants'] > 0): ?>
                                                    <p class="mb-1"><strong>Availability:</strong></p>
                                                    <?php 
                                                    $spots_left = $program['max_participants'] - $program['current_participants'];
                                                    if ($spots_left <= 0): 
                                                    ?>
                                                        <p class="text-danger">Program Full (Waitlist Available)</p>
                                                    <?php elseif ($spots_left <= 3): ?>
                                                        <p class="text-warning">Only <?php echo $spots_left; ?> spots left!</p>
                                                    <?php else: ?>
                                                        <p class="text-success">Available (<?php echo $spots_left; ?> spots left)</p>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Program Description</h5>
                                            <div class="mb-4">
                                                <?php echo $program['description']; ?>
                                            </div>
                                            
                                            <?php
                                            // Get gallery images for this program
                                            $program_gallery = array_filter($gallery_items, function($item) use ($program) {
                                                return $item['program_id'] == $program['id'];
                                            });
                                            
                                            if (!empty($program_gallery)):
                                            ?>
                                                <h5>Program Gallery</h5>
                                                <div class="row g-2 mb-4">
                                                    <?php foreach ($program_gallery as $item): ?>
                                                        <div class="col-6">
                                                            <a href="<?php echo SITE_URL . '/' . $item['image']; ?>" data-lightbox="program-<?php echo $program['id']; ?>" data-title="<?php echo $item['title']; ?>">
                                                                <img src="<?php echo SITE_URL . '/' . $item['image']; ?>" alt="<?php echo $item['title']; ?>" class="img-fluid rounded">
                                                            </a>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary register-from-modal" data-program-id="<?php echo $program['id']; ?>">Register Now</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <p>No programs available at the moment. Please check back later or contact us for more information.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Products Section -->
<section id="products" class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h2 class="section-title">Educational Products for Kids</h2>
                <p class="section-description">Discover our range of educational technology products designed to inspire young minds</p>
            </div>
        </div>
        
        <div class="row mt-5">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $index => $product): ?>
                    <div class="col-lg-3 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="card h-100 product-card">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo SITE_URL . '/' . $product['image']; ?>" class="card-img-top" alt="<?php echo $product['title']; ?>">
                            <?php else: ?>
                                <div class="card-img-top bg-light text-center py-5">
                                    <i class="fas fa-robot fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="product-category-badge">
                                    <?php echo $product['category']; ?>
                                </div>
                                <div class="product-age-badge">
                                    <?php echo $product['age_range']; ?>
                                </div>
                                <h5 class="card-title"><?php echo $product['title']; ?></h5>
                                <p class="card-text small"><?php echo $product['short_description']; ?></p>
                                
                                <div class="product-price mt-3">
                                    <?php if ($product['sale_price'] > 0): ?>
                                        <span class="text-decoration-line-through text-muted me-2">₹<?php echo number_format($product['price'], 2); ?></span>
                                        <span class="text-danger fw-bold">₹<?php echo number_format($product['sale_price'], 2); ?></span>
                                    <?php elseif ($product['price'] > 0): ?>
                                        <span class="fw-bold">₹<?php echo number_format($product['price'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Contact for pricing</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['stock_status'] === 'in_stock'): ?>
                                        <span class="badge bg-success float-end">In Stock</span>
                                    <?php elseif ($product['stock_status'] === 'out_of_stock'): ?>
                                        <span class="badge bg-danger float-end">Out of Stock</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning float-end">On Backorder</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-grid gap-2 mt-3">
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#productModal<?php echo $product['id']; ?>">
                                        View Details
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary inquire-btn" data-bs-toggle="modal" data-bs-target="#inquiryModal" data-product-id="<?php echo $product['id']; ?>" data-product-title="<?php echo htmlspecialchars($product['title']); ?>">
                                        Inquire Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Modal -->
                    <div class="modal fade" id="productModal<?php echo $product['id']; ?>" tabindex="-1" aria-labelledby="productModalLabel<?php echo $product['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="productModalLabel<?php echo $product['id']; ?>"><?php echo $product['title']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-5 mb-4 mb-md-0">
                                            <?php if (!empty($product['image'])): ?>
                                                <img src="<?php echo SITE_URL . '/' . $product['image']; ?>" class="img-fluid rounded" alt="<?php echo $product['title']; ?>">
                                            <?php else: ?>
                                                <div class="bg-light text-center py-5 rounded">
                                                    <i class="fas fa-robot fa-5x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="product-details mt-3">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <span class="badge bg-primary"><?php echo $product['category']; ?></span>
                                                        <span class="badge bg-info"><?php echo $product['age_range']; ?></span>
                                                    </div>
                                                    <?php if ($product['stock_status'] === 'in_stock'): ?>
                                                        <span class="badge bg-success">In Stock</span>
                                                    <?php elseif ($product['stock_status'] === 'out_of_stock'): ?>
                                                        <span class="badge bg-danger">Out of Stock</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">On Backorder</span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="product-price mb-3">
                                                    <?php if ($product['sale_price'] > 0): ?>
                                                        <span class="text-decoration-line-through text-muted me-2">₹<?php echo number_format($product['price'], 2); ?></span>
                                                        <span class="text-danger fw-bold fs-4">₹<?php echo number_format($product['sale_price'], 2); ?></span>
                                                        <span class="badge bg-danger ms-2">SALE</span>
                                                    <?php elseif ($product['price'] > 0): ?>
                                                        <span class="fw-bold fs-4">₹<?php echo number_format($product['price'], 2); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Contact for pricing</span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="d-grid gap-2">
                                                    <a href="mailto:<?php echo SITE_EMAIL; ?>?subject=Order inquiry for <?php echo urlencode($product['title']); ?>&body=I am interested in ordering the <?php echo urlencode($product['title']); ?>. Please provide more information." class="btn btn-primary">
                                                        Order Now
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <h5>Product Description</h5>
                                            <div class="mb-4">
                                                <?php echo $product['description']; ?>
                                            </div>
                                            
                                            <?php if (!empty($product['features'])): ?>
                                                <h5>Key Features</h5>
                                                <ul class="mb-4">
                                                    <?php 
                                                    $features = explode("\n", $product['features']);
                                                    foreach ($features as $feature):
                                                        $feature = trim($feature);
                                                        if (!empty($feature)):
                                                            // Remove leading dash if present
                                                            $feature = ltrim($feature, '- ');
                                                    ?>
                                                        <li><?php echo $feature; ?></li>
                                                    <?php 
                                                        endif;
                                                    endforeach; 
                                                    ?>
                                                </ul>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($product['specifications'])): ?>
                                                <h5>Specifications</h5>
                                                <table class="table table-sm table-bordered">
                                                    <tbody>
                                                        <?php 
                                                        $specifications = explode("\n", $product['specifications']);
                                                        foreach ($specifications as $spec):
                                                            $spec = trim($spec);
                                                            if (!empty($spec)):
                                                                $parts = explode(':', $spec, 2);
                                                                if (count($parts) === 2):
                                                        ?>
                                                            <tr>
                                                                <th><?php echo trim($parts[0]); ?></th>
                                                                <td><?php echo trim($parts[1]); ?></td>
                                                            </tr>
                                                        <?php 
                                                                endif;
                                                            endif;
                                                        endforeach; 
                                                        ?>
                                                    </tbody>
                                                </table>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary inquire-btn" data-bs-toggle="modal" data-product-id="<?php echo $product['id']; ?>" data-product-title="<?php echo htmlspecialchars($product['title']); ?>">
                                        Order Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <p>No products available at the moment. Please check back later or contact us for more information.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h2 class="section-title">Why Choose Our Kids Programs</h2>
                <p class="section-description">What makes our technology programs for children special</p>
            </div>
        </div>
        
        <div class="row mt-5 kids-features">
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h4>Expert Instructors</h4>
                    <p>Our programs are led by experienced educators who specialize in teaching technology to children and know how to make learning fun and engaging.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Small Class Sizes</h4>
                    <p>We maintain small class sizes to ensure each child receives personalized attention and support throughout their learning journey.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h4>Hands-On Learning</h4>
                    <p>Children learn by doing in our programs, with hands-on projects and activities that reinforce concepts and make learning memorable.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-puzzle-piece"></i>
                    </div>
                    <h4>Age-Appropriate Content</h4>
                    <p>Our curriculum is carefully designed to match the developmental needs and interests of different age groups, ensuring an optimal learning experience.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h4>Project Showcase</h4>
                    <p>Children have the opportunity to showcase their projects and achievements, building confidence and pride in their work.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Safe Environment</h4>
                    <p>We provide a safe, supportive, and inclusive learning environment where children feel comfortable exploring, experimenting, and expressing themselves.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h2 class="section-title">Program Gallery</h2>
                <p class="section-description">See our kids in action during our various technology programs</p>
            </div>
        </div>
        
        <div class="row mt-5">
            <?php if (!empty($gallery_items)): ?>
                <div class="gallery-container">
                    <?php foreach ($gallery_items as $index => $item): ?>
                        <div class="gallery-item" data-aos="fade-up" data-aos-delay="<?php echo ($index % 6) * 100; ?>">
                            <a href="<?php echo SITE_URL . '/' . $item['image']; ?>" data-lightbox="program-gallery" data-title="<?php echo $item['title']; ?> - <?php echo $item['program_title']; ?>">
                                <img src="<?php echo SITE_URL . '/' . $item['image']; ?>" alt="<?php echo $item['title']; ?>" class="img-fluid">
                                <div class="gallery-overlay">
                                    <div class="gallery-info">
                                        <h5><?php echo $item['title']; ?></h5>
                                        <p><?php echo $item['program_title']; ?></p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <p>No gallery images available at the moment. Please check back later.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Product Inquiry Modal -->
<div class="modal fade" id="inquiryModal" tabindex="-1" aria-labelledby="inquiryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="inquiryModalLabel">Product Inquiry</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($inquiry_submitted && $inquiry_success): ?>
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Inquiry Submitted!</h4>
                        <p>Thank you for your interest in our product. We have received your inquiry and will contact you shortly.</p>
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                <?php else: ?>
                    <?php if (!empty($inquiry_errors['general'])): ?>
                        <div class="alert alert-danger"><?php echo $inquiry_errors['general']; ?></div>
                    <?php endif; ?>
                    
                    <form action="<?php echo SITE_URL; ?>/for-kids.php" method="post" class="inquiry-form">
                        <input type="hidden" name="product_id" id="inquiry_product_id" value="">
                        <div class="mb-3">
                            <div id="product_info_container" class="alert alert-info mb-3">
                                <div id="inquiry_product_info"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo isset($inquiry_errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                            <?php if (isset($inquiry_errors['name'])): ?>
                                <div class="invalid-feedback"><?php echo $inquiry_errors['name']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?php echo isset($inquiry_errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                            <?php if (isset($inquiry_errors['email'])): ?>
                                <div class="invalid-feedback"><?php echo $inquiry_errors['email']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                            <small class="form-text text-muted">Optional, but recommended for faster response</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message <span class="text-danger">*</span></label>
                            <textarea class="form-control <?php echo isset($inquiry_errors['message']) ? 'is-invalid' : ''; ?>" id="message" name="message" rows="4" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                            <?php if (isset($inquiry_errors['message'])): ?>
                                <div class="invalid-feedback"><?php echo $inquiry_errors['message']; ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">Please include any specific questions or requirements you have about the product</small>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="submit_inquiry" class="btn btn-success">Submit Inquiry</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Registration Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="registerModalLabel">Register for a Program</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($form_submitted && $form_success): ?>
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Registration Successful!</h4>
                        <p>Thank you for registering for our program. We have received your information and will contact you shortly with further details.</p>
                        <?php if (isset($waitlisted)): ?>
                            <hr>
                            <p class="mb-0">Note: The program you selected is currently full. Your registration has been added to our waitlist, and we will contact you if a spot becomes available.</p>
                        <?php endif; ?>
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                    </div>
                <?php else: ?>
                    <?php if (!empty($form_errors['general'])): ?>
                        <div class="alert alert-danger"><?php echo $form_errors['general']; ?></div>
                    <?php endif; ?>
                    
                    <form action="<?php echo SITE_URL; ?>/for-kids.php" method="post" class="registration-form">
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Program Selection</h5>
                            <div class="mb-3">
                                <label for="program_id" class="form-label">Select Program <span class="text-danger">*</span></label>
                                <select class="form-select <?php echo isset($form_errors['program_id']) ? 'is-invalid' : ''; ?>" id="program_id" name="program_id" required>
                                    <option value="">Choose a Program</option>
                                    <?php foreach ($programs as $program): ?>
                                        <option value="<?php echo $program['id']; ?>" <?php echo (isset($program_id) && $program_id == $program['id']) ? 'selected' : ''; ?>>
                                            <?php echo $program['title']; ?> (<?php echo $program['age_range']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($form_errors['program_id'])): ?>
                                    <div class="invalid-feedback"><?php echo $form_errors['program_id']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div id="program_details" class="mt-3" style="display: none;">
                                <div class="alert alert-info">
                                    <div id="program_info"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Child Information</h5>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="child_name" class="form-label">Child's Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php echo isset($form_errors['child_name']) ? 'is-invalid' : ''; ?>" id="child_name" name="child_name" value="<?php echo isset($child_name) ? htmlspecialchars($child_name) : ''; ?>" required>
                                    <?php if (isset($form_errors['child_name'])): ?>
                                        <div class="invalid-feedback"><?php echo $form_errors['child_name']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="child_age" class="form-label">Child's Age <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control <?php echo isset($form_errors['child_age']) ? 'is-invalid' : ''; ?>" id="child_age" name="child_age" min="1" max="18" value="<?php echo isset($child_age) && $child_age > 0 ? $child_age : ''; ?>" required>
                                    <?php if (isset($form_errors['child_age'])): ?>
                                        <div class="invalid-feedback"><?php echo $form_errors['child_age']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Parent/Guardian Information</h5>
                            <div class="mb-3">
                                <label for="parent_name" class="form-label">Parent/Guardian Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php echo isset($form_errors['parent_name']) ? 'is-invalid' : ''; ?>" id="parent_name" name="parent_name" value="<?php echo isset($parent_name) ? htmlspecialchars($parent_name) : ''; ?>" required>
                                <?php if (isset($form_errors['parent_name'])): ?>
                                    <div class="invalid-feedback"><?php echo $form_errors['parent_name']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control <?php echo isset($form_errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                    <?php if (isset($form_errors['email'])): ?>
                                        <div class="invalid-feedback"><?php echo $form_errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control <?php echo isset($form_errors['phone']) ? 'is-invalid' : ''; ?>" id="phone" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                                    <?php if (isset($form_errors['phone'])): ?>
                                        <div class="invalid-feedback"><?php echo $form_errors['phone']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($address) ? htmlspecialchars($address) : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Additional Information</h5>
                            <div class="mb-3">
                                <label for="special_requirements" class="form-label">Special Requirements or Notes</label>
                                <textarea class="form-control" id="special_requirements" name="special_requirements" rows="4" placeholder="Please let us know about any special requirements, allergies, or other information that would help us provide the best experience for your child."><?php echo isset($special_requirements) ? htmlspecialchars($special_requirements) : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="submit_registration" class="btn btn-primary">Submit Registration</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Get testimonials
$testimonials = get_testimonials(3);
?>

<!-- Testimonials Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h2 class="section-title">What Parents & Kids Say</h2>
                <p class="section-description">Hear from families who have participated in our programs</p>
            </div>
        </div>
        
        <div class="row mt-5">
            <?php if (!empty($testimonials)): ?>
                <?php foreach ($testimonials as $index => $testimonial): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="testimonial-card h-100 bg-white p-4 rounded shadow-sm">
                            <div class="testimonial-rating mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo ($i <= $testimonial['rating']) ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="testimonial-text mb-4">
                                <p class="fst-italic">"<?php echo $testimonial['content']; ?>"</p>
                            </div>
                            <div class="testimonial-author d-flex align-items-center">
                                <?php if (!empty($testimonial['image'])): ?>
                                <div class="author-image me-3">
                                    <img src="<?php echo SITE_URL . '/' . $testimonial['image']; ?>" alt="<?php echo $testimonial['name']; ?>" class="rounded-circle" width="60" height="60">
                                </div>
                                <?php endif; ?>
                                <div class="author-info">
                                    <h5 class="mb-0"><?php echo $testimonial['name']; ?></h5>
                                    <p class="mb-0 text-muted"><?php echo $testimonial['position']; ?>, <?php echo $testimonial['organization']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>No testimonials available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-description">Find answers to common questions about our kids programs</p>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-lg-10 offset-lg-1">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                What age groups do your programs cater to?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Our programs are designed for children between the ages of 7 and 14, with specific age ranges for each program. We carefully tailor the content and teaching methods to suit the developmental needs and abilities of each age group, ensuring that children are appropriately challenged and engaged.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Does my child need prior experience with technology?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                No prior experience is necessary! Our programs are designed to accommodate beginners and more experienced children alike. We start with the fundamentals and progress at a pace that works for each child. Our instructors are skilled at meeting children where they are and helping them advance their skills regardless of their starting point.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                What should my child bring to the program?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                All necessary equipment and materials are provided as part of the program fee. Your child only needs to bring their enthusiasm and creativity! We recommend bringing a water bottle and a snack for longer sessions. For some programs, children may want to bring a USB drive to save their projects, although we also provide cloud storage options.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                What is your cancellation and refund policy?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We offer full refunds for cancellations made at least 14 days before the program start date. Cancellations made 7-13 days before the start date receive a 50% refund. Unfortunately, we cannot offer refunds for cancellations less than 7 days before the program begins. However, we do allow transfers to another program or session if space is available.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                Do you offer any discounts or financial assistance?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we offer sibling discounts (10% off for each additional child) and early bird registration discounts for programs booked at least one month in advance. We also have a limited number of scholarships available for families who need financial assistance. Please contact us directly to learn more about our scholarship program and eligibility requirements.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-9 mb-4 mb-lg-0">
                <h3 class="fw-bold mb-2">Ready to inspire your child's interest in technology?</h3>
                <p class="mb-0">Register today to secure their spot in one of our exciting programs!</p>
            </div>
            <div class="col-lg-3 text-lg-end">
                <button type="button" class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">Register Now</button>
            </div>
        </div>
    </div>
</section>

<script>
// Function to select a program in the registration form
function selectProgram(programId) {
    const programSelect = document.getElementById('program_id');
    if (programSelect) {
        programSelect.value = programId;
        programSelect.dispatchEvent(new Event('change'));
    }
}

// Function to set product in the inquiry form
function selectProduct(productId, productTitle) {
    const productIdInput = document.getElementById('inquiry_product_id');
    const productInfoContainer = document.getElementById('inquiry_product_info');
    
    if (productIdInput && productInfoContainer) {
        productIdInput.value = productId;
        productInfoContainer.innerHTML = `<strong>Product:</strong> ${productTitle}`;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Program selection change handler
    const programSelect = document.getElementById('program_id');
    const programDetails = document.getElementById('program_details');
    const programInfo = document.getElementById('program_info');
    
    // Program data
    const programs = <?php echo json_encode($programs); ?>;
    
    if (programSelect) {
        programSelect.addEventListener('change', function() {
            const selectedProgramId = parseInt(this.value);
            
            if (selectedProgramId) {
                // Find selected program
                const program = programs.find(p => p.id === selectedProgramId);
                
                if (program) {
                    // Display program details
                    let infoHtml = `<strong>${program.title}</strong><br>`;
                    infoHtml += `Age Range: ${program.age_range}<br>`;
                    infoHtml += `Duration: ${program.duration}<br>`;
                    
                    if (program.schedule) {
                        infoHtml += `Schedule: ${program.schedule}<br>`;
                    }
                    
                    if (program.location) {
                        infoHtml += `Location: ${program.location}<br>`;
                    }
                    
                    if (program.price > 0) {
                        infoHtml += `Price: ₹${parseFloat(program.price).toFixed(2)}<br>`;
                    }
                    
                    if (program.max_participants > 0) {
                        const spotsLeft = program.max_participants - program.current_participants;
                        if (spotsLeft <= 0) {
                            infoHtml += `<span class="text-danger">Program Full (Waitlist Available)</span>`;
                        } else if (spotsLeft <= 3) {
                            infoHtml += `<span class="text-warning">Only ${spotsLeft} spots left!</span>`;
                        } else {
                            infoHtml += `<span class="text-success">Available (${spotsLeft} spots left)</span>`;
                        }
                    }
                    
                    programInfo.innerHTML = infoHtml;
                    programDetails.style.display = 'block';
                } else {
                    programDetails.style.display = 'none';
                }
            } else {
                programDetails.style.display = 'none';
            }
        });
        
        // Trigger change event if a program is already selected
        if (programSelect.value) {
            programSelect.dispatchEvent(new Event('change'));
        }
    }
    
    // Fix for Register buttons in program modals
    const registerButtons = document.querySelectorAll('.register-from-modal');
    registerButtons.forEach(button => {
        button.addEventListener('click', function() {
            const programId = this.getAttribute('data-program-id');
            if (programId) {
                selectProgram(programId);
                
                // Close the current modal
                const modal = this.closest('.modal');
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
                
                // Show the registration modal
                setTimeout(() => {
                    const registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
                    registerModal.show();
                }, 500);
            }
        });
    });
    
    // Show registration modal if form has errors
    <?php if ($form_submitted && !$form_success && !empty($form_errors)): ?>
    var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
    registerModal.show();
    <?php endif; ?>
    
    // Show success message in modal if registration was successful
    <?php if ($form_submitted && $form_success): ?>
    var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
    registerModal.show();
    <?php endif; ?>
    
    // Handle product inquiry buttons
    const inquireButtons = document.querySelectorAll('.inquire-btn');
    inquireButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const productTitle = this.getAttribute('data-product-title');
            
            if (productId && productTitle) {
                selectProduct(productId, productTitle);
                
                // If inside a product modal, close it first
                const productModal = this.closest('.modal');
                if (productModal) {
                    const modalInstance = bootstrap.Modal.getInstance(productModal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    
                    // Show the inquiry modal after a short delay
                    setTimeout(() => {
                        const inquiryModal = new bootstrap.Modal(document.getElementById('inquiryModal'));
                        inquiryModal.show();
                    }, 500);
                }
            }
        });
    });
    
    // Show inquiry modal if form has errors
    <?php if ($inquiry_submitted && !$inquiry_success && !empty($inquiry_errors)): ?>
    var inquiryModal = new bootstrap.Modal(document.getElementById('inquiryModal'));
    inquiryModal.show();
    <?php endif; ?>
    
    // Show success message in inquiry modal if submission was successful
    <?php if ($inquiry_submitted && $inquiry_success): ?>
    var inquiryModal = new bootstrap.Modal(document.getElementById('inquiryModal'));
    inquiryModal.show();
    <?php endif; ?>
});
</script>

<?php include 'includes/footer.php'; ?>