-- Create downloads table if it doesn't exist
CREATE TABLE IF NOT EXISTS `downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL DEFAULT 0,
  `category` varchar(50) DEFAULT 'Other',
  `version` varchar(20) DEFAULT NULL,
  `for_schools` tinyint(1) NOT NULL DEFAULT 0,
  `for_kids` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `download_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample downloads
INSERT INTO `downloads` (`title`, `description`, `file_path`, `file_size`, `category`, `version`, `for_schools`, `for_kids`, `active`, `download_count`)
VALUES
('School Management System', 'A comprehensive school management system for administrators, teachers, and students. Manage classes, attendance, grades, and more.', 'uploads/downloads/sample_school_management.zip', 15728640, 'Windows', '2.1.0', 1, 0, 1, 0),

('Interactive Learning App', 'An interactive learning application for students with engaging activities and quizzes across various subjects.', 'uploads/downloads/sample_learning_app.zip', 10485760, 'Windows', '1.5.2', 1, 1, 1, 0),

('Math Games for Kids', 'Fun math games designed to help children learn arithmetic, geometry, and problem-solving skills.', 'uploads/downloads/sample_math_games.apk', 8388608, 'Android', '3.0.1', 0, 1, 1, 0),

('Science Explorer', 'An educational app that makes learning science fun with interactive experiments and simulations.', 'uploads/downloads/sample_science_explorer.ipa', 12582912, 'iOS', '2.2.0', 0, 1, 1, 0),

('Teacher\'s Toolkit', 'Essential tools for teachers including lesson planners, grade trackers, and classroom management resources.', 'uploads/downloads/sample_teachers_toolkit.dmg', 20971520, 'macOS', '1.0.3', 1, 0, 1, 0),

('Classroom Assistant', 'A helpful application for teachers to manage classroom activities, assignments, and student progress.', 'uploads/downloads/sample_classroom_assistant.zip', 18874368, 'Windows', '2.3.1', 1, 0, 1, 0),

('Programming for Kids', 'A beginner-friendly programming environment designed specifically for children to learn coding concepts.', 'uploads/downloads/sample_programming_kids.apk', 9437184, 'Android', '1.1.0', 0, 1, 1, 0),

('Educational Games Bundle', 'A collection of educational games covering various subjects for elementary school students.', 'uploads/downloads/sample_edu_games.zip', 25165824, 'Windows', '3.2.0', 1, 1, 1, 0),

('School Timetable Planner', 'A tool for schools to create and manage class schedules and timetables efficiently.', 'uploads/downloads/sample_timetable_planner.zip', 5242880, 'Windows', '1.4.5', 1, 0, 1, 0),

('Student Progress Tracker', 'An application for parents and teachers to monitor student progress across different subjects.', 'uploads/downloads/sample_progress_tracker.ipa', 7340032, 'iOS', '2.0.1', 1, 0, 1, 0),

('STEM Activities App', 'An app with hands-on Science, Technology, Engineering, and Math activities for students.', 'uploads/downloads/sample_stem_activities.apk', 11534336, 'Android', '1.7.2', 0, 1, 1, 0),

('User Manual - School Management System', 'Comprehensive documentation for the School Management System software.', 'uploads/downloads/sample_user_manual.pdf', 2097152, 'Documentation', '2.1.0', 1, 0, 1, 0);