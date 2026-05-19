<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Internship_calendar extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // Model lịch
        $this->load->model(
            'internship_management/internship_calendar_model',
            'calendar_model'
        );

        // Perfex staff model
        $this->load->model('staff_model');
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
       TRANG LỊCH – VIEW
    ============================================================ */
    public function index()
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied();
        }

        // Dữ liệu cho bộ lọc
        $data['staffs'] = $this->staff_model->get();

        $data['students'] = $this->db
            ->select('id, full_name')
            ->from('tblinternship_applications')
            ->order_by('full_name', 'ASC')
            ->get()->result_array();

        $data['job_orders'] = $this->db
            ->select('id, company_name_vi, job_title_vi')
            ->from('tblinternship_job_orders')
            ->order_by('id', 'DESC')
            ->get()->result_array();

        $data['title'] = 'Lịch Công Việc – Internship Nhật Bản';
        $this->load->view('internship_management/calendar/index', $data);
    }

    /* ============================================================
       API EVENTS CHO FULLCALENDAR V5
       URL: /admin/internship_management/internship_calendar/events
    ============================================================ */
    public function events()
    {
        if (!has_permission('internship_management', '', 'view')) {
            ajax_access_denied();
        }

        $start = $this->input->get('start');
        $end   = $this->input->get('end');

        $filters = [
            'event_type'   => $this->input->get('event_type'),
            'staff_id'     => $this->input->get('staff_id'),
            'job_order_id' => $this->input->get('job_order_id'),
            'student_id'   => $this->input->get('student_id'),
        ];

        $events = $this->calendar_model->get_events($start, $end, $filters);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($events);
        exit;
    }

    /* ============================================================
       TẠO SỰ KIỆN MANUAL
    ============================================================ */
    public function create_event()
    {
        if (!has_permission('internship_management', '', 'create')) {
            ajax_access_denied();
        }

        $post = $this->input->post();

        $data = [
            'title'       => $post['title'] ?? '',
            'description' => $post['description'] ?? '',
            'event_date'  => $post['event_date'] ?? null,
            'event_type'  => $post['event_type'] ?? 'task',
            'color'       => $post['color'] ?? '#2563eb',
            'staff_id'    => !empty($post['staff_id']) ? (int)$post['staff_id'] : null,
            'job_order_id'=> !empty($post['job_order_id']) ? (int)$post['job_order_id'] : null,
            'student_id'  => !empty($post['student_id']) ? (int)$post['student_id'] : null,
        ];

        $id = $this->calendar_model->add($data);

        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $id ? true : false, 'id' => (int)$id]);
        exit;
    }

    /* ============================================================
       LẤY 1 EVENT MANUAL ĐỂ EDIT
    ============================================================ */
    public function get_event($id)
    {
        if (!has_permission('internship_management', '', 'view')) {
            ajax_access_denied();
        }

        $event = $this->calendar_model->get_by_id((int)$id);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($event);
        exit;
    }

    /* ============================================================
       UPDATE EVENT MANUAL
    ============================================================ */
    public function update_event($id)
    {
        if (!has_permission('internship_management', '', 'edit')) {
            ajax_access_denied();
        }

        $post = $this->input->post();

        $data = [
            'title'       => $post['title'] ?? '',
            'description' => $post['description'] ?? '',
            'event_date'  => $post['event_date'] ?? null,
            'event_type'  => $post['event_type'] ?? 'task',
            'color'       => $post['color'] ?? '#2563eb',
            'staff_id'    => !empty($post['staff_id']) ? (int)$post['staff_id'] : null,
            'job_order_id'=> !empty($post['job_order_id']) ? (int)$post['job_order_id'] : null,
            'student_id'  => !empty($post['student_id']) ? (int)$post['student_id'] : null,
        ];

        $ok = $this->calendar_model->update((int)$id, $data);

        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $ok ? true : false]);
        exit;
    }

    /* ============================================================
       XOÁ SỰ KIỆN MANUAL
    ============================================================ */
    public function delete_event($id)
    {
        if (!has_permission('internship_management', '', 'delete')) {
            ajax_access_denied();
        }

        $ok = $this->calendar_model->delete((int)$id);

        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $ok ? true : false]);
        exit;
    }

    /* ============================================================
       AUTO EVENTS
       ID dạng:
         - auto_interview_job_{jobId}
         - auto_entry_job_{jobId}
         - auto_return_app_{appId}
    ============================================================ */

    public function get_auto_event($auto_id)
    {
        if (!has_permission('internship_management', '', 'view')) {
            ajax_access_denied();
        }

        $info = $this->calendar_model->parse_auto_id($auto_id);
        if (!$info) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(null);
            exit;
        }

        $data = $this->calendar_model->get_auto_event_info($auto_id);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    public function update_auto_event($auto_id)
    {
        if (!has_permission('internship_management', '', 'edit')) {
            ajax_access_denied();
        }

        $info = $this->calendar_model->parse_auto_id($auto_id);
        if (!$info) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'ID lịch tự sinh không hợp lệ.']);
            exit;
        }

        $post = $this->input->post();
        $payload = [
            'event_date'  => $post['event_date'] ?? null,
            'description' => $post['description'] ?? '',
        ];

        $res = $this->calendar_model->update_auto_event($info, $payload);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($res);
        exit;
    }

    /* ============================================================
       ĐỒNG BỘ NGÀY VỀ NƯỚC (tự sinh từ entry_date + số tháng thực tập)
       URL: /admin/internship_management/internship_calendar/sync_return_dates
    ============================================================ */
    public function sync_return_dates()
    {
        if (!has_permission('internship_management', '', 'edit')) {
            ajax_access_denied();
        }

        $limit = (int)($this->input->post('limit') ?? 500);
        if ($limit <= 0) {
            $limit = 500;
        }

        $updated = $this->calendar_model->sync_missing_return_dates($limit);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'updated' => (int)$updated,
            'message' => 'Đã đồng bộ ' . (int)$updated . ' ngày về nước còn thiếu.'
        ]);
        exit;
    }
}