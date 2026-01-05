-- Fair Usage Policy (FUP) Implicit Database Update
-- Run these SQL queries to add FUP fields to tbl_plans table
-- This adds support for direct bandwidth specification (implicit FUP)
--
-- Note: If you already ran the 2025.12.20 update, skip the first 3 queries
-- If columns already exist, you'll get an error - that's okay, just continue with the remaining queries

-- Original FUP fields (skip if already added from 2025.12.20 update)
ALTER TABLE `tbl_plans` ADD `fup_threshold` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Fair Usage Policy threshold' AFTER `expired_date`;
ALTER TABLE `tbl_plans` ADD `fup_threshold_unit` ENUM('MB','GB') NULL DEFAULT NULL COMMENT 'FUP threshold unit' AFTER `fup_threshold`;
ALTER TABLE `tbl_plans` ADD `fup_plan_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Plan to move customer to when FUP threshold exceeded (deprecated - use direct bandwidth instead)' AFTER `fup_threshold_unit`;

-- New FUP bandwidth fields (implicit FUP) - Required for new functionality
ALTER TABLE `tbl_plans` ADD `fup_rate_down` INT UNSIGNED NULL DEFAULT NULL COMMENT 'FUP download speed' AFTER `fup_plan_id`;
ALTER TABLE `tbl_plans` ADD `fup_rate_down_unit` ENUM('Kbps','Mbps') NULL DEFAULT NULL COMMENT 'FUP download speed unit' AFTER `fup_rate_down`;
ALTER TABLE `tbl_plans` ADD `fup_rate_up` INT UNSIGNED NULL DEFAULT NULL COMMENT 'FUP upload speed' AFTER `fup_rate_down_unit`;
ALTER TABLE `tbl_plans` ADD `fup_rate_up_unit` ENUM('Kbps','Mbps') NULL DEFAULT NULL COMMENT 'FUP upload speed unit' AFTER `fup_rate_up`;
ALTER TABLE `tbl_plans` ADD `fup_burst` VARCHAR(128) NULL DEFAULT NULL COMMENT 'FUP burst settings' AFTER `fup_rate_up_unit`;

