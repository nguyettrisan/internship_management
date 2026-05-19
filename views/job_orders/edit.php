<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
<div class="content">
<div class="panel_s">
<div class="panel-body">

<h4 class="bold"><i class="fa fa-edit"></i> Cập Nhật Đơn Tuyển</h4>
<hr>

<?php echo form_open(); ?>

<?php $this->load->view('internship_management/job_orders/form'); ?>

<div class="text-right mtop20">
    <button class="btn btn-success"><i class="fa fa-save"></i> Lưu Thay Đổi</button>
</div>

<?php echo form_close(); ?>

<!-- Floating AI Translate widget (JP → VI) -->
<div id="imAiTranslateWidget" class="im-aiw">
  <div class="im-aiw-header">
    <strong><i class="fa fa-language"></i> Dịch AI</strong>
    <a href="#" class="im-aiw-toggle" onclick="return false;"><i class="fa fa-chevron-down"></i></a>
  </div>
  <div class="im-aiw-body">
    <div class="checkbox mtop5 mbot10">
      <input type="checkbox" id="im_ai_only_empty" checked>
      <label for="im_ai_only_empty">Chỉ dịch ô VI đang trống</label>
    </div>
    <button type="button" class="btn btn-info btn-sm" id="im_ai_translate_btn">
      <i class="fa fa-magic"></i> Dịch JP → VI
    </button>
    <span class="text-muted" id="im_ai_translate_status" style="margin-left:8px;"></span>
  </div>
</div>

<style>

/* =====================================================
   IFK AI WIDGET
   ===================================================== */

.im-aiw{
  position:fixed;
  right:20px;
  bottom:20px;
  z-index:9999;
  width:280px;
  background:#ffffff;
  border:1px solid rgba(0,50,90,.15);
  border-radius:14px;
  box-shadow:0 10px 30px rgba(0,50,90,.18);
  overflow:hidden;
  font-family:inherit;
  transition:.25s ease;
}

/* ===== HEADER ===== */

.im-aiw-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:12px 14px;
  background:linear-gradient(135deg,#00325a,#00a6dc);
  color:#ffffff;
  font-weight:600;
  font-size:14px;
}

.im-aiw-header a{
  color:#ffffff;
  opacity:.85;
  text-decoration:none;
  transition:.2s ease;
}

.im-aiw-header a:hover{
  opacity:1;
}

/* ===== BODY ===== */

.im-aiw-body{
  padding:12px 14px;
  background:#ffffff;
  font-size:13px;
  color:#1e293b;
}

/* collapsed state */

.im-aiw.collapsed .im-aiw-body{
  display:none;
}

/* subtle hover lift */

.im-aiw:hover{
  transform:translateY(-2px);
  box-shadow:0 14px 34px rgba(0,50,90,.22);
}

</style>

<?php init_tail(); ?>

<script>
(function(){
  'use strict';
  function waitForJQ(cb){
    // Support Perfex setups where window.jQuery may be unset (noConflict) but window.$ still exists
    if (typeof window.jQuery === 'function') return cb(window.jQuery);
    if (typeof window.$ === 'function') return cb(window.$);
    setTimeout(function(){ waitForJQ(cb); }, 50);
  }
  waitForJQ(function($){
    function setStatus(txt){ $('#im_ai_translate_status').text(txt || ''); }
        function collectPairs(){
          var onlyEmpty = $('#im_ai_only_empty').is(':checked');
          var fields = {};
    
          $('input[name$="_jp"], textarea[name$="_jp"], select[name$="_jp"]').each(function(){
            var $jp = $(this);
            var jpName = $jp.attr('name');
            if(!jpName) return;
            var base = jpName.replace(/_jp$/,'');
            var viName = base + '_vi';
            var $vi = $('[name="'+viName+'"]');
            if($vi.length === 0) return;
            var jpVal = ($jp.val() || '').toString().trim();
            if(jpVal === '') return;
            var viVal = ($vi.val() || '').toString().trim();
            if(onlyEmpty && viVal !== '') return;
            fields[viName] = jpVal;
          });
    
          $('input[name], textarea[name], select[name]').each(function(){
            var $jp = $(this);
            if($jp.is('[type="hidden"]')) return;
            var name = $jp.attr('name');
            if(!name) return;
            if(/_vi$/.test(name) || /_jp$/.test(name)) return;
            var viName = name + '_vi';
            var $vi = $('[name="'+viName+'"]');
            if($vi.length === 0) return;
            var jpVal = ($jp.val() || '').toString().trim();
            if(jpVal === '') return;
            var viVal = ($vi.val() || '').toString().trim();
            if(onlyEmpty && viVal !== '') return;
            if(typeof fields[viName] === 'undefined') fields[viName] = jpVal;
          });
    
          return fields;
        }
        function applyTranslated(map){
          if(!map) return;
          Object.keys(map).forEach(function(viName){
            var $vi = $('[name="'+viName+'"]');
            if($vi.length === 0) return;
            $vi.val(map[viName]);
            $vi.trigger('change');
          });
        }
        $(function(){
          $('.im-aiw-toggle').on('click', function(){ $('#imAiTranslateWidget').toggleClass('collapsed'); });
          $('#im_ai_translate_btn').on('click', function(){
            var fields = collectPairs();
            var keys = Object.keys(fields);
            if(keys.length === 0){ setStatus('Không có ô cần dịch.'); return; }
            setStatus('Đang dịch ' + keys.length + ' ô...');
            $(this).prop('disabled', true);
            $.post(admin_url + 'internship_management/internship_job_orders/ai_translate_fields', { fields: JSON.stringify(fields) })
              .done(function(resp){
                try{ if(typeof resp === 'string') resp = JSON.parse(resp); }catch(e){}
                if(resp && resp.success){ applyTranslated(resp.data || {}); setStatus('Đã dịch xong.'); }
                else{ setStatus((resp && resp.message) ? resp.message : 'Không dịch được.'); }
              })
              .fail(function(){ setStatus('Lỗi mạng/server.'); })
              .always(function(){ $('#im_ai_translate_btn').prop('disabled', false); setTimeout(function(){ setStatus(''); }, 3500); });
          });
        });
      
  });
})();
</script>
