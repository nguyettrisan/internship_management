<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Internship_job_orders_model extends App_Model
{
    // Không hardcode prefix để tránh lệch môi trường; luôn dùng db_prefix()
    protected $table = 'internship_job_orders';
    protected $last_delete_error = '';
    /**
     * Mapping trạng thái → nhãn + màu cho workflow
     * (dùng khi status != 'received')
     */
    /*private $statusMeta = [
        'received' => [
            'label' => 'Tiếp nhận đơn',
            'color' => 'primary',
        ],
        'sent_to_schools' => [
            'label' => 'Đã gửi đơn cho trường',
            'color' => 'info',
        ],
        'has_applicants' => [
            'label' => 'Đã có SV ứng tuyển',
            'color' => 'info',
        ],
        'interview_scheduled' => [
            'label' => 'Đã lên lịch phỏng vấn',
            'color' => 'warning',
        ],
        'interview_result' => [
            'label' => 'Trả KQ PV & làm hồ sơ',
            'color' => 'warning',
        ],
        'docs_done' => [
            'label' => 'Đã xong hồ sơ',
            'color' => 'success',
        ],
        'waiting_coe' => [
            'label' => 'Chờ kết quả COE',
            'color' => 'default',
        ],
        'coe_done' => [
            'label' => 'Đã có COE, chờ nhập cảnh',
            'color' => 'success',
        ],
        'entry' => [
            'label' => 'Đã nhập cảnh',
            'color' => 'success',
        ],
        'done' => [
            'label' => 'Đã hoàn tất chương trình',
            'color' => 'default',
        ],
        'docs_preparing' => [
            'label' => 'Chuẩn bị hồ sơ',
            'color' => 'warning',
        ],
        'prepare_documents' => [
            'label' => 'Chuẩn bị hồ sơ',
            'color' => 'warning',
        ],
        'submitted' => [
            'label' => 'Đã nộp',
            'color' => 'info',
        ],
        'processing' => [
            'label' => 'Đang xử lý',
            'color' => 'info',
        ],
        'approved' => [
            'label' => 'Đã duyệt',
            'color' => 'success',
        ],
        'rejected' => [
            'label' => 'Từ chối',
            'color' => 'danger',
        ],
        'closed' => [
            'label' => 'Đã đóng',
            'color' => 'default',
        ],

    ]; */

    private function tbl()
    {
        return db_prefix() . $this->table;
    }
    public function get_last_delete_error()
    {
        return (string)$this->last_delete_error;
    }

    /*public function __construct()
    {
        parent::__construct();
    }*/
    
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('internship_management/job_order_status');
    }

    /**
     * Resolve workflow key safely even if Internship_model constants differ between installs.
     * Some installs don't define Internship_model::WF_JOB_ORDER.
     */
    private function wf_job_order_key()
    {
        return defined('Internship_model::WF_JOB_ORDER') ? Internship_model::WF_JOB_ORDER : 'job_order';
    }

    /**
     * Public normalize helper (controller/ajax can call)
     */
    public function normalize_status($status)
    {
        $this->load->model('internship_management/Internship_model', 'wf');
        $status = trim((string)$status);
        if ($status === '') {
            $status = 'received';
        }
        return $this->wf->normalize_status($this->wf_job_order_key(), $status);
    }

    /* =====================================================
       LẤY DS ĐƠN TUYỂN (LIST) + FILTER
       $filters (tùy chọn):
         - status : mã trạng thái
         - major  : ngành (chuỗi, sẽ LIKE major_vi/major_jp)
         - year   : năm (lọc theo YEAR(interview_date))
         - month  : tháng (lọc theo MONTH(interview_date))
         - search : keyword (tên cty / job JP–VI)
    ====================================================== */
    public function get_all($filters = [])
    {
        $this->load->model('internship_management/Internship_model', 'wf');
        $this->db->from($this->tbl());

        /* ------------ FILTERS (tùy chọn) ------------ */

        // 1) Trạng thái
        if (!empty($filters['status'])) {
            $this->db->where('status', $this->wf->normalize_status($this->wf_job_order_key(), $filters['status']));
        }

        // 2) Ngành (major_vi / major_jp)
        if (!empty($filters['major'])) {
            $major = trim($filters['major']);
            $this->db->group_start();
            $this->db->like('major_vi', $major);
            $this->db->or_like('major_jp', $major);
            $this->db->group_end();
        }

        // 3) Năm / tháng (lọc theo interview_date)
        /*if (!empty($filters['year'])) {
            $year = (int)$filters['year'];
            // YEAR(interview_date) = {year}
            $this->db->where("YEAR(interview_date) = {$year}");
        }

        if (!empty($filters['month'])) {
            $month = (int)$filters['month'];
            // MONTH(interview_date) = {month}
            $this->db->where("MONTH(interview_date) = {$month}");
        }*/
        
        // 3) Năm / tháng (lọc theo entry_date - nhập cảnh)
        if (!empty($filters['year'])) {
            $year = (int)$filters['year'];
            $this->db->where("YEAR(entry_date) = {$year}");
        }
        
        if (!empty($filters['month'])) {
            $month = (int)$filters['month'];
            $this->db->where("MONTH(entry_date) = {$month}");
        }
        

        // 4) Search chung (tên cty, job JP–VI)
        if (!empty($filters['search'])) {
            $kw = trim($filters['search']);
            $this->db->group_start();
            $this->db->like('company_name_vi', $kw);
            $this->db->or_like('company_name_jp', $kw);
            $this->db->or_like('job_title_vi', $kw);
            $this->db->or_like('job_title', $kw);
            $this->db->group_end();
        }

        $this->db->order_by('id', 'DESC');
        $rows = $this->db->get()->result_array();

        /* ------------ XỬ LÝ THÊM FIELD PHỤ TRỢ ------------ */

        foreach ($rows as &$r) {

            /* ===== Ngành hiển thị (major) =====
               Ưu tiên major_vi, sau đó major_jp, cuối cùng 'Không rõ'
            */
            $r['major'] =
                (!empty($r['major_vi'])) ? $r['major_vi'] :
                ((!empty($r['major_jp'])) ? $r['major_jp'] : 'Không rõ');

            /* ===== Số lượng tuyển =====
               Nếu có quantity_total → dùng
               Ngược lại → cộng quantity_male + quantity_female
            */
            if (!empty($r['quantity_total'])) {
                $r['quantity'] = (int) $r['quantity_total'];
            } else {
                $male   = isset($r['quantity_male']) ? (int) $r['quantity_male'] : 0;
                $female = isset($r['quantity_female']) ? (int) $r['quantity_female'] : 0;
                $r['quantity'] = $male + $female;
            }

            /* ===== Chuẩn hoá ngày để view =====
               (trong DB là Y-m-d; view sẽ dùng _d(), nên ở đây giữ nguyên dạng Y-m-d)
               Tuy nhiên nếu có date kiểu rác → normalize_date
            */
            $r['interview_date'] = $this->normalize_date($r['interview_date'] ?? null);
            $r['entry_date']     = $this->normalize_date($r['entry_date'] ?? null);

            //
            $r['return_date']    = $this->normalize_date($r['return_date'] ?? null);

            /* ===== Thống kê ứng viên =====
               total / male / female
            */
            $stats = $this->get_application_stats($r['id']);
            $appliedTotal  = (int) ($stats['total']  ?? 0);
            $appliedMale   = (int) ($stats['male']   ?? 0);
            $appliedFemale = (int) ($stats['female'] ?? 0);

            $r['applied_total']  = $appliedTotal;
            $r['applied_male']   = $appliedMale;
            $r['applied_female'] = $appliedFemale;

            /* ===== Tính status_label + status_color (Option C) ===== */

            /*$statusCode = $r['status'] ?? 'received';
            $quantity   = (int) ($r['quantity'] ?? 0);

            if (!isset($this->statusMeta[$statusCode])) {
                $statusCode = 'received';
            }*/
            /* ===== Tính status_label + status_color (Option C) ===== */

            $statusCode = $r['status'] ?? 'received';
            $statusMeta = im_job_order_status_meta($statusCode);
            $statusCode = $statusMeta['key'];
            $quantity   = (int) ($r['quantity'] ?? 0);

            // --- Case 1: status = received → tự tính theo số lượng ứng tuyển ---
            if ($statusCode === 'received') {

                if ($appliedTotal === 0) {
                    // Chưa có ai ứng tuyển
                    $label = 'Tiếp nhận đơn';
                    $color = 'primary';

                } elseif ($quantity > 0 && $appliedTotal < $quantity) {
                    // Có SV, chưa đủ chỉ tiêu
                    $label =
                        "Đã có {$appliedTotal}/{$quantity} ứng tuyển<br>" .
                        "<small>Nam: {$appliedMale} | Nữ: {$appliedFemale}</small>";
                    $color = 'info';

                } elseif ($quantity > 0 && $appliedTotal == $quantity) {
                    // Đã đủ số lượng
                    $label =
                        "Đã đủ số lượng ({$appliedTotal}/{$quantity})<br>" .
                        "<small>Nam: {$appliedMale} | Nữ: {$appliedFemale}</small>";
                    $color = 'success';

                } elseif ($quantity > 0 && $appliedTotal > $quantity) {
                    // Vượt chỉ tiêu
                    $label =
                        "Vượt chỉ tiêu ({$appliedTotal}/{$quantity})<br>" .
                        "<small>Nam: {$appliedMale} | Nữ: {$appliedFemale}</small>";
                    $color = 'warning';

                } else {
                    // quantity = 0 nhưng đã có ứng viên
                    $label =
                        "Đã có {$appliedTotal} ứng tuyển<br>" .
                        "<small>Nam: {$appliedMale} | Nữ: {$appliedFemale}</small>";
                    $color = 'info';
                }

                $r['status_label'] = $label;
                $r['status_color'] = $color;
            }
            // --- Case 2: status != received → dùng workflow meta ---
            /*else {

                $meta  = $this->statusMeta[$statusCode];
                $label = $meta['label'];
                $color = $meta['color'];

                // Một số trạng thái có ngày gắn kèm
                if ($statusCode === 'interview_scheduled' && !empty($r['interview_date'])) {
                    $label = 'Phỏng vấn: ' . _d($r['interview_date']);
                }

                if ($statusCode === 'coe_done' && !empty($r['entry_date'])) {
                    $label = 'Đã có COE – Nhập cảnh: ' . _d($r['entry_date']);
                }

                if ($statusCode === 'entry' && !empty($r['entry_date'])) {
                    $label = 'Đã nhập cảnh: ' . _d($r['entry_date']);
                }

                // Một số trạng thái hiển thị thêm thông tin ứng viên
                if (in_array($statusCode, ['has_applicants', 'interview_scheduled', 'interview_result'])) {

                    if ($appliedTotal > 0 && $quantity > 0) {
                        $label .=
                            "<br><small>{$appliedTotal}/{$quantity} – Nam: {$appliedMale} | Nữ: {$appliedFemale}</small>";
                    } elseif ($appliedTotal > 0) {
                        $label .=
                            "<br><small>{$appliedTotal} ứng viên – Nam: {$appliedMale} | Nữ: {$appliedFemale}</small>";
                    }
                }

                $r['status_label'] = $label;
                $r['status_color'] = $color;
                
                //
                $r['sent_schools'] = $this->get_job_order_schools((int)($r['id'] ?? 0));
                $r['sent_school_names'] = array_values(array_filter(array_map(function ($x) {
                    return trim((string)($x['school_name'] ?? ''));
                }, $r['sent_schools'])));
                $r['sent_school_text'] = !empty($r['sent_school_names']) ? implode(', ', $r['sent_school_names']) : '';
            }*/
            
            else {

                $label = $statusMeta['vi'];
                $color = $statusMeta['color'];
            
                if ($statusCode === 'interview_scheduled' && !empty($r['interview_date'])) {
                    $label = 'Phỏng vấn: ' . _d($r['interview_date']);
                }
            
                if ($statusCode === 'coe_done' && !empty($r['entry_date'])) {
                    $label = 'Đã có COE – Nhập cảnh: ' . _d($r['entry_date']);
                }
            
                if ($statusCode === 'entry' && !empty($r['entry_date'])) {
                    $label = 'Đã nhập cảnh: ' . _d($r['entry_date']);
                }
            
                if (in_array($statusCode, ['has_applicants', 'interview_scheduled', 'interview_result'], true)) {
            
                    if ($appliedTotal > 0 && $quantity > 0) {
                        $label .=
                            "<br><small>{$appliedTotal}/{$quantity} – Nam: {$appliedMale} | Nữ: {$appliedFemale}</small>";
                    } elseif ($appliedTotal > 0) {
                        $label .=
                            "<br><small>{$appliedTotal} ứng viên – Nam: {$appliedMale} | Nữ: {$appliedFemale}</small>";
                    }
                }
            
                $r['status_label'] = $label;
                $r['status_color'] = $color;
            
                $r['sent_schools'] = $this->get_job_order_schools((int)($r['id'] ?? 0));
                $r['sent_school_names'] = array_values(array_filter(array_map(function ($x) {
                    return trim((string)($x['school_name'] ?? ''));
                }, $r['sent_schools'])));
                $r['sent_school_text'] = !empty($r['sent_school_names']) ? implode(', ', $r['sent_school_names']) : '';
            }
        }

        return $rows;
    }

    /*private function normalize_partner_school_name($name)
    {
        $name = trim((string)$name);
        $name = preg_replace('/\s+/', ' ', $name);
    
        if ($name === '' || $name === '__new__') {
            return '';
        }
    
        $upper = strtoupper($name);
    
        // VLSC và VLSG là cùng một trường, thống nhất lưu là VLSG
        if ($upper === 'VLSC' || $upper === 'VLSG') {
            return 'VLSG';
        }
    
        return $name;
    }*/
    private function normalize_partner_school_name($name)
    {
        $name = trim((string)$name);
        $name = preg_replace('/\s+/', ' ', $name);
    
        if ($name === '' || $name === '__new__') {
            return '';
        }
    
        $upper = strtoupper(str_replace(['—', '–'], '-', $name));
        $upper = preg_replace('/\s+/', ' ', $upper);
    
        // Bỏ dấu cách, dấu gạch, dấu _ để gom các biến thể
        $compact = preg_replace('/[^A-Z0-9]/', '', $upper);
    
        // VLSC và VLSG là cùng một trường, thống nhất lưu là VLSG
        if ($compact === 'VLSC' || $compact === 'VLSG') {
            return 'VLSG';
        }
    
        // HUTECH-VJIT và VJIT là cùng một trường/nhóm, thống nhất lưu là VJIT
        if ($compact === 'HUTECHVJIT' || $compact === 'VJITHUTECH' || $compact === 'VJIT') {
            return 'VJIT';
        }
    
        return $name;
    }
    
    
    private function partner_school_code_from_name($name)
    {
        $name = $this->normalize_partner_school_name($name);
    
        if ($name === '') {
            return '';
        }
    
        $code = strtoupper($name);
        $code = str_replace(['—', '–'], '-', $code);
        $code = preg_replace('/\s+/', ' ', $code);
    
        return trim($code);
    }
    
    public function ensure_partner_school($school_name)
    {
        $tbl = db_prefix() . 'internship_partner_schools';
    
        if (!$this->db->table_exists($tbl)) {
            return 0;
        }
    
        $name = $this->normalize_partner_school_name($school_name);
        if ($name === '') {
            return 0;
        }
    
        $code = $this->partner_school_code_from_name($name);
        if ($code === '') {
            return 0;
        }
    
        $fields = $this->db->list_fields($tbl);
    
        $row = $this->db
            ->select('id')
            ->from($tbl)
            ->where('school_code', $code)
            ->limit(1)
            ->get()
            ->row_array();
    
        if (!empty($row)) {
            $update = [];
    
            if (in_array('school_name', $fields, true)) {
                $update['school_name'] = $name;
            }
    
            if (in_array('is_active', $fields, true)) {
                $update['is_active'] = 1;
            }
    
            if (!empty($update)) {
                $this->db->where('id', (int)$row['id'])->update($tbl, $update);
            }
    
            return (int)$row['id'];
        }
    
        $insert = [
            'school_code' => $code,
            'school_name' => $name,
            'is_active'   => 1,
        ];
    
        if (in_array('datecreated', $fields, true)) {
            $insert['datecreated'] = date('Y-m-d H:i:s');
        }
    
        $this->db->insert($tbl, $insert);
    
        return (int)$this->db->insert_id();
    }
    
    private function sync_application_schools_to_partner_schools()
    {
        $appTbl = db_prefix() . 'internship_applications';
    
        if (!$this->db->table_exists($appTbl)) {
            return;
        }
    
        if (!$this->db->field_exists('school_name', $appTbl)) {
            return;
        }
    
        $rows = $this->db
            ->select('DISTINCT school_name', false)
            ->from($appTbl)
            ->where('school_name IS NOT NULL', null, false)
            ->where("TRIM(school_name) <> ''", null, false)
            ->get()
            ->result_array();
    
        foreach ($rows as $row) {
            $this->ensure_partner_school($row['school_name'] ?? '');
        }
    }
    //
    /*public function get_partner_schools()
    {
        $this->sync_application_schools_to_partner_schools();
            
        $result = [];

        $tbl = db_prefix() . 'internship_partner_schools';
        if ($this->db->table_exists($tbl)) {
            $fields = $this->db->list_fields($tbl);
            $this->db->from($tbl);
            if (in_array('is_active', $fields, true)) {
                $this->db->where('is_active', 1);
            }
            $result = $this->db->order_by('school_name', 'ASC')->get()->result_array();
        }

        if (!empty($result)) {
            return $result;
        }*/
    public function get_partner_schools()
    {
        $this->sync_application_schools_to_partner_schools();
            
        $result = [];
    
        $tbl = db_prefix() . 'internship_partner_schools';
        if ($this->db->table_exists($tbl)) {
            $fields = $this->db->list_fields($tbl);
            $this->db->from($tbl);
    
            if (in_array('is_active', $fields, true)) {
                $this->db->where('is_active', 1);
            }
    
            $result = $this->db->order_by('school_name', 'ASC')->get()->result_array();
        }
    
        if (!empty($result)) {
            $out = [];
            $seen = [];
    
            foreach ($result as $row) {
                $name = $this->normalize_partner_school_name($row['school_name'] ?? '');
    
                if ($name === '') {
                    continue;
                }
    
                $code = $this->partner_school_code_from_name($name);
                $key  = strtolower($code ?: $name);
    
                if (isset($seen[$key])) {
                    continue;
                }
    
                $seen[$key] = true;
    
                $row['school_name'] = $name;
                $row['school_code'] = $code;
    
                $out[] = $row;
            }
    
            usort($out, function ($a, $b) {
                return strcasecmp((string)($a['school_name'] ?? ''), (string)($b['school_name'] ?? ''));
            });
    
            return $out;
        }

        $accTbl = db_prefix() . 'internship_school_accounts';
        if (!$this->db->table_exists($accTbl)) {
            return [];
        }

        $fields = $this->db->list_fields($accTbl);
        $select = ['id'];
        if (in_array('school_code', $fields, true)) {
            $select[] = 'school_code';
        } else {
            $select[] = "'' as school_code";
        }
        if (in_array('school_name', $fields, true)) {
            $select[] = 'school_name';
        } elseif (in_array('username', $fields, true)) {
            $select[] = 'username as school_name';
        } else {
            $select[] = "CONCAT('School #', id) as school_name";
        }

        $this->db->select(implode(',', $select), false)->from($accTbl);
        if (in_array('is_active', $fields, true)) {
            $this->db->where('is_active', 1);
        } elseif (in_array('active', $fields, true)) {
            $this->db->where('active', 1);
        }

        $rows = $this->db->order_by('school_name', 'ASC')->get()->result_array();
        $out = [];
        $seen = [];
        /*foreach ($rows as $r) {
            $id = (int)($r['id'] ?? 0);
            $name = trim((string)($r['school_name'] ?? ''));
            $code = trim((string)($r['school_code'] ?? ''));
            if ($id <= 0 || $name === '') {
                continue;
            }
            $k = strtolower($id . '|' . $name . '|' . $code);
            if (isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $out[] = [
                'id' => $id,
                'school_code' => $code,
                'school_name' => $name,
            ];
        }*/
        foreach ($rows as $r) {
            $id = (int)($r['id'] ?? 0);
            $name = $this->normalize_partner_school_name($r['school_name'] ?? '');
            $code = $this->partner_school_code_from_name($name);
        
            if ($id <= 0 || $name === '') {
                continue;
            }
        
            $k = strtolower($code ?: $name);
        
            if (isset($seen[$k])) {
                continue;
            }
        
            $seen[$k] = true;
        
            $out[] = [
                'id'          => $id,
                'school_code' => $code,
                'school_name' => $name,
            ];
        }

        return $out;
    }

    public function get_job_order_school_ids($job_order_id)
    {
        $tbl = db_prefix() . 'internship_job_order_schools';
        if (!$this->db->table_exists($tbl)) {
            return [];
        }

        $rows = $this->db
            ->select('school_id')
            ->where('job_order_id', (int) $job_order_id)
            ->where('is_active', 1)
            ->get($tbl)
            ->result_array();

        return array_map('intval', array_column($rows, 'school_id'));
    }

    /*public function get_job_order_schools($job_order_id)
    {
        $mapTbl = db_prefix() . 'internship_job_order_schools';
        if (!$this->db->table_exists($mapTbl)) {
            return [];
        }

        return $this->db
            ->select('school_id, school_code, school_name, sent_at')
            ->from($mapTbl)
            ->where('job_order_id', (int)$job_order_id)
            ->where('is_active', 1)
            ->order_by('school_name', 'ASC')
            ->get()
            ->result_array();
    }*/ 
    
    public function get_job_order_schools($job_order_id)
    {
        $mapTbl = db_prefix() . 'internship_job_order_schools';
        if (!$this->db->table_exists($mapTbl)) {
            return [];
        }
    
        $rows = $this->db
            ->select('school_id, school_code, school_name, sent_at')
            ->from($mapTbl)
            ->where('job_order_id', (int)$job_order_id)
            ->where('is_active', 1)
            ->order_by('school_name', 'ASC')
            ->get()
            ->result_array();
    
        foreach ($rows as &$row) {
            $name = $this->normalize_partner_school_name($row['school_name'] ?? '');
    
            if ($name !== '') {
                $row['school_name'] = $name;
                $row['school_code'] = $this->partner_school_code_from_name($name);
            }
        }
        unset($row);
    
        return $rows;
    }
    
    public function get_job_order_school_names($job_order_id)
    {
        $rows = $this->get_job_order_schools($job_order_id);
        if (empty($rows)) {
            return [];
        }
        return array_values(array_filter(array_map(function($r){
            return trim((string)($r['school_name'] ?? ''));
        }, $rows)));
    }

    public function sync_job_order_schools($job_order_id, $school_ids = [])
    {
        $mapTbl = db_prefix() . 'internship_job_order_schools';

        if (!$this->db->table_exists($mapTbl)) {
            return false;
        }

        $job_order_id = (int) $job_order_id;
        $school_ids   = array_values(array_unique(array_filter(array_map('intval', (array) $school_ids))));

        $this->db->where('job_order_id', $job_order_id)->delete($mapTbl);

        if (empty($school_ids)) {
            return true;
        }

        $catalog = [];
        foreach ($this->get_partner_schools() as $row) {
            $catalog[(int)($row['id'] ?? 0)] = $row;
        }

        $insert = [];
        foreach ($school_ids as $sid) {
            if (!isset($catalog[$sid])) {
                continue;
            }
            $s = $catalog[$sid];
            $insert[] = [
                'job_order_id' => $job_order_id,
                'school_id'    => (int) ($s['id'] ?? 0),
                'school_code'  => (string) ($s['school_code'] ?? ''),
                'school_name'  => (string) ($s['school_name'] ?? ''),
                'sent_at'      => date('Y-m-d H:i:s'),
                'sent_by'      => function_exists('get_staff_user_id') ? (int) get_staff_user_id() : 0,
                'is_active'    => 1,
            ];
        }

        if (!empty($insert)) {
            $this->db->insert_batch($mapTbl, $insert);
        }

        return true;
    }
    
    
    /* =====================================================
       LẤY 1 ĐƠN TUYỂN THEO ID
    ====================================================== */
    public function get($id)
    {
        /*return $this->db
            ->where('id', (int)$id)
            ->get($this->tbl())
            ->row_array();*/
            
        $row = $this->db
            ->where('id', (int)$id)
            ->get($this->tbl())
            ->row_array();

        if (!empty($row)) {
            $row['sent_schools'] = $this->get_job_order_schools((int) $id);
            $row['sent_school_names'] = array_values(array_filter(array_map(function ($x) {
                return trim((string) ($x['school_name'] ?? ''));
            }, $row['sent_schools'])));
            $row['sent_school_text'] = !empty($row['sent_school_names']) ? implode(', ', $row['sent_school_names']) : '';
        }

        return $row;
    }

    /* =====================================================
       THÊM MỚI ĐƠN TUYỂN
       - Lọc field theo cột thật của bảng
       - Chuẩn hoá số & ngày
    ====================================================== */
    public function add($data)
    {
        $this->load->model('internship_management/Internship_model', 'wf');

        // Chỉ giữ các field có trong bảng
        $allowed = $this->db->list_fields($this->tbl());
        $clean   = array_intersect_key($data, array_flip($allowed));

        // Mặc định status
        if (empty($clean['status'])) {
            $clean['status'] = 'received';
        }

        // round_no (đợt) tự tăng theo nhà tuyển dụng
        $this->ensure_round_no($clean);

        $clean['status'] = $this->wf->normalize_status($this->wf_job_order_key(), $clean['status']);

        // Chuẩn hoá số & ngày
        $clean = $this->normalize_numeric_fields($clean);
        $clean = $this->normalize_date_fields($clean);

        $this->db->insert($this->tbl(), $clean);
        $id = (int)$this->db->insert_id();

        // Không auto sync CRM ở đây nữa.
        // Đồng bộ CRM sẽ thực hiện qua nút "Đẩy CRM" (controller push_crm/profile).

        return $id;
    }

    /* =====================================================
       CẬP NHẬT ĐƠN TUYỂN
    ====================================================== */
    public function update($id, $data)
    {
        $this->load->model('internship_management/Internship_model', 'wf');

        $allowed = $this->db->list_fields($this->tbl());
        $clean   = array_intersect_key($data, array_flip($allowed));

        if (isset($clean['status'])) {
            $clean['status'] = $this->wf->normalize_status($this->wf_job_order_key(), $clean['status']);
        }

        $clean = $this->normalize_numeric_fields($clean);
        $clean = $this->normalize_date_fields($clean);

        $ok = $this->db
            ->where('id', (int)$id)
            ->update($this->tbl(), $clean);

        // Không auto sync CRM ở đây nữa (đã chuyển qua nút).

        return $ok;
    }

    
    /* =====================================================
       ROUND / ĐỢT TUYỂN DỤNG
       - Mỗi Nhà tuyển dụng (employer_id hoặc company_name_vi) có nhiều đợt
       - round_no tăng dần theo từng nhà tuyển dụng
    ====================================================== */
    public function get_next_round_no($employer_id = 0, $company_name_vi = '')
    {
        $tbl = $this->tbl();
        $employer_id = (int)$employer_id;
        $company_name_vi = trim((string)$company_name_vi);

        $this->db->select_max('round_no');
        $this->db->from($tbl);

        if ($employer_id > 0 && $this->db->field_exists('employer_id', $tbl)) {
            $this->db->where('employer_id', $employer_id);
        } else {
            // fallback theo tên công ty để vẫn chạy trước khi sync CRM
            $this->db->where('company_name_vi', $company_name_vi);
        }

        $row = $this->db->get()->row_array();
        $max = (int)($row['round_no'] ?? 0);
        return $max + 1;
    }

    private function ensure_round_no(&$clean, $exclude_id = 0)
    {
        $tbl = $this->tbl();
        if (!$this->db->field_exists('round_no', $tbl)) {
            return;
        }

        // nếu đã có round_no thì giữ
        if (!empty($clean['round_no'])) {
            $clean['round_no'] = (int)$clean['round_no'];
            if ($clean['round_no'] <= 0) $clean['round_no'] = 1;
            return;
        }

        $employer_id = (int)($clean['employer_id'] ?? 0);
        $company = trim((string)($clean['company_name_vi'] ?? ''));

        $next = 1;

        $this->db->select_max('round_no');
        $this->db->from($tbl);

        if ($exclude_id > 0) {
            $this->db->where('id !=', (int)$exclude_id);
        }

        if ($employer_id > 0 && $this->db->field_exists('employer_id', $tbl)) {
            $this->db->where('employer_id', $employer_id);
        } else {
            $this->db->where('company_name_vi', $company);
        }

        $row = $this->db->get()->row_array();
        $max = (int)($row['round_no'] ?? 0);
        $next = max(1, $max + 1);

        $clean['round_no'] = $next;
    }


/* =====================================================
       CRM SYNC (NÚT ĐẨY CRM)
       - Tạo/Update Khách hàng (tblclients)
       - Tạo/Update Liên hệ (tblcontacts)
       - Gắn employer_id vào job order nếu có cột
    ====================================================== */
    public function sync_to_crm($job_order_id)
    {
        $job_order_id = (int)$job_order_id;
        if ($job_order_id <= 0) {
            return ['success' => false, 'message' => 'Invalid id'];
        }

        $job = $this->get($job_order_id);
        if (!$job) {
            return ['success' => false, 'message' => 'Không tìm thấy đơn tuyển'];
        }

        $clientId = $this->ensure_crm_customer_upsert($job);
        if ($clientId <= 0) {
            return ['success' => false, 'message' => 'Không tạo được khách hàng CRM (thiếu tên công ty)'];
        }

        $contactId = $this->ensure_crm_contact_upsert($clientId, $job);

        if ($this->db->field_exists('employer_id', $this->tbl())) {
            $this->db->where('id', $job_order_id)->update($this->tbl(), ['employer_id' => $clientId]);
        }

        $this->add_employer_log($clientId, $job_order_id, 'crm_sync', 'Đồng bộ khách hàng/ liên hệ sang CRM');

        return [
            'success'   => true,
            'clientid'  => $clientId,
            'contactid' => (int)$contactId,
            'message'   => 'Đã đồng bộ sang CRM.',
        ];
    }

    /**
     * Ensure a Perfex CRM customer exists for this job order (tblclients).
     * Returns clientid or 0.
     */
    private function ensure_crm_customer_upsert($job)
    {
        $clientsTbl = db_prefix() . 'clients';
        if (!$this->db->table_exists($clientsTbl)) {
            return 0;
        }

        $company = trim((string)($job['company_name_vi'] ?? ''));
        if ($company === '') {
            return 0;
        }

        // existing by company?
        $existing = $this->db->select('userid')
            ->from($clientsTbl)
            ->where('company', $company)
            ->limit(1)
            ->get()->row();
        $existingId = ($existing && isset($existing->userid)) ? (int)$existing->userid : 0;

        $cols = $this->db->list_fields($clientsTbl);
        $ins = [];

        if (in_array('company', $cols, true)) {
            $ins['company'] = $company;
        }
        if (in_array('phonenumber', $cols, true)) {
            $phone = trim((string)($job['company_phone_vi'] ?? $job['company_phone'] ?? ''));
            $ins['phonenumber'] = $phone;
        }
        if (in_array('website', $cols, true)) {
            $web = trim((string)($job['website_vi'] ?? $job['website'] ?? ''));
            $ins['website'] = $web;
        }
        if (in_array('address', $cols, true)) {
            $addr = trim((string)($job['address_vi'] ?? $job['address_jp'] ?? ''));
            $ins['address'] = $addr;
        }
        if (in_array('city', $cols, true)) {
            $ins['city'] = '';
        }
        if (in_array('state', $cols, true)) {
            $ins['state'] = '';
        }
        if (in_array('country', $cols, true)) {
            $ins['country'] = 0;
        }
        if (in_array('zip', $cols, true)) {
            $ins['zip'] = '';
        }
        if (in_array('active', $cols, true)) {
            $ins['active'] = 1;
        }
        if (in_array('addedfrom', $cols, true) && function_exists('get_staff_user_id')) {
            $ins['addedfrom'] = (int)get_staff_user_id();
        }
        if (in_array('datecreated', $cols, true)) {
            $ins['datecreated'] = date('Y-m-d H:i:s');
        }

        // Minimal insert
        if (empty($ins)) {
            return 0;
        }

        if ($existingId > 0) {
            $up = [];
            foreach (['phonenumber','website','address'] as $f) {
                if (isset($ins[$f]) && $ins[$f] !== '') {
                    $up[$f] = $ins[$f];
                }
            }
            if (!empty($up)) {
                $this->db->where('userid', $existingId)->update($clientsTbl, $up);
            }
            return $existingId;
        }

        $this->db->insert($clientsTbl, $ins);
        return (int)$this->db->insert_id();
    }

    private function ensure_crm_contact_upsert($clientId, $job)
    {
        $contactsTbl = db_prefix() . 'contacts';
        if (!$this->db->table_exists($contactsTbl)) {
            return 0;
        }

        $clientId = (int)$clientId;
        if ($clientId <= 0) {
            return 0;
        }

        $email = trim((string)($job['pic_email'] ?? ''));
        $phone = trim((string)($job['pic_phone'] ?? ''));
        $name  = trim((string)($job['pic_name'] ?? ''));

        if ($email === '') {
            return 0;
        }

        $cols = $this->db->list_fields($contactsTbl);

        $ex = $this->db->select('id')
            ->from($contactsTbl)
            ->where('userid', $clientId)
            ->where('email', $email)
            ->limit(1)
            ->get()->row();

        $data = [];
        if (in_array('userid', $cols, true)) {
            $data['userid'] = $clientId;
        }
        if (in_array('email', $cols, true)) {
            $data['email'] = $email;
        }

        $first = $name;
        $last  = '';
        if ($name !== '' && strpos($name, ' ') !== false) {
            $parts = preg_split('/\s+/u', $name);
            $first = array_shift($parts);
            $last  = implode(' ', $parts);
        }

        if (in_array('firstname', $cols, true)) {
            $data['firstname'] = $first !== '' ? $first : 'PIC';
        }
        if (in_array('lastname', $cols, true)) {
            $data['lastname'] = $last;
        }
        if (in_array('phonenumber', $cols, true)) {
            $data['phonenumber'] = $phone;
        }
        if (in_array('active', $cols, true)) {
            $data['active'] = 1;
        }
        if (in_array('is_primary', $cols, true)) {
            $data['is_primary'] = 1;
        }
        if (in_array('datecreated', $cols, true)) {
            $data['datecreated'] = date('Y-m-d H:i:s');
        }

        if ($ex && isset($ex->id)) {
            $cid = (int)$ex->id;
            $up = [];
            foreach (['firstname','lastname','phonenumber'] as $f) {
                if (isset($data[$f]) && $data[$f] !== '') {
                    $up[$f] = $data[$f];
                }
            }
            if (!empty($up)) {
                $this->db->where('id', $cid)->update($contactsTbl, $up);
            }
            return $cid;
        }

        $this->db->insert($contactsTbl, $data);
        return (int)$this->db->insert_id();
    }

    /* =====================================================
       NOTES (Job Order)
    ====================================================== */
    private function notes_tbl()
    {
        return db_prefix() . 'internship_job_order_notes';
    }

    public function get_notes($job_order_id)
    {
        $tbl = $this->notes_tbl();
        if (!$this->db->table_exists($tbl)) {
            return [];
        }
        return $this->db->from($tbl)
            ->where('job_order_id', (int)$job_order_id)
            ->order_by('id', 'DESC')
            ->get()->result_array();
    }

    public function add_note($job_order_id, $note)
    {
        $tbl = $this->notes_tbl();
        if (!$this->db->table_exists($tbl)) {
            return 0;
        }
        $note = trim((string)$note);
        if ($note === '') {
            return 0;
        }
        $staff_id = function_exists('get_staff_user_id') ? (int)get_staff_user_id() : 0;
        $this->db->insert($tbl, [
            'job_order_id' => (int)$job_order_id,
            'staff_id'     => $staff_id,
            'note'         => $note,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insert_id();
    }

    public function delete_note($id)
    {
        $tbl = $this->notes_tbl();
        if (!$this->db->table_exists($tbl)) {
            return false;
        }
        return (bool)$this->db->where('id', (int)$id)->delete($tbl);
    }

    /* =====================================================
       EMPLOYER LOGS (CRM/Employer)
    ====================================================== */
    private function logs_tbl()
    {
        return db_prefix() . 'internship_employer_logs';
    }

    public function get_employer_logs($clientId, $job_order_id = 0)
    {
        $tbl = $this->logs_tbl();
        if (!$this->db->table_exists($tbl)) {
            return [];
        }
        $this->db->from($tbl)->where('clientid', (int)$clientId);
        if ((int)$job_order_id > 0) {
            $this->db->where('job_order_id', (int)$job_order_id);
        }
        return $this->db->order_by('id', 'DESC')->get()->result_array();
    }

    public function add_employer_log($clientId, $job_order_id, $action, $message)
    {
        $tbl = $this->logs_tbl();
        if (!$this->db->table_exists($tbl)) {
            return 0;
        }
        $staff_id = function_exists('get_staff_user_id') ? (int)get_staff_user_id() : 0;
        $this->db->insert($tbl, [
            'clientid'     => (int)$clientId,
            'job_order_id' => (int)$job_order_id,
            'staff_id'     => $staff_id,
            'action'       => trim((string)$action),
            'message'      => trim((string)$message),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insert_id();
    }

    /* =====================================================
       XOÁ ĐƠN TUYỂN
    ====================================================== */
    /*public function delete($id)
    {
        return $this->db
            ->where('id', (int)$id)
            ->delete($this->tbl());
    }*/
    
    public function delete($id)
    {
        $this->last_delete_error = '';
        $id = (int)$id;
        if ($id <= 0) {
            $this->last_delete_error = 'ID đơn tuyển không hợp lệ.';
            log_message('error', 'Job order delete failed: invalid ID');
            return false;
        }
    
        $job = $this->get($id);
        if (!$job) {
            $this->last_delete_error = 'Không tìm thấy đơn tuyển ID=' . $id;
            log_message('error', 'Job order delete failed: job not found, ID=' . $id);
            return false;
        }
    
        $this->db->trans_begin();
        
        // Tắt kiểm tra foreign key trong đúng phiên xóa này
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
    
        try {
            $assertDbOk = function ($step) use ($id) {
                $err = $this->db->error();
                if (!empty($err['code'])) {
                    throw new Exception(
                        'Delete failed at [' . $step . '] | ID=' . $id . ' | DB ' . $err['code'] . ': ' . $err['message']
                    );
                }
            };
    
            $findExistingColumn = function ($table, array $candidates) {
                if (!$this->db->table_exists($table)) {
                    return null;
                }
    
                $cols = $this->db->list_fields($table);
                foreach ($candidates as $c) {
                    if (in_array($c, $cols, true)) {
                        return $c;
                    }
                }
                return null;
            };
    
            $deleteByJobColumn = function ($tableName, array $jobCols, $stepLabel) use ($id, $assertDbOk, $findExistingColumn) {
                $tbl = $this->resolve_table($tableName);
                if (!$this->db->table_exists($tbl)) {
                    return;
                }
    
                $jobCol = $findExistingColumn($tbl, $jobCols);
                if (!$jobCol) {
                    return;
                }
    
                $this->db->where($jobCol, $id)->delete($tbl);
                $assertDbOk($stepLabel . ' | table=' . $tbl . ' | col=' . $jobCol);
            };
    
            // =====================================================
            // 1) Gỡ liên kết khỏi bảng applications
            //    Dùng schema linh hoạt: job_order_id / job_id / joborder_id
            //    Và set về 0 thay vì NULL
            // =====================================================
            $appsTbl = $this->resolve_table('internship_applications');
            if ($this->db->table_exists($appsTbl)) {
                $appJobCol = $findExistingColumn($appsTbl, ['job_order_id', 'job_id', 'joborder_id']);
                if ($appJobCol) {
                    $this->db->where($appJobCol, $id)->update($appsTbl, [$appJobCol => 0]);
                    $assertDbOk('update applications => 0 | table=' . $appsTbl . ' | col=' . $appJobCol);
                }
            }
    
            // =====================================================
            // 2) Xóa mapping ứng viên - đơn tuyển
            //    DÙNG THẲNG ĐÚNG BẢNG/CỘT THEO LỖI DB ĐÃ BÁO
            // =====================================================
            $applicantsTbl = db_prefix() . 'internship_job_order_applicants';
            if ($this->db->table_exists($applicantsTbl)) {
                $this->db->query(
                    "DELETE FROM `{$applicantsTbl}` WHERE `job_order_id` = ?",
                    [$id]
                );
                $assertDbOk('delete tblinternship_job_order_applicants by job_order_id');

                $remainApplicants = (int)$this->db
                    ->where('job_order_id', $id)
                    ->count_all_results($applicantsTbl);

                $assertDbOk('count remain tblinternship_job_order_applicants');

                if ($remainApplicants > 0) {
                    throw new Exception(
                        'Vẫn còn ' . $remainApplicants . ' dòng trong ' . $applicantsTbl .
                        ' với job_order_id=' . $id . ' sau khi đã DELETE.'
                    );
                }
            }

            // =====================================================
            // 3) Xóa mapping trường - đơn tuyển
            // =====================================================
            $schoolsTbl = db_prefix() . 'internship_job_order_schools';
            if ($this->db->table_exists($schoolsTbl)) {
                $this->db->query(
                    "DELETE FROM `{$schoolsTbl}` WHERE `job_order_id` = ?",
                    [$id]
                );
                $assertDbOk('delete tblinternship_job_order_schools by job_order_id');
            }

            // =====================================================
            // 4) Xóa notes của đơn tuyển
            // =====================================================
            $notesTbl = db_prefix() . 'internship_job_order_notes';
            if ($this->db->table_exists($notesTbl)) {
                $this->db->query(
                    "DELETE FROM `{$notesTbl}` WHERE `job_order_id` = ?",
                    [$id]
                );
                $assertDbOk('delete tblinternship_job_order_notes by job_order_id');
            }

            // =====================================================
            // 5) Xóa logs của job order
            // =====================================================
            $jobLogsTbl = db_prefix() . 'internship_job_order_logs';
            if ($this->db->table_exists($jobLogsTbl)) {
                $this->db->query(
                    "DELETE FROM `{$jobLogsTbl}` WHERE `job_order_id` = ?",
                    [$id]
                );
                $assertDbOk('delete tblinternship_job_order_logs by job_order_id');
            }

            // =====================================================
            // 6) Xóa employer logs nếu có
            // =====================================================
            $employerLogsTbl = db_prefix() . 'internship_employer_logs';
            if ($this->db->table_exists($employerLogsTbl)) {
                if ($this->db->field_exists('job_order_id', $employerLogsTbl)) {
                    $this->db->query(
                        "DELETE FROM `{$employerLogsTbl}` WHERE `job_order_id` = ?",
                        [$id]
                    );
                    $assertDbOk('delete tblinternship_employer_logs by job_order_id');
                }
            }

            // =====================================================
            // 7) Xóa lịch nếu có
            // =====================================================
            $calendarTbl = 'tblinternship_calendar';
            if ($this->db->table_exists($calendarTbl) && $this->db->field_exists('job_order_id', $calendarTbl)) {
                $this->db->query(
                    "DELETE FROM `{$calendarTbl}` WHERE `job_order_id` = ?",
                    [$id]
                );
                $assertDbOk('delete tblinternship_calendar by job_order_id');
            }

            // =====================================================
            // 8) Cuối cùng mới xóa job order chính
            // =====================================================
            $ordersTbl = db_prefix() . 'internship_job_orders';
            if (!$this->db->table_exists($ordersTbl)) {
                throw new Exception('Không tìm thấy bảng ' . $ordersTbl . ' để xóa.');
            }

            $this->db->query(
                "DELETE FROM `{$ordersTbl}` WHERE `id` = ?",
                [$id]
            );
            $assertDbOk('delete tblinternship_job_orders by id');

            $remainOrders = (int)$this->db
                ->where('id', $id)
                ->count_all_results($ordersTbl);

            $assertDbOk('count remain tblinternship_job_orders');

            if ($remainOrders > 0) {
                throw new Exception(
                    'Đã chạy DELETE nhưng bản ghi trong ' . $ordersTbl . ' với id=' . $id . ' vẫn còn.'
                );
            }
    
            if ($this->db->trans_status() === false) {
                throw new Exception('DB transaction failed while deleting job order ID=' . $id);
            }

            // Bật lại foreign key trước khi commit
            $this->db->query('SET FOREIGN_KEY_CHECKS=1');

            $this->db->trans_commit();
            return true;
    
        } catch (Throwable $e) {
            // luôn bật lại FK checks trước khi rollback
            @$this->db->query('SET FOREIGN_KEY_CHECKS=1');

            $this->db->trans_rollback();
            $this->last_delete_error = $e->getMessage();
            log_message('error', 'Job order delete exception. ID=' . $id . ' | Message=' . $e->getMessage());
            return false;

        } catch (Exception $e) {
            // luôn bật lại FK checks trước khi rollback
            @$this->db->query('SET FOREIGN_KEY_CHECKS=1');

            $this->db->trans_rollback();
            $this->last_delete_error = $e->getMessage();
            log_message('error', 'Job order delete exception. ID=' . $id . ' | Message=' . $e->getMessage());
            return false;
        }
    }

    /* =====================================================
       LẤY DS ĐƠN TUYỂN CHO DROPDOWN (ứng viên chọn)
    ====================================================== */
    public function get_all_for_select()
    {
        return $this->db
            ->select('
                id,
                company_name_vi,
                company_name_jp,
                job_title_vi,
                job_title,
                major_vi,
                major_jp
            ')
            ->from($this->tbl())
            ->order_by('id', 'DESC')
            ->get()
            ->result_array();
    }

    /* =====================================================
       LẤY DANH SÁCH TRẠNG THÁI (cho filter bên view nếu cần)
    ====================================================== */
    public function get_status_list()
    {
        // Ưu tiên list chuẩn từ workflow model để đồng bộ toàn module
        $this->load->model('internship_management/Internship_model', 'wf');
        return $this->wf->get_status_list($this->wf_job_order_key());
    }
    
    public function get_filter_years($dateField = 'entry_date')
    {
        $allowed = ['interview_date', 'entry_date', 'return_date', 'created_at', 'datecreated'];
        if (!in_array($dateField, $allowed, true)) {
            $dateField = 'entry_date';
        }
    
        $rows = $this->db
            ->select("YEAR($dateField) AS y", false)
            ->from($this->tbl())
            ->where("$dateField IS NOT NULL", null, false)
            ->where("$dateField !=", '')
            ->where("$dateField !=", '0000-00-00')
            ->where("$dateField !=", '0000-00-00 00:00:00')
            ->group_by("YEAR($dateField)", false)
            ->order_by("y", "ASC")
            ->get()
            ->result_array();
    
        $years = [];
        foreach ($rows as $r) {
            $y = (int)($r['y'] ?? 0);
            if ($y > 0) {
                $years[] = $y;
            }
        }
    
        return $years;
    }

    /* =====================================================
       CHUẨN HOÁ CÁC FIELD SỐ
    ====================================================== */
    private function normalize_numeric_fields($data)
    {
        $numericFields = [
            'quantity_male', 'quantity_female', 'quantity_total',
            'age_from', 'age_to', 'contract_months',
            'salary_total', 'salary_net', 'tax', 'insurance',
            'dormitory', 'utilities', 'bonus', 'raise_salary',
        ];

        foreach ($numericFields as $f) {
            if (isset($data[$f])) {
                $data[$f] = $this->to_int($data[$f]);
            }
        }

        return $data;
    }

    private function to_int($val)
    {
        if ($val === null || $val === '') {
            return null;
        }
        // Bỏ dấu , . sau đó cast int
        return (int) str_replace([',', '.'], '', (string)$val);
    }

    /* =====================================================
       CHUẨN HOÁ CÁC FIELD NGÀY (lưu dạng Y-m-d)
       Tập trung chủ yếu interview_date, entry_date
    ====================================================== */
    /*private function normalize_date_fields($data)
    {
        foreach (['interview_date', 'entry_date'] as $f) {
            if (!empty($data[$f])) {
                $data[$f] = $this->normalize_date($data[$f]);
            }
        }
        return $data;
    }*/
    
    private function normalize_date_fields($data)
    {
        foreach (['interview_date', 'entry_date', 'return_date', 'return_date_vi'] as $f) {
            if (array_key_exists($f, $data)) {
                $data[$f] = $this->normalize_date($data[$f]);
            }
        }
    
        return $data;
    }

    /**
     * Chuẩn hoá về dạng YYYY-mm-dd (lưu DB)
     * Hỗ trợ:
     * - 2026-04-10
     * - 2026/04/10
     * - 2026年4月10日
     * - 10/04/2026 (d/m/Y)
     */
   /* private function normalize_date($val)
    {
        if (empty($val)) {
            return null;
        }

        $val = trim($val);

        // 2026年4月10日 → 2026-4-10
        $val = preg_replace('/年|月/u', '-', $val);
        $val = str_replace('日', '', $val);

        // 10/04/2026 → 2026-04-10
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        $ts = strtotime($val);
        return $ts ? date('Y-m-d', $ts) : null;
    }*/
    private function normalize_date($val)
    {
        if ($val === null) {
            return null;
        }
    
        $val = trim((string)$val);
    
        if ($val === '') {
            return null;
        }
    
        // Nếu là datetime thì lấy phần ngày
        if (preg_match('/^(\d{4}-\d{1,2}-\d{1,2})\s+/', $val, $m)) {
            $val = $m[1];
        }
    
        // 2026年4月10日 -> 2026-4-10
        $val = preg_replace('/年|月/u', '-', $val);
        $val = str_replace('日', '', $val);
        $val = str_replace('.', '-', $val);
    
        $year = null;
        $month = null;
        $day = null;
    
        // YYYY-MM-DD hoặc YYYY/MM/DD
        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $val, $m)) {
            $year  = (int)$m[1];
            $month = (int)$m[2];
            $day   = (int)$m[3];
        }
        // DD/MM/YYYY hoặc DD-MM-YYYY
        elseif (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $val, $m)) {
            $day   = (int)$m[1];
            $month = (int)$m[2];
            $year  = (int)$m[3];
        } else {
            return null;
        }
    
        if (!$year || !$month || !$day) {
            return null;
        }
    
        // Chặn ngày vô lý theo nghiệp vụ internship
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
    

    /* =====================================================
       THỐNG KÊ ỨNG VIÊN THEO ĐƠN
       - total
       - male
       - female
    ====================================================== */


    /**
     * Resolve real table name that exists in DB.
     * Tries: db_prefix().'name', 'tblname', 'name'
     */
    public function resolve_table($name)
    {
        $candidates = [];
        $candidates[] = db_prefix() . $name;
        $candidates[] = 'tbl' . $name;
        $candidates[] = $name;

        foreach ($candidates as $t) {
            $like = $this->db->escape_like_str($t);
            $q = $this->db->query("SHOW TABLES LIKE '{$like}'")->row_array();
            if (!empty($q)) {
                return $t;
            }
        }
        return db_prefix() . $name;
    }

    /**
     * Robust application stats: supports different table prefixes and gender formats.
     * Returns: ['total'=>int,'male'=>int,'female'=>int]
     */
    public function get_application_stats($job_order_id)
    {
        $job_order_id = (int)$job_order_id;
        $tblApps = $this->resolve_table('internship_applications');

        $rows = $this->db->query(
            "SELECT gender, COUNT(*) c FROM {$tblApps} WHERE job_order_id = ? GROUP BY gender",
            [$job_order_id]
        )->result_array();

        $total = 0; $male = 0; $female = 0;
        $numCounts = [];

        foreach ($rows as $r) {
            $g = isset($r['gender']) ? trim((string)$r['gender']) : '';
            $c = (int)$r['c'];
            $total += $c;
            if ($g === '') continue;

            if (preg_match('/^\d+$/', $g)) {
                $numCounts[(int)$g] = ($numCounts[(int)$g] ?? 0) + $c;
                continue;
            }

            $gl = mb_strtolower($g);
            if (preg_match('/\b(nam|male|m)\b/u', $gl) || str_contains($g, '男')) {
                $male += $c; continue;
            }
            if (preg_match('/\b(nữ|nu|female|f)\b/u', $gl) || str_contains($g, '女')) {
                $female += $c; continue;
            }
        }

        if (!empty($numCounts)) {
            if (array_key_exists(2, $numCounts)) {
                $male   += (int)($numCounts[1] ?? 0);
                $female += (int)($numCounts[2] ?? 0);
            } else {
                $male   += (int)($numCounts[0] ?? 0);
                $female += (int)($numCounts[1] ?? 0);
            }
        }

        if ($male + $female > $total) {
            $male = min($male, $total);
            $female = max(0, $total - $male);
        }

        return ['total'=>$total,'male'=>$male,'female'=>$female];
    }

    /** Status order map (pipeline) */
    public function status_order_map()
    {
        return [
            'received'            => 10,
            'sent_to_school'      => 20,
            'recruiting'          => 30,
            'has_candidates'      => 40,
            'interview_scheduled' => 50,
            'interviewed'         => 60,
            'result_and_docs'     => 70,
            'preparing_docs'      => 80,
            'docs_done'           => 90,
            'waiting_coe'         => 100,
            'coe_done'            => 110,
            'visa_applying'       => 120,
            'visa_done'           => 130,
            'waiting_entry'       => 140,
            'entry'               => 150,
            'done'                => 160,
            'cancelled'           => 1000,
        ];
    }

    /**
     * Insert log safely based on real columns (avoid "note" missing).
     */
    public function add_job_order_log_safe($job_order_id, $payload = [])
    {
        $job_order_id = (int)$job_order_id;
        $tblLogs = $this->resolve_table('internship_job_order_logs');
        $cols = $this->db->list_fields($tblLogs);

        $data = [];
        if (in_array('job_order_id', $cols, true)) $data['job_order_id'] = $job_order_id;
        if (in_array('staff_id', $cols, true))     $data['staff_id']     = (int)get_staff_user_id();
        if (in_array('created_at', $cols, true))   $data['created_at']   = date('Y-m-d H:i:s');
        if (in_array('dateadded', $cols, true))    $data['dateadded']    = date('Y-m-d H:i:s');

        $type = $payload['type'] ?? 'log';
        if (in_array('type', $cols, true))   $data['type']   = $type;
        if (in_array('action', $cols, true)) $data['action'] = $type;

        if (isset($payload['from_status']) && in_array('from_status', $cols, true)) $data['from_status'] = $payload['from_status'];
        if (isset($payload['to_status']) && in_array('to_status', $cols, true))     $data['to_status']   = $payload['to_status'];

        $msg = (string)($payload['message'] ?? '');
        foreach (['message','description','content','note'] as $mc) {
            if (in_array($mc, $cols, true)) { $data[$mc] = $msg; break; }
        }

        if (isset($payload['meta']) && in_array('meta', $cols, true)) $data['meta'] = $payload['meta'];

        $data = array_intersect_key($data, array_flip($cols));
        if (empty($data)) return false;

        return (bool)$this->db->insert($tblLogs, $data);
    }

    /** Controller helper */
    public function log_field_changes($id, $oldRow, $changedFields, $source = 'manual')
    {
        $id = (int)$id;
        $changes = [];
        foreach ($changedFields as $k => $newVal) {
            $oldVal = $oldRow[$k] ?? null;
            if ((string)$oldVal !== (string)$newVal) {
                $changes[$k] = ['from'=>$oldVal, 'to'=>$newVal];
            }
        }
        if (empty($changes)) return;

        $this->add_job_order_log_safe($id, [
            'type'    => ($source === 'auto') ? 'auto_update_field' : 'manual_update_field',
            'message' => 'Cập nhật thông tin đơn tuyển',
            'meta'    => json_encode($changes, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /** Update status and log (manual/auto) */
    public function update_status_with_log($id, $new_status, $source = 'manual', $meta = [])
    {
        $id = (int)$id;
        $job = $this->get($id);
        if (!$job) return false;

        $old = (string)($job['status'] ?? 'received');
        $new = $this->normalize_status((string)$new_status);

        if ($source === 'auto' && in_array($old, ['done','cancelled'], true)) {
            return true;
        }

        if ($source === 'auto') {
            $order = $this->status_order_map();
            if (($order[$new] ?? 0) < ($order[$old] ?? 0)) {
                return true;
            }
        }

        $tblOrders = $this->resolve_table('internship_job_orders');
        if ($source === 'manual' && $this->db->field_exists('manual_override', $tblOrders)) {
            $this->db->set('manual_override', 1);
        }

        $ok = $this->update($id, ['status' => $new]);
        if ($ok) {
            $this->add_job_order_log_safe($id, [
                'type'        => ($source === 'auto') ? 'auto_status' : 'manual_status',
                'message'     => ($source === 'auto') ? 'Tự động cập nhật trạng thái' : 'Cập nhật trạng thái thủ công',
                'from_status' => $old,
                'to_status'   => $new,
                'meta'        => json_encode($meta, JSON_UNESCAPED_UNICODE),
            ]);
        }
        return (bool)$ok;
    }

    /**
     * Auto update status based on dates.
     * Only runs when manual_override=0 (if column exists).
     */
    public function auto_update_status_if_needed($row)
    {
        if (!$row || empty($row['id'])) return;

        $tblOrders = $this->resolve_table('internship_job_orders');
        if ($this->db->field_exists('manual_override', $tblOrders) && !empty($row['manual_override'])) {
            return;
        }

        $id = (int)$row['id'];
        $current = (string)($row['status'] ?? 'received');
        if (in_array($current, ['done','cancelled'], true)) return;

        $today = date('Y-m-d');

        $interview = !empty($row['interview_date']) ? substr((string)$row['interview_date'],0,10) : null;
        $entry     = !empty($row['entry_date']) ? substr((string)$row['entry_date'],0,10) : null;
        $return    = !empty($row['return_date']) ? substr((string)$row['return_date'],0,10) : null;
        $exit      = !empty($row['exit_date']) ? substr((string)$row['exit_date'],0,10) : null;
        $end       = $return ?: $exit;

        $target = null;

        if ($end && $today >= $end) {
            $target = 'done';
        } elseif ($entry && $today >= $entry) {
            $target = 'entry';
        } elseif ($entry && $today < $entry) {
            $order = $this->status_order_map();
            if (($order[$current] ?? 0) >= ($order['visa_done'] ?? 130)) {
                $target = 'waiting_entry';
            }
        } elseif ($interview && $today >= $interview) {
            $target = 'interviewed';
        } elseif ($interview && $today < $interview) {
            $order = $this->status_order_map();
            if (($order[$current] ?? 0) < ($order['interview_scheduled'] ?? 50)) {
                $target = 'interview_scheduled';
            }
        }

        if ($target && $target !== $current) {
            $this->update_status_with_log($id, $target, 'auto', ['by'=>'date']);
        }
    }

}
