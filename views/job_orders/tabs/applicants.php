<?php defined('BASEPATH') or exit('No direct script access allowed');

// Expected: $applicants (array), $job_order_id
$rows = $applicants ?? [];
?>
<div class="row">
  <div class="col-md-12">
    <h4 class="tw-mt-0">Ứng viên</h4>
    <?php if (empty($rows)) { ?>
      <p class="text-muted">Chưa có ứng viên cho đơn tuyển này.</p>
    <?php } else { ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th style="width:70px;">ID</th>
              <th>Họ tên</th>
              <th>Trường</th>
              <th style="width:160px;">KQ phỏng vấn</th>
              <th style="width:200px;">Tiến độ hồ sơ</th>
              <th style="width:160px;">Cập nhật</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r) {
              $r = (array)$r;
              ?>
              <tr>
                <td><?php echo (int)($r['id'] ?? 0); ?></td>
                <td>
                  <strong><?php echo e($r['student_name'] ?? ($r['name'] ?? '')); ?></strong>
                  <?php if (!empty($r['email'])) { ?><div class="text-muted"><?php echo e($r['email']); ?></div><?php } ?>
                </td>
                <td><?php echo e($r['school'] ?? ''); ?></td>
                <td><?php echo e($r['interview_result'] ?? ''); ?></td>
                <td><?php echo e($r['dossier_progress'] ?? ($r['status'] ?? '')); ?></td>
                <td>
                  <?php if (!empty($r['updated_at'])) { echo e(_dt($r['updated_at'])); } else { echo '-'; } ?>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    <?php } ?>
  </div>
</div>
