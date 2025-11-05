<?php defined('BASEPATH') or exit('No direct script access allowed');

class Internship_management extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('internship_model');
    }

    /* ===============================
       DANH SÁCH HỌC SINH
       =============================== */
    public function index()
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied('internship_management');
        }

        // Lấy toàn bộ dữ liệu từ model
        $all  = $this->internship_model->get_all();
        $year = $this->input->get('year');

        // 🔹 Lọc theo năm (entry_date hoặc return_date)
        $students = $all;
        if (!empty($year)) {
            $students = array_values(array_filter($all, function ($r) use ($year) {
                $in_entry  = !empty($r['entry_date'])  && date('Y', strtotime($r['entry_date']))  == $year;
                $in_return = !empty($r['return_date']) && date('Y', strtotime($r['return_date'])) == $year;
                return $in_entry || $in_return;
            }));
        }

        // 🔹 Thống kê theo trạng thái
        $counters = [
            'total'       => count($students),
            'preparing'   => 0,
            'submitted'   => 0,
            'interviewed' => 0,
            'coe'         => 0,
            'entered'     => 0,
            'returning'   => 0,
            'returned'    => 0,
        ];

        $today = date('Y-m-d');
        foreach ($students as $row) {
            $status = isset($row['status']) ? $row['status'] : '';
            switch ($status) {
                case 'Chuẩn bị hồ sơ': $counters['preparing']++; break;
                case 'Đã nộp cục':     $counters['submitted']++; break;
                case 'Đã phỏng vấn':   $counters['interviewed']++; break;
                case 'Đã có COE':      $counters['coe']++; break;
                case 'Đã nhập cảnh':   $counters['entered']++; break;
                case 'Sắp về nước':    $counters['returning']++; break;
            }
            if (!empty($row['return_date']) && $row['return_date'] < $today) {
                $counters['returned']++;
            }
        }

        // 🔹 Dropdown lọc dữ liệu tự động
        $universities = [];
        $companies    = [];
        $provinces    = [];

        foreach ($all as $r) {
            if (!empty($r['university'])) {
                $universities[trim($r['university'])] = true;
            }
            if (!empty($r['company_name'])) {
                $companies[trim($r['company_name'])] = true;
            }
            if (!empty($r['company_address'])) {
                // Cắt phần đầu địa chỉ để lấy tên tỉnh
                $parts = explode(',', $r['company_address']);
                $province = trim($parts[0]);
                if (!empty($province)) {
                    $provinces[$province] = true;
                }
            }
        }

        $universities = array_keys($universities);
        $companies    = array_keys($companies);
        $provinces    = array_keys($provinces);
        sort($universities);
        sort($companies);
        sort($provinces);

        // 🔹 Danh sách trạng thái
        $statuses = [
            'Chuẩn bị hồ sơ',
            'Đã nộp cục',
            'Đã phỏng vấn',
            'Đã có COE',
            'Đã nhập cảnh',
            'Sắp về nước',
        ];

        // 🔹 Danh sách năm tự động
        $years_set = [];
        foreach ($all as $r) {
            if (!empty($r['entry_date']))  $years_set[date('Y', strtotime($r['entry_date']))] = true;
            if (!empty($r['return_date'])) $years_set[date('Y', strtotime($r['return_date']))] = true;
        }
        $years = array_keys($years_set);
        rsort($years);
        if (empty($years)) $years[] = date('Y');

        // 🔹 Gửi dữ liệu sang view
        $data = [
            'title'         => 'Quản lý học sinh Internship Nhật Bản',
            'students'      => $students,
            'counters'      => $counters,
            'universities'  => $universities,
            'companies'     => $companies,
            'provinces'     => $provinces,
            'statuses'      => $statuses,
            'years'         => $years,
            'selected_year' => $year ?: date('Y'),
        ];

        $this->load->view('internship_management/manage', $data);
    }

    /* ===============================
       THÊM / SỬA HỌC SINH
       =============================== */
    public function student($id = '')
    {
        $student = [];
        if ($id) {
            $student = $this->internship_model->get($id);
            if (!$student) {
                set_alert('warning', 'Không tìm thấy học sinh');
                redirect(admin_url('internship_management'));
            }
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            // Upload ảnh
            if (!empty($_FILES['photo']['name'])) {
                $upload_path = FCPATH . 'modules/internship_management/uploads/';
                if (!is_dir($upload_path)) mkdir($upload_path, 0755, true);
                $file_name = time() . '_' . preg_replace('/\s+/', '_', $_FILES['photo']['name']);
                move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path . $file_name);
                $data['photo'] = 'modules/internship_management/uploads/' . $file_name;
            }

            // Upload hồ sơ
            if (!empty($_FILES['attachment']['name'])) {
                $upload_path = FCPATH . 'modules/internship_management/uploads/';
                if (!is_dir($upload_path)) mkdir($upload_path, 0755, true);
                $file_name = time() . '_attach_' . preg_replace('/\s+/', '_', $_FILES['attachment']['name']);
                move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path . $file_name);
                $data['attachment'] = 'modules/internship_management/uploads/' . $file_name;
            }

            if ($id == '') {
                $this->internship_model->add($data);
                set_alert('success', '✅ Đã thêm học sinh mới');
            } else {
                $this->internship_model->update($id, $data);
                set_alert('success', '✅ Cập nhật học sinh thành công');
            }

            redirect(admin_url('internship_management'));
        }

        $data['student'] = $student;
        $data['title']   = ($id ? 'Cập nhật' : 'Thêm mới') . ' học sinh';
        $this->load->view('internship_management/form', $data);
    }

    /* ===============================
       XÓA
       =============================== */
    public function delete($id)
    {
        if (!has_permission('internship_management', '', 'delete')) {
            access_denied('internship_management');
        }

        $this->internship_model->delete($id);
        set_alert('success', '🗑️ Đã xóa học sinh thành công');
        redirect(admin_url('internship_management'));
    }

    /* ===============================
       GỬI NHẮC SẮP VỀ NƯỚC
       =============================== */
    public function notify()
    {
        $days = $this->input->get('days') ?: 30;
        $this->internship_model->notify_returning_in_days($days);
        set_alert('success', '🔔 Đã gửi thông báo sắp về nước trong ' . (int)$days . ' ngày tới.');
        redirect(admin_url('internship_management'));
    }

    /* ===============================
       XEM CHI TIẾT HỌC SINH
       =============================== */
    public function view($id = '')
    {
        if (empty($id)) show_404();

        $student = $this->internship_model->get($id);
        if (!$student) {
            set_alert('warning', 'Không tìm thấy học sinh');
            redirect(admin_url('internship_management'));
        }

        $data['title']   = 'Thông tin học sinh';
        $data['student'] = $student;
        $this->load->view('internship_management/view', $data);
    }
   /* ===============================
   BÁO CÁO TỔNG HỢP THEO NĂM
   =============================== */
public function report()
{
    if (!has_permission('internship_management', '', 'view')) {
        access_denied('internship_management');
    }

    // Năm được chọn (mặc định năm hiện tại)
    $year = $this->input->get('year') ?: date('Y');
    $all  = $this->internship_model->get_all();
    $today = date('Y-m-d');

    // 🔹 Lọc học sinh theo năm (entry_date hoặc return_date)
    $students = array_values(array_filter($all, function ($s) use ($year) {
        $in_entry  = !empty($s['entry_date'])  && date('Y', strtotime($s['entry_date']))  == $year;
        $in_return = !empty($s['return_date']) && date('Y', strtotime($s['return_date'])) == $year;
        return $in_entry || $in_return;
    }));

    // 🔹 Thống kê theo trạng thái
    $status_counts = [
        'Chuẩn bị hồ sơ' => 0,
        'Đã nộp cục'     => 0,
        'Đã phỏng vấn'   => 0,
        'Đã có COE'      => 0,
        'Đã nhập cảnh'   => 0,
        'Sắp về nước'    => 0,
        'Đã về nước'     => 0,
    ];

    foreach ($students as $s) {
        $st = $s['status'] ?? '';
        if (isset($status_counts[$st])) {
            $status_counts[$st]++;
        } elseif (!empty($s['return_date']) && $s['return_date'] < $today) {
            $status_counts['Đã về nước']++;
        }
    }

    // 🔹 Thống kê theo tháng (dựa trên entry_date)
    $monthly_counts = array_fill(1, 12, 0);
    foreach ($students as $s) {
        if (!empty($s['entry_date'])) {
            $month = (int)date('n', strtotime($s['entry_date']));
            $monthly_counts[$month]++;
        }
    }

    // 🔹 Lấy danh sách năm có dữ liệu
    $years = $this->internship_model->get_years_available();

    // 🔹 Chuẩn bị dữ liệu cho view
    $data = [
        'title'          => 'Báo cáo tổng hợp Internship Nhật Bản',
        'year'           => $year,
        'years'          => $years,
        'total'          => count($students),
        'status_counts'  => $status_counts,
        'monthly_counts' => $monthly_counts,
    ];

    $this->load->view('internship_management/report', $data);
}

}
