<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php

/**
 * internship_management/views/student_client/view.php
 * Single-file PRO view (tabs included here).
 * - No partials/head-card.
 * - Safe fallbacks: if controller doesn't pass $files/$notes/$logs, this view will query DB tables directly.
 */

/* ---------------- helpers ---------------- */
function sc_val($arr, $keys, $default='—'){
    foreach((array)$keys as $k){
        if(isset($arr[$k]) && $arr[$k] !== '' && $arr[$k] !== null) return $arr[$k];
    }
    return $default;
}
function sc_int($arr, $keys, $default=0){
    foreach((array)$keys as $k){
        if(isset($arr[$k]) && is_numeric($arr[$k])) return (int)$arr[$k];
    }
    return (int)$default;
}
/*function sc_status_label($raw){
    $raw = (string)$raw;
    $map = [
        'docs_preparing' => 'Đang chuẩn bị hồ sơ',
        'docs_done'      => 'Đã hoàn tất hồ sơ',
        'docs_pending'   => 'Chờ bổ sung hồ sơ',
        'interview'      => 'Đang phỏng vấn',
        'pass'           => 'Đạt',
        'fail'           => 'Không đạt',
        'rejected'       => 'Bị từ chối',
        'approved'       => 'Đã duyệt',
        'processing'     => 'Đang xử lý',
        'done'           => 'Hoàn tất',
    ];
    return $map[$raw] ?? ($raw === '' ? '—' : $raw);
}*/


/*function sc_status_label($raw){
    $raw = strtolower(trim((string)$raw));
    $map = [
        'not_updated'         => 'Chưa cập nhật',
        'applied'             => 'Ứng tuyển',
        'interview_scheduled' => 'Hẹn phỏng vấn',
        'interview'           => 'Đang phỏng vấn',
        'pass'                => 'Đạt',
        'fail'                => 'Không đạt',
        'interview_passed'    => 'Đậu phỏng vấn',
        'interview_fail'      => 'Rớt phỏng vấn',
        'docs_preparing'      => 'Đang chuẩn bị hồ sơ',
        'prepare_documents'   => 'Đang chuẩn bị hồ sơ',
        'docs_pending'        => 'Chờ bổ sung hồ sơ',
        'docs_done'           => 'Đã hoàn tất hồ sơ',
        'done_documents'      => 'Đã hoàn tất hồ sơ',
        'coe_waiting'         => 'Chờ COE',
        'visa_processing'     => 'Đang làm visa',
        'ticket_booking'      => 'Đang mua vé',
        'pre_departure'       => 'Chuẩn bị xuất cảnh',
        'in_japan'            => 'Đang ở Nhật',
        'returned'            => 'Đã về nước',
        'cancelled'           => 'Huỷ',
        'rejected'            => 'Bị từ chối',
        'approved'            => 'Đã duyệt',
        'processing'          => 'Đang xử lý',
        'done'                => 'Hoàn tất',
    ];
    return $map[$raw] ?? ($raw === '' ? '—' : $raw);
}*/

function sc_status_label($raw){
    if (function_exists('im_status_label_vi')) {
        return im_status_label_vi($raw);
    }

    $raw = strtolower(trim((string)$raw));
    return $raw === '' ? '—' : $raw;
}
function sc_table_exists($db, $t){
    return method_exists($db,'table_exists') ? $db->table_exists($t) : true;
}
function sc_field_exists($db, $f, $t){
    return method_exists($db,'field_exists') ? $db->field_exists($f,$t) : false;
}

function sc_staff_name($staff_id){
    $staff_id = is_numeric($staff_id) ? (int)$staff_id : 0;
    if ($staff_id <= 0) return '';
    static $cache = [];
    if (isset($cache[$staff_id])) return $cache[$staff_id];
    $CI = &get_instance();
    $name = '';
    if (isset($CI->db) && sc_table_exists($CI->db,'tblstaff')) {
        $t = 'tblstaff';
        // common Perfex fields: staffid, firstname, lastname, full_name
        if (sc_field_exists($CI->db,'staffid',$t)) $CI->db->where('staffid',$staff_id);
        elseif (sc_field_exists($CI->db,'id',$t)) $CI->db->where('id',$staff_id);
        $row = $CI->db->get($t)->row_array();
        if ($row) {
            if (!empty($row['firstname']) || !empty($row['lastname'])) {
                $name = trim(($row['firstname'] ?? '').' '.($row['lastname'] ?? ''));
            } elseif (!empty($row['full_name'])) {
                $name = $row['full_name'];
            } elseif (!empty($row['name'])) {
                $name = $row['name'];
            }
        }
    }
    $cache[$staff_id] = $name;
    return $name;
}
function sc_badge($text,$cls='default'){
    $clsMap = [
        'info'=>'sc-badge sc-badge-info',
        'success'=>'sc-badge sc-badge-success',
        'warning'=>'sc-badge sc-badge-warning',
        'danger'=>'sc-badge sc-badge-danger',
        'muted'=>'sc-badge sc-badge-muted',
        'default'=>'sc-badge'
    ];
    $c = $clsMap[$cls] ?? $clsMap['default'];
    return '<span class="'.html_escape($c).'">'.html_escape($text).'</span>';
}
function sc_link_file_by_name($file_name){
    $file_name = trim((string)$file_name);
    if ($file_name === '') return '';
    $CI = &get_instance();
    if (!isset($CI->db) || !sc_table_exists($CI->db,'tblinternship_files')) return '';
    $t='tblinternship_files';
    if (!sc_field_exists($CI->db,'file_name',$t)) return '';
    $CI->db->where('file_name',$file_name);
    $row = $CI->db->get($t)->row_array();
    if (!$row) return '';
    $path = $row['file_path'] ?? '';
    if (!$path) return '';
    return site_url($path);
}
function sc_format_log_content($l){
    $raw = (string)($l['content'] ?? ($l['description'] ?? ($l['action'] ?? ($l['note'] ?? ''))));
    $raw = trim($raw);
    if ($raw === '') return '<span class="text-muted">—</span>';

    // Detect patterns
    $lower = mb_strtolower($raw, 'UTF-8');
    $html = '';

    if (strpos($lower,'upload') !== false || strpos($lower,'tài liệu') !== false) {
        $html .= sc_badge('Tài liệu','info').' ';
        // try extract filename after colon
        $fn = '';
        if (preg_match('/:\s*(.+)$/u',$raw,$m)) $fn = trim($m[1]);
        if ($fn) {
            $url = sc_link_file_by_name($fn);
            if ($url) {
                $html .= 'Upload: <a href="'.html_escape($url).'" target="_blank">'.html_escape($fn).'</a>';
            } else {
                $html .= 'Upload: '.html_escape($fn);
            }
        } else {
            $html .= html_escape($raw);
        }
        return $html;
    }

    if (strpos($lower,'đẩy crm') !== false || strpos($lower,'crm') !== false) {
        $html .= sc_badge('CRM','success').' '.html_escape($raw);
        return $html;
    }

    if (strpos($lower,'ghi chú') !== false || strpos($lower,'note') !== false) {
        $html .= sc_badge('Ghi chú','warning').' ';
        // compress common "Thêm ghi chú:" prefix
        $html .= html_escape($raw);
        return $html;
    }

    return html_escape($raw);
}

/* -------------- bootstrap dataa -------------- */
$studentArr = [];
if (isset($student) && is_object($student)) $studentArr = (array)$student;
elseif (isset($student) && is_array($student)) $studentArr = $student;

$sid = sc_int($studentArr, ['id','student_id'], 0);
if ($sid <= 0 && isset($student_id) && is_numeric($student_id)) $sid = (int)$student_id;
if ($sid <= 0 && isset($id) && is_numeric($id)) $sid = (int)$id;

if ($sid <= 0) { init_head(); echo '<div id="wrapper"><div class="content"><div class="alert alert-danger">Missing student id</div></div></div>'; init_tail(); exit; }

$CI = &get_instance();

$crm_id = 0;
if (isset($crm_client_id) && is_numeric($crm_client_id)) $crm_id = (int)$crm_client_id;
if ($crm_id <= 0 && isset($client_id) && is_numeric($client_id)) $crm_id = (int)$client_id;
if ($crm_id <= 0) $crm_id = sc_int($studentArr, ['crm_client_id','crm_id','client_id','userid'], 0);

$has_crm = $crm_id > 0;
$crm_url = $has_crm ? admin_url('clients/client/'.$crm_id) : '';

$name  = sc_val($studentArr, ['full_name','name','fullname'], 'HS'.$sid);
$code  = sc_val($studentArr, ['code','student_code','hs_code'], 'HS'.$sid);
$email = sc_val($studentArr, ['email'], '—');
$phone = sc_val($studentArr, ['phone_student','student_phone','phone','phonenumber'], '—');

/* avatar candidates */
$placeholder = base_url('modules/internship_management/assets/no-image.png');
$avatar_candidates = [];
$avatar_url  = sc_val($studentArr, ['avatar_url'], '');
$avatar_file = sc_val($studentArr, ['avatar','student_avatar','photo','photo_file','image_file','id_card_photo'], '');
if (!empty($avatar_url)) $avatar_candidates[] = (strpos($avatar_url,'http')===0 || strpos($avatar_url,'//')===0) ? $avatar_url : base_url(ltrim($avatar_url,'/'));
if (!empty($avatar_file)) {
    if (strpos($avatar_file,'http')===0 || strpos($avatar_file,'//')===0) $avatar_candidates[] = $avatar_file;
    else {
        $f = ltrim($avatar_file,'/');
        $avatar_candidates[] = base_url('uploads/internship_avatar/'.$f);
        $avatar_candidates[] = base_url('uploads/internship_documents/'.$sid.'/'.$f);
        $avatar_candidates[] = base_url('uploads/'.$f);
    }
}
$avatar_candidates[] = $placeholder;

/* urls */
$url_back     = admin_url('internship_management/internship_applications');
/* urls */
//$url_back     = admin_url('internship_management/internship_applications');

/**
 * URL sửa hồ sơ: dùng internship_applications/edit/{app_id}
 * giống nút Sửa của view_ajax.php
 */
$app_id = 0;

// ưu tiên biến controller nếu có
if (isset($application_id) && is_numeric($application_id)) $app_id = (int)$application_id;
if ($app_id <= 0 && isset($app['id']) && is_numeric($app['id'])) $app_id = (int)$app['id'];

// fallback: lấy app_id từ mảng $applications (thường controller trả về)
if ($app_id <= 0 && !empty($applications) && is_array($applications)) {
    $first = $applications[0];

    if (is_array($first)) {
        if (isset($first['id']) && is_numeric($first['id'])) $app_id = (int)$first['id'];
        elseif (isset($first['application_id']) && is_numeric($first['application_id'])) $app_id = (int)$first['application_id'];
        elseif (isset($first['app_id']) && is_numeric($first['app_id'])) $app_id = (int)$first['app_id'];
    } elseif (is_object($first)) {
        if (isset($first->id) && is_numeric($first->id)) $app_id = (int)$first->id;
        elseif (isset($first->application_id) && is_numeric($first->application_id)) $app_id = (int)$first->application_id;
        elseif (isset($first->app_id) && is_numeric($first->app_id)) $app_id = (int)$first->app_id;
    }
}
$cv_file = '';

// ưu tiên biến $app (nếu view.php có)
if (isset($app) && (is_array($app) || is_object($app))) {
    $cv_file = is_array($app) ? ($app['cv_file'] ?? '') : ($app->cv_file ?? '');
}

// fallback: nếu view.php dùng $application hoặc $row
if ($cv_file === '' && isset($application)) {
    $cv_file = is_array($application) ? ($application['cv_file'] ?? '') : ($application->cv_file ?? '');
}
// đúng route giống view_ajax
if ($app_id > 0) {
    $url_edit = admin_url('internship_management/internship_applications/edit/'.$app_id);
} else {
    // fallback nếu không tìm được app_id (đỡ lỗi)
    $url_edit = admin_url('internship_management/student_client/edit/'.$sid);
}

// giống view_ajax: chỉ có cv_file mới cho mở
$url_cv = !empty($cv_file)
    ? admin_url('internship_management/internship_applications/preview_file/'.$app_id)
    : '';
$cv_btn_html = '';
if (!empty($url_cv)) {
    $cv_btn_html = '<a href="'.html_escape($url_cv).'" target="_blank" class="btn btn-primary">'
                 . '<i class="fa fa-file-text-o"></i> Mở CV</a>';
} else {
    $cv_btn_html = '<button class="btn btn-default" disabled>'
                 . '<i class="fa fa-file-text-o"></i> Chưa có CV</button>';
}
/**
 * In A4:
 * Dùng mẫu print của Internship Applications.
 * Theo yêu cầu: mỗi sinh viên in theo chính ID sinh viên.
 * Ví dụ student_id = 40 => .../internship_applications/print/40
 */
$url_a4       = admin_url('internship_management/internship_applications/print/'.$sid);

$url_push_crm = admin_url('internship_management/student_client/push_crm_client/'.$sid);

/* arrays from controller */
$applications   = (isset($applications) && is_array($applications)) ? $applications : [];
$files          = (isset($files) && is_array($files)) ? $files : [];
$logs           = (isset($logs) && is_array($logs)) ? $logs : [];
$notes          = (isset($notes) && is_array($notes)) ? $notes : [];
$crm_invoices   = (isset($crm_invoices) && is_array($crm_invoices)) ? $crm_invoices : [];
$crm_contracts  = (isset($crm_contracts) && is_array($crm_contracts)) ? $crm_contracts : [];
$invoices       = (isset($invoices) && is_array($invoices)) ? $invoices : [];
$contracts      = (isset($contracts) && is_array($contracts)) ? $contracts : [];

$notes_count = isset($notes_count) ? (int)$notes_count : count($notes);
$files_count = isset($files_count) ? (int)$files_count : count($files);
$logs_count  = isset($logs_count) ? (int)$logs_count  : count($logs);
$apps_count  = count($applications);

/* -------------- FALLBACK QUERY (DB) -------------- */
if (isset($CI->db)) {
    // files fallback: tblinternship_files (student_id, file_name, file_path, file_type, dateupload, created_at)
    if (empty($files) && sc_table_exists($CI->db, 'tblinternship_files')) {
        $t = 'tblinternship_files';
        if (sc_field_exists($CI->db,'student_id',$t)) {
            $CI->db->where('student_id', $sid);
            if (sc_field_exists($CI->db,'id',$t)) $CI->db->order_by('id','DESC');
            $files = $CI->db->get($t)->result_array();
        }
    }

    // notes fallback: tblinternship_notes (student_id, staff_id, content, note_type, file, reminder_at, created_at)
    if (empty($notes) && sc_table_exists($CI->db, 'tblinternship_notes')) {
        $t = 'tblinternship_notes';
        if (sc_field_exists($CI->db,'student_id',$t)) {
            $CI->db->where('student_id', $sid);
            if (sc_field_exists($CI->db,'id',$t)) $CI->db->order_by('id','DESC');
            $notes = $CI->db->get($t)->result_array();
        }
    }

    // logs fallback: try tblinternship_logs then tblinternship_processing_logs (student_id, staff_id, content/action, created_at/datecreated)
    if (empty($logs)) {
        $cands = ['tblinternship_logs','tblinternship_processing_logs','tblinternship_student_logs','tblstudent_logs'];
        foreach ($cands as $t) {
            if (!sc_table_exists($CI->db, $t)) continue;
            if (!sc_field_exists($CI->db,'student_id',$t) && !sc_field_exists($CI->db,'rel_id',$t)) continue;

            if (sc_field_exists($CI->db,'student_id',$t)) {
                $CI->db->where('student_id', $sid);
            } else {
                $CI->db->where('rel_id', $sid);
                if (sc_field_exists($CI->db,'rel_type',$t)) $CI->db->where('rel_type','student_client');
            }
            if (sc_field_exists($CI->db,'id',$t)) $CI->db->order_by('id','DESC');
            $logs = $CI->db->get($t)->result_array();
            break;
        }
    }
}

// recount after fallback
$notes_count = isset($notes_count) ? (int)$notes_count : count($notes);
$files_count = isset($files_count) ? (int)$files_count : count($files);
$logs_count  = isset($logs_count) ? (int)$logs_count  : count($logs);
?>
<?php init_head(); ?>

<?php

?>

<style>
/* =========================
   SC (IFK Theme) - Scoped
   Palette: #00325a / #96bc17 / #00a6dc / #ffffff
   ========================= */
:root{
  --ifk-navy:#00325a;
  --ifk-green:#96bc17;
  --ifk-cyan:#00a6dc;

  --ifk-text:#1e293b;
  --ifk-muted:#64748b;
  --ifk-border:rgba(0,50,90,.15);

  --ifk-soft:rgba(0,166,220,.06);
  --ifk-soft-2:rgba(150,188,23,.10);

  --ifk-shadow:0 10px 35px rgba(0,50,90,.10);
  --ifk-shadow-2:0 6px 18px rgba(0,50,90,.08);
}

/* Wrapper */
.sc-wrap{
  background:var(--ifk-soft);
  border-radius:16px;
  padding:18px;
}

/* Card */
.sc-card{
  background:#fff;
  border-radius:18px;
  box-shadow:var(--ifk-shadow);
  border:1px solid var(--ifk-border);
  margin-bottom:18px;
}
.sc-card-body{padding:18px;}

/* Actions */
.sc-actions{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  align-items:center;
  margin-bottom:14px;
}
.sc-actions .btn{border-radius:10px;font-weight:800;}

/* Header */
.sc-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:14px;
  flex-wrap:wrap;
}
.sc-header-left{
  display:flex;
  align-items:center;
  gap:16px;
  min-width:260px;
}
.sc-avatar{
  width:76px;height:76px;
  border-radius:16px;
  overflow:hidden;
  background:rgba(0,166,220,.08);
  border:1px solid var(--ifk-border);
  display:flex;align-items:center;justify-content:center;
}
.sc-avatar img{width:100%;height:100%;object-fit:cover;display:block;}

.sc-title{
  font-size:28px;
  font-weight:900;
  margin:0 0 6px;
  color:var(--ifk-navy);
}

.sc-subrow{
  display:flex;
  flex-wrap:wrap;
  gap:14px;
  color:var(--ifk-text);
  font-weight:700;
}
.sc-subrow span{display:inline-flex;gap:6px;align-items:center;}

.sc-header-right{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}

/* Pill */
.sc-pill{
  background:rgba(150,188,23,.16);
  color:#2f5e00;
  padding:8px 14px;
  border-radius:999px;
  font-weight:900;
  font-size:13px;
  border:1px solid rgba(150,188,23,.35);
}

/* Buttons (keep bootstrap behavior, only polish) */
.sc-btn{border-radius:14px;padding:10px 16px;font-weight:900;}

/* Tabs */
.sc-tabs{
  padding:0 18px;
  background:#fff;
  border-bottom:1px solid var(--ifk-border);
  border-top-left-radius:18px;
  border-top-right-radius:18px;
}
.sc-tabs.nav-tabs>li>a{
  border:0 !important;
  margin-right:14px;
  color:var(--ifk-muted);
  font-weight:800;
  padding:16px 6px;
}
.sc-tabs.nav-tabs>li>a:hover{color:var(--ifk-navy);}
.sc-tabs.nav-tabs>li.active>a{
  color:var(--ifk-navy);
  border:0;
  background:transparent;
  position:relative;
}
.sc-tabs.nav-tabs>li.active>a:after{
  content:"";
  position:absolute;
  left:0;right:0;bottom:-1px;
  height:3px;
  background:var(--ifk-green);
  border-radius:2px;
}

/* Tab content */
.sc-tab-content{
  padding:18px;
  background:#fff;
  border-bottom-left-radius:18px;
  border-bottom-right-radius:18px;
  border:1px solid var(--ifk-border);
  border-top:0;
}

/* Grid */
.sc-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
@media(max-width:991px){.sc-grid{grid-template-columns:1fr;}}

/* Boxes / KV */
.sc-box-title{
  font-size:18px;
  font-weight:900;
  margin:0 0 14px;
  color:var(--ifk-navy);
  display:flex;gap:8px;align-items:center;
}
.sc-kv{
  display:flex;
  justify-content:space-between;
  gap:18px;
  padding:10px 0;
  border-bottom:1px dashed rgba(0,50,90,.18);
}
.sc-k{color:var(--ifk-muted);font-weight:800;}
.sc-v{font-weight:900;color:var(--ifk-text);text-align:right;max-width:70%;}

/* Metrics */
.sc-metrics{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;}
.sc-metric{
  background:rgba(0,166,220,.08);
  border:1px solid var(--ifk-border);
  border-radius:999px;
  padding:6px 12px;
  font-weight:900;
  font-size:12px;
  color:var(--ifk-navy);
}

/* Table tweaks */
.table>thead>tr>th{font-weight:900;color:var(--ifk-navy);}
.table>tbody>tr>td{vertical-align:middle;}
.btn-xs{border-radius:8px;font-weight:800;}

/* Badges */
.sc-badge{
  display:inline-block;
  padding:2px 8px;
  border-radius:999px;
  font-size:12px;
  line-height:18px;
  background:rgba(0,50,90,.06);
  color:var(--ifk-navy);
  font-weight:700;
  border:1px solid rgba(0,50,90,.10);
}
.sc-badge-info{
  background:rgba(0,166,220,.12);
  color:var(--ifk-cyan);
  border-color:rgba(0,166,220,.25);
}
.sc-badge-success{
  background:rgba(150,188,23,.16);
  color:#2f5e00;
  border-color:rgba(150,188,23,.30);
}
.sc-badge-warning{
  background:#fef9c3;
  color:#854d0e;
  border-color:#fde68a;
}
.sc-badge-danger{
  background:#fee2e2;
  color:#991b1b;
  border-color:#fecaca;
}
.sc-badge-muted{
  background:#f1f5f9;
  color:var(--ifk-muted);
  border-color:#e2e8f0;
}
</style>

<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<div class="sc-wrap">

  <div class="sc-actions">
    <a href="<?= $url_back; ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Quay lại</a>
    <a href="<?= $url_edit; ?>" class="btn btn-info"><i class="fa fa-pencil"></i> Sửa hồ sơ</a>
    
    <a href="<?= $url_a4; ?>" target="_blank" class="btn btn-success">
   <i class="fa fa-print"></i> In A4
</a>
</div>

  <div class="sc-card"><div class="sc-card-body">
    <div class="sc-header">
      <div class="sc-header-left">
        <div class="sc-avatar">
          <img id="sc-avatar-img" src="<?= $avatar_candidates[0]; ?>" data-candidates='<?= html_escape(json_encode($avatar_candidates)); ?>' alt="avatar">
        </div>
        <div>
          <div class="sc-title"><?= html_escape($name); ?></div>
          <div class="sc-subrow">
            <span><i class="fa fa-id-card-o"></i> Mã hồ sơ: <?= html_escape($code); ?></span>
            <span><i class="fa fa-envelope-o"></i> Email: <?= html_escape($email); ?></span>
            <span><i class="fa fa-phone"></i> SĐT: <?= html_escape($phone); ?></span>
          </div>
        </div>
      </div>

      <div class="sc-header-right">
        <?php if ($has_crm): ?>
          <span class="sc-pill"><i class="fa fa-link"></i> Đã liên kết CRM #<?= (int)$crm_id; ?></span>
          <a class="btn btn-success sc-btn" target="_blank" href="<?= $crm_url; ?>"><i class="fa fa-external-link"></i> Xem CRM</a>
          <a class="btn btn-info sc-btn" href="<?= $url_push_crm; ?>"><i class="fa fa-refresh"></i> Đẩy lại CRM</a>
        <?php else: ?>
          <a class="btn btn-info sc-btn" href="<?= $url_push_crm; ?>"><i class="fa fa-cloud-upload"></i> Liên kết CRM</a>
        <?php endif; ?>
      </div>
    </div>
  </div></div>

  <div class="sc-card">
    <div class="panel-body" style="padding:0;">
      <ul class="nav nav-tabs sc-tabs" role="tablist" id="student-client-tabs">
        <li class="active"><a href="#tab_info" data-toggle="tab">Thông tin</a></li>
        <li><a href="#tab_documents" data-toggle="tab">Tài liệu</a></li>
        <li><a href="#tab_logs" data-toggle="tab">Nhật ký xử lý</a></li>
        <li><a href="#tab_notes" data-toggle="tab">Ghi chú xử lý</a></li>
        <li><a href="#tab_invoices" data-toggle="tab">Hóa đơn</a></li>
        <li><a href="#tab_contracts" data-toggle="tab">Hợp đồng</a></li>
      </ul>

      <div class="tab-content sc-tab-content">
        <div class="tab-pane active" id="tab_info">
          <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px;">
            <div class="sc-box-title" style="margin:0;"><i class="fa fa-dashboard"></i> Tổng quan hồ sơ</div>
            <div class="sc-metrics">
              <span class="sc-metric">Ứng tuyển: <?= (int)$apps_count; ?></span>
              <span class="sc-metric">Ghi chú: <?= (int)$notes_count; ?></span>
              <span class="sc-metric">Tài liệu: <?= (int)$files_count; ?></span>
              <span class="sc-metric">Nhật ký: <?= (int)$logs_count; ?></span>
            </div>
          </div>

          <div class="sc-grid">
            <div class="sc-card" style="box-shadow:none;"><div class="sc-card-body">
              <div class="sc-box-title"><i class="fa fa-user"></i> Thông tin cá nhân</div>
              <div class="sc-kv"><div class="sc-k">Họ tên</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['full_name','name','fullname'])); ?></div></div>
              <div class="sc-kv"><div class="sc-k">Giới tính</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['gender','sex'])); ?></div></div>
              <div class="sc-kv"><div class="sc-k">Ngày sinh</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['dob','birthday','birth_date'])); ?></div></div>
              <div class="sc-kv"><div class="sc-k">Email</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['email'])); ?></div></div>
              <div class="sc-kv"><div class="sc-k">SĐT sinh viên</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['phone_student','student_phone','phone','phonenumber'])); ?></div></div>
              <div class="sc-kv"><div class="sc-k">SĐT phụ huynh</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['parent_phone','guardian_phone','phone_parent'])); ?></div></div>
              <div class="sc-kv"><div class="sc-k">CMND/CCCD</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['cccd','id_number','cmnd','passport'])); ?></div></div>
              <div class="sc-kv"><div class="sc-k">Địa chỉ</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['address','current_address'])); ?></div></div>
              <div class="sc-kv" style="border-bottom:0;"><div class="sc-k">Ngày tạo hồ sơ</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['created_at','datecreated','created_date'])); ?></div></div>
            </div></div>

            <div class="sc-card" style="box-shadow:none;"><div class="sc-card-body">
              <div class="sc-box-title"><i class="fa fa-graduation-cap"></i> Học vấn & đơn tuyển</div>
              <div class="sc-kv"><div class="sc-k">Trường</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['school','university','school_name'])); ?></div></div>
              <div class="sc-kv"><div class="sc-k">Ngành / Chuyên ngành</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['major','department','faculty','major_name'])); ?></div></div>
              <div class="sc-kv"><div class="sc-k">JLPT</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['jlpt','jlpt_level'])); ?></div></div>
              <div class="sc-kv"><div class="sc-k">Tiếng Anh</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['english','english_level'])); ?></div></div>
              <div class="sc-kv"><div class="sc-k">Công ty</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['company','company_name'])); ?></div></div>
              <div class="sc-kv"><div class="sc-k">Vị trí (JP)</div><div class="sc-v"><?= html_escape(sc_val($studentArr,['position_jp','position','job_position'])); ?></div></div>

              <!-- <?php $status_raw = sc_val($studentArr,['status','profile_status','docs_status'], ''); ?> -->
              <!--<?php $status_raw = sc_val($studentArr,['dossier_progress','status','profile_status','docs_status'], ''); ?>-->
              <?php $status_raw = sc_val($studentArr,['status','dossier_progress','profile_status','docs_status'], ''); ?>
              <div class="sc-kv"><div class="sc-k">Trạng thái hồ sơ</div><div class="sc-v"><?= html_escape(sc_status_label($status_raw)); ?></div></div>

              <?php $pv_raw = sc_val($studentArr,['interview_result','pv_result'], ''); ?>
              <div class="sc-kv" style="border-bottom:0;"><div class="sc-k">Kết quả PV</div><div class="sc-v"><?= html_escape(sc_status_label($pv_raw)); ?></div></div>
            </div></div>
          </div>

          <div class="sc-card" style="box-shadow:none;"><div class="sc-card-body">
            <div class="sc-box-title"><i class="fa fa-briefcase"></i> Đơn ứng tuyển</div>
            <?php if (empty($applications)): ?>
              <div class="text-muted">Chưa có đơn ứng tuyển.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-bordered table-hover">
                  <thead><tr><th style="width:70px">#</th><th>Đơn / Job</th><th>Trạng thái</th><th>Ngày tạo</th><th style="width:120px">Thao tác</th></tr></thead>
                  <tbody>
                  <?php foreach ($applications as $a): ?>
                    <tr>
                      <td><?= (int)($a['id'] ?? 0); ?></td>
                      <td><?= html_escape($a['job_title'] ?? ($a['job_name'] ?? ('Job #'.($a['job_id'] ?? '')))); ?></td>
                      <td><?= html_escape(sc_status_label($a['status'] ?? ($a['application_status'] ?? ''))); ?></td>
                      <td><?= html_escape($a['created_at'] ?? ($a['datecreated'] ?? '')); ?></td>
                      <td><?php if (!empty($a['id'])): ?><a class="btn btn-xs btn-default" href="<?= admin_url('internship_management/internship_applications/view/'.$a['id']); ?>">Xem</a><?php else: ?>—<?php endif; ?></td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div></div>
        </div>

        <div class="tab-pane" id="tab_documents">
          <div class="sc-box-title"><i class="fa fa-paperclip"></i> Tài liệu</div>

          <?= form_open_multipart(admin_url('internship_management/student_client/upload_document/'.$sid)); ?>
            <div class="row">
              <div class="col-md-4">
                <label>Loại tài liệu</label>
                <select name="doc_type" class="form-control">
                  <option value="CV">CV</option>
                  <option value="Tệp hồ sơ">Tệp hồ sơ</option>
                  <option value="Khác">Khác</option>
                </select>
              </div>
              <div class="col-md-6">
                <label>Chọn file</label>
                <input type="file" name="file" class="form-control">
              </div>
              <div class="col-md-2" style="padding-top:27px;">
                <button type="submit" class="btn btn-info"><i class="fa fa-upload"></i> Tải lên</button>
              </div>
            </div>
          <?= form_close(); ?>
          <hr>

          <!--<?php if (!empty($files)): ?>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead><tr><th>Thời gian</th><th>Loại</th><th>Tệp</th><th>User</th></tr></thead>
                <tbody>
                <?php foreach ($files as $f): ?>
                  <?php
                    $fn = $f['file_name'] ?? ($f['filename'] ?? ($f['name'] ?? ''));
                    $docType = $f['doc_type'] ?? ($f['file_type'] ?? ($f['type'] ?? ($f['document_type'] ?? '')));
                    $created = $f['created_at'] ?? ($f['dateupload'] ?? ($f['datecreated'] ?? ''));
                    // Resolve staff info
                    $staff_id = $f['staff_id'] ?? ($f['uploaded_by'] ?? ($f['created_by'] ?? ($f['staff'] ?? ($f['user_id'] ?? ($f['addedfrom'] ?? null)))));
                    $staff_name = $f['staff_name'] ?? ($f['staff_full_name'] ?? ($f['user_name'] ?? ($f['staff'] ?? '')));
                    if (is_numeric($staff_name)) { $staff_name = ''; }
                    if (is_numeric($staff_id) && (int)$staff_id > 0) {
                        $resolved = sc_staff_name((int)$staff_id);
                        if ($resolved) $staff_name = $resolved;
                    }
                    // If we only got a free-text staff string, keep it as name
                    if (!$staff_name && !is_numeric($staff_id) && is_string($staff_id)) {
                        $staff_name = (string)$staff_id;
                        $staff_id = null;
                    }
                    $path = $f['file_path'] ?? '';
                    if (!empty($path)) {
                        $direct = (strpos($path,'http')===0 || strpos($path,'//')===0) ? $path : base_url(ltrim($path,'/'));
                    } else {
                        $direct = $fn ? base_url('uploads/internship_documents/'.$sid.'/'.ltrim($fn,'/')) : '';
                    }
                  ?>
                  <tr>
                    <td><?= html_escape($created); ?></td>
                    <td><?= html_escape($docType ?: '—'); ?></td>
                    <td>
                      <?php if ($direct): ?>
                        <a href="<?= $direct; ?>" target="_blank"><?= html_escape($fn); ?></a>
                      <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?= html_escape($staff_name ?: ($staff_id ? ('Nhân sự #'.$staff_id) : '—')); ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <p class="text-muted">Nguồn dữ liệu: tblinternship_files</p>
          <?php else: ?>
            <div class="text-muted">Chưa có tài liệu.</div>
          <?php endif; ?> -->
          
          <?php if (!empty($files)): ?>
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th style="width:160px;">Thời gian</th>
                      <th style="width:230px;">Loại</th>
                      <th>Tệp</th>
                      <th style="width:160px;">User</th>
                      <th style="width:160px;">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($files as $f): ?>
                    <?php
                      $doc_id = (int)($f['id'] ?? 0);
            
                      $fn = $f['file_name'] ?? ($f['filename'] ?? ($f['name'] ?? ($f['file'] ?? '')));
                      $docType = $f['doc_type'] ?? ($f['file_type'] ?? ($f['type'] ?? ($f['document_type'] ?? ($f['category'] ?? 'Khác'))));
                      $created = $f['created_at'] ?? ($f['dateupload'] ?? ($f['datecreated'] ?? ($f['date_uploaded'] ?? '')));
            
                      $staff_id = $f['staff_id'] ?? ($f['uploaded_by'] ?? ($f['created_by'] ?? ($f['staff'] ?? ($f['user_id'] ?? ($f['addedfrom'] ?? null)))));
                      $staff_name = $f['staff_name'] ?? ($f['staff_full_name'] ?? ($f['user_name'] ?? ($f['staff'] ?? '')));
            
                      if (is_numeric($staff_name)) {
                          $staff_name = '';
                      }
            
                      if (is_numeric($staff_id) && (int)$staff_id > 0) {
                          $resolved = sc_staff_name((int)$staff_id);
                          if ($resolved) {
                              $staff_name = $resolved;
                          }
                      }
            
                      if (!$staff_name && !is_numeric($staff_id) && is_string($staff_id)) {
                          $staff_name = (string)$staff_id;
                          $staff_id = null;
                      }
            
                      $path = $f['file_path'] ?? '';
                      if (!empty($path)) {
                          $direct = (strpos($path,'http')===0 || strpos($path,'//')===0) ? $path : base_url(ltrim($path,'/'));
                      } else {
                          $direct = $fn ? base_url('uploads/internship_documents/'.$sid.'/'.ltrim($fn,'/')) : '';
                      }
            
                      $updateDocUrl = $doc_id > 0 ? admin_url('internship_management/student_client/update_document/'.$sid.'/'.$doc_id) : '';
                      $deleteDocUrl = $doc_id > 0 ? admin_url('internship_management/student_client/delete_document/'.$sid.'/'.$doc_id) : '';
                    ?>
                    <tr>
                      <td><?= html_escape($created ?: '—'); ?></td>
            
                      <td>
                        <?php if ($doc_id > 0): ?>
                          <?= form_open($updateDocUrl, ['class' => 'form-inline']); ?>
                            <select name="doc_type" class="form-control input-sm" style="width:145px;">
                              <?php foreach (['CV','Tệp hồ sơ','Passport','Bằng cấp','Ảnh','Khác'] as $opt): ?>
                                <option value="<?= html_escape($opt); ?>" <?= ($docType === $opt ? 'selected' : ''); ?>>
                                  <?= html_escape($opt); ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-default btn-xs" title="Lưu loại tài liệu">
                              <i class="fa fa-save"></i>
                            </button>
                          <?= form_close(); ?>
                        <?php else: ?>
                          <?= html_escape($docType ?: '—'); ?>
                        <?php endif; ?>
                      </td>
            
                      <td>
                        <?php if ($direct): ?>
                          <a href="<?= $direct; ?>" target="_blank"><?= html_escape($fn); ?></a>
                        <?php else: ?>
                          —
                        <?php endif; ?>
                      </td>
            
                      <td><?= html_escape($staff_name ?: ($staff_id ? ('Nhân sự #'.$staff_id) : '—')); ?></td>
            
                      <td>
                        <?php if ($doc_id > 0): ?>
                          <?= form_open($deleteDocUrl, [
                            'style' => 'display:inline;',
                            'onsubmit' => "return confirm('Bạn chắc chắn muốn xóa tài liệu này? File vật lý cũng sẽ bị xóa nếu còn tồn tại trong thư mục upload.');"
                          ]); ?>
                            <button type="submit" class="btn btn-danger btn-xs">
                              <i class="fa fa-trash"></i> Xóa
                            </button>
                          <?= form_close(); ?>
                        <?php else: ?>
                          <span class="text-muted">Không có ID</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <p class="text-muted">Nguồn dữ liệu: tblinternship_files</p>
            <?php else: ?>
              <div class="text-muted">Chưa có tài liệu.</div>
            <?php endif; ?>
          
        </div>

        <div class="tab-pane" id="tab_logs">
          <div class="sc-box-title"><i class="fa fa-history"></i> Nhật ký xử lý</div>
          <?php if (!empty($logs)): ?>
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead><tr><th>Thời gian</th><th>User</th><th>Nội dung</th></tr></thead>
                <tbody>
                <?php foreach ($logs as $l): ?>
                  <?php
                    $when = $l['created_at'] ?? ($l['datecreated'] ?? ($l['date'] ?? ''));
                    $staff_id = $l['staff_id'] ?? ($l['created_by'] ?? ($l['staff'] ?? 0));
                    $staff_name = $l['staff_name'] ?? ($l['staff_name'] ?? ($l['staff'] ?? ''));
                    if (!$staff_name && $staff_id) $staff_name = sc_staff_name($staff_id);
                    $content_html = sc_format_log_content($l);
                  ?>
                  <tr>
                    <td><?= html_escape($when ?: '—'); ?></td>
                    <td><?= html_escape($staff_name ?: ($staff_id ? ('Nhân sự #'.$staff_id) : '—')); ?></td>
                    <td><?= $content_html; ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-muted">Chưa có nhật ký.</div>
          <?php endif; ?>
        </div>

        
        <div class="tab-pane" id="tab_notes">
          <div class="sc-box-title"><i class="fa fa-sticky-note-o"></i> Ghi chú xử lý</div>

          <?= form_open_multipart(admin_url('internship_management/student_client/add_note/'.$sid), ['id'=>'sc-note-form']); ?>
            <input type="hidden" name="note_type" value="internal">

            <div class="row">
              <div class="col-md-12">
                <label for="sc_note_content" class="control-label">Nội dung ghi chú <span class="text-danger">*</span></label>
                <textarea id="sc_note_content" name="content" class="form-control" rows="4" placeholder="Nhập ghi chú..."></textarea>
                <p class="text-info" style="margin-top:6px;">(Bạn có thể để trống nội dung nếu chỉ upload file đính kèm.)</p>
              </div>
            </div>

            <div class="row">
              <div class="col-md-12">
                <label class="control-label">File đính kèm</label>
                <input type="file" name="file[]" class="form-control" multiple>
                <p class="text-info" style="margin-top:6px;">Hỗ trợ nhiều file, tối đa 20MB/file.</p>
              </div>
            </div>

            <div style="margin-top:10px;">
              <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Thêm ghi chú</button>
            </div>
          <?= form_close(); ?>

          <hr style="margin:16px 0;">

          <!--<?php if (!empty($notes)): ?>
            <div class="table-responsive">
              <table id="tbl_student_client_view" class="table table-striped table-bordered dt-table" data-order-col="0" data-order-type="desc" style="width:100%">
                <thead>
                  <tr>
                    <th style="width:180px;">Thời gian</th>
                    <th style="width:120px;">User</th>
                    <th>Nội dung</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($notes as $n): ?>
                    <?php
                      $when = $n['created_at'] ?? ($n['datecreated'] ?? ($n['date'] ?? ''));
                      $staff_id = $n['staff_id'] ?? ($n['created_by'] ?? ($n['user_id'] ?? ''));
                      $staff_name = $n['staff_name'] ?? '';
                      if ($staff_name === '' && $staff_id !== '' && function_exists('get_staff_full_name')) {
                        $staff_name = get_staff_full_name($staff_id);
                      }
                      if ($staff_name === '' && $staff_id !== '') $staff_name = $staff_id;

                      $content = $n['content'] ?? ($n['note'] ?? ($n['description'] ?? ''));
                      $ntype = $n['note_type'] ?? '';
                      $file = $n['file'] ?? '';
                      $file_link = '';
                      if (!empty($file)) {
                        // allow storing direct path/url or just filename
                        if (strpos($file,'http') === 0 || strpos($file,'//') === 0) {
                          $file_link = $file;
                        } elseif (strpos($file,'/') !== false) {
                          $file_link = base_url(ltrim($file,'/'));
                        } else {
                          // common folders
                          $file_link = base_url('uploads/internship_notes/'.$sid.'/'.ltrim($file,'/'));
                        }
                      }

                      $content_txt = trim((string)$content);
$content_html = $content_txt !== '' ? nl2br(html_escape($content_txt)) : '<span class="text-muted"><i>(Không có nội dung)</i></span>';

// attachment
if (!empty($file_link)) {
    $content_html .= '<div style="margin-top:6px;"><i class="fa fa-paperclip"></i> <a target="_blank" href="' . html_escape($file_link) . '">' . html_escape($file) . '</a></div>';
} elseif (!empty($file)) {
    $content_html .= '<div style="margin-top:6px;"><i class="fa fa-paperclip"></i> ' . html_escape($file) . '</div>';
}
?>
                    <tr>
                      <td><?= html_escape($when ?: '—'); ?></td>
                      <td><?= html_escape($staff_name ?: '—'); ?></td>
                      <td><?= $content_html; ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-muted">Chưa có ghi chú.</div>
          <?php endif; ?> -->
          
          <?php if (!empty($notes)): ?>
              <div class="table-responsive">
                <table id="tbl_student_client_view" class="table table-striped table-bordered dt-table" data-order-col="0" data-order-type="desc" style="width:100%">
                  <thead>
                    <tr>
                      <th style="width:180px;">Thời gian</th>
                      <th style="width:120px;">User</th>
                      <th>Nội dung</th>
                      <th style="width:150px;">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($notes as $n): ?>
                      <?php
                        $note_id = (int)($n['id'] ?? 0);
            
                        $when = $n['created_at'] ?? ($n['datecreated'] ?? ($n['date'] ?? ''));
                        $staff_id = $n['staff_id'] ?? ($n['created_by'] ?? ($n['user_id'] ?? ''));
                        $staff_name = $n['staff_name'] ?? '';
            
                        if ($staff_name === '' && $staff_id !== '' && function_exists('get_staff_full_name')) {
                          $staff_name = get_staff_full_name($staff_id);
                        }
            
                        if ($staff_name === '' && $staff_id !== '') {
                          $staff_name = $staff_id;
                        }
            
                        $content = $n['content'] ?? ($n['note'] ?? ($n['description'] ?? ($n['message'] ?? '')));
                        $ntype = $n['note_type'] ?? ($n['type'] ?? 'internal');
                        $reminder = $n['reminder_at'] ?? '';
                        $file = $n['file'] ?? ($n['attachment'] ?? ($n['file_name'] ?? ($n['filename'] ?? '')));
            
                        $file_link = '';
                        if (!empty($file)) {
                          if (strpos($file,'http') === 0 || strpos($file,'//') === 0) {
                            $file_link = $file;
                          } elseif (strpos($file,'/') !== false) {
                            $file_link = base_url(ltrim($file,'/'));
                          } else {
                            $file_link = base_url('uploads/internship_notes/'.$sid.'/'.ltrim($file,'/'));
                          }
                        }
            
                        $content_txt = trim((string)$content);
                        $content_html = $content_txt !== ''
                          ? nl2br(html_escape($content_txt))
                          : '<span class="text-muted"><i>(Không có nội dung)</i></span>';
            
                        if (!empty($file_link)) {
                            $content_html .= '<div style="margin-top:6px;"><i class="fa fa-paperclip"></i> <a target="_blank" href="' . html_escape($file_link) . '">' . html_escape($file) . '</a></div>';
                        } elseif (!empty($file)) {
                            $content_html .= '<div style="margin-top:6px;"><i class="fa fa-paperclip"></i> ' . html_escape($file) . '</div>';
                        }
            
                        $updateNoteUrl = $note_id > 0 ? admin_url('internship_management/student_client/update_note/'.$sid.'/'.$note_id) : '';
                        $deleteNoteUrl = $note_id > 0 ? admin_url('internship_management/student_client/delete_note/'.$sid.'/'.$note_id) : '';
                        $editBoxId = 'sc-note-edit-'.$note_id;
                      ?>
            
                      <tr>
                        <td><?= html_escape($when ?: '—'); ?></td>
                        <td><?= html_escape($staff_name ?: '—'); ?></td>
                        <td><?= $content_html; ?></td>
                        <td>
                          <?php if ($note_id > 0): ?>
                            <button type="button" class="btn btn-default btn-xs sc-toggle-note-edit" data-target="#<?= $editBoxId; ?>">
                              <i class="fa fa-pencil"></i> Sửa
                            </button>
            
                            <?= form_open($deleteNoteUrl, [
                              'style' => 'display:inline;',
                              'onsubmit' => "return confirm('Bạn chắc chắn muốn xóa ghi chú này? File đính kèm nếu có cũng sẽ bị xóa.');"
                            ]); ?>
                              <button type="submit" class="btn btn-danger btn-xs">
                                <i class="fa fa-trash"></i> Xóa
                              </button>
                            <?= form_close(); ?>
                          <?php else: ?>
                            <span class="text-muted">Không có ID</span>
                          <?php endif; ?>
                        </td>
                      </tr>
            
                      <?php if ($note_id > 0): ?>
                        <tr id="<?= $editBoxId; ?>" class="sc-note-edit-row" style="display:none;">
                          <td colspan="4">
                            <?= form_open_multipart($updateNoteUrl, ['autocomplete' => 'off']); ?>
            
                              <div class="form-group">
                                <label>Nội dung ghi chú</label>
                                <textarea name="content" class="form-control" rows="3"><?= html_escape($content); ?></textarea>
                              </div>
            
                              <div class="row">
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label>Loại ghi chú</label>
                                    <select name="note_type" class="form-control">
                                      <option value="internal" <?= $ntype === 'internal' ? 'selected' : ''; ?>>Nội bộ</option>
                                      <option value="normal" <?= $ntype === 'normal' ? 'selected' : ''; ?>>Bình thường</option>
                                      <option value="public" <?= $ntype === 'public' ? 'selected' : ''; ?>>Công khai</option>
                                    </select>
                                  </div>
                                </div>
            
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label>Nhắc nhở</label>
                                    <input type="datetime-local" name="reminder_at" class="form-control" value="<?= html_escape($reminder); ?>">
                                  </div>
                                </div>
            
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label>Thay file đính kèm</label>
                                    <input type="file" name="file" class="form-control">
                                    <?php if (!empty($file)): ?>
                                      <small class="text-muted">Để trống nếu muốn giữ file hiện tại.</small>
                                    <?php endif; ?>
                                  </div>
                                </div>
                              </div>
            
                              <button type="submit" class="btn btn-info btn-sm">
                                <i class="fa fa-save"></i> Lưu ghi chú
                              </button>
            
                              <button type="button" class="btn btn-default btn-sm sc-toggle-note-edit" data-target="#<?= $editBoxId; ?>">
                                Đóng
                              </button>
            
                            <?= form_close(); ?>
                          </td>
                        </tr>
                      <?php endif; ?>
            
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="text-muted">Chưa có ghi chú.</div>
            <?php endif; ?>
          
        </div>
<div class="tab-pane" id="tab_invoices">
          <div class="sc-box-title"><i class="fa fa-file-text-o"></i> Hóa đơn</div>
          <?php if (!$has_crm): ?>
            <div class="alert alert-warning">Chưa liên kết CRM. Vui lòng bấm <b>Liên kết CRM</b> để tạo khách hàng CRM, sau đó hóa đơn sẽ hiển thị ở đây.</div>
          <?php endif; ?>

          <?php if ($has_crm && !empty($crm_invoices)): ?>
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead><tr><th>#</th><th>Số</th><th>Ngày</th><th>Tổng</th><th>Trạng thái</th><th></th></tr></thead>
                <tbody>
                  <?php foreach ($crm_invoices as $inv): ?>
                    <tr>
                      <td><?= (int)($inv['id'] ?? 0); ?></td>
                      <td><?= html_escape($inv['number'] ?? ''); ?></td>
                      <td><?= html_escape($inv['date'] ?? ''); ?></td>
                      <td><?= html_escape($inv['total'] ?? ''); ?></td>
                      <td><?= html_escape($inv['status'] ?? ''); ?></td>
                      <td><?php if (!empty($inv['id'])): ?><a class="btn btn-xs btn-default" href="<?= admin_url('invoices/list_invoices/'.$inv['id']); ?>">Xem</a><?php else: ?>—<?php endif; ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php elseif (!empty($invoices)): ?>
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead><tr><th>#</th><th>Ngày</th><th>Tổng</th><th>Trạng thái</th><th></th></tr></thead>
                <tbody>
                  <?php foreach ($invoices as $inv): ?>
                    <tr>
                      <td><?= (int)($inv['id'] ?? 0); ?></td>
                      <td><?= html_escape($inv['invoice_date'] ?? ($inv['date'] ?? '')); ?></td>
                      <td><?= html_escape($inv['total'] ?? ($inv['amount'] ?? '')); ?></td>
                      <td><?= html_escape(sc_status_label($inv['status'] ?? '')); ?></td>
                      <td><?php if (!empty($inv['id'])): ?><a class="btn btn-xs btn-default" href="<?= admin_url('internship_management/student_client/invoice_view/'.$inv['id']); ?>">Xem</a><?php else: ?>—<?php endif; ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-muted">Chưa có hóa đơn.</div>
          <?php endif; ?>
        </div>

        <div class="tab-pane" id="tab_contracts">
          <div class="sc-box-title"><i class="fa fa-handshake-o"></i> Hợp đồng</div>
          <?php if (!$has_crm): ?>
            <div class="alert alert-warning">Chưa liên kết CRM. Vui lòng bấm <b>Liên kết CRM</b> để tạo khách hàng CRM, sau đó hợp đồng sẽ hiển thị ở đây.</div>
          <?php endif; ?>

          <?php if ($has_crm && !empty($crm_contracts)): ?>
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead><tr><th>#</th><th>Chủ đề</th><th>Ngày</th><th>Trạng thái</th><th></th></tr></thead>
                <tbody>
                  <?php foreach ($crm_contracts as $ct): ?>
                    <tr>
                      <td><?= (int)($ct['id'] ?? 0); ?></td>
                      <td><?= html_escape($ct['subject'] ?? ''); ?></td>
                      <td><?= html_escape($ct['datecreated'] ?? ($ct['datestart'] ?? '')); ?></td>
                      <td><?= html_escape($ct['status'] ?? ''); ?></td>
                      <td><?php if (!empty($ct['id'])): ?><a class="btn btn-xs btn-default" href="<?= admin_url('contracts/contract/'.$ct['id']); ?>">Xem</a><?php else: ?>—<?php endif; ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php elseif (!empty($contracts)): ?>
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead><tr><th>#</th><th>Chủ đề</th><th>Ngày</th><th>Trạng thái</th><th></th></tr></thead>
                <tbody>
                  <?php foreach ($contracts as $ct): ?>
                    <tr>
                      <td><?= (int)($ct['id'] ?? 0); ?></td>
                      <td><?= html_escape($ct['subject'] ?? ($ct['title'] ?? '')); ?></td>
                      <td><?= html_escape($ct['datecreated'] ?? ($ct['created_at'] ?? '')); ?></td>
                      <td><?= html_escape(sc_status_label($ct['status'] ?? '')); ?></td>
                      <td><?php if (!empty($ct['id'])): ?><a class="btn btn-xs btn-default" href="<?= admin_url('internship_management/student_client/contract_view/'.$ct['id']); ?>">Xem</a><?php else: ?>—<?php endif; ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="text-muted">Chưa có hợp đồng.</div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>

</div></div></div></div></div>

<?php init_tail(); ?>

<script>
(function($){
  "use strict";

  // Avatar fallback
  (function(){
    try{
      var img = document.getElementById('sc-avatar-img');
      if(!img) return;
      var list = [];
      try{ list = JSON.parse(img.getAttribute('data-candidates') || '[]'); }catch(e){ list = []; }
      var idx = 0;
      img.onerror = function(){
        idx++;
        if(idx >= list.length) return;
        img.src = list[idx];
      };
    }catch(e){}
  })();

  // Tabs hash support
  function showFromHash(){
    var hash = window.location.hash || '';
    if(hash && hash.indexOf('#tab_') === 0){
      var $a = $('#student-client-tabs a[href="'+hash+'"]');
      if($a.length){ $a.tab('show'); }
    }
  }
  $('#student-client-tabs a[data-toggle="tab"]').on('shown.bs.tab', function(e){
    var href = $(e.target).attr('href');
    if(href && href.charAt(0)==='#'){
      var base = window.location.pathname + window.location.search;
      if(history && history.replaceState){ history.replaceState(null,null, base + href); }
      else { window.location.hash = href; }
    }
  });
  
    // Toggle form sửa ghi chú
  $(document).on('click', '.sc-toggle-note-edit', function(e){
    e.preventDefault();

    var target = $(this).data('target');
    if (target) {
      $(target).toggle();
    }
  });
  
  showFromHash();
})(jQuery);
</script>
</body>
</html>