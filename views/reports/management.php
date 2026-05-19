<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
$filters  = isset($filters) && is_array($filters) ? $filters : [];
$years    = isset($years) && is_array($years) ? $years : [];
$months   = isset($months) && is_array($months) ? $months : [];
$statuses = isset($statuses) && is_array($statuses) ? $statuses : [];
$schools  = isset($schools) && is_array($schools) ? $schools : [];

$k = isset($kpi) && is_array($kpi) ? $kpi : [];

$by_school       = isset($by_school) && is_array($by_school) ? $by_school : [];
$by_major        = isset($by_major) && is_array($by_major) ? $by_major : [];
$by_major_school = isset($by_major_school) && is_array($by_major_school) ? $by_major_school : [];
$by_status       = isset($by_status) && is_array($by_status) ? $by_status : [];

$curYear   = (int)($filters['year'] ?? 0);
$curMonth  = (int)($filters['month'] ?? 0);
$curStatus = trim((string)($filters['status'] ?? ''));
$curSchool = trim((string)($filters['school'] ?? ''));
$curQ      = trim((string)($filters['keyword'] ?? $filters['q'] ?? ''));

$qs = http_build_query([
    'year'   => $curYear ?: null,
    'month'  => $curMonth ?: null,
    'status' => $curStatus !== '' ? $curStatus : null,
    'school' => $curSchool !== '' ? $curSchool : null,
    'q'      => $curQ !== '' ? $curQ : null,
]);

$exportUrl = admin_url('internship_management/management_report/export_csv' . ($qs ? ('?' . $qs) : ''));
$resetUrl  = admin_url('internship_management/management_report');

$activeFilters = [];
if ($curYear) $activeFilters[] = 'Năm: ' . $curYear;
if ($curMonth) $activeFilters[] = 'Tháng: ' . $curMonth;
if ($curStatus !== '') $activeFilters[] = 'Trạng thái: ' . $curStatus;
if ($curSchool !== '') $activeFilters[] = 'Trường: ' . $curSchool;
if ($curQ !== '') $activeFilters[] = 'Từ khóa: ' . $curQ;

$majorBySchool = [];
foreach ($by_major_school as $row) {
    $school = trim((string)($row['school'] ?? ''));
    if ($school === '') $school = 'Chưa rõ trường';
    if (!isset($majorBySchool[$school])) $majorBySchool[$school] = [];
    $majorBySchool[$school][] = $row;
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  :root{
    --ifk-navy:#0b2e59;
    --ifk-blue:#123d73;
    --ifk-sky:#2ea7e0;
    --ifk-green:#95c11f;
    --ifk-bg:#f4f7fb;
    --ifk-card:#ffffff;
    --ifk-border:#e6edf5;
    --ifk-text:#18324d;
    --ifk-muted:#6b7f95;
  }

  body, #wrapper, .content{background:var(--ifk-bg);}
  .bcqt-wrap{padding:8px 0 18px;}
  .bcqt-card{
    background:var(--ifk-card);
    border:1px solid var(--ifk-border);
    border-radius:18px;
    box-shadow:0 8px 24px rgba(11,46,89,.06);
    overflow:hidden;
  }
  .bcqt-filter-card{
  overflow: visible !important;
  }
  .bcqt-filter-card .bcqt-card-body{
    overflow: visible !important;
  }
  .bcqt-filter-card .bootstrap-select{
  width: 100% !important;
  }
  .bcqt-filter-card .bootstrap-select > .dropdown-toggle{
  width: 100% !important;
  }
  .bcqt-filter-card .bootstrap-select .dropdown-menu{
    z-index: 9999 !important;
  }
  .bcqt-card-body{padding:18px;}
  .bcqt-header{
    display:flex;align-items:flex-end;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:16px;
  }
  .bcqt-title{
    margin:0;font-size:28px;font-weight:900;color:var(--ifk-navy);letter-spacing:-.02em;
  }
  .bcqt-sub{margin:6px 0 0;color:var(--ifk-muted);font-size:13px;font-weight:600;}
  .bcqt-actions{display:flex;gap:8px;flex-wrap:wrap;}

  .bcqt-actions .btn-default{
    border-color:var(--ifk-border);
    color:var(--ifk-navy);
    background:#fff;
    font-weight:700;
  }
  .bcqt-actions .btn-info,
  .bcqt-filter-submit{
    background:linear-gradient(135deg,var(--ifk-blue),var(--ifk-navy));
    border-color:var(--ifk-navy);
    color:#fff;
    font-weight:800;
  }

  .bcqt-filter-grid .form-group{margin-bottom:12px;}
  .bcqt-filter-grid label{
    color:var(--ifk-navy);
    font-size:12px;
    text-transform:uppercase;
    letter-spacing:.05em;
    font-weight:800;
    margin-bottom:6px;
  }
  .bcqt-filter-grid .form-control{
    border-radius:12px;
    border-color:var(--ifk-border);
    min-height:42px;
  }
  .bootstrap-select>.dropdown-toggle{
    border-radius:12px !important;
    border-color:var(--ifk-border) !important;
    min-height:42px;
  }

  .bcqt-filter-badges{margin-top:8px;}
  .bcqt-badge{
    display:inline-block;padding:5px 10px;border:1px solid #d5e5f2;background:#f8fbff;border-radius:999px;
    font-size:12px;color:var(--ifk-blue);margin:0 6px 6px 0;font-weight:700;
  }

  .bcqt-kpi{display:flex;align-items:center;justify-content:space-between;gap:14px;}
  .bcqt-kpi-label{
    font-size:11px;font-weight:900;color:var(--ifk-muted);text-transform:uppercase;letter-spacing:.08em;
  }
  .bcqt-kpi-value{margin-top:6px;font-size:34px;line-height:1;font-weight:900;color:var(--ifk-navy);}
  .bcqt-kpi-icon{
    width:54px;height:54px;border-radius:16px;display:flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg,#eef6ff,#f3fbff);color:var(--ifk-blue);font-size:24px;border:1px solid #dbe8f4;
  }

  .bcqt-section-head{
    display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;
  }
  .bcqt-section-title{margin:0;font-size:16px;font-weight:900;color:var(--ifk-navy);}
  .bcqt-section-meta{font-size:12px;color:var(--ifk-muted);font-weight:800;}

  .bcqt-chart-box{position:relative;height:320px;}
  .bcqt-chart-box.chart-sm{height:300px;}

  .bcqt-table{margin-bottom:0;}
  .bcqt-table thead th{
    white-space:nowrap;font-size:12px;color:var(--ifk-navy);border-bottom:1px solid var(--ifk-border) !important;
    text-transform:none;font-weight:800;
  }
  .bcqt-table tbody td{
    vertical-align:middle !important;color:#334155;border-top:1px solid #edf2f7 !important;
  }
  .bcqt-table tbody tr:hover td{background:#fbfdff;}
  .bcqt-report-table{
    table-layout: fixed;
    width: 100%;
  }
  .bcqt-report-table thead th,
  .bcqt-report-table tbody td{
    padding: 12px 14px !important;
  }
  .bcqt-report-table thead th:first-child,
  .bcqt-report-table tbody td:first-child{
    text-align: left !important;
  }
  .bcqt-report-table thead th:not(:first-child),
  .bcqt-report-table tbody td:not(:first-child){
    text-align: right !important;
  }
  .bcqt-report-table thead th{
    vertical-align: middle !important;
  }
  .bcqt-report-table tbody td{
    vertical-align: middle !important;
    font-variant-numeric: tabular-nums;
  }
  .bcqt-report-table tbody td:first-child{
    white-space: normal;
    word-break: break-word;
    line-height: 1.45;
  }
  .bcqt-report-table tbody td:not(:first-child),
  .bcqt-report-table thead th:not(:first-child){
    white-space: nowrap;
  }
  .bcqt-school-block{margin-top:14px;border:1px solid var(--ifk-border);border-radius:14px;overflow:hidden;}
  .bcqt-school-head{
      padding:14px 16px;
      background:linear-gradient(135deg,#f6fbff,#f9fcff);
      border-bottom:1px solid var(--ifk-border);
      font-weight:900;
      color:var(--ifk-navy);
    }
  .bcqt-note{margin-top:10px;font-size:12px;color:var(--ifk-muted);}
  .bcqt-empty{color:#94a3b8;font-style:italic;padding:10px 0 2px;}
  .mtop15{margin-top:15px;}
  .mtop20{margin-top:20px;}

  @media (max-width: 991px){
    .bcqt-kpi-value{font-size:28px;}
    .bcqt-chart-box,.bcqt-chart-box.chart-sm{height:280px;}
  }
</style>

<div id="wrapper">
  <div class="content">
    <div class="bcqt-wrap">

      <div class="bcqt-header">
        <div>
          <h3 class="bcqt-title"><?php echo html_escape($title ?? 'Báo cáo quản trị'); ?></h3>
          <p class="bcqt-sub">Dashboard quản trị tổng hợp theo bộ lọc: đơn tuyển, sinh viên, trường, ngành và pipeline.</p>
        </div>

        <div class="bcqt-actions">
          <a href="<?php echo html_escape($resetUrl); ?>" class="btn btn-default"><i class="fa fa-refresh"></i> Reset</a>
          <a href="<?php echo html_escape($exportUrl); ?>" class="btn btn-info"><i class="fa fa-download"></i> Xuất CSV</a>
        </div>
      </div>

      <!--<div class="bcqt-card">-->
      <div class="bcqt-card bcqt-filter-card">
        <div class="bcqt-card-body">
          <form method="get" action="<?php echo html_escape(admin_url('internship_management/management_report')); ?>">
            <div class="row bcqt-filter-grid">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Năm</label>
                  <!--<select class="selectpicker" data-width="100%" name="year">-->
                  <select class="selectpicker" data-width="100%" name="year" data-live-search="true">
                    <option value="0"<?php echo $curYear === 0 ? ' selected' : ''; ?>>Tất cả</option>
                    <?php foreach ($years as $y) { $y = (int)$y; ?>
                      <option value="<?php echo $y; ?>"<?php echo $curYear === $y ? ' selected' : ''; ?>><?php echo $y; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Tháng</label>
                  <!--<select class="selectpicker" data-width="100%" name="month">-->
                  <select class="selectpicker" data-width="100%" name="month" data-live-search="true">
                    <?php foreach ($months as $m) { $mv=(int)($m['value'] ?? 0); $ml=(string)($m['label'] ?? ''); ?>
                      <option value="<?php echo $mv; ?>"<?php echo $curMonth === $mv ? ' selected' : ''; ?>><?php echo html_escape($ml); ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Trạng thái</label>
                  <select class="selectpicker" data-width="100%" name="status" data-live-search="true">
                    <?php foreach ($statuses as $val => $label) { $val=(string)$val; $label=(string)$label; ?>
                      <option value="<?php echo html_escape($val); ?>"<?php echo $curStatus === $val ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label>Trường</label>
                  <!--<select class="selectpicker" data-width="100%" name="school" data-live-search="true">-->
                  <select class="selectpicker" data-width="100%" name="school" data-live-search="true" title="Tất cả">
                    <?php foreach ($schools as $val => $label) { $val=(string)$val; $label=(string)$label; ?>
                      <option value="<?php echo html_escape($val); ?>"<?php echo $curSchool === $val ? ' selected' : ''; ?>><?php echo html_escape($label); ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <div class="col-md-9">
                <div class="form-group">
                  <label>Từ khóa</label>
                  <input type="text" class="form-control" name="q" value="<?php echo html_escape($curQ); ?>" placeholder="Tên SV / SĐT / Email / Công ty tiếp nhận / Tỉnh...">
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group" style="margin-top:24px;">
                  <button type="submit" class="btn btn-primary btn-block bcqt-filter-submit"><i class="fa fa-filter"></i> Lọc</button>
                </div>
              </div>
            </div>
          </form>

          <?php if (!empty($activeFilters)) { ?>
            <div class="bcqt-filter-badges">
              <?php foreach ($activeFilters as $af) { ?>
                <span class="bcqt-badge"><?php echo html_escape($af); ?></span>
              <?php } ?>
            </div>
          <?php } ?>
        </div>
      </div>

      <div class="row mtop15">
        <div class="col-md-3">
          <div class="bcqt-card"><div class="bcqt-card-body">
            <div class="bcqt-kpi">
              <div><div class="bcqt-kpi-label">Đơn tuyển</div><div class="bcqt-kpi-value"><?php echo (int)($k['total_job_orders'] ?? 0); ?></div></div>
              <div class="bcqt-kpi-icon"><i class="fa fa-briefcase"></i></div>
            </div>
          </div></div>
        </div>

        <div class="col-md-3">
          <div class="bcqt-card"><div class="bcqt-card-body">
            <div class="bcqt-kpi">
              <div><div class="bcqt-kpi-label">Sinh viên</div><div class="bcqt-kpi-value"><?php echo (int)($k['total_students'] ?? 0); ?></div></div>
              <div class="bcqt-kpi-icon"><i class="fa fa-users"></i></div>
            </div>
          </div></div>
        </div>

        <div class="col-md-3">
          <div class="bcqt-card"><div class="bcqt-card-body">
            <div class="bcqt-kpi">
              <div><div class="bcqt-kpi-label">Đang ở Nhật</div><div class="bcqt-kpi-value"><?php echo (int)($k['in_japan'] ?? 0); ?></div></div>
              <div class="bcqt-kpi-icon"><i class="fa fa-plane"></i></div>
            </div>
          </div></div>
        </div>

        <div class="col-md-3">
          <div class="bcqt-card"><div class="bcqt-card-body">
            <div class="bcqt-kpi">
              <div><div class="bcqt-kpi-label">Đã về nước</div><div class="bcqt-kpi-value"><?php echo (int)($k['returned'] ?? 0); ?></div></div>
              <div class="bcqt-kpi-icon"><i class="fa fa-home"></i></div>
            </div>
          </div></div>
        </div>
      </div>

      <div class="row mtop15">
        <div class="col-md-6">
          <div class="bcqt-card"><div class="bcqt-card-body">
            <div class="bcqt-section-head">
              <h5 class="bcqt-section-title">Phân bố pipeline</h5>
              <div class="bcqt-section-meta">Theo số SV</div>
            </div>
            <div class="bcqt-chart-box chart-sm"><canvas id="bcqtPipelinePie"></canvas></div>
          </div></div>
        </div>

        <div class="col-md-6">
          <div class="bcqt-card"><div class="bcqt-card-body">
            <div class="bcqt-section-head">
              <h5 class="bcqt-section-title">So sánh trạng thái</h5>
              <div class="bcqt-section-meta">Bar chart</div>
            </div>
            <div class="bcqt-chart-box chart-sm"><canvas id="bcqtStatusBar"></canvas></div>
          </div></div>
        </div>
      </div>

      <div class="row mtop15">
        <div class="col-md-6">
          <div class="bcqt-card"><div class="bcqt-card-body">
            <div class="bcqt-section-head">
              <h5 class="bcqt-section-title">Top trường</h5>
              <div class="bcqt-section-meta">Theo tổng SV</div>
            </div>
            <div class="bcqt-chart-box"><canvas id="bcqtSchoolBar"></canvas></div>
          </div></div>
        </div>

        <div class="col-md-6">
          <div class="bcqt-card"><div class="bcqt-card-body">
            <div class="bcqt-section-head">
              <h5 class="bcqt-section-title">Theo trường</h5>
              <div class="bcqt-section-meta"><?php echo count($by_school); ?> dòng</div>
            </div>

            <?php if (!empty($by_school)) { ?>
              <div class="table-responsive">
                <!--<table class="table table-striped bcqt-table">-->
                <table class="table table-striped bcqt-table bcqt-report-table">
                <colgroup>
                  <col style="width:38%">
                  <col style="width:14.5%">
                  <col style="width:14.5%">
                  <col style="width:14.5%">
                  <col style="width:14.5%">
                </colgroup>   
                  <thead>
                    <tr>
                      <th>Trường</th>
                      <th class="text-right">Tổng SV</th>
                      <th class="text-right">Đang Nhật</th>
                      <th class="text-right">Về nước</th>
                      <th class="text-right">Tỷ trọng</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($by_school as $r) { ?>
                      <tr>
                        <td><?php echo html_escape((string)($r['name'] ?? '')); ?></td>
                        <td class="text-right"><?php echo (int)($r['total'] ?? 0); ?></td>
                        <td class="text-right"><?php echo (int)($r['in_japan'] ?? 0); ?></td>
                        <td class="text-right"><?php echo (int)($r['returned'] ?? 0); ?></td>
                        <td class="text-right"><?php echo html_escape((string)($r['ratio'] ?? '')); ?></td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            <?php } else { ?>
              <div class="bcqt-empty">Không có dữ liệu.</div>
            <?php } ?>
          </div></div>
        </div>
      </div>

      <div class="bcqt-card mtop15">
        <div class="bcqt-card-body">
          <div class="bcqt-section-head">
            <h5 class="bcqt-section-title">Theo ngành × Trường</h5>
            <div class="bcqt-section-meta"><?php echo count($by_major_school); ?> dòng</div>
          </div>

          <?php if (!empty($majorBySchool)) { ?>
            <?php foreach ($majorBySchool as $schoolName => $rows) { ?>
              <div class="bcqt-school-block">
                <div class="bcqt-school-head"><?php echo html_escape($schoolName); ?></div>
                <div class="table-responsive">
                  <!--<table class="table table-striped bcqt-table" style="margin-bottom:0;">-->
                  <table class="table table-striped bcqt-table bcqt-report-table" style="margin-bottom:0;">    
                  <colgroup>
                      <col style="width:38%">
                      <col style="width:14.5%">
                      <col style="width:14.5%">
                      <col style="width:14.5%">
                      <col style="width:14.5%">
                    </colgroup>
                    <thead>
                      <tr>
                        <th>Ngành</th>
                        <th class="text-right">Tổng SV</th>
                        <th class="text-right">Đang Nhật</th>
                        <th class="text-right">Về nước</th>
                        <th class="text-right">Tỷ trọng</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($rows as $r) { ?>
                        <tr>
                          <td><?php echo html_escape((string)($r['major'] ?? '')); ?></td>
                          <td class="text-right"><?php echo (int)($r['total'] ?? 0); ?></td>
                          <td class="text-right"><?php echo (int)($r['in_japan'] ?? 0); ?></td>
                          <td class="text-right"><?php echo (int)($r['returned'] ?? 0); ?></td>
                          <td class="text-right"><?php echo html_escape((string)($r['ratio'] ?? '')); ?></td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php } ?>
            <div class="bcqt-note">* “Tỷ trọng” = tỷ lệ SV của ngành trong từng trường.</div>
          <?php } else { ?>
            <div class="bcqt-empty">Không có dữ liệu.</div>
          <?php } ?>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
/*(function () {
  if (window.jQuery && $.fn.selectpicker) $('.selectpicker').selectpicker('refresh'); */
  (function () {
  if (window.jQuery && $.fn.selectpicker) {
    $('.selectpicker').selectpicker('render');
    $('.selectpicker').selectpicker('refresh');
  }

  const byStatus = <?php echo json_encode(array_values($by_status), JSON_UNESCAPED_UNICODE); ?>;
  const bySchool = <?php echo json_encode(array_values($by_school), JSON_UNESCAPED_UNICODE); ?>;

  const pipelineLabels = byStatus.map(r => (r.name || ''));
  const pipelineValues = byStatus.map(r => parseInt(r.total_students || 0));
  
  const pipelineKeys = byStatus.map(r => ((r.key || '') + '').toLowerCase().trim());

    function statusColor(key) {
      const map = {
        not_updated: '#9ca3af',
        applied: '#00a6dc',
        interview_scheduled: '#00325a',
        pass: '#96bc17',
        passed: '#96bc17',
        fail: '#ef4444',
    
        prepare_documents: '#f59e0b',
        docs_preparing: '#f59e0b',
    
        done_documents: '#22c55e',
        docs_done: '#22c55e',
    
        waiting_coe: '#6366f1',
        coe_waiting: '#6366f1',
    
        got_coe: '#8b5cf6',
        coe_done: '#8b5cf6',
    
        visa_processing: '#0ea5e9',
        ticket_booking: '#fb7185',
        pre_departure: '#f97316',
    
        entry: '#00a6dc',
        in_japan: '#96bc17',
        returned: '#14b8a6',
    
        cancelled: '#6b7280',
        canceled: '#6b7280',
        done: '#16a34a',
        received: '#94a3b8'
      };
    
      return map[key] || '#123d73';
    }
    
    const pipelineColors = pipelineKeys.map(statusColor);
  
  
  const schoolLabels = bySchool.map(r => (r.name || ''));
  const schoolValues = bySchool.map(r => parseInt(r.total || 0));

  const textColor = '#334155';
  const gridColor = '#e5e7eb';
  const commonLegend = {
    position: 'bottom',
    labels: {
      boxWidth: 12,
      boxHeight: 12,
      usePointStyle: true,
      pointStyle: 'circle',
      font: { size: 11 }
    }
  };

  if (document.getElementById('bcqtPipelinePie')) {
    new Chart(document.getElementById('bcqtPipelinePie'), {
      type: 'doughnut',
      data: {
        labels: pipelineLabels,
        datasets: [{
          data: pipelineValues,
          //backgroundColor: ['#123d73','#95c11f','#2ea7e0','#f59e0b','#ef4444','#8b5cf6'],
          backgroundColor: pipelineColors,
          borderColor: '#ffffff',
          borderWidth: 2,
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '58%',
        plugins: {
          legend: commonLegend,
          tooltip: { callbacks: { label: function(ctx) { return ctx.label + ': ' + ctx.parsed + ' SV'; } } }
        }
      }
    });
  }

  if (document.getElementById('bcqtStatusBar')) {
    new Chart(document.getElementById('bcqtStatusBar'), {
      type: 'bar',
      data: {
        labels: pipelineLabels,
        datasets: [{
          label: 'Số SV',
          data: pipelineValues,
          //backgroundColor: '#123d73',
          backgroundColor: pipelineColors,
          borderRadius: 8,
          maxBarThickness: 48
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { ticks: { color: textColor, font: { size: 11 } }, grid: { display: false } },
          y: { beginAtZero: true, ticks: { color: textColor, precision: 0 }, grid: { color: gridColor } }
        }
      }
    });
  }

  if (document.getElementById('bcqtSchoolBar')) {
    new Chart(document.getElementById('bcqtSchoolBar'), {
      type: 'bar',
      data: {
        labels: schoolLabels,
        datasets: [{
          label: 'Tổng SV',
          data: schoolValues,
          backgroundColor: '#2ea7e0',
          borderRadius: 8,
          maxBarThickness: 56
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { beginAtZero: true, ticks: { color: textColor, precision: 0 }, grid: { color: gridColor } },
          y: { ticks: { color: textColor, font: { size: 11 } }, grid: { display: false } }
        }
      }
    });
  }
})();
</script>

<?php init_tail(); ?>
