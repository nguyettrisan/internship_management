<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('im_audit_log')) {
    /**
     * Ghi log audit cho module Internship
     * @param string $rel_type  ví dụ: 'student','job_order','template','settings','mail','cron'
     * @param int    $rel_id
     * @param string $action    ví dụ: 'student_created','student_updated','email_sent'
     * @param string $message   mô tả tiếng Việt
     * @param mixed  $old_data  array/object/string
     * @param mixed  $new_data  array/object/string
     */
    function im_audit_log($rel_type, $rel_id, $action, $message = '', $old_data = null, $new_data = null)
    {
        $CI = &get_instance();
        $CI->load->model('internship_management/Im_audit_log_model', 'im_audit');

        $staff_id = function_exists('get_staff_user_id') ? (int)get_staff_user_id() : 0;

        // Chuẩn hoá JSON
        $old = is_string($old_data) ? $old_data : json_encode($old_data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $new = is_string($new_data) ? $new_data : json_encode($new_data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

        // Chặn JSON "null"
        if ($old === 'null') $old = null;
        if ($new === 'null') $new = null;

        // add(rel_type, rel_id, action, note, old_data, new_data)
        $CI->im_audit->add((string)$rel_type, (int)$rel_id, (string)$action, (string)$message, $old, $new);
        return true;
    }
}