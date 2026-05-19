<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$CI->load->helper('internship_management/internship_status');
?>

<?php
$events  = is_array($events ?? null) ? $events : [];
$years   = is_array($years ?? null) ? $years : [];
$filters = is_array($filters ?? null) ? $filters : [];

$month   = (int) ($this->input->get('month') ?: date('n'));
$year    = (int) ($this->input->get('year') ?: date('Y'));
$type    = trim((string) $this->input->get('type'));
$q       = trim((string) ($filters['q'] ?? $this->input->get('q')));
$job     = trim((string) $this->input->get('job_order_id'));
$student = trim((string) $this->input->get('student_name'));

if ($month < 1 || $month > 12) { $month = (int) date('n'); }
if ($year < 2000 || $year > 2100) { $year = (int) date('Y'); }

if (!$years) {
    $years = [(int) date('Y')];
}
if (!in_array($year, $years, true)) {
    $years[] = $year;
    rsort($years);
}

if (!function_exists('sp_value')) {
    function sp_value(array $row, array $keys, $default = '')
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && trim((string) $row[$key]) !== '') {
                return trim((string) $row[$key]);
            }
        }
        return $default;
    }
}

if (!function_exists('sp_clean_date')) {
    function sp_clean_date($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '';
        }
        return substr($value, 0, 10);
    }
}

/*if (!function_exists('sp_event_type_label')) {
    function sp_event_type_label($type)
    {
        global $calendar_event_type_map;
        return cal_vi($type, $calendar_event_type_map);
    }
}*/

if (!function_exists('sp_event_type_label')) {
    function sp_event_type_label($type)
    {
        return function_exists('im_calendar_event_type_label')
            ? im_calendar_event_type_label($type)
            : (string)$type;
    }
}
/*if (!function_exists('sp_event_type_class')) {
    function sp_event_type_class($type)
    {
        if ($type === 'return') {
            return 'return';
        }
        if ($type === 'interview') {
            return 'pv';
        }
        return 'entry';
    }
}*/

if (!function_exists('sp_event_type_class')) {
    function sp_event_type_class($type)
    {
        return function_exists('im_calendar_event_type_badge_class')
            ? im_calendar_event_type_badge_class($type)
            : 'entry';
    }
}

/*if (!function_exists('sp_status_class')) {
    function sp_status_class($status)
    {
        $status = strtolower(trim((string) $status));

        if ($status === '') {
            return 'slate';
        }

        if (in_array($status, ['in_japan', 'đang ở nhật'], true)) {
            return 'cyan';
        }

        if (in_array($status, ['docs_preparing', 'prepare_documents', 'đang làm hồ sơ', 'chuẩn bị hồ sơ'], true)) {
            return 'blue';
        }

        if (in_array($status, ['applied', 'processing', 'đã nộp đơn', 'đang xử lý'], true)) {
            return 'amber';
        }

        if (in_array($status, ['interview', 'interviewed', 'interview_scheduled', 'phỏng vấn', 'đã phỏng vấn', 'đã lên lịch phỏng vấn'], true)) {
            return 'amber';
        }

        if (in_array($status, ['pass', 'passed', 'đỗ'], true)) {
            return 'green';
        }

        if (in_array($status, ['returned', 'return', 'đã về nước', 'về nước'], true)) {
            return 'green';
        }

        if (in_array($status, ['cancelled', 'canceled', 'đã hủy', 'fail', 'failed', 'trượt'], true)) {
            return 'slate';
        }

        return 'slate';
    }
}*/

if (!function_exists('sp_status_class')) {
    function sp_status_class($status)
    {
        return function_exists('im_calendar_status_class')
            ? im_calendar_status_class($status)
            : 'slate';
    }
}

/*$normalized = [];
foreach ($events as $event) {
    if (!is_array($event)) {
        continue;
    }

    $eventDate = sp_clean_date(sp_value($event, ['event_date', 'date', 'interview_date', 'entry_date', 'return_date']));
    if ($eventDate === '') {
        continue;
    }

    $eventType = trim((string) sp_value($event, ['event_type', 'type'], 'entry'));
    if (!in_array($eventType, ['interview', 'entry', 'return'], true)) {
        $eventType = 'entry';
    }

   $statusRaw = sp_value($event, ['status', 'status_label']);

$normalized[] = [*/
$normalized = [];
foreach ($events as $event) {
    if (!is_array($event)) {
        continue;
    }

    $eventDate = sp_clean_date(sp_value($event, ['event_date', 'date', 'interview_date', 'entry_date', 'return_date']));
    if ($eventDate === '') {
        continue;
    }

    $eventType = trim((string) sp_value($event, ['event_type', 'type'], 'entry'));
    if (!in_array($eventType, ['interview', 'entry', 'return'], true)) {
        $eventType = 'entry';
    }

    $statusRaw = sp_value($event, ['status', 'status_label']);

    $statusKey = '';
    if (function_exists('im_normalize_dossier_progress')) {
        $statusKey = im_normalize_dossier_progress($statusRaw);
    }

    if ($statusKey !== 'cancelled' && function_exists('im_normalize_status')) {
        $statusKey2 = im_normalize_status($statusRaw);
        if ($statusKey2 === 'cancelled') {
            $statusKey = 'cancelled';
        }
    }

    if ($statusKey === 'cancelled') {
        continue;
    }

    $normalized[] = [
    'event_date'       => $eventDate,
    'event_type'       => $eventType,
    'student_name'     => sp_value($event, ['student_name', 'full_name', 'name']),
    'company_receive'  => sp_value($event, ['company_receive', 'company_name', 'receiver_company']),
    'job_order_id'     => sp_value($event, ['job_order_id', 'job_order', 'order_id']),
    'province_receive' => sp_value($event, ['province_receive', 'receiver_prefecture', 'province', 'prefecture']),
    'company_address'  => sp_value($event, ['company_address', 'receiver_address', 'work_address', 'address', 'office_address']),
    'status_raw'       => $statusRaw,
    //'status_label'     => cal_vi($statusRaw, $status_map),
    'status_label'     => function_exists('im_calendar_status_label') ? im_calendar_status_label($statusRaw) : (string)$statusRaw,
    'status_class'     => function_exists('im_calendar_status_class') ? im_calendar_status_class($statusRaw) : 'slate',
    'school_name'      => sp_value($event, ['school_name', 'school', 'university_name', 'partner_school', 'college_name']),
    'major_name'       => sp_value($event, ['major_name', 'major', 'department', 'faculty_name', 'specialization']),
    'note'             => sp_value($event, ['note', 'notes', 'description']),
];
}

$jobOptions = ['' => 'Tất cả'];
$studentOptions = ['' => 'Tất cả'];

foreach ($normalized as $e) {
    $jid = $e['job_order_id'];
    if ($jid !== '' && !isset($jobOptions[$jid])) {
        $jobOptions[$jid] = $jid;
    }

    $sname = $e['student_name'];
    if ($sname !== '' && !isset($studentOptions[$sname])) {
        $studentOptions[$sname] = $sname;
    }
}
ksort($jobOptions);
ksort($studentOptions);

$filtered = [];
foreach ($normalized as $e) {
    $ts = strtotime($e['event_date']);
    if (!$ts) {
        continue;
    }
    if ((int) date('Y', $ts) !== $year || (int) date('n', $ts) !== $month) {
        continue;
    }
    if ($type !== '' && $e['event_type'] !== $type) {
        continue;
    }
    if ($job !== '' && $e['job_order_id'] !== $job) {
        continue;
    }
    if ($student !== '' && $e['student_name'] !== $student) {
        continue;
    }
    if ($q !== '') {
        $hay = strtolower(trim(implode(' ', [
            $e['student_name'],
            $e['company_receive'],
            $e['job_order_id'],
            $e['province_receive'],
            $e['company_address'],
            $e['status_label'],
            $e['major_name'],
        ])));
        if (strpos($hay, strtolower($q)) === false) {
            continue;
        }
    }
    $filtered[] = $e;
}

usort($filtered, function ($a, $b) {
    $cmp = strcmp($a['event_date'], $b['event_date']);
    if ($cmp !== 0) {
        return $cmp;
    }
    return strcmp($a['student_name'], $b['student_name']);
});

$eventsByDay = [];
$stats = ['entry' => 0, 'return' => 0, 'interview' => 0];
foreach ($filtered as $e) {
    $d = (int) date('j', strtotime($e['event_date']));
    $eventsByDay[$d][] = $e;
    if (isset($stats[$e['event_type']])) {
        $stats[$e['event_type']]++;
    }
}

$firstTs = strtotime(sprintf('%04d-%02d-01', $year, $month));
$daysInMonth = (int) date('t', $firstTs);
$startWeekday = (int) date('N', $firstTs);
$monthNames = [1=>'tháng 1',2=>'tháng 2',3=>'tháng 3',4=>'tháng 4',5=>'tháng 5',6=>'tháng 6',7=>'tháng 7',8=>'tháng 8',9=>'tháng 9',10=>'tháng 10',11=>'tháng 11',12=>'tháng 12'];
$weekdayNames = ['Th 2','Th 3','Th 4','Th 5','Th 6','Th 7','CN'];

$prevTs = strtotime('-1 month', $firstTs);
$nextTs = strtotime('+1 month', $firstTs);
$todayY = (int) date('Y');
$todayM = (int) date('n');
$todayD = (int) date('j');

if (!function_exists('sp_build_qs')) {
    function sp_build_qs($override = [])
    {
        $params = array_merge($_GET, $override);
        foreach ($params as $k => $v) {
            if ($v === '' || $v === null) {
                unset($params[$k]);
            }
        }
        return http_build_query($params);
    }
}

$favicon = base_url('uploads/company/favicon.png');
$titleText = html_escape($title ?? 'Lịch công việc');
$schoolLabel = html_escape($school_name ?? '');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $titleText; ?></title>
<link rel="icon" type="image/png" href="<?php echo $favicon; ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>">
<style>
:root{
    --ifk-navy:#0b2e59;
    --ifk-navy-2:#113d75;
    --ifk-blue:#2d6cdf;
    --ifk-sky:#10a6de;
    --ifk-cyan:#16b8d8;
    --ifk-green:#1fbf85;
    --ifk-red:#ef5a5a;
    --ifk-amber:#f7a622;
    --ifk-bg:#edf3f8;
    --ifk-card:#ffffff;
    --ifk-line:#dde7f1;
    --ifk-line-soft:#ebf1f7;
    --ifk-text:#18324d;
    --ifk-muted:#6b7b8f;
    --ifk-soft:#f7fafe;
    --radius-sm:12px;
    --radius-md:18px;
    --radius-lg:24px;
    --shadow-sm:0 10px 28px rgba(11,46,89,.05);
    --shadow-md:0 18px 40px rgba(11,46,89,.08);
    --shadow-lg:0 28px 60px rgba(11,46,89,.14);
}
*{box-sizing:border-box}
body{
    margin:0;
    color:var(--ifk-text);
    font-family:Arial,Helvetica,sans-serif;
    background:
        radial-gradient(circle at top right, rgba(16,166,222,.14), transparent 22%),
        radial-gradient(circle at bottom left, rgba(45,108,223,.08), transparent 20%),
        linear-gradient(180deg,#f7fbff 0%, var(--ifk-bg) 100%);
}
.shell{max-width:1600px;margin:0 auto;padding:28px 18px 42px}
.top{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:18px}
.title h2{margin:0;color:var(--ifk-navy);font-size:36px;line-height:1.05;font-weight:900;letter-spacing:-.03em}
.sub{margin-top:8px;color:var(--ifk-muted);font-size:14px;line-height:1.55}
.navx{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.btnx{display:inline-flex;align-items:center;justify-content:center;min-width:96px;height:44px;padding:0 16px;border-radius:14px;background:#fff;border:1px solid var(--ifk-line);color:var(--ifk-navy);text-decoration:none;font-weight:800;transition:all .2s ease;box-shadow:0 4px 12px rgba(11,46,89,.03)}
.btnx:hover{text-decoration:none;color:var(--ifk-navy);transform:translateY(-1px);box-shadow:0 10px 18px rgba(11,46,89,.08)}
.btnx.primary{background:linear-gradient(135deg,var(--ifk-navy) 0%, #184f95 100%);border-color:transparent;color:#fff;box-shadow:0 12px 22px rgba(11,46,89,.16)}
.btnx.soft{background:#fff;color:var(--ifk-navy)}
.cardx{background:var(--ifk-card);border:1px solid var(--ifk-line);border-radius:24px;box-shadow:var(--shadow-sm)}
.pad{padding:18px}
.filters{margin-bottom:18px;padding:16px;background:rgba(255,255,255,.94);backdrop-filter:blur(6px)}
.filters label{display:block;margin:0 0 8px;font-size:13px;font-weight:800;color:var(--ifk-navy)}
.filters .form-control{height:48px;border-radius:14px;border:1px solid #d5e1ec;box-shadow:none;color:var(--ifk-text)}
.filters .form-control:focus{border-color:var(--ifk-sky);box-shadow:0 0 0 4px rgba(16,166,222,.10)}
.btn-filter,.btn-reset{width:100%;height:48px;border-radius:14px;font-weight:900;transition:all .2s ease}
.btn-filter{border:none;color:#fff;background:linear-gradient(135deg,var(--ifk-navy) 0%, #17457f 55%, #0f6ea2 100%);box-shadow:0 12px 22px rgba(11,46,89,.15)}
.btn-filter:hover{transform:translateY(-1px);box-shadow:0 16px 26px rgba(11,46,89,.18)}
.btn-reset{border:1px solid var(--ifk-line);background:#fff;color:var(--ifk-navy);display:inline-flex;align-items:center;justify-content:center;text-decoration:none}
.btn-reset:hover{text-decoration:none;color:var(--ifk-navy)}
.hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:18px;margin-bottom:18px}
.hero-left{position:relative;overflow:hidden;min-height:190px;border:none;background:linear-gradient(135deg,#103b73 0%, #194f95 52%, #0f7ab0 100%);color:#fff;box-shadow:var(--shadow-lg)}
.hero-left .hero-blob-1,.hero-left .hero-blob-2{position:absolute;border-radius:999px;pointer-events:none}
.hero-left .hero-blob-1{width:240px;height:240px;top:-80px;right:-30px;background:radial-gradient(circle,rgba(255,255,255,.18) 0%, rgba(255,255,255,0) 72%)}
.hero-left .hero-blob-2{width:200px;height:200px;bottom:-70px;right:50px;background:radial-gradient(circle,rgba(16,166,222,.24) 0%, rgba(16,166,222,0) 75%)}
.hero-inner{position:relative;z-index:2;display:flex;flex-direction:column;justify-content:space-between;height:100%}
.hero-topline{display:inline-flex;align-items:center;width:max-content;padding:8px 12px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.16);font-size:12px;font-weight:900;letter-spacing:.05em;text-transform:uppercase}
.hero-title{margin:16px 0 8px;font-size:34px;line-height:1.05;font-weight:900;letter-spacing:-.03em}
.hero-desc{max-width:720px;font-size:14px;line-height:1.65;color:rgba(255,255,255,.9)}
.hero-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
.hero-chip{display:inline-flex;align-items:center;padding:8px 12px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.16);font-size:12px;font-weight:800}
.hero-right{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.metric-card{padding:18px;border-radius:22px;background:linear-gradient(180deg,#fff 0%, #f6f9fd 100%);border:1px solid var(--ifk-line);box-shadow:var(--shadow-sm);min-height:108px}
.metric-label{font-size:11px;line-height:1.4;color:var(--ifk-muted);text-transform:uppercase;font-weight:900;letter-spacing:.06em}
.metric-value{margin-top:10px;font-size:30px;line-height:1;color:var(--ifk-navy);font-weight:900}
.metric-sub{margin-top:10px;font-size:13px;color:var(--ifk-muted);line-height:1.5}
.calendar-card{overflow:hidden}
.calendar-head{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:16px}
.calendar-head-left,.calendar-head-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.month-title{font-size:30px;font-weight:900;color:var(--ifk-navy);letter-spacing:-.03em;min-width:280px;text-align:center}
.legend{display:flex;gap:16px;flex-wrap:wrap;align-items:center}
.legend span{display:inline-flex;align-items:center;gap:8px;color:#4f6177;font-weight:800;font-size:13px}
.dot{width:11px;height:11px;border-radius:999px;display:inline-block}
.dot.entry{background:var(--ifk-green)}
.dot.return{background:var(--ifk-red)}
.dot.pv{background:var(--ifk-blue)}
.calendar-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));border-top:1px solid var(--ifk-line);border-left:1px solid var(--ifk-line);border-radius:20px;overflow:hidden;background:#fff}
.dow,.day{border-right:1px solid var(--ifk-line);border-bottom:1px solid var(--ifk-line)}
.dow{padding:14px 8px;background:linear-gradient(180deg,#fbfdff 0%, #f4f8fc 100%);text-align:center;font-weight:900;color:#56657a;font-size:13px;text-transform:uppercase;letter-spacing:.04em}
.day{min-height:144px;padding:10px;position:relative;background:#fff;transition:background .18s ease}
.day:hover{background:#f9fcff}
.day.muted{background:#fafcff}
.day.today{background:#f4faff;box-shadow:inset 0 0 0 2px rgba(16,166,222,.14)}
.day-num{position:absolute;right:10px;top:8px;color:#8b99ab;font-size:14px;font-weight:900}
.day.today .day-num{color:var(--ifk-sky)}
.day-events{margin-top:28px;display:flex;flex-direction:column;gap:8px}
.event-btn{display:flex;align-items:flex-start;gap:8px;width:100%;padding:10px 12px;border:none;border-radius:12px;color:#fff;font-size:12px;font-weight:800;line-height:1.35;text-align:left;cursor:pointer;box-shadow:0 8px 18px rgba(0,0,0,.08);transition:transform .15s ease, box-shadow .15s ease}
.event-btn:hover{transform:translateY(-1px);box-shadow:0 10px 20px rgba(0,0,0,.12)}
.event-btn .event-pill{display:inline-flex;align-items:center;justify-content:center;min-width:24px;height:24px;border-radius:999px;background:rgba(255,255,255,.18);font-size:11px;font-weight:900;flex:0 0 auto}
.event-btn .event-copy{min-width:0;display:flex;flex-direction:column;gap:2px}
.event-btn .event-title{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.event-btn .event-sub{display:block;font-size:11px;color:rgba(255,255,255,.88);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.event-btn.entry{background:linear-gradient(135deg,#1fbf85 0%, #17a06f 100%)}
.event-btn.return{background:linear-gradient(135deg,#f16b6b 0%, #e65050 100%)}
.event-btn.pv{background:linear-gradient(135deg,#3c73f1 0%, #285fd8 100%)}
.day-more{margin-top:2px;font-size:11px;font-weight:800;color:var(--ifk-muted);padding-left:2px}
.detail-card{margin-top:18px}
.detail-card .head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;margin-bottom:14px}
.detail-card .head h3{margin:0;color:var(--ifk-navy);font-size:22px;line-height:1.15;font-weight:900;letter-spacing:-.02em}
.head-meta{color:var(--ifk-muted);font-size:13px;font-weight:700}
.table-wrap{overflow-x:auto;border-radius:18px}
.mini-table{margin:0;width:100%;background:#fff}
.mini-table thead th{padding:14px 12px;color:var(--ifk-navy);border-bottom:1px solid var(--ifk-line);font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;background:linear-gradient(180deg,#fbfdff 0%, #f5f9fd 100%)}
.mini-table tbody tr{transition:background .18s ease}
.mini-table tbody tr:hover{background:#f8fbff}
.mini-table tbody td{padding:14px 12px;border-top:1px solid var(--ifk-line-soft);vertical-align:top;word-wrap:break-word;overflow-wrap:anywhere}
.student-name{display:block;color:var(--ifk-navy);font-size:14px;line-height:1.4;font-weight:900}
.student-sub{margin-top:4px;color:var(--ifk-muted);font-size:12px;line-height:1.45}
.badge-type,.badge-status{display:inline-flex;align-items:center;justify-content:center;min-height:30px;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:800;line-height:1.2;text-align:center;max-width:100%;white-space:normal}
.badge-entry{background:#e7fbf2;color:#168256}
.badge-return{background:#fdeaea;color:#b42318}
.badge-pv{background:#eaf1ff;color:#184b9b}
.status-blue{background:#eaf1ff;color:#184b9b}
.status-cyan{background:#e5f8ff;color:#0d799c}
.status-green{background:#e7fbf2;color:#168256}
.status-amber{background:#fff4df;color:#9a6307}
.status-slate{background:#eef2f7;color:#475569}
.note{color:var(--ifk-muted)}
.empty{padding:42px 14px;text-align:center;color:var(--ifk-muted);font-weight:700}
.modal-pro{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:22px;background:rgba(7,24,46,.45);backdrop-filter:blur(4px);z-index:9999}
.modal-pro.show{display:flex}
.modal-dialog-pro{width:min(760px,100%);background:#fff;border-radius:24px;box-shadow:0 28px 60px rgba(11,46,89,.24);border:1px solid var(--ifk-line);overflow:hidden}
.modal-head-pro{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;padding:18px 20px;border-bottom:1px solid var(--ifk-line-soft);background:linear-gradient(180deg,#fbfdff 0%, #f6f9fd 100%)}
.modal-title-wrap{min-width:0}
.modal-title-pro{margin:0;color:var(--ifk-navy);font-size:24px;line-height:1.15;font-weight:900;letter-spacing:-.02em}
.modal-sub-pro{margin-top:6px;color:var(--ifk-muted);font-size:13px;line-height:1.5}
.modal-close-pro{width:40px;height:40px;border:none;border-radius:12px;background:#edf3f8;color:var(--ifk-navy);font-size:24px;line-height:1;cursor:pointer}
.modal-body-pro{padding:20px}
.detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.detail-box{padding:14px 16px;border-radius:18px;background:#f8fbff;border:1px solid var(--ifk-line-soft)}
.detail-label{font-size:11px;line-height:1.4;color:var(--ifk-muted);text-transform:uppercase;font-weight:900;letter-spacing:.06em}
.detail-value{margin-top:8px;color:var(--ifk-navy);font-size:15px;line-height:1.55;font-weight:800;word-break:break-word}
.detail-box.full{grid-column:1/-1}
.modal-foot-pro{display:flex;justify-content:flex-end;gap:10px;padding:0 20px 20px}
@media (max-width:1280px){.hero-grid{grid-template-columns:1fr}.month-title{min-width:unset}}
@media (max-width:980px){.title h2{font-size:30px}.calendar-head{align-items:flex-start}.month-title{width:100%;text-align:left;font-size:26px}.day{min-height:120px}.event-btn{padding:9px 10px}}
@media (max-width:768px){.shell{padding-left:12px;padding-right:12px}.title h2{font-size:28px}.hero-right{grid-template-columns:1fr 1fr}.detail-grid{grid-template-columns:1fr}.day{min-height:96px;padding:8px}.day-num{right:8px;top:6px;font-size:13px}.day-events{margin-top:24px}.event-btn .event-sub{display:none}.mini-table thead th,.mini-table tbody td{padding:12px 10px}}
@media (max-width:560px){.hero-right{grid-template-columns:1fr}.calendar-head-left,.calendar-head-right{width:100%}.calendar-head-right{justify-content:flex-start}.modal-pro{padding:12px}.modal-dialog-pro{border-radius:18px}.modal-title-pro{font-size:20px}}
</style>
</head>
<body>
<div class="shell">
    <div class="top">
        <div class="title">
            <h2>Lịch Công Việc - Internship Nhật Bản</h2>
            <div class="sub">Hiển thị lịch theo đơn tuyển và sinh viên của trường <?php echo $schoolLabel; ?>, có thể bấm trực tiếp vào từng sự kiện để xem chi tiết nhanh.</div>
        </div>
        <div class="navx">
            <a class="btnx" href="<?php echo site_url('school_portal/dashboard'); ?>">Dashboard</a>
            <a class="btnx" href="<?php echo site_url('school_portal/students'); ?>">Sinh viên</a>
            <a class="btnx primary" href="<?php echo site_url('school_portal/calendar'); ?>">Lịch</a>
            <a class="btnx" href="<?php echo site_url('school_portal/job_orders'); ?>">Đơn tuyển</a>
        </div>
    </div>

    <div class="cardx filters">
        <form method="get" class="row">
            <div class="col-md-2 col-sm-6">
                <label>Loại sự kiện</label>
                <select name="type" class="form-control">
                    <option value="">Tất cả</option>
                    <option value="interview" <?php echo $type==='interview'?'selected':''; ?>>Phỏng vấn</option>
                    <option value="entry" <?php echo $type==='entry'?'selected':''; ?>>Nhập cảnh</option>
                    <option value="return" <?php echo $type==='return'?'selected':''; ?>>Về nước</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label>Năm</label>
                <select name="year" class="form-control">
                    <?php foreach ($years as $y): ?>
                        <option value="<?php echo (int) $y; ?>" <?php echo (int) $year === (int) $y ? 'selected' : ''; ?>><?php echo (int) $y; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label>Tháng</label>
                <select name="month" class="form-control">
                    <?php for ($m=1; $m<=12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $month === $m ? 'selected' : ''; ?>><?php echo $monthNames[$m]; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label>Đơn tuyển</label>
                <select name="job_order_id" class="form-control">
                    <?php foreach ($jobOptions as $k => $v): ?>
                        <option value="<?php echo html_escape($k); ?>" <?php echo $job === (string) $k ? 'selected' : ''; ?>><?php echo html_escape($v); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label>Học sinh</label>
                <select name="student_name" class="form-control">
                    <?php foreach ($studentOptions as $k => $v): ?>
                        <option value="<?php echo html_escape($k); ?>" <?php echo $student === (string) $k ? 'selected' : ''; ?>><?php echo html_escape($v); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-10" style="margin-top:14px;">
                <label>Từ khóa</label>
                <input type="text" name="q" class="form-control" value="<?php echo html_escape($q); ?>" placeholder="Tên sinh viên / công ty / đơn tuyển / trạng thái">
            </div>
            <div class="col-md-2" style="margin-top:38px;display:flex;gap:8px;">
                <button type="submit" class="btn-filter">Lọc dữ liệu</button>
                <a class="btn-reset" href="<?php echo site_url('school_portal/calendar'); ?>">Reset</a>
            </div>
        </form>
    </div>

    <div class="hero-grid">
        <div class="cardx pad hero-left">
            <div class="hero-blob-1"></div>
            <div class="hero-blob-2"></div>
            <div class="hero-inner">
                <div>
                    <div class="hero-topline">IFK Calendar Pro</div>
                    <div class="hero-title"><?php echo $monthNames[$month] . ' năm ' . $year; ?></div>
                    <div class="hero-desc">Lịch công việc hiển thị theo tháng, phân loại rõ phỏng vấn, nhập cảnh và về nước. Bạn có thể bấm trực tiếp vào từng sự kiện trong ô ngày hoặc trong bảng chi tiết để mở popup xem đầy đủ thông tin.</div>
                </div>
                <div class="hero-actions">
                    <span class="hero-chip">Trường: <?php echo $schoolLabel !== '' ? $schoolLabel : 'Tất cả'; ?></span>
                    <span class="hero-chip">Tổng sự kiện: <?php echo count($filtered); ?></span>
                    <span class="hero-chip">Tháng đang xem: <?php echo $month . '/' . $year; ?></span>
                </div>
            </div>
        </div>

        <div class="hero-right">
            <div class="metric-card">
                <div class="metric-label">Tổng sự kiện</div>
                <div class="metric-value"><?php echo count($filtered); ?></div>
                <div class="metric-sub">Tổng lịch phù hợp bộ lọc hiện tại.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Phỏng vấn</div>
                <div class="metric-value"><?php echo (int) $stats['interview']; ?></div>
                <div class="metric-sub">Lịch PV trong tháng.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Nhập cảnh</div>
                <div class="metric-value"><?php echo (int) $stats['entry']; ?></div>
                <div class="metric-sub">Lịch xuất cảnh sang Nhật.</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Về nước</div>
                <div class="metric-value"><?php echo (int) $stats['return']; ?></div>
                <div class="metric-sub">Lịch hoàn tất chương trình.</div>
            </div>
        </div>
    </div>

    <div class="cardx pad calendar-card">
        <div class="calendar-head">
            <div class="calendar-head-left navx">
                <a class="btnx primary" href="?<?php echo sp_build_qs(['year'=>date('Y', $prevTs), 'month'=>date('n', $prevTs)]); ?>">&lsaquo; Tháng trước</a>
                <a class="btnx primary" href="?<?php echo sp_build_qs(['year'=>date('Y', $nextTs), 'month'=>date('n', $nextTs)]); ?>">Tháng sau &rsaquo;</a>
                <a class="btnx soft" href="?<?php echo sp_build_qs(['year'=>date('Y'), 'month'=>date('n')]); ?>">Hôm nay</a>
            </div>
            <div class="month-title"><?php echo $monthNames[$month] . ' năm ' . $year; ?></div>
            <div class="calendar-head-right">
                <div class="legend">
                    <span><i class="dot pv"></i> Phỏng vấn</span>
                    <span><i class="dot entry"></i> Nhập cảnh</span>
                    <span><i class="dot return"></i> Về nước</span>
                </div>
            </div>
        </div>

        <div class="calendar-grid">
            <?php foreach ($weekdayNames as $name): ?>
                <div class="dow"><?php echo $name; ?></div>
            <?php endforeach; ?>

            <?php
            $cell = 1;
            for ($i = 1; $i < $startWeekday; $i++, $cell++) {
                echo '<div class="day muted"></div>';
            }
            for ($day = 1; $day <= $daysInMonth; $day++, $cell++) {
                $isToday = ($todayY === $year && $todayM === $month && $todayD === $day);
                echo '<div class="day'.($isToday ? ' today' : '').'">';
                echo '<div class="day-num">'.$day.'</div>';
                echo '<div class="day-events">';
                if (!empty($eventsByDay[$day])) {
                    $dayEvents = $eventsByDay[$day];
                    $visible = array_slice($dayEvents, 0, 3);
                    foreach ($visible as $ev) {
                        $cls = sp_event_type_class($ev['event_type']);
                        $typeLabel = sp_event_type_label($ev['event_type']);
                        $mainLabel = trim((string) ($ev['company_receive'] !== '' ? $ev['company_receive'] : ($ev['job_order_id'] !== '' ? 'Đơn '.$ev['job_order_id'] : $typeLabel)));
                        $title = trim((string) ($ev['student_name'] !== '' ? $ev['student_name'] : $mainLabel));
                        $sub = trim((string) ($ev['job_order_id'] !== '' ? 'Đơn '.$ev['job_order_id'] : $typeLabel));
                       echo '<button type="button" class="event-btn '.$cls.' js-event-detail" '
    .'data-date="'.html_escape($ev['event_date']).'" '
    .'data-type="'.html_escape($typeLabel).'" '
    .'data-student="'.html_escape($ev['student_name']).'" '
    .'data-company="'.html_escape($ev['company_receive']).'" '
    .'data-job="'.html_escape($ev['job_order_id']).'" '
    .'data-province="'.html_escape($ev['province_receive']).'" '
    .'data-address="'.html_escape($ev['company_address']).'" '
    .'data-status="'.html_escape($ev['status_label']).'" '
    .'data-school="'.html_escape($ev['school_name']).'" '
    .'data-major="'.html_escape($ev['major_name']).'" '
    .'data-note="'.html_escape($ev['note']).'">';
                        echo '<span class="event-pill">'.html_escape(mb_substr($typeLabel, 0, 1)).'</span>';
                        echo '<span class="event-copy">';
                        echo '<span class="event-title">'.html_escape($title).'</span>';
                        echo '<span class="event-sub">'.html_escape($mainLabel.' · '.$sub).'</span>';
                        echo '</span>';
                        echo '</button>';
                    }
                    if (count($dayEvents) > 3) {
                        echo '<div class="day-more">+'.(count($dayEvents) - 3).' sự kiện khác</div>';
                    }
                }
                echo '</div>';
                echo '</div>';
            }
            while ((($cell - 1) % 7) !== 0) {
                echo '<div class="day muted"></div>';
                $cell++;
            }
            ?>
        </div>
    </div>

    <div class="cardx pad detail-card">
        <div class="head">
            <div>
                <h3>Chi tiết lịch trong tháng</h3>
                <div class="head-meta">Bấm nút <strong>Xem chi tiết</strong> hoặc bấm trực tiếp vào sự kiện trong lịch để mở popup.</div>
            </div>
            <div class="note"><?php echo count($filtered); ?> sự kiện</div>
        </div>
        <div class="table-wrap">
            <table class="table mini-table">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Loại</th>
                        <th>Học sinh</th>
                        <th>Đơn tuyển</th>
                        <th>Công ty tiếp nhận</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($filtered): foreach ($filtered as $e): ?>
                    <tr>
                        <td><?php echo html_escape($e['event_date']); ?></td>
                        <td>
                            <?php if ($e['event_type'] === 'return'): ?>
                                <span class="badge-type badge-return">Về nước</span>
                            <?php elseif ($e['event_type'] === 'interview'): ?>
                                <span class="badge-type badge-pv">Phỏng vấn</span>
                            <?php else: ?>
                                <span class="badge-type badge-entry">Nhập cảnh</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="student-name"><?php echo html_escape($e['student_name']); ?></span>
                            <div class="student-sub"><?php echo html_escape($e['major_name'] !== '' ? $e['major_name'] : '—'); ?></div>
                        </td>
                        <td><?php echo html_escape($e['job_order_id'] !== '' ? $e['job_order_id'] : '—'); ?></td>
                        <td><?php echo html_escape($e['company_receive'] !== '' ? $e['company_receive'] : '—'); ?></td>
                        <td>
                            <span class="badge-status status-<?php echo html_escape(sp_status_class($e['status_label'])); ?>"><?php echo html_escape($e['status_label'] !== '' ? $e['status_label'] : 'Chưa cập nhật'); ?></span>
                        </td>
                        <td>
                            <button type="button" class="btnx js-event-detail"
                                data-date="<?php echo html_escape($e['event_date']); ?>"
                                data-type="<?php echo html_escape(sp_event_type_label($e['event_type'])); ?>"
                                data-student="<?php echo html_escape($e['student_name']); ?>"
                                data-company="<?php echo html_escape($e['company_receive']); ?>"
                                data-job="<?php echo html_escape($e['job_order_id']); ?>"
                                data-province="<?php echo html_escape($e['province_receive']); ?>"
                                data-address="<?php echo html_escape($e['company_address']); ?>"
                                data-status="<?php echo html_escape($e['status_label']); ?>"
                                data-school="<?php echo html_escape($e['school_name']); ?>"
                                data-major="<?php echo html_escape($e['major_name']); ?>"
                                data-note="<?php echo html_escape($e['note']); ?>">Xem chi tiết</button>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="7" class="empty">Không có lịch phù hợp trong tháng này.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-pro" id="eventDetailModal" aria-hidden="true">
    <div class="modal-dialog-pro" role="dialog" aria-modal="true" aria-labelledby="eventDetailTitle">
        <div class="modal-head-pro">
            <div class="modal-title-wrap">
                <h3 class="modal-title-pro" id="eventDetailTitle">Chi tiết lịch</h3>
                <div class="modal-sub-pro" id="eventDetailSub">Thông tin chi tiết sự kiện</div>
            </div>
            <button type="button" class="modal-close-pro" id="modalCloseBtn" aria-label="Đóng">×</button>
        </div>
        <div class="modal-body-pro">
            <div class="detail-grid">
                <div class="detail-box">
                    <div class="detail-label">Loại sự kiện</div>
                    <div class="detail-value" id="detailType">—</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Ngày</div>
                    <div class="detail-value" id="detailDate">—</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Học sinh</div>
                    <div class="detail-value" id="detailStudent">—</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Trường / ngành</div>
                    <div class="detail-value" id="detailSchoolMajor">—</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Đơn tuyển</div>
                    <div class="detail-value" id="detailJob">—</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Địa chỉ đơn vị tuyển dụng</div>
                    <div class="detail-value" id="detailAddress">—</div>
                </div>
                <div class="detail-box full">
                    <div class="detail-label">Công ty tiếp nhận</div>
                    <div class="detail-value" id="detailCompany">—</div>
                </div>
                <div class="detail-box">
                    <div class="detail-label">Trạng thái</div>
                    <div class="detail-value" id="detailStatus">—</div>
                </div>
                <div class="detail-box full">
                    <div class="detail-label">Ghi chú</div>
                    <div class="detail-value" id="detailNote">—</div>
                </div>
            </div>
        </div>
        <div class="modal-foot-pro">
            <button type="button" class="btnx primary" id="modalCloseBtn2">Đóng</button>
        </div>
    </div>
</div>

<script>
(function(){
    var modal = document.getElementById('eventDetailModal');
    var closeBtn = document.getElementById('modalCloseBtn');
    var closeBtn2 = document.getElementById('modalCloseBtn2');
    var titleEl = document.getElementById('eventDetailTitle');
    var subEl = document.getElementById('eventDetailSub');

    var fieldMap = {
        detailType: 'type',
        detailDate: 'date',
        detailStudent: 'student',
        detailJob: 'job',
        detailAddress: 'address',
        detailCompany: 'company',
        detailStatus: 'status',
        detailNote: 'note'
    };

    function safe(value){
        return value && String(value).trim() !== '' ? String(value).trim() : '—';
    }

    function openModal(data){
        titleEl.textContent = safe(data.type) + ' - ' + safe(data.student);
        subEl.textContent = 'Ngày ' + safe(data.date) + ' | Đơn tuyển ' + safe(data.job);

        Object.keys(fieldMap).forEach(function(id){
            var el = document.getElementById(id);
            if (el) {
                el.textContent = safe(data[fieldMap[id]]);
            }
        });

        var schoolMajor = [safe(data.school), safe(data.major)].filter(function(v){ return v !== '—'; }).join(' / ');
        document.getElementById('detailSchoolMajor').textContent = schoolMajor || '—';

        var addressText = safe(data.address);
        if (addressText === '—') {
            addressText = safe(data.province);
        }
        document.getElementById('detailAddress').textContent = addressText;

        modal.classList.add('show');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(){
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.js-event-detail').forEach(function(btn){
        btn.addEventListener('click', function(){
            openModal({
                type: this.getAttribute('data-type'),
                date: this.getAttribute('data-date'),
                student: this.getAttribute('data-student'),
                company: this.getAttribute('data-company'),
                job: this.getAttribute('data-job'),
                province: this.getAttribute('data-province'),
                address: this.getAttribute('data-address'),
                status: this.getAttribute('data-status'),
                school: this.getAttribute('data-school'),
                major: this.getAttribute('data-major'),
                note: this.getAttribute('data-note')
            });
        });
    });

    closeBtn.addEventListener('click', closeModal);
    closeBtn2.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e){
        if (e.target === modal) {
            closeModal();
        }
    });
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            closeModal();
        }
    });
})();
</script>
</body>
</html>
