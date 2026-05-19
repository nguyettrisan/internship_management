<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
.view-container {
    max-width: 1000px;
    margin: auto;
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.view-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
    font-size: 14px;
}
.view-table th {
    background: #f4f6f9;
    padding: 10px;
    font-weight: bold;
    border: 1px solid #ddd;
}
.view-table td {
    padding: 9px;
    border: 1px solid #ddd;
}
.section-title {
    font-weight: bold;
    font-size: 18px;
    margin: 25px 0 10px;
    padding-bottom: 5px;
    border-bottom: 2px solid #007bff;
}
</style>

<div id="wrapper">
<div class="content">
<div class="view-container">

    <div class="row">
        <div class="col-md-6">
            <h3 class="bold">📄 CHI TIẾT ĐƠN TUYỂN</h3>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?= admin_url('internship_management/internship_job_orders'); ?>" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Quay lại
            </a>

            <a href="<?= admin_url('internship_management/internship_job_orders/print/'.$job['id']); ?>" 
               class="btn btn-primary" target="_blank">
                <i class="fa fa-print"></i> In PDF
            </a>
        </div>
    </div>

    <ul class="nav nav-tabs mtop30" role="tablist">
        <li class="active"><a href="#jp" data-toggle="tab">🇯🇵 Tiếng Nhật</a></li>
        <li><a href="#vi" data-toggle="tab">🇻🇳 Tiếng Việt</a></li>
    </ul>

    <div class="tab-content mtop20">

<!-- 🇯🇵 TAB JP -->
<div class="tab-pane active" id="jp">

    <!-- COMPANY JP -->
    <div class="section-title">1) 企業情報 – Thông tin công ty (JP)</div>
    <table class="view-table">
        <tr><th>会社名</th><td><?= $job['company_name_jp'] ?? '' ?></td></tr>
        <tr><th>代表取締役</th><td><?= $job['company_president'] ?? '' ?></td></tr>
        <tr><th>住所</th><td><?= $job['address_jp'] ?? '' ?></td></tr>
        <tr><th>従業員数</th><td><?= $job['employee_count'] ?? '' ?></td></tr>
        <tr><th>設立</th><td><?= $job['established_year'] ?? '' ?></td></tr>
        <tr><th>Website</th><td><?= $job['website'] ?? '' ?></td></tr>
        <tr><th>電話番号</th><td><?= $job['company_phone'] ?? '' ?></td></tr>
    </table>

    <!-- JOB JP -->
    <div class="section-title">2) 募集職種 – Vị trí tuyển dụng (JP)</div>
    <table class="view-table">
        <tr><th>職種</th><td><?= $job['job_title'] ?? '' ?></td></tr>
        <tr><th>勤務地</th><td><?= $job['workplace_jp'] ?? '' ?></td></tr>
        <tr><th>業務内容</th><td><?= nl2br($job['job_description_jp'] ?? '') ?></td></tr>
    </table>

    <!-- REQUIREMENTS JP -->
    <div class="section-title">3) 応募条件 – Điều kiện ứng viên (JP)</div>
    <table class="view-table">
        <tr><th>男性</th><td><?= $job['quantity_male'] ?? '' ?></td></tr>
        <tr><th>女性</th><td><?= $job['quantity_female'] ?? '' ?></td></tr>
        <tr><th>合計</th><td><?= $job['quantity_total'] ?? '' ?></td></tr>
        <tr><th>年齢から</th><td><?= $job['age_from'] ?? '' ?></td></tr>
        <tr><th>年齢まで</th><td><?= $job['age_to'] ?? '' ?></td></tr>
        <tr><th>学歴</th><td><?= $job['education'] ?? '' ?></td></tr>
        <tr><th>専攻</th><td><?= $job['major_jp'] ?? '' ?></td></tr>
        <tr><th>日本語レベル</th><td><?= $job['japanese_level'] ?? '' ?></td></tr>
        <tr><th>英語レベル</th><td><?= $job['english_level'] ?? '' ?></td></tr>
    </table>

    <!-- WORK CONDITIONS JP -->
    <div class="section-title">4) 雇用条件 – Điều kiện làm việc (JP)</div>
    <table class="view-table">
        <tr><th>契約期間（月）</th><td><?= $job['contract_months'] ?? '' ?></td></tr>
        <tr><th>勤務日数</th><td><?= $job['work_days'] ?? '' ?></td></tr>
        <tr><th>休日</th><td><?= $job['holidays'] ?? '' ?></td></tr>
        <tr><th>就業時間</th><td><?= $job['working_hours'] ?? '' ?></td></tr>
        <tr><th>休憩時間</th><td><?= $job['break_time'] ?? '' ?></td></tr>
        <tr><th>残業</th><td><?= $job['overtime'] ?? '' ?></td></tr>
    </table>

    <!-- SALARY JP -->
    <div class="section-title">5) 給与・控除 – Lương & khấu trừ (JP)</div>
    <table class="view-table">
        <tr><th>総支給額</th><td><?= number_format($job['salary_total'] ?? 0) ?></td></tr>
        <tr><th>手取り</th><td><?= number_format($job['salary_net'] ?? 0) ?></td></tr>
        <tr><th>税金</th><td><?= number_format($job['tax'] ?? 0) ?></td></tr>
        <tr><th>保険料</th><td><?= number_format($job['insurance'] ?? 0) ?></td></tr>
        <tr><th>寮費</th><td><?= number_format($job['dormitory'] ?? 0) ?></td></tr>
        <tr><th>光熱費</th><td><?= number_format($job['utilities'] ?? 0) ?></td></tr>
        <tr><th>食費</th><td><?= $job['food'] ?? '' ?></td></tr>
        <tr><th>賞与</th><td><?= $job['bonus'] ?? '' ?></td></tr>
        <tr><th>昇給</th><td><?= $job['raise_salary'] ?? '' ?></td></tr>
    </table>

    <!-- BENEFITS JP -->
    <div class="section-title">6) 福利厚生 – Phúc lợi (JP)</div>
    <table class="view-table">
        <tr><th>チケット補助</th><td><?= $job['benefit_flight'] ?? '' ?></td></tr>
        <tr><th>その他</th><td><?= nl2br($job['benefit_other'] ?? '') ?></td></tr>
    </table>

    <!-- SCHEDULE JP -->
    <div class="section-title">7) 面接・入国 – Lịch trình (JP)</div>
    <table class="view-table">
        <tr><th>面接日</th><td><?= _d($job['interview_date'] ?? '') ?></td></tr>
        <tr><th>入国日</th><td><?= _d($job['entry_date'] ?? '') ?></td></tr>
        <tr><th>面接場所</th><td><?= $job['interview_place'] ?? '' ?></td></tr>
    </table>

</div>


        <!-- 🇻🇳 TAB VIỆT -->
<div class="tab-pane" id="vi">

    <!-- 1) COMPANY VI -->
    <div class="section-title">1) Thông tin công ty</div>
    <table class="view-table">
        <tr><th>Tên công ty</th><td><?= $job['company_name_vi']; ?></td></tr>
        <tr><th>Chủ tịch</th><td><?= $job['company_president_vi']; ?></td></tr>
        <tr><th>Địa chỉ</th><td><?= $job['address_vi']; ?></td></tr>
        <tr><th>Số nhân viên</th><td><?= $job['employee_count_vi']; ?></td></tr>
        <tr><th>Năm thành lập</th><td><?= $job['established_year_vi']; ?></td></tr>
        <tr><th>Website</th><td><?= $job['website_vi']; ?></td></tr>
        <tr><th>Điện thoại công ty</th><td><?= $job['company_phone_vi']; ?></td></tr>
    </table>

    <!-- 2) JOB VI -->
    <div class="section-title">2) Vị trí tuyển dụng</div>
    <table class="view-table">
        <tr><th>Tên vị trí</th><td><?= $job['job_title_vi']; ?></td></tr>
        <tr><th>Nơi làm việc</th><td><?= $job['workplace_vi']; ?></td></tr>
        <tr><th>Mô tả công việc</th><td><?= nl2br($job['job_description_vi']); ?></td></tr>
    </table>

    <!-- 3) REQUIREMENTS VI -->
    <div class="section-title">3) Điều kiện ứng viên</div>
    <table class="view-table">
        <tr><th>Số lượng Nam</th><td><?= $job['quantity_male_vi']; ?></td></tr>
        <tr><th>Số lượng Nữ</th><td><?= $job['quantity_female_vi']; ?></td></tr>
        <tr><th>Tổng số</th><td><?= $job['quantity_total_vi']; ?></td></tr>
        <tr><th>Tuổi từ</th><td><?= $job['age_from_vi']; ?></td></tr>
        <tr><th>Tuổi đến</th><td><?= $job['age_to_vi']; ?></td></tr>
        <tr><th>Học vấn</th><td><?= $job['education_vi']; ?></td></tr>
        <tr><th>Chuyên ngành</th><td><?= $job['major_vi']; ?></td></tr>
        <tr><th>Trình độ tiếng Nhật</th><td><?= $job['japanese_level_vi']; ?></td></tr>
        <tr><th>Trình độ tiếng Anh</th><td><?= $job['english_level_vi']; ?></td></tr>
    </table>

    <!-- 4) WORK CONDITIONS VI -->
    <div class="section-title">4) Điều kiện làm việc</div>
    <table class="view-table">
        <tr><th>Số tháng hợp đồng</th><td><?= $job['contract_months_vi']; ?></td></tr>
        <tr><th>Số ngày làm việc</th><td><?= $job['work_days_vi']; ?></td></tr>
        <tr><th>Ngày nghỉ</th><td><?= $job['holidays_vi']; ?></td></tr>
        <tr><th>Giờ làm việc</th><td><?= $job['working_hours_vi']; ?></td></tr>
        <tr><th>Giờ nghỉ</th><td><?= $job['break_time_vi']; ?></td></tr>
        <tr><th>Tăng ca</th><td><?= $job['overtime_vi']; ?></td></tr>
    </table>

    <!-- 5) SALARY VI -->
    <div class="section-title">5) Lương & khấu trừ</div>
    <table class="view-table">
        <tr><th>Lương tổng</th><td><?= number_format($job['salary_total_vi']); ?></td></tr>
        <tr><th>Lương thực nhận</th><td><?= number_format($job['salary_net_vi']); ?></td></tr>
        <tr><th>Thuế</th><td><?= number_format($job['tax_vi']); ?></td></tr>
        <tr><th>Bảo hiểm</th><td><?= number_format($job['insurance_vi']); ?></td></tr>
        <tr><th>Ký túc xá</th><td><?= number_format($job['dormitory_vi']); ?></td></tr>
        <tr><th>Điện nước</th><td><?= number_format($job['utilities_vi']); ?></td></tr>
        <tr><th>Chi phí ăn uống</th><td><?= $job['food_vi']; ?></td></tr>
        <tr><th>Bonus</th><td><?= $job['bonus_vi']; ?></td></tr>
        <tr><th>Tăng lương</th><td><?= $job['raise_salary_vi']; ?></td></tr>
    </table>

    <!-- 6) BENEFITS VI -->
    <div class="section-title">6) Phúc lợi</div>
    <table class="view-table">
        <tr><th>Hỗ trợ vé</th><td><?= $job['benefit_flight_vi']; ?></td></tr>
        <tr><th>Đãi ngộ khác</th><td><?= nl2br($job['benefit_other_vi']); ?></td></tr>
    </table>

    <!-- 7) SCHEDULE VI -->
    <div class="section-title">7) Lịch trình</div>
    <table class="view-table">
        <tr><th>Ngày phỏng vấn</th><td><?= _d($job['interview_date_vi']); ?></td></tr>
        <tr><th>Ngày nhập cảnh</th><td><?= _d($job['entry_date_vi']); ?></td></tr>
        <tr><th>Địa điểm phỏng vấn</th><td><?= $job['interview_place_vi']; ?></td></tr>
    </table>

        </div>

    </div>
</div>
</div>
</div>

<?php init_tail(); ?>
</body>
</html>