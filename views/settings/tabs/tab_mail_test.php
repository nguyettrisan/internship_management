<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="im-card">
  <h5><i class="fa fa-envelope"></i> Test Mail</h5>

  <div class="row">
    <div class="col-md-6">
      <label class="control-label">Email nhận test</label>
      <input type="email" id="im_mail_to" class="form-control" placeholder="example@gmail.com">
    </div>

    <div class="col-md-12 mtop15">
      <label class="control-label">Nội dung mail test</label>
      <textarea id="im_mail_msg" class="form-control" rows="6">Test gửi mail từ module Internship</textarea>
    </div>

    <div class="col-md-12 mtop15">
      <button type="button" class="btn btn-warning" id="imBtnMail">
        <i class="fa fa-paper-plane"></i> Gửi Mail Test
      </button>
      <span id="imMailLoading" class="text-muted mleft10" style="display:none;">
        <i class="fa fa-spinner fa-spin"></i> Đang gửi...
      </span>
    </div>

    <div class="col-md-12 mtop15">
      <label class="control-label">Kết quả</label>
      <pre id="im_mail_result" class="im-pre"></pre>
    </div>
  </div>
</div>

<script>
(function(){
  function safeJson(txt){
    try { return JSON.stringify(txt, null, 2); } catch(e){ return String(txt||''); }
  }

  $('#imBtnMail').on('click', function(){
    var to = $('#im_mail_to').val().trim();
    var msg = $('#im_mail_msg').val();

    if(!to){
      alert('Vui lòng nhập email nhận test.');
      return;
    }

    $('#imMailLoading').show();
    $('#im_mail_result').text('');

    $.post(imAdminUrl + 'internship_management/internship_settings/test_mail', { email: to, message: msg }, function(res){
      $('#im_mail_result').text(safeJson(res));
      if(res && res.status === 'success') alert(res.message || 'Gửi mail OK');
    }, 'json').fail(function(xhr){
      $('#im_mail_result').text('ERROR: ' + (xhr.responseText || xhr.statusText));
    }).always(function(){
      $('#imMailLoading').hide();
    });
  });
})();
</script>
