<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Student_client_model
 * - Wrapper model cho màn hình hồ sơ sinh viên (student_client)
 * - Giữ logic cũ, bổ sung helper chọn đúng tên bảng theo môi trường
 */
class Student_client_model extends App_Model
{
    /** @var string */
    private $tbl_students;
    /** @var string */
    private $tbl_contracts;
    /** @var string */
    private $tbl_files;
    /** @var string */
    private $tbl_logs;
    /** @var string */
    private $tbl_signatures;

    /** @var string */
    private $tbl_apps;

    /** @var string */
    private $tbl_crm_links;

    public function __construct()
    {
        parent::__construct();

        $this->tbl_students   = $this->pick_table(['internship_students', 'tblinternship_students']);
        $this->tbl_contracts  = $this->pick_table(['internship_contracts', 'tblinternship_contracts']);
        $this->tbl_files      = $this->pick_table(['internship_student_files', 'tblinternship_student_files']);
        $this->tbl_logs       = $this->pick_table(['internship_student_logs', 'tblinternship_student_logs']);
        $this->tbl_signatures = $this->pick_table(['internship_signatures', 'tblinternship_signatures']);

        // applications (ứng tuyển)
        $this->tbl_apps = $this->pick_table(['internship_applications', 'tblinternship_applications']);

        // mapping CRM nếu DB chưa có cột crm_client_id
        $this->tbl_crm_links = $this->pick_table(['internship_crm_links', 'tblinternship_crm_links']);

        // invoices đang dùng model riêng
        $this->load->model('internship_management/Internship_invoices_model', 'inv');
        $this->load->model('internship_management/Internship_notes_model', 'notes');
    }

    /**
     * Trả về db_prefix() . table nếu tồn tại, ngược lại thử raw table.
     * @param string[] $candidates
     */
    private function pick_table($candidates)
    {
        foreach ($candidates as $t) {
            $pref = db_prefix() . $t;
            if ($this->db->table_exists($pref)) {
                return $pref;
            }
            if ($this->db->table_exists($t)) {
                return $t;
            }
        }
        // fallback cuối: dùng candidate đầu + prefix
        return db_prefix() . $candidates[0];
    }

    /* ===================== STUDENT ===================== */

    /**
     * Resolve context từ ID truyền vào:
     * - Nếu ID là student_id trong internship_students => trả về student
     * - Nếu không có => coi là application_id trong internship_applications => trả về student-like data
     *
     * @return array{student:array, student_id:int|null, application_id:int|null, crm_client_id:int}
     */
    /*public function resolve_student_context($id)
    {
        $id = (int)$id;

        // 1) thử student table
        $student = null;
        if ($this->db->table_exists($this->tbl_students)) {
            $student = $this->db->where('id', $id)->get($this->tbl_students)->row_array();
        }
        if (!empty($student)) {
            $crm = $this->extract_crm_client_id($student, 'student', $id);
            return [
                'student'        => $student,
                'student_id'     => $id,
                'application_id' => null,
                'crm_client_id'  => $crm,
            ];
        }

        // 2) fallback application table
        $app = null;
        if ($this->db->table_exists($this->tbl_apps)) {
            $app = $this->db->where('id', $id)->get($this->tbl_apps)->row_array();
        }
        if (empty($app)) {
            return [
                'student'        => [],
                'student_id'     => null,
                'application_id' => null,
                'crm_client_id'  => 0,
            ];
        }

        // Chuẩn hoá field để view/controller dùng lại như student
        $studentLike = $app;
        if (!isset($studentLike['phone_student']) && isset($studentLike['phone'])) {
            $studentLike['phone_student'] = $studentLike['phone'];
        }
        if (!isset($studentLike['phone']) && isset($studentLike['phone_student'])) {
            $studentLike['phone'] = $studentLike['phone_student'];
        }

        $crm = $this->extract_crm_client_id($studentLike, 'application', $id);

        return [
            'student'        => $studentLike,
            'student_id'     => null,
            'application_id' => $id,
            'crm_client_id'  => $crm,
        ];
    }*/
    
    
    public function resolve_student_context($id)
    {
        $id = (int)$id;
    
        // 1) Ưu tiên application table trước (tránh nhầm khi student.id trùng application.id)
        $app = null;
        if ($this->db->table_exists($this->tbl_apps)) {
            $app = $this->db->where('id', $id)->get($this->tbl_apps)->row_array();
        }
        if (!empty($app)) {
            // Chuẩn hoá field để view/controller dùng lại như student
            $studentLike = $app;
    
            if (!isset($studentLike['phone_student']) && isset($studentLike['phone'])) {
                $studentLike['phone_student'] = $studentLike['phone'];
            }
            if (!isset($studentLike['phone']) && isset($studentLike['phone_student'])) {
                $studentLike['phone'] = $studentLike['phone_student'];
            }
    
            $crm = $this->extract_crm_client_id($studentLike, 'application', $id);
    
            return [
                'student'        => $studentLike,
                'student_id'     => null,
                'application_id' => $id,
                'crm_client_id'  => $crm,
            ];
        }
    
        // 2) Nếu không có application thì mới thử student table
        $student = null;
        if ($this->db->table_exists($this->tbl_students)) {
            $student = $this->db->where('id', $id)->get($this->tbl_students)->row_array();
        }
        if (!empty($student)) {
            $crm = $this->extract_crm_client_id($student, 'student', $id);
            return [
                'student'        => $student,
                'student_id'     => $id,
                'application_id' => null,
                'crm_client_id'  => $crm,
            ];
        }
    
        // Không tìm thấy ở cả 2 bảng
        return [
            'student'        => [],
            'student_id'     => null,
            'application_id' => null,
            'crm_client_id'  => 0,
        ];
    }

    /**
     * Lấy crm_client_id từ record nếu có, nếu không thì lookup mapping table.
     */
    private function extract_crm_client_id($row, $source, $source_id)
    {
        $crm = 0;
        if (is_array($row) && isset($row['crm_client_id'])) {
            $crm = (int)$row['crm_client_id'];
        }
        if ($crm > 0) {
            return $crm;
        }
        return $this->get_crm_client_from_links($source, (int)$source_id);
    }

    private function ensure_crm_links_table()
    {
        if ($this->db->table_exists($this->tbl_crm_links)) {
            return;
        }
        $tbl = $this->tbl_crm_links;
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `{$tbl}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `source_type` VARCHAR(20) NOT NULL,
                `source_id` INT(11) NOT NULL,
                `crm_client_id` INT(11) NOT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_source` (`source_type`,`source_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        );
    }

    private function get_crm_client_from_links($source_type, $source_id)
    {
        $this->ensure_crm_links_table();
        if (!$this->db->table_exists($this->tbl_crm_links)) {
            return 0;
        }
        $row = $this->db->where('source_type', $source_type)
            ->where('source_id', (int)$source_id)
            ->get($this->tbl_crm_links)
            ->row_array();
        return (int)($row['crm_client_id'] ?? 0);
    }

    private function upsert_crm_link($source_type, $source_id, $crm_client_id)
    {
        $this->ensure_crm_links_table();
        if (!$this->db->table_exists($this->tbl_crm_links)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $exists = $this->db->where('source_type', $source_type)
            ->where('source_id', (int)$source_id)
            ->get($this->tbl_crm_links)
            ->row_array();

        if ($exists) {
            $this->db->where('id', (int)$exists['id'])->update($this->tbl_crm_links, [
                'crm_client_id' => (int)$crm_client_id,
                'updated_at'    => $now,
            ]);
            return $this->db->affected_rows() >= 0;
        }

        $this->db->insert($this->tbl_crm_links, [
            'source_type'   => $source_type,
            'source_id'     => (int)$source_id,
            'crm_client_id' => (int)$crm_client_id,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function get_student($student_id)
    {
        $ctx = $this->resolve_student_context($student_id);
        return $ctx['student'] ?? null;
    }

    public function set_crm_client($student_id, $crm_client_id, $sync_status = 'synced', $error = null)
    {
        $ctx = $this->resolve_student_context($student_id);
        $now = date('Y-m-d H:i:s');

        // ưu tiên update vào bảng nguồn nếu có cột
        if (!empty($ctx['student_id']) && $this->db->table_exists($this->tbl_students) && $this->db->field_exists('crm_client_id', $this->tbl_students)) {
            $data = [
                'crm_client_id' => (int)$crm_client_id,
            ];
            if ($this->db->field_exists('crm_sync_status', $this->tbl_students)) {
                $data['crm_sync_status'] = $sync_status;
            }
            if ($this->db->field_exists('crm_last_synced_at', $this->tbl_students)) {
                $data['crm_last_synced_at'] = $now;
            }
            if ($this->db->field_exists('crm_last_error', $this->tbl_students)) {
                $data['crm_last_error'] = $error;
            }
            $this->db->where('id', (int)$ctx['student_id'])->update($this->tbl_students, $data);
            return $this->db->affected_rows() >= 0;
        }

        if (!empty($ctx['application_id']) && $this->db->table_exists($this->tbl_apps) && $this->db->field_exists('crm_client_id', $this->tbl_apps)) {
            $data = [
                'crm_client_id' => (int)$crm_client_id,
            ];
            if ($this->db->field_exists('crm_sync_status', $this->tbl_apps)) {
                $data['crm_sync_status'] = $sync_status;
            }
            if ($this->db->field_exists('crm_last_synced_at', $this->tbl_apps)) {
                $data['crm_last_synced_at'] = $now;
            }
            if ($this->db->field_exists('crm_last_error', $this->tbl_apps)) {
                $data['crm_last_error'] = $error;
            }
            $this->db->where('id', (int)$ctx['application_id'])->update($this->tbl_apps, $data);
            return $this->db->affected_rows() >= 0;
        }

        // fallback mapping table
        $sourceType = !empty($ctx['student_id']) ? 'student' : 'application';
        $sourceId   = !empty($ctx['student_id']) ? (int)$ctx['student_id'] : (int)($ctx['application_id'] ?? (int)$student_id);
        return $this->upsert_crm_link($sourceType, $sourceId, (int)$crm_client_id);
    }

    public function mark_crm_sync($student_id, $sync_status, $error = null)
    {
        $ctx = $this->resolve_student_context($student_id);
        $crm = (int)($ctx['crm_client_id'] ?? 0);
        return $this->set_crm_client($student_id, $crm, $sync_status, $error);
    }

    /* ===================== CRM (core Perfex) ===================== */

    public function get_crm_invoices($crm_client_id)
    {
        $crm_client_id = (int)$crm_client_id;
        if ($crm_client_id <= 0) {
            return [];
        }
        $tbl = db_prefix() . 'invoices';
        if (!$this->db->table_exists($tbl)) {
            return [];
        }
        return $this->db->select('id, number, prefix, date, duedate, total, status')
            ->where('clientid', $crm_client_id)
            ->order_by('id', 'DESC')
            ->get($tbl)
            ->result_array();
    }

    public function get_crm_contracts($crm_client_id)
    {
        $crm_client_id = (int)$crm_client_id;
        if ($crm_client_id <= 0) {
            return [];
        }
        $tbl = db_prefix() . 'contracts';
        if (!$this->db->table_exists($tbl)) {
            return [];
        }
        return $this->db->select('id, subject, datestart, dateend, contract_value, signed')
            ->where('client', $crm_client_id)
            ->order_by('id', 'DESC')
            ->get($tbl)
            ->result_array();
    }

    /* ===================== INVOICES ===================== */

    public function get_invoices($student_id)
    {
        return $this->inv->get_by_student($student_id);
    }

    public function get_invoice($invoice_id)
    {
        $tbl = $this->pick_table(['internship_invoices', 'tblinternship_invoices']);
        return $this->db->where('id', (int)$invoice_id)->get($tbl)->row_array();
    }

    public function get_invoice_items($invoice_id)
    {
        return $this->inv->get_items($invoice_id);
    }

    public function update_invoice_full($invoice_id, $invoice_data, $items)
    {
        return $this->inv->update_invoice_full($invoice_id, $invoice_data, $items);
    }

    public function update_invoice_status($invoice_id, $status)
    {
        $tbl = $this->pick_table(['internship_invoices', 'tblinternship_invoices']);
        $this->db->where('id', (int)$invoice_id)->update($tbl, ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        return $this->db->affected_rows() >= 0;
    }

    /* ===================== CONTRACTS ===================== */

    public function get_contracts($student_id)
    {
        if (!$this->db->table_exists($this->tbl_contracts)) {
            return [];
        }
        return $this->db->where('student_id', (int)$student_id)
            ->order_by('id', 'DESC')
            ->get($this->tbl_contracts)
            ->result_array();
    }

    public function get_contract($contract_id)
    {
        if (!$this->db->table_exists($this->tbl_contracts)) {
            return null;
        }
        return $this->db->where('id', (int)$contract_id)->get($this->tbl_contracts)->row_array();
    }

    public function create_contract($data)
    {
        if (!$this->db->table_exists($this->tbl_contracts)) {
            return false;
        }
        $this->db->insert($this->tbl_contracts, $data);
        $id = (int)$this->db->insert_id();
        return $id > 0 ? $id : false;
    }

    public function update_contract_status($contract_id, $status)
    {
        if (!$this->db->table_exists($this->tbl_contracts)) {
            return false;
        }
        $data = ['status' => $status];
        if ($this->db->field_exists('dateupdated', $this->tbl_contracts)) {
            $data['dateupdated'] = date('Y-m-d H:i:s');
        }
        $this->db->where('id', (int)$contract_id)->update($this->tbl_contracts, $data);
        return $this->db->affected_rows() >= 0;
    }

    /* ===================== FILES & LOGS ===================== */

    public function get_files($student_id)
    {
        if (!$this->db->table_exists($this->tbl_files)) {
            return [];
        }
        return $this->db->where('student_id', (int)$student_id)
            ->order_by('id', 'DESC')
            ->get($this->tbl_files)
            ->result_array();
    }

    public function get_logs($student_id)
    {
        if (!$this->db->table_exists($this->tbl_logs)) {
            return [];
        }
        $rows = $this->db->where('student_id', (int)$student_id)
            ->order_by('id', 'DESC')
            ->get($this->tbl_logs)
            ->result_array();

        // Enrich staff name if possible (without breaking old schema)
        foreach ($rows as &$r) {
            if (!isset($r['staff_name']) && !empty($r['staff_id'])) {
                $r['staff_name'] = function_exists('get_staff_full_name') ? get_staff_full_name((int)$r['staff_id']) : '';
            }
        }
        unset($r);
        return $rows;
    }

    /**
     * Add activity log for student.
     * - Keeps old schema safe: only inserts columns that exist.
     */
    public function add_log($student_id, $description, $action = '')
    {
        $student_id = (int)$student_id;
        if ($student_id <= 0) {
            return false;
        }
        if (!$this->db->table_exists($this->tbl_logs)) {
            return false;
        }

        $data = [];

        if ($this->db->field_exists('student_id', $this->tbl_logs)) {
            $data['student_id'] = $student_id;
        }
        if ($this->db->field_exists('description', $this->tbl_logs)) {
            $data['description'] = (string)$description;
        }

        if ($action !== '' && $this->db->field_exists('action', $this->tbl_logs)) {
            $data['action'] = (string)$action;
        }
        if ($this->db->field_exists('staff_id', $this->tbl_logs)) {
            $data['staff_id'] = (int)(function_exists('get_staff_user_id') ? get_staff_user_id() : 0);
        }
        if ($this->db->field_exists('ip', $this->tbl_logs)) {
            $data['ip'] = (string)($this->input->ip_address() ?? '');
        }
        $now = date('Y-m-d H:i:s');
        if ($this->db->field_exists('datecreated', $this->tbl_logs)) {
            $data['datecreated'] = $now;
        }
        if ($this->db->field_exists('created_at', $this->tbl_logs)) {
            $data['created_at'] = $now;
        }

        if (empty($data)) {
            return false;
        }

        $this->db->insert($this->tbl_logs, $data);
        return $this->db->affected_rows() > 0;
    }

    /* ===================== SIGNATURES (generic) ===================== */

    public function get_signatures($rel_type, $rel_id)
    {
        if (!$this->db->table_exists($this->tbl_signatures)) {
            // fallback: dùng bảng signatures riêng của invoice model
            if ($rel_type === 'invoice') {
                return $this->inv->get_signatures($rel_id);
            }
            return [];
        }

        return $this->db->where('rel_type', $rel_type)
            ->where('rel_id', (int)$rel_id)
            ->order_by('signed_at', 'ASC')
            ->get($this->tbl_signatures)
            ->result_array();
    }

    public function add_signature($data)
    {
        // dữ liệu đang được controller truyền: rel_type, rel_id, signed_by, signed_at
        if (!$this->db->table_exists($this->tbl_signatures)) {
            // fallback invoice signature
            if (($data['rel_type'] ?? '') === 'invoice') {
                return $this->inv->add_signature((int)($data['rel_id'] ?? 0), (string)($data['signed_by'] ?? ''));
            }
            return false;
        }

        $insert = [
            'rel_type'  => $data['rel_type'] ?? '',
            'rel_id'    => (int)($data['rel_id'] ?? 0),
            'signed_by' => $data['signed_by'] ?? '',
            'signed_at' => $data['signed_at'] ?? date('Y-m-d H:i:s'),
        ];

        if ($this->db->field_exists('staff_id', $this->tbl_signatures)) {
            $insert['staff_id'] = (int)get_staff_user_id();
        }

        $this->db->insert($this->tbl_signatures, $insert);
        return $this->db->affected_rows() > 0;
    }

    /* ===================== NOTES ===================== */

    public function get_notes($student_id)
    {
        return $this->notes->get_by_student($student_id);
    }
}
