<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/*function imq_translate_status($status)
{
    $status = strtolower(trim((string)$status));
    $status = str_replace([' ', '-'], '_', $status);

    $map = [
        // đúng theo dropdown anh chụp
        'apply'                 => 'Ứng tuyển',
        'applying'              => 'Ứng tuyển',
        'applied' => 'Ứng tuyển',

        'interview'             => 'Hẹn phỏng vấn',
        'interviewing'          => 'Hẹn phỏng vấn',
        'schedule_interview'    => 'Hẹn phỏng vấn',

        'docs_preparing'        => 'Chuẩn bị hồ sơ',
        'preparing_docs'        => 'Chuẩn bị hồ sơ',

        'docs_completed'        => 'Hoàn thành hồ sơ',
        'completed_docs'        => 'Hoàn thành hồ sơ',

        'waiting_coe'           => 'Đợi COE',
        'wait_coe'              => 'Đợi COE',

        'has_coe'               => 'Đã có COE',
        'coe_received'          => 'Đã có COE',

        'visa'                  => 'Làm visa',
        'making_visa'           => 'Làm visa',
        'visa_processing'       => 'Làm visa',

        'buy_ticket'            => 'Mua vé nhập cảnh',
        'buying_ticket'         => 'Mua vé nhập cảnh',
        'entry_ticket'          => 'Mua vé nhập cảnh',

        'prepare_flight'         => 'Chuẩn bị bay',
        'preparing_flight'       => 'Chuẩn bị bay',
        'flight_preparing'       => 'Chuẩn bị bay',

        'in_japan'              => 'Đang ở Nhật',
        'working_in_japan'      => 'Đang ở Nhật',

        'returned'              => 'Đã về nước',
        'back_home'             => 'Đã về nước',

        'cancel'                => 'Huỷ',
        'cancelled'             => 'Huỷ',

        'not_updated'           => 'Chưa cập nhật',
        'unupdated'             => 'Chưa cập nhật',
        'none'                  => 'Chưa cập nhật',
        ''                      => 'Chưa cập nhật',
    ];

    // nếu status DB đã là tiếng Việt thì trả luôn
    $viList = ['ứng tuyển','hẹn phỏng vấn','chuẩn bị hồ sơ','hoàn thành hồ sơ','đợi coe','đã có coe','làm visa','mua vé nhập cảnh','chuẩn bị bay','đang ở nhật','đã về nước','huỷ','chưa cập nhật'];
    if (in_array(mb_strtolower($status), $viList, true)) {
        return mb_convert_case($status, MB_CASE_TITLE, "UTF-8");
    }

    if (isset($map[$status])) return $map[$status];

    // fallback đẹp
    return ucwords(str_replace('_', ' ', $status));
}*/
$CI = &get_instance();
$CI->load->helper('internship_management/internship_status');

function imq_translate_status($status)
{
    if (function_exists('im_status_label_vi')) {
        return im_status_label_vi($status);
    }

    $status = strtolower(trim((string)$status));
    return $status === '' ? 'Chưa cập nhật' : ucwords(str_replace('_', ' ', $status));
}
/**
 * Internship Application - Quick View (Modal) - PRO UI
 * - Responsive / đẹp / rõ ràng
 * - Tabs: Tổng quan / Liên hệ / Hồ sơ / CRM
 * - Copy nhanh: Email / SĐT
 * - Badge trạng thái
 */

$app_id = (int)($app['id'] ?? 0);
$sid    = $app_id; // theo hệ thống anh đang dùng

// ===== URLS =====
$url_profile  = admin_url('internship_management/student_client/view/'.$sid);
$url_edit     = admin_url('internship_management/internship_applications/edit/'.$app_id);
$url_print    = admin_url('internship_management/internship_applications/print/'.$app_id);
$url_delete   = admin_url('internship_management/internship_applications/delete/'.$app_id);
$url_reapply  = admin_url('internship_management/internship_applications/create?clone='.$app_id);
$url_push_crm = admin_url('internship_management/internship_applications/push_crm/'.$app_id);
$url_cv       = !empty($app['cv_file'])
    ? admin_url('internship_management/internship_applications/preview_file/'.$app_id)
    : '';

$avatar = !empty($app['avatar'])
    ? base_url('uploads/internship_avatar/'.$app['avatar'])
    : base_url('modules/internship_management/assets/no-image.png');

$full_name      = trim((string)($app['full_name'] ?? '—'));
$school_name    = trim((string)($app['school_name'] ?? '—'));
$major          = trim((string)($app['major'] ?? '—'));
$email          = trim((string)($app['email'] ?? ''));
$phone_student  = trim((string)($app['phone_student'] ?? ''));
$phone_parent   = trim((string)($app['phone_parent'] ?? ''));
$address        = trim((string)($app['address'] ?? '—'));
$jlpt           = trim((string)($app['japanese_level'] ?? '—'));
$english        = trim((string)($app['english_level'] ?? '—'));
$status_raw  = trim((string)($app['status'] ?? ''));
$status_text = imq_translate_status($status_raw);

// Badge màu theo trạng thái (anh có thể map lại theo status code hệ thống)
function imq_status_class($s)
{
    $s = mb_strtolower(trim((string)$s));
    if ($s === '' || $s === '—') return 'imq-badge imq-badge-muted';
    if (strpos($s, 'đậu') !== false || strpos($s, 'pass') !== false || strpos($s, 'accepted') !== false) return 'imq-badge imq-badge-success';
    if (strpos($s, 'rớt') !== false || strpos($s, 'fail') !== false || strpos($s, 'rejected') !== false) return 'imq-badge imq-badge-danger';
    if (strpos($s, 'phỏng vấn') !== false || strpos($s, 'interview') !== false) return 'imq-badge imq-badge-warning';
    if (strpos($s, 'chờ') !== false || strpos($s, 'pending') !== false) return 'imq-badge imq-badge-info';
    return 'imq-badge imq-badge-primary';
}

?>
<style>

/* =====================================================
   IFK QUICK PROFILE – Scoped .imq
   Brand: #00325a / #00a6dc / #96bc17
   ===================================================== */

.imq{
  --ifk-navy:#00325a;
  --ifk-cyan:#00a6dc;
  --ifk-green:#96bc17;

  --ink:#0f172a;
  --muted:#64748b;
  --bd:rgba(0,50,90,.15);
  --bg:rgba(0,166,220,.05);
}

.imq *{box-sizing:border-box}
.imq a{outline:0}

/* ===== Shell ===== */

.imq .imq-shell{
  background:#fff;
  border:1px solid var(--bd);
  border-radius:16px;
  overflow:hidden;
}

/* ===== Header ===== */

.imq .imq-head{
  padding:16px 18px;
  background:linear-gradient(
      135deg,
      rgba(0,50,90,.08),
      rgba(0,166,220,.08)
  );
  border-bottom:1px solid var(--bd);
}

.imq .imq-head-row{
  display:flex;
  gap:16px;
  align-items:center;
}

.imq .imq-avatar{
  width:86px;
  height:86px;
  border-radius:16px;
  object-fit:cover;
  border:1px solid var(--bd);
  background:#fff;
}

.imq .imq-title{
  margin:0;
  font-size:20px;
  font-weight:800;
  color:var(--ifk-navy);
  line-height:1.2;
}

.imq .imq-sub{
  margin-top:6px;
  color:var(--muted);
  font-weight:600;
}

.imq .imq-badges{
  margin-top:10px;
  display:flex;
  flex-wrap:wrap;
  gap:8px;
}

/* ===== Badges ===== */

.imq .imq-badge{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:5px 10px;
  border-radius:999px;
  font-size:12px;
  font-weight:800;
  border:1px solid var(--bd);
  background:#fff;
  color:#334155;
}

.imq .imq-badge-primary{
  border-color:rgba(0,50,90,.25);
  background:rgba(0,50,90,.08);
  color:var(--ifk-navy);
}

.imq .imq-badge-success{
  border-color:rgba(150,188,23,.30);
  background:rgba(150,188,23,.12);
  color:#2f5e00;
}

.imq .imq-badge-warning{
  border-color:rgba(245,158,11,.30);
  background:rgba(245,158,11,.12);
  color:#92400e;
}

.imq .imq-badge-danger{
  border-color:rgba(239,68,68,.30);
  background:rgba(239,68,68,.10);
  color:#7f1d1d;
}

.imq .imq-badge-info{
  border-color:rgba(0,166,220,.30);
  background:rgba(0,166,220,.12);
  color:var(--ifk-cyan);
}

.imq .imq-badge-muted{
  border-color:rgba(100,116,139,.25);
  background:rgba(100,116,139,.10);
  color:#334155;
}

/* ===== Actions ===== */

.imq .imq-actions{
  padding:14px 18px;
  border-bottom:1px solid var(--bd);
  background:var(--bg);
}

.imq .imq-actions .btn{
  border-radius:12px;
  font-weight:800;
  padding:8px 14px;
}

.imq .imq-actions .btn i{
  margin-right:6px;
}

.imq .imq-actions-row{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  align-items:center;
  justify-content:space-between;
}

.imq .imq-actions-left,
.imq .imq-actions-right{
  display:flex;
  flex-wrap:wrap;
  gap:10px;
}

/* ===== Tabs ===== */

.imq .nav-tabs{
  padding:0 18px;
  margin:0;
  background:#fff;
  border-bottom:1px solid var(--bd);
}

.imq .nav-tabs>li>a{
  border-radius:12px 12px 0 0;
  font-weight:800;
  color:var(--muted);
}

.imq .nav-tabs>li.active>a{
  background:rgba(0,166,220,.08);
  color:var(--ifk-navy);
  border-color:var(--bd) var(--bd) transparent;
}

.imq .tab-content{
  padding:16px 18px;
}

/* ===== Grid ===== */

.imq .imq-grid{
  display:grid;
  grid-template-columns:200px 1fr;
  gap:10px 14px;
}

@media (max-width:768px){
  .imq .imq-grid{grid-template-columns:1fr}
}

/* ===== Field ===== */

.imq .imq-field{
  padding:10px 12px;
  border:1px solid var(--bd);
  border-radius:14px;
  background:#fff;
}

.imq .imq-label{
  font-size:12px;
  font-weight:900;
  color:var(--ifk-navy);
  text-transform:uppercase;
  letter-spacing:.04em;
  margin-bottom:6px;
}

.imq .imq-val{
  font-size:14px;
  font-weight:700;
  color:var(--ink);
  word-break:break-word;
}

.imq .imq-val.muted{
  color:var(--muted);
}

/* ===== Footer ===== */

.imq .imq-foot{
  padding:12px 18px;
  border-top:1px solid var(--bd);
  display:flex;
  justify-content:space-between;
  gap:10px;
  color:var(--muted);
  font-size:12px;
  background:#fff;
}

.imq .imq-foot code{
  font-weight:800;
  color:var(--ifk-navy);
  background:rgba(0,50,90,.06);
  padding:2px 6px;
  border-radius:8px;
}

/* ===== Toast ===== */

.imq .imq-toast{
  position:fixed;
  right:18px;
  bottom:18px;
  z-index:9999;
  display:none;
}

.imq .imq-toast .alert{
  border-radius:14px;
  font-weight:800;
  box-shadow:0 10px 28px rgba(2,6,23,.15);
}

</style>

<div class="imq">
  <div class="imq-shell">

    <!-- HEADER -->
    <div class="imq-head">
      <div class="imq-head-row">
        <img class="imq-avatar" src="<?= html_escape($avatar); ?>" alt="avatar">
        <div style="min-width:0;flex:1">
          <h3 class="imq-title"><?= html_escape($full_name ?: '—'); ?></h3>
          <div class="imq-sub">
            <?= html_escape($school_name ?: '—'); ?>
            <span style="opacity:.6">•</span>
            <?= html_escape($major ?: '—'); ?>
          </div>

          <div class="imq-badges">
          <span class="<?= imq_status_class($status_raw); ?>"><i class="fa fa-flag"></i> <?= html_escape($status_text ?: '—'); ?></span>
            <span class="imq-badge imq-badge-info"><i class="fa fa-language"></i> JLPT: <?= html_escape($jlpt ?: '—'); ?></span>
            <span class="imq-badge imq-badge-muted"><i class="fa fa-globe"></i> EN: <?= html_escape($english ?: '—'); ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- ACTIONS -->
    <div class="imq-actions">
      <div class="imq-actions-row">
        <div class="imq-actions-left">
          <a href="<?= html_escape($url_profile); ?>" target="_blank" class="btn btn-default">
            <i class="fa fa-user"></i> Profile
          </a>
          <a href="<?= html_escape($url_edit); ?>" class="btn btn-primary">
            <i class="fa fa-pencil"></i> Sửa
          </a>
          <a href="<?= html_escape($url_print); ?>" target="_blank" class="btn btn-warning">
            <i class="fa fa-print"></i> In
          </a>
          <?php if (!empty($url_cv)): ?>
            <a href="<?= html_escape($url_cv); ?>" target="_blank" class="btn btn-info">
              <i class="fa fa-file-text-o"></i> CV
            </a>
          <?php endif; ?>
        </div>

        <div class="imq-actions-right">
          <a href="<?= html_escape($url_reapply); ?>" class="btn btn-success">
            <i class="fa fa-refresh"></i> Ứng tuyển thêm
          </a>
          <a href="<?= html_escape($url_delete); ?>" class="btn btn-danger _delete">
            <i class="fa fa-trash"></i> Xoá
          </a>
        </div>
      </div>
    </div>

    <!-- BODY / TABS -->
    <div class="imq-body">
      <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#imq_tab_overview" aria-controls="imq_tab_overview" role="tab" data-toggle="tab"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
        <li role="presentation"><a href="#imq_tab_contact" aria-controls="imq_tab_contact" role="tab" data-toggle="tab"><i class="fa fa-address-card"></i> Liên hệ</a></li>
        <li role="presentation"><a href="#imq_tab_docs" aria-controls="imq_tab_docs" role="tab" data-toggle="tab"><i class="fa fa-folder-open"></i> Hồ sơ</a></li>
        <li role="presentation"><a href="#imq_tab_crm" aria-controls="imq_tab_crm" role="tab" data-toggle="tab"><i class="fa fa-external-link"></i> CRM</a></li>
      </ul>

      <div class="tab-content">

        <!-- OVERVIEW -->
        <div role="tabpanel" class="tab-pane active" id="imq_tab_overview">
          <div class="imq-grid">

            <div class="imq-field">
              <div class="imq-label">Ứng viên</div>
              <div class="imq-val"><?= html_escape($full_name ?: '—'); ?></div>
              <div class="imq-val muted" style="margin-top:6px">
                <i class="fa fa-university"></i> <?= html_escape($school_name ?: '—'); ?>
              </div>
            </div>

            <div class="imq-field">
              <div class="imq-label">Chuyên ngành</div>
              <div class="imq-val"><?= html_escape($major ?: '—'); ?></div>
              <div class="imq-val muted" style="margin-top:6px">
                <i class="fa fa-map-marker"></i> <?= html_escape($address ?: '—'); ?>
              </div>
            </div>

          </div>
        </div>

        <!-- CONTACT -->
        <div role="tabpanel" class="tab-pane" id="imq_tab_contact">
          <div class="imq-grid">

            <div class="imq-field">
              <div class="imq-label">Email</div>
              <div class="imq-val"><?= html_escape($email ?: '—'); ?></div>
              <div class="imq-mini-actions">
                <?php if (!empty($email)): ?>
                  <a class="btn btn-default btn-xs" href="mailto:<?= html_escape($email); ?>"><i class="fa fa-envelope"></i> Gửi mail</a>
                  <button type="button" class="btn btn-default btn-xs" onclick="IMQ.copy('<?= html_escape($email); ?>')"><i class="fa fa-copy"></i> Copy</button>
                <?php endif; ?>
              </div>
            </div>

            <div class="imq-field">
              <div class="imq-label">SĐT sinh viên</div>
              <div class="imq-val"><?= html_escape($phone_student ?: '—'); ?></div>
              <div class="imq-mini-actions">
                <?php if (!empty($phone_student)): ?>
                  <a class="btn btn-default btn-xs" href="tel:<?= html_escape($phone_student); ?>"><i class="fa fa-phone"></i> Gọi</a>
                  <button type="button" class="btn btn-default btn-xs" onclick="IMQ.copy('<?= html_escape($phone_student); ?>')"><i class="fa fa-copy"></i> Copy</button>
                <?php endif; ?>
              </div>
            </div>

            <div class="imq-field">
              <div class="imq-label">SĐT phụ huynh</div>
              <div class="imq-val"><?= html_escape($phone_parent ?: '—'); ?></div>
              <div class="imq-mini-actions">
                <?php if (!empty($phone_parent)): ?>
                  <a class="btn btn-default btn-xs" href="tel:<?= html_escape($phone_parent); ?>"><i class="fa fa-phone"></i> Gọi</a>
                  <button type="button" class="btn btn-default btn-xs" onclick="IMQ.copy('<?= html_escape($phone_parent); ?>')"><i class="fa fa-copy"></i> Copy</button>
                <?php endif; ?>
              </div>
            </div>

            <div class="imq-field">
              <div class="imq-label">Địa chỉ</div>
              <div class="imq-val"><?= html_escape($address ?: '—'); ?></div>
            </div>

          </div>
        </div>

        <!-- DOCS -->
        <div role="tabpanel" class="tab-pane" id="imq_tab_docs">
          <div class="imq-grid">

            <div class="imq-field">
              <div class="imq-label">CV</div>
              <?php if (!empty($url_cv)): ?>
                <div class="imq-val"><i class="fa fa-check-circle" style="color:#16a34a"></i> Có file CV</div>
                <div class="imq-mini-actions">
                  <a class="btn btn-info btn-xs" href="<?= html_escape($url_cv); ?>" target="_blank"><i class="fa fa-eye"></i> Xem CV</a>
                </div>
              <?php else: ?>
                <div class="imq-val muted"><i class="fa fa-info-circle"></i> Chưa có file CV</div>
              <?php endif; ?>
            </div>

            <div class="imq-field">
              <div class="imq-label">Thao tác nhanh</div>
              <div class="imq-mini-actions">
                <a href="<?= html_escape($url_profile); ?>" target="_blank" class="btn btn-default btn-xs"><i class="fa fa-user"></i> Mở Profile</a>
                <a href="<?= html_escape($url_edit); ?>" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i> Sửa hồ sơ</a>
                <a href="<?= html_escape($url_print); ?>" target="_blank" class="btn btn-warning btn-xs"><i class="fa fa-print"></i> In</a>
              </div>
            </div>

          </div>
        </div>

        <!-- CRM -->
        <div role="tabpanel" class="tab-pane" id="imq_tab_crm">
          <div class="imq-grid">

            <div class="imq-field">
              <div class="imq-label">Trạng thái liên kết</div>

              <?php if (!empty($crm_link['linked'])): ?>
                <div class="imq-val">
                  <span class="imq-badge imq-badge-success"><i class="fa fa-link"></i> Đã liên kết</span>
                  <span class="imq-badge imq-badge-muted">#<?= (int)($crm_link['crm_id'] ?? 0); ?></span>
                </div>

                <?php if (!empty($crm_link['crm_url'])): ?>
                  <div class="imq-mini-actions" style="margin-top:10px">
                    <a class="btn btn-default btn-xs" target="_blank" href="<?= html_escape($crm_link['crm_url']); ?>">
                      <i class="fa fa-external-link"></i> Mở CRM
                    </a>
                    <button type="button" class="btn btn-default btn-xs" onclick="IMQ.copy('<?= html_escape($crm_link['crm_url']); ?>')">
                      <i class="fa fa-copy"></i> Copy link
                    </button>
                  </div>
                <?php endif; ?>

              <?php else: ?>
                <div class="imq-val">
                  <span class="imq-badge imq-badge-muted"><i class="fa fa-unlink"></i> Chưa liên kết</span>
                </div>
              <?php endif; ?>
            </div>

            <div class="imq-field">
              <div class="imq-label">Đồng bộ</div>
              <div class="imq-val muted">Đẩy dữ liệu ứng viên sang CRM (theo cấu hình module)</div>
              <div class="imq-mini-actions" style="margin-top:10px">
                <a href="<?= html_escape($url_push_crm); ?>" class="btn btn-success btn-xs">
                  <i class="fa fa-cloud-upload"></i> Đẩy CRM
                </a>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- FOOT -->
    <div class="imq-foot">
      <div>Application ID: <code><?= (int)$app_id; ?></code></div>
      <div style="opacity:.9">Quick View • Internship Management</div>
    </div>

  </div>

  <!-- Toast -->
  <div class="imq-toast" id="imq_toast">
    <div class="alert alert-success" style="margin:0">
      <i class="fa fa-check"></i> <span id="imq_toast_text">Đã copy</span>
    </div>
  </div>
</div>

<script>
(function(){
  // namespace
  window.IMQ = window.IMQ || {};

  function toast(msg){
    var box = document.getElementById('imq_toast');
    var txt = document.getElementById('imq_toast_text');
    if(!box || !txt) return;
    txt.textContent = msg || 'Đã copy';
    box.style.display = 'block';
    setTimeout(function(){ box.style.display = 'none'; }, 1400);
  }

  IMQ.copy = function(text){
    try{
      if(!text){ toast('Không có dữ liệu'); return; }
      if(navigator.clipboard && navigator.clipboard.writeText){
        navigator.clipboard.writeText(text).then(function(){ toast('Đã copy'); }).catch(function(){ fallbackCopy(text); });
      }else{
        fallbackCopy(text);
      }
    }catch(e){
      fallbackCopy(text);
    }
  };

  function fallbackCopy(text){
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly','readonly');
    ta.style.position='fixed';
    ta.style.left='-9999px';
    document.body.appendChild(ta);
    ta.select();
    try{ document.execCommand('copy'); toast('Đã copy'); }catch(e){ toast('Copy thất bại'); }
    document.body.removeChild(ta);
  }
})();
</script>