<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
/**
 * TAB: Audit Log (Settings)
 * Kỳ vọng controller truyền:
 * - $audit_logs: array các log
 * - $audit_total, $audit_page, $audit_limit (optional)
 *
 * Field gợi ý trong từng log:
 * - created_at / datecreated
 * - staff_name (join), staff_id
 * - rel_type, rel_id
 * - action
 * - message
 * - ip, user_agent
 * - old_data, new_data (JSON)
 */

$audit_logs  = isset($audit_logs) ? $audit_logs : [];
$audit_total = isset($audit_total) ? (int)$audit_total : count($audit_logs);
$audit_page  = isset($audit_page) ? (int)$audit_page : (int)(get_instance()->input->get('log_page') ?: 1);
$audit_limit = isset($audit_limit) ? (int)$audit_limit : (int)(get_instance()->input->get('log_limit') ?: 50);

$q        = (string)get_instance()->input->get('log_q');
$action   = (string)get_instance()->input->get('log_action');
$rel_type = (string)get_instance()->input->get('log_rel_type');
$rel_id   = (string)get_instance()->input->get('log_rel_id');
$staff_id = (string)get_instance()->input->get('log_staff_id');

$pages = ($audit_limit > 0) ? (int)ceil(max(1, $audit_total) / $audit_limit) : 1;

if (!function_exists('im_audit_pick')) {
    function im_audit_pick($row, $keys, $default = '') {
        foreach ($keys as $k) {
            if (is_array($row) && array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') return $row[$k];
            if (is_object($row) && isset($row->$k) && $row->$k !== null && $row->$k !== '') return $row->$k;
        }
        return $default;
    }
}

if (!function_exists('im_build_qs')) {
    function im_build_qs($overrides = []) {
        $params = $_GET;
        foreach ($overrides as $k => $v) {
            if ($v === null || $v === '') unset($params[$k]);
            else $params[$k] = $v;
        }
        return http_build_query($params);
    }
}
?>

<div class="alert alert-info">
    <i class="fa fa-info-circle"></i>
    Ghi nhận mọi thao tác trong module: đổi trạng thái, cập nhật hồ sơ, upload tài liệu, push CRM, tạo hóa đơn/hợp đồng...
</div>

<form method="get" action="<?= admin_url('internship_management/internship_settings'); ?>" class="mbot15">
    <div class="row">
        <div class="col-md-4">
            <input type="text" class="form-control" name="log_q" value="<?= html_escape($q); ?>"
                   placeholder="Tìm theo message/action/rel_type...">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="log_action" value="<?= html_escape($action); ?>"
                   placeholder="action (vd: status_changed)">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="log_rel_type" value="<?= html_escape($rel_type); ?>"
                   placeholder="rel_type (job_order/student/...)">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="log_rel_id" value="<?= html_escape($rel_id); ?>"
                   placeholder="rel_id">
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control" name="log_staff_id" value="<?= html_escape($staff_id); ?>"
                   placeholder="staff_id">
        </div>
    </div>

    <div class="row mtop10">
        <div class="col-md-12 text-right">
            <button class="btn btn-primary btn-sm" type="submit">
                <i class="fa fa-filter"></i> Lọc
            </button>
            <a class="btn btn-default btn-sm" href="<?= admin_url('internship_management/internship_settings#im_tab_audit'); ?>">
                <i class="fa fa-refresh"></i>
            </a>
        </div>
    </div>
</form>

<div class="panel_s">
    <div class="panel-body">
        <?php if (empty($audit_logs)) { ?>
            <div class="text-muted">
                <i class="fa fa-info-circle"></i> Chưa có log.
            </div>
        <?php } else { ?>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th style="width:165px;">Thời gian</th>
                        <th style="width:190px;">Nhân viên</th>
                        <th style="width:120px;">rel_type</th>
                        <th style="width:90px;">rel_id</th>
                        <th style="width:160px;">action</th>
                        <th>Nội dung</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($audit_logs as $lg) {
                        $created = im_audit_pick($lg, ['created_at','datecreated','date_added','dateadded'], '');
                        $staff   = im_audit_pick($lg, ['staff_name','fullname','staff','name'], '');
                        $sid     = im_audit_pick($lg, ['staff_id'], '');
                        $staff_show = $staff !== '' ? $staff : ($sid !== '' ? ('Staff #' . $sid) : 'Hệ thống');

                        $relType = im_audit_pick($lg, ['rel_type','type'], '');
                        $relId   = im_audit_pick($lg, ['rel_id','relid','id'], '');
                        $act     = im_audit_pick($lg, ['action','event'], '');
                        $msg     = im_audit_pick($lg, ['message','note','content'], '');

                        $oldJson = im_audit_pick($lg, ['old_data','old'], '');
                        $newJson = im_audit_pick($lg, ['new_data','new'], '');

                        $old = is_string($oldJson) && $oldJson !== '' ? json_decode($oldJson, true) : null;
                        $new = is_string($newJson) && $newJson !== '' ? json_decode($newJson, true) : null;
                    ?>
                        <tr>
                            <td><?= $created ? _dt($created) : '-'; ?></td>
                            <td><?= html_escape($staff_show); ?></td>
                            <td><?= html_escape($relType ?: '-'); ?></td>
                            <td><?= html_escape((string)$relId ?: '-'); ?></td>
                            <td><span class="label label-default"><?= html_escape($act ?: '-'); ?></span></td>
                            <td>
                                <div class="bold"><?= html_escape($msg ?: '-'); ?></div>

                                <?php if (is_array($old) || is_array($new)) { ?>
                                    <div class="mtop10" style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
                                        <?php
                                        $fields = array_unique(array_merge(array_keys((array)$old), array_keys((array)$new)));
                                        $shown = 0;
                                        foreach ($fields as $f) {
                                            $ov = $old[$f] ?? null;
                                            $nv = $new[$f] ?? null;
                                            if ($ov === $nv) continue;
                                            $shown++;
                                            ?>
                                            <div style="margin:4px 0;">
                                                <code><?= html_escape($f); ?></code>:
                                                <span style="color:#b91c1c;"><?= html_escape((string)$ov); ?></span>
                                                →
                                                <span style="color:#065f46;"><?= html_escape((string)$nv); ?></span>
                                            </div>
                                            <?php
                                            if ($shown >= 12) { echo '<div class="text-muted">…</div>'; break; }
                                        }
                                        if ($shown === 0) echo '<div class="text-muted">Không có thay đổi field.</div>';
                                        ?>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pages > 1) { ?>
                <div class="text-right">
                    <ul class="pagination no-margin">
                        <?php for ($p = 1; $p <= $pages; $p++) { ?>
                            <li class="<?= $p == $audit_page ? 'active' : ''; ?>">
                                <a href="<?= admin_url('internship_management/internship_settings?'.im_build_qs(['log_page'=>$p])); ?>">
                                    <?= $p; ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

        <?php } ?>
    </div>
</div>
