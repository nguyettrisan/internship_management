<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Internship_japan_model extends CRM_Model
{
    private $table = 'internship_japan_students';

    public function check_exists($student_id)
    {
        return $this->db->where('student_id', $student_id)
                        ->get($this->table)
                        ->row_array();
    }

    public function add($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update_by_student($student_id, $data)
    {
        return $this->db->where('student_id', $student_id)
                        ->update($this->table, $data);
    }
}