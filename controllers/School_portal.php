<?php
defined('BASEPATH') or exit('No direct script access allowed');

class School_portal extends App_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->library('session');
        /*$this->load->helper(['url', 'form', 'internship_management/im_filter']);
        $this->load->model('internship_management/school_portal_model', 'school_portal_model');
        $this->load->helper('internship_status');*/
        $this->load->helper(['url', 'form', 'internship_management/im_filter']);
        $this->load->helper('internship_management/internship_status');
        $this->load->helper('internship_management/job_order_status');
        $this->load->model('internship_management/school_portal_model', 'school_portal_model');
    }

    public function index()
    {
        if ($this->is_logged_in()) {
            redirect(site_url('school_portal/dashboard'));
        }
        redirect(site_url('school_portal/login'));
    }

   public function login()
{
    if ($this->is_logged_in()) {
        redirect(site_url('school_portal/dashboard'));
    }

    $data = ['title' => 'IFK School Portal'];

    if (strtolower($this->input->method()) === 'post') {
        $username = trim((string)$this->input->post('username', true));
        $password = (string)$this->input->post('password');
        $captcha  = trim((string)$this->input->post('captcha', true));

        if (!$this->captcha_ok($captcha)) {
            $this->session->unset_userdata('school_portal_captcha');
            $data['error'] = 'Mã xác nhận không đúng.';
            $this->load->view('internship_management/school_portal/login_pro', $data);
            return;
        }

        $account = $this->school_portal_model->authenticate($username, $password);
        if ($account) {
            $this->session->set_userdata([
                'school_portal_logged_in' => 1,
                'school_account_id'       => (int)$account['id'],
                'school_code'             => (string)($account['school_code'] ?? ''),
                'school_name'             => (string)($account['school_name'] ?? ''),
                'school_username'         => (string)($account['username'] ?? ''),
            ]);
            $this->session->unset_userdata('school_portal_captcha');
            redirect(site_url('school_portal/dashboard'));
        }

        $this->session->unset_userdata('school_portal_captcha');
        $data['error'] = 'Sai tài khoản hoặc mật khẩu.';
    }

    $this->load->view('internship_management/school_portal/login_pro', $data);
}
    public function logout()
    {
        $this->session->unset_userdata([
            'school_portal_logged_in',
            'school_account_id',
            'school_code',
            'school_name',
            'school_username',
        ]);

        redirect(site_url('school_portal/login'));
    }

    /*public function dashboard()
    {
        $this->guard_login();

        $filters = $this->read_filters();
        $filters['school'] = $this->current_school();

        $data = [
            'title'           => 'Dashboard trường',
            'school_name'     => $this->current_school(),
            'filters'         => $filters,
            'years'           => $this->school_portal_model->get_years($this->current_school()),
            'statuses'        => $this->school_portal_model->get_status_options(),
            'summary'         => $this->school_portal_model->get_dashboard_summary($filters),
            'recent_students' => $this->school_portal_model->get_students(array_merge($filters, ['limit' => 8])),
            'status_chart'    => $this->school_portal_model->get_status_chart($filters),
            'job_orders'      => $this->school_portal_model->get_job_orders($this->current_school(), 8),
            'upcoming_events' => $this->school_portal_model->get_calendar_events($filters, 8),
        ];

        $this->load->view('internship_management/school_portal/dashboard_pro', $data);
    }*/
    
    public function dashboard()
    {
        $this->guard_login();
    
        $filters = $this->read_filters();
        $filters['school'] = $this->current_school();
    
        $school     = $this->current_school();
        $schoolCode = trim((string)$this->session->userdata('school_code'));
    
        $data = [
            'title'           => 'Dashboard trường',
            'school_name'     => $school,
            'filters'         => $filters,
            'years'           => $this->school_portal_model->get_years($school),
            //'months'          => function_exists('im_month_options') ? im_month_options(false) : [],
            'months'          => function_exists('im_month_options') ? im_month_options(true) : [],
            'statuses'        => $this->school_portal_model->get_status_options(),
            'summary'         => $this->school_portal_model->get_dashboard_summary($filters),
            'recent_students' => $this->school_portal_model->get_students(array_merge($filters, ['limit' => 8])),
            'status_chart'    => $this->school_portal_model->get_status_chart($filters),
            //'job_orders'      => $this->school_portal_model->get_job_orders($school, 8, $schoolCode),
            'job_orders'      => $this->school_portal_model->get_job_orders($school, 8, $schoolCode, $filters),
            'upcoming_events' => $this->school_portal_model->get_calendar_events($filters, 8),
        ];
    
        $this->load->view('internship_management/school_portal/dashboard_pro', $data);
    }

    public function students()
    {
        $this->guard_login();

        $filters = $this->read_filters();
        $filters['school'] = $this->current_school();

        $data = [
            'title'       => 'Danh sách sinh viên',
            'school_name' => $this->current_school(),
            'filters'     => $filters,
            'years'       => $this->school_portal_model->get_years($this->current_school()),
            'statuses'    => $this->school_portal_model->get_status_options(),
            'students'    => $this->school_portal_model->get_students($filters),
        ];

        $this->load->view('internship_management/school_portal/students_pro', $data);
    }

    public function student($id = 0)
    {
        $this->guard_login();

        $student = $this->school_portal_model->get_student_detail((int)$id, $this->current_school());
        if (!$student) {
            show_404();
        }

        $data = [
            'title'       => 'Chi tiết sinh viên',
            'school_name' => $this->current_school(),
            'student'     => $student,
        ];

        $this->load->view('internship_management/school_portal/student_detail', $data);
    }

 public function calendar()
{
    $school = $this->current_school();

    $year  = (int) $this->input->get('year');
    $month = (int) $this->input->get('month');

    if ($year <= 0) {
        $year = (int) date('Y');
    }
    if ($month <= 0 || $month > 12) {
        $month = (int) date('n');
    }

    $filters = $this->read_filters();
    $filters['school'] = $school;

    $all_events = $this->school_portal_model->get_calendar_events($filters, 0);

    $month_events = [];
    foreach ($all_events as $e) {
        $d = trim((string)($e['event_date'] ?? ''));
        if ($d === '' || $d === '0000-00-00') {
            continue;
        }

        $ts = strtotime($d);
        if (!$ts) {
            continue;
        }

        if ((int) date('Y', $ts) === $year && (int) date('n', $ts) === $month) {
            $month_events[] = $e;
        }
    }

    usort($month_events, function ($a, $b) {
        return strcmp((string)($a['event_date'] ?? ''), (string)($b['event_date'] ?? ''));
    });

    $data = [];
    $data['title']        = 'Lịch';
    $data['school_name']  = $school;
    $data['filters']      = $filters;
    $data['year']         = $year;
    $data['month']        = $month;
    $data['events']       = $month_events;
    $data['statuses']     = $this->school_portal_model->get_status_options();
    $data['years']        = $this->school_portal_model->get_years($school);

  $this->load->view('internship_management/school_portal/calendar_pro', $data);
}

    public function export_csv()
    {
        $this->guard_login();

        $filters = $this->read_filters();
        $filters['school'] = $this->current_school();
        $students = $this->school_portal_model->get_students($filters);

        $filename = 'school_portal_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->current_school()) . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, ['Họ tên', 'Trường', 'Ngành', 'Công ty tiếp nhận', 'Đơn tuyển', 'Trạng thái', 'Ngày PV', 'Ngày nhập cảnh', 'Ngày về nước', 'Số tháng']);

        foreach ($students as $row) {
            fputcsv($out, [
                $row['student_name'],
                $row['school'],
                $row['major'],
                $row['company_receive'],
                $row['job_order_id'],
                $row['status_label'],
                $row['interview_date'],
                $row['entry_date'],
                $row['return_date'],
                $row['months'],
            ]);
        }

        fclose($out);
        exit;
        
    }

    private function is_logged_in()
    {
        return (int)$this->session->userdata('school_portal_logged_in') === 1;
    }

    private function guard_login()
    {
        if (!$this->is_logged_in()) {
            redirect(site_url('school_portal/login'));
        }
    }

    private function current_school()
    {
        $school = trim((string)$this->session->userdata('school_name'));
        if ($school !== '') {
            return $school;
        }

        return trim((string)$this->session->userdata('school_code'));
    }

    /*private function read_filters()
    {
        return [
            'year'   => (int)$this->input->get('year'),
            'status' => trim((string)$this->input->get('status', true)),
            'q'      => trim((string)$this->input->get('q', true)),
        ];
    }*/
    
    private function read_filters()
    {
        $yearRaw  = $this->input->get('year', true);
        $monthRaw = $this->input->get('month', true);
    
        $hasYear  = ($yearRaw !== null && $yearRaw !== '');
        $hasMonth = ($monthRaw !== null && $monthRaw !== '');
    
        $year  = (int)$yearRaw;
        $month = (int)$monthRaw;
    
        // Chỉ mặc định năm hiện tại khi user CHƯA truyền year
        if (!$hasYear) {
            $year = (int)date('Y');
        } elseif ($year < 0) {
            $year = 0;
        }
    
        // Chỉ mặc định tháng hiện tại khi user CHƯA truyền month
        if (!$hasMonth) {
            $month = (int)date('n');
        } elseif ($month < 0 || $month > 12) {
            $month = 0;
        }
    
        return [
            'year'   => $year,
            'month'  => $month,
            'status' => trim((string)$this->input->get('status', true)),
            'q'      => trim((string)$this->input->get('q', true)),
        ];
    }
/*   public function job_orders()
{
    $this->guard_login();

    $school = $this->current_school();
    
    //
    $schoolCode = trim((string) $this->session->userdata('school_code'));

    $data = [
        'title'       => 'Đơn tuyển',
        'school_name' => $school,
        
        //'job_orders'  => $this->school_portal_model->get_job_orders($school),
        'job_orders'  => $this->school_portal_model->get_job_orders($school, 0, $schoolCode),
    ];

    $this->load->view('internship_management/school_portal/job_orders_pro', $data);

}*/
public function job_orders()
{
    $this->guard_login();

    $school     = $this->current_school();
    $schoolCode = trim((string) $this->session->userdata('school_code'));

    /*$scope = trim((string)$this->input->get('scope', true));

    if (!in_array($scope, ['active', 'year', 'all'], true)) {
        $scope = 'active';
    }

    $year = (int)$this->input->get('year', true);

    if ($year <= 0) {
        $year = (int)date('Y');
    }

    $filters = [
        'scope' => $scope,
        'year'  => $year,
    ];*/
    $scope = trim((string)$this->input->get('scope', true));

    if (!in_array($scope, ['active', 'year', 'all'], true)) {
        $scope = 'active';
    }
    
    if ($scope === 'year') {
        $year = (int)$this->input->get('year', true);
    
        if ($year <= 0) {
            $year = (int)date('Y');
        }
    } else {
        // Active và All không lấy year trên URL.
        // Tránh trường hợp URL còn ?scope=active&year=2025 làm người dùng hiểu nhầm.
        $year = (int)date('Y');
    }
    
    $filters = [
        'scope' => $scope,
        'year'  => $year,
    ];

    $data = [
        'title'       => 'Đơn tuyển',
        'school_name' => $school,
        'filters'     => $filters,
        //'years'       => $this->school_portal_model->get_years($school),
        'years'       => $this->school_portal_model->get_job_order_years($school, $schoolCode),
        'job_orders'  => $this->school_portal_model->get_job_orders($school, 0, $schoolCode, $filters),
    ];

    $this->load->view('internship_management/school_portal/job_orders_pro', $data);
}

//
public function job_order($id = 0)
{
    $this->guard_login();

    $school     = $this->current_school();
    $schoolCode = trim((string) $this->session->userdata('school_code'));
    $job        = $this->school_portal_model->get_job_order_detail((int) $id, $school, $schoolCode);

    if (!$job) {
        show_404();
    }

    $data = [
        'title'       => 'Chi tiết đơn tuyển',
        'school_name' => $school,
        'job'         => $job,
    ];

    $this->load->view('internship_management/school_portal/job_order_detail', $data);
}

public function print_job_order($id = 0)
{
    $this->guard_login();

    $school     = $this->current_school();
    $schoolCode = trim((string) $this->session->userdata('school_code'));
    $job        = $this->school_portal_model->get_job_order_detail((int) $id, $school, $schoolCode);

    if (!$job) {
        show_404();
    }

    $this->load->view('internship_management/school_portal/print_job_order', ['job' => $job, 'school_name' => $school]);
}
//

private function random_captcha_word($length = 5)
{
    $pool = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $max  = strlen($pool) - 1;
    $word = '';

    for ($i = 0; $i < $length; $i++) {
        $word .= $pool[random_int(0, $max)];
    }

    return $word;
}

public function captcha()
{
    $word = $this->random_captcha_word(5);

    $this->session->set_userdata('school_portal_captcha', strtoupper($word));

    $width  = 170;
    $height = 52;

    $chars = mb_str_split($word);
    $svg = [];
    $svg[] = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$width.'" height="'.$height.'" viewBox="0 0 '.$width.' '.$height.'">';
    $svg[] = '<rect width="100%" height="100%" rx="12" ry="12" fill="#f8fbfe" stroke="#dbe6f2"/>';

    for ($i = 0; $i < 8; $i++) {
        $x1 = random_int(0, $width);
        $y1 = random_int(0, $height);
        $x2 = random_int(0, $width);
        $y2 = random_int(0, $height);
        $svg[] = '<line x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke="#d9e6f2" stroke-width="1"/>';
    }

    for ($i = 0; $i < 18; $i++) {
        $cx = random_int(5, $width - 5);
        $cy = random_int(5, $height - 5);
        $r  = random_int(1, 2);
        $svg[] = '<circle cx="'.$cx.'" cy="'.$cy.'" r="'.$r.'" fill="#c9d9ea"/>';
    }

    $baseX = 18;
    foreach ($chars as $i => $ch) {
        $x = $baseX + ($i * 28) + random_int(-2, 2);
        $y = random_int(32, 40);
        $rotate = random_int(-18, 18);
        $size = random_int(24, 30);
        $color = ($i % 2 === 0) ? '#0b2e59' : '#17457f';

        $svg[] = '<text x="'.$x.'" y="'.$y.'" font-family="Arial, Helvetica, sans-serif" font-size="'.$size.'" font-weight="700" fill="'.$color.'" transform="rotate('.$rotate.' '.$x.' '.$y.')">'.$ch.'</text>';
    }

    $svg[] = '</svg>';

    $output = implode('', $svg);

    header('Content-Type: image/svg+xml; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    echo $output;
    exit;
}

private function captcha_ok($input)
{
    $expected = strtoupper(trim((string) $this->session->userdata('school_portal_captcha')));
    $actual   = strtoupper(trim((string) $input));

    return $expected !== '' && $actual !== '' && hash_equals($expected, $actual);
}

public function forgot_password()
{
    if ($this->is_logged_in()) {
        redirect(site_url('school_portal/dashboard'));
    }

    $data = ['title' => 'Quên mật khẩu'];

    if (strtolower($this->input->method()) === 'post') {
        $username = trim((string) $this->input->post('username', true));
        $email    = trim((string) $this->input->post('email', true));
        $captcha  = trim((string) $this->input->post('captcha', true));

        if (!$this->captcha_ok($captcha)) {
            $this->session->unset_userdata('school_portal_captcha');
            $data['error'] = 'Mã xác nhận không đúng.';
            $this->load->view('internship_management/school_portal/forgot_password_pro', $data);
            return;
        }

        $account = $this->school_portal_model->find_account_for_reset($username, $email);

        if (!$account) {
            $this->session->unset_userdata('school_portal_captcha');
            $data['error'] = 'Không tìm thấy tài khoản khớp với email.';
            $this->load->view('internship_management/school_portal/forgot_password_pro', $data);
            return;
        }

        $token = bin2hex(random_bytes(32));
        $this->school_portal_model->save_reset_token(
            (int) $account['id'],
            $token,
            date('Y-m-d H:i:s', time() + 3600)
        );

        $this->session->unset_userdata('school_portal_captcha');
        $data['success'] = 'Đã tạo link đặt lại mật khẩu: ' . site_url('school_portal/reset_password/' . $token);
    }

    $this->load->view('internship_management/school_portal/forgot_password_pro', $data);
}

public function reset_password($token = '')
{
    $data = ['title' => 'Đặt lại mật khẩu'];
    // Implement reset form if needed
    show_error('Chưa triển khai form đặt lại mật khẩu. Token: ' . html_escape($token));

}
}