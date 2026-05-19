<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="im-card">
  <h5><i class="fa fa-robot"></i> Test AI</h5>

  <div class="row">
    <div class="col-md-12">
      <label class="control-label">Nội dung test</label>
      <textarea id="im_ai_content" class="form-control" rows="6" placeholder="Nhập nội dung để test AI..."></textarea>
      <div class="im-note">Sử dụng OpenAI API Key ở tab Cấu hình chung.</div>
    </div>

    <div class="col-md-12 mtop15">
      <button type="button" class="btn btn-success" id="imBtnAiRun">
        <i class="fa fa-play"></i> Chạy AI
      </button>
      <span id="imAiLoading" class="text-muted mleft10" style="display:none;">
        <i class="fa fa-spinner fa-spin"></i> Đang xử lý...
      </span>
    </div>

    <div class="col-md-12 mtop15">
      <label class="control-label">Kết quả</label>
      <pre id="im_ai_result" class="im-pre"></pre>
    </div>
  </div>
</div>

<script>
(function(){
  function safeJson(txt){
    try { return JSON.stringify(txt, null, 2); } catch(e){ return String(txt||''); }
  }

  $('#imBtnAiRun').on('click', function(){
    var content = $('#im_ai_content').val().trim();
    if(!content){
      alert('Vui lòng nhập nội dung test AI.');
      return;
    }

    $('#imAiLoading').show();
    $('#im_ai_result').text('');

    $.post(imAdminUrl + 'internship_management/internship_settings/test_ai', { content: content }, function(res){
      $('#im_ai_result').text(safeJson(res));
    }, 'json').fail(function(xhr){
      $('#im_ai_result').text('ERROR: ' + (xhr.responseText || xhr.statusText));
    }).always(function(){
      $('#imAiLoading').hide();
    });
  });
})();
</script>
