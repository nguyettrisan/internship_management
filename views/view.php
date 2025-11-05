<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">
        <h4 class="no-margin font-bold">
          <i class="fa fa-id-card"></i> <?= $title; ?>
        </h4>
        <hr class="hr-panel-heading"/>

        <!-- ====== THÔNG TIN CHI TIẾT ====== -->
        <div class="row">
          <div class="col-md-4 text-center">
            <?php if (!empty($student['photo'])): ?>
              <img src="<?= base_url($student['photo']); ?>" class="img-thumbnail" style="width:150px;height:150px;">
            <?php else: ?>
              <i class="fa fa-user-circle fa-5x text-muted"></i>
            <?php endif; ?>
          </div>

          <div class="col-md-8">
            <table class="table table-bordered table-striped">
              <tr><th>Họ và tên</th><td><?= html_escape($student['full_name']); ?></td></tr>
              <tr><th>Email</th><td><?= html_escape($student['email']); ?></td></tr>
              <tr><th>Điện thoại</th><td><?= html_escape($student['phone']); ?></td></tr>
              <tr><th>Địa chỉ</th><td><?= html_escape($student['address']); ?></td></tr>
              <tr><th>Trường</th><td><?= html_escape($student['university']); ?></td></tr>
              <tr><th>Đơn vị tiếp nhận</th><td><?= html_escape($student['company_name']); ?></td></tr>
              <tr><th>Ngày tuyển dụng</th><td><?= _d($student['recruit_date']); ?></td></tr>
              <tr><th>Ngày nhập cảnh</th><td><?= _d($student['entry_date']); ?></td></tr>
              <tr><th>Ngày về nước</th><td><?= _d($student['return_date']); ?></td></tr>
              <tr><th>Trạng thái</th><td><span class="label label-info"><?= html_escape($student['status']); ?></span></td></tr>
              
              <?php if (!empty($student['note'])): ?>
                <tr><th>Ghi chú</th><td><?= nl2br(html_escape($student['note'])); ?></td></tr>
              <?php endif; ?>

              <?php if (!empty($student['attachment'])): ?>
                <tr><th>Hồ sơ đính kèm</th>
                  <td>
                    <a href="<?= base_url($student['attachment']); ?>" target="_blank" class="btn btn-success btn-sm">
                      <i class="fa fa-download"></i> Tải xuống
                    </a>
                  </td>
                </tr>
              <?php endif; ?>
            </table>
          </div>
        </div>

        <hr class="hr-panel-heading"/>

        <!-- ====== NÚT HÀNH ĐỘNG ====== -->
        <div class="text-right">
          <a href="<?= admin_url('internship_management'); ?>" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Quay lại
          </a>
          <a href="<?= admin_url('internship_management/student/'.$student['id']); ?>" class="btn btn-primary">
            <i class="fa fa-edit"></i> Sửa
          </a>
          <a href="javascript:window.print()" class="btn btn-info">
            <i class="fa fa-print"></i> In
          </a>
        </div>

      </div>
    </div>
  </div>
</div>
<style>
@media print {
  /* Ẩn phần ngoài */
  body * {
    visibility: hidden !important;
  }

  /* Hiển thị panel chính */
  .panel_s, .panel_s * {
    visibility: visible !important;
  }

  .panel_s {
    background: #fff !important;
    color: #000 !important;
    box-shadow: none !important;
    border: 1px solid #ccc !important;
    margin: 0 auto !important;
    width: 95% !important;
    padding: 10px 25px;
    page-break-inside: avoid;
    transform: scale(0.80);          /* THU TOÀN BỘ NỘI DUNG NHỎ LẠI */
    transform-origin: top left;      /* canh theo góc trái */
    max-height: 18cm !important;     /* ép chiều cao vừa A4 ngang */
    overflow: hidden !important;
  }

  /* Ẩn các phần thừa */
  #side-menu, .navbar, .topbar, .footer, .btn, .alert, .breadcrumb {
    display: none !important;
  }

  /* Tiêu đề */
  h4 {
    text-align: center !important;
    font-size: 16px !important;
    font-weight: bold !important;
    margin-bottom: 15px !important;
  }

  /* Ảnh sinh viên */
  img {
    display: block;
    margin: 0 auto 8px auto !important;
    max-width: 80px !important;
    height: auto !important;
  }

  /* Bảng */
  table.table {
    border-collapse: collapse !important;
    width: 100% !important;
    font-size: 10pt !important;
    table-layout: fixed;
    word-wrap: break-word;
  }

  table th, table td {
    border: 1px solid #777 !important;
    padding: 5px 8px !important;
    vertical-align: top !important;
  }

  table th {
    width: 25%;
    background: #f9f9f9 !important;
    font-weight: 600 !important;
  }

  .label {
    border: 1px solid #000 !important;
    background: none !important;
    color: #000 !important;
    padding: 2px 5px !important;
    font-size: 9pt !important;
  }

  /* Trang in A4 ngang, lề nhỏ */
  @page {
    size: A4 landscape;
    margin: 6mm 8mm 6mm 6mm;
  }

  html, body {
    background: #fff !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
  }
}
</style>


<?php init_tail(); ?>
