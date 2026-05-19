<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();

/**
 * TAB LOGS - an toàn, không JS, không đè biến, không chết khi thiếu bảng
 * Input có thể có:
 * - $student_id (int)
 * - $student (array|object)
 * - $logs (array) (nếu controller truyền xuống)
 */

// 1) Resolve student_id an toàn
$student_id = (int)($student_id ?? 0);

if ($student_id <= 0 && isset($student)) {
    if (is_array($student) && isset($student['id'])) {
        $student_id = (int)$student['id'];
    } elseif (is_object($student) && isset($student->id)) {
        $student_id = (int)$student->id;
    }
}

// 2) Logs nếu controller truyền xuống thì dùng luôn
if (!isset($logs) || !is_array($logs)) {
    $logs = [];
}

// 3) Nếu chưa có logs thì fallback query DB
if (empty($logs) && $student_id > 0) {
    $candidates = [
        db_prefix().'internship_student_logs',
        db_prefix().'student_logs',
        db_prefix().'internship_audit_logs',
    ];

    $log_table = null;
    foreach ($candidates as $t) {
        if ($CI->db->table_exists($t)) {
            $log_table = $t;
            break;
        }
    }

    if ($log_table) {
        // Cố gắng join staff để lấy tên người thao tác
        $CI->db->select('l.*');
        $CI->db->from($log_table . ' l');
        $CI->db->where('l.student_id', $student_id);
        $CI->db->order_by('l.id', 'DESC');
        $CI->db->limit(200);

        // join staff nếu có field created_by
        $fields = $CI->db->list_fields($log_table);
        if (in_array('created_by', $fields, true)) {
            $CI->db->select('s.firstname, s.lastname');
            $CI->db->join(db_prefix().'staff s', 's.staffid = l.created_by', 'left');
        }

        $logs = $CI->db->get()->result_array();
    }
}

function im_logs_get($row, array $keys, $default = '')
{
    foreach ($keys as $k) {
        if (isset($row[$k]) && $row[$k] !== '' && $row[$k] !== null) return $row[$k];
    }
    return $default;
}
?>

<div class="panel_s">
    <div class="panel-body">
        <div class="clearfix mbot10">
            <span class="pull-right text-muted"><small>Hiển thị tối đa 200 dòng mới nhất</small></span>
            <h4 class="no-margin">Nhật ký xử lý</h4>
        </div>

        <?php if (!empty($logs)) { ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th style="width:180px;">Thời gian</th>
                        <th style="width:220px;">Người thao tác</th>
                        <th>Nội dung</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $log) {
                        $created_at = im_logs_get($log, ['created_at', 'date', 'created_date'], '');
                        $desc       = im_logs_get($log, ['description', 'message', 'note', 'action'], '');
                        $firstname  = im_logs_get($log, ['firstname'], '');
                        $lastname   = im_logs_get($log, ['lastname'], '');
                        $by_name    = trim($lastname . ' ' . $firstname);

                        if ($by_name === '') {
                            // fallback nếu bảng có created_by nhưng không join được staff
                            $by_name = im_logs_get($log, ['created_by', 'staff_id', 'user_id'], 'Hệ thống');
                        }

                        $desc_safe = nl2br(htmlspecialchars((string)$desc, ENT_QUOTES, 'UTF-8'));
                        ?>
                        <tr>
                            <td><?php echo $created_at ? _dt($created_at) : '<span class="text-muted">-</span>'; ?></td>
                            <td><?php echo htmlspecialchars((string)$by_name, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo $desc_safe ?: '<span class="text-muted">-</span>'; ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p class="text-muted mbot0">Chưa có nhật ký.</p>
            <p class="text-muted mbot0"><small>Debug: student_id=<?php echo (int)$student_id; ?> | logs=<?php echo is_array($logs) ? count($logs) : 0; ?></small></p>
        <?php } ?>
    </div>
</div>