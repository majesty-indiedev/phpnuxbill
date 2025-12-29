-- Fair Usage Policy (FUP) Database Update
-- Run these SQL queries to add FUP fields to tbl_plans table

ALTER TABLE `tbl_plans` ADD `fup_threshold` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Fair Usage Policy threshold' AFTER `expired_date`;
ALTER TABLE `tbl_plans` ADD `fup_threshold_unit` ENUM('MB','GB') NULL DEFAULT NULL COMMENT 'FUP threshold unit' AFTER `fup_threshold`;
ALTER TABLE `tbl_plans` ADD `fup_plan_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Plan to move customer to when FUP threshold exceeded' AFTER `fup_threshold_unit`;

