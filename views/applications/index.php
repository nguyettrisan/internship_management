<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
/**
 * Helpers hiển thị song ngữ (ưu tiên VI, kèm JA nếu có)
 */
if (!function_exists('im_bi_text')) {
    function im_bi_text($vi, $ja = '', $fallback = '—')
    {
        $vi = trim((string)$vi);
        $ja = trim((string)$ja);

        if ($vi === '' && $ja === '') {
            return '<span class="text-muted">' . html_escape($fallback) . '</span>';
        }

        $html = '';
        if ($vi !== '') {
            $html .= '<div class="im-primary">' . html_escape($vi) . '</div>';
            if ($ja !== '') {
                $html .= '<div class="im-sub text-muted">' . html_escape($ja) . '</div>';
            }
        } else {
            // không có VI → dùng JA làm dòng chính
            $html .= '<div class="im-primary">' . html_escape($ja) . '</div>';
        }
        return $html;
    }
}

if (!function_exists('im_avatar_url')) {
    function im_avatar_url($avatar)
    {
        return !empty($avatar)
            ? base_url('uploads/internship_avatar/' . $avatar)
            : base_url('modules/internship_management/assets/no-image.png');
    }
}
?>

<style>

/* =====================================================
   IFK APPLICATIONS TABLE - FINAL STABLE
   ===================================================== */

:root{
  --ifk-navy:#00325a;
  --ifk-green:#96bc17;
  --ifk-cyan:#00a6dc;
  --ifk-soft:rgba(0,166,220,.08);
  --ifk-border:rgba(0,50,90,.15);
  --ifk-text:#1e293b;
  --ifk-muted:#64748b;
}

/* ===== HEADER ===== */

.im-header{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:12px;
  margin-bottom:16px;
}

.im-header h4{
  margin:0;
  font-weight:800;
  color:var(--ifk-navy);
}

.im-subtitle{
  font-size:13px;
  color:var(--ifk-muted);
  margin-top:4px;
}

.panel_s{
  border-radius:14px;
  border:1px solid var(--ifk-border);
  box-shadow:0 8px 20px rgba(0,50,90,.08);
}

hr{border-top:1px solid var(--ifk-border)}

/* ===== FILTER ===== */

.im-filter-label{
  font-size:12px;
  font-weight:800;
  color:var(--ifk-navy);
  margin-bottom:6px;
}

.im-filter .input-group-addon{
  background:var(--ifk-soft);
  border-color:var(--ifk-border);
}

.im-filter .form-control{
  border-radius:10px;
  border:1px solid var(--ifk-border);
}

.im-filter .form-control:focus{
  border-color:var(--ifk-cyan);
  box-shadow:0 0 0 2px rgba(0,166,220,.15);
}

.im-filter .btn{
  border-radius:10px;
}

/* ===== TABLE ===== */

table.dataTable{width:100%!important}

#tbl_applications{table-layout:fixed}

#tbl_applications th,
#tbl_applications td{
  white-space:normal;
  word-break:break-word;
  vertical-align:middle!important;
}

#tbl_applications thead th{
  border:none;
  background:rgba(0,166,220,.06);
  font-size:12px;
  text-transform:uppercase;
  letter-spacing:.03em;
  color:var(--ifk-muted);
  white-space:nowrap;
}

#tbl_applications tbody tr{
  background:#fff;
  box-shadow:0 2px 6px rgba(0,50,90,.05);
  border-radius:12px;
  transition:.15s ease;
}

#tbl_applications tbody tr:hover{
  box-shadow:0 8px 20px rgba(0,50,90,.12);
  transform:translateY(-1px);
}

#tbl_applications tbody td{
  border-top:none!important;
  border-bottom:none!important;
  color:var(--ifk-text);
}

/* widths giữ nguyên */

#tbl_applications th:nth-child(1){width:60px}
#tbl_applications th:nth-child(2){width:260px}
#tbl_applications th:nth-child(3){width:240px}
#tbl_applications th:nth-child(4){width:280px}
#tbl_applications th:nth-child(5){width:200px}
#tbl_applications th:nth-child(6){width:150px}
#tbl_applications th:nth-child(7){width:160px}
#tbl_applications th:nth-child(8){width:220px}
#tbl_applications th:nth-child(9){width:150px}

/* ===== APPLICANT CELL ===== */

.im-applicant{
  display:flex;
  align-items:center;
  gap:10px;
}

.im-applicant img{
  width:38px;
  height:38px;
  border-radius:8px;
  object-fit:cover;
  background:#f3f4f6;
  border:1px solid var(--ifk-border);
}

.im-applicant a{
  font-weight:800;
  color:var(--ifk-navy);
  text-decoration:none;
}

.im-applicant a:hover{
  color:var(--ifk-cyan);
}

.im-primary{
  font-weight:700;
  color:var(--ifk-text);
}

.im-sub{
  font-size:12px;
  color:var(--ifk-muted);
}

/* ===== JOB/COMPANY BLOCK (match "Đơn vị tuyển dụng") ===== */
.job-company-block{ line-height:1.35; }
.job-company-name{
  font-weight:700;
  font-size:13px;
  color:var(--ifk-text);
  display:flex;
  align-items:center;
  gap:6px;
  flex-wrap:wrap;
}
.job-batch{
  font-size:11px;
  padding:3px 8px;
  border-radius:8px;
  background:rgba(0,50,90,.06);
  color:var(--ifk-muted);
  border:1px solid rgba(0,50,90,.10);
}
.job-company-jp{
  margin-top:2px;
  font-size:12px;
  color:var(--ifk-muted);
}
.job-company-address{
  margin-top:1px;
  font-size:12px;
  color:#94a3b8;
}

/* ===== SELECT ===== */

.im-select{
  border-radius:10px!important;
  border:1px solid var(--ifk-border)!important;
  padding:6px 10px!important;
  font-size:13px;
  cursor:pointer;
  background:#fff;
}

.im-select:hover{
  border-color:var(--ifk-cyan)!important;
}

.im-select.pass{
  background:rgba(150,188,23,.15);
  color:#2f5e00;
  border-color:rgba(150,188,23,.35)!important;
}

.im-select.fail{
  background:#fee2e2;
  color:#b91c1c;
  border-color:#fca5a5!important;
}

/* ===== ACTION BUTTONS ===== */

.im-actions{
  display:flex;
  justify-content:center;
  align-items:center;
  gap:8px;
}

.im-btn-circle{
  width:30px;
  height:30px;
  border-radius:999px;
  display:inline-flex!important;
  align-items:center;
  justify-content:center;
  padding:0!important;
  border:none!important;
  box-shadow:0 2px 6px rgba(0,50,90,.12);
  text-decoration:none!important;
  transition:.2s ease;
}

.im-btn-circle i{
  font-size:14px!important;
  color:#fff!important;
}

/* IFK button colors */

.im-btn-view{background:var(--ifk-navy)!important;}
.im-btn-edit{background:var(--ifk-cyan)!important;}
.im-btn-more{background:#64748b!important;}

.im-btn-view:hover{background:#002744!important;}
.im-btn-edit:hover{background:#0095c5!important;}
.im-btn-more:hover{background:#4b5563!important;}

/* ===== MODAL TOOLBAR ===== */

.im-modal-toolbar{
  display:flex;
  flex-wrap:wrap;
  gap:8px;
  margin-bottom:12px;
}

.im-modal-toolbar .btn{
  border-radius:10px;
}

.im-modal-toolbar .btn i{
  margin-right:6px;
}

.im-modal-toolbar .btn-danger i,
.im-modal-toolbar .btn-primary i,
.im-modal-toolbar .btn-default i{
  color:inherit;
}

/* Hide QuickView */

#imQuickViewToolbar{
  display:none!important;
}


/* Inline update saving state */
.im-select.im-saving{
  opacity:.65;
  cursor:wait;
}
</style>

<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">

        <div class="im-header">
          <div>
            <h4><i class="fa-solid fa-user-graduate"></i> Ứng tuyển Internship</h4>
            <div class="im-subtitle">Danh sách sinh viên ứng tuyển theo từng đơn tuyển (mỗi dòng = 1 lần ứng tuyển)</div>
          </div>

          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <a href="<?= admin_url('internship_management/internship_applications/create'); ?>" class="btn btn-primary">
              <i class="fa-solid fa-plus"></i> Thêm Ứng viên
            </a>
          </div>
        </div>

        <hr>

        <!-- FILTER -->
        <form method="get" class="row im-filter" style="margin-bottom:18px">
          <div class="col-md-3">
            <div class="im-filter-label">Tìm kiếm</div>
            <div class="input-group">
              <span class="input-group-addon"><i class="fa-solid fa-magnifying-glass"></i></span>
              <input type="text" name="search" class="form-control" value="<?= html_escape($filters['search'] ?? ''); ?>" placeholder="Tên ứng viên / trường / ngành">
            </div>
          </div>

          <div class="col-md-3">
            <div class="im-filter-label">Ngành</div>
            <input type="text" name="major" class="form-control" value="<?= html_escape($filters['major'] ?? ''); ?>" placeholder="VD: Nhật Bản học, Điều dưỡng">
          </div>

          <div class="col-md-3">
            <div class="im-filter-label">Trường</div>
            <input type="text" name="school" class="form-control" value="<?= html_escape($filters['school'] ?? ''); ?>" placeholder="Tên trường">
          </div>

          <div class="col-md-3">
            <div class="im-filter-label">Trạng thái</div>
            <select name="status" class="form-control">
              <option value="">Tất cả</option>
              <?php if (!empty($status_list)): ?>
                <?php foreach($status_list as $key => $label): ?>
                  <option value="<?= html_escape($key); ?>" <?= (isset($filters['status']) && $filters['status'] == $key ? 'selected' : ''); ?>>
                    <?= html_escape($label); ?>
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>

          <div class="col-md-12" style="margin-top:12px">
            <button class="btn btn-primary"><i class="fa-solid fa-filter"></i> Lọc</button>
            <a href="<?= admin_url('internship_management/internship_applications'); ?>" class="btn btn-default">
              <i class="fa-solid fa-rotate"></i> Reset
            </a>
          </div>
        </form>

        <div class="table-responsive">
          <!--<table class="table dt-table table-hover" id="tbl_applications">-->
          <table
            class="table dt-table table-hover"
            id="tbl_applications"
            data-order-col="0"
            data-order-type="desc">
            <thead>
              <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Trường</th>
                <th>Đơn tuyển</th>
                <th>Ngành</th>
                <th>Ngày ứng tuyển</th>
                <th>Kết quả PV</th>
                <th>Tiến độ hồ sơ</th>
                <th class="text-center">Thao tác</th>
              </tr>
            </thead>

            <tbody>
            <?php if (!empty($applications)) : ?>
              <?php foreach ($applications as $app): ?>
                <?php
                  $app_id     = (int)($app['id'] ?? 0);
                  $student_id = (int)($app['student_id'] ?? 0);

                  // School bilingual
                  $school_vi = $app['school_name_vi'] ?? ($app['school_name'] ?? '');
                  $school_ja = $app['school_name_ja'] ?? '';

                  // Major bilingual
                  $major_vi = $app['major_vi'] ?? ($app['major'] ?? ($app['job_major_vi'] ?? ''));
                  $major_ja = $app['major_ja'] ?? ($app['job_major_ja'] ?? ($app['job_major_jp'] ?? ''));

                  $avatar = im_avatar_url($app['avatar'] ?? '');

                  // urls cho modal toolbar
                  $url_view   = admin_url('internship_management/internship_applications/view/' . $app_id);
                  $url_edit   = admin_url('internship_management/internship_applications/edit/' . $app_id);
                    $url_view_page = ($student_id > 0)
                        ? admin_url('internship_management/internship_applications/student/' . $student_id)
                        : admin_url('internship_management/internship_applications/view/' . $app_id);
$url_student_profile = ($student_id > 0) ? admin_url('internship_management/internship_applications/student/' . $student_id) : '';
$url_print  = admin_url('internship_management/internship_applications/print/' . $app_id);
                  $url_cv     = !empty($app['cv_file']) ? admin_url('internship_management/internship_applications/preview_file/' . $app_id) : '';
                  $url_reapply= admin_url('internship_management/internship_applications/create?clone=' . $app_id);
                  $url_delete = admin_url('internship_management/internship_applications/delete/' . $app_id);
                  $url_student = ($student_id > 0) ? admin_url('internship_management/internship_applications/student/' . $student_id) : '';

                  // inline status fields (giữ như hệ bạn đang dùng)
                  /*$progress  = $app['dossier_progress'] ?? ($app['status'] ?? 'not_updated');
                  $interview = $app['interview_result'] ?? '';*/
                  // inline status fields
                    $progress = im_normalize_dossier_progress($app['dossier_progress'] ?? ($app['status'] ?? 'not_updated'));
                    $interview = im_normalize_interview_result($app['interview_result'] ?? '');
                    
                    // Nếu dữ liệu cũ chưa có interview_result nhưng progress đã ở bước sau PV,
                    // tạm suy ra để hiển thị đúng.
                    if ($interview === '') {
                        $interview = im_progress_implies_interview($progress);
                    }
                ?>
                <tr>
                  <td><?= $app_id; ?></td>

                  <td>
                    <div class="im-applicant">
                      <!-- <img src="<?= $avatar; ?>" alt="avatar"> -->
                      <img 
                          src="<?= $avatar; ?>" 
                          alt="avatar"
                          loading="lazy"
                          decoding="async"
                          width="38"
                          height="38">
                      <div>
                        <a href="javascript:void(0);"
                           class="js-quick-view"
                           data-id="<?= $app_id; ?>"
                           data-fullname="<?= html_escape($app['full_name'] ?? ''); ?>"
                           data-school-vi="<?= html_escape($school_vi); ?>"
                           data-school-ja="<?= html_escape($school_ja); ?>"
                           data-major-vi="<?= html_escape($major_vi); ?>"
                           data-major-ja="<?= html_escape($major_ja); ?>"
                           data-url-view="<?= html_escape($url_view); ?>"
                           data-url-edit="<?= html_escape($url_edit); ?>"
                           data-url-print="<?= html_escape($url_print); ?>"
                           data-url-cv="<?= html_escape($url_cv); ?>"
                           data-url-reapply="<?= html_escape($url_reapply); ?>"
                           data-url-delete="<?= html_escape($url_delete); ?>"
                           data-url-student="<?= html_escape($url_student); ?>"
                        >
                          <?= html_escape($app['full_name'] ?? '—'); ?>
                        </a>
                        <div class="im-sub text-muted">
                          <?= !empty($app['email']) ? html_escape($app['email']) : ''; ?>
                          <?= (!empty($app['phone_student']) ? ' • ' . html_escape($app['phone_student']) : ''); ?>
                        </div>
                      </div>
                    </div>
                  </td>

                  <td><?= im_bi_text($school_vi, $school_ja, '—'); ?></td>

                  <td>
                    <?php
                      $company_vi = $app['company_name_vi'] ?? ($app['company_name'] ?? '');
                      $company_jp = $app['company_name_jp'] ?? ($app['company_name_ja'] ?? ($app['company_name_japanese'] ?? ($app['company_jp'] ?? '')));
                      $addr_jp    = $app['company_address_jp'] ?? ($app['company_address_ja'] ?? ($app['company_address_japanese'] ?? ($app['address_jp'] ?? ($app['job_address_jp'] ?? ''))));
                      $batch_no   = $app['batch'] ?? ($app['dot'] ?? ($app['batch_no'] ?? ($app['job_batch'] ?? ($app['wave'] ?? ''))));
                    ?>
                    <div class="job-company-block">
                      <div class="job-company-name">
                        <?= html_escape($company_vi !== '' ? $company_vi : ($app['job_name'] ?? '—')); ?>
                        <?php if ($batch_no !== '' && $batch_no !== null): ?>
                          <span class="badge badge-light job-batch">Đợt <?= html_escape($batch_no); ?></span>
                        <?php endif; ?>
                      </div>

                      <?php if (!empty($company_jp)): ?>
                        <div class="job-company-jp"><?= html_escape($company_jp); ?></div>
                      <?php endif; ?>

                      <?php if (!empty($addr_jp)): ?>
                        <div class="job-company-address"><?= html_escape($addr_jp); ?></div>
                      <?php endif; ?>
                    </div>
                  </td>

                  <td><?= im_bi_text($major_vi, $major_ja, 'Không rõ'); ?></td>

                  <td><?= !empty($app['datecreated']) ? _dt($app['datecreated']) : '—'; ?></td>

                  <td>
                    <!--<select class="form-control input-sm im-select js-interview" data-id="<?= $app_id; ?>">
                      <option value="" <?= ($interview===''?'selected':''); ?>>— Chưa đánh giá —</option>
                      <option value="pass" <?= ($interview==='pass'?'selected':''); ?>>Đạt</option>
                      <option value="fail" <?= ($interview==='fail'?'selected':''); ?>>Rớt</option>
                    </select>-->
                    <select class="form-control input-sm im-select js-interview" data-id="<?= $app_id; ?>">
                	  <?php foreach (($interview_result_list ?? im_interview_result_list()) as $k => $label): ?>
                		<option value="<?= html_escape($k); ?>" <?= ($interview === (string)$k ? 'selected' : ''); ?>>
                		  <?= html_escape($label); ?>
                		</option>
                	  <?php endforeach; ?>
                	</select>
                  </td>

                  <td>
                    <select class="form-control input-sm im-select js-progress" data-id="<?= $app_id; ?>">
                      <?php
                      // Dùng list chuẩn từ helper/controller
	                  $progress_list = $dossier_progress_list ?? im_dossier_progress_list();
                      $cur = (string)$progress;
                      if (!isset($progress_list[$cur])) $cur = 'not_updated';
                      foreach ($progress_list as $k=>$t) { ?>
                        <option value="<?= html_escape($k); ?>" <?= ($cur===(string)$k?'selected':''); ?>><?= html_escape($t); ?></option>
                      <?php } ?>
                    </select>
                  </td>

                  <td class="text-center">
                    <div class="im-actions">
                      <!-- Quick view only -->
                      <a href="javascript:void(0);"
                         class="im-btn-circle im-btn-view js-quick-view"
                         title="Xem nhanh"
                         data-id="<?= $app_id; ?>"
                         data-url="<?= html_escape($url_view); ?>"
                         data-url-view-page="<?= html_escape($url_view_page); ?>"
                         data-url-edit="<?= html_escape($url_edit); ?>"
                         data-url-print="<?= html_escape($url_print); ?>"
                         data-url-cv="<?= html_escape($url_cv); ?>"
                         data-url-reapply="<?= html_escape($url_reapply); ?>"
                         data-url-delete="<?= html_escape($url_delete); ?>"
                         data-url-student="<?= html_escape($url_student); ?>"
                      >
                        <i class="fa-solid fa-eye"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" class="text-center text-muted" style="padding:40px 0;">Chưa có ứng viên nào.</td>
              </tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- QUICK VIEW MODAL -->
<div class="modal fade" id="imQuickViewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="imQuickViewTitle">Thông tin ứng viên</h4>
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>

      <div class="modal-body">
        <!-- toolbar -->
        <div class="im-modal-toolbar" id="imQuickViewToolbar"></div>

        <!-- ajax content -->
        <div id="imQuickViewContent">
          <div class="text-center text-muted" style="padding:18px 0;">Đang tải...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>

<script>
(function waitForJQ(){
  if (!window.jQuery) return setTimeout(waitForJQ, 50);
  var $ = window.jQuery;
  "use strict";

  // ====== QUICK VIEW with TOOLBAR ======
  function buildToolbar(d){
    var buttons = [];

    if (d.url_view) {
      buttons.push('<a class="btn btn-default" href="'+d.url_view+'"><i class="fa fa-external-link"></i> Xem chi tiết</a>');
    }
    if (d.url_edit) {
      buttons.push('<a class="btn btn-primary" href="'+d.url_edit+'"><i class="fa fa-pen"></i> Sửa</a>');
    }
    if (d.url_print) {
      buttons.push('<a class="btn btn-default" target="_blank" href="'+d.url_print+'"><i class="fa fa-print"></i> In</a>');
    }
    if (d.url_cv) {
      buttons.push('<a class="btn btn-default" target="_blank" href="'+d.url_cv+'"><i class="fa fa-file-pdf-o"></i> CV</a>');
    }
    if (d.url_student) {
      buttons.push('<a class="btn btn-default" href="'+d.url_student+'"><i class="fa fa-user"></i> Trang SV</a>');
    }
    if (d.url_reapply) {
      buttons.push('<a class="btn btn-success" href="'+d.url_reapply+'"><i class="fa fa-rotate-right"></i> Ứng tuyển thêm đơn</a>');
    }
    if (d.url_delete) {
      buttons.push('<a class="btn btn-danger _delete" href="'+d.url_delete+'"><i class="fa fa-trash"></i> Xóa</a>');
    }

    return buttons.join(' ');
  }

  function biTitle(name, school_vi, school_ja, major_vi, major_ja){
    var parts = [];
    if (name) parts.push(name);
    var extra = [];
    if (school_vi) extra.push(school_vi);
    else if (school_ja) extra.push(school_ja);
    if (major_vi) extra.push(major_vi);
    else if (major_ja) extra.push(major_ja);
    if (extra.length) parts.push('• ' + extra.join(' • '));
    return parts.join(' ');
  }

  $(document).on('click', '.js-quick-view', function () {
    var $el = $(this);

    var d = {
      id: $el.data('id'),
      fullname: $el.data('fullname') || '',
      school_vi: $el.data('school-vi') || '',
      school_ja: $el.data('school-ja') || '',
      major_vi: $el.data('major-vi') || '',
      major_ja: $el.data('major-ja') || '',
      url_view: $el.data('url-view') || '',
      url_edit: $el.data('url-edit') || '',
      url_print: $el.data('url-print') || '',
      url_cv: $el.data('url-cv') || '',
      url_reapply: $el.data('url-reapply') || '',
      url_delete: $el.data('url-delete') || '',
      url_student: $el.data('url-student') || ''
    };

    $('#imQuickViewTitle').text(biTitle(d.fullname, d.school_vi, d.school_ja, d.major_vi, d.major_ja));
    $('#imQuickViewToolbar').html('');

    $('#imQuickViewContent').html('<div class="text-center text-muted" style="padding:18px 0;">Đang tải...</div>');
    $('#imQuickViewModal').modal('show');

    $.get('<?= admin_url("internship_management/internship_applications/view_ajax/"); ?>' + d.id, function (html) {
      $('#imQuickViewContent').html(html);
    });
  });

  // ====== Inline update (chuẩn hoá logic) ======
  var UPDATE_URL = '<?= admin_url("internship_management/internship_applications/update_application_state"); ?>';

  // CI/Perfex CSRF
  var CSRF_NAME   = '<?= $this->security->get_csrf_token_name(); ?>';
  var CSRF_HASH   = '<?= $this->security->get_csrf_hash(); ?>';
  var CSRF_COOKIE = '<?= (string)$this->config->item("csrf_cookie_name"); ?>';

  function getCookie(name){
    if (!name) return '';
    var m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([$?*|{}\(\)\[\]\\/\+^])/g,'\$1') + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : '';
  }
  function refreshCsrf(){
    // Nếu hệ thống bật csrf_regenerate thì cookie sẽ đổi sau mỗi request
    var c = getCookie(CSRF_COOKIE);
    if (c) CSRF_HASH = c;
  }
  function toast(type, msg){
    if (typeof alert_float === 'function') return alert_float(type, msg);
    console.log(type.toUpperCase() + ': ' + msg);
  }

  function setSaving($el, saving){
    $el.prop('disabled', !!saving);
    $el.toggleClass('im-saving', !!saving);
  }

function refreshSelectUI($el){
    // Nếu dùng bootstrap-select / selectpicker
    try {
      if ($el && $el.length) {
        if ($el.hasClass('selectpicker') && $.fn.selectpicker) {
          $el.selectpicker('refresh');
        }
        // Nếu dùng select2
        if ($el.data('select2') && $.fn.select2) {
          $el.trigger('change.select2');
        }
      }
    } catch(e){}
  }

  function postUpdate(id, field, value){
    var payload = {};
    payload[CSRF_NAME] = CSRF_HASH;
    payload.id = id;
    payload.field = field;
    payload.value = value;

    return $.ajax({
      url: UPDATE_URL,
      method: 'POST',
      data: payload
    }).always(function(){
      refreshCsrf();
    });
  }

  // Interview result UI helper
  function applyInterviewClass($el){
    $el.removeClass('pass fail');
    if ($el.val()==='pass') $el.addClass('pass');
    if ($el.val()==='fail') $el.addClass('fail');
  }
  $('.js-interview').each(function(){ applyInterviewClass($(this)); });

  // ====== Linkage rules giữa Kết quả PV & Tiến độ hồ sơ ======
  // Quy ước:
  //  - Tiến độ = applied (Ứng tuyển)  => Kết quả PV phải là '' (— Chưa đánh giá —)
  //  - Kết quả PV = fail (Rớt)        => Tiến độ phải là cancelled (Huỷ)
  function getLinked($el, cls){
    var id = $el.data('id');
    return $('.' + cls + '[data-id="' + id + '"]');
  }

  function syncProgressToCancelled($interview, prevProgress){
    var $progress = getLinked($interview, 'js-progress');
    if (!$progress.length) return $.Deferred().resolve().promise();

    // Nếu đã huỷ rồi thì thôi
    if ($progress.val() === 'cancelled') return $.Deferred().resolve().promise();

    // cập nhật UI trước (cho cảm giác tức thời), nhưng vẫn giữ prev để revert nếu lỗi
    $progress.data('prev', prevProgress);
    $progress.val('cancelled');
    refreshSelectUI($progress);

    setSaving($progress, true);

    return postUpdate($progress.data('id'), 'dossier_progress', 'cancelled')
      .done(function(res){
        if (typeof res === 'string') { try { res = JSON.parse(res); } catch (e) {} }
        if (res && res.success === false) {
          // revert UI nếu server báo fail
          $progress.val(prevProgress);
          refreshSelectUI($progress);
          return;
        }
        $progress.data('prev', 'cancelled');
        refreshSelectUI($progress);
        if (res && res.csrf_hash) CSRF_HASH = res.csrf_hash;
      })
      .fail(function(){
        $progress.val(prevProgress);
        refreshSelectUI($progress);
      })
      .always(function(){
        setSaving($progress, false);
      });
  }


    function syncProgressToPrepareDocs($interview, prevProgress){
      var $progress = getLinked($interview, 'js-progress');
      if (!$progress.length) return $.Deferred().resolve().promise();
    
      // Nếu đã là Đang làm hồ sơ thì thôi
      if ($progress.val() === 'docs_preparing') return $.Deferred().resolve().promise();
    
      $progress.data('prev', prevProgress);
      $progress.val('docs_preparing');
      refreshSelectUI($progress);
    
      setSaving($progress, true);
    
      return postUpdate($progress.data('id'), 'dossier_progress', 'docs_preparing')
      .done(function(res){
        if (typeof res === 'string') { try { res = JSON.parse(res); } catch (e) {} }
        if (res && res.success === false) {
          $progress.val(prevProgress);
          refreshSelectUI($progress);
          return;
        }
        //$progress.data('prev', 'prepare_documents');
        $progress.data('prev', 'docs_preparing');
        refreshSelectUI($progress);
        if (res && res.csrf_hash) CSRF_HASH = res.csrf_hash;
      })
      .fail(function(){
        $progress.val(prevProgress);
        refreshSelectUI($progress);
      })
      .always(function(){
        setSaving($progress, false);
      });
  }

  function syncInterviewToPending($progress, prevInterview){
    var $interview = getLinked($progress, 'js-interview');
    if (!$interview.length) return $.Deferred().resolve().promise();

    if (String($interview.val() || '') === '') return $.Deferred().resolve().promise();

    $interview.data('prev', prevInterview);
    $interview.val('');
    applyInterviewClass($interview);
    refreshSelectUI($interview);

    setSaving($interview, true);

    return postUpdate($interview.data('id'), 'interview_result', '')
      .done(function(res){
        if (typeof res === 'string') { try { res = JSON.parse(res); } catch (e) {} }
        if (res && res.success === false) {
          $interview.val(prevInterview);
          applyInterviewClass($interview);
          refreshSelectUI($interview);
          return;
        }
        $interview.data('prev', '');
        applyInterviewClass($interview);
        refreshSelectUI($interview);
        if (res && res.csrf_hash) CSRF_HASH = res.csrf_hash;
      })
      .fail(function(){
        $interview.val(prevInterview);
        applyInterviewClass($interview);
        refreshSelectUI($interview);
      })
      .always(function(){
        setSaving($interview, false);
      });
  }


  function syncInterviewToPass($progress, prevInterview){
    var $interview = getLinked($progress, 'js-interview');
    if (!$interview.length) return $.Deferred().resolve().promise();

    if ($interview.val() === 'pass') return $.Deferred().resolve().promise();

    $interview.data('prev', prevInterview);
    $interview.val('pass');
    applyInterviewClass($interview);
    refreshSelectUI($interview);

    setSaving($interview, true);

    return postUpdate($interview.data('id'), 'interview_result', 'pass')
      .done(function(res){
        if (typeof res === 'string') { try { res = JSON.parse(res); } catch (e) {} }
        if (res && res.success === false) {
          $interview.val(prevInterview);
          applyInterviewClass($interview);
          refreshSelectUI($interview);
          return;
        }
        $interview.data('prev', 'pass');
        applyInterviewClass($interview);
        refreshSelectUI($interview);
        if (res && res.csrf_hash) CSRF_HASH = res.csrf_hash;
      })
      .fail(function(){
        $interview.val(prevInterview);
        applyInterviewClass($interview);
        refreshSelectUI($interview);
      })
      .always(function(){
        setSaving($interview, false);
      });
  }


  // Re-apply UI classes after DataTables redraw (nếu bảng dùng AJAX/draw)
  $(document).on('draw.dt', function(){
    $('.js-interview').each(function(){ applyInterviewClass($(this)); refreshSelectUI($(this)); });
    $('.js-progress').each(function(){ refreshSelectUI($(this)); });
  });

  // Lưu giá trị cũ để revert khi lỗi
  $(document).on('focus', '.js-interview, .js-progress', function(){
    $(this).data('prev', $(this).val());
  });

  $(document).on('change', '.js-interview', function(){
    var $el = $(this);
    var id = $el.data('id');
    var prev = ($el.data('prev') ?? '');
    var val  = $el.val();

    if (String(val) === String(prev)) return;

    applyInterviewClass($el);
    refreshSelectUI($el);
    setSaving($el, true);

    postUpdate(id, 'interview_result', val)
      .done(function(res){
        if (typeof res === 'string') { try { res = JSON.parse(res); } catch (e) {} }

        // chuẩn: res.success true/false
        if (res && res.success === false) {
          $el.val(prev);
          applyInterviewClass($el);
          refreshSelectUI($el);
          toast('danger', res.message || 'Không lưu được Kết quả PV.');
          return;
        }
        toast('success', 'Đã cập nhật Kết quả PV.');
        $el.data('prev', val);
        refreshSelectUI($el);
        if (res && res.csrf_hash) CSRF_HASH = res.csrf_hash;
        // Linkage:
        //  - Rớt  => Tiến độ = Huỷ
        //  - Đạt  => Tiến độ = Chuẩn bị hồ sơ
        if (String(val)==='fail') {
          var $progress = getLinked($el, 'js-progress');
          var prevProg = $progress.length ? ($progress.data('prev') ?? $progress.val()) : '';
          syncProgressToCancelled($el, prevProg);
        } else if (String(val)==='pass') {
          var $progress2 = getLinked($el, 'js-progress');
          var prevProg2 = $progress2.length ? ($progress2.data('prev') ?? $progress2.val()) : '';
          syncProgressToPrepareDocs($el, prevProg2);
        }
      })
      .fail(function(){
        $el.val(prev);
        applyInterviewClass($el);
        refreshSelectUI($el);
        toast('danger', 'Lỗi mạng/CSRF. Vui lòng thử lại.');
      })
      .always(function(){
        setSaving($el, false);
      });
  });

  $(document).on('change', '.js-progress', function(){
    var $el = $(this);
    var id = $el.data('id');
    var prev = ($el.data('prev') ?? '');
    var val  = $el.val();

    if (String(val) === String(prev)) return;

    setSaving($el, true);

    postUpdate(id, 'dossier_progress', val)
      .done(function(res){
        if (typeof res === 'string') { try { res = JSON.parse(res); } catch (e) {} }

        if (res && res.success === false) {
          $el.val(prev);
          refreshSelectUI($el);
          toast('danger', res.message || 'Không lưu được Tiến độ hồ sơ.');
          return;
        }
        toast('success', 'Đã cập nhật Tiến độ hồ sơ.');
        $el.data('prev', val);
        refreshSelectUI($el);
        if (res && res.csrf_hash) CSRF_HASH = res.csrf_hash;
        // Linkage:
        //  - Ứng tuyển / Hẹn phỏng vấn => Kết quả PV = Chưa đánh giá
        //  - Chuẩn bị hồ sơ            => Kết quả PV = Đạt
        if (String(val)==='applied' || String(val)==='interview_scheduled') {
          var $interview = getLinked($el, 'js-interview');
          var prevIv = $interview.length ? ($interview.data('prev') ?? $interview.val()) : '';
          syncInterviewToPending($el, prevIv);
        //} else if (String(val)==='prepare_documents') {
        } else if (String(val)==='docs_preparing') {
          var $interview2 = getLinked($el, 'js-interview');
          var prevIv2 = $interview2.length ? ($interview2.data('prev') ?? $interview2.val()) : '';
          syncInterviewToPass($el, prevIv2);
        }
      })
      .fail(function(){
        $el.val(prev);
        refreshSelectUI($el);
        toast('danger', 'Lỗi mạng/CSRF. Vui lòng thử lại.');
      })
      .always(function(){
        setSaving($el, false);
      });
  });
  
})( );
</script>