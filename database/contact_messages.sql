-- Create contact_messages table if it doesn't exist
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

-- Create testimonials table if it doesn't exist
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

-- Insert sample testimonials if table is empty
INSERT INTO testimonials (name, position, organization, content, rating, active, display_order)
SELECT 'Sarah Johnson', 'Principal', 'Oakridge Academy', 'FLIONE has transformed our school\'s technology infrastructure. Our students are more engaged, and our teachers have the tools they need to deliver exceptional education.', 5, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM testimonials LIMIT 1);

INSERT INTO testimonials (name, position, organization, content, rating, active, display_order)
SELECT 'Michael Chen', 'IT Director', 'Westfield Schools', 'The implementation process was seamless, and the ongoing support has been exceptional. FLIONE truly understands the unique challenges schools face with technology integration.', 5, 1, 2
WHERE NOT EXISTS (SELECT 1 FROM testimonials LIMIT 1);

INSERT INTO testimonials (name, position, organization, content, rating, active, display_order)
SELECT 'Emily Rodriguez', 'Science Teacher', 'Greenwood High', 'As a teacher, I appreciate how FLIONE\'s solutions are designed with the classroom in mind. The technology enhances my teaching without getting in the way.', 5, 1, 3
WHERE NOT EXISTS (SELECT 1 FROM testimonials LIMIT 1);