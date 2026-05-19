<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();

/**
 * PRO NOTES TAB (internship_management)
 * - UI đẹp hơn (timeline + badge)
 * - Giữ logic an toàn + fallback query DB
 *
 * Input expected:
 * - $student_id (int)
 * - $student (array|object) optional
 * - $notes (array) optional
 */

// ---------- resolve student id ----------
$sid = (int)($student_id ?? 0);
if ($sid <= 0 && isset($student)) {
    if (is_array($student) && !empty($student['id'])) $sid = (int)$student['id'];
    if (is_object($student) && !empty($student->id)) $sid = (int)$student->id;
}
if ($sid <= 0) {
    echo '<div class="alert alert-danger mbot0">Không xác định được student_id (sid=0). Vui lòng kiểm tra Controller/View truyền biến.</div>';
    return;
}

// ---------- helpers ----------
if (!function_exists('im_first_existing_table')) {
    function im_first_existing_table($CI, array $cands) {
        foreach ($cands as $t) {
            if ($CI->db->table_exists($t)) return $t;
        }
        return null;
    }
}
if (!function_exists('im_pick')) {
    function im_pick(array $row, array $keys, $default = '') {
        foreach ($keys as $k) {
            if (isset($row[$k]) && $row[$k] !== '' && $row[$k] !== null) return $row[$k];
        }
        return $default;
    }
}
if (!function_exists('im_staff_name_from_row')) {
    function im_staff_name_from_row(array $row) {
        $fn = trim((string)($row['firstname'] ?? ''));
        $ln = trim((string)($row['lastname'] ?? ''));
        $full = trim($fn.' '.$ln);
        if ($full !== '') return $full;

        // fallback to staff_id if no name
        foreach (['staff_id','addedfrom','created_by','createdby','user_id'] as $k) {
            if (!empty($row[$k])) return 'Nhân sự #' . (int)$row[$k];
        }
        return '—';
    }
}
if (!function_exists('im_note_badge')) {
    function im_note_badge($type) {
        $type = (string)$type;
        $map = [
            'internal' => ['Nội bộ', 'badge badge-primary'],
            'normal'   => ['Bình thường', 'badge badge-default'],
            'public'   => ['Công khai', 'badge badge-success'],
        ];
        return $map[$type] ?? [($type ?: '—'), 'badge badge-default'];
    }
}

// ---------- data ----------
if (!isset($notes) || !is_array($notes)) $notes = [];

// DB fallback: tblinternship_notes (student_id, staff_id, content, note_type, file, reminder_at, created_at)
$notes_table = im_first_existing_table($CI, [
    db_prefix().'internship_notes',
    db_prefix().'internship_student_notes',
    db_prefix().'internship_processing_notes',
    db_prefix().'student_notes',
]);

if (empty($notes) && $notes_table) {
    $fields = $CI->db->list_fields($notes_table);

    $CI->db->from($notes_table.' n');
    $CI->db->where('n.student_id', $sid);
    $CI->db->order_by('n.id','DESC');
    $CI->db->limit(300);

    $select = ['n.*'];

    $staffField = null;
    foreach (['staff_id','addedfrom','created_by','createdby','user_id'] as $sf) {
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

// ---------- action url ----------
$action_url = admin_url('internship_management/student_client/add_note/'.$sid);

// ---------- attachment url resolver ----------
if (!function_exists('im_note_file_url')) {
    function im_note_file_url($sid, $file) {
        $file = (string)$file;
        if ($file === '') return '';
        if (strpos($file, 'http') === 0 || strpos($file, '//') === 0) return $file;

        // allow stored relative path
        if (strpos($file, '/') !== false) return base_url(ltrim($file,'/'));

        // default: per-student note folder
        return base_url('uploads/internship_notes/'.$sid.'/'.rawurlencode($file));
    }
}
?>

<style>
.im-notes-pro{--bg:#f4f6f9;--card:#fff;--muted:#6b7280;--text:#111827;--line:#e5e7eb;}
.im-notes-pro .im-card{background:var(--card);border:0;border-radius:16px;box-shadow:0 10px 35px rgba(0,0,0,.06);}
.im-notes-pro .im-card .im-card-body{padding:16px;}
.im-notes-pro .im-title{font-size:18px;font-weight:900;color:var(--text);display:flex;gap:10px;align-items:center;margin:0 0 12px;}
.im-notes-pro .im-form-grid{display:grid;grid-template-columns:1fr 280px;gap:14px;align-items:start;}
@media(max-width:991px){.im-notes-pro .im-form-grid{grid-template-columns:1fr;}}
.im-notes-pro textarea.form-control{border-radius:12px;min-height:90px;}
.im-notes-pro .im-side .form-control{border-radius:12px;}
.im-notes-pro .im-actions{display:flex;gap:10px;justify-content:flex-end;align-items:center;margin-top:10px;flex-wrap:wrap;}
.im-notes-pro .im-divider{height:1px;background:var(--line);margin:16px 0;}
.im-notes-pro .im-timeline{position:relative;margin:0;padding:0;list-style:none;}
.im-notes-pro .im-timeline:before{content:"";position:absolute;left:14px;top:0;bottom:0;width:2px;background:var(--line);}
.im-notes-pro .im-item{position:relative;padding-left:44px;padding-bottom:18px;}
.im-notes-pro .im-dot{position:absolute;left:6px;top:3px;width:18px;height:18px;border-radius:999px;background:#fff;border:2px solid #93c5fd;box-shadow:0 4px 12px rgba(0,0,0,.08);}
.im-notes-pro .im-meta{display:flex;gap:10px;flex-wrap:wrap;align-items:center;color:var(--muted);font-weight:700;font-size:12px;margin-bottom:6px;}
.im-notes-pro .im-content{font-weight:700;color:var(--text);line-height:1.45;white-space:pre-wrap;}
.im-notes-pro .im-attach{margin-top:8px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;}
.im-notes-pro .im-attach a{font-weight:800;}
.im-notes-pro .badge{border-radius:999px;padding:6px 10px;font-weight:900;font-size:11px;}
.im-notes-pro .badge-primary{background:#dbeafe;color:#1e40af;}
.im-notes-pro .badge-success{background:#dcfce7;color:#14532d;}
.im-notes-pro .badge-default{background:#f3f4f6;color:#374151;}
</style>

<div class="im-notes-pro">
    <div class="im-card">
        <div class="im-card-body">
            <h4 class="im-title"><i class="fa fa-sticky-note-o"></i> Ghi chú xử lý</h4>

            <?php echo form_open_multipart($action_url, ['autocomplete'=>'off']); ?>
                <input type="hidden" name="student_id" value="<?php echo (int)$sid; ?>">

                <div class="im-form-grid">
                    <div>
                        <label for="im_note_content">Nội dung</label>
                        <textarea id="im_note_content" name="content" class="form-control" placeholder="Nhập ghi chú..."></textarea>

                        <div class="im-actions">
                            <div class="pull-left" style="flex:1;min-width:220px;">
                                <input type="file" name="file" class="form-control">
                            </div>

                            <button type="submit" class="btn btn-info">
                                <i class="fa fa-plus"></i> Thêm ghi chú
                            </button>
                        </div>
                    </div>

                    <div class="im-side">
                        <div class="form-group">
                            <label>Loại ghi chú</label>
                            <select name="note_type" class="form-control">
                                <option value="internal">Nội bộ</option>
                                <option value="normal">Bình thường</option>
                                <option value="public">Công khai</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Nhắc nhở (tuỳ chọn)</label>
                            <input type="datetime-local" name="reminder_at" class="form-control">
                            <small class="text-muted">Nếu hệ thống chưa dùng cột này thì sẽ bỏ qua.</small>
                        </div>
                    </div>
                </div>
            <?php echo form_close(); ?>

            <div class="im-divider"></div>

            <?php if (empty($notes)): ?>
                <div class="text-muted">Chưa có ghi chú.</div>
            <?php else: ?>
                <ul class="im-timeline">
                    <?php foreach ($notes as $n): ?>
                        <?php
                            $when = im_pick($n, ['created_at','datecreated','created_date','date'], '');
                            $staffName = im_staff_name_from_row($n);
                            $type = im_pick($n, ['note_type','type'], '');
                            $content = (string)im_pick($n, ['content','note','description','message'], '');
                            $file = (string)im_pick($n, ['file','attachment','file_name','filename'], '');
                            $reminder = im_pick($n, ['reminder_at'], '');

                            [$typeLabel, $typeClass] = im_note_badge($type);

                            $fileUrl = $file ? im_note_file_url($sid, $file) : '';
                        ?>
                        <li class="im-item">
                            <span class="im-dot"></span>

                            <div class="im-meta">
                                <span><i class="fa fa-clock-o"></i> <?php echo html_escape($when ?: '—'); ?></span>
                                <span><i class="fa fa-user"></i> <?php echo html_escape($staffName); ?></span>
                                <span class="<?php echo $typeClass; ?>"><?php echo html_escape($typeLabel); ?></span>
                                <?php if (!empty($reminder)): ?>
                                    <span class="badge badge-success"><i class="fa fa-bell-o"></i> Nhắc: <?php echo html_escape($reminder); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="im-content"><?php echo html_escape($content); ?></div>

                            <?php if ($fileUrl): ?>
                                <div class="im-attach">
                                    <i class="fa fa-paperclip text-muted"></i>
                                    <a href="<?php echo $fileUrl; ?>" target="_blank"><?php echo html_escape($file); ?></a>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>