<?php defined('BASEPATH') or exit('No direct script access allowed');

class Student_contracts_model extends App_Model
{
    protected $table = 'tblinternship_contracts';

    public function __construct()
    {
        parent::__construct();
    }

    /** Lấy hợp đồng theo student_id */
    public function get_by_student($student_id)
    {
        return $this->db
            ->where('student_id', $student_id)
            ->order_by('id', 'DESC')
            ->get($this->table)
            ->result_array();
    }

    /** Tạo hợp đồng */
    public function create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /** Cập nhật hợp đồng */
    public function update($id, $data)
    {
        return $this->db->where('id', $id)
                        ->update($this->table, $data);
    }

    /** Xóa hợp đồng */
    public function delete($id)
    {
        return $this->db->where('id', $id)
                        ->delete($this->table);
    }
}