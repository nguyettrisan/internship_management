<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Management_report_model extends App_Model
{
    
        public function __construct()
    {
        parent::__construct();
        //$this->load->helper('internship_management/job_order_status');
        $this->load->helper('internship_management/internship_status');
    }
    
    private function has_field($table, $field)
    {
        $fields = $this->db->list_fields($table);
        return is_array($fields) && in_array($field, $fields, true);
    }

    private function pick_first_field($table, $candidates = [])
    {
        $fields = $this->db->list_fields($table);
        if (!is_array($fields)) {
            return '';
        }

        foreach ($candidates as $c) {
            if (in_array($c, $fields, true)) {
                return $c;
            }
        }
        return '';
    }
    
        private function normalize_school_name($name)
    {
        $name = trim((string)$name);
        if ($name === '') {
            return '';
        }

        $norm = mb_strtoupper($name, 'UTF-8');
        $norm = str_replace(['–', '—'], '-', $norm);
        $norm = preg_replace('/\s+/u', ' ', $norm);
        $norm = trim($norm);

        $map = [
            'HUTECH-VJIT'  => 'VJIT',
            'HUTECH - VJIT'=> 'VJIT',
            'HUTECH VJIT'  => 'VJIT',
            'VJIT-HUTECH'  => 'VJIT',
            'VJIT - HUTECH'=> 'VJIT',
            'VJIT HUTECH'  => 'VJIT',
            //VLSG 
            'VLSC' => 'VLSG',
            'VLSG' => 'VLSG',
        ];

        return $map[$norm] ?? $name;
    }

    /*private function school_group_expr($col)
    {
        return "
            CASE
                WHEN REPLACE(TRIM(UPPER(REPLACE(REPLACE({$col}, '—', '-'), '–', '-'))), '  ', ' ') IN (
                    'HUTECH-VJIT',
                    'HUTECH - VJIT',
                    'HUTECH VJIT',
                    'VJIT-HUTECH',
                    'VJIT - HUTECH',
                    'VJIT HUTECH'
                ) THEN 'VJIT'
                ELSE TRIM({$col})
            END
        ";
    }*/
    
    private function school_group_expr($col)
    {
        return "
            CASE
                WHEN REPLACE(TRIM(UPPER(REPLACE(REPLACE({$col}, '—', '-'), '–', '-'))), '  ', ' ') IN (
                    'HUTECH-VJIT',
                    'HUTECH - VJIT',
                    'HUTECH VJIT',
                    'VJIT-HUTECH',
                    'VJIT - HUTECH',
                    'VJIT HUTECH'
                ) THEN 'VJIT'
    
                WHEN REPLACE(TRIM(UPPER(REPLACE(REPLACE({$col}, '—', '-'), '–', '-'))), '  ', ' ') IN (
                    'VLSC',
                    'VLSG'
                ) THEN 'VLSG'
    
                ELSE TRIM({$col})
            END
        ";
    }
    
    private function normalize_text_soft($text)
    {
        $text = trim((string)$text);
        if ($text === '') {
            return '';
        }

        $text = str_replace(["\xc2\xa0", '–', '—', '_'], [' ', '-', '-', ' '], $text);
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);

        return $text;
    }

    private function normalize_text_key($text)
    {
        $text = $this->normalize_text_soft($text);
        if ($text === '') {
            return '';
        }

        $text = mb_strtolower($text, 'UTF-8');

        // bỏ dấu tiếng Việt để so khớp mềm hơn
        $from = ['à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
                 'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
                 'ì','í','ị','ỉ','ĩ',
                 'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
                 'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ',
                 'ỳ','ý','ỵ','ỷ','ỹ',
                 'đ'];
        $to   = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
                 'e','e','e','e','e','e','e','e','e','e','e',
                 'i','i','i','i','i',
                 'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
                 'u','u','u','u','u','u','u','u','u','u','u',
                 'y','y','y','y','y',
                 'd'];

        $text = str_replace($from, $to, $text);

        // bỏ ký tự nhiễu nhưng vẫn giữ chữ số và chữ
        $text = preg_replace('/[^a-z0-9\s\-]/u', ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);

        return $text;
    }

    private function canonical_school_name($school)
    {
        $raw = $this->normalize_text_soft($school);
        $key = $this->normalize_text_key($school);

        if ($key === '') {
            return '';
        }

        // normalize mềm cho trường
        /*if (strpos($key, 'hutech vjit') !== false || strpos($key, 'vjit hutech') !== false) {
            return 'VJIT';
        }*/
        // normalize mềm cho trường
        if (
            strpos($key, 'hutech-vjit') !== false ||
            strpos($key, 'hutech vjit') !== false ||
            strpos($key, 'vjit-hutech') !== false ||
            strpos($key, 'vjit hutech') !== false ||
            $key === 'hutech-vjit' ||
            $key === 'vjit-hutech'
        ) {
            return 'VJIT';
        }
        if ($key === 'vjit') {
            return 'VJIT';
        }
        if ($key === 'vlsc' || $key === 'vlsg') {
            return 'VLSG';
        }
        if ($key === 'uef') {
            return 'UEF';
        }
        if ($key === 'vhu') {
            return 'VHU';
        }
        if ($key === 'hiu') {
            return 'HIU';
        }
        if ($key === 'sonadezi') {
            return 'SONADEZI';
        }
        if ($key === 'kien giang' || $key === 'kiengiang') {
            return 'KIENGIANG';
        }

        // HUTECH riêng với VJIT
        /*if (strpos($key, 'hutech') !== false && strpos($key, 'vjit') === false) {
            return 'HUTECH';
        }*/
        
        // HUTECH riêng với VJIT, bao gồm cả các biến thể như HUTECH - KHOA THÚ Y
        if (
            strpos($key, 'hutech') !== false &&
            strpos($key, 'vjit') === false
        ) {
            return 'HUTECH';
        }

        if (
            strpos($key, 'khoa thu y') !== false &&
            strpos($key, 'hutech') !== false
        ) {
            return 'HUTECH';
        }

        return $raw;
    }

    private function canonical_major_by_school($school, $major)
    {
        $schoolCanon = $this->canonical_school_name($school);
        $majorRaw    = $this->normalize_text_soft($major);
        $majorKey    = $this->normalize_text_key($major);

        if ($majorKey === '') {
            return '';
        }

        // ===== nhóm nhận diện mềm =====
        $isJapaneseStudies =
            strpos($majorKey, 'nhat ban hoc') !== false;

        $isJapaneseLanguage =
            strpos($majorKey, 'ngon ngu nhat') !== false ||
            strpos($majorKey, 'tieng nhat') !== false ||
            strpos($majorKey, 'chuyen nganh tieng nhat') !== false ||
            strpos($majorKey, 'van hoa quoc te') !== false;

        $isHotel =
            strpos($majorKey, 'quan tri khach san') !== false ||
            strpos($majorKey, 'quan tri khac san') !== false ||
            strpos($majorKey, 'quan ly khach san') !== false;

        $isBusiness =
            strpos($majorKey, 'quan tri kinh doanh') !== false;

        $isMarketing =
            strpos($majorKey, 'marketing') !== false;

        $isMultimedia =
            strpos($majorKey, 'truyen thong da phuong tien') !== false;

        $isRestaurant =
            strpos($majorKey, 'quan tri nha hang') !== false ||
            strpos($majorKey, 'dich vu an uong') !== false;

        $isTourismTravel =
            strpos($majorKey, 'du lich va lu hanh') !== false ||
            strpos($majorKey, 'quan tri dich vu du lich') !== false ||
            strpos($majorKey, 'quan tri du lich va khach san') !== false ||
            $majorKey === 'du lich' ||
            strpos($majorKey, 'du lich') !== false;

        $isIntlRelations =
            strpos($majorKey, 'quan he quoc te') !== false;

        $isIntlBusiness =
            strpos($majorKey, 'kinh doanh quoc te') !== false;

        $isHr =
            strpos($majorKey, 'quan tri nhan luc') !== false;

        $isAccounting =
            strpos($majorKey, 'ke toan') !== false;

        $isPr =
            strpos($majorKey, 'quan he cong chung') !== false;

        $isVet =
            strpos($majorKey, 'thu y') !== false ||
            strpos($majorKey, 'chan nuoi') !== false;

        $isJapaneseEconomicInterpretation =
            strpos($majorKey, 'phien dich tieng nhat kinh te thuong mai') !== false ||
            strpos($majorKey, 'phien dich tieng nhat') !== false;

        // ===== chuẩn hoá theo trường =====

        if ($schoolCanon === 'HIU') {
            return 'Nhật Bản học';
        }

        /*if ($schoolCanon === 'HUTECH') {
            if ($isVet) {
                return 'Thú y - Chăn nuôi';
            }
            return 'Ngôn ngữ Nhật';
        }*/
        
        if ($schoolCanon === 'HUTECH') {
            if ($isVet) {
                return 'Thú y - Chăn nuôi';
            }

            if ($isJapaneseLanguage || $isJapaneseStudies) {
                return 'Ngôn ngữ Nhật';
            }

            // các ngành còn lại thực chất thuộc VJIT
            if ($isBusiness) return 'Quản trị kinh doanh';
            if ($isHotel) return 'Quản trị khách sạn';
            if ($isMarketing) return 'Marketing';
            if ($isMultimedia) return 'Truyền thông đa phương tiện';
            if ($isRestaurant) return 'Quản trị nhà hàng và dịch vụ ăn uống';
            if ($isTourismTravel) return 'Quản trị dịch vụ du lịch và lữ hành';

            return 'Ngôn ngữ Nhật';
        }

        if ($schoolCanon === 'VJIT') {
            if ($isBusiness) {
                return 'Quản trị kinh doanh';
            }
            if ($isHotel) {
                return 'Quản trị khách sạn';
            }
            if ($isMarketing) {
                return 'Marketing';
            }
            if ($isMultimedia) {
                return 'Truyền thông đa phương tiện';
            }
            if ($isRestaurant) {
                return 'Quản trị nhà hàng và dịch vụ ăn uống';
            }
            if ($isTourismTravel) {
                return 'Quản trị dịch vụ du lịch và lữ hành';
            }
            if ($isJapaneseLanguage || $isJapaneseStudies) {
                return 'Ngôn ngữ Nhật';
            }
            return $majorRaw;
        }

        if ($schoolCanon === 'KIENGIANG') {
            return 'Quản trị dịch vụ du lịch và lữ hành';
        }

        if ($schoolCanon === 'SONADEZI') {
            return 'Phiên dịch tiếng Nhật kinh tế, thương mại';
        }

        if ($schoolCanon === 'UEF') {
            if ($isJapaneseLanguage || $isJapaneseStudies) {
                return 'Ngôn ngữ Nhật';
            }
            if ($isIntlRelations) {
                return 'Quan hệ quốc tế';
            }
            if ($isHotel) {
                return 'Quản trị khách sạn';
            }
            if ($isBusiness) {
                return 'Quản trị kinh doanh';
            }
            if ($isRestaurant) {
                return 'Quản trị nhà hàng và dịch vụ ăn uống';
            }
            if ($isIntlBusiness) {
                return 'Kinh doanh quốc tế';
            }
            if ($isHr) {
                return 'Quản trị nhân lực';
            }
            if ($isAccounting) {
                return 'Kế toán';
            }
            if ($isMarketing) {
                return 'Marketing';
            }
            if ($isPr) {
                return 'Quan hệ công chúng';
            }
            if ($isTourismTravel) {
                return 'Quản trị Du lịch - Khách sạn';
            }
            return $majorRaw;
        }

        if ($schoolCanon === 'VHU') {
            if ($isJapaneseStudies) {
                return 'Nhật Bản học';
            }
            return 'Ngôn ngữ Nhật';
        }

        return $majorRaw;
    }
    
    private function canonical_school_by_major($school, $major)
    {
        $schoolCanon = $this->canonical_school_name($school);
        $majorKey    = $this->normalize_text_key($major);
        $schoolKey = $this->normalize_text_key($school);
        // nếu đã là biến thể HUTECH-VJIT thì ép thẳng về VJIT
        if ($schoolCanon === 'VJIT') {
            return 'VJIT';
        }

        /*if ($schoolCanon === 'HUTECH') {
            $isJapaneseStudies =
                strpos($majorKey, 'nhat ban hoc') !== false;

            $isJapaneseLanguage =
                strpos($majorKey, 'ngon ngu nhat') !== false ||
                strpos($majorKey, 'tieng nhat') !== false ||
                strpos($majorKey, 'chuyen nganh tieng nhat') !== false ||
                strpos($majorKey, 'van hoa quoc te') !== false;

            $isVet =
                strpos($majorKey, 'thu y') !== false ||
                strpos($majorKey, 'chan nuoi') !== false;

            // HUTECH thật chỉ có 2 nhóm này
            if ($isVet || $isJapaneseLanguage || $isJapaneseStudies) {
                return 'HUTECH';
            }

            // các ngành còn lại thuộc VJIT
            return 'VJIT';
        }*/
        
        if ($schoolCanon === 'HUTECH') {
            $isJapaneseStudies =
                strpos($majorKey, 'nhat ban hoc') !== false;

            $isJapaneseLanguage =
                strpos($majorKey, 'ngon ngu nhat') !== false ||
                strpos($majorKey, 'tieng nhat') !== false ||
                strpos($majorKey, 'chuyen nganh tieng nhat') !== false ||
                strpos($majorKey, 'van hoa quoc te') !== false;

            $isVet =
                strpos($majorKey, 'thu y') !== false ||
                strpos($majorKey, 'chan nuoi') !== false ||
                strpos($schoolKey, 'khoa thu y') !== false;

            // HUTECH thật chỉ có 2 nhóm này
            if ($isVet || $isJapaneseLanguage || $isJapaneseStudies) {
                return 'HUTECH';
            }

            // các ngành còn lại thuộc VJIT
            return 'VJIT';
        }

        return $schoolCanon;
    }

    private function resolve_apps_table()
    {
        $pref = function_exists('db_prefix') ? db_prefix() : '';
        $candidates = [];
        if ($pref !== '') {
            $candidates[] = $pref . 'internship_applications';
        }
        $candidates[] = 'internship_applications';

        $best = '';
        $bestCount = -1;

        foreach ($candidates as $t) {
            $q = $this->db->query('SHOW TABLES LIKE ' . $this->db->escape($t));
            if (!$q || $q->num_rows() === 0) {
                continue;
            }

            $cnt = 0;
            try {
                $row = $this->db->query("SELECT COUNT(*) c FROM {$t}")->row_array();
                $cnt = (int) ($row['c'] ?? 0);
            } catch (Throwable $e) {
                $cnt = 0;
            }

            if ($cnt > $bestCount) {
                $bestCount = $cnt;
                $best = $t;
            }
        }

        return $best !== '' ? $best : (($pref !== '') ? $pref . 'internship_applications' : 'internship_applications');
    }
    
    private function resolve_jobs_table()
    {
        $pref = function_exists('db_prefix') ? db_prefix() : '';
    
        $candidates = [];
        if ($pref !== '') {
            $candidates[] = $pref . 'internship_job_orders';
        }
        $candidates[] = 'internship_job_orders';
    
        foreach ($candidates as $t) {
            $q = $this->db->query('SHOW TABLES LIKE ' . $this->db->escape($t));
            if ($q && $q->num_rows() > 0) {
                return $t;
            }
        }
    
        return ($pref !== '') ? $pref . 'internship_job_orders' : 'internship_job_orders';
    }
    
    private function count_job_orders_for_management($filters = [])
    {
        $jobs = $this->resolve_jobs_table();
    
        $year  = (int)($filters['year'] ?? 0);
        $month = (int)($filters['month'] ?? 0);
        $q     = trim((string)($filters['q'] ?? $filters['keyword'] ?? ''));
        $status = trim((string)($filters['status'] ?? ''));
    
        $where = [];
        $params = [];
    
        $interview = $this->pick_first_field($jobs, ['interview_date', 'date_interview']);
        $entry     = $this->pick_first_field($jobs, ['entry_date', 'date_entry', 'arrival_date']);
        $return    = $this->pick_first_field($jobs, ['return_date', 'date_return', 'back_date']);
        $statusCol = $this->pick_first_field($jobs, ['status', 'job_status', 'stage']);
    
        if ($year > 0 || ($month > 0 && $month <= 12)) {
            $dateParts = [];
    
            foreach ([$interview, $entry, $return] as $dc) {
                if ($dc === '') {
                    continue;
                }
    
                $parts = [];
                $parts[] = "{$dc} IS NOT NULL";
                $parts[] = "{$dc} <> ''";
                $parts[] = "{$dc} <> '0000-00-00'";
                $parts[] = "{$dc} <> '0000-00-00 00:00:00'";
    
                if ($year > 0) {
                    $parts[] = "YEAR({$dc}) = ?";
                    $params[] = $year;
                }
    
                if ($month > 0 && $month <= 12) {
                    $parts[] = "MONTH({$dc}) = ?";
                    $params[] = $month;
                }
    
                $dateParts[] = '(' . implode(' AND ', $parts) . ')';
            }
    
            if (!empty($dateParts)) {
                $where[] = '(' . implode(' OR ', $dateParts) . ')';
            }
        }
    
        if ($status !== '' && $statusCol !== '') {
            $where[] = "{$statusCol} = ?";
            $params[] = $status;
        }
    
        if ($q !== '') {
            $likeParts = [];
    
            foreach (['company_name_vi', 'company_name', 'company_receive', 'receiver_company', 'company_name_jp', 'major', 'major_vi', 'major_name'] as $c) {
                if ($this->has_field($jobs, $c)) {
                    $likeParts[] = "{$c} LIKE ?";
                    $params[] = '%' . $q . '%';
                }
            }
    
            if (!empty($likeParts)) {
                $where[] = '(' . implode(' OR ', $likeParts) . ')';
            }
        }
    
        $whereSql = !empty($where) ? (' WHERE ' . implode(' AND ', $where)) : '';
    
        $row = $this->db->query("SELECT COUNT(*) AS c FROM {$jobs} {$whereSql}", $params)->row_array();
    
        return (int)($row['c'] ?? 0);
    }

    private function where_tail($whereSql)
    {
        return $whereSql ? ($whereSql . ' AND ') : ' WHERE ';
    } 
    
    private function get_report_date_columns($table)
    {
        $candidates = [
            'apply_date',
            'interview_date',
            'entry_date',
            'return_date',
            'datecreated',
            'createdat',
            'updated_at',
            'dateupdated',
        ];

        $out = [];
        foreach ($candidates as $c) {
            if ($this->has_field($table, $c)) {
                $out[] = $c;
            }
        }

        return array_values(array_unique($out));
    }

    private function valid_date_sql($col)
    {
        return "({$col} IS NOT NULL AND {$col} <> '' AND {$col} <> '0000-00-00' AND {$col} <> '0000-00-00 00:00:00')";
    }

    private function build_where($apps, $filters = [], $opt = [])
    {
        $opt = is_array($opt) ? $opt : [];
        $ignore_school = !empty($opt['ignore_school']);

        $where = [];
        $params = [];

        $has_status = $this->has_field($apps, 'status');
        //$apply_col  = $this->pick_first_field($apps, ['apply_date', 'datecreated', 'createdat', 'updated_at', 'dateupdated']);
        $date_cols  = $this->get_report_date_columns($apps);
        $apply_col  = !empty($date_cols) ? $date_cols[0] : '';
        $school_col = $this->pick_first_field($apps, ['school_name', 'school', 'university', 'university_name', 'training_school']);
        //
        $school_expr = ($school_col !== '') ? $this->school_group_expr($school_col) : '';
        $major_col  = $this->pick_first_field($apps, ['major', 'major_name']);

        $year   = (int) ($filters['year'] ?? 0);
        $month  = (int) ($filters['month'] ?? 0);
        $kw     = trim((string) ($filters['keyword'] ?? $filters['q'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        //$school = trim((string) ($filters['school'] ?? ''));
        $school = $this->normalize_school_name($filters['school'] ?? '');

        /*if ($apply_col !== '') {
            if ($year > 0) {
                $where[] = "YEAR({$apply_col}) = ?";
                $params[] = $year;
            }
            if ($month >= 1 && $month <= 12) {
                $where[] = "MONTH({$apply_col}) = ?";
                $params[] = $month;
            }
        }*/
        
        if (!empty($date_cols)) {
            if ($year > 0) {
                $yearParts = [];
                foreach ($date_cols as $dc) {
                    $yearParts[] = "(" . $this->valid_date_sql($dc) . " AND YEAR({$dc}) = ?)";
                    $params[] = $year;
                }
                if (!empty($yearParts)) {
                    $where[] = '(' . implode(' OR ', $yearParts) . ')';
                }
            }

            if ($month >= 1 && $month <= 12) {
                $monthParts = [];
                foreach ($date_cols as $dc) {
                    $monthParts[] = "(" . $this->valid_date_sql($dc) . " AND MONTH({$dc}) = ?)";
                    $params[] = $month;
                }
                if (!empty($monthParts)) {
                    $where[] = '(' . implode(' OR ', $monthParts) . ')';
                }
            }
        }

        if ($has_status && $status !== '') {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        if (!$ignore_school && $school !== '' && $school_col !== '') {
            $where[] = "{$school_col} = ?";
            $params[] = $school;
        }

        if ($kw !== '') {
            $likeParts = [];
            foreach (['full_name', 'student_name', 'phone_student', 'email', 'receiver_company', 'company_receive', 'receiver_prefecture'] as $c) {
                if ($this->has_field($apps, $c)) {
                    $likeParts[] = "{$c} LIKE ?";
                    $params[] = '%' . $kw . '%';
                }
            }
            if ($school_col !== '') {
                $likeParts[] = "{$school_col} LIKE ?";
                $params[] = '%' . $kw . '%';
            }
            if ($major_col !== '') {
                $likeParts[] = "{$major_col} LIKE ?";
                $params[] = '%' . $kw . '%';
            }

            if (!empty($likeParts)) {
                $where[] = '(' . implode(' OR ', $likeParts) . ')';
            }
        }

        $whereSql = !empty($where) ? (' WHERE ' . implode(' AND ', $where)) : '';

        //return [$whereSql, $params, $apply_col, $school_col, $major_col];
        return [$whereSql, $params, $apply_col, $school_col, $major_col, $school_expr];
    }

    /*public function get_years()
    {
        $apps = $this->resolve_apps_table();
        $dateCol = $this->pick_first_field($apps, ['apply_date', 'datecreated', 'createdat', 'updated_at', 'dateupdated']);
        if ($dateCol === '') {
            return [];
        }

        $sql = "SELECT DISTINCT YEAR($dateCol) AS y
                FROM $apps
                WHERE $dateCol IS NOT NULL AND $dateCol <> '0000-00-00'
                ORDER BY y DESC";
        $rows = $this->db->query($sql)->result_array();

        return array_values(array_filter(array_map(function ($r) {
            return (int) ($r['y'] ?? 0);
        }, $rows)));
    }*/
    
    public function get_years()
    {
        $apps = $this->resolve_apps_table();
        $dateCols = $this->get_report_date_columns($apps);

        if (empty($dateCols)) {
            return [];
        }

        $parts = [];
        foreach ($dateCols as $col) {
            $parts[] = "
                SELECT DISTINCT YEAR({$col}) AS y
                FROM {$apps}
                WHERE " . $this->valid_date_sql($col);
        }

        $sql = implode(" UNION ", $parts) . " ORDER BY y DESC";
        $rows = $this->db->query($sql)->result_array();

        $years = array_values(array_filter(array_map(function ($r) {
            return (int)($r['y'] ?? 0);
        }, $rows)));

        $years = array_values(array_unique($years));
        rsort($years, SORT_NUMERIC);

        return $years;
    }

    public function get_statuses()
    {
        $apps = $this->resolve_apps_table();
        if (!$this->has_field($apps, 'status')) {
            return [];
        }

        $sql = "SELECT DISTINCT status
                FROM $apps
                WHERE status IS NOT NULL AND status <> ''
                ORDER BY status ASC";
        $rows = $this->db->query($sql)->result_array();

        /*return array_values(array_filter(array_map(function ($r) {
            return (string) ($r['status'] ?? '');
        }, $rows)));*/
        $out = [];
        foreach ($rows as $r) {
            $st = trim((string)($r['status'] ?? ''));
            if ($st !== '') {
                $out[$st] = $this->translate_status_label($st);
            }
        }
        return $out;
    }
    
    /*private function translate_status_label($val)
    {
        $v = strtolower(trim((string)$val));
        $map = [
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
    
    /*private function translate_status_label($val)
    {
        return im_job_order_status_label($val, 'vi');
    }*/
    
    private function translate_status_label($val)
    {
        return function_exists('im_status_label_vi')
            ? im_status_label_vi($val)
            : (string)$val;
    }
    

    /*public function get_schools($filters = [])
    {
        $apps = $this->resolve_apps_table();
        list($whereSql, $params, , $school_col) = $this->build_where($apps, $filters, ['ignore_school' => true]);
        if ($school_col === '') {
            return [];
        }

        $sql = "
            SELECT {$school_col} AS name
            FROM {$apps}
            " . $this->where_tail($whereSql) . "
            {$school_col} IS NOT NULL AND {$school_col} <> ''
            GROUP BY {$school_col}
            ORDER BY COUNT(*) DESC, name ASC
            LIMIT 200
        ";
        $rows = $this->db->query($sql, $params)->result_array();

        return array_values(array_filter(array_map(function ($r) {
            return trim((string) ($r['name'] ?? ''));
        }, $rows)));
    }*/
    
    public function get_schools($filters = [])
    {
        $apps = $this->resolve_apps_table();
        list($whereSql, $params, , $school_col, , $school_expr) = $this->build_where($apps, $filters, ['ignore_school' => true]);

        if ($school_col === '' || $school_expr === '') {
            return [];
        }

        $sql = "
            SELECT {$school_expr} AS name
            FROM {$apps}
            " . $this->where_tail($whereSql) . "
            {$school_col} IS NOT NULL AND {$school_col} <> ''
            GROUP BY {$school_expr}
            ORDER BY COUNT(*) DESC, name ASC
            LIMIT 200
        ";

        $rows = $this->db->query($sql, $params)->result_array();

        return array_values(array_filter(array_map(function ($r) {
            return trim((string)($r['name'] ?? ''));
        }, $rows)));
    }

    public function get_management_report($filters = [])
    {
        $apps = $this->resolve_apps_table();
        //list($whereSql, $params, , $school_col, $major_col) = $this->build_where($apps, $filters);
        list($whereSql, $params, , $school_col, $major_col, $school_expr) = $this->build_where($apps, $filters);

        $has_job_order = $this->has_field($apps, 'job_order_id');
        $has_student   = $this->has_field($apps, 'student_id');
        $has_school    = ($school_col !== '');
        $has_major     = ($major_col !== '');
        $has_status    = $this->has_field($apps, 'status');
        $has_entry     = $this->has_field($apps, 'entry_date');
        $has_return    = $this->has_field($apps, 'return_date');

        $studentKey = $has_student ? 'COALESCE(NULLIF(student_id,0), id)' : 'id';

        $todayExpr = 'CURDATE()';
        $status_in_japan = $has_status ? "status IN ('in_japan','entered','in_jp','inJapan','Đang ở Nhật')" : '0';
        $status_returned = $has_status ? "status IN ('returned','back_home','returned_vn','backVN','Đã về nước')" : '0';

        $expr_in_japan_by_date = ($has_entry)
            ? "(entry_date IS NOT NULL AND entry_date <> '0000-00-00' " . ($has_return ? "AND (return_date IS NULL OR return_date='0000-00-00' OR return_date > {$todayExpr})" : '') . ')'
            : '0';

        $expr_returned_by_date = ($has_return)
            ? "(return_date IS NOT NULL AND return_date <> '0000-00-00' AND return_date <= {$todayExpr})"
            : '0';

        $expr_in_japan = ($has_entry || $has_return)
            ? "CASE WHEN {$expr_in_japan_by_date} THEN 1 WHEN {$status_in_japan} THEN 1 ELSE 0 END"
            : "CASE WHEN {$status_in_japan} THEN 1 ELSE 0 END";

        $expr_returned = ($has_return)
            ? "CASE WHEN {$expr_returned_by_date} THEN 1 WHEN {$status_returned} THEN 1 ELSE 0 END"
            : "CASE WHEN {$status_returned} THEN 1 ELSE 0 END";

        $count_joborders = $has_job_order ? 'COUNT(DISTINCT job_order_id)' : '0';

        $sqlKpi = "
            SELECT
              {$count_joborders} AS total_job_orders,
              COUNT(DISTINCT {$studentKey}) AS total_students,
              COUNT(DISTINCT CASE WHEN ({$expr_in_japan})=1 THEN {$studentKey} END) AS in_japan,
              COUNT(DISTINCT CASE WHEN ({$expr_returned})=1 THEN {$studentKey} END) AS returned
            FROM {$apps}
            {$whereSql}
        ";
        $kpiRow = $this->db->query($sqlKpi, $params)->row_array();
        /*$kpis = [
            'total_job_orders' => (int) ($kpiRow['total_job_orders'] ?? 0),
            'total_students'   => (int) ($kpiRow['total_students'] ?? 0),
            'in_japan'         => (int) ($kpiRow['in_japan'] ?? 0),
            'returned'         => (int) ($kpiRow['returned'] ?? 0),
        ];*/
        
        $kpis = [
            'total_job_orders' => $this->count_job_orders_for_management($filters),
            'total_students'   => (int) ($kpiRow['total_students'] ?? 0),
            'in_japan'         => (int) ($kpiRow['in_japan'] ?? 0),
            'returned'         => (int) ($kpiRow['returned'] ?? 0),
        ];

        /*$by_school = [];
        if ($has_school) {
            $den = max(1, (int) $kpis['total_students']);
            /*$sqlSchool = "
              SELECT
                {$school_col} AS name,
                COUNT(DISTINCT {$studentKey}) AS total,
                COUNT(DISTINCT CASE WHEN ({$expr_in_japan})=1 THEN {$studentKey} END) AS in_japan,
                COUNT(DISTINCT CASE WHEN ({$expr_returned})=1 THEN {$studentKey} END) AS returned
              FROM {$apps}
              {$whereSql}
              GROUP BY {$school_col}
              ORDER BY total DESC
              LIMIT 80
            ";*/
            /*$sqlSchool = "
              SELECT
                {$school_expr} AS name,
                COUNT(DISTINCT {$studentKey}) AS total,
                COUNT(DISTINCT CASE WHEN ({$expr_in_japan})=1 THEN {$studentKey} END) AS in_japan,
                COUNT(DISTINCT CASE WHEN ({$expr_returned})=1 THEN {$studentKey} END) AS returned
              FROM {$apps}
              {$whereSql}
              GROUP BY {$school_expr}
              ORDER BY total DESC
              LIMIT 80
            ";
            $rows = $this->db->query($sqlSchool, $params)->result_array();
            foreach ($rows as $r) {
                $total = (int) ($r['total'] ?? 0);
                $by_school[] = [
                    'name'     => (string) ($r['name'] ?? '—'),
                    'total'    => $total,
                    'in_japan' => (int) ($r['in_japan'] ?? 0),
                    'returned' => (int) ($r['returned'] ?? 0),
                    'ratio'    => $den ? round(($total * 100) / $den, 2) . '%' : '0%',
                ];
            }
        }*/
        
        $by_school = [];

        if ($has_school && $major_col !== '') {
            $sqlSchool = "
              SELECT
                {$school_col} AS school_raw,
                {$major_col} AS major_raw,
                {$studentKey} AS student_key,
                CASE WHEN ({$expr_in_japan})=1 THEN 1 ELSE 0 END AS is_in_japan,
                CASE WHEN ({$expr_returned})=1 THEN 1 ELSE 0 END AS is_returned
              FROM {$apps}
              {$whereSql}
            ";

            $rowsSchool = $this->db->query($sqlSchool, $params)->result_array();

            $mergedSchool = [];

            foreach ($rowsSchool as $r) {
                $school = $this->canonical_school_by_major(
                    $r['school_raw'] ?? '',
                    $r['major_raw'] ?? ''
                );
                $school = $this->canonical_school_name($school);
                $studentKeyVal = trim((string)($r['student_key'] ?? ''));

                if ($school === '' || $studentKeyVal === '') {
                    continue;
                }

                if (!isset($mergedSchool[$school])) {
                    $mergedSchool[$school] = [
                        'name' => $school,
                        'students' => [],
                        'in_japan_students' => [],
                        'returned_students' => [],
                    ];
                }

                $mergedSchool[$school]['students'][$studentKeyVal] = true;

                if ((int)($r['is_in_japan'] ?? 0) === 1) {
                    $mergedSchool[$school]['in_japan_students'][$studentKeyVal] = true;
                }

                if ((int)($r['is_returned'] ?? 0) === 1) {
                    $mergedSchool[$school]['returned_students'][$studentKeyVal] = true;
                }
            }

            $den = max(1, (int) $kpis['total_students']);

            foreach ($mergedSchool as $row) {
                $total = count($row['students']);

                $by_school[] = [
                    'name'     => $row['name'],
                    'total'    => $total,
                    'in_japan' => count($row['in_japan_students']),
                    'returned' => count($row['returned_students']),
                    'ratio'    => $den ? round(($total * 100) / $den, 2) . '%' : '0%',
                ];
            }

            usort($by_school, function ($a, $b) {
                if ((int)$a['total'] === (int)$b['total']) {
                    return strcmp($a['name'], $b['name']);
                }
                return ((int)$b['total'] <=> (int)$a['total']);
            });
        }

        /*$by_major = [];
        if ($has_major) {
            $den = max(1, (int) $kpis['total_students']);
            $sqlMajor = "
              SELECT
                {$major_col} AS name,
                COUNT(DISTINCT {$studentKey}) AS total,
                COUNT(DISTINCT CASE WHEN ({$expr_in_japan})=1 THEN {$studentKey} END) AS in_japan,
                COUNT(DISTINCT CASE WHEN ({$expr_returned})=1 THEN {$studentKey} END) AS returned
              FROM {$apps}
              {$whereSql}
              GROUP BY {$major_col}
              ORDER BY total DESC
              LIMIT 80
            ";
            $rows = $this->db->query($sqlMajor, $params)->result_array();
            foreach ($rows as $r) {
                $total = (int) ($r['total'] ?? 0);
                $by_major[] = [
                    'name'     => (string) ($r['name'] ?? '—'),
                    'total'    => $total,
                    'in_japan' => (int) ($r['in_japan'] ?? 0),
                    'returned' => (int) ($r['returned'] ?? 0),
                    'ratio'    => $den ? round(($total * 100) / $den, 2) . '%' : '0%',
                ];
            }
        }*/
        
        $by_major = [];

        if ($major_col !== '') {
            $sqlMajor = "
              SELECT
                {$school_col} AS school_raw,
                {$major_col} AS major_raw,
                {$studentKey} AS student_key,
                CASE WHEN ({$expr_in_japan})=1 THEN 1 ELSE 0 END AS is_in_japan,
                CASE WHEN ({$expr_returned})=1 THEN 1 ELSE 0 END AS is_returned
              FROM {$apps}
              {$whereSql}
            ";

            $rowsMajor = $this->db->query($sqlMajor, $params)->result_array();

            $mergedMajor = [];

            foreach ($rowsMajor as $r) {
                //$school = $this->canonical_school_name($r['school_raw'] ?? '');
                //$major  = $this->canonical_major_by_school($school, $r['major_raw'] ?? '');
                $school = $this->canonical_school_by_major(
                    $r['school_raw'] ?? '',
                    $r['major_raw'] ?? ''
                );
                $school = $this->canonical_school_name($school);
                $major  = $this->canonical_major_by_school($school, $r['major_raw'] ?? '');
                $studentKeyVal = trim((string)($r['student_key'] ?? ''));

                if ($major === '' || $studentKeyVal === '') {
                    continue;
                }

                if (!isset($mergedMajor[$major])) {
                    $mergedMajor[$major] = [
                        'name' => $major,
                        'students' => [],
                        'in_japan_students' => [],
                        'returned_students' => [],
                    ];
                }

                $mergedMajor[$major]['students'][$studentKeyVal] = true;

                if ((int)($r['is_in_japan'] ?? 0) === 1) {
                    $mergedMajor[$major]['in_japan_students'][$studentKeyVal] = true;
                }

                if ((int)($r['is_returned'] ?? 0) === 1) {
                    $mergedMajor[$major]['returned_students'][$studentKeyVal] = true;
                }
            }

            foreach ($mergedMajor as $row) {
                $total = count($row['students']);
                $by_major[] = [
                    'name'     => $row['name'],
                    'total'    => $total,
                    'in_japan' => count($row['in_japan_students']),
                    'returned' => count($row['returned_students']),
                    'ratio'    => $den ? round(($total * 100) / $den, 2) . '%' : '0%',
                ];
            }

            usort($by_major, function ($a, $b) {
                if ((int)$a['total'] === (int)$b['total']) {
                    return strcmp($a['name'], $b['name']);
                }
                return ((int)$b['total'] <=> (int)$a['total']);
            });
        }

        /*$by_major_school = [];
        if ($has_school && $has_major) {
            /*$sqlMajorSchool = "
              SELECT
                {$school_col} AS school,
                {$major_col} AS major,
                COUNT(DISTINCT {$studentKey}) AS total,
                COUNT(DISTINCT CASE WHEN ({$expr_in_japan})=1 THEN {$studentKey} END) AS in_japan,
                COUNT(DISTINCT CASE WHEN ({$expr_returned})=1 THEN {$studentKey} END) AS returned
              FROM {$apps}
              " . $this->where_tail($whereSql) . "
              {$school_col} IS NOT NULL AND {$school_col} <> ''
              AND {$major_col} IS NOT NULL AND {$major_col} <> ''
              GROUP BY {$school_col}, {$major_col}
              ORDER BY {$school_col} ASC, total DESC, {$major_col} ASC
            ";*/
            
            /*$sqlMajorSchool = "
              SELECT
                {$school_expr} AS school,
                {$major_col} AS major,
                COUNT(DISTINCT {$studentKey}) AS total,
                COUNT(DISTINCT CASE WHEN ({$expr_in_japan})=1 THEN {$studentKey} END) AS in_japan,
                COUNT(DISTINCT CASE WHEN ({$expr_returned})=1 THEN {$studentKey} END) AS returned
              FROM {$apps}
              " . $this->where_tail($whereSql) . "
              {$school_col} IS NOT NULL AND {$school_col} <> ''
              AND {$major_col} IS NOT NULL AND {$major_col} <> ''
              GROUP BY {$school_expr}, {$major_col}
              ORDER BY {$school_expr} ASC, total DESC, {$major_col} ASC
            ";

            $rowsMajorSchool = $this->db->query($sqlMajorSchool, $params)->result_array();

            $schoolTotals = [];
            foreach ($rowsMajorSchool as $r) {
                $school = (string) ($r['school'] ?? '');
                if ($school === '') {
                    continue;
                }
                $schoolTotals[$school] = (int) ($schoolTotals[$school] ?? 0) + (int) ($r['total'] ?? 0);
            }

            foreach ($rowsMajorSchool as $r) {
                $school = (string) ($r['school'] ?? '');
                $total  = (int) ($r['total'] ?? 0);
                $den    = max(1, (int) ($schoolTotals[$school] ?? 0));

                $by_major_school[] = [
                    'school'    => $school,
                    'major'     => (string) ($r['major'] ?? '—'),
                    'total'     => $total,
                    'in_japan'  => (int) ($r['in_japan'] ?? 0),
                    'returned'  => (int) ($r['returned'] ?? 0),
                    'ratio'     => round(($total * 100) / $den, 2) . '%',
                ];
            }
        }*/
        
        $by_major_school = [];

        if ($school_col !== '' && $major_col !== '') {
            $sqlMajorSchool = "
              SELECT
                {$school_col} AS school_raw,
                {$major_col} AS major_raw,
                {$studentKey} AS student_key,
                CASE WHEN ({$expr_in_japan})=1 THEN 1 ELSE 0 END AS is_in_japan,
                CASE WHEN ({$expr_returned})=1 THEN 1 ELSE 0 END AS is_returned
              FROM {$apps}
              " . $this->where_tail($whereSql) . "
              {$school_col} IS NOT NULL AND {$school_col} <> ''
              AND {$major_col} IS NOT NULL AND {$major_col} <> ''
            ";

            $rowsMajorSchool = $this->db->query($sqlMajorSchool, $params)->result_array();

            $merged = [];

            foreach ($rowsMajorSchool as $r) {
                //$school = $this->canonical_school_name($r['school_raw'] ?? '');
                //$major  = $this->canonical_major_by_school($school, $r['major_raw'] ?? '');
                //
                $school = $this->canonical_school_by_major(
                    $r['school_raw'] ?? '',
                    $r['major_raw'] ?? ''
                );
                $school = $this->canonical_school_name($school);
                $major  = $this->canonical_major_by_school($school, $r['major_raw'] ?? '');
                $studentKeyVal = trim((string)($r['student_key'] ?? ''));

                if ($school === '' || $major === '' || $studentKeyVal === '') {
                    continue;
                }

                $groupKey = $school . '||' . $major;

                if (!isset($merged[$groupKey])) {
                    $merged[$groupKey] = [
                        'school' => $school,
                        'major' => $major,
                        'students' => [],
                        'in_japan_students' => [],
                        'returned_students' => [],
                    ];
                }

                $merged[$groupKey]['students'][$studentKeyVal] = true;

                if ((int)($r['is_in_japan'] ?? 0) === 1) {
                    $merged[$groupKey]['in_japan_students'][$studentKeyVal] = true;
                }

                if ((int)($r['is_returned'] ?? 0) === 1) {
                    $merged[$groupKey]['returned_students'][$studentKeyVal] = true;
                }
            }

            $schoolTotals = [];
            foreach ($merged as $row) {
                $school = $row['school'];
                $schoolTotals[$school] = (int)($schoolTotals[$school] ?? 0) + count($row['students']);
            }

            foreach ($merged as $row) {
                $school = $row['school'];
                $total  = count($row['students']);
                $den    = max(1, (int)($schoolTotals[$school] ?? 0));

                $by_major_school[] = [
                    'school'    => $school,
                    'major'     => $row['major'],
                    'total'     => $total,
                    'in_japan'  => count($row['in_japan_students']),
                    'returned'  => count($row['returned_students']),
                    'ratio'     => round(($total * 100) / $den, 2) . '%',
                ];
            }

            usort($by_major_school, function ($a, $b) {
                if ($a['school'] === $b['school']) {
                    if ((int)$a['total'] === (int)$b['total']) {
                        return strcmp($a['major'], $b['major']);
                    }
                    return ((int)$b['total'] <=> (int)$a['total']);
                }
                return strcmp($a['school'], $b['school']);
            });
        }

        /*$by_status = [];
        if ($has_status) {
            $sqlStatus = "
              SELECT
                status AS name,
                COUNT(*) AS total_apps,
                COUNT(DISTINCT {$studentKey}) AS total_students
              FROM {$apps}
              {$whereSql}
              GROUP BY status
              ORDER BY total_students DESC, total_apps DESC
            ";
            $rows = $this->db->query($sqlStatus, $params)->result_array();
            foreach ($rows as $r) {
                $key = (string) ($r['name'] ?? '');

                $by_status[] = [
                    'key'            => $key,
                    'name'           => $this->translate_status_label($key),
                    'total_apps'     => (int) ($r['total_apps'] ?? 0),
                    'total_students' => (int) ($r['total_students'] ?? 0),
                ];
            }
        }*/
        
        $by_status = [];

        if ($has_status) {
            $sqlStatus = "
              SELECT
                status AS raw_status,
                {$studentKey} AS student_key,
                CASE WHEN ({$expr_in_japan})=1 THEN 1 ELSE 0 END AS is_in_japan,
                CASE WHEN ({$expr_returned})=1 THEN 1 ELSE 0 END AS is_returned
              FROM {$apps}
              {$whereSql}
            ";
        
            $rows = $this->db->query($sqlStatus, $params)->result_array();
        
            $mergedStatus = [];
        
            foreach ($rows as $r) {
                $studentKeyVal = trim((string)($r['student_key'] ?? ''));
                if ($studentKeyVal === '') {
                    continue;
                }
        
                $rawStatus = (string)($r['raw_status'] ?? '');
                $key = function_exists('im_normalize_status')
                    ? im_normalize_status($rawStatus)
                    : strtolower(trim($rawStatus));
        
                /*
                 * Quan trọng:
                 * KPI "Đang ở Nhật" và "Đã về nước" đang tính bằng entry_date / return_date.
                 * Vì vậy biểu đồ trạng thái cũng phải ưu tiên cùng logic này.
                 */
                if ((int)($r['is_returned'] ?? 0) === 1) {
                    $key = 'returned';
                } elseif ((int)($r['is_in_japan'] ?? 0) === 1) {
                    $key = 'in_japan';
                }
        
                if ($key === '' || in_array($key, ['-', '—', '--', '_', 'null', 'undefined'], true)) {
                    $key = 'not_updated';
                }
        
                if (!isset($mergedStatus[$key])) {
                    $mergedStatus[$key] = [
                        'key'      => $key,
                        'name'     => $this->translate_status_label($key),
                        'apps'     => 0,
                        'students' => [],
                    ];
                }
        
                $mergedStatus[$key]['apps']++;
                $mergedStatus[$key]['students'][$studentKeyVal] = true;
            }
        
            $statusOrder = [
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
            ];
        
            foreach ($statusOrder as $st) {
                if (!isset($mergedStatus[$st])) {
                    continue;
                }
        
                $by_status[] = [
                    'key'            => $st,
                    'name'           => $mergedStatus[$st]['name'],
                    'total_apps'     => (int)$mergedStatus[$st]['apps'],
                    'total_students' => count($mergedStatus[$st]['students']),
                ];
        
                unset($mergedStatus[$st]);
            }
        
            foreach ($mergedStatus as $st => $row) {
                $by_status[] = [
                    'key'            => $st,
                    'name'           => $row['name'],
                    'total_apps'     => (int)$row['apps'],
                    'total_students' => count($row['students']),
                ];
            }
        }
        

        return [
            'kpis'            => $kpis,
            'by_school'       => $by_school,
            'by_major'        => $by_major,
            'by_major_school' => $by_major_school,
            'by_status'       => $by_status,
        ];
    }
}
