<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * ROUTES PUBLIC – INVOICE VIEW
 * Ưu tiên đặt ở đầu để tránh bị override
 */
$route['invoice/(:any)']    = 'internship_management/Internship_invoice_public/view/$1';
$route['invoice-id/(:num)'] = 'internship_management/Internship_invoice_public/view_online/$1';

/**
 * ROUTES ADMIN (CRM)
 */
$route['internship_management/Internship_applications']                 = 'internship_management/Internship_applications/index';
$route['internship_management/Internship_applications/create']          = 'internship_management/Internship_applications/create';
$route['internship_management/Internship_applications/edit/(:num)']     = 'internship_management/Internship_applications/edit/$1';
$route['internship_management/Internship_applications/view_ajax/(:num)'] = 'internship_management/Internship_applications/view_ajax/$1';

// Inline update status (AJAX)
$route['internship_management/Internship_applications/update_status_ajax/(:num)']
    = 'internship_management/Internship_applications/update_status_ajax/$1';

/**
 * ROUTES PUBLIC – FORM KHẢO SÁT
 */
$route['survey_form/(:num)/(:num)']       = 'internship_management/survey_form/index/$1/$2';
$route['survey_form/index/(:num)/(:num)'] = 'internship_management/survey_form/index/$1/$2';

/**
 * ROUTES ADMIN – SURVEY
 */
$route['admin/internship_management/internship_survey/ai_analysis/(:num)']
    = 'internship_management/internship_survey/ai_analysis/$1';
$route['admin/internship_management/internship_survey/export_excel/(:num)']
    = 'internship_management/internship_survey/export_excel/$1';

/**
 * STUDENT CLIENT (ADMIN)
 * Map /admin/internship_management/student_client/... to module controller
 */
$route['admin/internship_management/student_client'] = 'internship_management/student_client/index';
$route['admin/internship_management/student_client/index'] = 'internship_management/student_client/index';
$route['admin/internship_management/student_client/view/(:num)'] = 'internship_management/student_client/view/$1';
$route['admin/internship_management/student_client/push_crm_client/(:num)'] = 'internship_management/student_client/push_crm_client/$1';

// Fallback cho các method khác
$route['admin/internship_management/student_client/(:any)'] = 'internship_management/student_client/$1';
$route['admin/internship_management/student_client/(:any)/(:num)'] = 'internship_management/student_client/$1/$2';


// Nếu file routes.php của bạn đã có sẵn, chỉ cần thêm đúng dòng dưới (KHÔNG duplicate):
$route['admin/internship_management/student_client/print_a4/(:num)']
    = 'internship_management/student_client/print_a4/$1';
$route['admin/internship_management/internship_applications/print'] = 'internship_management/internship_applications/print';
$route['admin/internship_management/internship_applications/print/(:num)'] = 'internship_management/internship_applications/print/$1';
// Student profile (Student Client) by STUDENT ID
$route['admin/internship_management/student_client/view/(:num)'] = 'internship_management/student_client/view/$1';

// (Optional) keep ajax endpoint explicit if you use it anywhere
$route['admin/internship_management/student_client/view_ajax/(:num)'] = 'internship_management/student_client/view_ajax/$1';
// Internship mail routes
$route['admin/internship_management/internship_mail/send_mail'] = 'internship_management/internship_mail/send_mail';
$route['admin/internship_management/internship_mail/do_send_students'] = 'internship_management/internship_mail/do_send_students';
$route['admin/internship_management/internship_mail/do_send_job'] = 'internship_management/internship_mail/do_send_job';

$route['admin/internship_management/internship_mail/ajax_template/(:num)'] = 'internship_management/internship_mail/ajax_template/$1';
$route['admin/internship_management/internship_mail/ajax_search_students'] = 'internship_management/internship_mail/ajax_search_students';
$route['admin/internship_management/internship_mail/ajax_recipients_by_job'] = 'internship_management/internship_mail/ajax_recipients_by_job';
$route['admin/internship_management/internship_mail/ajax_preview'] = 'internship_management/internship_mail/ajax_preview';

$route['admin/internship_management/internship_mail/email_templates'] = 'internship_management/internship_mail/email_templates';
$route['admin/internship_management/internship_mail/save_template'] = 'internship_management/internship_mail/save_template';
$route['admin/internship_management/internship_mail/delete_template/(:num)'] = 'internship_management/internship_mail/delete_template/$1';

$route['admin/internship_management/internship_mail/email_logs'] = 'internship_management/internship_mail/email_logs';

$route['admin/internship_management/internship_mail/email_settings'] = 'internship_management/internship_mail/email_settings';
$route['admin/internship_management/internship_mail/save_settings'] = 'internship_management/internship_mail/save_settings';

//
$route['school_portal']                        = 'internship_management/school_portal/index';
$route['school_portal/login']                  = 'internship_management/school_portal/login';
$route['school_portal/logout']                 = 'internship_management/school_portal/logout';
$route['school_portal/dashboard']              = 'internship_management/school_portal/dashboard';
$route['school_portal/students']               = 'internship_management/school_portal/students';
$route['school_portal/student/(\d+)']          = 'internship_management/school_portal/student/$1';
$route['school_portal/calendar']               = 'internship_management/school_portal/calendar';
$route['school_portal/export_csv']             = 'internship_management/school_portal/export_csv';
$route['school_portal/job_orders']             = 'internship_management/school_portal/job_orders';
$route['school_portal/job_order/(\d+)']        = 'internship_management/school_portal/job_order/$1';
$route['school_portal/print_job_order/(\d+)']  = 'internship_management/school_portal/print_job_order/$1';
$route['school_portal/forgot_password']        = 'internship_management/school_portal/forgot_password';
$route['school_portal/reset_password/(:any)']  = 'internship_management/school_portal/reset_password/$1';
$route['school_portal/captcha']                = 'internship_management/school_portal/captcha';

