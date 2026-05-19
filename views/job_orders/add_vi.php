<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
.ifk-header {
    text-align:center;
    margin-bottom:25px;
}
.ifk-header img {
    max-width:160px;
    margin-bottom:8px;
}
.ifk-title {
    font-size:22px;
    font-weight:bold;
    color:#0b4da2;
}
.section-title {
    font-size:17px;
    font-weight:bold;
    margin-top:25px;
    padding-bottom:6px;
    border-bottom:2px solid #e5e5e5;
}
</style>

<div id="wrapper">
<div class="content">

    <div class="panel_s">
        <div class="panel-body">

            <div class="ifk-header">
                <img src="https://ifkgroup.net/ifk-edu-logo.png">
                <div class="ifk-title">Form Nhập Đơn Tuyển – Tiếng Việt</div>
            </div>

            <hr>

            <?= form_open(admin_url('internship_management/internship_job_orders/add_vi')); ?>

                <?php $this->load->view('internship_management/job_orders/partial_form_vi'); ?>

                <div class="text-right mtop30">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Lưu Đơn Tuyển
                    </button>
                </div>

            <?= form_close(); ?>

        </div>
    </div>

</div>
</div>

<?php init_tail(); ?>