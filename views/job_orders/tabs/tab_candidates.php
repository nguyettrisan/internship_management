<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
if (!function_exists('im_avatar_url')) {
  function im_avatar_url($avatar)
  {
    return !empty($avatar)
      ? base_url('uploads/internship_avatar/' . $avatar)
      : base_url('modules/internship_management/assets/no-image.png');
  }
}

$rows = [];
if (isset($applications)) $rows = (array)$applications;
elseif (isset($candidates)) $rows = (array)$candidates;
elseif (isset($applicants)) $rows = (array)$applicants;

// list chuẩn (y index)
/*$progress_list = $dossier_progress_list ?? [
  'not_updated'         => 'Chưa cập nhật',
  'applied'             => 'Ứng tuyển',
  'interview_scheduled' => 'Hẹn phỏng vấn',
  'prepare_documents'   => 'Chuẩn bị hồ sơ',
  'done_documents'      => 'Hoàn thành hồ sơ',
  'coe_waiting'         => 'Đợi COE',
  'coe_received'        => 'Đã có COE',
  'visa_processing'     => 'Làm visa',
  'ticket_booking'      => 'Mua vé nhập cảnh',
  'pre_departure'       => 'Chuẩn bị bay',
  'in_japan'            => 'Đang ở Nhật',
  'returned'            => 'Đã về nước',
  'cancelled'           => 'Huỷ',
];*/
$CI = &get_instance();
$CI->load->helper('internship_management/internship_status');

$progress_list = $dossier_progress_list ?? im_dossier_progress_list();
$interview_result_list = $interview_result_list ?? im_interview_result_list();
?>

<style>
:root{
  --ifk-navy:#00325a;
  --ifk-green:#96bc17;
  --ifk-cyan:#00a6dc;
  --ifk-soft:rgba(0,166,220,.08);
  --ifk-muted:#64748b;
}

.im-stt{width:30px;height:30px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;
  background:rgba(150,188,23,.14);border:1px solid rgba(150,188,23,.25);font-weight:800;color:var(--ifk-navy);}

.im-cand{display:flex;align-items:center;gap:12px;}
.im-cand img{width:46px;height:46px;border-radius:14px;object-fit:cover;border:1px solid #e5e7eb;background:#f3f4f6;}

.im-name{font-weight:900;color:var(--ifk-navy);line-height:1.15;}
.im-sub{margin-top:4px;font-size:13px;font-weight:700;color:#5b6778;display:flex;align-items:center;flex-wrap:wrap;gap:10px;}
.im-meta{display:inline-flex;align-items:center;gap:6px;}
.im-meta i{color:var(--ifk-cyan);}
.im-dot{color:#cbd5e1;font-weight:900;}

.im-date{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;
  background:var(--ifk-soft);border:1px solid rgba(0,166,220,.18);font-weight:800;color:var(--ifk-navy);}
.im-date i{color:var(--ifk-cyan);}

.im-select{border-radius:14px !important;padding:8px 12px !important;font-weight:900 !important;min-width:170px;}
.im-select.pass{background:rgba(150,188,23,.12)!important;border-color:rgba(150,188,23,.28)!important;}
.im-select.fail{background:rgba(239,68,68,.08)!important;border-color:rgba(239,68,68,.22)!important;}
.im-select.pending{background:rgba(0,166,220,.08)!important;border-color:rgba(0,166,220,.22)!important;}

.im-btn-view{border-radius:14px !important;padding:9px 14px !important;font-weight:900 !important;display:inline-flex;align-items:center;gap:8px;}
.im-btn-view i{color:var(--ifk-cyan);}
</style>

<div class="panel_s">
  <div class="panel-body">

    <h4 class="no-margin" style="font-weight:900;color:#00325a;">
      <i class="fa fa-users" style="color:#00a6dc;"></i>
      Ứng viên
      <span class="label label-info" style="border-radius:999px;padding:6px 10px;margin-left:8px;"># <?php echo count($rows); ?></span>
    </h4>

    <hr class="hr-panel-heading" />

    <div class="table-responsive">
      <table id="tbl_job_tab_candidates" class="table dt-table table-hover">
        <thead>
          <tr>
            <th style="width:90px;">STT</th>
            <th>Họ & Tên</th>
            <th style="width:160px;">Trường</th>
            <th style="width:220px;">Ngày ứng tuyển</th>
            <th style="width:220px;">Kết quả PV</th>
            <th style="width:240px;">Tiến độ hồ sơ</th>
            <th style="width:120px;text-align:right;">Xem</th>
          </tr>
        </thead>

        <tbody>
        <?php if (empty($rows)) { ?>
          <tr><td colspan="7" class="text-center text-muted">Chưa có ứng viên.</td></tr>
        <?php } else { ?>
          <?php $i=0; foreach ($rows as $app) { $i++;

            // ===== lấy đúng theo index (6).php =====
           $app_id = (int)($app['id'] ?? 0);

// ưu tiên student_id, fallback nhiều key
$sid = (int)($app['student_id'] ?? 0);
if ($sid <= 0) $sid = (int)($app['sid'] ?? 0);
if ($sid <= 0) $sid = (int)($app['studentid'] ?? 0);
if ($sid <= 0) $sid = (int)($app['student_client_id'] ?? 0);
if ($sid <= 0) $sid = (int)($app['candidate_id'] ?? 0);
if ($sid <= 0) $sid = (int)($app['applicant_id'] ?? 0);

// fallback cuối cùng để KHÔNG MẤT NÚT (giống view_ajax bạn gửi)
if ($sid <= 0) $sid = $app_id;
            $full_name = $app['full_name'] ?? '—';
            $email     = $app['email'] ?? '';
            $phone     = $app['phone_student'] ?? ($app['phone'] ?? '');
            $school    = $app['school_name_vi'] ?? ($app['school_name'] ?? '-');
            $avatar    = im_avatar_url($app['avatar'] ?? '');

            $applied_text = !empty($app['datecreated']) ? _dt($app['datecreated']) : '—';

            /*$interview = (string)($app['interview_result'] ?? '');
            $progress  = (string)($app['dossier_progress'] ?? ($app['status'] ?? 'not_updated'));

            // ===== fix "sai tiến độ": nếu progress không nằm trong list -> not_updated =====
            $cur_progress = $progress;
            if (!isset($progress_list[$cur_progress])) $cur_progress = 'not_updated';*/
            $interview = im_normalize_interview_result($app['interview_result'] ?? '');
            $cur_progress = im_normalize_dossier_progress($app['dossier_progress'] ?? ($app['status'] ?? 'not_updated'));
            
            if ($interview === '') {
                $interview = im_progress_implies_interview($cur_progress);
            }
            
            if (!isset($progress_list[$cur_progress])) {
                $cur_progress = 'not_updated';
            }

            // class PV
            $iv_cls = 'pending';
            if ($interview === 'pass') $iv_cls = 'pass';
            if ($interview === 'fail') $iv_cls = 'fail';

            // ===== fix "nút xem": đúng route profile =====
            $url_view = admin_url('internship_management/student_client/view/' . $sid);
          ?>
            <tr>
              <td><span class="im-stt"><?php echo $i; ?></span></td>

              <td>
                <div class="im-cand">
                  <img src="<?php echo html_escape($avatar); ?>" alt="avatar">
                  <div>
                    <div class="im-name"><?php echo html_escape($full_name); ?></div>

                    <div class="im-sub">
                      <?php if ($email) { ?>
                        <span class="im-meta">
                          <i class="fa fa-envelope"></i>
                          <a href="mailto:<?php echo html_escape($email); ?>"><?php echo html_escape($email); ?></a>
                        </span>
                      <?php } ?>

                      <?php if ($email && $phone) { ?><span class="im-dot">•</span><?php } ?>

                      <?php if ($phone) { ?>
                        <span class="im-meta">
                          <i class="fa fa-phone"></i>
                          <a href="tel:<?php echo html_escape($phone); ?>"><?php echo html_escape($phone); ?></a>
                        </span>
                      <?php } ?>
                    </div>

                  </div>
                </div>
              </td>

              <td style="font-weight:900;"><?php echo html_escape($school); ?></td>

              <td>
                <span class="im-date"><i class="fa fa-calendar"></i> <?php echo html_escape($applied_text); ?></span>
              </td>

              <td>
                <!-- <select class="form-control im-select <?php echo $iv_cls; ?>" disabled>
                  <option value="" <?php echo ($interview===''?'selected':''); ?>>— Chưa đánh giá —</option>
                  <option value="pass" <?php echo ($interview==='pass'?'selected':''); ?>>Đạt</option>
                  <option value="fail" <?php echo ($interview==='fail'?'selected':''); ?>>Rớt</option>
                </select> -->
                <select class="form-control im-select <?php echo $iv_cls; ?>" disabled>
                  <?php foreach ($interview_result_list as $k => $label) {
                    $sel = ($interview === (string)$k) ? 'selected' : '';
                    echo '<option value="'.html_escape($k).'" '.$sel.'>'.html_escape($label).'</option>';
                  } ?>
                </select>
              </td>

              <td>
                <select class="form-control im-select pending" disabled>
                  <?php foreach ($progress_list as $k => $label) {
                    $sel = ($k === $cur_progress) ? 'selected' : '';
                    echo '<option value="'.html_escape($k).'" '.$sel.'>'.html_escape($label).'</option>';
                  } ?>
                </select>
              </td>

           <td style="text-align:right;">
  <a class="btn btn-default im-btn-view" href="<?php echo $url_view; ?>" target="_blank">
    <i class="fa fa-eye"></i> Xem
  </a>
</td>
            </tr>
          <?php } ?>
        <?php } ?>
        </tbody>
      </table>
    </div>

  </div>
</div>