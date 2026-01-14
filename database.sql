-- Research Paper Annotation Tool Database

CREATE DATABASE IF NOT EXISTS if0_40665526_researchhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE if0_40665526_researchhub;

-- ======================================
-- USERS TABLE
-- ======================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'moderator', 'viewer') NOT NULL DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================
-- PAPERS TABLE
-- ======================================
CREATE TABLE IF NOT EXISTS papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL,
    authors VARCHAR(500),
    year INT,
    link VARCHAR(500),
    file_path VARCHAR(500),
    methodology LONGTEXT,
    limitations LONGTEXT,
    remark LONGTEXT,
    tags VARCHAR(500),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_title (title),
    INDEX idx_year (year),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;




-- ======================================
-- CREATE DEFAULT ADMIN USER
-- ======================================
INSERT INTO users (name, email, password_hash, role) 
VALUES ('Jubayer', 'jubayer@gmail.com', '$2y$10$yEfHQcXu20c95rCMkmVoyeSve/IDIVmPDLlN1UzUFGXd1z/VmpEeO', 'Admin')
ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id);

