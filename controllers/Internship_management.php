<?php defined('BASEPATH') or exit('No direct script access allowed');

class Internship_management extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('internship_management/Internship_management_model');
        $this->load->model('internship_management/Im_audit_log_model', 'im_audit');
 $this->load->helper('internship_management/im_audit');
 if ($this->input->method() === 'post') {

    $post = $this->input->post(NULL, true);

    // lọc dữ liệu nhạy cảm
    foreach (['password','pass','smtp_pass','api_key','token'] as $k) {
        if (isset($post[$k])) $post[$k] = '***';
    }

    im_audit_log(
        'http',
        0,
        'http_post',
        'POST vào Internship Applications',
        null,
        [
            'url' => current_url(),
            'staff_id' => get_staff_user_id(),
            'post' => $post
        ]
    );
}
    }

    public function index()
    {
        redirect(admin_url('internship_management/manage'));
    }

    public function manage()
    {
        if (!has_permission('internship_management', '', 'view')) {
            access_denied('internship_management');
        }

        $year = (int)$this->input->get('year');
        if ($year <= 0) $year = (int)date('Y');

        $data['title'] = 'Internship Management';
        $data['years'] = $this->Internship_management_model->get_years_available();
        $data['year']  = in_array($year, $data['years'], true) ? $year : (int)$data['years'][0];

        // This call was crashing before; model now uses IN subquery (no JOIN) to avoid ".university" SQL syntax.
        $data['filter_options'] = $this->Internship_management_model->get_filter_options($data['year']);

        $data['rows'] = $this->Internship_management_model->get_managed_students($data['year'], [
            'keyword'         => $this->input->get('keyword'),
            'filter_status'   => $this->input->get('filter_status'),
            'filter_school'   => $this->input->get('filter_school'),
            'filter_company'  => $this->input->get('filter_company'),
            'filter_staff_id' => $this->input->get('filter_staff_id'),
        ]);

        $data['counters'] = $this->Internship_management_model->get_counters($data['year'], []);

        $this->load->view('internship_management/manage', $data);
    }

    // Backward compatible endpoint used by older JS (if any)
    public function get_filter_options()
    {
        $year = (int)$this->input->get('year');
        if ($year <= 0) $year = (int)date('Y');
        $out = $this->Internship_management_model->get_filter_options($year);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($out);
        exit;
    }


    /**
     * AJAX: Quick view modal for an application
     * URL: /admin/internship_management/quick_view/{application_id}
     */
    public function quick_view($application_id = 0)
    {
        $application_id = (int)$application_id;
        if ($application_id <= 0) {
            show_404();
        }

        $row = $this->Internship_management_model->get_quick_view_row($application_id);

        $data['row'] = $row;

        // view path (module): views/ajax/quick_view.php
        $this->load->view('internship_management/ajax/quick_view', $data);
    }

}