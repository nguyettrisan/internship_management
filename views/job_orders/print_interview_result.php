<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Interview Result #<?php echo (int)($job['id'] ?? 0); ?></title>

<style>

/* =====================================================
   IFK PORTRAIT PRINT TEMPLATE
   A4 Portrait - Stable Version
   ===================================================== */

@page {
  size: A4 portrait;
  margin: 18mm;
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
  padding-bottom:10px;
  margin-bottom:18px;
}

.logo{ width:220px; }
.logo img{ max-height:60px; max-width:220px; }

.title{
  text-align:center;
  flex:1;
}

.t1{
  font-size:18px;
  font-weight:700;
  color:#00325a;
  letter-spacing:.3px;
}

.t2{
  font-size:12px;
  color:#64748b;
  margin-top:4px;
}

.qr{
  width:150px;
  text-align:right;
}

.qr img{
  width:110px;
  height:110px;
  border:1px solid rgba(0,50,90,.25);
  padding:4px;
  border-radius:4px;
}

/* ===== META BOX ===== */

.meta{
  display:flex;
  gap:15px;
  margin:15px 0;
}

.box{
  flex:1;
  border:1px solid rgba(0,50,90,.20);
  border-radius:8px;
  padding:12px 14px;
  background:#ffffff;
}

.row{
  display:flex;
  margin:6px 0;
}

.lbl{
  width:140px;
  font-weight:600;
  color:#00325a;
}

.val{ flex:1; }

/* ===== RESULT BOX ===== */

.result-box{
  margin-top:24px;
  border:2px solid #00325a;
  border-radius:12px;
  padding:22px;
  text-align:center;
}

/* PASS / FAIL */

.pass{
  color:#96bc17;
  font-size:22px;
  font-weight:700;
  letter-spacing:.5px;
}

.fail{
  color:#b91c1c;
  font-size:22px;
  font-weight:700;
  letter-spacing:.5px;
}

/* ===== FOOTER ===== */

.footer{
  margin-top:24px;
  display:flex;
  justify-content:space-between;
  font-size:11px;
  color:#64748b;
  border-top:1px solid rgba(0,50,90,.20);
  padding-top:8px;
}

</style>
</head>

<body>

<?php
function im_print_logo(){
  if (function_exists('get_company_logo')) { echo get_company_logo(); return; }
  if (function_exists('get_option')) {
    $logo = get_option('company_logo');
    if (!empty($logo)) echo '<img src="'.base_url('uploads/company_logo/'.$logo).'" alt="Logo">';
  }
}

$job_id   = (int)($job['id'] ?? 0);
$company  = $job['company_name'] ?? '';
$company  = $job['company_name'] ?? ($job['company_name_vi'] ?? ($job['company'] ?? ''));
$address  = $job['address_vi'] ?? ($job['address'] ?? ($job['company_address'] ?? ''));
$field    = $job['field_vi'] ?? ($job['field'] ?? ($job['industry'] ?? ''));
$industry = $job['industry_jp'] ?? ($job['industry'] ?? '');


$app_name   = $app['full_name'] ?? ($app['name'] ?? '');
$app_school = $app['school_name'] ?? ($app['school'] ?? '');
$app_gender = $app['gender'] ?? ($app['sex'] ?? '');
$app_date   = $app['apply_date'] ?? ($app['datecreated'] ?? ($app['created_at'] ?? ''));
$result     = $app['interview_result'] ?? ($app['result'] ?? '');

$print_time = date('Y-m-d H:i');
$link = admin_url('internship_management/internship_job_orders/applicants/'.$job_id);
?>

<!-- ================= VI PAGE ================= -->
<div class="page">

<div class="header">
  <div class="logo"><?php im_print_logo(); ?></div>
  <div class="title">
    <div class="t1">THÔNG BÁO KẾT QUẢ PHỎNG VẤN</div>
    <div class="t2">IFK Internship Program</div>
  </div>
  <div class="qr">
    <?php if(!empty($qr_src)){ ?><img src="<?php echo html_escape($qr_src); ?>"><?php } ?>
  </div>
</div>

<div class="meta">
  <div class="box">
    <div class="row"><div class="lbl">Mã đơn:</div><div class="val">#<?php echo $job_id; ?></div></div>
    <div class="row"><div class="lbl">Tên công ty:</div><div class="val"><?php echo html_escape($company); ?></div></div>
    <div class="row"><div class="lbl">Địa chỉ:</div><div class="val"><?php echo html_escape($address); ?></div></div>
    <div class="row"><div class="lbl">Lĩnh vực:</div><div class="val"><?php echo html_escape($field); ?></div></div>
  </div>

  <div class="box">
    <div class="row"><div class="lbl">Ứng viên:</div><div class="val"><?php echo html_escape($app_name); ?></div></div>
    <div class="row"><div class="lbl">Trường:</div><div class="val"><?php echo html_escape($app_school); ?></div></div>
    <div class="row"><div class="lbl">Giới tính:</div><div class="val"><?php echo html_escape($app_gender); ?></div></div>
    <div class="row"><div class="lbl">Ngày ứng tuyển:</div><div class="val"><?php echo html_escape($app_date); ?></div></div>
  </div>
</div>

<div class="result-box">
  <strong>KẾT QUẢ:</strong><br><br>

  <?php if($result == 'pass'){ ?>
    <div class="pass">ỨNG VIÊN ĐẠT PHỎNG VẤN</div>
    <p>Chúc mừng ứng viên đã vượt qua vòng phỏng vấn. Bộ phận nhân sự sẽ liên hệ để hướng dẫn bước tiếp theo.</p>
  <?php } else { ?>
    <div class="fail">ỨNG VIÊN KHÔNG ĐẠT PHỎNG VẤN</div>
    <p>Rất tiếc ứng viên chưa đáp ứng yêu cầu của vị trí trong vòng phỏng vấn này.</p>
  <?php } ?>

  <br>Ngày in: <?php echo $print_time; ?>
</div>

<div class="footer">
  <div>© IFK SolarTech</div>
  <div>Trang 1/2 (VI)</div>
</div>

</div>

<!-- ================= JA PAGE ================= -->
<div class="page">

<div class="header">
  <div class="logo"><?php im_print_logo(); ?></div>
  <div class="title">
    <div class="t1">面接結果通知書</div>
    <div class="t2">IFK Internship Program</div>
  </div>
  <div class="qr">
    <?php if(!empty($qr_src)){ ?><img src="<?php echo html_escape($qr_src); ?>"><?php } ?>
  </div>
</div>

<div class="meta">
  <div class="box">
    <div class="row"><div class="lbl">求人番号:</div><div class="val">#<?php echo $job_id; ?></div></div>
    <div class="row"><div class="lbl">会社名:</div><div class="val"><?php echo html_escape($company); ?></div></div>
    <div class="row"><div class="lbl">所在地:</div><div class="val"><?php echo html_escape($address); ?></div></div>
    <div class="row"><div class="lbl">業種:</div><div class="val"><?php echo html_escape($industry); ?></div></div>
  </div>

  <div class="box">
    <div class="row"><div class="lbl">応募者氏名:</div><div class="val"><?php echo html_escape($app_name); ?></div></div>
    <div class="row"><div class="lbl">学校名:</div><div class="val"><?php echo html_escape($app_school); ?></div></div>
  </div>
</div>

<div class="result-box">
  <strong>結果:</strong><br><br>

  <?php if($result == 'pass'){ ?>
    <div class="pass">面接合格</div>
    <p>この度は面接に合格されました。今後の手続きについて改めてご連絡いたします。</p>
  <?php } else { ?>
    <div class="fail">面接不合格</div>
    <p>誠に残念ながら、今回はご期待に添えない結果となりました。</p>
  <?php } ?>

  <br>印刷日時: <?php echo $print_time; ?>
</div>

<div class="footer">
  <div>© IFK SolarTech</div>
  <div>ページ 2/2 (JA)</div>
</div>

</div>

</body>
</html>