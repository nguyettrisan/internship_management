<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$student = $student ?? [];

if (!function_exists('sdv')) {
    function sdv($row, $keys, $fallback = '—')
    {
        foreach ((array)$keys as $key) {
            if (isset($row[$key])) {
                $val = trim((string)$row[$key]);
                if ($val !== '') {
                    return $val;
                }
            }
        }
        return $fallback;
    }
}

if (!function_exists('sd_date')) {
    function sd_date($value)
    {
        $value = trim((string)$value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '—';
        }
        $ts = strtotime($value);
        return $ts ? date('Y-m-d', $ts) : $value;
    }
}

if (!function_exists('sd_text_vi')) {
    function sd_text_vi($value, $type = 'general')
    {
        $raw = trim((string)$value);
        if ($raw === '') {
            return '—';
        }

        $normalized = strtolower($raw);
        $normalized = str_replace(['-', ' '], '_', $normalized);

        $map = [
            'prepare_documents'   => 'Chuẩn bị hồ sơ',
            'prepare_document'    => 'Chuẩn bị hồ sơ',
            'prepare_profile'     => 'Chuẩn bị hồ sơ',
            'document_preparing'  => 'Chuẩn bị hồ sơ',
            'processing'          => 'Đang xử lý',
            'in_progress'         => 'Đang xử lý',
            'waiting'             => 'Đang chờ',
            'pending'             => 'Chờ xử lý',
            'interview'           => 'Phỏng vấn',
            'interviewing'        => 'Đang phỏng vấn',
            'pass'                => 'Đậu phỏng vấn',
            'passed'              => 'Đậu phỏng vấn',
            'fail'                => 'Rớt phỏng vấn',
            'failed'              => 'Rớt phỏng vấn',
            'visa'                => 'Xin visa',
            'visa_processing'     => 'Đang xin visa',
            'pre_departure'       => 'Chuẩn bị xuất cảnh',
            'pre_departure_stage' => 'Chuẩn bị xuất cảnh',
            'pre_departure_phase' => 'Chuẩn bị xuất cảnh',
            'coe_waiting'         => 'Chờ COE',
            'waiting_coe'         => 'Chờ COE',
            'coe_issued'          => 'Đã có COE',
            'departed'            => 'Đã xuất cảnh',
            'arrived_japan'       => 'Đang ở Nhật',
            'in_japan'            => 'Đang ở Nhật',
            'returned'            => 'Đã về nước',
            'return_home'         => 'Đã về nước',
            'male'                => 'Nam',
            'female'              => 'Nữ',
            'other'               => 'Khác',
            'none'                => 'Chưa cập nhật',
            'null'                => 'Chưa cập nhật',
            'n_a'                 => 'Chưa cập nhật',
            'na'                  => 'Chưa cập nhật',
            'not_available'       => 'Chưa cập nhật',
            'yes'                 => 'Có',
            'no'                  => 'Không',
            'ok'                  => 'Đạt',
        ];

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        if ($normalized === '—') {
            return '—';
        }

        if ($type === 'status' || $type === 'progress' || $type === 'result') {
            return ucwords(str_replace('_', ' ', $normalized));
        }

        return $raw;
    }
}

$name            = sdv($student, ['student_name', 'full_name', 'name']);
$school          = sdv($student, ['school_name', 'school', 'university_name', 'partner_school', 'college_name']);
$major           = sdv($student, ['major_name', 'major', 'department', 'faculty_name', 'specialization']);
$company         = sdv($student, ['company_receive', 'company_name', 'receiver_company', 'accept_company', 'company']);
$jobOrder        = sdv($student, ['job_order_id', 'job_order', 'order_no']);
$statusRaw       = sdv($student, ['status_label', 'status_name', 'status']);
$status          = sd_text_vi($statusRaw, 'status');
$entryDate       = sd_date(sdv($student, ['entry_date', 'date_of_entry', 'entry_at'], ''));
$returnDate      = sd_date(sdv($student, ['return_date', 'date_of_return', 'return_at'], ''));
$interviewDate   = sd_date(sdv($student, ['interview_date', 'pv_date'], ''));
$email           = sdv($student, ['email', 'student_email']);
$phone           = sdv($student, ['phone_student', 'phone', 'mobile', 'student_phone']);
$parentPhone     = sdv($student, ['phone_parent', 'parent_phone', 'guardian_phone']);
$birthday        = sd_date(sdv($student, ['birthday', 'birth_date', 'dob'], ''));
$english         = sd_text_vi(sdv($student, ['english_level', 'english'], '—'));
$japanese        = sd_text_vi(sdv($student, ['japanese_level', 'japanese'], '—'));
$interviewResult = sd_text_vi(sdv($student, ['interview_result', 'pv_result'], '—'), 'result');
$dossier         = sd_text_vi(sdv($student, ['dossier_progress', 'progress_label', 'progress', 'progress_steps'], '—'), 'progress');
$staff           = sdv($student, ['staff_in_charge', 'care_staff', 'staff_name']);
$address         = sdv($student, ['address', 'student_address', 'contact_address']);
$province        = sdv($student, ['province', 'city', 'hometown']);
$gender          = sd_text_vi(sdv($student, ['gender', 'sex'], '—'));
$noteRaw         = sdv($student, ['note', 'notes', 'remark'], '');
$note            = ($noteRaw === '' || strtolower(trim($noteRaw)) === 'none') ? '—' : $noteRaw;

$statusClass = 'slate';
$statusLc = function_exists('mb_strtolower') ? mb_strtolower($status, 'UTF-8') : strtolower($status);
if (strpos($statusLc, 'nhật') !== false || strpos($statusLc, 'đang ở nhật') !== false) {
    $statusClass = 'cyan';
} elseif (strpos($statusLc, 'chuẩn bị xuất cảnh') !== false || strpos($statusLc, 'hồ sơ') !== false || strpos($statusLc, 'đang xử lý') !== false) {
    $statusClass = 'blue';
} elseif (strpos($statusLc, 'chờ coe') !== false || strpos($statusLc, 'coe') !== false) {
    $statusClass = 'blue';
} elseif (strpos($statusLc, 'về nước') !== false || strpos($statusLc, 'chưa cập nhật') !== false) {
    $statusClass = 'slate';
} elseif (strpos($statusLc, 'rớt') !== false || strpos($statusLc, 'hủy') !== false) {
    $statusClass = 'red';
} elseif (strpos($statusLc, 'đậu') !== false || strpos($statusLc, 'đạt') !== false || strpos($statusLc, 'xuất cảnh') !== false || strpos($statusLc, 'đã có coe') !== false) {
    $statusClass = 'green';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/png" href="<?php echo base_url('uploads/company/favicon.png'); ?>">
<title><?php echo html_escape($title ?? 'Chi tiết sinh viên'); ?></title>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>">
<style>
:root{
    --ifk-navy:#0b2e59;
    --ifk-navy-2:#18457a;
    --ifk-blue:#2d6cdf;
    --ifk-sky:#10a6de;
    --ifk-bg:#eef3f8;
    --ifk-card:#ffffff;
    --ifk-line:#e1eaf3;
    --ifk-line-soft:#edf2f7;
    --ifk-text:#18324d;
    --ifk-muted:#6b7b8f;
    --ifk-soft:#f7fafe;
    --ifk-shadow:0 18px 44px rgba(11,46,89,.08);
}
*{box-sizing:border-box}
body{margin:0;background:radial-gradient(circle at top right, rgba(16,166,222,.12), transparent 20%),radial-gradient(circle at bottom left, rgba(45,108,223,.08), transparent 24%),var(--ifk-bg);color:var(--ifk-text);font-family:Arial,Helvetica,sans-serif}
.shell{max-width:1360px;margin:0 auto;padding:28px 18px 40px}
.top{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:18px}
.title h2{margin:0;color:var(--ifk-navy);font-size:40px;line-height:1.05;font-weight:900;letter-spacing:-.03em}
.sub{margin-top:8px;color:var(--ifk-muted);font-size:14px;line-height:1.5}
.navx{display:flex;gap:10px;flex-wrap:wrap}
.btnx{display:inline-flex;align-items:center;justify-content:center;height:46px;padding:0 18px;border-radius:14px;background:#fff;border:1px solid var(--ifk-line);color:var(--ifk-navy);text-decoration:none;font-weight:800;box-shadow:0 6px 14px rgba(11,46,89,.05);transition:.2s ease}
.btnx:hover{color:var(--ifk-navy);text-decoration:none;transform:translateY(-1px);box-shadow:0 10px 18px rgba(11,46,89,.08)}
.btnx.primary{background:linear-gradient(135deg,var(--ifk-navy),var(--ifk-navy-2));border-color:transparent;color:#fff}
.cardx{background:var(--ifk-card);border:1px solid var(--ifk-line);border-radius:24px;box-shadow:var(--ifk-shadow)}
.pad{padding:22px}
.hero-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:18px;margin-bottom:18px}
.hero-card{position:relative;overflow:hidden;border:none;border-radius:26px;padding:28px;background:linear-gradient(135deg,#103b73 0%,#194f95 55%,#0f7ab0 100%);color:#fff;box-shadow:0 24px 54px rgba(11,46,89,.18)}
.hero-card:before,.hero-card:after{content:"";position:absolute;border-radius:50%;pointer-events:none}
.hero-card:before{width:260px;height:260px;right:-70px;top:-60px;background:radial-gradient(circle, rgba(255,255,255,.18) 0%, rgba(255,255,255,0) 70%)}
.hero-card:after{width:220px;height:220px;right:80px;bottom:-90px;background:radial-gradient(circle, rgba(16,166,222,.24) 0%, rgba(16,166,222,0) 75%)}
.hero-kicker{position:relative;z-index:1;font-size:12px;font-weight:900;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.78)}
.hero-main{position:relative;z-index:1;display:grid;grid-template-columns:minmax(120px,auto) 36px minmax(120px,auto);align-items:end;column-gap:12px;margin-top:18px;max-width:100%}
.hero-brand,.hero-school{font-size:48px;line-height:1;font-weight:900;letter-spacing:-.02em;color:#fff;white-space:nowrap}
.hero-brand{text-align:right;color:#dbeaff}
.hero-x{font-size:22px;line-height:1;font-weight:900;color:rgba(255,255,255,.84);text-align:center;padding-bottom:6px}
.hero-school{text-align:left}
.hero-desc{position:relative;z-index:1;margin-top:16px;max-width:660px;color:rgba(255,255,255,.9);font-size:15px;line-height:1.65}
.hero-tags{position:relative;z-index:1;display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
.hero-tags span{display:inline-flex;align-items:center;padding:8px 13px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.16);font-size:12px;font-weight:800}
.summary-card{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;padding:22px}
.mini-kpi{background:linear-gradient(180deg,#fbfdff 0%,#f4f8fc 100%);border:1px solid var(--ifk-line);border-radius:18px;padding:16px;display:flex;flex-direction:column;justify-content:space-between;min-height:112px}
.mini-kpi .k{font-size:11px;color:var(--ifk-muted);font-weight:900;text-transform:uppercase;letter-spacing:.06em}
.mini-kpi .v{margin-top:10px;font-size:20px;line-height:1.3;color:var(--ifk-navy);font-weight:900;word-break:break-word}
.section{margin-bottom:18px}
.section-head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;margin-bottom:12px}
.section-title{margin:0;color:var(--ifk-navy);font-size:24px;line-height:1.15;font-weight:900;letter-spacing:-.02em}
.section-sub{color:var(--ifk-muted);font-size:13px;font-weight:700}
.grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
.item{background:var(--ifk-soft);border:1px solid var(--ifk-line);border-radius:18px;padding:16px;min-height:106px}
.item.wide{grid-column:span 2}
.label{font-size:12px;color:var(--ifk-muted);font-weight:900;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px}
.value{font-size:18px;line-height:1.45;font-weight:800;color:var(--ifk-text);word-break:break-word}
.value.muted{color:var(--ifk-muted)}
.status-badge{display:inline-flex;align-items:center;justify-content:center;padding:8px 14px;border-radius:999px;font-size:13px;font-weight:900}
.status-blue{background:#eaf1ff;color:#184b9b}
.status-cyan{background:#e5f8ff;color:#0d799c}
.status-green{background:#e7fbf2;color:#168256}
.status-slate{background:#eef2f7;color:#475569}
.status-red{background:#fdeaea;color:#b42318}
.pre{white-space:pre-wrap;font-family:Consolas,Monaco,monospace;font-size:12px;line-height:1.65;background:#f8fbff;border:1px solid var(--ifk-line);border-radius:18px;padding:16px}
@media (max-width:1180px){.hero-grid{grid-template-columns:1fr}.grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:860px){.title h2{font-size:32px}.hero-main{grid-template-columns:1fr;row-gap:8px;align-items:start}.hero-brand,.hero-school{white-space:normal;text-align:left;font-size:34px}.hero-x{display:none}.grid{grid-template-columns:1fr}.item.wide{grid-column:span 1}.summary-card{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="shell">
    <div class="top">
        <div class="title">
            <h2>Chi tiết sinh viên</h2>
            <div class="sub">Hồ sơ chi tiết của sinh viên thuộc cổng trường đối tác.</div>
        </div>
        <div class="navx">
            <a class="btnx" href="<?php echo site_url('school_portal/students'); ?>">Về danh sách</a>
            <a class="btnx primary" href="<?php echo site_url('school_portal/calendar'); ?>">Xem lịch</a>
        </div>
    </div>

    <div class="hero-grid">
        <div class="hero-card">
            <div class="hero-kicker">IFK School Portal</div>
            <div class="hero-main">
                <div class="hero-brand">IFK</div>
                <div class="hero-x">×</div>
                <div class="hero-school"><?php echo html_escape($school); ?></div>
            </div>
            <div class="hero-desc">Trang hồ sơ sinh viên hiển thị đầy đủ thông tin học tập, đơn tuyển, tiến độ hồ sơ và lịch thực tế để tra cứu nhanh trong hệ thống internship Nhật Bản.</div>
            <div class="hero-tags">
                <span><?php echo html_escape($name); ?></span>
                <span>Đơn tuyển <?php echo html_escape($jobOrder); ?></span>
                <span><?php echo html_escape($status); ?></span>
            </div>
        </div>

        <div class="cardx summary-card">
            <div class="mini-kpi"><div class="k">Họ tên</div><div class="v"><?php echo html_escape($name); ?></div></div>
            <div class="mini-kpi"><div class="k">Ngành</div><div class="v"><?php echo html_escape($major); ?></div></div>
            <div class="mini-kpi"><div class="k">Công ty tiếp nhận</div><div class="v"><?php echo html_escape($company); ?></div></div>
            <div class="mini-kpi"><div class="k">Trạng thái</div><div class="v"><?php echo html_escape($status); ?></div></div>
        </div>
    </div>

    <div class="cardx pad section">
        <div class="section-head"><div><h3 class="section-title">Thông tin hồ sơ</h3><div class="section-sub">Thông tin cơ bản, liên hệ và tiến độ xử lý hồ sơ.</div></div></div>
        <div class="grid">
            <div class="item"><div class="label">Họ tên</div><div class="value"><?php echo html_escape($name); ?></div></div>
            <div class="item"><div class="label">Trường</div><div class="value"><?php echo html_escape($school); ?></div></div>
            <div class="item"><div class="label">Ngành</div><div class="value"><?php echo html_escape($major); ?></div></div>
            <div class="item"><div class="label">Giới tính</div><div class="value"><?php echo html_escape($gender); ?></div></div>
            <div class="item"><div class="label">Email</div><div class="value"><?php echo html_escape($email); ?></div></div>
            <div class="item"><div class="label">Điện thoại</div><div class="value"><?php echo html_escape($phone); ?></div></div>
            <div class="item"><div class="label">Điện thoại phụ huynh</div><div class="value"><?php echo html_escape($parentPhone); ?></div></div>
            <div class="item"><div class="label">Ngày sinh</div><div class="value"><?php echo html_escape($birthday); ?></div></div>
            <div class="item wide"><div class="label">Địa chỉ</div><div class="value"><?php echo html_escape($address); ?></div></div>
            <div class="item"><div class="label">Tỉnh / Thành</div><div class="value"><?php echo html_escape($province); ?></div></div>
            <div class="item"><div class="label">Nhân sự phụ trách</div><div class="value"><?php echo html_escape($staff); ?></div></div>
            <div class="item"><div class="label">Tiến độ hồ sơ</div><div class="value"><?php echo html_escape($dossier); ?></div></div>
        </div>
    </div>

    <div class="cardx pad section">
        <div class="section-head"><div><h3 class="section-title">Thông tin đơn tuyển & lịch</h3><div class="section-sub">Theo dõi đơn tuyển, kết quả phỏng vấn và mốc thời gian thực tế.</div></div></div>
        <div class="grid">
            <div class="item"><div class="label">Đơn tuyển</div><div class="value"><?php echo html_escape($jobOrder); ?></div></div>
            <div class="item wide"><div class="label">Công ty tiếp nhận</div><div class="value"><?php echo html_escape($company); ?></div></div>
            <div class="item"><div class="label">Trạng thái</div><div class="value"><span class="status-badge status-<?php echo html_escape($statusClass); ?>"><?php echo html_escape($status); ?></span></div></div>
            <div class="item"><div class="label">Ngày phỏng vấn</div><div class="value"><?php echo html_escape($interviewDate); ?></div></div>
            <div class="item"><div class="label">Kết quả phỏng vấn</div><div class="value"><?php echo html_escape($interviewResult); ?></div></div>
            <div class="item"><div class="label">Ngày nhập cảnh</div><div class="value"><?php echo html_escape($entryDate); ?></div></div>
            <div class="item"><div class="label">Ngày về nước</div><div class="value"><?php echo html_escape($returnDate); ?></div></div>
            <div class="item"><div class="label">Tiếng Anh</div><div class="value"><?php echo html_escape($english); ?></div></div>
            <div class="item"><div class="label">Tiếng Nhật</div><div class="value"><?php echo html_escape($japanese); ?></div></div>
            <div class="item wide"><div class="label">Ghi chú</div><div class="value <?php echo $note === '—' ? 'muted' : ''; ?>"><?php echo $note === '—' ? '—' : nl2br(html_escape($note)); ?></div></div>
        </div>
    </div>

    <?php if (!empty($student['progress_steps_pretty'])) { ?>
    <div class="cardx pad section"><div class="section-head"><h3 class="section-title">Chi tiết tiến độ</h3></div><div class="pre"><?php echo html_escape($student['progress_steps_pretty']); ?></div></div>
    <?php } ?>
    <?php if (!empty($student['parsed_data_pretty'])) { ?>
    <div class="cardx pad section"><div class="section-head"><h3 class="section-title">Dữ liệu phân tích</h3></div><div class="pre"><?php echo html_escape($student['parsed_data_pretty']); ?></div></div>
    <?php } ?>
</div>
</body>
</html>
