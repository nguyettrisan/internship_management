<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// ===== helpers: đọc array/object =====
if (!function_exists('imv')) {
  function imv($row, $key, $default = null) {
    if (is_array($row)) return array_key_exists($key, $row) ? $row[$key] : $default;
    if (is_object($row)) return isset($row->$key) ? $row->$key : $default;
    return $default;
  }
}
if (!function_exists('impick')) {
  function impick($row, $keys, $default = '') {
    foreach ($keys as $k) {
      $v = imv($row, $k, null);
      if ($v === null) continue;
      if (is_string($v)) {
        $v = trim($v);
        if ($v !== '') return $v;
      } else {
        if ($v !== '' && $v !== false) return $v;
      }
    }
    return $default;
  }
}
if (!function_exists('imdt')) {
  function imdt($d) {
    if (!$d) return '-';
    return function_exists('_dt') ? _dt($d) : $d;
  }
}
if (!function_exists('im_escape')) {
  function im_escape($s) { return html_escape((string)$s); }
}

// ===== db accessor inside view (CI) =====
$CI = &get_instance();

// ===== inputs =====
$job_order_id = isset($job_order_id)
  ? (int)$job_order_id
  : (isset($job_order) ? (int)impick($job_order, ['id'], 0) : 0);

$notes = isset($notes) ? (array)$notes : [];
$current_staff_id = 0;
if (function_exists('get_staff_user_id')) $current_staff_id = (int)get_staff_user_id();

// ===== staff cache =====
$__staff_cache = [];
function im_staff_info($staff_id, $row_fallback = null) {
  global $__staff_cache, $CI;

  $staff_id = (int)$staff_id;
  $placeholder = base_url('assets/images/user-placeholder.jpg');

  // 1) nếu row đã join sẵn (giống tab log) -> lấy luôn
  $joined_name = $row_fallback ? impick($row_fallback, ['staff_name','fullname','name'], '') : '';
  $joined_avatar = $row_fallback ? impick($row_fallback, ['profile_image','avatar','staff_avatar'], '') : '';

  if ($joined_name !== '') {
    $avatar_url = $placeholder;
    if ($joined_avatar !== '' && $staff_id > 0) {
      $avatar_url = base_url('uploads/staff_profile_images/'.$staff_id.'/'.$joined_avatar);
    }
    return ['name' => $joined_name, 'avatar' => $avatar_url];
  }

  // 2) cache
  if ($staff_id <= 0) return ['name' => '-', 'avatar' => $placeholder];
  if (isset($__staff_cache[$staff_id])) return $__staff_cache[$staff_id];

  // 3) query tblstaff để lấy firstname lastname + profile_image
  $tbl = db_prefix().'staff';
  $q = $CI->db->select('firstname,lastname,profile_image')->where('staffid', $staff_id)->get($tbl);
  $r = $q ? $q->row() : null;

  $name = $r ? trim($r->firstname.' '.$r->lastname) : ('#'.$staff_id);
  if ($name === '') $name = '#'.$staff_id;

  $avatar = $placeholder;
  if ($r && !empty($r->profile_image)) {
    $avatar = base_url('uploads/staff_profile_images/'.$staff_id.'/'.$r->profile_image);
  }

  $__staff_cache[$staff_id] = ['name' => $name, 'avatar' => $avatar];
  return $__staff_cache[$staff_id];
}
?>

<style>
:root{
  --ifk-navy:#00325a;
  --ifk-green:#96bc17;
  --ifk-cyan:#00a6dc;

  --ifk-border:#e6eef5;
  --ifk-soft:rgba(0,166,220,.08);
  --ifk-soft2:rgba(150,188,23,.12);
  --ifk-muted:#64748b;
  --ifk-text:#0f172a;
}

/* ===== Top form card ===== */
.im-note-card{
  background:#fff;
  border:1px solid var(--ifk-border);
  border-radius:16px;
  padding:16px;
  box-shadow:0 10px 28px rgba(0,0,0,.04);
  margin-bottom:14px;
}
.im-note-card .form-group label{
  font-weight:900;
  color:var(--ifk-navy);
}
.im-note-card textarea{
  border-radius:14px !important;
}
.im-note-help{
  font-weight:700;
  color:var(--ifk-muted);
  margin-top:8px;
}

/* ===== File input nicer ===== */
.im-file-wrap{
  border:1px dashed rgba(0,50,90,.22);
  border-radius:14px;
  padding:12px;
  background:#fbfdff;
}

/* ===== Buttons ===== */
.im-btn-primary{
  border-radius:999px !important;
  font-weight:900 !important;
  padding:9px 16px !important;
  background:var(--ifk-navy) !important;
  border-color:var(--ifk-navy) !important;
}
.im-btn-primary i{ color:#fff; opacity:.95; }
.im-btn-primary:hover{ filter:brightness(1.05); }

.im-note-actions{
  white-space:nowrap;
}
.im-note-actions .btn{
  padding:6px 12px !important;
  border-radius:12px !important;
  font-weight:900 !important;
}
.im-note-actions .btn + .btn{ margin-left:8px; }
.im-btn-edit{
  background:rgba(0,166,220,.10) !important;
  border:1px solid rgba(0,166,220,.28) !important;
  color:var(--ifk-navy) !important;
}
.im-btn-edit i{ color:var(--ifk-cyan); }
.im-btn-del{
  background:rgba(239,68,68,.08) !important;
  border:1px solid rgba(239,68,68,.22) !important;
  color:#991b1b !important;
}
.im-btn-del i{ color:#ef4444; }

/* ===== Header badge ===== */
.im-notes-head{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
  margin-bottom:12px;
}
.im-notes-title{
  font-weight:900;
  color:var(--ifk-navy);
  display:flex;
  align-items:center;
  gap:10px;
  margin:0;
}
.im-notes-title i{ color:var(--ifk-cyan); }
.im-notes-badge{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:7px 12px;
  border-radius:999px;
  background:var(--ifk-soft2);
  border:1px solid rgba(150,188,23,.28);
  font-weight:900;
  color:var(--ifk-navy);
}
.im-notes-badge i{ color:var(--ifk-green); }

/* ===== Table ===== */
.im-table{
  border:1px solid var(--ifk-border);
  border-radius:16px;
  overflow:hidden;
  box-shadow:0 10px 28px rgba(0,0,0,.03);
}
.im-table table{
  margin:0;
}
.im-table thead th{
  background:#f8fbff;
  color:var(--ifk-navy);
  font-weight:900;
  border-bottom:1px solid var(--ifk-border) !important;
}
.im-table tbody td{
  color:var(--ifk-text);
  font-weight:650;
  vertical-align:middle !important;
}
.im-table tbody tr:hover td{
  background:#fbfdff;
}

/* ===== User cell like LOG (icon + avatar + name) ===== */
.im-usercell{
  display:flex;
  align-items:center;
  gap:10px;
  min-width:220px;
}
.im-user-ic{
  width:28px;height:28px;
  border-radius:10px;
  background:var(--ifk-soft);
  border:1px solid rgba(0,166,220,.20);
  display:inline-flex;
  align-items:center;
  justify-content:center;
  flex:0 0 auto;
}
.im-user-ic i{ color:var(--ifk-cyan); }

.im-usercell .avt{
  width:30px;height:30px;border-radius:10px;overflow:hidden;
  border:1px solid #e2e8f0;background:#f3f4f6;flex:0 0 auto;
}
.im-usercell .avt img{width:100%;height:100%;object-fit:cover;display:block;}
.im-usercell .name{
  font-weight:900;
  color:var(--ifk-navy);
  line-height:1.1;
}
.im-usercell .sub{
  display:block;
  margin-top:3px;
  font-size:12px;
  font-weight:800;
  color:var(--ifk-muted);
}

/* ===== Date chip ===== */
.im-datechip{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:6px 10px;
  border-radius:999px;
  background:var(--ifk-soft);
  border:1px solid rgba(0,166,220,.18);
  font-weight:900;
  color:var(--ifk-navy);
}
.im-datechip i{ color:var(--ifk-cyan); }

/* ===== Content ===== */
.im-note-content{
  line-height:1.55;
}
.im-note-filelist{margin:8px 0 0 16px;}
.im-note-filelist li{margin:3px 0;}
.im-note-filelist a{font-weight:800; color:var(--ifk-navy);}

/* ===== Modal ===== */
#imEditNoteModal .modal-content{
  border-radius:16px;
  overflow:hidden;
}
#imEditNoteModal .modal-header{
  background:#f8fbff;
  border-bottom:1px solid var(--ifk-border);
}
#imEditNoteModal .modal-title{
  font-weight:900;
  color:var(--ifk-navy);
}
#imEditNoteModal textarea{
  border-radius:14px !important;
}
</style>

<!-- ===== Form Add Note ===== -->
<div class="row">
  <div class="col-md-12">
    <div class="im-note-card">
      <?php echo form_open_multipart(admin_url('internship_management/internship_job_orders/add_note/'.$job_order_id), ['id'=>'imAddNoteForm']); ?>

        <div class="im-notes-head">
          <h4 class="im-notes-title">
            <i class="fa fa-sticky-note-o"></i>
            Ghi chú
          </h4>
          <span class="im-notes-badge">
            <i class="fa fa-list-ul"></i>
            <?php echo count($notes); ?> ghi chú
          </span>
        </div>

        <div class="form-group">
          <label for="note_content">Nội dung ghi chú <span class="text-danger">*</span></label>
          <textarea id="note_content" name="note" class="form-control" rows="4" placeholder="Nhập ghi chú..."></textarea>
          <div class="im-note-help">(Bạn có thể để trống nội dung nếu chỉ upload file đính kèm.)</div>
        </div>

        <div class="form-group im-file-wrap">
          <label for="note_files" style="margin-bottom:8px;">
            <i class="fa fa-paperclip" style="color:var(--ifk-cyan);"></i> File đính kèm
          </label>
          <input id="note_files" type="file" name="attachments[]" class="form-control" multiple>
          <div class="im-note-help">Hỗ trợ nhiều file, tối đa 20MB/file.</div>
        </div>

        <button type="submit" class="btn btn-primary im-btn-primary">
          <i class="fa fa-plus"></i> Thêm ghi chú
        </button>

      <?php echo form_close(); ?>
    </div>

    <hr class="hr-panel-heading" />
  </div>
</div>

<?php if (empty($notes)) { ?>
  <div class="alert alert-info">
    <i class="fa fa-info-circle"></i> Chưa có ghi chú.
  </div>
<?php } else { ?>

  <!-- ===== Table Notes ===== -->
  <div class="im-table">
    <div class="table-responsive">
      <table id="tbl_job_tab_notes" class="table dt-table">
        <thead>
          <tr>
            <th style="width:220px;">Thời gian</th>
            <th style="width:320px;">User</th>
            <th>Nội dung</th>
            <th style="width:160px;" class="text-right">Thao tác</th>
          </tr>
        </thead>
        <tbody>

        <?php foreach ($notes as $nt) {

          $note_id = (int)impick($nt, ['id','note_id'], 0);
          $dt      = impick($nt, ['datecreated','dateadded','created_at','datetime','time'], '');
          $content = impick($nt, ['note','content','message','body','description'], '');

          $staff_id = (int)impick($nt, ['staff_id','addedfrom','created_by','user_id'], 0);

          $files = imv($nt, 'files', []);
          if (!is_array($files)) $files = [];

          // permission: only creator or admin
          $can_manage = false;
          if (function_exists('is_admin') && is_admin()) $can_manage = true;
          if ($staff_id > 0 && $current_staff_id > 0 && $staff_id === $current_staff_id) $can_manage = true;

          $si = im_staff_info($staff_id, $nt);
        ?>
          <tr>
            <td>
              <span class="im-datechip">
                <i class="fa fa-clock-o"></i>
                <?php echo im_escape(imdt($dt)); ?>
              </span>
            </td>

            <td>
              <div class="im-usercell">
                <span class="im-user-ic" title="Người tạo ghi chú">
                  <i class="fa fa-user"></i>
                </span>

                <span class="avt">
                  <img src="<?php echo im_escape($si['avatar']); ?>"
                       onerror="this.onerror=null;this.src='<?php echo im_escape(base_url('assets/images/user-placeholder.jpg')); ?>';"
                       alt="user">
                </span>

                <div>
                  <span class="name"><?php echo im_escape($si['name']); ?></span>
                  <?php if ($staff_id > 0) { ?>
                    <span class="sub">ID: <?php echo (int)$staff_id; ?></span>
                  <?php } ?>
                </div>
              </div>
            </td>

            <td class="im-note-content">
              <?php echo $content !== '' ? nl2br(im_escape($content)) : '<span class="text-muted">-</span>'; ?>

              <?php if (!empty($files)) { ?>
                <div style="margin-top:10px;">
                  <strong><i class="fa fa-paperclip" style="color:var(--ifk-cyan);"></i> File:</strong>
                  <ul class="im-note-filelist">
                    <?php foreach ($files as $f) {
                      $fname = impick($f, ['file_name','name'], 'file');
                      $fpath = impick($f, ['file_path','path'], '');
                      $url   = $fpath ? site_url($fpath) : '#';
                    ?>
                      <li><a href="<?php echo $url; ?>" target="_blank"><?php echo im_escape($fname); ?></a></li>
                    <?php } ?>
                  </ul>
                </div>
              <?php } ?>
            </td>

            <td class="text-right im-note-actions">
              <?php if ($can_manage && $note_id > 0) { ?>
                <button
                  type="button"
                  class="btn im-btn-edit"
                  data-toggle="modal"
                  data-target="#imEditNoteModal"
                  data-note-id="<?php echo (int)$note_id; ?>"
                  data-note-content="<?php echo im_escape($content); ?>"
                >
                  <i class="fa fa-edit"></i> Sửa
                </button>

                <a class="btn im-btn-del"
                   href="<?php echo admin_url('internship_management/internship_job_orders/delete_note/'.$job_order_id.'/'.$note_id); ?>"
                   onclick="return confirm('Xoá ghi chú này?');">
                  <i class="fa fa-trash"></i> Xoá
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
  </div>

<?php } ?>

<!-- ===== Modal edit ===== -->
<div class="modal fade" id="imEditNoteModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <?php echo form_open(admin_url('internship_management/internship_job_orders/update_note/'.$job_order_id), ['id'=>'imEditNoteForm']); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="fa fa-edit"></i> Sửa ghi chú</h4>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="note_id" id="im_edit_note_id" value="0">
          <div class="form-group">
            <label style="font-weight:900;color:var(--ifk-navy);">Nội dung</label>
            <textarea name="note" id="im_edit_note_content" class="form-control" rows="5"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
          <button type="submit" class="btn btn-primary im-btn-primary"><i class="fa fa-save"></i> Lưu</button>
        </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>

<script>
(function(){
  $('#imEditNoteModal').on('show.bs.modal', function (e) {
    var btn = $(e.relatedTarget);
    $('#im_edit_note_id').val(btn.data('note-id') || 0);
    $('#im_edit_note_content').val(btn.data('note-content') || '');
  });
})();
</script>