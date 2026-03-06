-- ========================================
-- ProjektAdmin Database Schema
-- BBK Modul 307
-- ========================================

CREATE DATABASE IF NOT EXISTS MraJscEadLboM307
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE projektadmin;

-- ========================================
-- Benutzer Tabelle
-- ========================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL,
    role ENUM('admin', 'lp', 'lb', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Berechtigungen Tabelle
-- ========================================
CREATE TABLE IF NOT EXISTS permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role VARCHAR(50) NOT NULL,
    permission VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    UNIQUE KEY unique_role_permission (role, permission),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Kunden Tabelle
-- ========================================
CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    city VARCHAR(100),
    postal_code VARCHAR(10),
    contact_person VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Projekte Tabelle
-- ========================================
CREATE TABLE IF NOT EXISTS projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    status ENUM('planning', 'in_progress', 'completed', 'on_hold') DEFAULT 'planning',
    budget DECIMAL(10, 2),
    customer_id INT,
    project_manager_id INT,
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (project_manager_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Stunden Tabelle
-- ========================================
CREATE TABLE IF NOT EXISTS hours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    hours DECIMAL(5, 2) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_entry (user_id, project_id, date),
    INDEX idx_date (date),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Kundenanfragen Tabelle
-- ========================================
CREATE TABLE IF NOT EXISTS customer_inquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'in_progress', 'resolved', 'closed') DEFAULT 'new',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Activity Log Tabelle
-- ========================================
CREATE TABLE IF NOT EXISTS activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- SAMPLE DATA
-- ========================================

-- Berechtigungen
INSERT INTO permissions (role, permission, description) VALUES
('admin', 'manage_permissions', 'Berechtigungen verwalten'),
('admin', 'manage_users', 'Benutzer verwalten'),
('admin', 'view_all_projects', 'Alle Projekte ansehen'),
('admin', 'view_all_hours', 'Alle Stunden ansehen'),
('lp', 'create_project', 'Projekte erstellen'),
('lp', 'manage_team', 'Team verwalten'),
('lp', 'view_customer_data', 'Kundendaten ansehen'),
('lb', 'log_hours', 'Stunden erfassen'),
('lb', 'view_own_hours', 'Eigene Stunden ansehen'),
('user', 'view_own_profile', 'Eigenes Profil ansehen');

-- Test Benutzer (Passwort: password123)
INSERT INTO users (username, password, email, name, role) VALUES
('admin', '$2y$10$abcd1234efgh5678ijkl9012mnopqrst', 'admin@test.de', 'Administrator', 'admin'),
('lp1', '$2y$10$abcd1234efgh5678ijkl9012mnopqrst', 'lp1@test.de', 'Leiter Projekt', 'lp'),
('lb1', '$2y$10$abcd1234efgh5678ijkl9012mnopqrst', 'lb1@test.de', 'Leiter Bereich', 'lb'),
('user1', '$2y$10$abcd1234efgh5678ijkl9012mnopqrst', 'user1@test.de', 'Benutzer', 'user');

-- Test Kunden
INSERT INTO customers (name, email, phone, address, city, postal_code, contact_person) VALUES
('TechCorp GmbH', 'info@techcorp.de', '+49 30 123456', 'Hauptstr. 10', 'Berlin', '10115', 'Max Müller'),
('WebDesign AG', 'contact@webdesign.de', '+49 40 789012', 'Hafenstr. 5', 'Hamburg', '20457', 'Anna Schmidt'),
('Software Solutions', 'sales@software.de', '+49 89 345678', 'Marktplatz 3', 'München', '80331', 'Peter König');

-- Test Projekte
INSERT INTO projects (name, description, status, budget, customer_id, project_manager_id, start_date, end_date) VALUES
('Website Redesign', 'Komplettes Redesign der Unternehmenswebsite', 'in_progress', 15000.00, 1, 2, '2026-01-15', '2026-03-15'),
('Mobile App', 'Native iOS und Android App', 'planning', 25000.00, 2, 2, '2026-02-01', '2026-06-30'),
('Dashboard System', 'Analytisches Dashboard', 'completed', 12000.00, 3, 2, '2025-11-01', '2026-01-31');