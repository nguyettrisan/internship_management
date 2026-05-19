<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="tw-flex tw-justify-between tw-items-center">
              <h4 class="no-margin"><?php echo e($title); ?></h4>
              <?php if (has_permission('internship_pro', '', 'create')) { ?>
                <a href="<?php echo admin_url('internship_management_pro/applications/create'); ?>" class="btn btn-primary"><?php echo _l('new'); ?></a>
              <?php } ?>
            </div>
            <hr class="hr-panel-heading" />

            <?php
              $table_data = [
                _l('id'),
                _l('internship_pro_full_name'),
                _l('phone'),
                _l('email'),
                _l('internship_pro_school'),
                _l('internship_pro_gender'),
                _l('status'),
                _l('created_at'),
              ];
              render_datatable($table_data, 'internship-pro-applications');
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
    initDataTable('.table-internship-pro-applications', window.location.href, undefined, undefined, undefined, [0,'desc']);
  });
</script>
<?php ?>
