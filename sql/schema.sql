-- Minimal schema for the billing system
CREATE DATABASE IF NOT EXISTS utility_db;
USE utility_db;

CREATE TABLE IF NOT EXISTS customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role ENUM('admin','employee','user') NOT NULL DEFAULT 'user',
  name VARCHAR(200),
  contact VARCHAR(20),
  address TEXT,
  service_no VARCHAR(40) UNIQUE,
  service_type VARCHAR(20) DEFAULT 'household',
  prev_reading INT DEFAULT 0,
  curr_reading INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bill_no VARCHAR(50) UNIQUE,
  service_no VARCHAR(40),
  prev_reading INT DEFAULT 0,
  curr_reading INT DEFAULT 0,
  units INT DEFAULT 0,
  bill_price DECIMAL(10,2) DEFAULT 0,
  prev_dues DECIMAL(10,2) DEFAULT 0,
  fine DECIMAL(10,2) DEFAULT 0,
  total_bill DECIMAL(10,2) DEFAULT 0,
  due_date DATE,
  status ENUM('paid','unpaid') DEFAULT 'unpaid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (service_no) REFERENCES customers(service_no) ON DELETE CASCADE
);

-- IMPORTANT: Replace REPLACE_WITH_HASH with a secure hash created by:
-- php -r "echo password_hash('admin123', PASSWORD_DEFAULT).PHP_EOL;"
INSERT INTO customers (username, password, role, name, service_no)
VALUES ('admin', 'REPLACE_WITH_HASH', 'admin', 'System Administrator', 'ADM-001');