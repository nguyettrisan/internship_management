<?php
/*
Module Name: Internship Management
Description: Quản lý Internship Japan (IFK)
Version: 1.0.1
Requires at least: 3.0.0
Author: IFK
*/
defined('BASEPATH') or exit('No direct script access allowed');

define('INTERNSHIP_MODULE_NAME', 'internship_management');

register_activation_hook(INTERNSHIP_MODULE_NAME, 'internship_management_activate');
register_uninstall_hook(INTERNSHIP_MODULE_NAME, 'internship_management_uninstall');

function internship_management_activate()
{
    require_once(__DIR__ . '/install.php');
    internship_management_install();
}

function internship_management_uninstall()
{
    require_once(__DIR__ . '/uninstall.php');
    internship_management_uninstall_run();
}

// Đăng ký menu
hooks()->add_action('admin_init', 'internship_management_menu');
hooks()->add_action('app_admin_footer', 'internship_management_load_list_state_asset');

function internship_management_menu()
{
    $CI = &get_instance();

    if (!has_permission('internship_management', '', 'view')) {
        return;
    }

    // MENU CHA – QL Intern Japan
    $CI->app_menu->add_sidebar_menu_item('internship_japan', [
        'name'      => 'QL Intern Japan',
        'icon'      => 'fa fa-graduation-cap',
        'position'  => 15,
        'collapsed' => true,
    ]);

    // 1. Nhập Đơn Tuyển
    $CI->app_menu->add_sidebar_children_item('internship_japan', [
        'slug' => 'internship_job_orders',
        'name' => 'Nhập Đơn Tuyển',
        'href' => admin_url('internship_management/internship_job_orders'),
        'icon' => 'fa fa-file-text',
    ]);

    // 2. Ứng Tuyển
    $CI->app_menu->add_sidebar_children_item('internship_japan', [
        'slug' => 'internship_apply',
        'name' => 'HS Ứng Viên',
        'href' => admin_url('internship_management/internship_applications'),
        'icon' => 'fa fa-user-plus',
    ]);

    // 3. Lịch Công Việc
    $CI->app_menu->add_sidebar_children_item('internship_japan', [
        'slug' => 'internship_calendar',
        'name' => 'Lịch Công Việc',
        'href' => admin_url('internship_management/internship_calendar'),
        'icon' => 'fa fa-calendar',
    ]);
// 5. DS SV Tại Nhật
    $CI->app_menu->add_sidebar_children_item('internship_japan', [
        'slug' => 'internship_management_list',
        'name' => 'DS SV Tại Nhật',
        'href' => admin_url('internship_management'),
        'icon' => 'fa fa-users',
    ]);
    // 4. Báo Cáo Tuyển Dụng
$CI->app_menu->add_sidebar_children_item('internship_japan', [
    'slug' => 'internship_report_center',
    'name' => 'Báo Cáo Tổng Hợp',
    'icon' => 'fa fa-chart-line',
    'href' => admin_url('internship_management/reports'),
]);

// Báo Cáo Quản Trị
$CI->app_menu->add_sidebar_children_item('internship_japan', [
    'slug' => 'internship_management_report',
    'name' => 'Báo Cáo Quản Trị',
    'href' => admin_url('internship_management/management_report'),
    'icon' => 'fa fa-chart-pie',
]);


     // 7. Gửi Email
$CI->app_menu->add_sidebar_children_item('internship_japan', [
    'slug' => 'internship_send_email',
    'name' => 'Gửi Email',
    'href' => admin_url('internship_management/internship_mail/send_mail'),
    'icon' => 'fa fa-paper-plane',
]);




$CI->app_menu->add_sidebar_children_item('internship_japan', [
    'slug' => 'internship_survey_templates',
    'name' => 'Khảo Sát',
    'href' => admin_url('internship_management/internship_survey/templates'),
    'icon' => 'fa fa-list-alt',
]);

$CI->app_menu->add_sidebar_children_item('internship_japan', [
    'slug' => 'school_portal',
    'name' => 'Đăng Nhập Đối Tác',
    'href' => site_url('school_portal/dashboard'),
    'icon' => 'fa fa-university',
]);




    // 13. Cài Đặt Intern
    $CI->app_menu->add_sidebar_children_item('internship_japan', [
        'slug' => 'internship_settings',
        'name' => 'Cài Đặt',
        'href' => admin_url('internship_management/internship_settings'),
        'icon' => 'fa fa-cog',
    ]);
}

// Đăng ký quyền cho module để hiển thị trong mục "Quyền hạn"
hooks()->add_filter('staff_permissions', 'internship_management_staff_permissions');

function internship_management_staff_permissions($permissions)
{
    $permissions['internship_management'] = [
        'name'         => 'Quản lý Internship', // tên hiển thị trong UI Quyền hạn
        'capabilities' => [
            'view'   => 'Xem',
            'create' => 'Tạo',
            'edit'   => 'Sửa',
            'delete' => 'Xóa',
        ],
    ];

    return $permissions;
}

function internship_management_load_list_state_asset()
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/admin/internship_management') === false) {
        return;
    }

    echo '<script src="' . module_dir_url(INTERNSHIP_MODULE_NAME, 'assets/js/im_list_state.js') . '?v=2.0.0"></script>';
}