<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-10">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo e($title); ?></h4>
            <hr class="hr-panel-heading" />

            <?php
              $is_edit = isset($job);
              $action  = $is_edit
                ? admin_url('internship_management_pro/job_orders/edit/' . $job->id)
                : admin_url('internship_management_pro/job_orders/create');
              echo form_open($action);
            ?>

            <?php echo render_input('title', 'internship_pro_job_title', $is_edit ? $job->title : '', 'text', ['required' => true]); ?>
            <?php echo render_input('company_name', 'internship_pro_company', $is_edit ? $job->company_name : ''); ?>
            <?php echo render_input('industry', 'internship_pro_industry', $is_edit ? $job->industry : ''); ?>
            <?php echo render_textarea('description', 'description', $is_edit ? $job->description : '', ['rows' => 6]); ?>

            <div class="form-group">
              <label for="status"><?php echo _l('status'); ?></label>
              <select name="status" id="status" class="form-control">
                <?php
                  $current = $is_edit ? $job->status : 'open';
                  $opts = ['open'=>'Open','closed'=>'Closed'];
                  foreach ($opts as $k=>$v) {
                    $sel = $k === $current ? 'selected' : '';
                    echo '<option value="'.e($k).'" '.$sel.'>'.e($v).'</option>';
                  }
                ?>
              </select>
            </div>

            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            <a href="<?php echo admin_url('internship_management_pro/job_orders'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>

            <?php echo form_close(); ?>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<?php ?>
