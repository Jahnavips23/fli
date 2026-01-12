-- Create products table if it doesn't exist
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    short_description VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    specifications TEXT,
    features TEXT,
    price DECIMAL(10,2),
    image VARCHAR(255),
    category VARCHAR(50) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create product_enquiries table if it doesn't exist
CREATE TABLE IF NOT EXISTS product_enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    school_name VARCHAR(100),
    message TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Insert sample products if table is empty
INSERT INTO products (name, slug, short_description, description, specifications, features, price, image, category, display_order, active)
SELECT 
    'Smart Interactive Whiteboard', 
    'smart-interactive-whiteboard', 
    'Transform your classroom with our state-of-the-art interactive whiteboard that enhances student engagement and collaboration.',
    '<p>The Smart Interactive Whiteboard is a revolutionary teaching tool designed specifically for modern classrooms. With its intuitive touch interface, 4K display, and seamless integration with educational software, it transforms traditional teaching into an interactive, engaging experience.</p><p>Teachers can easily annotate over any content, save lessons for future reference, and share materials with students instantly. The multi-touch capability allows multiple students to interact simultaneously, promoting collaborative learning and group activities.</p><p>Built with durability in mind, our whiteboards are designed to withstand the rigors of daily classroom use while maintaining exceptional performance and clarity.</p>',
    '{"Display Size": "65 inches", "Resolution": "4K UHD (3840 x 2160)", "Touch Points": "20 simultaneous touch points", "Connectivity": "HDMI, USB, Wi-Fi, Bluetooth", "Operating System": "Android 11.0", "Storage": "32GB internal", "Warranty": "5 years"}',
    '["Multi-touch capability for collaborative learning", "Built-in lesson creation software", "Cloud integration for easy content sharing", "Screen recording and lesson capture", "Compatible with all major educational apps", "Energy-efficient design", "Anti-glare screen for better visibility"]',
    2499.99,
    'assets/images/products/interactive-whiteboard.jpg',
    'Classroom Technology',
    1,
    1
WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);

INSERT INTO products (name, slug, short_description, description, specifications, features, price, image, category, display_order, active)
SELECT 
    'Classroom Management System', 
    'classroom-management-system', 
    'A comprehensive software solution that helps teachers manage digital classrooms, track student progress, and streamline administrative tasks.',
    '<p>Our Classroom Management System is a powerful software solution designed to help educators create, deliver, and manage digital learning experiences with ease. From attendance tracking to real-time assessment, this all-in-one platform simplifies classroom administration while enhancing the teaching and learning process.</p><p>Teachers can easily distribute and collect assignments digitally, provide timely feedback, and track student progress through intuitive dashboards. The system integrates seamlessly with existing school management software and supports various learning models including in-person, hybrid, and remote education.</p><p>With robust reporting features, educators and administrators gain valuable insights into student performance, engagement levels, and areas needing additional support.</p>',
    '{"Deployment": "Cloud-based or on-premises", "User Capacity": "Unlimited students and teachers", "Integrations": "LMS, SIS, Google Workspace, Microsoft 365", "Mobile Support": "iOS and Android apps available", "Data Security": "FERPA compliant, end-to-end encryption", "Updates": "Automatic quarterly updates", "Support": "24/7 technical support"}',
    '["Real-time student monitoring and engagement tracking", "Digital assignment distribution and collection", "Automated grading for objective assessments", "Comprehensive reporting and analytics", "Parent portal for progress monitoring", "Behavior and attendance tracking", "Customizable classroom layouts and seating charts"]',
    1299.99,
    'assets/images/products/classroom-management.jpg',
    'Educational Software',
    2,
    1
WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);

INSERT INTO products (name, slug, short_description, description, specifications, features, price, image, category, display_order, active)
SELECT 
    'STEM Learning Lab', 
    'stem-learning-lab', 
    'A complete STEM education solution with hardware, software, and curriculum resources designed to inspire the next generation of innovators.',
    '<p>The STEM Learning Lab is a comprehensive educational solution that combines cutting-edge technology with carefully crafted curriculum materials to create immersive learning experiences in science, technology, engineering, and mathematics.</p><p>Each lab includes a variety of hands-on learning tools such as robotics kits, 3D printers, coding platforms, and scientific instruments, all selected to align with educational standards and promote inquiry-based learning. The accompanying curriculum provides structured activities that guide students through the process of discovery while developing critical thinking and problem-solving skills.</p><p>Our STEM Learning Lab is designed to be flexible, allowing schools to customize the components based on their specific needs, grade levels, and existing resources. Professional development and ongoing support ensure that educators can effectively implement the lab and maximize its educational impact.</p>',
    '{"Components": "Robotics kits, 3D printer, coding platforms, scientific instruments", "Grade Levels": "Elementary through High School", "Curriculum": "Aligned with NGSS and Common Core standards", "Professional Development": "Initial training plus quarterly workshops", "Space Requirements": "Flexible configurations for any classroom size", "Technical Requirements": "Basic internet connectivity", "Support": "Dedicated STEM specialist assigned to your school"}',
    '["Hands-on, project-based learning experiences", "Cross-curricular connections between STEM subjects", "Progressive skill development across grade levels", "Real-world problem-solving challenges", "Career exploration components", "Assessment tools to measure student growth", "Community showcase opportunities"]',
    8999.99,
    'assets/images/products/stem-lab.jpg',
    'STEM Education',
    3,
    1
WHERE NOT EXISTS (SELECT 1 FROM products LIMIT 1);