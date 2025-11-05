<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Module Name: Internship Management
 * Description: Quản lý học sinh Internship tại Nhật Bản (có ảnh, tiến độ, hồ sơ và cảnh báo sắp về nước).
 * Author: IFK Group
 * Version: 1.1
 */

define('INTERNSHIP_MODULE_NAME', 'internship_management');

register_activation_hook(INTERNSHIP_MODULE_NAME, 'internship_management_activate');
register_uninstall_hook(INTERNSHIP_MODULE_NAME, 'internship_management_uninstall');

// ✅ Đặt priority = 20 để load sau Perfex core menu
hooks()->add_action('admin_init', 'internship_management_admin_init', 20);

/**
 * Kích hoạt module
 */
function internship_management_activate()
{
    require_once(__DIR__ . '/install.php');
    internship_management_install();
}

/**
 * Gỡ cài đặt module
 */
function internship_management_uninstall()
{
    require_once(__DIR__ . '/uninstall.php');
    internship_management_uninstall_run();
}

/**
 * Khởi tạo menu và quyền
 */
function internship_management_admin_init()
{
    $capabilities = [
        'view'   => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities(INTERNSHIP_MODULE_NAME, $capabilities, _l('Internship Management'));

    if (has_permission('internship_management', '', 'view')) {

        $CI = &get_instance();

        // 🔹 Chỉ tạo menu nếu chưa tồn tại để tránh re-render nhiều lần
        if (!isset($CI->app_menu->menus['internship_management'])) {

            // ===== MENU CHÍNH =====
            $CI->app_menu->add_sidebar_menu_item('internship_management', [
                'slug'     => 'internship_management',
                'name'     => 'Quản lý Internship Nhật Bản',
                'icon'     => 'fa fa-graduation-cap',
                'position' => 10,
            ]);

            // ===== MENU CON: DANH SÁCH HỌC SINH =====
            $CI->app_menu->add_sidebar_children_item('internship_management', [
                'slug'     => 'internship_management_list',
                'name'     => 'Danh sách học sinh',
                'href'     => admin_url('internship_management'),
                'icon'     => 'fa fa-users',
            ]);

            // ===== MENU CON: BÁO CÁO TỔNG HỢP =====
            $CI->app_menu->add_sidebar_children_item('internship_management', [
                'slug'     => 'internship_management_report',
                'name'     => 'Báo cáo tổng hợp',
                'href'     => admin_url('internship_management/report'),
                'icon'     => 'fa fa-bar-chart',
            ]);

            // ===== MENU CON: NHẮC SẮP VỀ NƯỚC (tùy chọn) =====
            $CI->app_menu->add_sidebar_children_item('internship_management', [
                'slug'     => 'internship_management_notify',
                'name'     => 'Nhắc sắp về nước',
                'href'     => admin_url('internship_management/notify'),
                'icon'     => 'fa fa-bell',
            ]);
        }
    }
}
