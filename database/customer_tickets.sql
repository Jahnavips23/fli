-- Customer tickets tables

-- Ticket priorities table
CREATE TABLE IF NOT EXISTS ticket_priorities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(20) NOT NULL DEFAULT '#3498db',
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Ticket categories table
CREATE TABLE IF NOT EXISTS ticket_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Ticket statuses table
CREATE TABLE IF NOT EXISTS ticket_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(20) NOT NULL DEFAULT '#3498db',
    is_closed TINYINT(1) NOT NULL DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customer tickets table
CREATE TABLE IF NOT EXISTS customer_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_number VARCHAR(20) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(50),
    status_id INT NOT NULL,
    priority_id INT NOT NULL,
    category_id INT,
    assigned_to INT,
    project_id INT,
    last_reply_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (status_id) REFERENCES ticket_statuses(id) ON DELETE RESTRICT,
    FOREIGN KEY (priority_id) REFERENCES ticket_priorities(id) ON DELETE RESTRICT,
    FOREIGN KEY (category_id) REFERENCES ticket_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
);

-- Ticket replies table
CREATE TABLE IF NOT EXISTS ticket_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    message TEXT NOT NULL,
    is_customer TINYINT(1) NOT NULL DEFAULT 0,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES customer_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Ticket attachments table
CREATE TABLE IF NOT EXISTS ticket_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    reply_id INT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES customer_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (reply_id) REFERENCES ticket_replies(id) ON DELETE CASCADE
);

-- Insert default ticket priorities
INSERT INTO ticket_priorities (name, color, display_order) VALUES
('Low', '#28a745', 1),
('Medium', '#ffc107', 2),
('High', '#fd7e14', 3),
('Critical', '#dc3545', 4)
ON DUPLICATE KEY UPDATE id = id;

-- Insert default ticket statuses
INSERT INTO ticket_statuses (name, color, is_closed, display_order) VALUES
('New', '#007bff', 0, 1),
('In Progress', '#17a2b8', 0, 2),
('Waiting for Customer', '#ffc107', 0, 3),
('Resolved', '#28a745', 1, 4),
('Closed', '#6c757d', 1, 5)
ON DUPLICATE KEY UPDATE id = id;

-- Insert default ticket categories
INSERT INTO ticket_categories (name, description, display_order) VALUES
('General Inquiry', 'General questions about products or services', 1),
('Technical Support', 'Technical issues and troubleshooting', 2),
('Billing', 'Questions about billing, invoices, or payments', 3),
('Feature Request', 'Suggestions for new features or improvements', 4),
('Bug Report', 'Report of software bugs or issues', 5)
ON DUPLICATE KEY UPDATE id = id;