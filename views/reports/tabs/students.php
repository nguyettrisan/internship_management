<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$dash = $students_dashboard ?? [];
$kpi  = $dash['kpi'] ?? ['job_orders'=>0,'students'=>0,'processing'=>0,'in_japan'=>0,'returned'=>0];
$by_school = $dash['by_school'] ?? [];
$by_major  = $dash['by_major'] ?? [];
?>

<style>
.rep-kpi{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin:10px 0 16px}
@media (max-width:1200px){.rep-kpi{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media (max-width:520px){.rep-kpi{grid-template-columns:1fr;}}
.rep-card{border-radius:16px;border:1px solid #eef2f7;background:#fff;box-shadow:0 1px 3px rgba(15,23,42,.04);padding:14px}
.rep-card .k{font-size:12px;color:#64748b;font-weight:900;text-transform:uppercase;letter-spacing:.03em}
.rep-card .v{font-size:22px;color:#0f172a;font-weight:950;margin-top:6px}
.rep-split{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media (max-width:992px){.rep-split{grid-template-columns:1fr;}}
.rep-table thead th{background:#f6f8fc;border:none;font-size:12px;text-transform:uppercase;letter-spacing:.03em;color:#64748b;white-space:nowrap}
.rep-table tbody td{vertical-align:middle}
</style>

<?php if (!empty($dash['error'])): ?>
  <div class="alert alert-warning">
    <b>Không thể tạo báo cáo sinh viên:</b> <?= html_escape($dash['error']); ?>
  </div>
<?php endif; ?>

<div class="rep-kpi">
  <div class="rep-card"><div class="k">Đơn tuyển</div><div class="v"><?= (int)$kpi['job_orders']; ?></div></div>
  <div class="rep-card"><div class="k">Sinh viên</div><div class="v"><?= (int)$kpi['students']; ?></div></div>
  <div class="rep-card"><div class="k">Đang làm hồ sơ</div><div class="v"><?= (int)$kpi['processing']; ?></div></div>
  <div class="rep-card"><div class="k">Đang ở Nhật</div><div class="v"><?= (int)$kpi['in_japan']; ?></div></div>
  <div class="rep-card"><div class="k">Đã về nước</div><div class="v"><?= (int)$kpi['returned']; ?></div></div>
</div>

<div class="rep-split">
  <div class="panel_s" style="border-radius:16px;border:none;box-shadow:0 10px 22px rgba(15,23,42,.06)">
    <div class="panel-body">
      <h4 style="margin:0 0 10px;font-weight:950;color:#0f172a;"><i class="fa fa-university"></i> Theo Trường</h4>
      <div class="table-responsive">
        <table class="table rep-table">
          <thead>
            <tr>
              <th>Trường</th>
              <th class="text-right">Tổng SV</th>
              <th class="text-right">Hồ sơ</th>
              <th class="text-right">Đang Nhật</th>
              <th class="text-right">Về nước</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($by_school)): foreach($by_school as $r): ?>
              <tr>
                <td><?= html_escape($r['label']); ?></td>
                <td class="text-right"><?= (int)$r['total']; ?></td>
                <td class="text-right"><?= (int)$r['processing']; ?></td>
                <td class="text-right"><?= (int)$r['in_japan']; ?></td>
                <td class="text-right"><?= (int)$r['returned']; ?></td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="5" class="text-center text-muted">Chưa có dữ liệu.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="panel_s" style="border-radius:16px;border:none;box-shadow:0 10px 22px rgba(15,23,42,.06)">
    <div class="panel-body">
      <h4 style="margin:0 0 10px;font-weight:950;color:#0f172a;"><i class="fa fa-graduation-cap"></i> Theo Ngành/Khoa</h4>
      <div class="table-responsive">
        <table class="table rep-table">
          <thead>
            <tr>
              <th>Ngành/Khoa</th>
              <th class="text-right">Tổng SV</th>
              <th class="text-right">Hồ sơ</th>
              <th class="text-right">Đang Nhật</th>
              <th class="text-right">Về nước</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($by_major)): foreach($by_major as $r): ?>
              <tr>
                <td><?= html_escape($r['label']); ?></td>
                <td class="text-right"><?= (int)$r['total']; ?></td>
                <td class="text-right"><?= (int)$r['processing']; ?></td>
                <td class="text-right"><?= (int)$r['in_japan']; ?></td>
                <td class="text-right"><?= (int)$r['returned']; ?></td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="5" class="text-center text-muted">Chưa có dữ liệu.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
