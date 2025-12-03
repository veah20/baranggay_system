-- Livelihood & Skills Registry Tables
-- Run this file to add the new tables to your existing BIRS database

USE `birs_db`;

-- --------------------------------------------------------
-- Table structure for `skills`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `skills` (
  `skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `skill_name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`skill_id`),
  UNIQUE KEY `skill_name` (`skill_name`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample skills data
INSERT INTO `skills` (`skill_name`, `category`, `description`) VALUES
('Carpentry', 'Construction', 'Woodworking and carpentry skills'),
('Plumbing', 'Construction', 'Plumbing installation and repair'),
('Electrical Work', 'Construction', 'Electrical installation and maintenance'),
('Welding', 'Construction', 'Metal welding and fabrication'),
('Cooking', 'Hospitality', 'Food preparation and cooking'),
('Baking', 'Hospitality', 'Baking and pastry making'),
('Sewing', 'Tailoring', 'Garment making and alterations'),
('Hairdressing', 'Beauty', 'Hair styling and salon services'),
('Computer Basics', 'IT', 'Basic computer skills and MS Office'),
('Web Design', 'IT', 'Website design and development'),
('Graphic Design', 'IT', 'Graphic design and digital art'),
('Agriculture', 'Farming', 'Farming and crop cultivation'),
('Livestock Raising', 'Farming', 'Animal husbandry and livestock management'),
('Automotive Repair', 'Mechanics', 'Vehicle repair and maintenance'),
('Masonry', 'Construction', 'Brickwork and masonry'),
('Beautician', 'Beauty', 'Beauty and cosmetics services'),
('Photography', 'Creative', 'Photography and videography'),
('Accounting', 'Finance', 'Basic accounting and bookkeeping');

-- --------------------------------------------------------
-- Table structure for `resident_skills`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `resident_skills` (
  `resident_skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `proficiency_level` enum('Beginner','Intermediate','Advanced','Expert') NOT NULL DEFAULT 'Beginner',
  `years_of_experience` int(11) DEFAULT 0,
  `certification` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`resident_skill_id`),
  UNIQUE KEY `resident_skill_unique` (`resident_id`, `skill_id`),
  KEY `resident_id` (`resident_id`),
  KEY `skill_id` (`skill_id`),
  KEY `proficiency_level` (`proficiency_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `livelihood_programs`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `livelihood_programs` (
  `program_id` int(11) NOT NULL AUTO_INCREMENT,
  `program_name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `program_type` enum('Training','Livelihood','Microfinance','Skills Development','Other') NOT NULL DEFAULT 'Training',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `target_beneficiaries` int(11) DEFAULT NULL,
  `budget` decimal(12,2) DEFAULT NULL,
  `coordinator_id` int(11) NOT NULL,
  `status` enum('Planning','Ongoing','Completed','Cancelled') NOT NULL DEFAULT 'Planning',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`program_id`),
  KEY `coordinator_id` (`coordinator_id`),
  KEY `status` (`status`),
  KEY `start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `training_sessions`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `training_sessions` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `program_id` int(11) NOT NULL,
  `session_name` varchar(200) NOT NULL,
  `skill_id` int(11) DEFAULT NULL,
  `trainer_name` varchar(100) NOT NULL,
  `session_date` date NOT NULL,
  `session_time` time DEFAULT NULL,
  `duration_hours` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `status` enum('Scheduled','Ongoing','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`session_id`),
  KEY `program_id` (`program_id`),
  KEY `skill_id` (`skill_id`),
  KEY `session_date` (`session_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `training_participants`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `training_participants` (
  `participant_id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `attendance_status` enum('Present','Absent','Excused') DEFAULT 'Present',
  `completion_status` enum('Not Started','In Progress','Completed','Dropped') NOT NULL DEFAULT 'Not Started',
  `certificate_issued` enum('Yes','No') NOT NULL DEFAULT 'No',
  `certificate_number` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `enrolled_date` date NOT NULL,
  `completion_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`participant_id`),
  UNIQUE KEY `session_resident_unique` (`session_id`, `resident_id`),
  KEY `session_id` (`session_id`),
  KEY `resident_id` (`resident_id`),
  KEY `completion_status` (`completion_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `job_opportunities`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `job_opportunities` (
  `opportunity_id` int(11) NOT NULL AUTO_INCREMENT,
  `job_title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `required_skills` text NOT NULL,
  `employer_name` varchar(100) NOT NULL,
  `employer_contact` varchar(20) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `salary_range` varchar(100) DEFAULT NULL,
  `employment_type` enum('Full-time','Part-time','Contract','Temporary','Freelance') NOT NULL DEFAULT 'Full-time',
  `posted_date` date NOT NULL,
  `deadline_date` date DEFAULT NULL,
  `status` enum('Open','Closed','On Hold') NOT NULL DEFAULT 'Open',
  `posted_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`opportunity_id`),
  KEY `posted_by` (`posted_by`),
  KEY `status` (`status`),
  KEY `posted_date` (`posted_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `job_applications`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `job_applications` (
  `application_id` int(11) NOT NULL AUTO_INCREMENT,
  `opportunity_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `application_date` date NOT NULL,
  `status` enum('Applied','Under Review','Shortlisted','Rejected','Hired','Withdrawn') NOT NULL DEFAULT 'Applied',
  `notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `review_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`application_id`),
  UNIQUE KEY `opportunity_resident_unique` (`opportunity_id`, `resident_id`),
  KEY `opportunity_id` (`opportunity_id`),
  KEY `resident_id` (`resident_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Foreign Key Constraints for Livelihood & Skills
-- --------------------------------------------------------

ALTER TABLE `resident_skills`
  ADD CONSTRAINT `resident_skills_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resident_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`skill_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resident_skills_ibfk_3` FOREIGN KEY (`added_by`) REFERENCES `users` (`user_id`);

ALTER TABLE `livelihood_programs`
  ADD CONSTRAINT `livelihood_programs_ibfk_1` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `training_sessions`
  ADD CONSTRAINT `training_sessions_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `livelihood_programs` (`program_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_sessions_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`skill_id`) ON DELETE SET NULL;

ALTER TABLE `training_participants`
  ADD CONSTRAINT `training_participants_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `training_sessions` (`session_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_participants_ibfk_2` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE;

ALTER TABLE `job_opportunities`
  ADD CONSTRAINT `job_opportunities_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`user_id`);

ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`opportunity_id`) REFERENCES `job_opportunities` (`opportunity_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_applications_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
