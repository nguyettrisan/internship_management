<?php defined('BASEPATH') or exit('No direct script access allowed');

class Internship_model extends App_Model
{
    protected $table = 'internship_students';

    private function tbl()
    {
        return db_prefix() . $this->table;
    }

    /** ===============================
     * Lấy toàn bộ học sinh
     * =============================== */
    public function get_all()
    {
        return $this->db->order_by('created_at', 'DESC')->get($this->tbl())->result_array();
    }

    /** Lấy 1 học sinh */
    public function get($id)
    {
        if (!$id) return [];
        return $this->db->where('id', (int)$id)->get($this->tbl())->row_array();
    }

    /** Thêm mới */
    public function add($data)
    {
        $data = $this->sanitize($data);
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->tbl(), $data);
        return $this->db->insert_id();
    }

    /** Cập nhật */
    public function update($id, $data)
    {
        $data = $this->sanitize($data);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', (int)$id)->update($this->tbl(), $data);
        return $this->db->affected_rows();
    }

    /** Xóa */
    public function delete($id)
    {
        $this->db->where('id', (int)$id)->delete($this->tbl());
        return $this->db->affected_rows();
    }

    /** ===============================
     *  Lấy danh sách về nước trong N ngày
     * =============================== */
    public function get_returning_in_days($days = 30)
    {
        $today = date('Y-m-d');
        $to = date('Y-m-d', strtotime("+{$days} days"));
        $this->db->where('return_date >=', $today);
        $this->db->where('return_date <=', $to);
        return $this->db->get($this->tbl())->result_array();
    }

    /** ===============================
     *  Gửi thông báo sắp về nước
     * =============================== */
    public function notify_returning_in_days($days = 30)
    {
        $students = $this->get_returning_in_days($days);
        if (empty($students)) return;

        foreach ($students as $s) {
            $full_name = $s['full_name'] ?? '';
            $university = $s['university'] ?? '';
            $company = $s['company_name'] ?? '';
            $email = $s['email'] ?? '';
            $phone = $s['phone'] ?? '';
            $return_date = !empty($s['return_date']) ? _d($s['return_date']) : '';

            $subject = "Nhắc: {$full_name} sẽ về nước ngày {$return_date}";
            $html = "Xin chào,<br><br>
                Học sinh <strong>{$full_name}</strong> (Trường {$university}) dự kiến về nước ngày <b>{$return_date}</b>.<br>
                Đơn vị tiếp nhận: {$company}.<br><br>
                Trân trọng.";

            if (!empty($email)) {
                send_mail_template('generic', $email, '', '', [
                    '{content}' => $html,
                    '{subject}' => $subject
                ]);
            }

            if (function_exists('zalo_ifk_send_message') && !empty($phone)) {
                zalo_ifk_send_message($phone, "Thông báo: {$full_name} sẽ về nước ngày {$return_date}");
            }

            log_activity('Đã gửi thông báo sắp về nước cho: ' . $full_name);
        }
    }

    /** ===============================
     *  Danh sách trường
     * =============================== */
    public function get_all_universities()
    {
        $rows = $this->db->select('university')
            ->from($this->tbl())
            ->where('university IS NOT NULL')
            ->where('university !=', '')
            ->group_by('university')
            ->order_by('university', 'ASC')
            ->get()->result_array();

        return array_map(fn($r) => $r['university'], $rows);
    }

    /** ===============================
     *  Danh sách công ty
     * =============================== */
    public function get_all_companies()
    {
        $rows = $this->db->select('company_name')
            ->from($this->tbl())
            ->where('company_name IS NOT NULL')
            ->where('company_name !=', '')
            ->group_by('company_name')
            ->order_by('company_name', 'ASC')
            ->get()->result_array();

        return array_map(fn($r) => $r['company_name'], $rows);
    }

    /** ===============================
     *  Danh sách tỉnh (company_province)
     * =============================== */
    public function get_all_provinces()
    {
        $rows = $this->db->select('company_province')
            ->from($this->tbl())
            ->where('company_province IS NOT NULL')
            ->where('company_province !=', '')
            ->group_by('company_province')
            ->order_by('company_province', 'ASC')
            ->get()->result_array();

        return array_map(fn($r) => $r['company_province'], $rows);
    }

    /** ===============================
     *  Làm sạch dữ liệu trước khi ghi DB
     * =============================== */
    private function sanitize($data)
    {
        $allowed = [
            'full_name','email','phone','address','university',
            'company_name','company_address','company_province',
            'recruit_date','entry_date','months_stay','return_date',
            'status','note','photo','attachment'
        ];

        $clean = [];
        foreach ($allowed as $k) {
            if (isset($data[$k])) $clean[$k] = $data[$k];
        }
        return $clean;
    }

    /** ===============================
     *  Danh sách năm có dữ liệu
     * =============================== */
    public function get_years_available()
    {
        $query = $this->db->query("
            SELECT DISTINCT YEAR(entry_date) AS y 
            FROM " . $this->tbl() . " 
            WHERE entry_date IS NOT NULL AND entry_date != ''
            UNION
            SELECT DISTINCT YEAR(return_date) AS y 
            FROM " . $this->tbl() . " 
            WHERE return_date IS NOT NULL AND return_date != ''
            ORDER BY y DESC
        ");

        $years = [];
        foreach ($query->result_array() as $r) {
            if (!empty($r['y'])) $years[] = (int)$r['y'];
        }

        if (empty($years)) {
            $current = (int)date('Y');
            $years = [$current - 1, $current, $current + 1];
        }

        return array_unique($years);
    }
}
