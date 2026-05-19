<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-8">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo _l('internship_pro_add_applicant'); ?> — <?php echo e($job->title); ?></h4>
            <hr class="hr-panel-heading" />

            <?php echo form_open(admin_url('internship_management_pro/job_orders/add_applicant/' . $job->id)); ?>

            <div class="form-group">
              <label for="application_id"><?php echo _l('internship_pro_application'); ?></label>
              <select name="application_id" id="application_id" class="form-control" required>
                <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                <?php foreach (($applications ?? []) as $a) { ?>
                  <option value="<?php echo (int)$a->id; ?>"><?php echo e($a->full_name); ?><?php if ($a->phone) { echo ' — ' . e($a->phone); } ?></option>
                <?php } ?>
              </select>
            </div>

            <div class="form-group">
              <label for="status"><?php echo _l('status'); ?></label>
              <select name="status" id="status" class="form-control">
                <?php foreach (($statuses ?? []) as $k => $v) { ?>
                  <option value="<?php echo e($k); ?>"><?php echo e($v); ?></option>
                <?php } ?>
              </select>
            </div>

            <div class="form-group">
              <label for="note"><?php echo _l('note'); ?></label>
              <textarea name="note" id="note" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            <a href="<?php echo admin_url('internship_management_pro/job_orders/view/' . $job->id); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>

            <?php echo form_close(); ?>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<?php ?>
