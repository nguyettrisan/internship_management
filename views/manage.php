<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id=\"wrapper\">
  <div class=\"content\">
<div class="ifk-manage">
<?php
/**
 * Internship Management - Manage (PRO UI)
 * Fixes:
 *  - Use controller-provided variables: $filter_options, $counters
 *  - Robust helpers (avatar, date)
 *  - Ensure entry/return dates render even when stored in different formats
 *  - Sensible fallbacks for status map + mail url
 */

// ---------- helpers ----------
if (!function_exists('im_fmt_date')) {
  function im_fmt_date($v, $fallback='—'){
    $v = trim((string)$v);
    if ($v === '' || $v === '0000-00-00' || $v === '0000-00-00 00:00:00') return $fallback;

    // numeric timestamp
    if (ctype_digit($v)) {
      $t = (int)$v;
      if ($t > 0) return date('Y-m-d', $t);
    }

    $t = strtotime($v);
    if ($t) return date('Y-m-d', $t);

    return html_escape($v);
  }
}

if (!function_exists('im_avatar_url')) {
  function im_avatar_url($avatar, $student_id = 0, $contact_id = 0) {
    $avatar     = trim((string)$avatar);
    $student_id = (int)$student_id;
    $contact_id = (int)$contact_id;

    // Prefer Perfex contact avatar (uploads/client_profile_images/{contact_id}/...)
    if ($contact_id > 0) {
      $dir = rtrim((string)FCPATH, '/').'/uploads/client_profile_images/'.$contact_id.'/';
      if (is_dir($dir)) {
        $files = glob($dir.'*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
        if (!empty($files)) {
          return base_url('uploads/client_profile_images/'.$contact_id.'/'.basename($files[0]));
        }
      }
    }

    // If stored as url or relative path already
    if ($avatar !== '') {
      if (preg_match('/^https?:\/\//i', $avatar)) return $avatar;
      if (strpos($avatar, '/') !== false) return base_url(ltrim($avatar, '/'));
    }

    // Try common patterns
    $candidates = [];
    if ($student_id > 0 && $avatar !== '') {
      $candidates[] = 'uploads/internship_students/'.$student_id.'/'.$avatar;
      $candidates[] = 'uploads/internship_avatar/'.$student_id.'/'.$avatar;
      $candidates[] = 'uploads/internship_files/students/'.$student_id.'/'.$avatar;
      $candidates[] = 'uploads/students/'.$student_id.'/'.$avatar;
    }
    if ($avatar !== '') {
      $candidates[] = 'uploads/internship_avatar/'.$avatar;
      $candidates[] = 'uploads/internship_files/'.$avatar;
      $candidates[] = 'uploads/internship_students/'.$avatar;
      $candidates[] = 'uploads/students/'.$avatar;
    }

    $base = rtrim((string)FCPATH, '/').'/';
    foreach ($candidates as $rel) {
      if (@file_exists($base.$rel)) return base_url($rel);
    }

    return base_url('assets/images/user-placeholder.png');
  }
}

if (!function_exists('im_badge')) {
  function im_badge($text, $type='default') {
    $map = [
      'default' => 'im-badge',
      'info'    => 'im-badge im-badge-info',
      'warn'    => 'im-badge im-badge-warn',
      'success' => 'im-badge im-badge-success',
      'danger'  => 'im-badge im-badge-danger',
    ];
    $cls = $map[$type] ?? $map['default'];
    return '<span class="'.html_escape($cls).'">'.html_escape($text).'</span>';
  }
}

// ---------- inputs / fallbacks ----------
$filters = isset($filters) && is_array($filters) ? $filters : [
  'keyword'         => (string)get_instance()->input->get('keyword'),
  'filter_status'   => (string)get_instance()->input->get('filter_status'),
  'filter_school'   => (string)get_instance()->input->get('filter_school'),
  'filter_company'  => (string)get_instance()->input->get('filter_company'),
  'filter_staff_id' => (string)get_instance()->input->get('filter_staff_id'),
];

$opts = $filter_options ?? $options ?? [];
if (!is_array($opts)) $opts = [];

$counters = $counters ?? $kpi ?? [];
if (!is_array($counters)) $counters = [];

// Status labels (sync with model/manage_status values)
$status_map = $status_map ?? [
  'processing' => 'Đang làm hồ sơ',
  'in_japan'   => 'Đang ở Nhật',
  'returned'   => 'Đã về nước',
  'cancelled'  => 'Đã hủy',
];

// Mail URL fallback
$mail_url = $mail_url ?? admin_url('internship_management/internship_mail/send_mail');
$care_url = $care_url ?? $mail_url;
?>

<style>
/* =====================================================
   IFK Manage (SAFE SCOPED) - chỉ ăn trong .ifk-manage
   Palette: #00325a / #96bc17 / #00a6dc
   Font: inherit (CRM)
   ===================================================== */
.ifk-manage{
  --ifk-navy:#00325a;
  --ifk-green:#96bc17;
  --ifk-cyan:#00a6dc;

  --ifk-text:#0f172a;
  --ifk-muted:#64748b;
  --ifk-border:rgba(0,50,90,.14);

  --ifk-soft:rgba(0,166,220,.06);
  --ifk-soft-2:rgba(150,188,23,.10);

  --ifk-shadow:0 10px 22px rgba(0,50,90,.10);
  --ifk-shadow-2:0 6px 16px rgba(0,50,90,.08);
  font-family:inherit;
}

/* ✅ KHÔNG đè global .panel_s nữa */
.ifk-manage .panel_s{
  border-radius:16px !important;
  border:1px solid var(--ifk-border) !important;
  box-shadow:var(--ifk-shadow-2) !important;
  background:#fff !important;
}
.ifk-manage .panel-body{padding:16px 18px}

/* Header */
.ifk-manage .im-header{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:12px;
  margin-bottom:14px;
}
.ifk-manage .im-header h4{
  margin:0;
  font-weight:950;
  color:var(--ifk-navy);
}
.ifk-manage .im-subtitle{
  font-size:13px;
  color:var(--ifk-muted);
  margin-top:6px;
  font-weight:600;
}

/* Filter bar */
.ifk-manage .im-filterbar{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  align-items:flex-end;
  margin:10px 0 16px;
}
.ifk-manage .im-field{min-width:160px}
.ifk-manage .im-field.big{flex:1;min-width:280px}
.ifk-manage .im-label{
  font-size:12px;
  font-weight:900;
  color:#334155;
  margin-bottom:6px;
}
.ifk-manage .im-filterbar input,
.ifk-manage .im-filterbar select{
  border-radius:12px !important;
  border:1px solid var(--ifk-border) !important;
}
.ifk-manage .im-filterbar .btn{border-radius:12px;font-weight:800}
.ifk-manage .im-filterbar .btn i{margin-right:6px}

/* Buttons IFK (scoped) */
.ifk-manage .btn-primary{
  background:var(--ifk-navy) !important;
  border-color:var(--ifk-navy) !important;
  color:#fff !important;
}
.ifk-manage .btn-info{
  background:var(--ifk-cyan) !important;
  border-color:var(--ifk-cyan) !important;
  color:#fff !important;
}
.ifk-manage .btn-default{
  background:#fff !important;
  border-color:var(--ifk-border) !important;
  color:var(--ifk-navy) !important;
}

/* Stats cards */
.ifk-manage .im-stats{
  display:grid;
  grid-template-columns:repeat(4,minmax(0,1fr));
  gap:12px;
  margin:12px 0 16px;
}
@media (max-width:1200px){.ifk-manage .im-stats{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media (max-width:520px){.ifk-manage .im-stats{grid-template-columns:1fr;}}
.ifk-manage .im-stat{
  background:#fff;
  border:1px solid var(--ifk-border);
  border-radius:16px;
  padding:14px;
  box-shadow:var(--ifk-shadow-2);
}
.ifk-manage .im-stat .k{font-size:12px;color:var(--ifk-muted);font-weight:900}
.ifk-manage .im-stat .v{font-size:22px;color:var(--ifk-navy);font-weight:950;margin-top:6px}

/* =====================================================
   TABLE: bo góc row chuẩn (không vỡ)
   ===================================================== */
.ifk-manage table.dataTable{width:100% !important}
.ifk-manage #tbl_manage{
  table-layout:fixed;
  border-collapse:separate !important;   /* ✅ cần cái này mới bo góc row đẹp */
  border-spacing:0 10px !important;      /* khoảng cách giữa row */
}
.ifk-manage #tbl_manage thead th{
  border:none !important;
  background:var(--ifk-soft) !important;
  font-size:12px;
  text-transform:uppercase;
  letter-spacing:.03em;
  color:var(--ifk-navy) !important;
  white-space:nowrap;
  font-weight:900;
  padding:12px 12px !important;
}
.ifk-manage #tbl_manage tbody td{
  border:none !important;
  background:#fff !important;
  padding:14px 12px !important;
  vertical-align:middle !important;
  word-break:break-word;
}
.ifk-manage #tbl_manage tbody tr{
  box-shadow:var(--ifk-shadow-2);
  transition:transform .12s ease, box-shadow .12s ease;
}
.ifk-manage #tbl_manage tbody tr:hover{
  box-shadow:0 14px 28px rgba(0,50,90,.14);
  transform:translateY(-1px);
}

/* bo góc cho row bằng td first/last */
.ifk-manage #tbl_manage tbody td:first-child{
  border-top-left-radius:14px;
  border-bottom-left-radius:14px;
}
.ifk-manage #tbl_manage tbody td:last-child{
  border-top-right-radius:14px;
  border-bottom-right-radius:14px;
}

/* widths (giữ như bạn đặt) */
.ifk-manage #tbl_manage th:nth-child(1){width:64px}
.ifk-manage #tbl_manage th:nth-child(2){width:280px}
.ifk-manage #tbl_manage th:nth-child(3){width:120px}
.ifk-manage #tbl_manage th:nth-child(4){width:120px}
.ifk-manage #tbl_manage th:nth-child(5){width:180px}
.ifk-manage #tbl_manage th:nth-child(6){width:220px}
.ifk-manage #tbl_manage th:nth-child(7){width:140px}
.ifk-manage #tbl_manage th:nth-child(8){width:140px}
.ifk-manage #tbl_manage th:nth-child(9){width:140px}
.ifk-manage #tbl_manage th:nth-child(10){width:170px}

/* applicant cell */
.ifk-manage .im-app{display:flex;align-items:center;gap:10px}
.ifk-manage .im-app img{
  width:40px;height:40px;
  border-radius:12px;
  object-fit:cover;
  background:#f1f5f9;
  border:1px solid var(--ifk-border);
}
.ifk-manage .im-app .n{font-weight:950;color:var(--ifk-text);line-height:1.2}
.ifk-manage .im-app .s{font-size:12px;color:var(--ifk-muted);margin-top:3px}

/* badges */
.ifk-manage .im-badge{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:6px 10px;
  border-radius:999px;
  font-size:12px;
  font-weight:900;
  border:1px solid var(--ifk-border);
  color:var(--ifk-navy);
  background:var(--ifk-soft);
}
.ifk-manage .im-badge-info{border-color:rgba(0,166,220,.25);background:rgba(0,166,220,.10);color:var(--ifk-navy)}
/*.ifk-manage .im-badge-warn{border-color:rgba(150,188,23,.35);background:rgba(150,188,23,.12);color:#1f4d00}*/
.ifk-manage .im-badge-warn{border-color:rgba(245,158,11,.45);background:rgba(245,158,11,.18);color:#7c2d12}
.ifk-manage .im-badge-success{border-color:rgba(150,188,23,.45);background:rgba(150,188,23,.18);color:#1f4d00}
.ifk-manage .im-badge-danger{border-color:rgba(239,68,68,.25);background:rgba(239,68,68,.10);color:#7f1d1d}

/* actions */
.ifk-manage .im-actions{display:flex;justify-content:center;align-items:center;gap:8px}
.ifk-manage .im-btn{
  width:34px;height:34px;
  border-radius:999px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  border:1px solid transparent;
  box-shadow:0 1px 3px rgba(0,0,0,.10);
  text-decoration:none;
}
.ifk-manage .im-btn i{font-size:14px;color:#fff}
.ifk-manage .im-btn.view{background:var(--ifk-navy)}
.ifk-manage .im-btn.mail{background:var(--ifk-cyan)}
.ifk-manage .im-btn:hover{filter:brightness(1.05)}

/* ✅ chống ảnh bung full page nếu lỡ có img trong table */
.ifk-manage table img,
.ifk-manage .table img,
.ifk-manage .dataTable img{
  max-width:64px !important;
  max-height:64px !important;
  width:auto !important;
  height:auto !important;
  object-fit:cover;
}
/* ===== IFK KPI (more colorful) ===== */
.ifk-manage .im-stat{
  position:relative;
  overflow:hidden;
  border:1px solid rgba(0,50,90,.12) !important;
  box-shadow:0 10px 22px rgba(0,50,90,.08) !important;
}

/* dải màu trên đầu card */
.ifk-manage .im-stat:before{
  content:"";
  position:absolute;
  left:0; right:0; top:0;
  height:6px;
  background: linear-gradient(90deg, var(--ifk-navy), var(--ifk-cyan), var(--ifk-green));
}

/* nền nhẹ + icon mờ bên phải */
.ifk-manage .im-stat:after{
  content:"";
  position:absolute;
  right:-18px; top:-18px;
  width:88px; height:88px;
  border-radius:999px;
  background: rgba(0,166,220,.10);
}

/* text */
.ifk-manage .im-stat .k{
  color: rgba(0,50,90,.75) !important;
  font-weight:900 !important;
}
.ifk-manage .im-stat .v{
  color: var(--ifk-navy) !important;
  font-weight:950 !important;
}

/* màu từng ô theo thứ tự 1-4 */
.ifk-manage .im-stats .im-stat:nth-child(1){
  background: linear-gradient(180deg, rgba(0,166,220,.08), #fff 60%);
}
.ifk-manage .im-stats .im-stat:nth-child(2){
  background: linear-gradient(180deg, rgba(150,188,23,.10), #fff 60%);
}
.ifk-manage .im-stats .im-stat:nth-child(3){
  background: linear-gradient(180deg, rgba(0,50,90,.06), #fff 60%);
}
.ifk-manage .im-stats .im-stat:nth-child(4){
  background: linear-gradient(180deg, rgba(0,166,220,.06), rgba(150,188,23,.06) 55%, #fff 80%);
}

/* số nổi bật hơn */
.ifk-manage .im-stats .im-stat:nth-child(1) .v{ color: var(--ifk-cyan) !important; }
.ifk-manage .im-stats .im-stat:nth-child(2) .v{ color: #1f4d00 !important; }
.ifk-manage .im-stats .im-stat:nth-child(3) .v{ color: var(--ifk-navy) !important; }
.ifk-manage .im-stats .im-stat:nth-child(4) .v{ color: var(--ifk-navy) !important; }
</style>

<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">

        <div class="im-header">
          <div>
            <h4><i class="fa-solid fa-user-check"></i> <?= html_escape($title ?? 'Internship Management'); ?></h4>
            <div class="im-subtitle">Lọc nâng cao • theo tiến độ / đơn tuyển / trường / phụ trách • theo dõi nhập cảnh & về nước</div>
          </div>

          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="<?= $mail_url; ?>" class="btn btn-primary"><i class="fa-solid fa-envelope"></i> Gửi Email</a>
          </div>
        </div>

        <!-- FILTER BAR -->
        <form method="get" class="im-filterbar">

          <div class="im-field">
            <div class="im-label">Năm</div>
            <select name="year" class="selectpicker" data-width="100%">
              <?php foreach(($years ?? [date('Y')]) as $y){ $y=(int)$y; ?>
                <option value="<?= $y; ?>" <?= ((int)($year ?? date('Y'))===$y?'selected':''); ?>>Năm <?= $y; ?></option>
              <?php } ?>
            </select>
          </div>

          <div class="im-field big">
            <div class="im-label">Tìm kiếm</div>
            <input type="text" name="keyword" class="form-control" value="<?= html_escape($filters['keyword'] ?? ''); ?>"
                   placeholder="Tên / SĐT SV / SĐT PH / Email / Trường / Đơn tuyển">
          </div>

          <div class="im-field">
            <div class="im-label">Tiến độ</div>
            <select name="filter_status" class="selectpicker" data-width="100%">
              <option value="">Tất cả</option>
              <?php foreach(($status_map ?? []) as $k=>$lbl){ ?>
                <option value="<?= html_escape($k); ?>" <?= (($filters['filter_status'] ?? '')===$k?'selected':''); ?>>
                  <?= html_escape($lbl); ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <div class="im-field">
            <div class="im-label">Đơn tuyển</div>
            <select name="filter_company" class="selectpicker" data-live-search="true" data-width="100%">
              <option value="">Tất cả</option>
              <?php foreach(($opts['companies'] ?? []) as $c){ ?>
                <option value="<?= html_escape($c); ?>" <?= (($filters['filter_company'] ?? '')===$c?'selected':''); ?>>
                  <?= html_escape($c); ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <div class="im-field">
            <div class="im-label">Trường</div>
            <select name="filter_school" class="selectpicker" data-live-search="true" data-width="100%">
              <option value="">Tất cả</option>
              <?php foreach(($opts['universities'] ?? []) as $u){ ?>
                <option value="<?= html_escape($u); ?>" <?= (($filters['filter_school'] ?? '')===$u?'selected':''); ?>>
                  <?= html_escape($u); ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <div class="im-field">
            <div class="im-label">Phụ trách</div>
            <select name="filter_staff_id" class="selectpicker" data-live-search="true" data-width="100%">
              <option value="">Tất cả</option>
              <?php foreach(($opts['staffs'] ?? []) as $s){ ?>
                <option value="<?= (int)$s['id']; ?>" <?= ((int)($filters['filter_staff_id'] ?? 0)===(int)$s['id']?'selected':''); ?>>
                  <?= html_escape($s['name']); ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <div class="im-field" style="min-width:120px">
            <button class="btn btn-info btn-block"><i class="fa-solid fa-filter"></i> Lọc</button>
          </div>

          <div class="im-field" style="min-width:120px">
            <a class="btn btn-default btn-block" href="<?= admin_url('internship_management/manage'); ?>"><i class="fa-solid fa-rotate"></i> Reset</a>
          </div>

        </form>

        <!-- STATS -->
        <div class="im-stats">
          <div class="im-stat">
            <div class="k">Tổng SV</div>
            <div class="v"><?= (int)($counters['total'] ?? 0); ?></div>
          </div>
          <div class="im-stat">
            <div class="k"><?= html_escape($status_map['processing'] ?? 'Đang làm hồ sơ'); ?></div>
            <div class="v"><?= (int)($counters['processing'] ?? 0); ?></div>
          </div>
          <div class="im-stat">
            <div class="k"><?= html_escape($status_map['in_japan'] ?? 'Đang ở Nhật'); ?></div>
            <div class="v"><?= (int)($counters['in_japan'] ?? 0); ?></div>
          </div>
          <div class="im-stat">
            <div class="k"><?= html_escape($status_map['returned'] ?? 'Đã về nước'); ?></div>
            <div class="v"><?= (int)($counters['returned'] ?? 0); ?></div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table dt-table table-hover" id="tbl_manage">
            <thead>
              <tr>
                <th>ID</th>
                <th>Họ và tên</th>
                <th>SĐT SV</th>
                <th>SĐT PH</th>
                <th>Trường</th>
                <th>Đơn tuyển</th>
                <th>Ngày nhập cảnh</th>
                <th>Ngày về nước</th>
                <th>Tiến độ</th>
                <th class="text-center">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($rows)): foreach($rows as $r):
                $app_id    = (int)($r['application_id'] ?? 0);
                $student_id= (int)($r['student_id'] ?? 0);
                $contact_id= (int)($r['contact_id'] ?? 0);

                $name   = (string)($r['full_name'] ?? '—');
                $email  = (string)($r['email'] ?? '');
                $phone  = (string)($r['phone'] ?? '');
                $pphone = (string)($r['parent_phone'] ?? '');
                $school = (string)($r['university'] ?? '');
                $company= (string)($r['company_name'] ?? '');
                $entry  = $r['entry_date'] ?? '';
                $ret    = $r['return_date'] ?? '';

                $ms = (string)($r['manage_status'] ?? 'processing');
                $msLbl = $status_map[$ms] ?? $ms;

                $bType = 'info';
                if ($ms === 'processing') $bType = 'warn';
                if ($ms === 'in_japan')   $bType = 'success';
                if ($ms === 'returned')   $bType = 'danger';
                if ($ms === 'cancelled')  $bType = 'danger';

                $url_view = $student_id > 0
                  ? admin_url('internship_management/student_client/view/'.$student_id)
                  : admin_url('internship_management/internship_applications/view/'.$app_id);

                $url_care = $care_url ? ($care_url.(strpos($care_url,'?')===false?'?':'&').'app_id='.$app_id) : '#';
              ?>
                <tr>
                  <td class="text-muted"><?= $app_id; ?></td>
                  <td>
                    <div class="im-app">
                      <img src="<?= im_avatar_url($r['avatar'] ?? '', $student_id, $contact_id); ?>" alt="avatar">
                      <div>
                        <div class="n"><a href="javascript:void(0)" class="quick-view-btn" data-id="<?= $app_id; ?>" style="text-decoration:none;color:#0f172a;"><?= html_escape($name); ?></a></div>
                        <div class="s">
                          <?= $email ? html_escape($email) : ''; ?>
                          <?= $phone ? ' • '.html_escape($phone) : ''; ?>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td><?= $phone ? html_escape($phone) : '—'; ?></td>
                  <td><?= $pphone ? html_escape($pphone) : '—'; ?></td>
                  <td><?= $school ? html_escape($school) : '—'; ?></td>
                  <td><?= $company ? html_escape($company) : '—'; ?></td>
                  <td><?= im_fmt_date($entry); ?></td>
                  <td><?= im_fmt_date($ret); ?></td>
                  <td><?= im_badge($msLbl, $bType); ?></td>
                  <td class="text-center">
                    <div class="im-actions">
                      <a class="im-btn view quick-view-btn" href="javascript:void(0)" data-id="<?= $app_id; ?>" title="Xem nhanh"><i class="fa fa-eye"></i></a>
                      <a class="im-btn mail" href="<?= $url_care; ?>" title="Gửi mail"><i class="fa fa-envelope"></i></a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="10" class="text-center text-muted" style="padding:36px 0;">Chưa có dữ liệu.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document" style="max-width:920px">
    <div class="modal-content"></div>
  </div>
</div>


  </div>
</div>
<?php init_tail(); ?>
<script>
  $(function(){
    // keep Perfex styles for selectpicker
    if ($.fn.selectpicker) $('.selectpicker').selectpicker('refresh');

    // DataTable (client-side) for sorting/paging UI
    if ($.fn.DataTable && !$.fn.dataTable.isDataTable('#tbl_manage')) {
      $('#tbl_manage').DataTable({
        pageLength: 25,
        searching: false,
        lengthChange: true,
        order: [[0,'desc']]
      });
    }
  });
</script>


<script>
  (function(){
    var ADMIN_BASE = (typeof window.admin_url !== 'undefined' && window.admin_url) ? window.admin_url : '<?= admin_url(); ?>';

    $(document).on('click', '.quick-view-btn', function(e){
      e.preventDefault();
      var id = $(this).data('id');
      if(!id) return;

      if (!$('#quickViewModal').length) {
        $('body').append(`<!-- Quick View Modal --><div class="modal fade" id="quickViewModal" tabindex="-1" role="dialog" aria-hidden="true">  <div class="modal-dialog modal-lg" role="document" style="max-width:920px">    <div class="modal-content"></div>  </div></div>`);
      }

      $('#quickViewModal .modal-content').html('<div class="p-5 text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
      $('#quickViewModal').modal('show');

      $.get(ADMIN_BASE + 'internship_management/quick_view/' + id, function(res){
        $('#quickViewModal .modal-content').html(res);
      }).fail(function(xhr){
        $('#quickViewModal .modal-content').html('<div class="p-4"><div class="alert alert-danger">Không tải được xem nhanh. ('+xhr.status+')</div></div>');
      });
    });
  })();
</script>

