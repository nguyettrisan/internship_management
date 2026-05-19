<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Internship_model
 *
 * Model nền cho bảng internship_students + cung cấp helper workflow dùng chung.
 *
 * Mục tiêu:
 * - Không hardcode các cột có thể khác nhau giữa DB (created_at/datecreated,...)
 * - Thống nhất trạng thái workflow (job order / application / student)
 * - Cung cấp helper để các model khác (job_orders/applications/...) có thể gọi
 *   đồng bộ trạng thái và hiển thị label/màu nhất quán.
 *
 * Lưu ý: module của bạn đang chạy trên Perfex CRM (CI3), db_prefix() = 'tbl'
 */
class Internship_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('internship_management/internship_status');
    }
    
    /**
     * Bảng học viên (students) của module.
     * Không chứa prefix ở đây.
     */
    protected $table = 'internship_students';

    /** Workflow types */
    const WF_JOB_ORDER    = 'job_order';
    const WF_APPLICATION  = 'application';
    const WF_STUDENT      = 'student';

    /**
     * Thứ tự stage để so sánh / đồng bộ.
     */
    private $wfOrder = [
        self::WF_JOB_ORDER => [
            'received',
            'sent_to_schools',
            'recruiting',
            'interview_scheduled',
            'interview_result',
            'docs_preparing',
            'docs_done',
            'waiting_coe',
            'coe_done',
            'entry',
            'done',
            'cancelled',
        ],
        self::WF_APPLICATION => [
            'applied',
            'interview_scheduled',
            'interview_passed',
            'interview_fail',
            'docs_preparing',
            'docs_done',
            'coe_waiting',
            'coe_done',
            'visa_processing',
            'ticket_booking',
            'pre_departure',
            'in_japan',
            'returned',
            'cancelled',
        ],
        self::WF_STUDENT => [
            'docs_preparing',
            'docs_done',
            'coe_waiting',
            'coe_done',
            'visa_processing',
            'pre_departure',
            'in_japan',
            'pre_return',
            'returned',
            'cancelled',
        ],
    ];

    /** alias -> canonical */
    private $wfAlias = [
        self::WF_JOB_ORDER => [
            'has_applicants'     => 'recruiting',
            'prepare_documents'  => 'docs_preparing',
            'done_documents'     => 'docs_done',
            'coe_waiting'        => 'waiting_coe',
            'finished'           => 'done',
        ],
        self::WF_APPLICATION => [
            'prepare_documents'  => 'docs_preparing',
            'done_documents'     => 'docs_done',
            'waiting_coe'        => 'coe_waiting',
            'entry'              => 'in_japan',
            'pre_return'         => 'returned',
        ],
        self::WF_STUDENT => [
            'prepare_documents'  => 'docs_preparing',
            'done_documents'     => 'docs_done',
            'waiting_coe'        => 'coe_waiting',
            'entry'              => 'in_japan',
        ],
    ];

    /** label/color meta */
    private $wfMeta = [
        self::WF_JOB_ORDER => [
            'received'            => ['label' => 'Tiếp nhận đơn',        'color' => 'primary'],
            'sent_to_schools'     => ['label' => 'Đã gửi đơn cho trường', 'color' => 'info'],
            'recruiting'          => ['label' => 'Đang tuyển ứng viên',   'color' => 'info'],
            'interview_scheduled' => ['label' => 'Đã lên lịch phỏng vấn', 'color' => 'warning'],
            'interview_result'    => ['label' => 'Trả KQ PV & làm hồ sơ', 'color' => 'warning'],
            'docs_preparing'      => ['label' => 'Đang chuẩn bị hồ sơ',   'color' => 'warning'],
            'docs_done'           => ['label' => 'Đã xong hồ sơ',         'color' => 'success'],
            'waiting_coe'         => ['label' => 'Chờ kết quả COE',       'color' => 'default'],
            'coe_done'            => ['label' => 'Đã có COE',             'color' => 'success'],
            'entry'               => ['label' => 'Đã nhập cảnh',          'color' => 'success'],
            'done'                => ['label' => 'Hoàn tất chương trình', 'color' => 'default'],
            'cancelled'           => ['label' => 'Đã huỷ',                'color' => 'danger'],
        ],
        self::WF_APPLICATION => [
            'applied'             => ['label' => 'Ứng tuyển',            'color' => 'primary'],
            'interview_scheduled' => ['label' => 'Hẹn phỏng vấn',         'color' => 'warning'],
            'interview_passed'    => ['label' => 'Đậu phỏng vấn',         'color' => 'success'],
            'interview_fail'      => ['label' => 'Rớt phỏng vấn',         'color' => 'danger'],
            'docs_preparing'      => ['label' => 'Chuẩn bị hồ sơ',       'color' => 'warning'],
            'docs_done'           => ['label' => 'Hoàn thành hồ sơ',     'color' => 'success'],
            'coe_waiting'         => ['label' => 'Chờ COE',               'color' => 'default'],
            'coe_done'            => ['label' => 'Đã có COE',             'color' => 'success'],
            'visa_processing'     => ['label' => 'Làm visa',              'color' => 'info'],
            'ticket_booking'      => ['label' => 'Mua vé nhập cảnh',     'color' => 'info'],
            'pre_departure'       => ['label' => 'Chuẩn bị bay',         'color' => 'warning'],
            'in_japan'            => ['label' => 'Đang ở Nhật',           'color' => 'success'],
            'returned'            => ['label' => 'Đã về nước',            'color' => 'default'],
            'cancelled'           => ['label' => 'Huỷ',                   'color' => 'danger'],
        ],
        self::WF_STUDENT => [
            'docs_preparing'      => ['label' => 'Chuẩn bị hồ sơ',       'color' => 'warning'],
            'docs_done'           => ['label' => 'Hoàn thành hồ sơ',     'color' => 'success'],
            'coe_waiting'         => ['label' => 'Chờ COE',               'color' => 'default'],
            'coe_done'            => ['label' => 'Đã có COE',             'color' => 'success'],
            'visa_processing'     => ['label' => 'Làm visa',              'color' => 'info'],
            'pre_departure'       => ['label' => 'Chuẩn bị bay',         'color' => 'warning'],
            'in_japan'            => ['label' => 'Đang ở Nhật',           'color' => 'success'],
            'pre_return'          => ['label' => 'Chuẩn bị về nước',     'color' => 'info'],
            'returned'            => ['label' => 'Đã về nước',            'color' => 'default'],
            'cancelled'           => ['label' => 'Huỷ',                   'color' => 'danger'],
        ],
    ];

    private function tbl()
    {
        return db_prefix() . $this->table;
    }

    /* ==============================
     * CRUD CƠ BẢN
     * ============================== */
    public function get_all()
    {
        $orderCol = $this->safe_date_col($this->tbl(), ['created_at', 'datecreated', 'date_created', 'createdon', 'id']);
        if ($orderCol === '') $orderCol = 'id';

        return $this->db
            ->order_by($orderCol, 'DESC')
            ->get($this->tbl())
            ->result_array();
    }

    public function get($id)
    {
        if (!$id) return [];
        return $this->db->where('id', (int)$id)->get($this->tbl())->row_array();
    }

    public function add($data)
    {
        $data = $this->sanitize($data);

        // Tương thích schema: created_at hoặc datecreated
        $createdCol = $this->safe_date_col($this->tbl(), ['created_at', 'datecreated', 'date_created', 'createdon']);
        if ($createdCol !== '') {
            $data[$createdCol] = date('Y-m-d H:i:s');
        }

        // Normalize status nếu có
        if (isset($data['status'])) {
            $data['status'] = $this->normalize_status(self::WF_STUDENT, $data['status']);
        }

        $this->db->insert($this->tbl(), $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $data = $this->sanitize($data);

        $updatedCol = $this->safe_date_col($this->tbl(), ['updated_at', 'dateupdated', 'updatedon']);
        if ($updatedCol !== '') {
            $data[$updatedCol] = date('Y-m-d H:i:s');
        }

        if (isset($data['status'])) {
            $data['status'] = $this->normalize_status(self::WF_STUDENT, $data['status']);
        }

        $this->db->where('id', (int)$id)->update($this->tbl(), $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        $this->db->where('id', (int)$id)->delete($this->tbl());
        return $this->db->affected_rows();
    }

    /* ==============================
     * SẮP VỀ NƯỚC
     * ============================== */
    public function get_returning_in_days($days = 30)
    {
        $today = date('Y-m-d');
        $to    = date('Y-m-d', strtotime("+{$days} days"));

        return $this->db
            ->where('return_date >=', $today)
            ->where('return_date <=', $to)
            ->get($this->tbl())
            ->result_array();
    }

    public function notify_returning_in_days($days = 30)
    {
        $students = $this->get_returning_in_days($days);
        if (empty($students)) return;

        foreach ($students as $s) {

            $full_name   = $s['full_name'] ?? '';
            $university  = $s['university'] ?? '';
            $company     = $s['company_name'] ?? '';
            $email       = $s['email'] ?? '';
            $phone       = $s['phone'] ?? '';
            $return_date = !empty($s['return_date']) ? _d($s['return_date']) : '';

            $subject = "Nhắc: {$full_name} sẽ về nước ngày {$return_date}";
            $html = "
                Xin chào,<br><br>
                Học sinh <strong>{$full_name}</strong> (Trường {$university})
                dự kiến về nước ngày <b>{$return_date}</b>.<br>
                Đơn vị tiếp nhận: {$company}.<br><br>
                Trân trọng.
            ";

            if (!empty($email)) {
                send_mail_template('generic', $email, '', '', [
                    '{content}' => $html,
                    '{subject}' => $subject,
                ]);
            }

            if (function_exists('zalo_ifk_send_message') && !empty($phone)) {
                zalo_ifk_send_message($phone, "Thông báo: {$full_name} sẽ về nước ngày {$return_date}");
            }

            log_activity('Đã gửi thông báo sắp về nước cho: ' . $full_name);
        }
    }

    /* ==============================
     * DROPDOWN LIST
     * ============================== */

    public function get_all_universities()
    {
        $rows = $this->db->select('university')
            ->from($this->tbl())
            ->where('university IS NOT NULL')
            ->where('university !=', '')
            ->group_by('university')
            ->order_by('university', 'ASC')
            ->get()->result_array();

        return array_map(static fn($r) => $r['university'], $rows);
    }

    public function get_all_companies()
    {
        $rows = $this->db->select('company_name')
            ->from($this->tbl())
            ->where('company_name IS NOT NULL')
            ->where('company_name !=', '')
            ->group_by('company_name')
            ->order_by('company_name', 'ASC')
            ->get()->result_array();

        return array_map(static fn($r) => $r['company_name'], $rows);
    }

    public function get_all_provinces()
    {
        $rows = $this->db->select('company_province')
            ->from($this->tbl())
            ->where('company_province IS NOT NULL')
            ->where('company_province !=', '')
            ->group_by('company_province')
            ->order_by('company_province', 'ASC')
            ->get()->result_array();

        return array_map(static fn($r) => $r['company_province'], $rows);
    }

    /* ==============================
     * SANITIZE DỮ LIỆU
     * ============================== */

    private function sanitize($data)
    {
        $allowed = [
            'full_name', 'email', 'phone', 'address',
            'university', 'company_name', 'company_address', 'company_province',
            'recruit_date', 'entry_date', 'months_stay', 'return_date',
            'status', 'note', 'photo', 'attachment', 'manager', 'dob',
            'parent_phone', 'year',
        ];

        $clean = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $data)) {
                $clean[$k] = $data[$k];
            }
        }

        if (isset($clean['manager']) && $clean['manager'] === '') {
            $clean['manager'] = null;
        }

        return $clean;
    }

    /* ==============================
     * GET YEARS (report)
     * ============================== */
    public function get_years_available()
    {
        // ưu tiên lấy năm theo job_orders (thường là nơi phát sinh nghiệp vụ)
        $tbl = db_prefix() . 'internship_job_orders';
        if (!$this->db->table_exists($tbl)) {
            return [];
        }

        $candidates = [
            'date_created', 'created_at', 'created_date', 'createdon',
            'interview_date', 'entry_date', 'apply_date', 'date',
            'updated_at', 'last_updated',
        ];

        $dateCol = '';
        foreach ($candidates as $c) {
            if ($this->db->field_exists($c, $tbl)) {
                $dateCol = $c;
                break;
            }
        }

        if ($dateCol === '') {
            return [];
        }

        $sql = "
            SELECT DISTINCT YEAR(`{$dateCol}`) AS year
            FROM `{$tbl}`
          WHERE `{$dateCol}` IS NOT NULL
  AND `{$dateCol}` NOT IN ('0000-00-00','0000-00-00 00:00:00')
            ORDER BY year DESC
        ";

        $rows = $this->db->query($sql)->result_array();

        $years = [];
        foreach ($rows as $r) {
            $y = (int)($r['year'] ?? 0);
            if ($y > 0) $years[] = $y;
        }

        return $years;
    }

    /* ==============================
     * LẤY TOÀN BỘ STUDENTS
     * ============================== */
    public function get_students()
    {
        return $this->db
            ->order_by('id', 'DESC')
            ->get($this->tbl())
            ->result_array();
    }

    /* ==============================
     * ADD HOẶC UPDATE THEO FULL NAME
     * ============================== */
    public function add_or_update($data)
    {
        $data = $this->sanitize($data);
        if (empty($data['full_name'])) return;

        if (isset($data['status'])) {
            $data['status'] = $this->normalize_status(self::WF_STUDENT, $data['status']);
        }

        $exists = $this->db
            ->where('full_name', $data['full_name'])
            ->get($this->tbl())
            ->row();

        if ($exists) {
            $updatedCol = $this->safe_date_col($this->tbl(), ['updated_at', 'dateupdated', 'updatedon']);
            if ($updatedCol !== '') {
                $data[$updatedCol] = date('Y-m-d H:i:s');
            }
            $this->db->where('id', (int)$exists->id)->update($this->tbl(), $data);
        } else {
            $createdCol = $this->safe_date_col($this->tbl(), ['created_at', 'datecreated', 'date_created', 'createdon']);
            if ($createdCol !== '') {
                $data[$createdCol] = date('Y-m-d H:i:s');
            }
            $this->db->insert($this->tbl(), $data);
        }
    }

    /* ==============================
     * WORKFLOW HELPERS (PUBLIC)
     * ============================== */

    /*public function normalize_status($type, $status)
    {
        $type = (string)$type;
        $status = trim((string)$status);
        if ($status === '') return '';

        if (!isset($this->wfOrder[$type])) {
            return $status;
        }

        if (isset($this->wfAlias[$type][$status])) {
            $status = $this->wfAlias[$type][$status];
        }

        if (!in_array($status, $this->wfOrder[$type], true)) {
            return $this->wfOrder[$type][0] ?? $status;
        }

        return $status;
    }*/
    
    public function normalize_status($type, $status)
    {
        if (function_exists('im_normalize_status')) {
            return im_normalize_status($status);
        }
    
        return strtolower(trim((string)$status));
    }

    /*public function get_status_meta($type, $status)
    {
        $type = (string)$type;
        $status = $this->normalize_status($type, $status);
        if (isset($this->wfMeta[$type][$status])) return $this->wfMeta[$type][$status];
        return ['label' => $status, 'color' => 'default'];
    }*/
    
    public function get_status_meta($type, $status)
    {
        if (function_exists('im_status_meta')) {
            return im_status_meta($type, $status);
        }
    
        return ['label' => (string)$status, 'color' => 'default'];
    }

    /*public function get_status_list($type)
    {
        $type = (string)$type;
        if (!isset($this->wfOrder[$type])) return [];
        $out = [];
        foreach ($this->wfOrder[$type] as $code) {
            $meta = $this->get_status_meta($type, $code);
            $out[$code] = $meta['label'];
        }
        return $out;
    }*/
    
    public function get_status_list($type)
    {
        if (function_exists('im_status_list')) {
            return im_status_list($type);
        }
    
        return [];
    }

    /*public function status_rank($type, $status)
    {
        $type = (string)$type;
        $status = $this->normalize_status($type, $status);
        if (!isset($this->wfOrder[$type])) return 0;
        $idx = array_search($status, $this->wfOrder[$type], true);
        return ($idx === false) ? 0 : (int)$idx;
    }*/
    
    public function status_rank($type, $status)
    {
        if (function_exists('im_status_rank')) {
            return im_status_rank($type, $status);
        }
    
        return 0;
    }

    /**
     * Đồng bộ trạng thái job order dựa trên tiến độ ứng viên.
     */
    public function sync_job_order_status_from_applications($job_order_id)
    {
        $job_order_id = (int)$job_order_id;
        if ($job_order_id <= 0) return false;

        $jobTbl = db_prefix() . 'internship_job_orders';
        $appTbl = db_prefix() . 'internship_applications';

        if (!$this->db->table_exists($jobTbl) || !$this->db->table_exists($appTbl)) {
            return false;
        }

        $job = $this->db->select('id,status')
            ->where('id', $job_order_id)
            ->get($jobTbl)->row_array();
        if (!$job) return false;

        $cur = $this->normalize_status(self::WF_JOB_ORDER, $job['status'] ?? 'received');
        if (in_array($cur, ['done', 'cancelled'], true)) return false;

        $apps = $this->db->select('status')
            ->from($appTbl)
            ->where('job_order_id', $job_order_id)
            ->get()->result_array();

        if (empty($apps)) return false;

        $maxRank = -1;
        $maxStatus = 'applied';
        foreach ($apps as $a) {
            $st = $this->normalize_status(self::WF_APPLICATION, $a['status'] ?? 'applied');
            $rk = $this->status_rank(self::WF_APPLICATION, $st);
            if ($rk > $maxRank) {
                $maxRank = $rk;
                $maxStatus = $st;
            }
        }

        /*$map = [
            'applied'             => 'recruiting',
            'interview_scheduled' => 'interview_scheduled',
            'interview_passed'    => 'interview_result',
            'interview_fail'      => 'recruiting',
            'docs_preparing'      => 'docs_preparing',
            'docs_done'           => 'docs_done',
            'coe_waiting'         => 'waiting_coe',
            'coe_done'            => 'coe_done',
            'visa_processing'     => 'coe_done',
            'ticket_booking'      => 'coe_done',
            'pre_departure'       => 'coe_done',
            'in_japan'            => 'entry',
            'returned'            => 'done',
            'cancelled'           => $cur,
        ];*/
        
        $map = [
            'applied'             => 'recruiting',
            'interview_scheduled' => 'interview_scheduled',
            'pass'                => 'interview_result',
            'fail'                => 'recruiting',
            'docs_preparing'      => 'docs_preparing',
            'docs_done'           => 'docs_done',
            'coe_waiting'         => 'coe_waiting',
            'has_coe'             => 'has_coe',
            'visa_processing'     => 'has_coe',
            'ticket_booking'      => 'has_coe',
            'pre_departure'       => 'has_coe',
            'entry'               => 'entry',
            'in_japan'            => 'entry',
            'returned'            => 'done',
            'cancelled'           => $cur,
        ];

        $next = $map[$maxStatus] ?? 'recruiting';
        $next = $this->normalize_status(self::WF_JOB_ORDER, $next);

        if ($this->status_rank(self::WF_JOB_ORDER, $next) > $this->status_rank(self::WF_JOB_ORDER, $cur)) {
            $this->db->where('id', $job_order_id)->update($jobTbl, ['status' => $next]);
            return $this->db->affected_rows() > 0;
        }

        return false;
    }

    /* ==============================
     * INTERNAL HELPERS
     * ============================== */

    /**
     * Chọn cột ngày tồn tại thật trong bảng (an toàn tránh lỗi Unknown column).
     */
    private function safe_date_col($table, array $candidates)
    {
        foreach ($candidates as $c) {
            if ($c === 'id') return 'id';
            if ($this->db->field_exists($c, $table)) return $c;
        }
        return '';
    }
}
