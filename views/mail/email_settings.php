<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
    <div class="content">

        <div class="panel_s">
            <div class="panel-body">

                <h4 class="tw-mt-0 tw-mb-3">
                    <i class="fa fa-cog"></i> Cài Đặt Mail Internship
                </h4>
                <hr>

                <?php echo form_open(admin_url('internship_management/internship_mail/save_settings')); ?>

                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>SMTP Host</label>
                            <input type="text" class="form-control" name="ifk_smtp_host"
                                   value="<?php echo get_option('ifk_smtp_host'); ?>" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>SMTP Port</label>
                            <input type="number" class="form-control" name="ifk_smtp_port"
                                   value="<?php echo get_option('ifk_smtp_port'); ?>" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>SMTP Secure</label>
                            <select name="ifk_smtp_secure" class="form-control">
                                <option value="" <?= get_option('ifk_smtp_secure') == '' ? 'selected' : '' ?>>None</option>
                                <option value="tls" <?= get_option('ifk_smtp_secure') == 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= get_option('ifk_smtp_secure') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                            </select>
                        </div>
                    </div>

                </div>


                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>SMTP User</label>
                            <input type="text" class="form-control" name="ifk_smtp_user"
                                   value="<?php echo get_option('ifk_smtp_user'); ?>" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>SMTP Password</label>
                            <input type="password" class="form-control" name="ifk_smtp_pass"
                                   value="<?php echo get_option('ifk_smtp_pass'); ?>" required>
                        </div>
                    </div>

                </div>


                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tên người gửi (Sender Name)</label>
                            <input type="text" class="form-control" name="ifk_sender_name"
                                   value="<?php echo get_option('ifk_sender_name'); ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email người gửi (Sender Email)</label>
                            <input type="email" class="form-control" name="ifk_sender_email"
                                   value="<?php echo get_option('ifk_sender_email'); ?>">
                        </div>
                    </div>

                </div>

                <div class="text-right">
                    <button class="btn btn-primary">
                        <i class="fa fa-save"></i> Lưu Cài Đặt
                    </button>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
</body>
</html>