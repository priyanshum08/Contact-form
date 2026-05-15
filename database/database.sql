-- ─────────────────────────────────────────────────────────────────────────────
-- Contact Form Project — Database Setup
-- Import this file via phpMyAdmin or MySQL CLI
-- ─────────────────────────────────────────────────────────────────────────────

-- 1. Create and select the database
CREATE DATABASE IF NOT EXISTS `contact_project`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `contact_project`;

-- 2. Create the contacts table
CREATE TABLE IF NOT EXISTS `contacts` (
  `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150)     NOT NULL,
  `email`      VARCHAR(255)     NOT NULL,
  `phone`      VARCHAR(30)      DEFAULT NULL,
  `subject`    VARCHAR(255)     DEFAULT NULL,
  `message`    TEXT             NOT NULL,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email`      (`email`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────────────────────
-- Table starts EMPTY — no demo records inserted intentionally.
-- Submit the contact form on index.html to populate data.
-- ─────────────────────────────────────────────────────────────────────────────
