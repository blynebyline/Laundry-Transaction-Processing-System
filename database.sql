CREATE DATABASE IF NOT EXISTS laundry_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE laundry_system;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(20) NOT NULL UNIQUE,     
    customer_name VARCHAR(150) NOT NULL,

    mode ENUM('pickup', 'delivery',) NOT NULL,
    service_type ENUM('wash-fold', 'dry-cleaning', 'full-service', 'fold-only') NULL,

    washer_count TINYINT NULL,                 
    dryer_count TINYINT NULL,                   

    weight_kg DECIMAL(5,2) NULL,
    item_count INT NULL,
    special_instructions TEXT NULL,

    schedule_date DATE NULL,
    schedule_time VARCHAR(20) NULL,           
    address VARCHAR(255) NULL,                  

    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    order_status ENUM('pending', 'washing', 'finished') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid') DEFAULT 'unpaid',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);