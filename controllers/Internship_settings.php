<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Internship_settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!is_admin()) {
            access_denied('Internship Settings');
        }

        $this->load->model('internship_management/Im_audit_log_model', 'im_audit');
        $this->load->model('internship_management/Internship_mail_model', 'im_mail');
        $this->im_mail->ensure_tables();
    }

    /**
     * Settings page (General + Email tabs + Audit)
     * - Multiple forms: distinguish by hidden "form_type"
     */
    public function index()
    {
        $data['title'] = 'Cài đặt Internship Japan';

        /* ===================== AUDIT LOGS ===================== */
        $page   = max(1, (int)$this->input->get('log_page'));
        $limit  = 50;
        $offset = ($page - 1) * $limit;

        $filters = [
            'q'        => (string)$this->input->get('log_q'),
            'action'   => (string)$this->input->get('log_action'),
            'rel_type' => (string)$this->input->get('log_rel_type'),
            'rel_id'   => (int)$this->input->get('log_rel_id'),
            'staff_id' => (int)$this->input->get('log_staff_id'),
        ];

        $data['audit_logs']  = $this->im_audit->get_all($filters, $limit, $offset);
        $data['audit_total'] = $this->im_audit->count_all($filters);
        $data['audit_page']  = $page;
        $data['audit_limit'] = $limit;

        /* ===================== EMAIL DATA ===================== */
        $data['email_templates'] = $this->im_mail->get_templates(['include_inactive' => 1]);
        $data['email_logs']      = $this->im_mail->get_logs(200);

        /* ===================== LOAD OPTIONS ===================== */
        $data['smtp_host']        = get_option('intern_smtp_host') ?: get_option('ifk_smtp_host');
        $data['smtp_port']        = get_option('intern_smtp_port') ?: get_option('ifk_smtp_port');
        $data['smtp_user']        = get_option('intern_smtp_user') ?: get_option('ifk_smtp_user');
        $data['smtp_pass']        = get_option('intern_smtp_pass') ?: get_option('ifk_smtp_pass');
        $data['smtp_secure']      = get_option('intern_smtp_secure') ?: get_option('ifk_smtp_secure');

        $data['sender_name']      = get_option('intern_sender_name') ?: get_option('ifk_sender_name');
        $data['sender_email']     = get_option('intern_sender_email') ?: get_option('ifk_sender_email');

        $data['brand_logo']       = get_option('intern_brand_logo');
        $data['brand_color']      = get_option('intern_brand_color') ?: '#00325a';
        $data['background_color'] = get_option('intern_background_color') ?: '#96bc17';

        $data['auto_email_entry']  = (int)get_option('intern_auto_email_entry');
        $data['auto_email_return'] = (int)get_option('intern_auto_email_return');
        $data['auto_email_survey'] = (int)get_option('intern_auto_email_survey');

        // GOOGLE AI (Gemini)
        $data['google_api_key']   = get_option('intern_google_api_key');
        $data['google_ai_model']  = get_option('intern_google_ai_model');

        // GOOGLE TRANSLATE API
        $data['google_translate_api_key'] = get_option('intern_google_translate_api_key');

        /* ===================== HANDLE POST ===================== */
        if ($this->input->method() === 'post') {
            $form_type = (string)$this->input->post('form_type');

            if ($form_type === 'mail_settings') {
                $this->save_mail_settings_internal();
                set_alert('success', 'Đã lưu cài đặt mail.');
                redirect(admin_url('internship_management/internship_settings?tab=email&sub=mail'));
            }

            if ($form_type === 'template') {
                $this->save_template_internal();
                redirect(admin_url('internship_management/internship_settings?tab=email&sub=templates'));
            }

            // Default: general settings (AI/Translate/whatever you already use)
            $this->save_general_internal();
            set_alert('success', 'Đã lưu cài đặt Internship Japan');
            redirect(admin_url('internship_management/internship_settings'));
        }

        $this->load->view('internship_management/settings/settings_page', $data);
    }

    

    /* ============================================================
        PING: Test API (Gemini) - legacy/simple GET endpoint for UI
        GET: /internship_settings/test_api
        Output: HTML <span>...</span>
    ============================================================ */
   // =============================================
// TEST GOOGLE AI (Gemini) - PING (as old file 4)
// =============================================
public function test_api()
{
    $api_key = get_option('intern_google_api_key');

    if (!$api_key) {
        echo "<span class='text-danger'>❌ Chưa nhập Google AI API Key</span>";
        return;
    }

    // Old way: list models (no model/content needed)
    $url = 'https://generativelanguage.googleapis.com/v1/models?key=' . rawurlencode($api_key);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($curl);
    $err      = curl_error($curl);
    $code     = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($err) {
        echo "<span class='text-danger'>❌ CURL lỗi: " . html_escape($err) . "</span>";
        return;
    }

    if ($code >= 200 && $code < 300 && strpos((string)$response, 'models') !== false) {
        echo "<span class='text-success'>✅ Google AI API hoạt động</span>";
    } else {
        echo "<span class='text-danger'>❌ Google AI API lỗi (HTTP {$code})</span>";
    }
}

    /* ============================================================
        PING: Test Translate (Google Translate v2) - legacy/simple GET endpoint
        GET: /internship_settings/test_translate_api
        Output: HTML <span>...</span>
    ============================================================ */
    public function test_translate_api()
    {
        if (!is_admin()) {
            access_denied('Internship Settings');
        }

        if ($this->input->method() !== 'get') {
            show_404();
        }

        $apiKey = trim((string)get_option('intern_google_translate_api_key'));
        if ($apiKey === '') {
            echo '<span class="text-danger">✖ Chưa cấu hình Google Translate API Key</span>';
            return;
        }

        $url = 'https://translation.googleapis.com/language/translate/v2?key=' . rawurlencode($apiKey);
        $payload = [
            'q'      => '会社',   // sample
            'source' => 'ja',
            'target' => 'vi',
            'format' => 'text',
        ];

        $resp = $this->_curl_json_raw($url, $payload);
        if (!$resp['ok']) {
            echo '<span class="text-danger">✖ Google Dịch lỗi</span>';
            return;
        }

        $translated = '';
        if (isset($resp['data']['data']['translations'][0]['translatedText'])) {
            $translated = html_entity_decode((string)$resp['data']['data']['translations'][0]['translatedText'], ENT_QUOTES, 'UTF-8');
        }

        echo '<span class="text-success">✔ Google Dịch OK: ' . html_escape($translated) . '</span>';
    }
/* ============================================================
        AJAX: Test SMTP
    ============================================================ */
    

    /* ============================================================
        AJAX: TEST AI (Google Gemini)
        POST: content
    ============================================================ */
    public function test_ai()
    {
        if (!is_admin()) {
            access_denied('Internship Settings');
        }
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $content = trim((string)$this->input->post('content', true));
        if ($content === '') {
            echo json_encode(['ok' => false, 'message' => 'Thiếu nội dung test AI.']);
            return;
        }

        $apiKey = trim((string)get_option('intern_google_api_key'));
        $model  = trim((string)get_option('intern_google_ai_model'));
        if ($apiKey === '') {
            echo json_encode(['ok' => false, 'message' => 'Chưa cấu hình Google AI API Key.']);
            return;
        }
        if ($model === '') $model = 'gemini-1.5-flash';

        // Gemini Generative Language API (v1beta)
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($apiKey);

        $payload = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [['text' => $content]],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.2,
            ],
        ];

        $resp = $this->_curl_json_raw($url, $payload);

        if (!$resp['ok']) {
            echo json_encode(['ok' => false, 'message' => 'Gọi Gemini thất bại', 'error' => $resp['error'], 'raw' => $resp['raw']]);
            return;
        }

        echo json_encode(['ok' => true, 'data' => $resp['data']]);
    }

    /* ============================================================
        AJAX: TEST TRANSLATION (Google Translate v2)
        POST: text
    ============================================================ */
    public function test_translation()
    {
        if (!is_admin()) {
            access_denied('Internship Settings');
        }
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $text = trim((string)$this->input->post('text', true));
        if ($text === '') {
            echo json_encode(['ok' => false, 'message' => 'Thiếu nội dung cần dịch.']);
            return;
        }

        $apiKey = trim((string)get_option('intern_google_translate_api_key'));
        if ($apiKey === '') {
            echo json_encode(['ok' => false, 'message' => 'Chưa cấu hình Google Translate API Key.']);
            return;
        }

        $url = 'https://translation.googleapis.com/language/translate/v2?key=' . rawurlencode($apiKey);
        $payload = [
            'q'      => $text,
            'source' => 'ja',
            'target' => 'vi',
            'format' => 'text',
        ];

        $resp = $this->_curl_json_raw($url, $payload);

        if (!$resp['ok']) {
            echo json_encode(['ok' => false, 'message' => 'Gọi Translate thất bại', 'error' => $resp['error'], 'raw' => $resp['raw']]);
            return;
        }

        $translated = '';
        if (isset($resp['data']['data']['translations'][0]['translatedText'])) {
            $translated = html_entity_decode((string)$resp['data']['data']['translations'][0]['translatedText'], ENT_QUOTES, 'UTF-8');
        }

        echo json_encode(['ok' => true, 'translated' => $translated, 'data' => $resp['data']]);
    }

public function test_smtp()
    {
        if (!is_admin()) {
            access_denied('Internship Settings');
        }

        if ($this->input->method() !== 'post') {
            show_404();
        }

        // Always JSON for AJAX
        header('Content-Type: application/json; charset=utf-8');

        $to = trim((string)$this->input->post('to', true));
        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'ok'      => false,
                'message' => 'Email nhận không hợp lệ.',
                'csrf'    => $this->security->get_csrf_hash(),
            ]);
            exit;
        }

        $res = $this->im_mail->send_test_email($to);

        // Normalize response
        if (!is_array($res)) {
            $res = ['ok' => (bool)$res, 'message' => $res ? 'Đã gửi email test.' : 'Gửi email test thất bại.'];
        }
        if (!isset($res['ok'])) $res['ok'] = false;
        if (!isset($res['message'])) $res['message'] = $res['ok'] ? 'Đã gửi email test.' : 'Gửi email test thất bại.';

        $res['csrf'] = $this->security->get_csrf_hash();

        echo json_encode($res);
        exit;
    }

    /* ============================================================
        TEMPLATE actions (internal)
    ============================================================ */
    private function save_template_internal()
    {
        $id = (int)$this->input->post('id');
        $payload = [
            'name'      => $this->input->post('name', true),
            'code'      => $this->input->post('code', true),
            'subject'   => $this->input->post('subject', true),
            'content'   => $this->input->post('content', false),
            'is_active' => $this->input->post('is_active') ? 1 : 0,
        ];

        $res = $this->im_mail->upsert_template($id, $payload);
        if ($res['ok']) set_alert('success', $res['message']);
        else set_alert('danger', $res['message']);
    }

    public function delete_template($id)
    {
        if (!is_admin()) {
            access_denied('Internship Settings');
        }
        $res = $this->im_mail->delete_template((int)$id);
        if ($res) set_alert('success', 'Đã xoá mẫu.');
        else set_alert('warning', 'Không thể xoá mẫu.');
        redirect(admin_url('internship_management/internship_settings?tab=email&sub=templates'));
    }

    /* ============================================================
        Save mail settings (internal)
    ============================================================ */
    private function save_mail_settings_internal()
    {
        // Snapshot old (audit)
        $old = [
            'smtp_host' => get_option('intern_smtp_host'),
            'smtp_port' => get_option('intern_smtp_port'),
            'smtp_user' => get_option('intern_smtp_user'),
            'smtp_pass' => get_option('intern_smtp_pass'),
            'smtp_secure' => get_option('intern_smtp_secure'),
            'sender_name' => get_option('intern_sender_name'),
            'sender_email' => get_option('intern_sender_email'),
            'brand_logo' => get_option('intern_brand_logo'),
            'brand_color' => get_option('intern_brand_color'),
            'background_color' => get_option('intern_background_color'),
            'auto_email_entry' => get_option('intern_auto_email_entry'),
            'auto_email_return' => get_option('intern_auto_email_return'),
            'auto_email_survey' => get_option('intern_auto_email_survey'),
        ];

        update_option('intern_smtp_host',   $this->input->post('smtp_host', true));
        update_option('intern_smtp_port',   $this->input->post('smtp_port', true));
        update_option('intern_smtp_user',   $this->input->post('smtp_user', true));
        update_option('intern_smtp_pass',   $this->input->post('smtp_pass', true));
        update_option('intern_smtp_secure', $this->input->post('smtp_secure', true));

        update_option('intern_sender_name',  $this->input->post('sender_name', true));
        update_option('intern_sender_email', $this->input->post('sender_email', true));

        update_option('intern_brand_color',      $this->input->post('brand_color', true));
        update_option('intern_background_color', $this->input->post('background_color', true));

        update_option('intern_auto_email_entry',  $this->input->post('auto_email_entry') ? 1 : 0);
        update_option('intern_auto_email_return', $this->input->post('auto_email_return') ? 1 : 0);
        update_option('intern_auto_email_survey', $this->input->post('auto_email_survey') ? 1 : 0);

        // Upload logo
        if (!empty($_FILES['brand_logo']['name'])) {
            $logo = $this->upload_logo();
            if ($logo) {
                update_option('intern_brand_logo', $logo);
            }
        }

        // Keep backward compatibility (old keys)
        $compat = [
            'ifk_smtp_host'   => get_option('intern_smtp_host'),
            'ifk_smtp_port'   => get_option('intern_smtp_port'),
            'ifk_smtp_secure' => get_option('intern_smtp_secure'),
            'ifk_smtp_user'   => get_option('intern_smtp_user'),
            'ifk_smtp_pass'   => get_option('intern_smtp_pass'),
            'ifk_sender_name' => get_option('intern_sender_name'),
            'ifk_sender_email'=> get_option('intern_sender_email'),
        ];
        foreach ($compat as $k => $v) update_option($k, $v);

        $new = [
            'smtp_host' => get_option('intern_smtp_host'),
            'smtp_port' => get_option('intern_smtp_port'),
            'smtp_user' => get_option('intern_smtp_user'),
            'smtp_pass' => get_option('intern_smtp_pass'),
            'smtp_secure' => get_option('intern_smtp_secure'),
            'sender_name' => get_option('intern_sender_name'),
            'sender_email' => get_option('intern_sender_email'),
            'brand_logo' => get_option('intern_brand_logo'),
            'brand_color' => get_option('intern_brand_color'),
            'background_color' => get_option('intern_background_color'),
            'auto_email_entry' => get_option('intern_auto_email_entry'),
            'auto_email_return' => get_option('intern_auto_email_return'),
            'auto_email_survey' => get_option('intern_auto_email_survey'),
        ];

        foreach (['smtp_pass'] as $k) {
            if (isset($old[$k]) && $old[$k] !== '') $old[$k] = '***';
            if (isset($new[$k]) && $new[$k] !== '') $new[$k] = '***';
        }

        $this->im_audit->add('settings', 0, 'mail_settings_updated', 'Cập nhật cài đặt mail Internship', $old, $new);
    }

    /* ============================================================
        Save general settings (internal) - keep your current logic
    ============================================================ */
    private function save_general_internal()
    {
        $old = [
            'google_api_key' => get_option('intern_google_api_key'),
            'google_ai_model' => get_option('intern_google_ai_model'),
            'google_translate_api_key' => get_option('intern_google_translate_api_key'),
        ];

        update_option('intern_google_api_key',  $this->input->post('google_api_key', true));
        update_option('intern_google_ai_model', $this->input->post('google_ai_model', true));
        update_option('intern_google_translate_api_key', $this->input->post('google_translate_api_key', true));

        $new = [
            'google_api_key' => get_option('intern_google_api_key'),
            'google_ai_model' => get_option('intern_google_ai_model'),
            'google_translate_api_key' => get_option('intern_google_translate_api_key'),
        ];
        foreach (['google_api_key','google_translate_api_key'] as $k) {
            if (isset($old[$k]) && $old[$k] !== '') $old[$k] = '***';
            if (isset($new[$k]) && $new[$k] !== '') $new[$k] = '***';
        }

        $this->im_audit->add('settings', 0, 'settings_updated', 'Cập nhật cài đặt Internship', $old, $new);
    }

    /* ============================================================
        Upload logo
    ============================================================ */
    private function upload_logo()
    {
        $path = FCPATH . 'modules/internship_management/uploads/logo/';
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }

        $config['upload_path']   = $path;
        $config['allowed_types'] = 'jpg|jpeg|png|svg';
        $config['max_size']      = 2048;
        $config['file_name']     = 'brand_logo_' . time();

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('brand_logo')) {
            log_message('error', 'UPLOAD LOGO ERROR: ' . $this->upload->display_errors());
            return false;
        }

        return 'modules/internship_management/uploads/logo/' . $this->upload->data('file_name');
    }

    /* ============================================================
        CURL JSON helper (no external deps)
    ============================================================ */
    private function _curl_json_raw($url, $payload)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT        => 30,
        ]);

        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            return ['ok'=>false,'error'=>$err ?: 'curl_error','raw'=>null,'code'=>$code,'data'=>null];
        }

        $data = json_decode($raw, true);
        if ($code < 200 || $code >= 300) {
            return ['ok'=>false,'error'=>$data ?: $raw,'raw'=>$raw,'code'=>$code,'data'=>$data];
        }

        return ['ok'=>true,'error'=>null,'raw'=>$raw,'code'=>$code,'data'=>$data];
    }
public function get_template_ajax()
{
    if (!is_admin()) access_denied('Internship Settings');
    if ($this->input->method() !== 'post') show_404();

    $this->output->set_content_type('application/json');

    $id = (int)$this->input->post('id');
    if ($id <= 0) {
        $this->output->set_output(json_encode(['ok'=>false,'message'=>'ID không hợp lệ']));
        return;
    }

    $tpl = $this->im_mail->get_template($id);
    if (!$tpl) {
        $this->output->set_output(json_encode(['ok'=>false,'message'=>'Không tìm thấy mẫu']));
        return;
    }

    // chuẩn hoá field active
    $active = 1;
    if (isset($tpl->is_active)) $active = (int)$tpl->is_active;
    else if (isset($tpl->active)) $active = (int)$tpl->active;
    else if (isset($tpl->enabled)) $active = (int)$tpl->enabled;

    $this->output->set_output(json_encode([
        'ok' => true,
        'data' => [
            'id'      => (int)$tpl->id,
            'name'    => (string)$tpl->name,
            'code'    => (string)$tpl->code,
            'subject' => (string)$tpl->subject,
            'content' => (string)$tpl->content,
            'is_active' => $active ? 1 : 0,
        ]
    ]));
}
}
