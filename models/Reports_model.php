<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reports_model extends App_Model
{
    private $col_cache = [];

    /*public function __construct()
    {
        parent::__construct();
        $this->load->helper('internship_management/job_order_status');
    }*/
    
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('internship_management/job_order_status');
        $this->load->helper('internship_management/internship_status');
    }

    /* ============================================================
        Helpers (FLEX columns/tables)
    ============================================================ */
    private function tbl($name)
    {
        return db_prefix() . $name;
    }

    private function has_col($table, $col)
    {
        $key = $table . '::' . $col;
        if (isset($this->col_cache[$key])) return $this->col_cache[$key];

        $ok = $this->db->field_exists($col, $table);
        $this->col_cache[$key] = $ok;
        return $ok;
    }

    private function pick_col($table, $candidates = [])
    {
        foreach ($candidates as $c) {
            if ($this->has_col($table, $c)) return $c;
        }
        return null;
    }

    /**
     * Sanitize column name that may come as "a.status", "`a`.`status`", ".status", etc.
     * Return bare column name without alias/backticks/dots.
     */
    private function clean_col($col)
    {
        $col = trim((string)$col);
        if ($col === '') return '';

        // remove backticks and spaces
        $col = str_replace('`', '', $col);

        // if comes as ".status" or " a.status " etc.
        $col = trim($col);
        $col = preg_replace('/^[^a-zA-Z0-9_]+/', '', $col); // strip leading symbols like dot

        // take last segment after dots
        if (strpos($col, '.') !== false) {
            $parts = explode('.', $col);
            $col = end($parts);
        }

        // final sanitize
        $col = preg_replace('/[^a-zA-Z0-9_]/', '', $col);
        return $col;
    }

    /**
     * Build safe backticked alias+col: `a`.`status`
     */
    private function ref($alias, $col)
    {
        $alias = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$alias);
        $col = $this->clean_col($col);
        if ($alias === '' || $col === '') return '';
        return '`' . $alias . '`.`' . $col . '`';
    }



    private function like_any($fields, $keyword)
    {
        if (empty($keyword) || empty($fields)) return;
        $this->db->group_start();
        $first = true;
        foreach ($fields as $f) {
            if (!$f) continue;
            if ($first) {
                $this->db->like($f, $keyword);
                $first = false;
            } else {
                $this->db->or_like($f, $keyword);
            }
        }
        $this->db->group_end();
    }

    private function where_year_month($field, $year, $month)
    {
        if (!empty($year))  $this->db->where("YEAR($field)", (int)$year, false);
        if (!empty($month)) $this->db->where("MONTH($field)", (int)$month, false);
    }

    private function where_date_range($field, $from = null, $to = null)
    {
        if (!$field) return;
        if (!empty($from)) $this->db->where($field . ' >=', $from);
        if (!empty($to))   $this->db->where($field . ' <=', $to);
    }

    /* ============================================================
        Status / Gender normalize
    ============================================================ */
    /*public function normalize_status($val)
    {
        $v = strtolower(trim((string)$val));
        if ($v === 'passed') return 'pass';
        if ($v === 'cancel' || $v === 'canceled') return 'cancelled';
        return $v;
    }*/
    
    public function normalize_status($val)
    {
        if (function_exists('im_normalize_status')) {
            return im_normalize_status($val);
        }
    
        $v = strtolower(trim((string)$val));
        if ($v === '' || in_array($v, ['-', '—', '--', 'null', 'undefined', 'n/a', 'na'], true)) {
            return 'not_updated';
        }
    
        if ($v === 'passed') return 'pass';
        if ($v === 'cancel' || $v === 'canceled') return 'cancelled';
    
        return $v;
    }

    /*public function translate_status($val)
    {
        $v = $this->normalize_status($val);
        /*$map = [
            'not_updated'         => 'Chưa cập nhật',
            'applied'             => 'Đã ứng tuyển',
            'interview_scheduled' => 'Hẹn lịch phỏng vấn',
            'pass'                => 'Đậu phỏng vấn',
            'fail'                => 'Rớt phỏng vấn',

            'prepare_documents'   => 'Chuẩn bị hồ sơ',
            'done_documents'      => 'Hoàn tất hồ sơ',
             'docs_done'      => 'Hoàn tất hồ sơ',

            'waiting_coe'         => 'Chờ COE',
            'got_coe'             => 'Đã có COE',

            'visa_processing'     => 'Làm visa',
            'ticket_booking'      => 'Mua vé nhập cảnh',
            'pre_departure'       => 'Chuẩn bị bay',
             // ✅ bạn cần dịch 2 cái này
        'done'                => 'Hoàn thành Chương Trình',
        'received'            => 'Tiếp nhận đơn',

            'entry'               => 'Đã nhập cảnh',
            'in_japan'            => 'Đang ở Nhật',
            'returned'            => 'Đã về nước',
            'cancelled'           => 'Huỷ',
        ];*/
        
        /*$map = [
            'not_updated'         => 'Chưa cập nhật',
            'applied'             => 'Ứng tuyển',
            'interview_scheduled' => 'Hẹn phỏng vấn',
            'pass'                => 'Đạt',
            'passed'              => 'Đạt',
            'fail'                => 'Rớt',
            'recruiting'          => 'Đang tuyển ứng viên',
            
            'sent_to_schools'     => 'Đã gửi đơn cho trường',
            'prepare_documents'   => 'Chuẩn bị hồ sơ',
            'docs_preparing'      => 'Chuẩn bị hồ sơ',
        
            'done_documents'      => 'Hoàn tất hồ sơ',
            'docs_done'           => 'Hoàn tất hồ sơ',
        
            'waiting_coe'         => 'Chờ COE',
            'coe_waiting'         => 'Chờ COE',
        
            'got_coe'             => 'Đã có COE',
            'coe_done'            => 'Đã có COE',
        
            'visa_processing'     => 'Làm visa',
            'ticket_booking'      => 'Mua vé nhập cảnh',
            'pre_departure'       => 'Chuẩn bị bay',
        
            'done'                => 'Hoàn thành chương trình',
            'received'            => 'Tiếp nhận đơn',
        
            'entry'               => 'Đã nhập cảnh',
            'in_japan'            => 'Đang ở Nhật',
            'returned'            => 'Đã về nước',
        
            'cancelled'           => 'Huỷ',
            'canceled'            => 'Huỷ',
        ];
        return $map[$v] ?? (string)$val;
    }*/
    
    /*public function translate_status($val)
    {
        return im_job_order_status_label($val, 'vi');
    }*/
    
    public function translate_status($val)
    {
        $v = trim((string)$val);
        if ($v === '') {
            return '—';
        }
    
        return im_status_label_vi($v);
    }

    public function translate_gender($val)
    {
        $v = strtolower(trim((string)$val));
        if ($v === 'm' || $v === '1' || $v === 'male' || $v === 'nam') return 'Nam';
        if ($v === 'f' || $v === '2' || $v === 'female' || $v === 'nữ' || $v === 'nu') return 'Nữ';
        return (string)$val;
    }

    /* ============================================================
        Detect schema for Applications / Job Orders
    ============================================================ */
    private function apps_schema()
    {
        $t = $this->tbl('internship_applications');

        //$status = $this->pick_col($t, ['status', 'dossier_progress', 'stage', 'application_status', 'app_status']);
        $status = $this->pick_col($t, ['dossier_progress', 'status', 'stage', 'application_status', 'app_status']);
        $apply  = $this->pick_col($t, ['apply_date', 'created_at', 'date_created', 'datecreated', 'addedfrom_date', 'created_date']);
        $entry  = $this->pick_col($t, ['entry_date', 'date_entry', 'arrival_date']);
        $return = $this->pick_col($t, ['return_date', 'date_return', 'back_date']);

        $interview_date   = $this->pick_col($t, ['interview_date', 'date_interview']);
        $interview_result = $this->pick_col($t, ['interview_result', 'interview_status', 'result_interview']);

        $name   = $this->pick_col($t, ['full_name', 'candidate_name', 'name', 'fullname']);
        $gender = $this->pick_col($t, ['gender', 'sex']);
        $school = $this->pick_col($t, ['school_name', 'school_name_vi', 'university', 'school', 'college']);
        $major  = $this->pick_col($t, ['major', 'major_vi', 'major_name']);

        $job_id = $this->pick_col($t, ['job_order_id', 'job_id', 'order_id', 'internship_job_order_id']);

        return compact('t','status','apply','entry','return','interview_date','interview_result','name','gender','school','major','job_id');
    }

    private function jobs_schema()
    {
        $t = $this->tbl('internship_job_orders');

        $company_vi = $this->pick_col($t, ['company_name_vi', 'company_name', 'company']);
        $company_jp = $this->pick_col($t, ['company_name_jp', 'company_jp']);

        $major_vi   = $this->pick_col($t, ['major_vi', 'major', 'major_name']);
        $major_jp   = $this->pick_col($t, ['major_jp']);

        $status     = $this->pick_col($t, ['status', 'job_status', 'stage']);

        $round_no   = $this->pick_col($t, ['round_no', 'round', 'recruit_round']);

        /*$qty_male   = $this->pick_col($t, ['quantity_male', 'male_qty']);
        $qty_female = $this->pick_col($t, ['quantity_female', 'female_qty']);*/
        
        $qty_male   = $this->pick_col($t, ['quantity_male', 'quantity_male_vi', 'male_qty']);
        $qty_female = $this->pick_col($t, ['quantity_female', 'quantity_female_vi', 'female_qty']);
        
  /*$qty_total  = $this->pick_col($t, [
  'qty_total',         // ✅ cái bạn đang dùng thực tế
  'quantity_total',
  'so_luong',
  'recruit_quantity',
  'quantity',
  'total_qty',
  'recruit_qty',
  'quantity_need',
  'qty_required',
  'total_quantity'
]);*/
        
        $qty_total  = $this->pick_col($t, [
          'qty_total',
          'quantity_total',
          'quantity_total_vi',
          'so_luong',
          'recruit_quantity',
          'quantity',
          'quantity_vi',
          'total_qty',
          'recruit_qty',
          'quantity_need',
          'qty_required',
          'total_quantity'
        ]); 

        $interview  = $this->pick_col($t, ['interview_date', 'date_interview']);
        $entry      = $this->pick_col($t, ['entry_date', 'date_entry', 'arrival_date']);
        $return     = $this->pick_col($t, ['return_date', 'date_return', 'back_date']);

        return compact('t','company_vi','company_jp','major_vi','major_jp','status','round_no','qty_male','qty_female','qty_total','interview','entry','return');
    }

    /* ============================================================
        Status lists (match view behaviour)
    ============================================================ */
 public function get_job_order_status_list()
{
    // Hard-fix: use raw SQL to avoid CI QB edge-cases with "IS NOT NULL"
    $table = db_prefix() . 'internship_job_orders';

    // pick real status column
    $col = null;
    foreach (['status','job_status','stage'] as $c) {
        if ($this->db->field_exists($c, $table)) { $col = $c; break; }
    }
    if (!$col) return [];

    // sanitize any weird whitespace (including NBSP)
    $col = preg_replace('/\s+/u', '', trim((string)$col));
    if ($col === '') return [];
    if (!$this->db->field_exists($col, $table)) return [];

    $table_bt = '`' . str_replace('`','',$table) . '`';
    $ref = '`j`.`' . $col . '`';

    $sql = "SELECT DISTINCT {$ref} AS st
            FROM {$table_bt} AS `j`
            WHERE {$ref} IS NOT NULL AND {$ref} <> ''
            ORDER BY st ASC";

    $rows = $this->db->query($sql)->result_array();

    /*$out = [];
    foreach ($rows as $r) {
        $k = (string)($r['st'] ?? '');
        if ($k !== '') $out[$k] = $this->translate_status($k);
    }
    return $out;*/
    $out = [];
    foreach ($rows as $r) {
        $raw = trim((string)($r['st'] ?? ''));
        if ($raw === '') {
            continue;
        }
    
        $key = function_exists('im_job_order_normalize_status')
            ? im_job_order_normalize_status($raw)
            : $raw;
    
        $out[$key] = function_exists('im_job_order_status_label')
            ? im_job_order_status_label($key, 'vi')
            : $key;
    }
    return $out;
}


 /*public function get_application_status_list()
{
    // Hard-fix: raw SQL to avoid QB "IS NOT NULL" parsing issues
    $apps = $this->apps_schema();
    if (empty($apps['status'])) return [];

    $col = $this->clean_col($apps['status']);
    $col = preg_replace('/\s+/u','', trim((string)$col));
    if ($col === '') $col = 'status';
    if (!$this->has_col($apps['t'], $col)) return [];

    $table_bt = '`' . str_replace('`','',$apps['t']) . '`';
    $ref = '`a`.`' . $col . '`';

    $sql = "SELECT DISTINCT {$ref} AS st
            FROM {$table_bt} AS `a`
            WHERE {$ref} IS NOT NULL AND {$ref} <> ''
            ORDER BY st ASC";

    $rows = $this->db->query($sql)->result_array();

    $out = [];
    foreach ($rows as $r) {
        $k = (string)($r['st'] ?? '');
        if ($k !== '') $out[$k] = $this->translate_status($k);
    }
    return $out;
}*/
    public function get_application_status_list()
    {
        $this->load->helper('internship_management/internship_status');
        return im_application_filter_status_list();
    }


    /* ============================================================
        Apply shared filters (tab-aware)
    ============================================================ */
    private function apply_app_filters($filters, $apps, $alias = 'a')
    {
        // keyword search
        if (!empty($filters['search'])) {
            $fields = [];
            if ($apps['name'])   $fields[] = $alias . '.' . $apps['name'];
            if ($apps['school']) $fields[] = $alias . '.' . $apps['school'];
            if ($apps['major'])  $fields[] = $alias . '.' . $apps['major'];
            $this->like_any($fields, $filters['search']);
        }

        if (!empty($filters['school']) && $apps['school']) {
            $this->db->like($alias . '.' . $apps['school'], $filters['school']);
        }

        if (!empty($filters['major']) && $apps['major']) {
            $this->db->like($alias . '.' . $apps['major'], $filters['major']);
        }

        /*if (!empty($filters['status']) && $apps['status']) {
            // accept pass/passed as one filter value "pass"
            $st = $this->normalize_status($filters['status']);
            if ($st === 'pass') {
                $this->db->group_start();
                $this->db->where($alias . '.' . $apps['status'], 'pass');
                $this->db->or_where($alias . '.' . $apps['status'], 'passed');
                $this->db->group_end();
            } else {
                $this->db->where($alias . '.' . $apps['status'], $filters['status']);
            }
        }*/
        if (!empty($filters['status'])) {
            $this->load->helper('internship_management/internship_status');
        
            $target = im_application_filter_target($filters['status']);
            $values = $target['values'] ?? [];
        
            $normal_values = array_values(array_filter($values, function($v) {
                return $v !== null && $v !== '';
            }));
        
            $has_empty = in_array('', $values, true);
            $has_null  = in_array(null, $values, true);
        
            if (($target['type'] ?? '') === 'interview_result') {
                if (!empty($apps['interview_result']) && !empty($normal_values)) {
                    $this->db->where_in($alias . '.' . $apps['interview_result'], $normal_values);
                } elseif (!empty($apps['status']) && !empty($normal_values)) {
                    // fallback cho DB cũ
                    $this->db->where_in($alias . '.' . $apps['status'], $normal_values);
                }
            } else {
                if (!empty($apps['status'])) {
                    $this->db->group_start();
        
                    if (!empty($normal_values)) {
                        $this->db->where_in($alias . '.' . $apps['status'], $normal_values);
                    }
        
                    if ($has_empty) {
                        if (!empty($normal_values)) {
                            $this->db->or_where($alias . '.' . $apps['status'], '');
                        } else {
                            $this->db->where($alias . '.' . $apps['status'], '');
                        }
                    }
        
                    if ($has_null) {
                        if (!empty($normal_values) || $has_empty) {
                            $this->db->or_where($alias . '.' . $apps['status'] . ' IS NULL', null, false);
                        } else {
                            $this->db->where($alias . '.' . $apps['status'] . ' IS NULL', null, false);
                        }
                    }
        
                    $this->db->group_end();
                }
            }
        }

        if (!empty($filters['gender']) && $apps['gender']) {
            $this->db->where($alias . '.' . $apps['gender'], $filters['gender']);
        }

        if (!empty($filters['interview_result']) && $apps['interview_result']) {
            $this->db->where($alias . '.' . $apps['interview_result'], $filters['interview_result']);
        }

        // year/month by apply date (default)
        /*if (!empty($filters['year']) || !empty($filters['month'])) {
            if ($apps['apply']) $this->where_year_month($alias . '.' . $apps['apply'], $filters['year'] ?? null, $filters['month'] ?? null);
        }*/
        
        if (!empty($filters['year']) || !empty($filters['month'])) {
            $year  = (int)($filters['year'] ?? 0);
            $month = (int)($filters['month'] ?? 0);
        
            $dateCols = [];
        
            if (!empty($apps['apply']))          $dateCols[] = $apps['apply'];
            if (!empty($apps['interview_date'])) $dateCols[] = $apps['interview_date'];
            if (!empty($apps['entry']))          $dateCols[] = $apps['entry'];
            if (!empty($apps['return']))         $dateCols[] = $apps['return'];
        
            if (!empty($dateCols)) {
                $this->db->group_start();
        
                $first = true;
        
                foreach ($dateCols as $dc) {
                    $cond = [];
        
                    $cond[] = $alias . '.' . $dc . " IS NOT NULL";
                    $cond[] = $alias . '.' . $dc . " <> ''";
                    $cond[] = $alias . '.' . $dc . " <> '0000-00-00'";
                    $cond[] = $alias . '.' . $dc . " <> '0000-00-00 00:00:00'";
        
                    if ($year > 0) {
                        $cond[] = 'YEAR(' . $alias . '.' . $dc . ') = ' . $year;
                    }
        
                    if ($month > 0 && $month <= 12) {
                        $cond[] = 'MONTH(' . $alias . '.' . $dc . ') = ' . $month;
                    }
        
                    $sql = '(' . implode(' AND ', $cond) . ')';
        
                    if ($first) {
                        $this->db->where($sql, null, false);
                        $first = false;
                    } else {
                        $this->db->or_where($sql, null, false);
                    }
                }
        
                $this->db->group_end();
            }
        }

        // date ranges
        if ($apps['apply']) $this->where_date_range($alias . '.' . $apps['apply'], $filters['date_from'] ?? null, $filters['date_to'] ?? null);
        if ($apps['interview_date']) $this->where_date_range($alias . '.' . $apps['interview_date'], $filters['interview_from'] ?? null, $filters['interview_to'] ?? null);
        if ($apps['entry']) $this->where_date_range($alias . '.' . $apps['entry'], $filters['entry_from'] ?? null, $filters['entry_to'] ?? null);
        if ($apps['return']) $this->where_date_range($alias . '.' . $apps['return'], $filters['return_from'] ?? null, $filters['return_to'] ?? null);
    }

    private function apply_job_filters($filters, $jobs, $alias = 'j')
    {
        if (!empty($filters['search'])) {
            $fields = [];
            if ($jobs['company_vi']) $fields[] = $alias . '.' . $jobs['company_vi'];
            if ($jobs['company_jp']) $fields[] = $alias . '.' . $jobs['company_jp'];
            if ($jobs['major_vi'])   $fields[] = $alias . '.' . $jobs['major_vi'];
            if ($jobs['major_jp'])   $fields[] = $alias . '.' . $jobs['major_jp'];
            $this->like_any($fields, $filters['search']);
        }

        if (!empty($filters['company'])) {
            $fields = [];
            if ($jobs['company_vi']) $fields[] = $alias . '.' . $jobs['company_vi'];
            if ($jobs['company_jp']) $fields[] = $alias . '.' . $jobs['company_jp'];
            $this->like_any($fields, $filters['company']);
        }

        if (!empty($filters['major'])) {
            $fields = [];
            if ($jobs['major_vi']) $fields[] = $alias . '.' . $jobs['major_vi'];
            if ($jobs['major_jp']) $fields[] = $alias . '.' . $jobs['major_jp'];
            $this->like_any($fields, $filters['major']);
        }

        if (!empty($filters['status']) && $jobs['status']) {
            $this->db->where($alias . '.' . $jobs['status'], $filters['status']);
        }

        if (!empty($filters['round_no']) && $jobs['round_no']) {
            $this->db->where($alias . '.' . $jobs['round_no'], (int)$filters['round_no']);
        }

        // year/month uses interview date as main for job orders (closest to recruitment timeline)
        /*if (!empty($filters['year']) || !empty($filters['month'])) {
            if ($jobs['interview']) $this->where_year_month($alias . '.' . $jobs['interview'], $filters['year'] ?? null, $filters['month'] ?? null);
        }*/
        if (!empty($filters['year']) || !empty($filters['month'])) {
            $year  = (int)($filters['year'] ?? 0);
            $month = (int)($filters['month'] ?? 0);
        
            $dateCols = [];
            if (!empty($jobs['interview'])) $dateCols[] = $jobs['interview'];
            if (!empty($jobs['entry']))     $dateCols[] = $jobs['entry'];
            if (!empty($jobs['return']))    $dateCols[] = $jobs['return'];
        
            if (!empty($dateCols)) {
                $this->db->group_start();
        
                $first = true;
                foreach ($dateCols as $dc) {
                    $cond = [];
                    if ($year > 0) {
                        $cond[] = 'YEAR(' . $alias . '.' . $dc . ') = ' . $year;
                    }
                    if ($month > 0 && $month <= 12) {
                        $cond[] = 'MONTH(' . $alias . '.' . $dc . ') = ' . $month;
                    }
        
                    if (!empty($cond)) {
                        $sql = '(' . implode(' AND ', $cond) . ')';
                        if ($first) {
                            $this->db->where($sql, null, false);
                            $first = false;
                        } else {
                            $this->db->or_where($sql, null, false);
                        }
                    }
                }
        
                $this->db->group_end();
            }
        }

        if ($jobs['interview']) $this->where_date_range($alias . '.' . $jobs['interview'], $filters['interview_from'] ?? null, $filters['interview_to'] ?? null);
        if ($jobs['entry'])     $this->where_date_range($alias . '.' . $jobs['entry'], $filters['entry_from'] ?? null, $filters['entry_to'] ?? null);
        if ($jobs['return'])    $this->where_date_range($alias . '.' . $jobs['return'], $filters['return_from'] ?? null, $filters['return_to'] ?? null);

        // numeric min/max
        if (!empty($filters['min_qty']) && $jobs['qty_total']) {
            $this->db->where($alias . '.' . $jobs['qty_total'] . ' >=', (int)$filters['min_qty']);
        }
    }

    /* ============================================================
        1) Job Orders Report
    ============================================================ */
    public function job_orders_report($filters = [])
    {
        $jobs = $this->jobs_schema();
        $apps = $this->apps_schema();

        $j = $jobs['t'];
        $a = $apps['t'];

        $company_vi = $jobs['company_vi'] ?: 'id';

        $job_id = $apps['job_id']; // join key in applications

        /*$qty_total_sql = "0 AS quantity_total";
        if ($jobs['qty_total']) {
            $qty_total_sql = "j.{$jobs['qty_total']} AS quantity_total";
        } elseif ($jobs['qty_male'] && $jobs['qty_female']) {
            $qty_total_sql = "(IFNULL(j.{$jobs['qty_male']},0) + IFNULL(j.{$jobs['qty_female']},0)) AS quantity_total";
        }*/
        
        $qty_total_sql = "0 AS quantity_total";
        if ($jobs['qty_total'] && $jobs['qty_male'] && $jobs['qty_female']) {
            $qty_total_sql = "COALESCE(NULLIF(j.{$jobs['qty_total']},0), (IFNULL(j.{$jobs['qty_male']},0) + IFNULL(j.{$jobs['qty_female']},0))) AS quantity_total";
        } elseif ($jobs['qty_total']) {
            $qty_total_sql = "j.{$jobs['qty_total']} AS quantity_total";
        } elseif ($jobs['qty_male'] && $jobs['qty_female']) {
            $qty_total_sql = "(IFNULL(j.{$jobs['qty_male']},0) + IFNULL(j.{$jobs['qty_female']},0)) AS quantity_total";
        }

        $this->db->select("
            j.id AS id,
            " . ($jobs['status'] ? "j.{$jobs['status']} AS status," : "'' AS status,") . "
            " . ($jobs['company_vi'] ? "j.{$jobs['company_vi']} AS company_name_vi," : "'' AS company_name_vi,") . "
            " . ($jobs['company_jp'] ? "j.{$jobs['company_jp']} AS company_name_jp," : "'' AS company_name_jp,") . "
            " . ($jobs['major_vi'] ? "j.{$jobs['major_vi']} AS major_vi," : "'' AS major_vi,") . "
            " . ($jobs['major_jp'] ? "j.{$jobs['major_jp']} AS major_jp," : "'' AS major_jp,") . "
            $qty_total_sql,
            " . ($jobs['interview'] ? "j.{$jobs['interview']} AS interview_date," : "NULL AS interview_date,") . "
            " . ($jobs['entry'] ? "j.{$jobs['entry']} AS entry_date," : "NULL AS entry_date,") . "
            " . ($jobs['return'] ? "j.{$jobs['return']} AS return_date," : "NULL AS return_date,") . "
            COUNT(a.id) AS total_applications
        ", false);

        $this->db->from($j . ' AS j');
        if ($job_id) $this->db->join($a . ' AS a', 'a.' . $job_id . ' = j.id', 'left');
        else $this->db->join($a . ' AS a', '1=0', 'left');

        // Filters on job orders
        $this->apply_job_filters($filters, $jobs, 'j');

        // Applications range filter by apply_date (optional) affects total_applications count
        if ($apps['apply'] && (!empty($filters['date_from']) || !empty($filters['date_to']))) {
            $this->where_date_range('a.' . $apps['apply'], $filters['date_from'] ?? null, $filters['date_to'] ?? null);
        }

        // min/max apps
        $this->db->group_by('j.id');
        if (!empty($filters['min_apps'])) $this->db->having('total_applications >=', (int)$filters['min_apps']);
        if (!empty($filters['max_apps'])) $this->db->having('total_applications <=', (int)$filters['max_apps']);

        $this->db->order_by('j.id', 'DESC');

        return $this->db->get()->result_array();
    }

    /* ============================================================
        2) Applications Report
    ============================================================ */
    public function applications_report($filters = [])
    {
        $apps = $this->apps_schema();
        $jobs = $this->jobs_schema();

        $a = $apps['t'];
        $j = $jobs['t'];

        $status = $this->clean_col($apps['status']);
        $apply  = $apps['apply'] ?: ($apps['entry'] ?: 'id');

        $name   = $apps['name'] ?: 'id';
        $gender = $apps['gender'];
        $school = $apps['school'];
        $major  = $apps['major'];
        $job_id = $apps['job_id'];

        $this->db->select("
            a.id AS id,
            a.$name AS full_name,
            " . ($gender ? "a.$gender AS gender," : "'' AS gender,") . "
            " . ($school ? "a.$school AS school_name," : "'' AS school_name,") . "
            " . ($major ? "a.$major AS major," : "'' AS major,") . "
            " . ($apply ? "a.$apply AS apply_date," : "NULL AS apply_date,") . "
            " . ($apps['interview_result'] ? "a.{$apps['interview_result']} AS interview_result," : "'' AS interview_result,") . "
            " . ($status ? "a.$status AS status," : "'' AS status,") . "
            " . ($jobs['company_vi'] ? "j.{$jobs['company_vi']} AS company_name_vi," : "'' AS company_name_vi,") . "
            j.id AS job_order_id
        ", false);

        $this->db->from($a . ' AS a');
        if ($job_id) $this->db->join($j . ' AS j', 'j.id = a.' . $job_id, 'left');
        else $this->db->join($j . ' AS j', '1=0', 'left');

        $this->apply_app_filters($filters, $apps, 'a');

        // company filter via joined job orders
        if (!empty($filters['company'])) {
            $fields = [];
            if ($jobs['company_vi']) $fields[] = 'j.' . $jobs['company_vi'];
            if ($jobs['company_jp']) $fields[] = 'j.' . $jobs['company_jp'];
            $this->like_any($fields, $filters['company']);
        }

        $this->db->order_by('a.id', 'DESC');

        $rows = $this->db->get()->result_array();
        foreach ($rows as &$r) {
            $r['gender_text'] = $this->translate_gender($r['gender'] ?? '');
            $r['status_text'] = $this->translate_status($r['status'] ?? '');
        }
        return $rows;
    }

    /* ============================================================
        3) Progress Report
    ============================================================ */
    
    public function progress_report($filters = [])
    {
        $apps = $this->apps_schema();
        $jobs = $this->jobs_schema();

        $a = $apps['t'];
        $j = $jobs['t'];

        $status = $this->clean_col($apps['status']);
        $name   = $apps['name'] ?: 'id';
        $gender = $apps['gender'];

        $interview_a = $apps['interview_date'];
        $entry_a     = $apps['entry'] ?: ($apps['apply'] ?: 'id');
        $return_a    = $apps['return'];

        $interview_j = $jobs['interview'];
        $entry_j     = $jobs['entry'];
        $return_j    = $jobs['return'];

        $job_id = $apps['job_id'];

        // Company: prefer VI, fallback JP, treat empty string as NULL
        $company_sql = "'' AS company_name";
        if ($jobs['company_vi'] || $jobs['company_jp']) {
            $parts = [];
            if ($jobs['company_vi']) $parts[] = "NULLIF(TRIM(j.{$jobs['company_vi']}),'')";
            if ($jobs['company_jp']) $parts[] = "NULLIF(TRIM(j.{$jobs['company_jp']}),'')";
            $company_sql = "COALESCE(" . implode(',', $parts) . ") AS company_name";
        }

        // Dates: prefer job_order dates (input at đơn tuyển), fallback app dates
        $interview_sql = "NULL AS interview_date";
        if ($interview_a || $interview_j) {
            if ($interview_j && $interview_a) {
                $interview_sql = "COALESCE(NULLIF(j.$interview_j,'0000-00-00'), NULLIF(a.$interview_a,'0000-00-00')) AS interview_date";
            } elseif ($interview_j) {
                $interview_sql = "NULLIF(j.$interview_j,'0000-00-00') AS interview_date";
            } else {
                $interview_sql = "NULLIF(a.$interview_a,'0000-00-00') AS interview_date";
            }
        }

        $entry_sql = "NULL AS entry_date";
        if ($entry_a || $entry_j) {
            if ($entry_j && $entry_a) {
                $entry_sql = "COALESCE(NULLIF(j.$entry_j,'0000-00-00'), NULLIF(a.$entry_a,'0000-00-00')) AS entry_date";
            } elseif ($entry_j) {
                $entry_sql = "NULLIF(j.$entry_j,'0000-00-00') AS entry_date";
            } else {
                $entry_sql = "NULLIF(a.$entry_a,'0000-00-00') AS entry_date";
            }
        }

        $return_sql = "NULL AS return_date";
        if ($return_a || $return_j) {
            if ($return_j && $return_a) {
                $return_sql = "COALESCE(NULLIF(j.$return_j,'0000-00-00'), NULLIF(a.$return_a,'0000-00-00')) AS return_date";
            } elseif ($return_j) {
                $return_sql = "NULLIF(j.$return_j,'0000-00-00') AS return_date";
            } else {
                $return_sql = "NULLIF(a.$return_a,'0000-00-00') AS return_date";
            }
        }

        $interview_result = $apps['interview_result'];

        $this->db->select("
            a.id AS id,
            a.$name AS full_name,
            " . ($gender ? "a.$gender AS gender," : "'' AS gender,") . "
            " . ($status ? "a.$status AS status," : "'' AS status,") . "
            $interview_sql,
            " . ($interview_result ? "a.$interview_result AS interview_result," : "'' AS interview_result,") . "
            $entry_sql,
            $return_sql,
            $company_sql
        ", false);

        $this->db->from($a . ' AS a');
        if ($job_id) $this->db->join($j . ' AS j', 'j.id = a.' . $job_id, 'left');
        else $this->db->join($j . ' AS j', '1=0', 'left');

        $this->apply_app_filters($filters, $apps, 'a');

        // Year/month filter: prefer job entry_date
        if (!empty($filters['year']) || !empty($filters['month'])) {
            $y = (int)($filters['year'] ?? date('Y'));
            if ($entry_j) {
                $this->db->where("YEAR(j.$entry_j)", $y, false);
                if (!empty($filters['month'])) $this->db->where("MONTH(j.$entry_j)", (int)$filters['month'], false);
            } elseif ($entry_a) {
                $this->db->where("YEAR(a.$entry_a)", $y, false);
                if (!empty($filters['month'])) $this->db->where("MONTH(a.$entry_a)", (int)$filters['month'], false);
            }
        }

        $this->db->order_by(($entry_j ? ('j.' . $entry_j) : ($entry_a ? ('a.' . $entry_a) : 'a.id')), 'DESC');

        $rows = $this->db->get()->result_array();
        foreach ($rows as &$r) {
            $r['gender_text'] = $this->translate_gender($r['gender'] ?? '');
            $r['status_text'] = $this->translate_status($r['status'] ?? '');
        }
        return $rows;
    }


    
    /**
     * Report by School + Major/Faculty:
     * total, processing, in_japan, returned
     */
    public function students_report($filters = [])
    {
        $apps = $this->apps_schema();
        $jobs = $this->jobs_schema();

        $a = $apps['t'];
        $j = $jobs['t'];

        $school = $apps['school'] ?: 'id';
        $major  = $apps['major'] ?: 'id';
        $status = $apps['status'];
        $job_id = $apps['job_id'];

        $entry_a = $apps['entry'];
        $entry_j = $jobs['entry'];

        $in_japan_vals   = ['in_japan','inJapan','dang_o_nhat','dang_nhat'];
        $returned_vals   = ['returned','da_ve_nuoc','ve_nuoc','da_ve'];
        $processing_vals = ['processing','dang_lam_ho_so','chuan_bi_ho_so','lam_ho_so','preparing'];

        $st_col = $status ? "a.$status" : "''";

        $this->db->select("
            COALESCE(NULLIF(TRIM(a.$school),''),'—') AS school,
            COALESCE(NULLIF(TRIM(a.$major),''),'—') AS major,
            COUNT(a.id) AS total,
            SUM(CASE WHEN $st_col IN ('" . implode("','", $processing_vals) . "') THEN 1 ELSE 0 END) AS processing,
            SUM(CASE WHEN $st_col IN ('" . implode("','", $in_japan_vals) . "') THEN 1 ELSE 0 END) AS in_japan,
            SUM(CASE WHEN $st_col IN ('" . implode("','", $returned_vals) . "') THEN 1 ELSE 0 END) AS returned
        ", false);

        $this->db->from($a . ' AS a');
        if ($job_id) $this->db->join($j . ' AS j', 'j.id = a.' . $job_id, 'left');
        else $this->db->join($j . ' AS j', '1=0', 'left');

        $this->apply_app_filters($filters, $apps, 'a');

        // Year/month filter: prefer job entry_date
        if (!empty($filters['year']) || !empty($filters['month'])) {
            $y = (int)($filters['year'] ?? date('Y'));
            if ($entry_j) {
                $this->db->where("YEAR(j.$entry_j)", $y, false);
                if (!empty($filters['month'])) $this->db->where("MONTH(j.$entry_j)", (int)$filters['month'], false);
            } elseif ($entry_a) {
                $this->db->where("YEAR(a.$entry_a)", $y, false);
                if (!empty($filters['month'])) $this->db->where("MONTH(a.$entry_a)", (int)$filters['month'], false);
            }
        }

        $this->db->group_by(['school','major']);
        $this->db->order_by('total', 'DESC');

        return $this->db->get()->result_array();
    }

    //
    public function get_report_years()
    {
        $years = [];
    
        $jobs = $this->jobs_schema();
        $apps = $this->apps_schema();
    
        foreach ([$jobs['interview'], $jobs['entry'], $jobs['return']] as $col) {
            if ($col) {
                $sql = "SELECT DISTINCT YEAR({$col}) AS y FROM {$jobs['t']} WHERE {$col} IS NOT NULL AND {$col} <> '0000-00-00'";
                $rows = $this->db->query($sql)->result_array();
                foreach ($rows as $r) {
                    $y = (int)($r['y'] ?? 0);
                    if ($y > 0) $years[$y] = $y;
                }
            }
        }
    
        if ($apps['apply']) {
            $sql = "SELECT DISTINCT YEAR({$apps['apply']}) AS y FROM {$apps['t']} WHERE {$apps['apply']} IS NOT NULL AND {$apps['apply']} <> '0000-00-00'";
            $rows = $this->db->query($sql)->result_array();
            foreach ($rows as $r) {
                $y = (int)($r['y'] ?? 0);
                if ($y > 0) $years[$y] = $y;
            }
        }
    
        $current = (int)date('Y');
        $years[$current] = $current;
        $years[$current + 1] = $current + 1;
    
        ksort($years);
        return array_values($years);
    }

/* ============================================================
        4) KPI + Pipeline + Monthly (optimized)
    ============================================================ */
    public function get_kpi_summary($filters = [])
    {
        $apps = $this->apps_schema();
        $jobs = $this->jobs_schema();

        $a = $apps['t'];
        $j = $jobs['t'];

        // total jobs (filters by job_orders)
        $this->db->from($j . ' AS j');
        $this->apply_job_filters($filters, $jobs, 'j');
        $total_jobs = (int)$this->db->count_all_results();

        // total apps (filters by applications)
        $this->db->from($a . ' AS a');
        $this->apply_app_filters($filters, $apps, 'a');
        $total_apps = (int)$this->db->count_all_results();

        // passed count (by status=pass/passed OR interview_result=pass if available)
        $passed = 0;
        $this->db->from($a . ' AS a');
        $this->apply_app_filters($filters, $apps, 'a');

        $this->db->group_start();
        if ($apps['interview_result']) {
            $this->db->where('a.' . $apps['interview_result'], 'pass');
        }
        if ($apps['status']) {
            $this->db->or_group_start();
            $this->db->where('a.' . $apps['status'], 'pass');
            $this->db->or_where('a.' . $apps['status'], 'passed');
            $this->db->group_end();
        }
        $this->db->group_end();
        $passed = (int)$this->db->count_all_results();

        $rate = $total_apps > 0 ? round(($passed / $total_apps) * 100, 2) : 0;

        return [
            'total_jobs' => $total_jobs,
            'total_apps' => $total_apps,
            'passed'     => $passed,
            'rate'       => $rate,
        ];
    }

    /**
     * Pipeline optimized:
     *  - Single grouped query instead of looping N times
     *  - Merge pass+passed
     *  - Order by predefined stages (consistent UI)
     */
    public function get_pipeline_summary($filters = [])
    {
        $apps = $this->apps_schema();
        $a = $apps['t'];
        $status = $this->clean_col($apps['status']);
        
        /*$stages = [
            'not_updated',
            'applied',
            'interview_scheduled',
            'pass',
            'fail',
            'docs_preparing',
            'docs_done',
            'coe_waiting',
            'has_coe',
            'visa_processing',
            'ticket_booking',
            'pre_departure',
            'entry',
            'in_japan',
            'returned',
            'cancelled',
        ];*/
        $this->load->helper('internship_management/internship_status');
        $stages = array_keys(im_dossier_progress_list());
        

        // If no status column => return zeros to avoid errors
        if (!$status) {
            $out = [];
            foreach ($stages as $s) $out[] = ['key'=>$s,'label'=>$this->translate_status($s),'total'=>0];
            return $out;
        }

        $this->db->select('a.' . $status . ' AS st, COUNT(a.id) AS total', false);
        $this->db->from($a . ' AS a');
        $this->apply_app_filters($filters, $apps, 'a');
        $this->db->group_by('a.' . $status);

        $rows = $this->db->get()->result_array();

        // map totals by normalized status
        $totals = [];
        foreach ($rows as $r) {
            $k = $this->normalize_status($r['st'] ?? '');
            $totals[$k] = ($totals[$k] ?? 0) + (int)($r['total'] ?? 0);
        }

        $out = [];
        foreach ($stages as $s) {
            $out[] = [
                'key'   => $s,
                'label' => $this->translate_status($s),
                'total' => (int)($totals[$s] ?? 0),
            ];
        }

        // Append any custom statuses not in stages (still visible, avoid missing data)
        /*foreach ($totals as $k=>$v) {
            if (!in_array($k, $stages, true)) {
                $out[] = ['key'=>$k,'label'=>$this->translate_status($k),'total'=>(int)$v];
            }
        }*/
        
        foreach ($totals as $k => $v) {
            if ((int)$v <= 0) {
                continue;
            }
        
            // status rác => dồn về not_updated, không tạo card dấu "-"
            if ($k === '' || in_array($k, ['-', '—', '--', '_', 'null', 'undefined'], true)) {
                continue;
            }
        
            if (!in_array($k, $stages, true)) {
                $out[] = [
                    'key'   => $k,
                    'label' => $this->translate_status($k),
                    'total' => (int)$v
                ];
            }
        }

        return $out;
    }

    /**
     * Tab-aware monthly series:
     *  - job_orders: group by interview_date (job order)
     *  - applications: group by apply_date
     *  - progress: group by entry_date
     */
    public function get_monthly_series($filters = [])
    {
        $tab = $filters['tab'] ?? 'job_orders';
        $year = !empty($filters['year']) ? (int)$filters['year'] : (int)date('Y');

        $map = array_fill(1, 12, 0);

        if ($tab === 'job_orders') {
            $jobs = $this->jobs_schema();
            if (!$jobs['interview']) {
                $out = [];
                for ($m=1;$m<=12;$m++) $out[] = ['month'=>$m,'total'=>0];
                return $out;
            }

            $this->db->select("MONTH(j.{$jobs['interview']}) AS month, COUNT(j.id) AS total", false);
            $this->db->from($jobs['t'] . ' AS j');
            $this->apply_job_filters($filters, $jobs, 'j');
            $this->db->where("YEAR(j.{$jobs['interview']})", $year, false);
            $this->db->group_by("MONTH(j.{$jobs['interview']})");
            $rows = $this->db->get()->result_array();

            foreach ($rows as $r) {
                $m = (int)($r['month'] ?? 0);
                if ($m >= 1 && $m <= 12) $map[$m] = (int)($r['total'] ?? 0);
            }
        } elseif ($tab === 'progress') {
            $apps = $this->apps_schema();
            if (!$apps['entry']) {
                $out = [];
                for ($m=1;$m<=12;$m++) $out[] = ['month'=>$m,'total'=>0];
                return $out;
            }

            $this->db->select("MONTH(a.{$apps['entry']}) AS month, COUNT(a.id) AS total", false);
            $this->db->from($apps['t'] . ' AS a');
            $this->apply_app_filters($filters, $apps, 'a');
            $this->db->where("YEAR(a.{$apps['entry']})", $year, false);
            $this->db->group_by("MONTH(a.{$apps['entry']})");
            $rows = $this->db->get()->result_array();

            foreach ($rows as $r) {
                $m = (int)($r['month'] ?? 0);
                if ($m >= 1 && $m <= 12) $map[$m] = (int)($r['total'] ?? 0);
            }
        } else {
            $apps = $this->apps_schema();
            if (!$apps['apply']) {
                $out = [];
                for ($m=1;$m<=12;$m++) $out[] = ['month'=>$m,'total'=>0];
                return $out;
            }

            $this->db->select("MONTH(a.{$apps['apply']}) AS month, COUNT(a.id) AS total", false);
            $this->db->from($apps['t'] . ' AS a');
            $this->apply_app_filters($filters, $apps, 'a');
            $this->db->where("YEAR(a.{$apps['apply']})", $year, false);
            $this->db->group_by("MONTH(a.{$apps['apply']})");
            $rows = $this->db->get()->result_array();

            foreach ($rows as $r) {
                $m = (int)($r['month'] ?? 0);
                if ($m >= 1 && $m <= 12) $map[$m] = (int)($r['total'] ?? 0);
            }
        }

        $out = [];
        for ($m=1;$m<=12;$m++) $out[] = ['month'=>$m,'total'=>$map[$m]];
        return $out;
    }

    /* ============================================================
        Export (CSV / XLSX)
        - We output "xls" tab-separated for both formats for compatibility.
        - Controller chooses 'csv'/'xlsx' but both are safe in Excel.
    ============================================================ */
    public function export_by_tab($tab, $filters, $format = 'csv', $status_list = [])
    {
        $filename = $tab . "_report_" . date('Ymd_His');

        if ($format === 'xlsx') {
            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename={$filename}.xls");
        } else {
            header("Content-Type: text/csv; charset=utf-8");
            header("Content-Disposition: attachment; filename={$filename}.csv");
        }

        header("Pragma: no-cache");
        header("Expires: 0");

        if ($tab === 'job_orders') {
            $rows = $this->job_orders_report($filters);
            echo "ID\tDoanh nghiệp\tNgành\tSL tuyển\tNgày PV\tNhập cảnh\tVề nước\tỨng tuyển\tTrạng thái\n";
            foreach ($rows as $r) {
                $st = $r['status'] ?? '';
                $st_txt = $status_list[$st] ?? $st;
                echo ($r['id'] ?? '') . "\t"
                    . ($r['company_name_vi'] ?? '') . "\t"
                    . ($r['major_vi'] ?? '') . "\t"
                    . ($r['quantity_total'] ?? '') . "\t"
                    . ($r['interview_date'] ?? '') . "\t"
                    . ($r['entry_date'] ?? '') . "\t"
                    . ($r['return_date'] ?? '') . "\t"
                    . ($r['total_applications'] ?? '') . "\t"
                    . ($st_txt) . "\n";
            }
            exit;
        }

        if ($tab === 'progress') {
            $rows = $this->progress_report($filters);
            echo "ID\tHọ tên\tDoanh nghiệp\tNgày PV\tKQ PV\tTiến độ\tNhập cảnh\tVề nước\n";
            foreach ($rows as $r) {
                $st = $r['status'] ?? '';
                $st_txt = $status_list[$st] ?? $this->translate_status($st);
                $ir = $r['interview_result'] ?? '';
                echo ($r['id'] ?? '') . "\t"
                    . ($r['full_name'] ?? '') . "\t"
                    . ($r['company_name_vi'] ?? '') . "\t"
                    . ($r['interview_date'] ?? '') . "\t"
                    . ($ir) . "\t"
                    . ($st_txt) . "\t"
                    . ($r['entry_date'] ?? '') . "\t"
                    . ($r['return_date'] ?? '') . "\n";
            }
            exit;
        }

        // applications default
        $rows = $this->applications_report($filters);
        echo "ID\tHọ tên\tTrường\tĐơn tuyển\tNgành\tNgày ứng tuyển\tKQ PV\tTiến độ\n";
        foreach ($rows as $r) {
            $st = $r['status'] ?? '';
            $st_txt = $status_list[$st] ?? $this->translate_status($st);
            $ir = $r['interview_result'] ?? '';
            echo ($r['id'] ?? '') . "\t"
                . ($r['full_name'] ?? '') . "\t"
                . ($r['school_name'] ?? '') . "\t"
                . ($r['company_name_vi'] ?? '') . "\t"
                . ($r['major'] ?? '') . "\t"
                . ($r['apply_date'] ?? '') . "\t"
                . ($ir) . "\t"
                . ($st_txt) . "\n";
        }
        exit;
    }
}
