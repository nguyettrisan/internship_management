<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/*$job_orders  = is_array($job_orders ?? null) ? $job_orders : [];
$school_name = (string) ($school_name ?? 'HUTECH');
$title       = (string) ($title ?? 'Đơn tuyển');*/

$job_orders  = is_array($job_orders ?? null) ? $job_orders : [];
$school_name = (string) ($school_name ?? 'HUTECH');
$title       = (string) ($title ?? 'Đơn tuyển');

$filters = is_array($filters ?? null) ? $filters : [];

$currentScope = (string)($filters['scope'] ?? 'active');

if (!in_array($currentScope, ['active', 'year', 'all'], true)) {
    $currentScope = 'active';
}

$currentYear = (int)($filters['year'] ?? date('Y'));

$years = is_array($years ?? null) ? $years : [];

if (empty($years)) {
    $years = range((int)date('Y') + 2, (int)date('Y') - 5);
}

if (!function_exists('jop_scalar_text')) {
    function jop_scalar_text($raw)
    {
        if (is_string($raw) || is_numeric($raw)) {
            $value = trim((string) $raw);
            return $value;
        }
        if (is_array($raw)) {
            $parts = [];
            foreach ($raw as $item) {
                if (is_scalar($item)) {
                    $item = trim((string) $item);
                    if ($item !== '') {
                        $parts[] = $item;
                    }
                }
            }
            return trim(implode(' - ', $parts));
        }
        return '';
    }
}

if (!function_exists('jop_val')) {
    function jop_val(array $row, array $keys, $default = '')
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                $value = jop_scalar_text($row[$key]);
                if ($value !== '') {
                    return $value;
                }
            }
        }
        return $default;
    }
}

if (!function_exists('jop_guess_by_terms')) {
    function jop_guess_by_terms(array $row, array $includeTerms, array $excludeTerms = [], $default = '')
    {
        foreach ($row as $key => $value) {
            $keyNorm = mb_strtolower((string) $key);
            $ok = false;
            foreach ($includeTerms as $term) {
                if (mb_strpos($keyNorm, mb_strtolower($term)) !== false) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                continue;
            }
            foreach ($excludeTerms as $term) {
                if (mb_strpos($keyNorm, mb_strtolower($term)) !== false) {
                    $ok = false;
                    break;
                }
            }
            if (!$ok) {
                continue;
            }
            $text = jop_scalar_text($value);
            if ($text !== '') {
                return $text;
            }
        }
        return $default;
    }
}

if (!function_exists('jop_date')) {
    function jop_date($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '';
        }
        return substr($value, 0, 10);
    }
}

if (!function_exists('jop_badge_class')) {
    function jop_badge_class($value)
    {
        $value = mb_strtolower(trim((string) $value));
        if ($value === '') return 'b-slate';
        if (strpos($value, 'nhật') !== false || strpos($value, 'đang') !== false) return 'b-cyan';
        if (strpos($value, 'về nước') !== false) return 'b-slate';
        if (strpos($value, 'đậu') !== false || strpos($value, 'đỗ') !== false || strpos($value, 'hoàn thành') !== false) return 'b-green';
        if (strpos($value, 'chuẩn') !== false || strpos($value, 'hồ sơ') !== false) return 'b-blue';
        if (strpos($value, 'trễ') !== false || strpos($value, 'hủy') !== false) return 'b-red';
        return 'b-amber';
    }
}

if (!function_exists('jop_company')) {
    function jop_company(array $row)
    {
        $value = jop_val($row, ['company_receive', 'company_name', 'receiver_company', 'accept_company', 'company', 'company_jp', 'company_vi']);
        if ($value !== '') return $value;
        return jop_guess_by_terms($row, ['company', 'receiver', 'accept'], ['address', 'province', 'prefecture', 'date', 'status'], '—');
    }
}

if (!function_exists('jop_prefecture')) {
    function jop_prefecture(array $row)
    {
        $value = jop_val($row, ['province_receive', 'receiver_prefecture', 'prefecture_name', 'prefecture', 'province', 'work_prefecture', 'region_receive', 'work_region']);
        if ($value !== '') return $value;
        return jop_guess_by_terms($row, ['province', 'prefecture', 'region', 'khu_vuc', 'tinh'], ['address', 'company', 'date', 'code', 'id'], '');
    }
}

if (!function_exists('jop_address')) {
    function jop_address(array $row)
    {
        $value = jop_val($row, ['address_receive', 'receiver_address', 'company_address', 'work_address', 'address', 'office_address', 'workplace_address', 'factory_address', 'address_detail', 'receiver_location']);
        if ($value !== '') return $value;
        return jop_guess_by_terms($row, ['address', 'location', 'dia_chi'], ['province', 'prefecture', 'date', 'status', 'school', 'major'], '');
    }
}

if (!function_exists('jop_school')) {
    function jop_school(array $row, $fallback = '—')
    {
        $value = jop_val($row, ['school_name', 'school', 'partner_school', 'university_name', 'college_name']);
        if ($value !== '') return $value;
        return jop_guess_by_terms($row, ['school', 'university', 'college'], ['id', 'code'], $fallback);
    }
}

if (!function_exists('jop_major')) {
    function jop_major(array $row)
    {
        $value = jop_val($row, ['major_name', 'major', 'department', 'faculty_name', 'specialization']);
        if ($value !== '') return $value;
        return jop_guess_by_terms($row, ['major', 'department', 'faculty', 'special'], ['id', 'code'], '—');
    }
}

$companyCount = [];
$prefectureCount = [];
$totalStudents = 0;
$interviewCount = 0;
$entryCount = 0;
$returnCount = 0;
$missingLocationCount = 0;
$missingAddressCount = 0;

foreach ($job_orders as $row) {
    $company  = jop_company($row);
    $pref     = jop_prefecture($row);
    $address  = jop_address($row);
    $students = (int) jop_val($row, ['total_students', 'students_total', 'student_count', 'qty_students', 'quantity_students'], '0');

    if (!isset($companyCount[$company])) $companyCount[$company] = 0;
    $companyCount[$company]++;

    $prefKey = $pref !== '' ? $pref : '—';
    if (!isset($prefectureCount[$prefKey])) $prefectureCount[$prefKey] = 0;
    $prefectureCount[$prefKey]++;

    if ($pref === '') $missingLocationCount++;
    if ($address === '') $missingAddressCount++;

    $totalStudents += $students;
    if (jop_date(jop_val($row, ['interview_date', 'pv_date', 'date_interview']))) $interviewCount++;
    if (jop_date(jop_val($row, ['entry_date', 'departure_date', 'date_entry']))) $entryCount++;
    if (jop_date(jop_val($row, ['return_date', 'finish_date', 'date_return']))) $returnCount++;
}

arsort($companyCount);
arsort($prefectureCount);
$topCompany = !empty($companyCount) ? array_key_first($companyCount) : '—';
$topPrefecture = '—';
foreach (array_keys($prefectureCount) as $k) {
    if ($k !== '—') { $topPrefecture = $k; break; }
}
$favicon = base_url('uploads/company/favicon.png');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_escape($title); ?></title>
    <link rel="icon" type="image/png" href="<?php echo $favicon; ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>">
    <style>
        :root{
            --ifk-navy:#0b2e59;
            --ifk-navy-2:#173f73;
            --ifk-sky:#10a6de;
            --ifk-blue:#2d6cdf;
            --ifk-bg:#edf3f8;
            --ifk-card:#ffffff;
            --ifk-line:#dfe8f2;
            --ifk-line-soft:#ebf1f7;
            --ifk-text:#18324d;
            --ifk-muted:#6b7b8f;
            --ifk-soft:#f7fbff;
            --shadow-sm:0 10px 24px rgba(11,46,89,.05);
            --shadow-md:0 18px 42px rgba(11,46,89,.08);
            --shadow-lg:0 26px 56px rgba(11,46,89,.14);
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            color:var(--ifk-text);
            font-family:Arial,Helvetica,sans-serif;
            background:
                radial-gradient(circle at top right, rgba(16,166,222,.12), transparent 22%),
                radial-gradient(circle at bottom left, rgba(45,108,223,.08), transparent 18%),
                linear-gradient(180deg,#f7fbff 0%, var(--ifk-bg) 100%);
        }
        .wrap{max-width:1480px;margin:0 auto;padding:28px 18px 42px}
        .top{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:18px}
        .title h2{margin:0;color:var(--ifk-navy);font-size:42px;line-height:1.05;font-weight:900;letter-spacing:-.03em}
        .sub{margin-top:8px;color:var(--ifk-muted);font-size:14px;line-height:1.55}
        .navx{display:flex;gap:10px;flex-wrap:wrap}
        .btnx{display:inline-flex;align-items:center;justify-content:center;height:44px;padding:0 18px;border-radius:14px;background:#fff;border:1px solid var(--ifk-line);color:var(--ifk-navy);text-decoration:none;font-weight:800;transition:.2s ease;box-shadow:0 4px 10px rgba(11,46,89,.03)}
        .btnx:hover{text-decoration:none;color:var(--ifk-navy);transform:translateY(-1px);box-shadow:0 10px 18px rgba(11,46,89,.08)}
        .btnx.primary{background:linear-gradient(135deg,var(--ifk-navy),#184f95);border-color:transparent;color:#fff;box-shadow:0 12px 22px rgba(11,46,89,.16)}
        .cardx{background:var(--ifk-card);border:1px solid var(--ifk-line);border-radius:24px;box-shadow:var(--shadow-sm)}
        .pad{padding:18px}

        .hero-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:18px;margin-bottom:18px}
        .hero{display:flex;justify-content:space-between;gap:18px;min-height:176px}
        .hero-left{flex:1;display:flex;flex-direction:column;justify-content:center}
        .hero-kicker{display:inline-flex;width:max-content;padding:8px 12px;border-radius:999px;background:#eef4ff;color:var(--ifk-navy);font-size:12px;font-weight:900}
        .hero-title{margin:16px 0 8px;color:var(--ifk-navy);font-size:26px;line-height:1.15;font-weight:900;letter-spacing:-.02em}
        .hero-desc{color:var(--ifk-muted);font-size:14px;line-height:1.6;max-width:760px}
        .hero-right{width:280px;display:grid;grid-template-columns:1fr 1fr;gap:12px;flex:0 0 auto}
        .mini-kpi{min-height:82px;border:1px solid var(--ifk-line);border-radius:18px;padding:14px;background:linear-gradient(180deg,#fbfdff 0%,#f4f8fc 100%)}
        .mini-kpi .k{font-size:11px;color:var(--ifk-muted);text-transform:uppercase;font-weight:900;letter-spacing:.05em}
        .mini-kpi .v{margin-top:10px;color:var(--ifk-navy);font-size:26px;line-height:1;font-weight:900}

        .brand-card{position:relative;overflow:hidden;min-height:176px;border:none;border-radius:24px;background:radial-gradient(circle at 85% 18%, rgba(255,255,255,.12), transparent 16%),radial-gradient(circle at 70% 80%, rgba(16,166,222,.18), transparent 22%),linear-gradient(135deg,#103b73 0%,#194f95 50%,#0f7ab0 100%);color:#fff;box-shadow:var(--shadow-lg)}
        .brand-wrap{position:relative;z-index:2;display:flex;flex-direction:column;justify-content:center;min-height:140px}
        .brand-top{font-size:12px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:rgba(255,255,255,.76);margin-bottom:14px}
        .brand-main{display:grid;grid-template-columns:minmax(110px,.7fr) auto minmax(180px,1.3fr);align-items:end;column-gap:18px}
        .brand-ifk,.brand-school{display:block;line-height:.92;font-weight:900;margin:0;white-space:nowrap}
        .brand-ifk{font-size:clamp(44px,4vw,58px);letter-spacing:.03em;color:#e8f2ff;text-align:right}
        .brand-x{font-size:clamp(20px,1.4vw,28px);font-weight:900;color:rgba(255,255,255,.88);line-height:1;align-self:center}
        .brand-school{font-size:clamp(44px,4vw,58px);letter-spacing:-.035em;color:#fff;text-shadow:0 8px 20px rgba(0,0,0,.12);text-align:left}
        .brand-desc{margin-top:16px;font-size:14px;line-height:1.6;color:rgba(255,255,255,.90);max-width:520px}
        .brand-tags{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}
        .brand-tags span{display:inline-flex;align-items:center;padding:8px 14px;border-radius:999px;font-size:12px;font-weight:800;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.16)}

        /*.toolbar{display:grid;grid-template-columns:1.3fr .9fr .9fr auto;gap:14px;align-items:end;margin-bottom:18px}
        .field label{display:block;margin:0 0 8px;font-size:13px;font-weight:800;color:var(--ifk-navy)}
        .field .form-control,.field .form-select{height:48px;border-radius:14px;border:1px solid #d5e1ec;box-shadow:none}
        .field .form-control:focus,.field .form-select:focus{border-color:var(--ifk-sky);box-shadow:0 0 0 4px rgba(16,166,222,.10)}
        .toolbar-actions{display:flex;gap:10px}
        .action-btn{height:48px;padding:0 18px;border-radius:14px;border:1px solid var(--ifk-line);background:#fff;color:var(--ifk-navy);font-weight:900}
        .action-btn.primary{border:none;background:linear-gradient(135deg,var(--ifk-navy),#17457f 55%,#0f6ea2 100%);color:#fff;box-shadow:0 12px 22px rgba(11,46,89,.15)}*/
        .toolbar{
            display:grid;
            grid-template-columns:200px 130px minmax(260px,1.2fr) 160px minmax(280px,1.4fr);
            gap:14px 16px;
            align-items:end;
            margin-bottom:0;
        }
        
        .field label{
            display:block;
            margin:0 0 8px;
            font-size:13px;
            font-weight:900;
            color:var(--ifk-navy);
        }
        
        .field .form-control,
        .field .form-select{
            width:100%;
            height:48px;
            border-radius:14px;
            border:1px solid #d5e1ec;
            box-shadow:none;
            background:#fff;
            color:var(--ifk-text);
            font-weight:700;
        }
        
        .field .form-control:focus,
        .field .form-select:focus{
            border-color:var(--ifk-sky);
            box-shadow:0 0 0 4px rgba(16,166,222,.10);
        }
        
        .field.is-disabled{
            opacity:.48;
        }
        
        .field.is-disabled .form-select{
            background:#f3f7fb;
            cursor:not-allowed;
        }
        
        .toolbar-actions{
            grid-column:1/-1;
            display:flex;
            gap:10px;
            flex-wrap:wrap;
            padding-top:2px;
        }
        
        .action-btn{
            min-width:118px;
            height:48px;
            padding:0 18px;
            border-radius:14px;
            border:1px solid var(--ifk-line);
            background:#fff;
            color:var(--ifk-navy);
            font-weight:900;
        }
        
        .action-btn.primary{
            border:none;
            background:linear-gradient(135deg,var(--ifk-navy),#17457f 55%,#0f6ea2 100%);
            color:#fff;
            box-shadow:0 12px 22px rgba(11,46,89,.15);
        }
        
        .action-link{
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            justify-content:center;
        }
        
        .action-link:hover{
            text-decoration:none;
            color:var(--ifk-navy);
        }

        .section-head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;margin-bottom:14px}
        .section-head h3{margin:0;color:var(--ifk-navy);font-size:24px;line-height:1.15;font-weight:900;letter-spacing:-.02em}
        .section-sub{color:var(--ifk-muted);font-size:13px;font-weight:700}

        .table-wrap{overflow-x:auto;border-radius:18px}
        .tablex{width:100%;min-width:1220px;border-collapse:separate;border-spacing:0;background:#fff}
        .tablex thead th{padding:14px 12px;background:linear-gradient(180deg,#fbfdff 0%,#f5f9fd 100%);color:var(--ifk-navy);border-bottom:1px solid var(--ifk-line);font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
        .tablex tbody tr{transition:background .18s ease}
        .tablex tbody tr:hover{background:#f8fbff}
        .tablex tbody td{padding:16px 12px;border-top:1px solid var(--ifk-line-soft);vertical-align:top;color:#304a69}
        .order-id{display:inline-flex;align-items:center;justify-content:center;min-width:58px;padding:10px 14px;border-radius:18px;background:#eef4ff;color:var(--ifk-navy);font-weight:900;font-size:18px}
        .company-name{display:block;color:var(--ifk-navy);font-weight:900;font-size:14px;line-height:1.4}
        .subtxt{display:block;margin-top:4px;color:var(--ifk-muted);font-size:12px;line-height:1.45}
        .addrtxt{display:block;margin-top:0;color:#304a69;font-size:13px;line-height:1.5}
        .pill{display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:6px 12px;border-radius:999px;background:#edf3ff;color:#244d8f;font-weight:800;font-size:12px}
        .date-chip{display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:6px 10px;border-radius:10px;background:#f7fafe;border:1px solid #e8eef5;color:var(--ifk-navy);font-size:12px;font-weight:800;white-space:nowrap}
        .badge-status{display:inline-flex;align-items:center;justify-content:center;min-height:30px;padding:6px 12px;border-radius:999px;font-size:12px;font-weight:800;line-height:1.2}
        .b-blue{background:#eaf1ff;color:#184b9b}
        .b-amber{background:#fff4df;color:#9a6307}
        .b-green{background:#e7fbf2;color:#168256}
        .b-cyan{background:#e5f8ff;color:#0d799c}
        .b-slate{background:#eef2f7;color:#475569}
        .b-red{background:#fdeaea;color:#b42318}
        .btn-detail{display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border-radius:12px;background:#fff;border:1px solid var(--ifk-line);color:var(--ifk-navy);font-size:12px;font-weight:800;cursor:pointer}
        .btn-detail:hover{background:#f8fbff}
        .empty{padding:40px 16px;text-align:center;color:var(--ifk-muted);font-weight:700}
        .foot-note{margin-top:12px;padding:14px 16px;border-radius:16px;background:#f8fbff;border:1px dashed #d8e5f1;color:#48627f;font-size:13px;line-height:1.6}

        .modalx{position:fixed;inset:0;background:rgba(10,31,58,.42);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;padding:18px;z-index:9999}
        .modalx.open{display:flex}
        .modal-card{width:min(980px,100%);max-height:92vh;overflow:auto;background:#fff;border-radius:28px;box-shadow:0 34px 80px rgba(11,46,89,.22);border:1px solid rgba(223,232,242,.9)}
        .modal-head{display:flex;justify-content:space-between;gap:18px;padding:28px 30px 20px;border-bottom:1px solid var(--ifk-line)}
        .modal-title{margin:0;color:var(--ifk-navy);font-size:24px;line-height:1.2;font-weight:900}
        .modal-sub{margin-top:10px;color:var(--ifk-muted);font-size:14px}
        .modal-close{width:56px;height:56px;border:none;border-radius:18px;background:#eef3f8;color:var(--ifk-navy);font-size:24px;font-weight:900;cursor:pointer}
        .modal-body{padding:28px 30px 24px}
        .detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:22px}
        .detail-box{padding:20px 26px;border-radius:22px;background:#f7f9fc;border:1px solid #dde7f1}
        .detail-box.wide{grid-column:1/-1}
        .detail-label{margin:0 0 12px;color:#687a90;font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.06em}
        .detail-value{margin:0;color:var(--ifk-navy);font-size:19px;line-height:1.5;font-weight:900;word-break:break-word}
        .modal-foot{display:flex;justify-content:flex-end;padding:0 30px 26px}
        .modal-foot .close2{min-width:156px;height:54px;border:none;border-radius:18px;background:linear-gradient(135deg,var(--ifk-navy),#184f95);color:#fff;font-size:18px;font-weight:900;cursor:pointer}

        /*@media (max-width:1280px){
            .hero-grid{grid-template-columns:1fr}
            .toolbar{grid-template-columns:1fr 1fr}
        }
        @media (max-width:980px){
            .hero{display:block}
            .hero-right{width:auto;margin-top:14px}
            .toolbar{grid-template-columns:1fr}*/
        @media (max-width:1280px){
            .hero-grid{grid-template-columns:1fr}
            .toolbar{grid-template-columns:1fr 1fr 1fr}
            .toolbar-actions{grid-column:1/-1}
        }
        
        @media (max-width:980px){
            .hero{display:block}
            .hero-right{width:auto;margin-top:14px}
            .toolbar{grid-template-columns:1fr 1fr}
            .detail-grid{grid-template-columns:1fr}
            .detail-box.wide{grid-column:auto}
            .brand-main{grid-template-columns:1fr auto 1fr}
        }
        @media (max-width:760px){
            .wrap{padding-left:12px;padding-right:12px}
            .title h2{font-size:30px}
            .toolbar{grid-template-columns:1fr}
            .hero-right{grid-template-columns:1fr 1fr}
            .brand-main{column-gap:10px}
            .brand-ifk,.brand-school{font-size:clamp(28px,10vw,38px)}
            .brand-ifk{text-align:right}
            .brand-school{text-align:left}
            .modal-head,.modal-body,.modal-foot{padding-left:18px;padding-right:18px}
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <div class="title">
            <h2>Đơn tuyển - <?php echo html_escape($school_name); ?></h2>
            <div class="sub">Trang quản lý đơn tuyển theo trường, theo dõi công ty tiếp nhận, địa chỉ, lịch phỏng vấn, nhập cảnh và về nước theo phong cách portal hiện đại.</div>
        </div>
        <div class="navx">
            <a class="btnx" href="<?php echo site_url('school_portal/dashboard'); ?>">Dashboard</a>
            <a class="btnx" href="<?php echo site_url('school_portal/students'); ?>">Sinh viên</a>
            <a class="btnx" href="<?php echo site_url('school_portal/calendar'); ?>">Lịch</a>
            <a class="btnx primary" href="<?php echo site_url('school_portal/job_orders'); ?>">Đơn tuyển</a>
        </div>
    </div>

    <div class="hero-grid">
        <div class="cardx pad hero">
            <div class="hero-left">
                <span class="hero-kicker">IFK JOB ORDER PORTAL</span>
                <div class="hero-title">Quản lý đơn tuyển theo từng trường đối tác</div>
                <div class="hero-desc">Tập trung toàn bộ thông tin quan trọng của đơn tuyển trong một màn hình: công ty tiếp nhận, tỉnh tiếp nhận, địa chỉ làm việc, số sinh viên, lịch phỏng vấn và mốc xuất cảnh - về nước.</div>
            </div>
            <div class="hero-right">
                <div class="mini-kpi"><div class="k">Tổng đơn tuyển</div><div class="v"><?php echo count($job_orders); ?></div></div>
                <div class="mini-kpi"><div class="k">Tổng sinh viên</div><div class="v"><?php echo (int) $totalStudents; ?></div></div>
                <div class="mini-kpi"><div class="k">Có lịch PV</div><div class="v"><?php echo (int) $interviewCount; ?></div></div>
                <div class="mini-kpi"><div class="k">Đã có nhập cảnh</div><div class="v"><?php echo (int) $entryCount; ?></div></div>
            </div>
        </div>

        <div class="cardx pad brand-card">
            <div class="brand-wrap">
                <div class="brand-top">IFK SCHOOL PORTAL</div>
                <div class="brand-main">
                    <div class="brand-ifk">IFK</div>
                    <div class="brand-x">×</div>
                    <div class="brand-school"><?php echo html_escape($school_name); ?></div>
                </div>
                <div class="brand-desc">Màn hình tổng hợp đơn tuyển theo doanh nghiệp tiếp nhận và lịch thực tế của từng đơn.</div>
                <div class="brand-tags"><span>Job Orders</span><span>Company Tracking</span><span>Portal Pro</span></div>
            </div>
        </div>
    </div>

    <!--<div class="cardx pad" style="margin-bottom:18px;">
        <div class="toolbar">
            <div class="field">
                <label>Tìm nhanh</label>
                <input type="text" id="jobOrderSearch" class="form-control" placeholder="Tìm theo đơn tuyển / công ty / tỉnh / địa chỉ">
            </div>
            <div class="field">
                <label>Lọc tỉnh</label>
                <select id="prefFilter" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach (array_keys($prefectureCount) as $pref): ?>
                        <?php if ($pref !== '—'): ?>
                        <option value="<?php echo html_escape($pref); ?>"><?php echo html_escape($pref); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Lọc công ty</label>
                <select id="companyFilter" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach (array_keys($companyCount) as $company): ?>
                        <option value="<?php echo html_escape($company); ?>"><?php echo html_escape($company); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="toolbar-actions">
                <button type="button" class="action-btn primary" id="applyQuickFilter">Lọc dữ liệu</button>
                <button type="button" class="action-btn" id="resetQuickFilter">Reset</button>
            </div>
        </div>
    </div> -->
    <div class="cardx pad" style="margin-bottom:18px;">
        <!--<form class="toolbar" method="get" action="<?php echo site_url('school_portal/job_orders'); ?>">-->
        <form class="toolbar job-order-filters" method="get" action="<?php echo site_url('school_portal/job_orders'); ?>">
            <div class="field">
                <label>Phạm vi đơn tuyển</label>
                <select name="scope" id="scopeFilter" class="form-select">
                    <option value="active" <?php echo $currentScope === 'active' ? 'selected' : ''; ?>>Đơn đang tuyển</option>
                    <option value="year" <?php echo $currentScope === 'year' ? 'selected' : ''; ?>>Theo năm</option>
                    <option value="all" <?php echo $currentScope === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                </select>
            </div>
    
            <!--<div class="field" id="yearFilterWrap">
                <label>Năm</label>
                <select name="year" id="yearFilter" class="form-select">-->
            <div class="field <?php echo $currentScope !== 'year' ? 'is-disabled' : ''; ?>" id="yearFilterWrap">
                <label>Năm</label>
                <select name="year" id="yearFilter" class="form-select" <?php echo $currentScope !== 'year' ? 'disabled' : ''; ?>>
            
                    <?php foreach ($years as $y): ?>
                        <?php $y = (int)$y; ?>
                        <option value="<?php echo $y; ?>" <?php echo $currentYear === $y ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
    
            <div class="field">
                <label>Tìm nhanh</label>
                <input type="text" id="jobOrderSearch" class="form-control" placeholder="Tìm theo đơn tuyển / công ty / tỉnh / địa chỉ">
            </div>
    
            <div class="field">
                <label>Lọc tỉnh</label>
                <select id="prefFilter" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach (array_keys($prefectureCount) as $pref): ?>
                        <?php if ($pref !== '—'): ?>
                        <option value="<?php echo html_escape($pref); ?>"><?php echo html_escape($pref); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
    
            <div class="field">
                <label>Lọc công ty</label>
                <select id="companyFilter" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach (array_keys($companyCount) as $company): ?>
                        <option value="<?php echo html_escape($company); ?>"><?php echo html_escape($company); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
    
            <div class="toolbar-actions">
                <button type="submit" class="action-btn primary">Tải danh sách</button>
                <button type="button" class="action-btn" id="applyQuickFilter">Lọc nhanh</button>
                <!--<a class="action-btn" href="<?php echo site_url('school_portal/job_orders?scope=active'); ?>" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">Reset</a>-->
                <a class="action-btn action-link" href="<?php echo site_url('school_portal/job_orders?scope=active'); ?>">Reset</a>
            </div>
        </form>
    </div>

    <div class="cardx pad">
        <div class="section-head">
            <div>
                <h3>Danh sách đơn tuyển</h3>
                <div class="section-sub">Công ty nổi bật: <?php echo html_escape($topCompany); ?> · Tỉnh nổi bật: <?php echo html_escape($topPrefecture); ?></div>
            </div>
            <div class="section-sub"><span id="visibleCount"><?php echo count($job_orders); ?></span> / <?php echo count($job_orders); ?> đơn tuyển</div>
        </div>

        <div class="table-wrap">
            <table class="tablex" id="jobOrdersTable">
                <thead>
                    <tr>
                        <th style="width:110px;">Đơn tuyển</th>
                        <th style="width:310px;">Công ty tiếp nhận</th>
                        <th style="width:150px;">Tỉnh</th>
                        <th>Địa chỉ</th>
                        <th style="width:100px;">Số SV</th>
                        <th style="width:130px;">PV</th>
                        <th style="width:130px;">Nhập cảnh</th>
                        <th style="width:130px;">Về nước</th>
                        <th style="width:130px;">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($job_orders)): ?>
                    <?php foreach ($job_orders as $job): ?>
                        <?php
                            //$jobId = jop_val($job, ['job_order_id', 'job_order', 'order_id', 'id'], '—');
                            $jobId = jop_val($job, ['portal_job_id', 'id', 'job_order_id', 'job_order', 'order_id'], '—');
                            
                            $company = jop_company($job);
                            $pref = jop_prefecture($job);
                            $address = jop_address($job);
                            $students = jop_val($job, ['total_students', 'students_total', 'student_count', 'qty_students', 'quantity_students'], '0');
                            $interview = jop_date(jop_val($job, ['interview_date', 'pv_date', 'date_interview']));
                            $entry = jop_date(jop_val($job, ['entry_date', 'departure_date', 'date_entry']));
                            $return = jop_date(jop_val($job, ['return_date', 'finish_date', 'date_return']));
                            $status = jop_val($job, ['status_label', 'status_name', 'current_status', 'student_status'], 'Đang xử lý');
                            $major = jop_major($job);
                            $school = jop_school($job, $school_name ?: '—');
                            $note = jop_val($job, ['note', 'notes', 'description', 'job_note'], '—');
                            $prefView = $pref !== '' ? $pref : '—';
                            $addressView = $address !== '' ? $address : ($pref !== '' ? 'Chưa có địa chỉ chi tiết, hiện có tỉnh/khu vực: '.$pref : '—');
                        ?>
                        <tr class="job-row"
                            data-search="<?php echo html_escape(mb_strtolower($jobId . ' ' . $company . ' ' . $prefView . ' ' . $addressView . ' ' . $major)); ?>"
                            data-pref="<?php echo html_escape($prefView); ?>"
                            data-company="<?php echo html_escape($company); ?>"
                            data-job-id="<?php echo html_escape($jobId); ?>"
                            data-company-name="<?php echo html_escape($company); ?>"
                            data-prefecture="<?php echo html_escape($prefView); ?>"
                            data-address="<?php echo html_escape($addressView); ?>"
                            data-students="<?php echo html_escape($students); ?>"
                            data-interview="<?php echo html_escape($interview ?: '—'); ?>"
                            data-entry="<?php echo html_escape($entry ?: '—'); ?>"
                            data-return="<?php echo html_escape($return ?: '—'); ?>"
                            data-status="<?php echo html_escape($status); ?>"
                            data-major="<?php echo html_escape($major); ?>"
                            data-school="<?php echo html_escape($school); ?>"
                            data-note="<?php echo html_escape($note); ?>"
                            
                            data-detail-url="<?php echo site_url('school_portal/job_order/' . (int) $jobId); ?>"
                            data-print-url="<?php echo site_url('school_portal/print_job_order/' . (int) $jobId); ?>">
                            
                            <td><span class="order-id"><?php echo html_escape($jobId); ?></span></td>
                            <td>
                                <span class="company-name"><?php echo html_escape($company); ?></span>
                                <span class="subtxt"><?php echo html_escape($school); ?> · <?php echo html_escape($major); ?></span>
                            </td>
                            <td><?php echo html_escape($prefView); ?></td>
                            <td><span class="addrtxt"><?php echo html_escape($addressView); ?></span></td>
                            <td><span class="pill"><?php echo html_escape($students); ?> SV</span></td>
                            <td><?php echo $interview ? '<span class="date-chip">' . html_escape($interview) . '</span>' : '<span class="subtxt" style="margin-top:0;">—</span>'; ?></td>
                            <td><?php echo $entry ? '<span class="date-chip">' . html_escape($entry) . '</span>' : '<span class="subtxt" style="margin-top:0;">—</span>'; ?></td>
                            <td><?php echo $return ? '<span class="date-chip">' . html_escape($return) . '</span>' : '<span class="subtxt" style="margin-top:0;">—</span>'; ?></td>
                            <td>
                                <button type="button" class="btn-detail open-job-detail">Xem chi tiết</button>
                                
                                <!-- -->
                                 <a class="btn-detail" style="margin-top:8px;display:inline-flex;justify-content:center;" href="<?php echo site_url('school_portal/job_order/' . (int) $jobId); ?>">Xem chi tiết</a>
                                <a class="btn-detail" style="margin-top:8px;display:inline-flex;justify-content:center;" target="_blank" href="<?php echo site_url('school_portal/print_job_order/' . (int) $jobId); ?>">In đơn</a>
                                
                                <div style="margin-top:8px;"><span class="badge-status <?php echo html_escape(jop_badge_class($status)); ?>"><?php echo html_escape($status); ?></span></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="empty">Chưa có dữ liệu đơn tuyển.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="foot-note">Đã mở rộng cách lấy dữ liệu theo 2 lớp: ưu tiên key chuẩn, sau đó tự dò theo tên cột chứa từ khóa như company, province, prefecture, region, address. Nếu sau khi thay file mà Tỉnh hoặc Địa chỉ vẫn là “—”, thì controller/model đang chưa đẩy dữ liệu đó sang view. Hiện có <?php echo (int) $missingLocationCount; ?> đơn chưa có tỉnh/khu vực và <?php echo (int) $missingAddressCount; ?> đơn chưa có địa chỉ chi tiết trong dữ liệu trả về.</div>
    </div>
</div>

<div class="modalx" id="jobDetailModal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-head">
            <div>
                <h3 class="modal-title" id="mdTitle">Chi tiết đơn tuyển</h3>
                <div class="modal-sub" id="mdSub">Thông tin chi tiết</div>
            </div>
            <button type="button" class="modal-close" id="closeJobDetail">×</button>
        </div>
        <div class="modal-body">
            <div class="detail-grid">
                <div class="detail-box"><div class="detail-label">Đơn tuyển</div><div class="detail-value" id="mdJobId">—</div></div>
                <div class="detail-box"><div class="detail-label">Trạng thái</div><div class="detail-value" id="mdStatus">—</div></div>
                <div class="detail-box"><div class="detail-label">Công ty tiếp nhận</div><div class="detail-value" id="mdCompany">—</div></div>
                <div class="detail-box"><div class="detail-label">Tỉnh / Khu vực</div><div class="detail-value" id="mdPref">—</div></div>
                <div class="detail-box"><div class="detail-label">Trường / Ngành</div><div class="detail-value" id="mdSchoolMajor">—</div></div>
                <div class="detail-box"><div class="detail-label">Số sinh viên</div><div class="detail-value" id="mdStudents">—</div></div>
                <div class="detail-box wide"><div class="detail-label">Địa chỉ tiếp nhận</div><div class="detail-value" id="mdAddress">—</div></div>
                <div class="detail-box"><div class="detail-label">Ngày phỏng vấn</div><div class="detail-value" id="mdInterview">—</div></div>
                <div class="detail-box"><div class="detail-label">Ngày nhập cảnh</div><div class="detail-value" id="mdEntry">—</div></div>
                <div class="detail-box"><div class="detail-label">Ngày về nước</div><div class="detail-value" id="mdReturn">—</div></div>
                <div class="detail-box wide"><div class="detail-label">Ghi chú</div><div class="detail-value" id="mdNote">—</div></div>
            </div>
        </div>
        <div class="modal-foot">
            
            <!-- -->
            <a href="#" class="close2" id="jobDetailPrintLink" target="_blank" style="text-decoration:none;line-height:42px;text-align:center;">In đơn</a>
            <a href="#" class="close2" id="jobDetailOpenLink" style="text-decoration:none;line-height:42px;text-align:center;">Mở trang chi tiết</a>
            
            <button type="button" class="close2" id="closeJobDetail2">Đóng</button>
        </div>
    </div>
</div>

<script>
(function(){
    var searchInput = document.getElementById('jobOrderSearch');
    var prefFilter = document.getElementById('prefFilter');
    var companyFilter = document.getElementById('companyFilter');
    var applyBtn = document.getElementById('applyQuickFilter');
    //var resetBtn = document.getElementById('resetQuickFilter');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.job-row'));
    var visibleCount = document.getElementById('visibleCount');
    
    /*var scopeFilter = document.getElementById('scopeFilter');
    var yearFilterWrap = document.getElementById('yearFilterWrap');
    
    function toggleYearFilter(){
        if (!scopeFilter || !yearFilterWrap) return;
        yearFilterWrap.style.display = scopeFilter.value === 'year' ? '' : 'none';
    }
    
    toggleYearFilter();
    
    if (scopeFilter) {
        scopeFilter.addEventListener('change', toggleYearFilter);
    }*/
    var scopeFilter = document.getElementById('scopeFilter');
    var yearFilterWrap = document.getElementById('yearFilterWrap');
    var yearFilter = document.getElementById('yearFilter');
    
    function toggleYearFilter(){
        if (!scopeFilter || !yearFilterWrap || !yearFilter) return;
    
        var useYear = scopeFilter.value === 'year';
    
        yearFilter.disabled = !useYear;
    
        if (useYear) {
            yearFilterWrap.classList.remove('is-disabled');
        } else {
            yearFilterWrap.classList.add('is-disabled');
        }
    }
    
    toggleYearFilter();
    
    if (scopeFilter) {
        scopeFilter.addEventListener('change', toggleYearFilter);
    }

    function applyFilters(){
        var q = (searchInput.value || '').toLowerCase().trim();
        var pref = prefFilter.value || '';
        var company = companyFilter.value || '';
        var count = 0;

        rows.forEach(function(row){
            var matchQ = !q || (row.getAttribute('data-search') || '').indexOf(q) !== -1;
            var matchPref = !pref || (row.getAttribute('data-pref') || '') === pref;
            var matchCompany = !company || (row.getAttribute('data-company') || '') === company;
            var show = matchQ && matchPref && matchCompany;
            row.style.display = show ? '' : 'none';
            if (show) count++;
        });

        if (visibleCount) visibleCount.textContent = count;
    }

    if (applyBtn) applyBtn.addEventListener('click', applyFilters);
    if (searchInput) searchInput.addEventListener('keydown', function(e){ if (e.key === 'Enter') applyFilters(); });
    if (prefFilter) prefFilter.addEventListener('change', applyFilters);
    if (companyFilter) companyFilter.addEventListener('change', applyFilters);
    /*if (resetBtn) {
        resetBtn.addEventListener('click', function(){
            searchInput.value = '';
            prefFilter.value = '';
            companyFilter.value = '';
            applyFilters();
        });
    }*/

    var modal = document.getElementById('jobDetailModal');
    var close1 = document.getElementById('closeJobDetail');
    var close2 = document.getElementById('closeJobDetail2');

    function setText(id, value){
        var el = document.getElementById(id);
        if (el) el.textContent = value && String(value).trim() !== '' ? value : '—';
    }

    function openModalFromRow(row){
        setText('mdTitle', 'Đơn tuyển ' + (row.getAttribute('data-job-id') || ''));
        setText('mdSub', (row.getAttribute('data-company-name') || '—') + ' | ' + (row.getAttribute('data-prefecture') || '—'));
        setText('mdJobId', row.getAttribute('data-job-id') || '—');
        setText('mdStatus', row.getAttribute('data-status') || '—');
        setText('mdCompany', row.getAttribute('data-company-name') || '—');
        setText('mdPref', row.getAttribute('data-prefecture') || '—');
        setText('mdSchoolMajor', (row.getAttribute('data-school') || '—') + ' / ' + (row.getAttribute('data-major') || '—'));
        setText('mdStudents', (row.getAttribute('data-students') || '0') + ' sinh viên');
        setText('mdAddress', row.getAttribute('data-address') || '—');
        setText('mdInterview', row.getAttribute('data-interview') || '—');
        setText('mdEntry', row.getAttribute('data-entry') || '—');
        setText('mdReturn', row.getAttribute('data-return') || '—');
        setText('mdNote', row.getAttribute('data-note') || '—');
        
        //
         var openLink = document.getElementById('jobDetailOpenLink');
        var printLink = document.getElementById('jobDetailPrintLink');
        if (openLink) openLink.setAttribute('href', row.getAttribute('data-detail-url') || '#');
        if (printLink) printLink.setAttribute('href', row.getAttribute('data-print-url') || '#');
        
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }

    Array.prototype.slice.call(document.querySelectorAll('.open-job-detail')).forEach(function(btn){
        btn.addEventListener('click', function(){
            var row = btn.closest('.job-row');
            if (row) openModalFromRow(row);
        });
    });

    function closeModal(){
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }

    if (close1) close1.addEventListener('click', closeModal);
    if (close2) close2.addEventListener('click', closeModal);
    if (modal) modal.addEventListener('click', function(e){ if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && modal.classList.contains('open')) closeModal(); });
})();
</script>
</body>
</html>
