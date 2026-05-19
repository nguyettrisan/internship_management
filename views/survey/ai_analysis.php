<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
/* ================================
   IFK PREMIUM – AI ANALYSIS VIEW
================================ */
.ai-box {
    background: #ffffff;
    padding: 28px;
    border-radius: 14px;
    border: 1px solid #e4e9f1;
    box-shadow: 0 6px 22px rgba(0,0,0,0.06);
}

.ai-title {
    font-size: 22px;
    font-weight: 700;
    color: #1b3358;
    margin-bottom: 18px;
}

.ai-block {
    padding: 20px;
    background: #f6f9ff;
    border-left: 4px solid #1a73e8;
    border-radius: 10px;
    white-space: pre-line;
    margin-bottom: 20px;
}

.ai-empty {
    background: #fff7e6;
    border-left: 4px solid #ffca6a;
    padding: 16px;
    border-radius: 8px;
    font-size: 14px;
}

.btn-reload {
    background: #1a73e8;
    color: #fff;
    border-radius: 8px;
}
.btn-reload:hover {
    background: #1558b0;
    color: #fff;
}
</style>

<div class="content">
    <h4 class="mb-2">
        <i class="fa fa-robot"></i>
        Phân Tích AI – <?php echo html_escape($survey->title); ?>
    </h4>

    <p class="text-muted">
        Dưới đây là phần nhận xét được tạo tự động bởi <b>Google Gemini</b>, dựa trên toàn bộ phản hồi khảo sát.
    </p>

    <hr>

    <div class="ai-box">

        <?php if (!empty($analysis)): ?>

            <h5 class="ai-title">
                <i class="fa fa-magic"></i>
                Kết quả phân tích từ Gemini
            </h5>

            <div class="ai-block">
                <?php echo nl2br(html_escape($analysis)); ?>
            </div>

        <?php else: ?>

            <div class="ai-empty">
                <i class="fa fa-info-circle text-warning"></i>
                Không có dữ liệu hoặc hệ thống AI chưa tạo phân tích cho khảo sát này.
            </div>

        <?php endif; ?>


        <!-- BUTTONS -->
        <div class="mt-3">

            <!-- Tạo lại bằng AI -->
            <button id="btn_ai_run" class="btn btn-reload mr-2">
                <i class="fa fa-sync"></i> Tạo lại bằng AI
            </button>

            <!-- Back -->
            <a href="<?php echo admin_url('internship_management/internship_survey/dashboard/'.$survey->id); ?>"
               class="btn btn-default">
               <i class="fa fa-arrow-left"></i> Quay lại Dashboard
            </a>

        </div>

    </div>
</div>

<?php init_tail(); ?>

<script>
/* ========================================
   CALL AI – TẠO LẠI COMMENT TỰ ĐỘNG
======================================== */
$('#btn_ai_run').on('click', function () {

    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang phân tích...');

    $.post(
        admin_url + "internship_management/internship_survey/ai_generate_comment/<?php echo $survey->id; ?>",
        {},
        function (res) {

            if (!res.success) {
                alert(res.message);
                btn.prop('disabled', false).html('<i class="fa fa-sync"></i> Tạo lại bằng AI');
                return;
            }

            alert('AI đã tạo phân tích mới!');
            location.reload();

        },
        'json'
    );
});
</script>
</body>
</html>