<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$CI->load->helper('internship_management/job_order_status');

$logo_url = '';

if (function_exists('pdf_logo_url')) { $logo_url = pdf_logo_url(); }
$option_logo = function_exists('get_option') ? get_option('company_logo') : '';
if (!empty($option_logo)) {
    $candidate = FCPATH . 'uploads/company/' . $option_logo;
    if (file_exists($candidate)) { $logo_url = base_url('uploads/company/' . $option_logo); }
}
if (empty($logo_url)) { $logo_url = base_url('uploads/company/f31a955528a927060f926976605f3d1b.png'); }
if (empty($logo_url)) { $logo_url = base_url('uploads/company/favicon.png'); }
$favicon_url = base_url('uploads/company/favicon.png');
if (!function_exists('jo_get')) {
    function jo_get($arr, $key, $default = '') { return isset($arr[$key]) && $arr[$key] !== null ? $arr[$key] : $default; }
}
if (!function_exists('jo_format_number')) {
    function jo_format_number($v) { return ($v === null || $v === '' || !is_numeric($v)) ? '' : number_format((float)$v, 0, '.', ','); }
}
if (!function_exists('jo_format_date')) {
    function jo_format_date($d) { if (empty($d)) return ''; return function_exists('_d') ? _d($d) : htmlspecialchars($d); }
}
/*$status_raw = jo_get($job, 'status', 'received');
$status_label_vi = (string)$status_raw;
$status_label_jp = (string)$status_raw;
if (trim((string)$status_raw) === 'received') { $status_label_vi = 'Tiếp nhận đơn'; $status_label_jp = '受付済み（求人票受領）'; }
$status_note = jo_get($job, 'status_note', '');*/

$status_raw      = jo_get($job, 'status', 'received');
$status_label_vi = im_job_order_status_label($status_raw, 'vi');
$status_label_jp = im_job_order_status_label($status_raw, 'jp');
$status_note     = jo_get($job, 'status_note', '');

$qr_link = site_url('school_portal/job_order/' . ($job['id'] ?? 0));
$qr_api  = 'https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=' . urlencode($qr_link);
$quantity_total = isset($job['quantity']) ? (int)$job['quantity'] : (int)jo_get($job, 'quantity_total', 0);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>In đơn tuyển</title>
    <link rel="icon" type="image/png" href="<?php echo $favicon_url; ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo $favicon_url; ?>">
    <style>
*{box-sizing:border-box;}
body{font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:12px;margin:0;padding:0;color:#1e293b;background:#f5f5f5}
.page{width:210mm;min-height:297mm;margin:10px auto;padding:15mm 15mm 20mm;background:#ffffff}
.page + .page{page-break-before:always;}
.header{display:flex;align-items:center;justify-content:space-between;border-bottom:3px solid #00325a;padding-bottom:12px;margin-bottom:16px;gap:20px}
.header-logo img{max-height:78px;width:auto}
.header-text{flex:1}
.header-text h2{margin:0;font-size:20px;font-weight:700;letter-spacing:.5px;color:#00325a}
.header-text small{color:#64748b;font-size:12px}
.meta-line{margin-top:3px;font-size:11px;color:#475569}.header-qr img{width:78px;height:78px;border:1px solid rgba(0,50,90,.20);padding:3px;border-radius:4px;background:#fff}
.doc-title{text-align:center;font-weight:700;font-size:15px;text-transform:uppercase;margin:6px 0 4px;color:#00325a}
.doc-subtitle{text-align:center;font-size:11px;color:#64748b;margin-bottom:10px}
.status-box{border:1px dashed #00a6dc;padding:6px 8px;font-size:11px;margin-bottom:10px;background:rgba(0,166,220,.05)}
.status-label{font-weight:700;text-transform:uppercase;font-size:11px;color:#00325a}.status-value{font-weight:600;color:#96bc17}
.section-group-row{display:flex;gap:8px;margin-bottom:8px}.section-block{flex:1;border:1px solid rgba(0,50,90,.20);border-radius:4px;overflow:hidden}.section-title{background:rgba(0,166,220,.08);padding:4px 8px;font-weight:700;font-size:12px;border-bottom:1px solid rgba(0,50,90,.20);color:#00325a}.section-title span{color:#00325a}
.info-table{width:100%;border-collapse:collapse;font-size:11px}.info-table th,.info-table td{padding:3px 6px;vertical-align:top}.info-table th{width:34%;font-weight:600;color:#00325a;background:rgba(0,166,220,.04);border-bottom:1px solid #e2e8f0;white-space:nowrap}.info-table td{width:66%;border-bottom:1px solid #e2e8f0}.info-table tr:last-child th,.info-table tr:last-child td{border-bottom:none}.section-block.compact .info-table th,.section-block.compact .info-table td{padding-top:2px;padding-bottom:2px}
.schedule-section{margin-top:6px;border:1px solid rgba(0,50,90,.20);border-radius:4px;overflow:hidden}.notes{margin-top:8px;font-size:10px;color:#64748b}.toolbar{text-align:center;padding:12px}.toolbar .btn{display:inline-block;padding:8px 14px;border-radius:6px;border:1px solid #cbd5e1;background:#fff;color:#00325a;font-weight:600;text-decoration:none;margin:0 4px}.toolbar .btn.primary{background:#00325a;color:#fff;border-color:#00325a}
@media print{body{background:#fff}.page{margin:0 auto;padding:12mm 14mm 16mm;box-shadow:none}.toolbar{display:none !important;}}
    </style>
</head>
<body>
<div class="toolbar"><button class="btn primary" onclick="window.print()">In đơn</button><button class="btn" onclick="window.close()">Đóng</button></div>

<div class="page">
    <div class="header">
        <div class="header-logo"><img src="<?php echo $logo_url; ?>" alt="IFK"></div>
        <div class="header-text">
            <h2>求人票（インターンシップ）</h2>
            <small>IFK Internship Program - Job Order (Japanese)</small>
            <div class="meta-line">作成日: <?php echo date('Y-m-d H:i:s', strtotime((string)jo_get($job,'datecreated', date('Y-m-d H:i:s')))); ?></div>
        </div>
        <div class="header-qr"><img src="<?php echo $qr_api; ?>" alt="QR"></div>
    </div>

    <div class="doc-title">ĐƠN TUYỂN THỰC TẬP SINH NHẬT BẢN</div>
    <div class="doc-subtitle">Bản tiếng Nhật</div>

    <div class="status-box">
        <span class="status-label">ステータス:</span>
        <span class="status-value"><?php echo htmlspecialchars($status_label_jp); ?></span>
        <?php if ($status_note !== ''): ?><div><?php echo nl2br(htmlspecialchars($status_note)); ?></div><?php endif; ?>
    </div>

    <div class="section-group-row">
        <div class="section-block">
            <div class="section-title">1) 企業情報</div>
            <table class="info-table">
                <tr><th>会社名</th><td><?php echo htmlspecialchars(jo_get($job,'company_name_jp')); ?></td></tr>
                <tr><th>代表取締役</th><td><?php echo htmlspecialchars(jo_get($job,'company_president')); ?></td></tr>
                <tr><th>所在地</th><td><?php echo nl2br(htmlspecialchars(jo_get($job,'address_jp'))); ?></td></tr>
                <tr><th>従業員数</th><td><?php echo htmlspecialchars(jo_get($job,'employee_count')); ?></td></tr>
                <tr><th>設立</th><td><?php echo htmlspecialchars(jo_get($job,'established_year')); ?></td></tr>
                <tr><th>電話番号 / Website</th><td><?php echo htmlspecialchars(jo_get($job,'company_phone')); ?><br><?php echo htmlspecialchars(jo_get($job,'website')); ?></td></tr>
            </table>
        </div>
        <div class="section-block">
            <div class="section-title">2) 募集職種</div>
            <table class="info-table">
                <tr><th>職種</th><td><?php echo htmlspecialchars(jo_get($job,'job_title')); ?></td></tr>
                <tr><th>勤務地</th><td><?php echo htmlspecialchars(jo_get($job,'workplace_jp')); ?></td></tr>
                <tr><th>業務内容</th><td><?php echo nl2br(htmlspecialchars(jo_get($job,'job_description_jp'))); ?></td></tr>
            </table>
        </div>
    </div>

    <div class="section-group-row">
        <div class="section-block compact">
            <div class="section-title">3) 応募条件</div>
            <table class="info-table">
                <tr><th>人数</th><td>男性: <?php echo htmlspecialchars(jo_get($job,'quantity_male')); ?> / 女性: <?php echo htmlspecialchars(jo_get($job,'quantity_female')); ?> / 合計: <?php echo $quantity_total; ?></td></tr>
                <tr><th>年齢</th><td><?php echo htmlspecialchars(jo_get($job,'age_from')); ?> ～ <?php echo htmlspecialchars(jo_get($job,'age_to')); ?> 歳</td></tr>
                <tr><th>学歴</th><td><?php echo htmlspecialchars(jo_get($job,'education')); ?></td></tr>
                <tr><th>専攻</th><td><?php echo htmlspecialchars(jo_get($job,'major_jp')); ?></td></tr>
                <tr><th>日本語レベル</th><td><?php echo htmlspecialchars(jo_get($job,'japanese_level')); ?></td></tr>
                <tr><th>英語レベル</th><td><?php echo htmlspecialchars(jo_get($job,'english_level')); ?></td></tr>
            </table>
        </div>
        <div class="section-block compact">
            <div class="section-title">4) 雇用条件</div>
            <table class="info-table">
                <tr><th>契約期間</th><td><?php echo htmlspecialchars(jo_get($job,'contract_months')); ?> ヶ月</td></tr>
                <tr><th>勤務日数</th><td><?php echo htmlspecialchars(jo_get($job,'work_days')); ?></td></tr>
                <tr><th>休日</th><td><?php echo htmlspecialchars(jo_get($job,'holidays')); ?></td></tr>
                <tr><th>就業時間</th><td><?php echo htmlspecialchars(jo_get($job,'working_hours')); ?></td></tr>
                <tr><th>休憩時間</th><td><?php echo htmlspecialchars(jo_get($job,'break_time')); ?></td></tr>
                <tr><th>残業</th><td><?php echo htmlspecialchars(jo_get($job,'overtime')); ?></td></tr>
            </table>
        </div>
    </div>

    <div class="section-group-row">
        <div class="section-block compact">
            <div class="section-title">5) 給与・控除</div>
            <table class="info-table">
                <tr><th>総支給額</th><td><?php echo jo_format_number(jo_get($job,'salary_total')); ?> 円</td></tr>
                <tr><th>手取り</th><td><?php echo jo_format_number(jo_get($job,'salary_net')); ?> 円</td></tr>
                <tr><th>税金 / 保険</th><td>税金: <?php echo jo_format_number(jo_get($job,'tax')); ?> 円 / 保険: <?php echo jo_format_number(jo_get($job,'insurance')); ?> 円</td></tr>
                <tr><th>寮費 / 光熱費</th><td>寮費: <?php echo jo_format_number(jo_get($job,'dormitory')); ?> 円 / 光熱費: <?php echo jo_format_number(jo_get($job,'utilities')); ?> 円</td></tr>
                <tr><th>食費</th><td><?php echo htmlspecialchars(jo_get($job,'food')); ?></td></tr>
                <tr><th>賞与 / 昇給</th><td>賞与: <?php echo htmlspecialchars(jo_get($job,'bonus')); ?> / 昇給: <?php echo htmlspecialchars(jo_get($job,'raise_salary')); ?></td></tr>
            </table>
        </div>
        <div class="section-block compact">
            <div class="section-title">6) 福利厚生</div>
            <table class="info-table">
                <tr><th>チケット補助</th><td><?php echo htmlspecialchars(jo_get($job,'benefit_flight')); ?></td></tr>
                <tr><th>その他</th><td><?php echo nl2br(htmlspecialchars(jo_get($job,'benefit_other'))); ?></td></tr>
            </table>
        </div>
    </div>

    <div class="schedule-section">
        <div class="section-title">7) 面接・入国スケジュール</div>
        <table class="info-table">
            <tr><th>面接日</th><td><?php echo jo_format_date(jo_get($job,'interview_date')); ?></td></tr>
            <tr><th>入国予定日</th><td><?php echo jo_format_date(jo_get($job,'entry_date')); ?></td></tr>
            <tr><th>面接場所</th><td><?php echo htmlspecialchars(jo_get($job,'interview_place')); ?></td></tr>
        </table>
    </div>

    <div class="notes">※ Đây là bản tiếng Nhật, dùng trao đổi với trường đối tác / doanh nghiệp Nhật Bản.</div>
</div>

<div class="page">
    <div class="header">
        <div class="header-logo"><img src="<?php echo $logo_url; ?>" alt="IFK"></div>
        <div class="header-text">
            <h2>PHIẾU THÔNG TIN ĐƠN TUYỂN</h2>
            <small>IFK Internship Program - Job Order (Vietnamese)</small>
            <div class="meta-line">Ngày lập: <?php echo date('Y-m-d H:i:s', strtotime((string)jo_get($job,'datecreated', date('Y-m-d H:i:s')))); ?></div>
        </div>
        <div class="header-qr"><img src="<?php echo $qr_api; ?>" alt="QR"></div>
    </div>

    <div class="doc-title">ĐƠN TUYỂN THỰC TẬP SINH NHẬT BẢN</div>
    <div class="doc-subtitle">Bản tiếng Việt</div>

    <div class="status-box">
        <span class="status-label">TRẠNG THÁI ĐƠN:</span>
        <span class="status-value"><?php echo htmlspecialchars($status_label_vi); ?></span>
        <?php if ($status_note !== ''): ?><div>Ghi chú: <?php echo nl2br(htmlspecialchars($status_note)); ?></div><?php endif; ?>
    </div>

    <div class="section-group-row">
        <div class="section-block">
            <div class="section-title">1) THÔNG TIN CÔNG TY</div>
            <table class="info-table">
                <tr><th>Tên công ty</th><td><?php echo htmlspecialchars(jo_get($job,'company_name_vi')); ?></td></tr>
                <tr><th>Chủ tịch / Giám đốc</th><td><?php echo htmlspecialchars(jo_get($job,'company_president_vi')); ?></td></tr>
                <tr><th>Địa chỉ</th><td><?php echo nl2br(htmlspecialchars(jo_get($job,'address_vi'))); ?></td></tr>
                <tr><th>Số nhân viên</th><td><?php echo htmlspecialchars(jo_get($job,'employee_count_vi', jo_get($job,'employee_count'))); ?></td></tr>
                <tr><th>Năm thành lập</th><td><?php echo htmlspecialchars(jo_get($job,'established_year_vi', jo_get($job,'established_year'))); ?></td></tr>
                <tr><th>Điện thoại / Website</th><td><?php echo htmlspecialchars(jo_get($job,'company_phone_vi', jo_get($job,'company_phone'))); ?><br><?php echo htmlspecialchars(jo_get($job,'website_vi', jo_get($job,'website'))); ?></td></tr>
            </table>
        </div>
        <div class="section-block">
            <div class="section-title">2) VỊ TRÍ TUYỂN DỤNG</div>
            <table class="info-table">
                <tr><th>Tên vị trí</th><td><?php echo htmlspecialchars(jo_get($job,'job_title_vi')); ?></td></tr>
                <tr><th>Nơi làm việc</th><td><?php echo htmlspecialchars(jo_get($job,'workplace_vi')); ?></td></tr>
                <tr><th>Mô tả công việc</th><td><?php echo nl2br(htmlspecialchars(jo_get($job,'job_description_vi'))); ?></td></tr>
            </table>
        </div>
    </div>

    <div class="section-group-row">
        <div class="section-block compact">
            <div class="section-title">3) ĐIỀU KIỆN ỨNG VIÊN</div>
            <table class="info-table">
                <tr><th>Số lượng</th><td>Nam: <?php echo htmlspecialchars(jo_get($job,'quantity_male_vi', jo_get($job,'quantity_male'))); ?> / Nữ: <?php echo htmlspecialchars(jo_get($job,'quantity_female_vi', jo_get($job,'quantity_female'))); ?> / Tổng: <?php echo htmlspecialchars(jo_get($job,'quantity_total_vi', $quantity_total)); ?></td></tr>
                <tr><th>Độ tuổi</th><td>Từ <?php echo htmlspecialchars(jo_get($job,'age_from_vi', jo_get($job,'age_from'))); ?> đến <?php echo htmlspecialchars(jo_get($job,'age_to_vi', jo_get($job,'age_to'))); ?> tuổi</td></tr>
                <tr><th>Giới tính / Ngành</th><td><?php echo htmlspecialchars(jo_get($job,'gender_vi')); ?><br><?php echo htmlspecialchars(jo_get($job,'major_vi', jo_get($job,'major_jp'))); ?></td></tr>
                <tr><th>Trình độ học vấn</th><td><?php echo htmlspecialchars(jo_get($job,'education_vi', jo_get($job,'education'))); ?></td></tr>
                <tr><th>Tiếng Nhật</th><td><?php echo htmlspecialchars(jo_get($job,'japanese_level_vi', jo_get($job,'japanese_level'))); ?></td></tr>
                <tr><th>Tiếng Anh</th><td><?php echo htmlspecialchars(jo_get($job,'english_level_vi', jo_get($job,'english_level'))); ?></td></tr>
            </table>
        </div>
        <div class="section-block compact">
            <div class="section-title">4) ĐIỀU KIỆN LÀM VIỆC</div>
            <table class="info-table">
                <tr><th>Thời hạn HĐ</th><td><?php echo htmlspecialchars(jo_get($job,'contract_months_vi', jo_get($job,'contract_months'))); ?> tháng</td></tr>
                <tr><th>Số ngày làm việc</th><td><?php echo htmlspecialchars(jo_get($job,'work_days_vi', jo_get($job,'work_days'))); ?></td></tr>
                <tr><th>Ngày nghỉ</th><td><?php echo htmlspecialchars(jo_get($job,'holidays_vi', jo_get($job,'holidays'))); ?></td></tr>
                <tr><th>Giờ làm việc</th><td><?php echo htmlspecialchars(jo_get($job,'working_hours_vi', jo_get($job,'working_hours'))); ?></td></tr>
                <tr><th>Giờ nghỉ</th><td><?php echo htmlspecialchars(jo_get($job,'break_time_vi', jo_get($job,'break_time'))); ?></td></tr>
                <tr><th>Tăng ca</th><td><?php echo htmlspecialchars(jo_get($job,'overtime_vi', jo_get($job,'overtime'))); ?></td></tr>
            </table>
        </div>
    </div>

    <div class="section-group-row">
        <div class="section-block compact">
            <div class="section-title">5) LƯƠNG & KHẤU TRỪ</div>
            <table class="info-table">
                <tr><th>Lương tổng</th><td><?php echo jo_format_number(jo_get($job,'salary_total_vi', jo_get($job,'salary_total'))); ?> Yên</td></tr>
                <tr><th>Lương thực nhận</th><td><?php echo jo_format_number(jo_get($job,'salary_net_vi', jo_get($job,'salary_net'))); ?> Yên</td></tr>
                <tr><th>Thuế / BH</th><td>Thuế: <?php echo jo_format_number(jo_get($job,'tax_vi', jo_get($job,'tax'))); ?> Yên / BH: <?php echo jo_format_number(jo_get($job,'insurance_vi', jo_get($job,'insurance'))); ?> Yên</td></tr>
                <tr><th>Ký túc xá / Điện nước</th><td>KTX: <?php echo jo_format_number(jo_get($job,'dormitory_vi', jo_get($job,'dormitory'))); ?> Yên / Điện nước: <?php echo jo_format_number(jo_get($job,'utilities_vi', jo_get($job,'utilities'))); ?> Yên</td></tr>
                <tr><th>Chi phí ăn uống</th><td><?php echo htmlspecialchars(jo_get($job,'food_vi', jo_get($job,'food'))); ?></td></tr>
                <tr><th>Bonus / Tăng lương</th><td>Bonus: <?php echo htmlspecialchars(jo_get($job,'bonus_vi', jo_get($job,'bonus'))); ?><br>Tăng lương: <?php echo htmlspecialchars(jo_get($job,'raise_salary_vi', jo_get($job,'raise_salary'))); ?></td></tr>
            </table>
        </div>
        <div class="section-block compact">
            <div class="section-title">6) PHÚC LỢI</div>
            <table class="info-table">
                <tr><th>Hỗ trợ vé máy bay</th><td><?php echo htmlspecialchars(jo_get($job,'benefit_flight_vi', jo_get($job,'benefit_flight'))); ?></td></tr>
                <tr><th>Đãi ngộ khác</th><td><?php echo nl2br(htmlspecialchars(jo_get($job,'benefit_other_vi', jo_get($job,'benefit_other')))); ?></td></tr>
            </table>
        </div>
    </div>

    <div class="schedule-section">
        <div class="section-title">7) LỊCH TRÌNH</div>
        <table class="info-table">
            <tr><th>Ngày phỏng vấn</th><td><?php echo jo_format_date(jo_get($job,'interview_date_vi', jo_get($job,'interview_date'))); ?></td></tr>
            <tr><th>Ngày dự kiến nhập cảnh</th><td><?php echo jo_format_date(jo_get($job,'entry_date_vi', jo_get($job,'entry_date'))); ?></td></tr>
            <tr><th>Địa điểm phỏng vấn</th><td><?php echo htmlspecialchars(jo_get($job,'interview_place_vi', jo_get($job,'interview_place'))); ?></td></tr>
        </table>
    </div>

    <div class="notes">Ghi chú: Bản tiếng Việt dùng tư vấn sinh viên, đối tác trường và lưu hồ sơ nội bộ IFK.</div>
</div>
</body>
</html>
