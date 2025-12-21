-- ===========================================
-- HireGenius Database Schema
-- Video Interview Platform
-- ===========================================

-- Create Database
CREATE DATABASE IF NOT EXISTS hiregenius 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE hiregenius;

-- ===========================================
-- Drop existing tables (for fresh install)
-- ===========================================
DROP TABLE IF EXISTS `answers`;
DROP TABLE IF EXISTS `questions`;
DROP TABLE IF EXISTS `interview_candidates`;
DROP TABLE IF EXISTS `interviews`;
DROP TABLE IF EXISTS `candidates`;
DROP TABLE IF EXISTS `recruiters`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `settings`;

-- ===========================================
-- Admins Table
-- ===========================================
CREATE TABLE `admins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Recruiters Table
-- ===========================================
CREATE TABLE `recruiters` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `company_name` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `status` ENUM('pending', 'approved', 'rejected', 'suspended') DEFAULT 'pending',
    `approved_by` INT UNSIGNED DEFAULT NULL,
    `approved_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_recruiter_email` (`email`),
    INDEX `idx_recruiter_status` (`status`),
    FOREIGN KEY (`approved_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Candidates Table
-- ===========================================
CREATE TABLE `candidates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `phone` VARCHAR(20) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_candidate_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Interviews Table
-- ===========================================
CREATE TABLE `interviews` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `recruiter_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `interview_code` VARCHAR(10) NOT NULL UNIQUE,
    `question_type` ENUM('default', 'custom') DEFAULT 'default',
    `time_per_question` INT UNSIGNED DEFAULT 180 COMMENT 'Time in seconds',
    `start_datetime` DATETIME NOT NULL,
    `end_datetime` DATETIME NOT NULL,
    `status` ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_interview_code` (`interview_code`),
    INDEX `idx_interview_recruiter` (`recruiter_id`),
    INDEX `idx_interview_status` (`status`),
    INDEX `idx_interview_dates` (`start_datetime`, `end_datetime`),
    FOREIGN KEY (`recruiter_id`) REFERENCES `recruiters`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Questions Table
-- ===========================================
CREATE TABLE `questions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `interview_id` INT UNSIGNED NOT NULL,
    `question_text` TEXT NOT NULL,
    `question_order` INT UNSIGNED DEFAULT 1,
    `is_required` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_question_interview` (`interview_id`),
    INDEX `idx_question_order` (`question_order`),
    FOREIGN KEY (`interview_id`) REFERENCES `interviews`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Interview Candidates (Junction Table)
-- ===========================================
CREATE TABLE `interview_candidates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `interview_id` INT UNSIGNED NOT NULL,
    `candidate_id` INT UNSIGNED NOT NULL,
    `status` ENUM('invited', 'started', 'completed', 'expired') DEFAULT 'invited',
    `started_at` TIMESTAMP NULL DEFAULT NULL,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_interview_candidate` (`interview_id`, `candidate_id`),
    INDEX `idx_ic_interview` (`interview_id`),
    INDEX `idx_ic_candidate` (`candidate_id`),
    INDEX `idx_ic_status` (`status`),
    FOREIGN KEY (`interview_id`) REFERENCES `interviews`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`candidate_id`) REFERENCES `candidates`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Answers Table
-- ===========================================
CREATE TABLE `answers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `interview_candidate_id` INT UNSIGNED NOT NULL,
    `question_id` INT UNSIGNED NOT NULL,
    `answer_text` TEXT DEFAULT NULL,
    `video_path` VARCHAR(500) DEFAULT NULL COMMENT 'Path to recorded video file',
    `video_duration` INT UNSIGNED DEFAULT NULL COMMENT 'Video duration in seconds',
    `time_taken` INT UNSIGNED DEFAULT NULL COMMENT 'Time taken to answer in seconds',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_answer` (`interview_candidate_id`, `question_id`),
    INDEX `idx_answer_ic` (`interview_candidate_id`),
    INDEX `idx_answer_question` (`question_id`),
    FOREIGN KEY (`interview_candidate_id`) REFERENCES `interview_candidates`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Settings Table
-- ===========================================
CREATE TABLE `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `setting_type` ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- Insert Default Admin (password: admin123)
-- ===========================================
INSERT INTO `admins` (`name`, `email`, `password`) VALUES 
('Super Admin', 'admin@hiregenius.com', '$2y$10$0KZgsgwuQdB5yiBjivsq4eVgjAV5Qqh9gOquyrjajx33EurI6ex7q');

-- ===========================================
-- Insert Default Settings
-- ===========================================
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES 
('site_name', 'HireGenius', 'string', 'Website name'),
('site_tagline', 'Smart Video Interview Platform', 'string', 'Website tagline'),
('default_time_per_question', '180', 'number', 'Default time per question in seconds'),
('max_questions_per_interview', '20', 'number', 'Maximum questions allowed per interview'),
('allow_recruiter_signup', 'true', 'boolean', 'Allow new recruiter signups'),
('require_admin_approval', 'true', 'boolean', 'Require admin approval for recruiters');

-- ===========================================
-- Insert Default Questions (for default interviews)
-- ===========================================
-- These will be used when recruiter selects "default" question type
-- The application will copy these to the interview

-- ===========================================
-- Sample Data (Optional - for testing)
-- ===========================================

-- Sample Recruiter (password: password123)
-- INSERT INTO `recruiters` (`name`, `email`, `password`, `company_name`, `status`) VALUES 
-- ('John Doe', 'recruiter@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tech Corp', 'approved');

-- ===========================================
-- Views (Optional - for reporting)
-- ===========================================

-- View: Interview Summary
CREATE OR REPLACE VIEW `v_interview_summary` AS
SELECT 
    i.id AS interview_id,
    i.title AS interview_title,
    i.interview_code,
    i.start_datetime,
    i.end_datetime,
    i.status AS interview_status,
    r.name AS recruiter_name,
    r.company_name,
    COUNT(DISTINCT ic.candidate_id) AS total_candidates,
    SUM(CASE WHEN ic.status = 'completed' THEN 1 ELSE 0 END) AS completed_candidates
FROM interviews i
LEFT JOIN recruiters r ON i.recruiter_id = r.id
LEFT JOIN interview_candidates ic ON i.id = ic.interview_id
GROUP BY i.id;

-- View: Candidate Results
CREATE OR REPLACE VIEW `v_candidate_results` AS
SELECT 
    c.id AS candidate_id,
    c.name AS candidate_name,
    c.email AS candidate_email,
    i.id AS interview_id,
    i.title AS interview_title,
    i.interview_code,
    ic.status AS interview_status,
    ic.started_at,
    ic.completed_at,
    COUNT(a.id) AS questions_answered
FROM candidates c
JOIN interview_candidates ic ON c.id = ic.candidate_id
JOIN interviews i ON ic.interview_id = i.id
LEFT JOIN answers a ON ic.id = a.interview_candidate_id
GROUP BY c.id, i.id;
