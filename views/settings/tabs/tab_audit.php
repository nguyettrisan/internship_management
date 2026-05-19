<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
// $audit_logs, $audit_total, $audit_page, $audit_limit đang được controller truyền vào.
// $this->im_audit->get_all(...) trả về list object/array

// Helpers
if (!function_exists('im_h')) {
    function im_h($v){ return html_escape((string)$v); }
}
if (!function_exists('im_json_pretty')) {
    function im_json_pretty($v){
        if ($v === null || $v === '') return '';
        if (is_string($v)) {
            $try = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE) $v = $try;
        }
        if (is_array($v) || is_object($v)) {
            return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }
        return (string)$v;
    }
}
if (!function_exists('im_time_ago_safe')) {
    function im_time_ago_safe($dt){
        $dt = trim((string)$dt);
        if ($dt === '' || $dt === '0000-00-00 00:00:00') return '—';
        // Perfex có time_ago() và _dt()
        if (function_exists('time_ago')) return time_ago($dt);
        return $dt;
    }
}

// Việt hoá action
$action_map = [
    'mail_settings_updated' => 'Cập nhật cài đặt Mail',
    'settings_updated'      => 'Cập nhật cài đặt chung',
    'template_created'      => 'Tạo mẫu email',
    'template_updated'      => 'Cập nhật mẫu email',
    'template_deleted'      => 'Xoá mẫu email',
    'cron_ran'              => 'Cron đã chạy',
    'cron_failed'           => 'Cron lỗi',
    'email_sent'            => 'Gửi email',
    'email_failed'          => 'Gửi email thất bại',
];

// Việt hoá rel_type (nếu có)
$rel_map = [
    'settings'   => 'Cài đặt',
    'template'   => 'Mẫu email',
    'cron'       => 'Cron',
    'student'    => 'Học viên',
    'job_order'  => 'Đơn tuyển',
    'mail'       => 'Email',
];

// Lấy query lọc hiện tại
$log_q        = (string)$this->input->get('log_q');
$log_action   = (string)$this->input->get('log_action');
$log_rel_type = (string)$this->input->get('log_rel_type');
$log_staff_id = (int)$this->input->get('log_staff_id');

?>
<style>
/* PRO style nhẹ, theo Perfex */
.im-audit-wrap { margin-top: 10px; }
.im-audit-filters .form-group { margin-bottom: 10px; }
.im-audit-table thead th { font-weight: 600; }
.im-audit-badge {
  display:inline-flex; align-items:center; gap:6px;
  padding: 4px 10px; border-radius: 999px; font-size: 12px;
  border: 1px solid rgba(0,0,0,.08);
}
.im-audit-badge--ok { background:#e9f8ee; color:#1f7a3a; }
.im-audit-badge--warn { background:#fff5e6; color:#8a5a00; }
.im-audit-badge--danger { background:#ffecec; color:#b42318; }
.im-staff {
  display:flex; align-items:center; gap:10px; min-width: 220px;
}
.im-staff img {
  width:34px; height:34px; border-radius:50%;
  object-fit:cover; border: 1px solid rgba(0,0,0,.08);
}
.im-staff .name { font-weight:600; line-height:1.1; }
.im-staff .meta { font-size:12px; color:#6b7280; margin-top:2px; }
.im-rel { color:#475569; }
.im-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 12px; }
.im-empty { padding: 16px; background: #f8fafc; border: 1px dashed rgba(0,0,0,.12); border-radius: 10px; color:#64748b; }
</style>

<div class="im-audit-wrap">

  <!-- Filters -->
  <div class="panel_s">
    <div class="panel-body im-audit-filters">
      <form method="get" action="<?php echo admin_url('internship_management/internship_settings'); ?>">
        <input type="hidden" name="tab" value="audit">

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label>Từ khoá</label>
              <input type="text" class="form-control" name="log_q" value="<?php echo im_h($log_q); ?>" placeholder="VD: email, template, cron...">
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label>Hành động</label>
              <select class="form-control" name="log_action">
                <option value="">— Tất cả —</option>
                <?php foreach ($action_map as $k => $label): ?>
                  <option value="<?php echo im_h($k); ?>" <?php echo ($log_action===$k?'selected':''); ?>>
                    <?php echo im_h($label); ?> (<?php echo im_h($k); ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-3">
            <div class="form-group">
              <label>Nhóm</label>
              <select class="form-control" name="log_rel_type">
                <option value="">— Tất cả —</option>
                <?php foreach ($rel_map as $k => $label): ?>
                  <option value="<?php echo im_h($k); ?>" <?php echo ($log_rel_type===$k?'selected':''); ?>>
                    <?php echo im_h($label); ?> (<?php echo im_h($k); ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-2">
            <div class="form-group">
              <label>ID nhân viên</label>
              <input type="number" class="form-control" name="log_staff_id" value="<?php echo (int)$log_staff_id ?: ''; ?>" placeholder="VD: 4">
            </div>
          </div>
        </div>

        <div class="row mtop5">
          <div class="col-md-12">
            <button type="submit" class="btn btn-primary">
              <i class="fa fa-filter"></i> Lọc
            </button>
            <a class="btn btn-default" href="<?php echo admin_url('internship_management/internship_settings?tab=audit'); ?>">
              <i class="fa fa-refresh"></i> Làm mới
            </a>

            <span class="pull-right text-muted mtop5">
              Tổng: <strong><?php echo (int)$audit_total; ?></strong> bản ghi
            </span>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Table -->
  <div class="panel_s">
    <div class="panel-body">
      <?php if (empty($audit_logs)): ?>
        <div class="im-empty">
          <strong>Chưa có nhật ký thao tác.</strong>
          <div class="mtop5">Hãy thao tác lưu cài đặt / chỉnh mẫu email / chạy cron để hệ thống ghi log.</div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover im-audit-table">
            <thead>
              <tr>
                <th style="width:80px;">ID</th>
                <th>Hành động</th>
                <th style="width:220px;">Đối tượng</th>
                <th style="width:280px;">Nhân viên</th>
                <th style="width:170px;">Thời gian</th>
                <th style="width:110px;" class="text-right">Chi tiết</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($audit_logs as $r):
                // hỗ trợ cả object/array
                $id       = is_object($r) ? ($r->id ?? 0) : ($r['id'] ?? 0);
                $action   = is_object($r) ? ($r->action ?? '') : ($r['action'] ?? '');
                $rel_type = is_object($r) ? ($r->rel_type ?? '') : ($r['rel_type'] ?? '');
                $rel_id   = is_object($r) ? ($r->rel_id ?? 0) : ($r['rel_id'] ?? 0);
                $staff_id = is_object($r) ? ($r->staff_id ?? 0) : ($r['staff_id'] ?? 0);
                $created  = is_object($r) ? ($r->created_at ?? ($r->time ?? '')) : ($r['created_at'] ?? ($r['time'] ?? ''));

                // data json
                $old_data = is_object($r) ? ($r->old_data ?? '') : ($r['old_data'] ?? '');
                $new_data = is_object($r) ? ($r->new_data ?? '') : ($r['new_data'] ?? '');
                $note     = is_object($r) ? ($r->note ?? ($r->message ?? '')) : ($r['note'] ?? ($r['message'] ?? ''));

                $action_vi = $action_map[$action] ?? ('Hành động: ' . $action);
                $rel_vi    = $rel_map[$rel_type] ?? $rel_type;

                // Badge màu theo action
                $badgeClass = 'im-audit-badge--ok';
                if (stripos($action, 'failed') !== false || stripos($action, 'error') !== false) $badgeClass = 'im-audit-badge--danger';
                elseif (stripos($action, 'deleted') !== false) $badgeClass = 'im-audit-badge--warn';

                // Staff info
                $staffName = $staff_id ? (function_exists('get_staff_full_name') ? get_staff_full_name($staff_id) : ('ID #' . $staff_id)) : '—';
                $avatar = '';
                if ($staff_id && function_exists('staff_profile_image')) {
                    // staff_profile_image returns <img> sometimes; ta tự build url nếu có helper, còn không thì dùng placeholder
                    // Perfex có staff_profile_image_url()
                    if (function_exists('staff_profile_image_url')) {
                        $avatar = staff_profile_image_url($staff_id, 'small');
                    }
                }
                if (!$avatar) {
                    $avatar = admin_url('assets/images/user-placeholder.jpg');
                }

                $modalId = 'imAuditModal_' . (int)$id;
              ?>
                <tr>
                  <td class="im-mono">#<?php echo (int)$id; ?></td>

                  <td>
                    <span class="im-audit-badge <?php echo $badgeClass; ?>">
                      <i class="fa fa-bolt"></i>
                      <?php echo im_h($action_vi); ?>
                    </span>
                    <div class="text-muted mtop5 im-mono"><?php echo im_h($action); ?></div>
                    <?php if ($note): ?>
                      <div class="mtop5"><?php echo im_h($note); ?></div>
                    <?php endif; ?>
                  </td>

                  <td>
                    <div class="im-rel">
                      <strong><?php echo im_h($rel_vi ?: '—'); ?></strong>
                      <?php if ($rel_id): ?>
                        <span class="im-mono">#<?php echo (int)$rel_id; ?></span>
                      <?php endif; ?>
                    </div>
                    <?php if ($rel_type): ?>
                      <div class="text-muted im-mono"><?php echo im_h($rel_type); ?></div>
                    <?php endif; ?>
                  </td>

                  <td>
                    <div class="im-staff">
                      <img src="<?php echo im_h($avatar); ?>" alt="avatar">
                      <div>
                        <div class="name"><?php echo im_h($staffName); ?></div>
                        <div class="meta im-mono">ID: <?php echo (int)$staff_id; ?></div>
                      </div>
                    </div>
                  </td>

                  <td>
                    <span title="<?php echo im_h($created); ?>">
                      <?php echo im_h(im_time_ago_safe($created)); ?>
                    </span>
                    <div class="text-muted mtop5"><?php echo function_exists('_dt') ? _dt($created) : im_h($created); ?></div>
                  </td>

                  <td class="text-right">
                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#<?php echo $modalId; ?>">
                      <i class="fa fa-eye"></i> Xem
                    </button>
                  </td>
                </tr>

                <!-- Modal detail -->
                <div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                          Chi tiết log #<?php echo (int)$id; ?> — <?php echo im_h($action_vi); ?>
                        </h4>
                      </div>
                      <div class="modal-body">
                        <div class="row">
                          <div class="col-md-6">
                            <label>Dữ liệu trước (OLD)</label>
                            <pre class="im-mono" style="max-height:340px;overflow:auto;"><?php echo im_h(im_json_pretty($old_data)); ?></pre>
                          </div>
                          <div class="col-md-6">
                            <label>Dữ liệu sau (NEW)</label>
                            <pre class="im-mono" style="max-height:340px;overflow:auto;"><?php echo im_h(im_json_pretty($new_data)); ?></pre>
                          </div>
                        </div>

                        <hr>

                        <div class="row">
                          <div class="col-md-4"><strong>Nhân viên:</strong> <?php echo im_h($staffName); ?> (ID: <?php echo (int)$staff_id; ?>)</div>
                          <div class="col-md-4"><strong>Đối tượng:</strong> <?php echo im_h($rel_vi ?: '—'); ?> <?php echo $rel_id ? ('#'.(int)$rel_id) : ''; ?></div>
                          <div class="col-md-4"><strong>Thời gian:</strong> <?php echo function_exists('_dt') ? _dt($created) : im_h($created); ?></div>
                        </div>

                        <?php if ($note): ?>
                          <div class="mtop10"><strong>Ghi chú:</strong> <?php echo im_h($note); ?></div>
                        <?php endif; ?>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                      </div>
                    </div>
                  </div>
                </div>

              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <?php
          // Pagination đơn giản theo $audit_total/$audit_page/$audit_limit
          $total_pages = (int)ceil(((int)$audit_total) / max(1,(int)$audit_limit));
          if ($total_pages > 1):
            $base = admin_url('internship_management/internship_settings?tab=audit'
              . '&log_q=' . rawurlencode($log_q)
              . '&log_action=' . rawurlencode($log_action)
              . '&log_rel_type=' . rawurlencode($log_rel_type)
              . '&log_staff_id=' . rawurlencode((string)$log_staff_id)
            );
        ?>
          <nav class="text-center">
            <ul class="pagination mtop10" style="margin:0;">
              <?php for ($p=1; $p<=$total_pages; $p++): ?>
                <li class="<?php echo ($p==(int)$audit_page?'active':''); ?>">
                  <a href="<?php echo $base . '&log_page=' . $p; ?>"><?php echo $p; ?></a>
                </li>
              <?php endfor; ?>
            </ul>
          </nav>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </div>

</div>