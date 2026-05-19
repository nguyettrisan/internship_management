<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_escape($title ?? 'Quên mật khẩu'); ?></title>
    <link rel="icon" type="image/png" href="<?php echo base_url('uploads/company/favicon.png'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>">
    <style>
        :root{
            --ifk-navy:#0b2e59;
            --ifk-bg:#eef3f8;
            --ifk-line:#dde7f1;
            --ifk-muted:#6f8094;
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            min-height:100vh;
            font-family:Arial,Helvetica,sans-serif;
            background:var(--ifk-bg);
            color:#18324d;
        }
        .page{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:24px;
        }
        .cardx{
            width:100%;
            max-width:520px;
            background:#fff;
            border:1px solid var(--ifk-line);
            border-radius:24px;
            box-shadow:0 24px 60px rgba(11,46,89,.10);
            overflow:hidden;
        }
        .head{
            padding:26px 28px 16px;
            border-bottom:1px solid var(--ifk-line);
            background:linear-gradient(135deg, rgba(11,46,89,.03), rgba(16,166,222,.07));
        }
        .head h1{
            margin:0;
            font-size:24px;
            color:var(--ifk-navy);
            font-weight:800;
        }
        .head p{
            margin:6px 0 0;
            color:var(--ifk-muted);
        }
        .body{
            padding:24px 28px 28px;
        }
        .form-group label{
            font-weight:700;
            color:var(--ifk-navy);
            margin-bottom:7px;
        }
        .form-control{
            height:48px;
            border-radius:14px;
            border:1px solid var(--ifk-line);
            box-shadow:none;
        }
        .form-control:focus{
            border-color:#10a6de;
            box-shadow:0 0 0 3px rgba(16,166,222,.12);
        }
        .captcha-row{
            display:flex;
            gap:10px;
            align-items:center;
        }
        .captcha-box{
            flex:1;
            min-height:48px;
            border:1px solid var(--ifk-line);
            border-radius:14px;
            background:#f8fbfe;
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
        }
        .captcha-box img{
            display:block;
            width:100%;
            height:48px;
            object-fit:contain;
        }
        .btn-main{
            width:100%;
            height:50px;
            border:none;
            border-radius:14px;
            background:linear-gradient(135deg,var(--ifk-navy),#17457f);
            color:#fff;
            font-size:15px;
            font-weight:800;
        }
        .btn-refresh{
            height:48px;
            border-radius:14px;
            border:1px solid var(--ifk-line);
            background:#fff;
            color:var(--ifk-navy);
            font-weight:700;
            padding:0 14px;
        }
        .back{
            display:inline-block;
            margin-top:14px;
            color:var(--ifk-navy);
            font-weight:700;
            text-decoration:none;
        }
        @media (max-width:520px){
            .head,.body{padding-left:18px;padding-right:18px}
            .captcha-row{flex-direction:column;align-items:stretch}
        }
    </style>
</head>
<body>
<div class="page">
    <div class="cardx">
        <div class="head">
            <h1>Quên mật khẩu</h1>
            <p>Nhập tài khoản và email đã đăng ký để tạo mật khẩu mới.</p>
        </div>

        <div class="body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo html_escape($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo html_escape($success); ?></div>
            <?php endif; ?>

            <form method="post" action="<?php echo site_url('school_portal/forgot_password'); ?>" autocomplete="off">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                <div class="form-group">
                    <label>Tài khoản</label>
                    <input type="text" name="username" class="form-control" value="<?php echo html_escape(set_value('username')); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email đã đăng ký</label>
                    <input type="email" name="email" class="form-control" value="<?php echo html_escape(set_value('email')); ?>" required>
                </div>

                <div class="form-group">
                    <label>Mật khẩu mới</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Nhập lại mật khẩu mới</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Mã xác nhận</label>
                    <div class="captcha-row">
                        <div class="captcha-box">
                            <img id="ifk-captcha-forgot" src="<?php echo site_url('school_portal/captcha'); ?>?t=<?php echo time(); ?>" alt="Captcha">
                        </div>
                        <button type="button" class="btn-refresh" onclick="refreshForgotCaptcha()">Làm mới</button>
                    </div>
                </div>

                <div class="form-group">
                    <input type="text" name="captcha" class="form-control" placeholder="Nhập mã xác nhận" required>
                </div>

                <button type="submit" class="btn-main">Đặt lại mật khẩu</button>
            </form>

            <a class="back" href="<?php echo site_url('school_portal/login'); ?>">← Quay lại đăng nhập</a>
        </div>
    </div>
</div>

<script>
function refreshForgotCaptcha() {
    var img = document.getElementById('ifk-captcha-forgot');
    img.src = "<?php echo site_url('school_portal/captcha'); ?>?t=" + new Date().getTime();
}
</script>
</body>
</html>
