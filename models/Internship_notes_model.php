<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Internship_notes_model
 * Ghi chú xử lý cho hồ sơ sinh viên
 */
class Internship_notes_model extends App_Model
{
    private $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = $this->pick_table(['internship_notes', 'tblinternship_notes']);
    }

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
        return db_prefix() . $candidates[0];
    }

    public function get_by_student($student_id)
    {
        if (!$this->db->table_exists($this->table)) {
            return [];
        }

        return $this->db
            ->where('student_id', (int)$student_id)
            ->order_by('id', 'DESC')
            ->get($this->table)
            ->result_array();
    }

    public function add($data)
    {
        if (!$this->db->table_exists($this->table)) {
            return false;
        }

        $insert = [
            'student_id'  => (int)($data['student_id'] ?? 0),
            'staff_id'    => (int)($data['staff_id'] ?? get_staff_user_id()),
            'note_type'   => $data['note_type'] ?? 'normal',
            'content'     => $data['content'] ?? '',
            'reminder_at' => !empty($data['reminder_at']) ? $data['reminder_at'] : null,
            'file'        => $data['file'] ?? null,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        // Hỗ trợ cột datecreated nếu DB cũ
        if ($this->db->field_exists('datecreated', $this->table) && !$this->db->field_exists('created_at', $this->table)) {
            unset($insert['created_at']);
            $insert['datecreated'] = date('Y-m-d H:i:s');
        }

        $this->db->insert($this->table, $insert);
        $id = (int)$this->db->insert_id();
        return $id > 0 ? $id : false;
    }
}
