-- ============================================================
-- Barangay Information and Reporting System (BIRS)
-- Complete Database Schema
-- ============================================================
-- This file contains the complete database structure for BIRS
-- including all tables, data, and relationships
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `role` enum('Admin','Secretary','Barangay Captain') NOT NULL DEFAULT 'Secretary',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin account (password: admin123)
INSERT INTO `users` (`username`, `password`, `fullname`, `role`, `status`) VALUES
('admin', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe6nMXyLYLvJxPdJQqZfIXVKGQVXUJqtu', 'System Administrator', 'Admin', 'Active');

-- ============================================================
-- TABLE: residents
-- ============================================================
CREATE TABLE `residents` (
  `resident_id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `birthdate` date NOT NULL,
  `birthplace` varchar(100) DEFAULT NULL,
  `civil_status` enum('Single','Married','Widowed','Separated','Divorced') NOT NULL DEFAULT 'Single',
  `nationality` varchar(50) DEFAULT 'Filipino',
  `religion` varchar(50) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `purok` varchar(50) NOT NULL,
  `street` varchar(100) DEFAULT NULL,
  `household_id` int(11) DEFAULT NULL,
  `is_voter` enum('Yes','No') DEFAULT 'No',
  `is_pwd` enum('Yes','No') DEFAULT 'No',
  `is_senior` enum('Yes','No') DEFAULT 'No',
  `is_4ps` enum('Yes','No') DEFAULT 'No',
  `status` enum('Active','Deceased','Moved Out') NOT NULL DEFAULT 'Active',
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`resident_id`),
  KEY `household_id` (`household_id`),
  KEY `purok` (`purok`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: households
-- ============================================================
CREATE TABLE `households` (
  `household_id` int(11) NOT NULL AUTO_INCREMENT,
  `household_number` varchar(50) NOT NULL,
  `head_resident_id` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `purok` varchar(50) NOT NULL,
  `income_level` enum('Low','Middle','High') DEFAULT 'Low',
  `house_type` enum('Owned','Rented','Shared') DEFAULT 'Owned',
  `number_of_members` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`household_id`),
  UNIQUE KEY `household_number` (`household_number`),
  KEY `head_resident_id` (`head_resident_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: certificates
-- ============================================================
CREATE TABLE `certificates` (
  `cert_id` int(11) NOT NULL AUTO_INCREMENT,
  `cert_number` varchar(50) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `cert_type` enum('Barangay Clearance','Certificate of Residency','Certificate of Indigency','Business Permit','Good Moral','Other') NOT NULL,
  `purpose` text NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT 0.00,
  `or_number` varchar(50) DEFAULT NULL,
  `issued_by` int(11) NOT NULL,
  `date_issued` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`cert_id`),
  UNIQUE KEY `cert_number` (`cert_number`),
  KEY `resident_id` (`resident_id`),
  KEY `issued_by` (`issued_by`),
  KEY `cert_type` (`cert_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: blotter
-- ============================================================
CREATE TABLE `blotter` (
  `blotter_id` int(11) NOT NULL AUTO_INCREMENT,
  `case_number` varchar(50) NOT NULL,
  `complainant_id` int(11) DEFAULT NULL,
  `complainant_name` varchar(100) NOT NULL,
  `complainant_address` varchar(255) DEFAULT NULL,
  `respondent_id` int(11) DEFAULT NULL,
  `respondent_name` varchar(100) NOT NULL,
  `respondent_address` varchar(255) DEFAULT NULL,
  `incident_type` varchar(100) NOT NULL,
  `incident_date` date NOT NULL,
  `incident_time` time DEFAULT NULL,
  `incident_location` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `status` enum('Pending','Under Investigation','Resolved','Dismissed','Referred to Higher Authority') NOT NULL DEFAULT 'Pending',
  `resolution` text DEFAULT NULL,
  `filed_by` int(11) NOT NULL,
  `date_filed` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`blotter_id`),
  UNIQUE KEY `case_number` (`case_number`),
  KEY `complainant_id` (`complainant_id`),
  KEY `respondent_id` (`respondent_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: officials
-- ============================================================
CREATE TABLE `officials` (
  `official_id` int(11) NOT NULL AUTO_INCREMENT,
  `resident_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `committee` varchar(100) DEFAULT NULL,
  `term_start` date NOT NULL,
  `term_end` date NOT NULL,
  `status` enum('Current','Past') NOT NULL DEFAULT 'Current',
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`official_id`),
  KEY `resident_id` (`resident_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample officials data
INSERT INTO `officials` (`name`, `position`, `committee`, `term_start`, `term_end`, `status`) VALUES
('Juan Dela Cruz', 'Barangay Captain', 'Executive', '2023-01-01', '2025-12-31', 'Current'),
('Maria Santos', 'Kagawad', 'Health and Sanitation', '2023-01-01', '2025-12-31', 'Current'),
('Pedro Reyes', 'Kagawad', 'Peace and Order', '2023-01-01', '2025-12-31', 'Current'),
('Ana Garcia', 'Kagawad', 'Education', '2023-01-01', '2025-12-31', 'Current'),
('Jose Mendoza', 'Kagawad', 'Infrastructure', '2023-01-01', '2025-12-31', 'Current'),
('Rosa Aquino', 'Kagawad', 'Social Services', '2023-01-01', '2025-12-31', 'Current'),
('Carlos Ramos', 'Kagawad', 'Agriculture', '2023-01-01', '2025-12-31', 'Current'),
('Linda Torres', 'Kagawad', 'Environment', '2023-01-01', '2025-12-31', 'Current'),
('Roberto Cruz', 'SK Chairman', 'Youth Development', '2023-01-01', '2025-12-31', 'Current'),
('Elena Flores', 'Barangay Secretary', 'Administrative', '2023-01-01', '2025-12-31', 'Current'),
('Miguel Santos', 'Barangay Treasurer', 'Finance', '2023-01-01', '2025-12-31', 'Current');

-- ============================================================
-- TABLE: announcements
-- ============================================================
CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `category` enum('Event','Notice','Emergency','General') NOT NULL DEFAULT 'General',
  `date_posted` date NOT NULL,
  `event_date` date DEFAULT NULL,
  `posted_by` int(11) NOT NULL,
  `status` enum('Active','Archived') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`announcement_id`),
  KEY `posted_by` (`posted_by`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: system_settings
-- ============================================================
CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `barangay_name` varchar(100) NOT NULL DEFAULT 'Barangay Sample',
  `municipality` varchar(100) NOT NULL DEFAULT 'Municipality',
  `province` varchar(100) NOT NULL DEFAULT 'Province',
  `region` varchar(100) NOT NULL DEFAULT 'Region',
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `captain_signature` varchar(255) DEFAULT NULL,
  `secretary_signature` varchar(255) DEFAULT NULL,
  `clearance_fee` decimal(10,2) DEFAULT 50.00,
  `indigency_fee` decimal(10,2) DEFAULT 0.00,
  `residency_fee` decimal(10,2) DEFAULT 30.00,
  `business_permit_fee` decimal(10,2) DEFAULT 500.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default system settings
INSERT INTO `system_settings` (`barangay_name`, `municipality`, `province`, `region`) VALUES
('Barangay Sample', 'Sample Municipality', 'Sample Province', 'Region I');

-- ============================================================
-- TABLE: activity_logs
-- ============================================================
CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: skills
-- ============================================================
CREATE TABLE `skills` (
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

-- ============================================================
-- TABLE: resident_skills
-- ============================================================
CREATE TABLE `resident_skills` (
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

-- ============================================================
-- TABLE: livelihood_programs
-- ============================================================
CREATE TABLE `livelihood_programs` (
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

-- ============================================================
-- TABLE: training_sessions
-- ============================================================
CREATE TABLE `training_sessions` (
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

-- ============================================================
-- TABLE: training_participants
-- ============================================================
CREATE TABLE `training_participants` (
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

-- ============================================================
-- TABLE: job_opportunities
-- ============================================================
CREATE TABLE `job_opportunities` (
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

-- ============================================================
-- TABLE: job_applications
-- ============================================================
CREATE TABLE `job_applications` (
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

-- ============================================================
-- FOREIGN KEY CONSTRAINTS
-- ============================================================

ALTER TABLE `residents`
  ADD CONSTRAINT `residents_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`) ON DELETE SET NULL;

ALTER TABLE `households`
  ADD CONSTRAINT `households_ibfk_1` FOREIGN KEY (`head_resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE;

ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`issued_by`) REFERENCES `users` (`user_id`);

ALTER TABLE `blotter`
  ADD CONSTRAINT `blotter_ibfk_1` FOREIGN KEY (`complainant_id`) REFERENCES `residents` (`resident_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `blotter_ibfk_2` FOREIGN KEY (`respondent_id`) REFERENCES `residents` (`resident_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `blotter_ibfk_3` FOREIGN KEY (`filed_by`) REFERENCES `users` (`user_id`);

ALTER TABLE `officials`
  ADD CONSTRAINT `officials_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`resident_id`) ON DELETE SET NULL;

ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`user_id`);

ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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

COMMIT;
