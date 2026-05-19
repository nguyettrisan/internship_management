<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$student_id = (int)($student->id ?? 0);

function im_val($o,$keys,$d=''){
  foreach($keys as $k){
    if(is_object($o)&&isset($o->$k)&&$o->$k!==''&&$o->$k!==null) return $o->$k;
    if(is_array($o)&&isset($o[$k])&&$o[$k]!==''&&$o[$k]!==null) return $o[$k];
  }
  return $d;
}

// Quick stats
$cnt_app=0; $cnt_notes=0; $cnt_docs=0; $cnt_logs=0;
if ($CI->db->table_exists(db_prefix().'internship_applications')) {
  $CI->db->where('student_id',$student_id);
  $cnt_app = (int)$CI->db->count_all_results(db_prefix().'internship_applications');
}
foreach([db_prefix().'internship_notes',db_prefix().'internship_student_notes',db_prefix().'internship_processing_notes'] as $t){
  if($CI->db->table_exists($t)){ $CI->db->where('student_id',$student_id); $cnt_notes=(int)$CI->db->count_all_results($t); break; }
}
foreach([db_prefix().'internship_documents',db_prefix().'internship_student_documents',db_prefix().'internship_files'] as $t){
  if($CI->db->table_exists($t)){ $CI->db->where('student_id',$student_id); $cnt_docs=(int)$CI->db->count_all_results($t); break; }
}
foreach([db_prefix().'internship_student_logs',db_prefix().'internship_audit_logs'] as $t){
  if($CI->db->table_exists($t)){ $CI->db->where('student_id',$student_id); $cnt_logs=(int)$CI->db->count_all_results($t); break; }
}
?>

<div class="row">
  <div class="col-md-12">
    <div class="panel_s">
      <div class="panel-body">
        <div class="tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-3">
          <h4 class="tw-font-semibold tw-m-0"><i class="fa fa-id-card-o"></i> <?php echo _l('Tổng quan hồ sơ'); ?></h4>
          <div class="tw-flex tw-gap-2 tw-flex-wrap">
            <span class="label label-default"><?php echo _l('Ứ111111111111ng tuyển'); ?>: <b><?php echo $cnt_app; ?></b></span>
            <span class="label label-default"><?php echo _l('Ghi chú'); ?>: <b><?php echo $cnt_notes; ?></b></span>
            <span class="label label-default"><?php echo _l('Tài liệu'); ?>: <b><?php echo $cnt_docs; ?></b></span>
            <span class="label label-default"><?php echo _l('Nhật ký'); ?>: <b><?php echo $cnt_logs; ?></b></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="panel_s">
      <div class="panel-body">
        <h4 class="tw-font-semibold tw-mb-4"><i class="fa fa-user"></i> <?php echo _l('Thông tin cá nhân'); ?></h4>
        <table class="table table-bordered">
          <tr><th style="width:200px;"><?php echo _l('Họ tên'); ?></th><td><?php echo html_escape(im_val($student,['name','full_name'],'')); ?></td></tr>
          <tr><th><?php echo _l('Email'); ?></th><td><?php echo html_escape(im_val($student,['email'],'')); ?></td></tr>
          <tr><th><?php echo _l('SĐT sinh viên'); ?></th><td><?php echo html_escape(im_val($student,['phone','phonenumber','mobile'],'')); ?></td></tr>
          <tr><th><?php echo _l('SĐT phụ huynh'); ?></th><td><?php echo html_escape(im_val($student,['parent_phone','guardian_phone'],'')); ?></td></tr>
          <tr><th><?php echo _l('Giới tính'); ?></th><td><?php echo html_escape(im_val($student,['gender'],'')); ?></td></tr>
          <tr><th><?php echo _l('Ngày sinh'); ?></th><td><?php echo html_escape(im_val($student,['birthday','dob'],'')); ?></td></tr>
          <tr><th><?php echo _l('CMND/CCCD'); ?></th><td><?php echo html_escape(im_val($student,['identity_no','id_number','cccd'],'')); ?></td></tr>
          <tr><th><?php echo _l('Địa chỉ'); ?></th><td><?php echo html_escape(im_val($student,['address','current_address'],'')); ?></td></tr>
          <tr><th><?php echo _l('Ngày tạo hồ sơ'); ?></th><td><?php $c=im_val($student,['created_at','datecreated'],null); echo $c ? _dt($c) : '-'; ?></td></tr>
          <tr><th><?php echo _l('Ghi chú nội bộ'); ?></th><td><?php echo nl2br(html_escape(im_val($student,['internal_note','note','notes'],''))); ?></td></tr>
        </table>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="panel_s">
      <div class="panel-body">
        <h4 class="tw-font-semibold tw-mb-4"><i class="fa fa-graduation-cap"></i> <?php echo _l('Học tập & thực tập'); ?></h4>
        <table class="table table-bordered">
          <tr><th style="width:200px;"><?php echo _l('Trường'); ?></th><td><?php echo html_escape(im_val($student,['school','university'],'')); ?></td></tr>
          <tr><th><?php echo _l('Ngành / Chuyên ngành'); ?></th><td><?php echo html_escape(im_val($student,['major','department'],'')); ?></td></tr>
          <tr><th><?php echo _l('JLPT'); ?></th><td><?php echo html_escape(im_val($student,['jlpt','japanese_level'],'')); ?></td></tr>
          <tr><th><?php echo _l('Tiếng Anh'); ?></th><td><?php echo html_escape(im_val($student,['english_level'],'')); ?></td></tr>
          <tr><th><?php echo _l('Kỹ năng'); ?></th><td><?php echo html_escape(im_val($student,['skills'],'')); ?></td></tr>
          <tr><th><?php echo _l('Kinh nghiệm'); ?></th><td><?php echo nl2br(html_escape(im_val($student,['experience'],''))); ?></td></tr>
          <tr><th><?php echo _l('Mong muốn'); ?></th><td><?php echo nl2br(html_escape(im_val($student,['expectation'],''))); ?></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>
