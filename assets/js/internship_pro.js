(function($){
  'use strict';

  // Delegate change event for status dropdown in job order applicants table
  $(document).on('change', '.internship-pro-applicant-status', function(){
    var $el = $(this);
    var id = $el.data('id');
    var status = $el.val();

    // Optional note: pick the <small> text next to it? (kept simple)
    var note = '';

    $.post(admin_url + 'internship_management_pro/job_orders/update_applicant_status', {
      id: id,
      status: status,
      note: note
    }).done(function(res){
      try { res = JSON.parse(res); } catch(e) {}
      if(!res || !res.success){
        alert_float('danger', 'Update failed');
        return;
      }
      alert_float('success', 'Updated');
    }).fail(function(){
      alert_float('danger', 'Update failed');
    });
  });

  // Process stage change (application-level, shared across screens)
  $(document).on('change', '.internship-pro-process-stage', function(){
    var $el = $(this);
    var application_id = $el.data('application-id');
    var stage_key = $el.val();

    $.post(admin_url + 'internship_management_pro/applications/update_process_stage', {
      application_id: application_id,
      stage_key: stage_key
    }).done(function(res){
      try { res = JSON.parse(res); } catch(e) {}
      if(!res || !res.success){
        alert_float('danger', 'Update failed');
        return;
      }
      alert_float('success', 'Updated');
    }).fail(function(){
      alert_float('danger', 'Update failed');
    });
  });

  // Workflow step status change
  $(document).on('change', '.internship-pro-step-status', function(){
    var $el = $(this);
    var application_id = $el.data('application-id');
    var step_key = $el.data('step-key');
    var status = $el.val();

    $.post(admin_url + 'internship_management_pro/applications/update_step_status', {
      application_id: application_id,
      step_key: step_key,
      status: status
    }).done(function(res){
      try { res = JSON.parse(res); } catch(e) {}
      if(!res || !res.success){
        alert_float('danger', 'Update failed');
        return;
      }
      alert_float('success', 'Updated');
    }).fail(function(){
      alert_float('danger', 'Update failed');
    });
  });

})(jQuery);
