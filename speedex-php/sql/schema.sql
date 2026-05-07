-- SpeedEx Courier Service - MySQL Schema
CREATE DATABASE IF NOT EXISTS speedex_courier CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE speedex_courier;

CREATE TABLE hubs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  address VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(20),
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','hub_manager') NOT NULL,
  hub_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (hub_id) REFERENCES hubs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE parcels (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tracking_id VARCHAR(30) NOT NULL UNIQUE,
  sender_name VARCHAR(100) NOT NULL,
  sender_phone VARCHAR(20) NOT NULL,
  sender_address VARCHAR(255) NOT NULL,
  sender_hub_id INT NOT NULL,
  receiver_name VARCHAR(100) NOT NULL,
  receiver_phone VARCHAR(20) NOT NULL,
  receiver_address VARCHAR(255) NOT NULL,
  receiver_hub_id INT NOT NULL,
  parcel_type VARCHAR(50) NOT NULL,
  weight DECIMAL(6,2) NOT NULL,
  description TEXT,
  payment_type ENUM('sender','receiver') NOT NULL,
  status ENUM('received','transit','arrived','ready','delivered') DEFAULT 'received',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_hub_id) REFERENCES hubs(id),
  FOREIGN KEY (receiver_hub_id) REFERENCES hubs(id)
) ENGINE=InnoDB;

CREATE TABLE parcel_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parcel_id INT NOT NULL,
  status VARCHAR(50) NOT NULL,
  hub_id INT,
  note VARCHAR(255),
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (parcel_id) REFERENCES parcels(id) ON DELETE CASCADE,
  FOREIGN KEY (hub_id) REFERENCES hubs(id)
) ENGINE=InnoDB;

INSERT INTO hubs (name, address) VALUES
  ('Dhaka Hub', 'House 12, Road 5, Dhanmondi, Dhaka-1205'),
  ('Mymensingh Hub', 'House 45, Road 3, Mymensingh Sadar, Mymensingh-2200'),
  ('Chattogram Hub', 'Agrabad C/A, Chattogram-4100'),
  ('Sylhet Hub', 'Zindabazar, Sylhet-3100'),
  ('Khulna Hub', 'Sonadanga, Khulna-9100');
