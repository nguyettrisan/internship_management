<?php defined('BASEPATH') or exit('No direct script access allowed');

function internship_management_install()
{
    $CI = &get_instance();
    if (!$CI->db->table_exists(db_prefix() . 'internship_students')) {
        $CI->db->query("
            CREATE TABLE `" . db_prefix() . "internship_students` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `full_name` VARCHAR(255),
                `email` VARCHAR(255),
                `phone` VARCHAR(50),
                `address` TEXT,
                `university` VARCHAR(255),
                `company_name` VARCHAR(255),
                `recruitment_date` DATE,
                `entry_date` DATE,
                `return_date` DATE,
                `photo` VARCHAR(255),
                `status` VARCHAR(50) DEFAULT 'Chuẩn bị hồ sơ',
                `progress_note` TEXT,
                `documents` LONGTEXT,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    // tạo thư mục upload
    $path = FCPATH . 'modules/internship_management/uploads/';
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        file_put_contents($path . '.htaccess', "Options -Indexes\n");
    }
}
