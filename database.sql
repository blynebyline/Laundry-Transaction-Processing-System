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

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS machines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    type ENUM('washer', 'dryer') NOT NULL,
    status ENUM('vacant', 'in-use', 'unavailable') DEFAULT 'vacant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT NULL,
    customer_name VARCHAR(150) NOT NULL,

    mode ENUM('pickup', 'delivery') NOT NULL,
    service_type ENUM('wash-fold', 'dry-cleaning', 'full-service', 'fold-only') NULL,

    weight_kg DECIMAL(5,2) NULL,
    item_count INT NULL,
    special_instructions TEXT NULL,
    extras TEXT NULL,
    delivery_note TEXT NULL,

    schedule_date DATE NULL,
    schedule_time VARCHAR(20) NULL,
    address VARCHAR(255) NULL,

    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    order_status ENUM('pending', 'washing', 'finished') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid') DEFAULT 'unpaid',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_machines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    machine_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    released_at DATETIME NULL,

    UNIQUE KEY uniq_order_machine (order_id, machine_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (machine_id) REFERENCES machines(id) ON DELETE CASCADE
);


/* Nakalimutan ko kung ilan, eto nlang muna para saktong 8*/
INSERT INTO machines (name, type) VALUES
('Washer 1', 'washer'),
('Washer 2', 'washer'),
('Washer 3', 'washer'),
('Washer 4', 'washer'),
('Dryer 1', 'dryer'),
('Dryer 2', 'dryer'),
('Dryer 3', 'dryer'),
('Dryer 4', 'dryer');