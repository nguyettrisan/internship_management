<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Internship_pro_model extends App_Model
{
    private $t_app;
    private $t_job;
    private $t_link;
    private $t_log;
    private $t_steps;

    public function __construct()
    {
        parent::__construct();
        $this->t_app  = db_prefix() . 'internship_applications';
        $this->t_job  = db_prefix() . 'internship_job_orders';
        $this->t_link = db_prefix() . 'internship_job_order_applicants';
        $this->t_log  = db_prefix() . 'internship_audit_logs';
        $this->t_steps = db_prefix() . 'internship_application_steps';
    }

    // -------------------- Status dictionaries --------------------

    public function get_application_statuses()
    {
        return [
            'applied'   => _l('internship_pro_status_applied'),
            'interview' => _l('internship_pro_status_interview'),
            'passed'    => _l('internship_pro_status_passed'),
            'failed'    => _l('internship_pro_status_failed'),
        ];
    }

    public function get_applicant_statuses()
    {
        // Status per job order
        return [
            'applied'   => _l('internship_pro_status_applied'),
            'screening' => _l('internship_pro_status_screening'),
            'interview' => _l('internship_pro_status_interview'),
            'passed'    => _l('internship_pro_status_passed'),
            'failed'    => _l('internship_pro_status_failed'),
            'withdrawn' => _l('internship_pro_status_withdrawn'),
        ];
    }

    // -------------------- Applications --------------------

    public function get_application($id)
    {
        return $this->db->where('id', $id)->get($this->t_app)->row();
    }

    public function get_applications_for_picker($limit = 500)
    {
        $this->db->select('id, full_name, phone, email');
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);
        return $this->db->get($this->t_app)->result();
    }

    public function create_application($data)
    {
        $payload = $this->sanitize_application_payload($data);
        $payload['created_at'] = date('Y-m-d H:i:s');
        $payload['created_by'] = get_staff_user_id();

        $this->db->insert($this->t_app, $payload);
        $id = $this->db->insert_id();

        if ($id) {
            $this->log('application', $id, 'create', null, $payload);
            // Ensure workflow steps are created for the new application
            $this->ensure_default_steps($id);
        }

        return $id;
    }

    // -------------------- Process / Workflow --------------------

    /**
     * Default workflow template (can be adjusted per your business process).
     */
    public function get_default_steps_template()
    {
        return [
            ['key' => 'profile_received', 'label' => _l('internship_pro_step_profile_received') ?: 'Đã nhận hồ sơ', 'order' => 10],
            ['key' => 'profile_review',   'label' => _l('internship_pro_step_profile_review') ?: 'Đang kiểm tra hồ sơ', 'order' => 20],
            ['key' => 'docs_translation', 'label' => _l('internship_pro_step_docs_translation') ?: 'Dịch thuật hồ sơ', 'order' => 30],
            ['key' => 'docs_submitted',   'label' => _l('internship_pro_step_docs_submitted') ?: 'Đã nộp hồ sơ', 'order' => 40],
            ['key' => 'coe',              'label' => _l('internship_pro_step_coe') ?: 'COE / Tư cách lưu trú', 'order' => 50],
            ['key' => 'visa',             'label' => _l('internship_pro_step_visa') ?: 'Xin visa', 'order' => 60],
            ['key' => 'departure',        'label' => _l('internship_pro_step_departure') ?: 'Xuất cảnh', 'order' => 70],
        ];
    }

    public function get_steps($application_id)
    {
        $application_id = (int)$application_id;
        $this->ensure_default_steps($application_id);

        $this->db->where('application_id', $application_id);
        $this->db->order_by('sort_order', 'ASC');
        return $this->db->get($this->t_steps)->result();
    }

    public function ensure_default_steps($application_id)
    {
        $application_id = (int)$application_id;
        if ($application_id <= 0) return false;

        $template = $this->get_default_steps_template();
        foreach ($template as $t) {
            $exists = $this->db->where('application_id', $application_id)
                ->where('step_key', $t['key'])
                ->get($this->t_steps)->row();
            if ($exists) continue;

            $this->db->insert($this->t_steps, [
                'application_id' => $application_id,
                'step_key'       => $t['key'],
                'step_label'     => $t['label'],
                'status'         => 'pending',
                'sort_order'     => (int)$t['order'],
            ]);
        }
        return true;
    }

    public function update_process_stage($application_id, $stage_key)
    {
        $application_id = (int)$application_id;
        $stage_key = trim((string)$stage_key);
        $old = $this->get_application($application_id);
        if (!$old) return false;

        $payload = [
            'process_stage'      => $stage_key !== '' ? $stage_key : null,
            'process_updated_at' => date('Y-m-d H:i:s'),
            'process_updated_by' => get_staff_user_id(),
        ];

        $this->db->where('id', $application_id)->update($this->t_app, $payload);
        $ok = $this->db->affected_rows() >= 0;
        if ($ok) {
            $this->log('application', $application_id, 'process_stage_change', ['process_stage' => $old->process_stage ?? null], ['process_stage' => $payload['process_stage']]);
        }
        return $ok;
    }

    public function update_step_status($application_id, $step_key, $status)
    {
        $application_id = (int)$application_id;
        $step_key = trim((string)$step_key);
        $status = trim((string)$status);
        if (!in_array($status, ['pending', 'doing', 'done', 'blocked'], true)) {
            return false;
        }

        $this->ensure_default_steps($application_id);
        $old = $this->db->where('application_id', $application_id)->where('step_key', $step_key)->get($this->t_steps)->row();
        if (!$old) return false;

        $payload = [
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => get_staff_user_id(),
        ];

        $this->db->where('application_id', $application_id)->where('step_key', $step_key)->update($this->t_steps, $payload);
        $ok = $this->db->affected_rows() >= 0;
        if ($ok) {
            $this->log('application', $application_id, 'process_step_change', ['step_key' => $step_key, 'status' => $old->status], ['step_key' => $step_key, 'status' => $status]);
        }
        return $ok;
    }

    public function get_process_progress($application_id)
    {
        $steps = $this->get_steps($application_id);
        $total = count($steps);
        if ($total === 0) return ['done' => 0, 'total' => 0, 'percent' => 0];
        $done = 0;
        foreach ($steps as $s) {
            if ($s->status === 'done') $done++;
        }
        $percent = (int) round(($done / $total) * 100);
        return ['done' => $done, 'total' => $total, 'percent' => $percent];
    }

    public function update_application($id, $data)
    {
        $old = $this->get_application($id);
        if (!$old) return false;

        $payload = $this->sanitize_application_payload($data);
        $payload['updated_at'] = date('Y-m-d H:i:s');
        $payload['updated_by'] = get_staff_user_id();

        $this->db->where('id', $id)->update($this->t_app, $payload);
        $ok = $this->db->affected_rows() >= 0;

        if ($ok) {
            $this->log('application', $id, 'update', $old, $payload);
        }

        return $ok;
    }

    public function delete_application($id)
    {
        $old = $this->get_application($id);
        if (!$old) return false;

        $this->db->where('id', $id)->delete($this->t_app);
        $ok = $this->db->affected_rows() > 0;
        if ($ok) {
            $this->log('application', $id, 'delete', $old, null);
        }
        return $ok;
    }

    private function sanitize_application_payload($data)
    {
        return [
            'code'      => isset($data['code']) ? trim($data['code']) : null,
            'full_name' => trim((string)($data['full_name'] ?? '')),
            'phone'     => isset($data['phone']) ? trim($data['phone']) : null,
            'email'     => isset($data['email']) ? trim($data['email']) : null,
            'address'   => isset($data['address']) ? trim($data['address']) : null,
            'gender'    => isset($data['gender']) ? trim($data['gender']) : null,
            'school'    => isset($data['school']) ? trim($data['school']) : null,
            'major'     => isset($data['major']) ? trim($data['major']) : null,
        ];
    }

    // -------------------- Job Orders --------------------

    public function get_job_order($id)
    {
        return $this->db->where('id', $id)->get($this->t_job)->row();
    }

    public function create_job_order($data)
    {
        $payload = $this->sanitize_job_order_payload($data);
        $payload['created_at'] = date('Y-m-d H:i:s');
        $payload['created_by'] = get_staff_user_id();

        $this->db->insert($this->t_job, $payload);
        $id = $this->db->insert_id();
        if ($id) {
            $this->log('job_order', $id, 'create', null, $payload);
        }
        return $id;
    }

    public function update_job_order($id, $data)
    {
        $old = $this->get_job_order($id);
        if (!$old) return false;

        $payload = $this->sanitize_job_order_payload($data);
        $this->db->where('id', $id)->update($this->t_job, $payload);
        $ok = $this->db->affected_rows() >= 0;

        if ($ok) {
            $this->log('job_order', $id, 'update', $old, $payload);
        }

        return $ok;
    }

    private function sanitize_job_order_payload($data)
    {
        return [
            'customer_id'  => isset($data['customer_id']) && $data['customer_id'] !== '' ? (int)$data['customer_id'] : null,
            'company_name' => isset($data['company_name']) ? trim($data['company_name']) : null,
            'title'        => trim((string)($data['title'] ?? '')),
            'industry'     => isset($data['industry']) ? trim($data['industry']) : null,
            'description'  => isset($data['description']) ? $data['description'] : null,
            'status'       => isset($data['status']) ? trim($data['status']) : 'open',
        ];
    }

    // -------------------- Link table: applicants per job order --------------------

    public function get_job_orders_by_application($application_id)
    {
        $this->db->select('l.*, j.title as job_title, j.company_name');
        $this->db->from($this->t_link . ' l');
        $this->db->join($this->t_job . ' j', 'j.id = l.job_order_id', 'left');
        $this->db->where('l.application_id', $application_id);
        $this->db->order_by('l.applied_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_job_order_applicant($id)
    {
        return $this->db->where('id', $id)->get($this->t_link)->row();
    }

    public function attach_application_to_job_order($job_order_id, $application_id, $status = 'applied', $note = '')
    {
        $now = date('Y-m-d H:i:s');
        $payload = [
            'job_order_id'   => (int)$job_order_id,
            'application_id' => (int)$application_id,
            'applied_at'     => $now,
            'status'         => $status ?: 'applied',
            'note'           => $note,
            'last_status_at' => $now,
            'last_status_by' => get_staff_user_id(),
        ];

        // Prevent duplicates
        $exists = $this->db->where('job_order_id', $payload['job_order_id'])
            ->where('application_id', $payload['application_id'])
            ->get($this->t_link)->row();

        if ($exists) {
            return false;
        }

        $this->db->insert($this->t_link, $payload);
        $id = $this->db->insert_id();
        if ($id) {
            $this->log('job_order_applicant', $id, 'attach', null, $payload);
            $this->recalculate_application_status($application_id);
            return true;
        }

        return false;
    }

    public function update_job_order_applicant_status($id, $status, $note = '')
    {
        $row = $this->get_job_order_applicant($id);
        if (!$row) return false;

        $allowed = array_keys($this->get_applicant_statuses());
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $old = clone $row;
        $payload = [
            'status'         => $status,
            'note'           => $note,
            'last_status_at' => date('Y-m-d H:i:s'),
            'last_status_by' => get_staff_user_id(),
        ];

        $this->db->where('id', $id)->update($this->t_link, $payload);
        $ok = $this->db->affected_rows() >= 0;
        if ($ok) {
            $this->log('job_order_applicant', $id, 'status_change', $old, $payload);
            $this->recalculate_application_status($row->application_id);
        }
        return $ok;
    }

    public function delete_job_order_applicant($id)
    {
        $row = $this->get_job_order_applicant($id);
        if (!$row) return false;

        $this->db->where('id', $id)->delete($this->t_link);
        $ok = $this->db->affected_rows() > 0;
        if ($ok) {
            $this->log('job_order_applicant', $id, 'delete', $row, null);
            $this->recalculate_application_status($row->application_id);
        }
        return $ok;
    }

    /**
     * The core rule:
     * Application status is derived from all job-order statuses.
     * - if ANY passed => passed
     * - else if ANY interview => interview
     * - else if ALL failed/withdrawn (and has at least 1) => failed
     * - else => applied
     */
    public function recalculate_application_status($application_id)
    {
        $application_id = (int)$application_id;
        $rows = $this->db->where('application_id', $application_id)->get($this->t_link)->result();

        $hasPassed = false;
        $hasInterview = false;
        $hasAny = count($rows) > 0;
        $allFailed = $hasAny;

        foreach ($rows as $r) {
            if ($r->status === 'passed') {
                $hasPassed = true;
            }
            if ($r->status === 'interview') {
                $hasInterview = true;
            }
            if (!in_array($r->status, ['failed', 'withdrawn'], true)) {
                $allFailed = false;
            }
        }

        if ($hasPassed) {
            $final = 'passed';
        } elseif ($hasInterview) {
            $final = 'interview';
        } elseif ($allFailed && $hasAny) {
            $final = 'failed';
        } else {
            $final = 'applied';
        }

        $old = $this->get_application($application_id);
        if (!$old) return false;

        if ($old->status === $final) {
            return true;
        }

        $payload = [
            'status'     => $final,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => get_staff_user_id(),
        ];

        $this->db->where('id', $application_id)->update($this->t_app, $payload);
        $ok = $this->db->affected_rows() >= 0;
        if ($ok) {
            $this->log('application', $application_id, 'status_sync', ['status' => $old->status], ['status' => $final]);
        }

        return $ok;
    }

    // -------------------- Audit logs --------------------

    public function log($rel_type, $rel_id, $action, $old_value = null, $new_value = null)
    {
        $payload = [
            'rel_type'   => $rel_type,
            'rel_id'     => (int)$rel_id,
            'action'     => $action,
            'old_value'  => $old_value === null ? null : json_encode($old_value, JSON_UNESCAPED_UNICODE),
            'new_value'  => $new_value === null ? null : json_encode($new_value, JSON_UNESCAPED_UNICODE),
            'staff_id'   => get_staff_user_id() ?: null,
            'ip'         => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->t_log, $payload);
        return $this->db->insert_id();
    }

    public function get_audit_logs($rel_type = null, $rel_id = null, $limit = 100)
    {
        if ($rel_type !== null) $this->db->where('rel_type', $rel_type);
        if ($rel_id !== null)   $this->db->where('rel_id', (int)$rel_id);
        $this->db->order_by('id', 'DESC');
        $this->db->limit((int)$limit);
        return $this->db->get($this->t_log)->result();
    }
}

