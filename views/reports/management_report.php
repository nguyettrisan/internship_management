<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
$filters  = is_array($filters ?? null) ? $filters : [];
$years    = is_array($years ?? null) ? $years : [];
$months   = is_array($months ?? null) ? $months : [];
$statuses = is_array($statuses ?? null) ? $statuses : [];

$year   = (int)($filters['year'] ?? 0);
$month  = (int)($filters['month'] ?? 0);
$q      = (string)($filters['q'] ?? '');
$major  = (string)($filters['major'] ?? '');
$status = (string)($filters['status'] ?? '');

$kpi = is_array($kpi ?? null) ? $kpi : ['total_job_orders'=>0,'total_students'=>0,'in_japan'=>0,'returned'=>0];
$by_school = is_array($by_school ?? null) ? $by_school : [];
$by_major  = is_array($by_major ?? null) ? $by_major : [];

function sel($a,$b){ return ((string)$a===(string)$b) ? 'selected' : ''; }
?>

<style>
  .mr-page{max-width:1400px;}
  .mr-filter-card{border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(16,24,40,.06);padding:16px 16px 12px;margin-bottom:16px;}
  .mr-filter-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
  .mr-filter-title{display:flex;align-items:center;gap:10px;font-weight:900;font-size:18px;margin:0;}
  .mr-actions{display:flex;gap:10px;flex-wrap:wrap;}
  .mr-actions .btn{border-radius:12px;}
  .mr-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:12px;margin-top:14px;}
  .c2{grid-column:span 2;} .c3{grid-column:span 3;} .c4{grid-column:span 4;} .c12{grid-column:span 12;}
  @media(max-width:1200px){.c2,.c3,.c4{grid-column:span 12;}}
  .mr-right{display:flex;justify-content:flex-end;align-items:end;}
  .mr-report{border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(16,24,40,.06);padding:18px;margin-bottom:16px;}
  .mr-title{font-weight:900;font-size:20px;margin:0;display:flex;align-items:center;gap:10px;}
  .mr-sub{color:#667085;margin-top:6px;}
  .mr-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-top:16px;}
  @media(max-width:1200px){.mr-kpis{grid-template-columns:repeat(2,minmax(0,1fr));}}
  @media(max-width:620px){.mr-kpis{grid-template-columns:1fr;}}
  .kpi{border:1px solid #eaecf0;border-radius:14px;padding:18px;background:#fff;}
  .kpi .label{color:#667085;font-weight:800;}
  .kpi .val{font-size:32px;font-weight:900;margin-top:6px;}
  .mr-two{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:16px;}
  .card{border-radius:16px;background:#fff;box-shadow:0 8px 24px rgba(16,24,40,.06);padding:18px;}
  .span6{grid-column:span 6;} @media(max-width:1200px){.span6{grid-column:span 12;}}
  .hint{color:#667085;margin-top:4px;}
  .mr-table th{color:#667085;font-size:12px;font-weight:900;}
  .mr-table td{font-size:13px;}
</style>

<div id="wrapper">
  <div class="content mr-page">

    <div class="mr-filter-card">
      <div class="mr-filter-head">
        <div class="mr-filter-title">
          <i class="fa fa-filter" style="color:#0ea5e9;"></i> Bộ lọc báo cáo
        </div>

        <div class="mr-actions">
          <button type="button" class="btn btn-default" id="btnAdvanced">
            <i class="fa fa-sliders"></i> Nâng cao
          </button>

          <a class="btn btn-default" href="<?php echo admin_url('internship_management/management_report/export_csv?'.http_build_query($_GET)); ?>">
            <i class="fa fa-download"></i> CSV
          </a>

          <a class="btn btn-default" href="<?php echo admin_url('internship_management/management_report/export_excel?'.http_build_query($_GET)); ?>">
            <i class="fa fa-file-excel-o"></i> Excel
          </a>

          <a class="btn btn-default" href="<?php echo admin_url('internship_management/management_report'); ?>">
            <i class="fa fa-refresh"></i> Reset
          </a>
        </div>
      </div>

      <form method="get">
        <div class="mr-grid">
          <div class="c2">
            <div class="form-group">
              <label>Năm</label>
              <select class="form-control" name="year">
                <option value="0" <?php echo sel(0,$year); ?>>Tất cả</option>
                <?php foreach ($years as $y): $y=(int)$y; ?>
                  <option value="<?php echo $y; ?>" <?php echo sel($y,$year); ?>><?php echo $y; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="c2">
            <div class="form-group">
              <label>Tháng</label>
              <select class="form-control" name="month">
                <?php foreach ($months as $m): ?>
                  <option value="<?php echo (int)$m['value']; ?>" <?php echo sel((int)$m['value'],$month); ?>>
                    <?php echo html_escape($m['label']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="c4">
            <div class="form-group">
              <label>Tìm kiếm</label>
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                <input type="text" class="form-control" name="q" value="<?php echo html_escape($q); ?>"
                       placeholder="Tên SV / trường / ngành / công ty">
              </div>
            </div>
          </div>

          <div class="c3">
            <div class="form-group">
              <label>Ngành</label>
              <input type="text" class="form-control" name="major" value="<?php echo html_escape($major); ?>"
                     placeholder="VD: Điều dưỡng, Cơ khí...">
            </div>
          </div>

          <div class="c2" id="advWrap" style="display:none;">
            <div class="form-group">
              <label>Trạng thái</label>
              <select class="form-control" name="status">
                <?php foreach ($statuses as $k=>$v): ?>
                  <option value="<?php echo html_escape($k); ?>" <?php echo sel($k,$status); ?>>
                    <?php echo html_escape($v); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="c12 mr-right">
            <button class="btn btn-primary" style="border-radius:12px;">
              <i class="fa fa-filter"></i> Lọc
            </button>
          </div>
        </div>
      </form>
    </div>

    <div class="mr-report">
      <div class="mr-title"><i class="fa fa-line-chart"></i> BÁO CÁO QUẢN TRỊ</div>
      <div class="mr-sub">
        Tổng hợp theo bộ lọc hiện tại: số đơn tuyển, số sinh viên, phân bố theo trường/ngành, và tình trạng đang ở Nhật / đã về nước.
      </div>

      <div class="mr-kpis">
        <div class="kpi"><div class="label">Đơn tuyển</div><div class="val"><?php echo (int)$kpi['total_job_orders']; ?></div></div>
        <div class="kpi"><div class="label">Sinh viên</div><div class="val"><?php echo (int)$kpi['total_students']; ?></div></div>
        <div class="kpi"><div class="label">Đang ở Nhật</div><div class="val"><?php echo (int)$kpi['in_japan']; ?></div></div>
        <div class="kpi"><div class="label">Đã về nước</div><div class="val"><?php echo (int)$kpi['returned']; ?></div></div>
      </div>
    </div>

    <div class="mr-two">
      <div class="card span6">
        <h3 style="margin:0;font-weight:900;font-size:20px;">Theo trường</h3>
        <div class="hint">Top theo tổng số sinh viên • có hiển thị tỷ trọng</div>

        <div class="table-responsive" style="margin-top:14px;">
          <table class="table table-hover mr-table">
            <thead>
              <tr>
                <th>Trường</th>
                <th class="text-center">Tổng SV</th>
                <th class="text-center">Đang Nhật</th>
                <th class="text-center">Về nước</th>
                <th class="text-center">Tỷ trọng</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($by_school)): foreach ($by_school as $r): ?>
                <tr>
                  <td><?php echo html_escape($r['school'] ?? ''); ?></td>
                  <td class="text-center"><?php echo (int)($r['total'] ?? 0); ?></td>
                  <td class="text-center"><?php echo (int)($r['in_japan'] ?? 0); ?></td>
                  <td class="text-center"><?php echo (int)($r['returned'] ?? 0); ?></td>
                  <td class="text-center"><?php echo html_escape($r['ratio'] ?? ''); ?></td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="5" class="text-muted text-center">Không có dữ liệu</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card span6">
        <h3 style="margin:0;font-weight:900;font-size:20px;">Theo ngành</h3>
        <div class="hint">Top theo tổng số sinh viên • có hiển thị tỷ trọng</div>

        <div class="table-responsive" style="margin-top:14px;">
          <table class="table table-hover mr-table">
            <thead>
              <tr>
                <th>Ngành</th>
                <th class="text-center">Tổng SV</th>
                <th class="text-center">Đang Nhật</th>
                <th class="text-center">Về nước</th>
                <th class="text-center">Tỷ trọng</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($by_major)): foreach ($by_major as $r): ?>
                <tr>
                  <td><?php echo html_escape($r['major'] ?? ''); ?></td>
                  <td class="text-center"><?php echo (int)($r['total'] ?? 0); ?></td>
                  <td class="text-center"><?php echo (int)($r['in_japan'] ?? 0); ?></td>
                  <td class="text-center"><?php echo (int)($r['returned'] ?? 0); ?></td>
                  <td class="text-center"><?php echo html_escape($r['ratio'] ?? ''); ?></td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="5" class="text-muted text-center">Không có dữ liệu</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
  (function(){
    var btn = document.getElementById('btnAdvanced');
    var adv = document.getElementById('advWrap');
    if(btn && adv){
      btn.addEventListener('click', function(){
        adv.style.display = (adv.style.display === 'none' || adv.style.display === '') ? 'block' : 'none';
      });
    }
  })();
</script>

<?php init_tail(); ?>