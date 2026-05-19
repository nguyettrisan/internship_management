<?php defined('BASEPATH') or exit('No direct script access allowed');

class Im_audit_log_model extends App_Model
{
    private $table = 'tblim_audit_logs';

    public function add($rel_type, $rel_id, $action, $message = '', $old = null, $new = null)
    {
        $staff_id = function_exists('get_staff_user_id') ? (int)get_staff_user_id() : null;

        $data = [
            'rel_type'   => (string)$rel_type,
            'rel_id'     => (int)$rel_id,
            'action'     => (string)$action,
            'message'    => $message,
            'old_data'   => $old !== null ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
            'new_data'   => $new !== null ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
            'staff_id'   => $staff_id ?: null,
            'ip'         => $this->input->ip_address(),
            'user_agent' => substr((string)$this->input->user_agent(), 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->table, $data);
        if ($this->db->affected_rows() <= 0) {
            log_message('error', 'IM_AUDIT_INSERT_FAIL: '.json_encode($this->db->error()));
            log_message('error', 'IM_AUDIT_LAST_QUERY: '.$this->db->last_query());
        }
        return $this->db->insert_id();
    }

    public function get_by_rel($rel_type, $rel_id, $limit = 200)
    {
        $this->db->select("l.*, CONCAT(s.firstname,' ',s.lastname) as staff_name", false);
        $this->db->from($this->table . ' l');
        $this->db->join(db_prefix().'staff s', 's.staffid = l.staff_id', 'left');
        $this->db->where('l.rel_type', $rel_type);
        $this->db->where('l.rel_id', (int)$rel_id);
        $this->db->order_by('l.id', 'DESC');
        $this->db->limit((int)$limit);
        return $this->db->get()->result_array();
    }
    public function get_all($filters = [], $limit = 50, $offset = 0)
{
    $this->db->select("l.*, CONCAT(s.firstname,' ',s.lastname) as staff_name", false);
    $this->db->from($this->table.' l');
    $this->db->join(db_prefix().'staff s', 's.staffid = l.staff_id', 'left');

    if (!empty($filters['q'])) {
        $q = $filters['q'];
        $this->db->group_start();
        $this->db->like('l.message', $q);
        $this->db->or_like('l.action', $q);
        $this->db->or_like('l.rel_type', $q);
        $this->db->group_end();
    }
    if (!empty($filters['action'])) {
        $this->db->where('l.action', $filters['action']);
    }
    if (!empty($filters['rel_type'])) {
        $this->db->where('l.rel_type', $filters['rel_type']);
    }
    
    if (!empty($filters['rel_id'])) {
        $this->db->where('l.rel_id', (int)$filters['rel_id']);
    }
if (!empty($filters['staff_id'])) {
        $this->db->where('l.staff_id', (int)$filters['staff_id']);
    }

    $this->db->order_by('l.id', 'DESC');
    $this->db->limit((int)$limit, (int)$offset);
    return $this->db->get()->result_array();
}

public function count_all($filters = [])
{
    $this->db->from($this->table.' l');

    if (!empty($filters['q'])) {
        $q = $filters['q'];
        $this->db->group_start();
        $this->db->like('l.message', $q);
        $this->db->or_like('l.action', $q);
        $this->db->or_like('l.rel_type', $q);
        $this->db->group_end();
    }
    if (!empty($filters['action'])) {
        $this->db->where('l.action', $filters['action']);
    }
    if (!empty($filters['rel_type'])) {
        $this->db->where('l.rel_type', $filters['rel_type']);
    }
    if (!empty($filters['staff_id'])) {
        $this->db->where('l.staff_id', (int)$filters['staff_id']);
    }

    return (int)$this->db->count_all_results();
}
}