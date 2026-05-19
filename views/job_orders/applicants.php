<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
$CI = &get_instance();
$CI->load->helper('internship_management/internship_status');

$im_interview_result_list = im_interview_result_list();
$im_progress_list = im_dossier_progress_list();
?>


<style>

/* =====================================================
   IFK APPLICANTS TABLE - FINAL STABLE
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

/* ===== PANEL ===== */

.panel_s{
  border-radius:14px;
  border:1px solid var(--ifk-border) !important;
  box-shadow:0 6px 18px rgba(0,50,90,.08);
  padding:6px 15px 20px;
}

/* ===== HEADER ===== */

.im-page-head{padding:6px 0 4px;}

.im-page-head .btn{
  border-radius:10px;
}

.im-page-head .btn.btn-info{
  background:var(--ifk-cyan);
  border-color:var(--ifk-cyan);
  color:#fff;
}

.page-header h4{
  font-size:20px;
  font-weight:700;
  color:var(--ifk-navy);
}

.page-subtitle{
  font-size:13px;
  color:var(--ifk-muted);
}

/* ===== TABLE ===== */

.table-applicants{
  border-collapse:separate!important;
  border-spacing:0 6px!important;
}

.table-applicants thead th{
  background:rgba(0,166,220,.06)!important;
  border:none!important;
  text-transform:uppercase;
  font-size:11px;
  color:var(--ifk-muted);
  padding:12px;
}

.table-applicants tbody tr{
  background:#fff!important;
  border-radius:12px!important;
  box-shadow:0 2px 6px rgba(0,50,90,.05);
  transition:.15s ease;
}

.table-applicants tbody tr:hover{
  box-shadow:0 6px 16px rgba(0,50,90,.12);
  transform:translateY(-1px);
}

.table-applicants tbody td{
  border:none!important;
  padding:14px 12px!important;
  vertical-align:middle!important;
  color:var(--ifk-text);
}

/* ===== AVATAR ===== */

.app-name{display:flex;align-items:center;gap:12px;}

.app-name img{
  width:42px;
  height:42px;
  border-radius:8px;
  object-fit:cover;
  background:#f3f4f6;
}

.app-name strong{
  font-size:14px;
  color:var(--ifk-navy);
}

.app-name small{
  font-size:12px;
  color:var(--ifk-muted);
}

/* ===== BADGE GENDER ===== */

.badge-gender{
  padding:3px 10px;
  border-radius:999px;
  font-size:11px;
  font-weight:600;
  color:#fff;
}

.badge-male{background:var(--ifk-cyan);}
.badge-female{background:#d946ef;}

/* ===== RESULT SELECT ===== */

.interview-result{
  border-radius:10px!important;
  border:1px solid var(--ifk-border)!important;
  padding:6px 10px!important;
  font-size:13px;
  cursor:pointer;
}

.interview-result:hover{
  border-color:var(--ifk-cyan)!important;
}

.interview-result.pass{
  background:rgba(150,188,23,.15);
  color:#2f5e00;
  border-color:rgba(150,188,23,.35)!important;
}

.interview-result.fail{
  background:#fee2e2;
  color:#b91c1c;
  border-color:#fca5a5!important;
}

/* ===== DATATABLE ===== */

.dataTables_filter input,
.dataTables_length select{
  border-radius:8px;
  border:1px solid var(--ifk-border);
  padding:6px 10px;
}

.dataTables_paginate .paginate_button{
  border-radius:8px!important;
}

.dataTables_paginate .paginate_button.current{
  background:var(--ifk-navy)!important;
  color:#fff!important;
}

/* ===== DOSSIER PROGRESS ===== */

.dossier-progress{
  border-radius:8px!important;
  border:1px solid var(--ifk-border)!important;
  padding:6px 10px!important;
}

.dossier-progress:hover{
  border-color:var(--ifk-cyan)!important;
}

/* ===== ACTION BUTTONS ===== */

.im-actions{
  display:flex;
  flex-wrap:wrap;
  justify-content:center;
  align-items:center;
  gap:8px;
}

.im-actions a{
  width:30px;
  height:30px;
  border-radius:999px;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  background:rgba(0,166,220,.10);
  color:var(--ifk-navy);
  transition:.2s ease;
}

.im-actions a:hover{
  background:var(--ifk-navy);
  color:#fff;
}

/* ===== SELECT STATUS (PRO) ===== */

.im-select{
  min-width:170px;
  border-radius:10px!important;
  height:34px;
  padding:6px 10px;
  border:1px solid var(--ifk-border);
}

.im-select.im-pass{
  background:rgba(150,188,23,.15);
  border-color:rgba(150,188,23,.35);
  color:#2f5e00;
}

.im-select.im-fail{
  background:#fee2e2;
  border-color:#fca5a5;
  color:#b91c1c;
}

/* ===== PROGRESS DOT ===== */

.im-dot{
  width:6px;
  height:6px;
  border-radius:999px;
  background:#9ca3af;
}

.im-dot.pass{background:var(--ifk-green);}
.im-dot.fail{background:#ef4444;}

@media(max-width:1200px){
  .im-select{min-width:140px;}
}

</style>

<div id="wrapper">
<div class="content">
<div class="panel_s">
<div class="panel-body">

    <div class="im-page-head tw-flex tw-items-start tw-justify-between tw-flex-wrap tw-gap-3">
    <div>
        <h4 class="bold" style="margin:0 0 6px;">
            <i class="fa fa-users"></i>
            Ứng viên — <?= html_escape($job['company_name_vi'] ?? $job['company_name'] ?? ''); ?>
        </h4>
        <?php $jobId = (int)($job['id'] ?? $job['job_order_id'] ?? $job['job_id'] ?? 0); ?>
        <div class="page-subtitle" style="line-height:1.6;">
            <div><b>Mã đơn:</b> #<?= $jobId; ?></div>
            <?php $addr = $job['address_vi'] ?? $job['company_address_vi'] ?? $job['company_address'] ?? $job['address'] ?? ''; ?>
            <?php if (!empty($addr)) { ?><div><b>Địa chỉ:</b> <?= html_escape($addr); ?></div><?php } ?>
            <?php $field = $job['job_title'] ?? $job['industry'] ?? $job['field'] ?? ''; ?>
            <?php if (!empty($field)) { ?><div><b>Lĩnh vực:</b> <?= html_escape($field); ?></div><?php } ?>
            <?php $major = $job['major_vi'] ?? $job['major'] ?? ''; ?>
            <?php if (!empty($major)) { ?><div><b>Ngành:</b> <?= html_escape($major); ?></div><?php } ?>
        </div>
    </div>

    <div class="tw-flex tw-gap-2 tw-items-center">
        <a href="javascript:history.back()" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Quay lại
        </a>
        <a href="<?= admin_url('internship_management/internship_job_orders/profile/'.(int)$jobId); ?>" class="btn btn-primary">
            <i class="fa fa-folder-open"></i> Hồ sơ đơn
        </a>
        <a href="<?= admin_url('internship_management/internship_job_orders'); ?>" class="btn btn-info">
            <i class="fa fa-list"></i> Danh sách đơn
        </a>
      <a href="<?php echo admin_url('internship_management/internship_job_orders/print_applicants/' . (int)$job['id']); ?>"
   target="_blank"
   class="btn btn-danger">
   <i class="fa fa-print"></i> In danh sách
</a>
    </div>
    
</div>

<hr>

    <table id="tbl_job_order_applicants" class="table table-applicants dt-table im-applicants-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Ứng viên</th>
            <th>Trường</th>
            <th>Giới tính</th>
            <th>Ngày ứng tuyển</th>
            <th>Kết quả phỏng vấn</th>
            <th>Tiến độ hồ sơ</th>
            <th class="text-center">IN KQ PV</th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($applicants as $a): ?>
        <tr>
            <td><?= $a['id']; ?></td>

            <td class="app-name">
                <?php $student_id = (int)($a['student_id'] ?? $a['student_client_id'] ?? $a['candidate_id'] ?? 0); $profile_url = $student_id>0 ? admin_url('internship_management/student_client/view/'.$student_id) : ''; ?>
                <img src="<?= !empty($a['avatar'])
                    ? base_url('uploads/internship_avatar/'.$a['avatar'])
                    : base_url('modules/internship_management/assets/no-image.png') ?>">
                <div>
                    <?php if ($profile_url) { ?><a href="<?= $profile_url; ?>" style="color:#111827;text-decoration:none;"><strong><?= html_escape($a['full_name']); ?></strong></a><?php } else { ?><strong><?= html_escape($a['full_name']); ?></strong><?php } ?><br>
                    <small><?= html_escape($a['major']); ?></small>
                </div>
            </td>

            <td>
                <div>
                    <div><?= html_escape($a['school_name_vi'] ?? $a['school_name'] ?? '—'); ?></div>
                    <?php if (!empty($a['school_name_ja'])) { ?>
                        <small class="text-muted"><?= html_escape($a['school_name_ja']); ?></small>
                    <?php } ?>
                </div>
            </td>

            <td>
                <?php if ($a['gender'] == 'male'): ?>
                    <span class="badge-gender badge-male">Nam</span>
                <?php elseif ($a['gender'] == 'female'): ?>
                    <span class="badge-gender badge-female">Nữ</span>
                <?php else: ?> — <?php endif; ?>
            </td>

            <td><?= _dt($a['datecreated']); ?></td>

            <td>
                  <?php
                      $cur_status = im_normalize_dossier_progress($a['dossier_progress'] ?? ($a['status'] ?? 'not_updated'));
                      $cur_iv = im_normalize_interview_result($a['interview_result'] ?? '');
                    
                      if ($cur_iv === '') {
                          $cur_iv = im_progress_implies_interview($cur_status);
                      }
                    
                      $ivClass = ($cur_iv === 'pass') ? 'im-pass' : (($cur_iv === 'fail') ? 'im-fail' : '');
                    ?>
                ?>
                <select class="form-control im-interview-result interview-result im-select <?php echo $ivClass; ?>"
                        data-id="<?php echo (int)$a['id']; ?>">
                  <?php foreach ($im_interview_result_list as $k => $label) { ?>
                    <option value="<?php echo html_escape($k); ?>" <?php echo ($cur_iv===$k?'selected':''); ?>>
                      <?php echo html_escape($label); ?>
                    </option>
                  <?php } ?>
                </select>
            </td>

            <td>
                <?php
                  $cur_status = im_normalize_dossier_progress($a['dossier_progress'] ?? ($a['status'] ?? 'not_updated'));
                ?>
                <select class="form-control im-dossier-progress dossier-progress im-select"
                        data-id="<?php echo (int)$a['id']; ?>">
                  <?php foreach ($im_progress_list as $k => $label) { ?>
                    <option value="<?php echo html_escape($k); ?>" <?php echo ($cur_status===$k?'selected':''); ?>>
                      <?php echo html_escape($label); ?>
                    </option>
                  <?php } ?>
                </select>
                <?php
                  $dot = ($cur_iv==='pass')?'pass':(($cur_iv==='fail')?'fail':'');
                ?>
                <div class="im-progress-pill text-muted">
                  <span class="im-dot <?php echo $dot; ?>"></span>
                </div>
            </td>
<td class="text-center">
    <a class="btn btn-default btn-sm im-btn-print" target="_blank"
       href="<?php echo admin_url('internship_management/internship_job_orders/print_interview_result/'.(int)$job['id'].'/'.(int)$a['id']); ?>"
       title="In thông báo kết quả phỏng vấn">
        <i class="fa fa-print"></i>
    </a>
</td>


        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>
</div>
</div>
</div>

<?php init_tail(); ?>


<script>
// Chờ jQuery sẵn sàng (tránh lỗi 'jQuery is not defined')
(function waitForJQ(){
  if (!window.jQuery) return setTimeout(waitForJQ, 50);
  var $ = window.jQuery;
  "use strict";

  // Unified endpoint (same as applications list)
  var UPDATE_URL = "<?php echo admin_url('internship_management/internship_job_orders/update_application_state'); ?>";
  var CSRF_NAME = (typeof csrfData !== 'undefined' && csrfData.token_name) ? csrfData.token_name : "<?= csrf_token_name(); ?>";
var CSRF_HASH = (typeof csrfData !== 'undefined' && csrfData.hash) ? csrfData.hash : "<?= csrf_hash(); ?>";

  function setCsrfFromResp(resp){
    if(resp && resp.csrf_hash){ CSRF_HASH = resp.csrf_hash; }
  }

  // Map server status keys -> UI canonical keys (to avoid duplicate/legacy statuses)
  /*function mapStatusToUi(s){
    s = (s || '').toString();
    var map = {
      'interview_fail':'stopped',
      'rejected':'stopped',
      'cancelled':'cancelled',
      'coe_received':'has_coe',
      'coe_done':'has_coe',
      'done_documents':'docs_done',
      'docs_preparing':'prepare_documents',
      'pre_return':'returned'
    };
    return map[s] || s;
  }*/
    // Map server status keys -> UI canonical keys.
    // Map này lấy từ internship_status_helper.php, không map tay trong JS nữa.
    var IM_PROGRESS_UI_MAP = <?php echo json_encode(im_dossier_progress_ui_map(), JSON_UNESCAPED_UNICODE); ?>;
    
    function mapStatusToUi(s){
      s = (s || '').toString();
    
      if (Object.prototype.hasOwnProperty.call(IM_PROGRESS_UI_MAP, s)) {
        return IM_PROGRESS_UI_MAP[s];
      }
    
      return s;
    }

  function applyInterviewClass($sel){
    $sel.removeClass('im-pass im-fail');
    if($sel.val()==='pass'){ $sel.addClass('im-pass'); }
    else if($sel.val()==='fail'){ $sel.addClass('im-fail'); }
  }

  function postUpdate(id, field, value, done){
    var payload = {id:id, field:field, value:value};
    payload[CSRF_NAME] = CSRF_HASH;

    $.ajax({
      url: UPDATE_URL,
      method: 'POST',
      data: payload,
      dataType: 'json'
    }).done(function(resp){
      // Some servers echo string JSON
      if(typeof resp === 'string'){
        try{ resp = JSON.parse(resp); }catch(e){}
      }
      setCsrfFromResp(resp);

      if(!resp || resp.success !== true){
        var msg = (resp && resp.message) ? resp.message : 'Không thể cập nhật';
        alert_float('danger', msg);
        return;
      }

      if(done) done(resp);
      alert_float('success', 'Đã cập nhật');
    }).fail(function(xhr){
      // Try parse csrf from headers? fallback keep current
      var msg = 'Không gọi được endpoint cập nhật';
      if(xhr && xhr.status){
        msg += ' (HTTP '+xhr.status+')';
        if(xhr.status === 403 || xhr.status === 419){
          msg += ' - CSRF/Session hết hạn, F5 lại trang';
        }
        if(xhr.status === 404){
          msg += ' - Sai URL endpoint';
        }
      }
      alert_float('danger', msg);
      // log detail for debugging
      try{ console.error('update_application_state fail', xhr.responseText); }catch(e){}
    });
  }

  // Init classes
  $('.interview-result').each(function(){ applyInterviewClass($(this)); });

  // Delegated events (works with datatables redraw)
  $(document).on('change', '.interview-result', function(){
    var $iv = $(this);
    var id  = parseInt($iv.data('id'),10) || 0;
    if(!id){ return; }

    var oldVal = $iv.data('old') ?? '';
    var newVal = $iv.val();
    $iv.data('old', newVal);
    applyInterviewClass($iv);

    postUpdate(id, 'interview_result', newVal, function(resp){
      // authoritative data
      var data = (resp && resp.data) ? resp.data : {};
      if(data.interview_result !== undefined){
        $iv.val(data.interview_result);
        applyInterviewClass($iv);
      }
      /*if(data.status !== undefined){
        var $pr = $('.dossier-progress[data-id="'+id+'"]');
        if($pr.length){ $pr.val(mapStatusToUi(data.status)); }
      }*/
      var serverProgress = (data.dossier_progress !== undefined) ? data.dossier_progress : data.status;
        if(serverProgress !== undefined){
          var $pr = $('.dossier-progress[data-id="'+id+'"]');
          if($pr.length){ $pr.val(mapStatusToUi(serverProgress)); }
        }
    });
  });

  $(document).on('change', '.dossier-progress', function(){
    var $pr = $(this);
    var id  = parseInt($pr.data('id'),10) || 0;
    if(!id){ return; }

    var newVal = $pr.val();

    postUpdate(id, 'dossier_progress', newVal, function(resp){
      var data = (resp && resp.data) ? resp.data : {};
      /*if(data.status !== undefined){
        $pr.val(mapStatusToUi(data.status));
      }*/
      var serverProgress = (data.dossier_progress !== undefined) ? data.dossier_progress : data.status;

        if(serverProgress !== undefined){
          $pr.val(mapStatusToUi(serverProgress));
        }
      if(data.interview_result !== undefined){
        var $iv = $('.interview-result[data-id="'+id+'"]');
        if($iv.length){
          $iv.val(data.interview_result);
          applyInterviewClass($iv);
        }
      }
    });
  });

})();
</script>



