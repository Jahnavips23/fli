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