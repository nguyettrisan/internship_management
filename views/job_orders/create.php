<?php defined('BASEPATH') or exit('No direct script access allowed');

init_head();

// ------------------------------------------------------
// CHUẨN BỊ DỮ LIỆU FORM
// ------------------------------------------------------
$dataRow = isset($job_ai) && is_array($job_ai) ? $job_ai : ((isset($job) && is_array($job)) ? $job : []);

if (!function_exists('imv')) {
    function imv($arr, $key, $default = '')
    {
        return isset($arr[$key]) ? $arr[$key] : $default;
    }
}

$is_edit = isset($job['id']) && (int)$job['id'] > 0;
$action_url = $is_edit
    ? admin_url('internship_management/internship_job_orders/edit/' . (int)$job['id'])
    : admin_url('internship_management/internship_job_orders/create');

// Helper: render cặp JP / VI cùng hàng cho dễ đối chiếu
if (!function_exists('im_render_pair_input')) {
    function im_render_pair_input($name_jp, $name_vi, $label_jp, $label_vi, $dataRow, $type = 'text', $attrs = [])
    {
        $vjp = imv($dataRow, $name_jp);
        $vvi = imv($dataRow, $name_vi);

        echo '<div class="row mbot10">';
        echo '  <div class="col-md-6">' . render_input($name_jp, $label_jp, $vjp, $type, $attrs) . '</div>';
        echo '  <div class="col-md-6">' . render_input($name_vi, $label_vi, $vvi, $type, $attrs) . '</div>';
        echo '</div>';
    }
}

if (!function_exists('im_render_pair_textarea')) {
    function im_render_pair_textarea($name_jp, $name_vi, $label_jp, $label_vi, $dataRow, $rows = 4, $attrs = [])
    {
        $vjp = imv($dataRow, $name_jp);
        $vvi = imv($dataRow, $name_vi);

        echo '<div class="row mbot10">';
        echo '  <div class="col-md-6">' . render_textarea($name_jp, $label_jp, $vjp, array_merge(['rows' => $rows], $attrs)) . '</div>';
        echo '  <div class="col-md-6">' . render_textarea($name_vi, $label_vi, $vvi, array_merge(['rows' => $rows], $attrs)) . '</div>';
        echo '</div>';
    }
}

$status_value = imv($dataRow, 'status', 'received');
$status_note  = imv($dataRow, 'status_note', '');

//
$partner_schools = isset($partner_schools) && is_array($partner_schools) ? $partner_schools : [];
$selected_school_ids = isset($selected_school_ids) && is_array($selected_school_ids) ? array_map('intval', $selected_school_ids) : [];
?>

<style>

/* =====================================================
   IFK FORM SECTION STYLE
   Scoped cho job order
   ===================================================== */

.im-jo-title{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
}

.im-jo-title h4{
  margin:0;
  font-weight:700;
  color:#00325a; /* IFK navy */
}

/* ===== SECTION BOX ===== */

.im-sec{
  border:1px solid rgba(0,50,90,.15);
  border-radius:14px;
  padding:16px 16px 10px;
  margin-bottom:16px;
  background:#ffffff;
  box-shadow:0 4px 12px rgba(0,50,90,.06);
}

.im-sec h5{
  margin:0 0 12px;
  font-weight:700;
  font-size:15px;
  color:#00325a;
}

.im-sec small{
  opacity:.7;
  color:#64748b;
}

/* ===== HINT ===== */

.im-pair-hint{
  margin-top:-6px;
  margin-bottom:10px;
  font-size:12px;
  color:#64748b;
}

/* ===== INLINE ACTIONS ===== */

.im-inline-actions{
  display:flex;
  gap:8px;
  align-items:center;
  flex-wrap:wrap;
}

/* ===== INPUT ALIGN RIGHT (SỐ) ===== */

input[name^="salary_"],
input[name^="tax"],
input[name^="insurance"],
input[name^="dormitory"],
input[name^="utilities"],
input[name^="employee_count"],
input[name^="quantity_"]{
  text-align:right;
  font-weight:500;
}

/* ===== FOCUS EFFECT IFK ===== */

.im-sec input:focus,
.im-sec select:focus,
.im-sec textarea:focus{
  border-color:#00a6dc !important;
  box-shadow:0 0 0 2px rgba(0,166,220,.15) !important;
}

/* ===== BUTTON LOOK IFK ===== */

.im-sec .btn-primary{
  background:#00325a;
  border-color:#00325a;
}

.im-sec .btn-success{
  background:#96bc17;
  border-color:#96bc17;
}

.im-sec .btn-info{
  background:#00a6dc;
  border-color:#00a6dc;
  color:#fff;
}

/* ===== HOVER ===== */

.im-sec .btn-primary:hover,
.im-sec .btn-success:hover,
.im-sec .btn-info:hover{
  filter:brightness(.95);
}

</style>

<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">

        <div class="im-jo-title">
          <div>
            <h4 class="bold"><i class="fa fa-briefcase"></i> <?php echo html_escape($title ?? ($is_edit ? 'Cập nhật Đơn Tuyển' : 'Tạo Đơn Tuyển')); ?></h4>
            <p class="text-muted mbot0">Nhập song song <strong>Tiếng Nhật</strong> và <strong>Tiếng Việt</strong>. Hai bên hiển thị cạnh nhau để tránh lệch nội dung.</p>
          </div>
          <div class="im-inline-actions">
            <a class="btn btn-default" href="<?php echo admin_url('internship_management/internship_job_orders'); ?>"><i class="fa fa-arrow-left"></i> Quay lại</a>
            <button class="btn btn-default" type="button" id="imCopyJP2VI"><i class="fa fa-copy"></i> Copy JP → VI (ô VI trống)</button>
            <button class="btn btn-default" type="button" id="imCopyVI2JP"><i class="fa fa-copy"></i> Copy VI → JP (ô JP trống)</button>
          </div>
        </div>

        <hr class="hr-panel-heading" />
        
        <!-- -->
            <div class="alert alert-warning" style="border-left:4px solid #00a6dc;">
          <strong><i class="fa fa-send"></i> Gửi cho trường:</strong>
          Cuộn xuống mục <strong>8) Gửi cho trường</strong> để chọn một hoặc nhiều trường đối tác.
          <?php if ($is_edit && !empty($selected_school_ids)): ?>
            <div style="margin-top:6px;">
              <strong>Trường đang nhận đơn:</strong>
              <?php echo html_escape(implode(', ', array_map(function($x) use ($partner_schools){ foreach($partner_schools as $ps){ if((int)$ps['id']===(int)$x){ return $ps['school_name']; } } return ''; }, $selected_school_ids))); ?>
            </div>
          <?php endif; ?>
        </div>
        
        <?php if (!$is_edit) { ?>
          <div class="alert alert-info">
            <strong>Tuỳ chọn:</strong> Upload file Word (.docx) tiếng Nhật để hệ thống tự phân tích & điền form.
          </div>

	          <div class="row">
	            <div class="col-md-7">
	              <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('internship_management/internship_job_orders/create'); ?>" class="form-inline mbot20">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            <input type="file" name="jp_docx" class="form-control" accept=".docx">
            <button type="submit" class="btn btn-default"><i class="fa fa-magic"></i> Phân tích từ Word</button>
	              </form>
	            </div>
	            <div class="col-md-5">
	              <!-- Mini translate box (Google Translate style) -->
	              <div class="panel panel-default" style="margin-bottom:15px;">
	                <div class="panel-body" style="padding:10px;">
	                  <div class="row" style="margin-bottom:8px;">
	                    <div class="col-xs-6">
	                      <label class="text-muted" style="font-size:12px;">Nguồn</label>
	                      <select id="imTrFrom" class="form-control input-sm">
	                        <option value="auto">Phát hiện</option>
	                        <option value="ja" selected>Nhật</option>
	                        <option value="vi">Việt</option>
	                        <option value="en">Anh</option>
	                      </select>
	                    </div>
	                    <div class="col-xs-6">
	                      <label class="text-muted" style="font-size:12px;">Sang</label>
	                      <select id="imTrTo" class="form-control input-sm">
	                        <option value="vi" selected>Việt</option>
	                        <option value="ja">Nhật</option>
	                        <option value="en">Anh</option>
	                      </select>
	                    </div>
	                  </div>
	                  <textarea id="imTrSrc" class="form-control" rows="3" placeholder="Dán nội dung cần dịch..."></textarea>
	                  <textarea id="imTrOut" class="form-control" rows="3" style="margin-top:6px;" placeholder="Kết quả..."></textarea>
	                  <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
	                    <button type="button" class="btn btn-default btn-sm" id="imTrPaste"><i class="fa fa-clipboard"></i> Dán vào ô</button>
	                    <button type="button" class="btn btn-default btn-sm" id="imTrDo"><i class="fa fa-language"></i> Dịch</button>
	                    <button type="submit" name="translate_vi" value="1" class="btn btn-success btn-sm" id="imTrFillMissing"><i class="fa fa-magic"></i> Dịch các ô còn trống</button>
	                  </div>
	                  <div class="text-muted" style="margin-top:6px;font-size:12px;">Tip: click vào 1 ô JP/VI để “chọn ô”. Nút <strong>Dán vào ô</strong> sẽ dán vào ô đang chọn.</div>
	                </div>
	              </div>
	            </div>
	          </div>
          <hr />
        <?php } ?>

        <form id="job_order_form" method="post" action="<?php echo $action_url; ?>">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
          <input type="hidden" name="save_job_order" value="1">

          <!-- STATUS (giữ nguyên workflow; sửa thủ công ở list) -->
          <input type="hidden" name="status" value="<?php echo html_escape($status_value); ?>">

          <div class="im-sec">
            <h5>1) Thông tin công ty / 企業情報</h5>
            <div class="im-pair-hint">Bên trái: JP • Bên phải: VI</div>
            <?php
              im_render_pair_input('company_name_jp','company_name_vi','会社名 (JP)','Tên công ty (VI)',$dataRow);
              im_render_pair_input('company_president','company_president_vi','代表者 (JP)','Người đại diện (VI)',$dataRow);
              im_render_pair_textarea('address_jp','address_vi','住所 (JP)','Địa chỉ (VI)',$dataRow,2);
              im_render_pair_input('employee_count','employee_count_vi','従業員数 (JP)','Số nhân viên (VI)',$dataRow,'number');
              im_render_pair_input('established_year','established_year_vi','設立年 (JP)','Năm thành lập (VI)',$dataRow);
              im_render_pair_input('website','website_vi','Website (JP)','Website (VI)',$dataRow);
              // company_phone: JP lưu ở company_phone, VI ở company_phone_vi
              im_render_pair_input('company_phone','company_phone_vi','電話 (JP)','Điện thoại (VI)',$dataRow);
            ?>
          </div>

          <div class="im-sec">
            <h5>2) Nội dung tuyển dụng / 募集内容</h5>
            <div class="im-pair-hint">Các trường VI có hậu tố <code>_vi</code>. Các trường JP là tên gốc hoặc có <code>_jp</code>.</div>
            <?php
              im_render_pair_input('job_title','job_title_vi','職種 (JP)','Vị trí (VI)',$dataRow);
              im_render_pair_input('workplace_jp','workplace_vi','勤務地 (JP)','Nơi làm việc (VI)',$dataRow);
              im_render_pair_textarea('job_description_jp','job_description_vi','業務内容 (JP)','Mô tả công việc (VI)',$dataRow,5);
            ?>
          </div>

          <div class="im-sec">
            <h5>3) Chỉ tiêu & điều kiện ứng viên / 応募条件</h5>
            <div class="im-pair-hint">Tổng số lượng sẽ tự tính = Nam + Nữ. Bạn vẫn có thể chỉnh tay nếu cần.</div>

            <div class="row">
              <div class="col-md-3">
                <?php echo render_input('quantity_male','Số lượng Nam (JP)', imv($dataRow,'quantity_male'),'number',['min'=>0,'step'=>1]); ?>
              </div>
              <div class="col-md-3">
                <?php echo render_input('quantity_female','Số lượng Nữ (JP)', imv($dataRow,'quantity_female'),'number',['min'=>0,'step'=>1]); ?>
              </div>
              <div class="col-md-3">
                <?php echo render_input('quantity_total','Tổng (JP)', imv($dataRow,'quantity_total'),'number',['min'=>0,'step'=>1]); ?>
              </div>
              <div class="col-md-3">
                <?php echo render_input('major_jp','Chuyên ngành (JP)', imv($dataRow,'major_jp')); ?>
              </div>
            </div>

            <div class="row">
              <div class="col-md-3">
                <?php echo render_input('quantity_male_vi','Số lượng Nam (VI)', imv($dataRow,'quantity_male_vi'),'number',['min'=>0,'step'=>1]); ?>
              </div>
              <div class="col-md-3">
                <?php echo render_input('quantity_female_vi','Số lượng Nữ (VI)', imv($dataRow,'quantity_female_vi'),'number',['min'=>0,'step'=>1]); ?>
              </div>
              <div class="col-md-3">
                <?php echo render_input('quantity_total_vi','Tổng (VI)', imv($dataRow,'quantity_total_vi'),'number',['min'=>0,'step'=>1]); ?>
              </div>
              <div class="col-md-3">
                <?php echo render_input('major_vi','Chuyên ngành (VI)', imv($dataRow,'major_vi')); ?>
              </div>
            </div>

            <?php
              im_render_pair_input('age_from','age_from_vi','年齢(From) (JP)','Tuổi từ (VI)',$dataRow,'number');
              im_render_pair_input('age_to','age_to_vi','年齢(To) (JP)','Tuổi đến (VI)',$dataRow,'number');
              im_render_pair_input('education','education_vi','学歴 (JP)','Trình độ (VI)',$dataRow);
              im_render_pair_input('japanese_level','japanese_level_vi','日本語 (JP)','Tiếng Nhật (VI)',$dataRow);
              im_render_pair_input('english_level','english_level_vi','英語 (JP)','Tiếng Anh (VI)',$dataRow);
              // các cột sau DB chỉ có 1 version (không _vi) => nhập chung
              echo '<div class="row mbot10">';
              echo '  <div class="col-md-6">' . render_input('height','身長 (JP)', imv($dataRow,'height')) . '</div>';
              echo '  <div class="col-md-6">' . render_input('weight','Cân nặng (VI)', imv($dataRow,'weight')) . '</div>';
              echo '</div>';
              echo '<div class="row mbot10">';
              echo '  <div class="col-md-6">' . render_input('experience','経験 (JP)', imv($dataRow,'experience')) . '</div>';
              echo '  <div class="col-md-6">' . render_textarea('other_requirements','Yêu cầu khác (VI)', imv($dataRow,'other_requirements'), ['rows'=>3]) . '</div>';
              echo '</div>';
            ?>
          </div>

          <div class="im-sec">
            <h5>4) Điều kiện làm việc / 雇用条件</h5>
            <?php
              im_render_pair_input('contract_months','contract_months_vi','契約(月) (JP)','Hợp đồng (tháng) (VI)',$dataRow,'number');
              im_render_pair_input('work_days','work_days_vi','勤務日数 (JP)','Ngày làm/tuần (VI)',$dataRow);
              im_render_pair_input('holidays','holidays_vi','休日 (JP)','Ngày nghỉ (VI)',$dataRow);
              im_render_pair_input('working_hours','working_hours_vi','就業時間 (JP)','Giờ làm (VI)',$dataRow);
              im_render_pair_input('break_time','break_time_vi','休憩 (JP)','Giờ nghỉ (VI)',$dataRow);
              im_render_pair_input('overtime','overtime_vi','残業 (JP)','Tăng ca (VI)',$dataRow);
            ?>
          </div>

          <div class="im-sec">
            <h5>5) Lương & phúc lợi / 給与・福利厚生</h5>
            <?php
              im_render_pair_input('salary_total','salary_total_vi','総支給 (JP)','Tổng lương (VI)',$dataRow,'number');
              im_render_pair_input('salary_net','salary_net_vi','手取り (JP)','Lương thực lãnh (VI)',$dataRow,'number');
              im_render_pair_input('tax','tax_vi','税金 (JP)','Thuế (VI)',$dataRow,'number');
              im_render_pair_input('insurance','insurance_vi','保険 (JP)','Bảo hiểm (VI)',$dataRow,'number');
              im_render_pair_input('dormitory','dormitory_vi','寮 (JP)','Ký túc xá (VI)',$dataRow,'number');
              im_render_pair_input('utilities','utilities_vi','光熱費 (JP)','Điện nước (VI)',$dataRow,'number');
              im_render_pair_input('food','food_vi','食事 (JP)','Ăn uống (VI)',$dataRow);
              im_render_pair_input('bonus','bonus_vi','賞与 (JP)','Thưởng (VI)',$dataRow);
              im_render_pair_input('raise_salary','raise_salary_vi','昇給 (JP)','Tăng lương (VI)',$dataRow);
              im_render_pair_input('benefit_flight','benefit_flight_vi','航空券 (JP)','Vé máy bay (VI)',$dataRow);
              im_render_pair_textarea('benefit_other','benefit_other_vi','その他 (JP)','Khác (VI)',$dataRow,3);
            ?>
          </div>

          <div class="im-sec">
            <h5>6) Phỏng vấn & nhập cảnh / 面接・入国</h5>
            <div class="row">
              <div class="col-md-6">
                <?php echo render_date_input('interview_date','面接日 (JP)', imv($dataRow,'interview_date')); ?>
              </div>
              <div class="col-md-6">
                <?php echo render_date_input('interview_date_vi','Ngày PV (VI)', imv($dataRow,'interview_date_vi')); ?>
              </div>
              <div class="col-md-6">
                <?php echo render_date_input('entry_date','入国予定 (JP)', imv($dataRow,'entry_date')); ?>
              </div>
              <div class="col-md-6">
                <?php echo render_date_input('entry_date_vi','Ngày nhập cảnh (VI)', imv($dataRow,'entry_date_vi')); ?>
              </div>
            </div>

            <div class="row mbot10">
              <div class="col-md-6">
                <?php echo render_date_input('return_date','帰国予定 (JP)', imv($dataRow,'return_date'), ['readonly'=>true]); ?>
              </div>
              <div class="col-md-6">
                <?php echo render_date_input('return_date_vi','Ngày về nước (VI)', imv($dataRow,'return_date_vi'), ['readonly'=>true]); ?>
              </div>
              </div>
            </div>
            <div class="row mbot10">
              <div class="col-md-6">
                <?php echo render_input('interview_place','面接場所 (JP)', imv($dataRow,'interview_place')); ?>
              </div>
              <div class="col-md-6">
                <?php echo render_input('interview_place_vi','Địa điểm PV (VI)', imv($dataRow,'interview_place_vi')); ?>
              </div>
            </div>
          </div>

          <div class="im-sec">
            <h5>7) Liên hệ / 連絡先</h5>
            <div class="im-pair-hint">Nhóm này DB chỉ có 1 bộ cột => nhập chung (không tách VI/JP).</div>
            <?php
              echo render_input('employer_name','Người liên hệ', imv($dataRow,'employer_name'));
              echo render_input('employer_address','Địa chỉ liên hệ', imv($dataRow,'employer_address'));
              echo render_input('employer_phone','Điện thoại liên hệ', imv($dataRow,'employer_phone'));
              echo render_input('employer_email','Email liên hệ', imv($dataRow,'employer_email'));
              echo '<hr class="hr-panel-heading" />';
              echo render_input('pic_name','PIC Name', imv($dataRow,'pic_name'));
              echo render_input('pic_phone','PIC Phone', imv($dataRow,'pic_phone'));
              echo render_input('pic_email','PIC Email', imv($dataRow,'pic_email'));
            ?>
          </div>

          <!--<div class="im-sec">
              
                <h5>8) Gửi cho trường / 提携校へ送付</h5>
                <p class="text-muted">Bấm tích để gửi đơn tuyển đến trường. Bỏ tích thì trường đó sẽ không còn thấy đơn tuyển này trên cổng thông tin.</p>
                <?php if (!empty($partner_schools)): ?>
                  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                    <button type="button" class="btn btn-default btn-sm" id="imCheckAllSchools"><i class="fa fa-check-square-o"></i> Chọn tất cả</button>
                    <button type="button" class="btn btn-default btn-sm" id="imClearAllSchools"><i class="fa fa-square-o"></i> Bỏ chọn tất cả</button>
                  </div>
                  <div class="row">
                    <?php foreach ($partner_schools as $school): ?>
                      <?php $sid = (int) ($school['id'] ?? 0); ?>
                      <div class="col-md-4 col-sm-6">
                        <label style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border:1px solid rgba(0,50,90,.15);border-radius:12px;margin-bottom:10px;cursor:pointer;background:#fff;">
                          <input type="checkbox" class="im-school-checkbox" name="school_ids[]" value="<?php echo $sid; ?>" <?php echo in_array($sid, $selected_school_ids, true) ? 'checked' : ''; ?> style="margin-top:3px;">
                          <span>
                            <strong style="display:block;color:#00325a;"><?php echo html_escape($school['school_name'] ?? ''); ?></strong>
                            <?php if (!empty($school['school_code'])): ?>
                              <small class="text-muted"><?php echo html_escape($school['school_code']); ?></small>
                            <?php endif; ?>
                          </span>
                        </label>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="alert alert-warning" style="margin-bottom:0;">Chưa lấy được danh sách trường liên kết. Kiểm tra lại bảng tài khoản trường hoặc bảng trường đối tác.</div>
                <?php endif; ?>
              </div> -->
          <div class="im-sec">
              <h5>8) Gửi cho trường / 提携校へ送付</h5>
              <p class="text-muted">
                Bấm tích để gửi đơn tuyển đến trường. Bỏ tích thì trường đó sẽ không còn thấy đơn tuyển này trên cổng thông tin.
              </p>
            
              <?php if (!empty($partner_schools)): ?>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                  <button type="button" class="btn btn-default btn-sm" id="imCheckAllSchools">
                    <i class="fa fa-check-square-o"></i> Chọn tất cả
                  </button>
                  <button type="button" class="btn btn-default btn-sm" id="imClearAllSchools">
                    <i class="fa fa-square-o"></i> Bỏ chọn tất cả
                  </button>
                </div>
            
                <div class="row">
                  <?php foreach ($partner_schools as $school): ?>
                    <?php $sid = (int) ($school['id'] ?? 0); ?>
                    <div class="col-md-4 col-sm-6">
                      <label style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border:1px solid rgba(0,50,90,.15);border-radius:12px;margin-bottom:10px;cursor:pointer;background:#fff;">
                        <input type="checkbox"
                               class="im-school-checkbox"
                               name="school_ids[]"
                               value="<?php echo $sid; ?>"
                               <?php echo in_array($sid, $selected_school_ids, true) ? 'checked' : ''; ?>
                               style="margin-top:3px;">
                        <span>
                          <strong style="display:block;color:#00325a;">
                            <?php echo html_escape($school['school_name'] ?? ''); ?>
                          </strong>
                          <?php if (!empty($school['school_code'])): ?>
                            <small class="text-muted"><?php echo html_escape($school['school_code']); ?></small>
                          <?php endif; ?>
                        </span>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="alert alert-warning">
                  Chưa lấy được danh sách trường liên kết. Bạn có thể nhập trường mới bên dưới rồi lưu đơn tuyển.
                </div>
              <?php endif; ?>
            
              <div class="row mtop10">
                <div class="col-md-6">
                  <label for="school_name_new">Thêm trường mới vào danh sách gửi</label>
                  <input type="text"
                         class="form-control"
                         name="school_name_new"
                         id="school_name_new"
                         value=""
                         placeholder="VD: SBS">
                  <small class="text-muted">
                    Nếu nhập tên trường mới ở đây, hệ thống sẽ thêm vào danh mục trường đối tác và tự gửi đơn tuyển này cho trường đó.
                  </small>
                </div>
              </div>
            </div>    
              
              <div class="im-sec">
              
            <h5>Ghi chú trạng thái (tuỳ chọn)</h5>
            <?php echo render_textarea('status_note','Ghi chú', $status_note, ['rows'=>3]); ?>
          </div>

          <div class="text-right">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo $is_edit ? 'Lưu thay đổi' : 'Tạo đơn tuyển'; ?></button>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>

<?php // Load Perfex core JS (jQuery, app scripts) BEFORE custom scripts on this page ?>

<script>
(function () {
  function qs(sel, root){ return (root||document).querySelector(sel); }
  function qsa(sel, root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }
  
  //
  function toggleAllSchools(checked){ qsa('.im-school-checkbox').forEach(function(el){ el.checked = !!checked; }); }

  function getAdminUrl(){
    if (typeof window.admin_url !== 'undefined' && window.admin_url) return window.admin_url;
    return <?php echo json_encode(admin_url()); ?>;
  }

  function getCsrfPair(){
    if (typeof window.csrfData !== 'undefined' && window.csrfData && window.csrfData.token_name) {
      return { name: window.csrfData.token_name, value: window.csrfData.hash };
    }
    var hidden = qs('input[type="hidden"][name]');
    if (hidden && hidden.value) return { name: hidden.name, value: hidden.value };
    return null;
  }
  
  //
  var checkAllBtn = qs('#imCheckAllSchools');
  var clearAllBtn = qs('#imClearAllSchools');
  if (checkAllBtn) checkAllBtn.addEventListener('click', function(){ toggleAllSchools(true); });
  if (clearAllBtn) clearAllBtn.addEventListener('click', function(){ toggleAllSchools(false); });

  function currentFromTo(){
    var fromEl = qs('#imTrFrom');
    var toEl   = qs('#imTrTo');
    var from = fromEl ? (fromEl.value || 'ja') : 'ja';
    var to   = toEl ? (toEl.value || 'vi') : 'vi';
    if (from === 'auto') from = 'ja'; // for per-field, default JP
    return {from: from, to: to};
  }

  function findSourceText(viEl){
    var name = viEl.getAttribute('name') || '';
    var src = '';

    // 1) Mapping by name: xxx_vi -> xxx_jp or xxx
    if (name) {
      var base = name.replace(/_vi$/, '');
      var jpName = base + '_jp';
      var jpEl = qs('[name="'+jpName.replace(/"/g,'\"')+'"]');
      if (jpEl) src = (jpEl.value || '').toString();

      if (!src) {
        var baseEl = qs('[name="'+base.replace(/"/g,'\"')+'"]');
        if (baseEl && baseEl !== viEl) src = (baseEl.value || '').toString();
      }
    }

    // 2) Fallback by DOM pairing: try nearest row/container and pick JP field
    if (!src) {
      var row = viEl.closest('.row') || viEl.closest('.form-group') || viEl.closest('.panel-body') || viEl.parentElement;
      if (row) {
        // prefer fields with name ending _jp
        var jpCandidates = qsa('input[name$="_jp"], textarea[name$="_jp"], select[name$="_jp"]', row);
        if (jpCandidates.length) src = (jpCandidates[0].value || '').toString();

        if (!src) {
          // otherwise take the nearest previous input/textarea/select before this VI field
          var all = qsa('input, textarea, select', row).filter(function(el){
            var n = el.getAttribute('name') || '';
            return el !== viEl && n && !/_vi$/.test(n);
          });
          // pick the one that appears before viEl in DOM
          var viIndex = -1;
          var nodes = qsa('input, textarea, select', row);
          for (var i=0;i<nodes.length;i++){ if (nodes[i]===viEl){ viIndex=i; break; } }
          if (viIndex > 0){
            for (var j=viIndex-1;j>=0;j--){
              var el = nodes[j];
              var n = el.getAttribute('name') || '';
              if (el !== viEl && n && !/_vi$/.test(n)) { src = (el.value||'').toString(); break; }
            }
          }
          if (!src && all.length) src = (all[0].value || '').toString();
        }
      }
    }

    return (src || '').toString();
  }

  function addBtn(viEl){
    if (viEl.dataset.imHasBtn === '1') return;
    viEl.dataset.imHasBtn = '1';

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-xs btn-info im-translate-single-btn';
    btn.style.marginTop = '6px';
    btn.style.whiteSpace = 'nowrap';
    btn.innerHTML = '<i class="fa fa-language"></i> Dịch ô này';

    btn.addEventListener('click', function(){
      var src = (findSourceText(viEl) || '').toString().trim();
      if (!src) {
        if (typeof window.alert_float === 'function') window.alert_float('warning', 'Không tìm thấy nội dung JP để dịch.');
        return;
      }

      btn.disabled = true;

      var ft = currentFromTo();
      var endpoint = getAdminUrl() + 'internship_management/internship_job_orders/im_google_translate_field';
      var csrf = getCsrfPair();

      var params = new URLSearchParams();
      params.append('text', src);
      params.append('from', ft.from);
      params.append('to', ft.to);
      if (csrf) params.append(csrf.name, csrf.value);

      fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: params.toString(),
        credentials: 'same-origin'
      }).then(function(r){ return r.text(); })
        .then(function(t){
          var resp = null;
          try { resp = JSON.parse(t); } catch(e) {}
          if (resp && resp.success) {
            viEl.value = resp.translated || '';
            if (typeof window.alert_float === 'function') window.alert_float('success', 'Đã dịch ô.');
          } else {
            if (typeof window.alert_float === 'function') window.alert_float('warning', (resp && resp.error) ? resp.error : 'Dịch thất bại.');
          }
        })
        .catch(function(){
          if (typeof window.alert_float === 'function') window.alert_float('warning', 'Lỗi mạng hoặc server.');
        })
        .finally(function(){ btn.disabled = false; });
    });

    viEl.insertAdjacentElement('afterend', btn);
  }

  document.addEventListener('DOMContentLoaded', function(){
    qsa('input[name$="_vi"], textarea[name$="_vi"], select[name$="_vi"]').forEach(addBtn);
  });
})();
</script>


<script>
(function(){
  function qs(sel, root){ return (root||document).querySelector(sel); }

  function parseYMD(s){
    s = (s||'').toString().trim();
    if(!s) return null;
    // Expect yyyy-mm-dd (Perfex date input). Fallback: replace '/' with '-'
    s = s.replace(/\//g,'-');
    var m = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
    if(!m) return null;
    var y = parseInt(m[1],10), mo = parseInt(m[2],10)-1, d = parseInt(m[3],10);
    var dt = new Date(y, mo, d);
    if(isNaN(dt.getTime())) return null;
    return dt;
  }

  function fmtYMD(dt){
    var yyyy = dt.getFullYear();
    var mm = String(dt.getMonth()+1).padStart(2,'0');
    var dd = String(dt.getDate()).padStart(2,'0');
    return yyyy+'-'+mm+'-'+dd;
  }

  function getMonths(){
    var m1 = qs('[name="contract_months_vi"]');
    var m2 = qs('[name="contract_months"]');
    var v = (m1 && m1.value) ? m1.value : ((m2 && m2.value) ? m2.value : '');
    v = (v||'').toString().trim();
    var n = parseInt(v,10);
    return isNaN(n) ? null : n;
  }

  function getEntryDate(){
    var e1 = qs('[name="entry_date_vi"]');
    var e2 = qs('[name="entry_date"]');
    var v = (e1 && e1.value) ? e1.value : ((e2 && e2.value) ? e2.value : '');
    return parseYMD(v);
  }

  function calcReturn(){
    var months = getMonths();
    var entry = getEntryDate();
    var outJP = qs('[name="return_date"]');
    var outVI = qs('[name="return_date_vi"]');

    if(!outJP && !outVI) return;

    if(months === null || !entry){
      // If missing input, don't overwrite user value
      return;
    }

    // Add months
    var dt = new Date(entry.getTime());
    var day = dt.getDate();
    dt.setMonth(dt.getMonth() + months);

    // Handle month overflow (e.g., Jan 31 + 1 month)
    if(dt.getDate() !== day){
      // go to last day of previous month
      dt.setDate(0);
    }

    var ymd = fmtYMD(dt);
    if(outJP) outJP.value = ymd;
    if(outVI) outVI.value = ymd;
  }

  document.addEventListener('DOMContentLoaded', function(){
    ['entry_date','entry_date_vi','contract_months','contract_months_vi'].forEach(function(n){
      var el = qs('[name="'+n+'"]');
      if(!el) return;
      el.addEventListener('change', calcReturn);
      el.addEventListener('input', calcReturn);
    });
    // initial calc
    setTimeout(calcReturn, 300);
  });
})();
</script>

<?php init_tail(); ?>

<script>
  // Safe globals for this page (avoid broken JS when some module prints unsafe chars)
  window.admin_url = window.admin_url || <?php echo json_encode(admin_url()); ?>;
  window.csrfData = window.csrfData || <?php echo json_encode([
    'token_name' => $this->security->get_csrf_token_name(),
    'hash'       => $this->security->get_csrf_hash(),
  ]); ?>;
</script>


<script>
  // Auto-translate after AI parse (JP filled but VI may be empty)
  window.IM_AUTO_TRANSLATE_AFTER_AI = false; // Step 1 only fills JP. Step 2 translates JP->VI by button.
</script>

<script>
(function($){
  // Track last focused field for paste/translate
  window.__imActiveEl = null;
  document.addEventListener('focusin', function(e){
    var t = e.target;
    if (!t) return;
    if (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.tagName === 'SELECT') {
      window.__imActiveEl = t;
    }
  }, true);
  "use strict";

  // Endpoints (server-side)
  var IM_ENDPOINT_TRANSLATE_FIELD = "<?php echo admin_url('internship_management/internship_job_orders/im_google_translate_field'); ?>";
  var IM_ENDPOINT_FILL_VI        = "<?php echo admin_url('internship_management/internship_job_orders/google_translate_fill_vi'); ?>";

  // Elements
  var $in  = $('#imTrIn');
  var $out = $('#imTrOut');
  var $from= $('#imTrFrom');
  var $to  = $('#imTrTo');

  // Track last focused input/textarea in the big form
  var lastTarget = null;
  $(document).on('focusin click', '#job_order_form input, #job_order_form textarea, #job_order_form select', function(){
    lastTarget = this;
  });

  function alertOk(msg){
    if (typeof alert_float === 'function') { alert_float('success', msg); }
    else { alert(msg); }
  }
  function alertWarn(msg){
    if (typeof alert_float === 'function') { alert_float('warning', msg); }
    else { alert(msg); }
  }
  function alertErr(msg){
    if (typeof alert_float === 'function') { alert_float('danger', msg); }
    else { alert(msg); }
  }

  function normalizeText(v){
    if (v === null || typeof v === 'undefined') return '';
    return String(v).replace(/\r\n/g, '\n').trim();
  }

  function setBtnLoading($btn, loading){
    if (!$btn || !$btn.length) return;
    $btn.prop('disabled', !!loading);
    if (loading) $btn.addClass('disabled');
    else $btn.removeClass('disabled');
  }

  async function postForm(url, data){
    // Prefer jQuery (Perfex already has CSRF handling in $.ajaxSetup)
    return $.ajax({
      url: url,
      method: 'POST',
      dataType: 'json',
      data: data
    });
  }

  // 1) Paste translated result into currently selected field
  $(document).off('click.imTrPaste').on('click.imTrPaste', '#imTrPaste', function(e){
    e.preventDefault();

    var txt = normalizeText($out.val());
    if (!txt) {
      alertWarn('Chưa có nội dung kết quả để dán. Hãy bấm "Dịch" trước.');
      return;
    }

    if (!lastTarget) {
      alertWarn('Bạn chưa chọn ô cần dán. Hãy click vào 1 ô JP/VI bên dưới trước.');
      return;
    }

    // Only allow input/textarea/select
    var $t = $(lastTarget);
    if (!$t.length || !($t.is('input') || $t.is('textarea') || $t.is('select'))) {
      alertWarn('Ô đang chọn không hợp lệ.');
      return;
    }

    // If target is select, we can't paste arbitrary string
    if ($t.is('select')) {
      alertWarn('Không thể dán vào ô chọn (select).');
      return;
    }

    $t.val(txt).trigger('input').trigger('change');
    try { $t.focus(); } catch(err){}

    alertOk('Đã dán kết quả vào ô đang chọn.');
  });

  // 2) Translate text in the top box (JP->VI by default)
  $(document).off('click.imTrDo').on('click.imTrDo', '#imTrDo', async function(e){
    e.preventDefault();

    var txt = normalizeText($in.val());
    if (!txt) {
      // Fallback: if user selected/focused a field in the form, try lấy nội dung JP tương ứng để dịch
      var active = window.__imActiveEl || document.activeElement;
      if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA')) {
        var $act = $(active);
        var name = $act.attr('name') || '';
        var valAct = normalizeText($act.val());
        // Nếu đang focus ô JP thì dùng luôn; nếu focus ô VI thì tìm ô JP pair
        var candidate = '';
        var isJP = /_jp$/.test(name) || /\[jp\]$/.test(name) || /jp\]$/.test(name);
        var isVI = /_vi$/.test(name) || /_vn$/.test(name) || /\[vi\]$/.test(name) || /\[vn\]$/.test(name);
        if (isJP && valAct) {
          candidate = valAct;
        } else if (isVI) {
          var jpName = name
            .replace(/_vi$/, '_jp')
            .replace(/_vn$/, '_jp')
            .replace(/\[vi\]$/, '[jp]')
            .replace(/\[vn\]$/, '[jp]');
          var $jp = jpName ? $('[name="' + jpName.replace(/"/g,'\\"') + '"]') : $();
          candidate = normalizeText(($jp.length ? $jp.val() : '')) || valAct;
        } else {
          candidate = valAct;
        }
        if (candidate) {
          txt = candidate;
          $in.val(candidate);
        }
      }
      if (!txt) {
        alertWarn('Vui lòng nhập nội dung cần dịch.');
        return;
      }
    }


    var from = ($from.val() || 'ja');
    var to   = ($to.val() || 'vi');

    var $btn = $(this);
    setBtnLoading($btn, true);

    try {
      var resp = await postForm(IM_ENDPOINT_TRANSLATE_FIELD, {text: txt, from: from, to: to});
      if (resp && resp.success && typeof resp.translated !== 'undefined') {
        $out.val(resp.translated).trigger('input').trigger('change');
        alertOk('Đã dịch xong.');
      } else {
        alertErr((resp && resp.error) ? resp.error : 'Dịch thất bại.');
      }
    } catch (err) {
      alertErr('Lỗi gọi server dịch: ' + (err && err.status ? ('HTTP ' + err.status) : ''));
    } finally {
      setBtnLoading($btn, false);
    }
  });

  // 3) Translate all missing VI fields from JP fields (two-step)
  $(document).off('click.imTrFillMissing').on('click.imTrFillMissing', '#imTrFillMissing', async function(e){
    e.preventDefault();

    var $btn = $(this);
    setBtnLoading($btn, true);

    try {
      // Collect all form values
      var values = {};
      $('#job_order_form').find('[name]').each(function(){
        var name = this.name;
        if (!name) return;
        values[name] = $(this).val();
      });

      var resp = await postForm(IM_ENDPOINT_FILL_VI, {values: values});
      if (resp && resp.success && resp.data) {
        var count = 0;
        $.each(resp.data, function(fieldName, translatedVal){
          var $field = $('#job_order_form').find('[name="'+ fieldName.replace(/"/g,'\\"') +'"]');
          if ($field.length) {
            // only fill if empty
            var cur = normalizeText($field.val());
            if (!cur) {
              $field.val(translatedVal).trigger('input').trigger('change');
              count++;
            }
          }
        });
        alertOk('Đã dịch & điền ' + count + ' ô VI còn trống.');
      } else {
        alertErr((resp && resp.error) ? resp.error : 'Không dịch được các ô còn trống.');
      }
    } catch (err) {
      alertErr('Lỗi gọi server dịch các ô còn trống: ' + (err && err.status ? ('HTTP ' + err.status) : ''));
    } finally {
      setBtnLoading($btn, false);
    }
  });

})(jQuery);
</script>

