-- Create counters table if it doesn't exist
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

-- Insert sample counters if table is empty
INSERT INTO counters (title, value, icon, display_order, active)
SELECT 'Client Retention', 95, 'fas fa-user-check', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM counters LIMIT 1);

INSERT INTO counters (title, value, icon, display_order, active)
SELECT 'Students Reached', 50000, 'fas fa-user-graduate', 2, 1
WHERE NOT EXISTS (SELECT 1 FROM counters LIMIT 1);

INSERT INTO counters (title, value, icon, display_order, active)
SELECT 'Years of Experience', 15, 'fas fa-calendar-alt', 3, 1
WHERE NOT EXISTS (SELECT 1 FROM counters LIMIT 1);

INSERT INTO counters (title, value, icon, display_order, active)
SELECT 'Schools Reached', 500, 'fas fa-school', 4, 1
WHERE NOT EXISTS (SELECT 1 FROM counters LIMIT 1);