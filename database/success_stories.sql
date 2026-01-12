-- Success Stories Table
CREATE TABLE IF NOT EXISTS success_stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    organization VARCHAR(255) NOT NULL,
    summary TEXT NOT NULL,
    content TEXT,
    image VARCHAR(255),
    results TEXT,
    display_order INT DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data
INSERT INTO success_stories (title, organization, summary, content, image, results, display_order, active) VALUES
('Digital Learning Platform Implementation', 'Oakridge Academy', 'Implemented a comprehensive digital learning platform that increased student engagement by 45% and improved academic performance across all grade levels.', 'Oakridge Academy, a leading K-12 institution, faced challenges with outdated learning systems and decreasing student engagement. FLIONE worked closely with their administration to implement a comprehensive digital learning platform tailored to their specific curriculum and teaching methodologies.\n\nThe solution included interactive digital textbooks, personalized learning paths, real-time assessment tools, and a collaborative learning environment. Teachers received extensive training and ongoing support to ensure successful adoption of the new technology.', 'assets/images/case-study-1.jpg', '{"Student Engagement":"Increased by 45%","Academic Performance":"Improved across all grade levels","Teacher Satisfaction":"92% reported improved teaching experience","Implementation Time":"Completed in just 3 months"}', 1, 1),
('School-wide Technology Modernization', 'Westfield School District', 'Modernized 15 schools with interactive classroom technology, resulting in a 30% increase in teacher satisfaction and improved student collaboration.', 'Westfield School District, comprising 15 schools serving over 12,000 students, needed to modernize their aging technology infrastructure. FLIONE developed a comprehensive modernization plan that addressed their immediate needs while providing a scalable foundation for future growth.\n\nThe project included upgrading classroom technology with interactive displays, implementing a district-wide learning management system, and creating collaborative learning spaces equipped with the latest educational technology tools.', 'assets/images/case-study-2.jpg', '{"Teacher Satisfaction":"Increased by 30%","Student Collaboration":"Significant improvement reported by 85% of teachers","Technology Utilization":"Increased by 60%","Cost Savings":"15% reduction in IT maintenance costs"}', 2, 1),
('STEM Learning Lab Development', 'Greenwood High School', 'Implemented a STEM Learning Lab that revolutionized science and technology education, leading to increased enrollment in advanced courses and improved test scores.', 'Greenwood High School wanted to strengthen their STEM program to better prepare students for college and careers in science and technology fields. FLIONE designed and implemented a state-of-the-art STEM Learning Lab featuring advanced equipment for robotics, 3D printing, computer programming, and scientific experimentation.\n\nThe project included curriculum development, teacher training, and ongoing technical support to ensure the lab was fully utilized across various STEM subjects.', 'assets/images/case-study-3.jpg', '{"Advanced Course Enrollment":"Increased by 35%","STEM Test Scores":"Improved by an average of 22%","College STEM Program Acceptance":"Increased by 40%","Student Interest in STEM Careers":"Grew from 45% to 72%"}', 3, 1);