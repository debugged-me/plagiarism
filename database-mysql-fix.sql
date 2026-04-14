-- Fix MySQL schema to match PHP code
-- Run this in phpMyAdmin

-- Drop existing tables (if they exist with wrong columns)
DROP TABLE IF EXISTS scans;
DROP TABLE IF EXISTS users;

-- Users table - MUST match PHP code exactly
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    google_id VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    avatar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_premium TINYINT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scan history table - MUST match PHP code exactly
CREATE TABLE scans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(64),
    text_preview TEXT,
    file_name TEXT,
    plagiarism_score REAL,
    sources_count INT,
    result_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
CREATE INDEX idx_users_google ON users(google_id);
CREATE INDEX idx_scans_user ON scans(user_id);
CREATE INDEX idx_scans_session ON scans(session_id);
