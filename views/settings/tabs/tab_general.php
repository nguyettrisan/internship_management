<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
if (!function_exists('im_h')) {
  function im_h($v){ return html_escape($v ?? ''); }
}

$ep_ping_ai = admin_url('internship_management/internship_settings/test_api');
$ep_ping_tr = admin_url('internship_management/internship_settings/test_translate_api');

// IMPORTANT: submit phải về đúng index() controller
$form_action = admin_url('internship_management/internship_settings?tab=general');
?>

<style>
.im-card{ border:1px solid #e5e7eb; border-radius:14px; padding:18px; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,.04); margin-bottom:16px; }
.im-card .im-hd{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.im-card .im-hd h4{ margin:0; font-weight:700; font-size:16px; }
.im-card .im-sub{ margin:4px 0 0; color:#6b7280; font-size:12.5px; }
.im-actions{ display:flex; align-items:center; gap:10px; }
.im-btn-test{ border-radius:12px; padding:9px 14px; font-weight:600; }
.im-status{ display:inline-flex; align-items:center; gap:8px; padding:7px 10px; border-radius:999px; font-weight:600; font-size:12.5px; }
.im-status--muted{ background:#f3f4f6; color:#6b7280; }
.im-status--ok{ background:#ecfdf5; color:#047857; }
.im-status--bad{ background:#fef2f2; color:#b91c1c; }
.im-note{ margin-top:10px; color:#6b7280; font-size:12.5px; }
.im-grid-tight .form-group{ margin-bottom:12px; }
.im-savebar{ display:flex; justify-content:flex-end; margin-top:10px; }
</style>

<?= form_open($form_action, ['id'=>'imGeneralForm']); ?>
  <input type="hidden" name="form_type" value="general"/>

  <!-- GOOGLE AI -->
  <div class="im-card">
    <div class="im-hd">
      <div>
        <h4><i class="fa fa-google mright5"></i> Google AI (Gemini)</h4>
        <div class="im-sub">Cấu hình API Key & Model. Bấm Test để kiểm tra kết nối (ping).</div>
      </div>

      <div class="im-actions">
        <span id="imPingAIResult" class="im-status im-status--muted">
          <i class="fa fa-info-circle"></i> Chưa kiểm tra
        </span>
        <button type="button" class="btn btn-info im-btn-test" id="imBtnPingAI">
          <i class="fa fa-plug"></i> Test API
        </button>
      </div>
    </div>

    <div class="row im-grid-tight">
      <div class="col-md-8">
        <?= render_input('google_api_key', 'Google AI API Key', im_h($google_api_key)); ?>
      </div>
      <div class="col-md-4">
        <?= render_input('google_ai_model', 'Model', im_h($google_ai_model)); ?>
        <div class="im-sub">Ví dụ: <b>gemini-1.5-flash</b>, <b>gemini-1.5-pro</b>…</div>
      </div>
    </div>

    <div class="im-note">Lưu ý: API Key sẽ được ẩn trong audit log.</div>
  </div>

  <!-- GOOGLE TRANSLATE -->
  <div class="im-card">
    <div class="im-hd">
      <div>
        <h4><i class="fa fa-language mright5"></i> Google Translate</h4>
        <div class="im-sub">Cấu hình API Key. Bấm Test để dịch mẫu JP→VI (ping).</div>
      </div>

      <div class="im-actions">
        <span id="imPingTRResult" class="im-status im-status--muted">
          <i class="fa fa-info-circle"></i> Chưa kiểm tra
        </span>
        <button type="button" class="btn btn-info im-btn-test" id="imBtnPingTR">
          <i class="fa fa-check"></i> Test API
        </button>
      </div>
    </div>

    <div class="row im-grid-tight">
      <div class="col-md-8">
        <?= render_input('google_translate_api_key', 'Google Translate API Key', im_h($google_translate_api_key)); ?>
      </div>
    </div>
  </div>

  <div class="im-savebar">
    <button type="submit" class="btn btn-primary">
      <i class="fa fa-save"></i> Lưu cài đặt
    </button>
  </div>
<?= form_close(); ?>

<script>
(function () {
  var PING_AI = <?= json_encode($ep_ping_ai); ?>;
  var PING_TR = <?= json_encode($ep_ping_tr); ?>;

  function el(id){ return document.getElementById(id); }
  function setHtml(node, html){ if(node) node.innerHTML = html || ''; }
  function setClass(node, cls){ if(node) node.className = 'im-status ' + cls; }

  function get(url, ok, fail){
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
    xhr.onreadystatechange = function(){
      if(xhr.readyState !== 4) return;
      if(xhr.status >= 200 && xhr.status < 300) ok(xhr.responseText);
      else fail(xhr.responseText || ('HTTP ' + xhr.status));
    };
    xhr.send(null);
  }

  function renderBadge(out, html){
    var ok = (html || '').indexOf('text-success') !== -1 || (html || '').toLowerCase().indexOf('ok') !== -1;
    setClass(out, ok ? 'im-status--ok' : 'im-status--bad');
    var tmp = document.createElement('div'); tmp.innerHTML = html;
    var text = (tmp.textContent || tmp.innerText || '').trim();
    setHtml(out, (ok ? '<i class="fa fa-check"></i> ' : '<i class="fa fa-times"></i> ') + (text || 'Kết quả'));
  }

  var btnAI = el('imBtnPingAI'), outAI = el('imPingAIResult');
  if(btnAI){
    btnAI.addEventListener('click', function(e){
      e.preventDefault();
      setClass(outAI, 'im-status--muted');
      setHtml(outAI, '<i class="fa fa-spinner fa-spin"></i> Đang kiểm tra...');
      get(PING_AI, function(html){ renderBadge(outAI, html); },
                 function(err){ setClass(outAI,'im-status--bad'); setHtml(outAI,'<i class="fa fa-times"></i> Lỗi: '+err); });
    });
  }

  var btnTR = el('imBtnPingTR'), outTR = el('imPingTRResult');
  if(btnTR){
    btnTR.addEventListener('click', function(e){
      e.preventDefault();
      setClass(outTR, 'im-status--muted');
      setHtml(outTR, '<i class="fa fa-spinner fa-spin"></i> Đang kiểm tra...');
      get(PING_TR, function(html){ renderBadge(outTR, html); },
                 function(err){ setClass(outTR,'im-status--bad'); setHtml(outTR,'<i class="fa fa-times"></i> Lỗi: '+err); });
    });
  }
})();
</script>