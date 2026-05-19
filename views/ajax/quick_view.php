<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// --------- helpers (local to modal) ----------
if (!function_exists('imq_fmt_date')) {
  function imq_fmt_date($v, $fallback='—') {
    $v = trim((string)$v);
    if ($v === '' || $v === '0000-00-00' || $v === '0000-00-00 00:00:00') return $fallback;
    $t = strtotime($v);
    return $t ? date('Y-m-d', $t) : html_escape($v);
  }
}
if (!function_exists('imq_avatar_url')) {
  function imq_avatar_url($avatar, $student_id = 0, $contact_id = 0) {
    $avatar     = trim((string)$avatar);
    $student_id = (int)$student_id;
    $contact_id = (int)$contact_id;

    // 1) Perfex contact avatar (uploads/client_profile_images/{contact_id}/*)
    if ($contact_id > 0) {
      $dir = rtrim((string)FCPATH, '/').'/uploads/client_profile_images/'.$contact_id.'/';
      if (is_dir($dir)) {
        $files = glob($dir.'*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
        if (!empty($files)) {
          return base_url('uploads/client_profile_images/'.$contact_id.'/'.basename($files[0]));
        }
      }
    }

    // 2) Stored as full URL
    if ($avatar !== '' && preg_match('/^https?:\/\//i', $avatar)) return $avatar;

    // 3) Stored as a relative path
    if ($avatar !== '' && strpos($avatar, '/') !== false) return base_url(ltrim($avatar, '/'));

    // 4) Try common upload folders
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

// --------- data ----------
$row = isset($row) && is_array($row) ? $row : [];
$app_id     = (int)($row['application_id'] ?? 0);
$student_id = (int)($row['student_id'] ?? 0);
$contact_id = (int)($row['contact_id'] ?? 0);

$name    = (string)($row['full_name'] ?? '');
$email   = (string)($row['email'] ?? '');
$phone   = (string)($row['phone'] ?? '');
$pphone  = (string)($row['parent_phone'] ?? '');
$school  = (string)($row['university'] ?? '');
$company = (string)($row['company_name'] ?? '');
$entry   = (string)($row['entry_date'] ?? '');
$ret     = (string)($row['return_date'] ?? '');
$status  = (string)($row['progress_label'] ?? ($row['manage_status'] ?? ''));

$avatar_url = imq_avatar_url($row['avatar'] ?? '', $student_id, $contact_id);

// Mail URLs (prefill)
$mail_base = admin_url('internship_management/internship_mail/send_mail');
$qs = http_build_query([
  'application_id' => $app_id,
  'full_name'      => $name,
  'email'          => $email,
  'phone'          => $phone,
  'parent_phone'   => $pphone,
  'university'     => $school,
  'company_name'   => $company,
]);
$mail_url   = $mail_base.'?'.$qs;
$survey_url = $mail_base.'?'.$qs.'&type=survey';

// Optional: open full profile if exists
$profile_url = $student_id > 0 ? admin_url('internship_management/student_client/view/'.$student_id) : '';
?>

<style>
/* =========================
   IFK Quick View Modal (scoped)
   Palette: #00325a / #96bc17 / #00a6dc / #ffffff
   Font: inherit from CRM
   ========================= */
#quickViewModal{
  --ifk-navy:#00325a;
  --ifk-green:#96bc17;
  --ifk-cyan:#00a6dc;

  --ifk-text:#1e293b;
  --ifk-muted:#64748b;
  --ifk-border:rgba(0,50,90,.15);

  --ifk-soft:rgba(0,166,220,.06);
  --ifk-soft-2:rgba(150,188,23,.10);

  --ifk-shadow:0 22px 60px rgba(0,50,90,.18);
  --ifk-shadow-2:0 8px 22px rgba(0,50,90,.10);
}

/* Modal shell */
#quickViewModal .modal-content{
  border-radius:18px;
  border:1px solid var(--ifk-border);
  overflow:hidden;
  box-shadow:var(--ifk-shadow);
  font-family:inherit;
}

/* Header */
#quickViewModal .imq-head{
  background:
    radial-gradient(900px 220px at 12% 0%, rgba(255,255,255,.14), transparent 60%),
    linear-gradient(135deg, var(--ifk-navy) 0%, #004a7a 45%, var(--ifk-cyan) 100%);
  color:#fff;
  padding:16px 18px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  border-bottom:1px solid rgba(255,255,255,.18);
}

#quickViewModal .imq-title{
  display:flex;
  align-items:center;
  gap:10px;
  font-weight:900;
  font-size:16px;
  letter-spacing:.2px;
}
#quickViewModal .imq-title i{opacity:.95}

/* Close */
#quickViewModal .imq-close{
  background:rgba(255,255,255,.14);
  border:1px solid rgba(255,255,255,.20);
  border-radius:12px;
  width:38px;
  height:38px;
  color:#fff;
  display:inline-flex;
  align-items:center;
  justify-content:center;
}
#quickViewModal .imq-close:hover{
  background:rgba(255,255,255,.22);
}

/* Body */
#quickViewModal .imq-body{
  padding:16px 18px;
  background:#fff;
}

/* Hero */
#quickViewModal .imq-hero{
  display:flex;
  gap:14px;
  align-items:center;
  margin-bottom:14px;
}

#quickViewModal .imq-avatar{
  width:64px;height:64px;
  border-radius:18px;
  object-fit:cover;
  border:1px solid var(--ifk-border);
  box-shadow:0 10px 20px rgba(0,50,90,.12);
  background:#f1f5f9;
}

#quickViewModal .imq-name{
  font-size:18px;
  font-weight:950;
  color:var(--ifk-navy);
  line-height:1.2;
}
#quickViewModal .imq-sub{
  color:var(--ifk-muted);
  margin-top:4px;
  font-size:13px;
  font-weight:600;
}

/* Badges */
#quickViewModal .imq-badges{
  margin-left:auto;
  display:flex;
  gap:8px;
  flex-wrap:wrap;
  justify-content:flex-end;
}

#quickViewModal .imq-pill{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:7px 10px;
  border-radius:999px;
  font-size:12px;
  font-weight:900;
  border:1px solid var(--ifk-border);
  background:var(--ifk-soft);
  color:var(--ifk-navy);
}
#quickViewModal .imq-pill i{opacity:.85;color:var(--ifk-cyan)}

/* Grid */
#quickViewModal .imq-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:12px;
}
@media (max-width: 820px){
  #quickViewModal .imq-grid{grid-template-columns:1fr}
}

/* Cards */
#quickViewModal .imq-card{
  border:1px solid var(--ifk-border);
  border-radius:16px;
  padding:12px 12px;
  background:#fff;
  box-shadow:var(--ifk-shadow-2);
}

#quickViewModal .imq-card h5{
  margin:0 0 10px 0;
  font-size:13px;
  font-weight:950;
  color:var(--ifk-navy);
  letter-spacing:.04em;
  text-transform:uppercase;
}

/* Rows */
#quickViewModal .imq-row{
  display:flex;
  gap:10px;
  align-items:flex-start;
  margin:8px 0;
}

#quickViewModal .imq-ico{
  width:34px;height:34px;
  border-radius:12px;
  display:flex;
  align-items:center;
  justify-content:center;
  background:rgba(0,166,220,.10);
  border:1px solid rgba(0,166,220,.22);
  color:var(--ifk-navy);
  flex:0 0 auto;
}
#quickViewModal .imq-ico i{color:var(--ifk-navy)}

#quickViewModal .imq-k{
  font-size:12px;
  color:var(--ifk-muted);
  font-weight:900;
  margin:0;
}
#quickViewModal .imq-v{
  margin:2px 0 0 0;
  color:var(--ifk-text);
  font-weight:700;
  word-break:break-word;
}

/* Footer */
#quickViewModal .imq-footer{
  padding:12px 18px;
  background:var(--ifk-soft);
  border-top:1px solid var(--ifk-border);
  display:flex;
  gap:10px;
  justify-content:flex-end;
  flex-wrap:wrap;
}

/* Buttons in footer */
#quickViewModal .imq-btn{
  border-radius:12px !important;
  font-weight:900;
  padding:8px 14px;
}

/* make bootstrap buttons match IFK (only inside modal) */
#quickViewModal .btn-default{
  background:#fff !important;
  border-color:var(--ifk-border) !important;
  color:var(--ifk-navy) !important;
}
#quickViewModal .btn-default:hover{
  background:rgba(0,166,220,.06) !important;
}

#quickViewModal .btn-info{
  background:var(--ifk-cyan) !important;
  border-color:var(--ifk-cyan) !important;
  color:#fff !important;
}
#quickViewModal .btn-info:hover{filter:brightness(.95)}

#quickViewModal .btn-success{
  background:var(--ifk-green) !important;
  border-color:var(--ifk-green) !important;
  color:#fff !important;
}
#quickViewModal .btn-success:hover{filter:brightness(.95)}
/* ===== IFK STATUS COLORS ===== */
#quickViewModal .imq-status{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:7px 14px;
  border-radius:999px;
  font-size:12px;
  font-weight:900;
  border:1px solid transparent;
  line-height:1;
}

/* Đang làm hồ sơ */
#quickViewModal .status-processing{
  background:#fef3c7;
  border-color:#fcd34d;
  color:#92400e;
}
#quickViewModal .status-processing i{
  color:#f59e0b;
}

/* Đang Nhật */
#quickViewModal .status-injapan{
  background:#dcfce7;
  border-color:#86efac;
  color:#166534;
}
#quickViewModal .status-injapan i{
  color:#16a34a;
}

/* Đã về nước */
#quickViewModal .status-returned{
  background:#fee2e2;
  border-color:#fca5a5;
  color:#991b1b;
}
#quickViewModal .status-returned i{
  color:#ef4444;
}

/* Mặc định */
#quickViewModal .status-default{
  background:#e0f2fe;
  border-color:#7dd3fc;
  color:#075985;
}
#quickViewModal .status-default i{
  color:#0284c7;
}
</style>

<div class="imq-head">
  <div class="imq-title">
    <i class="fa fa-user"></i>
    <span>Xem nhanh sinh viên</span>
  </div>
  <button type="button" class="imq-close" data-dismiss="modal" aria-label="Close">
    <i class="fa fa-times"></i>
  </button>
</div>

<div class="imq-body">
<?php if (empty($row)) { ?>
  <div class="alert alert-danger mb-0">Không tìm thấy dữ liệu.</div>
<?php } else { ?>

  <div class="imq-hero">
    <img class="imq-avatar" src="<?= html_escape($avatar_url); ?>" alt="avatar">
    <div>
      <div class="imq-name"><?= html_escape($name ?: '—'); ?></div>
      <div class="imq-sub">
        <?= $email ? html_escape($email) : '—'; ?>
        <?= $phone ? ' • '.html_escape($phone) : ''; ?>
      </div>
    </div>

    <div class="imq-badges">
      <?php if ($status) { ?>
      <?php
$raw = strtolower(trim((string)($row['manage_status'] ?? '')));
$status_class = 'status-default';

if ($raw === 'processing') {
  $status_class = 'status-processing';
} elseif ($raw === 'in_japan') {
  $status_class = 'status-injapan';
} elseif ($raw === 'returned') {
  $status_class = 'status-returned';
}
?>

<?php if ($status) { ?>
  <span class="imq-status <?= $status_class; ?>">
    <i class="fa fa-flag"></i>
    <?= html_escape($status); ?>
  </span>
<?php } ?>
      <?php } ?>
      <?php if ($company) { ?>
        <span class="imq-pill"><i class="fa fa-building"></i> <?= html_escape(mb_strimwidth($company, 0, 28, '…','UTF-8')); ?></span>
      <?php } ?>
    </div>
  </div>

  <div class="imq-grid">

    <div class="imq-card">
      <h5>Thông tin liên hệ</h5>

      <div class="imq-row">
        <div class="imq-ico"><i class="fa fa-phone"></i></div>
        <div>
          <p class="imq-k">SĐT sinh viên</p>
          <p class="imq-v"><?= $phone ? html_escape($phone) : '—'; ?></p>
        </div>
      </div>

      <div class="imq-row">
        <div class="imq-ico"><i class="fa fa-phone-square"></i></div>
        <div>
          <p class="imq-k">SĐT phụ huynh</p>
          <p class="imq-v"><?= $pphone ? html_escape($pphone) : '—'; ?></p>
        </div>
      </div>

      <div class="imq-row">
        <div class="imq-ico"><i class="fa fa-graduation-cap"></i></div>
        <div>
          <p class="imq-k">Trường</p>
          <p class="imq-v"><?= $school ? html_escape($school) : '—'; ?></p>
        </div>
      </div>
    </div>

    <div class="imq-card">
      <h5>Nhập cảnh & về nước</h5>

      <div class="imq-row">
        <div class="imq-ico"><i class="fa fa-plane"></i></div>
        <div>
          <p class="imq-k">Ngày nhập cảnh</p>
          <p class="imq-v"><?= html_escape(imq_fmt_date($entry)); ?></p>
        </div>
      </div>

      <div class="imq-row">
        <div class="imq-ico"><i class="fa fa-plane-arrival"></i></div>
        <div>
          <p class="imq-k">Ngày về nước</p>
          <p class="imq-v"><?= html_escape(imq_fmt_date($ret)); ?></p>
        </div>
      </div>

      <div class="imq-row">
        <div class="imq-ico"><i class="fa fa-briefcase"></i></div>
        <div>
          <p class="imq-k">Đơn tuyển</p>
          <p class="imq-v"><?= $company ? html_escape($company) : '—'; ?></p>
        </div>
      </div>

    </div>
  </div>

<?php } ?>
</div>

<div class="imq-footer">
  <?php if (!empty($row)) { ?>
    <?php if ($profile_url) { ?>
      <a class="btn btn-default imq-btn" href="<?= $profile_url; ?>" target="_blank">
        <i class="fa fa-external-link"></i> Hồ sơ
      </a>
    <?php } ?>
    <a class="btn btn-info imq-btn" href="<?= $mail_url; ?>">
      <i class="fa fa-envelope"></i> Gửi mail
    </a>
    <a class="btn btn-success imq-btn" href="<?= $survey_url; ?>">
      <i class="fa fa-list-alt"></i> Gửi khảo sát
    </a>
  <?php } ?>
  <button type="button" class="btn btn-default imq-btn" data-dismiss="modal">Đóng</button>
</div>
