<?php
/**
 * Common mail fields partial.
 * Usage: $this->load->view('mail/_common_fields', ['prefix' => 'students']);
 */
$prefix = isset($prefix) && $prefix ? preg_replace('/[^a-zA-Z0-9_]/', '', $prefix) : 'students';

$id_template = $prefix . '_template_id';
$id_subject  = $prefix . '_mail_subject';
$id_html     = $prefix . '_mail_html';
$id_preview  = $prefix . '_previewBox';
$id_btn_load = $prefix . '_btnLoadTemplate';
$id_btn_copy = $prefix . '_btnCopyHtml';
?>

<div class="mail-common" data-prefix="<?php echo html_escape($prefix); ?>">
  <div class="row">
    <div class="col-md-6">
      <div class="form-group">
        <label for="<?php echo $id_template; ?>">Mẫu Email</label>
        <select id="<?php echo $id_template; ?>" class="form-control js-template-select" name="template_id">
          <option value="">-- Chọn mẫu --</option>
          <?php if (!empty($templates) && is_array($templates)) : ?>
            <?php foreach ($templates as $t) : ?>
              <?php
                $tid = is_array($t) ? ($t['id'] ?? '') : ($t->id ?? '');
                $tname = is_array($t) ? ($t['name'] ?? '') : ($t->name ?? '');
                $tsub = is_array($t) ? ($t['subject'] ?? '') : ($t->subject ?? '');
              ?>
              <option value="<?php echo html_escape($tid); ?>" data-subject="<?php echo html_escape($tsub); ?>">
                #<?php echo html_escape($tid); ?> - <?php echo html_escape($tname); ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <small class="text-info">Chọn template → tự nạp Subject + HTML. (Bạn vẫn có thể chỉnh sửa trước khi gửi)</small>
      </div>
    </div>

    <div class="col-md-6">
      <div class="form-group">
        <label for="<?php echo $id_subject; ?>">Tiêu đề (Subject) – tuỳ chọn</label>
        <input id="<?php echo $id_subject; ?>" type="text" class="form-control js-mail-subject" name="subject" placeholder="Để trống → dùng subject của template">
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="form-group">
        <button type="button" id="<?php echo $id_btn_load; ?>" class="btn btn-default btn-sm js-load-template">
          <i class="fa fa-download"></i> Nạp nội dung từ template
        </button>
        <button type="button" id="<?php echo $id_btn_copy; ?>" class="btn btn-default btn-sm js-copy-html">
          <i class="fa fa-copy"></i> Copy HTML
        </button>
        <a href="javascript:void(0)" class="text-info js-open-preview" style="margin-left: 10px;">Preview dùng dữ liệu demo hoặc SV/đơn tuyển bạn chọn.</a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="form-group">
        <label for="<?php echo $id_html; ?>">Nội dung Email (HTML)</label>
        <textarea id="<?php echo $id_html; ?>" class="form-control js-mail-html" name="html" rows="16" style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;"></textarea>
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-group">
        <label>Preview (render token demo)</label>
        <div id="<?php echo $id_preview; ?>" class="well well-sm js-previewbox" style="min-height: 410px; background: #fff; overflow:auto;">Chưa xem trước...</div>
      </div>
    </div>
  </div>

  <div class="form-group">
    <label>Tokens gợi ý</label>
    <div>
      <?php
        $tokens = $token_catalog ?? [];
        if (!is_array($tokens)) $tokens = [];
        foreach ($tokens as $tk => $label) {
          echo '<span class="label label-info" style="display:inline-block;margin:2px;cursor:pointer;" data-token="' . html_escape($tk) . '">' . html_escape($tk) . '</span> ';
        }
      ?>
    </div>
    <small class="text-muted">Click token để copy vào clipboard.</small>
  </div>
</div>
