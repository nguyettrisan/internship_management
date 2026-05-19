<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Internship_applications_model extends App_Model
{
    // Không hardcode prefix để tránh lệch môi trường; luôn dùng db_prefix()
    protected $table = 'internship_applications';

    public function __construct()
    {
        parent::__construct();
    }
    
    /*private function canonical_school_name($name)
    {
        $name = trim((string)$name);
        $name = preg_replace('/\s+/', ' ', $name);
    
        if ($name === '' || $name === '__new__') {
            return $name;
        }
    
        $upper = strtoupper(str_replace(['—', '–'], '-', $name));
        $upper = preg_replace('/\s+/', ' ', $upper);
    
        if ($upper === 'VLSC' || $upper === 'VLSG') {
            return 'VLSG';
        }
    
        return $name;
    }*/
    
    private function canonical_school_name($name)
    {
        $name = trim((string)$name);
        $name = preg_replace('/\s+/', ' ', $name);
    
        if ($name === '' || $name === '__new__') {
            return $name;
        }
    
        $upper = strtoupper(str_replace(['—', '–'], '-', $name));
        $upper = preg_replace('/\s+/', ' ', $upper);
    
        // Bỏ dấu cách, dấu gạch, dấu _ để bắt các biến thể:
        // HUTECH-VJIT, HUTECH - VJIT, HUTECH VJIT, VJIT-HUTECH...
        $compact = preg_replace('/[^A-Z0-9]/', '', $upper);
    
        // VLSC và VLSG là cùng một trường, thống nhất là VLSG
        if ($compact === 'VLSC' || $compact === 'VLSG') {
            return 'VLSG';
        }
    
        // HUTECH-VJIT và VJIT là cùng một trường/nhóm, thống nhất là VJIT
        if ($compact === 'HUTECHVJIT' || $compact === 'VJITHUTECH' || $compact === 'VJIT') {
            return 'VJIT';
        }
    
        return $name;
    }
    
    private function canonicalize_school_row($row)
    {
        if (!is_array($row)) {
            return $row;
        }
    
        foreach (['school_name', 'school_name_vi', 'school', 'university'] as $field) {
            if (isset($row[$field])) {
                $row[$field] = $this->canonical_school_name($row[$field]);
            }
        }
    
        return $row;
    }

    /* ============================================================
       GET ALL
    ============================================================ */
 public function get_all($filters = [])
{
    $this->db->select("
        a.*,
        j.job_title AS job_name,
        j.company_name_vi,
        j.major_vi AS job_major_vi
    ");
    $this->db->from(db_prefix()."internship_applications AS a");
    $this->db->join(db_prefix()."internship_job_orders AS j", "j.id = a.job_order_id", "left");

    // SEARCH
    if (!empty($filters['search'])) {
        $this->db->group_start();
        $this->db->like('a.full_name', $filters['search']);
        $this->db->or_like('a.school_name', $filters['search']);
        $this->db->or_like('j.company_name_vi', $filters['search']);
        $this->db->group_end();
    }

    // NGÀNH
    if (!empty($filters['major'])) {
        $this->db->like('a.major', $filters['major']);
    }

     
    // TRƯỜNG
    /*if (!empty($filters['school'])) {
        $filterSchool = $this->canonical_school_name($filters['school']);
    
        $this->db->group_start();
        $this->db->like('a.school_name', $filterSchool);
    
        if ($filterSchool === 'VLSG') {
            $this->db->or_like('a.school_name', 'VLSC');
        }
    
        $this->db->group_end();
    }*/
    // TRƯỜNG
    if (!empty($filters['school'])) {
        $filterSchool = $this->canonical_school_name($filters['school']);
    
        $this->db->group_start();
        $this->db->like('a.school_name', $filterSchool);
    
        if ($filterSchool === 'VLSG') {
            $this->db->or_like('a.school_name', 'VLSC');
        }
    
        if ($filterSchool === 'VJIT') {
            $this->db->or_like('a.school_name', 'HUTECH-VJIT');
            $this->db->or_like('a.school_name', 'HUTECH - VJIT');
            $this->db->or_like('a.school_name', 'HUTECH VJIT');
            $this->db->or_like('a.school_name', 'VJIT-HUTECH');
            $this->db->or_like('a.school_name', 'VJIT - HUTECH');
            $this->db->or_like('a.school_name', 'VJIT HUTECH');
        }
    
        $this->db->group_end();
    }
    // TRẠNG THÁI
    // Bộ lọc này là bộ lọc gộp:
    // - Đạt/Rớt lọc theo interview_result
    // - Các trạng thái hồ sơ lọc theo dossier_progress
    // - status chỉ là cột legacy, không dùng làm nguồn chính nếu đã có dossier_progress/interview_result
    if (isset($filters['status']) && $filters['status'] !== '') {
        $this->load->helper('internship_management/internship_status');
    
        $target = im_application_filter_target($filters['status']);
        $fields = $this->db->list_fields(db_prefix() . $this->table);
    
        $has_interview = in_array('interview_result', $fields, true);
        $has_dossier   = in_array('dossier_progress', $fields, true);
        $has_status    = in_array('status', $fields, true);
    
        $values = $target['values'] ?? [];
    
        $normal_values = array_values(array_filter($values, function($v) {
            return $v !== null && $v !== '';
        }));
    
        $has_empty = in_array('', $values, true);
        $has_null  = in_array(null, $values, true);
    
        if (($target['type'] ?? '') === 'interview_result') {
            if ($has_interview) {
                $this->db->where_in('a.interview_result', $normal_values);
            } elseif ($has_status) {
                // Fallback cho DB cũ chưa có interview_result
                $this->db->where_in('a.status', $normal_values);
            }
        } else {
            if ($has_dossier) {
                $this->db->group_start();
    
                if (!empty($normal_values)) {
                    $this->db->where_in('a.dossier_progress', $normal_values);
                }
    
                if ($has_empty) {
                    if (!empty($normal_values)) {
                        $this->db->or_where('a.dossier_progress', '');
                    } else {
                        $this->db->where('a.dossier_progress', '');
                    }
                }
    
                if ($has_null) {
                    if (!empty($normal_values) || $has_empty) {
                        $this->db->or_where('a.dossier_progress IS NULL', null, false);
                    } else {
                        $this->db->where('a.dossier_progress IS NULL', null, false);
                    }
                }
    
                $this->db->group_end();
            } elseif ($has_status) {
                // Fallback cho DB cũ chưa có dossier_progress
                $this->db->where_in('a.status', $normal_values);
            }
        }
    }

    /*$this->db->order_by('a.id', 'DESC');

    return $this->db->get()->result_array();*/
    
    $this->db->order_by('a.id', 'DESC');

    $rows = $this->db->get()->result_array();
    
    foreach ($rows as &$row) {
        $row = $this->canonicalize_school_row($row);
    }
    unset($row);
    
    return $rows;
}
    /* ============================================================
       GET BY ID
    ============================================================ */
    public function get($id)
    {
        /*return $this->db
            ->select("
                a.*,
                j.company_name_vi,
                j.company_name_jp,
                j.job_title,
                j.major_vi,
                j.major_jp,
                j.id AS job_id
            ")*/
            
        /*return $this->db
            ->select("
                a.*,
                j.company_name_vi,
                j.company_name_jp,
                j.job_title,
                j.major_vi,
                j.major_jp,
                j.address_vi,
                j.address_jp,
                j.id AS job_id
            ")
            ->from(db_prefix().$this->table . ' AS a')
            ->join(db_prefix().'internship_job_orders AS j', 'j.id = a.job_order_id', 'left')
            ->where('a.id', $id)
            ->get()->row_array();*/
            
        $row = $this->db
            ->select("
                a.*,
                j.company_name_vi,
                j.company_name_jp,
                j.job_title,
                j.major_vi,
                j.major_jp,
                j.address_vi,
                j.address_jp,
                j.id AS job_id
            ")
            ->from(db_prefix().$this->table . ' AS a')
            ->join(db_prefix().'internship_job_orders AS j', 'j.id = a.job_order_id', 'left')
            ->where('a.id', $id)
            ->get()
            ->row_array();
        
        if (!empty($row)) {
            $row = $this->canonicalize_school_row($row);
        }
        
        return $row;
    }

    /* ============================================================
       ADD NEW APPLICATION
    ============================================================ */
    public function add($data)
    {
        $this->load->model('internship_management/Internship_model', 'wf');
        
        $schoolName = $this->canonical_school_name($data['school_name'] ?? '');

        $insert = [
            'full_name'        => $data['full_name'] ?? null,
            'birthday'         => !empty($data['birthday']) ? to_sql_date($data['birthday']) : null,
            'email'            => $data['email'] ?? null,
            'phone_student'    => $data['phone_student'] ?? null,
            'phone_parent'     => $data['phone_parent'] ?? null,
            'address'          => $data['address'] ?? null,
            //'school_name'      => $data['school_name'] ?? null,
            'school_name'      => ($schoolName !== '' ? $schoolName : null),
            'major'            => $data['major'] ?? null,
            'japanese_level'   => $data['japanese_level'] ?? null,
            'english_level'    => $data['english_level'] ?? null,
            'gender'            => $data['gender'] ?? null,

            // JOB ORDER
            'job_order_id'        => $data['job_order_id'] ?? null,
            'receiver_company'    => $data['receiver_company'] ?? null,
            'receiver_prefecture' => $data['receiver_prefecture'] ?? null,
            'receiver_address'    => $data['receiver_address'] ?? null,

            'interview_date'      => !empty($data['interview_date']) ? to_sql_date($data['interview_date']) : null,
            'entry_date'          => !empty($data['entry_date']) ? to_sql_date($data['entry_date']) : null,
            'months'              => $data['months'] ?? null,
            'return_date'         => !empty($data['return_date']) ? to_sql_date($data['return_date']) : null,

            // FILES
            'cv_file'         => $data['cv_file'] ?? null,
            'cv_file_type'    => $data['cv_file_type'] ?? null,
            'avatar'          => $data['avatar'] ?? null,
            'avatar_type'     => $data['avatar_type'] ?? null,

            // RAW AI JSON
            'ai_json'         => $data['ai_json'] ?? null,

           'status' => $this->wf->normalize_status(Internship_model::WF_APPLICATION, $data['status'] ?? 'applied'),
            'note'            => $data['note'] ?? null,
            'apply_date'      => date('Y-m-d'),
            'datecreated' => date('Y-m-d H:i:s'),
            'dateupdated' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert(db_prefix().$this->table, $insert);
        $id = (int)$this->db->insert_id();

        // Đồng bộ trạng thái job order theo tiến độ ứng viên (tăng tiến độ thôi)
        $jobId = (int)($insert['job_order_id'] ?? 0);
        if ($jobId > 0) {
            $this->wf->sync_job_order_status_from_applications($jobId);
        }

        return $id;
    }

    /* ============================================================
       UPDATE APPLICATION
    ============================================================ */
    public function update($id, $data)
    {
        $this->load->model('internship_management/Internship_model', 'wf');

        // Lấy job_order_id cũ để sync lại nếu có thay đổi
        $oldRow = $this->db->select('job_order_id')
            ->from(db_prefix().$this->table)
            ->where('id', (int)$id)
            ->get()->row_array();
        $oldJobId = (int)($oldRow['job_order_id'] ?? 0);
        
        $oldFullRow = $this->db->from(db_prefix().$this->table)
            ->where('id', (int)$id)
            ->get()->row_array();
        
        $schoolName = $this->canonical_school_name($data['school_name'] ?? '');
        
        $update = [
            'full_name'        => $data['full_name'] ?? null,
            'birthday'         => !empty($data['birthday']) ? to_sql_date($data['birthday']) : null,
            'email'            => $data['email'] ?? null,
            'phone_student'    => $data['phone_student'] ?? null,
            'phone_parent'     => $data['phone_parent'] ?? null,
            'address'          => $data['address'] ?? null,
            //'school_name'      => $data['school_name'] ?? null,
            'school_name'      => ($schoolName !== '' ? $schoolName : null),
            'major'            => $data['major'] ?? null,
            'japanese_level'   => $data['japanese_level'] ?? null,
            'english_level'    => $data['english_level'] ?? null,
            'gender'            => $data['gender'] ?? null,

            /*'job_order_id'        => $data['job_order_id'] ?? null,
            'receiver_company'    => $data['receiver_company'] ?? null,
            'receiver_prefecture' => $data['receiver_prefecture'] ?? null,
            'receiver_address'    => $data['receiver_address'] ?? null,*/
            'job_order_id'        => !empty($data['job_order_id']) ? (int)$data['job_order_id'] : (int)($oldFullRow['job_order_id'] ?? 0),
            'receiver_company'    => isset($data['receiver_company']) ? $data['receiver_company'] : ($oldFullRow['receiver_company'] ?? null),
            'receiver_prefecture' => isset($data['receiver_prefecture']) ? $data['receiver_prefecture'] : ($oldFullRow['receiver_prefecture'] ?? null),
            'receiver_address'    => isset($data['receiver_address']) ? $data['receiver_address'] : ($oldFullRow['receiver_address'] ?? null),

            'interview_date'      => !empty($data['interview_date']) ? to_sql_date($data['interview_date']) : null,
            'entry_date'          => !empty($data['entry_date']) ? to_sql_date($data['entry_date']) : null,
            'months'              => $data['months'] ?? null,
            'return_date'         => !empty($data['return_date']) ? to_sql_date($data['return_date']) : null,

            // Luôn lưu canonical code để thống nhất trạng thái
            'status'          => $this->wf->normalize_status(Internship_model::WF_APPLICATION, $data['status'] ?? 'docs_preparing'),
            'note'            => $data['note'] ?? null,

            'dateupdated' => date('Y-m-d H:i:s'),
        ];

        if (!empty($data['cv_file'])) {
            $update['cv_file'] = $data['cv_file'];
            $update['cv_file_type'] = $data['cv_file_type'] ?? null;
        }

        if (!empty($data['avatar'])) {
            $update['avatar'] = $data['avatar'];
            $update['avatar_type'] = $data['avatar_type'] ?? null;
        }

        if (!empty($data['ai_json'])) {
            $update['ai_json'] = $data['ai_json'];
        }

        $this->db->where('id', $id)->update(db_prefix().$this->table, $update);

        // Đồng bộ job order status: sync cả job cũ và job mới (nếu đổi đơn tuyển)
        $newJobId = (int)($update['job_order_id'] ?? 0);
        if ($newJobId <= 0) {
            $row = $this->db->select('job_order_id')
                ->from(db_prefix().$this->table)
                ->where('id', (int)$id)
                ->get()->row_array();
            $newJobId = (int)($row['job_order_id'] ?? 0);
        }
        if ($oldJobId > 0) {
            $this->wf->sync_job_order_status_from_applications($oldJobId);
        }
        if ($newJobId > 0 && $newJobId !== $oldJobId) {
            $this->wf->sync_job_order_status_from_applications($newJobId);
        }

        return true;
    }

    /* ============================================================
       DELETE
    ============================================================ */
    public function delete($id)
    {
        $this->load->model('internship_management/Internship_model', 'wf');

        // lấy job_order_id trước khi xoá để sync lại
        $row = $this->db->select('job_order_id')
            ->from(db_prefix().$this->table)
            ->where('id', (int)$id)
            ->get()->row_array();

        $ok = (bool)$this->db->where('id', (int)$id)->delete(db_prefix().$this->table);
        if ($ok) {
            $jobId = (int)($row['job_order_id'] ?? 0);
            if ($jobId > 0) {
                $this->wf->sync_job_order_status_from_applications($jobId);
            }
        }
        return $ok;
    }

    /* ============================================================
       EXTRACT IMAGE FROM DOCX
    ============================================================ */
    public function extract_image_from_docx($file_path)
    {
        $zip = new ZipArchive;
        if ($zip->open($file_path) === true) {

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);

                if (preg_match('/word\/media\/image/i', $name)) {

                    $imgData = $zip->getFromIndex($i);

                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg','jpeg','png'])) {
                        $ext = 'jpg';
                    }

                    $newName = uniqid('avatar_') . '.' . $ext;
                    $path = FCPATH . 'uploads/internship_avatar/' . $newName;
                    file_put_contents($path, $imgData);

                    $zip->close();

                    return [
                        'file_name' => $newName,
                        'file_type' => $ext
                    ];
                }
            }

            $zip->close();
        }

        return false;
    }
    public function get_by_job_order($job_order_id)
{
    return $this->db
        ->select("
            a.*,
            j.company_name_vi,
            j.company_name_jp,
            j.job_title
        ")
        ->from(db_prefix().'internship_applications AS a')
        ->join(db_prefix().'internship_job_orders AS j', 'j.id = a.job_order_id', 'left')
        ->where('a.job_order_id', $job_order_id)
        ->order_by('a.id', 'DESC')
        ->get()->result_array();
}
/* ============================================================
   STATUS LIST – DÙNG CHO EDIT & CREATE FORM
============================================================ */
public function get_status_list()
{
    $this->load->helper('internship_management/internship_status');
    return im_application_filter_status_list();
}
public function create_client_for_student($student)
{
    $data = [
        'company'   => $student['full_name'],
        'firstname' => $student['first_name'],
        'lastname'  => $student['last_name'],
        'email'     => $student['email'],
        'phonenumber' => $student['phone'],
        'address'   => $student['address'],
        'city'      => $student['province'],
        'country'   => 245, // Việt Nam
        'default_language' => 'vietnamese'
    ];

    $this->load->model('clients_model');
    $client_id = $this->clients_model->add($data);

    // Lưu link vào ứng viên
    $this->db->where('id', (int)($student['application_id'] ?? 0));
    $this->db->update(db_prefix().'internship_applications', [
        'client_id' => $client_id
    ]);

    return $client_id;
}
public function get_student($student_id)
{
    return $this->db->where('id', (int)$student_id)
                    ->get(db_prefix().'internship_students')
                    ->row_array();
}

    /**
     * Lấy dữ liệu để tạo "Ứng tuyển mới" từ một ứng tuyển cũ (clone)
     * Không copy job_order_id để bắt buộc chọn đơn tuyển mới.
     */
    /*public function get_clone_data($id)
    {
        $row = $this->db->where('id', (int)$id)->get(db_prefix().$this->table)->row_array();
        if (!$row) {
            return null;
        }
        // Bỏ các trường không nên copy
        unset($row['id'], $row['job_order_id'], $row['status'], $row['note'], $row['apply_date'], $row['datecreated'], $row['dateupdated']);
        return $row;
    }*/
    
    public function get_clone_data($id)
    {
        $row = $this->db->where('id', (int)$id)->get(db_prefix().$this->table)->row_array();
        if (!$row) {
            return null;
        }
    
        $row = $this->canonicalize_school_row($row);
    
        // Bỏ các trường không nên copy
        unset($row['id'], $row['job_order_id'], $row['status'], $row['note'], $row['apply_date'], $row['datecreated'], $row['dateupdated']);
    
        return $row;
    }

    /**
     * Cập nhật trạng thái nhanh (inline) và sync job order.
     */
    public function update_status_only($id, $status)
    {
        $this->load->model('internship_management/Internship_model', 'wf');

        $row = $this->db->select('job_order_id')
            ->from(db_prefix().$this->table)
            ->where('id', (int)$id)
            ->get()->row_array();
        if (!$row) {
            return false;
        }
        $jobId = (int)($row['job_order_id'] ?? 0);

        $canon = $this->wf->normalize_status(Internship_model::WF_APPLICATION, (string)$status);
        $this->db->where('id', (int)$id)->update(db_prefix().$this->table, [
            'status'      => $canon,
            'dateupdated' => date('Y-m-d H:i:s'),
        ]);

        if ($jobId > 0) {
            $this->wf->sync_job_order_status_from_applications($jobId);
        }
        return true;
    }

    /**
     * Public wrapper để controller có thể ghi log (tránh gọi private add_student_log)
     */
    public function write_student_log(array $data): bool
    {
        return $this->add_student_log($data);
    }
    
    /*public function get_school_options()
    {
        $defaults = ['HUTECH', 'UEF', 'VHU', 'VJIT', 'VLSG', 'SONADEZI'];
    
        $schools = [];
    
        $table = db_prefix() . $this->table;*/
    public function get_school_options()
    {
        $defaults = ['HUTECH', 'UEF', 'VHU', 'VJIT', 'VLSG', 'SONADEZI'];
    
        $schools = [];
    
        // Lấy danh mục trường đối tác trước để đồng bộ với mục Gửi cho trường ở Đơn tuyển
        $partnerTbl = db_prefix() . 'internship_partner_schools';
        if ($this->db->table_exists($partnerTbl)) {
            $fields = $this->db->list_fields($partnerTbl);
    
            $this->db->select('school_name')->from($partnerTbl);
    
            if (in_array('is_active', $fields, true)) {
                $this->db->where('is_active', 1);
            }
    
            $partnerRows = $this->db->get()->result_array();
    
            /*foreach ($partnerRows as $row) {
                $name = trim((string)($row['school_name'] ?? ''));
                if ($name !== '' && $name !== '__new__') {
                    $schools[] = $name;
                }
            }*/
            foreach ($partnerRows as $row) {
                $name = $this->canonical_school_name($row['school_name'] ?? '');
            
                if ($name !== '' && $name !== '__new__') {
                    $schools[] = $name;
                }
            }
        }
    
        $table = db_prefix() . $this->table;
        if ($this->db->table_exists($table)) {
            $rows = $this->db->select('school_name')
                ->from($table)
                ->where('school_name IS NOT NULL', null, false)
                ->where('TRIM(school_name) !=', '')
                ->get()
                ->result_array();
    
            /*foreach ($rows as $row) {
                $name = trim((string)($row['school_name'] ?? ''));
                if ($name === '' || $name === '__new__') {
                    continue;
                }
                $schools[] = $name;
            }*/
            foreach ($rows as $row) {
                $name = $this->canonical_school_name($row['school_name'] ?? '');
            
                if ($name === '' || $name === '__new__') {
                    continue;
                }
            
                $schools[] = $name;
            }
        }
    
        /*$schools = array_merge($defaults, $schools);
        $schools = array_map('trim', $schools);*/
        $schools = array_merge($defaults, $schools);

        foreach ($schools as $idx => $schoolName) {
            $schools[$idx] = $this->canonical_school_name($schoolName);
        }
        
        $schools = array_map('trim', $schools);
        $schools = array_filter($schools, function ($v) {
            return $v !== '' && $v !== '__new__';
        });
        $schools = array_values(array_unique($schools));
    
        natcasesort($schools);
    
        return array_values($schools);
    }

}