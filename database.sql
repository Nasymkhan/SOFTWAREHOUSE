-- ============================================
-- Z9 INTERNATIONAL SOFTWARE HOUSE
-- DATABASE SETUP SQL SCRIPT
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS z9_international;
USE z9_international;

-- ============================================
-- TABLE: Job Applications
-- ============================================
CREATE TABLE IF NOT EXISTS job_applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    cnic VARCHAR(15) NOT NULL UNIQUE,
    role VARCHAR(100) NOT NULL,
    experience INT NOT NULL,
    tech_stack TEXT NOT NULL,
    projects TEXT NOT NULL,
    bio TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'reviewed', 'interview', 'rejected', 'accepted') DEFAULT 'pending',
    notes TEXT,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_submitted_at (submitted_at)
);

-- ============================================
-- TABLE: Contact Messages
-- ============================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_submitted_at (submitted_at)
);

-- ============================================
-- TABLE: Admin Users (Optional - for admin panel)
-- ============================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'moderator') DEFAULT 'moderator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP
);

-- ============================================
-- TABLE: Settings (Optional - for site configuration)
-- ============================================
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- INSERT DEFAULT SETTINGS
-- ============================================
INSERT INTO settings (setting_key, setting_value, description) VALUES
('site_name', 'Z9 International Software House', 'Company name'),
('admin_email', 'admin@z9international.com', 'Admin email for notifications'),
('phone_primary', '+92 (0) 000 0000000', 'Primary contact number'),
('phone_secondary', '+92 (0) 000 0000000', 'Secondary contact number'),
('address', 'Near Degree College Daggar, Buner, KPK, Pakistan', 'Office address'),
('business_hours', 'Monday - Friday: 9:00 AM - 6:00 PM', 'Business operating hours'),
('maintenance_mode', '0', '1 = On, 0 = Off'),
('submissions_enabled', '1', '1 = Accept applications, 0 = Disable');

-- ============================================
-- CREATE VIEWS (Optional - for easy data retrieval)
-- ============================================

-- View for pending job applications
CREATE VIEW pending_applications AS
SELECT * FROM job_applications 
WHERE status = 'pending'
ORDER BY submitted_at DESC;

-- View for new contact messages
CREATE VIEW new_messages AS
SELECT * FROM contact_messages
WHERE status = 'new'
ORDER BY submitted_at DESC;

-- View for application statistics
CREATE VIEW application_stats AS
SELECT 
    role,
    COUNT(*) as total_applications,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
    SUM(CASE WHEN status = 'interview' THEN 1 ELSE 0 END) as interview,
    SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
FROM job_applications
GROUP BY role;

-- ============================================
-- INSERT SAMPLE ADMIN USER (OPTIONAL)
-- ============================================
-- Username: admin
-- Password: admin123 (use password_hash() in real application)
-- INSERT INTO admin_users (username, email, password, role) VALUES
-- ('admin', 'admin@z9international.com', '$2y$10$N9qo8uLOickgx2ZMRZoMye', 'admin');

-- ============================================
-- INDEXES FOR PERFORMANCE
-- ============================================
CREATE INDEX idx_job_apps_email ON job_applications(email);
CREATE INDEX idx_job_apps_status ON job_applications(status);
CREATE INDEX idx_job_apps_submitted ON job_applications(submitted_at);
CREATE INDEX idx_messages_email ON contact_messages(email);
CREATE INDEX idx_messages_status ON contact_messages(status);
CREATE INDEX idx_messages_submitted ON contact_messages(submitted_at);

-- ============================================
-- PERMISSIONS (Optional)
-- ============================================
-- Create a database user for the website (more secure than using root)
-- GRANT SELECT, INSERT, UPDATE ON z9_international.* TO 'z9_user'@'localhost' IDENTIFIED BY 'secure_password';
-- FLUSH PRIVILEGES;

