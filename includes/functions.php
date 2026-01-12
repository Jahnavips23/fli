<?php
require_once 'config.php';

/**
 * Get all active carousel slides ordered by display_order
 * 
 * @return array Array of carousel slides
 */
function get_carousel_slides() {
    global $db;
    try {
        $stmt = $db->prepare("SELECT * FROM carousel_slides WHERE active = 1 ORDER BY display_order ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching carousel slides: " . $e->getMessage());
        return [];
    }
}

/**
 * Get recent blog posts
 * 
 * @param int $limit Number of posts to retrieve
 * @return array Array of blog posts
 */
function get_recent_blog_posts($limit = 3) {
    global $db;
    try {
        $stmt = $db->prepare("
            SELECT p.*, u.username as author_name, c.name as category_name 
            FROM blog_posts p
            LEFT JOIN users u ON p.author_id = u.id
            LEFT JOIN blog_categories c ON p.category_id = c.id
            WHERE p.status = 'published'
            ORDER BY p.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching recent blog posts: " . $e->getMessage());
        return [];
    }
}

/**
 * Subscribe to newsletter
 * 
 * @param string $email Subscriber email
 * @param string $name Subscriber name
 * @param string $type Subscriber type (parent, school_staff, other)
 * @return bool|string True on success, error message on failure
 */
function subscribe_to_newsletter($email, $name, $type) {
    global $db;
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email address";
    }
    
    if (!in_array($type, ['parent', 'school_staff', 'other'])) {
        return "Invalid subscriber type";
    }
    
    try {
        $stmt = $db->prepare("
            INSERT INTO newsletter_subscribers (email, name, subscriber_type)
            VALUES (:email, :name, :type)
        ");
        $stmt->execute([
            'email' => $email,
            'name' => $name,
            'type' => $type
        ]);
        return true;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            return "This email is already subscribed";
        }
        error_log("Error subscribing to newsletter: " . $e->getMessage());
        return "An error occurred. Please try again later.";
    }
}

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Format string (default: 'F j, Y')
 * @return string Formatted date
 */
function format_date($date, $format = 'F j, Y') {
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Create excerpt from content
 * 
 * @param string $content Content to create excerpt from
 * @param int $length Maximum length of excerpt
 * @return string Excerpt
 */
function create_excerpt($content, $length = 150) {
    $content = strip_tags($content);
    if (strlen($content) <= $length) {
        return $content;
    }
    
    $excerpt = substr($content, 0, $length);
    $lastSpace = strrpos($excerpt, ' ');
    
    if ($lastSpace !== false) {
        $excerpt = substr($excerpt, 0, $lastSpace);
    }
    
    return $excerpt . '...';
}

/**
 * Get page title based on current page
 * 
 * @return string Page title
 */
function get_page_title() {
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    
    switch ($current_page) {
        case 'index':
            return 'Home';
        case 'about-us':
            return 'About Us';
        case 'for-school':
            return 'For School';
        case 'for-kids':
            return 'For Kids';
        case 'blog':
            return 'Blog';
        case 'downloads':
            return 'Downloads';
        case 'track':
            return 'Track Your Project or Ticket';
        case 'track-project':
            return 'Track Your Project';
        case 'track-ticket':
            return 'Track Your Support Ticket';
        default:
            return ucfirst(str_replace('-', ' ', $current_page));
    }
}

/**
 * Check if current page is active
 * 
 * @param string $page Page to check
 * @return bool True if current page matches
 */
function is_active_page($page) {
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    
    // Special case for blog post pages
    if ($page === 'blog' && $current_page === 'blog-post') {
        return true;
    }
    
    // Special case for about page
    if ($page === 'about' && ($current_page === 'about' || $current_page === 'about-us')) {
        return true;
    }
    
    // Special case for careers pages
    if ($page === 'careers' && ($current_page === 'careers' || $current_page === 'job-details')) {
        return true;
    }
    
    // Special case for tracking pages
    if ($page === 'track' && ($current_page === 'track' || $current_page === 'track-project' || $current_page === 'track-ticket')) {
        return true;
    }
    
    return $current_page === $page;
}

/**
 * Get testimonials from database
 * 
 * @param int $limit Number of testimonials to retrieve (0 for all)
 * @return array Array of testimonials
 */
function get_testimonials($limit = 0) {
    global $db;
    $testimonials = [];
    
    try {
        $query = "SELECT * FROM testimonials WHERE active = 1 ORDER BY display_order ASC";
        if ($limit > 0) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $db->prepare($query);
        if ($limit > 0) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        $testimonials = $stmt->fetchAll();
        
        // If no testimonials found in database, use default ones
        if (empty($testimonials)) {
            $testimonials = [
                [
                    'id' => 1,
                    'name' => 'Sarah Johnson',
                    'position' => 'Principal',
                    'organization' => 'Oakridge Academy',
                    'content' => 'FLIONE has transformed our school\'s technology infrastructure. Our students are more engaged, and our teachers have the tools they need to deliver exceptional education.',
                    'rating' => 5,
                    'image' => 'assets/images/testimonials/testimonial-1.jpg',
                    'active' => 1,
                    'display_order' => 1
                ],
                [
                    'id' => 2,
                    'name' => 'Michael Chen',
                    'position' => 'IT Director',
                    'organization' => 'Westfield Schools',
                    'content' => 'The implementation process was seamless, and the ongoing support has been exceptional. FLIONE truly understands the unique challenges schools face with technology integration.',
                    'rating' => 5,
                    'image' => 'assets/images/testimonials/testimonial-2.jpg',
                    'active' => 1,
                    'display_order' => 2
                ],
                [
                    'id' => 3,
                    'name' => 'Emily Rodriguez',
                    'position' => 'Science Teacher',
                    'organization' => 'Greenwood High',
                    'content' => 'As a teacher, I appreciate how FLIONE\'s solutions are designed with the classroom in mind. The technology enhances my teaching without getting in the way.',
                    'rating' => 5,
                    'image' => 'assets/images/testimonials/testimonial-3.jpg',
                    'active' => 1,
                    'display_order' => 3
                ]
            ];
        }
    } catch (PDOException $e) {
        // Log error and use default testimonials
        error_log("Error fetching testimonials: " . $e->getMessage());
        $testimonials = [
            [
                'id' => 1,
                'name' => 'Sarah Johnson',
                'position' => 'Principal',
                'organization' => 'Oakridge Academy',
                'content' => 'FLIONE has transformed our school\'s technology infrastructure. Our students are more engaged, and our teachers have the tools they need to deliver exceptional education.',
                'rating' => 5,
                'image' => 'assets/images/testimonials/testimonial-1.jpg',
                'active' => 1,
                'display_order' => 1
            ],
            [
                'id' => 2,
                'name' => 'Michael Chen',
                'position' => 'IT Director',
                'organization' => 'Westfield Schools',
                'content' => 'The implementation process was seamless, and the ongoing support has been exceptional. FLIONE truly understands the unique challenges schools face with technology integration.',
                'rating' => 5,
                'image' => 'assets/images/testimonials/testimonial-2.jpg',
                'active' => 1,
                'display_order' => 2
            ],
            [
                'id' => 3,
                'name' => 'Emily Rodriguez',
                'position' => 'Science Teacher',
                'organization' => 'Greenwood High',
                'content' => 'As a teacher, I appreciate how FLIONE\'s solutions are designed with the classroom in mind. The technology enhances my teaching without getting in the way.',
                'rating' => 5,
                'image' => 'assets/images/testimonials/testimonial-3.jpg',
                'active' => 1,
                'display_order' => 3
            ]
        ];
    }
    
    return $testimonials;
}
?>