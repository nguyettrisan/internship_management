<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$app = isset($app) && is_array($app) ? $app : [];
?>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-body">

        <h4 class="bold">
          <i class="fa fa-user"></i> Thông tin chi tiết hồ sơ
        </h4>

        <table class="table table-bordered mtop15">
          <tbody>

            <tr>
              <th width="220">Họ tên</th>
              <td><?= html_escape($app['full_name'] ?? '—'); ?></td>
            </tr>

            <tr>
              <th>Giới tính</th>
              <td><?= html_escape($app['gender'] ?? '—'); ?></td>
            </tr>

            <tr>
              <th>Ngày sinh</th>
              <td><?= html_escape($app['birthday'] ?? '—'); ?></td>
            </tr>

            <tr>
              <th>Email</th>
              <td><?= html_escape($app['email'] ?? '—'); ?></td>
            </tr>

            <tr>
              <th>SĐT sinh viên</th>
              <td><?= html_escape($app['phone_student'] ?? '—'); ?></td>
            </tr>

            <tr>
              <th>SĐT phụ huynh</th>
              <td><?= html_escape($app['phone_parent'] ?? '—'); ?></td>
            </tr>

            <tr>
              <th>Địa chỉ</th>
              <td><?= html_escape($app['address'] ?? '—'); ?></td>
            </tr>

            <tr>
              <th>Trường</th>
              <td><?= html_escape($app['school_name'] ?? $app['school'] ?? '—'); ?></td>
            </tr>

            <tr>
              <th>Ngành</th>
              <td><?= html_escape($app['major'] ?? '—'); ?></td>
            </tr>

            <tr>
              <th>JLPT</th>
              <td><?= html_escape($app['japanese_level'] ?? '—'); ?></td>
            </tr>

            <tr>
              <th>Tiếng Anh</th>
              <td><?= html_escape($app['english_level'] ?? '—'); ?></td>
            </tr>

            <tr>
              <th>Trạng thái hồ sơ</th>
              <td>
                <span class="label label-info">
                  <?= html_escape($app['status'] ?? '—'); ?>
                </span>
              </td>
            </tr>

            <tr>
              <th>Kết quả phỏng vấn</th>
              <td>
                <?= html_escape($app['interview_result'] ?? '—'); ?>
              </td>
            </tr>

          </tbody>
        </table>

      </div>
    </div>
  </div>
</div>