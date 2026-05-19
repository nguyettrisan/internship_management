<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php if (empty($candidates)) { ?>
  <p>Chưa có ứng viên cho đơn tuyển này.</p>
<?php } else { ?>
  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Ứng viên</th>
          <th>Trạng thái</th>
          <th>Ngày tạo</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($candidates as $i => $row) { 
          $name = $row['fullname'] ?? $row['name'] ?? ($row['candidate_name'] ?? ('#' . ($row['id'] ?? '')));
          $status = $row['status'] ?? $row['stage'] ?? '';
          $created = $row['dateadded'] ?? $row['created_at'] ?? $row['date_created'] ?? '';
         $sid = (int)($row['student_id'] ?? $row['candidate_id'] ?? $row['sid'] ?? 0);
        ?>
          <tr>
            <td><?php echo $i+1; ?></td>
            <td><?php echo htmlspecialchars($name); ?></td>
            <td><?php echo htmlspecialchars($status); ?></td>
            <td><?php echo $created ? htmlspecialchars($created) : ''; ?></td>
            <td class="text-right">
              <?php if ($app_id > 0) { ?>
                <a class="btn btn-default btn-sm" target="_blank" href="<?php echo admin_url('internship_management/internship_applications/view/' . $app_id); ?>">
                  Xem
                </a>
              <?php } ?>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
<?php } ?>
