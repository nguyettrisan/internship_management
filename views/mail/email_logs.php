<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
    .ifk-badge {
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    .ifk-success { background: #d4edda; color: #155724; }
    .ifk-failed  { background: #f8d7da; color: #721c24; }
    .ifk-process { background: #fff3cd; color: #856404; }
    .ifk-unknown { background: #e2e3e5; color: #6c757d; }

    .error-tooltip {
        max-width: 300px;
        white-space: normal;
        font-size: 12px;
    }
</style>

<div id="wrapper">
  <div class="content">

    <div class="panel_s">
      <div class="panel-body">
        <h4 class="mbot20">
            <i class="fa fa-envelope-open-o"></i> Lịch sử gửi Email Internship
        </h4>

        <div class="table-responsive">
          <table id="tbl_mail_logs" class="table dt-table table-hover" width="100%">
            <thead>
              <tr>
                <th width="40">ID</th>
                <th>Người nhận</th>
                <th>Tiêu đề</th>
                <th width="120">Trạng thái</th>
                <th width="150">Thời gian gửi</th>
                <th width="50">Lỗi</th>
              </tr>
            </thead>

            <tbody>
              <?php foreach ($logs as $log): ?>

                <?php
                  $id      = $log->id        ?? '';
                  $email   = $log->email_to  ?? '';
                  $subject = $log->subject   ?? '';
                  $status  = $log->status    ?? 'unknown';
                  $sent_at = $log->date_sent ?? '';
                  $error   = $log->error_message ?? '';

                  // status badge
                  switch ($status) {
                      case 'success':
                          $badge = "<span class='ifk-badge ifk-success'>Thành công</span>";
                          break;
                      case 'failed':
                          $badge = "<span class='ifk-badge ifk-failed'>Thất bại</span>";
                          break;
                      case 'processing':
                          $badge = "<span class='ifk-badge ifk-process'>Đang xử lý</span>";
                          break;
                      default:
                          $badge = "<span class='ifk-badge ifk-unknown'>Không xác định</span>";
                  }
                ?>

                <tr>
                  <td><?= $id ?></td>

                  <td>
                    <strong><?= htmlspecialchars($email) ?></strong>
                  </td>

                  <td><?= htmlspecialchars($subject) ?></td>

                  <td><?= $badge ?></td>

                  <td><?= $sent_at ? _dt($sent_at) : '-' ?></td>

                  <td class="text-center">
                    <?php if ($error): ?>
                      <i class="fa fa-exclamation-circle text-danger"
                         data-toggle="tooltip"
                         data-placement="left"
                         title="<?= htmlspecialchars($error) ?>"></i>
                    <?php else: ?>
                      <i class="fa fa-check-circle text-success"></i>
                    <?php endif; ?>
                  </td>

                </tr>
              <?php endforeach; ?>
            </tbody>

          </table>
        </div>

      </div>
    </div>

  </div>
</div>

<script>
$(function(){
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

<?php init_tail(); ?>
</body>
</html>