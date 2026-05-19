<?php
defined('BASEPATH') or exit('No direct script access allowed');
// DOTSTATUS_20260228_1716


class Reports extends AdminController
{
    /*public function __construct()
    {
        parent::__construct();
        $this->load->model('internship_management/Reports_model', 'reports_model');
        $this->load->helper('internship_management/job_order_status');
    }*/
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('internship_management/Reports_model', 'reports_model');
        $this->load->helper('internship_management/job_order_status');
        $this->load->helper('internship_management/internship_status');
    }

    /**
     * Unified filters (GET/POST)
     * Must match view fields in index_report.php
     */
    private function get_filters($src = 'get')
    {
        $in = $src === 'post' ? $this->input->post(null, true) : $this->input->get(null, true);

        // Normalize blank to null
        $norm = function ($v) {
            if ($v === null) return null;
            if (is_string($v)) {
                $v = trim($v);
                return $v === '' ? null : $v;
            }
            return $v;
        };

        /*$f = [
            // basic
            'tab'    => $norm($in['tab'] ?? null),
            'year'   => $norm($in['year'] ?? null),
            'month'  => $norm($in['month'] ?? null),*/
            
            $currentYear  = (int)date('Y');
            $currentMonth = (int)date('n');
            
            $f = [
                'tab'    => $norm($in['tab'] ?? null),
                'year'   => $norm($in['year'] ?? $currentYear),
                'month'  => $norm($in['month'] ?? $currentMonth),
            
            'search' => $norm($in['search'] ?? null),
            'major'  => $norm($in['major'] ?? null),
            'status' => $norm($in['status'] ?? null),

            // advanced (applications/progress/job_orders)
            'school'           => $norm($in['school'] ?? null),
            'gender'           => $norm($in['gender'] ?? null),
            'interview_result' => $norm($in['interview_result'] ?? null),

            'company'   => $norm($in['company'] ?? null),
            'round_no'  => $norm($in['round_no'] ?? null),

            // date ranges
            'date_from'      => $norm($in['date_from'] ?? null),
            'date_to'        => $norm($in['date_to'] ?? null),

            'interview_from' => $norm($in['interview_from'] ?? null),
            'interview_to'   => $norm($in['interview_to'] ?? null),

            'entry_from'     => $norm($in['entry_from'] ?? null),
            'entry_to'       => $norm($in['entry_to'] ?? null),

            'return_from'    => $norm($in['return_from'] ?? null),
            'return_to'      => $norm($in['return_to'] ?? null),

            // numeric
            'min_apps' => $norm($in['min_apps'] ?? null),
            'max_apps' => $norm($in['max_apps'] ?? null),
            'min_qty'  => $norm($in['min_qty'] ?? null),
        ];

        // Hard normalize numeric fields
        foreach (['year','month','round_no','min_apps','max_apps','min_qty'] as $k) {
            if ($f[$k] !== null && $f[$k] !== '') {
                if (is_numeric($f[$k])) $f[$k] = (int)$f[$k];
                else $f[$k] = null;
            }
        }

        // normalize tab
        $tab = $f['tab'] ?: 'job_orders';
        if (!in_array($tab, ['job_orders','applications','progress','students'], true)) $tab = 'job_orders';
        $f['tab'] = $tab;

        return $f;
    }

    public function index()
    {
        $filters = $this->get_filters('get');
        $tab = $filters['tab'];

        // Status list: prefer injected from existing controllers; otherwise fallback to model derived list.
        if ($tab === 'job_orders') {
            $data['status_list'] = $this->reports_model->get_job_order_status_list();
        } else {
            $data['status_list'] = $this->reports_model->get_application_status_list();
        }

        //
        $data['report_years'] = $this->reports_model->get_report_years();
    
        // Dashboard data is tab-aware (chart + pipeline)
        $data['kpi']      = $this->reports_model->get_kpi_summary($filters);
        $data['pipeline'] = $this->reports_model->get_pipeline_summary($filters); // optimized (single query)
        $data['monthly']  = $this->reports_model->get_monthly_series($filters);   // tab-aware
      if (!empty($data['pipeline']) && is_array($data['pipeline'])) {
    foreach ($data['pipeline'] as &$p) {
        $key = $p['key'] ?? '';
        $lbl = $p['label'] ?? '';
        $p['label'] = $this->vi_status_label($key, $lbl);
    }
    unset($p);
} // optimized (single query)

        // Tab report
        if ($tab === 'applications') {
            $data['report'] = $this->reports_model->applications_report($filters);
        } elseif ($tab === 'progress') {
            $data['report'] = $this->reports_model->progress_report($filters);
        } elseif ($tab === 'students') {
            $data['report'] = $this->reports_model->students_report($filters);
        } else {
            $data['report'] = $this->reports_model->job_orders_report($filters);
        }

        // Export (CSV/XLSX): view passes ?export=csv|xlsx
        $export = $this->input->get('export', true);
        if (in_array($export, ['csv','xlsx'], true)) {
            $this->reports_model->export_by_tab($tab, $filters, $export, $data['status_list']);
            return;
        }

        $data['tab']     = $tab;
        $data['title']   = 'Báo cáo tổng hợp (PRO)';
        $data['filters'] = $filters;

        $this->load->view('internship_management/reports/index', $data);
    }

    public function ajax_dashboard()
    {
        if (!$this->input->is_ajax_request()) show_404();

        $filters = $this->get_filters('post');

        $res = [
            'kpi'      => $this->reports_model->get_kpi_summary($filters),
            'pipeline' => $this->reports_model->get_pipeline_summary($filters),
            'monthly'  => $this->reports_model->get_monthly_series($filters),
        ];

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($res);
        exit;
    }

/*private function vi_status_label($key, $label = '')
{
    $key = trim((string)$key);
    $label = trim((string)$label);

    if ($key !== '') {
        return im_job_order_status_label($key, 'vi');
    }

    if ($label !== '') {
        return im_job_order_status_label($label, 'vi');
    }

    return '—';
}*/

private function vi_status_label($key, $label = '')
{
    $key   = trim((string)$key);
    $label = trim((string)$label);

    if ($key !== '') {
        return im_status_label_vi($key);
    }

    if ($label !== '') {
        return im_status_label_vi($label);
    }

    return '—';
}

}
