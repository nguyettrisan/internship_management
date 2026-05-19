<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<script src="https://www.gstatic.com/charts/loader.js"></script>

<style>
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

/* Title */
.section-title{
  font-size:20px;
  font-weight:1000;
  color:var(--ifk-navy);
  margin:0 0 12px;
  letter-spacing:.2px;
  display:flex;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
}
.section-title i{ color:var(--ifk-blue); }
.section-title .btn{
  margin-left:auto;
}

/* Buttons */
.btn{
  border-radius:14px;
  font-weight:900;
  border:0;
  padding:10px 14px;
}
.btn-warning{ background:var(--ifk-green) !important; color:#0b1a08 !important; }
.btn-default{
  background:#fff !important;
  border:1px solid var(--ifk-border) !important;
  color:var(--ifk-navy) !important;
}
.btn-info{ background:var(--ifk-blue) !important; }
.btn-success{ background:var(--ifk-green) !important; color:#0b1a08 !important; }
.btn-danger{ background:#d92d20 !important; }

/* Cards */
.result-card{
  background:var(--ifk-card);
  border-radius:var(--ifk-radius);
  border:1px solid var(--ifk-border);
  padding:16px 16px;
  margin-bottom:16px;
  box-shadow:var(--ifk-shadow);
  position:relative;
  overflow:hidden;
}
.result-card:before{
  content:"";
  position:absolute;
  left:0; top:0; bottom:0;
  width:4px;
  background:linear-gradient(180deg,var(--ifk-green),var(--ifk-blue));
  opacity:.95;
}

.result-question{
  font-size:15px;
  font-weight:1000;
  margin:0 0 12px;
  color:var(--ifk-navy);
  padding-left:8px;
}

/* Text answers */
.answer-list{
  border-left:3px solid rgba(0,166,220,.65);
  padding-left:12px;
}
.answer-item{
  background:rgba(0,50,90,.03);
  border:1px solid rgba(230,238,246,.9);
  padding:10px 12px;
  border-radius:12px;
  margin-bottom:10px;
  white-space:pre-wrap;
  color:var(--ifk-text);
}

/* Charts row */
.chart-row{
  display:flex;
  gap:14px;
  flex-wrap:wrap;
}
.chart-left{
  flex:1;
  min-width:280px;
}
.chart-right{
  flex:1;
  min-width:320px;
  height:320px;
  border-radius:14px;
  border:1px dashed rgba(0,50,90,.15);
  background:linear-gradient(180deg, rgba(0,166,220,.06), #fff);
  padding:8px;
}

/* Stat boxes */
.stat-box{
  background:#fff;
  padding:10px 12px;
  border-radius:12px;
  margin-bottom:10px;
  border:1px solid var(--ifk-border);
  box-shadow:0 8px 18px rgba(0,50,90,.06);
}
.stat-box strong{ color:var(--ifk-navy); font-weight:1000; }
.stat-box::before{
  content:"";
  display:inline-block;
  width:10px;height:10px;
  border-radius:999px;
  background:var(--ifk-blue);
  margin-right:8px;
  vertical-align:middle;
}

/* Table */
.result-table th{
  background:rgba(0,50,90,.03) !important;
  color:var(--ifk-navy);
  font-weight:1000;
  border-bottom:1px solid var(--ifk-border) !important;
}
.result-table td{
  background:#fff !important;
  border-top:1px solid rgba(230,238,246,.9) !important;
}
.table-hover>tbody>tr:hover{
  background:rgba(0,166,220,.06);
}

/* DataTable buttons (không phá layout Perfex) */
.dt-buttons .btn{
  border-radius:12px;
  padding:8px 12px;
  font-weight:900;
  margin-right:6px;
}
.dt-buttons .btn.buttons-excel{ background:var(--ifk-green) !important; color:#0b1a08 !important; }
.dt-buttons .btn.buttons-csv{ background:var(--ifk-blue) !important; }
.dt-buttons .btn.buttons-print{ background:var(--ifk-navy) !important; color:#fff !important; }

/* Muted */
.text-muted{ color:var(--ifk-muted) !important; }

/* ============================
   PRINT MODE – A4 PRO
============================ */
@media print{
  @page{ size:A4; margin:14mm; }

  body, html{ background:#fff !important; }

  .left-menu, #side-menu, .navbar, .top-menu, footer,
  .breadcrumbs, .dt-buttons, .btn, .page-title, .alert,
  .sticky-header, .mobile-menu{
    display:none !important;
    visibility:hidden !important;
  }

  #wrapper, .content, .panel_s, .panel-body{
    width:100% !important;
    margin:0 !important;
    padding:0 !important;
    box-shadow:none !important;
    background:#fff !important;
  }

  .section-title{
    font-size:18px !important;
    margin-bottom:10px !important;
  }

  .result-card{
    page-break-inside:avoid;
    box-shadow:none !important;
    border:1px solid #d7d7d7 !important;
    margin-bottom:12px !important;
    padding:12px !important;
  }

  .chart-right{
    height:260px !important;
    border:1px solid #d7d7d7 !important;
    background:#fff !important;
  }

  table{
    page-break-inside:auto;
    border-collapse:collapse !important;
  }
  table td, table th{
    padding:6px !important;
    border:1px solid #cfcfcf !important;
    font-size:12px !important;
  }
}
</style>

<div id="wrapper">
<div class="content">

    <h4 class="section-title">
        
        <i class="fa fa-bar-chart"></i>
        Kết quả khảo sát: <?php echo html_escape($survey->title); ?>
     <br>   <button onclick="window.print()" class="btn btn-warning mtop20" style="margin-bottom:20px;">
    <i class="fa fa-print"></i> In báo cáo (A4 – Chuyên nghiệp)
</button>
    </h4>

    <p class="text-muted mtop10">
        Tổng số phản hồi: <strong><?php echo count($results); ?></strong>
    </p>

    <!-- ============================
         LOOP Q&A
    ============================ -->
    <?php foreach ($questions as $q): ?>

        <?php
        $chart_id = "chart_q_".$q->id;
        $counts   = [];

        foreach ($results as $r) {
            $raw = html_entity_decode($r->answers, ENT_QUOTES, 'UTF-8');
            $ans = json_decode($raw, true) ?: [];
            $key = "field_".$q->id;
            $val = $ans[$key] ?? '';

            if ($q->field_type === 'checkbox') {
                $parts = array_map('trim', explode(',', $val));
                foreach ($parts as $p) {
                    if (!isset($counts[$p])) $counts[$p] = 0;
                    $counts[$p]++;
                }
            } else {
                if (!isset($counts[$val])) $counts[$val] = 0;
                $counts[$val]++;
            }
        }
        ?>

        <div class="result-card">

            <div class="result-question">
                <?php echo html_escape($q->label); ?>
            </div>

            <?php if (in_array($q->field_type, ['text', 'textarea'])): ?>

                <!-- TEXTAREA / TEXT: hiển thị dạng danh sách -->
                <div class="answer-list">
                    <?php foreach ($results as $r):
                        $raw = html_entity_decode($r->answers, ENT_QUOTES, 'UTF-8');
                        $ans = json_decode($raw, true) ?: [];
                        $val = $ans["field_".$q->id] ?? '';
                    ?>
                        <div class="answer-item">
                            <?php echo nl2br(html_escape($val)); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>

                <!-- TRẮC NGHIỆM: Biểu đồ + thống kê -->
                <div class="chart-row">

                    <div class="chart-left">
                        <?php foreach ($counts as $label => $count): ?>
                            <div class="stat-box">
                                <strong><?php echo html_escape($label ?: 'Không trả lời'); ?>:</strong>
                                <?php echo $count; ?> lượt
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="chart-right" id="<?php echo $chart_id; ?>"></div>

                </div>

                <script>
                    google.charts.load('current', {'packages':['corechart']});
                    google.charts.setOnLoadCallback(draw_<?php echo $chart_id; ?>);

                    function draw_<?php echo $chart_id; ?>() {
                        var data = google.visualization.arrayToDataTable([
                            ['Lựa chọn', 'Số lượng'],
                            <?php foreach ($counts as $label => $count): ?>
                                ["<?php echo html_escape($label ?: 'Không trả lời'); ?>", <?php echo $count; ?>],
                            <?php endforeach; ?>
                        ]);

                        var options = {
                            pieHole: 0.35,
                            legend: { position: 'right' },
                            chartArea: { width: '90%', height: '90%' }
                        };

                        var chart = new google.visualization.PieChart(
                            document.getElementById('<?php echo $chart_id; ?>')
                        );
                        chart.draw(data, options);
                    }
                </script>

            <?php endif; ?>

        </div>

    <?php endforeach; ?>

    <hr/>

    <!-- ============================
         BẢNG DỮ LIỆU FULL
    ============================ -->
    <div class="table-responsive mtop20">
        <table class="table table-bordered result-table dt-table-export">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Sinh viên</th>
                    <th>Email</th>
                    <?php foreach ($questions as $q): ?>
                        <th><?php echo html_escape($q->label); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($results as $r):
                $raw = html_entity_decode($r->answers, ENT_QUOTES, 'UTF-8');
                $ans = json_decode($raw, true) ?: [];
            ?>
                <tr>
                    <td><?php echo _dt($r->submitted_at); ?></td>
                    <td><?php echo html_escape($r->full_name); ?></td>
                    <td><?php echo html_escape($r->email); ?></td>

                    <?php foreach ($questions as $q):
                        $val = $ans["field_".$q->id] ?? '';
                    ?>
                        <td><?php echo nl2br(html_escape($val)); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <a href="<?php echo admin_url('internship_management/internship_survey/templates'); ?>"
       class="btn btn-default mtop20">
        <i class="fa fa-arrow-left"></i> Quay lại Mẫu Khảo Sát
    </a>

</div>
</div>

<?php init_tail(); ?>

<script>
$('.dt-table-export').DataTable({
    dom: 'Bfrtip',
    buttons: [
        { extend: 'excel', text: 'Xuất Excel' },
        { extend: 'csv', text: 'Xuất CSV' },
        { extend: 'print', text: 'In' }
    ]
});
</script>

</body>
</html>