<?php
defined('BASEPATH') or exit('No direct script access allowed');

class School_portal_model extends App_Model
{
    private $table_accounts;
    private $table_apps;
    private $table_jobs;
    private $table_calendar;

    public function __construct()
    {
        parent::__construct();

        $this->table_accounts = db_prefix() . 'internship_school_accounts';
        $this->table_apps     = db_prefix() . 'internship_applications';
        $this->table_jobs     = db_prefix() . 'internship_job_orders';
        $this->table_calendar = db_prefix() . 'internship_calendar';
        $this->load->helper('internship_management/internship_status');
        $this->load->helper('internship_management/job_order_status');
    }

    private function clean_date($value)
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '';
        }

        return substr($value, 0, 10);
    }
    
    private function is_cancelled_calendar_row(array $row)
    {
        return function_exists('im_calendar_is_cancelled_row')
            ? im_calendar_is_cancelled_row($row)
            : false;
    }
    
    private function is_valid_job_date($value)
    {
        $date = $this->clean_date($value);
    
        if ($date === '') {
            return false;
        }
    
        $year = (int)substr($date, 0, 4);
    
        return $year >= 2000 && $year <= ((int)date('Y') + 20);
    }
    
    private function job_order_primary_date_from_row(array $row)
    {
        $entryDate = $this->clean_date($row['entry_date'] ?? '');
    
        if ($this->is_valid_job_date($entryDate)) {
            return $entryDate;
        }
    
        $interviewDate = $this->clean_date($row['interview_date'] ?? '');
    
        if ($this->is_valid_job_date($interviewDate)) {
            return $interviewDate;
        }
    
        return '';
    }
    
    private function job_order_primary_year_from_row(array $row)
    {
        $date = $this->job_order_primary_date_from_row($row);
    
        if ($date === '') {
            return 0;
        }
    
        return (int)substr($date, 0, 4);
    }
    
    private function valid_date_sql($expr)
    {
        $expr = trim((string)$expr);
    
        if ($expr === '') {
            return '0=1';
        }
    
        return '('
            . $expr . ' IS NOT NULL'
            . ' AND ' . $expr . " <> ''"
            . ' AND ' . $expr . " <> '0000-00-00'"
            . ' AND ' . $expr . " <> '0000-00-00 00:00:00'"
            . ' AND YEAR(' . $expr . ') BETWEEN 2000 AND YEAR(CURDATE()) + 20'
            . ')';
    }
    
    private function job_order_primary_year_sql($entryExpr, $interviewExpr)
    {
        $cases = [];
    
        $entryExpr = trim((string)$entryExpr);
        $interviewExpr = trim((string)$interviewExpr);
    
        if ($entryExpr !== '') {
            $cases[] = 'WHEN ' . $this->valid_date_sql($entryExpr) . ' THEN YEAR(' . $entryExpr . ')';
        }
    
        if ($interviewExpr !== '') {
            $cases[] = 'WHEN ' . $this->valid_date_sql($interviewExpr) . ' THEN YEAR(' . $interviewExpr . ')';
        }
    
        if (empty($cases)) {
            return '0';
        }
    
        return '(CASE ' . implode(' ', $cases) . ' ELSE 0 END)';
    }
    
    private function job_order_primary_date_sql($entryExpr, $interviewExpr)
    {
        $cases = [];
    
        $entryExpr = trim((string)$entryExpr);
        $interviewExpr = trim((string)$interviewExpr);
    
        if ($entryExpr !== '') {
            $cases[] = 'WHEN ' . $this->valid_date_sql($entryExpr) . ' THEN DATE(' . $entryExpr . ')';
        }
    
        if ($interviewExpr !== '') {
            $cases[] = 'WHEN ' . $this->valid_date_sql($interviewExpr) . ' THEN DATE(' . $interviewExpr . ')';
        }
    
        if (empty($cases)) {
            return 'NULL';
        }
    
        return '(CASE ' . implode(' ', $cases) . ' ELSE NULL END)';
    }
    
    /*private function is_active_job_order_row(array $row)
    {
        $today = date('Y-m-d');
        $currentYear = (int)date('Y');
    
        $statusRaw = trim((string)($row['status'] ?? ''));
        $statusKey = function_exists('im_job_order_normalize_status')
            ? im_job_order_normalize_status($statusRaw)
            : strtolower(str_replace([' ', '-'], '_', $statusRaw));
    
        $closedStatuses = [
            'cancelled',
            'canceled',
            'done',
            'closed',
            'returned',
            'stopped',
        ];
    
        if (in_array($statusKey, $closedStatuses, true)) {
            return false;
        }
    
        // Đơn đang tuyển chỉ lấy đơn thuộc năm hiện tại.
        // Năm của đơn = năm nhập cảnh; nếu chưa có nhập cảnh thì lấy năm PV.
        $primaryYear = $this->job_order_primary_year_from_row($row);
    
        if ($primaryYear !== $currentYear) {
            return false;
        }
    
        // Nếu đơn đã có ngày về nước hợp lệ và đã qua ngày về nước thì không còn là đơn đang tuyển.
        $returnDate = $this->clean_date($row['return_date'] ?? '');
    
        if ($this->is_valid_job_date($returnDate) && $returnDate < $today) {
            return false;
        }
    
        return true;
    }*/
    private function is_active_job_order_row(array $row)
    {
        $today = date('Y-m-d');
    
        $statusRaw = trim((string)($row['status'] ?? ''));
        $statusKey = function_exists('im_job_order_normalize_status')
            ? im_job_order_normalize_status($statusRaw)
            : strtolower(str_replace([' ', '-'], '_', $statusRaw));
    
        $closedStatuses = [
            'cancelled',
            'canceled',
            'done',
            'closed',
            'returned',
            'stopped',
        ];
    
        if (in_array($statusKey, $closedStatuses, true)) {
            return false;
        }
    
        // Ngày chính của đơn:
        // - ưu tiên ngày nhập cảnh
        // - nếu chưa có ngày nhập cảnh thì dùng ngày phỏng vấn
        $primaryDate = $this->job_order_primary_date_from_row($row);
    
        // Không có ngày nhập cảnh / PV hợp lệ thì không xem là "đang tuyển".
        if ($primaryDate === '') {
            return false;
        }
    
        // Nếu ngày chính đã qua hôm nay thì không còn là đơn đang tuyển.
        if ($primaryDate < $today) {
            return false;
        }
    
        return true;
    }
    
    
    private function status_label($status)
    {
        if (function_exists('im_status_label_vi')) {
            return im_status_label_vi($status);
        }
    
        return (string)$status;
    }

    private function list_table_fields($table)
    {
        static $cache = [];

        if (!isset($cache[$table])) {
            if ($this->db->table_exists($table)) {
                $cache[$table] = $this->db->list_fields($table);
            } else {
                $cache[$table] = [];
            }
        }

        return $cache[$table];
    }

    private function pick_col($table, array $candidates, $fallback = '')
    {
        $fields = $this->list_table_fields($table);

        foreach ($candidates as $col) {
            if (in_array($col, $fields, true)) {
                return $col;
            }
        }

        return $fallback;
    }

    private function school_from_input($value)
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_array($value)) {
            foreach (['school', 'school_name', 'university'] as $key) {
                if (!empty($value[$key]) && is_string($value[$key])) {
                    return trim($value[$key]);
                }
            }
        }

        return '';
    }

    private function app_map()
    {
        return [
            'id'              => $this->pick_col($this->table_apps, ['id'], 'id'),
            'student_name'    => $this->pick_col($this->table_apps, ['student_name', 'full_name', 'name'], ''),
            'school'          => $this->pick_col($this->table_apps, ['school', 'school_name', 'university'], ''),
            'major'           => $this->pick_col($this->table_apps, ['major'], ''),
            'job_order_id'    => $this->pick_col($this->table_apps, ['job_order_id'], ''),
            'status'          => $this->pick_col($this->table_apps, ['status', 'manage_status'], ''),
            'entry_date'      => $this->pick_col($this->table_apps, ['entry_date', 'entry_date_vi'], ''),
            'return_date'     => $this->pick_col($this->table_apps, ['return_date', 'return_date_vi'], ''),
            //'company_receive' => $this->pick_col($this->table_apps, ['company_receive', 'receiver_company', 'company_name'], ''),
            'company_receive' => $this->pick_col($this->table_apps, ['company_receive', 'receiver_company', 'company_name_vi', 'company_name', 'company_name_jp'], ''),
        ];
    }

    private function job_map()
    {
        return [
            'id'             => $this->pick_col($this->table_jobs, ['id'], 'id'),
            //'company_name'   => $this->pick_col($this->table_jobs, ['company_name', 'company_receive', 'receiver_company', 'job_order_name'], ''),
            'company_name'   => $this->pick_col($this->table_jobs, ['company_name_vi', 'company_name', 'company_receive', 'receiver_company', 'company_name_jp', 'job_order_name'], ''),
            'interview_date' => $this->pick_col($this->table_jobs, ['interview_date', 'interview_date_vi'], ''),
            'entry_date'     => $this->pick_col($this->table_jobs, ['entry_date_vi', 'entry_date'], ''),
            'return_date'    => $this->pick_col($this->table_jobs, ['return_date_vi', 'return_date'], ''),
        ];
    }

     public function authenticate($username, $password)
    {
        if (!$this->db->table_exists($this->table_accounts)) {
            return false;
        }
    
        $row = $this->db
            ->where('username', trim($username))
            ->where('is_active', 1)
            ->get($this->table_accounts)
            ->row_array();
    
        if (!$row) {
            return false;
        }
    
        $hash = trim((string)($row['password_hash'] ?? ''));
    
        if ($hash === '') {
            return false;
        }
    
        if (password_get_info($hash)['algo'] !== 0) {
            return password_verify($password, $hash) ? $row : false;
        }
    
        return hash_equals($hash, (string)$password) ? $row : false;
    }

    public function get_years($school = '')
    {
        $rows = $this->get_students([
            'school' => $this->school_from_input($school),
            'year'   => 0,
            'month'  => 0,
            'status' => '',
            'q'      => '',
            'limit'  => 0,
        ]);
    
        $years = [];
    
        foreach ($rows as $row) {
            foreach (['interview_date', 'entry_date', 'return_date'] as $field) {
                $val = trim((string)($row[$field] ?? ''));
                if ($val === '' || $val === '0000-00-00') {
                    continue;
                }
    
                $y = (int) substr($val, 0, 4);
                if ($y > 0) {
                    $years[$y] = $y;
                }
            }
        }
    
        $years = array_values($years);
        rsort($years, SORT_NUMERIC);
    
        return $years;
    }
    
    public function get_job_order_years($school = '', $school_code = '')
    {
        $jobTbl = $this->table_jobs;
        $mapTbl = db_prefix() . 'internship_job_order_schools';
    
        $school = $this->school_from_input($school);
        $school_code = trim((string)$school_code);
    
        if (!$this->db->table_exists($jobTbl) || !$this->db->table_exists($mapTbl)) {
            return $this->get_years($school);
        }
    
        $jobInterviewCol = $this->pick_col($jobTbl, ['interview_date', 'interview_date_vi'], '');
        $jobEntryCol     = $this->pick_col($jobTbl, ['entry_date_vi', 'entry_date'], '');
    
        $primaryYearSql = $this->job_order_primary_year_sql(
            $jobEntryCol !== '' ? 'j.' . $jobEntryCol : '',
            $jobInterviewCol !== '' ? 'j.' . $jobInterviewCol : ''
        );
    
        if ($primaryYearSql === '0') {
            return $this->get_years($school);
        }
    
        $this->db->select($primaryYearSql . ' AS filter_year', false);
        $this->db->from($jobTbl . ' j');
        $this->db->join($mapTbl . ' m', 'm.job_order_id = j.id AND m.is_active = 1', 'inner');
    
        if ($school_code !== '') {
            $this->db->group_start();
            $this->db->where('m.school_code', $school_code);
    
            if ($school !== '') {
                $this->db->or_where('LOWER(TRIM(m.school_name)) =', mb_strtolower($school));
            }
    
            $this->db->group_end();
        } elseif ($school !== '') {
            $this->db->where('LOWER(TRIM(m.school_name)) =', mb_strtolower($school));
        }
    
        $this->db->where($primaryYearSql . ' > 0', null, false);
        $this->db->group_by('filter_year');
        $this->db->order_by('filter_year', 'DESC');
    
        $rows = $this->db->get()->result_array();
    
        $years = [];
    
        foreach ($rows as $row) {
            $year = (int)($row['filter_year'] ?? 0);
    
            if ($year > 0) {
                $years[$year] = $year;
            }
        }
    
        $currentYear = (int)date('Y');
        $years[$currentYear] = $currentYear;
    
        rsort($years, SORT_NUMERIC);
    
        return array_values($years);
    }

    public function get_status_options()
    {
        $this->load->helper('internship_management/internship_status');
    
        return im_school_portal_status_options();
    }

    public function get_students($filters = [])
    {
        $school = $this->school_from_input($filters['school'] ?? '');
        $year   = (int) ($filters['year'] ?? 0);
        $month  = (int) ($filters['month'] ?? 0);
        $status = trim((string) ($filters['status'] ?? ''));
        $q      = trim((string) ($filters['q'] ?? ($filters['keyword'] ?? '')));
        $limit  = isset($filters['limit']) ? (int) $filters['limit'] : 0;

        $appNameCol    = $this->pick_col($this->table_apps, ['student_name', 'full_name', 'name'], '');
        $appSchoolCol  = $this->pick_col($this->table_apps, ['school', 'school_name', 'university'], '');
        $appMajorCol   = $this->pick_col($this->table_apps, ['major'], '');
        //$appCompanyCol = $this->pick_col($this->table_apps, ['company_receive', 'receiver_company', 'company_name'], '');
        $appCompanyCol = $this->pick_col($this->table_apps, ['company_receive', 'receiver_company', 'company_name_vi', 'company_name', 'company_name_jp'], '');
        $appJobCol     = $this->pick_col($this->table_apps, ['job_order_id'], '');
        //$appStatusCol  = $this->pick_col($this->table_apps, ['status'], '');
        $appStatusCol    = $this->pick_col($this->table_apps, ['status', 'manage_status'], '');
        $appDossierCol   = $this->pick_col($this->table_apps, ['dossier_progress', 'manage_status', 'status'], '');
        $appInterviewCol = $this->pick_col($this->table_apps, ['interview_result'], '');
        $appApplyCol   = $this->pick_col($this->table_apps, ['apply_date', 'datecreated', 'created_at', 'createdat'], '');
        $appEntryCol   = $this->pick_col($this->table_apps, ['entry_date'], '');
        $appReturnCol  = $this->pick_col($this->table_apps, ['return_date'], '');
        $appCreatedCol = $this->pick_col($this->table_apps, ['datecreated', 'created_at', 'createdat'], '');
        $appUpdatedCol = $this->pick_col($this->table_apps, ['updated_at', 'dateupdated'], '');

        //$jobNameCol      = $this->pick_col($this->table_jobs, ['company_name', 'receiver_company', 'company_receive'], '');
        $jobNameCol      = $this->pick_col($this->table_jobs, ['company_name_vi', 'company_name', 'receiver_company', 'company_receive', 'company_name_jp'], '');
        $jobInterviewCol = $this->pick_col($this->table_jobs, ['interview_date', 'interview_date_vi'], '');
        $jobEntryCol     = $this->pick_col($this->table_jobs, ['entry_date_vi', 'entry_date'], '');
        $jobReturnCol    = $this->pick_col($this->table_jobs, ['return_date_vi', 'return_date'], '');

        $select = [
            'a.id',
            ($appNameCol !== '' ? "a.$appNameCol AS student_name" : "'' AS student_name"),
            ($appSchoolCol !== '' ? "a.$appSchoolCol AS school" : "'' AS school"),
            ($appMajorCol !== '' ? "a.$appMajorCol AS major" : "'' AS major"),
            ($appJobCol !== '' ? "a.$appJobCol AS job_order_id" : "'' AS job_order_id"),
            //($appStatusCol !== '' ? "a.$appStatusCol AS status" : "'' AS status"),
            ($appStatusCol !== '' ? "a.$appStatusCol AS legacy_status" : "'' AS legacy_status"),
            ($appDossierCol !== '' ? "a.$appDossierCol AS dossier_progress" : "'' AS dossier_progress"),
            ($appInterviewCol !== '' ? "a.$appInterviewCol AS interview_result" : "'' AS interview_result"),
            ($appCompanyCol !== '' ? "a.$appCompanyCol AS app_company_receive" : "'' AS app_company_receive"),
            ($appApplyCol !== '' ? "a.$appApplyCol AS app_apply_date" : "'' AS app_apply_date"),
            ($appEntryCol !== '' ? "a.$appEntryCol AS app_entry_date" : "'' AS app_entry_date"),
            ($appReturnCol !== '' ? "a.$appReturnCol AS app_return_date" : "'' AS app_return_date"),
            ($appCreatedCol !== '' ? "a.$appCreatedCol AS app_created_at" : "'' AS app_created_at"),
            ($appUpdatedCol !== '' ? "a.$appUpdatedCol AS app_updated_at" : "'' AS app_updated_at"),
            ($jobNameCol !== '' ? "j.$jobNameCol AS job_company_receive" : "'' AS job_company_receive"),
            ($jobInterviewCol !== '' ? "j.$jobInterviewCol AS interview_date" : "'' AS interview_date"),
            ($jobEntryCol !== '' ? "j.$jobEntryCol AS job_entry_date" : "'' AS job_entry_date"),
            ($jobReturnCol !== '' ? "j.$jobReturnCol AS job_return_date" : "'' AS job_return_date"),
        ];

        $this->db->select(implode(",\n", $select), false);
        $this->db->from($this->table_apps . ' a');

        if ($appJobCol !== '' && $this->db->table_exists($this->table_jobs)) {
            $this->db->join($this->table_jobs . ' j', 'j.id = a.' . $appJobCol, 'left');
        }

  /*if ($school !== '' && $app['school'] !== '') {
    $this->db->where('LOWER(TRIM(a.' . $app['school'] . ')) =', mb_strtolower(trim($school)));
}*/

if ($school !== '' && $appSchoolCol !== '') {
    $this->db->where('LOWER(TRIM(a.' . $appSchoolCol . ')) =', mb_strtolower(trim($school)));
}
        /*if ($status !== '' && $appStatusCol !== '') {
            $this->db->where('a.' . $appStatusCol, $status);
        }*/
        
        if ($status !== '') {
            $this->load->helper('internship_management/internship_status');
        
            $target = im_application_filter_target($status);
            $values = $target['values'] ?? [];
        
            $normalValues = array_values(array_filter($values, function($v) {
                return $v !== null && $v !== '';
            }));
        
            $hasEmpty = in_array('', $values, true);
            $hasNull  = in_array(null, $values, true);
        
            if (($target['type'] ?? '') === 'interview_result') {
                if ($appInterviewCol !== '' && !empty($normalValues)) {
                    $this->db->where_in('a.' . $appInterviewCol, $normalValues);
                } elseif ($appStatusCol !== '' && !empty($normalValues)) {
                    $this->db->where_in('a.' . $appStatusCol, $normalValues);
                }
            } else {
                $filterCol = $appDossierCol !== '' ? $appDossierCol : $appStatusCol;
        
                if ($filterCol !== '') {
                    $this->db->group_start();
        
                    if (!empty($normalValues)) {
                        $this->db->where_in('a.' . $filterCol, $normalValues);
                    }
        
                    if ($hasEmpty) {
                        if (!empty($normalValues)) {
                            $this->db->or_where('a.' . $filterCol, '');
                        } else {
                            $this->db->where('a.' . $filterCol, '');
                        }
                    }
        
                    if ($hasNull) {
                        if (!empty($normalValues) || $hasEmpty) {
                            $this->db->or_where('a.' . $filterCol . ' IS NULL', null, false);
                        } else {
                            $this->db->where('a.' . $filterCol . ' IS NULL', null, false);
                        }
                    }
        
                    $this->db->group_end();
                }
            }
        }

        if ($q !== '') {
            $this->db->group_start();

            if ($appNameCol !== '') {
                $this->db->like('a.' . $appNameCol, $q);
            }

            if ($appSchoolCol !== '') {
                if ($appNameCol !== '') {
                    $this->db->or_like('a.' . $appSchoolCol, $q);
                } else {
                    $this->db->like('a.' . $appSchoolCol, $q);
                }
            }

            if ($appCompanyCol !== '') {
                if ($appNameCol !== '' || $appSchoolCol !== '') {
                    $this->db->or_like('a.' . $appCompanyCol, $q);
                } else {
                    $this->db->like('a.' . $appCompanyCol, $q);
                }
            }

            if ($jobNameCol !== '' && $this->db->table_exists($this->table_jobs)) {
                if ($appNameCol !== '' || $appSchoolCol !== '' || $appCompanyCol !== '') {
                    $this->db->or_like('j.' . $jobNameCol, $q);
                } else {
                    $this->db->like('j.' . $jobNameCol, $q);
                }
            }

            if ($appJobCol !== '') {
                $this->db->or_like('a.' . $appJobCol, $q);
            }

            $this->db->group_end();
        }

        if ($year > 0) {
            $this->db->group_start();

            $added = false;

            if ($jobInterviewCol !== '' && $this->db->table_exists($this->table_jobs)) {
                $this->db->where('YEAR(j.' . $jobInterviewCol . ') = ' . $year, null, false);
                $added = true;
            }

            if ($jobEntryCol !== '' && $this->db->table_exists($this->table_jobs)) {
                if ($added) {
                    $this->db->or_where('YEAR(j.' . $jobEntryCol . ') = ' . $year, null, false);
                } else {
                    $this->db->where('YEAR(j.' . $jobEntryCol . ') = ' . $year, null, false);
                    $added = true;
                }
            }

            if ($jobReturnCol !== '' && $this->db->table_exists($this->table_jobs)) {
                if ($added) {
                    $this->db->or_where('YEAR(j.' . $jobReturnCol . ') = ' . $year, null, false);
                } else {
                    $this->db->where('YEAR(j.' . $jobReturnCol . ') = ' . $year, null, false);
                    $added = true;
                }
            }

            if ($appEntryCol !== '') {
                if ($added) {
                    $this->db->or_where('YEAR(a.' . $appEntryCol . ') = ' . $year, null, false);
                } else {
                    $this->db->where('YEAR(a.' . $appEntryCol . ') = ' . $year, null, false);
                    $added = true;
                }
            }

            if ($appReturnCol !== '') {
                if ($added) {
                    $this->db->or_where('YEAR(a.' . $appReturnCol . ') = ' . $year, null, false);
                } else {
                    $this->db->where('YEAR(a.' . $appReturnCol . ') = ' . $year, null, false);
                }
            }
            
            if ($appApplyCol !== '') {
                if ($added) {
                    $this->db->or_where('YEAR(a.' . $appApplyCol . ') = ' . $year, null, false);
                } else {
                    $this->db->where('YEAR(a.' . $appApplyCol . ') = ' . $year, null, false);
                    $added = true;
                }
            }

            $this->db->group_end();
        }
        
            if ($month > 0 && $month <= 12) {
            $this->db->group_start();

            $added = false;

            if ($jobInterviewCol !== '' && $this->db->table_exists($this->table_jobs)) {
                $this->db->where('MONTH(j.' . $jobInterviewCol . ') = ' . $month, null, false);
                $added = true;
            }

            if ($jobEntryCol !== '' && $this->db->table_exists($this->table_jobs)) {
                if ($added) {
                    $this->db->or_where('MONTH(j.' . $jobEntryCol . ') = ' . $month, null, false);
                } else {
                    $this->db->where('MONTH(j.' . $jobEntryCol . ') = ' . $month, null, false);
                    $added = true;
                }
            }

            if ($jobReturnCol !== '' && $this->db->table_exists($this->table_jobs)) {
                if ($added) {
                    $this->db->or_where('MONTH(j.' . $jobReturnCol . ') = ' . $month, null, false);
                } else {
                    $this->db->where('MONTH(j.' . $jobReturnCol . ') = ' . $month, null, false);
                    $added = true;
                }
            }

            if ($appEntryCol !== '') {
                if ($added) {
                    $this->db->or_where('MONTH(a.' . $appEntryCol . ') = ' . $month, null, false);
                } else {
                    $this->db->where('MONTH(a.' . $appEntryCol . ') = ' . $month, null, false);
                    $added = true;
                }
            }

            if ($appReturnCol !== '') {
                if ($added) {
                    $this->db->or_where('MONTH(a.' . $appReturnCol . ') = ' . $month, null, false);
                } else {
                    $this->db->where('MONTH(a.' . $appReturnCol . ') = ' . $month, null, false);
                    $added = true;
                }
            }
            
            if ($appApplyCol !== '') {
                if ($added) {
                    $this->db->or_where('MONTH(a.' . $appApplyCol . ') = ' . $month, null, false);
                } else {
                    $this->db->where('MONTH(a.' . $appApplyCol . ') = ' . $month, null, false);
                    $added = true;
                }
            }

            $this->db->group_end();
        }

        //$this->db->order_by('a.id', 'DESC');
        if ($appUpdatedCol !== '') {
            $this->db->order_by('a.' . $appUpdatedCol, 'DESC');
        } elseif ($appCreatedCol !== '') {
            $this->db->order_by('a.' . $appCreatedCol, 'DESC');
        } else {
            $this->db->order_by('a.id', 'DESC');
        }

        if ($limit > 0) {
            $this->db->limit($limit);
        }

        $rows = $this->db->get()->result_array();

        /*foreach ($rows as &$r) {
            $jobCompany = trim((string) ($r['job_company_receive'] ?? ''));
            $appCompany = trim((string) ($r['app_company_receive'] ?? ''));

            $r['student_name']    = (string) ($r['student_name'] ?? '');
            $r['school']          = (string) ($r['school'] ?? '');
            $r['major']           = (string) ($r['major'] ?? '');
            $r['job_order_id']    = (string) ($r['job_order_id'] ?? '');
            $r['company_receive'] = $jobCompany !== '' ? $jobCompany : $appCompany;
            $r['interview_date']  = $this->clean_date($r['interview_date'] ?? '');
            $r['entry_date']      = $this->clean_date(($r['job_entry_date'] ?? '') ?: ($r['app_entry_date'] ?? ''));
            $r['return_date']     = $this->clean_date(($r['job_return_date'] ?? '') ?: ($r['app_return_date'] ?? ''));
            $r['status_label']    = $this->status_label($r['status'] ?? '');
            $r['recent_sort_date'] = trim((string)(($r['app_updated_at'] ?? '') ?: ($r['app_created_at'] ?? '')));
            $r['months']          = '';
        }*/
        foreach ($rows as &$r) {
            $jobCompany = trim((string) ($r['job_company_receive'] ?? ''));
            $appCompany = trim((string) ($r['app_company_receive'] ?? ''));
        
            $progressRaw = trim((string)(($r['dossier_progress'] ?? '') ?: ($r['legacy_status'] ?? '')));
            $progressKey = im_normalize_dossier_progress($progressRaw);
        
            $r['student_name']     = (string) ($r['student_name'] ?? '');
            $r['school']           = (string) ($r['school'] ?? '');
            $r['major']            = (string) ($r['major'] ?? '');
            $r['job_order_id']     = (string) ($r['job_order_id'] ?? '');
            $r['company_receive']  = $jobCompany !== '' ? $jobCompany : $appCompany;
            $r['interview_date']   = $this->clean_date($r['interview_date'] ?? '');
            $r['entry_date']       = $this->clean_date(($r['job_entry_date'] ?? '') ?: ($r['app_entry_date'] ?? ''));
            $r['return_date']      = $this->clean_date(($r['job_return_date'] ?? '') ?: ($r['app_return_date'] ?? ''));
        
            $r['status']           = $progressKey;
            $r['status_label']     = im_status_label_vi($progressKey);
            $r['status_color']     = im_status_color_hex($progressKey);
            $r['interview_result'] = im_normalize_interview_result($r['interview_result'] ?? '');
        
            $r['recent_sort_date'] = trim((string)(($r['app_updated_at'] ?? '') ?: ($r['app_created_at'] ?? '')));
            $r['months']           = '';
        }
        

        return $rows;
    }

    public function get_student_detail($id, $school = '')
    {
        if (!$this->db->table_exists($this->table_apps)) {
            return null;
        }

        $app = $this->app_map();
        $job = $this->job_map();

        $appFields     = $this->list_table_fields($this->table_apps);
        $studentTable  = db_prefix() . 'internship_students';
        $studentFields = $this->db->table_exists($studentTable) ? $this->list_table_fields($studentTable) : [];

        $pickApp = function (array $candidates, $default = '') use ($appFields) {
            foreach ($candidates as $field) {
                if (in_array($field, $appFields, true)) {
                    return $field;
                }
            }
            return $default;
        };

        $pickStu = function (array $candidates, $default = '') use ($studentFields) {
            foreach ($candidates as $field) {
                if (in_array($field, $studentFields, true)) {
                    return $field;
                }
            }
            return $default;
        };

        $appStudentIdCol = $pickApp(['student_id'], '');
        $appMajorCol     = $pickApp(['major'], '');

        $stuIdCol          = $pickStu(['id'], 'id');
        $stuNameCol        = $pickStu(['full_name', 'student_name', 'name'], '');
        $stuSchoolCol      = $pickStu(['university', 'school_name', 'school'], '');
        $stuMajorCol       = $pickStu(['major'], '');
        $stuEmailCol       = $pickStu(['email'], '');
        $stuPhoneCol       = $pickStu(['phone', 'phone_student'], '');
        $stuParentPhoneCol = $pickStu(['parent_phone', 'phone_parent', 'parent_phones'], '');
        $stuBirthdayCol    = $pickStu(['birthday', 'dob'], '');
        $stuEnglishCol     = $pickStu(['english_level'], '');
        $stuJapaneseCol    = $pickStu(['japanese_level'], '');
        $stuAddressCol     = $pickStu(['address'], '');
        $stuGenderCol      = $pickStu(['gender'], '');
        $stuPhotoCol       = $pickStu(['photo'], '');
        $stuEntryCol       = $pickStu(['entry_date'], '');
        $stuReturnCol      = $pickStu(['return_date'], '');
        $stuStatusCol      = $pickStu(['status'], '');
        $stuManagerCol     = $pickStu(['manager', 'staff_in_charge'], '');

        $appEmailCol       = $pickApp(['email'], '');
        $appPhoneCol       = $pickApp(['phone_student', 'phone'], '');
        $appParentPhoneCol = $pickApp(['phone_parent', 'parent_phone', 'parent_phones'], '');
        $appBirthdayCol    = $pickApp(['birthday', 'dob'], '');
        $appEnglishCol     = $pickApp(['english_level'], '');
        $appJapaneseCol    = $pickApp(['japanese_level'], '');
        $appInterviewCol   = $pickApp(['interview_result'], '');
        $appDossierCol     = $pickApp(['dossier_progress', 'manage_status'], '');
        $appStaffCol       = $pickApp(['staff_in_charge', 'manager'], '');
        $appNoteCol        = $pickApp(['note'], '');
        $appGenderCol      = $pickApp(['gender'], '');
        $appAddressCol     = $pickApp(['address'], '');
        $appParentCol      = $pickApp(['parent_name', 'parent'], '');
        $appPhotoCol       = $pickApp(['photo'], '');

        $select = [
            'a.' . $app['id'] . ' AS id',
            ($app['student_name'] !== '' ? 'a.' . $app['student_name'] . ' AS app_student_name' : "'' AS app_student_name"),
            ($app['school'] !== '' ? 'a.' . $app['school'] . ' AS app_school' : "'' AS app_school"),
            ($appMajorCol !== '' ? 'a.' . $appMajorCol . ' AS app_major' : "'' AS app_major"),
            ($app['job_order_id'] !== '' ? 'a.' . $app['job_order_id'] . ' AS job_order_id' : "'' AS job_order_id"),
            ($app['status'] !== '' ? 'a.' . $app['status'] . ' AS app_status' : "'' AS app_status"),
            ($app['company_receive'] !== '' ? 'a.' . $app['company_receive'] . ' AS app_company_receive' : "'' AS app_company_receive"),
            ($app['entry_date'] !== '' ? 'a.' . $app['entry_date'] . ' AS app_entry_date' : "'' AS app_entry_date"),
            ($app['return_date'] !== '' ? 'a.' . $app['return_date'] . ' AS app_return_date' : "'' AS app_return_date"),
            ($appEmailCol !== '' ? 'a.' . $appEmailCol . ' AS app_email' : "'' AS app_email"),
            ($appPhoneCol !== '' ? 'a.' . $appPhoneCol . ' AS app_phone' : "'' AS app_phone"),
            ($appParentPhoneCol !== '' ? 'a.' . $appParentPhoneCol . ' AS app_parent_phone' : "'' AS app_parent_phone"),
            ($appBirthdayCol !== '' ? 'a.' . $appBirthdayCol . ' AS app_birthday' : "'' AS app_birthday"),
            ($appEnglishCol !== '' ? 'a.' . $appEnglishCol . ' AS app_english_level' : "'' AS app_english_level"),
            ($appJapaneseCol !== '' ? 'a.' . $appJapaneseCol . ' AS app_japanese_level' : "'' AS app_japanese_level"),
            ($appInterviewCol !== '' ? 'a.' . $appInterviewCol . ' AS interview_result' : "'' AS interview_result"),
            ($appDossierCol !== '' ? 'a.' . $appDossierCol . ' AS dossier_progress' : "'' AS dossier_progress"),
            ($appStaffCol !== '' ? 'a.' . $appStaffCol . ' AS app_staff_in_charge' : "'' AS app_staff_in_charge"),
            ($appNoteCol !== '' ? 'a.' . $appNoteCol . ' AS note' : "'' AS note"),
            ($appGenderCol !== '' ? 'a.' . $appGenderCol . ' AS app_gender' : "'' AS app_gender"),
            ($appAddressCol !== '' ? 'a.' . $appAddressCol . ' AS app_address' : "'' AS app_address"),
            ($appParentCol !== '' ? 'a.' . $appParentCol . ' AS parent_name' : "'' AS parent_name"),
            ($appPhotoCol !== '' ? 'a.' . $appPhotoCol . ' AS app_photo' : "'' AS app_photo"),
        ];

        if ($this->db->table_exists($this->table_jobs) && $app['job_order_id'] !== '') {
            $select[] = ($job['company_name'] !== '' ? 'j.' . $job['company_name'] . ' AS job_company_name' : "'' AS job_company_name");
            $select[] = ($job['interview_date'] !== '' ? 'j.' . $job['interview_date'] . ' AS interview_date' : "'' AS interview_date");
            $select[] = ($job['entry_date'] !== '' ? 'j.' . $job['entry_date'] . ' AS job_entry_date' : "'' AS job_entry_date");
            $select[] = ($job['return_date'] !== '' ? 'j.' . $job['return_date'] . ' AS job_return_date' : "'' AS job_return_date");
        } else {
            $select[] = "'' AS job_company_name";
            $select[] = "'' AS interview_date";
            $select[] = "'' AS job_entry_date";
            $select[] = "'' AS job_return_date";
        }

        if ($this->db->table_exists($studentTable) && $appStudentIdCol !== '') {
            $select[] = ($stuNameCol !== '' ? 's.' . $stuNameCol . ' AS stu_student_name' : "'' AS stu_student_name");
            $select[] = ($stuSchoolCol !== '' ? 's.' . $stuSchoolCol . ' AS stu_school' : "'' AS stu_school");
            $select[] = ($stuMajorCol !== '' ? 's.' . $stuMajorCol . ' AS major' : "'' AS major");
            $select[] = ($stuEmailCol !== '' ? 's.' . $stuEmailCol . ' AS stu_email' : "'' AS stu_email");
            $select[] = ($stuPhoneCol !== '' ? 's.' . $stuPhoneCol . ' AS stu_phone' : "'' AS stu_phone");
            $select[] = ($stuParentPhoneCol !== '' ? 's.' . $stuParentPhoneCol . ' AS stu_parent_phone' : "'' AS stu_parent_phone");
            $select[] = ($stuBirthdayCol !== '' ? 's.' . $stuBirthdayCol . ' AS stu_birthday' : "'' AS stu_birthday");
            $select[] = ($stuEnglishCol !== '' ? 's.' . $stuEnglishCol . ' AS stu_english_level' : "'' AS stu_english_level");
            $select[] = ($stuJapaneseCol !== '' ? 's.' . $stuJapaneseCol . ' AS stu_japanese_level' : "'' AS stu_japanese_level");
            $select[] = ($stuAddressCol !== '' ? 's.' . $stuAddressCol . ' AS stu_address' : "'' AS stu_address");
            $select[] = ($stuGenderCol !== '' ? 's.' . $stuGenderCol . ' AS stu_gender' : "'' AS stu_gender");
            $select[] = ($stuPhotoCol !== '' ? 's.' . $stuPhotoCol . ' AS photo' : "'' AS photo");
            $select[] = ($stuEntryCol !== '' ? 's.' . $stuEntryCol . ' AS stu_entry_date' : "'' AS stu_entry_date");
            $select[] = ($stuReturnCol !== '' ? 's.' . $stuReturnCol . ' AS stu_return_date' : "'' AS stu_return_date");
            $select[] = ($stuStatusCol !== '' ? 's.' . $stuStatusCol . ' AS stu_status' : "'' AS stu_status");
            $select[] = ($stuManagerCol !== '' ? 's.' . $stuManagerCol . ' AS stu_staff_in_charge' : "'' AS stu_staff_in_charge");
        } else {
            $select[] = "'' AS stu_student_name";
            $select[] = "'' AS stu_school";
            $select[] = "'' AS major";
            $select[] = "'' AS stu_email";
            $select[] = "'' AS stu_phone";
            $select[] = "'' AS stu_parent_phone";
            $select[] = "'' AS stu_birthday";
            $select[] = "'' AS stu_english_level";
            $select[] = "'' AS stu_japanese_level";
            $select[] = "'' AS stu_address";
            $select[] = "'' AS stu_gender";
            $select[] = "'' AS photo";
            $select[] = "'' AS stu_entry_date";
            $select[] = "'' AS stu_return_date";
            $select[] = "'' AS stu_status";
            $select[] = "'' AS stu_staff_in_charge";
        }

        $this->db->select(implode(",\n", $select), false);
        $this->db->from($this->table_apps . ' a');

        if ($this->db->table_exists($this->table_jobs) && $app['job_order_id'] !== '') {
            $this->db->join($this->table_jobs . ' j', 'j.' . $job['id'] . ' = a.' . $app['job_order_id'], 'left');
        }

        if ($this->db->table_exists($studentTable) && $appStudentIdCol !== '') {
            $this->db->join($studentTable . ' s', 's.' . $stuIdCol . ' = a.' . $appStudentIdCol, 'left');
        }

        $this->db->where('a.' . $app['id'], (int) $id);

       if ($school !== '' && $app['school'] !== '') {
    $this->db->where('LOWER(TRIM(a.' . $app['school'] . ')) =', mb_strtolower(trim($school)));
}

        $row = $this->db->get()->row_array();
        if (!$row) {
            return null;
        }

        $row['student_name']     = trim((string) (($row['stu_student_name'] ?? '') ?: ($row['app_student_name'] ?? '')));
        $row['school']           = trim((string) (($row['stu_school'] ?? '') ?: ($row['app_school'] ?? '')));
        $row['company_receive']  = trim((string) (($row['app_company_receive'] ?? '') ?: ($row['job_company_name'] ?? '')));
        $row['entry_date']       = $this->clean_date(($row['job_entry_date'] ?? '') ?: ($row['stu_entry_date'] ?? '') ?: ($row['app_entry_date'] ?? ''));
        $row['return_date']      = $this->clean_date(($row['stu_return_date'] ?? '') ?: ($row['job_return_date'] ?? '') ?: ($row['app_return_date'] ?? ''));
        $row['birthday']         = $this->clean_date(($row['stu_birthday'] ?? '') ?: ($row['app_birthday'] ?? ''));
        $row['status']           = trim((string) (($row['stu_status'] ?? '') ?: ($row['app_status'] ?? '')));
        $row['status_label']     = $this->status_label($row['status']);
        $row['email']            = trim((string) (($row['stu_email'] ?? '') ?: ($row['app_email'] ?? '')));
        $row['phone_student']    = trim((string) (($row['stu_phone'] ?? '') ?: ($row['app_phone'] ?? '')));
        $row['phone_parent']     = trim((string) (($row['stu_parent_phone'] ?? '') ?: ($row['app_parent_phone'] ?? '')));
        $row['english_level']    = trim((string) (($row['stu_english_level'] ?? '') ?: ($row['app_english_level'] ?? '')));
        $row['japanese_level']   = trim((string) (($row['stu_japanese_level'] ?? '') ?: ($row['app_japanese_level'] ?? '')));
        $row['staff_in_charge']  = trim((string) (($row['stu_staff_in_charge'] ?? '') ?: ($row['app_staff_in_charge'] ?? '')));
        $row['gender']           = trim((string) (($row['stu_gender'] ?? '') ?: ($row['app_gender'] ?? '')));
        $row['address']          = trim((string) (($row['stu_address'] ?? '') ?: ($row['app_address'] ?? '')));
        $row['interview_date']   = $this->clean_date($row['interview_date'] ?? '');
        $row['interview_result'] = trim((string) ($row['interview_result'] ?? ''));
        $row['dossier_progress'] = trim((string) ($row['dossier_progress'] ?? ''));
        $row['note']             = trim((string) ($row['note'] ?? ''));
        $row['major']            = trim((string) (($row['major'] ?? '') ?: ($row['app_major'] ?? '')));
        $row['photo']            = trim((string) ($row['photo'] ?? ''));
        $row['parent_name']      = trim((string) ($row['parent_name'] ?? ''));

        return $row;
    }

    public function get_dashboard_summary($filters = [])
    {
        $rows = $this->get_students(array_merge((array) $filters, ['limit' => 0]));

        $jobIds = [];
        $out = [
            'total_students' => 0,
            'entry_count'    => 0,
            'return_count'   => 0,
            'job_orders'     => 0,
        ];

        foreach ($rows as $row) {
            $out['total_students']++;

            if ($row['entry_date'] !== '') {
                $out['entry_count']++;
            }

            if ($row['return_date'] !== '') {
                $out['return_count']++;
            }

            if ($row['job_order_id'] !== '') {
                $jobIds[$row['job_order_id']] = true;
            }
        }

        $out['job_orders'] = count($jobIds);

        return $out;
    }

    /*public function get_status_chart($filters = [])
    {
        $rows = $this->get_students(array_merge((array) $filters, ['limit' => 0]));
        $bucket = [];

        foreach ($rows as $row) {
            $label = $row['status_label'] !== '' ? $row['status_label'] : 'Chưa cập nhật';
            $bucket[$label] = ($bucket[$label] ?? 0) + 1;
        }

        $out = [];

        foreach ($bucket as $label => $total) {
            $out[] = [
                'label' => $label,
                'total' => $total,
            ];
        }

        return $out;
    }*/
    
    public function get_status_chart($filters = [])
    {
        $this->load->helper('internship_management/internship_status');
    
        $rows = $this->get_students(array_merge((array) $filters, ['limit' => 0]));
        $bucket = [];
    
        foreach ($rows as $row) {
            $key = im_normalize_dossier_progress($row['status'] ?? ($row['dossier_progress'] ?? ''));
    
            if (!isset($bucket[$key])) {
                $bucket[$key] = 0;
            }
    
            $bucket[$key]++;
        }
    
        $out = [];
        $order = array_keys(im_dossier_progress_list());
    
        foreach ($order as $key) {
            if (empty($bucket[$key])) {
                continue;
            }
    
            $out[] = [
                'status_key' => $key,
                'label'      => im_status_label_vi($key),
                'color'      => im_status_color_hex($key),
                'total'      => (int)$bucket[$key],
            ];
        }
    
        return $out;
    }

    public function get_calendar_events($filters = [], $limit = 0)
    {
        $rows = $this->get_students(array_merge((array) $filters, ['limit' => 0]));
        $events = [];
        $today = date('Y-m-d');
    
        foreach ($rows as $row) {
            if ($this->is_cancelled_calendar_row($row)) {
                continue;
            }
    
            $statusRaw = (string)($row['status'] ?? '');

            $baseEvent = [
                'student_id'        => (string)($row['id'] ?? ''),
                'student_name'      => (string)($row['student_name'] ?? ''),
                'company_receive'   => (string)($row['company_receive'] ?? ''),
                'job_order_id'      => (string)($row['job_order_id'] ?? ''),
                'status'            => $statusRaw,
                'status_label'      => (string)($row['status_label'] ?? ''),
                'status_class'      => function_exists('im_calendar_status_class') ? im_calendar_status_class($statusRaw) : 'slate',
                'province_receive'  => (string)($row['province_receive'] ?? ''),
                'company_address'   => (string)($row['company_address'] ?? ''),
                'school_name'       => (string)($row['school'] ?? ''),
                'major_name'        => (string)($row['major'] ?? ''),
                'note'              => (string)($row['note'] ?? ''),
            ];
    
            if (!empty($row['interview_date']) && $row['interview_date'] >= $today) {
                $events[] = array_merge($baseEvent, [
                    'event_type' => 'interview',
                    'event_date' => $row['interview_date'],
                ]);
            }
    
            if (!empty($row['entry_date']) && $row['entry_date'] >= $today) {
                $events[] = array_merge($baseEvent, [
                    'event_type' => 'entry',
                    'event_date' => $row['entry_date'],
                ]);
            }
    
            if (!empty($row['return_date']) && $row['return_date'] >= $today) {
                $events[] = array_merge($baseEvent, [
                    'event_type' => 'return',
                    'event_date' => $row['return_date'],
                ]);
            }
        }
    
        usort($events, function ($a, $b) {
            return strcmp((string)$a['event_date'], (string)$b['event_date']);
        });
    
        return $limit > 0 ? array_slice($events, 0, $limit) : $events;
    }


    
   //public function get_job_orders($school = '', $limit = 0, $school_code = '')
   /*public function get_job_orders($school = '', $limit = 0, $school_code = '', $filters = [])
    {
        $jobTbl = $this->table_jobs;
        $mapTbl = db_prefix() . 'internship_job_order_schools';
        $appTbl = $this->table_apps;
    
        $school      = $this->school_from_input($school);
        $school_code = trim((string) $school_code);
        $year  = (int)($filters['year'] ?? 0);
        $month = (int)($filters['month'] ?? 0);
    
        $rows = [];*/
    public function get_job_orders($school = '', $limit = 0, $school_code = '', $filters = [])
    {
        $jobTbl = $this->table_jobs;
        $mapTbl = db_prefix() . 'internship_job_order_schools';
        $appTbl = $this->table_apps;
    
        $school      = $this->school_from_input($school);
        $school_code = trim((string) $school_code);
    
        $scope = trim((string)($filters['scope'] ?? ''));
    
        if (!in_array($scope, ['active', 'year', 'all'], true)) {
            $scope = '';
        }
    
        $year  = (int)($filters['year'] ?? 0);
        $month = (int)($filters['month'] ?? 0);
    
        if ($scope === 'year' && $year <= 0) {
            $year = (int)date('Y');
        }
    
        // Với trang Đơn tuyển:
        // - active: không lọc năm/tháng, chỉ lấy đơn còn hiệu lực.
        // - all: không lọc năm/tháng.
        // - year: chỉ lọc theo năm.
        //
        // Với Dashboard cũ:
        // - scope rỗng thì giữ logic lọc year/month hiện tại.
        if ($scope === 'active' || $scope === 'all') {
            $year = 0;
            $month = 0;
        }
    
        if ($scope === 'year') {
            $month = 0;
        }
    
        $rows = [];
        $usedMap = false;
    
        // =========================
        // 1) Ưu tiên lấy từ bảng mapping gửi trường
        // =========================
        if ($this->db->table_exists($jobTbl) && $this->db->table_exists($mapTbl)) {
            $usedMap = true;
            $appJobCol       = $this->pick_col($appTbl, ['job_order_id'], '');
            //$jobCompanyCol   = $this->pick_col($jobTbl, ['company_name', 'company_receive', 'receiver_company'], '');
            $jobCompanyCol   = $this->pick_col($jobTbl, ['company_name_vi', 'company_name', 'company_receive', 'receiver_company', 'company_name_jp'], '');
            $jobInterviewCol = $this->pick_col($jobTbl, ['interview_date', 'interview_date_vi'], '');
            $jobEntryCol     = $this->pick_col($jobTbl, ['entry_date_vi', 'entry_date'], '');
            $jobReturnCol    = $this->pick_col($jobTbl, ['return_date_vi', 'return_date'], '');
    
            $select = [
                'j.*',
                'j.id AS portal_job_id',
                'm.school_id AS partner_school_id',
                'm.school_code AS partner_school_code',
                'm.school_name AS partner_school_name',
                'm.sent_at AS sent_to_school_at',
                ($jobCompanyCol !== '' ? 'j.' . $jobCompanyCol . ' AS company_receive' : "'' AS company_receive"),
                ($jobInterviewCol !== '' ? 'j.' . $jobInterviewCol . ' AS interview_date' : "'' AS interview_date"),
                ($jobEntryCol !== '' ? 'j.' . $jobEntryCol . ' AS entry_date' : "'' AS entry_date"),
                ($jobReturnCol !== '' ? 'j.' . $jobReturnCol . ' AS return_date' : "'' AS return_date"),
            ];
    
            if ($this->db->table_exists($appTbl) && $appJobCol !== '') {
                $select[] = 'COUNT(a.id) AS total_students';
            } else {
                $select[] = '0 AS total_students';
            }
    
            $this->db->select(implode(', ', $select), false);
            $this->db->from($jobTbl . ' j');
            $this->db->join($mapTbl . ' m', 'm.job_order_id = j.id AND m.is_active = 1', 'inner');
    
            if ($this->db->table_exists($appTbl) && $appJobCol !== '') {
                $this->db->join($appTbl . ' a', 'a.' . $appJobCol . ' = j.id', 'left');
            }
    
            if ($school_code !== '') {
                $this->db->group_start();
                $this->db->where('m.school_code', $school_code);
                if ($school !== '') {
                    $this->db->or_where('LOWER(TRIM(m.school_name)) =', mb_strtolower($school));
                }
                $this->db->group_end();
            } elseif ($school !== '') {
                $this->db->where('LOWER(TRIM(m.school_name)) =', mb_strtolower($school));
            }
            
            /*$primaryYearSql = $this->job_order_primary_year_sql(
                $jobEntryCol !== '' ? 'j.' . $jobEntryCol : '',
                $jobInterviewCol !== '' ? 'j.' . $jobInterviewCol : ''
            );
            
            // Trang Đơn tuyển - Đơn đang tuyển:
            // chỉ lấy đơn thuộc năm hiện tại theo ngày nhập cảnh; nếu chưa có nhập cảnh thì theo ngày PV.
            if ($scope === 'active') {
                $this->db->where($primaryYearSql . ' = ' . (int)date('Y'), null, false);
            
            // Trang Đơn tuyển - Theo năm:
            // chỉ xét năm nhập cảnh; nếu chưa có nhập cảnh thì xét năm PV.
            // Không xét ngày về nước.
            } elseif ($scope === 'year' && $year > 0) {
                $this->db->where($primaryYearSql . ' = ' . (int)$year, null, false);*/
            $primaryYearSql = $this->job_order_primary_year_sql(
                $jobEntryCol !== '' ? 'j.' . $jobEntryCol : '',
                $jobInterviewCol !== '' ? 'j.' . $jobInterviewCol : ''
            );
            
            $primaryDateSql = $this->job_order_primary_date_sql(
                $jobEntryCol !== '' ? 'j.' . $jobEntryCol : '',
                $jobInterviewCol !== '' ? 'j.' . $jobInterviewCol : ''
            );
            
            // Trang Đơn tuyển - Đơn đang tuyển:
            // chỉ lấy đơn có ngày chính từ hôm nay trở đi.
            // Ngày chính = ngày nhập cảnh; nếu chưa có nhập cảnh thì lấy ngày PV.
            if ($scope === 'active') {
                $this->db->where($primaryDateSql . ' >= CURDATE()', null, false);
            
            // Trang Đơn tuyển - Theo năm:
            // chỉ xét năm nhập cảnh; nếu chưa có nhập cảnh thì xét năm PV.
            // Không xét ngày về nước.
            } elseif ($scope === 'year' && $year > 0) {
                $this->db->where($primaryYearSql . ' = ' . (int)$year, null, false);
            
            // Dashboard cũ vẫn giữ logic cũ để không làm vỡ những chỗ khác đang dùng get_job_orders().
            } elseif ($scope === '') {
                if ($year > 0) {
                    $this->db->group_start();
            
                    $added = false;
            
                    if ($jobInterviewCol !== '') {
                        $this->db->where('YEAR(j.' . $jobInterviewCol . ') = ' . $year, null, false);
                        $added = true;
                    }
            
                    if ($jobEntryCol !== '') {
                        $added
                            ? $this->db->or_where('YEAR(j.' . $jobEntryCol . ') = ' . $year, null, false)
                            : $this->db->where('YEAR(j.' . $jobEntryCol . ') = ' . $year, null, false);
                        $added = true;
                    }
            
                    if ($jobReturnCol !== '') {
                        $added
                            ? $this->db->or_where('YEAR(j.' . $jobReturnCol . ') = ' . $year, null, false)
                            : $this->db->where('YEAR(j.' . $jobReturnCol . ') = ' . $year, null, false);
                    }
            
                    $this->db->group_end();
                }
            
                if ($month > 0 && $month <= 12) {
                    $this->db->group_start();
            
                    $added = false;
            
                    if ($jobInterviewCol !== '') {
                        $this->db->where('MONTH(j.' . $jobInterviewCol . ') = ' . $month, null, false);
                        $added = true;
                    }
            
                    if ($jobEntryCol !== '') {
                        $added
                            ? $this->db->or_where('MONTH(j.' . $jobEntryCol . ') = ' . $month, null, false)
                            : $this->db->where('MONTH(j.' . $jobEntryCol . ') = ' . $month, null, false);
                        $added = true;
                    }
            
                    if ($jobReturnCol !== '') {
                        $added
                            ? $this->db->or_where('MONTH(j.' . $jobReturnCol . ') = ' . $month, null, false)
                            : $this->db->where('MONTH(j.' . $jobReturnCol . ') = ' . $month, null, false);
                    }
            
                    $this->db->group_end();
                }
            }
            
    
            $this->db->group_by('j.id');
            $this->db->order_by('total_students', 'DESC');
            $this->db->order_by('j.id', 'DESC');
    
            if ((int) $limit > 0) {
                $this->db->limit((int) $limit);
            }
    
            $rows = $this->db->get()->result_array();
    
            foreach ($rows as &$r) {
                $r['job_order_id']    = (string)($r['job_order_id'] ?? $r['portal_job_id'] ?? '');
                $r['company_receive'] = (string)($r['company_receive'] ?? '');
                $r['interview_date']  = $this->clean_date($r['interview_date'] ?? '');
                $r['entry_date']      = $this->clean_date($r['entry_date'] ?? '');
                $r['return_date']     = $this->clean_date($r['return_date'] ?? '');
                $r['total_students']  = (int)($r['total_students'] ?? 0);
            }
            unset($r);
            
            if ($scope === 'active') {
                $rows = array_values(array_filter($rows, function ($row) {
                    return $this->is_active_job_order_row($row);
                }));
            }
        }
    
        // =========================
        // 2) Nếu mapping rỗng -> fallback gom từ sinh viên của trường
        // =========================
        /*if (!empty($rows)) {
            return $rows;
        }*/
        
        if ($usedMap) {
            return $rows;
        }
    
        $fallbackYear  = ($scope === '' || $scope === 'year') ? $year : 0;
        $fallbackMonth = ($scope === '') ? $month : 0;
        
        $students = $this->get_students([
            'school' => $school,
            'year'   => $fallbackYear,
            'month'  => $fallbackMonth,
            'status' => '',
            'q'      => '',
            'limit'  => 0,
        ]);
    
        $bucket = [];
    
        foreach ($students as $row) {
            $jobKey = trim((string)($row['job_order_id'] ?? ''));
            if ($jobKey === '') {
                continue;
            }
    
            if (!isset($bucket[$jobKey])) {
                $bucket[$jobKey] = [
                    'job_order_id'    => $jobKey,
                    'company_receive' => (string)($row['company_receive'] ?? ''),
                    'total_students'  => 0,
                    'interview_date'  => '',
                    'entry_date'      => '',
                    'return_date'     => '',
                ];
            }
            
            if ($bucket[$jobKey]['company_receive'] === '' && !empty($row['company_receive'])) {
                $bucket[$jobKey]['company_receive'] = (string)$row['company_receive'];
            }
    
            $bucket[$jobKey]['total_students']++;
    
            if (!empty($row['interview_date']) && ($bucket[$jobKey]['interview_date'] === '' || strcmp($row['interview_date'], $bucket[$jobKey]['interview_date']) < 0)) {
                $bucket[$jobKey]['interview_date'] = $row['interview_date'];
            }
    
            if (!empty($row['entry_date']) && ($bucket[$jobKey]['entry_date'] === '' || strcmp($row['entry_date'], $bucket[$jobKey]['entry_date']) < 0)) {
                $bucket[$jobKey]['entry_date'] = $row['entry_date'];
            }
    
            if (!empty($row['return_date']) && ($bucket[$jobKey]['return_date'] === '' || strcmp($row['return_date'], $bucket[$jobKey]['return_date']) > 0)) {
                $bucket[$jobKey]['return_date'] = $row['return_date'];
            }
        }
    
        $rows = array_values($bucket);
        
        if ($scope === 'active') {
            $rows = array_values(array_filter($rows, function ($row) {
                return $this->is_active_job_order_row($row);
            }));
        }
    
        usort($rows, function ($a, $b) {
            $cmp = (int)($b['total_students'] ?? 0) <=> (int)($a['total_students'] ?? 0);
            if ($cmp !== 0) return $cmp;
            return strcmp((string)$b['job_order_id'], (string)$a['job_order_id']);
        });
    
        if ((int)$limit > 0) {
            $rows = array_slice($rows, 0, (int)$limit);
        }
    
        return $rows;
    }

    public function get_job_order_detail($job_order_id, $school = '', $school_code = '')
    {
        $jobTbl = $this->table_jobs;
        $mapTbl = db_prefix() . 'internship_job_order_schools';

        if (!$this->db->table_exists($jobTbl) || !$this->db->table_exists($mapTbl)) {
            return null;
        }

        $school      = $this->school_from_input($school);
        $school_code = trim((string) $school_code);

        $this->db->select('j.*, j.id AS portal_job_id, m.school_id AS partner_school_id, m.school_code AS partner_school_code, m.school_name AS partner_school_name, m.sent_at AS sent_to_school_at');
        $this->db->from($jobTbl . ' j');
        $this->db->join($mapTbl . ' m', 'm.job_order_id = j.id AND m.is_active = 1', 'inner');
        $this->db->where('j.id', (int) $job_order_id);

        if ($school_code !== '') {
            $this->db->group_start();
            $this->db->where('m.school_code', $school_code);
            if ($school !== '') {
                $this->db->or_where('LOWER(TRIM(m.school_name)) =', mb_strtolower($school));
            }
            $this->db->group_end();
        } elseif ($school !== '') {
            $this->db->where('LOWER(TRIM(m.school_name)) =', mb_strtolower($school));
        } else {
            return null;
        }

        return $this->db->limit(1)->get()->row_array();
    }
    
    
    public function find_account_for_reset($username, $email)
{
    if (!$this->db->table_exists($this->table_accounts)) {
        return null;
    }

    $emailCol = $this->pick_col($this->table_accounts, ['email'], '');
    if ($emailCol === '') {
        return null;
    }

    return $this->db
        ->where('username', $username)
        ->where($emailCol, $email)
        ->get($this->table_accounts)
        ->row_array();
}

public function save_reset_token($accountId, $token, $expiresAt)
{
    $fields = $this->list_table_fields($this->table_accounts);
    if (!in_array('reset_token', $fields, true) || !in_array('reset_expires_at', $fields, true)) {
        return false;
    }

    return $this->db
        ->where('id', (int) $accountId)
        ->update($this->table_accounts, [
            'reset_token'      => $token,
            'reset_expires_at' => $expiresAt,
        ]);
}

private function normalize_student_status($status)
{
    if (function_exists('im_normalize_status')) {
        return im_normalize_status($status);
    }

    return strtolower(trim((string)$status));
}

private function translate_student_status($status)
{
    return $this->status_label($status);
}
}