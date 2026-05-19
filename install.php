<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Perfex activation hook entrypoint.
 * Must be a function because module file calls internship_management_install().
 */
function internship_management_install()
{
    $CI = &get_instance();

    // Create tables if not exist
    $tables = [];

    $tables[] = "CREATE TABLE IF NOT EXISTS `" . db_prefix() . "internship_applications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(32) NULL,
  `full_name` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(50) NULL,
  `email` VARCHAR(191) NULL,
  `address` VARCHAR(255) NULL,
  `gender` VARCHAR(20) NULL,
  `school` VARCHAR(191) NULL,
  `major` VARCHAR(191) NULL,
  `avatar` VARCHAR(255) NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'applied',
  `created_at` DATETIME NOT NULL,
  `created_by` INT NULL,
  `updated_at` DATETIME NULL,
  `updated_by` INT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_phone` (`phone`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";";

    $tables[] = "CREATE TABLE IF NOT EXISTS `" . db_prefix() . "internship_job_orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT NULL,
  `company_name` VARCHAR(191) NULL,
  `title` VARCHAR(191) NOT NULL,
  `industry` VARCHAR(191) NULL,
  `description` TEXT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'open',
  `created_at` DATETIME NOT NULL,
  `created_by` INT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";";

    $tables[] = "CREATE TABLE IF NOT EXISTS `" . db_prefix() . "internship_job_order_applicants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_order_id` INT UNSIGNED NOT NULL,
  `application_id` INT UNSIGNED NOT NULL,
  `applied_at` DATETIME NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'applied',
  `note` TEXT NULL,
  `last_status_at` DATETIME NULL,
  `last_status_by` INT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_job_application` (`job_order_id`,`application_id`),
  KEY `idx_job` (`job_order_id`),
  KEY `idx_application` (`application_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_internship_pro_applicant_job` FOREIGN KEY (`job_order_id`) REFERENCES `" . db_prefix() . "internship_job_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_internship_pro_applicant_application` FOREIGN KEY (`application_id`) REFERENCES `" . db_prefix() . "internship_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";";

    $tables[] = "CREATE TABLE IF NOT EXISTS `" . db_prefix() . "internship_audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rel_type` VARCHAR(50) NOT NULL,
  `rel_id` INT UNSIGNED NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `old_value` MEDIUMTEXT NULL,
  `new_value` MEDIUMTEXT NULL,
  `staff_id` INT NULL,
  `ip` VARCHAR(64) NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rel` (`rel_type`,`rel_id`),
  KEY `idx_staff` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";";

    foreach ($tables as $sql) {
        $CI->db->query($sql);
    }

    // ---- Schema upgrades (idempotent) ----
    // Applications: process tracking
    if (!$CI->db->field_exists('process_stage', db_prefix() . 'internship_applications')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "internship_applications` ADD `process_stage` VARCHAR(50) NULL AFTER `status`");
    }
    if (!$CI->db->field_exists('process_updated_at', db_prefix() . 'internship_applications')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "internship_applications` ADD `process_updated_at` DATETIME NULL AFTER `process_stage`");
    }
    if (!$CI->db->field_exists('process_updated_by', db_prefix() . 'internship_applications')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "internship_applications` ADD `process_updated_by` INT NULL AFTER `process_updated_at`");
    }

    // Workflow steps per application
    $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "internship_application_steps` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `application_id` INT UNSIGNED NOT NULL,
  `step_key` VARCHAR(50) NOT NULL,
  `step_label` VARCHAR(191) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `sort_order` INT NOT NULL DEFAULT 0,
  `updated_at` DATETIME NULL,
  `updated_by` INT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_app_step` (`application_id`,`step_key`),
  KEY `idx_app` (`application_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_internship_pro_step_app` FOREIGN KEY (`application_id`) REFERENCES `" . db_prefix() . "internship_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");

    //
    $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "internship_partner_schools` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_code` VARCHAR(100) NOT NULL,
  `school_name` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `datecreated` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_school_code` (`school_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "internship_job_order_schools` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_order_id` INT UNSIGNED NOT NULL,
  `school_id` INT UNSIGNED NOT NULL,
  `school_code` VARCHAR(100) DEFAULT NULL,
  `school_name` VARCHAR(255) DEFAULT NULL,
  `sent_at` DATETIME NULL,
  `sent_by` INT UNSIGNED DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_job_school` (`job_order_id`, `school_id`),
  KEY `idx_job_order_id` (`job_order_id`),
  KEY `idx_school_id` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    
    // Options (status labels)
    if (get_option('internship_pro_installed') != '1') {
        add_option('internship_pro_installed', '1');
    }
}

