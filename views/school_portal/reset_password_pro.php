<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$title = (string)($title ?? 'Đặt lại mật khẩu');
$error = (string)($error ?? '');
$success = (string)($success ?? '');
$favicon = base_url('uploads/company/favicon.png');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_escape($title); ?></title>
    <link rel="icon" type="image/png" href="<?php echo $favicon; ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>">
    <style>
        body{margin:0;background:#eef3f8;font-family:Arial,Helvetica,sans-serif;color:#18324d}
        .wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:22px}
        .cardx{width:100%;max-width:520px;background:#fff;border:1px solid #e2eaf2;border-radius:22px;box-shadow:0 16px 40px rgba(11,46,89,.10);padding:28px}
        .title{margin:0;color:#0b2e59;font-size:28px;font-weight:800}
        .sub{margin:8px 0 18px;color:#6b7b8f}
        .form-control{height:46px;border-radius:12px}
        .btnx{width:100%;border:none;border-radius:14px;background:#0b2e59;color:#fff;padding:12px 16px;font-weight:800}
        .meta{margin-top:14px;text-align:center}
        .meta a{color:#0b2e59;font-weight:700}
    </style>
</head>
<body>
<div class="wrap">
    <div class="cardx">
        <h1 class="title">Đặt lại mật khẩu</h1>
        <p class="sub">Nhập mật khẩu mới cho tài khoản trường.</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?php echo html_escape($error); ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success"><?php echo html_escape($success); ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            <div class="form-group">
                <label>Mật khẩu mới</label>
                <input type="password" name="new_password" class="form-control" required minlength="6">
            </div>
            <div class="form-group">
                <label>Nhập lại mật khẩu mới</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btnx">Cập nhật mật khẩu</button>
        </form>

        <div class="meta">
            <a href="<?php echo site_url('school_portal/login'); ?>">Quay lại đăng nhập</a>
        </div>
    </div>
</div>
</body>
</html>
