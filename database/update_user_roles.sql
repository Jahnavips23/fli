-- Update users table to include customer support role
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'customer_support') NOT NULL DEFAULT 'user';

-- Create a default customer support user (password: support123)
INSERT INTO users (username, email, password, role) 
VALUES ('support', 'support@flioneit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer_support')
ON DUPLICATE KEY UPDATE id = id;