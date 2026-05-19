<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<!-- FULLCALENDAR V5 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales/vi.js"></script>

<style>
 /* =====================================================
   IFK FULLCALENDAR PRO UI (FullCalendar v5)
   Target: #internship-calendar
   ===================================================== */

:root{
  --ifk-navy:#00325a;
  --ifk-green:#96bc17;
  --ifk-cyan:#00a6dc;
  --ifk-white:#ffffff;

  --ifk-bg:#f6fbff;
  --ifk-card:#ffffff;
  --ifk-border:#dce3ea;
  --ifk-text:#0f172a;
  --ifk-muted:#64748b;
  --ifk-shadow:0 10px 30px rgba(0,50,90,.10);
}

/* Wrapper */
#internship-calendar{
  background: var(--ifk-card);
  border:1px solid var(--ifk-border);
  border-radius:16px;
  box-shadow: var(--ifk-shadow);
  padding:14px 14px 10px;
}

/* Header toolbar spacing */
#internship-calendar .fc .fc-toolbar{
  margin: 6px 6px 12px;
  gap: 10px;
  flex-wrap: wrap;
}

/* Title */
#internship-calendar .fc .fc-toolbar-title{
  font-size: 20px;
  font-weight: 800;
  color: var(--ifk-navy);
  letter-spacing: .2px;
}

/* Buttons base */
#internship-calendar .fc .fc-button{
  border: 0 !important;
  border-radius: 12px !important;
  padding: 9px 12px !important;
  font-weight: 700;
  font-size: 13px;
  line-height: 1;
  box-shadow: 0 8px 18px rgba(0,50,90,.10);
  transition: transform .12s ease, box-shadow .12s ease, opacity .12s ease;
}

/* Primary-like buttons (prev/next/today/view) */
#internship-calendar .fc .fc-button-primary{
  color: #fff !important;
  background: linear-gradient(135deg, var(--ifk-navy), var(--ifk-cyan)) !important;
}

/* Hover/active */
#internship-calendar .fc .fc-button-primary:hover{
  opacity: .92;
  transform: translateY(-1px);
  box-shadow: 0 10px 20px rgba(0,50,90,.18);
}

#internship-calendar .fc .fc-button-primary:active{
  transform: translateY(0);
  box-shadow: 0 6px 14px rgba(0,50,90,.14);
}

/* Disabled */
#internship-calendar .fc .fc-button:disabled{
  opacity: .55 !important;
  box-shadow: none !important;
  transform: none !important;
}

/* Button group (Month/Week/Day) */
#internship-calendar .fc .fc-button-group{
  border-radius: 14px;
  padding: 3px;
  background: #e9f2f6;
  box-shadow: inset 0 0 0 1px rgba(0,50,90,.06);
}

#internship-calendar .fc .fc-button-group .fc-button{
  box-shadow: none !important;
  border-radius: 12px !important;
}

#internship-calendar .fc .fc-button-primary.fc-button-active{
  background: var(--ifk-white) !important;
  color: var(--ifk-navy) !important;
  box-shadow: 0 8px 18px rgba(0,50,90,.10) !important;
}

/* Calendar base typography */
#internship-calendar .fc{
  color: var(--ifk-text);
}

/* Grid border + background */
#internship-calendar .fc .fc-scrollgrid{
  border:1px solid var(--ifk-border) !important;
  border-radius: 14px;
  overflow: hidden;
}

#internship-calendar .fc-theme-standard td,
#internship-calendar .fc-theme-standard th{
  border-color: rgba(220,227,234,.8) !important;
}

/* Day headers (Mon/Tue...) */
#internship-calendar .fc .fc-col-header-cell{
  background: linear-gradient(180deg, #f2f9ff, #ffffff);
}

#internship-calendar .fc .fc-col-header-cell-cushion{
  padding: 10px 8px;
  font-weight: 800;
  color: var(--ifk-navy);
  text-transform: capitalize;
}

/* Day cells */
#internship-calendar .fc .fc-daygrid-day{
  background: #fff;
  transition: background .12s ease;
}

#internship-calendar .fc .fc-daygrid-day:hover{
  background: #f7fcff;
}

/* Day number */
#internship-calendar .fc .fc-daygrid-day-number{
  padding: 8px 10px;
  font-weight: 800;
  color: var(--ifk-navy);
}

/* Today highlight */
#internship-calendar .fc .fc-day-today{
  background: rgba(0,166,220,.08) !important;
}

#internship-calendar .fc .fc-day-today .fc-daygrid-day-number{
  background: rgba(150,188,23,.16);
  border-radius: 10px;
  padding: 6px 10px;
}

/* Selection highlight (dateClick feel) */
#internship-calendar .fc .fc-highlight{
  background: rgba(150,188,23,.18) !important;
}

/* Events (pill) */
#internship-calendar .fc .fc-daygrid-event,
#internship-calendar .fc .fc-timegrid-event{
  border-radius: 12px !important;
  padding: 2px 8px !important;
  border: 0 !important;
  box-shadow: 0 8px 18px rgba(0,50,90,.10);
  overflow: hidden;
}

#internship-calendar .fc .fc-event-title{
  font-weight: 800;
  font-size: 12.5px;
  letter-spacing: .1px;
}

#internship-calendar .fc .fc-event-time{
  font-weight: 700;
  opacity: .95;
}

/* Event hover */
#internship-calendar .fc .fc-event:hover{
  filter: brightness(0.98);
  transform: translateY(-1px);
}

/* More link */
#internship-calendar .fc .fc-daygrid-more-link{
  color: var(--ifk-cyan);
  font-weight: 800;
}

/* Week/Day timegrid */
#internship-calendar .fc .fc-timegrid-slot-label,
#internship-calendar .fc .fc-timegrid-axis-cushion{
  color: var(--ifk-muted);
  font-weight: 700;
  font-size: 12px;
}

#internship-calendar .fc .fc-timegrid-now-indicator-line{
  border-color: var(--ifk-green) !important;
}

#internship-calendar .fc .fc-timegrid-now-indicator-arrow{
  border-color: var(--ifk-green) !important;
}

/* Popover (more events) */
#internship-calendar .fc .fc-popover{
  border-radius: 14px;
  border: 1px solid var(--ifk-border);
  box-shadow: var(--ifk-shadow);
  overflow: hidden;
}

#internship-calendar .fc .fc-popover-header{
  background: linear-gradient(135deg, var(--ifk-navy), var(--ifk-cyan));
  color: #fff;
  padding: 10px 12px;
  font-weight: 800;
}

#internship-calendar .fc .fc-popover-body{
  padding: 10px;
  background: #fff;
}

/* List view (if ever used) */
#internship-calendar .fc .fc-list{
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid var(--ifk-border);
}

#internship-calendar .fc .fc-list-day-cushion{
  background: #f2f9ff;
  font-weight: 800;
  color: var(--ifk-navy);
}

/* Responsive */
@media (max-width: 768px){
  #internship-calendar{
    padding: 10px 10px 8px;
    border-radius: 14px;
  }
  #internship-calendar .fc .fc-toolbar-title{
    font-size: 18px;
  }
  #internship-calendar .fc .fc-button{
    padding: 8px 10px !important;
    border-radius: 11px !important;
  }
}

/* ========================= MONTH DETAIL ========================= */
/*.card-detail-month{
  margin-top:18px;
  background:#fff;
  border:1px solid var(--ifk-border);
  border-radius:16px;
  box-shadow: var(--ifk-shadow);
  padding:16px;
}

.detail-head{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:12px;
  margin-bottom:12px;
  flex-wrap:wrap;
}

.detail-title{
  margin:0;
  font-size:20px;
  font-weight:800;
  color:var(--ifk-navy);
}

.detail-sub{
  margin-top:4px;
  font-size:12px;
  color:var(--ifk-muted);
}

.detail-count{
  font-size:13px;
  font-weight:700;
  color:var(--ifk-cyan);
}

.table-detail-month thead th{
  background:#f3f8fc;
  color:var(--ifk-navy);
  font-weight:700;
  border-bottom:1px solid var(--ifk-border);
  white-space:nowrap;
}

.table-detail-month tbody td{
  vertical-align:middle !important;
}

.badge-event-type{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:6px 12px;
  border-radius:999px;
  font-size:12px;
  font-weight:700;
  color:#fff;
}

.badge-event-pv{ background:#2563eb; }
.badge-event-entry{ background:#10b981; }
.badge-event-return{ background:#ef4444; }
.badge-event-other{ background:#64748b; }

.detail-action-btn{
  border:none;
  border-radius:999px;
  padding:6px 12px;
  font-size:12px;
  font-weight:700;
  background:#eef6ff;
  color:#00325a;
  transition:.2s;
}

.detail-action-btn:hover{
  background:#00325a;
  color:#fff;
}*/

/* ========================= MONTH DETAIL ========================= */
.card-detail-month{
  margin-top:18px;
  background:#fff;
  border:1px solid var(--ifk-border);
  border-radius:22px;
  box-shadow:var(--ifk-shadow);
  padding:18px 18px 14px;
}

.card-detail-month .detail-head{
  display:flex;
  justify-content:space-between;
  align-items:flex-end;
  gap:12px;
  flex-wrap:wrap;
  margin-bottom:14px;
}

.card-detail-month .detail-title{
  margin:0;
  color:var(--ifk-navy);
  font-size:22px;
  line-height:1.15;
  font-weight:900;
  letter-spacing:-.02em;
}

.card-detail-month .detail-sub{
  margin-top:4px;
  color:var(--ifk-muted);
  font-size:13px;
  font-weight:700;
}

.card-detail-month .detail-count{
  font-size:13px;
  font-weight:800;
  color:var(--ifk-cyan);
}

.calendar-detail-table-wrap{
  overflow-x:auto;
  border-radius:18px;
}

.table-detail-month{
  margin:0;
  width:100%;
  background:#fff;
}

.table-detail-month thead th{
  padding:14px 12px;
  color:var(--ifk-navy);
  border-bottom:1px solid var(--ifk-border);
  font-size:12px;
  font-weight:900;
  text-transform:uppercase;
  letter-spacing:.04em;
  white-space:nowrap;
  background:linear-gradient(180deg,#fbfdff 0%, #f5f9fd 100%);
}

.table-detail-month tbody tr{
  transition:background .18s ease;
}

.table-detail-month tbody tr:hover{
  background:#f8fbff;
}

.table-detail-month tbody td{
  padding:14px 12px;
  border-top:1px solid rgba(220,227,234,.65);
  vertical-align:top !important;
  word-wrap:break-word;
  overflow-wrap:anywhere;
}

.detail-student-name{
  display:block;
  color:var(--ifk-navy);
  font-size:14px;
  line-height:1.4;
  font-weight:900;
}

.detail-student-sub{
  margin-top:2px;
  color:var(--ifk-muted);
  font-size:12px;
  font-weight:700;
}

.badge-event-type,
.badge-status-lite{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-height:30px;
  padding:6px 12px;
  border-radius:999px;
  font-size:12px;
  font-weight:800;
  line-height:1.2;
  text-align:center;
  max-width:100%;
  white-space:normal;
}

.badge-event-pv{
  background:#eaf1ff;
  color:#2453d4;
}
.badge-event-entry{
  background:#e8f8ef;
  color:#14925d;
}
.badge-event-return{
  background:#feecec;
  color:#dc3d3d;
}
.badge-event-other{
  background:#eef2f6;
  color:#475569;
}

.badge-status-lite{
  background:#eef2f7;
  color:#334155;
}

.badge-status-lite.s-blue{
  background:#eaf1ff;
  color:#184b9b;
}

.badge-status-lite.s-cyan{
  background:#e5f8ff;
  color:#0d799c;
}

.badge-status-lite.s-green{
  background:#e7fbf2;
  color:#168256;
}

.badge-status-lite.s-amber{
  background:#fff4df;
  color:#9a6307;
}

.badge-status-lite.s-red{
  background:#feecec;
  color:#dc2626;
}

.badge-status-lite.s-slate{
  background:#eef2f7;
  color:#475569;
}

.detail-action-btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:110px;
  height:40px;
  padding:0 16px;
  border-radius:14px;
  background:#fff;
  border:1px solid var(--ifk-border);
  color:var(--ifk-navy);
  text-decoration:none;
  font-weight:800;
  transition:all .2s ease;
  box-shadow:0 4px 12px rgba(11,46,89,.03);
}

.detail-action-btn:hover{
  text-decoration:none;
  color:var(--ifk-navy);
  transform:translateY(-1px);
  box-shadow:0 10px 18px rgba(11,46,89,.08);
}

.detail-empty{
  text-align:center;
  color:var(--ifk-muted);
  font-weight:700;
  padding:18px 12px !important;
}

@media (max-width:768px){
  .card-detail-month{
    padding:14px 14px 10px;
    border-radius:18px;
  }
  .card-detail-month .detail-title{
    font-size:20px;
  }
  .table-detail-month thead th,
  .table-detail-month tbody td{
    padding:12px 10px;
  }
}

</style>

<div id="wrapper">
    <div class="content">

        <h4 class="bold mtop15">
            <i class="fa fa-calendar text-primary"></i>
            Lịch Công Việc – Internship Nhật Bản
        </h4>
        <hr class="hr-panel-heading" />

        <!-- ========================= BỘ LỌC ========================= -->
        <div class="row filter-box">

            <div class="col-md-2">
                <label>Loại sự kiện</label>
                <select id="filter_event_type" class="selectpicker" data-width="100%">
                    <option value="">Tất cả</option>
                    <option value="interview">Phỏng vấn</option>
                    <option value="entry">Nhập cảnh</option>
                    <option value="return">Về nước</option>
                    <option value="task">Công việc nội bộ</option>
                    <option value="partner_meeting">Làm việc đối tác</option>
                </select>
            </div>

            <div class="col-md-2">
                <label>Phụ trách</label>
                <select id="filter_staff" class="selectpicker" data-live-search="true" data-width="100%">
                    <option value="">Tất cả</option>
                    <?php foreach ($staffs as $s) { ?>
                        <option value="<?= $s['staffid']; ?>"><?= get_staff_full_name($s['staffid']); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Đơn tuyển</label>
                <select id="filter_job_order" class="selectpicker" data-live-search="true" data-width="100%">
                    <option value="">Tất cả</option>
                    <?php foreach ($job_orders as $j) { ?>
                        <option value="<?= $j['id']; ?>">
                            <?= $j['company_name_vi'] . ' – ' . $j['job_title_vi']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Học sinh</label>
                <select id="filter_student" class="selectpicker" data-live-search="true" data-width="100%">
                    <option value="">Tất cả</option>
                    <?php foreach ($students as $st) { ?>
                        <option value="<?= $st['id']; ?>"><?= $st['full_name']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-2">
                <label>&nbsp;</label>
                <button id="btnResetFilters" class="btn btn-default btn-block btn-pill">
                    <i class="fa fa-eraser"></i> Reset
                </button>
            </div>

            <div class="col-md-2">
                <label>&nbsp;</label>
                <button id="btnSyncReturnDates" class="btn btn-info btn-block btn-pill">
                    <i class="fa fa-refresh"></i> Đồng bộ ngày về nước
                </button>
            </div>

        </div>

        <!-- ========================= CALENDAR ========================= -->
        <div id="internship-calendar"></div>
        
        <!-- ========================= MONTH DETAIL ========================= -->
        <!-- <div class="calendar-month-detail card-detail-month">
            <div class="detail-head">
                <div>
                    <h4 class="detail-title">Chi tiết lịch trong tháng</h4>
                    <div class="detail-sub">Theo dõi các sự kiện sắp tới trong tháng đang xem trên lịch.</div>
                </div>
                <div class="detail-count" id="monthDetailCount">0 sự kiện</div>
            </div>
        
            <div class="table-responsive">
                <table class="table table-hover table-detail-month">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Loại</th>
                            <th>Tiêu đề</th>
                            <th>Đơn tuyển</th>
                            <th>Học sinh</th>
                            <th>Ghi chú</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="monthDetailBody">
                        <tr>
                            <td colspan="7" class="text-center text-muted">Chưa có dữ liệu lịch trong tháng.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div> -->
        
        <div class="calendar-month-detail card-detail-month">
            <div class="detail-head">
                <div>
                    <h4 class="detail-title">Chi tiết lịch trong tháng</h4>
                    <div class="detail-sub">Bấm nút <strong>Xem chi tiết</strong> hoặc bấm trực tiếp vào sự kiện trong lịch để mở popup.</div>
                </div>
                <div class="detail-count" id="monthDetailCount">0 sự kiện</div>
            </div>
        
            <div class="calendar-detail-table-wrap">
                <table class="table table-detail-month">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Loại</th>
                            <th>Học sinh</th>
                            <th>Đơn tuyển</th>
                            <th>Công ty tiếp nhận</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="monthDetailBody">
                        <tr>
                            <td colspan="7" class="detail-empty">Chưa có dữ liệu lịch trong tháng.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        

    </div>
</div>

<!-- ========================= MODAL VIEW EVENT ========================= -->
<div class="modal fade modal-premium" id="viewEventModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Chi tiết sự kiện</h4>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <table class="table table-bordered">
                    <tr><th style="width:110px;">Tiêu đề</th><td id="v_title"></td></tr>
                    <tr><th>Ngày</th><td id="v_date"></td></tr>
                    <tr><th>Loại</th><td id="v_type"></td></tr>
                    <tr><th>Ghi chú</th><td id="v_note"></td></tr>
                </table>
            </div>

            <div class="modal-footer">
                <button class="btn btn-info btn-pill" id="btnEditEvent">
                    <i class="fa fa-pencil"></i> Sửa
                </button>
                <button class="btn btn-danger btn-pill" id="btnDeleteEvent">
                    <i class="fa fa-trash"></i> Xoá
                </button>
                <button class="btn btn-default btn-pill" data-dismiss="modal">Đóng</button>
            </div>

        </div>
    </div>
</div>

<!-- ========================= MODAL ADD EVENT ========================= -->
<div class="modal fade modal-premium" id="addEventModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="addEventForm">

                <div class="modal-header">
                    <h4 class="modal-title">Thêm sự kiện</h4>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Tiêu đề *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Ngày *</label>
                        <input type="date" name="event_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Loại *</label>
                        <select name="event_type" class="form-control" required>
                            <option value="interview">Phỏng vấn</option>
                            <option value="entry">Nhập cảnh</option>
                            <option value="return">Về nước</option>
                            <option value="task">Công việc nội bộ</option>
                            <option value="partner_meeting">Làm việc đối tác</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Màu *</label><br>
                        <div class="color-radio-wrap">
                            <?php
                           $colors = ['#96bc17', '#00325a', '#00a6dc', '#ffffff', '#ef4444', '#f59e0b'];
                            foreach ($colors as $idx => $c) {
                                echo '
                                <label>
                                    <input type="radio" name="color" value="'.$c.'" '.($idx == 0 ? 'checked' : '').'>
                                    <span class="color-dot" style="background:'.$c.'"></span>
                                </label>';
                            }
                            ?>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-default btn-pill" data-dismiss="modal">Đóng</button>
                    <button class="btn btn-primary btn-pill" type="submit">
                        <i class="fa fa-save"></i> Lưu
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- ========================= MODAL EDIT EVENT ========================= -->
<div class="modal fade modal-premium" id="editEventModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="editEventForm">

                <div class="modal-header">
                    <h4 class="modal-title">Cập nhật sự kiện</h4>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="event_id">

                    <div class="form-group">
                        <label>Tiêu đề *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Ngày *</label>
                        <input type="date" name="event_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Loại *</label>
                        <select name="event_type" class="form-control">
                            <option value="interview">Phỏng vấn</option>
                            <option value="entry">Nhập cảnh</option>
                            <option value="return">Về nước</option>
                            <option value="task">Công việc nội bộ</option>
                            <option value="partner_meeting">Làm việc đối tác</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Màu *</label><br>
                        <div class="color-radio-wrap">
                            <?php
                            foreach ($colors as $c) {
                                echo '
                                <label>
                                    <input type="radio" name="color" value="'.$c.'">
                                    <span class="color-dot" style="background:'.$c.'"></span>
                                </label>';
                            }
                            ?>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary btn-pill" type="submit">
                        <i class="fa fa-save"></i> Cập nhật
                    </button>
                    <button class="btn btn-default btn-pill" data-dismiss="modal">Đóng</button>
                </div>

            </form>

        </div>
    </div>
</div>


<!-- ================= AUTO EVENT MODAL ================= -->
<div class="modal fade modal-premium" id="autoEventModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h4 class="modal-title">Chỉnh sửa lịch tự sinh</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" style="color:#fff">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="alert alert-info" id="ae_hint" style="display:none;">
          Lịch <b>Về nước</b> đang được <b>tự sinh</b> từ <b>ngày nhập cảnh + số tháng thực tập</b>. Bạn có thể chỉnh tay để chốt lịch chính thức.
        </div>

        <p style="margin-bottom:6px;"><b id="ae_title"></b></p>
        <p style="margin-bottom:14px;color:#6b7280;"><span id="ae_type"></span></p>

        <form id="autoEventForm">
          <input type="hidden" name="auto_id" value="" />

          <div class="form-group">
            <label>Ngày *</label>
            <input type="date" name="event_date" class="form-control" required />
          </div>

          <div class="form-group">
            <label>Ghi chú</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
        <button type="button" class="btn btn-primary" id="btnSaveAutoEvent">Lưu</button>
      </div>

    </div>
  </div>
</div>


<?php init_tail(); ?>

<!-- CSRF -->
<script>
    var csrfName = "<?= $this->security->get_csrf_token_name(); ?>";
    var csrfHash = "<?= $this->security->get_csrf_hash(); ?>";
    const calendarToday = '<?php echo date('Y-m-d'); ?>';
</script>

<script>
let selectedID = null;

document.addEventListener("DOMContentLoaded", function () {
    
    let monthDetailEvents = [];
    var calendar = new FullCalendar.Calendar(
        document.getElementById("internship-calendar"), {

        locale: "vi",
        initialView: "dayGridMonth",
        height: "auto",

        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay"
        },

        /*events: function(fetchInfo, callback){
            $.getJSON(
                admin_url + "internship_management/internship_calendar/events",
                {
                    start       : fetchInfo.startStr,
                    end         : fetchInfo.endStr,
                    event_type  : $("#filter_event_type").val(),
                    staff_id    : $("#filter_staff").val(),
                    job_order_id: $("#filter_job_order").val(),
                    student_id  : $("#filter_student").val()
                },
                callback
            );
        },*/
        
        
        events: function(fetchInfo, callback){
            $.getJSON(
                admin_url + "internship_management/internship_calendar/events",
                {
                    start       : fetchInfo.startStr,
                    end         : fetchInfo.endStr,
                    event_type  : $("#filter_event_type").val(),
                    staff_id    : $("#filter_staff").val(),
                    job_order_id: $("#filter_job_order").val(),
                    student_id  : $("#filter_student").val()
                },
                /*function(res){
                    monthDetailEvents = Array.isArray(res) ? res : [];
                    callback(monthDetailEvents);
                    renderMonthDetailTable(calendar, monthDetailEvents);
                }*/
                function(res){
                    monthDetailEvents = Array.isArray(res) ? res : [];
                    callback(monthDetailEvents);
                    setTimeout(function(){
                        renderMonthDetailTable(calendar, monthDetailEvents);
                    }, 0);
                }
            );
        },

        dateClick: function(info){
            $("#addEventForm")[0].reset();
            $("#addEventForm input[name='event_date']").val(info.dateStr);
            $("#addEventModal").modal("show");
        },

        /*eventClick: function(info){

            selectedID = info.event.id;

            // AUTO EVENT: mở modal chỉnh sửa lịch tự sinh (cho phép đổi ngày & ghi chú)
            if (info.event.extendedProps && Number(info.event.extendedProps.is_auto) === 1) {
                loadAutoEvent(selectedID);
                return;
            }

            // MANUAL EVENT: chỉ xem + cho phép sửa/xoá
            $("#v_title").text(info.event.title);
            $("#v_type").text(info.event.extendedProps.type_text);
            $("#v_date").text(info.event.startStr);
            $("#v_note").text(info.event.extendedProps.description || "");

            $("#viewEventModal").modal("show");
        }
        
        datesSet: function(){
            renderMonthDetailTable(calendar, monthDetailEvents);
        }, */
        
        eventClick: function(info){

            selectedID = info.event.id;
        
            // AUTO EVENT: mở modal chỉnh sửa lịch tự sinh (cho phép đổi ngày & ghi chú)
            if (info.event.extendedProps && Number(info.event.extendedProps.is_auto) === 1) {
                loadAutoEvent(selectedID);
                return;
            }
        
            // MANUAL EVENT: chỉ xem + cho phép sửa/xoá
            $("#v_title").text(info.event.title);
            $("#v_type").text(info.event.extendedProps.type_text);
            $("#v_date").text(info.event.startStr);
            $("#v_note").text(info.event.extendedProps.description || "");
        
            $("#viewEventModal").modal("show");
        },
                
        datesSet: function(){
            renderMonthDetailTable(calendar, monthDetailEvents);
        },

    });

    calendar.render();
    
    function getEventTypeBadge(type){
        type = String(type || '').toLowerCase();
    
        if (type === 'interview') {
            return '<span class="badge-event-type badge-event-pv">Phỏng vấn</span>';
        }
        if (type === 'entry') {
            return '<span class="badge-event-type badge-event-entry">Nhập cảnh</span>';
        }
        if (type === 'return') {
            return '<span class="badge-event-type badge-event-return">Về nước</span>';
        }
        return '<span class="badge-event-type badge-event-other">Khác</span>';
    }
    
    /*function getStatusBadge(label){
        var text = String(label || '').trim();
        if (!text) {
            text = 'Chưa cập nhật';
        }
        return '<span class="badge-status-lite">' + escapeHtml(text) + '</span>';
    }*/
    function getStatusBadge(label, statusClass){
        var text = String(label || '').trim();
        if (!text) {
            text = 'Chưa cập nhật';
        }
    
        var cls = String(statusClass || '').trim();
        var extraClass = cls ? ' s-' + cls : ' s-slate';
    
        return '<span class="badge-status-lite' + extraClass + '">' + escapeHtml(text) + '</span>';
    }
    
    function escapeHtml(text){
        return $('<div>').text(text == null ? '' : String(text)).html();
    }
    
    /*function renderMonthDetailTable(calendar, events){
        var tbody = $('#monthDetailBody');
        var countBox = $('#monthDetailCount');
    
        tbody.html('');
    
        if (!Array.isArray(events) || !events.length) {
            tbody.html('<tr><td colspan="7" class="text-center text-muted">Không có dữ liệu lịch trong tháng.</td></tr>');
            countBox.text('0 sự kiện');
            return;
        }
    
        var currentDate = calendar.getDate();
        var currentYear = currentDate.getFullYear();
        var currentMonth = currentDate.getMonth() + 1;
    
        var monthEvents = events.filter(function(ev){
            var d = (ev.start || '').substring(0, 10);
            if (!d) return false;
    
            var parts = d.split('-');
            if (parts.length < 2) return false;
    
            var y = parseInt(parts[0], 10);
            var m = parseInt(parts[1], 10);
    
            return y === currentYear && m === currentMonth;
        });
    
        monthEvents.sort(function(a, b){
            return String(a.start || '').localeCompare(String(b.start || ''));
        });
    
        countBox.text(monthEvents.length + ' sự kiện');
    
        if (!monthEvents.length) {
            tbody.html('<tr><td colspan="7" class="text-center text-muted">Không có dữ liệu lịch trong tháng.</td></tr>');
            return;
        }
    
        monthEvents.forEach(function(ev){
            var note = ev.description || '';
            var title = ev.title || '—';
            var jobOrder = ev.job_order_id ? ev.job_order_id : '—';
            var student = ev.student_name ? ev.student_name : '—';
    
            var actionBtn = '';
            if (String(ev.id || '').indexOf('manual_') === 0) {
                actionBtn = '<button type="button" class="detail-action-btn js-open-manual-event" data-id="' + escapeHtml(ev.id) + '">Xem chi tiết</button>';
            } else {
                actionBtn = '<button type="button" class="detail-action-btn js-open-auto-event" data-id="' + escapeHtml(ev.id) + '">Xem chi tiết</button>';
            }
    
            var row = ''
                + '<tr>'
                + '<td>' + escapeHtml((ev.start || '').substring(0, 10)) + '</td>'
                + '<td>' + getEventTypeBadge(ev.event_type) + '</td>'
                + '<td>' + escapeHtml(title) + '</td>'
                + '<td>' + escapeHtml(jobOrder) + '</td>'
                + '<td>' + escapeHtml(student) + '</td>'
                + '<td>' + escapeHtml(note !== '' ? note : '—') + '</td>'
                + '<td>' + actionBtn + '</td>'
                + '</tr>';
    
            tbody.append(row);
        });
    }*/
    
    function renderMonthDetailTable(calendar, events){
        var tbody = $('#monthDetailBody');
        var countBox = $('#monthDetailCount');
    
        tbody.html('');
    
        if (!Array.isArray(events) || !events.length) {
            tbody.html('<tr><td colspan="7" class="detail-empty">Không có dữ liệu lịch trong tháng.</td></tr>');
            countBox.text('0 sự kiện');
            return;
        }
    
        var currentDate = calendar.getDate();
        var currentYear = currentDate.getFullYear();
        var currentMonth = currentDate.getMonth() + 1;
    
        /*var monthEvents = events.filter(function(ev){
            var d = (ev.start || '').substring(0, 10);
            if (!d) return false;
    
            var parts = d.split('-');
            if (parts.length < 2) return false;
    
            var y = parseInt(parts[0], 10);
            var m = parseInt(parts[1], 10);
    
            return y === currentYear && m === currentMonth;
        });*/
        
        /*var todayStr = calendarToday;

        var monthEvents = events.filter(function(ev){
            var d = (ev.start || '').substring(0, 10);
            if (!d) return false;
        
            var parts = d.split('-');
            if (parts.length < 3) return false;
        
            var y = parseInt(parts[0], 10);
            var m = parseInt(parts[1], 10);
        
            if (!(y === currentYear && m === currentMonth)) {
                return false;
            }
        
            return d >= todayStr;
        });
        
            // Ưu tiên event theo học sinh (app) để bảng chi tiết rõ như school portal
        var appLevelEvents = monthEvents.filter(function(ev){
            var id = String(ev.id || '');
            return id.indexOf('_app_') !== -1;
        });
    
        if (appLevelEvents.length > 0) {
            monthEvents = appLevelEvents;
        }*/
        
        var todayStr = calendarToday;

        var monthEvents = events.filter(function(ev){
            var d = (ev.start || '').substring(0, 10);
            if (!d) return false;
        
            var parts = d.split('-');
            if (parts.length < 3) return false;
        
            var y = parseInt(parts[0], 10);
            var m = parseInt(parts[1], 10);
        
            if (!(y === currentYear && m === currentMonth)) {
                return false;
            }
        
            return d >= todayStr;
        });
    
        monthEvents.sort(function(a, b){
            return String(a.start || '').localeCompare(String(b.start || ''));
        });
    
        countBox.text(monthEvents.length + ' sự kiện');
    
        if (!monthEvents.length) {
            tbody.html('<tr><td colspan="7" class="detail-empty">Không có dữ liệu lịch trong tháng.</td></tr>');
            return;
        }
    
        monthEvents.forEach(function(ev){
            var eventDate = (ev.start || '').substring(0, 10);
            var student = ev.student_name ? ev.student_name : '—';
            var major = ev.major_name ? ev.major_name : '—';
            var jobOrder = ev.job_order_id ? ev.job_order_id : '—';
            var company = ev.company_receive ? ev.company_receive : '—';
            var status = ev.status_label ? ev.status_label : 'Chưa cập nhật';
            var statusClass = ev.status_class ? ev.status_class : 'slate';
    
            var actionBtn = '';
            if (String(ev.id || '').indexOf('manual_') === 0) {
                actionBtn = '<button type="button" class="detail-action-btn js-open-manual-event" data-id="' + escapeHtml(ev.id) + '">Xem chi tiết</button>';
            } else {
                actionBtn = '<button type="button" class="detail-action-btn js-open-auto-event" data-id="' + escapeHtml(ev.id) + '">Xem chi tiết</button>';
            }
    
            var row = ''
                + '<tr>'
                + '<td>' + escapeHtml(eventDate) + '</td>'
                + '<td>' + getEventTypeBadge(ev.event_type) + '</td>'
                + '<td>'
                    + '<span class="detail-student-name">' + escapeHtml(student) + '</span>'
                    + '<div class="detail-student-sub">' + escapeHtml(major) + '</div>'
                + '</td>'
                + '<td>' + escapeHtml(jobOrder) + '</td>'
                + '<td>' + escapeHtml(company) + '</td>'
                //+ '<td>' + getStatusBadge(status) + '</td>'
                + '<td>' + getStatusBadge(status, statusClass) + '</td>'
                + '<td>' + actionBtn + '</td>'
                + '</tr>';
    
            tbody.append(row);
        });
    }

    $(document).on('click', '.js-open-manual-event', function(){
        selectedID = $(this).data('id');
        let id = String(selectedID).replace('manual_', '');
        if (!id) return;
    
        $.getJSON(
            admin_url + "internship_management/internship_calendar/get_event/" + id,
            function(e){
                if(!e){
                    alert_float("danger","Không tìm thấy sự kiện.");
                    return;
                }
    
                $("#v_title").text(e.title || '');
                $("#v_type").text(e.event_type || '');
                $("#v_date").text(e.event_date || '');
                $("#v_note").text(e.description || '');
                $("#viewEventModal").modal("show");
            }
        );
    });
    
    $(document).on('click', '.js-open-auto-event', function(){
        selectedID = $(this).data('id');
        loadAutoEvent(selectedID);
    });
    
    /* ================= AUTO EVENT MODAL ================= */
    function loadAutoEvent(autoId){
        $.getJSON(
            admin_url + "internship_management/internship_calendar/get_auto_event/" + encodeURIComponent(autoId),
            function(res){
                if(!res){
                    alert_float("danger","Không tìm thấy lịch tự sinh.");
                    return;
                }
                $("#autoEventForm input[name='auto_id']").val(res.auto_id);
                $("#autoEventForm input[name='event_date']").val(res.event_date || "");
                $("#autoEventForm textarea[name='description']").val(res.description || "");
                $("#ae_title").text(res.title || "");
                $("#ae_type").text(res.type_text || "");
                if(Number(res.computed) === 1){
                    $("#ae_hint").show();
                }else{
                    $("#ae_hint").hide();
                }
                $("#autoEventModal").modal("show");
            }
        );
    }

    $("#btnSaveAutoEvent").click(function(){
        let autoId = $("#autoEventForm input[name='auto_id']").val();
        if(!autoId) return;

        let formData = $("#autoEventForm").serializeArray();
        formData.push({name: csrfName, value: csrfHash});

        $.post(
            admin_url + "internship_management/internship_calendar/update_auto_event/" + encodeURIComponent(autoId),
            formData,
            function(resp){
                if(resp && resp.success){
                    alert_float("success", resp.message || "Đã cập nhật.");
                    $("#autoEventModal").modal("hide");
                    calendar.refetchEvents();
                }else{
                    alert_float("danger", (resp && resp.message) ? resp.message : "Không thể cập nhật.");
                }
            },
            'json'
        );
    });


    /* ================= SYNC RETURN DATES ================= */
    $("#btnSyncReturnDates").click(function(){
        $.post(
            admin_url + "internship_management/internship_calendar/sync_return_dates",
            {[csrfName]: csrfHash, limit: 1000},
            function(resp){
                if(resp && resp.success){
                    alert_float("success", resp.message || ("Đã đồng bộ: " + resp.updated));
                    calendar.refetchEvents();
                }else{
                    alert_float("danger","Không thể đồng bộ ngày về nước.");
                }
            },
            'json'
        );
    });




    /* /* removed duplicate render */ // removed duplicate render */

    /* FILTER */
    $(".selectpicker").on("change", function () {
        calendar.refetchEvents();
    });

    $("#btnResetFilters").click(function(){
        $(".selectpicker").val("").selectpicker("refresh");
        calendar.refetchEvents();
    });

    /* ADD EVENT */
    $("#addEventForm").on("submit", function(e){
        e.preventDefault();

        let form = $(this).serializeArray();
        form.push({name: csrfName, value: csrfHash});

        $.post(
            admin_url + "internship_management/internship_calendar/create_event",
            form,
            function(res){
                if(res && res.success){
                    $("#addEventModal").modal("hide");
                    calendar.refetchEvents();
                    alert_float("success","Đã thêm sự kiện");
                } else {
                    alert_float("danger","Không thể thêm sự kiện");
                }
            },
            'json'
        );
    });

    /* LOAD EVENT TO EDIT */
    $("#btnEditEvent").click(function(){

        let id = String(selectedID).replace("manual_","");
        if (!id) return;

        $.getJSON(
            admin_url + "internship_management/internship_calendar/get_event/" + id,
            function(e){

                if(!e){
                    alert_float("danger","Không tìm thấy sự kiện.");
                    return;
                }

                $("#editEventForm input[name='event_id']").val(e.id);
                $("#editEventForm input[name='title']").val(e.title);
                $("#editEventForm textarea[name='description']").val(e.description);
                $("#editEventForm input[name='event_date']").val(e.event_date);
                $("#editEventForm select[name='event_type']").val(e.event_type);
                $("#editEventForm input[name='color'][value='"+e.color+"']").prop("checked", true);

                $("#viewEventModal").modal("hide");
                $("#editEventModal").modal("show");
            }
        );
    });

    /* SUBMIT EDIT */
    $("#editEventForm").on("submit", function(e){
        e.preventDefault();

        let id = $("#editEventForm input[name='event_id']").val();
        if (!id) return;

        let form = $(this).serializeArray();
        form.push({name: csrfName, value: csrfHash});

        $.post(
            admin_url + "internship_management/internship_calendar/update_event/" + id,
            form,
            function(res){
                if(res && res.success){
                    $("#editEventModal").modal("hide");
                    calendar.refetchEvents();
                    alert_float("success","Cập nhật thành công!");
                } else {
                    alert_float("danger","Không thể cập nhật.");
                }
            },
            'json'
        );
    });

    /* DELETE EVENT */
    $("#btnDeleteEvent").click(function(){

        if (!confirm("Bạn chắc chắn xoá sự kiện này?")) return;

        let id = String(selectedID).replace("manual_","");
        if (!id) return;

        $.post(
            admin_url + "internship_management/internship_calendar/delete_event/" + id,
            {[csrfName]: csrfHash},
            function(res){
                if(res && res.success){
                    $("#viewEventModal").modal("hide");
                    calendar.refetchEvents();
                    alert_float("success","Đã xoá");
                } else {
                    alert_float("danger","Không xoá được");
                }
            },
            'json'
        );
    });

});
</script>

</body>
</html>