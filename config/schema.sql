CREATE DATABASE IF NOT EXISTS policy_renewal_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE policy_renewal_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(150)    NOT NULL,
    email       VARCHAR(200)    NOT NULL UNIQUE,
    password    VARCHAR(255)    NOT NULL,
    role        ENUM('admin','policy_officer','viewer') NOT NULL DEFAULT 'viewer',
    is_active   TINYINT(1)      NOT NULL DEFAULT 1,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Policies table
CREATE TABLE IF NOT EXISTS policies (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    policy_number   VARCHAR(50)     NOT NULL UNIQUE,
    client_name     VARCHAR(150)    NOT NULL,
    insurance_type  VARCHAR(100)    NOT NULL,
    premium_amount  DECIMAL(12,2)   NOT NULL,
    start_date      DATE            NOT NULL,
    renewal_date    DATE            NOT NULL,
    status          ENUM('active','expired','pending_renewal') NOT NULL DEFAULT 'active',
    created_by      INT UNSIGNED    NOT NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_policy_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Documents table
CREATE TABLE IF NOT EXISTS documents (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    policy_id   INT UNSIGNED    NOT NULL,
    file_name   VARCHAR(255)    NOT NULL,
    stored_name VARCHAR(255)    NOT NULL,
    file_type   VARCHAR(50)     NOT NULL,
    file_size   INT UNSIGNED    NOT NULL,
    uploaded_by INT UNSIGNED    NOT NULL,
    uploaded_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_doc_policy    FOREIGN KEY (policy_id)   REFERENCES policies(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_doc_uploaded  FOREIGN KEY (uploaded_by) REFERENCES users(id)    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Activity log table
CREATE TABLE IF NOT EXISTS activity_log (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED    NULL,
    action      VARCHAR(100)    NOT NULL,
    entity      VARCHAR(50)     NULL,
    entity_id   INT UNSIGNED    NULL,
    detail      TEXT            NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Seed: default admin user  (password: Admin@1234)
INSERT INTO users (full_name, email, password, role)
VALUES (
    'System Administrator',
    'admin@company.com',
    '$2b$10$J/ipATPGeSA.KjlyyqYMCO.ZmnnDfggfBoz3gXA0pDSMesmk1IrEy',
    'admin'
);

-- Seed: sample policy officer (password: Officer@1234)
INSERT INTO users (full_name, email, password, role)
VALUES (
    'Jane Doe',
    'officer@company.com',
    '$2b$10$oVeFpHdHR9H/5eRRalGgDesjfQy3/EY.mIU2ZJlZuog5IwTUE0XMO',
    'policy_officer'
);

-- Seed: sample viewer (password: Viewer@1234)
INSERT INTO users (full_name, email, password, role)
VALUES (
    'John Smith',
    'viewer@company.com',
    '$2b$10$C3f/iyIs./83Gz.GkHcCEeIPzooS66PZ5dE3YQ0aFgYMPDOOBempq',
    'viewer'
);

-- Seed: sample policies
INSERT INTO policies (policy_number, client_name, insurance_type, premium_amount, start_date, renewal_date, status, created_by)
VALUES
    ('POL-2024-001', 'Acme Corporation',    'Commercial Property', 15000.00, '2024-01-15', '2025-01-15', 'expired',        1),
    ('POL-2024-002', 'Bright Future Ltd',   'Life Insurance',       8500.00, '2024-03-01', '2025-03-01', 'pending_renewal',1),
    ('POL-2024-003', 'Green Valley Farm',   'Agricultural',         3200.00, '2024-06-10', '2025-06-10', 'active',         2),
    ('POL-2025-001', 'Metro Logistics',     'Motor Fleet',         22000.00, '2025-01-01', '2026-01-01', 'active',         2),
    ('POL-2025-002', 'Sunrise Medical',     'Health Insurance',    11000.00, '2025-02-14', '2026-02-14', 'active',         2),
    ('POL-2025-003', 'TechNova Systems',    'Cyber Liability',      9500.00, '2025-04-01', DATE_ADD(CURDATE(), INTERVAL 12 DAY), 'pending_renewal', 1),
    ('POL-2025-004', 'Harare Builders',     'Public Liability',     6700.00, '2025-05-01', DATE_ADD(CURDATE(), INTERVAL 5 DAY),  'active', 2),
    ('POL-2025-005', 'Eagle Eye Security',  'Professional Indemnity',4300.00,'2025-05-10', DATE_ADD(CURDATE(), INTERVAL 25 DAY), 'active', 2);
