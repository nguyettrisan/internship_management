<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once(FCPATH . 'modules/internship_management/libraries/PHPWord/src/PhpWord/Autoloader.php');
\PhpOffice\PhpWord\Autoloader::register();

use PhpOffice\PhpWord\IOFactory;

class Internship_job_orders extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // MODEL ỨNG VIÊN
        $this->load->model(
            'internship_management/internship_applications_model',
            'applications_model'
        );

        // MODEL ĐƠN TUYỂN
        $this->load->model(
            'internship_management/internship_job_orders_model',
            'job_orders_model'
        );

        // MODEL LỊCH (để đồng bộ phỏng vấn / nhập cảnh)
        $this->load->model(
            'internship_management/internship_calendar_model',
            'calendar_model'
            
        );
        $this->load->model('internship_management/Im_audit_log_model', 'im_audit');
 $this->load->helper('internship_management/im_audit');
 $this->load->helper('internship_management/job_order_status');
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

    private function im_app_table()
    {
        return db_prefix() . 'internship_applications';
    }

    public function update_application_state()
{
    // Standardized AJAX endpoint used by BOTH views
    header('Content-Type: application/json; charset=utf-8');

    if (!is_admin()
        && !has_permission('internship_management', '', 'edit')
        && !has_permission('internship_job_orders', '', 'edit')
        && !has_permission('internship_applications', '', 'edit')) {
        echo json_encode([
            'success'   => false,
            'message'   => 'Access denied',
            'csrf_hash' => $this->security->get_csrf_hash(),
        ]);
        exit;
    }

    if (!$this->input->is_ajax_request()) {
        // keep strict to avoid abuse
        echo json_encode([
            'success'   => false,
            'message'   => 'Invalid request',
            'csrf_hash' => $this->security->get_csrf_hash(),
        ]);
        exit;
    }

    $id    = (int)$this->input->post('id');
    $field = trim((string)$this->input->post('field'));
    $value = (string)$this->input->post('value');

    if ($id <= 0) {
        echo json_encode([
            'success'   => false,
            'message'   => 'Invalid id',
            'csrf_hash' => $this->security->get_csrf_hash(),
        ]);
        exit;
    }

    $table = $this->im_app_table();

    // Ensure record exists
    $row = $this->db->where('id', $id)->get($table)->row_array();
    if (!$row) {
        echo json_encode([
            'success'   => false,
            'message'   => 'Not found',
            'csrf_hash' => $this->security->get_csrf_hash(),
        ]);
        exit;
    }

    // Detect columns (support both legacy + new)
    $fields = $this->db->list_fields($table);
    $has_status   = in_array('status', $fields, true);
    $has_dossier  = in_array('dossier_progress', $fields, true);
    $has_iv       = in_array('interview_result', $fields, true);
    $has_updated  = in_array('dateupdated', $fields, true);

    $this->load->helper('internship_management/internship_status');

    $normalize_progress = function($v) {
        return im_normalize_dossier_progress($v);
    };
    
    $normalize_iv = function($v) {
        return im_normalize_interview_result($v);
    };
    
    $sync_from_progress = function($progress) {
        return im_sync_from_dossier_progress($progress);
    };
    
    $sync_from_iv = function($iv, $current_progress) {
        return im_sync_from_interview_result($iv, $current_progress);
    };

    // Apply update
    $update = [];
    if ($has_updated) {
        $update['dateupdated'] = date('Y-m-d H:i:s');
    }

    $current_progress = $has_dossier ? ($row['dossier_progress'] ?? '') : ($row['status'] ?? '');

    if ($field === 'dossier_progress' || $field === 'status') {
        $progress = $normalize_progress($value);
        [$progress, $iv] = $sync_from_progress($progress);

        if ($has_dossier) {
            $update['dossier_progress'] = $progress;
        }
        if ($has_status) {
            // keep legacy status in sync as well
            $update['status'] = $progress;
        }
        if ($has_iv) {
            $update['interview_result'] = $iv;
        }
    } elseif ($field === 'interview_result') {
        $iv = $normalize_iv($value);
        [$progress, $iv] = $sync_from_iv($iv, $current_progress);

        if ($has_dossier) {
            $update['dossier_progress'] = $progress;
        }
        if ($has_status) {
            $update['status'] = $progress;
        }
        if ($has_iv) {
            $update['interview_result'] = $iv;
        }
    } else {
        echo json_encode([
            'success'   => false,
            'message'   => 'Invalid field',
            'csrf_hash' => $this->security->get_csrf_hash(),
        ]);
        exit;
    }

    if (empty($update)) {
        echo json_encode([
            'success'   => false,
            'message'   => 'No fields to update',
            'csrf_hash' => $this->security->get_csrf_hash(),
        ]);
        exit;
    }

    $this->db->where('id', $id)->update($table, $update);

    // Return authoritative values
    $new = $this->db->where('id', $id)->get($table)->row_array();

    $out_progress = $has_dossier ? ($new['dossier_progress'] ?? ($update['dossier_progress'] ?? '')) : ($new['status'] ?? ($update['status'] ?? ''));
    $out_iv       = $has_iv ? ($new['interview_result'] ?? ($update['interview_result'] ?? '')) : '';

    echo json_encode([
        'success'   => true,
        'message'   => 'Đã cập nhật',
        'data'      => [
            'id'              => $id,
            'status'          => $has_status ? ($new['status'] ?? ($update['status'] ?? '')) : $out_progress,
            'dossier_progress'=> $out_progress,
            'interview_result'=> $out_iv,
        ],
        'csrf_hash' => $this->security->get_csrf_hash(),
    ]);
    exit;
}




    /* ============================================================
       LIST ĐƠN TUYỂN + FILTER
    ============================================================ */
    public function index()
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied();
        }

        $data['title'] = 'Danh sách Đơn Tuyển';

        // Lấy filter từ GET
        /*$filters = [
            'status' => $this->input->get('status'),
            'major'  => $this->input->get('major'),
            //'year'   => $this->input->get('year'),
            'year'   => ($this->input->get('year') === null ? date('Y') : $this->input->get('year')),
            'month'  => $this->input->get('month'),
            'search' => $this->input->get('search'),
        ];*/
        
        // Lấy filter từ GET
			/*$yearGet  = $this->input->get('year', true);
			$monthGet = $this->input->get('month', true);

			$filters = [
				'status' => trim((string)$this->input->get('status', true)),
				'major'  => trim((string)$this->input->get('major', true)),
				'year'   => ($yearGet === null || $yearGet === '') ? (int)date('Y') : (int)$yearGet,
				'month'  => ($monthGet === null || $monthGet === '') ? (int)date('n') : (int)$monthGet,
				'search' => trim((string)$this->input->get('search', true)),
			];*/
			
			// Lấy filter từ GET
            $getParams = $this->input->get(NULL, true);
            $hasFilterRequest = is_array($getParams) && count($getParams) > 0;
            
            $yearGet  = $this->input->get('year', true);
            $monthGet = $this->input->get('month', true);
            $statusGet = $this->input->get('status', true);
            $majorGet  = $this->input->get('major', true);
            $searchGet = $this->input->get('search', true);
            
            $filters = [
                'status' => trim((string)$statusGet),
                'major'  => trim((string)$majorGet),
                'year'   => $hasFilterRequest
                    ? (($yearGet === null) ? '' : trim((string)$yearGet))
                    : (int)date('Y'),
                'month'  => $hasFilterRequest
                    ? (($monthGet === null) ? '' : trim((string)$monthGet))
                    : (int)date('n'),
                'search' => trim((string)$searchGet),
            ];
			

        // Lấy danh sách đơn theo filter
        $orders = $this->job_orders_model->get_all($filters);

        // Nếu view cần thêm status_text cho chi tiết (safe, không ảnh hưởng list)
        /*foreach ($orders as &$o) {
            $o['status_text'] = $this->translate_status($o['status'] ?? '');
        }*/
        
        foreach ($orders as &$o) {
            $meta = im_job_order_status_meta($o['status'] ?? '');
            $o['status_text']  = $meta['vi'];
            $o['status_label'] = $meta['vi'];
            $o['status_jp']    = $meta['jp'];
            $o['status_color'] = $meta['color'];
        }
        unset($o);

        /*$data['orders']  = $orders;
        $data['filters'] = $filters;

        // Danh sách trạng thái cho filter (lấy từ model để đồng bộ)
        $data['status_list'] = $this->job_orders_model->get_status_list();

        $this->load->view('internship_management/job_orders/list', $data);*/
        
        $data['orders']  = $orders;
        $data['filters'] = $filters;
        
        // Danh sách trạng thái cho filter (lấy từ model để đồng bộ)
        $data['status_list'] = $this->job_orders_model->get_status_list();
        
        // Danh sách năm lấy động từ dữ liệu thật trong DB
        $data['years'] = $this->job_orders_model->get_filter_years('entry_date');
        
        $this->load->view('internship_management/job_orders/list', $data);
    }

    /* ============================================================
       AJAX: UPDATE STATUS (manual workflow)
       POST: status
    ============================================================ */
    public function update_status($id)
    {
        if (!is_admin()
        && !has_permission('internship_management', '', 'edit')
        && !has_permission('internship_job_orders', '', 'edit')
        && !has_permission('internship_applications', '', 'edit')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        $id = (int)$id;
        if ($id <= 0) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Invalid id']);
            exit;
        }

        $status = trim((string)$this->input->post('status'));
$status = $this->job_orders_model->normalize_status($status);

// Update + log + manual_override=1 (nếu có cột)
$ok  = $this->job_orders_model->update_status_with_log($id, $status, 'manual');
$job = $ok ? $this->job_orders_model->get($id) : null;


        if ($ok && $job) {
            // Some installs may not include calendar sync method; avoid fatal error.
            if (isset($this->calendar_model) && method_exists($this->calendar_model, 'sync_job_order_events')) {
                $this->ie_sync_calendar($job);
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => (bool)$ok,
            'message' => $ok ? 'Đã cập nhật trạng thái.' : 'Không cập nhật được.',
            'status'  => $job['status'] ?? $status,
        ]);
        exit;
    }

    /**
     * Inline update dates on job order list (interview/entry/exit/return).
     * Endpoint: admin/internship_management/internship_job_orders/update_dates
     * POST: id, interview_date, entry_date, exit_date, return_date
     */
     private function im_clean_inline_date($value)
    {
        if ($value === null) {
            return null;
        }
    
        $value = trim((string)$value);
    
        if ($value === '') {
            return null;
        }
    
        // Nếu là datetime thì lấy phần ngày
        if (preg_match('/^(\d{4}-\d{1,2}-\d{1,2})\s+/', $value, $m)) {
            $value = $m[1];
        }
    
        // 2026年4月10日 -> 2026-4-10
        $value = preg_replace('/年|月/u', '-', $value);
        $value = str_replace('日', '', $value);
        $value = str_replace('.', '-', $value);
    
        $year = null;
        $month = null;
        $day = null;
    
        // YYYY-MM-DD hoặc YYYY/MM/DD
        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $value, $m)) {
            $year  = (int)$m[1];
            $month = (int)$m[2];
            $day   = (int)$m[3];
        }
        // DD/MM/YYYY hoặc DD-MM-YYYY
        elseif (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $value, $m)) {
            $day   = (int)$m[1];
            $month = (int)$m[2];
            $year  = (int)$m[3];
        } else {
            return null;
        }
    
        $minYear = 2000;
        $maxYear = (int)date('Y') + 20;
    
        if ($year < $minYear || $year > $maxYear) {
            return null;
        }
    
        if (!checkdate($month, $day, $year)) {
            return null;
        }
    
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
     
    public function update_dates()
    {
        if (!is_staff_logged_in()) {
            ajax_access_denied();
        }

        $id = (int) $this->input->post('id');
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Missing id']);
            die();
        }

        
        $tblOrders = db_prefix() . 'internship_job_orders';
        $oldRow = $this->db->where('id', $id)->get($tblOrders)->row_array();
$allowed = ['interview_date', 'entry_date', 'exit_date', 'return_date'];
        $data    = [];

        /*foreach ($allowed as $field) {
            if ($this->input->post($field, true) !== null) {
                $val = trim((string) $this->input->post($field, true));
                // Normalize empty => NULL
                $data[$field] = ($val === '') ? null : $val;
            }
        }*/
        
        foreach ($allowed as $field) {
            if ($this->input->post($field, true) !== null) {
                $data[$field] = $this->im_clean_inline_date($this->input->post($field, true));
            }
        }

        // Add audit fields if exist
        if ($this->db->field_exists('updated_at', db_prefix() . 'internship_job_orders')) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        if ($this->db->field_exists('updated_by', db_prefix() . 'internship_job_orders')) {
            $data['updated_by'] = get_staff_user_id();
        }

        // Only update existing columns
        $safe = [];
        foreach ($data as $k => $v) {
            if ($this->db->field_exists($k, db_prefix() . 'internship_job_orders')) {
                $safe[$k] = $v;
            }
        }

        if (empty($safe)) {
            echo json_encode(['success' => false, 'message' => 'No valid fields']);
            die();
        }

        $this->db->where('id', $id);
        $ok = $this->db->update(db_prefix() . 'internship_job_orders', $safe);

        if (!$ok) {
            echo json_encode(['success' => false, 'message' => 'DB update failed']);
            die();
        }

        // return refreshed row
        $this->db->where('id', $id);
        $row = $this->db->get(db_prefix() . 'internship_job_orders')->row_array();

        // Log date changes
        if ($oldRow) {
            $this->job_orders_model->log_field_changes($id, $oldRow, $safe, 'manual');
        }

        // Auto-update status (chỉ khi manual_override=0, không đè terminal, không kéo lùi)
        if ($row) {
            $this->job_orders_model->auto_update_status_if_needed($row);
            // refresh row after possible auto update
            $row = $this->db->where('id', $id)->get(db_prefix() . 'internship_job_orders')->row_array();
        }

        echo json_encode(['success' => true, 'data' => $row]);
        die();
    }



    /* ============================================================
       CREATE JOB ORDER + AUTO CALENDAR EVENT
    ============================================================ */
    public function create()
    {
        $data = [
            'title'               => 'Tạo Đơn Tuyển – Nhật & Việt',
            'job_ai'              => null,
            'job'                 => null,
            'status_list'         => $this->job_orders_model->get_status_list(),
            //
            'partner_schools'     => $this->job_orders_model->get_partner_schools(),
            'selected_school_ids' => [],
        ];

        
// 0) TRANSLATE VI (SERVER-SIDE) - KHÔNG LƯU DB, chỉ fill các trường *_vi từ JP bằng Google
if ($this->input->post('translate_vi')) {
    $post = $this->input->post();

    // bỏ các field control
    unset($post['translate_vi'], $post['save_job_order']);

    // Fill VI từ JP (không phụ thuộc JS)
    $post = $this->ie_google_fill_vi_from_jp($post);

    // Đổ lại lên form
    $data['job_ai'] = $post;

    $this->load->view('internship_management/job_orders/create', $data);
    return;
}

// 1) SAVE FORM (NHẤN LƯU)
        if ($this->input->post('save_job_order')) {

            $post = $this->input->post();
            
            //unset($post['save_job_order']);
            /*$school_ids = array_map('intval', (array) $this->input->post('school_ids'));
            unset($post['save_job_order'], $post['school_ids']);*/
            
            // Danh sách trường đã tích chọn
            $school_ids = array_map('intval', (array) $this->input->post('school_ids'));
            
            // Trường mới nhập trực tiếp ở mục 8
            $new_school_name = trim((string)$this->input->post('school_name_new', true));
            
            unset($post['save_job_order'], $post['school_ids'], $post['school_name_new']);
            
            if ($new_school_name !== '') {
                $new_school_id = $this->job_orders_model->ensure_partner_school($new_school_name);
            
                if ($new_school_id > 0) {
                    $school_ids[] = $new_school_id;
                }
            }
            
            $school_ids = array_values(array_unique(array_filter(array_map('intval', $school_ids))));
            
            // HƯỚNG 1 (PRO): nếu VI trống mà JP có -> tự copy (không ghi đè VI đã nhập)
            $post = $this->autofill_post_vi_from_jp($post);
            
             //
             if (!empty($school_ids) && (empty($post['status']) || $post['status'] === 'received')) {
                $post['status'] = 'sent_to_schools';
            }
            
            // Mặc định trạng thái
            if (empty($post['status'])) {
                $post['status'] = 'received';
            }

            // LƯU DATABASE
            $id = $this->job_orders_model->add($post);

            if ($id) {
                
                //
                $this->job_orders_model->sync_job_order_schools($id, $school_ids);
                
                // ⭐ TỰ ĐỘNG SINH LỊCH CHO ĐƠN TUYỂN
                $job = $this->job_orders_model->get($id);
                $this->ie_sync_calendar($job);

                //set_alert('success', 'Đã tạo Đơn Tuyển & sinh lịch tự động!');
                set_alert('success', 'Đã tạo Đơn Tuyển, đồng bộ lịch và gửi cho trường thành công!');
            } else {
                set_alert('danger', 'Lỗi! Không thể tạo Đơn Tuyển.');
            }

            redirect(admin_url('internship_management/internship_job_orders'));
            return;
        }

        // 2) UPLOAD + PARSE FILE WORD (AI)
        if (!empty($_FILES['jp_docx']['name'])) {

            $upload_dir = FCPATH . 'modules/internship_management/uploads/job_orders/';
            if (!is_dir($upload_dir)) {
                @mkdir($upload_dir, 0755, true);
            }

            $filename = uniqid('job_', true) . '.docx';
            $path     = $upload_dir . $filename;

            if (!move_uploaded_file($_FILES['jp_docx']['tmp_name'], $path)) {
                set_alert('danger', 'Không upload được file Word.');
                redirect(admin_url('internship_management/internship_job_orders/create'));
            }

            try {
                $parsed = $this->parse_docx_and_ai($path);
                $parsed = $this->ie_normalize_ai_to_form_jp($parsed);
                $data['job_ai'] = $this->ie_ai_keep_only_jp($parsed);

                file_put_contents(
                    FCPATH . 'debug_final_parsed_job_order.json',
                    json_encode($parsed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                );

                set_alert('success', 'Đã phân tích file Word thành công, vui lòng kiểm tra & lưu.');

            } catch (Exception $e) {

                file_put_contents(
                    FCPATH . 'debug_job_ai_error.txt',
                    '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . PHP_EOL,
                    FILE_APPEND
                );

                set_alert('danger', 'AI lỗi: ' . $e->getMessage());
            }

            @unlink($path); // Xóa file tạm
        }

        $this->load->view('internship_management/job_orders/create', $data);
    }

    /* ============================================================
       EDIT JOB ORDER + AUTO UPDATE LỊCH
    ============================================================ */
    public function edit($id)
    {
        // Anti-spam: rate limit Gemini Word parsing
        if (!$this->im_gemini_rate_limit('im_gemini_word_bucket', 5, 60)) {
            echo json_encode(['success' => false, 'error' => 'Bạn thao tác quá nhanh. Vui lòng đợi 1 phút rồi thử lại.']);
            return;
        }

        $order = $this->job_orders_model->get($id);
        if (!$order) {
            blank_page('Không tìm thấy Đơn Tuyển');
        }

        // NHẤN LƯU
        if ($this->input->post('save_job_order')) {

            $post = $this->input->post();
            
            //unset($post['save_job_order']);
            /*$school_ids = array_map('intval', (array) $this->input->post('school_ids'));
            unset($post['save_job_order'], $post['school_ids']);*/

            // Danh sách trường đã tích chọn
            $school_ids = array_map('intval', (array) $this->input->post('school_ids'));
            
            // Trường mới nhập trực tiếp ở mục 8
            $new_school_name = trim((string)$this->input->post('school_name_new', true));
            
            unset($post['save_job_order'], $post['school_ids'], $post['school_name_new']);
            
            if ($new_school_name !== '') {
                $new_school_id = $this->job_orders_model->ensure_partner_school($new_school_name);
            
                if ($new_school_id > 0) {
                    $school_ids[] = $new_school_id;
                }
            }
            
            $school_ids = array_values(array_unique(array_filter(array_map('intval', $school_ids))));
        
            // HƯỚNG 1 (PRO): nếu VI trống mà JP có -> tự copy (không ghi đè VI đã nhập)
            $post = $this->autofill_post_vi_from_jp($post);
            
            //
            if (!empty($school_ids) && (($post['status'] ?? ($order['status'] ?? 'received')) === 'received')) {
                $post['status'] = 'sent_to_schools';
            }

            if (empty($post['status'])) {
                $post['status'] = $order['status'] ?? 'received';
            }

            $ok = $this->job_orders_model->update($id, $post);

            if ($ok) {
                
                //
                $this->job_orders_model->sync_job_order_schools($id, $school_ids);
                
                // ⭐ TỰ ĐỘNG CẬP NHẬT LỊCH ĐƠN TUYỂN
                $job = $this->job_orders_model->get($id);
                $this->ie_sync_calendar($job);

                set_alert('success', 'Cập nhật thành công & lịch đã được đồng bộ!');
                
            } else {
                set_alert('danger', 'Không thể cập nhật.');
            }

            redirect(admin_url('internship_management/internship_job_orders'));
            return;
        }

        $order['status_text'] = $this->translate_status($order['status'] ?? '');

        $data              = [];
        $data['title']     = 'Cập nhật Đơn Tuyển';
        $data['job']       = $order;
        $data['job_ai']    = null;
        $data['status_list'] = $this->job_orders_model->get_status_list();
        
        //
        $data['partner_schools']    = $this->job_orders_model->get_partner_schools();
        $data['selected_school_ids'] = $this->job_orders_model->get_job_order_school_ids($id);


        $this->load->view('internship_management/job_orders/create', $data);
    }

    /* ============================================================
       DELETE
    ============================================================ */
    /*public function delete($id)
    {
        $ok = $this->job_orders_model->delete($id);
        set_alert($ok ? 'success' : 'danger', $ok ? 'Đã xoá!' : 'Không thể xoá.');
        redirect(admin_url('internship_management/internship_job_orders'));
    }*/
    
    public function delete($id)
    {
        $id = (int)$id;
    
        if ($id <= 0) {
            set_alert('danger', 'ID đơn tuyển không hợp lệ.');
            redirect(admin_url('internship_management/internship_job_orders'));
            return;
        }
    
        try {
            $ok = $this->job_orders_model->delete($id);
    
            if ($ok) {
                set_alert('success', 'Đã xoá đơn tuyển thành công.');
            } else {
                $debug = method_exists($this->job_orders_model, 'get_last_delete_error')
                    ? $this->job_orders_model->get_last_delete_error()
                    : '';
            
                if ($debug !== '') {
                    set_alert('danger', 'Không thể xoá đơn tuyển. Lỗi thật: ' . $debug);
                } else {
                    set_alert('danger', 'Không thể xoá đơn tuyển. Hệ thống đã rollback nhưng model không trả lỗi chi tiết.');
                }
            }
        } catch (Throwable $e) {
            log_message('error', 'Controller delete job order failed. ID=' . $id . ' | Message=' . $e->getMessage());
            set_alert('danger', 'Xoá đơn tuyển thất bại: ' . $e->getMessage());
        } catch (Exception $e) {
            log_message('error', 'Controller delete job order failed. ID=' . $id . ' | Message=' . $e->getMessage());
            set_alert('danger', 'Xoá đơn tuyển thất bại: ' . $e->getMessage());
        }
    
        redirect(admin_url('internship_management/internship_job_orders'));
    }

    /* ============================================================
       DOCX → TEXT (PHPWORD)
    ============================================================ */
    private function parse_docx_to_text($file_path)
    {
        try {
            $phpWord = IOFactory::load($file_path);
        } catch (Exception $e) {
            throw new Exception('Không load được file Word: ' . $e->getMessage());
        }

        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $e) {

                // TABLE
                if ($e instanceof \PhpOffice\PhpWord\Element\Table) {
                    foreach ($e->getRows() as $row) {
                        foreach ($row->getCells() as $cell) {
                            foreach ($cell->getElements() as $ce) {
                                if (method_exists($ce, 'getText')) {
                                    $text .= $ce->getText() . "\n";
                                }
                            }
                        }
                    }
                }

                // PARAGRAPH / TEXT RUN
                if (method_exists($e, 'getText')) {
                    $text .= $e->getText() . "\n";
                }
            }
        }

        $text = trim($text);
        if ($text === '') {
            throw new Exception('Không đọc được văn bản từ Word.');
        }

        file_put_contents(FCPATH . 'debug_job_docx_text.txt', $text);

        return $text;
    }

    /* ============================================================
       GỌI GEMINI: TRÍCH XUẤT JP → JSON PHẲNG
    ============================================================ */
    private function gemini_extract_flat($text)
    {
        $api_key = get_option('intern_google_api_key');
        if (!$api_key) {
            throw new Exception('Thiếu API key (intern_google_api_key).');
        }

        $model = 'gemini-2.5-flash-lite';
        $url   = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

        // Schema JP (khớp với DB)
        $schema_jp = [
            // COMPANY
            'company_name_jp',
            'company_president',
            'address_jp',
            'employee_count',
            'established_year',
            'website',
            'company_phone',

            // JOB
            'job_title',
            'workplace_jp',
            'job_description_jp',

            // REQUIREMENTS
            'quantity_male',
            'quantity_female',
            'quantity_total',
            'gender',
            'age_from',
            'age_to',
            'education',
            'major_jp',
            'japanese_level',
            'english_level',

            // WORK CONDITIONS
            'contract_months',
            'work_days',
            'holidays',
            'working_hours',
            'break_time',
            'overtime',

            // SALARY
            'salary_total',
            'salary_net',
            'tax',
            'insurance',
            'dormitory',
            'utilities',
            'food',
            'bonus',
            'raise_salary',

            // BENEFIT
            'benefit_flight',
            'benefit_other',

            // SCHEDULE
            'interview_date',
            'entry_date',
            'interview_place',
        ];

        $schema_example = json_encode(
            array_fill_keys($schema_jp, ''),
            JSON_UNESCAPED_UNICODE
        );

        $prompt = "
Bạn là hệ thống trích xuất ĐƠN TUYỂN THỰC TẬP SINH NHẬT BẢN.

Hãy TRẢ VỀ DUY NHẤT 1 JSON PHẲNG với các key SAU (không thêm bớt, không đổi tên):

$schema_example

QUY TẮC:
- Không thêm key khác
- Không đổi tên key
- Nếu không có thông tin → để \"\" hoặc 0 (với số)
- Trả JSON phẳng 1 cấp, KHÔNG JSON lồng
- KHÔNG giải thích, KHÔNG markdown, KHÔNG ```json
Chỉ trả JSON thuần 100%.
";

        $payload = [
            'contents' => [[
                'parts' => [
                    ['text' => $prompt],
                    ['text' => '===== NỘI DUNG GỐC ====='],
                    ['text' => $text],
                ],
            ]],
        ];

        $retry = 3;
        $last  = null;

        while ($retry--) {

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_TIMEOUT        => 45,
            ]);

            $res = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                $last = 'CURL: ' . $err;
                if ($retry == 0) {
                    throw new Exception('CURL error: ' . $err);
                }
                usleep(900000);
                continue;
            }

            $json = json_decode($res, true);
            $last = $res;

            if (isset($json['error'])) {

                if (
                    $retry > 0 &&
                    in_array($json['error']['status'], ['UNAVAILABLE', 'RESOURCE_EXHAUSTED'])
                ) {
                    usleep(900000);
                    continue;
                }

                throw new Exception('Gemini error: ' . $json['error']['message']);
            }

            $out = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Loại bỏ ```json ... ```
            $out = trim(preg_replace('/```(json)?|```/i', '', $out));

            // Cắt từ { ... } để tránh text rác
            $start = strpos($out, '{');
            $end   = strrpos($out, '}');
            if ($start !== false && $end !== false) {
                $out = substr($out, $start, $end - $start + 1);
            }

            $data = json_decode($out, true);

            if (!is_array($data)) {
                if ($retry == 0) {
                    file_put_contents(FCPATH . 'debug_job_json_fail.txt', $out);
                    throw new Exception('Không parse được JSON từ AI.');
                }
                usleep(900000);
                continue;
            }

            // Đảm bảo đủ key
            foreach ($schema_jp as $f) {
                if (!isset($data[$f])) {
                    $data[$f] = '';
                }
            }

            file_put_contents(
                FCPATH . 'debug_gemini_extract_jp.json',
                json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            );

            return $data;
        }

        file_put_contents(FCPATH . 'debug_gemini_extract_last.txt', $last);
        throw new Exception('Gemini quá tải sau nhiều lần thử.');
    }

    /* ============================================================
       BỔ SUNG LƯƠNG NẾU AI BỎ QUA (DÙNG TEXT GỐC)
    ============================================================ */
    private function ensure_salary_field($current, $text, $keywords = [])
    {
        $current = trim((string)$current);
        if ($current !== '') {
            return $current;
        }

        if (empty($keywords)) {
            return $current;
        }

        foreach ($keywords as $kw) {
            // Ví dụ: 総支給 230000円 / 総支給：230,000
            $pattern = '/' . preg_quote($kw, '/') . '\s*[:：]?\s*([0-9,\.]+)\s*円?/u';
            if (preg_match($pattern, $text, $m)) {
                return $m[1];
            }
        }

        return $current;
    }

    /* ============================================================
       DỊCH JP → VI (Gemini 2.5 Flash)
    ============================================================ */
    private function translate_jp_to_vi($jp_text)
    {
        $jp_text = trim((string)$jp_text);
        if ($jp_text === '') {
            return null;
        }

        $api_key = get_option('intern_google_api_key');
        if (!$api_key) {
            return null;
        }

        $model = 'gemini-2.5-flash';
        $url   = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

        $prompt = "
Dịch đoạn sau từ TIẾNG NHẬT sang TIẾNG VIỆT.
Chỉ trả về bản dịch, KHÔNG giải thích, KHÔNG markdown, KHÔNG ký tự thừa.

Văn bản:
{$jp_text}
";

        $payload = [
            'contents' => [[
                'role'  => 'user',
                'parts' => [
                    ['text' => $prompt],
                ],
            ]],
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 40,
        ]);

        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return null;
        }

        $json = json_decode($res, true);
        if (isset($json['error'])) {
            return null;
        }

        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$text) {
            return null;
        }

        $text = trim(preg_replace('/```(json)?|```/i', '', $text));

        return trim($text);
    }

    /**
     * Batch translate JP => VI using Gemini.
     * Input: associative array field_key => jp_text
     * Output: associative array field_key => vi_text
     */
    private function translate_jp_to_vi_batch(array $items)
    {
        $has = [];
        foreach ($items as $k => $v) {
            $v = trim((string)$v);
            if ($v !== '') {
                $has[$k] = $v;
            }
        }
        if (empty($has)) {
            return [];
        }

        $api_key = get_option('intern_google_api_key');
        if (!$api_key) {
            return [];
        }

        $model = 'gemini-2.5-flash';
        $url   = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

        $prompt = "\nBạn là công cụ dịch.\n\nYÊU CẦU:\n- Dịch TOÀN BỘ giá trị trong JSON bên dưới từ TIẾNG NHẬT sang TIẾNG VIỆT.\n- GIỮ NGUYÊN key.\n- Trả về DUY NHẤT một JSON hợp lệ, KHÔNG markdown, KHÔNG giải thích.\n- Nếu giá trị là số/URL/email/điện thoại, giữ nguyên.\n\nINPUT_JSON:\n" . json_encode($has, JSON_UNESCAPED_UNICODE) . "\n";

        $payload = [
            'contents' => [[
                'role'  => 'user',
                'parts' => [
                    ['text' => $prompt],
                ],
            ]],
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 60,
        ]);

        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return [];
        }

        $json = json_decode($res, true);
        if (isset($json['error'])) {
            return [];
        }

        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $text = trim((string)$text);
        if ($text === '') {
            return [];
        }

        // Remove fenced blocks if any
        $text = trim(preg_replace('/```(json)?|```/i', '', $text));

        $decoded = json_decode($text, true);
        if (!is_array($decoded)) {
            // Try to extract first {...}
            if (preg_match('/\{[\s\S]*\}/', $text, $m)) {
                $decoded = json_decode($m[0], true);
            }
        }

        if (!is_array($decoded)) {
            return [];
        }

        $result = [];
        foreach ($has as $k => $src) {
            $t = isset($decoded[$k]) ? trim((string)$decoded[$k]) : '';
            $result[$k] = ($t !== '') ? $t : $src;
        }

        return $result;
    }

    /**
     * Generic translate helper (widget): supports jp|vi|en|auto => jp|vi|en.
     * - Fast-path jp->vi reuse translate_jp_to_vi().
     */
    private function gemini_translate_text($text, $source, $target)
    {
        $text = trim((string)$text);
        if ($text === '') {
            return '';
        }

        $source = strtolower(trim((string)$source));
        $target = strtolower(trim((string)$target));
        if ($target === '') {
            $target = 'vi';
        }

        if ($source === 'jp' && $target === 'vi') {
            $t = $this->translate_jp_to_vi($text);
            return $t ? trim((string)$t) : '';
        }

        $api_key = get_option('intern_google_api_key');
        if (!$api_key) {
            return '';
        }

        $model = 'gemini-2.5-flash';
        $url   = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

        $src = ($source === '' || $source === 'auto') ? 'AUTO' : strtoupper($source);
        $dst = strtoupper($target);
        $prompt = "Dịch đoạn sau từ {$src} sang {$dst}.\nChỉ trả về bản dịch, KHÔNG giải thích, KHÔNG markdown, KHÔNG ký tự thừa.\n\nVăn bản:\n{$text}\n";

        $payload = [
            'contents' => [[
                'role'  => 'user',
                'parts' => [
                    ['text' => $prompt],
                ],
            ]],
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 40,
        ]);

        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return '';
        }

        $json = json_decode($res, true);
        if (isset($json['error'])) {
            return '';
        }

        $out = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $out = trim((string)$out);
        if ($out === '') {
            return '';
        }

        $out = trim(preg_replace('/```(json)?|```/i', '', $out));
        return trim($out);
    }

    /**
     * AJAX: dịch nhanh các field JP -> VI (UI widget).
     * POST: fields: { field_vi: jp_text }
     */
    public function ai_translate_fields_legacy()
    {
        $this->ie_check_perm('edit');

        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $fields = $this->input->post('fields');
        if (is_string($fields)) {
            $tmp = json_decode($fields, true);
            if (is_array($tmp)) {
                $fields = $tmp;
            }
        }

        if (!is_array($fields) || empty($fields)) {
            echo json_encode(['success' => false, 'message' => 'Không có dữ liệu để dịch.']);
            return;
        }

        // Safety: limit batch size
        $fields = array_slice($fields, 0, 35, true);

        $translated = $this->translate_jp_to_vi_batch($fields);

        echo json_encode([
            'success' => true,
            'data'    => $translated,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Dịch 1 đoạn text (widget dịch nhỏ ở góc).
     * POST(AJAX): text, source(jp|vi|en|auto), target(jp|vi|en)
     */
    public function ai_translate_text_legacy()
    {
        $this->ie_check_perm('edit');

        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $text   = trim((string)$this->input->post('text'));
        $source = trim((string)$this->input->post('source'));
        $target = trim((string)$this->input->post('target'));

        if ($text === '') {
            echo json_encode(['success' => false, 'message' => 'Nội dung trống.']);
            return;
        }
        if ($target === '') {
            $target = 'vi';
        }

        $translated = $this->gemini_translate_text($text, $source, $target);
        if ($translated === '') {
            echo json_encode(['success' => false, 'message' => 'Không dịch được (Gemini).']);
            return;
        }

        echo json_encode(['success' => true, 'data' => ['text' => $translated]], JSON_UNESCAPED_UNICODE);
    }

    /**
     * HƯỚNG 1 (PRO): JP là nguồn. Nếu field VI trống mà JP có -> copy JP sang VI.
     * Dùng cho submit form (create/edit) để đảm bảo luôn có dữ liệu VI tối thiểu.
     * KHÔNG ghi đè khi user đã nhập VI.
     */
    private function autofill_post_vi_from_jp(array $post)
    {
        $pairs = [
            // COMPANY
            'company_name_jp'   => 'company_name_vi',
            'company_president' => 'company_president_vi',
            'address_jp'        => 'address_vi',
            'employee_count'    => 'employee_count_vi',
            'established_year'  => 'established_year_vi',
            'website'           => 'website_vi',
            'company_phone'     => 'company_phone_vi',
            // JOB
            'job_title'         => 'job_title_vi',
            'workplace_jp'      => 'workplace_vi',
            'job_description_jp'=> 'job_description_vi',
            // REQUIREMENTS
            'quantity_male'     => 'quantity_male_vi',
            'quantity_female'   => 'quantity_female_vi',
            'quantity_total'    => 'quantity_total_vi',
            'age_from'          => 'age_from_vi',
            'age_to'            => 'age_to_vi',
            'education'         => 'education_vi',
            'major_jp'          => 'major_vi',
            'japanese_level'    => 'japanese_level_vi',
            'english_level'     => 'english_level_vi',

            // EXTRA REQUIREMENTS
            'height'             => 'height_vi',
            'weight'             => 'weight_vi',
            'experience'         => 'experience_vi',
            'other_requirements' => 'other_requirements_vi',

            // NOTE: các field chỉ có 1 version (không _vi) sẽ không đưa vào map_translate.
            'height'            => 'height_vi',
            'weight'            => 'weight_vi',
            'experience'        => 'experience_vi',
            'other_requirements'=> 'other_requirements_vi',
            // CONDITIONS
            'contract_months'   => 'contract_months_vi',
            'work_days'         => 'work_days_vi',
            'holidays'          => 'holidays_vi',
            'working_hours'     => 'working_hours_vi',
            'break_time'        => 'break_time_vi',
            'overtime'          => 'overtime_vi',
            // SALARY
            'salary_total'      => 'salary_total_vi',
            'salary_net'        => 'salary_net_vi',
            'tax'               => 'tax_vi',
            'insurance'         => 'insurance_vi',
            'dormitory'         => 'dormitory_vi',
            'utilities'         => 'utilities_vi',
            'food'              => 'food_vi',
            'bonus'             => 'bonus_vi',
            'raise_salary'      => 'raise_salary_vi',
            'benefit_flight'    => 'benefit_flight_vi',
            'benefit_other'     => 'benefit_other_vi',
            // INTERVIEW
            'interview_date'    => 'interview_date_vi',
            'entry_date'        => 'entry_date_vi',
            'interview_place'   => 'interview_place_vi',
        ];

        foreach ($pairs as $jpKey => $viKey) {
            $jpVal = isset($post[$jpKey]) ? trim((string)$post[$jpKey]) : '';
            $viVal = isset($post[$viKey]) ? trim((string)$post[$viKey]) : '';
            if ($viVal === '' && $jpVal !== '') {
                $post[$viKey] = $jpVal;
            }
        }

        // Generic fallback: nếu vẫn còn field *_vi rỗng thì copy từ *_jp hoặc field gốc
        foreach ($post as $k => $v) {
            if (substr($k, -3) !== '_vi') {
                continue;
            }
            $viVal = trim((string)$v);
            if ($viVal !== '') {
                continue;
            }
            $base = substr($k, 0, -3);
            $candJp = $base . '_jp';

            if (isset($post[$candJp]) && trim((string)$post[$candJp]) !== '') {
                $post[$k] = (string)$post[$candJp];
                continue;
            }
            if (isset($post[$base]) && trim((string)$post[$base]) !== '') {
                $post[$k] = (string)$post[$base];
            }
        }

        return $post;
    }

    /* ============================================================
       DOCX → TEXT → GEMINI (JP) → AUTO JP+VI (2 TAB)
    ============================================================ */
    private function parse_docx_and_ai($file_path)
    {
        // 1) TEXT GỐC
        $text = $this->parse_docx_to_text($file_path);

        // 2) JP ONLY (JSON phẳng)
        $jp = $this->gemini_extract_flat($text);
        if (!is_array($jp)) {
            throw new Exception('Gemini không trả JSON JP hợp lệ.');
        }

        // Base data = JP
        $data = $jp;

        // 3) Fallback lương nếu AI bỏ qua
        $data['salary_total'] = $this->ensure_salary_field($data['salary_total'] ?? '', $text, ['総支給', '基本給']);
        $data['salary_net']   = $this->ensure_salary_field($data['salary_net']   ?? '', $text, ['手取り', '控除後']);

        // Chuẩn hoá số lương (chỉ để hiển thị trong form; lúc lưu Model sẽ convert int)
        foreach (['salary_total', 'salary_net', 'tax', 'insurance', 'dormitory', 'utilities'] as $sf) {
            if (!empty($data[$sf])) {
                $num = preg_replace('/\D/', '', (string)$data[$sf]);
                if ($num !== '') {
                    $data[$sf] = (string)((int)$num);
                }
            }
        }

        // 4) AUTO TRANSLATE JP → VI (TEXT FIELD)
        $map_translate = [
            // COMPANY
            'company_name_jp'   => 'company_name_vi',
            'company_president' => 'company_president_vi',
            'address_jp'        => 'address_vi',
            'established_year'  => 'established_year_vi',

            // JOB
            'job_title'         => 'job_title_vi',
            'workplace_jp'      => 'workplace_vi',
            'job_description_jp'=> 'job_description_vi',

            // REQUIREMENTS
            'gender'            => 'gender_vi',
            'education'         => 'education_vi',
            'major_jp'          => 'major_vi',
            'japanese_level'    => 'japanese_level_vi',
            'english_level'     => 'english_level_vi',

            // WORK CONDITIONS
            'work_days'         => 'work_days_vi',
            'holidays'          => 'holidays_vi',
            'working_hours'     => 'working_hours_vi',
            'break_time'        => 'break_time_vi',
            'overtime'          => 'overtime_vi',

            // SALARY (text remark)
            'food'              => 'food_vi',
            'bonus'             => 'bonus_vi',
            'raise_salary'      => 'raise_salary_vi',

            // BENEFIT
            'benefit_flight'    => 'benefit_flight_vi',
            'benefit_other'     => 'benefit_other_vi',

            // SCHEDULE
            'interview_date'    => 'interview_date_vi',
            'entry_date'        => 'entry_date_vi',
            'interview_place'   => 'interview_place_vi',
        ];

        // HƯỚNG 1 (PRO): JP là nguồn. Nếu dịch fail/rỗng -> copy JP sang VI.
        foreach ($map_translate as $jp_key => $vi_key) {
            $src = isset($jp[$jp_key]) ? trim((string)$jp[$jp_key]) : '';
            if ($src === '') {
                $data[$vi_key] = '';
                continue;
            }

            $trans = $this->translate_jp_to_vi($src);
            $trans = is_string($trans) ? trim($trans) : '';

            // ✅ fallback pro: copy JP nếu dịch lỗi
            $data[$vi_key] = ($trans !== '') ? $trans : $src;
        }

        // 5) COPY NUMBER / VALUE JP → VI (không dịch chữ)
        $map_copy = [
            'employee_count'  => 'employee_count_vi',
            'contract_months' => 'contract_months_vi',

            'salary_total'    => 'salary_total_vi',
            'salary_net'      => 'salary_net_vi',
            'tax'             => 'tax_vi',
            'insurance'       => 'insurance_vi',
            'dormitory'       => 'dormitory_vi',
            'utilities'       => 'utilities_vi',

            'company_phone'   => 'company_phone_vi',
            'website'         => 'website_vi',
            'age_from'        => 'age_from_vi',
            'age_to'          => 'age_to_vi',

            'quantity_male'   => 'quantity_male_vi',
            'quantity_female' => 'quantity_female_vi',
            'quantity_total'  => 'quantity_total_vi',
        ];

        foreach ($map_copy as $jp_key => $vi_key) {
            $data[$vi_key] = isset($data[$jp_key]) ? $data[$jp_key] : '';
        }

        // 5.1) SAFEGUARD: nếu field VI nào còn trống mà JP có -> copy JP sang VI
        // (chỉ áp dụng cho các cặp trong map_translate; map_copy đã copy thẳng)
        foreach ($map_translate as $jp_key => $vi_key) {
            $jpVal = isset($data[$jp_key]) ? trim((string)$data[$jp_key]) : '';
            $viVal = isset($data[$vi_key]) ? trim((string)$data[$vi_key]) : '';
            if ($viVal === '' && $jpVal !== '') {
                $data[$vi_key] = $jpVal;
            }
        }

        // 6) ĐẢM BẢO ĐỦ TOÀN BỘ FIELD CHO 2 TAB
        $all_fields = [

            // ================= JP TAB =================
            'company_name_jp','company_president','address_jp',
            'employee_count','established_year','website','company_phone',

            'job_title','workplace_jp','job_description_jp',

            'quantity_male','quantity_female','quantity_total',
            'gender','age_from','age_to','education',
            'major_jp','japanese_level','english_level',

            'contract_months','work_days','holidays','working_hours',
            'break_time','overtime',

            'salary_total','salary_net','tax','insurance',
            'dormitory','utilities','food','bonus','raise_salary',

            'benefit_flight','benefit_other',

            'interview_date','entry_date','interview_place',

            // ================= VI TAB =================
            'company_name_vi','company_president_vi','address_vi',
            'employee_count_vi','established_year_vi','website_vi','company_phone_vi',

            'job_title_vi','workplace_vi','job_description_vi',

            'quantity_male_vi','quantity_female_vi','quantity_total_vi',
            'gender_vi','age_from_vi','age_to_vi','education_vi',
            'major_vi','japanese_level_vi','english_level_vi',

            'contract_months_vi','work_days_vi','holidays_vi','working_hours_vi',
            'break_time_vi','overtime_vi',

            'salary_total_vi','salary_net_vi','tax_vi','insurance_vi',
            'dormitory_vi','utilities_vi','food_vi','bonus_vi','raise_salary_vi',

            'benefit_flight_vi','benefit_other_vi',

            'interview_date_vi','entry_date_vi','interview_place_vi',
        ];

        foreach ($all_fields as $f) {
            if (!isset($data[$f])) {
                $data[$f] = '';
            }
        }

        // Ghi log debug cuối cùng
        file_put_contents(
            FCPATH . 'debug_final_parsed_job_order.json',
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        return $data;
    }

    /* ============================================================
       VIEW DETAIL
    ============================================================ */
    public function view($id)
    {
        $order = $this->job_orders_model->get($id);

        if (!$order) {
            blank_page('Không tìm thấy Đơn Tuyển');
        }

        $order['status_text'] = $this->translate_status($order['status'] ?? '');

        $data['title'] = 'Xem Đơn Tuyển';
        $data['job']   = $order;

        $this->load->view('internship_management/job_orders/view', $data);
    }

    /* ============================================================
       PRINT PAGE
    ============================================================ */
    public function print($id)
    {
        $job = $this->job_orders_model->get($id);
        if (!$job) {
            blank_page('Không tìm thấy đơn tuyển');
        }

        // Tạo QR code – gắn link xem đơn
        $qr_link = admin_url('internship_management/internship_job_orders/view/' . $id);
        $qr_src  = "https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=" . urlencode($qr_link);

        // Trạng thái tiếng Việt
        $job['status_text'] = $this->translate_status($job['status'] ?? '');

        $data['job']    = $job;
        $data['qr_src'] = $qr_src;

        $this->load->view('internship_management/job_orders/print', $data);
    }

    /* ============================================================
       VIEW AJAX (POPUP)
    ============================================================ */
    public function view_ajax($id)
    {
        $job = $this->job_orders_model->get($id);
        if ($job) {
            $job['status_text'] = $this->translate_status($job['status'] ?? '');
        }

        $data['job'] = $job;

        $this->load->view('internship_management/job_orders/view_ajax', $data);
    }

    /* ============================================================
       DANH SÁCH ỨNG VIÊN THEO ĐƠN TUYỂN
    ============================================================ */
    public function applicants($job_id)
    {
        $job = $this->job_orders_model->get($job_id);
        if (!$job) {
            show_404();
        }

        $job['status_text'] = $this->translate_status($job['status'] ?? '');

        $applicants = $this->applications_model->get_by_job_order($job_id);

        $data['job']        = $job;
        $data['applicants'] = $applicants;
        $data['title']      = 'Ứng viên của đơn tuyển';

        $this->load->view('internship_management/job_orders/applicants', $data);
    }

    /* ============================================================
       AJAX UPDATE KẾT QUẢ PHỎNG VẤN ỨNG VIÊN
    ============================================================ */
    public function update_interview_result()
    {
        if (!has_permission('internship_management', '', 'edit')) {
            ajax_access_denied();
        }

        $id     = (int)$this->input->post('id');
        $result = (string)$this->input->post('result');

        if ($id <= 0) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'Invalid id', 'csrf_hash' => $this->security->get_csrf_hash()]);
            return;
        }

        // Delegate to unified endpoint logic: interview_result -> status
        $_POST['field'] = 'interview_result';
        $_POST['value'] = $result;
        $_POST['id']    = $id;

        // Call internal method directly
        $this->update_application_state();
    }

    /* ============================================================
       EXPORT DANH SÁCH ỨNG VIÊN THEO ĐƠN
    ============================================================ */
    public function export_applicants($job_order_id)
    {
        $job = $this->job_orders_model->get($job_order_id);
        if (!$job) {
            blank_page('Đơn tuyển không tồn tại');
        }

        $list = $this->applications_model->get_by_job_order($job_order_id);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=applicants_job_' . $job_order_id . '.xls');

        echo "<table border='1'>
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Giới tính</th>
                    <th>Trường</th>
                    <th>Ngành</th>
                    <th>SĐT</th>
                    <th>Kết quả PV</th>
                    <th>Ngày tạo</th>
                </tr>";

        foreach ($list as $r) {
            echo '<tr>
                    <td>' . $r['id'] . '</td>
                    <td>' . $r['full_name'] . '</td>
                    <td>' . $r['gender'] . '</td>
                    <td>' . $r['school_name'] . '</td>
                    <td>' . $r['major'] . '</td>
                    <td>' . $r['phone_student'] . '</td>
                    <td>' . $r['interview_result'] . '</td>
                    <td>' . $r['datecreated'] . '</td>
                  </tr>';
        }

        echo '</table>';
        exit;
    }

	    /* ============================================================
	       PRO SYNC: ĐỒNG BỘ LỊCH (INTERVIEW/ENTRY) CHO ĐƠN TUYỂN
	       - Nếu calendar model có sync_job_order_events() thì dùng.
	       - Nếu không có, fallback: upsert vào bảng tblinternship_calendar (nếu tồn tại).
	    ============================================================ */
	    private function ie_sync_calendar($job)
	    {
	        if (!$job) {
	            return;
	        }

	        // Ưu tiên dùng model nếu hệ thống có
	        if (isset($this->calendar_model) && method_exists($this->calendar_model, 'sync_job_order_events')) {
	            $this->calendar_model->sync_job_order_events($job);
	            return;
	        }

	        // Fallback: tự đồng bộ vào bảng internship_calendar
	        $this->ie_sync_calendar_fallback($job);
	    }

	    private function ie_sync_calendar_fallback($job)
	    {
	        $tbl = db_prefix() . 'internship_calendar';
	        if (!$this->db->table_exists($tbl)) {
	            return;
	        }

	        $cols = $this->db->list_fields($tbl);
	        $has = function ($c) use ($cols) {
	            return in_array($c, $cols, true);
	        };

	        $jobId = (int)($job->id ?? 0);
	        if ($jobId <= 0) {
	            return;
	        }

	        // Lấy ngày phỏng vấn / ngày nhập cảnh (có thể ở cột *_vi hoặc *_jp)
	        $interviewDate = (string)($job->interview_date_vi ?? $job->interview_date ?? '');
	        $entryDate     = (string)($job->entry_date_vi ?? $job->entry_date ?? '');

	        // Title ưu tiên tiếng Việt
	        $company = trim((string)($job->company_name_vi ?? $job->company_name_jp ?? $job->company_name ?? ''));
	        $jobTitle = trim((string)($job->job_title_vi ?? $job->job_title_jp ?? $job->job_title ?? ''));
	
	        // Upsert 2 event: interview + entry (nếu có)
	        if ($interviewDate !== '' && $interviewDate !== '0000-00-00') {
	            $this->ie_upsert_calendar_event($tbl, $has, $jobId, 'job_interview', $interviewDate, 'Phỏng vấn', $company, $jobTitle);
	        }
	        if ($entryDate !== '' && $entryDate !== '0000-00-00') {
	            $this->ie_upsert_calendar_event($tbl, $has, $jobId, 'job_entry', $entryDate, 'Nhập cảnh', $company, $jobTitle);
	        }
	    }

	    private function ie_upsert_calendar_event($tbl, $has, $jobId, $eventType, $date, $prefix, $company, $jobTitle)
	    {
	        $title = trim($prefix . ': ' . $company . ($jobTitle !== '' ? ' - ' . $jobTitle : ''));
	
	        // Determine column names (best-effort)
	        $cDate  = $has('event_date') ? 'event_date' : ($has('date') ? 'date' : ($has('start_date') ? 'start_date' : ''));
	        $cType  = $has('event_type') ? 'event_type' : ($has('type') ? 'type' : '');
	        $cTitle = $has('title') ? 'title' : ($has('name') ? 'name' : '');
	        $cDesc  = $has('description') ? 'description' : ($has('note') ? 'note' : '');
	
	        if ($cDate === '' || $cType === '' || $cTitle === '') {
	            // schema không đúng kỳ vọng -> không sync để tránh phá dữ liệu
	            return;
	        }

	        $where = [];
	        if ($has('job_order_id')) {
	            $where['job_order_id'] = $jobId;
	        }
	        $where[$cType] = $eventType;

	        $row = null;
	        if (!empty($where)) {
	            $row = $this->db->where($where)->get($tbl)->row();
	        }

	        $data = [
	            $cDate  => $date,
	            $cType  => $eventType,
	            $cTitle => $title,
	        ];
	        if ($cDesc !== '') {
	            $data[$cDesc] = $title;
	        }
	        if ($has('job_order_id')) {
	            $data['job_order_id'] = $jobId;
	        }
	        if ($has('student_id')) {
	            $data['student_id'] = null;
	        }
	        if ($has('staff_id')) {
	            $data['staff_id'] = (int)get_staff_user_id();
	        }
	        if ($has('is_auto')) {
	            $data['is_auto'] = 1;
	        }
	        if ($has('datecreated')) {
	            // Perfex convention
	            $data['datecreated'] = date('Y-m-d H:i:s');
	        }
	        if ($has('created_at')) {
	            $data['created_at'] = date('Y-m-d H:i:s');
	        }

	        if ($row && isset($row->id)) {
	            $this->db->where('id', (int)$row->id)->update($tbl, $data);
	        } else {
	            $this->db->insert($tbl, $data);
        $note_id = (int)$this->db->insert_id();
        if ($note_id > 0 && method_exists($this, 'im_handle_note_attachments')) {
            $this->im_handle_note_attachments($job_order_id, $note_id);
        }
	        }
	    }

    /* ============================================================
       TRANSLATE STATUS ĐƠN TUYỂN → TIẾNG VIỆT
       (Hỗ trợ cả mã cũ & mã mới cho an toàn)
    ============================================================ */
    /*private function translate_status($status)
    {
        $map = [
            // Mặc định
            'received'            => 'Tiếp nhận đơn',

            // Gửi trường
            'sent_schools'        => 'Đã gửi đến trường',
            'sent_to_schools'     => 'Đã gửi đến trường',

            // Có ứng viên
            'has_students'        => 'Đã có ứng viên',
            'has_applicants'      => 'Đã có ứng viên',

            // Phỏng vấn
            'interview_scheduled' => 'Hẹn lịch phỏng vấn',
            'interview_done'      => 'Đã phỏng vấn – chờ kết quả',
            'interview_result'    => 'Đã phỏng vấn – chờ kết quả',

            // Hồ sơ
            'making_documents'    => 'Đang làm hồ sơ',
            'docs_done'           => 'Đã hoàn tất hồ sơ',
            'done_documents'      => 'Đã hoàn tất hồ sơ',

            // COE
            'waiting_coe'         => 'Chờ kết quả COE',
            'got_coe'             => 'Đã có COE – chờ nhập cảnh',
            'coe_done'            => 'Đã có COE – chờ nhập cảnh',

            // Nhập cảnh / hoàn tất
            'waiting_entry'       => 'Chờ nhập cảnh',
            'entry'               => 'Đã nhập cảnh',
            'entered'             => 'Đã nhập cảnh',
            'done'                => 'Đã hoàn tất chương trình',
        ];

        return $map[$status] ?? $status;
    }*/
    
    private function translate_status($status)
    {
        return im_job_order_status_label($status, 'vi');
    }

    // ============================================================
    // AI Translate (Gemini) - used by Translate Widget on Create/Edit
    // ============================================================

    /**
     * Try to read Gemini API key from common places.
     * You can store it in Perfex options as one of:
     * - gemini_api_key
     * - google_gemini_api_key
     * - internship_gemini_api_key
     * Or define ENV: GEMINI_API_KEY
     */
    private function ie_get_gemini_key()
    {
        $keys = [
            'gemini_api_key',
            'google_gemini_api_key',
            'internship_gemini_api_key',
            'google_ai_api_key',
        ];

        foreach ($keys as $k) {
            if (function_exists('get_option')) {
                $v = trim((string) get_option($k));
                if ($v !== '') return $v;
            }
        }

        $env = getenv('GEMINI_API_KEY');
        if ($env) {
            $env = trim((string)$env);
            if ($env !== '') return $env;
        }

        return '';
    }

    /**
     * Call Gemini generateContent. Returns [ok=>bool, text=>string, error=>string]
     */
    private function ie_gemini_translate($sourceText, $fromLang = 'ja', $toLang = 'vi')
    {
        $sourceText = trim((string)$sourceText);
        if ($sourceText === '') {
            return ['ok' => true, 'text' => '', 'error' => ''];
        }

        $apiKey = $this->ie_get_gemini_key();
        if ($apiKey === '') {
            return ['ok' => false, 'text' => '', 'error' => 'Missing Gemini API key'];
        }

        // Prefer stable models; fall back if unavailable.
        $models = [
            'gemini-1.5-flash',
            'gemini-1.5-pro',
            'gemini-2.0-flash',
        ];

        $prompt = "Bạn là trợ lý dịch thuật chuyên nghiệp. Hãy dịch văn bản từ {$fromLang} sang {$toLang}.\n" .
                  "- Giữ nguyên số, ký hiệu, đơn vị đo, cấu trúc dòng nếu có.\n" .
                  "- Không thêm lời giải thích. Chỉ trả về bản dịch.\n\n" .
                  "Văn bản:\n" . $sourceText;

        $payload = [
            'contents' => [[
                'role'  => 'user',
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 2048,
            ],
        ];

        $lastErr = '';
        foreach ($models as $model) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($apiKey);

            if (!function_exists('curl_init')) {
                return ['ok' => false, 'text' => '', 'error' => 'cURL extension is disabled'];
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

            $raw = curl_exec($ch);
            $err = curl_error($ch);
            $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($raw === false) {
                $lastErr = 'cURL error: ' . $err;
                continue;
            }

            $json = json_decode($raw, true);
            if ($http >= 400) {
                $msg = '';
                if (is_array($json) && isset($json['error']['message'])) {
                    $msg = (string)$json['error']['message'];
                }
                $lastErr = 'HTTP ' . $http . ($msg ? (': ' . $msg) : '');
                continue;
            }

            $text = '';
            if (is_array($json)
                && isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                $text = (string)$json['candidates'][0]['content']['parts'][0]['text'];
            }

            $text = trim($text);
            if ($text !== '') {
                return ['ok' => true, 'text' => $text, 'error' => ''];
            }

            $lastErr = 'Empty response';
        }

        return ['ok' => false, 'text' => '', 'error' => $lastErr ?: 'Translate failed'];
    }

    /**
     * Translate multiple fields (JP -> VI).
     * POST:
     *  - fields[] : list of VI field names
     *  - values   : object { fieldName => value }
     */
    public function ai_translate_fields()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $fields = $this->input->post('fields');
        $values = $this->input->post('values');

        if (!is_array($fields)) $fields = [];
        if (!is_array($values)) $values = [];

        $out = [];
        foreach ($fields as $viField) {
            $viField = trim((string)$viField);
            if ($viField === '') continue;

            // If already has value, keep it.
            if (!empty($values[$viField])) {
                $out[$viField] = (string)$values[$viField];
                continue;
            }

            // Find JP source by naming convention.
            $jpField = '';
            if (substr($viField, -3) === '_vi') {
                $jpField = substr($viField, 0, -3) . '_jp';
            }
            if ($jpField === '' || !isset($values[$jpField])) {
                // Sometimes JP field has no suffix
                $jpField = str_replace('_vi', '', $viField);
            }

            $src = isset($values[$jpField]) ? (string)$values[$jpField] : '';
            $src = trim($src);
            if ($src === '') continue;

            $res = $this->ie_gemini_translate($src, 'ja', 'vi');
            if (!empty($res['ok'])) {
                $out[$viField] = (string)$res['text'];
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'data'    => $out,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Translate and fill missing VI fields only.
     * POST: values (all fields)
     */
    public function ai_translate_fill_missing()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $values = $this->input->post('values');
        if (!is_array($values)) $values = [];

        $out = [];

        foreach ($values as $k => $v) {
            $k = (string)$k;
            if (substr($k, -3) !== '_vi') continue;
            if (trim((string)$v) !== '') continue; // only empty

            $jp = substr($k, 0, -3) . '_jp';
            $src = isset($values[$jp]) ? trim((string)$values[$jp]) : '';
            if ($src === '') continue;

            $res = $this->ie_gemini_translate($src, 'ja', 'vi');
            if (!empty($res['ok'])) {
                $out[$k] = (string)$res['text'];
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'data'    => $out,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    


/**
 * Keep only Japanese fields after AI parse (Step 1).
 * Remove all *_vi keys so AI only fills JP. VI will be filled by Google Translate on button click (Step 2).
 */
private function ie_ai_keep_only_jp($arr)
{
    if (!is_array($arr)) return $arr;
    foreach ($arr as $k => $v) {
        if (!is_string($k)) continue;
        if (substr($k, -3) === '_vi') {
            unset($arr[$k]);
        }
    }
    return $arr;
}

private function ie_normalize_ai_to_form_jp($arr)
{
    // Mục tiêu: đảm bảo key AI trả về khớp name input JP trong form (company_name_jp, address_jp, workplace_jp, job_description_jp, major_jp...)
    if (!is_array($arr)) return $arr;

    // Map các key phổ biến AI hay trả về (không có _jp) -> đúng key JP của form
    $map = [
        'company_name'      => 'company_name_jp',
        'companyName'       => 'company_name_jp',
        'address'           => 'address_jp',
        'company_address'   => 'address_jp',
        'workplace'         => 'workplace_jp',
        'work_place'        => 'workplace_jp',
        'job_description'   => 'job_description_jp',
        'description'       => 'job_description_jp',
        'major'             => 'major_jp',
        'speciality'        => 'major_jp',
        'other'             => 'benefit_other', // nếu AI trả "other" thì map sang JP textarea benefit_other
        'benefit_other_jp'  => 'benefit_other', // đôi khi AI dùng _jp nhưng form dùng base
        'job_title_jp'      => 'job_title',      // form job_title là JP (không _jp)
        'education_jp'      => 'education',
        'japanese_level_jp' => 'japanese_level',
        'english_level_jp'  => 'english_level',
        'working_hours_jp'  => 'working_hours',
        'break_time_jp'     => 'break_time',
        'work_days_jp'      => 'work_days',
        'holidays_jp'       => 'holidays',
        'overtime_jp'       => 'overtime',
        'contract_months_jp'=> 'contract_months',
    ];

    foreach ($map as $srcKey => $dstKey) {
        if (!isset($arr[$dstKey]) && isset($arr[$srcKey]) && trim((string)$arr[$srcKey]) !== '') {
            $arr[$dstKey] = $arr[$srcKey];
        }
    }

    return $arr;
}


private function ie_is_url($v)
{
    return (bool)preg_match('#^https?://#i', trim((string)$v));
}

private function ie_is_email($v)
{
    return (bool)filter_var(trim((string)$v), FILTER_VALIDATE_EMAIL);
}

private function ie_is_numeric_like($v)
{
    $v = trim((string)$v);
    if ($v === '') return false;
    return (bool)preg_match('/^[0-9\.\,\-\+\(\)\s]+$/', $v);
}

private function ie_contains_japanese($text)
{
    $text = (string)$text;
    if ($text === '') return false;
    return (bool)preg_match('/[\x{3040}-\x{30FF}\x{4E00}-\x{9FFF}]/u', $text);
}

/**
 * Normalize some JP date formats into VI-friendly text.
 * - YYYY年M月D日 => dd/mm/YYYY
 * - YYYY年M月     => mm/YYYY
 * - M月D日        => dd/mm
 * Other fuzzy formats (e.g. 9月下旬) are returned as-is.
 */
private function ie_normalize_date_vi($text)
{
    $t = trim((string)$text);

    if (preg_match('/(\d{4})\s*年\s*(\d{1,2})\s*月\s*(\d{1,2})\s*日/u', $t, $m)) {
        $y = (int)$m[1]; $mo = (int)$m[2]; $d = (int)$m[3];
        return sprintf('%02d/%02d/%04d', $d, $mo, $y);
    }

    if (preg_match('/(\d{4})\s*年\s*(\d{1,2})\s*月/u', $t, $m)) {
        $y = (int)$m[1]; $mo = (int)$m[2];
        return sprintf('%02d/%04d', $mo, $y);
    }

    if (preg_match('/(\d{1,2})\s*月\s*(\d{1,2})\s*日/u', $t, $m)) {
        $mo = (int)$m[1]; $d = (int)$m[2];
        return sprintf('%02d/%02d', $d, $mo);
    }

    return $t;
}

/**
 * Step 2: Translate JP -> VI using Google Translate API and fill VI fields.
 * Only fills VI fields that are currently empty.
 * POST: values (array or json string)
 */

/**
 * Server-side fill VI from JP values (two-step workflow).
 * - Copy numeric/url/email/phone as-is
 * - Normalize common JP date patterns
 * - Translate remaining JP text via Google Translate
 * @param array $post Raw POST data
 * @return array Updated POST with *_vi filled
 */
private function ie_google_fill_vi_from_jp(array $post)
{
    $needTranslate = []; // [vi_key => jp_text]
    $directCopy    = []; // [vi_key => value]
    $dateCopy      = []; // [vi_key => value]

    foreach ($post as $k => $viVal) {
        if (!is_string($k)) continue;
        if (substr($k, -3) !== '_vi') continue;

        $base = substr($k, 0, -3);
        $jpKey = $base . '_jp';

        // JP nguồn: *_jp hoặc fallback key gốc
        $jpVal = '';
        if (array_key_exists($jpKey, $post)) $jpVal = $post[$jpKey];
        elseif (array_key_exists($base, $post)) $jpVal = $post[$base];

        $jpVal = trim((string)$jpVal);
        if ($jpVal === '') continue;

        // Số/URL/Email => copy nguyên
        if ($this->im_is_url($jpVal) || $this->im_is_email($jpVal) || $this->im_is_numeric_like($jpVal)) {
            $directCopy[$k] = $jpVal;
            continue;
        }

        // Ngày tháng: normalize (giữ nguyên dạng mơ hồ)
        if (preg_match('/年|月|日/u', $jpVal)) {
            $dateCopy[$k] = $this->im_normalize_date_vi($jpVal);
            continue;
        }

        // Không có ký tự Nhật => copy (WEB, latin...)
        if (!$this->im_contains_japanese($jpVal)) {
            $directCopy[$k] = $jpVal;
            continue;
        }

        // Còn lại dịch
        $needTranslate[$k] = $jpVal;
    }

    // Batch translate
    $out = [];
    if (!empty($needTranslate)) {
        $texts = array_values($needTranslate);
        $translated = $this->im_google_translate_batch($texts, 'ja', 'vi');

        $i = 0;
        foreach ($needTranslate as $viKey => $jpText) {
            $val = isset($translated[$i]) ? trim((string)$translated[$i]) : '';
            $out[$viKey] = ($val !== '') ? $val : $jpText;
            $i++;
        }
    }

    // merge copies
    foreach ($directCopy as $k => $v) $out[$k] = $v;
    foreach ($dateCopy as $k => $v)   $out[$k] = $v;

    // apply to post
    foreach ($out as $k => $v) {
        $post[$k] = $v;
    }

    return $post;
}

public function google_translate_fill_vi()
{
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $values = $this->input->post('values');

    // Support both array payload and JSON string payload
    if (is_string($values)) {
        $decoded = json_decode($values, true);
        if (is_array($decoded)) {
            $values = $decoded;
        }
    }
    if (!is_array($values)) $values = [];

    // Prefer Google Translate API key
    $gKey = get_option('intern_google_translate_api_key');
    if (!$gKey) $gKey = get_option('google_translate_api_key');

    if (!$gKey) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>false,'error'=>'Chưa cấu hình Google Translate API key (intern_google_translate_api_key hoặc google_translate_api_key).'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $out = [];

    foreach ($values as $k => $viVal) {
        $k = (string)$k;
        if (substr($k, -3) !== '_vi') continue;

        // Only fill empty VI fields
        if (trim((string)$viVal) !== '') continue;

        $base = substr($k, 0, -3);
        $jpKey = $base . '_jp';

        $jpVal = '';
        if (array_key_exists($jpKey, $values)) $jpVal = $values[$jpKey];
        elseif (array_key_exists($base, $values)) $jpVal = $values[$base];

        $jpVal = trim((string)$jpVal);
        if ($jpVal === '') continue;

        // Copy (no translate) for URL / email / numeric-only
        if ($this->ie_is_url($jpVal) || $this->ie_is_email($jpVal) || $this->ie_is_numeric_like($jpVal)) {
            $out[$k] = $jpVal;
            continue;
        }

        // Normalize date-like strings (contains 年/月/日)
        if (preg_match('/年|月|日/u', $jpVal)) {
            $out[$k] = $this->ie_normalize_date_vi($jpVal);
            continue;
        }

        // If no Japanese chars, just copy (e.g. WEB, latin names)
        if (!$this->ie_contains_japanese($jpVal)) {
            $out[$k] = $jpVal;
            continue;
        }

        // Translate with Google
        $res = $this->ie_google_translate($jpVal, 'ja', 'vi', $gKey);
        if (!empty($res['ok'])) {
            $out[$k] = (string)$res['text'];
        } else {
            // Fallback copy JP (do not break whole response)
            $out[$k] = $jpVal;
        }
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>true,'data'=>$out], JSON_UNESCAPED_UNICODE);
    exit;
}
/**
     * Translate free text from widget textarea.
     * POST: text, from_lang, to_lang
     */
    public function ai_translate_text()
{
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $text = (string)$this->input->post('text');
    $from = strtolower(trim((string)$this->input->post('from_lang')));
    $to   = strtolower(trim((string)$this->input->post('to_lang')));

    if ($from === '') $from = 'ja';
    if ($to === '') $to = 'vi';

    $textTrim = trim($text);
    if ($textTrim === '') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success'=>false,'text'=>'','error'=>'EMPTY_TEXT'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Prefer Google Translate API key (settings: intern_google_translate_api_key)
    $gKey = get_option('intern_google_translate_api_key');
    if (!$gKey) $gKey = get_option('google_translate_api_key');

    if ($gKey) {
        $res = $this->ie_google_translate($textTrim, $from, $to, $gKey);
    } else {
        // Fallback Gemini
        $res = $this->ie_gemini_translate($textTrim, $from, $to);
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => !empty($res['ok']),
        'text'    => (string)($res['text'] ?? ''),
        'error'   => (string)($res['error'] ?? ''),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Google Translate v2 (simple) - server side.
 */
private function ie_google_translate($text, $from, $to, $apiKey)
{
    $endpoint = 'https://translation.googleapis.com/language/translate/v2';
    $payload  = [
        'q'      => $text,
        'source' => $from,
        'target' => $to,
       'format' => 'text',
'model'  => 'nmt',
        'key'    => $apiKey,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        return ['ok'=>false, 'error'=>'CURL_ERROR: '.$err];
    }

    $json = json_decode($raw, true);
    if ($code >= 400 || !is_array($json)) {
        return ['ok'=>false, 'error'=>'HTTP_'.$code.': '.substr($raw,0,200)];
    }

    if (!empty($json['error']['message'])) {
        return ['ok'=>false, 'error'=>(string)$json['error']['message']];
    }

    $translated = $json['data']['translations'][0]['translatedText'] ?? '';
    return ['ok'=>true, 'text'=>html_entity_decode((string)$translated, ENT_QUOTES, 'UTF-8')];
}


    /**
     * Healthcheck endpoint for Gemini API.
     * Usage: /admin/internship_management/internship_job_orders/gemini_healthcheck?text=...&from=ja&to=vi
     */
    public function gemini_healthcheck()
    {
        header('Content-Type: application/json; charset=utf-8');

        $text = $this->input->get('text');
        if ($text === null || $text === '') {
            $text = 'テストです';
        }
        $from = $this->input->get('from');
        $to   = $this->input->get('to');
        if ($from === null || $from === '') $from = 'ja';
        if ($to === null || $to === '') $to = 'vi';

        // Detect whether an API key exists (common option names)
        $apiKey = get_option('gemini_api_key');
        if (!$apiKey) $apiKey = get_option('google_gemini_api_key');
        if (!$apiKey) $apiKey = get_option('internship_gemini_api_key');
        if (!$apiKey) $apiKey = get_option('google_ai_api_key');
        if (!$apiKey && function_exists('getenv')) {
            $apiKey = getenv('GEMINI_API_KEY');
        }

        $res = $this->ie_gemini_translate($text, $from, $to);

        echo json_encode([
            'ok'      => !empty($res['ok']),
            'text'    => (string)($res['text'] ?? ''),
            'error'   => (string)($res['error'] ?? ''),
            'has_key' => !empty($apiKey),
            'from'    => $from,
            'to'      => $to,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }


/**
 * Profile page (tabs). If profile view not found, fallback to view().
 */

   public function profile($id)
{
    $id = (int)$id;
    if ($id <= 0) {
        show_404();
    }

    // ================= PERMISSION =================
    if (function_exists('has_permission')) {
        if (!is_admin() && !has_permission('internship_management', '', 'view')) {
            access_denied('internship_management');
        }
    }

    // ================= ENSURE STRUCTURE =================
    $this->im_ensure_job_order_crm_column();
    $this->im_ensure_job_order_logs_table();
    $this->im_ensure_job_order_notes_table();
    if (method_exists($this, 'im_ensure_job_order_note_files_table')) {
        $this->im_ensure_job_order_note_files_table();
    }

    // ================= LOAD MODELS =================
    $this->load->model('internship_management/internship_job_orders_model', 'job_orders_model');
    $this->load->model('internship_management/internship_applications_model', 'applications_model');

    // ================= GET JOB ORDER =================
    $job_order = $this->job_orders_model->get($id);

    if (!$job_order) {
        show_404();
    }

    // ================= ACTIVE TAB =================
    $allowed_tabs = ['candidates','invoices','contracts','logs','notes','info'];
    $active_tab   = $this->input->get('tab', true);

    if (!$active_tab || !in_array($active_tab, $allowed_tabs)) {
        $active_tab = 'candidates';
    }

    // ================= BASE DATA =================
    $data = [];
    $data['title']              = 'Hồ sơ Đơn tuyển #' . $id;
    $data['job_order']          = $job_order;

    // Alias để tránh lỗi $job undefined trong view
    $data['job']                = $job_order;

    $data['job_order_id']       = $id;
    $data['active_tab']         = $active_tab;

    $data['crm_client_id']      = (int)$this->im_read_job_order_crm_id($job_order);
    $data['crm_client_exists']  = ($data['crm_client_id'] > 0);

    // ================= LOAD TAB DATA =================
    $data['candidates'] = [];
    $data['invoices']   = [];
    $data['contracts']  = [];
    $data['logs']       = [];
    $data['notes']      = [];

    switch ($active_tab) {

        case 'candidates':
            $data['candidates'] = $this->im_get_candidates_for_job_order($id);
            break;

        case 'invoices':
            if ($data['crm_client_exists']) {
                $data['invoices'] = $this->im_get_invoices_for_job_order($data['crm_client_id']);
            }
            break;

        case 'contracts':
            if ($data['crm_client_exists']) {
                $data['contracts'] = $this->im_get_contracts_for_job_order($data['crm_client_id']);
            }
            break;

        case 'logs':
            $data['logs'] = $this->im_get_job_order_logs($id);
            break;

        case 'notes':
            $data['notes'] = $this->im_get_job_order_notes($id);
            break;

        case 'info':
            // chỉ dùng job_order đã load
            break;
    }

    // ================= LOAD VIEW =================
    $this->load->view('internship_management/job_orders/profile', $data);
}
/**
 * Create a new recruitment round (Đợt) for same company/order.
 * Duplicates current order and increments round_no if column exists.
 */
public function create_round($id)
{
    $id = (int)$id;
    if ($id <= 0) show_404();

    $tbl = db_prefix().'internship_job_orders';
    $row = $this->db->get_where($tbl, ['id'=>$id])->row_array();
    if (!$row) show_404();

    // Determine round number by counting existing orders with same company name (VI preferred)
    $companyVi = $row['company_name_vi'] ?? ($row['company_name'] ?? '');
    $companyJp = $row['company_name_jp'] ?? '';
    $this->db->from($tbl);
    if ($companyVi !== '') {
        $this->db->where('company_name_vi', $companyVi);
    } elseif ($companyJp !== '') {
        $this->db->where('company_name_jp', $companyJp);
    } else {
        // fallback: same id only -> round 2
        $this->db->where('id', $id);
    }
    $count = (int)$this->db->count_all_results();
    $nextRound = max(1, $count + 1);

    unset($row['id']);
    // common meta columns
    if (isset($row['datecreated'])) $row['datecreated'] = date('Y-m-d H:i:s');
    if (isset($row['created_at'])) $row['created_at'] = date('Y-m-d H:i:s');
    if (isset($row['updated_at'])) $row['updated_at'] = date('Y-m-d H:i:s');
    if (isset($row['addedfrom'])) $row['addedfrom'] = get_staff_user_id();

    // set parent link if column exists
    foreach (['parent_id','parent_job_order_id','job_order_parent_id','round_parent_id'] as $c) {
        if ($this->db->field_exists($c, $tbl)) {
            $row[$c] = $id;
            break;
        }
    }

    // set round fields if exist
    foreach (['round_no','round_number'] as $c) {
        if ($this->db->field_exists($c, $tbl)) {
            $row[$c] = $nextRound;
            break;
        }
    }
    foreach (['round_label','round_name'] as $c) {
        if ($this->db->field_exists($c, $tbl)) {
            $row[$c] = 'Đợt '.$nextRound;
            break;
        }
    }

    $this->db->insert($tbl, $row);
    $newId = (int)$this->db->insert_id();

    if ($newId > 0) {
        set_alert('success', 'Đã tạo Đợt '.$nextRound.' thành công.');
        redirect(admin_url('internship_management/internship_job_orders/edit/'.$newId));
    }

    set_alert('danger', 'Không thể tạo đợt mới.');
    redirect(admin_url('internship_management/internship_job_orders'));
}
private function im_has_japanese($text)
{
    $text = (string)$text;
    if ($text === '') return false;
    // Hiragana, Katakana, Kanji
    return (bool)preg_match('/[\x{3040}-\x{30FF}\x{4E00}-\x{9FFF}]/u', $text);
}

private function im_should_translate_vi($vi, $jp)
{
    $vi = trim((string)$vi);
    $jp = trim((string)$jp);

    if ($jp === '') return false;

    // 1) vi rỗng
    if ($vi === '') return true;

    // 2) vi y hệt jp (AI copy sang)
    if ($vi === $jp) return true;

    // 3) vi còn ký tự Nhật
    if ($this->im_has_japanese($vi)) return true;

    return false;
}

private function im_is_non_translatable_field($name, $value)
{
    $v = trim((string)$value);

    // URL / email / phone / số => không dịch
    if (preg_match('#^https?://#i', $v)) return true;
    if (preg_match('/^[\d\-\+\(\)\s]+$/', $v) && $v !== '') return true; // phone / numeric-only
    if (filter_var($v, FILTER_VALIDATE_EMAIL)) return true;

    // Các field thường không cần dịch (tuỳ bạn bổ sung)
    $skipNames = [
        'website','website_vi',
        'company_phone','company_phone_vi',
        'employer_phone','pic_phone',
        'employer_email','pic_email',
        'salary_total','salary_total_vi','salary_net','salary_net_vi',
        'tax','tax_vi','insurance','insurance_vi','dormitory','dormitory_vi','utilities','utilities_vi',
        'quantity_male','quantity_female','quantity_total',
        'quantity_male_vi','quantity_female_vi','quantity_total_vi',
        'age_from','age_to','age_from_vi','age_to_vi',
        'employee_count','employee_count_vi',
    ];

    return in_array($name, $skipNames, true);
}

    // === Compatibility aliases for translate helpers ===
    private function im_is_url($v) { return $this->ie_is_url($v); }
    private function im_is_email($v) { return $this->ie_is_email($v); }
    private function im_is_numeric_like($v) { return $this->ie_is_numeric_like($v); }
    private function im_contains_japanese($v) { return $this->ie_contains_japanese($v); }
    private function im_normalize_date_vi($v) { return $this->ie_normalize_date_vi($v); }



    /**
     * Google Translate batch (Translation API v2)
     * Returns array of translated strings in the same order as $texts.
     * On error returns array('__error__' => 'message')
     */
    private function im_google_translate_batch(array $texts, $from = 'ja', $to = 'vi')
    {
        // Try multiple option keys (keep backward compatible)
        $apiKey = get_option('intern_google_translate_api_key');
        if (!$apiKey) $apiKey = get_option('google_translate_api_key');
        if (!$apiKey) $apiKey = get_option('intern_google_api_key');

        // Keep original list order but remove empties
        $texts = array_values(array_filter($texts, function ($t) {
            return trim((string)$t) !== '';
        }));

        if (empty($texts)) return [];
        if (!$apiKey) return ['__error__' => 'Thiếu API key Google Translate. Cấu hình option: intern_google_translate_api_key hoặc google_translate_api_key hoặc intern_google_api_key'];

        // Use JSON payload to avoid unicode/form-encoding issues (Japanese text)
        $endpoint = 'https://translation.googleapis.com/language/translate/v2';
        $url = $endpoint . '?key=' . rawurlencode($apiKey);

        $payload = [
            'q'      => array_values($texts),
            'source' => $from,
            'target' => $to,
            'format' => 'text',
'model'  => 'nmt',
        ];
        $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE);

        if ($jsonBody === false) {
            return ['__error__' => 'Không thể encode JSON để dịch.'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            return ['__error__' => 'CURL error: '.$err];
        }
        if ($code >= 400) {
            $json = json_decode($raw, true);
            $msg = is_array($json) && !empty($json['error']['message']) ? $json['error']['message'] : ('HTTP '.$code);
            return ['__error__' => 'Google Translate HTTP lỗi: '.$msg];
        }

        $json = json_decode($raw, true);
        if (!is_array($json)) return ['__error__' => 'Google Translate trả về dữ liệu không hợp lệ.'];
        if (!empty($json['error']['message'])) return ['__error__' => $json['error']['message']];
        if (empty($json['data']['translations'])) return ['__error__' => 'Google Translate không trả kết quả.'];

        $out = [];
        foreach ($json['data']['translations'] as $tr) {
            $out[] = html_entity_decode((string)($tr['translatedText'] ?? ''), ENT_QUOTES, 'UTF-8');
        }
        return $out;
    }
/**
     * AJAX: Translate a single field value
     * POST: text, from(optional), to(optional)
     */
    public function im_google_translate_field()
    {
        // CI considers AJAX when header X-Requested-With is present
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $text = trim((string)$this->input->post('text'));
        $from = trim((string)$this->input->post('from'));
        $to   = trim((string)$this->input->post('to'));

        if ($from === '') $from = 'ja';
        if ($to === '')   $to   = 'vi';

        if ($text === '') {
            echo json_encode(['success' => true, 'translated' => '']);
            return;
        }

        // Keep numbers/urls/emails unchanged
        if ($this->im_is_url($text) || $this->im_is_email($text) || $this->im_is_numeric_like($text)) {
            echo json_encode(['success' => true, 'translated' => $text]);
            return;
        }

        // Normalize Japanese date patterns (年/月/日) to VI format
        if (preg_match('/年|月|日/u', $text)) {
            echo json_encode(['success' => true, 'translated' => $this->im_normalize_date_vi($text)]);
            return;
        }

        // If no Japanese and source isn't explicitly Japanese, return as-is
        if ($from !== 'ja' && !$this->im_contains_japanese($text)) {
            echo json_encode(['success' => true, 'translated' => $text]);
            return;
        }

        $arr = $this->im_google_translate_batch([$text], $from, $to);
        if (is_array($arr) && isset($arr['__error__'])) {
            echo json_encode(['success' => false, 'error' => (string)$arr['__error__']]);
            return;
        }

        $tr = (isset($arr[0]) && $arr[0] !== '') ? $arr[0] : '';
        if ($tr === '') {
            echo json_encode(['success' => false, 'error' => 'Google Translate không trả kết quả (key/quota/permission).']);
            return;
        }

        echo json_encode(['success' => true, 'translated' => $tr]);
    }



    // === Gemini anti-spam helpers (Word parse) ===
    private function im_gemini_rate_limit($key = 'im_gemini_word_bucket', $limit = 5, $windowSec = 60)
    {
        $now = time();
        $bucket = $this->session->userdata($key);
        if (!is_array($bucket)) $bucket = [];

        $bucket = array_values(array_filter($bucket, function ($t) use ($now, $windowSec) {
            return ($now - (int)$t) < $windowSec;
        }));

        if (count($bucket) >= $limit) {
            return false;
        }

        $bucket[] = $now;
        $this->session->set_userdata($key, $bucket);
        return true;
    }

    private function im_cache_get($key)
    {
        return $this->session->userdata($key);
    }

    private function im_cache_set($key, $val)
    {
        $this->session->set_userdata($key, $val);
    }

    private function im_is_retryable_http($code)
    {
        $code = (int)$code;
        return ($code === 429 || $code === 503 || $code === 500);
    }



    /**
     * AJAX: translate free text (for translate box)
     * POST: text, from, to
     */
    public function im_google_translate_text()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $text = trim((string)$this->input->post('text'));
        $from = trim((string)$this->input->post('from'));
        $to   = trim((string)$this->input->post('to'));

        if ($from === '') $from = 'ja';
        if ($to === '')   $to   = 'vi';

        if ($text === '') {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Required Text']));
            return;
        }

        // Numbers/dates: return original (no API call)
        if (preg_match('/^[\d\s,\.\%\-\+]+$/u', $text) ||
            preg_match('/^\d{4}[\/\-\.\]\d{1,2}[\/\-\.\]\d{1,2}$/u', $text) ||
            preg_match('/^\d{1,2}[\/\-\.\]\d{1,2}[\/\-\.\]\d{2,4}$/u', $text)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => true, 'translated' => $text]));
            return;
        }

        $out = $this->im_google_translate_batch([$text], $from, $to);
        $translated = is_array($out) && isset($out[0]) ? (string)$out[0] : '';

        if ($translated === '') {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Google Translate không trả kết quả (key/quota/permission).']));
            return;
        }

        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'translated' => $translated]));
    }

    /**
     * AJAX: bulk translate items (for "Dịch các ô còn trống")
     * POST: items (json: {viFieldName: jpText}), from, to
     */
    public function im_google_translate_bulk()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $from = trim((string)$this->input->post('from'));
        $to   = trim((string)$this->input->post('to'));
        if ($from === '') $from = 'ja';
        if ($to === '')   $to   = 'vi';

        $itemsRaw = $this->input->post('items');
        $items = [];
        if (is_array($itemsRaw)) {
            $items = $itemsRaw;
        } else {
            $items = json_decode((string)$itemsRaw, true);
            if (!is_array($items)) $items = [];
        }

        if (empty($items)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'No items']));
            return;
        }

        // Preserve keys order
        $keys = array_keys($items);
        $texts = [];
        foreach ($keys as $k) {
            $texts[] = trim((string)$items[$k]);
        }

        // Translate in batch
        $translated = $this->im_google_translate_batch($texts, $from, $to);
        if (!is_array($translated)) $translated = [];

        $out = [];
        foreach ($keys as $i => $k) {
            $out[$k] = isset($translated[$i]) ? (string)$translated[$i] : '';
        }

        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'translations' => $out]));
    }



    /**
     * ===== PRO PROFILE HELPERS (safe / idempotent) =====
     */

    private function im_table_exists($table)
    {
        return $this->db->table_exists($table);
    }

    private function im_column_exists($table, $column)
    {
        return $this->db->field_exists($column, $table);
    }

    private function im_ensure_job_order_crm_column()
    {
        $tbl = db_prefix() . 'internship_job_orders';
        if (!$this->im_table_exists($tbl)) {
            return;
        }
        if (!$this->im_column_exists($tbl, 'crm_client_id')) {
            $this->db->query("ALTER TABLE `{$tbl}` ADD `crm_client_id` INT(11) NULL DEFAULT NULL AFTER `id`");
        }
        if (!$this->im_column_exists($tbl, 'crm_pushed_at')) {
            $this->db->query("ALTER TABLE `{$tbl}` ADD `crm_pushed_at` DATETIME NULL DEFAULT NULL AFTER `crm_client_id`");
        }
    }

    private function im_ensure_job_order_logs_table()
    {
        $tbl = db_prefix() . 'internship_job_order_logs';
        if ($this->im_table_exists($tbl)) {
            return;
        }
        $this->db->query("CREATE TABLE `{$tbl}` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `job_order_id` INT(11) NOT NULL,
            `staff_id` INT(11) NULL DEFAULT NULL,
            `description` TEXT NULL,
            `datecreated` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `job_order_id` (`job_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    private function im_ensure_job_order_notes_table()
    {
        $tbl = db_prefix() . 'internship_job_order_notes';
        if ($this->im_table_exists($tbl)) {
            return;
        }
        $this->db->query("CREATE TABLE `{$tbl}` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `job_order_id` INT(11) NOT NULL,
            `staff_id` INT(11) NULL DEFAULT NULL,
            `note` TEXT NULL,
            `datecreated` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `job_order_id` (`job_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    private function im_log_job_order($job_order_id, $description)
    {
        $tbl = db_prefix() . 'internship_job_order_logs';
        if (!$this->im_table_exists($tbl)) {
            return;
        }

        $cols = $this->im_table_columns($tbl);

        // Map columns safely across different schemas
        $c_job   = in_array('job_order_id', $cols, true) ? 'job_order_id' : (in_array('joborder_id', $cols, true) ? 'joborder_id' : (in_array('job_id', $cols, true) ? 'job_id' : null));
        $c_staff = in_array('staff_id', $cols, true) ? 'staff_id' : (in_array('addedfrom', $cols, true) ? 'addedfrom' : (in_array('created_by', $cols, true) ? 'created_by' : null));
        $c_desc  = in_array('description', $cols, true) ? 'description' : (in_array('content', $cols, true) ? 'content' : (in_array('note', $cols, true) ? 'note' : (in_array('message', $cols, true) ? 'message' : null)));
        $c_date  = in_array('datecreated', $cols, true) ? 'datecreated' : (in_array('dateadded', $cols, true) ? 'dateadded' : (in_array('created_at', $cols, true) ? 'created_at' : (in_array('datetime', $cols, true) ? 'datetime' : null)));

        $data = [];
        if ($c_job)   $data[$c_job]   = (int)$job_order_id;
        if ($c_staff) $data[$c_staff] = (int)get_staff_user_id();
        if ($c_desc)  $data[$c_desc]  = (string)$description;
        if ($c_date)  $data[$c_date]  = date('Y-m-d H:i:s');

        // If table doesn't have expected columns, avoid crashing
        if (empty($data)) {
            return;
        }

        $this->db->insert($tbl, $data);
        $note_id = (int)$this->db->insert_id();
        if ($note_id > 0 && method_exists($this, 'im_handle_note_attachments')) {
            $this->im_handle_note_attachments($job_order_id, $note_id);
        }
    }

    private function im_get_candidates_for_job_order($job_order_id)
    {
        // Prefer model if it has a method
        if (isset($this->applications_model) && method_exists($this->applications_model, 'get_by_job_order')) {
            return $this->applications_model->get_by_job_order($job_order_id);
        }

        // Fallback to DB (best-effort)
        $tbl = db_prefix() . 'internship_applications';
        if (!$this->im_table_exists($tbl)) {
            return [];
        }
        $this->db->where('job_order_id', (int)$job_order_id);
        $this->db->order_by('id', 'DESC');
        return $this->db->get($tbl)->result();
    }

    private function im_get_invoices_for_job_order($client_id)
    {
        $client_id = (int)$client_id;
        if ($client_id <= 0) return [];
        $tbl = db_prefix() . 'invoices';
        if (!$this->im_table_exists($tbl)) return [];
        $this->db->where('clientid', $client_id);
        $this->db->order_by('id', 'DESC');
        return $this->db->get($tbl)->result();
    }

    private function im_get_contracts_for_job_order($client_id)
    {
        $client_id = (int)$client_id;
        if ($client_id <= 0) return [];
        $tbl = db_prefix() . 'contracts';
        if (!$this->im_table_exists($tbl)) return [];
        $this->db->where('client', $client_id);
        $this->db->order_by('id', 'DESC');
        return $this->db->get($tbl)->result();
    }

    private function im_get_job_order_logs($job_order_id)
    {
        $tbl = db_prefix() . 'internship_job_order_logs';
        if (!$this->im_table_exists($tbl)) return [];
        $this->db->where('job_order_id', (int)$job_order_id);
        $this->db->order_by('id', 'DESC');
        return $this->db->get($tbl)->result();
    }

    private function im_get_job_order_notes($job_order_id)
    {
        $tbl = db_prefix() . 'internship_job_order_notes';
        if (!$this->im_table_exists($tbl)) return [];

        $this->db->where('job_order_id', (int)$job_order_id);
        $this->db->order_by('id', 'DESC');
        $notes = $this->db->get($tbl)->result();

        if (empty($notes)) {
            return [];
        }

        $ftbl = db_prefix() . 'internship_job_order_note_files';
        if ($this->im_table_exists($ftbl)) {
            $noteIds = [];
            foreach ($notes as $n) {
                if (isset($n->id)) $noteIds[] = (int)$n->id;
            }
            $noteIds = array_values(array_unique(array_filter($noteIds)));
            if (!empty($noteIds)) {
                $this->db->where_in('note_id', $noteIds);
                $this->db->order_by('id', 'ASC');
                $files = $this->db->get($ftbl)->result();

                $map = [];
                foreach ($files as $f) {
                    $nid = isset($f->note_id) ? (int)$f->note_id : 0;
                    if (!$nid) continue;
                    if (!isset($map[$nid])) $map[$nid] = [];
                    $map[$nid][] = $f;
                }

                foreach ($notes as $n) {
                    $nid = isset($n->id) ? (int)$n->id : 0;
                    $n->files = isset($map[$nid]) ? $map[$nid] : [];
                }
            }
        }

        return $notes;
    }

    /**
     * Push Job Order's employer to CRM (tblclients).
     * One company name => one CRM client (reused across job orders).
     */
    public function push_crm($job_order_id)
    {
        $job_order_id = (int)$job_order_id;
        if ($job_order_id <= 0) show_404();

        if (function_exists('has_permission')) {
            if (!is_admin() && !has_permission('internship_management', '', 'edit')) {
                access_denied('internship_management');
            }
        }

        $this->im_ensure_job_order_crm_column();
        $this->im_ensure_job_order_logs_table();

        $this->load->model('internship_management/internship_job_orders_model', 'job_orders_model');
        $job_order = $this->job_orders_model->get($job_order_id);
        if (!$job_order) show_404();

        // If already pushed for this job order, block
        if (isset($job_order->crm_client_id) && (int)$job_order->crm_client_id > 0) {
            set_alert('warning', 'Đơn tuyển này đã liên kết CRM (client_id=' . (int)$job_order->crm_client_id . ').');
            redirect(admin_url('internship_management/internship_job_orders/profile/' . $job_order_id));
        }

        // Build company key (support array/object + multiple schemas)
        $company = (string)$this->im_pick_first($job_order, [
            'company_name_vi','company_name_vn','company_vi',
            'company_name_jp','company_name',
            'company','company_name_text',
            'employer_company','employer_name','customer_company','client_company',
            'ten_cong_ty','tencongty'
        ], '');

        if (trim($company) === '') {
            set_alert('danger', 'Không có tên công ty để đẩy CRM.');
            redirect(admin_url('internship_management/internship_job_orders/profile/' . $job_order_id));
        }

        // Reuse existing client mapped by any other job order with same company
        $tblOrders = db_prefix() . 'internship_job_orders';
        $this->db->where('crm_client_id IS NOT NULL', null, false);
        $this->db->group_start();
        if ($this->im_column_exists($tblOrders, 'company_name_vi')) $this->db->or_where('company_name_vi', $company);
        if ($this->im_column_exists($tblOrders, 'company_name_jp')) $this->db->or_where('company_name_jp', $company);
        if ($this->im_column_exists($tblOrders, 'company_name')) $this->db->or_where('company_name', $company);
        $this->db->group_end();
        $this->db->limit(1);
        $row = $this->db->get($tblOrders)->row();
        if ($row && (int)$row->crm_client_id > 0) {
            $this->db->where('id', $job_order_id)->update($tblOrders, [
                'crm_client_id' => (int)$row->crm_client_id,
                'crm_pushed_at' => date('Y-m-d H:i:s'),
            ]);
            $this->im_log_job_order($job_order_id, 'Liên kết CRM dùng lại client_id=' . (int)$row->crm_client_id);
            set_alert('success', 'Đã liên kết CRM (dùng lại khách hàng có sẵn).');
            redirect(admin_url('internship_management/internship_job_orders/profile/' . $job_order_id));
        }

        // Reuse existing CRM client by company in tblclients
        $tblClients = db_prefix() . 'clients';
        $client = null;
        if ($this->im_table_exists($tblClients)) {
            $this->db->where('company', $company);
            $this->db->limit(1);
            $client = $this->db->get($tblClients)->row();
        }
        if ($client && (int)$client->userid > 0) {
            $this->db->where('id', $job_order_id)->update($tblOrders, [
                'crm_client_id' => (int)$client->userid,
                'crm_pushed_at' => date('Y-m-d H:i:s'),
            ]);
            $this->im_log_job_order($job_order_id, 'Liên kết CRM theo tên công ty: client_id=' . (int)$client->userid);
            set_alert('success', 'Đã liên kết CRM theo khách hàng đã tồn tại.');
            redirect(admin_url('internship_management/internship_job_orders/profile/' . $job_order_id));
        }

        // Create new client (best effort)
        $this->load->model('clients_model');
        $client_data = [
            'company'     => $company,
            'phonenumber' => (string)$this->im_pick_first($job_order, ['company_phone','company_phone_vi','phone','phonenumber','contact_phone'], ''),
            'address' => (string)$this->im_pick_first($job_order, ['company_address_vi','address_vi','company_address','address','address_jp','company_address_jp'], ''),
            'city'        => '',
            'country'     => 0,
            'zip'         => '',
            'active'      => 1,
        ];

        // Remove empty keys that may break add()
        foreach ($client_data as $k => $v) {
            if ($v === '' || $v === null) unset($client_data[$k]);
        }

        $new_client_id = 0;
        try {
            $new_client_id = (int)$this->clients_model->add($client_data);
        } catch (\Throwable $e) {
            $new_client_id = 0;
        }

        if ($new_client_id <= 0) {
            set_alert('danger', 'Đẩy CRM thất bại. Vui lòng kiểm tra cấu hình CRM/Clients model.');
            redirect(admin_url('internship_management/internship_job_orders/profile/' . $job_order_id));
        }

        $this->db->where('id', $job_order_id)->update($tblOrders, [
            'crm_client_id' => $new_client_id,
            'crm_pushed_at' => date('Y-m-d H:i:s'),
        ]);

        $this->im_log_job_order($job_order_id, 'Đẩy CRM tạo mới client_id=' . $new_client_id);
        set_alert('success', 'Đã đẩy CRM thành công (client_id=' . $new_client_id . ').');
        redirect(admin_url('internship_management/internship_job_orders/profile/' . $job_order_id));
    }

    public function add_note($job_order_id)
    {
        $job_order_id = (int)$job_order_id;
        if ($job_order_id <= 0) show_404();

        $this->im_ensure_job_order_notes_table();
        $note = $this->input->post('note', true);
        if ($note === null) { $note = $this->input->post('content', true); }
        if ($note === null) { $note = $this->input->post('note_content', true); }
        if (is_string($note)) { $note = trim($note); }

        // allow empty note if user uploads at least one file
        $has_file = (isset($_FILES['attachments']) && is_array($_FILES['attachments']['name']) && count(array_filter($_FILES['attachments']['name'])) > 0);

        if ((!$note || $note === '') && !$has_file) {
            set_alert('warning', 'Vui lòng nhập nội dung ghi chú.');
            redirect(admin_url('internship_management/internship_job_orders/profile/' . $job_order_id . '?tab=notes'));
        }

        $tbl = db_prefix() . 'internship_job_order_notes';
        $cols = $this->im_table_columns($tbl);
        $dateCol = in_array('datecreated', $cols, true) ? 'datecreated'
                 : (in_array('dateadded', $cols, true) ? 'dateadded'
                 : (in_array('created_at', $cols, true) ? 'created_at' : null));

        $data = [
            'job_order_id' => (int)$job_order_id,
            'staff_id'     => (int)get_staff_user_id(),
            'note'         => $note,
        ];
        if ($dateCol) {
            $data[$dateCol] = date('Y-m-d H:i:s');
        }

        $this->db->insert($tbl, $data);
        $note_id = (int)$this->db->insert_id();
        if ($note_id > 0 && method_exists($this, 'im_handle_note_attachments')) {
            $this->im_handle_note_attachments($job_order_id, $note_id);
        }
$this->im_log_job_order($job_order_id, 'Thêm ghi chú');
        set_alert('success', 'Đã thêm ghi chú.');
        redirect(admin_url('internship_management/internship_job_orders/profile/' . $job_order_id . '?tab=notes'));
    }


    /**
     * Safe getter for row (array/object).
     */
    private function im_row_get($row, $key)
    {
        if (is_array($row)) {
            return array_key_exists($key, $row) ? $row[$key] : null;
        }
        if (is_object($row)) {
            return isset($row->$key) ? $row->$key : null;
        }
        return null;
    }

    /**
     * Pick first non-empty value among keys from row (array/object).
     */
    private function im_pick_first($row, array $keys, $default = '')
    {
        foreach ($keys as $k) {
            $v = $this->im_row_get($row, $k);
            if ($v === null) continue;
            if (is_string($v)) {
                $v = trim($v);
                if ($v !== '') return $v;
            } else {
                // numeric/other
                if ($v !== '' && $v !== false) return $v;
            }
        }
        return $default;
    }



    /**
     * Get table columns list (field names). Returns [] if table not exists.
     */
    private function im_table_columns($table)
    {
        try {
            if (!$this->im_table_exists($table)) {
                return [];
            }
            $fields = $this->db->list_fields($table);
            if (is_array($fields)) {
                return $fields;
            }
        } catch (Throwable $e) {
            // ignore
        } catch (Exception $e) {
            // ignore for PHP < 7 compatibility
        }
        return [];
    }



    /**
     * Save CRM client/customer id back to Job Order record (supports different column names).
     */
    private function im_update_job_order_crm_link($job_order_id, $crm_client_id)
    {
        $job_order_id = (int)$job_order_id;
        $crm_client_id = (int)$crm_client_id;
        if ($job_order_id <= 0 || $crm_client_id <= 0) {
            return false;
        }

        $tbl = db_prefix() . 'internship_job_orders';
        if (!$this->im_table_exists($tbl)) {
            // fallback table name without prefix helper
            $tbl = 'tblinternship_job_orders';
            if (!$this->im_table_exists($tbl)) {
                return false;
            }
        }

        $cols = $this->im_table_columns($tbl);

        // find primary key column
        $pk = in_array('id', $cols, true) ? 'id' : (in_array('job_order_id', $cols, true) ? 'job_order_id' : null);
        if (!$pk) return false;

        // choose best CRM column
        $crmCol = null;
        foreach (['crm_client_id','crm_customer_id','clientid','customer_id','crm_id'] as $c) {
            if (in_array($c, $cols, true)) { $crmCol = $c; break; }
        }

        if (!$crmCol) {
            // cannot save, but still return false (so UI can compute by alternative way)
            return false;
        }

        $this->db->where($pk, $job_order_id);
        return (bool)$this->db->update($tbl, [$crmCol => $crm_client_id]);
    }

    /**
     * Read CRM id from job order row using multiple possible columns.
     */
    private function im_read_job_order_crm_id($job_order_row)
    {
        return (int)$this->im_pick_first($job_order_row, ['crm_client_id','crm_customer_id','clientid','customer_id','crm_id'], 0);
    }



    
/**
 * Ensure note files table exists (attachments for job order notes).
 */
private function im_ensure_job_order_note_files_table()
{
    $tbl = db_prefix() . 'internship_job_order_note_files';
    if ($this->im_table_exists($tbl)) {
        return;
    }

    $sql = "CREATE TABLE `" . $tbl . "` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `job_order_id` INT(11) NOT NULL DEFAULT 0,
        `note_id` INT(11) NOT NULL DEFAULT 0,
        `staff_id` INT(11) NOT NULL DEFAULT 0,
        `file_name` VARCHAR(191) NULL,
        `file_path` VARCHAR(500) NULL,
        `mime` VARCHAR(100) NULL,
        `dateadded` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `job_order_id` (`job_order_id`),
        KEY `note_id` (`note_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $this->db->char_set . ";";

    $this->db->query($sql);
}

/**
 * Save uploaded note attachments (multiple).
 */
private function im_handle_note_attachments($job_order_id, $note_id)
{
    $job_order_id = (int)$job_order_id;
    $note_id = (int)$note_id;
    if ($job_order_id <= 0 || $note_id <= 0) {
        return;
    }

    if (!isset($_FILES['attachments']) || empty($_FILES['attachments']['name'])) {
        return;
    }

    $this->im_ensure_job_order_note_files_table();

    $upload_base = FCPATH . 'uploads/internship_management/job_order_notes/' . $job_order_id . '/';
    if (!is_dir($upload_base)) {
        @mkdir($upload_base, 0755, true);
    }

    $this->load->library('upload');

    $names = $_FILES['attachments']['name'];
    $count = is_array($names) ? count($names) : 0;

    for ($i = 0; $i < $count; $i++) {
        if (!isset($_FILES['attachments']['name'][$i]) || $_FILES['attachments']['name'][$i] === '') {
            continue;
        }

        $_FILES['im_file']['name']     = $_FILES['attachments']['name'][$i];
        $_FILES['im_file']['type']     = $_FILES['attachments']['type'][$i];
        $_FILES['im_file']['tmp_name'] = $_FILES['attachments']['tmp_name'][$i];
        $_FILES['im_file']['error']    = $_FILES['attachments']['error'][$i];
        $_FILES['im_file']['size']     = $_FILES['attachments']['size'][$i];

        $config = [
            'upload_path'   => $upload_base,
            'allowed_types' => '*',
            'max_size'      => 20480, // 20MB
            'encrypt_name'  => true,
        ];
        $this->upload->initialize($config);

        if ($this->upload->do_upload('im_file')) {
            $d = $this->upload->data();
            $rel_path = 'uploads/internship_management/job_order_notes/' . $job_order_id . '/' . $d['file_name'];

            $this->db->insert(db_prefix() . 'internship_job_order_note_files', [
                'job_order_id' => $job_order_id,
                'note_id'      => $note_id,
                'staff_id'     => (int)get_staff_user_id(),
                'file_name'    => $d['client_name'],
                'file_path'    => $rel_path,
                'mime'         => $d['file_type'],
                'dateadded'    => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
public function update_date_inline()
{
    if (!$this->input->is_ajax_request()) {
        show_404();
    }

    $id    = (int)$this->input->post('id');
    $field = $this->input->post('field');
    $value = $this->input->post('value');

    if (!$id || !$field) {
        echo json_encode(['status' => false]);
        exit;
    }

    $allowed_fields = ['interview_date', 'entry_date', 'return_date'];

    if (!in_array($field, $allowed_fields)) {
        echo json_encode(['status' => false]);
        exit;
    }

    /*$this->db->where('id', $id);
    $this->db->update(db_prefix() . 'internship_job_orders', [
        $field => !empty($value) ? $value : null,
    ]);*/
    $this->db->where('id', $id);
    $this->db->update(db_prefix() . 'internship_job_orders', [
        $field => $this->im_clean_inline_date($value),
    ]);

    echo json_encode(['status' => true]);
    exit;
}
/* ============================================================
   PRINT APPLICANTS (BILINGUAL - 2 PAGES A4 LANDSCAPE)
============================================================ */
public function print_applicants($job_id)
{
    $job_id = (int)$job_id;
    if ($job_id <= 0) {
        show_404();
    }

    $this->load->model('internship_management/internship_job_orders_model', 'job_orders_model');
    $this->load->model('internship_management/internship_applications_model', 'applications_model');

    $job = $this->job_orders_model->get($job_id);
    if (!$job) {
        blank_page('Không tìm thấy đơn tuyển');
    }

    // danh sách ứng viên theo đơn
    $applicants = $this->applications_model->get_by_job_order($job_id);

    // QR trỏ về trang danh sách ứng viên (để scan mở lại)
    $qr_link = admin_url('internship_management/internship_job_orders/applicants/' . $job_id);
    $qr_src  = "https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=" . urlencode($qr_link);

    $data = [];
    $data['job']        = $job;
    $data['applicants'] = $applicants;
    $data['qr_src']     = $qr_src;

    $this->load->view('internship_management/job_orders/print_applicants', $data);
}
public function print_interview_result($job_id = 0, $application_id = 0)
{
    if (!is_staff_logged_in()) {
        redirect(admin_url('authentication'));
    }

    $job_id         = (int)$job_id;
    $application_id = (int)$application_id;

    if ($job_id <= 0 || $application_id <= 0) {
        show_404();
    }

    /* =====================================
       1️⃣ LOAD JOB
    ===================================== */

    $jobTbl = db_prefix() . 'internship_job_orders';

    if (!$this->db->table_exists($jobTbl)) {
        show_error('Missing table: ' . $jobTbl);
    }

    $job = $this->db
        ->where('id', $job_id)
        ->get($jobTbl)
        ->row_array();

    if (!$job) {
        show_404();
    }

    /* =====================================
       2️⃣ LOAD APPLICATION
    ===================================== */

    /* =====================================
   LOAD APPLICATION - AUTO FK DETECT
===================================== */

$appTbl = db_prefix() . 'internship_applications';

if (!$this->db->table_exists($appTbl)) {
    show_error('Missing table: ' . $appTbl);
}

$appFields = $this->db->list_fields($appTbl);

$where = ['id' => $application_id];

/* Detect foreign key column */
if (in_array('job_order_id', $appFields)) {
    $where['job_order_id'] = $job_id;
}
elseif (in_array('job_id', $appFields)) {
    $where['job_id'] = $job_id;
}
elseif (in_array('internship_job_order_id', $appFields)) {
    $where['internship_job_order_id'] = $job_id;
}

$app = $this->db->get_where($appTbl, $where)->row_array();


if (!$app) {
    show_404();
}

    /* =====================================
       3️⃣ LOAD STUDENT (OPTIONAL)
    ===================================== */

    $student = [];

    if (!empty($app['student_id'])) {

        $studentTbl = db_prefix() . 'internship_students';

        if ($this->db->table_exists($studentTbl)) {

            $student = $this->db
                ->where('id', (int)$app['student_id'])
                ->get($studentTbl)
                ->row_array();
        }
    }

    /* =====================================
       4️⃣ GENERATE QR
    ===================================== */

    $print_url = admin_url(
        'internship_management/internship_job_orders/print_interview_result/'
        . $job_id . '/' . $application_id
    );

    $qr_src = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data='
        . urlencode($print_url);

    /* =====================================
       5️⃣ SEND TO VIEW
    ===================================== */

    $data = [
        'job'       => $job,
        'app'       => $app,
        'student'   => $student,
        'qr_src'    => $qr_src,
        'print_url' => $print_url
    ];

    $this->load->view('job_orders/print_interview_result', $data);
}

}
