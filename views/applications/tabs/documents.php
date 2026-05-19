<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$student_id = (int)($student->id ?? 0);

function im_table_first_exists($CI, $candidates){
  foreach($candidates as $t){ if($CI->db->table_exists($t)) return $t; }
  return null;
}

$docs_table = im_table_first_exists($CI, [
  db_prefix().'internship_documents',
  db_prefix().'internship_student_documents',
  db_prefix().'internship_files',
]);

$docs = [];
if ($docs_table && $student_id>0) {
  $CI->db->where('student_id',$student_id);
  $CI->db->order_by('id','DESC');
  $docs = $CI->db->get($docs_table)->result_array();
}

// Upload folder fallback
$folder_docs = [];
$base_dir = FCPATH.'uploads'.DIRECTORY_SEPARATOR.'internship_documents'.DIRECTORY_SEPARATOR.$student_id.DIRECTORY_SEPARATOR;
if (is_dir($base_dir)) {
  $files = array_values(array_filter(scandir($base_dir), fn($f)=>!in_array($f,['.','..']) && is_file($base_dir.$f)));
  foreach($files as $f){
    $folder_docs[] = [
      'file_name'=>$f,
      'type'=>'uploaded',
      'created_at'=>date('Y-m-d H:i:s', filemtime($base_dir.$f)),
      'url'=>site_url('uploads/internship_documents/'.$student_id.'/'.$f),
    ];
  }
}

$upload_url = admin_url('internship_management/student_client/upload_document/'.$student_id);
?>

<div class="panel_s">
  <div class="panel-body">
    <div class="tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-2 tw-mb-4">
      <h4 class="tw-font-semibold tw-m-0"><i class="fa fa-folder-open-o"></i> <?php echo _l('Tài liệu'); ?></h4>
    </div>

    <form action="<?php echo $upload_url; ?>" method="post" enctype="multipart/form-data" class="tw-mb-6">
      <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label><?php echo _l('Loại tài liệu'); ?></label>
            <select name="doc_type" class="form-control selectpicker" data-live-search="true">
              <option value="CV">CV</option>
              <option value="Passport">Passport</option>
              <option value="Bằng cấp">Bằng cấp</option>
              <option value="Ảnh">Ảnh</option>
              <option value="Khác"><?php echo _l('Khác'); ?></option>
            </select>
          </div>
        </div>
        <div class="col-md-5">
          <div class="form-group">
            <label><?php echo _l('Chọn file'); ?></label>
            <input type="file" name="file" class="form-control" required>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label>&nbsp;</label><br>
            <button class="btn btn-primary" type="submit"><i class="fa fa-upload"></i> <?php echo _l('Tải lên'); ?></button>
          </div>
        </div>
      </div>
    </form>

    <?php if (empty($docs) && empty($folder_docs)) { ?>
      <p class="text-muted"><?php echo _l('Chưa có tài liệu.'); ?></p>
    <?php } else { ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th style="width:160px;"><?php echo _l('Thời gian'); ?></th>
              <th style="width:200px;"><?php echo _l('Loại'); ?></th>
              <th><?php echo _l('Tệp'); ?></th>
              <th style="width:180px;"><?php echo _l('Nhân sự'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($docs as $d){
              $file = $d['file'] ?? ($d['file_name'] ?? ($d['attachment'] ?? ''));
              if (!$file) continue;
              $url = site_url('uploads/internship_documents/'.$student_id.'/'.$file);
              $type = $d['doc_type'] ?? ($d['type'] ?? 'Document');
              $created = $d['created_at'] ?? ($d['datecreated'] ?? '');
              $staff_id = (int)($d['staff_id'] ?? ($d['addedfrom'] ?? 0));
              $staff_name = $d['staff_name'] ?? ($staff_id ? get_staff_full_name($staff_id) : '-');
            ?>
              <tr>
                <td><?php echo $created ? _dt($created) : ''; ?></td>
                <td><?php echo html_escape($type); ?></td>
                <td><a href="<?php echo $url; ?>" target="_blank" rel="noreferrer"><?php echo html_escape($file); ?></a></td>
                <td><?php echo html_escape($staff_name ?: '-'); ?></td>
              </tr>
            <?php } ?>
            <?php foreach($folder_docs as $d){ ?>
              <tr>
                <td><?php echo _dt($d['created_at']); ?></td>
                <td><?php echo _l('Tệp hồ sơ'); ?></td>
                <td><a href="<?php echo $d['url']; ?>" target="_blank" rel="noreferrer"><?php echo html_escape($d['file_name']); ?></a></td>
                <td>-</td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <?php if ($docs_table) { ?>
        <p class="text-muted"><?php echo _l('Nguồn dữ liệu'); ?>: <?php echo html_escape($docs_table); ?></p>
      <?php } ?>
    <?php } ?>
  </div>
</div>
