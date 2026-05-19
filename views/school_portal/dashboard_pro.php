<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$CI->load->helper('internship_management/internship_status');
?>
<?php
$summary         = $summary ?? [];
$recent_students = $recent_students ?? [];
$status_chart    = $status_chart ?? [];
$job_orders      = $job_orders ?? [];
$upcoming_events = $upcoming_events ?? [];
$statuses        = $statuses ?? [];
$years           = $years ?? [];
$months          = $months ?? [];
$filters         = $filters ?? [];
$school_name     = $school_name ?? '';

$eventLabelMap = [
    'interview' => 'Phỏng vấn',
    'entry'     => 'Nhập cảnh',
    'return'    => 'Về nước',
];

if (!function_exists('dashboard_safe_date')) {
    function dashboard_safe_date($date)
    {
        $date = trim((string)$date);
        if ($date === '' || $date === '0000-00-00') {
            return '—';
        }
        $ts = strtotime($date);
        return $ts ? date('d/m/Y', $ts) : html_escape($date);
    }
}

/*if (!function_exists('dashboard_status_label')) {
    
    function dashboard_status_label($value)
    {
        if (function_exists('im_status_label_vi')) {
            return im_status_label_vi($value);
        }
    
        $value = trim((string)$value);
        return $value === '' ? 'Chưa cập nhật' : $value;
    }
}*/

if (!function_exists('dashboard_status_label')) {
    function dashboard_status_label($value)
    {
        $value = trim((string)$value);

        if ($value === '') {
            return 'Chưa cập nhật';
        }

        return im_status_label_vi($value);
    }
}

/*if (!function_exists('dashboard_status_group')) {
    
    function dashboard_status_group($value)
    {
        if (function_exists('im_normalize_status')) {
            return im_normalize_status($value);
        }
    
        $value = strtolower(trim((string)$value));
        $value = str_replace(['-', ' '], '_', $value);
        return preg_replace('/_+/', '_', $value);
    }
}*/
if (!function_exists('dashboard_status_group')) {
    function dashboard_status_group($value)
    {
        return im_normalize_dossier_progress($value);
    }
}


/*$translatedStatuses = [];
foreach ($statuses as $val => $label) {
    $translatedStatuses[$val] = dashboard_status_label($label !== '' ? $label : $val);
}
$statuses = $translatedStatuses;*/

$translatedStatuses = [];

foreach ($statuses as $val => $label) {
    $val = (string)$val;

    if ($val === '') {
        $translatedStatuses[$val] = 'Tất cả';
    } else {
        $translatedStatuses[$val] = im_status_label_vi($val);
    }
}

$statuses = $translatedStatuses;

/*$statusLabels = [];
$statusTotals = [];
$statusColors = ['#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316', '#22c55e', '#0ea5e9'];*/

$statusLabels = [];
$statusTotals = [];
$statusColors = [];

/*$statusGroupCounts = [
    'in_japan' => 0,
    'docs_preparing' => 0,
    'cancelled' => 0,
    'returned' => 0,
    'interview_scheduled' => 0,
    'applied' => 0,
    'pass' => 0,
    'fail' => 0,
];*/
$statusGroupCounts = [
    'in_japan'            => 0,
    'docs_preparing'      => 0,
    'cancelled'           => 0,
    'returned'            => 0,
    'interview_scheduled' => 0,
    'applied'             => 0,
];


/*foreach ($status_chart as $index => &$row) {
    $rawStatus = (string)($row['status'] ?? $row['value'] ?? $row['key'] ?? $row['label'] ?? '');
    $translated = dashboard_status_label((string)($row['label'] ?? $rawStatus));
    $row['label'] = $translated;
    $row['status_label'] = $translated;
    $row['status_group'] = dashboard_status_group($rawStatus !== '' ? $rawStatus : $translated);

    $statusLabels[] = $translated;
    $total = (int)($row['total'] ?? 0);
    $statusTotals[] = $total;

    if (isset($statusGroupCounts[$row['status_group']])) {
        $statusGroupCounts[$row['status_group']] += $total;
    }
}
unset($row);*/

foreach ($status_chart as $index => &$row) {
    $rawStatus = (string)($row['status_key'] ?? $row['status'] ?? $row['value'] ?? $row['key'] ?? '');
    $group = dashboard_status_group($rawStatus);

    $translated = im_status_label_vi($group);
    $color = (string)($row['color'] ?? im_status_color_hex($group));

    $row['label'] = $translated;
    $row['status_label'] = $translated;
    $row['status_group'] = $group;
    $row['color'] = $color;

    $statusLabels[] = $translated;

    $total = (int)($row['total'] ?? 0);
    $statusTotals[] = $total;
    $statusColors[] = $color;

    if (isset($statusGroupCounts[$group])) {
        $statusGroupCounts[$group] += $total;
    }
}
unset($row);

foreach ($recent_students as &$sv) {
    $raw = (string)($sv['status'] ?? $sv['status_label'] ?? '');
    $sv['status_label'] = dashboard_status_label($raw);
}
unset($sv);

/*$jobChartLabels = [];
$jobChartTotals = [];
foreach (array_slice($job_orders, 0, 6) as $row) {
    $jobChartLabels[] = (string)($row['job_order_id'] ?? 'Đơn tuyển');
    $jobChartTotals[] = (int)($row['total_students'] ?? 0);
}*/

$jobChartLabels = [];
$jobChartTotals = [];
$jobChartMeta   = [];

foreach (array_slice($job_orders, 0, 6) as $row) {
    $jobId   = trim((string)($row['job_order_id'] ?? ''));
    $company = trim((string)($row['company_receive'] ?? ''));

    // Label ngắn trên trục X: chỉ hiện ID
    if ($jobId !== '') {
        $shortLabel = (string)$jobId;
    } else {
        $shortLabel = 'Đơn';
    }

    // Label đầy đủ để hiện trong tooltip
    if ($company !== '' && $jobId !== '') {
        $fullLabel = '#' . $jobId . ' - ' . $company;
    } elseif ($jobId !== '') {
        $fullLabel = 'Đơn #' . $jobId;
    } elseif ($company !== '') {
        $fullLabel = $company;
    } else {
        $fullLabel = 'Đơn tuyển';
    }

    $jobChartLabels[] = $shortLabel;
    $jobChartTotals[] = (int)($row['total_students'] ?? 0);
    $jobChartMeta[]   = $fullLabel;
}

$totalStudents   = (int)($summary['total_students'] ?? 0);
$entryCount      = (int)($summary['entry_count'] ?? 0);
$returnCount     = (int)($summary['return_count'] ?? 0);
$jobOrderCount   = (int)($summary['job_order_count'] ?? $summary['job_orders'] ?? 0);
$interviewCount  = 0;
foreach ($job_orders as $row) {
    if (!empty($row['interview_date'])) {
        $interviewCount++;
    }
}
$entryRate       = $totalStudents > 0 ? round(($entryCount / $totalStudents) * 100) : 0;
$returnRate      = $totalStudents > 0 ? round(($returnCount / $totalStudents) * 100) : 0;
$inJapanCount    = (int)$statusGroupCounts['in_japan'];
/*$docsCount       = (int)$statusGroupCounts['docs_preparing'];
$prepareDocsCount= (int)$statusGroupCounts['prepare_documents'];*/
$docsCount        = (int)$statusGroupCounts['docs_preparing'];
$prepareDocsCount = 0;
$cancelledCount  = (int)$statusGroupCounts['cancelled'];
$appliedCount    = (int)$statusGroupCounts['applied'];
$scheduledCount  = (int)$statusGroupCounts['interview_scheduled'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="<?php echo base_url('uploads/company/favicon.png'); ?>">
    <title><?php echo html_escape($title ?? 'Dashboard'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>">
    <style>
        :root{
            --bg:#eef4fb;
            --panel:#ffffff;
            --panel-soft:#f6f9fc;
            --text:#12304a;
            --muted:#72839a;
            --line:#dde7f2;
            --primary:#1d4ed8;
            --primary-2:#4f46e5;
            --success:#10b981;
            --warning:#f59e0b;
            --danger:#ef4444;
            --shadow:0 18px 45px rgba(15, 23, 42, .08);
            --radius-xl:24px;
            --radius-lg:18px;
            --radius-md:14px;
        }
        *{box-sizing:border-box}
        html,body{margin:0;padding:0}
        body{
            font-family:Inter,Arial,Helvetica,sans-serif;
            color:var(--text);
            background:
                radial-gradient(circle at top left, rgba(79,70,229,.14), transparent 28%),
                radial-gradient(circle at top right, rgba(6,182,212,.14), transparent 24%),
                linear-gradient(180deg, #f8fbff 0%, var(--bg) 100%);
        }
        a{text-decoration:none}
        .shell{max-width:1440px;margin:0 auto;padding:24px 18px 40px}
        .hero{
            position:relative;overflow:hidden;border-radius:32px;
            background:linear-gradient(135deg, #0f2f63 0%, #1d4ed8 52%, #06b6d4 100%);
            color:#fff;padding:28px;box-shadow:0 24px 60px rgba(29,78,216,.24);margin-bottom:18px;
        }
        .hero:before,.hero:after{content:'';position:absolute;border-radius:999px;background:rgba(255,255,255,.08);pointer-events:none}
        .hero:before{width:240px;height:240px;right:-70px;top:-90px}
        .hero:after{width:180px;height:180px;left:-50px;bottom:-75px}
        .hero-top{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;flex-wrap:wrap;position:relative;z-index:1}
        .hero h1{margin:0;font-size:32px;font-weight:800;letter-spacing:-.02em}
        .hero p{margin:10px 0 0;color:rgba(255,255,255,.82);max-width:760px;font-size:14px;line-height:1.65}
        .navx{display:flex;gap:10px;flex-wrap:wrap}
        .btnx{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:10px 16px;border-radius:999px;border:1px solid transparent;font-weight:700;transition:all .2s ease;color:var(--text);background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.08)}
        .btnx:hover{transform:translateY(-1px);color:var(--text)}
        .btnx.primary{background:rgba(255,255,255,.14);backdrop-filter:blur(10px);color:#fff;border-color:rgba(255,255,255,.18);box-shadow:none}
        .hero-stats{position:relative;z-index:1;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-top:22px}
        .hero-stat{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.16);backdrop-filter:blur(10px);border-radius:20px;padding:16px 18px}
        .hero-stat .label{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.72);font-weight:700}
        .hero-stat .value{margin-top:8px;font-size:30px;line-height:1;font-weight:800;color:#fff}
        .hero-stat .meta{margin-top:6px;color:rgba(255,255,255,.82);font-size:12px}
        .cardx{background:rgba(255,255,255,.88);border:1px solid rgba(221,231,242,.85);border-radius:var(--radius-xl);box-shadow:var(--shadow);backdrop-filter:blur(6px)}
        .pad{padding:22px}
        .filters{margin-bottom:18px}
        .filters-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px;align-items:end}
        .field{grid-column:span 3}
        .field.wide{grid-column:span 4}
        .field.action{grid-column:span 2}
        .labelx{display:block;margin-bottom:8px;font-size:12px;font-weight:800;text-transform:uppercase;color:#6e8095;letter-spacing:.05em}
        .filters .form-control{height:46px;border-radius:14px;border:1px solid var(--line);box-shadow:none;background:#fbfdff;color:var(--text);padding:10px 14px}
        .filters .form-control:focus{border-color:#9bb8ff;box-shadow:0 0 0 4px rgba(29,78,216,.08)}
        .overview-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:18px}
        .metric-card{position:relative;overflow:hidden}
        .metric-card:before{content:'';position:absolute;inset:auto -30px -40px auto;width:110px;height:110px;border-radius:999px;background:linear-gradient(135deg, rgba(79,70,229,.14), rgba(6,182,212,.10))}
        .metric-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;position:relative;z-index:1}
        .metric-title{font-size:12px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#6c7e93}
        .metric-value{margin-top:10px;font-size:34px;font-weight:800;line-height:1;color:#0f2f63}
        .metric-meta{margin-top:10px;font-size:13px;color:#71839a}
        .metric-chip{padding:8px 10px;border-radius:12px;font-size:12px;font-weight:800;background:#eef4ff;color:#2956c8}
        .main-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:18px;margin-bottom:18px}
        .sub-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
        .section-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px}
        .section-title{margin:0;font-size:18px;font-weight:800;color:#12304a}
        .section-sub{font-size:13px;color:#73849a}
        .chart-stack{display:grid;gap:18px}
        .status-bars{display:grid;gap:12px}
        .status-row{display:grid;grid-template-columns:minmax(140px, 200px) 1fr 56px;gap:12px;align-items:center}
        .status-name{font-weight:700;color:#29445f;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .bar-track{height:12px;background:#edf3fa;border-radius:999px;overflow:hidden;position:relative}
        .bar-fill{height:100%;border-radius:999px;background:linear-gradient(90deg, #4f46e5 0%, #06b6d4 100%)}
        .status-total{text-align:right;font-weight:800;color:#12304a}
        .chart-panel{background:linear-gradient(180deg,#fbfdff 0%,#f4f8fd 100%);border:1px solid #e7eef7;border-radius:20px;padding:18px}
        .chart-canvas{position:relative;height:300px}
        .chart-canvas.small{height:250px}
        .table-card .table-responsive{border:1px solid #edf2f8;border-radius:18px;overflow:auto}
        .table-card .table{margin:0;background:#fff}
        .table-card .table>thead>tr>th{border-bottom:1px solid #e7eef7;border-top:none;background:#f7faff;color:#173552;font-size:12px;text-transform:uppercase;letter-spacing:.05em;padding:14px 16px;white-space:nowrap}
        .table-card .table>tbody>tr>td{padding:14px 16px;border-top:1px solid #f0f4f8;vertical-align:middle}
        .table-card .table-striped>tbody>tr:nth-of-type(odd){background:#fcfdff}
        .table-card .table tbody tr:hover{background:#f7fbff}
        .badge-status{display:inline-flex;align-items:center;padding:7px 12px;border-radius:999px;background:#edf4ff;color:#2956c8;font-weight:800;font-size:12px}
        .empty{padding:18px;text-align:center;color:#73849a;background:#f9fbfe;border:1px dashed #d7e3ef;border-radius:16px}
        .tiny{font-size:12px;color:#73849a}
        @media (max-width:1200px){.overview-grid,.hero-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.main-grid,.sub-grid{grid-template-columns:1fr}}
        @media (max-width:992px){.filters-grid{grid-template-columns:repeat(6,minmax(0,1fr))}.field,.field.wide,.field.action{grid-column:span 3}}
        @media (max-width:768px){.shell{padding:14px 12px 28px}.hero{padding:20px;border-radius:24px}.hero h1{font-size:26px}.overview-grid,.hero-stats,.filters-grid{grid-template-columns:1fr}.field,.field.wide,.field.action{grid-column:span 1}.status-row{grid-template-columns:1fr}.status-total{text-align:left}.btnx{width:100%}.navx{width:100%}}
    </style>
</head>
<body>
<div class="shell">
    <section class="hero">
        <div class="hero-top">
            <div>
                <h1>Dashboard trường · <?php echo html_escape($school_name); ?></h1>
                <p>Theo dõi tổng quan tình hình sinh viên, đơn tuyển và lịch hẹn trên cùng một màn hình. Bố cục được tối ưu để nhìn nhanh số liệu quan trọng, đối chiếu tiến độ và lọc dữ liệu thuận tiện hơn.</p>
            </div>
            <div class="navx">
                <a class="btnx primary" href="<?php echo site_url('school_portal/dashboard'); ?>">Dashboard</a>
                <a class="btnx" href="<?php echo site_url('school_portal/students'); ?>">Sinh viên</a>
                <a class="btnx" href="<?php echo site_url('school_portal/calendar'); ?>">Lịch</a>
                <a class="btnx" href="<?php echo site_url('school_portal/job_orders'); ?>">Đơn tuyển</a>
                <a class="btnx" href="<?php echo site_url('school_portal/logout'); ?>">Thoát</a>
            </div>
        </div>

        <div class="hero-stats">
            <div class="hero-stat">
                <div class="label">Tổng sinh viên</div>
                <div class="value"><?php echo $totalStudents; ?></div>
                <div class="meta">Quy mô dữ liệu hiện tại</div>
            </div>
            <div class="hero-stat">
                <div class="label">Đã nhập cảnh</div>
                <div class="value"><?php echo $entryCount; ?></div>
                <div class="meta">Tỷ lệ <?php echo $entryRate; ?>% trên tổng hồ sơ</div>
            </div>
            <div class="hero-stat">
                <div class="label">Đã về nước</div>
                <div class="value"><?php echo $returnCount; ?></div>
                <div class="meta">Tỷ lệ <?php echo $returnRate; ?>% trên tổng hồ sơ</div>
            </div>
            <div class="hero-stat">
                <div class="label">Đơn tuyển</div>
                <div class="value"><?php echo $jobOrderCount; ?></div>
                <div class="meta">Có <?php echo $interviewCount; ?> lịch phỏng vấn</div>
            </div>
        </div>
    </section>

    <section class="cardx pad filters">
        <div class="section-head" style="margin-bottom:14px;">
            <div>
                <h2 class="section-title">Bộ lọc dashboard</h2>
                <div class="section-sub">Lọc nhanh theo năm, trạng thái và từ khóa tìm kiếm.</div>
            </div>
        </div>
        <form method="get">
            <div class="filters-grid">
                <div class="field">
                    <label class="labelx">Năm</label>
                    <!--<select name="year" class="form-control">
                        <option value="0">Tất cả</option>
                        <?php foreach($years as $y){ ?>
                            <option value="<?php echo (int)$y; ?>" <?php echo ((int)($filters['year'] ?? 0) === (int)$y) ? 'selected' : ''; ?>><?php echo (int)$y; ?></option>
                        <?php } ?>
                    </select> -->
                    <select name="year" class="form-control">
                        <option value="0" <?php echo ((int)($filters['year'] ?? (int)date('Y')) === 0) ? 'selected' : ''; ?>>Tất cả</option>
                        <?php foreach($years as $y){ ?>
                            <option value="<?php echo (int)$y; ?>" <?php echo ((int)($filters['year'] ?? (int)date('Y')) === (int)$y) ? 'selected' : ''; ?>>
                                <?php echo (int)$y; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="field">
                    <label class="labelx">Tháng</label>
                    <select name="month" class="form-control">
                        <?php foreach($months as $m){ ?>
                            <?php $mVal = (int)($m['value'] ?? 0); ?>
                            <option value="<?php echo $mVal; ?>"
                                <?php echo ((int)($filters['month'] ?? (int)date('n')) === $mVal) ? 'selected' : ''; ?>>
                                <?php echo html_escape((string)($m['label'] ?? '')); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="field">
                    <label class="labelx">Trạng thái</label>
                    <select name="status" class="form-control">
                        <?php foreach($statuses as $val => $label){ ?>
                            <option value="<?php echo html_escape((string)$val); ?>" <?php echo ((string)($filters['status'] ?? '') === (string)$val) ? 'selected' : ''; ?>><?php echo html_escape((string)$label); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="field wide">
                    <label class="labelx">Từ khóa</label>
                    <input class="form-control" type="text" name="q" value="<?php echo html_escape((string)($filters['q'] ?? '')); ?>" placeholder="Tên / ngành / công ty / đơn tuyển">
                </div>
                <div class="field action">
                    <button class="btnx" style="width:100%;height:46px;border:none;background:linear-gradient(135deg,#1d4ed8,#4f46e5);color:#fff;box-shadow:0 18px 35px rgba(29,78,216,.22);">Lọc dữ liệu</button>
                </div>
            </div>
        </form>
    </section>

    <section class="overview-grid">
        <div class="cardx pad metric-card">
            <div class="metric-top">
                <div>
                    <div class="metric-title">Tiến độ nhập cảnh</div>
                    <div class="metric-value"><?php echo $entryRate; ?>%</div>
                    <div class="metric-meta"><?php echo $entryCount; ?> / <?php echo $totalStudents; ?> sinh viên đã có ngày nhập cảnh</div>
                </div>
                <!--<span class="metric-chip">Entry</span>-->
                <span class="metric-chip" style="background:#e0f2fe;color:#0369a1;">Nhập cảnh</span>
            </div>
        </div>
        <div class="cardx pad metric-card">
            <div class="metric-top">
                <div>
                    <div class="metric-title">Tiến độ về nước</div>
                    <div class="metric-value"><?php echo $returnRate; ?>%</div>
                    <div class="metric-meta"><?php echo $returnCount; ?> sinh viên đã hoàn thành chu trình</div>
                </div>
                <!--<span class="metric-chip" style="background:#ecfdf5;color:#0f9f6e;">Return</span>-->
                <span class="metric-chip" style="background:#ecfdf5;color:#0f9f6e;">Về nước</span>
            </div>
        </div>
        <div class="cardx pad metric-card">
            <div class="metric-top">
                <div>
                    <div class="metric-title">Đang ở Nhật</div>
                    <div class="metric-value"><?php echo $inJapanCount; ?></div>
                    <div class="metric-meta">Số sinh viên đang trong giai đoạn làm việc tại Nhật</div>
                </div>
                <!--<span class="metric-chip" style="background:#eef2ff;color:#4f46e5;">JP</span>-->
                <span class="metric-chip" style="background:#f0fdf4;color:#65a30d;">Nhật</span>
            </div>
        </div>
        <div class="cardx pad metric-card">
            <div class="metric-top">
                <div>
                    <div class="metric-title">Đang làm hồ sơ</div>
                    <div class="metric-value"><?php echo $docsCount + $prepareDocsCount; ?></div>
                    <div class="metric-meta">Gồm đang làm hồ sơ và chuẩn bị hồ sơ</div>
                </div>
                <!--<span class="metric-chip" style="background:#ecfeff;color:#0891b2;">Hồ sơ</span>-->
                <span class="metric-chip" style="background:#fffbeb;color:#d97706;">Hồ sơ</span>
            </div>
        </div>
        <div class="cardx pad metric-card">
            <div class="metric-top">
                <div>
                    <div class="metric-title">Lịch phỏng vấn</div>
                    <div class="metric-value"><?php echo $interviewCount; ?></div>
                    <div class="metric-meta">Số đơn tuyển đã có ngày phỏng vấn</div>
                </div>
                <span class="metric-chip" style="background:#fff7ed;color:#d97706;">PV</span>
            </div>
        </div>
        <div class="cardx pad metric-card">
            <div class="metric-top">
                <div>
                    <div class="metric-title">Đã hủy</div>
                    <div class="metric-value"><?php echo $cancelledCount; ?></div>
                    <div class="metric-meta">Tổng số hồ sơ đang ở trạng thái hủy</div>
                </div>
                <span class="metric-chip" style="background:#fef2f2;color:#dc2626;">Hủy</span>
            </div>
        </div>
        <div class="cardx pad metric-card">
            <div class="metric-top">
                <div>
                    <div class="metric-title">Đã nộp đơn</div>
                    <div class="metric-value"><?php echo $appliedCount; ?></div>
                    <div class="metric-meta">Số hồ sơ đã nộp cho đơn tuyển</div>
                </div>
                <!--<span class="metric-chip" style="background:#fffbeb;color:#d97706;">Apply</span>-->
                <span class="metric-chip" style="background:#e0f2fe;color:#0369a1;">Nộp đơn</span>
            </div>
        </div>
        <div class="cardx pad metric-card">
            <div class="metric-top">
                <div>
                    <div class="metric-title">Sự kiện sắp tới</div>
                    <div class="metric-value"><?php echo count($upcoming_events); ?></div>
                    <div class="metric-meta">Có <?php echo $scheduledCount; ?> trạng thái đã lên lịch phỏng vấn</div>
                </div>
                <span class="metric-chip" style="background:#f5f3ff;color:#6d28d9;">Lịch</span>
            </div>
        </div>
    </section>

    <section class="main-grid">
        <div class="cardx pad">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Biểu đồ trạng thái sinh viên</h2>
                    <div class="section-sub">Kết hợp biểu đồ tròn và thanh để quan sát phân bổ trạng thái rõ hơn.</div>
                </div>
                <div class="tiny"><?php echo count($status_chart); ?> trạng thái</div>
            </div>

            <div class="chart-stack">
                <div class="chart-panel">
                    <div class="chart-canvas"><canvas id="statusDonutChart"></canvas></div>
                </div>

                <div class="status-bars">
                    <?php
                    $max = 1;
                    foreach($status_chart as $r){
                        if((int)($r['total'] ?? 0) > $max){
                            $max = (int)($r['total'] ?? 0);
                        }
                    }
                    foreach($status_chart as $r){
                        $pct = round(((int)($r['total'] ?? 0) / $max) * 100);
                    ?>
                        <!--<div class="status-row">
                            <div class="status-name"><?php echo html_escape((string)($r['label'] ?? '')); ?></div>
                            <div class="bar-track"><div class="bar-fill" style="width:<?php echo (int)$pct; ?>%"></div></div>
                            <div class="status-total"><?php echo (int)($r['total'] ?? 0); ?></div>
                        </div> -->
                        <?php $barColor = (string)($r['color'] ?? im_status_color_hex($r['status_group'] ?? '')); ?>
                        <div class="status-row">
                            <div class="status-name"><?php echo html_escape((string)($r['label'] ?? '')); ?></div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:<?php echo (int)$pct; ?>%;background:<?php echo html_escape($barColor); ?>"></div>
                            </div>
                            <div class="status-total"><?php echo (int)($r['total'] ?? 0); ?></div>
                        </div>
                    <?php } ?>
                    <?php if(empty($status_chart)){ ?>
                        <div class="empty">Không có dữ liệu trạng thái để hiển thị biểu đồ.</div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="cardx pad">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Đồ thị đơn tuyển nổi bật</h2>
                    <div class="section-sub">Top đơn tuyển có nhiều sinh viên nhất.</div>
                </div>
                <!--<a class="btnx" href="<?php echo site_url('school_portal/job_orders'); ?>">Xem đơn tuyển</a>-->
                <a class="btnx" href="<?php echo site_url('school_portal/job_orders?scope=active'); ?>">Xem đơn tuyển</a>
            </div>
            <div class="chart-panel">
                <div class="chart-canvas small"><canvas id="jobOrderBarChart"></canvas></div>
            </div>
        </div>
    </section>

    <section class="sub-grid" style="margin-bottom:18px;">
        <div class="cardx pad table-card">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Lịch gần nhất</h2>
                    <div class="section-sub">Theo dõi các cột mốc phỏng vấn, nhập cảnh và về nước.</div>
                </div>
                <a class="btnx" href="<?php echo site_url('school_portal/calendar'); ?>">Xem lịch</a>
            </div>
            <?php if(!empty($upcoming_events)){ ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Loại</th>
                                <th>Ngày</th>
                                <th>Sinh viên</th>
                                <th>Công ty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($upcoming_events as $e){
                                $etype = (string)($e['event_type'] ?? '');
                                $elabel = (string)($e['event_label'] ?? ($eventLabelMap[$etype] ?? $etype));
                            ?>
                                <tr>
                                    <td><span class="badge-status"><?php echo html_escape($elabel); ?></span></td>
                                    <td><?php echo dashboard_safe_date($e['event_date'] ?? ''); ?></td>
                                    <td><?php echo html_escape((string)($e['student_name'] ?? '')); ?></td>
                                    <td><?php echo html_escape((string)($e['company_receive'] ?? '')); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="empty">Chưa có lịch sắp tới.</div>
            <?php } ?>
        </div>

        <div class="cardx pad table-card">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Sinh viên gần đây</h2>
                    <div class="section-sub">Danh sách hồ sơ mới hoặc vừa cập nhật gần đây.</div>
                </div>
                <a class="btnx" href="<?php echo site_url('school_portal/students'); ?>">Xem tất cả</a>
            </div>
            <?php if(!empty($recent_students)){ ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Họ tên</th>
                                <th>Ngành</th>
                                <th>Đơn tuyển</th>
                                <th>Trạng thái</th>
                                <th>PV</th>
                                <th>Nhập cảnh</th>
                                <th>Về nước</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_students as $sv){ ?>
                                <tr>
                                    <td><a href="<?php echo site_url('school_portal/student/' . (int)($sv['id'] ?? 0)); ?>"><strong><?php echo html_escape((string)($sv['student_name'] ?? '')); ?></strong></a></td>
                                    <td><?php echo html_escape((string)($sv['major'] ?? '')); ?></td>
                                    <!--<td><?php echo html_escape((string)($sv['job_order_id'] ?? '')); ?></td>-->
                                    <td><?php echo html_escape((string)($sv['job_order_id'] !== '' ? ('#' . $sv['job_order_id']) : '')); ?></td>
                                    <td><span class="badge-status"><?php echo html_escape((string)($sv['status_label'] ?? '')); ?></span></td>
                                    <td><?php echo dashboard_safe_date($sv['interview_date'] ?? ''); ?></td>
                                    <td><?php echo dashboard_safe_date($sv['entry_date'] ?? ''); ?></td>
                                    <td><?php echo dashboard_safe_date($sv['return_date'] ?? ''); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="empty">Không có dữ liệu sinh viên.</div>
            <?php } ?>
        </div>
    </section>

    <section class="cardx pad table-card">
        <div class="section-head">
            <div>
                <h2 class="section-title">Đơn tuyển theo trường</h2>
                <div class="section-sub">Tổng hợp tình trạng từng đơn tuyển và các mốc liên quan.</div>
            </div>
            <a class="btnx" href="<?php echo site_url('school_portal/job_orders'); ?>">Danh sách đơn tuyển</a>
        </div>
        <?php if(!empty($job_orders)){ ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Đơn tuyển</th>
                            <th>Công ty</th>
                            <th>Số SV</th>
                            <th>PV</th>
                            <th>Nhập cảnh</th>
                            <th>Về nước</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($job_orders as $jo){ ?>
                            <tr>
                                <td><strong><?php echo html_escape((string)($jo['job_order_id'] ?? '')); ?></strong></td>
                                <!--<td><?php echo html_escape((string)($jo['company_receive'] ?? '')); ?></td>-->
                                <td>
                                    <?php
                                        $company = trim((string)($jo['company_receive'] ?? ''));
                                        echo html_escape($company !== '' ? $company : ('Đơn tuyển #' . (string)($jo['job_order_id'] ?? '')));
                                    ?>
                                </td>
                                <td><?php echo (int)($jo['total_students'] ?? 0); ?></td>
                                <td><?php echo dashboard_safe_date($jo['interview_date'] ?? ''); ?></td>
                                <td><?php echo dashboard_safe_date($jo['entry_date'] ?? ''); ?></td>
                                <td><?php echo dashboard_safe_date($jo['return_date'] ?? ''); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <div class="empty">Không có dữ liệu đơn tuyển.</div>
        <?php } ?>
    </section>
</div>

<script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/plugins/bootstrap/js/bootstrap.min.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/*const statusLabels = <?php echo json_encode($statusLabels, JSON_UNESCAPED_UNICODE); ?>;
const statusTotals = <?php echo json_encode($statusTotals, JSON_UNESCAPED_UNICODE); ?>;
const statusColors = <?php echo json_encode(array_slice($statusColors, 0, max(count($statusLabels), 1)), JSON_UNESCAPED_UNICODE); ?>;*/
const statusLabels = <?php echo json_encode($statusLabels, JSON_UNESCAPED_UNICODE); ?>;
const statusTotals = <?php echo json_encode($statusTotals, JSON_UNESCAPED_UNICODE); ?>;
const statusColors = <?php echo json_encode($statusColors, JSON_UNESCAPED_UNICODE); ?>;
//const jobLabels    = <?php echo json_encode($jobChartLabels, JSON_UNESCAPED_UNICODE); ?>;
//const jobTotals    = <?php echo json_encode($jobChartTotals, JSON_UNESCAPED_UNICODE); ?>;
const jobLabels    = <?php echo json_encode($jobChartLabels, JSON_UNESCAPED_UNICODE); ?>;
const jobTotals    = <?php echo json_encode($jobChartTotals, JSON_UNESCAPED_UNICODE); ?>;
const jobMeta      = <?php echo json_encode($jobChartMeta, JSON_UNESCAPED_UNICODE); ?>;

if (document.getElementById('statusDonutChart') && statusLabels.length) {
    new Chart(document.getElementById('statusDonutChart'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusTotals,
                backgroundColor: statusColors,
                borderColor: '#ffffff',
                borderWidth: 6,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '64%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10, color: '#29445f', font: { size: 12, weight: '700' } } },
                tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.parsed}` } }
            }
        }
    });
}

/*if (document.getElementById('jobOrderBarChart') && jobLabels.length) {
    new Chart(document.getElementById('jobOrderBarChart'), {
        type: 'bar',
        data: {
            labels: jobLabels,
            datasets: [{
                label: 'Số sinh viên',
                data: jobTotals,
                backgroundColor: '#4f46e5',
                borderRadius: 10,
                maxBarThickness: 42
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#5f738a', font: { weight: '700' } } },
                y: { beginAtZero: true, ticks: { precision: 0, color: '#5f738a' }, grid: { color: '#ecf1f7' } }
            }
        }
    });
} */

if (document.getElementById('jobOrderBarChart') && jobLabels.length) {
    new Chart(document.getElementById('jobOrderBarChart'), {
        type: 'bar',
        data: {
            labels: jobLabels,
            datasets: [{
                label: 'Số sinh viên',
                data: jobTotals,
                backgroundColor: '#4f46e5',
                borderRadius: 10,
                maxBarThickness: 42
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: function(items) {
                            const idx = items && items.length ? items[0].dataIndex : -1;
                            return idx >= 0 ? (jobMeta[idx] || jobLabels[idx] || '') : '';
                        },
                        label: function(ctx) {
                            return 'Số sinh viên: ' + ctx.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#5f738a',
                        font: { weight: '700' },
                        maxRotation: 0,
                        minRotation: 0,
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: '#5f738a' },
                    grid: { color: '#ecf1f7' }
                }
            }
        }
    });
}
</script>
</body>
</html>
