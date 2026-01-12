-- Create job_listings table
CREATE TABLE IF NOT EXISTS job_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    department VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    job_type ENUM('Full-time', 'Part-time', 'Contract', 'Remote', 'Internship') NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    responsibilities TEXT NOT NULL,
    salary_range VARCHAR(100),
    application_deadline DATE,
    is_featured BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create job_applications table
CREATE TABLE IF NOT EXISTS job_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    resume_path VARCHAR(255) NOT NULL,
    cover_letter TEXT,
    status ENUM('New', 'Under Review', 'Shortlisted', 'Interviewed', 'Offered', 'Hired', 'Rejected') DEFAULT 'New',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES job_listings(id) ON DELETE CASCADE
);

-- Insert sample job listings
INSERT INTO job_listings (title, slug, department, location, job_type, description, requirements, responsibilities, salary_range, application_deadline, is_featured, is_active) VALUES
(
    'Educational Technology Specialist', 
    'educational-technology-specialist', 
    'Education', 
    'London, UK', 
    'Full-time', 
    '<p>FLIONE is seeking an Educational Technology Specialist to join our growing team. In this role, you will work directly with schools and educational institutions to implement and optimize our technology solutions.</p><p>The ideal candidate will have a strong background in both education and technology, with a passion for improving learning outcomes through innovative tools.</p>', 
    '<ul>
        <li>Bachelor\'s degree in Education, Computer Science, or related field</li>
        <li>Minimum 3 years of experience in educational technology implementation</li>
        <li>Strong understanding of K-12 curriculum and teaching methodologies</li>
        <li>Experience with learning management systems and educational software</li>
        <li>Excellent communication and presentation skills</li>
        <li>Ability to train educators on new technology tools</li>
    </ul>', 
    '<ul>
        <li>Consult with schools to assess technology needs and recommend appropriate solutions</li>
        <li>Implement and configure educational technology systems</li>
        <li>Provide training and support to teachers and administrators</li>
        <li>Develop training materials and documentation</li>
        <li>Gather feedback and suggest product improvements</li>
        <li>Stay current with educational technology trends and best practices</li>
    </ul>', 
    '₹35,00,000 - ₹45,00,000', 
    '2023-12-31', 
    1, 
    1
),
(
    'Software Developer - Educational Applications', 
    'software-developer-educational-applications', 
    'Technology', 
    'Remote', 
    'Full-time', 
    '<p>Join our development team at FLIONE to create innovative applications that transform the educational experience. We\'re looking for a talented Software Developer with experience in building user-friendly, accessible applications for educational purposes.</p>', 
    '<ul>
        <li>Bachelor\'s degree in Computer Science, Software Engineering, or related field</li>
        <li>3+ years of experience in software development</li>
        <li>Proficiency in JavaScript, HTML, CSS, and at least one modern framework (React, Vue, Angular)</li>
        <li>Experience with backend technologies (Node.js, PHP, Python)</li>
        <li>Understanding of database design and management</li>
        <li>Knowledge of accessibility standards and best practices</li>
        <li>Experience with educational software is a plus</li>
    </ul>', 
    '<ul>
        <li>Develop and maintain educational applications and tools</li>
        <li>Collaborate with UX/UI designers to implement intuitive interfaces</li>
        <li>Write clean, maintainable, and efficient code</li>
        <li>Participate in code reviews and contribute to team knowledge sharing</li>
        <li>Troubleshoot and debug applications</li>
        <li>Stay updated with emerging trends and technologies</li>
    </ul>', 
    '₹40,00,000 - ₹55,00,000', 
    '2023-11-30', 
    1, 
    1
),
(
    'IT Support Specialist', 
    'it-support-specialist', 
    'Technical Support', 
    'Manchester, UK', 
    'Full-time', 
    '<p>FLIONE is looking for an IT Support Specialist to provide technical assistance to our school clients. The successful candidate will be responsible for resolving technical issues, installing and configuring hardware and software, and ensuring smooth operation of our educational technology solutions.</p>', 
    '<ul>
        <li>Associate\'s or Bachelor\'s degree in IT, Computer Science, or related field</li>
        <li>2+ years of experience in IT support</li>
        <li>Strong knowledge of computer systems, networks, and common software applications</li>
        <li>Experience with troubleshooting hardware and software issues</li>
        <li>Excellent customer service skills</li>
        <li>Ability to explain technical concepts to non-technical users</li>
    </ul>', 
    '<ul>
        <li>Provide technical support to schools and educational institutions</li>
        <li>Install, configure, and maintain hardware and software</li>
        <li>Troubleshoot and resolve technical issues</li>
        <li>Document support requests and solutions</li>
        <li>Train users on basic system operations</li>
        <li>Collaborate with the development team to improve product reliability</li>
    </ul>', 
    '₹25,00,000 - ₹35,00,000', 
    '2023-10-31', 
    0, 
    1
),
(
    'Curriculum Development Specialist', 
    'curriculum-development-specialist', 
    'Education', 
    'Birmingham, UK', 
    'Part-time', 
    '<p>We are seeking a Curriculum Development Specialist to help create engaging, technology-enhanced learning materials for our educational platforms. The ideal candidate will have experience in curriculum design and a passion for integrating technology into education.</p>', 
    '<ul>
        <li>Master\'s degree in Education, Curriculum and Instruction, or related field</li>
        <li>3+ years of experience in curriculum development</li>
        <li>Knowledge of educational standards and frameworks</li>
        <li>Experience with instructional design principles</li>
        <li>Familiarity with educational technology tools</li>
        <li>Strong writing and content creation skills</li>
    </ul>', 
    '<ul>
        <li>Develop curriculum content for various subjects and grade levels</li>
        <li>Design engaging, interactive learning activities</li>
        <li>Align content with educational standards</li>
        <li>Collaborate with subject matter experts and technology teams</li>
        <li>Review and revise existing curriculum materials</li>
        <li>Provide recommendations for technology integration</li>
    </ul>', 
    '£30,000 - £40,000 (pro-rated)', 
    '2023-11-15', 
    0, 
    1
);