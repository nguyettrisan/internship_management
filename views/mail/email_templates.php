<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><i class="fa fa-file-text"></i> Mẫu Email Internship</h4>
            <hr class="hr-panel-heading" />

            <?php echo form_open(admin_url('internship_management/internship_mail/save_template')); ?>
            <input type="hidden" name="id" id="tpl_id" value="0">

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Tên mẫu</label>
                  <input type="text" class="form-control" name="name" id="tpl_name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Mã (code)</label>
                  <input type="text" class="form-control" name="code" id="tpl_code" placeholder="vd: PRE_DEPARTURE">
                </div>
              </div>
            </div>

            <div class="form-group">
              <label>Tiêu đề email (Subject)</label>
              <input type="text" class="form-control" name="subject" id="tpl_subject">
            </div>

            <div class="form-group">
              <label>Nội dung email (HTML)</label>
              <textarea class="form-control" rows="12" name="content" id="tpl_content" style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;"></textarea>
              <p class="text-muted" style="margin-top:6px;">Tokens hỗ trợ: {{student.*}}, {{job.*}}, {{today}}, {{now}}</p>
            </div>

            <div class="checkbox checkbox-primary">
              <input type="checkbox" name="is_active" id="tpl_active" value="1" checked>
              <label for="tpl_active">Kích hoạt mẫu</label>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Lưu mẫu</button>
            <button type="button" class="btn btn-default" id="btnResetTpl">Làm mới</button>
            <?php echo form_close(); ?>

            <hr />

            <h4>Danh sách mẫu Email</h4>
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th style="width:70px;">ID</th>
                    <th>Tên mẫu</th>
                    <th style="width:160px;">Mã</th>
                    <th>Tiêu đề</th>
                    <th style="width:110px;">Kích hoạt</th>
                    <th style="width:160px;">Ngày tạo</th>
                    <th style="width:120px;">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach (($templates ?? []) as $t) { ?>
                    <tr>
                      <td><?php echo (int)$t->id; ?></td>
                      <td><?php echo htmlspecialchars((string)$t->name, ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars((string)$t->code, ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars((string)$t->subject, ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <?php if ((int)$t->is_active === 1) { ?>
                          <span class="label label-success">Bật</span>
                        <?php } else { ?>
                          <span class="label label-default">Tắt</span>
                        <?php } ?>
                      </td>
                      <td><?php echo htmlspecialchars((string)$t->created_at, ENT_QUOTES, 'UTF-8'); ?></td>
                      <td>
                        <button type="button" class="btn btn-xs btn-info btnEditTpl"
                          data-id="<?php echo (int)$t->id; ?>"
                          data-name="<?php echo htmlspecialchars((string)$t->name, ENT_QUOTES, 'UTF-8'); ?>"
                          data-code="<?php echo htmlspecialchars((string)$t->code, ENT_QUOTES, 'UTF-8'); ?>"
                          data-subject="<?php echo htmlspecialchars((string)$t->subject, ENT_QUOTES, 'UTF-8'); ?>"
                          data-content="<?php echo htmlspecialchars((string)$t->content, ENT_QUOTES, 'UTF-8'); ?>"
                          data-active="<?php echo (int)$t->is_active; ?>"
                        ><i class="fa fa-pencil"></i></button>
                        <a class="btn btn-xs btn-danger" href="<?php echo admin_url('internship_management/internship_mail/delete_template/' . (int)$t->id); ?>" onclick="return confirm('Xoá mẫu này?');"><i class="fa fa-trash"></i></a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
  var btnReset = document.getElementById("btnResetTpl");
  function setVal(id, v){ var el=document.getElementById(id); if(el) el.value = v; }
  function setChecked(id, v){ var el=document.getElementById(id); if(el) el.checked = !!v; }

  if(btnReset){
    btnReset.addEventListener("click", function(){
      setVal("tpl_id", 0);
      setVal("tpl_name", "");
      setVal("tpl_code", "");
      setVal("tpl_subject", "");
      setVal("tpl_content", "");
      setChecked("tpl_active", true);
    });
  }

  document.querySelectorAll(".btnEditTpl").forEach(function(btn){
    btn.addEventListener("click", function(){
      setVal("tpl_id", btn.getAttribute("data-id") || 0);
      setVal("tpl_name", btn.getAttribute("data-name") || "");
      setVal("tpl_code", btn.getAttribute("data-code") || "");
      setVal("tpl_subject", btn.getAttribute("data-subject") || "");
      setVal("tpl_content", btn.getAttribute("data-content") || "");
      setChecked("tpl_active", parseInt(btn.getAttribute("data-active")||"1",10) === 1);
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  });
});
</script>

<?php init_tail(); ?>
