<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
// Clone data (nếu tạo ứng tuyển mới từ hồ sơ cũ)
$clone = isset($clone) && is_array($clone) ? $clone : [];
$clone_from_id = isset($clone_from_id) ? (int)$clone_from_id : 0;
?>

<style>
.label-title { font-weight:600; margin-bottom:4px; display:block; }
.ai-badge { background:#2563eb; color:#fff; padding:3px 8px; border-radius:6px; font-size:11px; }
.preview-avatar {
    width:110px;height:110px;border-radius:8px;object-fit:cover;border:1px solid #ddd;margin-top:6px;
}

/* Repeater blocks (data mở – thêm dòng) */
.repeater-box {
    background:#f8fafc;
    border:1px solid #e2e8f0;
    padding:12px;
    border-radius:8px;
    margin-bottom:10px;
}
.repeater-box .remove-row {
    color:#dc2626;
    cursor:pointer;
    font-size:13px;
    float:right;
}
</style>

<div id="wrapper">
<div class="content">
<div class="panel_s">
<div class="panel-body">

<h4 class="bold">
    <i class="fa fa-user-plus"></i> Thêm ứng viên mới
    <span class="ai-badge">AI Auto Extract</span>
</h4>

<?php if (!empty($clone_from_id)) : ?>
    <div class="alert alert-info" style="margin-top:10px;">
        Đang tạo <b>ứng tuyển mới</b> từ hồ sơ ứng viên cũ #<?= (int)$clone_from_id; ?>. Vui lòng chọn <b>Đơn tuyển</b> mới.
    </div>
<?php endif; ?>
<hr>

<form id="appForm" method="post"
      enctype="multipart/form-data"
      action="<?= admin_url('internship_management/internship_applications/create'); ?>">

    <!-- CSRF -->
    <input type="hidden"
           name="<?= $this->security->get_csrf_token_name(); ?>"
           value="<?= $this->security->get_csrf_hash(); ?>">

    <!-- Avatar do AI tạo (tên file trong uploads/internship_avatar/) -->
    <input type="hidden" name="avatar_ai_file" id="avatar_ai_file">
    <!-- Base64 chỉ dùng để preview (không bắt buộc lưu DB) -->
    <input type="hidden" name="avatar_base64" id="avatar_base64">

    <!-- UPLOAD CV -->
    <?php
        // Khi "Ứng tuyển lại" (clone), nếu đã có CV thì KHÔNG bắt buộc upload lại.
        $has_existing_cv = isset($clone) && !empty($clone['cv_file']);
    ?>
    <div class="form-group">
        <label class="label-title">Upload CV (PDF / Word / Ảnh)</label>

        <?php if ($has_existing_cv): ?>
            <p class="text-muted" style="margin-bottom:6px;">
                CV hiện tại: <b><?= e($clone['cv_file']); ?></b>
                <?php if (!empty($clone['cv_file'])): ?>
                    <a href="<?= base_url('uploads/internship_cv/'.$clone['cv_file']); ?>" target="_blank" class="mleft5">(Xem)</a>
                <?php endif; ?>
                <br>
                <small>Không cần upload lại nếu CV không đổi. Nếu muốn thay CV, chọn file mới bên dưới.</small>
            </p>
        <?php endif; ?>

        <input type="file" name="cv_file" id="cv_file"
               class="form-control"
               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" <?= $has_existing_cv ? '' : 'required'; ?>>

        <?php if ($has_existing_cv): ?>
            <input type="hidden" name="existing_cv_file" value="<?= e($clone['cv_file']); ?>">
            <input type="hidden" name="existing_cv_file_type" value="<?= e($clone['cv_file_type'] ?? ''); ?>">
        <?php endif; ?>
    </div>

    <!-- AI BUTTON -->
    <button type="button" class="btn btn-info" id="btnExtractAI">
        <i class="fa fa-magic"></i> Trích xuất thông tin bằng AI
    </button>

    <hr>

    <div class="row">
        <!-- LEFT -->
        <div class="col-md-6">

            <h4><b>1. Thông tin cá nhân</b></h4>

            <div class="form-group">
                <label class="label-title">Họ và tên</label>
                <input type="text" class="form-control" name="full_name"
                       value="<?= html_escape($clone['full_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="label-title">Ngày sinh</label>
                <input type="text" class="form-control datepicker" name="birthday"
                       value="<?= html_escape(!empty($clone['birthday']) ? _d($clone['birthday']) : ''); ?>">
            </div>

            <div class="form-group">
                <label class="label-title">Giới tính</label>
                <select name="gender" class="form-control">
                    <option value="">— Chọn giới tính —</option>
                    <option value="male" <?= (isset($clone['gender']) && $clone['gender']=='male') ? 'selected' : ''; ?>>Nam</option>
                    <option value="female" <?= (isset($clone['gender']) && $clone['gender']=='female') ? 'selected' : ''; ?>>Nữ</option>
                </select>
            </div>

            <div class="form-group">
                <label class="label-title">Email</label>
                <input type="email" class="form-control" name="email"
                       value="<?= html_escape($clone['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="label-title">SĐT sinh viên</label>
                <input type="text" class="form-control" name="phone_student"
                       value="<?= html_escape($clone['phone_student'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="label-title">SĐT phụ huynh (chính)</label>
                <input type="text" class="form-control" name="phone_parent"
                       value="<?= html_escape($clone['phone_parent'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="label-title">Địa chỉ</label>
                <input type="text" class="form-control" name="address"
                       value="<?= html_escape($clone['address'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="label-title">Trường đang theo học</label>
                    <?php
                        $school_current = trim((string)($clone['school_name'] ?? ''));
                        $schools = isset($schools) && is_array($schools) ? $schools : ['HUTECH','UEF','VHU','VJIT','VLSG','SONADEZI'];
                    
                        $schools = array_map('trim', $schools);
                        $schools = array_filter($schools, function ($v) {
                            return $v !== '' && $v !== '__new__';
                        });
                        $schools = array_values(array_unique($schools));
                    
                        $school_in_list = in_array($school_current, $schools, true);
                    ?>
                <select class="form-control" name="school_name" id="school_name">
                    <option value="">— Chọn trường —</option>
                    <?php foreach ($schools as $s): ?>
                        <option value="<?= html_escape($s); ?>" <?= ($school_current === $s) ? 'selected' : ''; ?>><?= html_escape($s); ?></option>
                    <?php endforeach; ?>
                    <option value="__new__" <?= (!$school_in_list && $school_current !== '') ? 'selected' : ''; ?>>Thêm Trường Mới</option>
                </select>

                <input type="text" class="form-control" name="school_name_new" id="school_name_new"
                       placeholder="Nhập tên trường mới"
                       value="<?= (!$school_in_list ? html_escape($school_current) : ''); ?>"
                       style="margin-top:8px; display: <?= (!$school_in_list && $school_current !== '') ? 'block' : 'none'; ?>;">
            </div>

            <div class="form-group">
                <label class="label-title">Chuyên ngành</label>
                <input type="text" class="form-control" name="major"
                       value="<?= html_escape($clone['major'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="label-title">Trình độ tiếng Nhật (JLPT)</label>
                <?php $jlpt_current = trim((string)($clone['japanese_level'] ?? '')); ?>
                <select class="form-control" name="japanese_level" id="japanese_level">
                    <option value="">— Chọn trình độ —</option>
                    <option value="none" <?= ($jlpt_current === 'none') ? 'selected' : ''; ?>>Chưa có bằng</option>
                    <option value="N5" <?= ($jlpt_current === 'N5') ? 'selected' : ''; ?>>N5</option>
                    <option value="N4" <?= ($jlpt_current === 'N4') ? 'selected' : ''; ?>>N4</option>
                    <option value="N3" <?= ($jlpt_current === 'N3') ? 'selected' : ''; ?>>N3</option>
                    <option value="N2" <?= ($jlpt_current === 'N2') ? 'selected' : ''; ?>>N2</option>
                    <option value="N1" <?= ($jlpt_current === 'N1') ? 'selected' : ''; ?>>N1</option>
                    <option value="__other__" <?= (!in_array($jlpt_current, ['','none','N5','N4','N3','N2','N1'], true) && $jlpt_current !== '') ? 'selected' : ''; ?>>Trình độ khác</option>
                </select>

                <?php
                    $jlpt_is_other = (!in_array($jlpt_current, ['','none','N5','N4','N3','N2','N1'], true) && $jlpt_current !== '');
                ?>
                <input type="text" class="form-control" name="japanese_level_other" id="japanese_level_other"
                       placeholder="Nhập trình độ khác (vd: J-TEST, NAT, ... )"
                       value="<?= $jlpt_is_other ? html_escape($jlpt_current) : ''; ?>"
                       style="margin-top:8px; display: <?= $jlpt_is_other ? 'block' : 'none'; ?>;">
            </div>

            <div class="form-group">
                <label class="label-title">Tiếng Anh</label>
                <input type="text" class="form-control" name="english_level">
            </div>

            <!-- AVATAR -->
            <div class="form-group">
                <label class="label-title">Ảnh đại diện (Avatar)</label>
                <input type="file" name="avatar" class="form-control" accept=".jpg,.jpeg,.png">
                <img id="avatar_preview" class="preview-avatar" style="display:none;">
            </div>

            <hr>

            <!-- DYNAMIC PARENT PHONES -->
            <h4><b>2. Số điện thoại phụ huynh (nhiều số)</b></h4>

            <div id="parentPhoneRepeater">
                <div class="repeater-box repeat-parent-phone">
                    <label>SĐT phụ huynh</label>
                    <input type="text" class="form-control" name="parent_phones[]" placeholder="Nhập số điện thoại">
                    <span class="remove-row" onclick="removeRepeaterRow(this)">X</span>
                </div>
            </div>

            <button type="button" class="btn btn-default btn-sm" onclick="addParentPhone()">
                + Thêm số phụ huynh
            </button>

            <hr>

            <!-- DYNAMIC ZALO LINKS -->
            <h4><b>3. Link Zalo</b></h4>

            <div id="zaloRepeater">
                <div class="repeater-box repeat-zalo">
                    <label>Link Zalo</label>
                    <input type="text" class="form-control" name="zalo_links[]" placeholder="https://zalo.me/...">
                    <span class="remove-row" onclick="removeRepeaterRow(this)">X</span>
                </div>
            </div>

            <button type="button" class="btn btn-default btn-sm" onclick="addZaloLink()">
                + Thêm link Zalo
            </button>

        </div>

        <!-- RIGHT -->
        <div class="col-md-6">

            <h4><b>4. Thông tin tuyển dụng</b></h4>

            <!-- <div class="form-group">
                <label class="label-title">Đơn tuyển (Job Order)</label>
                <select name="job_order_id" id="job_order_id" class="form-control" required>
                    <option value="">— Chọn đơn tuyển —</option>
                    <?php foreach ($job_orders as $job): ?>
                        <option value="<?= $job['id']; ?>">
                            <?= $job['job_title'] . ' — ' . $job['company_name_vi']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Chọn đơn tuyển sẽ tự động điền thông tin tiếp nhận (nếu đơn tuyển có dữ liệu).</small>
            </div> -->
            
            <div class="form-group">
                <label class="label-title">Đơn tuyển (Job Order)</label>
            
                <select
                    name="job_order_id"
                    id="job_order_id"
                    class="selectpicker"
                    data-live-search="true"
                    data-width="100%"
                    data-size="10"
                    data-none-selected-text="— Chọn đơn tuyển —"
                    data-live-search-placeholder="Nhập mã đơn, tên công ty, ngành..."
                    required>
                    <option value="">— Chọn đơn tuyển —</option>
            
                    <?php foreach ($job_orders as $job): ?>
                        <?php
                            $jobId      = (int)($job['id'] ?? 0);
                            $title      = trim((string)($job['job_title_vi'] ?? ($job['job_title'] ?? '')));
                            $companyVi  = trim((string)($job['company_name_vi'] ?? ''));
                            $companyJp  = trim((string)($job['company_name_jp'] ?? ''));
                            $company    = $companyVi !== '' ? $companyVi : $companyJp;
                            $major      = trim((string)($job['major_vi'] ?? ($job['major_jp'] ?? '')));
                            $text       = trim($title . ' — ' . $company, ' —');
            
                            $tokens = implode(' ', array_filter([
                                $jobId,
                                $title,
                                $companyVi,
                                $companyJp,
                                $company,
                                $major,
                            ]));
                        ?>
                        <option
                            value="<?= $jobId; ?>"
                            data-tokens="<?= html_escape($tokens); ?>">
                            <?= html_escape(($text !== '' ? $text : ('Đơn tuyển #' . $jobId)) . ($major !== '' ? ' | ' . $major : '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            
                <small class="text-muted">
                    Chọn đơn tuyển sẽ tự động điền thông tin tiếp nhận (nếu đơn tuyển có dữ liệu).
                </small>
            </div>

            <div class="form-group">
                <label class="label-title">Công ty tiếp nhận</label>
                <input type="text" class="form-control" name="receiver_company">
            </div>

            <div class="form-group">
                <label class="label-title">Tỉnh tiếp nhận</label>
                <input type="text" class="form-control" name="receiver_prefecture">
            </div>

            <div class="form-group">
                <label class="label-title">Địa chỉ tiếp nhận</label>
                <input type="text" class="form-control" name="receiver_address">
            </div>

            <div class="form-group">
                <label class="label-title">Ngày phỏng vấn</label>
                <input type="text" class="form-control datepicker" name="interview_date">
            </div>

            <div class="form-group">
                <label class="label-title">Ngày nhập cảnh (dự kiến)</label>
                <input type="text" class="form-control datepicker" name="entry_date">
            </div>

            <div class="form-group">
                <label class="label-title">Thời gian thực tập (tháng)</label>
                <input type="number" class="form-control" name="months" min="1">
            </div>

            <div class="form-group">
                <label class="label-title">Ngày về nước (dự kiến)</label>
                <input type="text" class="form-control datepicker" name="return_date">
            </div>

            <div class="form-group">
                <label class="label-title">Ghi chú</label>
                <textarea class="form-control" name="note" rows="3"></textarea>
            </div>

            <hr>

            <!-- DYNAMIC EXTRA FILES -->
            <h4><b>5. Tài liệu bổ sung</b></h4>

            <div id="fileRepeater">
                <div class="repeater-box repeat-file">
                    <label>Chọn file</label>
                    <input type="file" name="extra_files[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                    <span class="remove-row" onclick="removeRepeaterRow(this)">X</span>
                </div>
            </div>

            <button type="button" class="btn btn-default btn-sm" onclick="addExtraFile()">
                + Thêm file
            </button>

        </div>
    </div>

    <hr>

    <button class="btn btn-primary" type="submit">
        <i class="fa fa-save"></i> Lưu ứng viên
    </button>

    <a href="<?= admin_url('internship_management/internship_applications'); ?>"
       class="btn btn-default">Quay lại</a>

</form>

</div>
</div>
</div>
</div>

<?php init_tail(); ?>

<script>
// =========================
// PREVIEW AVATAR TỰ UPLOAD
// =========================
$('input[name="avatar"]').on('change', function(e){
    let file = e.target.files[0];
    if (file) {
        $('#avatar_preview').attr('src', URL.createObjectURL(file)).show();
        // Xoá avatar AI nếu user chọn ảnh thủ công
        $('#avatar_ai_file').val('');
        $('#avatar_base64').val('');
    }
});

// =========================
// AI EXTRACT – FULL
// =========================
$("#btnExtractAI").click(function () {

    let file = $("#cv_file")[0].files[0];
    if (!file) {
        alert("Vui lòng chọn CV trước.");
        return;
    }

    let data = new FormData();
    data.append("cv_file", file);

    // CSRF phải gửi kèm
    data.append("<?= $this->security->get_csrf_token_name(); ?>",
                "<?= $this->security->get_csrf_hash(); ?>");

    $.ajax({
        url: "<?= admin_url('internship_management/internship_applications/extract_from_cv'); ?>",
        type: "POST",
        data: data,
        processData: false,
        contentType: false,
        dataType: "text", // luôn nhận string, tự JSON.parse để tránh Perfex parse sai

        beforeSend: function () {
            $("#btnExtractAI").html('<i class="fa fa-spinner fa-spin"></i> Đang phân tích...');
        },

        success: function (responseText) {

            console.log("RAW backend:", responseText);

            let res = null;
            try {
                res = JSON.parse(responseText);
            } catch (e) {
                alert("Backend trả về sai định dạng (không phải JSON). Kiểm tra Console.");
                console.error("JSON parse error:", e);
                $("#btnExtractAI").html('<i class="fa fa-magic"></i> Trích xuất thông tin bằng AI');
                return;
            }

            if (!res || typeof res !== 'object') {
                alert("Dữ liệu AI không hợp lệ.");
                $("#btnExtractAI").html('<i class="fa fa-magic"></i> Trích xuất thông tin bằng AI');
                return;
            }

            if (!res.success) {
                alert(res.message || "AI không trả dữ liệu.");
                $("#btnExtractAI").html('<i class="fa fa-magic"></i> Trích xuất thông tin bằng AI');
                return;
            }

            let d = res.data || {};

            // Fill form nếu field tồn tại
            Object.keys(d).forEach(function(k){
                let input = $("[name='" + k + "']");
                if (input.length && k !== 'avatar_base64' && k !== 'avatar_file') {
                    input.val(d[k]);
                }
            });

            // Special: school_name (dropdown + add new)
            /*if (d.school_name) {
                d.school_name = String(d.school_name || '').trim();
            
                if (d.school_name.toUpperCase() === 'VLSC') {
                    d.school_name = 'VLSG';
                }
            
                var list = ['HUTECH','UEF','VHU','VJIT','VLSG','SONADEZI'];*/
            if (d.school_name) {
                d.school_name = String(d.school_name || '').trim();
            
                var schoolCompact = d.school_name
                    .toUpperCase()
                    .replace(/[—–]/g, '-')
                    .replace(/[^A-Z0-9]/g, '');
            
                if (schoolCompact === 'VLSC' || schoolCompact === 'VLSG') {
                    d.school_name = 'VLSG';
                }
            
                if (schoolCompact === 'HUTECHVJIT' || schoolCompact === 'VJITHUTECH' || schoolCompact === 'VJIT') {
                    d.school_name = 'VJIT';
                }
            
                var list = ['HUTECH','UEF','VHU','VJIT','VLSG','SONADEZI'];
                if (list.indexOf(d.school_name) !== -1) {
                    $('#school_name').val(d.school_name).trigger('change');
                    $('#school_name_new').val('').hide();
                } else {
                    $('#school_name').val('__new__').trigger('change');
                    $('#school_name_new').val(d.school_name).show();
                }
            }

            // Avatar base64 để preview
            if (d.avatar_base64) {
                $('#avatar_preview').attr('src', d.avatar_base64).show();
                $('#avatar_base64').val(d.avatar_base64);
            }

            // Tên file avatar đã được backend lưu vào uploads/internship_avatar/
            if (d.avatar_file) {
                $('#avatar_ai_file').val(d.avatar_file);
            }

            alert("Đã tự động điền thành công!");
        },

        error: function(xhr) {
            console.log("AJAX ERROR:", xhr.responseText);
            alert("Lỗi hệ thống hoặc AI.");
        },

        complete: function(){
            $("#btnExtractAI").html('<i class="fa fa-magic"></i> Trích xuất thông tin bằng AI');
        }
    });
});

// =========================
// DATA MỞ – REPEATER
// =========================
function removeRepeaterRow(el){
    var box = el.parentNode;
    var repeater = box.parentNode;

    if (repeater.children.length > 1){
        box.remove();
    } else {
        alert("Cần ít nhất 1 dòng.");
    }
}

function addParentPhone(){
    var clone = $(".repeat-parent-phone:first").clone();
    clone.find("input").val("");
    $("#parentPhoneRepeater").append(clone);
}

function addZaloLink(){
    var clone = $(".repeat-zalo:first").clone();
    clone.find("input").val("");
    $("#zaloRepeater").append(clone);
}

function addExtraFile(){
    var clone = $(".repeat-file:first").clone();
    clone.find("input").val("");
    $("#fileRepeater").append(clone);
}
// =========================
// AUTO CALC RETURN DATE
// =========================
function calcReturnDate(){
    let entry = $("[name='entry_date']").val();
    let months = parseInt($("[name='months']").val());

    if (!entry || !months || months <= 0) return;

    // Convert to Date object
    let parts = entry.split('/');
    if (parts.length === 3) {
        // Perfex datepicker format: dd/mm/yyyy
        let d = parseInt(parts[0]);
        let m = parseInt(parts[1]) - 1;
        let y = parseInt(parts[2]);

        let date = new Date(y, m, d);
        date.setMonth(date.getMonth() + months);

        let retDay = ("0" + date.getDate()).slice(-2);
        let retMonth = ("0" + (date.getMonth() + 1)).slice(-2);
        let retYear = date.getFullYear();

        $("[name='return_date']").val(retDay + "/" + retMonth + "/" + retYear);
    }
}

// Trigger khi user thay đổi ngày nhập cảnh hoặc thời hạn
$("[name='entry_date']").on("change", calcReturnDate);
$("[name='months']").on("keyup change", calcReturnDate);
</script>

<script>
// =========================
// SCHOOL: "THÊM TRƯỜNG MỚI"
// =========================
$('#school_name').on('change', function(){
    var v = $(this).val();
    if (v === '__new__') {
        $('#school_name_new').val('').show().focus();
    } else {
        $('#school_name_new').hide();
    }
});

// =========================
// JLPT: "TRÌNH ĐỘ KHÁC"
// =========================
$('#japanese_level').on('change', function(){
    var v = $(this).val();
    if (v === '__other__') {
        $('#japanese_level_other').val('').show().focus();
    } else {
        $('#japanese_level_other').hide();
    }
});

// =========================
// INIT SEARCHABLE JOB ORDER SELECT
// =========================
if ($.fn.selectpicker) {
    $('#job_order_id').selectpicker();
    $('#job_order_id').selectpicker('refresh');
}

// =========================
// JOB ORDER AUTO FILL
// =========================
/*$('#job_order_id').on('change', function(){
    var jobId = parseInt($(this).val() || '0');
    if (!jobId) return;

    $.ajax({
        url: "<?= admin_url('internship_management/internship_applications/job_order_info/'); ?>" + jobId,
        type: 'GET',
        dataType: 'json',
        success: function(res){
            if (!res || !res.success || !res.data) return;
            var d = res.data;

            // Only fill if empty (avoid overriding manual edits)
            if (d.receiver_company && !$('[name="receiver_company"]').val()) $('[name="receiver_company"]').val(d.receiver_company);
            if (d.receiver_prefecture && !$('[name="receiver_prefecture"]').val()) $('[name="receiver_prefecture"]').val(d.receiver_prefecture);
            if (d.receiver_address && !$('[name="receiver_address"]').val()) $('[name="receiver_address"]').val(d.receiver_address);
            if (d.interview_date && !$('[name="interview_date"]').val()) $('[name="interview_date"]').val(d.interview_date);
            if (d.entry_date && !$('[name="entry_date"]').val()) $('[name="entry_date"]').val(d.entry_date);
            if (d.months && !$('[name="months"]').val()) $('[name="months"]').val(d.months);
            if (d.return_date && !$('[name="return_date"]').val()) $('[name="return_date"]').val(d.return_date);

            // Recalc return date if needed
            calcReturnDate();
        }
    ,
        error: function(xhr){
            console.log('job_order_info ajax error', xhr.status, xhr.responseText);
        }
    });
}); */

function fillJobOrderFields(d) {
    if (!d) return;

    $('[name="receiver_company"]').val(d.receiver_company || '');
    $('[name="receiver_prefecture"]').val(d.receiver_prefecture || '');
    $('[name="receiver_address"]').val(d.receiver_address || '');
    $('[name="interview_date"]').val(d.interview_date || '');
    $('[name="entry_date"]').val(d.entry_date || '');
    $('[name="months"]').val(d.months || '');
    $('[name="return_date"]').val(d.return_date || '');

    calcReturnDate();
}

function clearJobOrderFields() {
    $('[name="receiver_company"]').val('');
    $('[name="receiver_prefecture"]').val('');
    $('[name="receiver_address"]').val('');
    $('[name="interview_date"]').val('');
    $('[name="entry_date"]').val('');
    $('[name="months"]').val('');
    $('[name="return_date"]').val('');
}

$('#job_order_id').off('change.imjob').on('change.imjob', function(){
    var jobId = parseInt($(this).val() || '0', 10);

    if (!jobId) {
        clearJobOrderFields();
        return;
    }

    $.ajax({
        url: "<?= admin_url('internship_management/internship_applications/job_order_info/'); ?>" + jobId,
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function(res){
            if (!res || !res.success || !res.data) {
                console.log('job_order_info invalid response:', res);
                return;
            }

            fillJobOrderFields(res.data);
        },
        error: function(xhr){
            console.log('job_order_info ajax error:', xhr.status, xhr.responseText);
            alert('Không lấy được thông tin đơn tuyển. Vui lòng kiểm tra lại.');
        }
    });
});

// Before submit: if user selected school in list, clear new field to avoid confusion
/*$('#appForm').on('submit', function(){
    if ($('#school_name').val() !== '__new__') {
        $('#school_name_new').val('');
    }

    // Nếu chọn "Trình độ khác" thì lấy giá trị input để lưu vào DB
    if ($('#japanese_level').val() === '__other__') {
        var other = ($.trim($('#japanese_level_other').val()) || '');
        if (other !== '') {
            // overwrite select value by setting hidden input with same name (CI will take last)
            $('<input>').attr({type:'hidden', name:'japanese_level', value: other}).appendTo('#appForm');
        }
    } else {
        $('#japanese_level_other').val('');
    }
});*/

/*$('#appForm').on('submit', function(){

    if ($('#school_name').val() === '__new__') {
        var newSchool = $.trim($('#school_name_new').val() || '');

        if (newSchool !== '') {
            $('<input>').attr({
                type: 'hidden',
                name: 'school_name',
                value: newSchool
            }).appendTo('#appForm');
        }
    } else {
        $('#school_name_new').val('');
    }

    // Nếu chọn "Trình độ khác" thì lấy giá trị input để lưu vào DB
    if ($('#japanese_level').val() === '__other__') {
        var other = $.trim($('#japanese_level_other').val() || '');
        if (other !== '') {
            $('<input>').attr({
                type: 'hidden',
                name: 'japanese_level',
                value: other
            }).appendTo('#appForm');
        }
    } else {
        $('#japanese_level_other').val('');
    }
});*/
$('#appForm').on('submit', function(){

    if ($('#school_name').val() === '__new__') {
        var newSchool = $.trim($('#school_name_new').val() || '');

        if (newSchool === '') {
            alert('Vui lòng nhập tên trường mới.');
            $('#school_name_new').show().focus();
            return false;
        }

        $('<input>').attr({
            type: 'hidden',
            name: 'school_name',
            value: newSchool
        }).appendTo('#appForm');
    } else {
        $('#school_name_new').val('');
    }

    // Nếu chọn "Trình độ khác" thì lấy giá trị input để lưu vào DB
    if ($('#japanese_level').val() === '__other__') {
        var other = ($.trim($('#japanese_level_other').val()) || '');
        if (other !== '') {
            $('<input>').attr({
                type: 'hidden',
                name: 'japanese_level',
                value: other
            }).appendTo('#appForm');
        }
    } else {
        $('#japanese_level_other').val('');
    }
});
</script>

</body>
</html>