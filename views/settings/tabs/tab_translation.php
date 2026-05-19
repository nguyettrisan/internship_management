<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="im-card">
  <h5><i class="fa fa-language"></i> Dịch tự động (JP → VI)</h5>

  <div class="row">
    <div class="col-md-6">
      <label class="control-label">Tiếng Nhật</label>
      <textarea id="im_jp_text" class="form-control" rows="8" placeholder="Nhập tiếng Nhật..."></textarea>
    </div>
    <div class="col-md-6">
      <label class="control-label">Tiếng Việt</label>
      <textarea id="im_vi_text" class="form-control" rows="8" placeholder="Kết quả dịch..."></textarea>
    </div>

    <div class="col-md-12 mtop15">
      <button type="button" class="btn btn-info" id="imBtnTranslate">
        <i class="fa fa-language"></i> Dịch sang Tiếng Việt
      </button>
      <span id="imTransLoading" class="text-muted mleft10" style="display:none;">
        <i class="fa fa-spinner fa-spin"></i> Đang dịch...
      </span>
      <div class="im-note">Sử dụng Google Translate API Key ở tab Cấu hình chung.</div>
    </div>
  </div>
</div>

<script>
(function(){
  $('#imBtnTranslate').on('click', function(){
    var jp = $('#im_jp_text').val().trim();
    if(!jp){
      alert('Vui lòng nhập tiếng Nhật để dịch.');
      return;
    }
    $('#imTransLoading').show();

    $.post(imAdminUrl + 'internship_management/internship_settings/test_translation', { text: jp }, function(res){
      if(res && typeof res.translated !== 'undefined'){
        $('#im_vi_text').val(res.translated);
      }else{
        $('#im_vi_text').val(JSON.stringify(res || {}, null, 2));
      }
    }, 'json').fail(function(xhr){
      alert('Dịch lỗi: ' + (xhr.responseText || xhr.statusText));
    }).always(function(){
      $('#imTransLoading').hide();
    });
  });
})();
</script>
