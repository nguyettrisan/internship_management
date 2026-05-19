<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Internship_calendar_model extends App_Model
{
    protected $table = 'tblinternship_calendar';

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('internship_management/internship_status');
    }

    /*private function app_status_label($status)
    {
        $status = trim((string)$status);
        if ($status === '') {
            return '';
        }
    
        if (function_exists('im_status_label_vi')) {
            return im_status_label_vi($status);
        }
    
        $map = [
            'docs_preparing'      => 'Đang làm hồ sơ',
            'prepare_documents'   => 'Đang làm hồ sơ',
            'applied'             => 'Đã nộp đơn',
            'interview_scheduled' => 'Phỏng vấn',
            'in_japan'            => 'Đang ở Nhật',
            'returned'            => 'Đã về nước',
            'cancelled'           => 'Đã hủy',
            'canceled'            => 'Đã hủy',
        ];
    
        $key = strtolower(str_replace([' ', '-'], '_', $status));
        return $map[$key] ?? $status;
    }*/
    
    private function app_status_label($status)
    {
        $status = trim((string)$status);
        if ($status === '') {
            return '';
        }
    
        if (function_exists('im_status_label_vi')) {
            return im_status_label_vi($status);
        }
    
        return $status;
    }
    
    /*private function calendar_event_status_label($rawStatus, $eventType)
    {
        $normalized = function_exists('im_normalize_status')
            ? im_normalize_status($rawStatus)
            : strtolower(trim((string)$rawStatus));
    
        $baseLabel = $this->app_status_label($rawStatus);
        $eventType = strtolower(trim((string)$eventType));
    
        // Nếu status hiện tại đã là 1 trạng thái "mạnh" / cuối giai đoạn thì giữ nguyên
        $strongStatuses = [
            'in_japan',
            'returned',
            'cancelled',
            'pass',
            'fail',
            'coe_waiting',
            'pre_departure',
            'processing',
        ];
    
        if (in_array($normalized, $strongStatuses, true)) {
            return $baseLabel;
        }
    
        // Nếu status còn quá sớm nhưng event đã đến giai đoạn sau,
        // thì dùng nhãn hiển thị theo ngữ cảnh event để tránh vô lý trên UI
        if ($eventType === 'return') {
            return 'Sắp về nước';
        }
    
        if ($eventType === 'entry') {
            return 'Sắp nhập cảnh';
        }
    
        if ($eventType === 'interview') {
            return 'Sắp phỏng vấn';
        }
    
        return $baseLabel !== '' ? $baseLabel : 'Chưa cập nhật';
    }*/
    
    private function calendar_event_status_label($rawStatus, $eventType)
    {
        $normalized = function_exists('im_normalize_status')
            ? im_normalize_status($rawStatus)
            : strtolower(trim((string)$rawStatus));
    
        if ($normalized === '' || $normalized === 'not_updated') {
            $eventType = strtolower(trim((string)$eventType));
    
            if ($eventType === 'return') {
                return 'Sắp về nước';
            }
    
            if ($eventType === 'entry') {
                return 'Sắp nhập cảnh';
            }
    
            if ($eventType === 'interview') {
                return 'Sắp phỏng vấn';
            }
    
            return 'Chưa cập nhật';
        }
    
        return function_exists('im_status_label_vi')
            ? im_status_label_vi($normalized)
            : $normalized;
    }

    /* ============================================================
       FULLCALENDAR: GET EVENTS (manual + auto)
    ============================================================ */
    public function get_events($start, $end, $filters = [])
    {
        $start = $this->normalize_date($start);
        $end   = $this->normalize_date($end);

        if (empty($start) || empty($end)) {
            return [];
        }

        $manual = $this->get_manual_events($start, $end, $filters);
        $auto   = $this->get_auto_events($start, $end, $filters);

        return array_merge($manual, $auto);
    }

    /* ============================================================
       MANUAL EVENTS (CRUD)
    ============================================================ */
    public function get_manual_events($start, $end, $filter = [])
    {
        $events = [];

        $this->db->from($this->table);
        $this->db->where('event_date >=', $start);
        $this->db->where('event_date <=', $end);

        if (!empty($filter['event_type'])) {
            $this->db->where('event_type', $filter['event_type']);
        }
        if (!empty($filter['staff_id'])) {
            $this->db->where('staff_id', (int)$filter['staff_id']);
        }
        if (!empty($filter['job_order_id'])) {
            $this->db->where('job_order_id', (int)$filter['job_order_id']);
        }
        if (!empty($filter['student_id'])) {
            $this->db->where('student_id', (int)$filter['student_id']);
        }

        $rows = $this->db->order_by('event_date', 'ASC')->get()->result_array();

        foreach ($rows as $e) {
            /*$events[] = [
                'id'          => 'manual_' . $e['id'],
                'source'      => 'manual',
                'is_auto'     => 0,
                'title'       => $e['title'],
                'start'       => $e['event_date'],
                'color'       => !empty($e['color']) ? $e['color'] : '#2563eb',
                'event_type'  => $e['event_type'],
                'type_text'   => $this->type_text($e['event_type']),
                'description' => $e['description'],
                'staff_id'    => $e['staff_id'],
                'job_order_id'=> $e['job_order_id'],
                'student_id'  => $e['student_id'],
            ];*/
            
            $events[] = [
                'id'            => 'manual_' . $e['id'],
                'source'        => 'manual',
                'is_auto'       => 0,
                'title'         => $e['title'],
                'start'         => $e['event_date'],
                'color'         => !empty($e['color']) ? $e['color'] : '#2563eb',
                'event_type'    => $e['event_type'],
                'type_text'     => $this->type_text($e['event_type']),
                'description'   => $e['description'],
                'staff_id'      => $e['staff_id'],
                'job_order_id'  => $e['job_order_id'],
                'student_id'    => $e['student_id'],
                'student_name'  => '',
                'company_receive'=> '',
                'status_label'  => '',
                'major_name'    => '',
            ];
        }

        return $events;
    }

    public function get_by_id($id)
    {
        $row = $this->db->get_where($this->table, ['id' => (int)$id])->row_array();
        if (!$row) return null;
        return $row;
    }

    public function add($data)
    {
        $data = $this->sanitize_manual_payload($data);
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $data = $this->sanitize_manual_payload($data);
        return $this->db->where('id', (int)$id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', (int)$id)->delete($this->table);
    }

    private function sanitize_manual_payload($data)
    {
        $payload = [
            'title'       => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'event_date'  => $this->normalize_date($data['event_date'] ?? null),
            'event_type'  => $data['event_type'] ?? 'task',
            'color'       => $data['color'] ?? '#2563eb',
        ];

        // Optional mapping fields (nếu có cột)
        foreach (['staff_id', 'job_order_id', 'student_id'] as $k) {
            if (array_key_exists($k, $data)) {
                $payload[$k] = !empty($data[$k]) ? (int)$data[$k] : null;
            }
        }

        return $payload;
    }

    /* ============================================================
       AUTO EVENTS (Job Orders + Applications)
    ============================================================ */
    public function get_auto_events($start, $end, $filters = [])
    {
        $out = [];
    
        $start = $this->normalize_date($start);
        $end   = $this->normalize_date($end);
    
        if (empty($start) || empty($end)) {
            return $out;
        }
    
        $wantedType      = trim((string)($filters['event_type'] ?? ''));
        $wantedJobId     = !empty($filters['job_order_id']) ? (int)$filters['job_order_id'] : 0;
        $wantedStudentId = !empty($filters['student_id']) ? (int)$filters['student_id'] : 0;
    
        $CI = &get_instance();
        $CI->load->model('internship_management/school_portal_model', 'calendar_school_portal_model');
    
        if (
            empty($CI->calendar_school_portal_model) ||
            !method_exists($CI->calendar_school_portal_model, 'get_calendar_events')
        ) {
            return $out;
        }
    
        $portalEvents = $CI->calendar_school_portal_model->get_calendar_events([], 0);
    
        if (!is_array($portalEvents)) {
            return $out;
        }
    
        foreach ($portalEvents as $event) {
            if (!is_array($event)) {
                continue;
            }
    
            if (function_exists('im_calendar_is_cancelled_row') && im_calendar_is_cancelled_row($event)) {
                continue;
            }
    
            $eventDate = $this->normalize_date($event['event_date'] ?? ($event['start'] ?? ''));
    
            if (empty($eventDate)) {
                continue;
            }
    
            if ($eventDate < $start || $eventDate > $end) {
                continue;
            }
    
            $eventType = function_exists('im_calendar_event_type_key')
                ? im_calendar_event_type_key($event['event_type'] ?? '')
                : strtolower(trim((string)($event['event_type'] ?? 'entry')));
    
            if (!in_array($eventType, ['interview', 'entry', 'return'], true)) {
                $eventType = 'entry';
            }
    
            if ($wantedType !== '' && $eventType !== $wantedType) {
                continue;
            }
    
            $jobOrderId = (int)($event['job_order_id'] ?? 0);
            $studentId  = (int)($event['student_id'] ?? ($event['id'] ?? 0));
    
            if ($wantedJobId > 0 && $jobOrderId !== $wantedJobId) {
                continue;
            }
    
            if ($wantedStudentId > 0 && $studentId !== $wantedStudentId) {
                continue;
            }
    
            $studentName = trim((string)($event['student_name'] ?? ($event['full_name'] ?? '')));
            $company     = trim((string)($event['company_receive'] ?? ($event['receiver_company'] ?? '')));
            $majorName   = trim((string)($event['major_name'] ?? ($event['major'] ?? '')));
    
            $rawStatus = trim((string)($event['status'] ?? ''));
            if ($rawStatus === '') {
                $rawStatus = trim((string)($event['status_label'] ?? ''));
            }
    
            $statusLabel = trim((string)($event['status_label'] ?? ''));
            if ($statusLabel === '') {
                $statusLabel = function_exists('im_calendar_status_label')
                    ? im_calendar_status_label($rawStatus)
                    : 'Chưa cập nhật';
            }
    
            $statusClass = trim((string)($event['status_class'] ?? ''));
            if ($statusClass === '') {
                $statusClass = function_exists('im_calendar_status_class')
                    ? im_calendar_status_class($rawStatus)
                    : 'slate';
            }
    
            if ($eventType === 'interview') {
                $titlePrefix = 'PV: ';
                $description = 'Lịch phỏng vấn của học sinh ' . $studentName;
            } elseif ($eventType === 'return') {
                $titlePrefix = 'Về nước: ';
                $description = 'Ngày dự kiến về nước của học sinh ' . $studentName;
            } else {
                $titlePrefix = 'Nhập cảnh: ';
                $description = 'Ngày dự kiến nhập cảnh của học sinh ' . $studentName;
            }
    
            $jobTitlePart = $company !== '' ? ' – ' . $company : '';
    
            $out[] = [
                'id'               => 'auto_' . $eventType . '_app_' . $studentId,
                'source'           => 'auto',
                'is_auto'          => 1,
                'title'            => $titlePrefix . $studentName . $jobTitlePart,
                'start'            => $eventDate,
                'color'            => $this->event_color($eventType),
                'event_type'       => $eventType,
                'type_text'        => $this->type_text($eventType),
                'description'      => $description,
                'job_order_id'     => $jobOrderId,
                'student_id'       => $studentId,
                'staff_id'         => null,
                'student_name'     => $studentName,
                'company_receive'  => $company,
                'status_label'     => $statusLabel,
                'status_class'     => $statusClass,
                'major_name'       => $majorName,
                'province_receive' => (string)($event['province_receive'] ?? ''),
                'company_address'  => (string)($event['company_address'] ?? ''),
                'school_name'      => (string)($event['school_name'] ?? ''),
                'note'             => (string)($event['note'] ?? ''),
            ];
        }
    
        usort($out, function ($a, $b) {
            $cmp = strcmp((string)($a['start'] ?? ''), (string)($b['start'] ?? ''));
    
            if ($cmp !== 0) {
                return $cmp;
            }
    
            return strcmp((string)($a['id'] ?? ''), (string)($b['id'] ?? ''));
        });
    
        return $out;
    }
    /* ============================================================
       AUTO EVENT EDITING
    ============================================================ */
    public function parse_auto_id($auto_id)
    {
        $auto_id = (string)$auto_id;

        /*if (preg_match('/^auto_(interview|entry)_job_(\d+)$/', $auto_id, $m)) {
            return ['kind' => $m[1], 'entity' => 'job', 'id' => (int)$m[2], 'auto_id' => $auto_id];
        }
        if (preg_match('/^auto_return_job_(\d+)$/', $auto_id, $m)) {
            return ['kind' => 'return', 'entity' => 'job', 'id' => (int)$m[1], 'auto_id' => $auto_id];
        }

        if (preg_match('/^auto_return_app_(\d+)$/', $auto_id, $m)) {
            return ['kind' => 'return', 'entity' => 'app', 'id' => (int)$m[1], 'auto_id' => $auto_id];
        }*/
        
        if (preg_match('/^auto_(interview|entry)_job_(\d+)$/', $auto_id, $m)) {
            return ['kind' => $m[1], 'entity' => 'job', 'id' => (int)$m[2], 'auto_id' => $auto_id];
        }
        
        if (preg_match('/^auto_(interview|entry|return)_app_(\d+)$/', $auto_id, $m)) {
            return ['kind' => $m[1], 'entity' => 'app', 'id' => (int)$m[2], 'auto_id' => $auto_id];
        }
        
        if (preg_match('/^auto_return_job_(\d+)$/', $auto_id, $m)) {
            return ['kind' => 'return', 'entity' => 'job', 'id' => (int)$m[1], 'auto_id' => $auto_id];
        }

        return null;
    }

    public function get_auto_event_info($auto_id)
{
    $info = $this->parse_auto_id($auto_id);
    if (!$info) return null;

    $data = [
        'auto_id'     => $auto_id,
        'source'      => 'auto',
        'is_auto'     => 1,
        'event_type'  => $info['kind'], // interview|entry|return
        'type_text'   => $this->type_text($info['kind']),
        'color'       => $this->event_color($info['kind']),
        'job_order_id'=> null,
        'student_id'  => null,
        'staff_id'    => null,
        'title'       => '',
        'event_date'  => date('Y-m-d'),
        'description' => '',
        'computed'    => 0,
    ];

    if ($info['entity'] === 'job') {
        $jobReturnCol = $this->guess_first_existing_column('tblinternship_job_orders', [
            'return_date','return_home_date','home_return_date','back_date','date_return','ngay_ve_nuoc'
        ]);

        $select = 'id, company_name_vi, job_title_vi, interview_date, entry_date';
        if (!empty($jobReturnCol)) {
            $select .= ', ' . $jobReturnCol . ' AS job_return_date';
        } else {
            $select .= ', NULL AS job_return_date';
        }

        $row = $this->db->select($select, false)
            ->from('tblinternship_job_orders')
            ->where('id', (int)$info['id'])
            ->get()->row_array();
        if (!$row) return null;

        $jobTitle = trim(($row['company_name_vi'] ?? '') . ' – ' . ($row['job_title_vi'] ?? ''));
        $data['job_order_id'] = (int)$row['id'];

        if ($info['kind'] === 'interview') {
            $data['title']       = 'PV: ' . $jobTitle;
            $data['event_date']  = $this->normalize_date($row['interview_date'] ?? null) ?: date('Y-m-d');
            $data['description'] = 'Lịch phỏng vấn của đơn tuyển: ' . $jobTitle;
        } elseif ($info['kind'] === 'entry') {
            $data['title']       = 'Nhập cảnh: ' . $jobTitle;
            $data['event_date']  = $this->normalize_date($row['entry_date'] ?? null) ?: date('Y-m-d');
            $data['description'] = 'Ngày dự kiến nhập cảnh của đơn tuyển: ' . $jobTitle;
        } else { // return
            $data['title']       = 'Về nước: ' . $jobTitle;
            $data['event_date']  = $this->normalize_date($row['job_return_date'] ?? null) ?: date('Y-m-d');
            $data['description'] = 'Ngày dự kiến về nước (đơn tuyển): ' . $jobTitle;
        }

        return $data;
    }

    // Applications (return)
    /*if ($info['entity'] === 'app') {
        $monthCols = $this->guess_month_columns('tblinternship_applications');
        $selectCols = ['a.id', 'a.full_name', 'a.return_date', 'a.job_order_id'];
        foreach ($monthCols as $c) $selectCols[] = 'a.' . $c;
        $selectCols[] = 'j.entry_date';
        $selectCols[] = 'j.company_name_vi';
        $selectCols[] = 'j.job_title_vi';

        $row = $this->db->select(implode(',', $selectCols), false)
            ->from('tblinternship_applications AS a')
            ->join('tblinternship_job_orders AS j', 'j.id = a.job_order_id', 'left')
            ->where('a.id', (int)$info['id'])
            ->get()->row_array();
        if (!$row) return null;

        $returnInfo = $this->compute_return_date($row);
        $jobTitle = trim(($row['company_name_vi'] ?? '') . ' – ' . ($row['job_title_vi'] ?? ''));
        $data['student_id']   = (int)$row['id'];
        $data['job_order_id'] = (int)($row['job_order_id'] ?? 0);
        $data['title']        = 'Về nước: ' . ($row['full_name'] ?? 'Ứng viên') . ($jobTitle ? (' – ' . $jobTitle) : '');
        $data['event_date']   = $returnInfo['date'] ?: date('Y-m-d');
        $data['computed']     = $returnInfo['computed'] ? 1 : 0;
        $data['description']  = $row['return_note'] ?? ($returnInfo['computed'] ? 'Tự sinh từ ngày nhập cảnh + số tháng thực tập.' : 'Ngày dự kiến về nước.');

        return $data;
    }*/
    
    if ($info['entity'] === 'app') {
        $monthCols = $this->guess_month_columns('tblinternship_applications');
        /*$selectCols = [
            'a.id',
            'a.full_name',
            'a.job_order_id',
            'a.interview_date',
            'a.entry_date',
            'a.return_date'
        ];*/
        $jobReturnCol = $this->guess_first_existing_column('tblinternship_job_orders', [
            'return_date_vi',
            'return_date',
            'return_home_date',
            'home_return_date',
            'back_date',
            'date_return',
            'ngay_ve_nuoc'
        ]);
        
        $selectCols = [
            'a.id',
            'a.full_name',
            'a.job_order_id',
            'a.interview_date',
            'a.entry_date',
            'a.return_date',
            'j.interview_date AS job_interview_date',
            'j.entry_date AS job_entry_date',
        ];
        
        if (!empty($jobReturnCol)) {
            $selectCols[] = 'j.' . $jobReturnCol . ' AS job_return_date';
        } else {
            $selectCols[] = 'NULL AS job_return_date';
        }
    
        foreach ($monthCols as $c) {
            $selectCols[] = 'a.' . $c;
        }
    
        $selectCols[] = 'j.entry_date AS job_entry_date';
        $selectCols[] = 'j.company_name_vi';
        $selectCols[] = 'j.job_title_vi';
    
        $row = $this->db->select(implode(',', $selectCols), false)
            ->from('tblinternship_applications AS a')
            ->join('tblinternship_job_orders AS j', 'j.id = a.job_order_id', 'left')
            ->where('a.id', (int)$info['id'])
            ->get()->row_array();
        if (!$row) return null;
    
        $jobTitle = trim(($row['company_name_vi'] ?? '') . ' – ' . ($row['job_title_vi'] ?? ''));
        $studentName = (string)($row['full_name'] ?? 'Ứng viên');
    
        $data['student_id']   = (int)$row['id'];
        $data['job_order_id'] = (int)($row['job_order_id'] ?? 0);
    
        if ($info['kind'] === 'interview') {
            $eventDate = $this->normalize_date($row['job_interview_date'] ?? '');
            if (empty($eventDate)) {
                $eventDate = $this->normalize_date($row['interview_date'] ?? '');
            }
        
            $data['title']       = 'PV: ' . $studentName . ($jobTitle ? ' – ' . $jobTitle : '');
            $data['event_date']  = $eventDate ?: date('Y-m-d');
            $data['description'] = 'Lịch phỏng vấn của học sinh ' . $studentName;
        } elseif ($info['kind'] === 'entry') {
            $eventDate = $this->normalize_date($row['job_entry_date'] ?? '');
            if (empty($eventDate)) {
                $eventDate = $this->normalize_date($row['entry_date'] ?? '');
            }
        
            $data['title']       = 'Nhập cảnh: ' . $studentName . ($jobTitle ? ' – ' . $jobTitle : '');
            $data['event_date']  = $eventDate ?: date('Y-m-d');
            $data['description'] = 'Ngày dự kiến nhập cảnh của học sinh ' . $studentName;
        } else {
            $eventDate = $this->normalize_date($row['job_return_date'] ?? '');
        
            if (empty($eventDate)) {
                $returnInfo = $this->compute_return_date([
                    'return_date' => $row['return_date'] ?? '',
                    'entry_date'  => $row['job_entry_date'] ?? '',
                ] + $row);
        
                $eventDate = $returnInfo['date'];
                $data['computed'] = $returnInfo['computed'] ? 1 : 0;
            } else {
                $data['computed'] = 0;
            }
        
            $data['title']       = 'Về nước: ' . $studentName . ($jobTitle ? ' – ' . $jobTitle : '');
            $data['event_date']  = $eventDate ?: date('Y-m-d');
            $data['description'] = !empty($data['computed'])
                ? 'Tự sinh từ ngày nhập cảnh + số tháng thực tập.'
                : 'Ngày dự kiến về nước.';
        }
    
        return $data;
    }

    return null;
}

    public function update_auto_event($info, $payload)
    {
        $date = $this->normalize_date($payload['event_date'] ?? null);
        $note = $payload['description'] ?? null;

        if (empty($date)) {
            return ['success' => false, 'message' => 'Ngày không hợp lệ.'];
        }

        $ok = false;

        if (($info['entity'] ?? '') === 'job') {
            $update = [];

            if (($info['kind'] ?? '') === 'interview') {
                $update['interview_date'] = $date;
                if ($this->db->field_exists('interview_note', 'tblinternship_job_orders')) {
                    $update['interview_note'] = $note;
                }
            } elseif (($info['kind'] ?? '') === 'entry') {
                $update['entry_date'] = $date;
                if ($this->db->field_exists('entry_note', 'tblinternship_job_orders')) {
                    $update['entry_note'] = $note;
                }
            } elseif (($info['kind'] ?? '') === 'return') {
                $jobReturnCol = $this->guess_first_existing_column('tblinternship_job_orders', [
                    'return_date','return_home_date','home_return_date','back_date','date_return','ngay_ve_nuoc'
                ]);
                if (!empty($jobReturnCol) && $this->db->field_exists($jobReturnCol, 'tblinternship_job_orders')) {
                    $update[$jobReturnCol] = $date;

                    // Lưu note nếu có cột
                    if ($this->db->field_exists('return_note', 'tblinternship_job_orders')) {
                        $update['return_note'] = $note;
                    } elseif ($this->db->field_exists('job_return_note', 'tblinternship_job_orders')) {
                        $update['job_return_note'] = $note;
                    }
                }
            }

            if (!empty($update)) {
                $ok = $this->db->where('id', (int)($info['id'] ?? 0))->update('tblinternship_job_orders', $update);

                // Nếu vừa cập nhật entry_date, có thể đồng bộ return_date cho các ứng viên thiếu
                if ($ok && (($info['kind'] ?? '') === 'entry')) {
                    $this->sync_missing_return_dates_by_job((int)($info['id'] ?? 0), 500);
                }
            }
        /*} elseif (($info['entity'] ?? '') === 'app') {
            $update = ['return_date' => $date];
            if ($this->db->field_exists('return_note', 'tblinternship_applications')) {
                $update['return_note'] = $note;
            }
            $ok = $this->db->where('id', (int)($info['id'] ?? 0))->update('tblinternship_applications', $update);
        }*/
        
        } elseif (($info['entity'] ?? '') === 'app') {
            $update = [];
        
            if (($info['kind'] ?? '') === 'interview') {
                $update['interview_date'] = $date;
                if ($this->db->field_exists('interview_note', 'tblinternship_applications')) {
                    $update['interview_note'] = $note;
                }
            } elseif (($info['kind'] ?? '') === 'entry') {
                $update['entry_date'] = $date;
                if ($this->db->field_exists('entry_note', 'tblinternship_applications')) {
                    $update['entry_note'] = $note;
                }
            } else {
                $update['return_date'] = $date;
                if ($this->db->field_exists('return_note', 'tblinternship_applications')) {
                    $update['return_note'] = $note;
                }
            }
        
            $ok = !empty($update)
                ? $this->db->where('id', (int)($info['id'] ?? 0))->update('tblinternship_applications', $update)
                : false;
        }

        return [
            'success' => (bool)$ok,
            'message' => $ok ? 'Đã cập nhật lịch tự sinh.' : 'Không thể cập nhật lịch tự sinh.'
        ];
    }

    /* ============================================================
       SYNC: Tự điền return_date nếu đang trống (entry_date + số tháng thực tập)
    ============================================================ */
    public function sync_missing_return_dates($limit = 500)
    {
        $limit = (int)$limit;
        if ($limit <= 0) $limit = 500;

        $monthCols = $this->guess_month_columns('tblinternship_applications');
        $selectCols = ['a.id', 'a.job_order_id', 'a.return_date'];
        foreach ($monthCols as $c) {
            $selectCols[] = 'a.' . $c;
        }
        $selectCols[] = 'j.entry_date';

        $this->db->select(implode(',', $selectCols), false);
        $this->db->from('tblinternship_applications AS a');
        $this->db->join('tblinternship_job_orders AS j', 'j.id = a.job_order_id', 'left');
        $this->db->group_start();
        $this->db->where('a.return_date IS NULL', null, false);
        $this->db->or_where('a.return_date', '');
        $this->db->group_end();
        $this->db->limit($limit);

        $rows = $this->db->get()->result_array();

        $updated = 0;
        foreach ($rows as $r) {
            $info = $this->compute_return_date($r);
            if (!$info['computed'] || empty($info['date'])) {
                continue;
            }

            $ok = $this->db->where('id', (int)$r['id'])->update('tblinternship_applications', [
                'return_date' => $info['date'],
            ]);

            if ($ok) $updated++;
        }

        return $updated;
    }

    private function sync_missing_return_dates_by_job($jobId, $limit = 500)
    {
        $jobId = (int)$jobId;
        if ($jobId <= 0) return 0;

        $limit = (int)$limit;
        if ($limit <= 0) $limit = 500;

        $monthCols = $this->guess_month_columns('tblinternship_applications');
        $selectCols = ['a.id', 'a.job_order_id', 'a.return_date'];
        foreach ($monthCols as $c) {
            $selectCols[] = 'a.' . $c;
        }
        $selectCols[] = 'j.entry_date';

        $this->db->select(implode(',', $selectCols), false);
        $this->db->from('tblinternship_applications AS a');
        $this->db->join('tblinternship_job_orders AS j', 'j.id = a.job_order_id', 'left');
        $this->db->where('a.job_order_id', $jobId);
        $this->db->group_start();
        $this->db->where('a.return_date IS NULL', null, false);
        $this->db->or_where('a.return_date', '');
        $this->db->group_end();
        $this->db->limit($limit);

        $rows = $this->db->get()->result_array();

        $updated = 0;
        foreach ($rows as $r) {
            $info = $this->compute_return_date($r);
            if (!$info['computed'] || empty($info['date'])) {
                continue;
            }
            $ok = $this->db->where('id', (int)$r['id'])->update('tblinternship_applications', [
                'return_date' => $info['date'],
            ]);
            if ($ok) $updated++;
        }

        return $updated;
    }

    /* ============================================================
       Helpers: Return date compute + column guessing
    ============================================================ */
    private function compute_return_date($row)
    {
        // 1) Có sẵn return_date
        $rd = $this->normalize_date($row['return_date'] ?? null);
        if (!empty($rd)) {
            return ['date' => $rd, 'computed' => false];
        }

        // 2) Tự sinh: entry_date + months
        $entry = $this->normalize_date($row['entry_date'] ?? null);
        if (empty($entry)) {
            return ['date' => null, 'computed' => false];
        }

        $months = $this->extract_months($row);
        if ($months <= 0) {
            return ['date' => null, 'computed' => false];
        }

        $dt = date_create($entry);
        if (!$dt) {
            return ['date' => null, 'computed' => false];
        }
        date_add($dt, date_interval_create_from_date_string($months . ' months'));
        $out = $dt->format('Y-m-d');

        return ['date' => $out, 'computed' => true];
    }

    private function extract_months($row)
    {
        $candidates = $this->guess_month_columns('tblinternship_applications');
        foreach ($candidates as $c) {
            if (isset($row[$c]) && $row[$c] !== '' && $row[$c] !== null) {
                $m = (int)$row[$c];
                if ($m > 0) return $m;
            }
        }

        // fallback: một vài key thường gặp dạng camel
        foreach (['internship_months', 'months_internship', 'intern_months', 'so_thang_thuc_tap', 'training_months'] as $k) {
            if (isset($row[$k]) && $row[$k] !== '' && $row[$k] !== null) {
                $m = (int)$row[$k];
                if ($m > 0) return $m;
            }
        }
        return 0;
    }

    private function guess_month_columns($table)
    {
        // Các tên cột hay gặp cho "số tháng thực tập"
        $cands = [
            'internship_months',
            'months_internship',
            'intern_months',
            'so_thang_thuc_tap',
            'training_months',
            'duration_months',
            'internship_duration_months',
        ];

        $ok = [];
        foreach ($cands as $c) {
            if ($this->db->field_exists($c, $table)) {
                $ok[] = $c;
            }
        }
        return $ok;
    }

/* ============================================================
   HELPER: tìm cột tồn tại đầu tiên trong list
============================================================ */
protected function guess_first_existing_column($table, $candidates = [])
{
    if (empty($table) || empty($candidates)) return null;
    foreach ($candidates as $c) {
        if ($this->db->field_exists($c, $table)) {
            return $c;
        }
    }
    return null;
}


    private function normalize_date($value)
    {
        if (empty($value)) return null;
        // Accept both "Y-m-d" and datetime strings
        $d = substr((string)$value, 0, 10);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
            return $d;
        }
        $ts = strtotime((string)$value);
        if ($ts) return date('Y-m-d', $ts);
        return null;
    }

    /*private function type_text($t)
    {
        $map = [
            'interview'       => '📞 Phỏng vấn',
            'entry'           => '🛬 Nhập cảnh',
            'return'          => '🛄 Về nước',
            'task'            => '📌 Công việc nội bộ',
            'partner_meeting' => '🤝 Làm việc đối tác',
        ];
        return $map[$t] ?? 'Khác';
    }*/
    
    private function type_text($t)
    {
        if (function_exists('im_calendar_event_type_label')) {
            return im_calendar_event_type_label($t);
        }
    
        return 'Khác';
    }

    /*private function event_color($t)
    {
        $colors = [
            'interview'       => '#2563eb',
            'entry'           => '#10b981',
            'return'          => '#ef4444',
            'task'            => '#f59e0b',
            'partner_meeting' => '#8b5cf6',
        ];
        return $colors[$t] ?? '#6b7280';
    }*/
    private function event_color($t)
    {
        if (function_exists('im_calendar_event_type_color')) {
            return im_calendar_event_type_color($t);
        }
    
        return '#64748b';
    }

    // Backward compatible (nếu code cũ gọi)
    public function get_range($start, $end, $filter = [])
    {
        return $this->get_events($start, $end, $filter);
    }

    public function sync_application_events($id)
    {
        // Giữ hàm để tránh lỗi nơi khác gọi
        return true;
    }
}
