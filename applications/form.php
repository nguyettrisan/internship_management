<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-8">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo e($title); ?></h4>
            <hr class="hr-panel-heading" />

            <?php
              $is_edit = isset($application);
              $action  = $is_edit
                ? admin_url('internship_management_pro/applications/edit/' . $application->id)
                : admin_url('internship_management_pro/applications/create');
              echo form_open($action);
            ?>

            <?php echo render_input('full_name', 'internship_pro_full_name', $is_edit ? $application->full_name : '', 'text', ['required' => true]); ?>
            <?php echo render_input('phone', 'phone', $is_edit ? $application->phone : ''); ?>
            <?php echo render_input('email', 'email', $is_edit ? $application->email : '', 'email'); ?>
            <?php echo render_input('school', 'internship_pro_school', $is_edit ? $application->school : ''); ?>
            <?php echo render_input('major', 'internship_pro_major', $is_edit ? $application->major : ''); ?>
            <?php echo render_input('gender', 'internship_pro_gender', $is_edit ? $application->gender : ''); ?>
            <?php echo render_textarea('address', 'address', $is_edit ? $application->address : '', ['rows' => 3]); ?>

            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            <a href="<?php echo admin_url('internship_management_pro/applications'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>

            <?php echo form_close(); ?>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<?php ?>
