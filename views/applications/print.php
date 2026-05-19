<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// ============================================
// Lấy logo công ty
// ============================================
$logo_url = '';
$opt_logo = get_option('company_logo');

if (!empty($opt_logo) && file_exists(FCPATH.'uploads/company/'.$opt_logo)) {
    $logo_url = base_url('uploads/company/'.$opt_logo);
} else {
    $logo_url = 'https://work.ifk.io.vn/uploads/company/f31a955528a927060f926976605f3d1b.png';
}

// ============================================
// QR CODE
// ============================================
$qr_link = admin_url('internship_management/internship_applications/view/'.$app['id']);
$qr_api  = 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&data='.urlencode($qr_link);

// ============================================
// Hàm tiện ích
// ============================================
/*function ap_get($a, $k, $d='—') { return isset($a[$k]) && $a[$k] !== '' ? $a[$k] : $d; }
function ap_dt($d) { return !empty($d) ? _d($d) : '—'; }*/


function ap_get($a, $k, $d='—') {
    return isset($a[$k]) && trim((string)$a[$k]) !== '' ? $a[$k] : $d;
}

function ap_pick($a, $keys, $d='—') {
    foreach ((array)$keys as $k) {
        if (isset($a[$k]) && trim((string)$a[$k]) !== '') {
            return $a[$k];
        }
    }
    return $d;
}

function ap_dt($d) {
    return !empty($d) && $d !== '—' ? _d($d) : '—';
}

function ap_gender_vi($v) {
    $v = strtolower(trim((string)$v));
    $map = [
        'male'   => 'Nam',
        'm'      => 'Nam',
        'nam'    => 'Nam',
        'female' => 'Nữ',
        'f'      => 'Nữ',
        'nu'     => 'Nữ',
        'nữ'     => 'Nữ',
        'other'  => 'Khác',
    ];
    return $map[$v] ?? ($v !== '' ? ucfirst($v) : '—');
}

function ap_gender_jp($v) {
    $v = strtolower(trim((string)$v));
    $map = [
        'male'   => '男性',
        'm'      => '男性',
        'nam'    => '男性',
        'female' => '女性',
        'f'      => '女性',
        'nu'     => '女性',
        'nữ'     => '女性',
        'other'  => 'その他',
    ];
    return $map[$v] ?? ($v !== '' ? $v : '—');
}

function ap_jlpt_vi($v) {
    $v = trim((string)$v);
    if ($v === '') return '—';
    if (strtolower($v) === 'none') return 'Chưa có';
    return $v;
}

function ap_jlpt_jp($v) {
    $v = trim((string)$v);
    if ($v === '') return '—';
    if (strtolower($v) === 'none') return 'なし';
    return $v;
}

function ap_interview_vi($v) {
    $v = strtolower(trim((string)$v));
    $map = [
        'pass' => 'Đậu',
        'fail' => 'Rớt',
        'dat'  => 'Đậu',
        'rot'  => 'Rớt',
    ];
    return $map[$v] ?? ($v !== '' ? $v : '—');
}

function ap_interview_jp($v) {
    $v = strtolower(trim((string)$v));
    $map = [
        'pass' => '合格',
        'fail' => '不合格',
        'dat'  => '合格',
        'rot'  => '不合格',
    ];
    return $map[$v] ?? ($v !== '' ? $v : '—');
}


/*function ap_dt_jp($d) {
    if (empty($d) || $d === '—') return '—';

    $ts = strtotime($d);
    if (!$ts) return $d;

    return date('Y年m月d日', $ts);
}*/

function ap_dt_jp($d) {
    $d = trim((string)$d);

    // chặn toàn bộ date rác
    if (
        $d === '' ||
        $d === '—' ||
        $d === '0000-00-00' ||
        $d === '0000-00-00 00:00:00' ||
        strtolower($d) === 'null'
    ) {
        return '—';
    }

    $ts = strtotime($d);
    if (!$ts) {
        return '—';
    }

    return date('Y年m月d日', $ts);
}

function ap_name_jp($app) {
    $name = trim((string)ap_pick($app, ['full_name_katakana', 'name_katakana', 'furigana', 'full_name'], '—'));
    return $name !== '' ? $name : '—';
}

function ap_address_jp_format($addr) {
    $addr = trim((string)$addr);
    if ($addr === '') {
        return '—';
    }

    // Nếu đã là địa chỉ Nhật thì giữ nguyên
    if (preg_match('/[ぁ-んァ-ヶ一-龯〒]/u', $addr)) {
        return $addr;
    }

    // Chuẩn hóa khoảng trắng
    $addr = preg_replace('/\s+/u', ' ', $addr);

    // Tách theo dấu phẩy, chấm phẩy, xuống dòng
    $parts = preg_split('/\s*[,;\n\r]+\s*/u', $addr);
    $parts = array_values(array_filter(array_map('trim', $parts), function ($v) {
        return $v !== '';
    }));

    if (empty($parts)) {
        return '—';
    }

    // Đảo thứ tự theo kiểu Nhật: lớn -> nhỏ
    $parts = array_reverse($parts);

    // Quy tắc thay thế tiền tố/hậu tố
    $rules = [
        // Thành phố trực thuộc / tỉnh thành lớn
        '/^TP\.?\s*Hồ\s*Chí\s*Minh$/iu' => 'ホーチミン市',
        '/^TP\.?\s*Ho\s*Chi\s*Minh$/iu' => 'ホーチミン市',
        '/^Hồ\s*Chí\s*Minh$/iu'         => 'ホーチミン市',
        '/^Ho\s*Chi\s*Minh$/iu'         => 'ホーチミン市',
        '/^TP\.?\s*Hà\s*Nội$/iu'        => 'ハノイ市',
        '/^TP\.?\s*Ha\s*Noi$/iu'        => 'ハノイ市',
        '/^Hà\s*Nội$/iu'                => 'ハノイ市',
        '/^Ha\s*Noi$/iu'                => 'ハノイ市',
        '/^TP\.?\s*Đà\s*Nẵng$/iu'       => 'ダナン市',
        '/^TP\.?\s*Da\s*Nang$/iu'       => 'ダナン市',
        '/^Đà\s*Nẵng$/iu'               => 'ダナン市',
        '/^Da\s*Nang$/iu'               => 'ダナン市',

        // Tỉnh / Thành phố
        '/^Tỉnh\s+(.+)$/iu'             => '$1省',
        '/^Tinh\s+(.+)$/iu'             => '$1省',
        '/^Thành\s+phố\s+(.+)$/iu'      => '$1市',
        '/^Thanh\s+pho\s+(.+)$/iu'      => '$1市',
        '/^TP\.?\s+(.+)$/iu'            => '$1市',

        // Quận / Huyện / Thị xã / Thị trấn
        '/^Quận\s+(.+)$/iu'             => '$1区',
        '/^Quan\s+(.+)$/iu'             => '$1区',
        '/^Q\.?\s*(.+)$/iu'             => '$1区',
        '/^Huyện\s+(.+)$/iu'            => '$1郡',
        '/^Huyen\s+(.+)$/iu'            => '$1郡',
        '/^H\.?\s*(.+)$/iu'             => '$1郡',
        '/^Thị\s*xã\s+(.+)$/iu'         => '$1町',
        '/^Thi\s*xa\s+(.+)$/iu'         => '$1町',
        '/^Thị\s*trấn\s+(.+)$/iu'       => '$1町',
        '/^Thi\s*tran\s+(.+)$/iu'       => '$1町',

        // Phường / Xã
        '/^Phường\s+(.+)$/iu'           => '$1街区',
        '/^Phuong\s+(.+)$/iu'           => '$1街区',
        '/^P\.?\s*(.+)$/iu'             => '$1街区',
        '/^Xã\s+(.+)$/iu'               => '$1地区',
        '/^Xa\s+(.+)$/iu'               => '$1地区',

        // Thôn / Ấp / Bản / Khóm / Khu phố / Tổ
        '/^Thôn\s+(.+)$/iu'             => '$1村',
        '/^Thon\s+(.+)$/iu'             => '$1村',
        '/^Ấp\s+(.+)$/iu'               => '$1村',
        '/^Ap\s+(.+)$/iu'               => '$1村',
        '/^Bản\s+(.+)$/iu'              => '$1村',
        '/^Ban\s+(.+)$/iu'              => '$1村',
        '/^Khóm\s+(.+)$/iu'             => '$1地区',
        '/^Khom\s+(.+)$/iu'             => '$1地区',
        '/^Khu\s*phố\s+(.+)$/iu'        => '$1街区',
        '/^Khu\s*pho\s+(.+)$/iu'        => '$1街区',
        '/^Tổ\s+(.+)$/iu'               => '$1組',
        '/^To\s+(.+)$/iu'               => '$1組',
    ];

    foreach ($parts as &$p) {
        $p = trim($p);

        foreach ($rules as $pattern => $replacement) {
            if (preg_match($pattern, $p)) {
                $p = preg_replace($pattern, $replacement, $p);
                break;
            }
        }
    }
    unset($p);

    return implode('、', $parts);
}


// MAP trạng thái → tiếng Việt
/*$vi_status = [
    'applied'               => 'Ứng tuyển',
    'interview_scheduled'   => 'Hẹn phỏng vấn',
    'interview_passed'      => 'Đậu phỏng vấn',
    'interview_fail'        => 'Rớt phỏng vấn',
    'docs_preparing'        => 'Chuẩn bị hồ sơ',
    'prepare_documents'     => 'Chuẩn bị hồ sơ',
    'docs_done'             => 'Hoàn thành hồ sơ',
    'done_documents'        => 'Hoàn thành hồ sơ',
    'coe_waiting'           => 'Chờ COE',
    'visa_processing'       => 'Làm visa',
    'ticket_booking'        => 'Mua vé nhập cảnh',
    'pre_departure'         => 'Chuẩn bị bay',
    'in_japan'              => 'Đã sang Nhật',
    'returned'              => 'Đã về nước',
    'cancelled'             => 'Huỷ',
    'not_updated'           => 'Chưa cập nhật',
];

// MAP trạng thái → tiếng Nhật
$jp_status = [
    'applied'               => '応募済み',
    'interview_scheduled'   => '面接予定',
    'interview_passed'      => '面接合格',
    'interview_fail'        => '面接不合格',
    'docs_preparing'        => '書類準備中',
    'prepare_documents'     => '書類準備中',
    'docs_done'             => '書類完了',
    'done_documents'        => '書類完了',
    'coe_waiting'           => 'COE待ち',
    'visa_processing'       => 'ビザ申請中',
    'ticket_booking'        => '航空券手配',
    'pre_departure'         => '出国準備',
    'in_japan'              => '来日済み',
    'returned'              => '帰国済み',
    'cancelled'             => 'キャンセル',
    'not_updated'           => '未更新',
    
];

$vi_status_label = isset($vi_status[$app['status']]) ? $vi_status[$app['status']] : $app['status'];
$jp_status_label = isset($jp_status[$app['status']]) ? $jp_status[$app['status']] : $app['status'];
*/

$CI = &get_instance();
$CI->load->helper('internship_management/internship_status');

$status_for_print = $app['dossier_progress'] ?? ($app['status'] ?? '');

$vi_status_label = im_status_label_vi($status_for_print);
$jp_status_label = im_status_label_jp($status_for_print);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">

<title>In ứng viên – <?= html_escape($app['full_name']); ?></title>

<!-- Favicon mặc định hệ thống -->
<link rel="icon"
      type="image/png"
      href="<?= base_url('uploads/company/favicon.png'); ?>">

<link rel="shortcut icon"
      href="<?= base_url('uploads/company/favicon.png'); ?>">
<style>

/* =====================================================
   IFK PROFILE PRINT TEMPLATE
   A4 Stable - Corporate Version
   ===================================================== */

*{box-sizing:border-box;}

body{
    font-family:DejaVu Sans, Arial, Helvetica, sans-serif;
    margin:0;
    padding:0;
    background:#f5f5f5;
    font-size:13px;
    color:#1e293b;
}

.page{
    background:#ffffff;
    width:210mm;
    margin:12px auto;
    padding:14mm;
    border:1px solid rgba(0,50,90,.15);
}

.page + .page{
    page-break-before:always;
}

/* ===== HEADER ===== */

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    border-bottom:3px solid #00325a;
    padding-bottom:12px;
    margin-bottom:18px;
}

.header-logo img{
    max-height:70px;
}

.header-text h2{
    margin:0;
    font-size:22px;
    font-weight:700;
    color:#00325a;
}

.header-text small{
    color:#64748b;
}

.header-qr img{
    width:80px;
    height:80px;
    border:1px solid rgba(0,50,90,.25);
    padding:4px;
    border-radius:6px;
}

/* ===== SECTION TITLE ===== */

.section-title{
    margin-top:16px;
    padding:6px 10px;
    background:rgba(0,166,220,.08);
    font-weight:700;
    border-left:4px solid #00325a;
    color:#00325a;
}

/* ===== INFO TABLE ===== */

.info-table{
    width:100%;
    border-collapse:collapse;
    margin-top:8px;
}

.info-table th{
    width:28%;
    padding:6px;
    background:rgba(0,166,220,.04);
    border-bottom:1px solid #e2e8f0;
    font-weight:600;
    color:#00325a;
}

.info-table td{
    padding:6px;
    border-bottom:1px solid #e2e8f0;
}

/* ===== AVATAR ===== */

.avatar-box{
    text-align:right;
}

.avatar-box img{
    width:110px;
    height:140px;
    object-fit:cover;
    border-radius:6px;
    border:1px solid rgba(0,50,90,.25);
}

/* ===== STATUS BOX ===== */

.status-box{
    background:rgba(150,188,23,.15);
    border:1px solid rgba(150,188,23,.35);
    padding:10px;
    margin-top:14px;
    border-radius:8px;
    color:#2f5e00;
    font-weight:600;
}

/* ===== NOTES ===== */

.notes{
    margin-top:14px;
    font-size:12px;
    color:#64748b;
}

/* ===== PRINT ===== */

@media print{
    .page{
        margin:0;
        border:none;
    }
}

</style>

</head>

<body>

<!-- ============================================
     PAGE 1 – TIẾNG VIỆT
============================================ -->
<div class="page">

    <div class="header">
        <div class="header-logo"><img src="<?= $logo_url ?>"></div>

        <div class="header-text">
            <h2>THÔNG TIN ỨNG VIÊN</h2>
            <small>IFK Internship Program</small><br>
            <small>Ngày in: <?= date('d/m/Y') ?></small>
        </div>

        <div class="header-qr"><img src="<?= $qr_api ?>"></div>
    </div>

    <table width="100%">
        <tr>
            <td style="width:70%; vertical-align: top;">

                <!--<div class="section-title">1) Thông tin cá nhân</div>
                <table class="info-table">
                    <tr><th>Họ tên</th><td><?= ap_get($app,'full_name') ?></td></tr>
                    <tr><th>Ngày sinh</th><td><?= ap_dt(ap_get($app,'birthday')) ?></td></tr>
                    <tr><th>Giới tính</th><td><?= ap_get($app,'gender') ?></td></tr>
                    <tr><th>SĐT học sinh</th><td><?= ap_get($app,'phone_student') ?></td></tr>
                    <tr><th>SĐT phụ huynh</th><td><?= ap_get($app,'phone_parent') ?></td></tr>
                    <tr><th>Email</th><td><?= ap_get($app,'email') ?></td></tr>
                    <tr><th>Địa chỉ</th><td><?= nl2br(ap_get($app,'address')) ?></td></tr>
                </table> --> 
                
                <div class="section-title">1) Thông tin cá nhân</div>
                <table class="info-table">
                    <tr><th>Họ tên</th><td><?= ap_get($app,'full_name') ?></td></tr>
                    <tr><th>Ngày sinh</th><td><?= ap_dt(ap_get($app,'birthday')) ?></td></tr>
                    <tr><th>Giới tính</th><td><?= ap_gender_vi(ap_get($app,'gender','')) ?></td></tr>
                    <tr><th>SĐT học sinh</th><td><?= ap_get($app,'phone_student') ?></td></tr>
                    <tr><th>SĐT phụ huynh</th><td><?= ap_get($app,'phone_parent') ?></td></tr>
                    <tr><th>Email</th><td><?= ap_get($app,'email') ?></td></tr>
                    <tr><th>Địa chỉ</th><td><?= nl2br(ap_get($app,'address')) ?></td></tr>
                </table>
                

            </td>

            <td class="avatar-box">
                <img src="<?=
                    !empty($app['avatar'])
                    ? base_url('uploads/internship_avatar/'.$app['avatar'])
                    : base_url('modules/internship_management/assets/no-image.png')
                ?>">
            </td>
        </tr>
    </table>

    <!-- <div class="section-title">2) Thông tin học vấn</div>
    <table class="info-table">
        <tr><th>Trường</th><td><?= ap_get($app,'school_name') ?></td></tr>
        <tr><th>Ngành</th><td><?= ap_get($app,'major') ?></td></tr>
        <tr><th>Trình độ tiếng Nhật</th><td><?= ap_get($app,'japanese_level') ?></td></tr>
    </table> -->
    
    <div class="section-title">2) Thông tin học vấn</div>
		<table class="info-table">
			<tr><th>Trường</th><td><?= ap_pick($app, ['school_name_vi','school_name']) ?></td></tr>
			<tr><th>Ngành</th><td><?= ap_pick($app, ['major_vi','major']) ?></td></tr>
			<tr><th>Trình độ tiếng Nhật</th><td><?= ap_jlpt_vi(ap_get($app,'japanese_level','')) ?></td></tr>
			<tr><th>Trình độ tiếng Anh</th><td><?= ap_get($app,'english_level') ?></td></tr>
		</table>
    
    <!-- <div class="section-title">3) Thông tin ứng tuyển</div>
    <table class="info-table">
        <tr><th>Đơn tuyển</th><td><?= ap_get($app,'job_name') ?></td></tr>
        <tr><th>Công ty</th><td><?= ap_get($app,'company_name_vi') ?></td></tr>
        <tr><th>Ngày ứng tuyển</th><td><?= ap_dt(ap_get($app,'datecreated')) ?></td></tr>
    </table> --> 
    
    <div class="section-title">3) Thông tin ứng tuyển</div>
		<table class="info-table">
			<tr><th>Đơn tuyển</th><td><?= ap_get($app,'job_name') ?></td></tr>
			<tr><th>Công ty</th><td><?= ap_get($app,'company_name_vi') ?></td></tr>
			<tr><th>Ngày ứng tuyển</th><td><?= ap_dt(ap_get($app,'datecreated')) ?></td></tr>
		</table>
		
	<!-- <div class="section-title">3) Thông tin ứng tuyển</div>
        <table class="info-table">
            <tr><th>Đơn tuyển</th><td><?= ap_pick($app, ['job_name','job_title']) ?></td></tr>
            <tr><th>Công ty tiếp nhận</th><td><?= ap_pick($app, ['company_name_vi','receiver_company']) ?></td></tr>
            <tr><th>Tỉnh tiếp nhận</th><td><?= ap_get($app,'receiver_prefecture') ?></td></tr>
            <tr><th>Địa chỉ tiếp nhận</th><td><?= nl2br(ap_get($app,'receiver_address')) ?></td></tr>
            <tr><th>Ngày phỏng vấn</th><td><?= ap_dt(ap_get($app,'interview_date')) ?></td></tr>
            <tr><th>Ngày nhập cảnh (dự kiến)</th><td><?= ap_dt(ap_get($app,'entry_date')) ?></td></tr>
            <tr><th>Thời gian thực tập</th><td><?= ap_get($app,'months') !== '—' ? ap_get($app,'months').' tháng' : '—' ?></td></tr>
            <tr><th>Ngày về nước (dự kiến)</th><td><?= ap_dt(ap_get($app,'return_date')) ?></td></tr>
            <tr><th>Ngày ứng tuyển</th><td><?= ap_dt(ap_pick($app, ['apply_date','datecreated','created_at'])) ?></td></tr>
        </table> -->
		

    <!-- <div class="section-title">4) Trạng thái hồ sơ</div>
    <div class="status-box">
        <b>Trạng thái:</b> <?= $vi_status_label ?><br>
        <b>Ghi chú:</b> <?= nl2br(ap_get($app,'status_note','—')) ?>
    </div> --> 
    
    <div class="section-title">4) Trạng thái hồ sơ</div>
		<div class="status-box">
			<b>Trạng thái:</b> <?= $vi_status_label ?><br>
			<b>Kết quả phỏng vấn:</b> <?= ap_interview_vi(ap_get($app,'interview_result','')) ?><br>
			<b>Ghi chú:</b> <?= nl2br(ap_get($app,'status_note','—')) ?>
		</div>

    <table class="info-table">
        <tr>
            <th>File CV</th>
            <td>
                <?php if (!empty($app['cv_file'])): ?>
                    <a href="<?= admin_url('internship_management/internship_applications/preview_file/'.$app['id']) ?>" target="_blank">
                        Xem CV (PDF/Word)
                    </a>
                <?php else: ?>
                    Chưa nộp
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <div class="notes">
        ※ Phiếu tiếng Việt – dùng tư vấn sinh viên, nghiệp đoàn, đối tác trường.
    </div>
</div>



<!-- ============================================
     PAGE 2 – TIẾNG NHẬT
============================================ -->
<!-- ============================================
     PAGE 2 – TIẾNG NHẬT
============================================ -->
<div class="page">

    <div class="header">
        <div class="header-logo"><img src="<?= $logo_url ?>"></div>

        <div class="header-text">
            <h2>候補者情報（インターンシップ）</h2>
            <small>IFK Internship Program</small><br>
            <!-- <small>印刷日: <?= date('Y-m-d') ?></small> -->
            <small>印刷日: <?= date('Y年m月d日') ?></small>
        </div>

        <div class="header-qr"><img src="<?= $qr_api ?>"></div>
    </div>


    <!-- ✨ THÊM AVATAR CHO TRANG NHẬT -->
    <table width="100%">
        <tr>
            <td style="width:70%; vertical-align: top;">

                <!-- <div class="section-title">１）個人情報</div>
                <table class="info-table">
                    <tr><th>氏名</th><td><?= ap_get($app,'full_name') ?></td></tr>
                    <tr><th>生年月日</th><td><?= ap_dt(ap_get($app,'birthday')) ?></td></tr>
                    <tr><th>性別</th><td><?= ap_get($app,'gender') ?></td></tr>
                    <tr><th>学生電話</th><td><?= ap_get($app,'phone_student') ?></td></tr>
                    <tr><th>保護者電話</th><td><?= ap_get($app,'phone_parent') ?></td></tr>
                    <tr><th>Email</th><td><?= ap_get($app,'email') ?></td></tr>
                    <tr><th>住所</th><td><?= nl2br(ap_get($app,'address')) ?></td></tr>
                </table> -->
                
                <div class="section-title">１）個人情報</div>
        		<table class="info-table">
        			<!-- <tr><th>氏名</th><td><?= ap_get($app,'full_name') ?></td></tr> -->
        			<tr><th>氏名</th><td><?= ap_name_jp($app) ?></td></tr>
        			<!--<tr><th>生年月日</th><td><?= ap_dt(ap_get($app,'birthday')) ?></td></tr>-->
        			<tr><th>生年月日</th><td><?= ap_dt_jp(ap_get($app,'birthday')) ?></td></tr>
        			<tr><th>性別</th><td><?= ap_gender_jp(ap_get($app,'gender','')) ?></td></tr>
        			<tr><th>学生電話</th><td><?= ap_get($app,'phone_student') ?></td></tr>
        			<tr><th>保護者電話</th><td><?= ap_get($app,'phone_parent') ?></td></tr>
        			<tr><th>Email</th><td><?= ap_get($app,'email') ?></td></tr>
        			<!-- <tr><th>住所</th><td><?= nl2br(ap_get($app,'address')) ?></td></tr> -->
        			<tr><th>住所</th><td><?= nl2br(ap_address_jp_format(ap_pick($app, ['address_jp','address']))) ?></td></tr>
        		</table>

            </td>

            <td class="avatar-box">
                <img src="<?=
                    !empty($app['avatar'])
                    ? base_url('uploads/internship_avatar/'.$app['avatar'])
                    : base_url('modules/internship_management/assets/no-image.png')
                ?>">
            </td>
        </tr>
     </table>
    <!-- <div class="section-title">２）学歴</div>
    <table class="info-table">
        <tr><th>学校名</th><td><?= ap_get($app,'school_name') ?></td></tr>
        <tr><th>専攻</th><td><?= ap_get($app,'major') ?></td></tr>
        <tr><th>日本語レベル</th><td><?= ap_get($app,'japanese_level') ?></td></tr>
    </table> -->
    
    <div class="section-title">２）学歴</div>
		<table class="info-table">
			<tr><th>学校名</th><td><?= ap_pick($app, ['school_name_ja','school_name_jp','school_name']) ?></td></tr>
			<tr><th>専攻</th><td><?= ap_pick($app, ['major_jp','major']) ?></td></tr>
			<tr><th>日本語レベル</th><td><?= ap_jlpt_jp(ap_get($app,'japanese_level','')) ?></td></tr>
			<tr><th>英語レベル</th><td><?= ap_get($app,'english_level') ?></td></tr>
		</table>

    <!-- <div class="section-title">３）応募情報</div>
    <table class="info-table">
        <tr><th>応募求人</th><td><?= ap_get($app,'job_name') ?></td></tr>
        <tr><th>企業名</th><td><?= ap_get($app,'company_name_jp','—') ?></td></tr>
        <tr><th>応募日</th><td><?= ap_dt(ap_get($app,'datecreated')) ?></td></tr>
    </table> -->
    
    <div class="section-title">３）応募情報</div>
		<table class="info-table">
			<tr><th>応募求人</th><td><?= ap_pick($app, ['job_name','job_title']) ?></td></tr>
			<tr><th>受入企業</th><td><?= ap_pick($app, ['company_name_jp','receiver_company']) ?></td></tr>
			<tr><th>受入都道府県</th><td><?= ap_get($app,'receiver_prefecture') ?></td></tr>
			<!--<tr><th>受入住所</th><td><?= nl2br(ap_get($app,'receiver_address')) ?></td></tr>-->
			<tr><th>受入住所</th><td><?= nl2br(ap_address_jp_format(ap_pick($app, ['receiver_address_jp','address_jp','receiver_address']))) ?></td></tr>
			<!--<tr><th>面接日</th><td><?= ap_dt(ap_get($app,'interview_date')) ?></td></tr>-->
			<tr><th>面接日</th><td><?= ap_dt_jp(ap_get($app,'interview_date')) ?></td></tr>
			<!-- <tr><th>入国予定日</th><td><?= ap_dt(ap_get($app,'entry_date')) ?></td></tr> -->
			<tr><th>入国予定日</th><td><?= ap_dt_jp(ap_get($app,'entry_date')) ?></td></tr>
			<tr><th>実習期間</th><td><?= ap_get($app,'months') !== '—' ? ap_get($app,'months').'か月' : '—' ?></td></tr>
			<!-- <tr><th>帰国予定日</th><td><?= ap_dt(ap_get($app,'return_date')) ?></td></tr> -->
			<tr><th>帰国予定日</th><td><?= ap_dt_jp(ap_get($app,'return_date')) ?></td></tr>
			<!--<tr><th>応募日</th><td><?= ap_dt(ap_pick($app, ['apply_date','datecreated','created_at'])) ?></td></tr>-->
			<tr><th>応募日</th><td><?= ap_dt_jp(ap_pick($app, ['apply_date','datecreated','created_at'])) ?></td></tr>
		</table>

    <!-- <div class="section-title">４）書類ステータス</div>
    <div class="status-box">
        <b>ステータス:</b> <?= $jp_status_label ?><br>
        <b>備考:</b> <?= nl2br(ap_get($app,'status_note','—')) ?>
    </div> -->
    
    <div class="section-title">４）書類ステータス</div>
		<div class="status-box">
			<b>ステータス:</b> <?= $jp_status_label ?><br>
			<b>面接結果:</b> <?= ap_interview_jp(ap_get($app,'interview_result','')) ?><br>
			<b>備考:</b> <?= nl2br(ap_get($app,'status_note','—')) ?>
		</div>
    <div class="notes">
        ※ 日本語版 – 技能実習・インターンシップの面接・書類提出用
    </div>

</div>

</body>
</html>