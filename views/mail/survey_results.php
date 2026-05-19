<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">

        <h4 class="bold"><i class="fa fa-check-circle"></i> Kết Quả Khảo Sát Internship</h4>
        <hr>

        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead>
              <tr>
                <th width="5%">ID</th>
                <th width="15%">Họ tên</th>
                <th width="20%">Email</th>
                <th width="20%">Khảo sát</th>
                <th width="25%">Kết quả</th>
                <th width="15%">Thời gian</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($results)) : ?>
                <?php foreach ($results as $r) : ?>
                  <tr>
                    <td><?= $r->id; ?></td>
                    <td><?= html_escape($r->student_name); ?></td>
                    <td><?= html_escape($r->student_email); ?></td>
                    <td><?= html_escape($r->survey_title); ?></td>
                    <td>
                      <div style="max-height:120px;overflow:auto;font-size:12px;">
                        <?= nl2br(html_escape($r->result_data)); ?>
                      </div>
                    </td>
                    <td><?= _dt($r->submitted_at); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else : ?>
                <tr>
                  <td colspan="6" class="text-center text-muted">Chưa có kết quả khảo sát.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
</body>
</html>