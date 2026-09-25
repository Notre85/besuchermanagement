-- Datenbankschema für das Besuchermanagement-System

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('Berichtersteller', 'Manager', 'Admin', 'Superadmin') NOT NULL DEFAULT 'Berichtersteller',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS visitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    company VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visitor_id INT NOT NULL,
    planned_visit_id INT,
    visit_reason TEXT NOT NULL,
    checkin_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    checkout_time DATETIME,
    FOREIGN KEY (visitor_id) REFERENCES visitors(id)
);

CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    building VARCHAR(100),
    floor VARCHAR(50),
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_location_name (name)
);

CREATE TABLE IF NOT EXISTS hosts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(254) NOT NULL,
    department VARCHAR(100),
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_host_email (email)
);

CREATE TABLE IF NOT EXISTS planned_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visitor_id INT NOT NULL,
    host_id INT,
    location_id INT,
    visit_reason VARCHAR(500) NOT NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    status ENUM('registered', 'checked_in', 'checked_out', 'expired', 'cancelled') NOT NULL DEFAULT 'registered',
    access_code_hash CHAR(64),
    access_code_expires_at DATETIME,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (visitor_id) REFERENCES visitors(id),
    FOREIGN KEY (host_id) REFERENCES hosts(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX ix_planned_visits_window (starts_at, ends_at, status),
    INDEX ix_planned_visits_code (access_code_hash)
);

CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    request_id CHAR(36),
    result VARCHAR(30) NOT NULL DEFAULT 'success',
    ip_address VARCHAR(45),
    device_id VARCHAR(100),
    metadata_json TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX ix_audit_created_at (created_at),
    INDEX ix_audit_entity (entity_type, entity_id)
);

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    host_id INT NOT NULL,
    planned_visit_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    sent_at DATETIME,
    failed_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (host_id) REFERENCES hosts(id) ON DELETE CASCADE,
    FOREIGN KEY (planned_visit_id) REFERENCES planned_visits(id) ON DELETE CASCADE,
    INDEX ix_notifications_host (host_id, sent_at)
);

CREATE TABLE IF NOT EXISTS printers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    printer_uri VARCHAR(255) NOT NULL,
    location_id INT,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    UNIQUE KEY uq_printer_name (name)
);

CREATE TABLE IF NOT EXISTS keys_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_number VARCHAR(50) NOT NULL,
    label VARCHAR(100) NOT NULL,
    key_type VARCHAR(50) NOT NULL DEFAULT 'standard',
    location_id INT,
    status ENUM('available', 'issued', 'defective', 'blocked') NOT NULL DEFAULT 'available',
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_key_number (key_number),
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    INDEX ix_keys_location_status (location_id, status, active)
);

CREATE TABLE IF NOT EXISTS key_assignments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    key_id INT NOT NULL,
    visit_id INT NOT NULL,
    status ENUM('issued', 'returned', 'lost', 'cancelled') NOT NULL DEFAULT 'issued',
    issued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    returned_at DATETIME,
    issued_by INT,
    returned_by INT,
    notes VARCHAR(500),
    FOREIGN KEY (key_id) REFERENCES keys_inventory(id),
    FOREIGN KEY (visit_id) REFERENCES visits(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (returned_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX ix_key_assignments_active (key_id, status),
    INDEX ix_key_assignments_visit (visit_id, status)
);

CREATE TABLE IF NOT EXISTS print_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    purpose ENUM('visitor_badge', 'key_receipt', 'key_label') NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_template_name_purpose (name, purpose),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS print_template_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    version_no INT NOT NULL,
    format ENUM('html', 'pdf', 'jasper') NOT NULL,
    source TEXT NOT NULL,
    page_width DECIMAL(7,2),
    page_height DECIMAL(7,2),
    variables_json TEXT NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 0,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_template_version (template_id, version_no),
    FOREIGN KEY (template_id) REFERENCES print_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS print_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    output_type ENUM('visitor_badge', 'key_receipt', 'key_label') NOT NULL,
    printer_id INT NOT NULL,
    template_version_id INT NOT NULL,
    location_id INT,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_print_profile_name (name),
    FOREIGN KEY (printer_id) REFERENCES printers(id) ON DELETE CASCADE,
    FOREIGN KEY (template_version_id) REFERENCES print_template_versions(id),
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS print_jobs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    printer_id INT NOT NULL,
    template_version_id INT NOT NULL,
    profile_id INT,
    output_type ENUM('visitor_badge', 'key_receipt', 'key_label') NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NOT NULL,
    payload_json LONGTEXT NOT NULL,
    rendered_format ENUM('html', 'pdf', 'jasper') NOT NULL,
    status ENUM('queued', 'sent', 'printed', 'failed', 'cancelled') NOT NULL DEFAULT 'queued',
    attempts INT NOT NULL DEFAULT 0,
    error_message VARCHAR(500),
    requested_by INT,
    device_id VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    sent_at DATETIME,
    completed_at DATETIME,
    FOREIGN KEY (printer_id) REFERENCES printers(id),
    FOREIGN KEY (template_version_id) REFERENCES print_template_versions(id),
    FOREIGN KEY (profile_id) REFERENCES print_profiles(id) ON DELETE SET NULL,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX ix_print_jobs_status (status, created_at),
    INDEX ix_print_jobs_entity (entity_type, entity_id)
);
