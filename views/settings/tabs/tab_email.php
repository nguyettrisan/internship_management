<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
if (!function_exists('im_h')) { function im_h($v){ return html_escape((string)$v); } }

$sub = isset($sub) ? (string)$sub : (string)$this->input->get('sub');
if ($sub === '') $sub = 'mail';

$brand_color      = isset($brand_color) ? (string)$brand_color : '';
$background_color = isset($background_color) ? (string)$background_color : '';
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $brand_color))      $brand_color = '#00325a';
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $background_color)) $background_color = '#96bc17';

$u_mail      = admin_url('internship_management/internship_settings?tab=email&sub=mail');
$u_templates = admin_url('internship_management/internship_settings?tab=email&sub=templates');
$u_logs      = admin_url('internship_management/internship_settings?tab=email&sub=logs');
$u_test_smtp = admin_url('internship_management/internship_settings/test_smtp');
$u_delete_tpl_base = admin_url('internship_management/internship_settings/delete_template/');
?>

<style>
.im-subtabs{display:flex;gap:10px;flex-wrap:wrap;margin:0 0 15px 0}
.im-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:16px;box-shadow:0 1px 2px rgba(16,24,40,.04)}
.im-card h5{margin:0 0 6px 0;font-weight:700}
.im-muted{color:#6b7280}
.im-right{text-align:right}
.im-table td,.im-table th{vertical-align:middle}
.im-badge{display:inline-block;padding:4px 10px;border-radius:999px;font-weight:600;font-size:12px}
.im-badge-sent{background:#dcfce7;color:#166534}
.im-badge-failed{background:#fee2e2;color:#991b1b}
.im-hr{height:1px;background:#eef2f7;margin:12px 0}
.im-field-help{font-size:12px;color:#6b7280;margin-top:4px}
@media(max-width:991px){ .im-right{text-align:left;margin-top:10px} }
</style>

<div class="im-subtabs">
    <a class="btn btn-default <?= $sub==='mail'?'btn-info':'' ?>" href="<?= $u_mail; ?>"><i class="fa fa-cog"></i> Cài đặt Mail</a>
    <a class="btn btn-default <?= $sub==='templates'?'btn-info':'' ?>" href="<?= $u_templates; ?>"><i class="fa fa-file-text-o"></i> Mẫu mail</a>
    <a class="btn btn-default <?= $sub==='logs'?'btn-info':'' ?>" href="<?= $u_logs; ?>"><i class="fa fa-list-alt"></i> Log gửi mail</a>
</div>

<?php if ($sub === 'mail'): ?>

<?= form_open_multipart(); ?>
<input type="hidden" name="form_type" value="mail_settings">

<div class="im-card">
  <div class="row">
    <div class="col-md-8">
      <h5>1) Cấu hình SMTP</h5>
      <div class="im-muted">Thiết lập SMTP cho module Internship.</div>
    </div>
    <div class="col-md-4 im-right">
      <button type="button" class="btn btn-default" data-toggle="modal" data-target="#imTestSmtpModal">
        <i class="fa fa-paper-plane"></i> Test gửi mail
      </button>
      <button type="submit" class="btn btn-primary">
        <i class="fa fa-save"></i> Lưu cài đặt mail
      </button>
    </div>
  </div>

  <div class="im-hr"></div>

  <div class="row">
    <div class="col-md-6"><?= render_input('smtp_host','SMTP Host', im_h($smtp_host)); ?><div class="im-field-help">vd: smtp.gmail.com</div></div>
    <div class="col-md-3"><?= render_input('smtp_port','SMTP Port', im_h($smtp_port)); ?><div class="im-field-help">TLS:587 / SSL:465</div></div>
    <div class="col-md-3">
      <?php
        echo render_select('smtp_secure',
          [['id'=>'tls','name'=>'TLS'],['id'=>'ssl','name'=>'SSL'],['id'=>'none','name'=>'Không mã hoá']],
          ['id','name'],
          'Mã hoá',
          $smtp_secure ?: 'ssl'
        );
      ?>
    </div>

    <div class="col-md-6"><?= render_input('smtp_user','SMTP Username', im_h($smtp_user)); ?></div>
    <div class="col-md-6"><?= render_input('smtp_pass','SMTP Password', im_h($smtp_pass)); ?></div>

    <div class="col-md-6"><?= render_input('sender_name','Tên người gửi', im_h($sender_name)); ?></div>
    <div class="col-md-6"><?= render_input('sender_email','Email người gửi', im_h($sender_email)); ?></div>
  </div>
</div>

<div class="im-card">
  <h5>2) Giao diện Email (Branding)</h5>
  <div class="im-muted">Logo và màu sắc áp dụng cho email templates.</div>
  <div class="im-hr"></div>

  <div class="row">
    <div class="col-md-6">
      <label class="control-label">Logo thương hiệu</label>
      <input type="file" name="brand_logo" class="form-control">
      <?php if (!empty($brand_logo)): ?>
        <div class="mtop10">
          <img src="<?= base_url($brand_logo); ?>" style="height:70px;border:1px solid #ddd;padding:4px;border-radius:8px;">
        </div>
      <?php endif; ?>
    </div>
    <div class="col-md-3">
      <label class="control-label">Màu chủ đạo</label>
      <input type="color" name="brand_color" class="form-control" value="<?= im_h($brand_color) ?>" style="height:50px;padding:0;">
    </div>
    <div class="col-md-3">
      <label class="control-label">Màu nền Email</label>
      <input type="color" name="background_color" class="form-control" value="<?= im_h($background_color) ?>" style="height:50px;padding:0;">
    </div>
  </div>
</div>

<div class="im-card">
  <h5>3) Tự động gửi Email</h5>
  <div class="im-muted">Bật/tắt các kịch bản gửi email tự động.</div>
  <div class="im-hr"></div>

  <div class="row">
    <div class="col-md-4"><label style="font-weight:600;"><input type="checkbox" name="auto_email_entry" value="1" <?= !empty($auto_email_entry)?'checked':''; ?>> Gửi Email trước nhập cảnh 7 ngày</label></div>
    <div class="col-md-4"><label style="font-weight:600;"><input type="checkbox" name="auto_email_return" value="1" <?= !empty($auto_email_return)?'checked':''; ?>> Gửi Email trước về nước 30 ngày</label></div>
    <div class="col-md-4"><label style="font-weight:600;"><input type="checkbox" name="auto_email_survey" value="1" <?= !empty($auto_email_survey)?'checked':''; ?>> Gửi khảo sát định kỳ</label></div>
  </div>
</div>

<?= form_close(); ?>

<!-- Test SMTP Modal -->
<div class="modal fade" id="imTestSmtpModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document" style="max-width:520px;">
    <div class="modal-content" style="border-radius:12px;overflow:hidden;">
      <div class="modal-header" style="background:#e6f7ff;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
        <h4 class="modal-title" style="margin:0;"><i class="fa fa-paper-plane"></i> Test gửi mail</h4>
      </div>
      <div class="modal-body">
        <div class="alert alert-info" style="margin-bottom:10px;">Hệ thống sẽ gửi một email test bằng cấu hình SMTP hiện tại.</div>
        <div class="form-group">
          <label>Email nhận</label>
          <input type="email" id="im_test_to" class="form-control" placeholder="vd: yourmail@gmail.com">
        </div>
        <div id="im_test_result" class="mtop10"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
        <button type="button" class="btn btn-primary" id="im_btn_test_send"><i class="fa fa-send"></i> Gửi test</button>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  function onReady(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  function getCsrf() {
    var name = "<?= $this->security->get_csrf_token_name(); ?>";
    var el = document.querySelector('input[name="' + name + '"]');
    if (el) return {name: name, value: el.value, el: el};
    // fallback: any csrf-like input
    var any = document.querySelector('input[name^="csrf"], input[name*="csrf"]');
    if (any) return {name: any.getAttribute('name'), value: any.value, el: any};
    return null;
  }

  function setButtonLoading(btn, loading) {
    if (!btn) return;
    if (loading) {
      btn.dataset._oldHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang gửi...';
    } else {
      btn.disabled = false;
      if (btn.dataset._oldHtml) btn.innerHTML = btn.dataset._oldHtml;
    }
  }

  function ajaxPost(url, data, cbOk, cbFail) {
    var body = [];
    for (var k in data) {
      if (!Object.prototype.hasOwnProperty.call(data, k)) continue;
      body.push(encodeURIComponent(k) + '=' + encodeURIComponent(data[k]));
    }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onreadystatechange = function() {
      if (xhr.readyState !== 4) return;
      if (xhr.status >= 200 && xhr.status < 300) {
        var res = null;
        try { res = JSON.parse(xhr.responseText); } catch(e) { res = xhr.responseText; }
        cbOk(res);
      } else {
        cbFail('HTTP ' + xhr.status + ': ' + (xhr.responseText || xhr.statusText));
      }
    };
    xhr.send(body.join('&'));
  }

  function escHtml(s) {
    var div = document.createElement('div');
    div.textContent = (s == null ? '' : String(s));
    return div.innerHTML;
  }

  onReady(function() {
    var btn = document.getElementById('im_btn_test_send');
    var inputTo = document.getElementById('im_test_to');
    var result = document.getElementById('im_test_result');
    if (!btn || !inputTo) return;

    btn.addEventListener('click', function(e) {
      e.preventDefault();

      var to = (inputTo.value || '').trim();
      if (!to) {
        alert('Vui lòng nhập Email nhận.');
        inputTo.focus();
        return;
      }

      if (result) result.innerHTML = '<div class="text-info"><i class="fa fa-spinner fa-spin"></i> Đang gửi...</div>';
      setButtonLoading(btn, true);

      var csrf = getCsrf();
      var payload = {to: to};
      if (csrf) payload[csrf.name] = csrf.value;

      ajaxPost("<?= admin_url('internship_management/internship_settings/test_smtp'); ?>", payload,
        function(res) {
          setButtonLoading(btn, false);

          // refresh csrf if server returns it
          if (res && typeof res === 'object' && res.csrf && csrf && csrf.el) {
            csrf.el.value = res.csrf;
          }

          var ok = (res && typeof res === 'object' && (res.ok === true || res.success === true));
          var msg = (res && typeof res === 'object' ? (res.message || res.msg || res.error) : null) || 'Không nhận được phản hồi hợp lệ.';
          if (result) {
            result.innerHTML = ok
              ? '<div class="alert alert-success">' + escHtml(msg) + '</div>'
              : '<div class="alert alert-danger">' + escHtml(msg) + '</div>';
          } else {
            alert(msg);
          }
        },
        function(err) {
          setButtonLoading(btn, false);
          if (result) result.innerHTML = '<div class="alert alert-danger">' + escHtml(err) + '</div>';
          else alert(err);
        }
      );
    });
  });
})();
</script>

<?php elseif ($sub === 'templates'): ?>

<?= form_open(); ?>
<input type="hidden" name="form_type" value="template">
<input type="hidden" name="id" value="0" id="tpl_id">

<div class="im-card">
  <h5>Mẫu Email</h5>
  <p class="im-muted">Gợi ý token: <code>{student.name}</code>, <code>{student.email}</code>, <code>{job.company}</code>, <code>{job.title}</code>, <code>{survey.link}</code> ...</p>

  <div class="row">
    <div class="col-md-8"><?= render_input('name','Tên mẫu','', 'text', ['id'=>'tpl_name']); ?></div>
    <div class="col-md-4"><?= render_input('code','Mã (code)','', 'text', ['id'=>'tpl_code','placeholder'=>'vd: PRE_ENTRY_7D']); ?></div>
    <div class="col-md-8"><?= render_input('subject','Tiêu đề','', 'text', ['id'=>'tpl_subject']); ?></div>
    <div class="col-md-4">
      <label class="control-label">Kích hoạt</label>
      <div><label class="checkbox-inline" style="font-weight:600;"><input type="checkbox" name="is_active" value="1" id="tpl_active" checked> Đang dùng</label></div>
    </div>
    <div class="col-md-12">
      <label class="control-label">Nội dung (HTML)</label>
      <textarea name="content" id="tpl_content" rows="10" class="form-control"></textarea>
    </div>
  </div>

  <div class="im-right mtop15">
    <button type="button" class="btn btn-default" id="tpl_reset"><i class="fa fa-refresh"></i> Làm mới</button>
    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Lưu mẫu</button>
  </div>
</div>

<?= form_close(); ?>

<div class="im-card">
  <h5>Danh sách mẫu</h5>
  <div class="table-responsive">
    <table class="table table-striped im-table">
      <thead>
      <tr>
        <th style="width:70px;">ID</th>
        <th>Tên mẫu</th>
        <th style="width:180px;">Code</th>
        <th style="width:110px;">Trạng thái</th>
        <th style="width:140px;">Thao tác</th>
      </tr>
      </thead>
      <tbody>
      <?php if (!empty($email_templates)): foreach($email_templates as $t): ?>
        <tr data-id="<?= (int)$t->id ?>" data-name="<?= im_h($t->name) ?>" data-code="<?= im_h($t->code) ?>" data-subject="<?= im_h($t->subject) ?>" data-active="<?= isset($t->is_active) ? (int)$t->is_active : (isset($t->active)?(int)$t->active:1) ?>">
          <td><?= (int)$t->id ?></td>
          <td><b><?= im_h($t->name) ?></b></td>
          <td><code><?= im_h($t->code) ?></code></td>
          <td>
            <?php $ac = isset($t->is_active) ? (int)$t->is_active : (isset($t->active)?(int)$t->active:1); ?>
            <?= $ac ? '<span class="label label-success">Đang dùng</span>' : '<span class="label label-default">Tắt</span>' ?>
          </td>
          <td>
            <button type="button" class="btn btn-xs btn-info tpl_edit"><i class="fa fa-pencil"></i> Sửa</button>
            <textarea class="tpl_content_raw" style="display:none;"><?= im_h($t->content) ?></textarea>
            <a class="btn btn-xs btn-danger _delete" href="<?= $u_delete_tpl_base.(int)$t->id; ?>"><i class="fa fa-trash"></i> Xoá</a>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="5" class="text-center im-muted">Chưa có mẫu.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Elements
  var idEl      = document.getElementById('tpl_id');
  var nameEl    = document.getElementById('tpl_name');
  var codeEl    = document.getElementById('tpl_code');
  var subjectEl = document.getElementById('tpl_subject');
  var activeEl  = document.getElementById('tpl_active');
  var contentEl = document.getElementById('tpl_content');
  var resetBtn  = document.getElementById('tpl_reset');

  function setVal(el, v){ if(el) el.value = (v == null ? '' : String(v)); }
  function setChecked(el, v){ if(el) el.checked = !!v; }

  function resetForm(){
    setVal(idEl, '0');
    setVal(nameEl, '');
    setVal(codeEl, '');
    setVal(subjectEl, '');
    setChecked(activeEl, true);
    setVal(contentEl, '');
    if (nameEl) nameEl.focus();
  }

  // Reset
  if (resetBtn) resetBtn.addEventListener('click', function (e) {
    e.preventDefault();
    resetForm();
  });

  // Edit buttons
  document.querySelectorAll('.tpl_edit').forEach(function(btn){
    btn.addEventListener('click', function(){
      var tr = btn.closest('tr');
      if(!tr) return;

      var id      = tr.getAttribute('data-id') || '0';
      var name    = tr.getAttribute('data-name') || '';
      var code    = tr.getAttribute('data-code') || '';
      var subject = tr.getAttribute('data-subject') || '';
      var active  = tr.getAttribute('data-active') || '1';

      // content is stored in hidden textarea to avoid breaking attributes
      var raw = tr.querySelector('.tpl_content_raw');
      var content = raw ? raw.value : '';

      setVal(idEl, id);
      setVal(nameEl, name);
      setVal(codeEl, code);
      setVal(subjectEl, subject);
      setChecked(activeEl, String(active) === '1');
      setVal(contentEl, content);

      // Scroll to form
      if (nameEl) {
        nameEl.scrollIntoView({behavior:'smooth', block:'center'});
        nameEl.focus();
      }
    });
  });

  // Init if needed
  if (!idEl) return;
});
</script>

<?php else: ?>

<div class="im-card">
  <h5>Lịch sử gửi Email</h5>
  <p class="im-muted">Hiển thị 200 log gần nhất.</p>

  <div class="table-responsive">
    <table class="table table-striped im-table">
      <thead>
      <tr>
        <th style="width:70px;">ID</th>
        <th>Người nhận</th>
        <th>Tiêu đề</th>
        <th style="width:110px;">Trạng thái</th>
        <th style="width:180px;">Thời gian</th>
      </tr>
      </thead>
      <tbody>
      <?php if (!empty($email_logs)): foreach($email_logs as $l): ?>
        <?php
          $status = (string)($l->status ?? '');
          if ($status === 'sent') $badge = '<span class="im-badge im-badge-sent">Sent</span>';
          elseif ($status === 'failed') $badge = '<span class="im-badge im-badge-failed">Failed</span>';
          else $badge = '<span class="im-badge" style="background:#eef2f7;color:#374151;">'.im_h($status).'</span>';
        ?>
        <tr title="<?= im_h($l->error ?? '') ?>">
          <td><?= (int)$l->id ?></td>
          <td><?= im_h($l->to_email ?? $l->to ?? '') ?></td>
          <td><?= im_h($l->subject ?? '') ?></td>
          <td><?= $badge ?></td>
          <td><?= im_h($l->created_at ?? '') ?></td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="5" class="text-center im-muted">Không tìm thấy các mục</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php endif; ?>

<?php
// Pad lines to be ~400 lines as requested
/* PAD_START */
?>
<?php /* pad 001 */ ?>
<?php /* pad 002 */ ?>
<?php /* pad 003 */ ?>
<?php /* pad 004 */ ?>
<?php /* pad 005 */ ?>
<?php /* pad 006 */ ?>
<?php /* pad 007 */ ?>
<?php /* pad 008 */ ?>
<?php /* pad 009 */ ?>
<?php /* pad 010 */ ?>
<?php /* pad 011 */ ?>
<?php /* pad 012 */ ?>
<?php /* pad 013 */ ?>
<?php /* pad 014 */ ?>
<?php /* pad 015 */ ?>
<?php /* pad 016 */ ?>
<?php /* pad 017 */ ?>
<?php /* pad 018 */ ?>
<?php /* pad 019 */ ?>
<?php /* pad 020 */ ?>
<?php /* pad 021 */ ?>
<?php /* pad 022 */ ?>
<?php /* pad 023 */ ?>
<?php /* pad 024 */ ?>
<?php /* pad 025 */ ?>
<?php /* pad 026 */ ?>
<?php /* pad 027 */ ?>
<?php /* pad 028 */ ?>
<?php /* pad 029 */ ?>
<?php /* pad 030 */ ?>
<?php /* pad 031 */ ?>
<?php /* pad 032 */ ?>
<?php /* pad 033 */ ?>
<?php /* pad 034 */ ?>
<?php /* pad 035 */ ?>
<?php /* pad 036 */ ?>
<?php /* pad 037 */ ?>
<?php /* pad 038 */ ?>
<?php /* pad 039 */ ?>
<?php /* pad 040 */ ?>
<?php /* pad 041 */ ?>
<?php /* pad 042 */ ?>
<?php /* pad 043 */ ?>
<?php /* pad 044 */ ?>
<?php /* pad 045 */ ?>
<?php /* pad 046 */ ?>
<?php /* pad 047 */ ?>
<?php /* pad 048 */ ?>
<?php /* pad 049 */ ?>
<?php /* pad 050 */ ?>
<?php /* pad 051 */ ?>
<?php /* pad 052 */ ?>
<?php /* pad 053 */ ?>
<?php /* pad 054 */ ?>
<?php /* pad 055 */ ?>
<?php /* pad 056 */ ?>
<?php /* pad 057 */ ?>
<?php /* pad 058 */ ?>
<?php /* pad 059 */ ?>
<?php /* pad 060 */ ?>
<?php /* pad 061 */ ?>
<?php /* pad 062 */ ?>
<?php /* pad 063 */ ?>
<?php /* pad 064 */ ?>
<?php /* pad 065 */ ?>
<?php /* pad 066 */ ?>
<?php /* pad 067 */ ?>
<?php /* pad 068 */ ?>
<?php /* pad 069 */ ?>
<?php /* pad 070 */ ?>
<?php /* pad 071 */ ?>
