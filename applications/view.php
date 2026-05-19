<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="tw-flex tw-justify-between tw-items-start">
              <div>
                <h4 class="no-margin"><?php echo e($application->full_name); ?></h4>
                <p class="text-muted mbot0"><?php echo e($application->phone); ?><?php if ($application->email) { echo ' • ' . e($application->email); } ?></p>
                <p class="text-muted"><?php echo e($application->school); ?><?php if ($application->major) { echo ' • ' . e($application->major); } ?></p>
              </div>
              <div class="tw-flex tw-gap-2">
                <?php if (has_permission('internship_pro', '', 'edit')) { ?>
                  <a class="btn btn-default" href="<?php echo admin_url('internship_management_pro/applications/edit/' . $application->id); ?>"><i class="fa fa-pencil"></i></a>
                <?php } ?>
                <?php if (has_permission('internship_pro', '', 'delete')) { ?>
                  <a class="btn btn-danger" href="<?php echo admin_url('internship_management_pro/applications/delete/' . $application->id); ?>" onclick="return confirm('<?php echo _l('confirm_action_prompt'); ?>')"><i class="fa fa-trash"></i></a>
                <?php } ?>
              </div>
            </div>

            <hr class="hr-panel-heading" />

            <div class="row">
              <div class="col-md-4">
                <h5 class="bold"><?php echo _l('internship_pro_summary'); ?></h5>
                <p><strong><?php echo _l('status'); ?>:</strong> <span class="label label-default internship-pro-status internship-pro-status-<?php echo e($application->status); ?>"><?php echo e($application->status); ?></span></p>
                <p><strong><?php echo _l('internship_pro_gender'); ?>:</strong> <?php echo e($application->gender); ?></p>
                <p><strong><?php echo _l('address'); ?>:</strong> <?php echo e($application->address); ?></p>
              </div>
              <div class="col-md-8">
                <h5 class="bold"><?php echo _l('internship_pro_job_orders'); ?></h5>
                <div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th><?php echo _l('internship_pro_job_title'); ?></th>
                        <th><?php echo _l('internship_pro_company'); ?></th>
                        <th><?php echo _l('internship_pro_applied_at'); ?></th>
                        <th><?php echo _l('status'); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach (($job_orders ?? []) as $r) { ?>
                        <tr>
                          <td><a href="<?php echo admin_url('internship_management_pro/job_orders/view/' . $r->job_order_id); ?>"><?php echo e($r->job_title); ?></a></td>
                          <td><?php echo e($r->company_name); ?></td>
                          <td><?php echo _dt($r->applied_at); ?></td>
                          <td><span class="label label-default internship-pro-status internship-pro-status-<?php echo e($r->status); ?>"><?php echo e($r->status); ?></span></td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>

                <hr />
                <h5 class="bold"><?php echo _l('internship_pro_audit_logs'); ?></h5>
                <div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th><?php echo _l('id'); ?></th>
                        <th><?php echo _l('date'); ?></th>
                        <th><?php echo _l('action'); ?></th>
                        <th><?php echo _l('staff'); ?></th>
                        <th><?php echo _l('ip'); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach (($logs ?? []) as $log) { ?>
                        <tr>
                          <td><?php echo (int)$log->id; ?></td>
                          <td><?php echo _dt($log->created_at); ?></td>
                          <td><?php echo e($log->action); ?></td>
                          <td><?php echo e($log->staff_id); ?></td>
                          <td><?php echo e($log->ip); ?></td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>

              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<?php ?>
