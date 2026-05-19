<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Internship_mail_model extends App_Model
{
    private $col_cache = [];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Token catalog for UI (chips + preview demo).
     * Keep token keys in sync with render_tokens().
     */
    public function get_token_catalog()
    {
        $tokens = [
            // student
            ['key' => '{{student.name}}',   'label' => 'Họ tên',        'group' => 'student'],
            ['key' => '{{student.email}}',  'label' => 'Email',         'group' => 'student'],
            ['key' => '{{student.phone}}',  'label' => 'Số điện thoại', 'group' => 'student'],
            ['key' => '{{student.school}}', 'label' => 'Trường',        'group' => 'student'],
            ['key' => '{{student.major}}',  'label' => 'Ngành',         'group' => 'student'],
            ['key' => '{{student.code}}',   'label' => 'Mã SV',         'group' => 'student'],

            // job order
            ['key' => '{{job.id}}',             'label' => 'ID đơn tuyển',   'group' => 'job'],
            ['key' => '{{job.company}}',        'label' => 'Công ty',        'group' => 'job'],
            ['key' => '{{job.major}}',          'label' => 'Ngành đơn',      'group' => 'job'],
            ['key' => '{{job.round}}',          'label' => 'Vòng / đợt',     'group' => 'job'],
            ['key' => '{{job.interview_date}}', 'label' => 'Ngày phỏng vấn', 'group' => 'job'],
            ['key' => '{{job.entry_date}}',     'label' => 'Ngày nhập cảnh', 'group' => 'job'],
            ['key' => '{{job.return_date}}',    'label' => 'Ngày về nước',   'group' => 'job'],

            // system
            ['key' => '{{today}}', 'label' => 'Hôm nay (dd/mm/YYYY)',     'group' => 'system'],
            ['key' => '{{now}}',   'label' => 'Giờ hiện tại (HH:ii:ss)',  'group' => 'system'],
        ];

        $demo = [
            'student' => [
                'name'   => 'Nguyễn Văn A',
                'email'  => 'demo.student@example.com',
                'phone'  => '0900 000 000',
                'school' => 'ĐH Demo',
                'major'  => 'Ngôn ngữ Nhật',
                'code'   => 'SV-DEMO',
            ],
            'job' => [
                'id'             => 27,
                'company'        => 'Công ty TNHH Demo',
                'major'          => 'Ngôn ngữ Nhật',
                'round'          => 'Đợt 1',
                'interview_date' => date('Y-m-d'),
                'entry_date'     => date('Y-m-d', strtotime('+60 days')),
                'return_date'    => date('Y-m-d', strtotime('+365 days')),
            ],
            'today' => date('d/m/Y'),
            'now'   => date('H:i:s'),
        ];

        return ['tokens' => $tokens, 'demo' => $demo];
    }

    /* ============================================================
        Helpers
    ============================================================ */
    private function tbl($name)
    {
        return db_prefix() . $name;
    }

    private function table_exists($table)
    {
        // $table is full name (with prefix) or raw
        return $this->db->table_exists($table);
    }

    private function field_exists_cached($field, $table)
    {
        $key = $table . '::' . $field;
        if (isset($this->col_cache[$key])) {
            return $this->col_cache[$key];
        }
        $ok = $this->db->field_exists($field, $table);
        $this->col_cache[$key] = $ok;
        return $ok;
    }

    private function pick_col($table, $candidates)
    {
        foreach ($candidates as $c) {
            if ($this->field_exists_cached($c, $table)) {
                return $c;
            }
        }
        return null;
    }

    /**
     * Some deployments use `is_active`, some use `active`/`enabled`.
     * Return the first matching column, or null if none.
     */
    private function pick_active_col($table)
    {
        return $this->pick_col($table, ['is_active', 'active', 'enabled', 'status']);
    }

    private function ensure_column($table, $col, $alter_sql)
    {
        // $alter_sql should be like: ADD COLUMN `x` ...
        if (!$this->field_exists_cached($col, $table)) {
            $this->db->query("ALTER TABLE `{$table}` {$alter_sql}");
            $this->col_cache[$table . '::' . $col] = true;
        }
    }

    private function apps_table()
    {
        // Try common names in your system
        $candidates = [
            $this->tbl('internship_applications'),
            'tblinternship_applications',
            $this->tbl('tblinternship_applications'),
        ];
        foreach ($candidates as $t) {
            if ($this->table_exists($t)) return $t;
        }
        return $this->tbl('internship_applications');
    }

    private function jobs_table()
    {
        $candidates = [
            $this->tbl('internship_job_orders'),
            'tblinternship_job_orders',
            $this->tbl('tblinternship_job_orders'),
        ];
        foreach ($candidates as $t) {
            if ($this->table_exists($t)) return $t;
        }
        return $this->tbl('internship_job_orders');
    }

    /* ============================================================
        Templates
    ============================================================ */

    public function templates_table()
    {
        $t = $this->tbl('internship_email_templates');
        if ($this->table_exists($t)) return $t;
        // fallback legacy
        $legacy = $this->tbl('email_templates');
        return $legacy;
    }

    public function ensure_tables()
    {
        // Create tables if missing (safe for enterprise deploy)
        $tpl = $this->tbl('internship_email_templates');
        if (!$this->table_exists($tpl)) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$tpl}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(191) NOT NULL,
                `code` VARCHAR(191) NULL,
                `subject` VARCHAR(255) NULL,
                `content` MEDIUMTEXT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_is_active` (`is_active`),
                KEY `idx_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } else {
            // Legacy installs: table exists but missing some columns (avoid "Unknown column" errors)
            $this->ensure_column($tpl, 'name', "ADD COLUMN `name` VARCHAR(191) NOT NULL DEFAULT ''");
            $this->ensure_column($tpl, 'code', "ADD COLUMN `code` VARCHAR(191) NULL");
            $this->ensure_column($tpl, 'subject', "ADD COLUMN `subject` VARCHAR(255) NULL");
            $this->ensure_column($tpl, 'content', "ADD COLUMN `content` MEDIUMTEXT NULL");
            $this->ensure_column($tpl, 'is_active', "ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1");
            $this->ensure_column($tpl, 'created_at', "ADD COLUMN `created_at` DATETIME NULL");
            $this->ensure_column($tpl, 'updated_at', "ADD COLUMN `updated_at` DATETIME NULL");
        }

        $log = $this->tbl('internship_email_logs');
        if (!$this->table_exists($log)) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `{$log}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `recipient_email` VARCHAR(191) NULL,
                `recipient_name` VARCHAR(191) NULL,
                `student_id` INT(11) NULL,
                `job_order_id` INT(11) NULL,
                `subject` VARCHAR(255) NULL,
                `is_dry_run` TINYINT(1) NOT NULL DEFAULT 0,
                `status` VARCHAR(50) NOT NULL DEFAULT 'sent',
                `error_message` TEXT NULL,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_created_at` (`created_at`),
                KEY `idx_student_id` (`student_id`),
                KEY `idx_job_order_id` (`job_order_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } else {
            $this->ensure_column($log, 'recipient_email', "ADD COLUMN `recipient_email` VARCHAR(191) NULL");
            $this->ensure_column($log, 'recipient_name', "ADD COLUMN `recipient_name` VARCHAR(191) NULL");
            $this->ensure_column($log, 'student_id', "ADD COLUMN `student_id` INT(11) NULL");
            $this->ensure_column($log, 'job_order_id', "ADD COLUMN `job_order_id` INT(11) NULL");
            $this->ensure_column($log, 'subject', "ADD COLUMN `subject` VARCHAR(255) NULL");
            $this->ensure_column($log, 'is_dry_run', "ADD COLUMN `is_dry_run` TINYINT(1) NOT NULL DEFAULT 0");
            $this->ensure_column($log, 'status', "ADD COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'sent'");
            $this->ensure_column($log, 'error_message', "ADD COLUMN `error_message` TEXT NULL");
            $this->ensure_column($log, 'created_at', "ADD COLUMN `created_at` DATETIME NULL");
        }
    }

    /**
     * Generic safe getter (legacy-friendly): ignores WHERE keys that are missing columns.
     * Used by some older controllers.
     */
    public function get($table, $where = [], $order_by = null)
    {
        $this->db->from($table);

        foreach ((array)$where as $k => $v) {
            // allow raw conditions like "col IS NOT NULL" by detecting spaces/operators
            if (is_string($k) && $k !== '' && strpos($k, ' ') === false && strpos($k, '(') === false && strpos($k, '.') === false) {
                if (!$this->field_exists_cached($k, $table)) {
                    continue;
                }
            }
            $this->db->where($k, $v);
        }

        if ($order_by) {
            $this->db->order_by($order_by);
        }
        return $this->db->get()->result_array();
    }

    public function get_templates($filters = [])
    {
        $this->ensure_tables();
        $t = $this->tbl('internship_email_templates');

        // Compat: some DBs use `active` instead of `is_active`
        $active_col = $this->pick_col($t, ['is_active','active','enabled','status']);

        $this->db->from($t);
        if (isset($filters['is_active']) && $active_col) {
            $this->db->where($active_col, (int)$filters['is_active']);
        } elseif ($active_col) {
            // Default behavior: only show active templates
            $this->db->where($active_col, 1);
        }
        $this->db->order_by('id', 'DESC');
        return $this->db->get()->result();
    }

    public function get_template($id)
    {
        $this->ensure_tables();
        $t = $this->tbl('internship_email_templates');
        return $this->db->get_where($t, ['id' => (int)$id])->row();
    }

    public function upsert_template($id, $data)
    {
        $this->ensure_tables();
        $t = $this->tbl('internship_email_templates');

        $active_col = $this->pick_active_col($t);

        $payload = [
            'name'      => trim((string)($data['name'] ?? '')),
            'code'      => trim((string)($data['code'] ?? '')),
            'subject'   => (string)($data['subject'] ?? ''),
            'content'   => (string)($data['content'] ?? ''),
        ];

        if ($active_col) {
            $payload[$active_col] = (int)($data['is_active'] ?? 1);
        }

        if ($payload['name'] === '') {
            return ['ok' => false, 'message' => 'Tên mẫu không được để trống.'];
        }

        $now = date('Y-m-d H:i:s');
        if ($id > 0) {
            $payload['updated_at'] = $now;
            $this->db->where('id', $id)->update($t, $payload);
            return ['ok' => true, 'message' => 'Đã cập nhật mẫu.'];
        }

        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;
        $this->db->insert($t, $payload);
        return ['ok' => true, 'message' => 'Đã thêm mẫu.'];
    }

    public function delete_template($id)
    {
        $this->ensure_tables();
        $t = $this->tbl('internship_email_templates');
        return $this->db->delete($t, ['id' => (int)$id]);
    }

    /* ============================================================
        Job orders / Students (schema-flex)
    ============================================================ */

    private function apps_schema()
    {
        $t = $this->apps_table();

        $student_id = $this->pick_col($t, [
            'student_id', 'internship_student_id', 'candidate_id', 'applicant_id',
            'intern_id', 'hs_id'
        ]);
        // NOTE: job/order FK naming varies across deployments
        $job_id     = $this->pick_col($t, [
            'job_order_id', 'joborder_id', 'don_tuyen_id', 'order_id', 'job_order',
            'job_id', 'internship_job_id', 'internship_job_order_id', 'internship_order_id',
            'recruitment_id'
        ]);

        $name  = $this->pick_col($t, ['full_name', 'candidate_name', 'student_name', 'name', 'fullname', 'ho_ten', 'hoten']);
        $email = $this->pick_col($t, ['email', 'candidate_email', 'student_email', 'email_address', 'mail', 'gmail', 'email_contact']);
        $phone = $this->pick_col($t, ['phone', 'phone_number', 'mobile']);
        $school = $this->pick_col($t, ['school_name', 'school', 'university']);
        $major  = $this->pick_col($t, ['major', 'major_vi', 'major_name']);

        return compact('t', 'student_id', 'job_id', 'name', 'email', 'phone', 'school', 'major');
    }

    /**
     * Resolve a master students table (when the applications/mapping table only stores student_id).
     * Best-effort, tolerant across environments.
     */
    private function students_master_schema()
    {
        $candidates = [
            $this->tbl('internship_students'),
            'tblinternship_students',
            $this->tbl('tblinternship_students'),
            $this->tbl('tbl_internship_students'),
            $this->tbl('internship_candidates'),
        ];

        $t = null;
        foreach ($candidates as $cand) {
            if ($this->table_exists($cand)) { $t = $cand; break; }
        }
        if (!$t) return null;

        $id    = $this->pick_col($t, ['id', 'student_id']);
        $name  = $this->pick_col($t, ['name', 'full_name', 'student_name', 'ten', 'ho_ten', 'hoten']);
        $email = $this->pick_col($t, ['email', 'student_email', 'mail']);

        return [
            't' => $t,
            'id' => $id ?: 'id',
            'name' => $name,
            'email' => $email,
        ];
    }

    /**
     * Non-select2 fallback: load a recent list of students (from applications table)
     * to populate the multi-select options server-side.
     */
    public function list_students_for_select($limit = 300)
    {
        $apps = $this->apps_schema();
        $a = $apps['t'];

        $nameCol  = $apps['name'] ?: 'id';
        $emailCol = $apps['email'];

        $this->db->select('a.id AS id, a.' . $nameCol . ' AS full_name' . ($emailCol ? ', a.' . $emailCol . ' AS email' : ", '' AS email"), false);
        $this->db->from($a . ' AS a');

        if ($emailCol) {
            $this->db->where('a.' . $emailCol . ' IS NOT NULL', null, false);
            $this->db->where('a.' . $emailCol . " <> ''", null, false);
        }

        $this->db->order_by('a.id', 'DESC');
        $this->db->limit((int)$limit);

        return $this->db->get()->result_array();
    }

    private function jobs_schema()
    {
        $t = $this->jobs_table();

        $company = $this->pick_col($t, ['company_name_vi', 'company_name', 'company']);
        $major   = $this->pick_col($t, ['major_vi', 'major', 'major_name']);
        $round   = $this->pick_col($t, ['round_no', 'round', 'recruit_round']);
        $interview_date = $this->pick_col($t, ['interview_date', 'date_interview']);

        return compact('t', 'company', 'major', 'round', 'interview_date');
    }

   public function get_job_orders_for_dropdown($limit = 500)
{
    $jobs = $this->jobs_schema();
    $t = $jobs['t'];

    $labelCols = [];
    if (!empty($jobs['company'])) $labelCols[] = $jobs['company'];
    if (!empty($jobs['major']))   $labelCols[] = $jobs['major'];

    $this->db->select($this->db->escape_identifiers('id'));

    if (!empty($labelCols)) {
        $parts = [];
        foreach ($labelCols as $col) {
            $parts[] = "IFNULL(" . $this->db->escape_identifiers($col) . ", '')";
        }
        $this->db->select('CONCAT_WS(" - ",' . implode(',', $parts) . ') AS label', false);
    } else {
        $this->db->select($this->db->escape_identifiers('id') . ' AS label', false);
    }

    $this->db->from($t);
    $this->db->order_by('id', 'DESC');
    $this->db->limit((int)$limit);

    return $this->db->get()->result();
}

    public function search_students($keyword, $page = 1, $per_page = 20)
    {
        $apps = $this->apps_schema();
        $t = $apps['t'];

        $name = $apps['name'] ?: 'id';
        $email = $apps['email'];

        $this->db->from($t);
        if ($keyword !== '') {
            $this->db->group_start();
            $this->db->like($name, $keyword);
            if ($email) $this->db->or_like($email, $keyword);
            $this->db->group_end();
        }

        $this->db->order_by('id', 'DESC');
        $this->db->limit($per_page, max(0, ($page - 1) * $per_page));

        $rows = $this->db->get()->result_array();

        $items = [];
        foreach ($rows as $r) {
            $id = (int)$r['id'];
            $label = (string)$r[$name];
            if ($email && !empty($r[$email])) {
                $label .= ' - ' . $r[$email];
            }
            $items[] = ['id' => $id, 'text' => $label];
        }

        return ['results' => $items, 'pagination' => ['more' => count($items) >= $per_page]];
    }

    /**
     * Quick list for initial render (fallback when Select2 not ready).
     */
    public function get_students_quick_list($limit = 200)
    {
        $apps = $this->apps_schema();
        $t = $apps['t'];

        $name = $apps['name'] ?: 'id';
        $email = $apps['email'];

        $this->db->select('id');
        $this->db->select($name . ' AS full_name', false);
        if ($email) {
            $this->db->select($email . ' AS email', false);
            $this->db->where("{$email} IS NOT NULL", null, false);
            $this->db->where("{$email} <> ''", null, false);
        } else {
            $this->db->select("'' AS email", false);
        }

        $this->db->from($t);
        $this->db->order_by('id', 'DESC');
        $this->db->limit((int)$limit);
        return $this->db->get()->result_array();
    }

    public function get_students_by_job_order($job_id)
    {
        $apps = $this->apps_schema();
        $t = $apps['t'];

        if (!$apps['job_id']) {
            return [];
        }

        // If applications table doesn't store email/name, try join a master students table.
        $has_name  = !empty($apps['name']);
        $has_email = !empty($apps['email']);
        $student_fk = $apps['student_id'];

        if ((!$has_name || !$has_email) && $student_fk) {
            $master = $this->students_master_schema();
            if ($master && $master['t'] && $master['name'] && $master['email']) {
               // ✅ IMPORTANT: return application id (a.id) so build_context($sid) works correctly
$this->db->select('a.id AS id', false);
$this->db->select('m.' . $master['id'] . ' AS student_master_id', false);
$this->db->select('m.' . $master['name'] . ' AS full_name', false);
$this->db->select('m.' . $master['email'] . ' AS email', false);

$this->db->from($t . ' a');
$this->db->join($master['t'] . ' m', 'm.' . $master['id'] . ' = a.' . $student_fk, 'inner');
$this->db->where('a.' . $apps['job_id'], (int)$job_id);
$this->db->order_by('a.id', 'DESC');
$rows = $this->db->get()->result_array();
            } else {
                $rows = [];
            }
        } else {
    // ✅ ALWAYS use application row id for mailing context (a.id)
    $name  = $apps['name'] ?: 'id';
    $email = $apps['email'];

    $this->db->select('id AS id', false); // ✅ application id
    $this->db->select($name . ' AS full_name', false);

    if ($email) $this->db->select($email . ' AS email', false);
    else $this->db->select("'' AS email", false);

    $this->db->from($t);
    $this->db->where($apps['job_id'], (int)$job_id);
    $this->db->order_by('id', 'DESC');
    $rows = $this->db->get()->result_array();
}

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'id' => (int)($r['id'] ?? 0),
                'name' => (string)($r['full_name'] ?? ''),
                'email' => (string)($r['email'] ?? ''),
            ];
        }
        return $out;
    }

    /* ============================================================
        Token render
    ============================================================ */

    public function build_context($student_id = null, $job_id = null)
    {
        $demo = $this->get_demo_data();

        $ctx = [
            'student' => $demo['student'],
            'job'     => $demo['job'],
            'today'   => date('d/m/Y'),
            'now'     => date('H:i:s'),
        ];

        if ($student_id) {
            $s = $this->get_student_row($student_id);
            if ($s) $ctx['student'] = $s;
        }

        if ($job_id) {
            $j = $this->get_job_row($job_id);
            if ($j) $ctx['job'] = $j;
        }

        return $ctx;
    }

    public function render_tokens($html, $ctx)
    {
        $repl = [
            '{{today}}' => $ctx['today'] ?? '',
            '{{now}}'   => $ctx['now'] ?? '',
        ];

        $student = $ctx['student'] ?? [];
        foreach ($student as $k => $v) {
            $repl['{{student.' . $k . '}}'] = (string)$v;
        }

        $job = $ctx['job'] ?? [];
        foreach ($job as $k => $v) {
            $repl['{{job.' . $k . '}}'] = (string)$v;
        }

        // Replace tokens (case-sensitive)
        return strtr((string)$html, $repl);
    }

    public function get_demo_data()
    {
        return [
            'student' => [
                'id' => 0,
                'name' => 'SV Demo',
                'email' => 'demo@student.com',
                'phone' => '0900000000',
                'school' => 'Đại học Demo',
                'major' => 'Ngành Demo',
            ],
            'job' => [
                'id' => 0,
                'company' => 'Công ty Demo',
                'major' => 'Ngành Tuyển Demo',
                'round' => '1',
                'interview_date' => date('d/m/Y'),
            ],
        ];
    }

  private function get_student_row($id)
{
    $apps = $this->apps_schema();
    $t = $apps['t'];

    $name   = $apps['name'] ?: 'id';
    $email  = $apps['email'];
    $phone  = $apps['phone'];
    $school = $apps['school'];
    $major  = $apps['major'];

    $select = function() use ($name,$email,$phone,$school,$major){
        $this->db->select('id');
        $this->db->select("{$name} AS name", false);
        $this->db->select($email ? "{$email} AS email" : "'' AS email", false);
        $this->db->select($phone ? "{$phone} AS phone" : "'' AS phone", false);
        $this->db->select($school ? "{$school} AS school" : "'' AS school", false);
        $this->db->select($major ? "{$major} AS major" : "'' AS major", false);
    };

    // 1) try by applications.id
    $select();
    $this->db->from($t);
    $this->db->where('id', (int)$id);
    $row = $this->db->get()->row_array();

    // 2) fallback by student_fk (student_id) if exists
    if (!$row && !empty($apps['student_id'])) {
        $select();
        $this->db->from($t);
        $this->db->where($apps['student_id'], (int)$id);
        $row = $this->db->get()->row_array();
    }

    return $row ?: null;
}

    private function get_job_row($id)
    {
        $jobs = $this->jobs_schema();
        $t = $jobs['t'];

        $company = $jobs['company'];
        $major   = $jobs['major'];
        $round   = $jobs['round'];
        $iv      = $jobs['interview_date'];

        $this->db->select('id');
        $this->db->select($company ? "{$company} AS company" : "'' AS company", false);
        $this->db->select($major ? "{$major} AS major" : "'' AS major", false);
        $this->db->select($round ? "{$round} AS round" : "'' AS round", false);
        $this->db->select($iv ? "DATE_FORMAT({$iv}, '%d/%m/%Y') AS interview_date" : "'' AS interview_date", false);
        $this->db->from($t);
        $this->db->where('id', (int)$id);
        $row = $this->db->get()->row_array();
        return $row ?: null;
    }

    /* ============================================================
        Sending + logs
    ============================================================ */

  

    public function get_logs($limit = 200)
    {
        $this->ensure_tables();
        $t = $this->tbl('internship_email_logs');
        $this->db->from($t);
        $this->db->order_by('id', 'DESC');
        $this->db->limit((int)$limit);
        return $this->db->get()->result();
    }

    private function parse_manual_emails($text)
    {
        $text = trim((string)$text);
        if ($text === '') return [];
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $parts = preg_split('/[\n,;]+/', $text);
        $emails = [];
        foreach ($parts as $p) {
            $e = trim($p);
            if ($e === '') continue;
            if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $e;
            }
        }
        return array_values(array_unique($emails));
    }

    /**
     * Send to selected students (+ optional manual emails).
     * @param array $student_ids
     * @param string $subject
     * @param string $html
     * @param bool $dry_run
     * @param string $manual_emails
     */
    public function send_to_students($student_ids, $subject, $html, $dry_run = false, $manual_emails = '')
    {
        $ok = 0;
        $fail = 0;

        foreach ($student_ids as $sid) {
            $ctx = $this->build_context($sid, null);
            $to = $ctx['student']['email'] ?? '';
            $name = $ctx['student']['name'] ?? '';

            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $fail++;
                $this->log_send([
                    'recipient_email' => $to,
                    'recipient_name'  => $name,
                    'student_id'      => $sid,
                    'subject'         => $subject,
                    'is_dry_run'      => $dry_run ? 1 : 0,
                    'status'          => 'invalid_email',
                    'error_message'   => 'Invalid email',
                ]);
                continue;
            }

            $sub = $this->render_tokens((string)$subject, $ctx);
            $body = $this->render_tokens((string)$html, $ctx);

            $sent = true;
            $err = '';

            if (!$dry_run) {
                try {
                    $sent = $this->send_mail($to, $sub, $body);
                } catch (Throwable $e) {
                    $sent = false;
                    $err = $e->getMessage();
                }
            }

            if ($sent) $ok++; else $fail++;

            $this->log_send([
                'recipient_email' => $to,
                'recipient_name'  => $name,
                'student_id'      => $sid,
                'subject'         => $sub,
                'is_dry_run'      => $dry_run ? 1 : 0,
                'status'          => $sent ? 'sent' : 'failed',
                'error_message'   => $err,
            ]);
        }

        // Manual emails (no student context)
        $manuals = $this->parse_manual_emails($manual_emails);
        if (!empty($manuals)) {
            foreach ($manuals as $to) {
                $ctx = $this->build_context(null, null);
                $sub = $this->render_tokens((string)$subject, $ctx);
                $body = $this->render_tokens((string)$html, $ctx);

                $sent = true;
                $err  = '';
                if (!$dry_run) {
                    try {
                        $sent = $this->send_mail($to, $sub, $body);
                    } catch (Throwable $e) {
                        $sent = false;
                        $err  = $e->getMessage();
                    }
                }

                if ($sent) $ok++; else $fail++;

                $this->log_send([
                    'recipient_email' => $to,
                    'recipient_name'  => '',
                    'student_id'      => null,
                    'subject'         => $sub,
                    'is_dry_run'      => $dry_run ? 1 : 0,
                    'status'          => $sent ? 'sent' : 'failed',
                    'error_message'   => $err,
                ]);
            }
        }

        return [
            'ok' => true,
            'message' => ($dry_run ? '[DRY RUN] ' : '') . "Đã xử lý: {$ok} thành công, {$fail} thất bại.",
            'success' => $ok,
            'failed'  => $fail,
        ];
    }

    public function send_by_job_order($job_id, $student_ids, $manual_emails, $subject, $html, $dry_run = false)
    {
        $ok = 0;
        $fail = 0;

        // If user didn't pick, default to all students in job order
        if ($job_id > 0 && empty($student_ids)) {
            $rows = $this->get_students_by_job_order($job_id);
            $student_ids = array_map(fn($r) => (int)$r['id'], $rows);
        }

        // Send to students
        foreach ($student_ids as $sid) {
            $ctx = $this->build_context($sid, $job_id ?: null);
            $to = $ctx['student']['email'] ?? '';
            $name = $ctx['student']['name'] ?? '';

            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $fail++;
                $this->log_send([
                    'recipient_email' => $to,
                    'recipient_name'  => $name,
                    'student_id'      => $sid,
                    'job_order_id'    => $job_id ?: null,
                    'subject'         => $subject,
                    'is_dry_run'      => $dry_run ? 1 : 0,
                    'status'          => 'invalid_email',
                    'error_message'   => 'Invalid email',
                ]);
                continue;
            }

            $sub = $this->render_tokens((string)$subject, $ctx);
            $body = $this->render_tokens((string)$html, $ctx);

            $sent = true;
            $err = '';
            if (!$dry_run) {
                try {
                    $sent = $this->send_mail($to, $sub, $body);
                } catch (Throwable $e) {
                    $sent = false;
                    $err = $e->getMessage();
                }
            }

            if ($sent) $ok++; else $fail++;

            $this->log_send([
                'recipient_email' => $to,
                'recipient_name'  => $name,
                'student_id'      => $sid,
                'job_order_id'    => $job_id ?: null,
                'subject'         => $sub,
                'is_dry_run'      => $dry_run ? 1 : 0,
                'status'          => $sent ? 'sent' : 'failed',
                'error_message'   => $err,
            ]);
        }

        // Manual emails (no student context)
        $emails = $this->parse_manual_emails($manual_emails);
        foreach ($emails as $to) {
            $ctx = $this->build_context(null, $job_id ?: null);
            $sub = $this->render_tokens((string)$subject, $ctx);
            $body = $this->render_tokens((string)$html, $ctx);

            $sent = true;
            $err = '';
            if (!$dry_run) {
                try {
                    $sent = $this->send_mail($to, $sub, $body);
                } catch (Throwable $e) {
                    $sent = false;
                    $err = $e->getMessage();
                }
            }

            if ($sent) $ok++; else $fail++;

            $this->log_send([
                'recipient_email' => $to,
                'recipient_name'  => '',
                'student_id'      => null,
                'job_order_id'    => $job_id ?: null,
                'subject'         => $sub,
                'is_dry_run'      => $dry_run ? 1 : 0,
                'status'          => $sent ? 'sent' : 'failed',
                'error_message'   => $err,
            ]);
        }

        return [
            'ok' => true,
            'message' => ($dry_run ? '[DRY RUN] ' : '') . "Đã xử lý: {$ok} thành công, {$fail} thất bại.",
            'success' => $ok,
            'failed'  => $fail,
        ];
    }

  private function send_mail($to, $subject, $html)
{
    $this->load->library('email');

    // đọc SMTP options (đồng bộ với send_test_email)
  $host   = trim((string)get_option('ifk_smtp_host'));
$port   = (int)get_option('ifk_smtp_port');
$user   = trim((string)get_option('ifk_smtp_user'));
$pass   = (string)get_option('ifk_smtp_pass');
$secure = strtolower(trim((string)get_option('ifk_smtp_secure')));

  $fromName  = get_option('ifk_sender_name') ?: (get_option('companyname') ?: 'IFK');
$fromEmail = get_option('ifk_sender_email') ?: ($user ?: get_option('smtp_email'));

    // Nếu có cấu hình SMTP riêng của module thì dùng
    if ($host !== '' && $port > 0 && $user !== '' && $pass !== '') {

       // Chuẩn hoá secure theo port
// Chuẩn hoá host (xoá ssl:// hoặc tls:// lặp)
$smtpHost  = preg_replace('/^((ssl|tls):\/\/)+/i', '', $host);

$smtpCrypto = '';
// Ép theo port để tránh cấu hình sai làm vỡ
if ($port == 465) {
    $smtpCrypto = 'ssl';
    $smtpHost   = 'ssl://' . $smtpHost;
} elseif ($port == 587) {
    $smtpCrypto = 'tls'; // STARTTLS
} else {
    // port 25 / custom: không prefix, không crypto
    $smtpCrypto = '';
}

        $config = [
            'protocol'    => 'smtp',
            'smtp_host'   => $smtpHost,
            'smtp_port'   => $port,
            'smtp_user'   => $user,
            'smtp_pass'   => $pass,
            'smtp_crypto' => $smtpCrypto,
            'mailtype'    => 'html',
            'charset'     => 'utf-8',
            'newline'     => "\r\n",
            'crlf'        => "\r\n",
            'wordwrap'    => true,
        ];

        $this->email->initialize($config);
    } else {
        // fallback config hệ thống Perfex (nếu module chưa set SMTP)
        $this->email->initialize(['mailtype'=>'html','charset'=>'utf-8','newline'=>"\r\n",'crlf'=>"\r\n"]);
    }

    $this->email->clear(true);
    if ($fromEmail) $this->email->from($fromEmail, $fromName);

    $this->email->to($to);
    $this->email->subject($subject);
    $this->email->message($html);

    $ok = (bool)$this->email->send(false);

    if (!$ok) {
        // ghi log debug để biết vì sao fail
        $debug = strip_tags($this->email->print_debugger(['headers','subject']));
        $this->log_send([
            'recipient_email' => $to,
            'recipient_name'  => '',
            'student_id'      => null,
            'job_order_id'    => null,
            'subject'         => $subject,
            'is_dry_run'      => 0,
            'status'          => 'failed',
            'error_message'   => $debug ?: 'Send failed',
        ]);
    }

    return $ok;
}
    /* ============================================================
    LOG helper
============================================================ */
private function log_send($row = [])
{
    $this->ensure_tables();
    $t = $this->tbl('internship_email_logs');

    $payload = [
        'recipient_email' => isset($row['recipient_email']) ? (string)$row['recipient_email'] : '',
        'recipient_name'  => isset($row['recipient_name'])  ? (string)$row['recipient_name']  : '',
        'student_id'      => isset($row['student_id'])      ? (int)$row['student_id']         : null,
        'job_order_id'    => isset($row['job_order_id'])    ? (int)$row['job_order_id']       : null,
        'subject'         => isset($row['subject'])         ? (string)$row['subject']         : '',
        'is_dry_run'      => !empty($row['is_dry_run']) ? 1 : 0,
        'status'          => isset($row['status']) ? (string)$row['status'] : 'sent',
        'error_message'   => isset($row['error_message']) ? (string)$row['error_message'] : '',
        'created_at'      => date('Y-m-d H:i:s'),
    ];

    $this->db->insert($t, $payload);
}
public function send_test_email($to)
{
    $to = trim((string)$to);
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Email nhận không hợp lệ.'];
    }

  $host   = trim((string)get_option('ifk_smtp_host'));
$port   = (int)get_option('ifk_smtp_port');
$user   = trim((string)get_option('ifk_smtp_user'));
$pass   = (string)get_option('ifk_smtp_pass');

$fromName  = get_option('ifk_sender_name') ?: (get_option('companyname') ?: 'IFK');
$fromEmail = get_option('ifk_sender_email') ?: $user;

    if ($host === '') return ['ok' => false, 'message' => 'SMTP Host đang trống.'];
    if ($port <= 0)   return ['ok' => false, 'message' => 'SMTP Port không hợp lệ.'];
    if ($user === '') return ['ok' => false, 'message' => 'SMTP Username đang trống.'];
    if ($pass === '') return ['ok' => false, 'message' => 'SMTP Password đang trống.'];

    // Auto-correct secure vs port
    if ($port == 587 && $secure === 'ssl') $secure = 'tls';
    if ($port == 465 && $secure === 'tls') $secure = 'ssl';
    if (!in_array($secure, ['tls','ssl','none'], true)) $secure = 'tls';

    $smtpCrypto = '';
    $smtpHost   = $host;

    // Clean prefixes
    $smtpHost = preg_replace('/^(ssl|tls):\/\//i', '', $smtpHost);

    if ($secure === 'ssl') {
        $smtpCrypto = 'ssl';
        $smtpHost   = 'ssl://' . $smtpHost; // implicit SSL
    } elseif ($secure === 'tls') {
        $smtpCrypto = 'tls'; // STARTTLS
    } else {
        $smtpCrypto = ''; // plain
    }

    $this->load->library('email');

    $config = [
        'protocol'    => 'smtp',
        'smtp_host'   => $smtpHost,
        'smtp_port'   => $port,
        'smtp_user'   => $user,
        'smtp_pass'   => $pass,
        'smtp_crypto' => $smtpCrypto, // tls|ssl|''
        'mailtype'    => 'html',
        'charset'     => 'utf-8',
        'newline'     => "\r\n",
        'crlf'        => "\r\n",
        'wordwrap'    => true,
    ];

    $subject = '[TEST SMTP] Internship Module';
    $body    = 'SMTP đang hoạt động bình thường.';

    $this->email->initialize($config);
    $this->email->clear(true);
    $this->email->from($fromEmail, $fromName);
    $this->email->to($to);
    $this->email->subject($subject);
    $this->email->message($body);

    $ok = (bool)$this->email->send(false);
    $debug = '';
    if (!$ok) {
        $debug = strip_tags($this->email->print_debugger(['headers','subject','body']));
    }

    // ✅ GHI LOG TEST (để tab Log thấy)
    $this->log_send([
        'recipient_email' => $to,
        'recipient_name'  => '[TEST SMTP]',
        'student_id'      => null,
        'job_order_id'    => null,
        'subject'         => $subject,
        'is_dry_run'      => 0,
        'status'          => $ok ? 'sent' : 'failed',
        'error_message'   => $ok ? '' : ($debug ?: 'Send failed'),
    ]);

    return $ok
        ? ['ok' => true, 'message' => 'Đã gửi email test thành công.']
        : ['ok' => false, 'message' => 'Gửi thất bại. Kiểm tra cấu hình SMTP/Firewall/SSL.', 'debug' => $debug];
}
private function fields($table)
{
    // $table là tên bảng đầy đủ (có prefix)
    return $this->db->list_fields($table);
}
}
