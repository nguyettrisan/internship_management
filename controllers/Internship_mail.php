<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Internship_mail extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('internship_management/Internship_mail_model');
            $this->Internship_mail_model->ensure_tables();
            $this->load->model('internship_management/Im_audit_log_model', 'im_audit');
 $this->load->helper('internship_management/im_audit');
 if ($this->input->method() === 'post') {

    $post = $this->input->post(NULL, true);

    // lọc dữ liệu nhạy cảm
    foreach (['password','pass','smtp_pass','api_key','token'] as $k) {
        if (isset($post[$k])) $post[$k] = '***';
    }

    im_audit_log(
        'http',
        0,
        'http_post',
        'POST vào Internship Applications',
        null,
        [
            'url' => current_url(),
            'staff_id' => get_staff_user_id(),
            'post' => $post
        ]
    );
}

        }

    /**
     * Main send mail screen (2 tabs)
     */
    public function send_mail()
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied('internship_management');
        }

        $data = [];
        $data['title'] = 'Gửi Email Internship';

        $data['templates']   = $this->Internship_mail_model->get_templates(['is_active' => 1]);
        $data['job_orders']  = $this->Internship_mail_model->get_job_orders_for_dropdown();
        // Preload a small list as fallback (Select2 may init later)
        $quick              = $this->Internship_mail_model->get_students_quick_list(200);
        $tokens             = $this->Internship_mail_model->get_token_catalog();

        // Backward/forward compatible keys (nhiều view đời cũ dùng tên khác nhau)
        $data['students']       = $quick;
        $data['students_quick'] = $quick;
        $data['tokens']         = $tokens;
        $data['token_catalog']  = $tokens;

        // Demo data for preview (server-side token render)
        $data['demo'] = $this->Internship_mail_model->get_demo_data();

        $this->load->view('internship_management/mail/send_mail', $data);
    }

    /* ============================================================
        AJAX endpoints
    ============================================================ */

    public function ajax_template($id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $tpl = $this->Internship_mail_model->get_template((int)$id);
        if (!$tpl) {
            echo json_encode(['ok' => false, 'message' => 'Template not found']);
            return;
        }

        echo json_encode([
            'ok' => true,
            'data' => [
                'id'      => (int)$tpl->id,
                'name'    => $tpl->name,
                'code'    => $tpl->code,
                'subject' => $tpl->subject,
                'html'    => $tpl->content,
            ],
        ]);
    }

    public function ajax_search_students()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $q = (string)$this->input->get('q');
        $page = (int)$this->input->get('page');
        $res = $this->Internship_mail_model->search_students($q, $page, 20);
        echo json_encode($res);
    }

    public function ajax_recipients_by_job()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $job_id = (int)($this->input->post('job_order_id') ?: $this->input->post('job_id') ?: $this->input->get('job_order_id') ?: $this->input->get('job_id'));
        $rows = $this->Internship_mail_model->get_students_by_job_order($job_id);

        echo json_encode(['ok' => true, 'recipients' => $rows]);
    }

    /**
     * Render preview with demo data or specific student/job.
     * POST: html, subject(optional), student_id(optional), job_id(optional)
     */
    public function ajax_preview()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $html = $this->input->post('html', false);
        $subject = $this->input->post('subject', true);
        $student_id = (int)$this->input->post('student_id');
        $job_id = (int)$this->input->post('job_id');

        $ctx = $this->Internship_mail_model->build_context($student_id ?: null, $job_id ?: null);
        $out = $this->Internship_mail_model->render_tokens((string)$html, $ctx);
        $sub = $this->Internship_mail_model->render_tokens((string)$subject, $ctx);

        echo json_encode(['ok' => true, 'subject' => $sub, 'html' => $out, 'ctx' => $ctx]);
    }

    /* ============================================================
        Actions: send
    ============================================================ */

    public function do_send_students()
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied('internship_management');
        }

        if ($this->input->method() !== 'post') {
            show_404();
        }

        $student_ids = $this->input->post('student_ids');
        $single_id   = (int)$this->input->post('student_id');
        $subject = $this->input->post('subject', true);
        // view đời cũ có thể post content
        $html = $this->input->post('html', false);
        if ($html === null || $html === '') {
            $html = $this->input->post('content', false);
        }
        $dry_run = (int)$this->input->post('dry_run') === 1;
        $manual_emails = (string)$this->input->post('manual_emails', true);

        $confirm = strtoupper(trim((string)$this->input->post('confirm_send')));
        if (!$dry_run && $confirm !== 'SEND') {
            set_alert('warning', 'Vui lòng gõ SEND để xác nhận gửi thật.');
            redirect(admin_url('internship_management/internship_mail/send_mail#tab_students'));
        }


        $student_ids = is_array($student_ids) ? array_map('intval', $student_ids) : [];
        if ($single_id > 0) {
            $student_ids[] = $single_id;
        }
        $student_ids = array_values(array_filter(array_unique($student_ids)));

        if (empty($student_ids)) {
            set_alert('warning', 'Vui lòng chọn sinh viên.');
            redirect(admin_url('internship_management/internship_mail/send_mail#tab=students'));
        }

        if (trim((string)$html) === '') {
            set_alert('warning', 'Nội dung email (HTML) không được để trống.');
            redirect(admin_url('internship_management/internship_mail/send_mail#tab=students'));
        }

        $result = $this->Internship_mail_model->send_to_students($student_ids, $subject, $html, $dry_run, $manual_emails);

        if ($result['ok']) {
            set_alert('success', $result['message']);
        } else {
            set_alert('danger', $result['message']);
        }

        redirect(admin_url('internship_management/internship_mail/send_mail#tab=students'));
    }

    public function do_send_job()
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied('internship_management');
        }

        if ($this->input->method() !== 'post') {
            show_404();
        }

        $job_id = (int)($this->input->post('job_id') ?: $this->input->post('job_order_id'));
        $picked = $this->input->post('recipient_ids');
        if (!is_array($picked)) {
            // view/JS đời khác có thể gửi student_ids[]
            $picked = $this->input->post('student_ids');
        }
        $manual_emails = (string)$this->input->post('manual_emails', true);

        $subject = $this->input->post('subject', true);
        $html = $this->input->post('html', false);
        if ($html === null || $html === '') {
            $html = $this->input->post('content', false);
        }

        $dry_run = (int)$this->input->post('dry_run') === 1;

        $confirm = strtoupper(trim((string)$this->input->post('confirm_send')));
        if (!$dry_run && $confirm !== 'SEND') {
            set_alert('warning', 'Vui lòng gõ SEND để xác nhận gửi thật.');
            redirect(admin_url('internship_management/internship_mail/send_mail#tab_job'));
        }

        $recipient_ids = is_array($picked) ? array_map('intval', $picked) : [];
        $recipient_ids = array_values(array_filter(array_unique($recipient_ids)));


        if (trim((string)$html) === '') {
            set_alert('warning', 'Nội dung email (HTML) không được để trống.');
            redirect(admin_url('internship_management/internship_mail/send_mail#tab=job'));
        }

        $result = $this->Internship_mail_model->send_by_job_order($job_id, $recipient_ids, $manual_emails, $subject, $html, $dry_run);

        if ($result['ok']) {
            set_alert('success', $result['message']);
        } else {
            set_alert('danger', $result['message']);
        }

        redirect(admin_url('internship_management/internship_mail/send_mail#tab=job'));
    }

    /* ============================================================
        Templates
    ============================================================ */

    public function email_templates()
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied('internship_management');
        }

        $data = [];
        $data['title'] = 'Mẫu Email Internship';
        $data['templates'] = $this->Internship_mail_model->get_templates();
        $this->load->view('internship_management/mail/email_templates', $data);
    }

    public function save_template()
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied('internship_management');
        }

        if ($this->input->method() !== 'post') {
            show_404();
        }

        $id = (int)$this->input->post('id');
        $payload = [
            'name'      => $this->input->post('name', true),
            'code'      => $this->input->post('code', true),
            'subject'   => $this->input->post('subject', true),
            'content'   => $this->input->post('content', false),
            'is_active' => $this->input->post('is_active') ? 1 : 0,
        ];

        $res = $this->Internship_mail_model->upsert_template($id, $payload);
        if ($res['ok']) set_alert('success', $res['message']);
        else set_alert('danger', $res['message']);

        redirect(admin_url('internship_management/internship_mail/email_templates'));
    }

    public function delete_template($id)
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied('internship_management');
        }

        $res = $this->Internship_mail_model->delete_template((int)$id);
        if ($res) set_alert('success', 'Đã xoá mẫu.');
        else set_alert('warning', 'Không thể xoá mẫu.');

        redirect(admin_url('internship_management/internship_mail/email_templates'));
    }

    /* ============================================================
        Logs
    ============================================================ */

    public function email_logs()
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied('internship_management');
        }

        $data = [];
        $data['title'] = 'Lịch sử Mail Internship';
        $data['logs']  = $this->Internship_mail_model->get_logs(200);
        $this->load->view('internship_management/mail/email_logs', $data);
    }

    /* ============================================================
        Settings
    ============================================================ */

    public function email_settings()
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied('internship_management');
        }

        $data = [];
        $data['title'] = 'Cài đặt Mail Internship';
        $this->load->view('internship_management/mail/email_settings', $data);
    }

    public function save_settings()
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied('internship_management');
        }

        if ($this->input->method() !== 'post') {
            show_404();
        }

        $keys = [
            'ifk_smtp_host',
            'ifk_smtp_port',
            'ifk_smtp_secure',
            'ifk_smtp_user',
            'ifk_smtp_pass',
            'ifk_sender_name',
            'ifk_sender_email',
        ];

        foreach ($keys as $k) {
            $v = $this->input->post($k, true);
            update_option($k, $v);
        }

        set_alert('success', 'Đã lưu cài đặt.');
        redirect(admin_url('internship_management/internship_mail/email_settings'));
    }
}
