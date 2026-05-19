<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<style>
.form-section-title {
    font-weight: bold;
    font-size: 17px;
    padding-bottom: 5px;
    border-bottom: 2px solid #e5e5e5;
    margin: 25px 0 15px;
}
</style>

<div class="row">

    <!-- ===============================
         1) THÔNG TIN ĐƠN VỊ TUYỂN DỤNG
    ================================ -->
    <div class="col-md-12">
        <div class="form-section-title">① Thông tin đơn vị tuyển dụng</div>
    </div>

    <div class="col-md-6">
        <?= render_input('company_name_vi', 'Tên đơn vị (Tiếng Việt)', $job['company_name_vi'] ?? ''); ?>
    </div>

    <div class="col-md-6">
        <?= render_input('company_name_jp', 'Tên đơn vị (Tiếng Nhật)', $job['company_name_jp'] ?? ''); ?>
    </div>

    <div class="col-md-12">
        <?= render_textarea('address', 'Địa chỉ', $job['address'] ?? ''); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('contact_name', 'Người liên hệ', $job['contact_name'] ?? ''); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('contact_phone', 'Số điện thoại', $job['contact_phone'] ?? ''); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('contact_email', 'Email liên hệ', $job['contact_email'] ?? ''); ?>
    </div>



    <!-- ===============================
         2) THÔNG TIN TUYỂN DỤNG
    ================================ -->
    <div class="col-md-12">
        <div class="form-section-title">② Thông tin tuyển dụng</div>
    </div>

    <!-- Phân loại công việc -->
    <div class="col-md-6">
        <?php
            $job_categories = [
                ['id' => 1, 'name' => 'Nhà hàng – Khách sạn'],
                ['id' => 2, 'name' => 'Viện dưỡng lão'],
                ['id' => 3, 'name' => 'Logistic'],
            ];
            echo render_select(
                'job_category',
                $job_categories,
                ['id', 'name'],
                'Phân loại công việc',
                $job['job_category'] ?? ''
            );
        ?>
    </div>

    <!-- Số lượng -->
    <div class="col-md-6">
        <?= render_input('quantity', 'Số lượng tuyển', $job['quantity'] ?? 1, 'number'); ?>
    </div>

    <!-- Mô tả công việc -->
    <div class="col-md-12">
        <?= render_textarea('job_description', 'Mô tả công việc', $job['job_description'] ?? ''); ?>
    </div>



    <!-- ===============================
         3) YÊU CẦU ỨNG VIÊN
    ================================ -->
    <div class="col-md-12">
        <div class="form-section-title">③ Yêu cầu ứng viên</div>
    </div>

    <!-- Chuyên ngành học -->
    <div class="col-md-6">
        <?php
            $majors = [
                ['id' => 'Nhật Bản Học', 'name' => 'Nhật Bản Học'],
                ['id' => 'Du Lịch', 'name' => 'Du Lịch'],
                ['id' => 'Nhà Hàng - Khách Sạn', 'name' => 'Nhà Hàng - Khách Sạn'],
                ['id' => 'Điều Dưỡng', 'name' => 'Điều Dưỡng'],
                ['id' => 'Logistic', 'name' => 'Logistic'],
            ];
            echo render_select(
                'major',
                $majors,
                ['id', 'name'],
                'Chuyên ngành học',
                $job['major'] ?? ''
            );
        ?>
    </div>

    <!-- JLPT -->
    <div class="col-md-4">
        <?php
            $jp_levels = [
                ['id' => 'N5', 'name' => 'N5'],
                ['id' => 'N4', 'name' => 'N4'],
                ['id' => 'N3', 'name' => 'N3'],
                ['id' => 'N2', 'name' => 'N2'],
                ['id' => 'N1', 'name' => 'N1'],
            ];
            echo render_select(
                'japanese_level',
                $jp_levels,
                ['id', 'name'],
                'Trình độ tiếng Nhật',
                $job['japanese_level'] ?? ''
            );
        ?>
    </div>

    <div class="col-md-2">
        <label>Có yêu cầu bằng JLPT?</label><br>
        <input type="checkbox" name="japanese_certificate" value="1"
               <?= !empty($job['japanese_certificate']) ? 'checked' : '' ?>>
        &nbsp;Yêu cầu có chứng chỉ
    </div>

    <!-- English -->
    <div class="col-md-6">
        <?= render_input('english_level', 'Trình độ tiếng Anh', $job['english_level'] ?? ''); ?>
    </div>



    <!-- ===============================
         4) LƯƠNG & PHÚC LỢI
    ================================ -->
    <div class="col-md-12">
        <div class="form-section-title">④ Lương & Phúc lợi</div>
    </div>

    <div class="col-md-4">
        <?= render_input('salary_total', 'Lương tổng (¥)', $job['salary_total'] ?? '', 'number'); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('tax', 'Khấu trừ thuế (¥)', $job['tax'] ?? '', 'number'); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('dormitory', 'Ký túc xá (¥)', $job['dormitory'] ?? '', 'number'); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('food', 'Chi phí ăn uống (¥)', $job['food'] ?? '', 'number'); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('utilities', 'Điện nước (¥)', $job['utilities'] ?? '', 'number'); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('insurance', 'Bảo hiểm (¥)', $job['insurance'] ?? '', 'number'); ?>
    </div>

    <div class="col-md-12">
        <?= render_input('salary_net', 'Lương thực nhận (¥)', $job['salary_net'] ?? '', 'number'); ?>
    </div>



    <!-- ===============================
         5) ĐÃI NGỘ KHÁC
    ================================ -->
    <div class="col-md-12">
        <div class="form-section-title">⑤ Đãi ngộ khác</div>
    </div>

    <div class="col-md-6">
        <?php
            $benefits = [
                ['id' => 'Không hỗ trợ', 'name' => 'Không hỗ trợ'],
                ['id' => 'Hỗ trợ vé VN → Nhật', 'name' => 'Hỗ trợ vé VN → Nhật'],
                ['id' => 'Hỗ trợ vé nội địa Nhật', 'name' => 'Hỗ trợ vé nội địa Nhật'],
            ];
            echo render_select(
                'benefit_flight',
                $benefits,
                ['id', 'name'],
                'Hỗ trợ vé máy bay',
                $job['benefit_flight'] ?? ''
            );
        ?>
    </div>

    <div class="col-md-6">
        <?= render_textarea('benefit_other', 'Đãi ngộ khác', $job['benefit_other'] ?? ''); ?>
    </div>



    <!-- ===============================
         6) LỊCH TRÌNH DỰ KIẾN
    ================================ -->
    <div class="col-md-12">
        <div class="form-section-title">⑥ Lịch trình dự kiến</div>
    </div>

    <div class="col-md-6">
        <?= render_date_input('interview_date', 'Ngày dự kiến phỏng vấn', $job['interview_date'] ?? ''); ?>
    </div>

    <div class="col-md-6">
        <?= render_date_input('entry_date', 'Ngày dự kiến nhập cảnh', $job['entry_date'] ?? ''); ?>
    </div>

</div>