<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
$tab = (string)$this->input->get('tab');
$sub = (string)$this->input->get('sub');
if ($tab === '') $tab = 'general';
if ($sub === '') $sub = 'mail';

function im_h($v){ return html_escape($v ?? ''); }
?>

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

/* Page */
#wrapper .content{ background:var(--ifk-bg); }
.panel_s>.panel-body{
  border-radius:18px;
}
h4.bold{
  color:var(--ifk-navy);
  font-weight:900;
  letter-spacing:.2px;
}
h4.bold i{ color:var(--ifk-blue); }

/* Tabs (main) */
.im-tabs .nav-tabs{
  border-bottom:1px solid var(--ifk-border);
  margin-bottom:16px;
}
.im-tabs .nav-tabs>li>a{
  border:0 !important;
  background:transparent !important;
  color:var(--ifk-muted);
  font-weight:900;
  padding:10px 14px;
  border-radius:12px 12px 0 0;
  margin-right:8px;
}
.im-tabs .nav-tabs>li>a i{
  color:var(--ifk-blue);
  margin-right:6px;
}
.im-tabs .nav-tabs>li.active>a,
.im-tabs .nav-tabs>li.active>a:focus,
.im-tabs .nav-tabs>li.active>a:hover{
  color:var(--ifk-navy) !important;
  background:rgba(0,166,220,.10) !important;
  border-bottom:2px solid var(--ifk-blue) !important;
}

/* Subtabs */
.im-subtabs{ margin-top:10px; margin-bottom:16px; }
.im-subtabs .btn{
  border-radius:999px;
  font-weight:900;
  padding:8px 12px;
  border:1px solid rgba(0,166,220,.25);
  background:#fff;
  color:var(--ifk-navy);
  margin-right:8px;
  transition:all .12s ease;
}
.im-subtabs .btn:hover{
  border-color:rgba(0,166,220,.55);
  box-shadow:0 10px 20px rgba(0,50,90,.08);
}
.im-subtabs .btn.btn-primary,
.im-subtabs .btn.active{
  background:var(--ifk-navy) !important;
  border-color:var(--ifk-navy) !important;
  color:#fff !important;
}

/* Card */
.im-card{
  background:var(--ifk-card);
  border:1px solid var(--ifk-border);
  border-radius:var(--ifk-radius);
  padding:16px;
  margin-bottom:16px;
  box-shadow:var(--ifk-shadow);
  position:relative;
  overflow:hidden;
}
.im-card:before{
  content:"";
  position:absolute;
  left:0; top:0; bottom:0;
  width:4px;
  background:linear-gradient(180deg,var(--ifk-green),var(--ifk-blue));
}
.im-card h5{
  margin:0 0 10px;
  font-weight:1000;
  color:var(--ifk-navy);
  display:flex;
  align-items:center;
  gap:8px;
}
.im-muted{ color:var(--ifk-muted) !important; }

/* Right actions row */
.im-right{
  display:flex;
  gap:10px;
  justify-content:flex-end;
  align-items:center;
  flex-wrap:wrap;
}
.im-right .btn{
  border-radius:14px;
  font-weight:900;
  border:0;
  padding:9px 14px;
}
.im-right .btn-primary{ background:var(--ifk-navy) !important; }
.im-right .btn-info{ background:var(--ifk-blue) !important; }
.im-right .btn-success{ background:var(--ifk-green) !important; color:#0b1a08 !important; }

/* Forms */
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

/* Table */
.im-table td,.im-table th{ vertical-align:middle !important; }
.table>thead>tr>th{
  color:var(--ifk-navy);
  font-weight:1000;
  border-bottom:1px solid var(--ifk-border) !important;
}
.table>tbody>tr>td{
  border-top:1px solid rgba(230,238,246,.9) !important;
}
.table-hover>tbody>tr:hover{
  background:rgba(0,166,220,.06);
}

/* Badge status */
.im-badge{
  padding:4px 10px;
  border-radius:999px;
  font-weight:1000;
  font-size:12px;
  display:inline-flex;
  align-items:center;
  gap:6px;
}
.im-badge:before{
  content:"";
  width:8px; height:8px;
  border-radius:999px;
  background:currentColor;
  opacity:.9;
}
.im-badge-sent{
  background:rgba(150,188,23,.16);
  color:#2f6f09;
}
.im-badge-failed{
  background:rgba(220,0,0,.10);
  color:#b42318;
}

/* Small polish */
hr{ border-top:1px solid var(--ifk-border) !important; }
.alert{
  border-radius:14px;
  border:1px solid var(--ifk-border);
}
</style>

<div id="wrapper">
<div class="content">
    <div class="panel_s">
    <div class="panel-body">
        <h4 class="bold"><i class="fa fa-cog"></i> Cài đặt Internship Japan</h4>
        <hr>

        <div class="im-tabs">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="<?= $tab==='general'?'active':'' ?>">
                    <a href="<?= admin_url('internship_management/internship_settings?tab=general'); ?>">
                        <i class="fa fa-sliders"></i> Cấu hình
                    </a>
                </li>
                <li role="presentation" class="<?= $tab==='email'?'active':'' ?>">
                    <a href="<?= admin_url('internship_management/internship_settings?tab=email&sub=mail'); ?>">
                        <i class="fa fa-envelope"></i> Email
                    </a>
                </li>
                <li role="presentation" class="<?= $tab==='audit'?'active':'' ?>">
                    <a href="<?= admin_url('internship_management/internship_settings?tab=audit'); ?>">
                        <i class="fa fa-history"></i> Nhật ký thao tác
                    </a>
                </li>
            </ul>
        </div>

        <?php
            if($tab==='general'){
                $this->load->view('internship_management/settings/tabs/tab_general', ['tab'=>$tab,'sub'=>$sub]);
            } elseif($tab==='email') {
                $this->load->view('internship_management/settings/tabs/tab_email', ['tab'=>$tab,'sub'=>$sub]);
            } else {
                $this->load->view('internship_management/settings/tabs/tab_audit', ['tab'=>$tab,'sub'=>$sub]);
            }
        ?>

    </div>
    </div>
</div>
</div>

<?php init_tail(); ?>
