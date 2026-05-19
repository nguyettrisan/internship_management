<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Management_report extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (function_exists('is_admin') && !is_admin()) {
            access_denied('Management report');
        }

        $this->load->model('internship_management/management_report_model', 'management_report_model');
        $this->load->helper('im_filter');
    }

    public function index()
    {
        $filters = $this->read_filters();

        /*$years    = $this->management_report_model->get_years();
        $months   = $this->build_months();
        /*$statuses = $this->management_report_model->get_statuses();
        $schools  = $this->management_report_model->get_schools($filters);

        $statuses_kv = ['' => 'Tất cả'];
        foreach ((array) $statuses as $st) {
            $st = trim((string) $st);
            if ($st !== '') {
                $statuses_kv[$st] = $st;
            }
        }*/
        
        /*$statuses = $this->management_report_model->get_statuses();

        $statuses_kv = ['' => 'Tất cả'];
        foreach ((array) $statuses as $key => $label) {
            $key   = trim((string) $key);
            $label = trim((string) $label);
            if ($key !== '') {
                $statuses_kv[$key] = ($label !== '' ? $label : $key);
            }
        }
        

        $schools_kv = ['' => 'Tất cả'];
        foreach ((array) $schools as $sc) {
            $sc = trim((string) $sc);
            if ($sc !== '') {
                $schools_kv[$sc] = $sc;
            }
        }*/
        
        $years    = $this->management_report_model->get_years();
        //$months   = $this->build_months();
        $months   = function_exists('im_month_options') ? im_month_options(true) : [];

        $statuses = $this->management_report_model->get_statuses();
        $schools  = $this->management_report_model->get_schools($filters);

        $statuses_kv = ['' => 'Tất cả'];
        foreach ((array) $statuses as $key => $label) {
            $key   = trim((string) $key);
            $label = trim((string) $label);
            if ($key !== '') {
                $statuses_kv[$key] = ($label !== '' ? $label : $key);
            }
        }

        $schools_kv = ['' => 'Tất cả'];
        foreach ((array) $schools as $sc) {
            $sc = trim((string) $sc);
            if ($sc !== '') {
                $schools_kv[$sc] = $sc;
            }
        }

        $report = [
            'kpis'            => ['total_job_orders' => 0, 'total_students' => 0, 'in_japan' => 0, 'returned' => 0],
            'by_school'       => [],
            'by_major'        => [],
            'by_major_school' => [],
            'by_status'       => [],
        ];

        try {
            $tmp = $this->management_report_model->get_management_report($filters);
            if (is_array($tmp) && isset($tmp['kpis'])) {
                $report = array_merge($report, $tmp);
            }
        } catch (Throwable $e) {
            // log_message('error', 'Management_report index error: ' . $e->getMessage());
        }

        $data = [];
        $data['title']           = 'Báo cáo quản trị';
        $data['filters']         = $filters;
        $data['years']           = $years;
        $data['months']          = $months;
        $data['statuses']        = $statuses_kv;
        $data['schools']         = $schools_kv;
        $data['report']          = $report;
        $data['kpi']             = $report['kpis'] ?? [];
        $data['by_school']       = $report['by_school'] ?? [];
        $data['by_major']        = $report['by_major'] ?? [];
        $data['by_major_school'] = $report['by_major_school'] ?? [];
        $data['by_status']       = $report['by_status'] ?? [];

        $this->load->view('internship_management/reports/management', $data);
    }

    public function export_csv()
    {
        $filters = $this->read_filters();
        $report  = $this->management_report_model->get_management_report($filters);

        $filename = 'management_report_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $kpi = $report['kpis'] ?? [];

        fputcsv($out, ['KPI', 'Giá trị']);
        fputcsv($out, ['Đơn tuyển', (int) ($kpi['total_job_orders'] ?? 0)]);
        fputcsv($out, ['Sinh viên', (int) ($kpi['total_students'] ?? 0)]);
        fputcsv($out, ['Đang ở Nhật', (int) ($kpi['in_japan'] ?? 0)]);
        fputcsv($out, ['Đã về nước', (int) ($kpi['returned'] ?? 0)]);
        fputcsv($out, []);

        fputcsv($out, ['THEO TRƯỜNG']);
        fputcsv($out, ['Trường', 'Tổng SV', 'Đang Nhật', 'Về nước', 'Tỷ trọng']);
        foreach ((array) ($report['by_school'] ?? []) as $r) {
            fputcsv($out, [
                (string) ($r['name'] ?? ''),
                (int) ($r['total'] ?? 0),
                (int) ($r['in_japan'] ?? 0),
                (int) ($r['returned'] ?? 0),
                (string) ($r['ratio'] ?? ''),
            ]);
        }
        fputcsv($out, []);

        fputcsv($out, ['THEO NGÀNH']);
        fputcsv($out, ['Ngành', 'Tổng SV', 'Đang Nhật', 'Về nước', 'Tỷ trọng']);
        foreach ((array) ($report['by_major'] ?? []) as $r) {
            fputcsv($out, [
                (string) ($r['name'] ?? ''),
                (int) ($r['total'] ?? 0),
                (int) ($r['in_japan'] ?? 0),
                (int) ($r['returned'] ?? 0),
                (string) ($r['ratio'] ?? ''),
            ]);
        }
        fputcsv($out, []);

        if (!empty($report['by_major_school'])) {
            fputcsv($out, ['THEO NGÀNH × TRƯỜNG']);
            fputcsv($out, ['Trường', 'Ngành', 'Tổng SV', 'Đang Nhật', 'Về nước', 'Tỷ trọng']);

            foreach ((array) $report['by_major_school'] as $row) {
                fputcsv($out, [
                    (string) ($row['school'] ?? ''),
                    (string) ($row['major'] ?? ''),
                    (int) ($row['total'] ?? 0),
                    (int) ($row['in_japan'] ?? 0),
                    (int) ($row['returned'] ?? 0),
                    (string) ($row['ratio'] ?? ''),
                ]);
            }
            fputcsv($out, []);
        }

        if (!empty($report['by_status'])) {
            fputcsv($out, ['PIPELINE THEO TRẠNG THÁI']);
            fputcsv($out, ['Trạng thái', 'Số hồ sơ', 'Số SV']);

            foreach ((array) $report['by_status'] as $row) {
                fputcsv($out, [
                    (string) ($row['name'] ?? ''),
                    (int) ($row['total_apps'] ?? 0),
                    (int) ($row['total_students'] ?? 0),
                ]);
            }
            fputcsv($out, []);
        }

        fclose($out);
        exit;
    }

    /*private function build_months()
    {
        $months   = [];
        $months[] = ['value' => 0, 'label' => 'Tất cả'];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = ['value' => $m, 'label' => 'Tháng ' . $m];
        }
        return $months;
    }*/

    private function read_filters()
    {
        $year  = (int) $this->input->get('year');
        $month = (int) $this->input->get('month');

        if ($year < 0) {
            $year = 0;
        }
        if ($month < 0 || $month > 12) {
            $month = 0;
        }

        $q = trim((string) $this->input->get('q'));
        $keyword = trim((string) $this->input->get('keyword'));
        if ($keyword === '' && $q !== '') {
            $keyword = $q;
        }

        return [
            'year'    => $year,
            'month'   => $month,
            'q'       => $keyword,
            'keyword' => $keyword,
            'status'  => trim((string) $this->input->get('status')),
            'school'  => trim((string) $this->input->get('school')),
        ];
    }
}
