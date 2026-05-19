<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$mgmt = isset($mgmt) && is_array($mgmt) ? $mgmt : ['kpi'=>[],'by_school'=>[],'by_major'=>[]];
$k = $mgmt['kpi'] ?? [];
$by_school = $mgmt['by_school'] ?? [];
$by_major  = $mgmt['by_major'] ?? [];
?>
<style>
.mgmt-wrap{margin-top:12px}
.mgmt-title{display:flex;align-items:center;gap:10px;margin:6px 0 10px}
.mgmt-title h4{margin:0;font-weight:950;color:#0f172a}
.mgmt-sub{color:#64748b;font-size:13px}
.mgmt-kpi{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:10px 0 14px}
@media(max-width:1100px){.mgmt-kpi{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:520px){.mgmt-kpi{grid-template-columns:1fr;}}
.mgmt-card{background:#fff;border:1px solid #eef2f7;border-radius:16px;padding:14px 14px;box-shadow:0 1px 3px rgba(15,23,42,.05)}
.mgmt-card .label{font-size:12px;color:#64748b;font-weight:900}
.mgmt-card .value{font-size:22px;color:#0f172a;font-weight:950;margin-top:6px}
.mgmt-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:1100px){.mgmt-grid{grid-template-columns:1fr;}}
.mgmt-table th{background:#f6f8fc!important;border:none!important;font-size:12px;text-transform:uppercase;letter-spacing:.03em;color:#64748b}
.mgmt-table td{border-top:none!important}
.badge-pill{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:4px 10px;font-weight:900;font-size:12px}
.badge-total{background:#eef2ff;color:#3730a3}
.badge-jp{background:#ecfeff;color:#155e75}
.badge-ret{background:#fff7ed;color:#9a3412}
</style>

<div class="mgmt-wrap">
  <div class="mgmt-title">
    <i class="fa fa-chart-line"></i>
    <div>
      <h4>BÁO CÁO QUẢN TRỊ</h4>
      <div class="mgmt-sub">Tổng hợp theo bộ lọc hiện tại: số đơn tuyển, số sinh viên, phân bổ theo trường/ngành, đang ở Nhật &amp; đã về nước.</div>
    </div>
  </div>

  <div class="mgmt-kpi">
    <div class="mgmt-card">
      <div class="label">Đơn tuyển</div>
      <div class="value"><?= (int)($k['job_orders'] ?? 0); ?></div>
    </div>
    <div class="mgmt-card">
      <div class="label">Sinh viên</div>
      <div class="value"><?= (int)($k['students'] ?? 0); ?></div>
    </div>
    <div class="mgmt-card">
      <div class="label">Đang ở Nhật</div>
      <div class="value"><?= (int)($k['in_japan'] ?? 0); ?></div>
    </div>
    <div class="mgmt-card">
      <div class="label">Đã về nước</div>
      <div class="value"><?= (int)($k['returned'] ?? 0); ?></div>
    </div>
  </div>

  <div class="mgmt-grid">
    <div class="mgmt-card">
      <div class="tw-flex tw-items-center tw-justify-between">
        <strong>Theo trường</strong>
        <span class="text-muted" style="font-size:12px">Top theo tổng SV</span>
      </div>
      <div class="table-responsive" style="margin-top:10px">
        <table class="table mgmt-table">
          <thead>
            <tr>
              <th>Trường</th>
              <th class="text-center">Tổng SV</th>
              <th class="text-center">Đang Nhật</th>
              <th class="text-center">Về nước</th>
            </tr>
          </thead>
          <tbody>
            <?php if(!empty($by_school)): foreach($by_school as $r): ?>
              <tr>
                <td><?= html_escape($r['label'] ?? '—'); ?></td>
                <td class="text-center"><span class="badge-pill badge-total"><?= (int)($r['total'] ?? 0); ?></span></td>
                <td class="text-center"><span class="badge-pill badge-jp"><?= (int)($r['in_japan'] ?? 0); ?></span></td>
                <td class="text-center"><span class="badge-pill badge-ret"><?= (int)($r['returned'] ?? 0); ?></span></td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="4" class="text-center text-muted" style="padding:16px">Chưa có dữ liệu.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="mgmt-card">
      <div class="tw-flex tw-items-center tw-justify-between">
        <strong>Theo ngành</strong>
        <span class="text-muted" style="font-size:12px">Top theo tổng SV</span>
      </div>
      <div class="table-responsive" style="margin-top:10px">
        <table class="table mgmt-table">
          <thead>
            <tr>
              <th>Ngành</th>
              <th class="text-center">Tổng SV</th>
              <th class="text-center">Đang Nhật</th>
              <th class="text-center">Về nước</th>
            </tr>
          </thead>
          <tbody>
            <?php if(!empty($by_major)): foreach($by_major as $r): ?>
              <tr>
                <td><?= html_escape($r['label'] ?? '—'); ?></td>
                <td class="text-center"><span class="badge-pill badge-total"><?= (int)($r['total'] ?? 0); ?></span></td>
                <td class="text-center"><span class="badge-pill badge-jp"><?= (int)($r['in_japan'] ?? 0); ?></span></td>
                <td class="text-center"><span class="badge-pill badge-ret"><?= (int)($r['returned'] ?? 0); ?></span></td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="4" class="text-center text-muted" style="padding:16px">Chưa có dữ liệu.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
