<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$logs = isset($logs) ? (array)$logs : [];

if (!function_exists('im_log_get')) {
    function im_log_get($row, $key) {
        if (is_array($row)) return $row[$key] ?? null;
        if (is_object($row)) return $row->$key ?? null;
        return null;
    }
}
if (!function_exists('im_log_pick')) {
    function im_log_pick($row, $keys, $default = '') {
        foreach ($keys as $k) {
            $v = im_log_get($row, $k);
            if ($v === null) continue;
            if (is_string($v) && trim($v) !== '') return trim($v);
            if (!is_string($v) && $v !== '') return $v;
        }
        return $default;
    }
}
if (!function_exists('im_log_badge')) {
    function im_log_badge($content, $type = '') {
        $c = mb_strtolower((string)$content);
        $t = mb_strtolower((string)$type);

        // Badge theo hành động
        if (strpos($c, 'đẩy crm') !== false || strpos($c, 'push crm') !== false) {
            return '<span class="im-badge im-badge-blue"><i class="fa fa-cloud-upload"></i> CRM</span>';
        }
        if (strpos($c, 'upload') !== false || preg_match('/\.(doc|docx|pdf|xlsx|xls|png|jpg|jpeg)$/i', $content)) {
            return '<span class="im-badge im-badge-cyan"><i class="fa fa-paperclip"></i> Tài liệu</span>';
        }
        if ($t === 'note' || strpos($t, 'note') !== false || strpos($c,'ghi chú') !== false) {
            return '<span class="im-badge im-badge-navy"><i class="fa fa-sticky-note"></i> Ghi chú</span>';
        }
        if ($t === 'system') {
            return '<span class="im-badge im-badge-green"><i class="fa fa-cog"></i> Hệ thống</span>';
        }
        return '<span class="im-badge im-badge-gray"><i class="fa fa-history"></i> Log</span>';
    }
}
?>

<style>
/* Brand colors */
:root{
  --im-green:#96bc17;
  --im-navy:#00325a;
  --im-cyan:#00a6dc;
  --im-bg:#f4f6f9;
  --im-text:#111827;
  --im-muted:#6b7280;
  --im-border:#e5e7eb;
}

/* Wrapper */
.im-log-card{
  background: var(--im-bg);
  border-radius: 18px;
  padding: 18px;
}

/* Header */
.im-log-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  margin-bottom: 14px;
}
.im-log-title{
  display:flex;
  align-items:center;
  gap:10px;
  font-weight:900;
  color: var(--im-navy);
  font-size: 18px;
}
.im-log-title i{
  color: var(--im-cyan);
}

/* Table container */
.im-table-wrap{
  background:#fff;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0,0,0,.06);
  border:1px solid var(--im-border);
  overflow:hidden;
}

/* Table */
.im-table{
  margin:0;
}
.im-table thead th{
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
  color: var(--im-navy);
  font-weight: 800;
  border-bottom: 1px solid var(--im-border) !important;
  padding: 14px 14px;
}
.im-table tbody td{
  padding: 14px 14px;
  border-top: 1px solid var(--im-border);
  vertical-align: middle;
  color: #1f2937;
}
.im-table tbody tr:hover{
  background: #f9fbff;
}

/* Time chip */
.im-time{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:6px 10px;
  border-radius: 999px;
  background: rgba(0,166,220,.08);
  color: var(--im-navy);
  font-weight: 700;
  font-size: 12px;
  border:1px solid rgba(0,166,220,.18);
}
.im-time i{ color: var(--im-cyan); }

/* User block */
.im-user{
  display:flex;
  align-items:center;
  gap:10px;
  font-weight: 800;
  color: var(--im-navy);
}
.im-user .staff-profile-image-small{
  width:28px !important;
  height:28px !important;
  border-radius: 10px !important;
  object-fit: cover;
}

/* Content */
.im-content{
  display:flex;
  align-items:flex-start;
  gap:10px;
}
.im-content-text{
  line-height: 1.55;
  color:#374151;
  font-weight:600;
}

/* Badge */
.im-badge{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:6px 10px;
  border-radius: 999px;
  font-weight: 800;
  font-size: 12px;
  border:1px solid transparent;
  white-space: nowrap;
}
.im-badge i{ font-size: 12px; }

.im-badge-green{ background: rgba(150,188,23,.12); color: var(--im-navy); border-color: rgba(150,188,23,.25); }
.im-badge-blue{  background: rgba(0,50,90,.10);  color: var(--im-navy); border-color: rgba(0,50,90,.18); }
.im-badge-cyan{  background: rgba(0,166,220,.10); color: var(--im-navy); border-color: rgba(0,166,220,.20); }
.im-badge-navy{  background: rgba(0,50,90,.08);  color: var(--im-navy); border-color: rgba(0,50,90,.15); }
.im-badge-gray{  background: rgba(107,114,128,.10); color:#374151; border-color: rgba(107,114,128,.18); }

/* Empty */
.im-empty{
  padding: 26px;
  text-align:center;
  color: var(--im-muted);
  font-weight:700;
}
</style>

<div class="im-log-card">

  <div class="im-log-header">
    <div class="im-log-title">
      <i class="fa fa-history" aria-hidden="true"></i>
      Nhật ký xử lý
    </div>
  </div>

  <div class="im-table-wrap">
    <div class="table-responsive">
      <table class="table im-table im-table table-hover">
        <thead>
          <tr>
            <th style="width:240px;">Thời gian</th>
            <th style="width:300px;">User</th>
            <th>Nội dung</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)) { ?>
            <tr>
              <td colspan="3" class="im-empty">
                Chưa có log nào.
              </td>
            </tr>
          <?php } else { ?>
            <?php foreach ($logs as $lg) {

              $dt = im_log_pick($lg, ['datecreated','created_at','dateadded','datetime'], '');
              $staff_id = (int) im_log_pick($lg, ['staff_id','addedfrom','created_by','user_id'], 0);

              $staff_name = im_log_pick($lg, ['staff_name','fullname','name'], '');
              if ($staff_name === '' && $staff_id > 0 && function_exists('get_staff_full_name')) {
                  $staff_name = get_staff_full_name($staff_id);
              }
              if ($staff_name === '') $staff_name = 'System';

              $content = im_log_pick($lg, ['description','content','message','note','log','action'], '-');
              $type = im_log_pick($lg, ['type','tag','event'], '');

              $badge = im_log_badge($content, $type);
              $time_text = $dt ? _dt($dt) : '-';
            ?>
              <tr>
                <td>
                  <span class="im-time">
                    <i class="fa fa-clock-o"></i>
                    <?php echo html_escape($time_text); ?>
                  </span>
                </td>
                <td>
                  <div class="im-user">
                    <?php
                      // Avatar staff (Perfex)
                      if ($staff_id > 0 && function_exists('staff_profile_image')) {
                          echo staff_profile_image($staff_id, ['staff-profile-image-small']);
                      } else {
                          echo '<span class="im-badge im-badge-gray" style="padding:6px 10px;"><i class="fa fa-user"></i></span>';
                      }
                    ?>
                    <span><?php echo html_escape($staff_name); ?></span>
                  </div>
                </td>
                <td>
                  <div class="im-content">
                    <?php echo $badge; ?>
                    <div class="im-content-text"><?php echo nl2br(html_escape($content)); ?></div>
                  </div>
                </td>
              </tr>
            <?php } ?>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

</div>