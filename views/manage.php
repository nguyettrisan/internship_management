<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">
        <h4 class="no-margin font-bold">
          <i class="fa fa-graduation-cap"></i> Danh sách học sinh Internship Nhật Bản
        </h4>

        <!-- ====== LỌC THEO NĂM ====== -->
        <div class="row mtop15">
          <div class="col-md-2">
            <label><i class="fa fa-calendar"></i> Năm</label>
            <select id="filterYear" class="form-control" onchange="filterByYear(this.value)">
              <?php foreach($years as $y): ?>
                <option value="<?= $y ?>" <?= ($y == $selected_year ? 'selected' : '') ?>>Năm <?= $y ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- ====== THỐNG KÊ ====== -->
        <div class="row mtop15">
          <?php
            $cards = [
              ['Tổng học sinh', $counters['total'], 'text-dark'],
              ['Đã nhập cảnh', $counters['entered'], 'text-success'],
              ['Sắp về nước', $counters['returning'], 'text-warning'],
              ['Đã về nước', $counters['returned'], 'text-danger'],
              ['Đã có COE', $counters['coe'], 'text-info'],
              ['Đã nộp cục', $counters['submitted'], 'text-primary'],
            ];
            foreach ($cards as $c): ?>
            <div class="col-md-2 col-sm-6 mtop10">
              <div class="widget" style="padding:12px;border:1px solid #eee;border-radius:8px;">
                <div class="row">
                  <div class="col-xs-7 <?= $c[2]; ?>"><?= $c[0]; ?></div>
                  <div class="col-xs-5 text-right"><strong><?= (int)$c[1]; ?></strong></div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <hr class="hr-panel-heading" />

        <!-- ====== NÚT HÀNH ĐỘNG ====== -->
        <div class="row mtop10">
          <div class="col-md-6">
            <a href="<?= admin_url('internship_management/student') ?>" class="btn btn-primary">
              <i class="fa fa-plus"></i> Học sinh mới
            </a>
          </div>
          <div class="col-md-6 text-right">
            <button class="btn btn-default" id="toggleFilters">
              <i class="fa fa-filter"></i> Bộ lọc nâng cao
            </button>
          </div>
        </div>

        <!-- ====== PANEL LỌC ====== -->
        <div id="filtersPanel" class="panel mtop15" style="display:none; border:1px solid #eee; border-radius:6px;">
          <div class="panel-body">
            <div class="row">
              <div class="col-md-3">
                <label>Trạng thái</label>
                <select id="f_status" class="form-control">
                  <option value="">-- Tất cả --</option>
                  <?php foreach($statuses as $st): ?>
                    <option value="<?= html_escape($st) ?>"><?= html_escape($st) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label>Trường đang theo học</label>
                <select id="f_university" class="form-control">
                  <option value="">-- Tất cả --</option>
                  <?php foreach($universities as $u): ?>
                    <option value="<?= html_escape($u) ?>"><?= html_escape($u) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label>Tỉnh tiếp nhận</label>
                <select id="f_province" class="form-control">
                  <option value="">-- Tất cả --</option>
                  <?php foreach($provinces as $p): ?>
                    <option value="<?= html_escape($p) ?>"><?= html_escape($p) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label>Đơn vị tiếp nhận</label>
                <select id="f_company" class="form-control">
                  <option value="">-- Tất cả --</option>
                  <?php foreach($companies as $c): ?>
                    <option value="<?= html_escape($c) ?>"><?= html_escape($c) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="row mtop10">
              <div class="col-md-12 text-right">
                <button id="btnApplyFilters" class="btn btn-info"><i class="fa fa-check"></i> Áp dụng</button>
                <button id="btnResetFilters" class="btn btn-default">Xóa lọc</button>
              </div>
            </div>
          </div>
        </div>

        <!-- ====== BẢNG DỮ LIỆU ====== -->
        <div class="table-responsive">
          <table id="internshipTable" class="table table-bordered table-striped table-hover" style="width:100%">
            <thead>
              <tr>
                <th style="width:40px;text-align:center;">#</th>
                <th>Ảnh</th>
                <th>Họ và tên</th>
                <th>Trường</th>
                <th>Đơn vị tiếp nhận</th>
                <th>Địa chỉ</th>
                <th>Nhập cảnh</th>
                <th>Về nước</th>
                <th>Trạng thái</th>
                <th width="130">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php $stt=1; foreach ($students as $s): ?>
                <tr>
                  <td class="text-center"><?= $stt++; ?></td>
                  <td class="text-center">
                    <?php if(!empty($s['photo'])): ?>
                      <img src="<?= base_url($s['photo']); ?>" width="45" height="45" class="img-thumbnail">
                    <?php else: ?>
                      <i class="fa fa-user-circle fa-2x text-muted"></i>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?= admin_url('internship_management/view/'.$s['id']); ?>" class="font-bold text-primary">
                      <?= html_escape($s['full_name']); ?>
                    </a>
                  </td>
                  <td><?= html_escape($s['university']); ?></td>
                  <td><?= html_escape($s['company_name']); ?></td>
                  <td><?= html_escape($s['company_address'] ?? ''); ?></td>
                  <td><?= _d($s['entry_date']); ?></td>
                  <td><?= _d($s['return_date']); ?></td>
                  <td><span class="label label-info"><?= html_escape($s['status']); ?></span></td>
                  <td class="text-center">
                    <a href="<?= admin_url('internship_management/view/'.$s['id']); ?>" class="btn btn-info btn-icon" title="Xem"><i class="fa fa-eye"></i></a>
                    <a href="<?= admin_url('internship_management/student/'.$s['id']); ?>" class="btn btn-default btn-icon" title="Sửa"><i class="fa fa-edit"></i></a>
                    <a href="<?= admin_url('internship_management/delete/'.$s['id']); ?>" class="btn btn-danger btn-icon _delete" title="Xóa"><i class="fa fa-trash"></i></a>
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

<?php init_tail(); ?>

<!-- ============= DATA TABLES ============= -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
  $('#toggleFilters').click(function(){
    $('#filtersPanel').slideToggle(200);
  });

  var table = $('#internshipTable').DataTable({
    dom: '<"row mb-3"<"col-sm-6"B><"col-sm-6"f>>rtip',
    order: [[2, 'asc']],
    pageLength: 25,
    buttons: [
      { extend: 'copyHtml5', text: '<i class="fa fa-copy"></i> Copy', className: 'btn btn-default' },
      { extend: 'excelHtml5', text: '<i class="fa fa-file-excel-o"></i> Excel', className: 'btn btn-success' },
      { extend: 'pdfHtml5', text: '<i class="fa fa-file-pdf-o"></i> PDF', className: 'btn btn-danger', orientation:'landscape', pageSize:'A4' },
      { extend: 'print', text: '<i class="fa fa-print"></i> In', className: 'btn btn-info' }
    ],
    columnDefs: [{ orderable:false, targets:[0,1,9] }],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json' }
  });

  // Auto numbering
  table.on('order.dt search.dt draw.dt', function(){
    let i = 1;
    table.cells(null, 0, { search:'applied', order:'applied' }).every(function(){
      this.data(i++);
    });
  }).draw();

  // Apply filter
  $('#btnApplyFilters').click(function(){
    table.column(3).search($('#f_university').val());
    table.column(4).search($('#f_company').val());
    table.column(5).search($('#f_province').val());
    table.column(8).search($('#f_status').val());
    table.draw();
  });

  // Reset filters
  $('#btnResetFilters').click(function(){
    $('#f_status,#f_university,#f_company,#f_province').val('');
    table.search('').columns().search('').draw();
  });
});

// Lọc theo năm
function filterByYear(year) {
  window.location.href = "<?= admin_url('internship_management') ?>?year=" + year;
}
</script>
