<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
:root{
  --ifk-green:#96bc17;
  --ifk-navy:#00325a;
  --ifk-blue:#00a6dc;

  --ifk-bg:#f6f9fc;
  --ifk-card:#ffffff;
  --ifk-border:#e6eef6;
  --ifk-text:#1c2b3a;
  --ifk-muted:#6b7c93;

  --ifk-radius:14px;
  --ifk-shadow:0 10px 26px rgba(0,50,90,.08);
}

#wrapper .content{ background:var(--ifk-bg); }

/* Panel */
.panel_s>.panel-body{
  border-radius:18px;
}
.panel_s{
  border-radius:18px;
}

/* Headings */
h4.mbot15{
  color:var(--ifk-navy);
  font-weight:1000;
  letter-spacing:.2px;
}
h4.mbot15 i{ color:var(--ifk-blue); margin-right:6px; }

h5{
  color:var(--ifk-navy);
  font-weight:1000;
}
h5 i{ color:var(--ifk-blue); }

/* Form controls */
label{
  color:var(--ifk-navy);
  font-weight:900;
}
.form-control{
  border-radius:14px;
  border:1px solid var(--ifk-border);
  box-shadow:none;
  transition:all .15s ease;
}
.form-control:focus{
  border-color:rgba(0,166,220,.55);
  box-shadow:0 0 0 4px rgba(0,166,220,.12);
}

/* Buttons */
.btn{
  border-radius:14px;
  font-weight:900;
}
.btn-primary{ background:var(--ifk-navy) !important; border:0 !important; }
.btn-default{
  background:#fff !important;
  border:1px solid var(--ifk-border) !important;
  color:var(--ifk-navy) !important;
}
.btn-info{ background:var(--ifk-blue) !important; border:0 !important; }
.btn-success{ background:var(--ifk-green) !important; border:0 !important; color:#0b1a08 !important; }
.btn-danger{ background:#d92d20 !important; border:0 !important; }

.btn-icon{
  width:36px; height:36px;
  display:inline-flex !important;
  align-items:center;
  justify-content:center;
  padding:0 !important;
  border-radius:12px !important;
  box-shadow:0 8px 16px rgba(0,50,90,.10);
  transition:all .12s ease;
}
.btn-icon:hover{ transform:translateY(-1px); box-shadow:0 14px 26px rgba(0,50,90,.14); }
.btn-icon i{ margin:0 !important; }

/* Status label -> chip style */
.label{
  border-radius:999px !important;
  padding:5px 10px !important;
  font-weight:1000;
  font-size:12px;
}
.label-success{
  background:rgba(150,188,23,.18) !important;
  color:#2f6f09 !important;
}
.label-default{
  background:rgba(0,50,90,.08) !important;
  color:var(--ifk-navy) !important;
}

/* Question blocks */
#question-list .q-item{
  border-radius:16px;
  border:1px solid var(--ifk-border);
  box-shadow:var(--ifk-shadow);
  overflow:hidden;
}
#question-list .q-item .panel-body{
  padding:14px 14px 12px;
}
#question-list .q-item .btn-remove-q{
  border-radius:12px;
}

/* Make "Options" area look like helper */
#question-list textarea[name="q_options[]"]{
  background:rgba(0,50,90,.03);
}

/* Divider */
hr{ border-top:1px solid var(--ifk-border) !important; }

/* Table */
.table>thead>tr>th{
  color:var(--ifk-navy);
  font-weight:1000;
  border-bottom:1px solid var(--ifk-border) !important;
  background:rgba(0,50,90,.03);
}
.table>tbody>tr>td{
  border-top:1px solid rgba(230,238,246,.9) !important;
}
.table-hover>tbody>tr:hover{
  background:rgba(0,166,220,.06);
}

/* code hint */
code{
  border-radius:12px;
  border:1px solid var(--ifk-border);
  background:#fff;
  padding:6px 10px;
  display:inline-block;
}

/* Small spacing polish */
.checkbox label{ font-weight:800; color:var(--ifk-text); }
.text-muted{ color:var(--ifk-muted) !important; }

/* Responsive */
@media (max-width: 991px){
  .btn-icon{ width:34px; height:34px; }
}
</style>
<div id="wrapper">
<div class="content">
    <div class="row">

        <!-- LEFT: FORM TẠO / SỬA -->
        <div class="col-md-5">
            <div class="panel_s">
                <div class="panel-body">

                    <h4 class="mbot15">
                        <i class="fa fa-list-alt"></i>
                        <?php echo $edit_survey ? 'Sửa Mẫu Khảo Sát' : 'Thêm Mẫu Khảo Sát'; ?>
                    </h4>

                    <?php echo form_open(admin_url('internship_management/internship_survey/templates')); ?>
                    <input type="hidden" name="id" value="<?php echo $edit_survey ? $edit_survey->id : ''; ?>">

                    <div class="form-group">
                        <label>Tiêu đề khảo sát</label>
                        <input type="text" name="title" class="form-control" required
                               value="<?php echo $edit_survey ? html_escape($edit_survey->title) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="description" class="form-control" rows="3"><?php
                            echo $edit_survey ? html_escape($edit_survey->description) : '';
                        ?></textarea>
                    </div>

                    <div class="checkbox mtop15">
                        <label>
                            <input type="checkbox" name="active"
                                   <?php echo (!$edit_survey || $edit_survey->active) ? 'checked' : ''; ?>>
                            Kích hoạt mẫu khảo sát
                        </label>
                    </div>

                    <hr/>

                    <h5><i class="fa fa-question-circle"></i> Các câu hỏi</h5>

                    <div id="question-list">
                        <?php if(!empty($questions)) foreach($questions as $q): ?>
                            <div class="q-item panel panel-default mtop10">
                                <div class="panel-body">

                                    <div class="form-group">
                                        <label>Câu hỏi</label>
                                        <input type="text" name="q_label[]" class="form-control"
                                               value="<?php echo html_escape($q->label); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>Loại trường</label>
                                        <select name="q_type[]" class="form-control">
                                            <?php
                                            $types = [
                                                'text' => 'Text ngắn',
                                                'textarea' => 'Đoạn văn',
                                                'radio' => 'Radio (một lựa chọn)',
                                                'checkbox' => 'Checkbox (nhiều lựa chọn)',
                                                'select' => 'Dropdown',
                                                'rating' => 'Rating 1-5',
                                                'date' => 'Ngày tháng',
                                            ];
                                            foreach ($types as $k => $v) {
                                                echo '<option value="'.$k.'" '.($q->field_type==$k?'selected':'').'>'.$v.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Options (mỗi dòng một giá trị)</label>
                                        <textarea name="q_options[]" class="form-control" rows="2"><?php
                                            echo html_escape($q->options);
                                        ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Thứ tự</label>
                                        <input type="number" name="q_sort[]" class="form-control"
                                               value="<?php echo $q->sort_order; ?>">
                                    </div>

                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="q_required[]" value="1"
                                                <?php echo $q->required ? 'checked' : ''; ?>>
                                            Bắt buộc
                                        </label>
                                    </div>

                                    <button type="button" class="btn btn-danger btn-xs btn-remove-q">
                                        <i class="fa fa-trash"></i> Xoá câu hỏi
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" id="btn-add-question" class="btn btn-default mtop10">
                        <i class="fa fa-plus"></i> Thêm câu hỏi
                    </button>

                    <hr/>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Lưu mẫu
                    </button>

                    </form>
                </div>
            </div>
        </div>

        <!-- RIGHT: LIST -->
        <div class="col-md-7">
            <div class="panel_s">
                <div class="panel-body">

                    <h4 class="mbot15"><i class="fa fa-table"></i> Danh sách mẫu khảo sát</h4>

                    <div class="table-responsive">
                    <table id="tbl_survey_templates" class="table dt-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tiêu đề</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php foreach($templates as $tpl): ?>
                            <tr>
                                <td><?= $tpl->id ?></td>
                                <td><?= html_escape($tpl->title) ?></td>

                                <td>
                                    <?= $tpl->active
                                        ? '<span class="label label-success">Đang dùng</span>'
                                        : '<span class="label label-default">Tắt</span>' ?>
                                </td>

                                <td>
                                    <?php
                                    echo isset($tpl->created_at)
                                        ? _d($tpl->created_at)
                                        : '—';
                                    ?>
                                </td>

                                <td>
    <!-- Sửa -->
    <a href="<?php echo admin_url('internship_management/internship_survey/templates?id='.$tpl->id); ?>"
       class="btn btn-default btn-icon">
        <i class="fa fa-edit"></i>
    </a>

    <!-- Xem kết quả -->
    <a href="<?php echo admin_url('internship_management/internship_survey/results/'.$tpl->id); ?>"
       class="btn btn-info btn-icon">
        <i class="fa fa-bar-chart"></i>
    </a>
    <!-- Dashboard -->
<a href="<?php echo admin_url('internship_management/internship_survey/dashboard/'.$tpl->id); ?>"
   class="btn btn-success btn-icon">
    <i class="fa fa-bar-chart"></i>
</a>

    <!-- Xem công khai -->
    <a href="<?php echo site_url('survey_form/index/'.$tpl->id.'/1'); ?>"
       target="_blank"
       class="btn btn-success btn-icon">
       <i class="fa fa-eye"></i>
    </a>
    

    <!-- Xóa -->
    <a href="<?php echo admin_url('internship_management/internship_survey/delete_template/'.$tpl->id); ?>"
       class="btn btn-danger btn-icon _delete">
        <i class="fa fa-trash"></i>
    </a>
</td>
                            </tr>
                        <?php endforeach; ?>

                        </tbody>
                    </table>
                    </div>

                    <p class="text-muted mtop15">
                        Link công khai khảo sát:<br>
                        <code><?= site_url('survey_form/index/{survey_id}/{student_id}') ?></code><br>
                        (Thay {survey_id} và {student_id} khi gửi email.)
                    </p>

                </div>
            </div>
        </div>

    </div>
</div>
</div>

<?php init_tail(); ?>

<script>
(function($){

    function newQuestionBlock() {
        return `
        <div class="q-item panel panel-default mtop10">
            <div class="panel-body">

                <div class="form-group">
                    <label>Câu hỏi</label>
                    <input type="text" name="q_label[]" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Loại trường</label>
                    <select name="q_type[]" class="form-control">
                        <option value="text">Text ngắn</option>
                        <option value="textarea">Đoạn văn</option>
                        <option value="radio">Radio</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="select">Dropdown</option>
                        <option value="rating">Rating 1–5</option>
                        <option value="date">Ngày tháng</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Options (mỗi dòng một giá trị)</label>
                    <textarea name="q_options[]" class="form-control" rows="2"></textarea>
                </div>

                <div class="form-group">
                    <label>Thứ tự</label>
                    <input type="number" name="q_sort[]" class="form-control" value="0">
                </div>

                <div class="checkbox">
                    <label><input type="checkbox" name="q_required[]" value="1"> Bắt buộc</label>
                </div>

                <button type="button" class="btn btn-danger btn-xs btn-remove-q">
                    <i class="fa fa-trash"></i> Xoá
                </button>

            </div>
        </div>`;
    }

    $('#btn-add-question').on('click', function(){
        $('#question-list').append(newQuestionBlock());
    });

    $(document).on('click', '.btn-remove-q', function(){
        $(this).closest('.q-item').remove();
    });

})(jQuery);
</script>

</body>
</html>