<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once(FCPATH . 'modules/internship_management/libraries/PHPWord/src/PhpWord/Autoloader.php');
\PhpOffice\PhpWord\Autoloader::register();

use PhpOffice\PhpWord\IOFactory;

class Internship_applications extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('internship_management/internship_applications_model', 'applications_model');
        $this->load->model('internship_management/internship_job_orders_model', 'job_orders_model');
        // ⭐ THÊM DÒNG NÀY
    $this->load->model('internship_management/internship_calendar_model', 'calendar_model');
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
    /* ============================================================
       LIST
    ============================================================ */
 public function index()
{
    if (!has_permission('internship_management', '', 'view')) {
        access_denied();
    }

    /* ================= FILTERS ================= */
    /*$filters = [
        'search' => $this->input->get('search') ?: '',
        'major'  => $this->input->get('major') ?: '',
        'school' => $this->input->get('school') ?: '',
        'status' => $this->input->get('status') ?: '',
    ];*/
    
    /* ================= FILTERS ================= */

    // Phân biệt rõ:
    // - Không có tham số status trên URL: mặc định lọc Đang làm hồ sơ.
    // - Có status nhưng rỗng: user chọn Tất cả.
    $status_get = $this->input->get('status', true);
    
    $filters = [
        'search' => $this->input->get('search', true) ?: '',
        'major'  => $this->input->get('major', true) ?: '',
        'school' => $this->input->get('school', true) ?: '',
        'status' => ($status_get === null ? 'docs_preparing' : $status_get),
    ];

    // Lấy danh sách ứng viên
    $applications = $this->applications_model->get_all($filters);

    // Thêm text trạng thái hiển thị
    foreach ($applications as &$a) {
        $a['status_text'] = $this->translate_status($a['status']);
    }
    
    /* ================= VIEW DATA ================= */
    $this->load->helper('internship_management/internship_status');
    
    $data['title']        = 'Ứng tuyển Internship';
    $data['filters']      = $filters;
    $data['status_list']  = im_application_filter_status_list();
    
    $data['interview_result_list'] = im_interview_result_list();
    $data['dossier_progress_list'] = im_dossier_progress_list();
    
    $data['applications'] = $applications;

    $this->load->view('internship_management/applications/index', $data);
}


    /* ============================================================
       CREATE
    ============================================================ */
  /* ============================================================
   CREATE
============================================================ */
public function create()
{
    if (!has_permission('internship_management', '', 'create')) {
        access_denied();
    }

    // Clone (Ứng tuyển lại): GIỮ 1 hồ sơ sinh viên, chỉ đổi Đơn tuyển.
    $cloneId = (int)$this->input->get('clone');
    if ($cloneId > 0) {
        $data['clone'] = $this->applications_model->get_clone_data($cloneId);
        $data['clone_from_id'] = $cloneId;
    }

    if ($this->input->post()) {

        $post = $this->input->post();

        // Map AI / legacy → field chuẩn
        $this->map_ai_fields($post);
        
        // Xử lý trường học
        /*if (isset($post['school_name']) && $post['school_name'] === '__new__') {
            $newSchool = trim((string)($post['school_name_new'] ?? ''));
        
            if ($newSchool === '') {
                set_alert('warning', 'Vui lòng nhập tên trường mới.');
                redirect(admin_url('internship_management/internship_applications/create'));
            }
        
            $post['school_name'] = $newSchool;
        } else {
            $post['school_name_new'] = null;
        }*/
        
        // Xử lý trường học
        if (isset($post['school_name']) && $post['school_name'] === '__new__') {
            $newSchool = trim((string)($post['school_name_new'] ?? ''));
        
            if ($newSchool === '') {
                set_alert('warning', 'Vui lòng nhập tên trường mới.');
                redirect(admin_url('internship_management/internship_applications/create'));
            }
        
            $post['school_name'] = $newSchool;
        }
        
        unset($post['school_name_new']);
        
        // Đồng bộ trường học sang danh mục trường đối tác để Đơn tuyển cũng dùng được
        if (!empty($post['school_name'])) {
            $this->job_orders_model->ensure_partner_school($post['school_name']);
        }

        // Upload CV
        $cv = $this->_upload_cv(false);
        if ($cv) {
            $post['cv_file']      = $cv['file_name'];
            $post['cv_file_type'] = $cv['file_type'];
        }

        // Upload avatar
        $avatar = $this->_upload_avatar();
        if ($avatar) {
            $post['avatar']      = $avatar['file_name'];
            $post['avatar_type'] = $avatar['file_type'];
        }

        // Avatar từ AI
        if (!empty($post['avatar_ai_file']) && empty($_FILES['avatar']['name'])) {
            $post['avatar']      = $post['avatar_ai_file'];
            $post['avatar_type'] = pathinfo($post['avatar_ai_file'], PATHINFO_EXTENSION);
        }

        // =============================
        // ỨNG TUYỂN LẠI (clone)
        // - KHÔNG tạo record ứng viên mới (tránh sinh viên bị nhân bản)
        // - Giữ nguyên CV/Avatar nếu không upload file mới
        // - Reset status/apply_date về trạng thái ứng tuyển
        // =============================
        if ($cloneId > 0) {
            $id = $this->applications_model->reapply_to_new_job($cloneId, $post);
        } else {
            // Lưu mới vào DB
            $id = $this->applications_model->add($post);
        }

        if ($id) {

            // ----------------------------
            // ⭐ TỰ ĐỘNG TẠO LỊCH
            // ----------------------------
            $app = $this->applications_model->get($id);

        /* ===== FORCE student_id always available ===== */
        if (empty($app['student_id']) && !empty($app['id'])) {
            $CI = &get_instance();
            $tbl = db_prefix() . 'internship_applications';
            if ($CI->db->table_exists($tbl)) {
                $row = $CI->db->select('student_id')
                              ->where('id', $app['id'])
                              ->get($tbl)
                              ->row_array();
                if (!empty($row['student_id'])) {
                    $app['student_id'] = (int)$row['student_id'];
                }
            }
        }

            

        // CRM link status
        /*$crm_link = ['linked' => false, 'crm_id' => 0, 'crm_url' => ''];
        $crm_table_name = 'internship_crm_links';
        $crm_table = db_prefix().$crm_table_name;
        if ($this->db->table_exists($crm_table)) {
            // Detect available columns
            $where = [];
            if ($this->db->field_exists('application_id', $crm_table_name)) {
                $where['application_id'] = (int)$id;
            } elseif ($this->db->field_exists('rel_id', $crm_table_name)) {
                $where['rel_id'] = (int)$id;
            } elseif (!empty($app['student_id']) && $this->db->field_exists('student_id', $crm_table_name)) {
                $where['student_id'] = (int)$app['student_id'];
            }

            if (!empty($where)) {
                $this->db->where($where);
                $row = $this->db->get($crm_table)->row_array();
                if ($row) {
                    $crm_id = 0;
                    foreach (['crm_id','client_id','customer_id','clientid'] as $k) {
                        if (isset($row[$k]) && (int)$row[$k] > 0) { $crm_id = (int)$row[$k]; break; }
                    }
                    if ($crm_id > 0) {
                        $crm_link['linked'] = true;
                        $crm_link['crm_id'] = $crm_id;
                        // Perfex default client view
                        $crm_link['crm_url'] = admin_url('clients/client/' . $crm_id);
                    }
                }
            }
        }*/
        //replace above 
        $crm_link = $this->im_get_crm_link((int)$id, (int)($app['student_id'] ?? 0));
        
        
        
$this->calendar_model->sync_application_events($app);

            set_alert('success', 'Thêm ứng viên thành công.');
            redirect(admin_url('internship_management/internship_applications'));

        } else {
            set_alert('danger', 'Không thể lưu dữ liệu.');
        }
    }

    /*$data['job_orders'] = $this->job_orders_model->get_all_for_select();
    $data['title'] = 'Thêm mới học sinh';

    $this->load->view('internship_management/applications/create', $data);*/
    $data['job_orders'] = $this->job_orders_model->get_all_for_select();
    $data['schools']    = $this->applications_model->get_school_options();
    $data['title']      = 'Thêm mới học sinh';
    
    $this->load->view('internship_management/applications/create', $data);
}   

    /* ============================================================
       UPDATE STATUS INLINE (AJAX)
    ============================================================ */
    public function update_status_ajax($id)
    {
        if (!is_admin()
        && !has_permission('internship_management', '', 'edit')
        && !has_permission('internship_job_orders', '', 'edit')
        && !has_permission('internship_applications', '', 'edit')) {
            ajax_access_denied();
        }

        $status = $this->input->post('status', true);
        $ok = $this->applications_model->update_status_only((int)$id, (string)$status);

        echo json_encode([
            'success' => (bool)$ok,
            'id'      => (int)$id,
        ]);
        die;
    }
    /* ============================================================
       EDIT
    ============================================================ */
 /* ============================================================
   EDIT
============================================================ */
public function edit($id)
{
    if (!is_admin()
        && !has_permission('internship_management', '', 'edit')
        && !has_permission('internship_job_orders', '', 'edit')
        && !has_permission('internship_applications', '', 'edit')) {
        access_denied();
    }

    $application = $this->applications_model->get($id);
    if (!$application) {
        blank_page('Ứng viên không tồn tại');
    }

    if ($this->input->post()) {

        $post = $this->input->post();

        $this->map_ai_fields($post);
        
       // Xử lý trường học
        /*if (isset($post['school_name']) && $post['school_name'] === '__new__') {
            $newSchool = trim((string)($post['school_name_new'] ?? ''));
        
            if ($newSchool === '') {
                set_alert('warning', 'Vui lòng nhập tên trường mới.');
                redirect(admin_url('internship_management/internship_applications/edit/' . $id));
            }
        
            $post['school_name'] = $newSchool;
        } else {
            $post['school_name_new'] = null;
        }*/
        
        // Xử lý trường học
        if (isset($post['school_name']) && $post['school_name'] === '__new__') {
            $newSchool = trim((string)($post['school_name_new'] ?? ''));
        
            if ($newSchool === '') {
                set_alert('warning', 'Vui lòng nhập tên trường mới.');
                redirect(admin_url('internship_management/internship_applications/edit/' . $id));
            }
        
            $post['school_name'] = $newSchool;
        }
        
        unset($post['school_name_new']);
        
        // Đồng bộ trường học sang danh mục trường đối tác để Đơn tuyển cũng dùng được
        if (!empty($post['school_name'])) {
            $this->job_orders_model->ensure_partner_school($post['school_name']);
        }

        // Upload CV
        $cv = $this->_upload_cv(false);
        if ($cv) {
            $post['cv_file']      = $cv['file_name'];
            $post['cv_file_type'] = $cv['file_type'];
        }

        // Upload avatar
        $avatar = $this->_upload_avatar();
        if ($avatar) {
            $post['avatar']      = $avatar['file_name'];
            $post['avatar_type'] = $avatar['file_type'];
        }

        // Update DB
        $ok = $this->applications_model->update($id, $post);
        // ============================================================
// ⭐ AUTO MOVE TO INTERN JAPAN
// Khi status = da_xuat_canh hoặc da_nhap_canh
// ============================================================
if (!empty($post['status']) && in_array($post['status'], ['da_xuat_canh', 'da_nhap_canh'])) {

    $this->load->model('internship_management/internship_japan_model', 'japan_model');

    // Lấy lại ứng viên sau update
    $app = $this->applications_model->get($id);

    // Lấy thông tin sinh viên
    $student = $this->applications_model->get_student($app['student_id']);
    if (!$student) {
        log_activity('INTERN_JAPAN_ERROR: student not found for app ' . $id);
        goto _continue_update;
    }

    // Kiểm tra đã tồn tại
    $exists = $this->japan_model->check_exists($student['id']);

    // Map đúng field theo DB
    $arrive  = !empty($app['date_arrival']) ? $app['date_arrival'] : date('Y-m-d');
    $months  = !empty($app['internship_months']) ? (int)$app['internship_months'] : 12;
    $return  = date('Y-m-d', strtotime("+{$months} months", strtotime($arrive)));

    // Chuẩn hóa data
    $data_jp = [
        'student_id'      => $student['id'],
        'full_name'       => $student['full_name'],
        'birthday'        => $student['birthday'],
        'school_name'     => $student['school_name'],
        'email'           => $student['email'],
        'phone_student'   => $student['phone_student'],
        'company_receive' => $app['receiver_company'] ?? '',
        'arrival_date'    => $arrive,
        'duration_months' => $months,
        'return_date'     => $return,
        'staff_incharge'  => $app['staff_id'] ?? null,
        'updated_at'      => date('Y-m-d H:i:s'),
    ];

    if (!$exists) {
        // ➕ ADD nếu chưa có
        $data_jp['created_at'] = date('Y-m-d H:i:s');
        $this->japan_model->add($data_jp);
    } else {
        // 🔄 UPDATE nếu đã có
        $this->japan_model->update_by_student($student['id'], $data_jp);
    }
}

_continue_update:

        if ($ok) {

            // ----------------------------
            // ⭐ TỰ ĐỘNG CẬP NHẬT LỊCH
            // ----------------------------
            $app = $this->applications_model->get($id);
            $this->calendar_model->sync_application_events($app);

            set_alert('success', 'Cập nhật ứng viên thành công.');
        } else {
            set_alert('danger', 'Không thể cập nhật ứng viên.');
        }

        redirect(admin_url('internship_management/internship_applications'));
    }

    /*$data['job_orders']  = $this->job_orders_model->get_all_for_select();
    $data['application'] = $application;
    $data['title']       = 'Cập nhật học sinh';

    $this->load->view('internship_management/applications/edit', $data);*/
    $data['job_orders']  = $this->job_orders_model->get_all_for_select();
    $data['schools']     = $this->applications_model->get_school_options();
    $data['application'] = $application;
    $data['title']       = 'Cập nhật học sinh';
    
    $this->load->view('internship_management/applications/edit', $data);
}

    /* ============================================================
       DELETE
    ============================================================ */
    public function delete($id)
    {
        if (!has_permission('internship_management', '', 'delete')) {
            access_denied();
        }

        $ok = $this->applications_model->delete($id);

        set_alert($ok ? 'success' : 'danger',
            $ok ? 'Đã xoá ứng viên.' : 'Không thể xoá.');

        redirect(admin_url('internship_management/internship_applications'));
    }

    /* ============================================================
       XEM NHANH POPUP (AJAX)
    ============================================================ */
    public function view_ajax($id)
    {
        // Default CRM link payload
        $crm_link = ['linked' => false, 'crm_id' => 0, 'crm_url' => ''];

        if (!has_permission('internship_management', '', 'view')) {
            ajax_access_denied();
        }

        $app = $this->applications_model->get($id);

        // -----------------------------------------------------------------
        // Ensure we ALWAYS have student_id for linking to Student Client view
        // Some model queries may not include student_id, but DB table does.
        // -----------------------------------------------------------------
        if (is_array($app)) {
            $sid = (int)($app['student_id'] ?? ($app['studentid'] ?? ($app['student_client_id'] ?? ($app['candidate_id'] ?? ($app['rel_id'] ?? 0)))));

            // Fallback query to db_prefix().'internship_applications' by application id
            if ($sid <= 0 && isset($this->db)
                && method_exists($this->db, 'table_exists')
                && $this->db->table_exists(db_prefix() . 'internship_applications')) {
                $tbl = db_prefix() . 'internship_applications';
                $row = $this->db->where('id', (int)$id)->get($tbl)->row_array();
                if ($row) {
                    $sid = (int)($row['student_id'] ?? ($row['studentid'] ?? ($row['student_client_id'] ?? ($row['candidate_id'] ?? ($row['rel_id'] ?? 0)))));
                }
            }

            // Normalize for all views
            $app['student_id'] = $sid;
        }

        if (!$app) {
            echo '<div class="text-danger text-center">Không tìm thấy dữ liệu.</div>';
            return;
        }

        $data['app'] = $app;
        $data['print_no'] = $id;

        
        // ===== CRM link (schema: source_type/source_id + crm_client_id) =====
        /*$crm_table = db_prefix() . 'internship_crm_links';
        $crm_link = ['linked' => false, 'crm_id' => 0, 'crm_url' => ''];
        if ($this->db->table_exists($crm_table)) {
            $row = null;

            // primary: source_type/source_id
            if ($this->db->field_exists('source_type', 'internship_crm_links') && $this->db->field_exists('source_id', 'internship_crm_links')) {
                $this->db->where('source_type', 'application');
                $this->db->where('source_id', (int)$id);
                $row = $this->db->get($crm_table)->row_array();
            }

            // fallback: application_id column (legacy)
            if (!$row && $this->db->field_exists('application_id', 'internship_crm_links')) {
                $this->db->where('application_id', (int)$id);
                $row = $this->db->get($crm_table)->row_array();
            }

            if ($row) {
                $crm_id = 0;
                if (isset($row['crm_client_id'])) $crm_id = (int)$row['crm_client_id'];
                elseif (isset($row['crm_id'])) $crm_id = (int)$row['crm_id'];
                elseif (isset($row['client_id'])) $crm_id = (int)$row['client_id'];
                elseif (isset($row['clientid'])) $crm_id = (int)$row['clientid'];
                elseif (isset($row['customer_id'])) $crm_id = (int)$row['customer_id'];
                elseif (isset($row['customerid'])) $crm_id = (int)$row['customerid'];
                elseif (isset($row['userid'])) $crm_id = (int)$row['userid'];

                if ($crm_id > 0) {
                    $crm_link['linked'] = true;
                    $crm_link['crm_id'] = $crm_id;
                    $crm_link['crm_url'] = admin_url('clients/client/' . $crm_id);
                }
            }
        }*/
        
        $crm_link = $this->im_get_crm_link((int)$id, (int)($app['student_id'] ?? 0));
        $data['crm_link'] = $crm_link;


        $this->load->view('applications/view_ajax', $data);
    }

    /**
     * Student profile (by application id)
     */
    public function profile($id)
    {
        if (!has_permission('internship_management', '', 'view') && !is_admin()) {
            access_denied('internship_management');
        }

        $id = (int)$id;
        $app = $this->applications_model->get($id);
        if (!$app) {
            set_alert('danger', 'Không tìm thấy hồ sơ.');
            redirect(admin_url('internship_management/internship_applications'));
        }

        // CRM link (schema: source_type/source_id + crm_client_id)
        /*$crm_table = db_prefix() . 'internship_crm_links';
        $crm_link = ['linked' => false, 'crm_id' => 0, 'crm_url' => ''];
        if ($this->db->table_exists($crm_table)) {
            $row = null;
            if ($this->db->field_exists('source_type', 'internship_crm_links') && $this->db->field_exists('source_id', 'internship_crm_links')) {
                $this->db->where('source_type', 'application');
                $this->db->where('source_id', $id);
                $row = $this->db->get($crm_table)->row_array();
            }
            if (!$row && $this->db->field_exists('application_id', 'internship_crm_links')) {
                $this->db->where('application_id', $id);
                $row = $this->db->get($crm_table)->row_array();
            }
            if ($row) {
                $crm_id = 0;
                if (isset($row['crm_client_id'])) $crm_id = (int)$row['crm_client_id'];
                elseif (isset($row['crm_id'])) $crm_id = (int)$row['crm_id'];
                elseif (isset($row['client_id'])) $crm_id = (int)$row['client_id'];
                if ($crm_id > 0) {
                    $crm_link['linked'] = true;
                    $crm_link['crm_id'] = $crm_id;
                    $crm_link['crm_url'] = admin_url('clients/client/' . $crm_id);
                }
            }
        }*/
        $crm_link = $this->im_get_crm_link((int)$id, (int)($app['student_id'] ?? 0));

        // --- Build $student object for tabs compatibility (legacy tabs expect $student->id and fields) ---
        $appArr = is_array($app) ? $app : (array)$app;

        $student = new stdClass();
        // IMPORTANT: In this module, "profile" is by application_id (each application = 1 row). Tabs were written to use application_id as student_id.
        $student->id           = $id;
        $student->full_name    = $appArr['full_name'] ?? '';
        $student->email        = $appArr['email'] ?? '';
        $student->phone        = $appArr['phone_student'] ?? ($appArr['phone'] ?? '');
        $student->parent_phone = $appArr['phone_parent'] ?? ($appArr['parent_phone'] ?? '');
        $student->address      = $appArr['address'] ?? '';
        $student->university   = $appArr['school_name'] ?? ($appArr['university'] ?? '');
        $student->major        = $appArr['major'] ?? '';
        $student->japanese_level = $appArr['japanese_level'] ?? '';
        $student->english_level  = $appArr['english_level'] ?? '';
        $student->dob          = $appArr['birthday'] ?? ($appArr['dob'] ?? '');
        $student->photo        = $appArr['avatar'] ?? ($appArr['photo'] ?? '');
        $student->status       = $appArr['status'] ?? '';
        $student->interview_result = $appArr['interview_result'] ?? '';

        // Resolve related Job Order
        $job = [];
        $job_order_id = (int)($appArr['job_order_id'] ?? 0);
        if ($job_order_id > 0) {
            if (!isset($this->job_orders_model)) {
                $this->load->model('internship_management/job_orders_model', 'job_orders_model');
            }
            if (method_exists($this->job_orders_model, 'get')) {
                $job = (array)$this->job_orders_model->get($job_order_id);
            }
        }

        // CRM client id for invoices/contracts tabs
        $client_id = (int)($crm_link['crm_id'] ?? 0);


        $data = [];
        $data['app'] = is_array($app) ? $app : (array)$app;
        $data['job'] = $job;
        $data['student'] = $student;
        $data['student_id'] = $id;
        $data['client_id'] = $client_id;
        $data['crm_link'] = $crm_link;

        $this->load->view('applications/profile', $data);
    }



    /* ============================================================
       XEM FILE CV
    ============================================================ */
    public function preview_file($id)
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied();
        }

        $app = $this->applications_model->get($id);
        if (!$app || empty($app['cv_file'])) {
            blank_page('Không tìm thấy file CV.');
        }

        $path = FCPATH . 'uploads/internship_cv/' . $app['cv_file'];

        if (!file_exists($path)) {
            blank_page('File CV không tồn tại trên hệ thống.');
        }

        $mime = get_mime_by_extension($path);
        if (!$mime) {
            $mime = 'application/octet-stream';
        }

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));

        readfile($path);
        exit;
    }

    /* ============================================================
       AI: TÁCH THÔNG TIN TỪ CV – GEMINI
       Route: admin/internship_management/internship_applications/extract_from_cv
    ============================================================ */
   public function extract_from_cv()
{
    if (!has_permission('internship_management', '', 'create')) {
        ajax_access_denied();
    }

    // CHẶN MỌI OUTPUT RÁC
    @ob_end_clean();
    ob_start();
    header_remove(); // remove php headers that cause html
    ini_set('display_errors', 0); // tuyệt đối tắt warning
    error_reporting(0);

    header('Content-Type: application/json; charset=utf-8');

    // --- Kiểm tra file ---
    if (empty($_FILES['cv_file']['name'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Chưa chọn file CV',
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    // Upload CV tạm
    $cv = $this->_upload_cv(true);
    if (!$cv) {
        echo json_encode([
            'success' => false,
            'message' => 'Không upload được file CV',
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    $filePath = $cv['full_path'];
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // --- Đọc TEXT ---
    try {
        $avatarBase64 = '';

if (in_array($ext, ['doc', 'docx'])) {
    // Lấy text
    $plainText = $this->_docx_to_text($filePath);

    // Lấy avatar base64 từ DOCX
    $avatarBase64 = $this->_extract_avatar_from_docx_base64($filePath);

} else {
    // PDF hoặc ảnh
    $plainText = @file_get_contents($filePath);
}
    } catch (Exception $e) {
        @unlink($filePath);
        echo json_encode([
            'success' => false,
            'message' => 'Không đọc được nội dung CV: '.$e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    if (trim($plainText) === '') {
        @unlink($filePath);
        echo json_encode([
            'success' => false,
            'message' => 'CV không có nội dung.',
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    // --- Gọi AI ---
    try {
        $ai = $this->_gemini_extract_personal_info($plainText);
    } catch (Exception $e) {
        @unlink($filePath);
        echo json_encode([
            'success' => false,
            'message' => 'AI lỗi: '.$e->getMessage(),
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    @unlink($filePath);

    // --- Fix missing fields ---
   $defaults = [
    'full_name'      => '',
    'birthday'       => '',
    'email'          => '',
    'phone_student'  => '',
    'phone_parent'   => '',
    'address'        => '',
    'school_name'    => '',
    'major'          => '',
    'japanese_level' => '',
    'gender'         => '',  // ⭐ ĐÃ THÊM
    'avatar_base64'  => '',
];
    $ai = array_merge($defaults, (array)$ai);
$ai['avatar_base64'] = $avatarBase64 ?: '';
// Nếu có avatar base64 thì lưu thành file
$avatarFile = '';
if (!empty($avatarBase64)) {
    $avatarFile = $this->_save_ai_avatar_from_base64($avatarBase64);
}

$ai['avatar_file'] = $avatarFile;

    // --- TRẢ JSON SAU KHI CLEAN BUFFER ---
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'OK',
        'data'    => $ai,
    ], JSON_UNESCAPED_UNICODE);
    ob_end_flush();
}

    /* ============================================================
       PRIVATE: MAP CÁC FIELD AI / LEGACY SANG FIELD CHUẨN
    ============================================================ */
    private function map_ai_fields(&$post)
    {
        if (!is_array($post)) {
            return;
        }

        // Legacy: fullname → full_name
        if (empty($post['full_name']) && !empty($post['fullname'])) {
            $post['full_name'] = $post['fullname'];
        }

        // Legacy: student_phone → phone_student
        if (empty($post['phone_student']) && !empty($post['student_phone'])) {
            $post['phone_student'] = $post['student_phone'];
        }

        // Legacy: parent_phone → phone_parent
        if (empty($post['phone_parent']) && !empty($post['parent_phone'])) {
            $post['phone_parent'] = $post['parent_phone'];
        }

        // Legacy: university → school_name
        if (empty($post['school_name']) && !empty($post['university'])) {
            $post['school_name'] = $post['university'];
        }

        // Nếu AI trả birthday dạng text → giữ nguyên, model sẽ to_sql_date()
    }

    /* ============================================================
       PRIVATE: UPLOAD CV
       $tmp = true  → uploads/internship_cv_tmp/
       $tmp = false → uploads/internship_cv/
    ============================================================ */
    private function _upload_cv($tmp = false)
    {
        if (empty($_FILES['cv_file']['name'])) {
            return false;
        }

        $folder = $tmp ? 'uploads/internship_cv_tmp/' : 'uploads/internship_cv/';
        $path   = FCPATH . $folder;

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $config = [
            'upload_path'   => $path,
            'allowed_types' => 'pdf|doc|docx',
            'encrypt_name'  => true,
            'max_size'      => 10240, // 10MB
        ];

        $this->load->library('upload', $config, 'cv_upload');

        if (!$this->cv_upload->do_upload('cv_file')) {
            log_activity('CV Upload Error: ' . $this->cv_upload->display_errors('', ''));
            return false;
        }

        $data              = $this->cv_upload->data();
        $data['full_path'] = $path . $data['file_name'];

        return $data;
    }

    /* ============================================================
       PRIVATE: UPLOAD AVATAR
    ============================================================ */
    private function _upload_avatar()
    {
        if (empty($_FILES['avatar']['name'])) {
            return false;
        }

        $path = FCPATH . 'uploads/internship_avatar/';

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $config = [
            'upload_path'   => $path,
            'allowed_types' => 'jpg|jpeg|png',
            'encrypt_name'  => true,
            'max_size'      => 4096, // 4MB
        ];

        $this->load->library('upload', $config, 'avatar_upload');

        if (!$this->avatar_upload->do_upload('avatar')) {
            log_activity('Avatar Upload Error: ' . $this->avatar_upload->display_errors('', ''));
            return false;
        }

        $data              = $this->avatar_upload->data();
        $data['full_path'] = $path . $data['file_name'];

        return $data;
    }

    /* ============================================================
       PRIVATE: DOCX → TEXT (PHPWORD)
    ============================================================ */
    private function _docx_to_text($filePath)
    {
        try {
            $phpWord = IOFactory::load($filePath);
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

                // TEXT
                if (method_exists($e, 'getText')) {
                    $text .= $e->getText() . "\n";
                }
            }
        }

        $text = trim($text);
        if ($text === '') {
            throw new Exception('Không đọc được nội dung Word.');
        }

        return $text;
    }

    /* ============================================================
       PRIVATE: GỌI GEMINI TRÍCH XUẤT THÔNG TIN CÁ NHÂN
       Dùng structured output (response_mime_type + response_json_schema)
    ============================================================ */
    private function _gemini_extract_personal_info($plainText)
    {
        $apiKey = get_option('intern_google_api_key');
        if (!$apiKey) {
            throw new Exception('Thiếu Google API Key (intern_google_api_key)');
        }

        $model = 'models/gemini-2.5-flash-lite';
        $url   = "https://generativelanguage.googleapis.com/v1beta/{$model}:generateContent?key={$apiKey}";

        // JSON Schema cho structured output
        $responseSchema = [
            'type'       => 'object',
           'properties' => [
    'full_name' => ['type' => 'string'],
    'birthday'  => ['type' => 'string'],
    'email'     => ['type' => 'string'],
    'phone_student' => ['type' => 'string'],
    'phone_parent'  => ['type' => 'string'],
    'address'        => ['type' => 'string'],
    'school_name'    => ['type' => 'string'],
    'major'          => ['type' => 'string'],
    'japanese_level' => ['type' => 'string'],
    'gender'         => ['type' => 'string'],  // ⭐ ĐÃ THÊM
    'avatar_base64'  => ['type' => 'string'],
            ],
            'required' => [
    'full_name',
    'birthday',
    'email',
    'phone_student',
    'phone_parent',
    'address',
    'school_name',
    'major',
    'japanese_level',
    'gender',          // ⭐ ĐÃ THÊM
    'avatar_base64',
],
        ];

        $systemPrompt = "
Bạn là hệ thống trích xuất thông tin cá nhân từ CV của ứng viên thực tập sinh Nhật Bản.

Trả về DUY NHẤT một JSON hợp lệ theo đúng schema đã cho (response_json_schema).
Tuyệt đối không ghi chú thích, không markdown, không thêm text.
Nếu không có dữ liệu cho một field thì để chuỗi rỗng \"\".
";

        $payload = [
            'contents' => [[
                'parts' => [
                    ['text' => $systemPrompt],
                    ['text' => "\n===== NỘI DUNG CV =====\n"],
                    ['text' => $plainText],
                ],
            ]],
            'generationConfig' => [
                'response_mime_type'   => 'application/json',
                'response_json_schema' => $responseSchema,
            ],
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
            throw new Exception('CURL error: ' . $err);
        }

        // Vì đã dùng response_mime_type = application/json
        // nên response là JSON đúng schema
        $data = json_decode($res, true);

// Nếu Gemini trả JSON lồng (embedded JSON inside candidate content)
if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    $embedded = $data['candidates'][0]['content']['parts'][0]['text'];

    // Text chứa JSON thật
    $decoded = json_decode($embedded, true);
    if (is_array($decoded)) {
        return $decoded;
    }
}

if (!is_array($data)) {
    throw new Exception('AI trả về dữ liệu không phải JSON hợp lệ.');
}

return $data;
}
    /* ============================================================
       PRIVATE: CHUẨN HOÁ NGÀY VỀ YYYY-MM-DD
    ============================================================ */
    private function _normalize_date($val)
    {
        $val = trim((string)$val);
        if ($val === '') {
            return '';
        }

        // Nhật: 1999年12月24日
        $val = str_replace(['年', '月', '日'], ['-', '-', ''], $val);

        // dd/mm/yyyy → yyyy-mm-dd
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // yyyy/mm/dd hoặc yyyy-mm-dd
        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        $ts = strtotime($val);
        if ($ts) {
            return date('Y-m-d', $ts);
        }

        return $val;
    }
    /* ============================================================
   PRIVATE: EXTRACT AVATAR FROM DOCX → BASE64 (CHO HIỂN THỊ)
============================================================ */
private function _extract_avatar_from_docx_base64($filePath)
{
    $zip = new ZipArchive;

    if ($zip->open($filePath) === true) {

        for ($i = 0; $i < $zip->numFiles; $i++) {

            $name = $zip->getNameIndex($i);

            // Lấy ảnh đầu tiên trong folder word/media/
            if (preg_match('/word\/media\/image/i', $name)) {

                $imgData = $zip->getFromIndex($i);

                // Xác định đuôi ảnh
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png'])) {
                    $ext = 'jpg'; // fallback
                }

                $zip->close();

                // Convert sang Base64 để hiển thị ngay
                $base64 = 'data:image/'.$ext.';base64,' . base64_encode($imgData);

                return $base64;
            }
        }

        $zip->close();
    }

    return '';
}
/* ============================================================
   PRIVATE: LƯU BASE64 AVATAR THÀNH FILE JPG
============================================================ */
private function _save_ai_avatar_from_base64($base64)
{
    if (!$base64 || strpos($base64, 'data:image') !== 0) {
        return false;
    }

    $path = FCPATH . 'uploads/internship_avatar/';
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }

    // tách header
    $data = explode(',', $base64);
    if (count($data) != 2) return false;

    $imgData = base64_decode($data[1]);
    $fileName = uniqid('ai_avatar_') . '.jpg';

    file_put_contents($path . $fileName, $imgData);

    return $fileName;
}
private function translate_status($status)
{
    $map = [
        'applied'           => _l('status_applied'),
        'interview_scheduled' => _l('status_interview_scheduled'),
        'interview_fail'    => _l('status_interview_fail'),
        'pass'              => _l('status_pass'),
        'prepare_documents' => _l('status_prepare_documents'),
        'done_documents'    => _l('status_done_documents'),
    ];

    return isset($map[$status]) ? $map[$status] : $status;
}
/* ============================================================
   /* =============================================================
   PRINT – IN THÔNG TIN ỨNG VIÊN (A4)
============================================================= */
public function print($id = 0)
{
    if (!has_permission('internship_management', '', 'view')) {
        access_denied();
    }

    $id = (int)$id;
    if ($id <= 0) {
        show_404();
    }

    // ===== 1. Nếu là application_id =====
    /*$app = $this->applications_model->get($id);
    if ($app) {
        $data['app'] = $app;
        $data['title'] = 'In thông tin ứng viên';
        $this->load->view('internship_management/applications/print', $data);
        return;
    }*/
    
    $app = $this->applications_model->get($id);
    if ($app) {
    
        // Chuẩn hóa alias để file print dùng ổn định hơn
        $app['job_name']       = $app['job_name'] ?? ($app['job_title'] ?? '');
        $app['apply_date']     = $app['apply_date'] ?? ($app['datecreated'] ?? '');
        $app['status_note']    = $app['status_note'] ?? ($app['note'] ?? '');
        $app['months']         = $app['months'] ?? ($app['internship_months'] ?? '');
    
        // Fallback field VI / JP
        $app['school_name_vi'] = $app['school_name_vi'] ?? ($app['school_name'] ?? '');
        $app['school_name_ja'] = $app['school_name_ja'] ?? ($app['school_name_jp'] ?? ($app['school_name'] ?? ''));
    
        $app['major_vi']       = $app['major_vi'] ?? ($app['major'] ?? '');
        $app['major_jp']       = $app['major_jp'] ?? ($app['major'] ?? '');
    
        $app['company_name_vi'] = $app['company_name_vi'] ?? ($app['receiver_company'] ?? '');
        $app['company_name_jp'] = $app['company_name_jp'] ?? ($app['receiver_company'] ?? '');
        
    			// Địa chỉ công ty / đơn tuyển
		$app['receiver_address_vi'] = $app['receiver_address_vi'] ?? ($app['receiver_address'] ?? ($app['address_vi'] ?? ''));
		$app['receiver_address_jp'] = $app['receiver_address_jp'] ?? ($app['address_jp'] ?? ($app['receiver_address'] ?? ''));

		// Địa chỉ ứng viên
		$app['address_vi'] = $app['address_vi'] ?? ($app['address'] ?? '');
		$app['address_jp'] = $app['address_jp'] ?? ($app['address'] ?? '');

		// Tên Katakana / Furigana nếu có
		$app['full_name_katakana'] = $app['full_name_katakana']
			?? ($app['name_katakana']
			?? ($app['furigana']
			?? ($app['full_name_kana'] ?? '')));
    
        // Nếu có ai_json thì ưu tiên lấy bản dịch
        if (!empty($app['ai_json'])) {
            $ai = json_decode($app['ai_json'], true);
            if (is_array($ai)) {
                if (!empty($ai['school_name_vi'])) $app['school_name_vi'] = $ai['school_name_vi'];
                if (!empty($ai['school_name_ja'])) $app['school_name_ja'] = $ai['school_name_ja'];
                if (!empty($ai['school_name_jp'])) $app['school_name_ja'] = $ai['school_name_jp'];
    
                if (!empty($ai['major_vi'])) $app['major_vi'] = $ai['major_vi'];
                if (!empty($ai['major_ja'])) $app['major_jp'] = $ai['major_ja'];
                if (!empty($ai['major_jp'])) $app['major_jp'] = $ai['major_jp'];
    
                if (!empty($ai['company_name_vi']) && empty($app['company_name_vi'])) $app['company_name_vi'] = $ai['company_name_vi'];
                if (!empty($ai['company_name_jp']) && empty($app['company_name_jp'])) $app['company_name_jp'] = $ai['company_name_jp'];
                
                if (!empty($ai['address_jp'])) $app['address_jp'] = $ai['address_jp'];
                if (!empty($ai['receiver_address_jp'])) $app['receiver_address_jp'] = $ai['receiver_address_jp'];
                if (!empty($ai['company_address_jp']) && empty($app['receiver_address_jp'])) $app['receiver_address_jp'] = $ai['company_address_jp'];

                if (!empty($ai['full_name_katakana'])) $app['full_name_katakana'] = $ai['full_name_katakana'];
                if (!empty($ai['name_katakana']) && empty($app['full_name_katakana'])) $app['full_name_katakana'] = $ai['name_katakana'];
                if (!empty($ai['furigana']) && empty($app['full_name_katakana'])) $app['full_name_katakana'] = $ai['furigana'];
            }
        }
    
        $data['app'] = $app;
        $data['title'] = 'In thông tin ứng viên';
        $this->load->view('internship_management/applications/print', $data);
        return;
    }
    
    

    // ===== 2. Nếu không có application -> coi là student_id =====
    $student_id = $id;
    $student = null;

    if ($this->db->table_exists('tblinternship_students')) {
        $student = $this->db
            ->where('id', $student_id)
            ->get('tblinternship_students')
            ->row_array();
    }

    if (!$student) {
        show_404();
    }

    // ===== Map student -> app (đúng field print.php đang dùng) =====
    $app = [];

    $app['id'] = 0;
    $app['student_id'] = $student_id;

    $app['full_name']  = $student['full_name'] ?? '';
    $app['birthday']   = $student['birthday'] ?? '';
    $app['gender']     = $student['gender'] ?? '';
    $app['email']      = $student['email'] ?? '';
    $app['phone_student'] = $student['phone_student'] ?? '';
    $app['phone_parent']  = $student['phone_parent'] ?? '';
    $app['address']    = $student['address'] ?? '';

    $app['school_name'] = $student['school_name'] ?? '';
    $app['major']       = $student['major'] ?? '';
    $app['japanese_level'] = $student['jlpt'] ?? '';
    $app['english_level']  = $student['english_level'] ?? '';

    $app['receiver_company'] = $student['company'] ?? '';
    $app['receiver_address'] = $student['company_address'] ?? '';
    $app['receiver_prefecture'] = $student['prefecture'] ?? '';

    $app['interview_result'] = $student['interview_result'] ?? '';
    $app['status'] = $student['status'] ?? '';

    $app['created_at'] = $student['created_at'] ?? '';

    $data['app'] = $app;
    $data['title'] = 'In thông tin ứng viên';

    $this->load->view('internship_management/applications/print', $data);
}
    /* ============================================================
       AJAX: Update interview_result / dossier_progress (SYNC + CSRF)
       - Used by applications index view
       - Keeps existing functions intact
    ============================================================ */
    public function update_application_state()
{
    // Standardized AJAX endpoint used by BOTH views
    header('Content-Type: application/json; charset=utf-8');

    if (!has_permission('internship_management', '', 'edit')) {
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

    $table = db_prefix().'internship_applications';

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


    /**
     * Xem chi tiết hồ sơ ứng tuyển (READ-ONLY)
     * Route: /admin/internship_management/internship_applications/view/{id}
     */
    public function view($id)
    {
        // Ensure model alias exists
        $this->load->model('internship_management/Internship_applications_model', 'internship_applications_model');
        if (!has_permission('internship_applications', '', 'view') && !is_admin()) {
            access_denied('internship_applications');
        }

        $id = (int)$id;
        $app = $this->internship_applications_model->get($id);

        if (empty($app)) {
            show_404();
        }

        // Lấy tên công ty JP/VI đúng từ job_orders (nếu có)
        if (!empty($app['job_order_id'])) {
            $this->db->select('jo.company_name_jp, jo.company_name_vi, jo.employer_id, e.name_jp as employer_name_jp, e.name_vi as employer_name_vi');
            $this->db->from(db_prefix().'internship_job_orders jo');
            $this->db->join(db_prefix().'internship_employers e', 'e.id = jo.employer_id', 'left');
            $this->db->where('jo.id', (int)$app['job_order_id']);
            $jo = $this->db->get()->row_array();

            if ($jo) {
                $app['company_name_jp'] = $jo['company_name_jp'] ?? '';
                $app['company_name_vi'] = $jo['company_name_vi'] ?? '';
                $app['employer_name_jp'] = $jo['employer_name_jp'] ?? '';
                $app['employer_name_vi'] = $jo['employer_name_vi'] ?? '';
            }
        }

        // Nếu ai_json có bản dịch, ưu tiên hiển thị
        if (!empty($app['ai_json'])) {
            $ai = json_decode($app['ai_json'], true);
            if (is_array($ai)) {
                if (!empty($ai['school_name_vi'])) $app['school_name_vi'] = $ai['school_name_vi'];
                if (!empty($ai['major_vi'])) $app['major_vi'] = $ai['major_vi'];
            }
        }

        $data['title'] = 'Xem hồ sơ ứng tuyển #' . $id;
        $data['app'] = is_array($app) ? $app : (array)$app;
        $data['job'] = $job;
        $data['student'] = $student;
        $data['student_id'] = $id;
        $data['client_id'] = $client_id;
        $data['crm_link'] = $crm_link;

        $this->load->view('internship_management/applications/view', $data);
    }

    /**
     * Xem profile sinh viên (đúng yêu cầu có nút profile riêng)
     * Route: /admin/internship_management/internship_applications/student/{student_id}
     */
    public function student($student_id)
    {
        // Backward compatible: treat param as application id and show profile
        return $this->profile($student_id);
    }


    //them 3 ham 
    private function im_find_crm_link_row($application_id, $student_id = 0)
    {
        $crm_table_name = 'internship_crm_links';
        $crm_table = db_prefix() . $crm_table_name;
    
        if (!$this->db->table_exists($crm_table)) {
            return null;
        }
    
        $row = null;
    
        // Ưu tiên tìm theo schema mới: source_type + source_id
        if ($this->db->field_exists('source_type', $crm_table_name) && $this->db->field_exists('source_id', $crm_table_name)) {
            $row = $this->db
                ->where('source_type', 'application')
                ->where('source_id', (int)$application_id)
                ->order_by('id', 'DESC')
                ->get($crm_table)
                ->row_array();
        }
    
        // Fallback schema cũ: application_id
        if (!$row && $this->db->field_exists('application_id', $crm_table_name)) {
            $row = $this->db
                ->where('application_id', (int)$application_id)
                ->order_by('id', 'DESC')
                ->get($crm_table)
                ->row_array();
        }
    
        // Fallback cuối: student_id
        if (!$row && $student_id > 0 && $this->db->field_exists('student_id', $crm_table_name)) {
            $row = $this->db
                ->where('student_id', (int)$student_id)
                ->order_by('id', 'DESC')
                ->get($crm_table)
                ->row_array();
        }
    
        return $row ?: null;
    }
    
    private function im_extract_crm_id($row)
    {
        if (!is_array($row) || empty($row)) {
            return 0;
        }
    
        foreach (['crm_client_id', 'crm_id', 'client_id', 'customer_id', 'clientid', 'customerid', 'userid'] as $k) {
            if (isset($row[$k]) && (int)$row[$k] > 0) {
                return (int)$row[$k];
            }
        }
    
        return 0;
    }
    
    private function im_get_crm_link($application_id, $student_id = 0)
    {
        $crm_link = [
            'linked'  => false,
            'crm_id'  => 0,
            'crm_url' => '',
        ];
    
        $row = $this->im_find_crm_link_row((int)$application_id, (int)$student_id);
        if ($row) {
            $crm_id = $this->im_extract_crm_id($row);
            if ($crm_id > 0) {
                $crm_link['linked']  = true;
                $crm_link['crm_id']  = $crm_id;
                $crm_link['crm_url'] = admin_url('clients/client/' . $crm_id);
            }
        }
    
        return $crm_link;
    }




    /**
     * Đẩy hồ sơ sang CRM (tạo client cơ bản nếu chưa liên kết) và lưu map vào internship_crm_links
     * Route: /admin/internship_management/internship_applications/push_crm/{application_id}
     */
    /*public function push_crm($id)
    {
        if (!has_permission('internship_management', '', 'edit') && !is_admin()) {
            access_denied('internship_management');
        }

        $id = (int)$id;
        $app = $this->applications_model->get($id);
        if (!$app) {
            set_alert('danger', 'Không tìm thấy hồ sơ.');
            redirect(admin_url('internship_management/internship_applications'));
        }

        $crm_table_name = 'internship_crm_links';
        $crm_table = db_prefix().$crm_table_name;
        if (!$this->db->table_exists($crm_table)) {
            set_alert('danger', 'Chưa có bảng liên kết CRM (internship_crm_links).');
            redirect(admin_url('internship_management/internship_applications'));
        }

        // Try create CRM client (Perfex clients_model)
        $crm_id = 0;
        if (file_exists(APPPATH.'models/Clients_model.php')) {
            $this->load->model('clients_model');
            $company = !empty($app['full_name']) ? $app['full_name'] : ('Student '.$id);
            $data = [
                'company' => $company,
                'phonenumber' => $app['phone_student'] ?? ($app['phone'] ?? ''),
                'address' => $app['address'] ?? '',
                'city' => '',
                'zip' => '',
                'country' => 0,
                'vat' => '',
                'website' => '',
            ];
            $crm_id = (int)$this->clients_model->add($data);
        }

        if ($crm_id <= 0) {
            set_alert('danger', 'Không thể tạo CRM client (thiếu Clients_model hoặc lỗi dữ liệu).');
            redirect(admin_url('internship_management/internship_applications'));
        }

        // Save link
        $insert = [];

        // schema used in your DB: source_type/source_id + crm_client_id
        if ($this->db->field_exists('source_type', $crm_table_name)) $insert['source_type'] = 'application';
        if ($this->db->field_exists('source_id', $crm_table_name)) $insert['source_id'] = $id;
        if ($this->db->field_exists('crm_client_id', $crm_table_name)) $insert['crm_client_id'] = $crm_id;

        // optional columns
        if ($this->db->field_exists('application_id', $crm_table_name)) $insert['application_id'] = $id;
        $student_id = (int)($app['student_id'] ?? 0);
        if ($student_id > 0 && $this->db->field_exists('student_id', $crm_table_name)) $insert['student_id'] = $student_id;

        if ($this->db->field_exists('created_at', $crm_table_name)) $insert['created_at'] = date('Y-m-d H:i:s');
        if ($this->db->field_exists('updated_at', $crm_table_name)) $insert['updated_at'] = date('Y-m-d H:i:s');

        // If existed row without crm_client_id -> update it instead of insert
        if ($existing) {
            $this->db->where('id', (int)$existing['id']);
            $this->db->update($crm_table, $insert);
        } else {
            $this->db->insert($crm_table, $insert);
        }
        set_alert('success', 'Đã đẩy CRM và liên kết #' . $crm_id);
        redirect(admin_url('internship_management/internship_applications'));
    }*/
    
    //thay ham 
    public function push_crm($id)
    {
        if (!has_permission('internship_management', '', 'edit') && !is_admin()) {
            access_denied('internship_management');
        }
    
        $id = (int)$id;
        $app = $this->applications_model->get($id);
    
        if (!$app) {
            set_alert('danger', 'Không tìm thấy hồ sơ.');
            redirect(admin_url('internship_management/internship_applications'));
        }
    
        $crm_table_name = 'internship_crm_links';
        $crm_table = db_prefix() . $crm_table_name;
    
        if (!$this->db->table_exists($crm_table)) {
            set_alert('danger', 'Chưa có bảng liên kết CRM (internship_crm_links).');
            redirect(admin_url('internship_management/internship_applications'));
        }
    
        $student_id = (int)($app['student_id'] ?? 0);
    
        // 1) Tìm mapping cũ của đúng application này
        $existing = $this->im_find_crm_link_row($id, $student_id);
        $existing_crm_id = $this->im_extract_crm_id($existing);
    
        // 2) Nếu đã có CRM đúng rồi thì không tạo mới nữa
        if ($existing_crm_id > 0) {
            set_alert('success', 'Ứng viên đã liên kết CRM #' . $existing_crm_id);
            redirect(admin_url('internship_management/internship_applications'));
        }
    
        // 3) Tạo client mới trong CRM
        $crm_id = 0;
    
        if (file_exists(APPPATH . 'models/Clients_model.php')) {
            $this->load->model('clients_model');
    
            $company = !empty($app['full_name']) ? trim($app['full_name']) : ('Student ' . $id);
            $phone   = !empty($app['phone_student']) ? trim($app['phone_student']) : (!empty($app['phone']) ? trim($app['phone']) : '');
            $address = !empty($app['address']) ? trim($app['address']) : '';
    
            $client_data = [
                'company'     => $company,
                'phonenumber' => $phone,
                'address'     => $address,
                'city'        => '',
                'zip'         => '',
                'country'     => 0,
                'vat'         => '',
                'website'     => '',
            ];
    
            $crm_id = (int)$this->clients_model->add($client_data);
        }
    
        if ($crm_id <= 0) {
            set_alert('danger', 'Không thể tạo khách hàng CRM cho sinh viên này.');
            redirect(admin_url('internship_management/internship_applications'));
        }
    
        // 4) Chuẩn bị dữ liệu mapping
        $now = date('Y-m-d H:i:s');
        $save = [];
    
        // schema mới
        if ($this->db->field_exists('source_type', $crm_table_name)) {
            $save['source_type'] = 'application';
        }
        if ($this->db->field_exists('source_id', $crm_table_name)) {
            $save['source_id'] = $id;
        }
        if ($this->db->field_exists('crm_client_id', $crm_table_name)) {
            $save['crm_client_id'] = $crm_id;
        }
    
        // schema cũ
        if ($this->db->field_exists('application_id', $crm_table_name)) {
            $save['application_id'] = $id;
        }
        if ($student_id > 0 && $this->db->field_exists('student_id', $crm_table_name)) {
            $save['student_id'] = $student_id;
        }
    
        if ($this->db->field_exists('crm_id', $crm_table_name)) {
            $save['crm_id'] = $crm_id;
        }
        if ($this->db->field_exists('client_id', $crm_table_name)) {
            $save['client_id'] = $crm_id;
        }
        if ($this->db->field_exists('customer_id', $crm_table_name)) {
            $save['customer_id'] = $crm_id;
        }
        if ($this->db->field_exists('clientid', $crm_table_name)) {
            $save['clientid'] = $crm_id;
        }
        if ($this->db->field_exists('customerid', $crm_table_name)) {
            $save['customerid'] = $crm_id;
        }
        if ($this->db->field_exists('userid', $crm_table_name)) {
            $save['userid'] = $crm_id;
        }
    
        if ($this->db->field_exists('updated_at', $crm_table_name)) {
            $save['updated_at'] = $now;
        }
    
        // 5) Update row cũ nếu có, ngược lại insert mới
        if ($existing && !empty($existing['id'])) {
            $this->db->where('id', (int)$existing['id']);
            $this->db->update($crm_table, $save);
        } else {
            if ($this->db->field_exists('created_at', $crm_table_name)) {
                $save['created_at'] = $now;
            }
            $this->db->insert($crm_table, $save);
        }
    
        // 6) Đồng bộ lại vào bảng internship_applications nếu có cột tương ứng
        $app_table_name = 'internship_applications';
        $app_table = db_prefix() . $app_table_name;
    
        if ($this->db->table_exists($app_table)) {
            $app_update = [];
    
            if ($this->db->field_exists('crm_client_id', $app_table_name)) {
                $app_update['crm_client_id'] = $crm_id;
            }
            if ($this->db->field_exists('client_id', $app_table_name)) {
                $app_update['client_id'] = $crm_id;
            }
            if ($this->db->field_exists('crm_sync_status', $app_table_name)) {
                $app_update['crm_sync_status'] = 'synced';
            }
            if ($this->db->field_exists('crm_last_synced_at', $app_table_name)) {
                $app_update['crm_last_synced_at'] = $now;
            }
    
            if (!empty($app_update)) {
                $this->db->where('id', $id)->update($app_table, $app_update);
            }
        }
    
        set_alert('success', 'Đã đẩy CRM và liên kết đúng khách hàng #' . $crm_id);
        redirect(admin_url('internship_management/internship_applications'));
    }
    
    
    /**
     * AJAX: Get job order info for auto fill in application create/edit
     * URL: admin_url('internship_management/internship_applications/job_order_info/{id}')
     */
    public function job_order_info($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return $this->_json(['success' => false, 'message' => 'Invalid job order id']);
        }

        $job = $this->job_orders_model->get($id);
        if (!$job) {
            return $this->_json(['success' => false, 'message' => 'Job order not found']);
        }

        // helper: pick first non-empty field
        $pick = function(array $row, array $keys) {
            foreach ($keys as $k) {
                if (isset($row[$k]) && trim((string)$row[$k]) !== '') return trim((string)$row[$k]);
            }
            return '';
        };

        /*$company = $pick($job, ['company_name_vi','company_name','company_vi','company','company_name_jp']);
        $address = $pick($job, ['address_vi','address','work_address_vi','work_address','address_jp']);
        $pref    = $pick($job, ['receiver_prefecture','prefecture_vi','prefecture','province_vi','province','city','work_city','work_location']);*/
        $company = $pick($job, [
            'receiver_company',
            'company_name_vi',
            'company_name',
            'company_vi',
            'company',
            'company_name_jp'
        ]);
        $address = $pick($job, [
            'receiver_address',
            'address_vi',
            'address',
            'work_address_vi',
            'work_address',
            'address_jp'
        ]);
        $pref = $pick($job, [
            'receiver_prefecture',
            'prefecture_vi',
            'prefecture',
            'province_vi',
            'province',
            'city',
            'work_city',
            'work_location'
        ]);

        // If no prefecture field exists in DB, try to derive from address
        if ($pref === '' && $address !== '') {
            $pref = $this->_guess_prefecture_from_address($address);
        }

        $months = $pick($job, ['contract_months','months','duration_months','period_months','internship_months']);
        $months = (int)$months;

        $interview = $this->_format_dmy($pick($job, ['interview_date','interview_at','interview_day']));
        $entry     = $this->_format_dmy($pick($job, ['entry_date','expected_entry_date','arrival_date','depart_date']));

        $return    = $this->_format_dmy($pick($job, ['return_date','expected_return_date','end_date','expected_end_date']));

        // compute return date if missing and we have entry_date + months
        if ($return === '' && $entry !== '' && $months > 0) {
            $return = $this->_add_months_to_dmy($entry, $months);
        }

        return $this->_json([
            'success' => true,
            'data' => [
                'receiver_company'     => $company,
                'receiver_address'     => $address,
                'receiver_prefecture'  => $pref,
                'months'               => $months > 0 ? (string)$months : '',
                'interview_date'       => $interview,
                'entry_date'           => $entry,
                'return_date'          => $return,
            ]
        ]);
    }

    private function _json($payload)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function _format_dmy($val)
    {
        $s = trim((string)$val);
        if ($s === '') return '';
        // already d/m/Y
        if (preg_match('/^\\d{1,2}\\/\\d{1,2}\\/\\d{4}$/', $s)) return $s;
        // Y-m-d
        if (preg_match('/^(\\d{4})-(\\d{1,2})-(\\d{1,2})/', $s, $m)) {
            $y = $m[1]; $mo = (int)$m[2]; $d = (int)$m[3];
            return str_pad($d,2,'0',STR_PAD_LEFT).'/'.str_pad($mo,2,'0',STR_PAD_LEFT).'/'.$y;
        }
        return $s;
    }

    private function _add_months_to_dmy($dmy, $months)
    {
        $dmy = trim((string)$dmy);
        if (!preg_match('/^(\\d{1,2})\\/(\\d{1,2})\\/(\\d{4})$/', $dmy, $m)) return '';
        $d = (int)$m[1]; $mo = (int)$m[2]; $y = (int)$m[3];

        try {
            $dt = new DateTime(sprintf('%04d-%02d-%02d', $y, $mo, $d));
            $dt->modify('+' . (int)$months . ' month');
            return $dt->format('d/m/Y');
        } catch (Exception $e) {
            return '';
        }
    }

    private function _guess_prefecture_from_address($address)
    {
        $a = trim((string)$address);
        if ($a === '') return '';
        // JP suffix
        if (preg_match('/([^\\s,、]+(?:都|道|府|県))/', $a, $m)) return $m[1];

        // VN heuristic: split by comma/dash and pick last segment
        $parts = preg_split('/[,-–]/', $a);
        $parts = array_values(array_filter(array_map('trim', $parts)));
        if (count($parts) === 0) return '';
        return $parts[count($parts)-1];
    }

}