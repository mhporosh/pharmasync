-- PharmaSync setup script
-- Creates database and required tables if they do not exist

-- Create database
CREATE DATABASE IF NOT EXISTS `pharmasync`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Select database
USE `pharmasync`;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional future tables can be added below, e.g. products, sales, etc.

-- Medicines table
CREATE TABLE IF NOT EXISTS medicines (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    medicine_name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    generic_name VARCHAR(255),
    unit_size VARCHAR(100),
    price DECIMAL(10,2),
    expiry_date DATE DEFAULT NULL,
    stock INT DEFAULT 0
);


-- Add id column to medicines table if it does not exist
ALTER TABLE medicines
ADD COLUMN id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
