-- Create database if not exists
CREATE DATABASE IF NOT EXISTS flioneit;
USE flioneit;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    remember_token VARCHAR(100) DEFAULT NULL,
    token_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blog categories table
CREATE TABLE IF NOT EXISTS blog_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blog posts table
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    author_id INT NOT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL
);

-- Carousel slides table
CREATE TABLE IF NOT EXISTS carousel_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    button_text VARCHAR(50),
    button_link VARCHAR(255),
    active TINYINT(1) NOT NULL DEFAULT 1,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Downloads table
CREATE TABLE IF NOT EXISTS downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL DEFAULT 0,
    category VARCHAR(100),
    for_schools TINYINT(1) NOT NULL DEFAULT 0,
    for_kids TINYINT(1) NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    download_count INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Newsletter subscribers table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100),
    subscriber_type ENUM('parent', 'school_staff', 'other') NOT NULL DEFAULT 'other',
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100),
    organization VARCHAR(100),
    content TEXT NOT NULL,
    rating INT NOT NULL DEFAULT 5,
    image VARCHAR(255),
    active TINYINT(1) NOT NULL DEFAULT 1,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    contact_type VARCHAR(50) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(50) NOT NULL,
    image VARCHAR(255),
    display_order INT NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Counters table
CREATE TABLE IF NOT EXISTS counters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    value INT NOT NULL,
    icon VARCHAR(50) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    short_description VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    specifications TEXT,
    features TEXT,
    price DECIMAL(10,2),
    image VARCHAR(255),
    category VARCHAR(50) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Product Enquiries table
CREATE TABLE IF NOT EXISTS product_enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    school_name VARCHAR(100),
    message TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Kids Programs table
CREATE TABLE IF NOT EXISTS kids_programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    short_description VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    age_range VARCHAR(50) NOT NULL,
    duration VARCHAR(50) NOT NULL,
    schedule VARCHAR(100),
    price DECIMAL(10,2),
    image VARCHAR(255),
    start_date DATE,
    end_date DATE,
    max_participants INT,
    current_participants INT DEFAULT 0,
    location VARCHAR(100),
    is_online TINYINT(1) DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Program Registrations table
CREATE TABLE IF NOT EXISTS program_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    child_name VARCHAR(100) NOT NULL,
    child_age INT NOT NULL,
    parent_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT,
    special_requirements TEXT,
    payment_status VARCHAR(20) DEFAULT 'pending',
    status VARCHAR(20) DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES kids_programs(id) ON DELETE CASCADE
);

-- Program Gallery table
CREATE TABLE IF NOT EXISTS program_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES kids_programs(id) ON DELETE SET NULL
);

-- Kids Products table (robotics kits, coding games, etc.)
CREATE TABLE IF NOT EXISTS kids_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    short_description VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    age_range VARCHAR(50) NOT NULL,
    price DECIMAL(10,2),
    sale_price DECIMAL(10,2),
    image VARCHAR(255),
    category VARCHAR(50) NOT NULL,
    features TEXT,
    specifications TEXT,
    stock_status VARCHAR(20) DEFAULT 'in_stock',
    display_order INT NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Kids Product Inquiries table
CREATE TABLE IF NOT EXISTS kids_product_inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    message TEXT,
    status ENUM('new', 'contacted', 'completed', 'cancelled') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES kids_products(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'admin@flioneit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id = id;

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_title', 'FLIONE - Future-ready Learning Infrastructure Optimized for Next-gen Education'),
('site_description', 'FLIONE designs smart, scalable, and child-centric educational technologies that empower institutions to deliver inspired, future-ready education.'),
('contact_email', 'reach@flioneit.com'),
('contact_phone', '+91 8296817008'),
('footer_text', '© 2023 FLIONE. All rights reserved.'),
('facebook_url', 'https://facebook.com/flioneit'),
('twitter_url', 'https://twitter.com/flioneit'),
('linkedin_url', 'https://linkedin.com/company/flioneit'),
('instagram_url', 'https://instagram.com/flioneit'),
('meta_keywords', 'FLIONE, educational technology, school infrastructure, learning ecosystem, edtech')
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);

-- Insert sample blog categories
INSERT INTO blog_categories (name, slug, description) VALUES
('Educational Technology', 'educational-technology', 'Articles about the latest educational technology trends and innovations'),
('School Infrastructure', 'school-infrastructure', 'Information about optimizing school infrastructure for modern learning'),
('Teaching Resources', 'teaching-resources', 'Helpful resources and tips for teachers and educators'),
('Student Engagement', 'student-engagement', 'Strategies and tools to improve student engagement and participation')
ON DUPLICATE KEY UPDATE id = id;

-- Insert sample testimonials
INSERT INTO testimonials (name, position, organization, content, rating, active, display_order) VALUES
('Sarah Johnson', 'Principal', 'Oakridge Academy', 'FLIONE has transformed our school\'s technology infrastructure. Our students are more engaged, and our teachers have the tools they need to deliver exceptional education.', 5, 1, 1),
('Michael Chen', 'IT Director', 'Westfield Schools', 'The implementation process was seamless, and the ongoing support has been exceptional. FLIONE truly understands the unique challenges schools face with technology integration.', 5, 1, 2),
('Emily Rodriguez', 'Science Teacher', 'Greenwood High', 'As a teacher, I appreciate how FLIONE\'s solutions are designed with the classroom in mind. The technology enhances my teaching without getting in the way.', 5, 1, 3)
ON DUPLICATE KEY UPDATE id = id;

-- Insert sample services
INSERT INTO services (title, description, icon, display_order, active) VALUES
('Smart Classroom Solutions', 'Integrated hardware and software systems designed to create engaging, interactive learning environments that enhance student participation and teacher effectiveness.', 'fas fa-chalkboard-teacher', 1, 1),
('School Management Systems', 'Comprehensive digital platforms that streamline administrative tasks, improve communication, and provide real-time insights into student performance and school operations.', 'fas fa-school', 2, 1),
('Learning Analytics', 'Data-driven tools that help educators understand student progress, identify learning gaps, and personalize instruction to meet individual needs.', 'fas fa-chart-line', 3, 1),
('Digital Content Library', 'Curated collection of high-quality educational resources aligned with curriculum standards, accessible anytime, anywhere to support teaching and learning.', 'fas fa-book-open', 4, 1),
('Infrastructure Planning', 'Expert consultation and implementation services to design and deploy robust, future-proof technology infrastructure tailored to your school\'s unique needs.', 'fas fa-network-wired', 5, 1),
('Professional Development', 'Comprehensive training programs that empower educators to effectively integrate technology into their teaching practices and maximize student learning outcomes.', 'fas fa-user-graduate', 6, 1)
ON DUPLICATE KEY UPDATE id = id;

-- Insert sample counters
INSERT INTO counters (title, value, icon, display_order, active) VALUES
('Client Retention', 95, 'fas fa-user-check', 1, 1),
('Students Reached', 50000, 'fas fa-user-graduate', 2, 1),
('Years of Experience', 15, 'fas fa-calendar-alt', 3, 1),
('Schools Reached', 500, 'fas fa-school', 4, 1)
ON DUPLICATE KEY UPDATE id = id;