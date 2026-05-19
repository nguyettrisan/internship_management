<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$student_id = isset($student_id) ? (int)$student_id : 0;
$student    = isset($student) && is_array($student) ? $student : [];

$name  = $student['full_name'] ?? ($student['name'] ?? ($student['student_name'] ?? ''));
$email = $student['email'] ?? ($student['student_email'] ?? '');
$phone = $student['phone_student'] ?? ($student['phonenumber'] ?? ($student['phone'] ?? ($student['mobile'] ?? ($student['sdt_sinh_vien'] ?? ''))));

$avatar = $student['avatar'] ?? ($student['profile_image'] ?? ($student['image'] ?? ''));
$avatarUrl = '';

if (!empty($avatar)) {
    // If already an absolute URL, keep it. Otherwise, treat as filename stored in uploads/internship_avatar/
    if (preg_match('/^https?:\/\//i', $avatar)) {
        $avatarUrl = $avatar;
    } else {
        $avatarUrl = base_url('uploads/internship_avatar/' . ltrim($avatar, '/'));
    }
} else {
    $avatarUrl = base_url('modules/internship_management/assets/no-image.png');
}

$editUrl   = admin_url('internship_management/student_client/edit/'.$student_id);
$pushCrmUrl= admin_url('internship_management/student_client/push_crm/'.$student_id);
$delUrl    = admin_url('internship_management/student_client/delete/'.$student_id);
?>
<div class="panel_s">
  <div class="panel-body">
    <div class="row">
      <div class="col-md-8">
        <div class="tw-flex tw-items-center tw-gap-4">
          <div style="width:96px;height:96px;border-radius:16px;overflow:hidden;background:#f3f4f6;display:flex;align-items:center;justify-content:center;border:1px solid #e5e7eb;">
              <img src="<?= htmlspecialchars($avatarUrl); ?>" alt="avatar" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='<?= htmlspecialchars(base_url('modules/internship_management/assets/no-image.png')); ?>';">
          </div>

          <div>
            <h3 class="tw-m-0 tw-font-bold"><?= htmlspecialchars($name ?: ('Hồ sơ #'.$student_id)); ?></h3>
            <div class="text-muted" style="margin-top:6px;">
              <span><b>Mã hồ sơ:</b> <?= (int)$student_id; ?></span>
              <?php if (!empty($email)): ?> &nbsp;&nbsp; <span><b>Email:</b> <?= htmlspecialchars($email); ?></span><?php endif; ?>
              <?php if (!empty($phone)): ?> &nbsp;&nbsp; <span><b>SĐT:</b> <?= htmlspecialchars($phone); ?></span><?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4 text-right">
        <a class="btn btn-default" href="<?= $editUrl; ?>"><i class="fa fa-pencil"></i> Sửa hồ sơ</a>
        <a class="btn btn-info" href="<?= $pushCrmUrl; ?>"><i class="fa fa-cloud-upload"></i> Đẩy CRM</a>
        <a class="btn btn-danger _delete" href="<?= $delUrl; ?>"><i class="fa fa-trash"></i> Xóa</a>
      </div>
    </div>
  </div>
</div>
