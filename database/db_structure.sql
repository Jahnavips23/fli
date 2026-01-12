-- Database structure for flioneit.com

-- Create database
CREATE DATABASE IF NOT EXISTS flioneit;
USE flioneit;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Carousel slides table
CREATE TABLE IF NOT EXISTS carousel_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    button_text VARCHAR(50),
    button_link VARCHAR(255),
    active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blog posts table
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    author_id INT,
    status ENUM('draft', 'published') DEFAULT 'draft',
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Blog categories table
CREATE TABLE IF NOT EXISTS blog_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add foreign key to blog_posts
ALTER TABLE blog_posts
ADD CONSTRAINT fk_category
FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL;

-- Downloads table
CREATE TABLE IF NOT EXISTS downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    file_size INT,
    download_count INT DEFAULT 0,
    category VARCHAR(100),
    for_schools BOOLEAN DEFAULT FALSE,
    for_kids BOOLEAN DEFAULT FALSE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Newsletter subscribers table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100),
    subscriber_type ENUM('parent', 'school_staff', 'other') NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Website settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (username, password, email, role) VALUES
('admin', '$2y$10$8KQT7rkP4oEbgJ1skJ9yAOdKrkR86Cy7ZW.nIuLpOKUUaDTs9bJVa', 'admin@flioneit.com', 'admin');

-- Insert initial carousel slides
INSERT INTO carousel_slides (title, description, image_path, button_text, button_link, display_order) VALUES
('Welcome to Flione IT', 'Innovative IT solutions for businesses and educational institutions', 'assets/images/carousel/slide1.jpg', 'Learn More', 'about.php', 1),
('Solutions for Schools', 'Specialized IT services designed for educational environments', 'assets/images/carousel/slide2.jpg', 'For Schools', 'for-school.php', 2),
('Kid-Friendly Technology', 'Safe and educational tech resources for children', 'assets/images/carousel/slide3.jpg', 'For Kids', 'for-kids.php', 3);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_title', 'Flione IT - Business Technology Solutions'),
('site_description', 'Professional IT solutions for businesses, schools, and educational purposes'),
('contact_email', 'contact@flioneit.com'),
('contact_phone', '+1234567890'),
('footer_text', '© 2023 Flione IT. All rights reserved.');