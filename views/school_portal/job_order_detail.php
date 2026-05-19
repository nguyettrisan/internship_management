<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$job = is_array($job ?? null) ? $job : [];
$school_name = (string)($school_name ?? '');
if (!function_exists('spv')) {
    function spv($job, $key, $default = '') { return isset($job[$key]) && $job[$key] !== null ? $job[$key] : $default; }
}
if (!function_exists('spv_num')) {
    function spv_num($job, $key, $default = 0) {
        $v = spv($job, $key, $default);
        return ($v === '' || $v === null || !is_numeric($v)) ? '' : number_format((float)$v, 0, '.', ',');
    }
}
if (!function_exists('spv_date')) {
    function spv_date($job, $key) {
        $v = spv($job, $key, '');
        if ($v === '') return '';
        return function_exists('_d') ? _d($v) : html_escape($v);
    }
}
$status_raw = (string)spv($job, 'status', 'received');
$status_note = (string)spv($job, 'status_note', '');
$favicon_url = base_url('uploads/company/favicon.png');
$print_url = site_url('school_portal/print_job_order/' . (int)spv($job, 'id', 0));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chi tiết đơn tuyển</title>
    <link rel="icon" type="image/png" href="<?php echo $favicon_url; ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo $favicon_url; ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>">
    <style>
        body{background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;margin:0;padding:18px;color:#1f2937}
        .view-container{max-width:1000px;margin:auto;background:#fff;padding:25px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1)}
        .view-table{width:100%;border-collapse:collapse;margin-bottom:25px;font-size:14px}
        .view-table th{background:#f4f6f9;padding:10px;font-weight:bold;border:1px solid #ddd;width:260px}
        .view-table td{padding:9px;border:1px solid #ddd}
        .section-title{font-weight:bold;font-size:18px;margin:25px 0 10px;padding-bottom:5px;border-bottom:2px solid #007bff;color:#0b2e59}
        .topbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
        .school-note{color:#64748b;margin-top:6px}
        .nav-tabs{margin-top:25px}
        .tab-pane{display:none}.tab-pane.active{display:block}
        .status-box{margin-top:18px;border:1px dashed #00a6dc;padding:10px 12px;background:rgba(0,166,220,.05);font-size:14px}
        .status-label{font-weight:700;color:#00325a;text-transform:uppercase}
        .status-value{font-weight:700;color:#96bc17}
        .muted{color:#64748b}
        @media (max-width: 767px){ .view-container{padding:15px} .view-table th{width:40%} }
    </style>
</head>
<body>
<div class="view-container">
    <div class="topbar">
        <div>
            <h3 class="bold" style="margin:0;">📄 CHI TIẾT ĐƠN TUYỂN</h3>
            <div class="school-note">Cổng trường: <?php echo html_escape($school_name); ?></div>
        </div>
        <div>
            <a href="<?php echo site_url('school_portal/job_orders'); ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Quay lại</a>
            <a href="<?php echo $print_url; ?>" class="btn btn-primary" target="_blank"><i class="fa fa-print"></i> In đơn</a>
        </div>
    </div>

    <div class="status-box">
        <span class="status-label">Trạng thái đơn:</span>
        <span class="status-value"><?php echo html_escape($status_raw); ?></span>
        <?php if ($status_note !== ''): ?>
            <div class="muted">Ghi chú: <?php echo nl2br(html_escape($status_note)); ?></div>
        <?php endif; ?>
    </div>

    <ul class="nav nav-tabs mtop30" role="tablist">
        <li class="active"><a href="#jp" data-target="jp">🇯🇵 Tiếng Nhật</a></li>
        <li><a href="#vi" data-target="vi">🇻🇳 Tiếng Việt</a></li>
    </ul>

    <div class="tab-content mtop20">
        <div class="tab-pane active" id="jp">
            <div class="section-title">1) 企業情報 – Thông tin công ty (JP)</div>
            <table class="view-table">
                <tr><th>会社名</th><td><?= html_escape(spv($job,'company_name_jp')) ?></td></tr>
                <tr><th>代表取締役</th><td><?= html_escape(spv($job,'company_president')) ?></td></tr>
                <tr><th>住所</th><td><?= nl2br(html_escape(spv($job,'address_jp'))) ?></td></tr>
                <tr><th>従業員数</th><td><?= html_escape(spv($job,'employee_count')) ?></td></tr>
                <tr><th>設立</th><td><?= html_escape(spv($job,'established_year')) ?></td></tr>
                <tr><th>Website</th><td><?= html_escape(spv($job,'website')) ?></td></tr>
                <tr><th>電話番号</th><td><?= html_escape(spv($job,'company_phone')) ?></td></tr>
            </table>

            <div class="section-title">2) 募集職種 – Vị trí tuyển dụng (JP)</div>
            <table class="view-table">
                <tr><th>職種</th><td><?= html_escape(spv($job,'job_title')) ?></td></tr>
                <tr><th>勤務地</th><td><?= html_escape(spv($job,'workplace_jp')) ?></td></tr>
                <tr><th>業務内容</th><td><?= nl2br(html_escape(spv($job,'job_description_jp'))) ?></td></tr>
            </table>

            <div class="section-title">3) 応募条件 – Điều kiện ứng viên (JP)</div>
            <table class="view-table">
                <tr><th>男性</th><td><?= html_escape(spv($job,'quantity_male')) ?></td></tr>
                <tr><th>女性</th><td><?= html_escape(spv($job,'quantity_female')) ?></td></tr>
                <tr><th>合計</th><td><?= html_escape(spv($job,'quantity_total', spv($job,'quantity'))) ?></td></tr>
                <tr><th>年齢から</th><td><?= html_escape(spv($job,'age_from')) ?></td></tr>
                <tr><th>年齢まで</th><td><?= html_escape(spv($job,'age_to')) ?></td></tr>
                <tr><th>学歴</th><td><?= html_escape(spv($job,'education')) ?></td></tr>
                <tr><th>専攻</th><td><?= html_escape(spv($job,'major_jp')) ?></td></tr>
                <tr><th>日本語レベル</th><td><?= html_escape(spv($job,'japanese_level')) ?></td></tr>
                <tr><th>英語レベル</th><td><?= html_escape(spv($job,'english_level')) ?></td></tr>
            </table>

            <div class="section-title">4) 雇用条件 – Điều kiện làm việc (JP)</div>
            <table class="view-table">
                <tr><th>契約期間（月）</th><td><?= html_escape(spv($job,'contract_months')) ?></td></tr>
                <tr><th>勤務日数</th><td><?= html_escape(spv($job,'work_days')) ?></td></tr>
                <tr><th>休日</th><td><?= html_escape(spv($job,'holidays')) ?></td></tr>
                <tr><th>就業時間</th><td><?= html_escape(spv($job,'working_hours')) ?></td></tr>
                <tr><th>休憩時間</th><td><?= html_escape(spv($job,'break_time')) ?></td></tr>
                <tr><th>残業</th><td><?= html_escape(spv($job,'overtime')) ?></td></tr>
            </table>

            <div class="section-title">5) 給与・控除 – Lương & khấu trừ (JP)</div>
            <table class="view-table">
                <tr><th>総支給額</th><td><?= spv_num($job,'salary_total') ?></td></tr>
                <tr><th>手取り</th><td><?= spv_num($job,'salary_net') ?></td></tr>
                <tr><th>税金</th><td><?= spv_num($job,'tax') ?></td></tr>
                <tr><th>保険料</th><td><?= spv_num($job,'insurance') ?></td></tr>
                <tr><th>寮費</th><td><?= spv_num($job,'dormitory') ?></td></tr>
                <tr><th>光熱費</th><td><?= spv_num($job,'utilities') ?></td></tr>
                <tr><th>食費</th><td><?= html_escape(spv($job,'food')) ?></td></tr>
                <tr><th>賞与</th><td><?= html_escape(spv($job,'bonus')) ?></td></tr>
                <tr><th>昇給</th><td><?= html_escape(spv($job,'raise_salary')) ?></td></tr>
            </table>

            <div class="section-title">6) 福利厚生 – Phúc lợi (JP)</div>
            <table class="view-table">
                <tr><th>チケット補助</th><td><?= html_escape(spv($job,'benefit_flight')) ?></td></tr>
                <tr><th>その他</th><td><?= nl2br(html_escape(spv($job,'benefit_other'))) ?></td></tr>
            </table>

            <div class="section-title">7) 面接・入国 – Lịch trình (JP)</div>
            <table class="view-table">
                <tr><th>面接日</th><td><?= spv_date($job,'interview_date') ?></td></tr>
                <tr><th>入国予定日</th><td><?= spv_date($job,'entry_date') ?></td></tr>
                <tr><th>面接場所</th><td><?= html_escape(spv($job,'interview_place')) ?></td></tr>
            </table>
        </div>

        <div class="tab-pane" id="vi">
            <div class="section-title">1) THÔNG TIN CÔNG TY (VI)</div>
            <table class="view-table">
                <tr><th>Tên công ty</th><td><?= html_escape(spv($job,'company_name_vi')) ?></td></tr>
                <tr><th>Chủ tịch / Giám đốc</th><td><?= html_escape(spv($job,'company_president_vi')) ?></td></tr>
                <tr><th>Địa chỉ</th><td><?= nl2br(html_escape(spv($job,'address_vi'))) ?></td></tr>
                <tr><th>Số nhân viên</th><td><?= html_escape(spv($job,'employee_count_vi', spv($job,'employee_count'))) ?></td></tr>
                <tr><th>Năm thành lập</th><td><?= html_escape(spv($job,'established_year_vi', spv($job,'established_year'))) ?></td></tr>
                <tr><th>Website</th><td><?= html_escape(spv($job,'website_vi', spv($job,'website'))) ?></td></tr>
                <tr><th>Điện thoại / Website</th><td><?= html_escape(spv($job,'company_phone_vi', spv($job,'company_phone'))) ?></td></tr>
            </table>

            <div class="section-title">2) VỊ TRÍ TUYỂN DỤNG (VI)</div>
            <table class="view-table">
                <tr><th>Tên vị trí</th><td><?= html_escape(spv($job,'job_title_vi')) ?></td></tr>
                <tr><th>Nơi làm việc</th><td><?= html_escape(spv($job,'workplace_vi')) ?></td></tr>
                <tr><th>Mô tả công việc</th><td><?= nl2br(html_escape(spv($job,'job_description_vi'))) ?></td></tr>
            </table>

            <div class="section-title">3) ĐIỀU KIỆN ỨNG VIÊN (VI)</div>
            <table class="view-table">
                <tr><th>Số lượng nam</th><td><?= html_escape(spv($job,'quantity_male_vi', spv($job,'quantity_male'))) ?></td></tr>
                <tr><th>Số lượng nữ</th><td><?= html_escape(spv($job,'quantity_female_vi', spv($job,'quantity_female'))) ?></td></tr>
                <tr><th>Tổng số</th><td><?= html_escape(spv($job,'quantity_total_vi', spv($job,'quantity_total', spv($job,'quantity')))) ?></td></tr>
                <tr><th>Từ tuổi</th><td><?= html_escape(spv($job,'age_from_vi', spv($job,'age_from'))) ?></td></tr>
                <tr><th>Đến tuổi</th><td><?= html_escape(spv($job,'age_to_vi', spv($job,'age_to'))) ?></td></tr>
                <tr><th>Trình độ học vấn</th><td><?= html_escape(spv($job,'education_vi', spv($job,'education'))) ?></td></tr>
                <tr><th>Chuyên ngành</th><td><?= html_escape(spv($job,'major_vi', spv($job,'major_jp'))) ?></td></tr>
                <tr><th>Tiếng Nhật</th><td><?= html_escape(spv($job,'japanese_level_vi', spv($job,'japanese_level'))) ?></td></tr>
                <tr><th>Tiếng Anh</th><td><?= html_escape(spv($job,'english_level_vi', spv($job,'english_level'))) ?></td></tr>
            </table>

            <div class="section-title">4) ĐIỀU KIỆN LÀM VIỆC (VI)</div>
            <table class="view-table">
                <tr><th>Thời hạn HĐ</th><td><?= html_escape(spv($job,'contract_months_vi', spv($job,'contract_months'))) ?></td></tr>
                <tr><th>Số ngày làm việc</th><td><?= html_escape(spv($job,'work_days_vi', spv($job,'work_days'))) ?></td></tr>
                <tr><th>Ngày nghỉ</th><td><?= html_escape(spv($job,'holidays_vi', spv($job,'holidays'))) ?></td></tr>
                <tr><th>Giờ làm việc</th><td><?= html_escape(spv($job,'working_hours_vi', spv($job,'working_hours'))) ?></td></tr>
                <tr><th>Giờ nghỉ</th><td><?= html_escape(spv($job,'break_time_vi', spv($job,'break_time'))) ?></td></tr>
                <tr><th>Tăng ca</th><td><?= html_escape(spv($job,'overtime_vi', spv($job,'overtime'))) ?></td></tr>
            </table>

            <div class="section-title">5) LƯƠNG & KHẤU TRỪ (VI)</div>
            <table class="view-table">
                <tr><th>Lương tổng</th><td><?= spv_num($job,'salary_total_vi', spv($job,'salary_total')) ?></td></tr>
                <tr><th>Lương thực nhận</th><td><?= spv_num($job,'salary_net_vi', spv($job,'salary_net')) ?></td></tr>
                <tr><th>Thuế</th><td><?= spv_num($job,'tax_vi', spv($job,'tax')) ?></td></tr>
                <tr><th>Bảo hiểm</th><td><?= spv_num($job,'insurance_vi', spv($job,'insurance')) ?></td></tr>
                <tr><th>Ký túc xá</th><td><?= spv_num($job,'dormitory_vi', spv($job,'dormitory')) ?></td></tr>
                <tr><th>Điện nước</th><td><?= spv_num($job,'utilities_vi', spv($job,'utilities')) ?></td></tr>
                <tr><th>Chi phí ăn uống</th><td><?= html_escape(spv($job,'food_vi', spv($job,'food'))) ?></td></tr>
                <tr><th>Bonus</th><td><?= html_escape(spv($job,'bonus_vi', spv($job,'bonus'))) ?></td></tr>
                <tr><th>Tăng lương</th><td><?= html_escape(spv($job,'raise_salary_vi', spv($job,'raise_salary'))) ?></td></tr>
            </table>

            <div class="section-title">6) PHÚC LỢI (VI)</div>
            <table class="view-table">
                <tr><th>Hỗ trợ vé máy bay</th><td><?= html_escape(spv($job,'benefit_flight_vi', spv($job,'benefit_flight'))) ?></td></tr>
                <tr><th>Đãi ngộ khác</th><td><?= nl2br(html_escape(spv($job,'benefit_other_vi', spv($job,'benefit_other')))) ?></td></tr>
            </table>

            <div class="section-title">7) LỊCH TRÌNH (VI)</div>
            <table class="view-table">
                <tr><th>Ngày phỏng vấn</th><td><?= spv_date($job,'interview_date_vi') ?: spv_date($job,'interview_date') ?></td></tr>
                <tr><th>Ngày dự kiến nhập cảnh</th><td><?= spv_date($job,'entry_date_vi') ?: spv_date($job,'entry_date') ?></td></tr>
                <tr><th>Địa điểm phỏng vấn</th><td><?= html_escape(spv($job,'interview_place_vi', spv($job,'interview_place'))) ?></td></tr>
            </table>
        </div>
    </div>
</div>
<script>
(function(){
  var tabs = document.querySelectorAll('.nav-tabs a');
  tabs.forEach(function(a){
    a.addEventListener('click', function(e){
      e.preventDefault();
      tabs.forEach(function(x){ x.parentElement.classList.remove('active'); });
      document.querySelectorAll('.tab-pane').forEach(function(p){ p.classList.remove('active'); });
      a.parentElement.classList.add('active');
      var target = document.getElementById(a.getAttribute('data-target'));
      if (target) target.classList.add('active');
    });
  });
})();
</script>
</body>
</html>
