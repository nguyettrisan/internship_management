<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();

/**
 * NOTES TAB - chuẩn Perfex, không sinh /0, an toàn array/object
 * Input từ view:
 * - $student_id (int)  [bắt buộc nên có]
 * - $student (array|object) [optional]
 * - $notes (array) [optional - nếu controller truyền]
 */

// 1) Resolve student_id an toàn
$sid = (int)($student_id ?? 0);

if ($sid <= 0 && isset($student)) {
    if (is_array($student) && !empty($student['id'])) {
        $sid = (int)$student['id'];
    } elseif (is_object($student) && !empty($student->id)) {
        $sid = (int)$student->id;
    }
}

// Guard: thiếu student id => không render form/action để khỏi /0
if ($sid <= 0) {
    echo '<div class="alert alert-danger mbot0">Không xác định được student_id (sid=0). Vui lòng kiểm tra Controller/View truyền biến.</div>';
    return;
}

// 2) Ưu tiên dùng $notes controller truyền xuống, nếu không có thì tự query DB
if (!isset($notes) || !is_array($notes)) {
    $notes = [];
}

// Helper: tìm bảng tồn tại
if (!function_exists('im_first_existing_table')) {
    function im_first_existing_table($CI, array $cands) {
        foreach ($cands as $t) {
            if ($CI->db->table_exists($t)) return $t;
        }
        return null;
    }
}

// Helper: pick field
if (!function_exists('im_pick')) {
    function im_pick(array $row, array $keys, $default = '') {
        foreach ($keys as $k) {
            if (isset($row[$k]) && $row[$k] !== '' && $row[$k] !== null) return $row[$k];
        }
        return $default;
    }
}

// 3) Xác định table notes
$notes_table = im_first_existing_table($CI, [
    db_prefix().'internship_student_notes',
    db_prefix().'internship_notes',
    db_prefix().'internship_processing_notes',
    db_prefix().'student_notes',
]);

// 4) Query notes nếu chưa có
if (empty($notes) && $notes_table) {
    // lấy fields để biết có cột nào (tránh SQL fail)
    $fields = $CI->db->list_fields($notes_table);

    $CI->db->from($notes_table . ' n');
    $CI->db->where('n.student_id', $sid);
    $CI->db->order_by('n.id', 'DESC');
    $CI->db->limit(200);

    // select core
    $select = ['n.*'];

    // join staff nếu có cột staff
    $staffField = null;
    foreach (['staff_id', 'addedfrom', 'created_by', 'createdby', 'user_id'] as $sf) {
        if (in_array($sf, $fields, true)) { $staffField = $sf; break; }
    }

    if ($staffField) {
        $select[] = 's.firstname';
        $select[] = 's.lastname';
        $CI->db->join(db_prefix().'staff s', 's.staffid = n.'.$staffField, 'left');
    }

    $CI->db->select(implode(',', $select), false);
    $notes = $CI->db->get()->result_array();
}

// 5) Action URL: an toàn nhất là post kèm hidden student_id
// Nếu controller của bạn đang dùng route add_note/{id} thì vẫn ok.
// Nếu controller dùng add_note (không id) thì vẫn dùng hidden để lấy.
$action_url = admin_url('internship_management/student_client/add_note/' . $sid);

// upload path (giữ như file cũ, bạn có thể đổi theo module thực tế)
$upload_dir_rel = 'uploads/internship_notes/' . $sid . '/';
$upload_dir_abs = FCPATH . $upload_dir_rel;
?>

<div class="panel_s">
    <div class="panel-body">
        <div class="clearfix mbot10">
            <h4 class="no-margin">
                <i class="fa fa-sticky-note-o"></i> Ghi chú xử lý
            </h4>
        </div>

        <?php echo form_open_multipart($action_url, ['autocomplete' => 'off']); ?>
            <input type="hidden" name="student_id" value="<?php echo (int)$sid; ?>">

            <div class="form-group">
                <label for="im_note_content">Nội dung ghi chú <span class="text-danger">*</span></label>
                <textarea id="im_note_content" name="content" class="form-control" rows="4" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Loại ghi chú</label>
                        <select name="note_type" class="form-control selectpicker" data-live-search="true">
                            <option value="Khác">Khác</option>
                            <option value="Hồ sơ">Hồ sơ</option>
                            <option value="Phỏng vấn">Phỏng vấn</option>
                            <option value="Visa">Visa</option>
                            <option value="Xuất cảnh">Xuất cảnh</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nhắc việc (tuỳ chọn)</label>
                        <input type="date" name="reminder_at" class="form-control">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Đính kèm (tuỳ chọn)</label>
                        <input type="file" name="file" class="form-control">
                        <p class="text-muted mtop5 mbot0"><small>Khuyến nghị: PDF/JPG/PNG, dung lượng theo cấu hình server.</small></p>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-plus"></i> Thêm ghi chú
            </button>
   <?php echo form_close(); ?>
    </div>
</div>

<div class="panel_s">
    <div class="panel-body">
        <div class="clearfix mbot10">
            <h4 class="no-margin">Lịch sử ghi chú</h4>
            <span class="pull-right text-muted"><small>Hiển thị tối đa 200 dòng mới nhất</small></span>
        </div>

        <?php if (empty($notes)) { ?>
            <p class="text-muted mbot0">Chưa có ghi chú nào.</p>
        <?php } else { ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th style="width:170px;">Thời gian</th>
                        <th style="width:200px;">Nhân sự</th>
                        <th style="width:130px;">Loại</th>
                        <th>Nội dung</th>
                        <th style="width:170px;">Nhắc việc</th>
                        <th style="width:190px;">Đính kèm</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($notes as $n) {
                        $created    = im_pick($n, ['created_at', 'datecreated', 'created', 'created_date'], '');
                        $type       = im_pick($n, ['note_type', 'type', 'category'], 'Khác');
                        $content    = im_pick($n, ['content', 'note', 'description', 'message'], '');
                        $reminder   = im_pick($n, ['reminder_at', 'reminder_date', 'reminder'], '');
                        $file       = im_pick($n, ['file', 'file_name', 'attachment', 'filename'], '');

                        // staff name
                        $firstname  = im_pick($n, ['firstname'], '');
                        $lastname   = im_pick($n, ['lastname'], '');
                        $staff_name = trim($lastname . ' ' . $firstname);

                        if ($staff_name === '') {
                            $staff_id = (int)im_pick($n, ['staff_id', 'addedfrom', 'created_by', 'user_id'], 0);
                            $staff_name = $staff_id ? get_staff_full_name($staff_id) : 'Hệ thống';
                        }

                        // file url: chỉ show nếu có file
                        $file_url = '';
                        if ($file) {
                            // nếu file tồn tại trên server theo cấu trúc hiện tại
                            $abs = $upload_dir_abs . $file;
                            if (@file_exists($abs)) {
                                $file_url = site_url($upload_dir_rel . rawurlencode($file));
                            } else {
                                // fallback: vẫn cho link, tuỳ hệ thống lưu ở đâu bạn đổi lại sau
                                $file_url = site_url($upload_dir_rel . rawurlencode($file));
                            }
                        }
                        ?>
                        <tr>
                            <td><?php echo $created ? _dt($created) : '<span class="text-muted">-</span>'; ?></td>
                            <td><?php echo html_escape($staff_name ?: '-'); ?></td>
                            <td><?php echo html_escape($type); ?></td>
                            <td><?php echo nl2br(html_escape($content)); ?></td>
                            <td><?php echo $reminder ? _dt($reminder) : '<span class="text-muted">-</span>'; ?></td>
                            <td>
                                <?php if ($file_url) { ?>
                                    <a href="<?php echo $file_url; ?>" target="_blank" rel="noreferrer">
                                        <?php echo html_escape($file); ?>
                                    </a>
                                <?php } else { ?>
                                    <span class="text-muted">-</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php if ($notes_table) { ?>
                <p class="text-muted mbot0"><small>Nguồn dữ liệu: <?php echo html_escape($notes_table); ?></small></p>
            <?php } ?>
        <?php } ?>
    </div>
</div>