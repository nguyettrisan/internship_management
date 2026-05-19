<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo e($title); ?></h4>
            <hr class="hr-panel-heading" />

            <?php
              $table_data = [
                _l('id'),
                _l('date'),
                _l('rel_type'),
                _l('rel_id'),
                _l('action'),
                _l('staff'),
                'IP',
              ];
              render_datatable($table_data, 'internship-pro-audit-logs');
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script>
  $(function(){
    initDataTable('.table-internship-pro-audit-logs', window.location.href, undefined, undefined, undefined, [0,'desc']);
  });
</script>
<?php ?>
