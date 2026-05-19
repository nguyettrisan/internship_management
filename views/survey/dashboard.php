<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
/** Chuẩn bị CI instance + CSRF */
$CI           =& get_instance();
$csrf_enabled = $CI->config->item('csrf_protection');
$csrf_name    = $csrf_enabled ? $CI->security->get_csrf_token_name() : '';
$csrf_hash    = $csrf_enabled ? $CI->security->get_csrf_hash() : '';

/** Chuẩn hoá biến để tránh notice */
$survey          = isset($survey) ? $survey : null;
$total_responses = isset($total_responses) ? (int)$total_responses : 0;
$rating_stats    = isset($rating_stats) && is_array($rating_stats) ? $rating_stats : [];
$last_submit     = isset($last_submit) ? $last_submit : null;
$primary_rating  = isset($primary_rating) ? $primary_rating : null;
$ai_comment      = isset($ai_comment) ? $ai_comment : ''; // nhận xét AI lưu trong DB (nếu có)
?>

<?php init_head(); ?>

<style>
/* ================================
   IFK BRAND UI (Survey Dashboard)
   Colors: #96bc17 #00325a #00a6dc
================================ */
:root{
  --ifk-green:#96bc17;
  --ifk-navy:#00325a;
  --ifk-blue:#00a6dc;

  --ifk-bg:#f6f9fc;
  --ifk-card:#ffffff;
  --ifk-border:#e6eef6;
  --ifk-text:#1c2b3a;
  --ifk-muted:#6b7c93;

  --ifk-radius:14px;
  --ifk-shadow:0 10px 26px rgba(0,50,90,.08);
}

#wrapper .content{ background:var(--ifk-bg); }

#survey-dashboard h4.no-margin{
  display:flex;
  align-items:center;
  gap:10px;
  color:var(--ifk-navy);
  font-weight:1000;
  letter-spacing:.2px;
}
#survey-dashboard h4.no-margin i{ color:var(--ifk-blue); }
#survey-dashboard p.text-muted{ color:var(--ifk-muted) !important; }

/* Buttons */
#survey-dashboard .btn{
  border-radius:14px;
  font-weight:900;
  padding:10px 14px;
  border:0;
}
#survey-dashboard .btn-warning{ background:var(--ifk-green) !important; color:#0b1a08 !important; }
#survey-dashboard .btn-danger{ background:#d92d20 !important; }
#survey-dashboard .btn-success{ background:var(--ifk-green) !important; color:#0b1a08 !important; }
#survey-dashboard .btn-info{ background:var(--ifk-blue) !important; }
#survey-dashboard .btn-default{
  background:#fff !important;
  border:1px solid var(--ifk-border) !important;
  color:var(--ifk-navy) !important;
}

/* KPI */
.ifk-kpi-box{
  border-radius:var(--ifk-radius);
  padding:18px 18px;
  background:var(--ifk-card);
  border:1px solid var(--ifk-border);
  box-shadow:var(--ifk-shadow);
  margin-bottom:18px;
  position:relative;
  overflow:hidden;
  transition:.18s ease;
}
.ifk-kpi-box:before{
  content:"";
  position:absolute;
  left:0; top:0; bottom:0;
  width:4px;
  background:linear-gradient(180deg,var(--ifk-green),var(--ifk-blue));
}
.ifk-kpi-box:hover{
  transform: translateY(-1px);
  box-shadow:0 16px 34px rgba(0,50,90,.12);
}
.ifk-kpi-label{
  font-size:12px;
  letter-spacing:.08em;
  color:var(--ifk-muted);
  font-weight:900;
  text-transform:uppercase;
  margin-bottom:6px;
}
.ifk-kpi-value{
  font-size:30px;
  font-weight:1000;
  color:var(--ifk-navy);
}
.ifk-kpi-sub{
  font-size:12px;
  color:var(--ifk-muted);
  font-weight:700;
  margin-top:4px;
}

/* Cards */
.ifk-card{
  background:var(--ifk-card);
  border-radius:var(--ifk-radius);
  border:1px solid var(--ifk-border);
  box-shadow:var(--ifk-shadow);
  margin-bottom:18px;
  overflow:hidden;
}
.ifk-card-header{
  padding:14px 16px;
  font-size:14px;
  font-weight:1000;
  color:var(--ifk-navy);
  border-bottom:1px solid rgba(230,238,246,.9);
  background:linear-gradient(90deg, rgba(0,166,220,.08), rgba(150,188,23,.06));
}
.ifk-card-header i{ color:var(--ifk-blue); margin-right:6px; }
.ifk-card-body{ padding:16px; }

/* Tables */
#survey-dashboard .table{
  border-radius:14px;
  overflow:hidden;
}
#survey-dashboard .table>thead>tr>th{
  color:var(--ifk-navy);
  font-weight:1000;
  border-bottom:1px solid var(--ifk-border) !important;
  background:rgba(0,50,90,.03);
}
#survey-dashboard .table>tbody>tr>td{
  border-top:1px solid rgba(230,238,246,.9) !important;
}
#survey-dashboard .table-hover>tbody>tr:hover{
  background:rgba(0,166,220,.06);
}

/* Form control */
#ai-comment-box.form-control{
  border-radius:14px;
  border:1px solid var(--ifk-border);
  min-height:140px;
  resize:vertical;
  box-shadow:none;
}
#ai-comment-box.form-control:focus{
  border-color:rgba(0,166,220,.55);
  box-shadow:0 0 0 4px rgba(0,166,220,.12);
}

/* Badges (nếu bạn có dùng ở nơi khác) */
.im-badge{ padding:4px 10px; border-radius:999px; font-weight:1000; font-size:12px; display:inline-flex; align-items:center; gap:6px; }
.im-badge:before{ content:""; width:8px; height:8px; border-radius:999px; background:currentColor; opacity:.9; }
.im-badge-sent{ background:rgba(150,188,23,.16); color:#2f6f09; }
.im-badge-failed{ background:rgba(220,0,0,.10); color:#b42318; }

/* Print mode */
@media print{
  body * { visibility:hidden !important; }
  #survey-dashboard, #survey-dashboard * { visibility:visible !important; }
  #survey-dashboard{
    position:absolute; top:0; left:0;
    width:100% !important;
    padding:10px;
  }
  .navbar, .left-menu, #side-menu, .screen-options-area,
  #wrapper > .content > .row:first-child{ display:none !important; }
}
</style>

<div id="wrapper">
    <div class="content">
        <div id="survey-dashboard">

            <!-- ===================================== -->
            <!-- PAGE TITLE -->
            <!-- ===================================== -->
            <div class="row">
                <div class="col-md-12">
                    <h4 class="no-margin">
                        <img src="https://ifkgroup.net/logo-ifk.png" height="32" class="mr-2" />
                        <i class="fa fa-bar-chart"></i>
                        <?php echo $survey ? html_escape($survey->title) : 'Dashboard khảo sát'; ?> – Dashboard thống kê
                    </h4>
                    <p class="text-muted">
                        Thống kê trực quan số lượng phản hồi và đánh giá chất lượng chương trình thực tập.
                    </p>
                    <hr>
                </div>
            </div>

            <!-- ===================================== -->
            <!-- KPI BOXES -->
            <!-- ===================================== -->
            <div class="row">

                <div class="col-md-4">
                    <div class="ifk-kpi-box">
                        <div class="ifk-kpi-label">Số lượt phản hồi</div>
                        <div class="ifk-kpi-value"><?php echo $total_responses; ?></div>
                        <div class="ifk-kpi-sub">Tổng số phiếu khảo sát được gửi</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="ifk-kpi-box">
                        <div class="ifk-kpi-label">Câu hỏi Rating</div>
                        <div class="ifk-kpi-value"><?php echo count($rating_stats); ?></div>
                        <div class="ifk-kpi-sub">Tổng số tiêu chí đánh giá thang 1–5 sao</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="ifk-kpi-box">
                        <div class="ifk-kpi-label">Lần gửi gần nhất</div>
                        <div class="ifk-kpi-value" style="font-size:18px;">
                            <?php
                            if ($last_submit) {
                                echo function_exists('_dt')
                                    ? _dt($last_submit)
                                    : date('d/m/Y H:i', strtotime($last_submit));
                            } else {
                                echo '—';
                            }
                            ?>
                        </div>
                        <div class="ifk-kpi-sub">Thời điểm có phản hồi mới nhất</div>
                    </div>
                </div>

            </div>

            <!-- ===================================== -->
            <!-- ACTION BUTTONS -->
            <!-- ===================================== -->
            <div class="row mb-3">
                <div class="col-md-12">

                    <a href="javascript:void(0)" onclick="window.print()" class="btn btn-warning">
                        <i class="fa fa-print"></i> In báo cáo
                    </a>

                    <a href="<?php echo admin_url('internship_management/internship_survey/export_pdf/'.($survey ? $survey->id : 0)); ?>"
                       class="btn btn-danger">
                        <i class="fa fa-file-pdf-o"></i> Xuất PDF
                    </a>

                    <a href="<?php echo admin_url('internship_management/internship_survey/export_results/'.($survey ? $survey->id : 0)); ?>"
                       class="btn btn-success">
                        <i class="fa fa-file-excel-o"></i> Xuất Excel
                    </a>

                    <!-- Nút gọi AI: dùng AJAX, không chuyển trang -->
                    <button type="button"
                            id="btn-ai-analyze"
                            class="btn btn-info">
                        <i class="fa fa-robot"></i>
                        <span class="btn-ai-text">Phân tích bằng AI Gemini</span>
                    </button>

                </div>
            </div>

            <!-- ===================================== -->
            <!-- RATING TABLE + CHART -->
            <!-- ===================================== -->
            <div class="row">

                <!-- Bảng rating -->
                <div class="col-md-6">
                    <div class="ifk-card">
                        <div class="ifk-card-header">
                            <i class="fa fa-star text-warning"></i>
                            Chi tiết câu hỏi Rating
                        </div>
                        <div class="ifk-card-body">

                            <?php if (empty($rating_stats)): ?>
                                <p class="text-muted">Không có câu hỏi dạng Rating trong mẫu khảo sát này.</p>
                            <?php else: ?>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="40%">Câu hỏi</th>
                                                <th class="text-center">Điểm TB</th>
                                                <th class="text-center">1★</th>
                                                <th class="text-center">2★</th>
                                                <th class="text-center">3★</th>
                                                <th class="text-center">4★</th>
                                                <th class="text-center">5★</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rating_stats as $stat): ?>
                                                <tr>
                                                    <td><?php echo html_escape($stat['label']); ?></td>
                                                    <td class="text-center">
                                                        <strong><?php echo number_format((float)$stat['avg'], 2); ?></strong>
                                                    </td>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <td class="text-center">
                                                            <?php echo isset($stat['count'][$i]) ? (int)$stat['count'][$i] : 0; ?>
                                                        </td>
                                                    <?php endfor; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <!-- Biểu đồ -->
                <div class="col-md-6">
                    <div class="ifk-card">
                        <div class="ifk-card-header">
                            <i class="fa fa-line-chart"></i>
                            Biểu đồ phân bố điểm – tiêu chí chính
                        </div>
                        <div class="ifk-card-body">

                            <?php if (empty($primary_rating)): ?>
                                <p class="text-muted">Không có dữ liệu đủ để vẽ biểu đồ.</p>
                            <?php else: ?>
                                <p class="text-muted">
                                    Câu hỏi: <strong><?php echo html_escape($primary_rating['label']); ?></strong>
                                </p>
                                <div style="height: 200px;">
                                    <canvas id="ratingChart"></canvas>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

            </div>

            <!-- ===================================== -->
            <!-- AI COMMENT BOX -->
            <!-- ===================================== -->
            <div class="row">
                <div class="col-md-12">
                    <div class="ifk-card">
                        <div class="ifk-card-header">
                            <i class="fa fa-magic"></i>
                            Nhận xét tổng quan từ AI (Gemini)
                        </div>
                        <div class="ifk-card-body">
                            <p class="text-muted">
                                Khi bấm nút <strong>“Phân tích bằng AI Gemini”</strong>, hệ thống sẽ gửi dữ liệu
                                khảo sát cho Gemini, nhận lại phân tích và tự động điền vào ô bên dưới. Nội dung
                                này cũng sẽ được lưu lại cho lần xem tiếp theo.
                            </p>

                            <textarea id="ai-comment-box"
                                      class="form-control"
                                      readonly="readonly"
                                      placeholder="Chưa có nhận xét từ AI. Hãy bấm nút &quot;Phân tích bằng AI Gemini&quot; để tạo báo cáo tự động."><?php
                                echo $ai_comment ? html_escape($ai_comment) : '';
                            ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================================== -->
            <!-- BOTTOM NAVIGATION -->
            <!-- ===================================== -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <a href="<?php echo admin_url('internship_management/internship_survey/templates'); ?>"
                       class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Danh sách mẫu khảo sát
                    </a>

                    <a href="<?php echo admin_url('internship_management/internship_survey/results/'.($survey ? $survey->id : 0)); ?>"
                       class="btn btn-info">
                        <i class="fa fa-table"></i> Xem bảng chi tiết
                    </a>
                </div>
            </div>

        </div><!-- /#survey-dashboard -->
    </div><!-- /.content -->
</div><!-- /#wrapper -->

<?php init_tail(); ?>

<?php if (!empty($primary_rating)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    var ctx  = document.getElementById('ratingChart').getContext('2d');
    var data = <?php echo json_encode(array_values($primary_rating['count'])); ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['1', '2', '3', '4', '5'],
            datasets: [{
                label: 'Số lượng đánh giá',
                data: data,
                backgroundColor: '#86b7ff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero:true } }
        }
    });
})();
</script>
<?php endif; ?>

<script>
(function($) {
    "use strict";

    var $btnAi = $('#btn-ai-analyze');
    var $boxAi = $('#ai-comment-box');
    var running = false;

    $btnAi.on('click', function () {

        if (running) return;
        running = true;

        var originalHtml = $btnAi.html();
        $btnAi.prop('disabled', true)
              .html('<i class="fa fa-spinner fa-spin"></i> Đang phân tích...');

        var postData = {};
        <?php if ($csrf_enabled && $csrf_name && $csrf_hash): ?>
            postData['<?php echo $csrf_name; ?>'] = '<?php echo $csrf_hash; ?>';
        <?php endif; ?>

        // ⭐ ĐÚNG ROUTE (hàm bạn đang dùng thực tế)
        var url = "<?php echo admin_url('internship_management/internship_survey/ai_generate_comment/' . ($survey ? $survey->id : 0)); ?>";

        $.post(url, postData, function(res){

            try {
                res = typeof res === 'string' ? JSON.parse(res) : res;
            } catch(e) {
                alert("AI trả dữ liệu không hợp lệ.");
                return;
            }

            if (res.success) {
                $('#ai-comment-box').val(res.comment);
            } else {
                alert(res.message || "AI không thể tạo phân tích.");
            }

        }).fail(function(){
            alert("Không thể kết nối máy chủ.");
        }).always(function(){
            running = false;
            $btnAi.prop('disabled', false).html(originalHtml);
        });

    });

})(jQuery);

</script>

</body>
</html>