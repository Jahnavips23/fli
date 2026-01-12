-- Create kids_programs table if it doesn't exist
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

-- Create program_registrations table if it doesn't exist
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

-- Create program_gallery table if it doesn't exist
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

-- Insert sample programs if table is empty
INSERT INTO kids_programs (title, slug, short_description, description, age_range, duration, schedule, price, image, start_date, end_date, max_participants, location, is_online, display_order, active)
SELECT 
    'Coding for Kids', 
    'coding-for-kids', 
    'Introduce your child to the exciting world of coding with our fun and interactive program designed specifically for young minds.',
    '<p>Our Coding for Kids program is designed to make learning to code fun and engaging for children. Through interactive games, puzzles, and creative projects, kids will learn the fundamentals of programming logic and computational thinking.</p><p>The curriculum is designed by education experts and professional programmers to ensure that children not only learn to code but also develop problem-solving skills, creativity, and logical thinking that will benefit them in all areas of life.</p><p>No prior experience is necessary! Our instructors will guide children through age-appropriate activities that gradually build their coding skills and confidence.</p>',
    '7-12 years',
    '8 weeks',
    'Saturdays, 10:00 AM - 12:00 PM',
    199.99,
    'assets/images/kids/coding-program.jpg',
    '2023-07-15',
    '2023-09-03',
    15,
    'FLIONE Learning Center',
    0,
    1,
    1
WHERE NOT EXISTS (SELECT 1 FROM kids_programs LIMIT 1);

INSERT INTO kids_programs (title, slug, short_description, description, age_range, duration, schedule, price, image, start_date, end_date, max_participants, location, is_online, display_order, active)
SELECT 
    'Robotics Workshop', 
    'robotics-workshop', 
    'Hands-on robotics workshop where kids build, program, and control their own robots while learning STEM concepts in a fun environment.',
    '<p>In our Robotics Workshop, children will dive into the exciting world of robotics, combining elements of mechanical engineering, electronics, and programming. Participants will work with age-appropriate robotics kits to build and program their own robots.</p><p>Throughout the workshop, kids will learn about sensors, motors, and basic programming concepts as they complete various challenges and projects. Our experienced instructors will guide them through the process, encouraging creativity and problem-solving skills.</p><p>The workshop culminates in a friendly competition where participants can showcase their robots and the skills they\'ve learned. All materials are provided, and no prior experience is necessary.</p>',
    '9-14 years',
    '6 weeks',
    'Wednesdays, 4:00 PM - 6:00 PM',
    249.99,
    'assets/images/kids/robotics-program.jpg',
    '2023-08-02',
    '2023-09-06',
    12,
    'FLIONE Tech Lab',
    0,
    2,
    1
WHERE NOT EXISTS (SELECT 1 FROM kids_programs LIMIT 1);

INSERT INTO kids_programs (title, slug, short_description, description, age_range, duration, schedule, price, image, start_date, end_date, max_participants, location, is_online, display_order, active)
SELECT 
    'Digital Art & Animation', 
    'digital-art-animation', 
    'Unleash your child\'s creativity with our digital art and animation program where they\'ll learn to create stunning digital artwork and animated stories.',
    '<p>Our Digital Art & Animation program introduces children to the world of digital creativity. Using child-friendly software and tools, participants will learn the fundamentals of digital illustration, character design, and animation techniques.</p><p>The course covers color theory, composition, storytelling through visuals, and basic animation principles. Children will create their own digital artwork, design characters, and produce short animated sequences.</p><p>By the end of the program, each participant will have completed a digital portfolio showcasing their artwork and a short animated story of their own creation. This program is perfect for artistic children who want to explore digital mediums and learn modern creative skills.</p>',
    '8-13 years',
    '10 weeks',
    'Sundays, 2:00 PM - 4:00 PM',
    179.99,
    'assets/images/kids/digital-art-program.jpg',
    '2023-07-09',
    '2023-09-10',
    15,
    'FLIONE Creative Studio',
    0,
    3,
    1
WHERE NOT EXISTS (SELECT 1 FROM kids_programs LIMIT 1);

-- Insert sample gallery images
INSERT INTO program_gallery (program_id, title, description, image, display_order, active)
SELECT 
    1, 
    'Coding Camp - Summer 2022', 
    'Kids learning to code and creating their first games during our summer coding camp.',
    'assets/images/kids/gallery/coding-camp-1.jpg',
    1,
    1
WHERE NOT EXISTS (SELECT 1 FROM program_gallery LIMIT 1);

INSERT INTO program_gallery (program_id, title, description, image, display_order, active)
SELECT 
    1, 
    'Coding Project Showcase', 
    'Students proudly presenting their coding projects to parents and peers.',
    'assets/images/kids/gallery/coding-camp-2.jpg',
    2,
    1
WHERE NOT EXISTS (SELECT 1 FROM program_gallery LIMIT 1);

INSERT INTO program_gallery (program_id, title, description, image, display_order, active)
SELECT 
    2, 
    'Robotics Competition', 
    'Our robotics workshop participants competing in the final challenge.',
    'assets/images/kids/gallery/robotics-workshop-1.jpg',
    3,
    1
WHERE NOT EXISTS (SELECT 1 FROM program_gallery LIMIT 1);

INSERT INTO program_gallery (program_id, title, description, image, display_order, active)
SELECT 
    2, 
    'Robot Building Session', 
    'Children working together to build and program their robots.',
    'assets/images/kids/gallery/robotics-workshop-2.jpg',
    4,
    1
WHERE NOT EXISTS (SELECT 1 FROM program_gallery LIMIT 1);

INSERT INTO program_gallery (program_id, title, description, image, display_order, active)
SELECT 
    3, 
    'Digital Art Exhibition', 
    'Showcasing the amazing digital artwork created by our young artists.',
    'assets/images/kids/gallery/digital-art-1.jpg',
    5,
    1
WHERE NOT EXISTS (SELECT 1 FROM program_gallery LIMIT 1);

INSERT INTO program_gallery (program_id, title, description, image, display_order, active)
SELECT 
    3, 
    'Animation Workshop', 
    'Kids learning animation principles and creating their first animated sequences.',
    'assets/images/kids/gallery/digital-art-2.jpg',
    6,
    1
WHERE NOT EXISTS (SELECT 1 FROM program_gallery LIMIT 1);