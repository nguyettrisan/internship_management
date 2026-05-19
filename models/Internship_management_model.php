<?php defined('BASEPATH') or exit('No direct script access allowed');

class Internship_management_model extends App_Model
{
    private $tbl_app;
    private $tbl_students;
    private $tbl_job_orders;
    private $tbl_files;
    private $tbl_contacts;
    private $tbl_crm_links;

    public function __construct()
    {
        parent::__construct();

        // Debug check:
        // /admin/internship_management/manage?imver=1
        if (isset($_GET['imdbg'])) {
            header('Content-Type: text/plain; charset=utf-8');
            echo "IM_DEBUG\n";
            echo "FILE=".__FILE__."\n";
            echo "TIME=".date('Y-m-d H:i:s')."\n";
            echo "DB_PREFIX=".db_prefix()."\n";
            echo "APP=".$this->tbl_app."\n";
            echo "STU=".$this->tbl_students."\n";
            echo "JOB=".$this->tbl_job_orders."\n";
            echo "FILES=".$this->tbl_files."\n";
            exit;
        }

        if (isset($_GET['imver'])) {
            header('Content-Type: text/plain; charset=utf-8');
            echo "IM_MODEL\n";
            echo "FILE=".__FILE__."\n";
            echo "TIME=".date('Y-m-d H:i:s')."\n";
            echo "DB_PREFIX=".db_prefix()."\n";
            exit;
        }

        // Your DB screenshot confirms these tables exist:
        $this->tbl_app       = $this->pick_table([db_prefix().'internship_applications','internship_applications','tblinternship_applications']);
        $this->tbl_students  = $this->pick_table([db_prefix().'internship_students','internship_students','tblinternship_students']);
        $this->tbl_job_orders= $this->pick_table([db_prefix().'internship_job_orders','internship_job_orders','tblinternship_job_orders']);
        $this->tbl_files   = $this->pick_table([db_prefix().'internship_files','internship_files','tblinternship_files']);
        $this->tbl_contacts = $this->pick_table([db_prefix().'contacts','tblcontacts','contacts']);
        $this->tbl_crm_links= $this->pick_table([db_prefix().'internship_crm_links','internship_crm_links','tblinternship_crm_links']);
        $this->load->helper('internship_management/internship_status');
    }

    /* ===================== Public ===================== */

    public function get_manage_statuses()
    {
        return [
            'processing' => 'Đang làm hồ sơ',
            'in_japan'   => 'Đang Nhật',
            'returned'   => 'Đã về nước',
            'cancelled'  => 'Đã hủy',
        ];
    }

    public function get_years_available()
    {
        if (!$this->tbl_app) return [date('Y')];

        $date_col = $this->detect_date_col($this->tbl_app);
        if (!$date_col) return [date('Y')];

        $sql = "SELECT DISTINCT YEAR(a.`{$date_col}`) AS y\n"
             . "FROM `{$this->tbl_app}` a\n"
             . "WHERE a.`{$date_col}` IS NOT NULL\n"
             . "ORDER BY y DESC";
        $rows = $this->db->query($sql)->result_array();

        // Fallback avatar from files table if students.avatar is empty
        $this->fill_missing_avatars($rows);

        $years = [];
        foreach ($rows as $r) if (!empty($r['y'])) $years[] = (int)$r['y'];
        return $years ?: [date('Y')];
    }

    /**
     * Options filters (Trường/Đơn tuyển/Phụ trách)
     * - Pull from JOINed tables to match your real schema.
     */
    public function get_filter_options($year)
    {
        $out = ['universities'=>[], 'companies'=>[], 'staffs'=>[]];
        if (!$this->tbl_app) return $out;

        $year = (int)$year;

        $date_col = $this->detect_date_col($this->tbl_app);
        $whereYear = $date_col ? " AND YEAR(a.`{$date_col}`) = {$year}" : "";

        // FK in applications
        $student_fk  = $this->detect_col($this->tbl_app, ['student_id','candidate_id','internship_student_id']);
        $job_fk      = $this->detect_col($this->tbl_app, ['job_order_id','joborder_id','order_id']);
        $staff_col   = $this->detect_col($this->tbl_app, ['staff_id','assigned_to','pic_staff_id']);

        // ---- Trường (from students) ----
        if ($this->tbl_students && $student_fk) {
            $stu_pk        = $this->detect_col($this->tbl_students, ['id','student_id','candidate_id']);
            $stu_school_vi = $this->detect_col($this->tbl_students, ['school_name_vi','school_vi','school_name','school','university','truong','ten_truong','university_name','school_vietnamese']);

            if ($stu_pk && $stu_school_vi) {
                $sql = "SELECT DISTINCT TRIM(s.`{$stu_school_vi}`) AS v\n"
                     . "FROM `{$this->tbl_app}` a\n"
                     . "LEFT JOIN `{$this->tbl_students}` s ON s.`{$stu_pk}` = a.`{$student_fk}`\n"
                     . "WHERE s.`{$stu_school_vi}` IS NOT NULL {$whereYear}\n"
                     . "  AND TRIM(s.`{$stu_school_vi}`) <> ''\n"
                     . "ORDER BY v ASC";
                foreach ($this->db->query($sql)->result_array() as $r) {
                    if (!empty($r['v'])) $out['universities'][] = $r['v'];
                }
            }
        }

        // ---- Đơn tuyển (from job_orders) ----
        if ($this->tbl_job_orders && $job_fk) {
            $job_company_vi = $this->detect_col($this->tbl_job_orders, ['company_name_vi','company_name','company','don_tuyen']);
            $job_company_jp = $this->detect_col($this->tbl_job_orders, ['company_name_jp','company_name_ja','company_jp','company_name_japanese']);
            $job_title_vi   = $this->detect_col($this->tbl_job_orders, ['job_title_vi','title_vi','job_title','position','name']);
            $job_title_jp   = $this->detect_col($this->tbl_job_orders, ['job_title','title','job_title_jp','job_title_ja']);

            $parts = [];
            if ($job_company_vi) $parts[] = "NULLIF(TRIM(j.`{$job_company_vi}`), '')";
            if ($job_title_vi)   $parts[] = "NULLIF(TRIM(j.`{$job_title_vi}`), '')";
            $expr_vi = $parts ? "TRIM(CONCAT_WS(' - ', ".implode(',', $parts)."))" : "''";

            $parts_jp = [];
            if ($job_company_jp) $parts_jp[] = "NULLIF(TRIM(j.`{$job_company_jp}`), '')";
            if ($job_title_jp)   $parts_jp[] = "NULLIF(TRIM(j.`{$job_title_jp}`), '')";
            $expr_jp = $parts_jp ? "TRIM(CONCAT_WS(' - ', ".implode(',', $parts_jp)."))" : "''";

            $sql = "SELECT DISTINCT\n"
                 . "  CASE WHEN {$expr_vi} <> '' THEN {$expr_vi} ELSE {$expr_jp} END AS v\n"
                 . "FROM `{$this->tbl_app}` a\n"
                 . "LEFT JOIN `{$this->tbl_job_orders}` j ON j.`id` = a.`{$job_fk}`\n"
                 . "WHERE 1=1 {$whereYear}\n"
                 . "  AND ( {$expr_vi} <> '' OR {$expr_jp} <> '' )\n"
                 . "ORDER BY v ASC";
            foreach ($this->db->query($sql)->result_array() as $r) {
                if (!empty($r['v'])) $out['companies'][] = $r['v'];
            }
        }

        // ---- Staffs ----
        if ($staff_col) {
            $sql = "SELECT DISTINCT a.`{$staff_col}` AS sid\n"
                 . "FROM `{$this->tbl_app}` a\n"
                 . "WHERE a.`{$staff_col}` IS NOT NULL {$whereYear}";
            $rows = $this->db->query($sql)->result_array();

        // Fallback avatar from files table if students.avatar is empty
        $this->fill_missing_avatars($rows);
            $ids = array_values(array_unique(array_filter(array_map('intval', array_column($rows, 'sid')))));
            if ($ids) {
                $in = implode(',', array_map('intval', $ids));
                $sql2 = "SELECT staffid, firstname, lastname\n"
                      . "FROM `".db_prefix()."staff`\n"
                      . "WHERE staffid IN ({$in})\n"
                      . "ORDER BY firstname ASC, lastname ASC";
                foreach ($this->db->query($sql2)->result_array() as $s) {
                    $out['staffs'][] = ['id'=>(int)$s['staffid'], 'name'=>trim($s['firstname'].' '.$s['lastname'])];
                }
            }
        }

        $out['universities'] = array_values(array_unique($out['universities']));
        $out['companies']    = array_values(array_unique($out['companies']));
        return $out;
    }

    /**
     * Main list for manage page
     * - Join students/job_orders to fetch full info.
     * - Alias fields to keys used by views/manage.php:
     *   full_name, email, phone, parent_phone, university, company_name, entry_date, return_date, avatar, student_id, manage_status
     */
    public function get_managed_students($year, $filters = [])
    {
        if (!$this->tbl_app) return [];

        $year = (int)$year;
        $filters = is_array($filters) ? $filters : [];

        $date_col = $this->detect_date_col($this->tbl_app);

        // FK in applications
        $student_fk  = $this->detect_col($this->tbl_app, ['student_id','candidate_id','internship_student_id']);
        $job_fk      = $this->detect_col($this->tbl_app, ['job_order_id','joborder_id','order_id']);

        // manage/status/interview columns in applications
        $manage_col    = $this->detect_col($this->tbl_app, ['manage_status']);
        $status_col    = $this->detect_col($this->tbl_app, ['status','application_status','dossier_progress']);
        $interview_col = $this->detect_col($this->tbl_app, ['interview_result','pv_result','interview']);

        // student columns (from internship_students)
        $stu_pk = ($this->tbl_students && $student_fk) ? $this->detect_col($this->tbl_students, ['id','student_id','candidate_id']) : null;
        $stu_name = $this->tbl_students ? $this->detect_col($this->tbl_students, ['full_name','fullname','name','student_name','ho_va_ten','ho_ten','ten','ten_day_du','candidate_name']) : null;
        $stu_email = $this->tbl_students ? $this->detect_col($this->tbl_students, ['email','mail']) : null;
        $stu_phone = $this->tbl_students ? $this->detect_col($this->tbl_students, ['phone_student','phone_sv','phone','mobile','sdt','mobilephone','telephone','so_dien_thoai']) : null;
        $stu_parent_phone = $this->tbl_students ? $this->detect_col($this->tbl_students, ['phone_parent','parent_phone','phone_ph','sdt_phu_huynh','sdt_ph','parent_mobile','guardian_phone']) : null;
        $stu_school_vi = $this->tbl_students ? $this->detect_col($this->tbl_students, ['school_name_vi','school_vi','school_name','school','university','truong','ten_truong','university_name','school_vietnamese']) : null;
        $stu_school_ja = $this->tbl_students ? $this->detect_col($this->tbl_students, ['school_name_ja','school_name_jp','school_jp','school_name_japanese','university_ja','university_jp']) : null;
        $stu_avatar = $this->tbl_students ? $this->detect_col($this->tbl_students, ['avatar','profile_image','image','photo','profile_photo','student_avatar','picture']) : null;
        $stu_entry = $this->tbl_students ? $this->detect_col($this->tbl_students, ['entry_date','arrival_date','date_entry','ngay_nhap_canh','nhap_canh','arrival']) : null;
        $stu_return = $this->tbl_students ? $this->detect_col($this->tbl_students, ['return_date','departure_date','date_return','ngay_ve_nuoc','ve_nuoc','xuat_canh','departure']) : null;

        // application columns fallback (from internship_applications)
        $app_name   = $this->detect_col($this->tbl_app, ['full_name','fullname','name','student_name','ho_va_ten','ho_ten']);
        $app_email  = $this->detect_col($this->tbl_app, ['email','mail']);
        $app_phone  = $this->detect_col($this->tbl_app, ['phone_student','student_phone','phone','mobile','sdt','phone_sv']);
        $app_parent_phone = $this->detect_col($this->tbl_app, ['phone_parent','parent_phone','sdt_phu_huynh','phone_ph']);
        $app_school_vi = $this->detect_col($this->tbl_app, ['school_name_vi','school_name','university','school','truong','ten_truong']);
        $app_school_ja = $this->detect_col($this->tbl_app, ['school_name_ja','school_name_jp','school_jp','university_ja','university_jp']);
        $app_avatar = $this->detect_col($this->tbl_app, ['avatar','avatar_ai_file','photo','image']);
        $app_entry  = $this->detect_col($this->tbl_app, ['arrival_date','entry_date','date_entry','ngay_nhap_canh','nhap_canh','arrival']);
        $app_return = $this->detect_col($this->tbl_app, ['departure_date','return_date','date_return','ngay_ve_nuoc','ve_nuoc','xuat_canh','departure']);
        $app_contact_id = $this->detect_col($this->tbl_app, ['contact_id','crm_contact_id','client_contact_id']);

        // job columns (from internship_job_orders)
        $job_company_vi = $this->tbl_job_orders ? $this->detect_col($this->tbl_job_orders, ['company_name_vi','company_name','company','don_tuyen']) : null;
        $job_company_jp = $this->tbl_job_orders ? $this->detect_col($this->tbl_job_orders, ['company_name_jp','company_name_ja','company_jp','company_name_japanese']) : null;
        $job_title_vi   = $this->tbl_job_orders ? $this->detect_col($this->tbl_job_orders, ['job_title_vi','title_vi']) : null;
        $job_title_jp   = $this->tbl_job_orders ? $this->detect_col($this->tbl_job_orders, ['job_title','title','job_title_jp','job_title_ja']) : null;
        $job_entry      = $this->tbl_job_orders ? $this->detect_col($this->tbl_job_orders, ['entry_date','entry_date_vi','date_entry']) : null;
        $job_return     = $this->tbl_job_orders ? $this->detect_col($this->tbl_job_orders, ['return_date','return_date_vi','departure_date','date_return']) : null;

        $whereYear = $date_col ? " AND YEAR(a.`{$date_col}`) = {$year}" : "";

        $select = ["a.`id` AS application_id"]; 
        if ($student_fk) $select[] = "a.`{$student_fk}` AS student_id";

        $where = " WHERE 1=1 {$whereYear} ";

        // Filter by application_id (for quick view)
        if (!empty($filters['application_id'])) {
            $where .= " AND a.`id` = " . (int)$filters['application_id'] . " ";
        }

        // Allow bypass year filter
        if (!empty($filters['ignore_year'])) {
            $whereYear = "";
            $where = " WHERE 1=1 ";
            if (!empty($filters['application_id'])) {
                $where .= " AND a.`id` = " . (int)$filters['application_id'] . " ";
            }
        }

        // PV đạt filter (nếu muốn show ALL thì bạn comment 1 khối này)
        if ($interview_col) {
            $where .= " AND (a.`{$interview_col}` = 'pass' OR a.`{$interview_col}` = 'PV đạt' OR a.`{$interview_col}` = 'Đạt') ";
        }

        $join = "";
        
        // Join students table if available (optional)
        $hasStudentJoin = ($this->tbl_students && $student_fk && $stu_pk);
        if ($hasStudentJoin) {
            $join .= " LEFT JOIN `{$this->tbl_students}` s ON s.`{$stu_pk}` = a.`{$student_fk}` ";
        }

        // Build COALESCE expressions (prefer students table, fallback to application fields)
        $exprStuName   = ($hasStudentJoin && $stu_name) ? "NULLIF(TRIM(s.`{$stu_name}`),'')" : "NULL";
        $exprAppName   = ($app_name) ? "NULLIF(TRIM(a.`{$app_name}`),'')" : "NULL";
        $select[] = "COALESCE({$exprStuName}, {$exprAppName}, '') AS full_name";

        $exprStuEmail  = ($hasStudentJoin && $stu_email) ? "NULLIF(TRIM(s.`{$stu_email}`),'')" : "NULL";
        $exprAppEmail  = ($app_email) ? "NULLIF(TRIM(a.`{$app_email}`),'')" : "NULL";
        $select[] = "COALESCE({$exprStuEmail}, {$exprAppEmail}, '') AS email";

        $exprStuPhone  = ($hasStudentJoin && $stu_phone) ? "NULLIF(TRIM(s.`{$stu_phone}`),'')" : "NULL";
        $exprAppPhone  = ($app_phone) ? "NULLIF(TRIM(a.`{$app_phone}`),'')" : "NULL";
        $select[] = "COALESCE({$exprStuPhone}, {$exprAppPhone}, '') AS phone";

        $exprStuPPhone = ($hasStudentJoin && $stu_parent_phone) ? "NULLIF(TRIM(s.`{$stu_parent_phone}`),'')" : "NULL";
        $exprAppPPhone = ($app_parent_phone) ? "NULLIF(TRIM(a.`{$app_parent_phone}`),'')" : "NULL";
        $select[] = "COALESCE({$exprStuPPhone}, {$exprAppPPhone}, '') AS parent_phone";

        // University bilingual (VI / JA)
        $exprStuVi = ($hasStudentJoin && $stu_school_vi) ? "NULLIF(TRIM(s.`{$stu_school_vi}`),'')" : "NULL";
        $exprAppVi = ($app_school_vi) ? "NULLIF(TRIM(a.`{$app_school_vi}`),'')" : "NULL";
        $exprStuJa = ($hasStudentJoin && $stu_school_ja) ? "NULLIF(TRIM(s.`{$stu_school_ja}`),'')" : "NULL";
        $exprAppJa = ($app_school_ja) ? "NULLIF(TRIM(a.`{$app_school_ja}`),'')" : "NULL";
        $vi = "COALESCE({$exprStuVi}, {$exprAppVi}, NULL)";
        $ja = "COALESCE({$exprStuJa}, {$exprAppJa}, NULL)";
        $select[] = "TRIM(CONCAT_WS(' / ', {$vi}, {$ja})) AS university";

        // Avatar + dates (prefer students, fallback application)
        $exprStuAv = ($hasStudentJoin && $stu_avatar) ? "NULLIF(TRIM(s.`{$stu_avatar}`),'')" : "NULL";
        $exprAppAv = ($app_avatar) ? "NULLIF(TRIM(a.`{$app_avatar}`),'')" : "NULL";
        $select[] = "COALESCE({$exprStuAv}, {$exprAppAv}, '') AS avatar";
        $exprStuEntry = ($hasStudentJoin && $stu_entry) ? "NULLIF(NULLIF(s.`{$stu_entry}`, '0000-00-00'), '0000-00-00 00:00:00')" : "NULL";
        $exprAppEntry = ($app_entry) ? "NULLIF(NULLIF(a.`{$app_entry}`, '0000-00-00'), '0000-00-00 00:00:00')" : "NULL";
        $exprJobEntry = "NULL";
        $exprJobReturn = "NULL";

        $exprStuReturn = ($hasStudentJoin && $stu_return) ? "NULLIF(NULLIF(s.`{$stu_return}`, '0000-00-00'), '0000-00-00 00:00:00')" : "NULL";
        $exprAppReturn = ($app_return) ? "NULLIF(NULLIF(a.`{$app_return}`, '0000-00-00'), '0000-00-00 00:00:00')" : "NULL";

        // Contact id (for Perfex client_profile_images)
        if ($app_contact_id) {
            $select[] = "a.`{$app_contact_id}` AS contact_id";
        } else {
            $select[] = "0 AS contact_id";
        }

        $expr_vi = "''";
        $expr_jp = "''";
        if ($this->tbl_job_orders && $job_fk) {
            $join .= " LEFT JOIN `{$this->tbl_job_orders}` j ON j.`id` = a.`{$job_fk}` ";

            $parts = [];
            if ($job_company_vi) $parts[] = "NULLIF(TRIM(j.`{$job_company_vi}`), '')";
            if ($job_title_vi)   $parts[] = "NULLIF(TRIM(j.`{$job_title_vi}`), '')";
            $expr_vi = $parts ? "TRIM(CONCAT_WS(' - ', ".implode(',', $parts)."))" : "''";

            $parts_jp = [];
            if ($job_company_jp) $parts_jp[] = "NULLIF(TRIM(j.`{$job_company_jp}`), '')";
            if ($job_title_jp)   $parts_jp[] = "NULLIF(TRIM(j.`{$job_title_jp}`), '')";
            $expr_jp = $parts_jp ? "TRIM(CONCAT_WS(' - ', ".implode(',', $parts_jp)."))" : "''";

            $select[] = "CASE WHEN {$expr_vi} <> '' THEN {$expr_vi} ELSE {$expr_jp} END AS company_name";

            // fallback entry/return dates from job_orders (if available)
            if ($job_entry) {
                $exprJobEntry = "NULLIF(NULLIF(j.`{$job_entry}`, '0000-00-00'), '0000-00-00 00:00:00')";
            }
            if (isset($job_return) && $job_return) {
                $exprJobReturn = "NULLIF(NULLIF(j.`{$job_return}`, '0000-00-00'), '0000-00-00 00:00:00')";
            }
        } else {
            $select[] = "'' AS company_name";
        }

        // Entry/Return dates (prefer students, then applications, then job_orders)
        $select[] = "COALESCE({$exprStuEntry}, {$exprAppEntry}, {$exprJobEntry}) AS entry_date";
        $select[] = "COALESCE({$exprStuReturn}, {$exprAppReturn}, {$exprJobReturn}) AS return_date";

        // manage_status
        /*if ($manage_col) {
            $select[] = "a.`{$manage_col}` AS manage_status";
        } elseif ($status_col) {
            $select[] = "a.`{$status_col}` AS manage_status";
        } else {
            $select[] = "'processing' AS manage_status";
        }*/
        
        // status gốc của hồ sơ
        if ($status_col) {
            $select[] = "a.`{$status_col}` AS status_raw";
        } else {
            $select[] = "'' AS status_raw";
        }
        
        // manage_status cũ chỉ giữ để fallback, không còn là nguồn chính
        if ($manage_col) {
            $select[] = "a.`{$manage_col}` AS manage_status_raw";
        } else {
            $select[] = "'' AS manage_status_raw";
        }

        // UI filters
        /*if (!empty($filters['filter_status']) && $manage_col) {
            $where .= " AND a.`{$manage_col}` = ".$this->db->escape((string)$filters['filter_status'])." ";
        }*/
        
        /*if (!empty($filters['filter_status'])) {
            $filterStatus = (string)$filters['filter_status'];
        
            if ($status_col) {
                if ($filterStatus === 'processing') {
                    $where .= " AND a.`{$status_col}` NOT IN ('entry', 'in_japan', 'returned', 'cancelled') ";
                } elseif ($filterStatus === 'in_japan') {
                    $where .= " AND a.`{$status_col}` IN ('entry', 'in_japan') ";
                } elseif ($filterStatus === 'returned') {
                    $where .= " AND a.`{$status_col}` = 'returned' ";
                } elseif ($filterStatus === 'cancelled') {
                    $where .= " AND a.`{$status_col}` = 'cancelled' ";
                }
            }
        }*/
        
        if (!empty($filters['filter_status'])) {
            $filterStatus = (string)$filters['filter_status'];
        
            if ($status_col) {
                if ($filterStatus === 'processing') {
                    $where .= " AND a.`{$status_col}` NOT IN (
                        'entry',
                        'in_japan',
                        'returned',
                        'cancelled',
                        'canceled',
                        'stopped',
                        'stop',
                        'dung_ho_so'
                    ) ";
                } elseif ($filterStatus === 'in_japan') {
                    $where .= " AND a.`{$status_col}` IN ('entry', 'in_japan') ";
                } elseif ($filterStatus === 'returned') {
                    $where .= " AND a.`{$status_col}` IN ('returned', 'return', 'returned_vn', 'back_vn') ";
                } elseif ($filterStatus === 'cancelled') {
                    $where .= " AND a.`{$status_col}` IN (
                        'cancelled',
                        'canceled',
                        'stopped',
                        'stop',
                        'dung_ho_so'
                    ) ";
                }
            }
        }

        $kw = trim((string)($filters['keyword'] ?? ''));
        if ($kw !== '') {
            $kwLike = $this->db->escape_like_str($kw);
            $likes = [];
            if ($stu_name) $likes[] = "s.`{$stu_name}` LIKE '%{$kwLike}%'";
            if ($stu_phone) $likes[] = "s.`{$stu_phone}` LIKE '%{$kwLike}%'";
            if ($stu_parent_phone) $likes[] = "s.`{$stu_parent_phone}` LIKE '%{$kwLike}%'";
            if ($stu_email) $likes[] = "s.`{$stu_email}` LIKE '%{$kwLike}%'";
            if ($stu_school_vi) $likes[] = "s.`{$stu_school_vi}` LIKE '%{$kwLike}%'";
            if ($job_company_vi) $likes[] = "j.`{$job_company_vi}` LIKE '%{$kwLike}%'";
            if ($job_company_jp) $likes[] = "j.`{$job_company_jp}` LIKE '%{$kwLike}%'";
            if ($job_title_vi) $likes[] = "j.`{$job_title_vi}` LIKE '%{$kwLike}%'";
            if ($job_title_jp) $likes[] = "j.`{$job_title_jp}` LIKE '%{$kwLike}%'";
            if ($likes) $where .= " AND (".implode(' OR ', $likes).") ";
        }

        if (!empty($filters['filter_school']) && $stu_school_vi) {
            $where .= " AND s.`{$stu_school_vi}` = ".$this->db->escape((string)$filters['filter_school'])." ";
        }

        if (!empty($filters['filter_company'])) {
            $fc = $this->db->escape_like_str((string)$filters['filter_company']);
            $where .= " AND ({$expr_vi} LIKE '%{$fc}%' OR {$expr_jp} LIKE '%{$fc}%') ";
        }

        $staff_col = $this->detect_col($this->tbl_app, ['staff_id','assigned_to','pic_staff_id']);
        if (!empty($filters['filter_staff_id']) && $staff_col) {
            $where .= " AND a.`{$staff_col}` = ".((int)$filters['filter_staff_id'])." ";
        }

        $order = $date_col ? " ORDER BY a.`{$date_col}` DESC " : " ORDER BY a.`id` DESC ";

        $sql = "SELECT ".implode(', ', $select)."\n"
             . "FROM `{$this->tbl_app}` a\n"
             . $join."\n"
             . $where."\n"
             . $order;

        $rows = $this->db->query($sql)->result_array();

        // Fallback avatar from files table if students.avatar is empty
        $this->fill_missing_avatars($rows);

        // normalize to expected 3 buckets
        /*$rawInJapan  = ['in_japan','working_japan','japan','Đang Nhật'];
        $rawReturned = ['returned','back_vn','ve_nuoc','Đã về nước','returned_vn'];

        foreach ($rows as &$r) {
            $raw = $r['progress_raw'] ?? ($r['manage_status'] ?? '');
            $bucket = $this->normalize_progress($raw);
            $r['manage_status'] = $bucket;
            $r['progress_label'] = $this->translate_status($raw);
            $r['bucket_label'] = ($bucket === 'in_japan' ? 'Đang ở Nhật' : ($bucket === 'returned' ? 'Đã về nước' : 'Đang làm hồ sơ'));*/
            
        foreach ($rows as &$r) {
            $raw = $r['status_raw'] ?? '';
        
            if (trim((string)$raw) === '') {
                $raw = $r['progress_raw'] ?? '';
            }
        
            if (trim((string)$raw) === '') {
                $raw = $r['manage_status_raw'] ?? '';
            }
        
            $normalized = function_exists('im_normalize_status')
                ? im_normalize_status($raw)
                : strtolower(trim((string)$raw));
        
            $bucket = function_exists('im_manage_bucket_from_status')
                ? im_manage_bucket_from_status($normalized)
                : $this->normalize_progress($normalized);
        
            $r['status'] = $normalized;
            $r['manage_status'] = $bucket;
            $r['progress_label'] = function_exists('im_status_label_vi')
                ? im_status_label_vi($normalized)
                : $this->translate_status($normalized);
        
            if ($bucket === 'cancelled') {
                $r['bucket_label'] = 'Đã hủy';
            } elseif ($bucket === 'in_japan') {
                $r['bucket_label'] = 'Đang ở Nhật';
            } elseif ($bucket === 'returned') {
                $r['bucket_label'] = 'Đã về nước';
            } else {
                $r['bucket_label'] = 'Đang làm hồ sơ';
            }

            // Ensure keys exist
            $r['university'] = $r['university'] ?? '';
            $r['company_name'] = $r['company_name'] ?? '';
            $r['entry_date'] = $r['entry_date'] ?? null;
            $r['return_date'] = $r['return_date'] ?? null;
        }
        unset($r);

        return $rows;
    }

    
    public function get_kpi($year, $filters = [])
    {
        return $this->get_counters($year, $filters);
    }

    /*public function get_counters($year, $filters = [])
    {
        $rows = $this->get_managed_students($year, $filters);
        $out = ['total'=>count($rows), 'processing'=>0, 'in_japan'=>0, 'returned'=>0];
        foreach ($rows as $r) {
            $ms = $r['manage_status'] ?? 'processing';
            if (isset($out[$ms])) $out[$ms]++;
        }
        return $out;
    }*/
    
    public function get_counters($year, $filters = [])
    {
        $rows = $this->get_managed_students($year, $filters);
    
        $out = [
            'total'      => count($rows),
            'processing' => 0,
            'in_japan'   => 0,
            'returned'   => 0,
            'cancelled'  => 0,
        ];
    
        foreach ($rows as $r) {
            $ms = $r['manage_status'] ?? 'processing';
            if (!isset($out[$ms])) {
                $ms = 'processing';
            }
            $out[$ms]++;
        }
    
        return $out;
    }


    /**
     * Fallback avatar from tblinternship_files (best-effort).
     * Some installs store student avatar as an attachment row instead of a direct column.
     */
    private function fill_missing_avatars(&$rows)
    {
        if (!$rows || !$this->tbl_files) return;

        $rel_id_col   = $this->detect_col($this->tbl_files, ['rel_id','relation_id','student_id']);
        $rel_type_col = $this->detect_col($this->tbl_files, ['rel_type','relation_type','type']);
        $file_col     = $this->detect_col($this->tbl_files, ['file_name','filename','file','name']);
        $id_col       = $this->detect_col($this->tbl_files, ['id','fileid']);
        if (!$rel_id_col || !$file_col) return;

        $ids = [];
        foreach ($rows as $r) {
            $sid = (int)($r['student_id'] ?? 0);
            if ($sid > 0 && empty($r['avatar'])) $ids[] = $sid;
        }
        $ids = array_values(array_unique($ids));
        if (!$ids) return;

        $in = implode(',', array_map('intval',$ids));
        $whereType = "";
        if ($rel_type_col) {
            $types = ['student','students','internship_student','internship_students','candidate','candidates','intern'];
            $typesEsc = implode(',', array_map([$this->db,'escape'],$types));
            $whereType = " AND f.`{$rel_type_col}` IN ({$typesEsc}) ";
        }

        $order = $id_col ? " ORDER BY f.`{$id_col}` DESC " : "";
        $sql = "SELECT f.`{$rel_id_col}` AS rid, f.`{$file_col}` AS fn
                FROM `{$this->tbl_files}` f
                WHERE f.`{$rel_id_col}` IN ({$in}) {$whereType} {$order}";
        $map = [];
        foreach ($this->db->query($sql)->result_array() as $row) {
            $rid = (int)$row['rid'];
            if ($rid && !isset($map[$rid]) && !empty($row['fn'])) $map[$rid] = $row['fn'];
        }
        if (!$map) return;

        foreach ($rows as &$r) {
            $sid = (int)($r['student_id'] ?? 0);
            if ($sid && empty($r['avatar']) && isset($map[$sid])) $r['avatar'] = $map[$sid];
        }
        unset($r);
    }


    

    /* ===================== Progress (from Reports) ===================== */

    /*public function translate_status($status_key)
    {
        $map = [
            'draft' => 'Nháp',
            'in_progress' => 'Đang xử lý',
            'submitted' => 'Đã nộp',
            'pending' => 'Chờ xử lý',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            'completed' => 'Hoàn tất',
            'cancelled' => 'Đã hủy',

            // Internship pipeline (dossier_progress)
            'prepare_documents' => 'Chuẩn bị hồ sơ',
            'waiting_for_interview' => 'Chờ phỏng vấn',
            'waiting_for_visa' => 'Làm hồ sơ visa',
            'visa_approved' => 'Đã có visa',
            'waiting_for_entry' => 'Chờ nhập cảnh',
            'entry' => 'Đã nhập cảnh',
            'in_japan' => 'Đang ở Nhật',
            'returned' => 'Đã về nước',

            // Common aliases
            'preparing' => 'Chuẩn bị hồ sơ',
            'interview' => 'Chờ phỏng vấn',
            'visa' => 'Làm hồ sơ visa',
            'has_visa' => 'Đã có visa',
            'waiting_entry' => 'Chờ nhập cảnh',
            'entered' => 'Đã nhập cảnh',
        ];
        $key = strtolower(trim((string)$status_key));
        return $map[$key] ?? ($status_key ?: 'Chưa cập nhật');
    }*/
    
    public function translate_status($status_key)
    {
        if (function_exists('im_status_label_vi')) {
            return im_status_label_vi($status_key);
        }
    
        return $status_key ?: 'Chưa cập nhật';
    }

    /*private function normalize_progress($raw)
    {
        $raw = strtolower(trim((string)$raw));
        if (in_array($raw, ['entry','in_japan'], true)) return 'in_japan';
        if ($raw === 'returned') return 'returned';
        return 'processing';
    }*/
    
    private function normalize_progress($raw)
    {
        if (function_exists('im_manage_bucket_from_status')) {
            return im_manage_bucket_from_status($raw);
        }
    
        $raw = strtolower(trim((string)$raw));
        if ($raw === 'cancelled') return 'cancelled';
        if (in_array($raw, ['entry', 'in_japan'], true)) return 'in_japan';
        if ($raw === 'returned') return 'returned';
        return 'processing';
    }

/* ===================== Helpers ===================== */

    private function pick_table($candidates)
    {
        foreach ((array)$candidates as $t) {
            $t = trim((string)$t);
            if ($t !== '' && $this->db->table_exists($t)) return $t;
        }
        try {
            $all = $this->db->list_tables();
            foreach ((array)$candidates as $t) {
                $t = trim((string)$t);
                foreach ((array)$all as $a) {
                    if (strcasecmp($a, $t) === 0) return $a;
                }
            }
        } catch (Throwable $e) {}
        return null;
    }

    private function detect_col($table, $candidates)
    {
        if (!$table) return null;
        foreach ((array)$candidates as $c) {
            if ($this->db->field_exists($c, $table)) return $c;
        }
        return null;
    }

    private function detect_date_col($table)
    {
        return $this->detect_col($table, [
            'datecreated','created_at','date_created','applied_at','application_date',
            'dateadded','created_date','apply_date'
        ]);
    }


    /**
     * Quick view row by application_id (AJAX modal)
     */
    public function get_quick_view_row($application_id)
    {
        $application_id = (int)$application_id;
        if (!$this->tbl_app || $application_id <= 0) {
            return null;
        }

        // Reuse managed list logic with safe select
        $year = (int)date('Y');
        $rows = $this->get_managed_students($year, ['application_id' => $application_id, 'ignore_year' => true]);
        if (is_array($rows) && !empty($rows)) {
            return $rows[0];
        }

        // Fallback: direct select minimal
        $this->db->where('id', $application_id);
        return $this->db->get($this->tbl_app)->row_array();
    }

}