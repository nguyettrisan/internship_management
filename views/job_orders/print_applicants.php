<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Applicants Print #<?php echo (int)($job['id'] ?? 0); ?></title>
<style>

/* =====================================================
   IFK LANDSCAPE PRINT TEMPLATE
   A4 Landscape - Stable
   ===================================================== */

@page {
  size: A4 landscape;
  margin: 12mm;
}

body{
  font-family: DejaVu Sans, Arial, sans-serif;
  color:#1e293b;
}

/* Page break */

.page{ page-break-after:always; }
.page:last-child{ page-break-after:auto; }

/* ===== HEADER ===== */

.header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  border-bottom:3px solid #00325a;
  padding-bottom:8px;
  margin-bottom:12px;
}

.logo{ width:220px; }
.logo img{ max-height:54px; max-width:220px; }

.title{
  text-align:center;
  flex:1;
}

.t1{
  font-size:16px;
  font-weight:700;
  letter-spacing:.3px;
  color:#00325a;
}

.t2{
  font-size:12px;
  color:#64748b;
  margin-top:3px;
}

.qr{
  width:170px;
  text-align:right;
}

.qr img{
  width:110px;
  height:110px;
  border:1px solid rgba(0,50,90,.20);
  padding:4px;
  border-radius:4px;
}

/* ===== META BOX ===== */

.meta{
  display:flex;
  gap:14px;
  margin:12px 0 14px;
}

.box{
  flex:1;
  border:1px solid rgba(0,50,90,.20);
  border-radius:8px;
  padding:10px 12px;
  background:#ffffff;
}

.row{
  display:flex;
  gap:10px;
  margin:5px 0;
}

.lbl{
  width:130px;
  color:#00325a;
  font-weight:600;
}

.val{ flex:1; }

/* ===== TABLE ===== */

table{
  width:100%;
  border-collapse:collapse;
}

th,td{
  border:1px solid #00325a;
  padding:8px 10px;
  vertical-align:top;
  font-size:12px;
}

th{
  background:rgba(0,166,220,.08);
  font-weight:700;
  color:#00325a;
}

td{
  color:#1e293b;
}

/* ===== UTIL ===== */

.muted{ color:#64748b; }
.small{ font-size:11px; }

.footer{
  margin-top:12px;
  display:flex;
  justify-content:space-between;
  color:#64748b;
  font-size:11px;
  border-top:1px solid rgba(0,50,90,.20);
  padding-top:6px;
}

.nowrap{ white-space:nowrap; }

</style>
</head>
<body>

<?php
$app_list = is_array($applicants) ? $applicants : (is_object($applicants) ? (array)$applicants : []);
function im_val($row, $keys, $default='') {
  foreach ((array)$keys as $k) {
    if (is_array($row) && isset($row[$k]) && $row[$k] !== '') return $row[$k];
    if (is_object($row) && isset($row->$k) && $row->$k !== '') return $row->$k;
  }
  return $default;
}
$job_id  = (int)($job['id'] ?? 0);
$company = $job['company_name'] ?? '';
$address = $job['address_vi'] ?? ($job['address'] ?? '');
$field   = $job['field_vi'] ?? ($job['industry'] ?? '');
$industry= $job['industry'] ?? '';
?>

<?php function im_print_logo(){
  if (function_exists('get_company_logo')) { echo get_company_logo(); return; }
  if (function_exists('get_option')) {
    $logo = get_option('company_logo');
    if (!empty($logo)) echo '<img src="'.base_url('uploads/company_logo/'.$logo).'" alt="Logo">';
  }
} ?>

<!-- PAGE 1 (VI) -->
<div class="page">
  <div class="header">
    <div class="logo"><?php im_print_logo(); ?></div>
    <div class="title">
      <div class="t1">DANH SÁCH SINH VIÊN ỨNG TUYỂN</div>
      <div class="t2">IFK Internship Program - Applicants List (VI)</div>
    </div>
    <div class="qr"><?php if(!empty($qr_src)){ ?><img src="<?php echo html_escape($qr_src); ?>" alt="QR"><?php } ?></div>
  </div>

  <div class="meta">
    <div class="box">
      <div class="row"><div class="lbl">Mã đơn:</div><div class="val">#<?php echo $job_id; ?></div></div>
      <div class="row"><div class="lbl">Tên công ty:</div><div class="val"><?php echo html_escape($company); ?></div></div>
      <div class="row"><div class="lbl">Địa chỉ:</div><div class="val"><?php echo html_escape($address); ?></div></div>
      <div class="row"><div class="lbl">Lĩnh vực:</div><div class="val"><?php echo html_escape($field); ?></div></div>
      <div class="row"><div class="lbl">Ngành:</div><div class="val"><?php echo html_escape($industry); ?></div></div>
    </div>
    <div class="box">
      <div class="row"><div class="lbl">Ngày in:</div><div class="val"><?php echo date('Y-m-d H:i'); ?></div></div>
      <div class="row"><div class="lbl">Tổng ứng viên:</div><div class="val"><?php echo count($app_list); ?></div></div>
      <div class="row"><div class="lbl">Link:</div><div class="val small"><?php echo html_escape(admin_url('internship_management/internship_job_orders/applicants/'.$job_id)); ?></div></div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th class="nowrap" style="width:90px;">ID</th>
        <th>Họ tên</th>
        <th>Trường</th>
        <th style="width:140px;">Giới tính</th>
        <th style="width:190px;">Ngày ứng tuyển</th>
      </tr>
    </thead>
    <tbody>
    <?php if(empty($app_list)){ ?>
      <tr><td colspan="5" class="muted">Chưa có ứng viên.</td></tr>
    <?php } else { foreach($app_list as $r){ ?>
      <tr>
        <td class="nowrap"><?php echo html_escape(im_val($r,['id','application_id'],'')); ?></td>
        <td><?php echo html_escape(im_val($r,['name','full_name','applicant_name'],'-')); ?></td>
        <td><?php echo html_escape(im_val($r,['school','university','school_name'],'-')); ?></td>
        <td><?php echo html_escape(im_val($r,['gender','sex'],'—')); ?></td>
        <td class="nowrap"><?php echo html_escape(im_val($r,['date_applied','created_at','datecreated'],'')); ?></td>
      </tr>
    <?php } } ?>
    </tbody>
  </table>

  <div class="footer"><div>Trang 1/2 (VI)</div><div>* Nội bộ IFK</div></div>
</div>

<!-- PAGE 2 (JA) -->
<div class="page">
  <div class="header">
    <div class="logo"><?php im_print_logo(); ?></div>
    <div class="title">
      <div class="t1">応募者一覧</div>
      <div class="t2">IFK Internship Program - Applicants List (JA)</div>
    </div>
    <div class="qr"><?php if(!empty($qr_src)){ ?><img src="<?php echo html_escape($qr_src); ?>" alt="QR"><?php } ?></div>
  </div>

  <div class="meta">
    <div class="box">
      <div class="row"><div class="lbl">求人番号:</div><div class="val">#<?php echo $job_id; ?></div></div>
      <div class="row"><div class="lbl">会社名:</div><div class="val"><?php echo html_escape($job['company_name_jp'] ?? $company); ?></div></div>
      <div class="row"><div class="lbl">住所:</div><div class="val"><?php echo html_escape($job['address_jp'] ?? $address); ?></div></div>
      <div class="row"><div class="lbl">分野:</div><div class="val"><?php echo html_escape($job['field_jp'] ?? $field); ?></div></div>
      <div class="row"><div class="lbl">職種/業種:</div><div class="val"><?php echo html_escape($job['industry_jp'] ?? $industry); ?></div></div>
    </div>
    <div class="box">
      <div class="row"><div class="lbl">印刷日時:</div><div class="val"><?php echo date('Y-m-d H:i'); ?></div></div>
      <div class="row"><div class="lbl">応募者数:</div><div class="val"><?php echo count($app_list); ?></div></div>
      <div class="row"><div class="lbl">リンク:</div><div class="val small"><?php echo html_escape(admin_url('internship_management/internship_job_orders/applicants/'.$job_id)); ?></div></div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th class="nowrap" style="width:90px;">ID</th>
        <th>氏名</th>
        <th>学校</th>
        <th style="width:140px;">性別</th>
        <th style="width:190px;">応募日</th>
      </tr>
    </thead>
    <tbody>
    <?php if(empty($app_list)){ ?>
      <tr><td colspan="5" class="muted">応募者なし。</td></tr>
    <?php } else { foreach($app_list as $r){ ?>
      <tr>
        <td class="nowrap"><?php echo html_escape(im_val($r,['id','application_id'],'')); ?></td>
        <td><?php echo html_escape(im_val($r,['name','full_name','applicant_name'],'-')); ?></td>
        <td><?php echo html_escape(im_val($r,['school','university','school_name'],'-')); ?></td>
        <td><?php echo html_escape(im_val($r,['gender','sex'],'—')); ?></td>
        <td class="nowrap"><?php echo html_escape(im_val($r,['date_applied','created_at','datecreated'],'')); ?></td>
      </tr>
    <?php } } ?>
    </tbody>
  </table>

  <div class="footer"><div>ページ 2/2 (JA)</div><div>* IFK 内部資料</div></div>
</div>

</body>
</html>