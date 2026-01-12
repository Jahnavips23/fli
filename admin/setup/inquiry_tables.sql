-- Create client_inquiries table
CREATE TABLE IF NOT EXISTS client_inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    company VARCHAR(255),
    source VARCHAR(100) NOT NULL COMMENT 'Where the inquiry came from (e.g., IndiaMart, Website, Referral)',
    inquiry_type VARCHAR(100) NOT NULL COMMENT 'Type of inquiry (e.g., Product, Service, Partnership)',
    message TEXT,
    status VARCHAR(50) NOT NULL DEFAULT 'new' COMMENT 'Status of the inquiry (new, contacted, qualified, converted, closed)',
    assigned_to INT NULL COMMENT 'User ID of staff member assigned to this inquiry',
    welcome_email_sent TINYINT(1) NOT NULL DEFAULT 0,
    welcome_email_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (status),
    INDEX (source),
    INDEX (assigned_to)
);

-- Create client_inquiry_notes table
CREATE TABLE IF NOT EXISTS client_inquiry_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inquiry_id INT NOT NULL,
    user_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inquiry_id) REFERENCES client_inquiries(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create email_templates table
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create email_attachments table
CREATE TABLE IF NOT EXISTS email_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create inquiry_sources table
CREATE TABLE IF NOT EXISTS inquiry_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create inquiry_types table
CREATE TABLE IF NOT EXISTS inquiry_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default inquiry sources
INSERT INTO inquiry_sources (name, display_order) VALUES 
('IndiaMart', 1),
('Website', 2),
('Referral', 3),
('Social Media', 4),
('Trade Show', 5),
('Cold Call', 6),
('Other', 7);

-- Insert default inquiry types
INSERT INTO inquiry_types (name, display_order) VALUES 
('Product Information', 1),
('Service Inquiry', 2),
('Partnership', 3),
('Support', 4),
('General Inquiry', 5);

-- Insert default email template
INSERT INTO email_templates (name, subject, body, is_default) VALUES 
('Welcome Email', 'Welcome to FLIONE IT Solutions', '<p>Dear {client_name},</p>\r\n\r\n<p>Thank you for your interest in FLIONE IT Solutions. We are delighted to connect with you and appreciate your inquiry.</p>\r\n\r\n<p>Attached to this email, you will find our company brochure that provides detailed information about our products and services. We believe our solutions can help address your business needs effectively.</p>\r\n\r\n<p>If you have any questions or would like to discuss your specific requirements, please feel free to contact us. Our team is always ready to assist you.</p>\r\n\r\n<p>We look forward to the opportunity of working with you.</p>\r\n\r\n<p>Best regards,<br>\r\nFLIONE IT Solutions Team<br>\r\nPhone: {company_phone}<br>\r\nEmail: {company_email}<br>\r\nWebsite: {company_website}</p>', 1);