<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
if (!function_exists('im_job_order_list_date')) {
    function im_job_order_list_date($value)
    {
        if ($value === null) {
            return '';
        }

        $value = trim((string)$value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/^(\d{4}-\d{1,2}-\d{1,2})\s+/', $value, $m)) {
            $value = $m[1];
        }

        $value = preg_replace('/年|月/u', '-', $value);
        $value = str_replace('日', '', $value);
        $value = str_replace('.', '-', $value);

        $year = null;
        $month = null;
        $day = null;

        if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $value, $m)) {
            $year  = (int)$m[1];
            $month = (int)$m[2];
            $day   = (int)$m[3];
        } elseif (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $value, $m)) {
            $day   = (int)$m[1];
            $month = (int)$m[2];
            $year  = (int)$m[3];
        } else {
            return '';
        }

        $minYear = 2000;
        $maxYear = (int)date('Y') + 20;

        if ($year < $minYear || $year > $maxYear) {
            return '';
        }

        if (!checkdate($month, $day, $year)) {
            return '';
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
}
?>
<?php init_head(); ?>

<style>

/* =====================================================
   IFK JOB ORDER TABLE STYLE
   ===================================================== */

.page-header-job{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:18px;
    flex-wrap:wrap;
    gap:10px;
}

.page-header-job h4{
    margin:0;
    font-weight:700;
    color:#00325a;
}

.page-header-job .subtitle{
    font-size:13px;
    color:#64748b;
}

/* ===== PANEL ===== */

.panel_s{
    border-radius:14px;
    border:1px solid rgba(0,50,90,.15);
    box-shadow:0 8px 20px rgba(0,50,90,.08);
}

.hr-panel-heading{
    margin:12px 0;
}

/* ===== TABLE ===== */

.table-job-orders td,
.table-job-orders th{
    white-space:nowrap;
    vertical-align:middle !important;
}

/* Stable column width */

.table-job-orders th:nth-child(1),
.table-job-orders td:nth-child(1){width:60px !important;}

.table-job-orders th:nth-child(3),
.table-job-orders td:nth-child(3){width:170px !important;}

.table-job-orders th:nth-child(4),
.table-job-orders td:nth-child(4){
    width:90px !important;
    text-align:center;
}

.table-job-orders th:nth-child(5),
.table-job-orders td:nth-child(5),
.table-job-orders th:nth-child(6),
.table-job-orders td:nth-child(6){width:120px !important;}

.table-job-orders th:nth-child(7),
.table-job-orders td:nth-child(7){width:190px !important;}

.table-job-orders td.status-icon-cell{
    width:70px !important;
    text-align:center;
}

/* ===== COMPANY ===== */

.job-company-name a{
    font-weight:700;
    font-size:15px;
    color:#00325a;
    text-decoration:none;
    transition:.2s;
}

.job-company-name a:hover{
    color:#00a6dc;
}

.job-company-address{
    font-size:12px;
    color:#64748b;
}

/* ===== BADGE MAJOR (IFK) ===== */

.badge-major{
    padding:6px 14px;
    border-radius:999px;
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    color:#fff;
}

/* Mapping theo IFK */

.badge-blue{background:#00a6dc;}
.badge-green{background:#96bc17;}
.badge-orange{background:#d97706;}
.badge-purple{background:#5b3cc4;}
.badge-gray{background:#475569;}

/* ===== STATUS PILL ===== */

.status-pill{
    display:inline-flex;
    align-items:center;
    padding:6px 14px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
    gap:6px;
    min-height:32px;
}

.status-dot{
    width:6px;
    height:6px;
    border-radius:50%;
}

.status-gender{
    margin-top:4px;
    font-size:12px;
    color:#64748b;
}

/* Status colors */

.status-primary{
    background:rgba(0,166,220,.10);
    color:#00a6dc;
}
.status-primary .status-dot{background:#00a6dc;}

.status-success{
    background:rgba(150,188,23,.15);
    color:#2f5e00;
}
.status-success .status-dot{background:#96bc17;}

.status-warning{
    background:#fff5db;
    color:#92400e;
}
.status-warning .status-dot{background:#f59e0b;}

.status-info{
    background:rgba(0,50,90,.08);
    color:#00325a;
}
.status-info .status-dot{background:#00325a;}

.status-default{
    background:#f3f4f6;
    color:#374151;
}
.status-default .status-dot{background:#6b7280;}

/* ===== ACTION BUTTON ===== */

.btn-status-icon{
    width:34px;
    height:34px;
    border-radius:50%;
    background:rgba(0,166,220,.10);
    color:#00325a !important;
    display:flex;
    align-items:center;
    justify-content:center;
    border:none;
    padding:0;
    transition:.2s ease;
}

.btn-status-icon:hover{
    background:#00325a;
    color:#fff !important;
}

/* ===== DATE FIELD ===== */

.im-date-field{
    min-width:110px;
    max-width:140px;
    border-radius:10px;
    border:1px solid rgba(0,50,90,.15);
    background:#fff;
}

.im-date-field:focus{
    border-color:#00a6dc;
    box-shadow:0 0 0 2px rgba(0,166,220,.15);
}

/* ===== COUNTRY PILL ===== */

.country-pill{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:44px;
    height:26px;
    padding:0 10px;
    border-radius:999px;
    background:rgba(0,166,220,.10);
    color:#00325a;
    font-weight:700;
    font-size:12px;
}

.country-name{
    font-size:11px;
    margin-top:4px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    max-width:100px;
}

</style>

<div id="wrapper">
<div class="content">

    <div class="panel_s">
        <div class="panel-body">

            <!-- HEADER -->
            <div class="page-header-job">
                <div>
                    <h4 class="bold">
                        <i class="fa fa-briefcase"></i>
                        Đơn Tuyển Nhật Bản (Job Orders JP)
                    </h4>
                    <div class="subtitle">
                        Quản lý đơn tuyển nghiệp đoàn – xí nghiệp Nhật cho chương trình Internship.
                    </div>
                </div>

                <a href="<?= admin_url('internship_management/internship_job_orders/create'); ?>"
                   class="btn btn-primary">
                    <i class="fa fa-plus"></i> Tạo Đơn Tuyển
                </a>
            </div>

            <hr class="hr-panel-heading">

            <!-- ==================== FILTER BAR ==================== -->
            <form method="get" class="row mb-20" style="margin-bottom:20px">

                <!-- Tháng -->
                <!-- <div class="col-md-2">
                    <label>Tháng</label>
                    <select name="month" class="form-control">
                        <option value="">Tất cả</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>"
                                <?= (!empty($filters['month']) && (int)$filters['month'] === $m ? 'selected' : '') ?>>
                                Tháng <?= $m ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div> -->
                
                <!-- Tháng -->
                <!-- <div class="col-md-2">
                    <label>Tháng</label>
                    <?php
                        $selectedMonth = isset($filters['month']) && $filters['month'] !== ''
                            ? (int)$filters['month']
                            : (int)date('n');
                    ?>
                    <select name="month" class="form-control">
                        <option value="">Tất cả</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($selectedMonth === $m ? 'selected' : '') ?>>
                                Tháng <?= $m ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div> -->
                <div class="col-md-2">
                    <label>Tháng</label>
                    <?php
                        $hasFilterRequest = !empty($_GET);
                        $selectedMonth = isset($filters['month']) ? (string)$filters['month'] : null;
                    ?>
                    <select name="month" class="form-control">
                        <option value="" <?= ($selectedMonth === '' ? 'selected' : (!$hasFilterRequest ? '' : '')) ?>>Tất cả</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ((string)$m === $selectedMonth || (!$hasFilterRequest && (int)date('n') === $m)) ? 'selected' : '' ?>>
                                Tháng <?= $m ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <!-- Năm -->
                <!-- <div class="col-md-2">
                    <label>Năm</label>
                    <select name="year" class="form-control">
                        <option value="">Tất cả</option>
                        <?php for ($y = date('Y') - 3; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>"
                                <?= (!empty($filters['year']) && (int)$filters['year'] === $y ? 'selected' : '') ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div> -->
                
                <!-- <div class="col-md-2">
                    <label>Năm</label>
                    <?php
                        $selectedYear = isset($filters['year']) ? (string)$filters['year'] : null;
                    ?>
                    <select name="year" class="form-control">
                        <option value="" <?= ($selectedYear === '' ? 'selected' : (!$hasFilterRequest ? '' : '')) ?>>Tất cả</option>
                        <?php for ($y = date('Y') - 3; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= ((string)$y === $selectedYear || (!$hasFilterRequest && (int)date('Y') === $y)) ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div> -->
                
                <div class="col-md-2">
                    <label>Năm</label>
                    <?php
                        $selectedYear = isset($filters['year']) ? (string)$filters['year'] : null;
                        $years = isset($years) && is_array($years) ? $years : [];
                
                        // Nếu năm đang chọn không có trong DB thì vẫn thêm vào để không mất lựa chọn
                        if ($selectedYear !== '' && $selectedYear !== null) {
                            $selectedYearInt = (int)$selectedYear;
                            if ($selectedYearInt > 0 && !in_array($selectedYearInt, $years, true)) {
                                $years[] = $selectedYearInt;
                            }
                        }
                
                        sort($years);
                    ?>
                    <select name="year" class="form-control">
                        <option value="" <?= ($selectedYear === '' ? 'selected' : '') ?>>Tất cả</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= (int)$y ?>" <?= ((string)$y === $selectedYear || (!$hasFilterRequest && (int)date('Y') === (int)$y)) ? 'selected' : '' ?>>
                                <?= (int)$y ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Ngành -->
                <div class="col-md-3">
                    <label>Ngành</label>
                    <input type="text" name="major" class="form-control"
                           value="<?= html_escape($filters['major'] ?? '') ?>"
                           placeholder="Nhập ngành (VD: Điều dưỡng)">
                </div>

                <!-- Trạng thái đơn -->
                <div class="col-md-3">
                    <label>Trạng thái đơn</label>
                    <select name="status" class="form-control">
                        <option value="">Tất cả</option>
                        <?php if (!empty($status_list)): ?>
                            <?php foreach ($status_list as $key => $label): ?>
                                <option value="<?= $key ?>"
                                    <?= (!empty($filters['status']) && $filters['status'] === $key ? 'selected' : '') ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Search -->
                <div class="col-md-2">
                    <label>Tìm kiếm</label>
                    <input type="text" name="search" class="form-control"
                           value="<?= html_escape($filters['search'] ?? '') ?>"
                           placeholder="Tên công ty / ngành">
                </div>

                <!-- Buttons -->
                <div class="col-md-12" style="margin-top:15px">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa fa-search"></i> Lọc
                    </button>

                    <a href="<?= admin_url('internship_management/internship_job_orders'); ?>"
                       class="btn btn-default">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </div>

            </form>
            <!-- ================== END FILTER BAR ================== -->

            <!-- TABLE -->
            <table id="tbl_job_orders" class="table table-job-orders dt-table table-hover">
                <thead>
                <tr>
                    <th style="width:60px;">ID</th>
                    <th>Đơn vị tuyển dụng</th>
                    <th style="width:160px;">Ngành</th>
                    <th class="text-center" style="width:80px;">Số lượng</th>
                    <th style="width:120px;">Ngày PV</th>
                    <th style="width:120px;">Nhập cảnh</th>
                    <th style="width:120px;">Về nước</th>
                    <th style="width:180px;">Ứng tuyển</th>
                    
                    <!-- -->
                    <th style="width:220px;">Gửi cho trường</th>
                    
                    <th style="width:240px;">Trạng thái đơn</th>
                    
                    <!-- <th class="text-center" style="width:160px;">Hành động</th> -->
                    <th class="text-center" style="width:190px;">Hành động</th>

                </tr>
                </thead>

                <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $o):

                        // Ngành (đã được tính sẵn trong model: $o['major'])
                        $major = $o['major'] ?? 'Không rõ';
                        $badge = 'badge-gray';
                        if ($major == 'Nhật Bản Học')      $badge = 'badge-blue';
                        elseif ($major == 'Du Lịch')       $badge = 'badge-green';
                        elseif ($major == 'Điều Dưỡng')    $badge = 'badge-purple';
                        elseif ($major == 'Logistic')      $badge = 'badge-orange';

                        $name_vi      = $o['company_name_vi'] ?? '(Không rõ tên)';
                        $name_jp      = $o['company_name_jp'] ?? '';
                        $status_label = $o['status_label'] ?? 'Tiếp nhận đơn';
                        $status_color = $o['status_color'] ?? 'primary';
                    ?>
                    <tr>
                        <td><?= $o['id']; ?></td>

                        <!-- Công ty -->
                        <td>
                            <div class="job-company-name">
                                <a href="javascript:void(0)" class="job-view-link"
                                   data-id="<?= $o['id']; ?>">
                                    <?= html_escape($name_vi); ?><?php $rn=(int)($o['round_no'] ?? 1); if($rn>0){ echo ' <span class="label label-default" style="margin-left:6px;">Đợt '. $rn .'</span>'; } ?>
                                </a>
                            </div>

                            <?php if (!empty($name_jp)): ?>
                                <div class="job-company-address">
                                    <?= html_escape($name_jp); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($o['address_jp'])): ?>
                                <div class="job-company-address">
                                    <?= html_escape($o['address_jp']); ?>
                                </div>
                            <?php endif; ?>
                        </td>

                        

                        <!-- Ngành -->
                        <td>
                            <span class="badge-major <?= $badge ?>">
                                <?= html_escape($major); ?>
                            </span>
                        </td>

                        <!-- Số lượng -->
                        <td class="text-center">
                            <strong><?= (int)($o['quantity'] ?? 0); ?></strong>
                        </td>

                        <!-- Ngày PV / Nhập cảnh -->
                        <td>
                            <input type="text"
                                   class="form-control input-sm im-date-field datepicker"
                                   data-id="<?= (int)$o['id']; ?>"
                                   data-field="interview_date"
                                   value="<?= html_escape(im_job_order_list_date($o['interview_date'] ?? '')); ?>"
                                   placeholder="YYYY-MM-DD">
                        </td>
                        <td>
                            <input type="text"
                                   class="form-control input-sm im-date-field datepicker"
                                   data-id="<?= (int)$o['id']; ?>"
                                   data-field="entry_date"
                                   value="<?= html_escape(im_job_order_list_date($o['entry_date'] ?? '')); ?>"
                                   placeholder="YYYY-MM-DD">
                        </td>
                        <td>
                            <input type="text"
                                   class="form-control input-sm im-date-field datepicker"
                                   data-id="<?= (int)$o['id']; ?>"
                                   data-field="return_date"
                                   value="<?= html_escape(im_job_order_list_date($o['return_date'] ?? '')); ?>"
                                   placeholder="YYYY-MM-DD">
                        </td>

                        <!-- ỨNG TUYỂN -->
                        <td>
                            <div class="tw-font-semibold"><?= (int)($o['applied_total'] ?? 0); ?> ứng viên</div>
                            <div class="status-gender">Nam: <?= (int)($o['applied_male'] ?? ($o['male'] ?? 0)); ?> | Nữ: <?= (int)($o['applied_female'] ?? ($o['female'] ?? 0)); ?></div>
                            <a href="<?= admin_url('internship_management/internship_job_orders/applicants/' . $o['id']); ?>"
                               class="btn btn-status-icon"
                               style="margin-top:6px"
                               title="Xem danh sách ứng viên & lịch phỏng vấn">
                                <i class="fa fa-users"></i>
                            </a>
                        </td>
                        
                        <!-- -->
                        <!-- GỬI CHO TRƯỜNG -->
                        <td>
                            <?php $sentSchoolText = trim((string)($o['sent_school_text'] ?? '')); ?>
                            <?php if ($sentSchoolText !== ''): ?>
                                <div style="font-weight:600;color:#00325a;line-height:1.5;">
                                    <?= html_escape($sentSchoolText); ?>
                                </div>
                                <small class="text-success"><i class="fa fa-check-circle"></i> Đã gửi lên cổng trường</small>
                            <?php else: ?>
                                <span class="text-muted">Chưa chọn trường</span>
                            <?php endif; ?>
                            <div style="margin-top:6px;">
                                <a href="<?= admin_url('internship_management/internship_job_orders/edit/' . (int)$o['id']); ?>" class="btn btn-default btn-xs">
                                    <i class="fa fa-send"></i> Gửi trường
                                </a>
                            </div>
                        </td>


                        <!-- TRẠNG THÁI ĐƠN: hiển thị + cho phép đổi thủ công -->
                        <td>
                            <div class="status-pill status-<?= html_escape($status_color); ?>" style="margin-bottom:8px;">
                                <span class="status-dot"></span>
                                <?= $status_label; ?>
                            </div>

                            <?php if (has_permission('internship_management', '', 'edit')): ?>
                                <div class="tw-flex tw-gap-2 tw-items-center">
                                    <select class="form-control input-sm ie-job-status" data-id="<?= (int)$o['id'] ?>">
                                        <?php foreach (($status_list ?? []) as $k => $lbl): ?>
                                            <option value="<?= html_escape($k) ?>" <?= (($o['status'] ?? '') === $k ? 'selected' : '') ?>>
                                                <?= html_escape($lbl) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="button" class="btn btn-default btn-sm ie-save-status" data-id="<?= (int)$o['id'] ?>" title="Lưu trạng thái">
                                        <i class="fa fa-save"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Cho phép thay đổi thủ công</small>
                            <?php else: ?>
                                <small class="text-muted">Chỉ xem</small>
                            <?php endif; ?>
                        </td>

                        <!-- HÀNH ĐỘNG CRUD -->
                        <td class="text-center">
                            
                            <!-- -->    
                              <a href="<?= admin_url('internship_management/internship_job_orders/edit/' . (int)$o['id']); ?>" class="btn btn-default btn-icon" title="Sửa / gửi cho trường">
                                <i class="fa fa-pencil"></i>
                                </a>

                            
                            <a href="javascript:void(0)" class="btn btn-info btn-icon job-view-link" data-id="<?= (int)$o['id']; ?>" title="Xem nhanh">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <!--<td colspan="10" class="text-center text-muted" style="padding:40px"> -->
                        <td colspan="11" class="text-center text-muted" style="padding:40px">
                            Chưa có đơn tuyển nào.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>

</div>
</div>

<!-- MODAL XEM NHANH -->
<div class="modal fade" id="jobViewModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Thông tin đơn tuyển</h4>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body" id="jobViewContent">
                <div class="text-center text-muted">Đang tải...</div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
// Xem nhanh đơn tuyển trong modal – JS rất nhẹ, không gây lag
$(document).on('click', '.job-view-link', function () {
    var id = $(this).data('id');
    $('#jobViewContent').html("<div class='text-center text-muted'>Đang tải...</div>");
    $('#jobViewModal').modal('show');

    $.get("<?= admin_url('internship_management/internship_job_orders/view_ajax/'); ?>" + id,
        function (html) {
            $('#jobViewContent').html(html);
        }
    );
});

// Đổi trạng thái thủ công (AJAX)
function iePostJobStatus(id, status) {
    var data = {
        status: status,
        csrf_token_name: $('input[name="csrf_token_name"]').val()
    };
    $.post("<?= admin_url('internship_management/internship_job_orders/update_status/'); ?>" + id, data)
        .done(function(res){
            try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch(e){ res = {}; }
            if (res && res.success) {
                alert_float('success', res.message || 'Đã cập nhật trạng thái.');
                //window.location.reload();
                sessionStorage.setItem('im_force_restore', '1');
                window.location.reload();
                return;
            }
            alert_float('warning', (res && res.message) ? res.message : 'Không cập nhật được.');
        })
        .fail(function(){
            alert_float('danger', 'Lỗi mạng hoặc server.');
        });
}

$(document).on('click', '.ie-save-status', function(){
    var id = parseInt($(this).data('id') || '0', 10);
    var status = $('.ie-job-status[data-id="'+id+'"]').val() || '';
    if (!id || !status) return;
    iePostJobStatus(id, status);
});

// Đẩy CRM
$(document).on('click', '.ie-push-crm', function(){
    var id = parseInt($(this).data('id') || '0', 10);
    if (!id) return;
    var data = { csrf_token_name: $('input[name="csrf_token_name"]').val() };
    $.post("<?= admin_url('internship_management/internship_job_orders/push_crm/'); ?>" + id, data)
        .done(function(res){
            try { res = typeof res === 'string' ? JSON.parse(res) : res; } catch(e){ res = {}; }
            if (res && res.success) {
                alert_float('success', res.message || 'Đã đồng bộ CRM.');
                //window.location.reload();
                sessionStorage.setItem('im_force_restore', '1');
                window.location.reload();
                return;
            }
            alert_float('warning', (res && res.message) ? res.message : 'Không đồng bộ được.');
        })
        .fail(function(){
            alert_float('danger', 'Lỗi mạng hoặc server.');
        });
});

// Inline edit: Interview date / Entry date
/*(function(){
    function imSaveDate($input){
        var id    = $input.data('id');
        var field = $input.data('field');
        var val   = ($input.val() || '').trim();

        if(!id || !field){ return; }

        var payload = { id: id, csrf_token_name: $('input[name="csrf_token_name"]').val() };
        payload[field] = val;

        $.post(admin_url + 'internship_management/internship_job_orders/update_dates', payload)
            .done(function(resp){
                try { if (typeof resp === 'string') resp = JSON.parse(resp); } catch(e){}
                if(resp && resp.success){
                    alert_float('success', 'Đã lưu ngày.');
                }else{
                    alert_float('warning', (resp && resp.message) ? resp.message : 'Không thể lưu.');
                }
            })
            .fail(function(){
                alert_float('danger', 'Lỗi kết nối khi lưu ngày.');
            });
    }

    // Save on changeDate (datepicker) and on blur (manual typing)
    $(document).on('changeDate', '.im-date-field', function(){ imSaveDate($(this)); });
    $(document).on('blur', '.im-date-field', function(){ imSaveDate($(this)); });
})();*/
(function(){
    function imNormalizeDateForPost(val){
        val = (val || '').trim();

        if (val === '') {
            return '';
        }

        val = val.replace(/[年月]/g, '-').replace(/日/g, '').replace(/\./g, '-');

        var y = null, m = null, d = null;
        var match;

        // YYYY-MM-DD hoặc YYYY/MM/DD
        match = val.match(/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/);
        if (match) {
            y = parseInt(match[1], 10);
            m = parseInt(match[2], 10);
            d = parseInt(match[3], 10);
        } else {
            // DD/MM/YYYY hoặc DD-MM-YYYY
            match = val.match(/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/);
            if (match) {
                d = parseInt(match[1], 10);
                m = parseInt(match[2], 10);
                y = parseInt(match[3], 10);
            } else {
                return '';
            }
        }

        var minYear = 2000;
        var maxYear = (new Date()).getFullYear() + 20;

        if (!y || !m || !d || y < minYear || y > maxYear || m < 1 || m > 12 || d < 1 || d > 31) {
            return '';
        }

        var dt = new Date(y, m - 1, d);

        if (
            dt.getFullYear() !== y ||
            dt.getMonth() !== (m - 1) ||
            dt.getDate() !== d
        ) {
            return '';
        }

        return String(y).padStart(4, '0') + '-' + String(m).padStart(2, '0') + '-' + String(d).padStart(2, '0');
    }

    function imSaveDate($input){
        var id    = $input.data('id');
        var field = $input.data('field');
        var val   = imNormalizeDateForPost($input.val());

        if(!id || !field){ return; }

        if (($input.val() || '').trim() !== val) {
            $input.val(val);
        }

        var oldVal = $input.data('last-saved-value');
        if (oldVal === val) {
            return;
        }

        $input.data('last-saved-value', val);

        var payload = { id: id, csrf_token_name: $('input[name="csrf_token_name"]').val() };
        payload[field] = val;

        $.post(admin_url + 'internship_management/internship_job_orders/update_dates', payload)
            .done(function(resp){
                try { if (typeof resp === 'string') resp = JSON.parse(resp); } catch(e){}
                if(resp && resp.success){
                    alert_float('success', 'Đã lưu ngày.');
                }else{
                    alert_float('warning', (resp && resp.message) ? resp.message : 'Không thể lưu.');
                }
            })
            .fail(function(){
                alert_float('danger', 'Lỗi kết nối khi lưu ngày.');
            });
    }

    $('.im-date-field').each(function(){
        var $input = $(this);
        var val = imNormalizeDateForPost($input.val());
        $input.val(val);
        $input.data('last-saved-value', val);
    });

    $(document).on('changeDate', '.im-date-field', function(){
        imSaveDate($(this));
    });

    $(document).on('blur', '.im-date-field', function(){
        imSaveDate($(this));
    });
})();
</script>

</body>
</html>