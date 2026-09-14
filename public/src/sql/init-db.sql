-- Database initialization script for CI
-- Run with: mysql -h 127.0.0.1 -u webuser -pstrongpassword < tests/init-db.sql

CREATE DATABASE IF NOT EXISTS `mydb`;
USE `mydb`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `rate_limits` (
  `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `identifier` VARCHAR(255) NOT NULL,
  `endpoint` VARCHAR(50) NOT NULL,
  `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_identifier_endpoint_time` (`identifier`, `endpoint`, `requested_at`)
);

CREATE TABLE IF NOT EXISTS `user_pictures` (
  `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`)
);

CREATE TABLE IF NOT EXISTS `site_views` (
  `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `total_views` BIGINT NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS `page_views` (
  `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `url` VARCHAR(500) NOT NULL,
  `view_count` BIGINT NOT NULL DEFAULT 0,
  UNIQUE KEY `uk_url` (`url`),
  INDEX `idx_url` (`url`)
);

CREATE TABLE IF NOT EXISTS `user_page_views` (
  `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `view_count` BIGINT NOT NULL DEFAULT 0,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_user_url` (`user_id`, `url`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_url` (`url`)
);

CREATE TABLE IF NOT EXISTS `user_total_views` (
  `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `total_views` BIGINT NOT NULL DEFAULT 0,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_user_id` (`user_id`)
);

INSERT IGNORE INTO `site_views` (`id`, `total_views`) VALUES (1, 0);