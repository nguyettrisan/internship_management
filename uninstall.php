<?php defined('BASEPATH') or exit('No direct script access allowed');

function internship_management_install() {
    $CI = &get_instance();
    $CI->load->dbforge();

    // Tạo bảng chính lưu thông tin học sinh + ảnh + mốc thời gian
    if (!$CI->db->table_exists(db_prefix().'internship_students')) {
        $sql = "
        CREATE TABLE `".db_prefix()."internship_students` (
          `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `full_name` VARCHAR(255) NOT NULL,
          `email` VARCHAR(255) DEFAULT NULL,
          `phone` VARCHAR(50) DEFAULT NULL,
          `address` TEXT DEFAULT NULL,
          `university` VARCHAR(255) DEFAULT NULL,
          `company_name` VARCHAR(255) DEFAULT NULL,
          `recruitment_date` DATE DEFAULT NULL,
          `entry_date` DATE DEFAULT NULL,
          `return_date` DATE DEFAULT NULL,
          `photo` VARCHAR(255) DEFAULT NULL,
          `status` VARCHAR(50) DEFAULT 'Chuẩn bị hồ sơ',
          `progress_note` TEXT DEFAULT NULL,
          `documents` LONGTEXT DEFAULT NULL, -- JSON danh sách file đính kèm nộp cục
          `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` DATETIME NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $CI->db->query($sql);
    }

    // Thư mục uploads riêng
    $uploadPath = FCPATH.'modules/'.INTERNSHIP_MODULE_NAME.'/uploads/';
    if (!is_dir($uploadPath)) {
        @mkdir($uploadPath, 0755, true);
        @file_put_contents($uploadPath.'.htaccess', "Options -Indexes\n");
    }

    // Tạo email template (nếu chưa có)
    if (!class_exists('\modules\internship_management\Internship_return_notice_template')) {
        require_once(__DIR__.'/'.'internship_management.php');
    }
    \modules\internship_management\register_email_templates();
}
