-- ============================================================================
-- SQL Migration Script for Production Database (u950539402_citizen_ptl_db)
-- Purpose: Adds async email queue columns to table `ayushman_applications`
-- This enables sub-second form response (<1s) by moving SMTP delivery to cron.
-- ============================================================================

ALTER TABLE `ayushman_applications` 
  ADD COLUMN `email_sent` TINYINT(1) DEFAULT 0 AFTER `consent_accepted`,
  ADD COLUMN `email_sent_at` DATETIME NULL AFTER `email_sent`,
  ADD COLUMN `email_error` TEXT NULL AFTER `email_sent_at`,
  ADD COLUMN `email_attempts` INT DEFAULT 0 AFTER `email_error`,
  ADD INDEX `idx_email_sent` (`email_sent`);
