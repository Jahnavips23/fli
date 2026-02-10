<?php
require_once 'includes/config.php';
$page_title = 'For Schools';
$page_description = 'Discover our comprehensive range of educational technology solutions designed specifically for schools and educational institutions.';

// Get products
$products = [];
try {
    $stmt = $db->prepare("SELECT * FROM products WHERE active = 1 ORDER BY display_order ASC, created_at DESC");
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching products: " . $e->getMessage());
}

// Get services
$services = [];
try {
    $stmt = $db->prepare("SELECT * FROM services WHERE active = 1 ORDER BY display_order ASC, created_at DESC");
    $stmt->execute();
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching services: " . $e->getMessage());
}

// Get success stories
$success_stories = [];
try {
    $stmt = $db->prepare("SELECT * FROM success_stories WHERE active = 1 ORDER BY display_order ASC, created_at DESC");
    $stmt->execute();
    $success_stories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching success stories: " . $e->getMessage());
}

// Process product enquiry form
$form_submitted = false;
$form_errors = [];
$form_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_enquiry'])) {
    $form_submitted = true;
    
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $school_name = isset($_POST['school_name']) ? trim($_POST['school_name']) : '';
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Validate form data
    if (empty($name)) {
        $form_errors['name'] = 'Please enter your name';
    }
    
    if (empty($email)) {
        $form_errors['email'] = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($message)) {
        $form_errors['message'] = 'Please enter your message';
    }
    
    // Verify reCAPTCHA
    $recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
    if (empty($recaptchaResponse)) {
        $form_errors['recaptcha'] = 'Please complete the reCAPTCHA verification';
    } elseif (!verifyRecaptcha($recaptchaResponse)) {
        $form_errors['recaptcha'] = 'reCAPTCHA verification failed. Please try again.';
    }
    
    // If no errors, save to database
    if (empty($form_errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO product_enquiries (product_id, name, email, phone, school_name, message)
                VALUES (:product_id, :name, :email, :phone, :school_name, :message)
            ");
            $stmt->execute([
                'product_id' => $product_id ?: null,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'school_name' => $school_name,
                'message' => $message
            ]);
            
            $form_success = true;
            
            // Clear form data after successful submission
            $name = $email = $phone = $school_name = $message = '';
            $product_id = 0;
        } catch (PDOException $e) {
            error_log("Error saving product enquiry: " . $e->getMessage());
            $form_errors['general'] = 'An error occurred while submitting your enquiry. Please try again later.';
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
                <h1 class="fw-bold">For Schools</h1>
                <p class="lead">Empowering educational institutions with innovative technology solutions</p>
            </div>
        </div>
    </div>
</section>

<!-- Introduction Section -->
<section class="section-padding">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0 school-intro-content" data-aos="fade-right">
                <h2 class="section-title">Transforming Education Through Technology</h2>
                <p>At Flione IT, we understand the unique challenges faced by educational institutions in today's rapidly evolving digital landscape. Our comprehensive suite of technology solutions is specifically designed to enhance teaching and learning experiences, streamline administrative processes, and create engaging educational environments.</p>
                <p>Whether you're looking to equip your classrooms with the latest interactive technology, implement a robust school management system, or develop a customized digital learning platform, our team of education technology specialists is here to help you achieve your goals.</p>
                <a href="#products" class="btn btn-primary">Explore Our Products</a>
                <a href="#enquiry" class="btn btn-outline-primary ms-2">Contact Us</a>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <img src="<?php echo SITE_URL; ?>/assets/images/school-technology.jpg" alt="School Technology" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Products Section -->
<section id="products" class="section-padding bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h2 class="section-title">Our Educational Products</h2>
                <p class="section-description">Discover our range of innovative products designed specifically for educational institutions</p>
            </div>
        </div>
        
        <div class="row mt-5">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $index => $product): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="card h-100 product-card">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo SITE_URL . '/' . $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>">
                            <?php else: ?>
                                <div class="card-img-top bg-light text-center py-5">
                                    <i class="fas fa-box fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                <p class="card-text"><?php echo $product['short_description']; ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-3">
                                    <?php if ($product['price'] > 0): ?>
                                        <span class="product-price">₹<?php echo number_format($product['price'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="product-price text-muted">Contact for pricing</span>
                                    <?php endif; ?>
                                    <a href="#" class="btn btn-sm btn-outline-primary view-product-details" data-bs-toggle="modal" data-bs-target="#productModal<?php echo $product['id']; ?>">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Modal -->
                    <div class="modal fade" id="productModal<?php echo $product['id']; ?>" tabindex="-1" aria-labelledby="productModalLabel<?php echo $product['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="productModalLabel<?php echo $product['id']; ?>"><?php echo $product['name']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-4 mb-md-0">
                                            <?php if (!empty($product['image'])): ?>
                                                <img src="<?php echo SITE_URL . '/' . $product['image']; ?>" class="img-fluid rounded" alt="<?php echo $product['name']; ?>">
                                            <?php else: ?>
                                                <div class="bg-light text-center py-5 rounded">
                                                    <i class="fas fa-box fa-5x text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="mt-3">
                                                <h6>Category: <?php echo $product['category']; ?></h6>
                                                <?php if ($product['price'] > 0): ?>
                                                    <h5 class="text-primary">Price: ₹<?php echo number_format($product['price'], 2); ?></h5>
                                                <?php else: ?>
                                                    <h5 class="text-primary">Contact for pricing</h5>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Description</h5>
                                            <div class="mb-4">
                                                <?php echo $product['description']; ?>
                                            </div>
                                            
                                            <?php if (!empty($product['features'])): ?>
                                                <h5>Key Features</h5>
                                                <ul class="mb-4">
                                                    <?php 
                                                    $features = json_decode($product['features'], true);
                                                    if (is_array($features)):
                                                        foreach ($features as $feature):
                                                    ?>
                                                        <li><?php echo $feature; ?></li>
                                                    <?php 
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </ul>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($product['specifications'])): ?>
                                                <h5>Specifications</h5>
                                                <table class="table table-sm">
                                                    <tbody>
                                                        <?php 
                                                        $specs = json_decode($product['specifications'], true);
                                                        if (is_array($specs)):
                                                            foreach ($specs as $key => $value):
                                                        ?>
                                                            <tr>
                                                                <th><?php echo $key; ?></th>
                                                                <td><?php echo $value; ?></td>
                                                            </tr>
                                                        <?php 
                                                            endforeach;
                                                        endif;
                                                        ?>
                                                    </tbody>
                                                </table>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <a href="#enquiry" class="btn btn-primary" onclick="$('#product_id').val(<?php echo $product['id']; ?>); $('#productModal<?php echo $product['id']; ?>').modal('hide');">Enquire Now</a>
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

<!-- Services Section -->
<section id="services" class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h2 class="section-title">Our Services for Schools</h2>
                <p class="section-description">Comprehensive technology services tailored to educational institutions</p>
            </div>
        </div>
        
        <div class="row mt-5">
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $index => $service): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="service-card h-100">
                            <div class="service-icon">
                                <i class="<?php echo $service['icon']; ?>"></i>
                            </div>
                            <h4 class="service-title"><?php echo $service['title']; ?></h4>
                            <p class="service-description"><?php echo $service['description']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <p>No services available at the moment. Please check back later or contact us for more information.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Success Stories Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h2 class="section-title">Success Stories</h2>
                <p class="section-description">See how schools have transformed their educational experience with our solutions</p>
            </div>
        </div>
        
        <div class="row mt-5">
            <?php if (!empty($success_stories)): ?>
                <?php foreach ($success_stories as $index => $story): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="card h-100">
                            <?php if (!empty($story['image'])): ?>
                                <img src="<?php echo SITE_URL . '/' . $story['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($story['organization']); ?>">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-building fa-4x"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($story['organization']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($story['summary']); ?></p>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#storyModal<?php echo $story['id']; ?>">
                                    Read Case Study
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Success Story Modal -->
                    <div class="modal fade" id="storyModal<?php echo $story['id']; ?>" tabindex="-1" aria-labelledby="storyModalLabel<?php echo $story['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="storyModalLabel<?php echo $story['id']; ?>"><?php echo htmlspecialchars($story['title']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <?php if (!empty($story['image'])): ?>
                                                <img src="<?php echo SITE_URL . '/' . $story['image']; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($story['organization']); ?>">
                                            <?php endif; ?>
                                            <h5 class="mt-3"><?php echo htmlspecialchars($story['organization']); ?></h5>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <h5>Challenge & Solution</h5>
                                            <div class="mb-4">
                                                <?php echo $story['content']; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($story['results'])): ?>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <h5>Results</h5>
                                                <div class="row">
                                                    <?php 
                                                    $results = json_decode($story['results'], true);
                                                    if (is_array($results)):
                                                        foreach ($results as $key => $value):
                                                    ?>
                                                        <div class="col-md-6 mb-3">
                                                            <div class="card h-100 bg-light">
                                                                <div class="card-body">
                                                                    <h6 class="card-title text-muted"><?php echo htmlspecialchars($key); ?></h6>
                                                                    <p class="card-text text-primary fw-bold fs-5"><?php echo htmlspecialchars($value); ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php 
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <a href="#enquiry" class="btn btn-primary" onclick="$('#storyModal<?php echo $story['id']; ?>').modal('hide');">
                                        <i class="fas fa-envelope me-1"></i> Contact Us
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default success stories if none in database -->
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                    <div class="card h-100">
                        <img src="<?php echo SITE_URL; ?>/assets/images/case-study-1.jpg" class="card-img-top" alt="Oakridge Academy">
                        <div class="card-body">
                            <h5 class="card-title">Oakridge Academy</h5>
                            <p class="card-text">Implemented a comprehensive digital learning platform that increased student engagement by 45% and improved academic performance across all grade levels.</p>
                            <a href="#" class="btn btn-outline-primary btn-sm">Read Case Study</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card h-100">
                        <img src="<?php echo SITE_URL; ?>/assets/images/case-study-2.jpg" class="card-img-top" alt="Westfield School District">
                        <div class="card-body">
                            <h5 class="card-title">Westfield School District</h5>
                            <p class="card-text">Modernized 15 schools with interactive classroom technology, resulting in a 30% increase in teacher satisfaction and improved student collaboration.</p>
                            <a href="#" class="btn btn-outline-primary btn-sm">Read Case Study</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card h-100">
                        <img src="<?php echo SITE_URL; ?>/assets/images/case-study-3.jpg" class="card-img-top" alt="Greenwood High School">
                        <div class="card-body">
                            <h5 class="card-title">Greenwood High School</h5>
                            <p class="card-text">Implemented a STEM Learning Lab that revolutionized science and technology education, leading to increased enrollment in advanced courses and improved test scores.</p>
                            <a href="#" class="btn btn-outline-primary btn-sm">Read Case Study</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Enquiry Form Section -->
<section id="enquiry" class="section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="section-title">Request Information</h2>
                <p class="section-description">Interested in our educational technology solutions? Fill out the form below and one of our education specialists will contact you.</p>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-lg-8 offset-lg-2">
                <?php if ($form_submitted && $form_success): ?>
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Thank you for your enquiry!</h4>
                        <p>We have received your message and will contact you shortly to discuss your requirements.</p>
                    </div>
                <?php else: ?>
                    <?php if (!empty($form_errors['general'])): ?>
                        <div class="alert alert-danger"><?php echo $form_errors['general']; ?></div>
                    <?php endif; ?>
                    
                    <form action="#enquiry" method="post" class="contact-form">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php echo isset($form_errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                                <?php if (isset($form_errors['name'])): ?>
                                    <div class="invalid-feedback"><?php echo $form_errors['name']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control <?php echo isset($form_errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                                <?php if (isset($form_errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo $form_errors['email']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="school_name" class="form-label">School/Institution Name</label>
                                <input type="text" class="form-control" id="school_name" name="school_name" value="<?php echo isset($school_name) ? htmlspecialchars($school_name) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product of Interest</label>
                            <select class="form-select" id="product_id" name="product_id">
                                <option value="">General Enquiry</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>" <?php echo (isset($product_id) && $product_id == $product['id']) ? 'selected' : ''; ?>>
                                        <?php echo $product['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message <span class="text-danger">*</span></label>
                            <textarea class="form-control <?php echo isset($form_errors['message']) ? 'is-invalid' : ''; ?>" id="message" name="message" rows="5" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                            <?php if (isset($form_errors['message'])): ?>
                                <div class="invalid-feedback"><?php echo $form_errors['message']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                            <?php if (isset($form_errors['recaptcha'])): ?>
                                <div class="text-danger mt-2"><?php echo $form_errors['recaptcha']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" name="submit_enquiry" class="btn btn-primary btn-lg">Submit Enquiry</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-description">Find answers to common questions about our educational technology solutions</p>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-lg-10 offset-lg-1">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                What types of educational institutions do you work with?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We work with a wide range of educational institutions, including K-12 schools, colleges, universities, and vocational training centers. Our solutions are customizable to meet the specific needs of different educational environments and age groups.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Do you offer professional development for teachers?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we provide comprehensive professional development programs to ensure educators can effectively integrate technology into their teaching practices. Our training sessions are hands-on, practical, and can be customized to address the specific needs of your teaching staff.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                How do your solutions integrate with existing school systems?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Our solutions are designed with interoperability in mind. We can integrate with most existing school management systems, learning management systems, and other educational platforms. During the implementation process, we work closely with your IT team to ensure seamless integration with your current infrastructure.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                What kind of support do you provide after implementation?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We offer ongoing technical support, regular maintenance, and updates for all our solutions. Our support packages include phone and email support, remote troubleshooting, and on-site assistance when needed. We also provide additional training sessions as your needs evolve or when new staff members join your team.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                How do you address data privacy and security concerns?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We take data privacy and security very seriously. All our solutions comply with relevant data protection regulations. We implement robust security measures, including encryption, secure authentication, and regular security audits. We also provide clear data management policies and can work with your institution to address specific privacy requirements.
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
                <h3 class="fw-bold mb-2">Ready to transform your educational institution?</h3>
                <p class="mb-0">Contact us today to discuss how we can help you achieve your technology goals.</p>
            </div>
            <div class="col-lg-3 text-lg-end">
                <a href="#enquiry" class="btn btn-light btn-lg">Get Started</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>