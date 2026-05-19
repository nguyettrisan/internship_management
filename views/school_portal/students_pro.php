<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

/*$status_map = [
    'in_japan'              => 'Đang ở Nhật',
    'docs_preparing'        => 'Đang làm hồ sơ',
    'prepare_documents'     => 'Đang làm hồ sơ',
    'preparing_documents'   => 'Đang làm hồ sơ',
    'document_preparation'  => 'Đang làm hồ sơ',
    'applied'               => 'Đã ứng tuyển',
    'application_submitted' => 'Đã ứng tuyển',
    'interview_scheduled'   => 'Đã lên lịch phỏng vấn',
    'interview'             => 'Phỏng vấn',
    'interviewed'           => 'Đã phỏng vấn',
    'cancelled'             => 'Đã hủy',
    'canceled'              => 'Đã hủy',
    'returned'              => 'Đã về nước',
    'return'                => 'Đã về nước',
    'pass'                  => 'Đỗ',
    'passed'                => 'Đỗ',
    'failed'                => 'Trượt',
    'fail'                  => 'Trượt',
    'processing'            => 'Đang xử lý',
    'pre_departure'         => 'Chuẩn bị xuất cảnh',
    'pre departure'         => 'Chuẩn bị xuất cảnh',
    'departure_preparation' => 'Chuẩn bị xuất cảnh',
    'coe_waiting'           => 'Chờ COE',
    'coe waiting'           => 'Chờ COE',
    'waiting_coe'           => 'Chờ COE',
];
function status_vi($status, $map) {
    $status = strtolower(trim((string)$status));
    return $map[$status] ?? ucwords(str_replace('_', ' ', $status));
}*/
$CI = &get_instance();
$CI->load->helper('internship_management/internship_status');

if (!function_exists('status_vi')) {
    function status_vi($status, $map = [])
    {
        return im_status_label_vi($status);
    }
}

?>
<?php

$filters  = isset($filters) && is_array($filters) ? $filters : [];
$years    = isset($years) && is_array($years) ? $years : [];
$months   = isset($months) && is_array($months) ? $months : [];
$statuses = isset($statuses) && is_array($statuses) ? $statuses : [];
$students = isset($students) && is_array($students) ? $students : [];

$curYear   = (int)($filters['year'] ?? 0);
$curMonth  = (int)($filters['month'] ?? 0);
$curStatus = (string)($filters['status'] ?? '');
$curQ      = trim((string)($filters['keyword'] ?? $filters['q'] ?? ''));
$school    = trim((string)($school_name ?? ''));

if (!function_exists('ifk_pick')) {
    function ifk_pick($row, array $keys, $default = '')
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && trim((string)$row[$key]) !== '') {
                return $row[$key];
            }
        }
        return $default;
    }
}

if (!function_exists('ifk_date')) {
    function ifk_date($value)
    {
        $value = trim((string)$value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '';
        }
        return substr($value, 0, 10);
    }
}

if (!function_exists('ifk_status_badge_class')) {
    function ifk_status_badge_class($value)
    {
        $value = strtolower(trim((string)$value));

        $map = [
            'chuẩn bị hồ sơ'        => 's-blue',
            'prepare_documents'     => 's-blue',
            'docs_preparing'        => 's-blue',
            'preparing_documents'   => 's-blue',
            'document_preparation'  => 's-blue',
            'đang làm hồ sơ'        => 's-blue',
            'pre_departure'         => 's-blue',
            'pre departure'         => 's-blue',
            'departure_preparation' => 's-blue',
            'chuẩn bị xuất cảnh'    => 's-blue',

            'đang xử lý'            => 's-amber',
            'processing'            => 's-amber',
            'applied'               => 's-amber',
            'application_submitted' => 's-amber',
            'đã nộp đơn'            => 's-amber',
            'coe_waiting'           => 's-amber',
            'coe waiting'           => 's-amber',
            'waiting_coe'           => 's-amber',
            'chờ coe'               => 's-amber',

            'phỏng vấn'             => 's-violet',
            'interview'             => 's-violet',
            'interviewed'           => 's-violet',
            'interview_scheduled'   => 's-violet',
            'đã lên lịch phỏng vấn' => 's-violet',

            'đạt'                   => 's-green',
            'pass'                  => 's-green',
            'passed'                => 's-green',
            'đỗ'                    => 's-green',

            'đang ở nhật'           => 's-cyan',
            'in_japan'              => 's-cyan',

            'đã về nước'            => 's-slate',
            'returned'              => 's-slate',
            'return'                => 's-slate',
            'về nước'               => 's-slate',

            'không đạt'             => 's-red',
            'fail'                  => 's-red',
            'failed'                => 's-red',
            'cancelled'             => 's-red',
            'canceled'              => 's-red',
            'đã hủy'                => 's-red',
        ];

        return $map[$value] ?? 's-blue';
    }
}

$totalStudents = count($students);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_escape($title ?? 'Danh sách sinh viên'); ?></title>
    <link rel="icon" type="image/png" href="<?php echo base_url('uploads/company/favicon.png'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>">
    <style>
        :root{
            --ifk-navy:#0b2e59;
            --ifk-blue:#2d6cdf;
            --ifk-sky:#10a6de;
            --ifk-bg:#eef3f8;
            --ifk-card:#ffffff;
            --ifk-line:#e1eaf3;
            --ifk-text:#18324d;
            --ifk-muted:#6b7b8f;
            --ifk-soft:#f7fafe;
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            background:
                radial-gradient(circle at top right, rgba(16,166,222,.14), transparent 22%),
                radial-gradient(circle at bottom left, rgba(45,108,223,.08), transparent 20%),
                var(--ifk-bg);
            color:var(--ifk-text);
            font-family:Arial,Helvetica,sans-serif;
        }
        .shell{
            max-width:1460px;
            margin:0 auto;
            padding:26px 18px 40px;
        }
        .top{
            display:flex;
            justify-content:space-between;
            align-items:flex-end;
            gap:16px;
            flex-wrap:wrap;
            margin-bottom:18px;
        }
        .title h2{
            margin:0;
            color:var(--ifk-navy);
            font-size:35px;
            line-height:1.05;
            font-weight:900;
            letter-spacing:-.02em;
        }
        .sub{
            color:var(--ifk-muted);
            margin-top:6px;
            font-size:14px;
        }
        .navx{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
            align-items:center;
        }
        .btnx{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            min-width:96px;
            padding:11px 16px;
            border-radius:12px;
            background:#fff;
            border:1px solid var(--ifk-line);
            color:var(--ifk-navy);
            text-decoration:none;
            font-weight:800;
            transition:.2s ease;
        }
        .btnx:hover{
            text-decoration:none;
            transform:translateY(-1px);
            box-shadow:0 8px 18px rgba(11,46,89,.07);
        }
        .btnx.primary{
            background:linear-gradient(135deg,var(--ifk-navy),#17457f);
            border-color:transparent;
            color:#fff;
        }
        .cardx{
            background:var(--ifk-card);
            border:1px solid var(--ifk-line);
            border-radius:22px;
            box-shadow:0 10px 28px rgba(11,46,89,.05);
        }
        .pad{padding:18px}
        .filters{
            margin-bottom:18px;
            padding:16px;
        }
        .grid-filter{
            display:grid;
            grid-template-columns:1fr 1fr 1fr 1.2fr .7fr;
            gap:14px;
            align-items:end;
        }
        .field label{
            display:block;
            margin:0 0 8px;
            font-size:13px;
            font-weight:800;
            color:var(--ifk-navy);
        }
        .field .form-control{
            height:46px;
            border-radius:12px;
            border:1px solid #d7e2ec;
            box-shadow:none;
            background:#fff;
        }
        .field .form-control:focus{
            border-color:var(--ifk-sky);
            box-shadow:0 0 0 3px rgba(16,166,222,.12);
        }
        .btn-filter{
            height:46px;
            width:100%;
            border:none;
            border-radius:12px;
            background:linear-gradient(135deg,var(--ifk-navy),#17457f);
            color:#fff;
            font-weight:800;
        }
        .btn-reset{
            height:46px;
            width:100%;
            border-radius:12px;
            background:#fff;
            color:var(--ifk-navy);
            border:1px solid var(--ifk-line);
            font-weight:800;
        }
        .summary{
            display:grid;
            grid-template-columns:1.15fr .85fr;
            gap:18px;
            margin-bottom:18px;
        }
        .hero{
            min-height:130px;
            display:flex;
            justify-content:space-between;
            gap:18px;
            align-items:stretch;
        }
        .hero-left{
            flex:1 1 auto;
        }
        .hero-kicker{
            display:inline-flex;
            padding:7px 11px;
            border-radius:999px;
            background:#eef4ff;
            color:var(--ifk-navy);
            font-size:12px;
            font-weight:900;
        }
        .hero-title{
            margin:14px 0 8px;
            color:var(--ifk-navy);
            font-size:24px;
            font-weight:900;
            line-height:1.15;
        }
        .hero-desc{
            color:var(--ifk-muted);
            font-size:14px;
            line-height:1.5;
            max-width:760px;
        }
        .hero-right{
            width:260px;
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:12px;
            flex:0 0 auto;
        }
        .mini-kpi{
            background:var(--ifk-soft);
            border:1px solid var(--ifk-line);
            border-radius:16px;
            padding:14px;
        }
        .mini-kpi .k{
            font-size:11px;
            color:var(--ifk-muted);
            text-transform:uppercase;
            font-weight:900;
            letter-spacing:.04em;
        }
        .mini-kpi .v{
            margin-top:10px;
            color:var(--ifk-navy);
            font-size:28px;
            line-height:1;
            font-weight:900;
        }
        .legend-card{
            min-height:130px;
        }
        .section-title{
            margin:0 0 12px;
            color:var(--ifk-navy);
            font-size:18px;
            font-weight:900;
        }
        .status-legend{
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:10px 12px;
        }
        .legend-item{
            display:flex;
            align-items:center;
            gap:8px;
            min-width:0;
        }
        .legend-dot{
            width:10px;
            height:10px;
            border-radius:50%;
            flex:0 0 auto;
        }
        .legend-name{
            color:#304a69;
            font-size:13px;
            font-weight:700;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
        }
        .table-card .head{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            margin-bottom:12px;
            flex-wrap:wrap;
        }
        .table-card .head h3{
            margin:0;
            color:var(--ifk-navy);
            font-size:22px;
            font-weight:900;
        }
        .head-meta{
            color:var(--ifk-muted);
            font-size:13px;
            font-weight:700;
        }
        .table-card .table{
            margin:0;
            table-layout:fixed;
        }
        .table-card .table>thead>tr>th{
            color:var(--ifk-navy);
            border-bottom:1px solid var(--ifk-line);
            font-size:12px;
            font-weight:900;
            text-transform:uppercase;
            letter-spacing:.02em;
            white-space:nowrap;
            background:#fbfdff;
        }
        .table-card .table>tbody>tr>td{
            border-top:1px solid #eef3f8;
            vertical-align:top;
            word-wrap:break-word;
            overflow-wrap:anywhere;
        }
        .table-card a{
            color:#1d5fc6;
            font-weight:800;
            text-decoration:none;
        }
        .table-card a:hover{text-decoration:underline}
        .student-name{
            color:var(--ifk-navy);
            font-weight:900;
            display:block;
            line-height:1.35;
        }
        .student-sub{
            margin-top:4px;
            color:var(--ifk-muted);
            font-size:12px;
            line-height:1.4;
        }
        .company-cell{
            line-height:1.45;
        }
        .badge-status{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:6px 10px;
            border-radius:999px;
            font-size:12px;
            font-weight:800;
            line-height:1.2;
            text-align:center;
            max-width:100%;
        }
        .s-blue{background:#eaf1ff;color:#184b9b}
        .s-amber{background:#fff4df;color:#9a6307}
        .s-violet{background:#f1ebff;color:#6d38c6}
        .s-green{background:#e7fbf2;color:#168256}
        .s-cyan{background:#e5f8ff;color:#0d799c}
        .s-slate{background:#eef2f7;color:#475569}
        .s-red{background:#fdeaea;color:#b42318}
        .date-chip{
            display:inline-flex;
            align-items:center;
            padding:6px 9px;
            border-radius:10px;
            background:#f7fafe;
            border:1px solid #e8eef5;
            color:var(--ifk-navy);
            font-size:12px;
            font-weight:800;
            min-height:32px;
        }
        .empty{
            padding:36px 12px;
            text-align:center;
            color:var(--ifk-muted);
            font-weight:700;
        }
        .tips{
            margin-top:12px;
            padding:14px 16px;
            border-radius:16px;
            background:#f8fbff;
            border:1px dashed #dbe6f2;
            color:#48627f;
            font-size:13px;
            line-height:1.55;
        }
        @media (max-width:1280px){
            .grid-filter{grid-template-columns:1fr 1fr 1fr}
            .summary{grid-template-columns:1fr}
        }
        @media (max-width:980px){
            .hero{display:block}
            .hero-right{
                width:auto;
                grid-template-columns:repeat(2,minmax(0,1fr));
                margin-top:14px;
            }
            .status-legend{grid-template-columns:1fr}
            .grid-filter{grid-template-columns:1fr 1fr}
        }
        @media (max-width:760px){
            .shell{padding-left:12px;padding-right:12px}
            .title h2{font-size:30px}
            .grid-filter,.hero-right{grid-template-columns:1fr}
        }
        .ifk-hero-card{
            position:relative;
            overflow:hidden;
            min-height:172px;
            border:none;
            border-radius:22px;
            background:linear-gradient(135deg,#0b2e59 0%, #17457f 48%, #0f6ea2 100%);
            color:#fff;
            box-shadow:0 18px 36px rgba(11,46,89,.18);
        }
        .ifk-hero-wrap{
            position:relative;
            z-index:2;
            display:flex;
            flex-direction:column;
            justify-content:center;
            min-height:136px;
        }
        .ifk-hero-top{
            margin-bottom:12px;
            font-size:12px;
            font-weight:900;
            letter-spacing:.16em;
            text-transform:uppercase;
            color:rgba(255,255,255,.76);
        }
        .ifk-hero-main{
            display:flex;
            align-items:center;
            gap:12px;
            flex-wrap:nowrap;
        }
        .ifk-hero-brand,
        .ifk-hero-school{
            display:block;
            margin:0;
            font-size:32px;
            line-height:1.05;
            font-weight:900;
            letter-spacing:.01em;
            color:#fff;
            text-transform:uppercase;
            text-shadow:0 8px 24px rgba(0,0,0,.14);
            flex:0 0 auto;
        }
        .ifk-hero-brand{
            min-width:auto;
            text-align:left;
            opacity:.98;
        }
        .ifk-hero-school{
            flex:0 0 auto;
        }
        .ifk-hero-x{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:18px;
            height:18px;
            font-size:14px;
            font-weight:900;
            color:rgba(255,255,255,.82);
            transform:translateY(-1px);
        }
        .ifk-hero-desc{
            margin-top:12px;
            max-width:520px;
            font-size:14px;
            line-height:1.55;
            color:rgba(255,255,255,.92);
        }
        .ifk-hero-tags{
            display:flex;
            gap:8px;
            flex-wrap:wrap;
            margin-top:16px;
        }
        .ifk-hero-tags span{
            display:inline-flex;
            align-items:center;
            padding:7px 10px;
            border-radius:999px;
            background:rgba(255,255,255,.12);
            border:1px solid rgba(255,255,255,.18);
            color:#fff;
            font-size:12px;
            font-weight:800;
            backdrop-filter:blur(2px);
        }
        .ifk-hero-bg{
            position:absolute;
            border-radius:50%;
            z-index:1;
            pointer-events:none;
        }
        .ifk-blob-1{
            width:190px;
            height:190px;
            top:-46px;
            right:-26px;
            background:radial-gradient(circle, rgba(255,255,255,.16) 0%, rgba(255,255,255,0) 68%);
        }
        .ifk-blob-2{
            width:170px;
            height:170px;
            bottom:-62px;
            right:88px;
            background:radial-gradient(circle, rgba(16,166,222,.24) 0%, rgba(16,166,222,0) 72%);
        }
        @media (max-width:980px){
            .ifk-hero-brand,
            .ifk-hero-school{
                font-size:28px;
            }
        }
        @media (max-width:760px){
            .ifk-hero-main{
                align-items:flex-start;
                flex-wrap:wrap;
                gap:8px;
            }
            .ifk-hero-brand,
            .ifk-hero-school{
                font-size:24px;
            }
            .ifk-hero-x{
                order:2;
            }
            .ifk-hero-school{
                width:100%;
                flex-basis:100%;
            }
            .ifk-hero-desc{
                font-size:13px;
            }
        }
    </style>
</head>
<body>
<div class="shell">

    <div class="top">
        <div class="title">
            <h2>Danh sách sinh viên - <?php echo html_escape($school); ?></h2>
            <div class="sub">Giao diện portal chuẩn IFK, chỉ hiển thị hồ sơ thuộc trường đang đăng nhập và tối ưu cho tra cứu nhanh.</div>
        </div>
        <div class="navx">
            <a class="btnx" href="<?php echo site_url('school_portal/dashboard'); ?>">Dashboard</a>
            <a class="btnx primary" href="<?php echo site_url('school_portal/students'); ?>">Sinh viên</a>
            <a class="btnx" href="<?php echo site_url('school_portal/calendar'); ?>">Lịch</a>
            <a class="btnx" href="<?php echo site_url('school_portal/job_orders'); ?>">Đơn tuyển</a>
            <a class="btnx" href="<?php echo site_url('school_portal/export_csv'); ?>">Xuất CSV</a>
        </div>
    </div>

    <div class="cardx filters">
        <form method="get" action="<?php echo site_url('school_portal/students'); ?>" class="grid-filter">
            <div class="field">
                <label>Năm</label>
                <select name="year" class="form-control">
                    <option value="0">Tất cả</option>
                    <?php foreach ($years as $y) { $y = (int)$y; ?>
                        <option value="<?php echo $y; ?>" <?php echo $curYear === $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php } ?>
                </select>
            </div>

            <?php if (!empty($months)) { ?>
            <div class="field">
                <label>Tháng</label>
                <select name="month" class="form-control">
                    <?php foreach ($months as $m) { ?>
                        <option value="<?php echo (int)($m['value'] ?? 0); ?>" <?php echo $curMonth === (int)($m['value'] ?? 0) ? 'selected' : ''; ?>>
                            <?php echo html_escape((string)($m['label'] ?? '')); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <?php } ?>

            <div class="field">
                <label>Trạng thái</label>
                <select name="status" class="form-control">
                 <!--<?php foreach ($statuses as $val => $label) {
    $translatedLabel = status_vi((string)$label, $status_map);
    if ($translatedLabel === (string)$label) {
        $translatedLabel = status_vi((string)$val, $status_map);
    }
?>
    <option value="<?php echo html_escape((string)$val); ?>" <?php echo $curStatus === (string)$val ? 'selected' : ''; ?>>
        <?php echo html_escape((string)$translatedLabel); ?>
    </option>
<?php } ?> -->
                <?php foreach ($statuses as $val => $label) {
                    $val = (string)$val;
                    $translatedLabel = ($val === '') ? 'Tất cả' : im_status_label_vi($val);
                ?>
                    <option value="<?php echo html_escape($val); ?>" <?php echo $curStatus === $val ? 'selected' : ''; ?>>
                        <?php echo html_escape($translatedLabel); ?>
                    </option>
                <?php } ?>
                
                </select>
            </div>

            <div class="field">
                <label>Từ khóa</label>
                <input type="text" class="form-control" name="q" value="<?php echo html_escape($curQ); ?>" placeholder="Tên / ngành / công ty / đơn tuyển">
            </div>

            <div class="field">
                <label>&nbsp;</label>
                <button type="submit" class="btn-filter">Lọc dữ liệu</button>
            </div>
        </form>
    </div>

    <div class="summary">
        <div class="cardx pad hero">
            <div class="hero-left">
                <span class="hero-kicker">IFK SCHOOL PORTAL</span>
                <div class="hero-title">Quản lý hồ sơ sinh viên theo từng trường đối tác</div>
               <div class="hero-desc">
Danh sách hồ sơ sinh viên thuộc trường <?php echo html_escape($school); ?>.
</div>
            </div>
            <div class="hero-right">
                <div class="mini-kpi">
                    <div class="k">Tổng hồ sơ</div>
                    <div class="v"><?php echo (int)$totalStudents; ?></div>
                </div>
                <div class="mini-kpi">
                    <div class="k">Trường hiện tại</div>
                    <div class="v" style="font-size:22px;"><?php echo html_escape($school !== '' ? $school : 'N/A'); ?></div>
                </div>
                <div class="mini-kpi">
                    <div class="k">Đang lọc</div>
                    <div class="v" style="font-size:22px;"><?php echo $curStatus !== '' ? '1' : '0'; ?></div>
                </div>
                <div class="mini-kpi">
                    <div class="k">Từ khóa</div>
                    <div class="v" style="font-size:22px;"><?php echo $curQ !== '' ? '1' : '0'; ?></div>
                </div>
            </div>
        </div>

        <div class="cardx pad legend-card ifk-hero-card">
            <div class="ifk-hero-bg ifk-blob-1"></div>
            <div class="ifk-hero-bg ifk-blob-2"></div>

            <div class="ifk-hero-wrap">
                <div class="ifk-hero-top">IFK School Portal</div>

                <div class="ifk-hero-main">
                    <div class="ifk-hero-brand">IFK</div>
                    <span class="ifk-hero-x">×</span>
                    <div class="ifk-hero-school"><?php echo html_escape($school); ?></div>
                </div>

                <div class="ifk-hero-desc">
                    Hệ thống quản lý sinh viên thực tập Nhật Bản
                </div>

                <div class="ifk-hero-tags">
                    <span>School Portal</span>
                    <span>Student Management</span>
                </div>
            </div>
        </div>
    </div>

    <div class="cardx pad table-card">
        <div class="head">
            <div>
                <h3>Danh sách hồ sơ</h3>
                <div class="head-meta">Đang hiển thị <?php echo (int)$totalStudents; ?> sinh viên của trường <?php echo html_escape($school); ?></div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <colgroup>
                    <col style="width:18%">
                    <col style="width:9%">
                    <col style="width:10%">
                    <col style="width:9%">
                    <col style="width:25%">
                    <col style="width:9%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:10%">
                </colgroup>
                <thead>
                    <tr>
                        <th>Họ tên</th>
                        <th>Trường</th>
                        <th>Ngành</th>
                        <th>Đơn tuyển</th>
                        <th>Công ty tiếp nhận</th>
                        <th>Trạng thái</th>
                        <th>PV</th>
                        <th>Nhập cảnh</th>
                        <th>Về nước</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($students)) { ?>
                    <?php foreach ($students as $sv) {
                        $id         = (int) ifk_pick($sv, ['id'], 0);
                        $name       = (string) ifk_pick($sv, ['student_name','full_name','name'], '');
                        $schoolRow  = (string) ifk_pick($sv, ['school','school_name','university'], '');
                        $major      = (string) ifk_pick($sv, ['major'], '');
                        $jobOrder   = (string) ifk_pick($sv, ['job_order_id','job_name'], '');
                        $company    = (string) ifk_pick($sv, ['company_receive','job_company_receive','company_name','app_company_receive'], '');
                        /*$statusRaw  = (string) ifk_pick($sv, ['status','status_label'], '');
                        $statusLbl  = status_vi($statusRaw, $status_map);
                        $statusKey  = $statusRaw;*/
                        $statusRaw  = (string) ifk_pick($sv, ['status', 'dossier_progress', 'status_label'], '');
                        $statusKey  = im_normalize_dossier_progress($statusRaw);
                        $statusLbl  = im_status_label_vi($statusKey);
                        $interview  = ifk_date(ifk_pick($sv, ['interview_date'], ''));
                        $entry      = ifk_date(ifk_pick($sv, ['entry_date'], ''));
                        $return     = ifk_date(ifk_pick($sv, ['return_date'], ''));
                    ?>
                        <tr>
                            <td>
                                <a class="student-name" href="<?php echo site_url('school_portal/student/' . $id); ?>">
                                    <?php echo html_escape($name); ?>
                                </a>
                                <div class="student-sub">
                                    ID hồ sơ: <?php echo $id; ?>
                                </div>
                            </td>
                            <td><?php echo html_escape($schoolRow); ?></td>
                            <td><?php echo html_escape($major); ?></td>
                            <td><?php echo html_escape($jobOrder); ?></td>
                            <td class="company-cell"><?php echo html_escape($company); ?></td>
                            <td>
                                <span class="badge-status <?php echo ifk_status_badge_class($statusKey); ?>">
                                    <?php echo html_escape($statusLbl); ?>
                                </span>
                            </td>
                            <td><?php echo $interview !== '' ? '<span class="date-chip">'.html_escape($interview).'</span>' : ''; ?></td>
                            <td><?php echo $entry !== '' ? '<span class="date-chip">'.html_escape($entry).'</span>' : ''; ?></td>
                            <td><?php echo $return !== '' ? '<span class="date-chip">'.html_escape($return).'</span>' : ''; ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="9" class="empty">Không có dữ liệu sinh viên phù hợp bộ lọc hiện tại.</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

       
    </div>

</div>
</body>
</html>
