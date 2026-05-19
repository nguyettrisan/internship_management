<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">

        <h4 class="bold"><i class="fa fa-list-alt"></i> Mẫu Khảo Sát Internship</h4>
        <hr>

        <div class="row">
          <div class="col-md-4">
            <h5 class="bold"><?= $edit_survey_tpl ? 'Sửa Mẫu Khảo Sát' : 'Thêm Mẫu Khảo Sát'; ?></h5>
            <hr>

            <?php echo form_open(admin_url('internship_mail/survey_templates')); ?>

            <input type="hidden" name="id"
                   value="<?= $edit_survey_tpl ? html_escape($edit_survey_tpl->id) : ''; ?>">

            <div class="form-group">
              <label>Tiêu đề khảo sát</label>
              <input type="text" name="title" class="form-control" required
                     value="<?= $edit_survey_tpl ? html_escape($edit_survey_tpl->title) : ''; ?>">
            </div>

            <div class="form-group">
              <label>Mô tả</label>
              <textarea name="description" class="form-control" rows="4"
                        placeholder="Giải thích nội dung khảo sát"><?= $edit_survey_tpl ? html_escape($edit_survey_tpl->description) : ''; ?></textarea>
            </div>

            <div class="form-group">
              <label>Link khảo sát (Google Form / Typeform...)</label>
              <input type="text" name="survey_link" class="form-control"
                     value="<?= $edit_survey_tpl ? html_escape($edit_survey_tpl->survey_link) : ''; ?>">
            </div>

            <div class="checkbox">
              <label>
                <input type="checkbox" name="active" value="1"
                  <?= $edit_survey_tpl ? ($edit_survey_tpl->active ? 'checked' : '') : 'checked'; ?>>
                Kích hoạt
              </label>
            </div>

            <div class="text-right">
              <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> Lưu mẫu
              </button>
            </div>

            <?php echo form_close(); ?>
          </div>

          <div class="col-md-8">
            <h5 class="bold">Danh sách mẫu khảo sát</h5>
            <hr>

            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th width="5%">ID</th>
                    <th width="25%">Tiêu đề</th>
                    <th width="25%">Mô tả</th>
                    <th width="25%">Link khảo sát</th>
                    <th width="10%">Trạng thái</th>
                    <th width="10%">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($survey_templates)) : ?>
                    <?php foreach ($survey_templates as $s) : ?>
                      <tr>
                        <td><?= $s->id; ?></td>
                        <td><?= html_escape($s->title); ?></td>
                        <td><?= html_escape($s->description); ?></td>
                        <td>
                          <?php if ($s->survey_link): ?>
                            <a href="<?= html_escape($s->survey_link); ?>" target="_blank">Mở khảo sát</a>
                          <?php else: ?>
                            -
                          <?php endif; ?>
                        </td>
                        <td>
                          <?php if ($s->active): ?>
                            <span class="label label-success">Active</span>
                          <?php else: ?>
                            <span class="label label-default">Inactive</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <a href="<?= admin_url('internship_mail/survey_templates?id='.$s->id); ?>"
                             class="btn btn-default btn-xs">
                            <i class="fa fa-pencil"></i>
                          </a>
                          <a href="<?= admin_url('internship_mail/delete_survey_template/'.$s->id); ?>"
                             class="btn btn-danger btn-xs _delete">
                            <i class="fa fa-trash"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else : ?>
                    <tr>
                      <td colspan="6" class="text-center text-muted">Chưa có mẫu khảo sát.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
</body>
</html>