-- Project tracking tables

-- Project status options table
CREATE TABLE IF NOT EXISTS project_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    color VARCHAR(20) NOT NULL DEFAULT '#3498db',
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(20) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(50),
    description TEXT,
    status_id INT NOT NULL,
    last_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (status_id) REFERENCES project_statuses(id) ON DELETE RESTRICT
);

-- Project updates table
CREATE TABLE IF NOT EXISTS project_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    status_id INT NOT NULL,
    comments TEXT,
    notify_customer TINYINT(1) NOT NULL DEFAULT 0,
    notification_sent TINYINT(1) NOT NULL DEFAULT 0,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (status_id) REFERENCES project_statuses(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
);

-- Insert default status options
INSERT INTO project_statuses (name, description, color, is_default, display_order) VALUES
('New', 'Project has been created but work has not started yet', '#3498db', 1, 1),
('In Progress', 'Work on the project has started', '#f39c12', 0, 2),
('On Hold', 'Project is temporarily paused', '#e74c3c', 0, 3),
('Completed', 'Project has been completed successfully', '#2ecc71', 0, 4),
('Cancelled', 'Project has been cancelled', '#95a5a6', 0, 5)
ON DUPLICATE KEY UPDATE id = id;