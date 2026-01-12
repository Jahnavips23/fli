<?php
require_once 'includes/config.php';

// SQL to create job_listings table
$sql_job_listings = "
CREATE TABLE IF NOT EXISTS `job_listings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `job_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `requirements` text NOT NULL,
  `responsibilities` text NOT NULL,
  `salary_range` varchar(100) DEFAULT NULL,
  `application_deadline` date DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// SQL to create job_applications table
$sql_job_applications = "
CREATE TABLE IF NOT EXISTS `job_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `resume_path` varchar(255) NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'New',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// SQL to insert sample job listings
$sql_sample_jobs = "
INSERT INTO `job_listings` (`title`, `slug`, `department`, `location`, `job_type`, `description`, `requirements`, `responsibilities`, `salary_range`, `application_deadline`, `is_featured`, `is_active`) VALUES
('Software Developer', 'software-developer', 'Engineering', 'London, UK', 'Full-time', '<p>We are looking for a skilled Software Developer to join our engineering team. You will be responsible for developing and maintaining high-quality applications.</p><p>As a Software Developer at FLIONE, you will work in a collaborative environment with other developers, designers, and product managers to build innovative solutions for our clients.</p>', '<ul><li>Bachelor\'s degree in Computer Science, Engineering, or related field</li><li>2+ years of experience in software development</li><li>Proficiency in at least one programming language such as Java, Python, or JavaScript</li><li>Experience with web development frameworks</li><li>Knowledge of database systems and SQL</li><li>Strong problem-solving skills</li><li>Excellent communication and teamwork abilities</li></ul>', '<ul><li>Design, develop, and maintain software applications</li><li>Write clean, efficient, and well-documented code</li><li>Collaborate with cross-functional teams to define and implement new features</li><li>Troubleshoot and debug applications</li><li>Participate in code reviews and contribute to team knowledge sharing</li><li>Stay up-to-date with emerging trends and technologies</li></ul>', '£40,000 - £60,000', DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY), 1, 1),

('UX/UI Designer', 'ux-ui-designer', 'Design', 'London, UK', 'Full-time', '<p>We are seeking a talented UX/UI Designer to create amazing user experiences. The ideal candidate should have an eye for clean and artful design, possess superior UI skills, and be able to translate high-level requirements into interaction flows and artifacts.</p>', '<ul><li>Bachelor\'s degree in Design, HCI, or related field</li><li>3+ years of experience in UX/UI design</li><li>Proficiency in design tools such as Figma, Sketch, or Adobe XD</li><li>Strong portfolio demonstrating UI design and interaction design skills</li><li>Experience with user research and usability testing</li><li>Knowledge of HTML, CSS, and JavaScript is a plus</li></ul>', '<ul><li>Create user-centered designs by understanding business requirements, user feedback, and user research</li><li>Design wireframes, mockups, and prototypes</li><li>Develop UI style guides and design systems</li><li>Collaborate with developers to implement designs</li><li>Conduct usability testing and iterate on designs</li><li>Stay up-to-date with the latest UI trends, techniques, and technologies</li></ul>', '£35,000 - £55,000', DATE_ADD(CURRENT_DATE, INTERVAL 45 DAY), 1, 1)
";

try {
    // Create job_listings table
    $db->exec($sql_job_listings);
    echo "job_listings table created successfully<br>";
    
    // Create job_applications table
    $db->exec($sql_job_applications);
    echo "job_applications table created successfully<br>";
    
    // Check if job_listings table is empty
    $stmt = $db->query("SELECT COUNT(*) FROM job_listings");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert sample job listings
        $db->exec($sql_sample_jobs);
        echo "Sample job listings inserted successfully<br>";
    } else {
        echo "Job listings already exist, skipping sample data insertion<br>";
    }
    
    echo "All done!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>