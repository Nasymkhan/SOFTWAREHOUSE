-- ============================================
-- UNIFIED USER AUTHENTICATION SYSTEM
-- For SOFTWAREA+ (Z9 Software House)
-- Works across all projects: Z9 Mine, NASYM, etc.
-- ============================================

USE z9_international;

-- ============================================
-- TABLE: Unified User Accounts
-- ============================================
-- This table creates a single account that works
-- across all Z9 projects and platforms
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    
    -- User Profile Information
    full_name VARCHAR(100),
    country VARCHAR(100),
    location VARCHAR(200) NOT NULL,  -- REQUIRED FIELD
    
    -- Profile Picture (stores filename in uploads directory)
    profile_pic_url VARCHAR(500),
    
    -- Account Status
    status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    
    -- Security & Performance
    login_attempts INT DEFAULT 0,
    last_login_attempt TIMESTAMP,
    
    -- Indexes
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- ============================================
-- TABLE: User Sessions (for "Remember Me")
-- ============================================
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    platform VARCHAR(50),  -- 'z9-software-house', 'z9-mine', 'nasym', etc.
    user_agent VARCHAR(500),
    ip_address VARCHAR(45),
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_token (session_token),
    INDEX idx_platform (platform)
);

-- ============================================
-- TABLE: User Login History
-- ============================================
-- Track all login attempts for security audit
CREATE TABLE IF NOT EXISTS login_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    username VARCHAR(50),
    email VARCHAR(100),
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    platform VARCHAR(50),
    login_status ENUM('success', 'failed', 'suspended') DEFAULT 'success',
    failure_reason VARCHAR(200),
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_login_at (login_at),
    INDEX idx_ip_address (ip_address)
);

-- ============================================
-- TABLE: Project Access (Track which projects user can access)
-- ============================================
CREATE TABLE IF NOT EXISTS user_project_access (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    project_name VARCHAR(50) NOT NULL,  -- 'z9-software-house', 'z9-mine', 'nasym', etc.
    access_level ENUM('viewer', 'contributor', 'admin') DEFAULT 'viewer',
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_access (user_id, project_name),
    INDEX idx_user_id (user_id),
    INDEX idx_project (project_name)
);

-- ============================================
-- TABLE: User Profile Updates History
-- ============================================
-- Track all profile changes for audit/recovery
CREATE TABLE IF NOT EXISTS profile_update_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    field_name VARCHAR(50),
    old_value TEXT,
    new_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_updated_at (updated_at)
);

-- ============================================
-- Default Admin User (optional)
-- ============================================
-- Insert default admin account
-- Username: admin | Email: admin@z9.com | Password: Admin@123
-- YOU MUST CHANGE THIS IMMEDIATELY AFTER SETUP!
INSERT IGNORE INTO users (username, email, password_hash, full_name, country, location, status, email_verified, created_at)
VALUES (
    'admin',
    'admin@z9.com',
    '$2y$10$YourHashedPasswordHere.UsePasswordHashFunctionInPHP',
    'Administrator',
    'Pakistan',
    'Daggar, Buner, Pakistan',
    'active',
    TRUE,
    NOW()
);

-- ============================================
-- VIEWS FOR EASY DATA RETRIEVAL
-- ============================================

-- Active users summary
CREATE OR REPLACE VIEW active_users AS
SELECT 
    id, 
    username, 
    email, 
    full_name, 
    country, 
    location,
    profile_pic_url,
    created_at, 
    last_login
FROM users
WHERE status = 'active';

-- Users by country (for analytics)
CREATE OR REPLACE VIEW users_by_country AS
SELECT 
    country, 
    COUNT(*) as user_count,
    COUNT(CASE WHEN last_login IS NOT NULL THEN 1 END) as active_count
FROM users
WHERE status = 'active'
GROUP BY country
ORDER BY user_count DESC;

-- Recent registrations
CREATE OR REPLACE VIEW recent_registrations AS
SELECT 
    id, 
    username, 
    email, 
    full_name, 
    country, 
    location,
    created_at
FROM users
ORDER BY created_at DESC
LIMIT 50;

-- ============================================
-- INDEXES FOR PERFORMANCE
-- ============================================
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_country ON users(country);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_created ON users(created_at);
CREATE INDEX idx_sessions_user ON user_sessions(user_id);
CREATE INDEX idx_login_history_user ON login_history(user_id);
CREATE INDEX idx_login_history_timestamp ON login_history(login_at);

-- ============================================
-- DATABASE COMPLETED
-- ============================================
-- Ready for user registration, login, and
-- cross-platform authentication
-- ============================================
