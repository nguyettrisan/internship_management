<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>



<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">
        <h4 class="no-margin font-bold">
          <i class="fa fa-user-graduate"></i> <?= html_escape($title); ?>
        </h4>
        <hr class="hr-panel-heading"/>

        <!-- BẮT ĐẦU FORM -->
        <form method="post" enctype="multipart/form-data" id="studentForm" accept-charset="utf-8">

          <!-- CSRF TOKEN -->
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                 value="<?php echo $this->security->get_csrf_hash(); ?>">

          <div class="row">
            <!-- CỘT TRÁI -->
            <div class="col-md-6">
              <div class="form-group">
                <label>Họ và tên <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" required
                       value="<?= isset($student['full_name']) ? html_escape($student['full_name']) : ''; ?>">
              </div>

              <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control"
                       value="<?= isset($student['email']) ? html_escape($student['email']) : ''; ?>">
              </div>

              <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" name="phone" class="form-control"
                       value="<?= isset($student['phone']) ? html_escape($student['phone']) : ''; ?>">
              </div>

              <div class="form-group">
                <label>Địa chỉ sinh viên</label>
                <input type="text" name="address" class="form-control"
                       value="<?= isset($student['address']) ? html_escape($student['address']) : ''; ?>">
              </div>

              <div class="form-group">
                <label>Trường đang theo học</label>
                <input type="text" name="university" class="form-control"
                       value="<?= isset($student['university']) ? html_escape($student['university']) : ''; ?>">
              </div>
            </div>

            <!-- CỘT PHẢI -->
            <div class="col-md-6">
              <div class="form-group">
                <label>Đơn vị tiếp nhận</label>
                <input type="text" name="company_name" class="form-control"
                       value="<?= isset($student['company_name']) ? html_escape($student['company_name']) : ''; ?>">
              </div>

              <div class="form-group">
                <label>Tỉnh tiếp nhận</label>
                <input type="text" name="company_province" class="form-control"
                       placeholder="Ví dụ: Tokyo, Osaka, Nagano..."
                       value="<?= isset($student['company_province']) ? html_escape($student['company_province']) : ''; ?>">
              </div>

              <div class="form-group">
                <label>Địa chỉ đơn vị tiếp nhận</label>
                <input type="text" name="company_address" class="form-control"
                       placeholder="Nhập địa chỉ chi tiết của công ty tiếp nhận"
                       value="<?= isset($student['company_address']) ? html_escape($student['company_address']) : ''; ?>">
              </div>

              <div class="form-group">
                <label>Ngày tuyển dụng</label>
                <input type="date" name="recruit_date" class="form-control"
                       value="<?= isset($student['recruit_date']) ? html_escape($student['recruit_date']) : ''; ?>">
              </div>

              <div class="form-group">
                <label>Ngày nhập cảnh</label>
                <input type="date" id="entry_date" name="entry_date" class="form-control"
                       value="<?= isset($student['entry_date']) ? html_escape($student['entry_date']) : ''; ?>">
              </div>
              
              <div class="form-group">
                <label>Số tháng</label>
                <input type="number" id="months_stay" name="months_stay" class="form-control" min="0" step="1"
                       value="<?= isset($student['months_stay']) ? (int)$student['months_stay'] : ''; ?>"
                       placeholder="Nhập số tháng">
                </div>

              <div class="form-group">
                <label>Ngày về nước</label>
                <input type="date" id="return_date" name="return_date" class="form-control"
                       value="<?= isset($student['return_date']) ? html_escape($student['return_date']) : ''; ?>">
              </div>

              <div class="form-group">
                <label>Trạng thái</label>
                <select name="status" class="form-control">
                  <?php
                    $status_list = ['Chuẩn bị hồ sơ', 'Đã nộp cục', 'Đã phỏng vấn', 'Đã có COE', 'Đã nhập cảnh', 'Sắp về nước'];
                    $cur = isset($student['status']) ? $student['status'] : '';
                    foreach ($status_list as $st) {
                        $sel = ($cur == $st) ? 'selected' : '';
                        echo "<option value='".html_escape($st)."' $sel>$st</option>";
                    }
                  ?>
                </select>
              </div>
            </div>
          </div>

          <!-- FILES -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Ảnh sinh viên</label><br>
                <?php if (!empty($student['photo'])): ?>
                  <img src="<?= base_url($student['photo']); ?>" alt="Photo"
                       style="width:100px;height:100px;margin-bottom:10px;border-radius:6px;"><br>
                <?php endif; ?>
                <input type="file" name="photo" accept="image/*" class="form-control">
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>File hồ sơ đính kèm</label><br>
                <?php if (!empty($student['attachment'])): ?>
                  <a href="<?= base_url($student['attachment']); ?>" target="_blank" class="btn btn-info btn-sm">
                    <i class="fa fa-download"></i> Tải xuống hồ sơ
                  </a><br><br>
                <?php endif; ?>
                <input type="file" name="attachment" accept=".pdf,.doc,.docx,.zip,.rar" class="form-control">
              </div>
            </div>
          </div>

          <!-- GHI CHÚ -->
          <div class="form-group">
            <label>Ghi chú</label>
            <textarea name="note" rows="4" class="form-control"><?= isset($student['note']) ? html_escape($student['note']) : ''; ?></textarea>
          </div>

          <hr class="hr-panel-heading"/>
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Lưu
          </button>
          <a href="<?= admin_url('internship_management'); ?>" class="btn btn-default">Quay lại</a>

        </form>
        <!-- KẾT THÚC FORM -->
      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
<script src="<?php echo base_url('modules/internship_management/assets/js/internship_returnday.js'); ?>"></script>

<?php init_tail(); ?>