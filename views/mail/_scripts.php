<script>
(function($){
  'use strict';

  function escHtml(str){
    return $('<div/>').text(str == null ? '' : String(str)).html();
  }

  function getScope(el){
    return $(el).closest('.mail-common');
  }

  function getPrefix(scope){
    return scope.data('prefix');
  }

  function getHtmlEl(scope){
    return scope.find('.js-mail-html');
  }

  function getSubjectEl(scope){
    return scope.find('.js-mail-subject');
  }

  function getTemplateSelect(scope){
    return scope.find('.js-template-select');
  }

  function getPreviewBox(scope){
    return scope.find('.js-previewbox');
  }

  function getSelectedStudentIdForPreview(prefix){
    if(prefix === 'students'){
      var ids = $('#students_student_ids').val() || [];
      return ids.length ? ids[0] : null;
    }

    // job tab: use first checked recipient, else null
    var v = $('#recipientsBox').find('input.recipient-check:checked:first').val();
    return v || null;
  }

  function refreshSendGuards(){
    // students
    var dryStudents = $('#dry_run_students').is(':checked');
    $('#students_confirm_send').prop('disabled', dryStudents);
    if(dryStudents) $('#students_confirm_send').val('');

    // job
    var dryJob = $('#dry_run_job').is(':checked');
    $('#job_confirm_send').prop('disabled', dryJob);
    if(dryJob) $('#job_confirm_send').val('');

    // enable/disable submit based on confirm and selection
    var canStudents = true;
    if(!dryStudents){
      canStudents = ($('#students_confirm_send').val() || '').trim().toUpperCase() === 'SEND';
    }
    // must have at least 1 student
    var sids = $('#students_student_ids').val() || [];
    if(sids.length < 1) canStudents = false;
    $('#btnSubmitStudents').prop('disabled', !canStudents);

    var canJob = true;
    if(!dryJob){
      canJob = ($('#job_confirm_send').val() || '').trim().toUpperCase() === 'SEND';
    }
    // must have recipients checked OR manual emails
    var picked = $('#recipientsBox').find('input.recipient-check:checked').length;
    var manual = ($('textarea[name="manual_emails"]').val() || '').trim();
    if(picked < 1 && manual === '') canJob = false;
    $('#btnSubmitJob').prop('disabled', !canJob);
  }

  function applyTemplateToScope(scope, tpl){
    if(!tpl) return;

    // subject only fill if empty
    var subjEl = getSubjectEl(scope);
    var curSubj = (subjEl.val() || '').trim();
    if(curSubj === '' && tpl.subject){
      subjEl.val(tpl.subject);
    }

    if(tpl.html){
      getHtmlEl(scope).val(tpl.html);
    }
  }

  function fetchTemplate(templateId, cb){
    if(!templateId){
      cb(null);
      return;
    }
    $.getJSON(window.IFK_MAIL.ajaxTemplateUrl + templateId, getCsrfData())
      .done(function(res){
        cb(res && res.ok ? res.data : null);
      })
      .fail(function(){ cb(null); });
  }

   function getCsrfData(){
    var inp = $("input[name=csrf_token_name]").first();
    if(inp.length){
      var o = {}; o[inp.attr("name")] = inp.val();
      return o;
    }
    return {};
  }

  function doPreview(prefix){
    var scope = $('.mail-common[data-prefix="'+prefix+'"]');
    var html = (getHtmlEl(scope).val() || '').trim();
    if(!html){
      getPreviewBox(scope).html('<div class="text-muted">Chưa có HTML để preview.</div>');
      return;
    }

    var payload = $.extend({}, getCsrfData(), {
      html: html,
      demo: 1,
      student_id: getSelectedStudentIdForPreview(prefix) || '',
      job_id: prefix === 'job' ? ($('#job_job_id').val() || '') : ''
    });

    getPreviewBox(scope).html('<div class="text-muted">Đang render preview...</div>');

    $.post(window.IFK_MAIL.ajaxPreviewUrl, payload)
      .done(function(res){
        if(typeof res === 'string'){
          try{ res = JSON.parse(res); }catch(e){}
        }
        if(res && res.ok){
          getPreviewBox(scope).html(res.html);
        } else {
          getPreviewBox(scope).html('<div class="text-danger">Preview lỗi: '+escHtml(res && res.msg ? res.msg : 'unknown')+'</div>');
        }
      })
      .fail(function(xhr){
        getPreviewBox(scope).html('<div class="text-danger">Preview lỗi (HTTP '+(xhr.status||0)+')</div>');
      });
  }

  function bindTokens(){
    $(document).on('click', '.mail-common [data-token]', function(){
      var tk = $(this).data('token');
      if(!tk) return;
      try {
        navigator.clipboard.writeText(tk);
      } catch(e) {}
    });
  }

  function bindTemplateActions(){
    // auto load template on change
    $(document).on('change', '.mail-common .js-template-select', function(){
      var scope = getScope(this);
      var tid = $(this).val();
      fetchTemplate(tid, function(tpl){
        applyTemplateToScope(scope, tpl);
        // refresh preview after apply
        doPreview(getPrefix(scope));
      });
    });

    // manual load button
    $(document).on('click', '.mail-common .js-load-template', function(){
      var scope = getScope(this);
      var tid = getTemplateSelect(scope).val();
      fetchTemplate(tid, function(tpl){
        applyTemplateToScope(scope, tpl);
        doPreview(getPrefix(scope));
      });
    });

    // copy html
    $(document).on('click', '.mail-common .js-copy-html', function(){
      var scope = getScope(this);
      var html = getHtmlEl(scope).val() || '';
      try{ navigator.clipboard.writeText(html); }catch(e){}
    });

    // open preview
    $(document).on('click', '.mail-common .js-open-preview', function(){
      var prefix = getPrefix(getScope(this));
      doPreview(prefix);
    });

    // live preview when editing
    var t = null;
    $(document).on('input', '.mail-common .js-mail-html', function(){
      var scope = getScope(this);
      clearTimeout(t);
      t = setTimeout(function(){ doPreview(getPrefix(scope)); }, 600);
    });
  }

  function bindStudents(){
    // selectpicker refresh guard
    try { $('.selectpicker').selectpicker('refresh'); } catch(e) {}

    // iCheck/custom checkbox may not trigger plain change reliably
    $('#dry_run_students').on('change click ifChanged', refreshSendGuards);
    $('#students_confirm_send').on('keyup change', refreshSendGuards);
    $('#students_student_ids').on('changed.bs.select', refreshSendGuards);

    $('#btnPreviewStudents').on('click', function(){
      doPreview('students');
    });

    $('#formSendStudents').on('submit', function(){
      refreshSendGuards();
      if($('#btnSubmitStudents').is(':disabled')) return false;
      return true;
    });
  }

  function bindRecipients(){
    function updatePicked(){
      var total = $('#recipientsBox').find('input.recipient-check').length;
      var picked = $('#recipientsBox').find('input.recipient-check:checked').length;
      $('#totalRecipients').text(total);
      $('#pickedRecipients').text(picked);
      refreshSendGuards();
    }

    $('#dry_run_job').on('change click ifChanged', refreshSendGuards);
    $('#job_confirm_send').on('keyup change', refreshSendGuards);

    $('#btnLoadRecipients').on('click', function(){
      var jobId = $('#job_job_id').val();
      if(!jobId){
        alert('Vui lòng chọn đơn tuyển');
        return;
      }

      $('#recipientsBox').html('<div class="text-muted">Đang load danh sách...</div>');

      $.getJSON(window.IFK_MAIL.ajaxRecipientsUrl, $.extend({}, getCsrfData(), {job_id: jobId}))
        .done(function(res){
          if(!res || !res.ok){
            $('#recipientsBox').html('<div class="text-danger">Không load được recipients.</div>');
            return;
          }

          var items = res.data || [];
          if(items.length < 1){
            $('#recipientsBox').html('<div class="text-muted">Không có recipients...</div>');
            updatePicked();
            return;
          }

          var html = '';
          for(var i=0;i<items.length;i++){
            var it = items[i] || {};
            var id = it.student_id || it.id || '';
            var name = it.full_name || it.name || '';
            var email = it.email || '';
            var line = (name ? name : '') + (email ? ' - ' + email : '');
            var cid = 'rcp_' + i;
            html += '<div class="checkbox"><input class="recipient-check" type="checkbox" id="'+cid+'" name="recipient_ids[]" value="'+escHtml(id)+'"> <label for="'+cid+'">'+escHtml(line)+'</label></div>';
          }

          $('#recipientsBox').html(html);
          updatePicked();

          // ensure template select + preview on job tab works
          try { $('.selectpicker').selectpicker('refresh'); } catch(e) {}
        })
        .fail(function(xhr){
          $('#recipientsBox').html('<div class="text-danger">Không load được recipients (HTTP '+(xhr.status||0)+').</div>');
        });
    });

    // pick all
    $('#chk_all').on('change', function(){
      var on = $(this).is(':checked');
      $('#recipientsBox').find('input.recipient-check').prop('checked', on);
      updatePicked();
    });

    // checkbox change
    $('#recipientsBox').on('change', 'input.recipient-check', updatePicked);

    // filter
    $('#filterRecipients').on('keyup', function(){
      var q = ($(this).val() || '').toLowerCase();
      $('#recipientsBox .checkbox').each(function(){
        var t = $(this).text().toLowerCase();
        $(this).toggle(t.indexOf(q) !== -1);
      });
    });

    $('#btnPreviewJob').on('click', function(){
      doPreview('job');
    });

    $('#job_job_id').on('change', function(){
      // clear recipients on job change
      $('#recipientsBox').html('<div class="text-muted">Chưa load recipients...</div>');
      $('#chk_all').prop('checked', false);
      updatePicked();
    });

    $('#formSendJob').on('submit', function(){
      refreshSendGuards();
      if($('#btnSubmitJob').is(':disabled')) return false;
      return true;
    });

    // initial
    updatePicked();
  }

  $(function(){
    bindTokens();
    bindTemplateActions();
    bindStudents();
    bindRecipients();
    refreshSendGuards();

    // if hash tab
    var hash = window.location.hash || '';
    if(hash === '#tab_job' || hash === '#tab_students'){
      $('a[href="'+hash+'"]').tab('show');
    }

    // refresh guards when changing tabs
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(){
      refreshSendGuards();
    });
  });

})(jQuery);
</script>
