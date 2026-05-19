<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * View in Đơn Tuyển Internship – 2 trang:
 *  - Trang 1: Tiếng Nhật
 *  - Trang 2: Tiếng Việt
 *
 * Biến $job được truyền từ controller Internship_job_orders::print($id)
 */

/* -------------------------------------------------------------
 * 1. LẤY LOGO TỪ PERFEX
 * ----------------------------------------------------------- */
$logo_url = '';

if (function_exists('pdf_logo_url')) {
    $logo_url = pdf_logo_url();
}

// Ưu tiên logo company cấu hình trong Settings
$option_logo = get_option('company_logo');
if (!empty($option_logo)) {
    $candidate = FCPATH . 'uploads/company/' . $option_logo;
    if (file_exists($candidate)) {
        $logo_url = base_url('uploads/company/' . $option_logo);
    }
}

// Fallback nếu chưa có logo hoặc file không tồn tại
if (empty($logo_url)) {
    // Logo mặc định trên server IFK (online)
    $logo_url = 'https://work.ifk.io.vn/uploads/company/f31a955528a927060f926976605f3d1b.png';
}

// Fallback cuối cùng (assets nội bộ)
if (empty($logo_url)) {
    $logo_url = base_url('assets/images/logo.png');
}

/* -------------------------------------------------------------
 * 1.1. LẤY FAVICON TỪ HỆ THỐNG
 * ----------------------------------------------------------- */
$favicon_url = '';

// Ưu tiên option favicon của hệ thống nếu có
$option_favicon = get_option('favicon');
if (!empty($option_favicon)) {
    $candidate = FCPATH . 'uploads/company/' . $option_favicon;
    if (file_exists($candidate)) {
        $favicon_url = base_url('uploads/company/' . $option_favicon);
    }
}

// Fallback phổ biến của Perfex / hệ thống
if (empty($favicon_url)) {
    $candidate = FCPATH . 'uploads/company/favicon.png';
    if (file_exists($candidate)) {
        $favicon_url = base_url('uploads/company/favicon.png');
    }
}

// Fallback cuối cùng
if (empty($favicon_url)) {
    $favicon_url = base_url('assets/images/favicon.png');
}

/* -------------------------------------------------------------
 * 2. HÀM HỖ TRỢ
 * ----------------------------------------------------------- */
if (!function_exists('jo_get')) {
    function jo_get($arr, $key, $default = '')
    {
        return isset($arr[$key]) && $arr[$key] !== null
            ? $arr[$key]
            : $default;
    }
}

if (!function_exists('jo_format_number')) {
    function jo_format_number($v)
    {
        if ($v === null || $v === '' || !is_numeric($v)) {
            return '';
        }
        return number_format((float) $v, 0, '.', ',');
    }
}

if (!function_exists('jo_format_date')) {
    function jo_format_date($d)
    {
        if (empty($d)) {
            return '';
        }
        if (function_exists('_d')) {
            return _d($d);
        }
        return htmlspecialchars($d);
    }
}

/* -------------------------------------------------------------
 * 3. XỬ LÝ TRẠNG THÁI ĐƠN
 * ----------------------------------------------------------- */
/*$status_raw = jo_get($job, 'status', '0');

$status_label_vi = '';
$status_label_jp = '';

if (is_numeric($status_raw)) {
    $s = (int) $status_raw;

    $map_vi = [
        0 => 'Tiếp nhận đơn',
        1 => 'Đã gửi đơn cho trường đối tác',
        2 => 'Đã có sinh viên ứng tuyển',
        3 => 'Đang phỏng vấn',
        4 => 'Đã phỏng vấn – đang trả kết quả',
        5 => 'Đang làm hồ sơ',
        6 => 'Chờ kết quả COE',
        7 => 'Đã có COE – chuẩn bị nhập cảnh',
    ];

    $map_jp = [
        0 => '受付済み（求人票受領）',
        1 => '提携校へ求人紹介済み',
        2 => '学生応募あり',
        3 => '面接実施中',
        4 => '面接結果連絡・調整中',
        5 => '書類作成中',
        6 => 'COE結果待ち',
        7 => 'COE交付済み・入国準備中',
    ];

    $status_label_vi = isset($map_vi[$s]) ? $map_vi[$s] : 'Tiếp nhận đơn';
    $status_label_jp = isset($map_jp[$s]) ? $map_jp[$s] : '受付済み';
/*} else {
    // Trường hợp lưu chữ (ví dụ "received")
    $raw_trim  = trim((string)$status_raw);
    $raw_lower = function_exists('mb_strtolower')
        ? mb_strtolower($raw_trim, 'UTF-8')
        : strtolower($raw_trim);

    if ($raw_lower === 'received') {
        // map riêng cho trường hợp "received"
        $status_label_vi = 'Tiếp nhận đơn';
        $status_label_jp = '受付済み（求人票受領）';
    } else {
        // các case khác giữ nguyên
        $status_label_vi = (string)$status_raw;
        $status_label_jp = (string)$status_raw;
    }
}*/

/*} else {
    // Trường hợp lưu chữ: received, entry, cancelled, waiting_coe...
    $raw_trim  = trim((string)$status_raw);
    $raw_lower = function_exists('mb_strtolower')
        ? mb_strtolower($raw_trim, 'UTF-8')
        : strtolower($raw_trim);

    $raw_lower = str_replace([' ', '-'], '_', $raw_lower);
    $raw_lower = preg_replace('/_+/', '_', $raw_lower);

    $map_vi = [
        'received'            => 'Tiếp nhận đơn',
        'sent_schools'        => 'Đã gửi đến trường',
        'sent_to_schools'     => 'Đã gửi đến trường',
        'has_students'        => 'Đã có ứng viên',
        'has_applicants'      => 'Đã có ứng viên',
        'interview_scheduled' => 'Hẹn lịch phỏng vấn',
        'interview_done'      => 'Đã phỏng vấn – chờ kết quả',
        'interview_result'    => 'Đã phỏng vấn – chờ kết quả',
        'making_documents'    => 'Đang làm hồ sơ',
        'docs_done'           => 'Đã hoàn tất hồ sơ',
        'done_documents'      => 'Đã hoàn tất hồ sơ',
        'waiting_coe'         => 'Chờ kết quả COE',
        'coe_waiting'         => 'Chờ kết quả COE',
        'got_coe'             => 'Đã có COE – chờ nhập cảnh',
        'coe_done'            => 'Đã có COE – chờ nhập cảnh',
        'waiting_entry'       => 'Chờ nhập cảnh',
        'entry'               => 'Đã nhập cảnh',
        'entered'             => 'Đã nhập cảnh',
        'done'                => 'Đã hoàn tất chương trình',
        'cancelled'           => 'Đã hủy',
        'canceled'            => 'Đã hủy',
    ];

    $map_jp = [
        'received'            => '受付済み（求人票受領）',
        'sent_schools'        => '提携校へ求人紹介済み',
        'sent_to_schools'     => '提携校へ求人紹介済み',
        'has_students'        => '学生応募あり',
        'has_applicants'      => '学生応募あり',
        'interview_scheduled' => '面接予定',
        'interview_done'      => '面接済み・結果待ち',
        'interview_result'    => '面接済み・結果待ち',
        'making_documents'    => '書類作成中',
        'docs_done'           => '書類完了',
        'done_documents'      => '書類完了',
        'waiting_coe'         => 'COE結果待ち',
        'coe_waiting'         => 'COE結果待ち',
        'got_coe'             => 'COE交付済み・入国待ち',
        'coe_done'            => 'COE交付済み・入国待ち',
        'waiting_entry'       => '入国待ち',
        'entry'               => '入国済み',
        'entered'             => '入国済み',
        'done'                => '完了',
        'cancelled'           => 'キャンセル',
        'canceled'            => 'キャンセル',
    ];

    $status_label_vi = isset($map_vi[$raw_lower]) ? $map_vi[$raw_lower] : $raw_trim;
    $status_label_jp = isset($map_jp[$raw_lower]) ? $map_jp[$raw_lower] : $raw_trim;
}

$status_note = jo_get($job, 'status_note', ''); */

$status_raw      = jo_get($job, 'status', 'received');
$status_label_vi = im_job_order_status_label($status_raw, 'vi');
$status_label_jp = im_job_order_status_label($status_raw, 'jp');
$status_note     = jo_get($job, 'status_note', '');

/* -------------------------------------------------------------
 * 4. TẠO QR CODE — DÙNG CHUNG CHO 2 TRANG
 * ----------------------------------------------------------- */
$qr_link = admin_url('internship_management/internship_job_orders/view/' . $job['id']);
$qr_api  = 'https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=' . urlencode($qr_link);

/* -------------------------------------------------------------
 * 5. TỔNG SỐ LƯỢNG
 * ----------------------------------------------------------- */
$quantity_total = isset($job['quantity'])
    ? (int) $job['quantity']
    : (int) jo_get($job, 'quantity_total', 0);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <?php if (!empty($favicon_url)) : ?>
        <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($favicon_url); ?>">
        <link rel="shortcut icon" href="<?php echo htmlspecialchars($favicon_url); ?>">
    <?php endif; ?>
    <title>Đơn tuyển – In</title>

   <style>

/* =====================================================
   IFK PRINT DOCUMENT STYLE
   A4 Stable Version
   ===================================================== */

*{box-sizing:border-box;}

body{
    font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
    font-size:12px;
    margin:0;
    padding:0;
    color:#1e293b;
    background:#f5f5f5;
}

.page{
    width:210mm;
    margin:10px auto;
    padding:15mm 15mm 20mm;
    background:#ffffff;
}

.page + .page{page-break-before:always;}

/* ===== HEADER ===== */

.header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    border-bottom:3px solid #00325a;
    padding-bottom:12px;
    margin-bottom:16px;
    gap:20px;
}

.header-logo img{
    max-height:65px;
    width:auto;
}

.header-text{
    flex:1;
}

.header-text h2{
    margin:0;
    font-size:20px;
    font-weight:700;
    letter-spacing:.5px;
    color:#00325a;
}

.header-text small{
    color:#64748b;
    font-size:12px;
}

.meta-line{
    margin-top:3px;
    font-size:11px;
    color:#475569;
}

.header-qr img{
    width:60px;
    height:60px;
    border:1px solid rgba(0,50,90,.20);
    padding:3px;
    border-radius:4px;
    background:#fff;
}

/* ===== TITLE ===== */

.doc-title{
    text-align:center;
    font-weight:700;
    font-size:15px;
    text-transform:uppercase;
    margin:6px 0 10px;
    color:#00325a;
}

.doc-subtitle{
    text-align:center;
    font-size:11px;
    color:#64748b;
    margin-bottom:10px;
}

/* ===== STATUS BOX ===== */

.status-box{
    border:1px dashed #00a6dc;
    padding:6px 8px;
    font-size:11px;
    margin-bottom:10px;
    background:rgba(0,166,220,.05);
}

.status-label{
    font-weight:700;
    text-transform:uppercase;
    font-size:11px;
    color:#00325a;
}

.status-value{
    font-weight:600;
    color:#96bc17;
}

/* ===== SECTION LAYOUT ===== */

.section-group-row{
    display:flex;
    gap:8px;
    margin-bottom:8px;
}

.section-block{
    flex:1;
    border:1px solid rgba(0,50,90,.20);
    border-radius:4px;
    overflow:hidden;
}

.section-title{
    background:rgba(0,166,220,.08);
    padding:4px 8px;
    font-weight:700;
    font-size:12px;
    border-bottom:1px solid rgba(0,50,90,.20);
    color:#00325a;
}

.section-title span{
    color:#00325a;
}

/* ===== INFO TABLE ===== */

.info-table{
    width:100%;
    border-collapse:collapse;
    font-size:11px;
}

.info-table th,
.info-table td{
    padding:3px 6px;
    vertical-align:top;
}

.info-table th{
    width:34%;
    font-weight:600;
    color:#00325a;
    background:rgba(0,166,220,.04);
    border-bottom:1px solid #e2e8f0;
    white-space:nowrap;
}

.info-table td{
    width:66%;
    border-bottom:1px solid #e2e8f0;
}

.info-table tr:last-child th,
.info-table tr:last-child td{
    border-bottom:none;
}

.info-table td span.value{
    display:inline-block;
    min-height:13px;
}

/* Compact mode */

.section-block.compact .info-table th,
.section-block.compact .info-table td{
    padding-top:2px;
    padding-bottom:2px;
}

/* ===== SCHEDULE ===== */

.schedule-section{
    margin-top:6px;
    border:1px solid rgba(0,50,90,.20);
    border-radius:4px;
    overflow:hidden;
}

/* ===== NOTES ===== */

.notes{
    margin-top:8px;
    font-size:10px;
    color:#64748b;
}

/* ===== UTILITY ===== */

.text-right{text-align:right;}

/* ===== PRINT ===== */

@media print{

    body{background:#ffffff;}

    .page{
        margin:0;
        width:210mm;
        padding:10mm 12mm 14mm;
        page-break-after:always;
    }

    .page:last-child{
        page-break-after:auto;
    }
}

</style>
</head>
<body>

<!-- ==========================================================
     PAGE 1 – JAPANESE
========================================================== -->
<div class="page">

    <div class="header">
        <div class="header-logo">
            <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Company Logo">
        </div>

        <div class="header-text">
            <h2>求人票（インターンシップ）</h2>
            <small>IFK Internship Program - Job Order (Japanese)</small>
            <div class="meta-line">
                作成日:
                <?php echo jo_format_date(isset($job['datecreated']) ? $job['datecreated'] : date('Y-m-d')); ?>
            </div>
        </div>

        <div class="header-qr">
            <img src="<?php echo $qr_api; ?>" alt="QR Code">
        </div>
    </div>

    <div class="doc-title">ĐƠN TUYỂN THỰC TẬP SINH NHẬT BẢN</div>
    <div class="doc-subtitle">Bản tiếng Nhật</div>

    <div class="status-box">
        <span class="status-label">ステータス: </span>
        <span class="status-value"><?php echo htmlspecialchars($status_label_jp); ?></span>
        <?php if (!empty($status_note)) : ?>
            <br>
            <span style="font-size:10px;">
                備考: <?php echo nl2br(htmlspecialchars($status_note)); ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- 1) COMPANY JP + 2) JOB JP -->
    <div class="section-group-row">

        <div class="section-block">
            <div class="section-title"><span>1) 企業情報</span></div>
            <table class="info-table">
                <tr>
                    <th>会社名</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'company_name_jp')); ?></span></td>
                </tr>
                <tr>
                    <th>代表取締役</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'company_president')); ?></span></td>
                </tr>
                <tr>
                    <th>所在地</th>
                    <td><span class="value"><?php echo nl2br(htmlspecialchars(jo_get($job, 'address_jp'))); ?></span></td>
                </tr>
                <tr>
                    <th>従業員数</th>
                    <td><span class="value"><?php echo jo_format_number(jo_get($job, 'employee_count')); ?></span></td>
                </tr>
                <tr>
                    <th>設立</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'established_year')); ?></span></td>
                </tr>
                <tr>
                    <th>電話番号 / Website</th>
                    <td>
                        <span class="value">
                            <?php echo htmlspecialchars(jo_get($job, 'company_phone')); ?>
                            <?php if (jo_get($job, 'website')) : ?>
                                &nbsp;/&nbsp;<?php echo htmlspecialchars($job['website']); ?>
                            <?php endif; ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-block">
            <div class="section-title"><span>2) 募集職種</span></div>
            <table class="info-table">
                <tr>
                    <th>職種</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'job_title')); ?></span></td>
                </tr>
                <tr>
                    <th>勤務地</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'workplace_jp')); ?></span></td>
                </tr>
                <tr>
                    <th>業務内容</th>
                    <td><span class="value"><?php echo nl2br(htmlspecialchars(jo_get($job, 'job_description_jp'))); ?></span></td>
                </tr>
            </table>
        </div>

    </div>

    <!-- 3) REQUIREMENTS JP + 4) WORK CONDITIONS JP -->
    <div class="section-group-row">

        <div class="section-block compact">
            <div class="section-title"><span>3) 応募条件</span></div>
            <table class="info-table">
                <tr>
                    <th>人数</th>
                    <td>
                        <span class="value">
                            男性: <?php echo jo_format_number(jo_get($job, 'quantity_male', 0)); ?> /
                            女性: <?php echo jo_format_number(jo_get($job, 'quantity_female', 0)); ?> /
                            合計: <?php echo jo_format_number($quantity_total); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>年齢</th>
                    <td>
                        <span class="value">
                            <?php echo htmlspecialchars(jo_get($job, 'age_from')); ?>
                            ～ <?php echo htmlspecialchars(jo_get($job, 'age_to')); ?> 歳
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>学歴</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'education')); ?></span></td>
                </tr>
                <tr>
                    <th>専攻</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'major_jp')); ?></span></td>
                </tr>
                <tr>
                    <th>日本語レベル</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'japanese_level')); ?></span></td>
                </tr>
                <tr>
                    <th>英語レベル</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'english_level')); ?></span></td>
                </tr>
            </table>
        </div>

        <div class="section-block compact">
            <div class="section-title"><span>4) 雇用条件</span></div>
            <table class="info-table">
                <tr>
                    <th>契約期間</th>
                    <td>
                        <span class="value">
                            <?php echo htmlspecialchars(jo_get($job, 'contract_months')); ?> ヶ月
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>勤務日数</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'work_days')); ?></span></td>
                </tr>
                <tr>
                    <th>休日</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'holidays')); ?></span></td>
                </tr>
                <tr>
                    <th>就業時間</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'working_hours')); ?></span></td>
                </tr>
                <tr>
                    <th>休憩時間</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'break_time')); ?></span></td>
                </tr>
                <tr>
                    <th>残業</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'overtime')); ?></span></td>
                </tr>
            </table>
        </div>

    </div>

    <!-- 5) SALARY JP + 6) BENEFITS JP -->
    <div class="section-group-row">

        <div class="section-block compact">
            <div class="section-title"><span>5) 給与・控除</span></div>
            <table class="info-table">
                <tr>
                    <th>総支給額</th>
                    <td><span class="value"><?php echo jo_format_number(jo_get($job, 'salary_total')); ?> 円</span></td>
                </tr>
                <tr>
                    <th>手取り</th>
                    <td><span class="value"><?php echo jo_format_number(jo_get($job, 'salary_net')); ?> 円</span></td>
                </tr>
                <tr>
                    <th>税金 / 保険</th>
                    <td>
                        <span class="value">
                            税金: <?php echo jo_format_number(jo_get($job, 'tax')); ?> 円 /
                            保険: <?php echo jo_format_number(jo_get($job, 'insurance')); ?> 円
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>寮費 / 光熱費</th>
                    <td>
                        <span class="value">
                            寮費: <?php echo jo_format_number(jo_get($job, 'dormitory')); ?> 円 /
                            光熱費: <?php echo jo_format_number(jo_get($job, 'utilities')); ?> 円
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>食費</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'food')); ?></span></td>
                </tr>
                <tr>
                    <th>賞与 / 昇給</th>
                    <td>
                        <span class="value">
                            賞与: <?php echo htmlspecialchars(jo_get($job, 'bonus')); ?> /
                            昇給: <?php echo htmlspecialchars(jo_get($job, 'raise_salary')); ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-block compact">
            <div class="section-title"><span>6) 福利厚生</span></div>
            <table class="info-table">
                <tr>
                    <th>チケット補助</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'benefit_flight')); ?></span></td>
                </tr>
                <tr>
                    <th>その他</th>
                    <td><span class="value"><?php echo nl2br(htmlspecialchars(jo_get($job, 'benefit_other'))); ?></span></td>
                </tr>
            </table>
        </div>

    </div>

    <!-- 7) SCHEDULE JP -->
    <div class="schedule-section">
        <div class="section-title"><span>7) 面接・入国スケジュール</span></div>
        <table class="info-table">
            <tr>
                <th>面接日</th>
                <td><span class="value"><?php echo jo_format_date(jo_get($job, 'interview_date')); ?></span></td>
            </tr>
            <tr>
                <th>入国予定日</th>
                <td><span class="value"><?php echo jo_format_date(jo_get($job, 'entry_date')); ?></span></td>
            </tr>
            <tr>
                <th>面接場所</th>
                <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'interview_place')); ?></span></td>
            </tr>
        </table>
    </div>

    <div class="notes">
        ※ Đây là bản tiếng Nhật, dùng trao đổi với nghiệp đoàn / xí nghiệp Nhật Bản.
    </div>

</div><!-- END PAGE 1 -->

<!-- ==========================================================
     PAGE 2 – VIETNAMESE
========================================================== -->
<div class="page">

    <div class="header">
        <div class="header-logo">
            <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Company Logo">
        </div>

        <div class="header-text">
            <h2>PHIẾU THÔNG TIN ĐƠN TUYỂN</h2>
            <small>IFK Internship Program - Job Order (Vietnamese)</small>
            <div class="meta-line">
                Ngày lập:
                <?php echo jo_format_date(isset($job['datecreated']) ? $job['datecreated'] : date('Y-m-d')); ?>
            </div>
        </div>

        <div class="header-qr">
            <!-- Dùng chung 1 QR cho cả 2 trang -->
            <img src="<?php echo $qr_api; ?>" alt="QR Code">
        </div>
    </div>

    <div class="doc-title">ĐƠN TUYỂN THỰC TẬP SINH NHẬT BẢN</div>
    <div class="doc-subtitle">Bản tiếng Việt</div>

    <div class="status-box">
        <span class="status-label">TRẠNG THÁI ĐƠN: </span>
        <span class="status-value"><?php echo htmlspecialchars($status_label_vi); ?></span>
        <?php if (!empty($status_note)) : ?>
            <br>
            <span style="font-size:10px;">
                Ghi chú: <?php echo nl2br(htmlspecialchars($status_note)); ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- 1) COMPANY VI + 2) JOB VI -->
    <div class="section-group-row">

        <!-- COMPANY INFO -->
        <div class="section-block">
            <div class="section-title"><span>1) THÔNG TIN CÔNG TY</span></div>
            <table class="info-table">
                <tr>
                    <th>Tên công ty</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'company_name_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Chủ tịch / Giám đốc</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'company_president_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Địa chỉ</th>
                    <td><span class="value"><?php echo nl2br(htmlspecialchars(jo_get($job, 'address_vi'))); ?></span></td>
                </tr>
                <tr>
                    <th>Số nhân viên</th>
                    <td><span class="value"><?php echo jo_format_number(jo_get($job, 'employee_count_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Năm thành lập</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'established_year_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Điện thoại / Website</th>
                    <td>
                        <span class="value">
                            <?php echo htmlspecialchars(jo_get($job, 'company_phone_vi')); ?>
                            <?php if (jo_get($job, 'website_vi')) : ?>
                                &nbsp;/&nbsp;<?php echo htmlspecialchars($job['website_vi']); ?>
                            <?php endif; ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- JOB INFO -->
        <div class="section-block">
            <div class="section-title"><span>2) VỊ TRÍ TUYỂN DỤNG</span></div>
            <table class="info-table">
                <tr>
                    <th>Tên vị trí</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'job_title_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Nơi làm việc</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'workplace_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Mô tả công việc</th>
                    <td><span class="value"><?php echo nl2br(htmlspecialchars(jo_get($job, 'job_description_vi'))); ?></span></td>
                </tr>
            </table>
        </div>

    </div>

    <!-- 3) REQUIREMENTS VI + 4) WORK CONDITIONS VI -->
    <div class="section-group-row">

        <!-- REQUIREMENTS -->
        <div class="section-block compact">
            <div class="section-title"><span>3) ĐIỀU KIỆN ỨNG VIÊN</span></div>
            <table class="info-table">
                <tr>
                    <th>Số lượng</th>
                    <td>
                        <span class="value">
                            Nam:
                            <?php echo jo_format_number(jo_get($job, 'quantity_male_vi', jo_get($job, 'quantity_male', 0))); ?>
                            /
                            Nữ:
                            <?php echo jo_format_number(jo_get($job, 'quantity_female_vi', jo_get($job, 'quantity_female', 0))); ?>
                            /
                            Tổng:
                            <?php echo jo_format_number($quantity_total); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Độ tuổi</th>
                    <td>
                        <span class="value">
                            Từ
                            <?php echo htmlspecialchars(jo_get($job, 'age_from_vi', jo_get($job, 'age_from'))); ?>
                            đến
                            <?php echo htmlspecialchars(jo_get($job, 'age_to_vi', jo_get($job, 'age_to'))); ?>
                            tuổi
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Giới tính / Ngành</th>
                    <td>
                        <span class="value">
                            Giới tính:
                            <?php echo htmlspecialchars(jo_get($job, 'gender_vi', jo_get($job, 'gender'))); ?><br>
                            Chuyên ngành:
                            <?php echo htmlspecialchars(jo_get($job, 'major_vi')); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Trình độ học vấn</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'education_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Tiếng Nhật</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'japanese_level_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Tiếng Anh</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'english_level_vi')); ?></span></td>
                </tr>
            </table>
        </div>

        <!-- WORK CONDITIONS -->
        <div class="section-block compact">
            <div class="section-title"><span>4) ĐIỀU KIỆN LÀM VIỆC</span></div>
            <table class="info-table">
                <tr>
                    <th>Thời hạn HĐ</th>
                    <td>
                        <span class="value">
                            <?php echo htmlspecialchars(jo_get($job, 'contract_months_vi', jo_get($job, 'contract_months'))); ?>
                            tháng
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Số ngày làm việc</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'work_days_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Ngày nghỉ</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'holidays_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Giờ làm việc</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'working_hours_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Giờ nghỉ</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'break_time_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Tăng ca</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'overtime_vi')); ?></span></td>
                </tr>
            </table>
        </div>

    </div>

    <!-- 5) SALARY VI + 6) BENEFITS VI -->
    <div class="section-group-row">

        <!-- SALARY -->
        <div class="section-block compact">
            <div class="section-title"><span>5) LƯƠNG &amp; KHẤU TRỪ</span></div>
            <table class="info-table">
                <tr>
                    <th>Lương tổng</th>
                    <td>
                        <span class="value">
                            <?php echo jo_format_number(jo_get($job, 'salary_total_vi', jo_get($job, 'salary_total'))); ?>
                            Yên
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Lương thực nhận</th>
                    <td>
                        <span class="value">
                            <?php echo jo_format_number(jo_get($job, 'salary_net_vi', jo_get($job, 'salary_net'))); ?>
                            Yên
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Thuế / BH</th>
                    <td>
                        <span class="value">
                            Thuế:
                            <?php echo jo_format_number(jo_get($job, 'tax_vi', jo_get($job, 'tax'))); ?>
                            Yên /
                            BH:
                            <?php echo jo_format_number(jo_get($job, 'insurance_vi', jo_get($job, 'insurance'))); ?>
                            Yên
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Ký túc xá / Điện nước</th>
                    <td>
                        <span class="value">
                            KTX:
                            <?php echo jo_format_number(jo_get($job, 'dormitory_vi', jo_get($job, 'dormitory'))); ?>
                            Yên /
                            Điện nước:
                            <?php echo jo_format_number(jo_get($job, 'utilities_vi', jo_get($job, 'utilities'))); ?>
                            Yên
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Chi phí ăn uống</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'food_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Bonus / Tăng lương</th>
                    <td>
                        <span class="value">
                            Bonus: <?php echo htmlspecialchars(jo_get($job, 'bonus_vi')); ?><br>
                            Tăng lương: <?php echo htmlspecialchars(jo_get($job, 'raise_salary_vi')); ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- BENEFITS -->
        <div class="section-block compact">
            <div class="section-title"><span>6) PHÚC LỢI</span></div>
            <table class="info-table">
                <tr>
                    <th>Hỗ trợ vé máy bay</th>
                    <td><span class="value"><?php echo htmlspecialchars(jo_get($job, 'benefit_flight_vi')); ?></span></td>
                </tr>
                <tr>
                    <th>Đãi ngộ khác</th>
                    <td><span class="value"><?php echo nl2br(htmlspecialchars(jo_get($job, 'benefit_other_vi'))); ?></span></td>
                </tr>
            </table>
        </div>

    </div>

    <!-- 7) SCHEDULE VI -->
    <div class="schedule-section">
        <div class="section-title"><span>7) LỊCH TRÌNH</span></div>
        <table class="info-table">
            <tr>
                <th>Ngày phỏng vấn</th>
                <td>
                    <span class="value">
                        <?php echo jo_format_date(jo_get($job, 'interview_date_vi', jo_get($job, 'interview_date'))); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Ngày dự kiến nhập cảnh</th>
                <td>
                    <span class="value">
                        <?php echo jo_format_date(jo_get($job, 'entry_date_vi', jo_get($job, 'entry_date'))); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Địa điểm phỏng vấn</th>
                <td>
                    <span class="value">
                        <?php echo htmlspecialchars(jo_get($job, 'interview_place_vi', jo_get($job, 'interview_place'))); ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <div class="notes">
        Ghi chú: Bản tiếng Việt dùng tư vấn sinh viên, đối tác trường &amp; lưu hồ sơ nội bộ IFK.
    </div>

</div><!-- END PAGE 2 -->

</body>
</html>