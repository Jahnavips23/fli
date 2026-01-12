-- Create services table if it doesn't exist
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

-- Insert sample services if table is empty
INSERT INTO services (title, description, icon, display_order, active)
SELECT 'Smart Classroom Solutions', 'Integrated hardware and software systems designed to create engaging, interactive learning environments that enhance student participation and teacher effectiveness.', 'fas fa-chalkboard-teacher', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM services LIMIT 1);

INSERT INTO services (title, description, icon, display_order, active)
SELECT 'School Management Systems', 'Comprehensive digital platforms that streamline administrative tasks, improve communication, and provide real-time insights into student performance and school operations.', 'fas fa-school', 2, 1
WHERE NOT EXISTS (SELECT 1 FROM services LIMIT 1);

INSERT INTO services (title, description, icon, display_order, active)
SELECT 'Learning Analytics', 'Data-driven tools that help educators understand student progress, identify learning gaps, and personalize instruction to meet individual needs.', 'fas fa-chart-line', 3, 1
WHERE NOT EXISTS (SELECT 1 FROM services LIMIT 1);

INSERT INTO services (title, description, icon, display_order, active)
SELECT 'Digital Content Library', 'Curated collection of high-quality educational resources aligned with curriculum standards, accessible anytime, anywhere to support teaching and learning.', 'fas fa-book-open', 4, 1
WHERE NOT EXISTS (SELECT 1 FROM services LIMIT 1);

INSERT INTO services (title, description, icon, display_order, active)
SELECT 'Infrastructure Planning', 'Expert consultation and implementation services to design and deploy robust, future-proof technology infrastructure tailored to your school\'s unique needs.', 'fas fa-network-wired', 5, 1
WHERE NOT EXISTS (SELECT 1 FROM services LIMIT 1);

INSERT INTO services (title, description, icon, display_order, active)
SELECT 'Professional Development', 'Comprehensive training programs that empower educators to effectively integrate technology into their teaching practices and maximize student learning outcomes.', 'fas fa-user-graduate', 6, 1
WHERE NOT EXISTS (SELECT 1 FROM services LIMIT 1);