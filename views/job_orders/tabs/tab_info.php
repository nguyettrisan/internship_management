<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$job = (array)($job_order ?? ($job ?? []));

/* -----------------------------
 * Helpers
 * --------------------------- */
if (!function_exists('jo_get')) {
  function jo_get($arr, $key, $default = '')
  {
    return (isset($arr[$key]) && $arr[$key] !== null && $arr[$key] !== '') ? $arr[$key] : $default;
  }
}
if (!function_exists('jo_num')) {
  function jo_num($v)
  {
    if ($v === null || $v === '' || !is_numeric($v)) return '';
    return number_format((float)$v, 0, '.', ',');
  }
}
if (!function_exists('jo_d')) {
  function jo_d($d)
  {
    if (empty($d)) return '';
    return function_exists('_d') ? _d($d) : $d;
  }
}
if (!function_exists('jo_dt')) {
  function jo_dt($d)
  {
    if (empty($d)) return '';
    return function_exists('_dt') ? _dt($d) : $d;
  }
}
if (!function_exists('jo_val')) {
  function jo_val($v)
  {
    $v = (string)$v;
    return trim($v) === '' ? '—' : $v;
  }
}

/* -----------------------------
 * Status mapping (VI) - include "interviewed"
 * --------------------------- */
/*$status_raw = jo_get($job, 'status', '0');

$status_map_vi_num = [
  0 => 'Tiếp nhận đơn',
  1 => 'Đã gửi trường',
  2 => 'Có ứng viên',
  3 => 'Đang phỏng vấn',
  4 => 'Đã phỏng vấn',
  5 => 'Đang làm hồ sơ',
  6 => 'Chờ COE',
  7 => 'Chuẩn bị nhập cảnh',
];

$status_label_vi = 'Tiếp nhận đơn';

if (is_numeric($status_raw)) {
  $status_label_vi = $status_map_vi_num[(int)$status_raw] ?? 'Tiếp nhận đơn';
} else {
  $low = strtolower(trim((string)$status_raw));
  if ($low === 'interviewed') $status_label_vi = 'Đã phỏng vấn';
  elseif ($low === 'received') $status_label_vi = 'Tiếp nhận đơn';
  elseif ($low === 'interviewing') $status_label_vi = 'Đang phỏng vấn';
  elseif ($low === 'processing') $status_label_vi = 'Đang xử lý';
  else $status_label_vi = $status_raw !== '' ? $status_raw : 'Tiếp nhận đơn';
}*/

$status_raw      = jo_get($job, 'status', 'received');
$status_label_vi = im_job_order_status_label($status_raw, 'vi');
$status_note = jo_get($job, 'status_note', '');



/* -----------------------------
 * Quantity
 * --------------------------- */
$quantity_total = (int) jo_get($job, 'quantity', jo_get($job, 'quantity_total', 0));
$quantity_male  = jo_get($job, 'quantity_male_vi', jo_get($job, 'quantity_male', 0));
$quantity_female= jo_get($job, 'quantity_female_vi', jo_get($job, 'quantity_female', 0));
?>

<style>
:root{
  --main-navy:#00325a;
  --main-green:#96bc17;
  --main-cyan:#00a6dc;
  --border-soft:#e6eef5;
  --bg-soft:#f8fbff;
  --text:#0f172a;
  --muted:#475569;
}

.im-header{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:14px;
  flex-wrap:wrap;
  margin-bottom:16px;
}

.im-title{
  font-weight:900;
  color:var(--main-navy);
  margin:0;
  display:flex;
  align-items:center;
  gap:10px;
}

.im-title i{ color:var(--main-cyan); }

.im-status-badge{
  margin-top:8px;
  display:inline-flex;
  align-items:center;
  gap:10px;
  padding:7px 14px;
  border-radius:999px;
  background:rgba(150,188,23,.14);
  border:1px solid rgba(150,188,23,.30);
  font-weight:900;
  color:var(--main-navy);
}
.im-status-dot{
  width:9px; height:9px; border-radius:50%;
  background:var(--main-green);
  box-shadow:0 0 0 3px rgba(150,188,23,.18);
}

.im-created{
  background:rgba(0,166,220,.08);
  border:1px solid rgba(0,166,220,.25);
  padding:9px 14px;
  border-radius:999px;
  font-weight:900;
  color:var(--main-navy);
  display:flex;
  align-items:center;
  gap:8px;
  white-space:nowrap;
}

.im-note{
  margin-top:10px;
  padding:10px 12px;
  border-radius:12px;
  border:1px dashed rgba(0,50,90,.18);
  background:#fff;
  color:var(--muted);
  font-weight:700;
}

.im-grid{ margin-top:10px; }

.im-block{
  background:#fff;
  border:1px solid var(--border-soft);
  border-radius:14px;
  overflow:hidden;
  margin-bottom:18px;
  box-shadow:0 10px 24px rgba(0,0,0,.04);
}

.im-block-h{
  padding:12px 16px;
  font-weight:900;
  background:var(--bg-soft);
  border-bottom:1px solid var(--border-soft);
  color:var(--main-navy);
  display:flex;
  align-items:center;
  gap:10px;
}

.im-block-h i{ color:var(--main-cyan); }

.im-block table{ margin:0; }
.im-block th{
  width:230px;
  font-weight:900;
  color:var(--main-navy);
  background:#f9fcff;
  border-top:1px solid rgba(0,0,0,.04) !important;
}
.im-block td{
  font-weight:600;
  color:var(--text);
  border-top:1px solid rgba(0,0,0,.04) !important;
}
.im-block tr:hover td{ background:#f6fbff; }

.im-chip{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:6px 12px;
  border-radius:999px;
  background:rgba(0,166,220,.08);
  border:1px solid rgba(0,166,220,.20);
  font-weight:900;
  color:var(--main-navy);
}
.im-chip i{ color:var(--main-cyan); }
</style>

<div class="panel_s">
  <div class="panel-body">

    <div class="im-header">
      <div>
        <h4 class="im-title">
          <i class="fa fa-info-circle"></i>
          Thông tin đơn tuyển
        </h4>

        <div class="im-status-badge">
          <span class="im-status-dot"></span>
          <span><?php echo html_escape($status_label_vi); ?></span>
        </div>

        <?php if (!empty($status_note)) { ?>
          <div class="im-note">
            <i class="fa fa-sticky-note-o" style="color:var(--main-cyan);"></i>
            <?php echo nl2br(html_escape($status_note)); ?>
          </div>
        <?php } ?>
      </div>

      <div>
        <?php if (!empty($job['datecreated'])) { ?>
          <div class="im-created">
            <i class="fa fa-calendar"></i>
            Ngày lập: <?php echo html_escape(jo_dt($job['datecreated'])); ?>
          </div>
        <?php } ?>
      </div>
    </div>

    <hr class="hr-panel-heading" />

    <div class="row im-grid">

      <!-- 1) Company -->
      <div class="col-md-6">
        <div class="im-block">
          <div class="im-block-h"><i class="fa fa-building-o"></i> 1) Thông tin công ty</div>
          <table class="table table-bordered">
            <tbody>
              <tr><th>Tên công ty</th><td><?php echo html_escape(jo_val(jo_get($job,'company_name_vi', jo_get($job,'company_name','')))); ?></td></tr>
              <tr><th>Chủ tịch / Giám đốc</th><td><?php echo html_escape(jo_val(jo_get($job,'company_president_vi', jo_get($job,'company_president','')))); ?></td></tr>
              <tr><th>Địa chỉ</th><td><?php echo nl2br(html_escape(jo_val(jo_get($job,'address_vi', jo_get($job,'address',''))))); ?></td></tr>
              <tr><th>Số nhân viên</th><td><?php echo html_escape(jo_val(jo_num(jo_get($job,'employee_count_vi', jo_get($job,'employee_count',''))))); ?></td></tr>
              <tr><th>Năm thành lập</th><td><?php echo html_escape(jo_val(jo_get($job,'established_year_vi', jo_get($job,'established_year','')))); ?></td></tr>
              <tr><th>Điện thoại / Website</th>
                <td>
                  <?php
                    $phone = jo_get($job,'company_phone_vi', jo_get($job,'company_phone',''));
                    $web   = jo_get($job,'website_vi', jo_get($job,'website',''));
                    $out = jo_val($phone);
                    if (!empty($web)) $out .= ' / '.$web;
                    echo html_escape($out);
                  ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 2) Job -->
      <div class="col-md-6">
        <div class="im-block">
          <div class="im-block-h"><i class="fa fa-briefcase"></i> 2) Vị trí tuyển dụng</div>
          <table class="table table-bordered">
            <tbody>
              <tr><th>Tên vị trí</th><td><?php echo html_escape(jo_val(jo_get($job,'job_title_vi', jo_get($job,'job_title','')))); ?></td></tr>
              <tr><th>Nơi làm việc</th><td><?php echo html_escape(jo_val(jo_get($job,'workplace_vi', jo_get($job,'workplace','')))); ?></td></tr>
              <tr><th>Mô tả công việc</th><td><?php echo nl2br(html_escape(jo_val(jo_get($job,'job_description_vi', jo_get($job,'job_description',''))))); ?></td></tr>
              <tr><th>Thời hạn hợp đồng</th>
                <td>
                  <?php
                    $m = jo_get($job,'contract_months_vi', jo_get($job,'contract_months',''));
                    echo html_escape($m !== '' ? ($m.' tháng') : '—');
                  ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 3) Candidate requirements -->
      <div class="col-md-6">
        <div class="im-block">
          <div class="im-block-h"><i class="fa fa-check-circle-o"></i> 3) Điều kiện ứng viên</div>
          <table class="table table-bordered">
            <tbody>
              <tr>
                <th>Số lượng</th>
                <td><?php echo 'Nam: '.html_escape(jo_val($quantity_male)).' / Nữ: '.html_escape(jo_val($quantity_female)).' / Tổng: '.html_escape(jo_val($quantity_total)); ?></td>
              </tr>
              <tr><th>Độ tuổi</th>
                <td>
                  Từ <?php echo html_escape(jo_val(jo_get($job,'age_from_vi', jo_get($job,'age_from','')))); ?>
                  đến <?php echo html_escape(jo_val(jo_get($job,'age_to_vi', jo_get($job,'age_to','')))); ?>
                </td>
              </tr>
              <tr><th>Giới tính / Ngành</th>
                <td>
                  <?php
                    $gender = jo_get($job,'gender_vi', jo_get($job,'gender',''));
                    $major  = jo_get($job,'major_vi', jo_get($job,'major',''));
                    echo html_escape(jo_val($gender));
                    if (!empty($major)) echo '<br>'.html_escape($major);
                  ?>
                </td>
              </tr>
              <tr><th>Trình độ học vấn</th><td><?php echo html_escape(jo_val(jo_get($job,'education_vi', jo_get($job,'education','')))); ?></td></tr>
              <tr><th>Trình độ tiếng Nhật</th><td><?php echo html_escape(jo_val(jo_get($job,'japanese_level_vi', jo_get($job,'japanese_level','')))); ?></td></tr>
              <tr><th>Trình độ tiếng Anh</th><td><?php echo html_escape(jo_val(jo_get($job,'english_level_vi', jo_get($job,'english_level','')))); ?></td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 4) Work conditions -->
      <div class="col-md-6">
        <div class="im-block">
          <div class="im-block-h"><i class="fa fa-clock-o"></i> 4) Điều kiện làm việc</div>
          <table class="table table-bordered">
            <tbody>
              <tr><th>Ngày làm việc</th><td><?php echo html_escape(jo_val(jo_get($job,'work_days_vi', jo_get($job,'work_days','')))); ?></td></tr>
              <tr><th>Ngày nghỉ</th><td><?php echo html_escape(jo_val(jo_get($job,'holidays_vi', jo_get($job,'holidays','')))); ?></td></tr>
              <tr><th>Giờ làm việc</th><td><?php echo html_escape(jo_val(jo_get($job,'working_hours_vi', jo_get($job,'working_hours','')))); ?></td></tr>
              <tr><th>Giờ nghỉ</th><td><?php echo html_escape(jo_val(jo_get($job,'break_time_vi', jo_get($job,'break_time','')))); ?></td></tr>
              <tr><th>Tăng ca</th><td><?php echo html_escape(jo_val(jo_get($job,'overtime_vi', jo_get($job,'overtime','')))); ?></td></tr>
              <tr><th>Nơi phỏng vấn</th><td><?php echo html_escape(jo_val(jo_get($job,'interview_place_vi', jo_get($job,'interview_place','')))); ?></td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 5) Salary -->
      <div class="col-md-6">
        <div class="im-block">
          <div class="im-block-h"><i class="fa fa-money"></i> 5) Lương & khấu trừ</div>
          <table class="table table-bordered">
            <tbody>
              <tr><th>Lương tổng</th><td><?php echo html_escape(jo_val(jo_num(jo_get($job,'salary_total_vi', jo_get($job,'salary_total',''))))); ?></td></tr>
              <tr><th>Lương thực lãnh</th><td><?php echo html_escape(jo_val(jo_num(jo_get($job,'salary_net_vi', jo_get($job,'salary_net',''))))); ?></td></tr>
              <tr><th>Thuế / Bảo hiểm</th>
                <td>
                  Thuế: <?php echo html_escape(jo_val(jo_num(jo_get($job,'tax_vi', jo_get($job,'tax',''))))); ?>
                  / BH: <?php echo html_escape(jo_val(jo_num(jo_get($job,'insurance_vi', jo_get($job,'insurance',''))))); ?>
                </td>
              </tr>
              <tr><th>Nhà ở / Điện nước</th>
                <td>
                  Nhà: <?php echo html_escape(jo_val(jo_num(jo_get($job,'dormitory_vi', jo_get($job,'dormitory',''))))); ?>
                  / Điện nước: <?php echo html_escape(jo_val(jo_num(jo_get($job,'utilities_vi', jo_get($job,'utilities',''))))); ?>
                </td>
              </tr>
              <tr><th>Ăn uống</th><td><?php echo html_escape(jo_val(jo_get($job,'food_vi', jo_get($job,'food','')))); ?></td></tr>
              <tr><th>Thưởng / Tăng lương</th>
                <td>
                  Bonus: <?php echo html_escape(jo_val(jo_get($job,'bonus_vi', jo_get($job,'bonus','')))); ?><br>
                  Tăng lương: <?php echo html_escape(jo_val(jo_get($job,'raise_salary_vi', jo_get($job,'raise_salary','')))); ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 6) Benefits & timeline -->
      <div class="col-md-6">
        <div class="im-block">
          <div class="im-block-h"><i class="fa fa-gift"></i> 6) Phúc lợi & lịch trình</div>
          <table class="table table-bordered">
            <tbody>
              <tr><th>Hỗ trợ vé máy bay</th><td><?php echo html_escape(jo_val(jo_get($job,'benefit_flight_vi', jo_get($job,'benefit_flight','')))); ?></td></tr>
              <tr><th>Phúc lợi khác</th><td><?php echo nl2br(html_escape(jo_val(jo_get($job,'benefit_other_vi', jo_get($job,'benefit_other',''))))); ?></td></tr>
              <tr><th>Ngày phỏng vấn</th><td><?php echo html_escape(jo_val(jo_d(jo_get($job,'interview_date_vi', jo_get($job,'interview_date',''))))); ?></td></tr>
              <tr><th>Ngày nhập cảnh</th><td><?php echo html_escape(jo_val(jo_d(jo_get($job,'entry_date_vi', jo_get($job,'entry_date',''))))); ?></td></tr>
              <tr>
                <th>Ghi chú nghiệp vụ</th>
                <td class="text-muted" style="font-weight:600;color:var(--muted);">
                  Dùng tab này để kiểm tra nhanh thông tin công ty/đơn tuyển, điều kiện ứng viên, điều kiện làm việc, lương & lịch PV – nhập cảnh theo đúng mẫu in (print).
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

  </div>
</div>