<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">

           <div class="clearfix im-page-head">
  <h4 class="pull-left mtop5"><?php echo html_escape($title ?? 'Gửi Email Internship'); ?></h4>

  <div class="pull-right im-head-actions">
    <a href="javascript:void(0)" class="btn btn-default btn-sm im-back-btn" onclick="window.history.back();">
      <i class="fa fa-arrow-left"></i> Quay lại
    </a>
  
  </div>
</div>
            </div>
            <hr class="hr-panel-heading" />

            <!-- CSRF hidden for JS -->
            <input type="hidden"
                   id="im_csrf_name"
                   value="<?php echo html_escape($this->security->get_csrf_token_name()); ?>">
            <input type="hidden"
                   id="im_csrf_hash"
                   value="<?php echo html_escape($this->security->get_csrf_hash()); ?>">

            <!-- TABS -->
            <ul class="nav nav-tabs" role="tablist" style="margin-bottom:15px;">
              <li role="presentation" class="active">
                <a href="#tab_students" aria-controls="tab_students" role="tab" data-toggle="tab">
                  <i class="fa fa-user"></i> Gửi học sinh
                </a>
              </li>
              <li role="presentation">
                <a href="#tab_job" aria-controls="tab_job" role="tab" data-toggle="tab">
                  <i class="fa fa-users"></i> Gửi theo đơn tuyển
                </a>
              </li>
              
            </ul>

            <div class="tab-content">

              <!-- ================= TAB: STUDENTS ================= -->
              <div role="tabpanel" class="tab-pane active" id="tab_students">
                <div class="row im-grid">
                  <!-- LEFT -->
                  <div class="col-md-6">
                    <div class="im-card">
                      <div class="im-card-h">
                        <div class="im-title"><i class="fa fa-pencil"></i> Soạn email</div>
                        <div class="im-tip">An toàn: gợi ý bật <b>DRY RUN</b> trước</div>
                      </div>

                      <form method="post" action="<?php echo admin_url('internship_management/internship_mail/do_send_students'); ?>" id="imFormStudents" class="im-form">
                        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

                        <input type="hidden" name="mode" value="students">
                        <input type="hidden" name="confirm_send" id="confirm_send_students" value="">

                        <div class="form-group">
                          <label>Chọn sinh viên</label>
                          <select class="form-control" id="student_id" name="student_id">
                            <option value="">-- Chọn sinh viên --</option>
                            <?php if (!empty($students_quick)) : ?>
                              <?php foreach ($students_quick as $s) : ?>
                                <option value="<?php echo (int)$s['id']; ?>">
                                  <?php echo html_escape($s['full_name'] ?? ('#'.$s['id'])); ?>
                                  <?php if (!empty($s['email'])) echo ' - ' . html_escape($s['email']); ?>
                                </option>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          </select>
                          <p class="text-muted mtop5" style="margin-bottom:0;">
                            Nếu danh sách lớn: ô này tự chuyển sang Select2 AJAX để tìm nhanh.
                          </p>
                        </div>

                        <div class="row">
                          <div class="col-md-8">
                            <div class="form-group">
                              <label>Chọn mẫu (Template)</label>
                              <select class="form-control" id="tpl_id_students">
                                <option value="">-- Không dùng template --</option>
                                <?php if (!empty($templates)) : foreach ($templates as $t) : ?>
                                  <option value="<?php echo (int)$t->id; ?>">
                                    <?php echo html_escape($t->name ?? ('Template #'.$t->id)); ?>
                                  </option>
                                <?php endforeach; endif; ?>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label>Gửi test / DRY RUN</label>
                              <div class="checkbox checkbox-primary">
                                <input type="checkbox" id="dry_run_students" name="dry_run" value="1">
                                <label for="dry_run_students">DRY RUN (không gửi thật)</label>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="form-group">
                          <label>Nhập email thủ công (tùy chọn)</label>
                          <textarea class="form-control" rows="2" name="manual_emails" id="manual_emails_students"
                                    placeholder="vd: a@x.com, b@y.com hoặc mỗi dòng 1 email"></textarea>
                          <p class="text-muted mtop5" style="margin-bottom:0;">Tách bằng xuống dòng, dấu phẩy “,” hoặc chấm phẩy “;”.</p>
                        </div>

                        <div class="form-group">
                          <label>Subject</label>
                          <input type="text" class="form-control" name="subject" id="subject_students"
                                 placeholder="VD: Thông báo lịch phỏng vấn {{student.name}}">
                        </div>

                        <div class="form-group">
                          <label>Nội dung (HTML)</label>
                          <textarea class="form-control im-editor" rows="10" name="html" id="content_students"
                                    placeholder="Nhập nội dung email, có thể dùng token..."></textarea>
                          <p class="text-muted mtop5" style="margin-bottom:0;">
                            Bấm token bên dưới để chèn vào vị trí con trỏ.
                          </p>
                        </div>

                        <!-- Tokens -->
                        <div class="im-tokens">
                          <?php if (!empty($token_catalog)) : ?>
                            <?php foreach ($token_catalog as $tk) : ?>
                              <button type="button"
                                      class="btn btn-default btn-xs im-token"
                                      data-token="<?php echo html_escape(is_array($tk) ? ($tk['key'] ?? $tk['token'] ?? $tk['name'] ?? '') : (string)$tk); ?>">
                                <?php echo html_escape(is_array($tk) ? ($tk['key'] ?? $tk['token'] ?? $tk['name'] ?? '') : (string)$tk); ?>
                              </button>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </div>

                        <div class="im-confirm">
                          <label>Xác nhận chống gửi nhầm: Gõ <b>SEND</b> để mở nút “Gửi”.</label>
                          <input type="text" class="form-control" id="confirm_students" placeholder="Gõ SEND">
                        </div>

                        <div class="im-actions">
                          <button type="button" class="btn btn-info" id="btnPreviewStudents">
                            <i class="fa fa-eye"></i> Preview
                          </button>
                          <button type="submit" class="btn btn-success" id="btnSendStudents" disabled>
                            <i class="fa fa-paper-plane"></i> Gửi
                          </button>
                        </div>

                        <div class="im-msg text-danger small" id="msg_students" style="display:none;"></div>
                      </form>
                    </div>
                  </div>

                  <!-- RIGHT -->
                  <div class="col-md-6">
                    <div class="im-card">
                      <div class="im-card-h">
                        <div class="im-title"><i class="fa fa-eye"></i> Preview</div>
                        <div class="im-tip">Render token theo dữ liệu demo / sinh viên</div>
                      </div>
                      <div class="im-preview">
                        <div class="im-preview-subject" id="pv_subject_students">Preview sẽ hiện thị ở đây...</div>
                        <div class="im-preview-body" id="pv_body_students"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ================= TAB: JOB (GROUP) ================= -->
              <div role="tabpanel" class="tab-pane" id="tab_job">
                <div class="row im-grid">
                  <!-- LEFT -->
                  <div class="col-md-6">
                    <div class="im-card">
                      <div class="im-card-h">
                        <div class="im-title"><i class="fa fa-pencil"></i> Soạn email</div>
                        <div class="im-tip">An toàn: luôn Load + tick recipients trước khi gửi</div>
                      </div>

                      <form method="post" action="<?php echo admin_url('internship_management/internship_mail/do_send_job'); ?>" id="imFormJob" class="im-form">
                        <?php echo form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>

                        <input type="hidden" name="mode" value="job">
                        <input type="hidden" name="confirm_send" id="confirm_send_job" value="">

                        <div class="form-group">
                          <label>Chọn đơn tuyển</label>
                          <select class="form-control" id="job_order_id" name="job_order_id">
                            <option value="">-- Chọn đơn tuyển --</option>
                            <?php if (!empty($job_orders)) : foreach ($job_orders as $j) : ?>
                              <option value="<?php echo (int)$j->id; ?>">
                                <?php echo html_escape($j->label ?? ('#'.$j->id)); ?>
                              </option>
                            <?php endforeach; endif; ?>
                          </select>
                        </div>

                        <div class="im-alert">
                          <i class="fa fa-shield"></i>
                          An toàn: luôn Load + tick chọn trước khi gửi. Nên bật DRY RUN để kiểm tra danh sách &amp; số lượng.
                        </div>

                        <div class="form-group">
                          <button type="button" class="btn btn-primary btn-block" id="btnLoadRecipients">
                            <i class="fa fa-download"></i> Load danh sách recipients (checkbox)
                          </button>
                        </div>

                        <div class="form-group">
                          <label>Danh sách sinh viên theo đơn (tick chọn)</label>
                          <div id="recipientsBox" class="im-recipients">
                            <div class="text-muted">Chưa load recipients...</div>
                          </div>
                          <div class="small text-muted mtop5" id="recipientsMeta"></div>
                        </div>

                        <div class="row">
                          <div class="col-md-8">
                            <div class="form-group">
                              <label>Chọn mẫu (Template)</label>
                              <select class="form-control" id="tpl_id_job">
                                <option value="">-- Không dùng template --</option>
                                <?php if (!empty($templates)) : foreach ($templates as $t) : ?>
                                  <option value="<?php echo (int)$t->id; ?>">
                                    <?php echo html_escape($t->name ?? ('Template #'.$t->id)); ?>
                                  </option>
                                <?php endforeach; endif; ?>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label>Gửi test / DRY RUN</label>
                              <div class="checkbox checkbox-primary">
                                <input type="checkbox" id="dry_run_job" name="dry_run" value="1">
                                <label for="dry_run_job">DRY RUN (không gửi thật)</label>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="form-group">
                          <label>Nhập email thủ công (tùy chọn)</label>
                          <textarea class="form-control" rows="2" name="manual_emails" id="manual_emails_job"
                                    placeholder="vd: a@x.com, b@y.com hoặc mỗi dòng 1 email"></textarea>
                          <p class="text-muted mtop5" style="margin-bottom:0;">Tách bằng xuống dòng, dấu phẩy “,” hoặc chấm phẩy “;”.</p>
                        </div>

                        <div class="form-group">
                          <label>Subject</label>
                          <input type="text" class="form-control" name="subject" id="subject_job"
                                 placeholder="VD: Thông báo lịch phỏng vấn {{student.name}}">
                        </div>

                        <div class="form-group">
                          <label>Nội dung (HTML)</label>
                          <textarea class="form-control im-editor" rows="10" name="html" id="content_job"
                                    placeholder="Nhập nội dung email, có thể dùng token..."></textarea>
                        </div>

                        <div class="im-tokens">
                          <?php if (!empty($token_catalog)) : ?>
                            <?php foreach ($token_catalog as $tk) : ?>
                              <button type="button"
                                      class="btn btn-default btn-xs im-token"
                                      data-token="<?php echo html_escape(is_array($tk) ? ($tk['key'] ?? $tk['token'] ?? $tk['name'] ?? '') : (string)$tk); ?>">
                                <?php echo html_escape(is_array($tk) ? ($tk['key'] ?? $tk['token'] ?? $tk['name'] ?? '') : (string)$tk); ?>
                              </button>
                            <?php endforeach; ?>
                          <?php endif; ?>
                        </div>

                        <div class="im-confirm">
                          <label>Xác nhận chống gửi nhầm: Gõ <b>SEND</b> để mở nút “Gửi”.</label>
                          <input type="text" class="form-control" id="confirm_job" placeholder="Gõ SEND">
                        </div>

                        <div class="im-actions">
                          <button type="button" class="btn btn-info" id="btnPreviewJob">
                            <i class="fa fa-eye"></i> Preview
                          </button>
                          <button type="submit" class="btn btn-success" id="btnSendJob" disabled>
                            <i class="fa fa-paper-plane"></i> Gửi
                          </button>
                        </div>

                        <div class="im-msg text-danger small" id="msg_job" style="display:none;"></div>
                      </form>
                    </div>
                  </div>

                  <!-- RIGHT -->
                  <div class="col-md-6">
                    <div class="im-card">
                      <div class="im-card-h">
                        <div class="im-title"><i class="fa fa-eye"></i> Preview</div>
                        <div class="im-tip">Render token theo dữ liệu demo / đơn tuyển</div>
                      </div>
                      <div class="im-preview">
                        <div class="im-preview-subject" id="pv_subject_job">Preview sẽ hiện thị ở đây...</div>
                        <div class="im-preview-body" id="pv_body_job"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ================= TAB: LOGS ================= -->
              <div role="tabpanel" class="tab-pane" id="tab_logs">
                <div class="alert alert-info">
                  Log hiển thị ở trang cài đặt hoặc trang log riêng (tùy hệ thống). Nếu bạn cần log tại đây, nói mình để mình build thêm bảng log.
                </div>
              </div>

            </div><!-- tab-content -->

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  :root{
    --ifk-green:#96bc17;
    --ifk-navy:#00325a;
    --ifk-blue:#00a6dc;

    --ifk-border:#e6eef6;
    --ifk-muted:#6b7c93;

    --ifk-radius:16px;
    --ifk-shadow:0 10px 26px rgba(0,50,90,.08);
  }

  /* Không đụng wrapper/panel để tránh nhảy layout */
  .panel-body h4{
    font-weight:800;
    color:var(--ifk-navy);
    letter-spacing:.2px;
  }

  /* Tabs */
  .nav-tabs{ border-bottom:1px solid var(--ifk-border); }
  .nav-tabs>li>a{
    border:0 !important;
    background:transparent !important;
    color:var(--ifk-muted);
    font-weight:800;
    padding:10px 14px;
    border-radius:12px 12px 0 0;
    margin-right:6px;
  }
  .nav-tabs>li>a i{ color:var(--ifk-blue); }
  .nav-tabs>li.active>a,
  .nav-tabs>li.active>a:focus,
  .nav-tabs>li.active>a:hover{
    color:var(--ifk-navy) !important;
    background:rgba(0,166,220,.10) !important;
    border-bottom:2px solid var(--ifk-blue) !important;
  }

  /* Card */
  .im-card{
    border:1px solid var(--ifk-border);
    border-radius:var(--ifk-radius);
    background:#fff;
    box-shadow:var(--ifk-shadow);
    padding:16px;
    margin-bottom:15px;
    position:relative;
    overflow:hidden;
  }
  .im-card:before{
    content:"";
    position:absolute;
    left:0; top:0; bottom:0;
    width:4px;
    background:linear-gradient(180deg,var(--ifk-green),var(--ifk-blue));
    opacity:.9;
  }
  .im-card-h{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:12px;
    padding-left:8px;
  }
  .im-title{
    font-weight:900;
    color:var(--ifk-navy);
    display:flex; align-items:center; gap:8px;
  }
  .im-title i{ color:var(--ifk-blue); }
  .im-tip{
    font-size:12px;
    color:var(--ifk-navy);
    background:rgba(0,50,90,.06);
    padding:6px 10px;
    border-radius:999px;
    font-weight:800;
    white-space:nowrap;
  }

  /* Forms */
  .im-form label{ color:var(--ifk-navy); font-weight:900; margin-bottom:6px; }
  .im-form .form-control{
    border-radius:14px;
    border:1px solid var(--ifk-border);
    box-shadow:none;
    transition: all .15s ease;
  }
  .im-form .form-control:focus{
    border-color: rgba(0,166,220,.55);
    box-shadow:0 0 0 4px rgba(0,166,220,.12);
  }
  .im-editor{ min-height:220px; resize:vertical; }

  /* Alert */
  .im-alert{
    border-left:4px solid var(--ifk-blue);
    background:linear-gradient(180deg, rgba(0,166,220,.10), rgba(150,188,23,.06));
    color:var(--ifk-navy);
    padding:10px 12px;
    border-radius:14px;
    margin-bottom:12px;
    font-weight:800;
  }

  /* Tokens */
  .im-tokens{
    display:flex; flex-wrap:wrap;
    gap:8px;
    margin-top:10px;
    padding:10px;
    border-radius:14px;
    background:rgba(0,50,90,.035);
    border:1px dashed rgba(0,50,90,.12);
  }
  .im-token.btn{
    border-radius:999px;
    border:1px solid rgba(0,166,220,.25);
    background:#fff;
    color:var(--ifk-navy);
    font-weight:900;
    padding:5px 10px;
    transition: all .12s ease;
  }
  .im-token.btn:hover{
    border-color:rgba(0,166,220,.55);
    box-shadow:0 10px 20px rgba(0,50,90,.08);
  }

  /* Confirm */
  .im-confirm{
    margin-top:12px;
    padding:12px;
    border-radius:14px;
    background:rgba(150,188,23,.09);
    border:1px solid rgba(150,188,23,.25);
  }
  .im-confirm input.form-control{ margin-top:8px; border-radius:14px; }

  /* Buttons */
  .im-actions{ display:flex; gap:10px; margin-top:14px; flex-wrap:wrap; }
  .im-actions .btn{ border-radius:14px; font-weight:900; padding:10px 14px; border:0; }
  .btn-info{ background:var(--ifk-blue) !important; }
  .btn-success{ background:var(--ifk-green) !important; color:#0b1a08 !important; }
  .btn-primary{ background:var(--ifk-navy) !important; }
  .btn[disabled]{ opacity:.55; cursor:not-allowed !important; }

  /* Recipients */
  .im-recipients{
    border:1px solid var(--ifk-border);
    border-radius:14px;
    padding:10px;
    max-height:260px;
    overflow:auto;
    background:#fff;
  }
  .im-rec-item{
    display:flex;
    align-items:flex-start;
    gap:10px;
    padding:10px 8px;
    border-bottom:1px solid #f1f5fa;
  }
  .im-rec-item:last-child{ border-bottom:none; }
  .im-rec-item input[type="checkbox"]{
    margin-top:4px;
    accent-color: var(--ifk-green);
    transform: scale(1.05);
  }
  .im-rec-email{ color:var(--ifk-muted); font-size:12px; font-weight:800; }

  /* Preview (giữ chiều cao ổn định để không nhảy) */
  .im-preview{
    border:1px solid rgba(0,50,90,.10);
    border-radius:var(--ifk-radius);
    padding:12px;
    background:linear-gradient(180deg, rgba(0,166,220,.06), #fff);
    min-height:520px; /* ✅ cố định hơn để không nhảy */
  }
  .im-preview-subject{
    font-weight:900;
    padding:12px 14px;
    border-radius:14px;
    background:linear-gradient(90deg, var(--ifk-navy), #0b4f7a);
    color:#fff;
    margin-bottom:10px;
  }
  .im-preview-body{
    background:#fff;
    border-radius:14px;
    padding:14px;
    min-height:420px; /* ✅ cố định hơn */
    border:1px solid #eef4fb;
    overflow:auto;
  }

  /* Select2 match */
  .select2-container .select2-selection--single{
    height:40px;
    border-radius:14px !important;
    border:1px solid var(--ifk-border) !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered{
    line-height:40px;
    font-weight:800;
    color:var(--ifk-navy);
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow{ height:40px; }
</style>

<script>
(function(){
  "use strict";

  // Perfex global
 const ADMIN_URL = '<?php echo rtrim(admin_url(), '/').'/'; ?>';

  const csrfName = document.getElementById('im_csrf_name').value;
  let csrfHash   = document.getElementById('im_csrf_hash').value;

  function setMsg(id, text){
    const el = document.getElementById(id);
    if(!el) return;
    el.style.display = text ? 'block' : 'none';
    el.textContent = text || '';
  }

  function insertAtCursor(textarea, text){
    if(!textarea) return;
    textarea.focus();
    const start = textarea.selectionStart || 0;
    const end   = textarea.selectionEnd || 0;
    const val = textarea.value || '';
    textarea.value = val.substring(0,start) + text + val.substring(end);
    const pos = start + text.length;
    textarea.setSelectionRange(pos, pos);
  }

  // Token click (both tabs)
  document.querySelectorAll('.im-token').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const token = btn.getAttribute('data-token') || '';
      // insert into active textarea (students/job)
      const activeTab = document.querySelector('.tab-pane.active');
      if(!activeTab) return;
      const ta = activeTab.querySelector('textarea.im-editor');
      insertAtCursor(ta, token);
    });
  });

  // Enable send only when typed SEND
  function bindConfirm(inputId, btnId, hiddenId){
    const inp = document.getElementById(inputId);
    const btn = document.getElementById(btnId);
    const hid = hiddenId ? document.getElementById(hiddenId) : null;
    if(!inp || !btn) return;
    inp.addEventListener('input', ()=>{
      const ok = (inp.value.trim().toUpperCase() === 'SEND');
      btn.disabled = !ok;
      if(hid) hid.value = ok ? 'SEND' : '';
    });
  }
  bindConfirm('confirm_students','btnSendStudents','confirm_send_students');
  bindConfirm('confirm_job','btnSendJob','confirm_send_job');

  async function safeJson(res){
    // If server returns HTML (doctype) => this will throw
    const text = await res.text();
    try { return JSON.parse(text); }
    catch(e){
      // show tail text to debug
      throw new Error('Response is not JSON. First chars: ' + text.substring(0,80));
    }
  }

  async function loadTemplate(tplId, subjectEl, contentEl, msgElId){
    if(!tplId){
      // do nothing (keep current)
      return;
    }
    setMsg(msgElId, '');
    try{
      const url = ADMIN_URL + 'internship_management/internship_mail/ajax_template/' + tplId;
      const res = await fetch(url, {
        credentials:'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const json = await safeJson(res);

      const tpl = (json && (json.template || json.data)) ? (json.template || json.data) : null;
      if(json && json.ok && tpl){
        subjectEl.value = tpl.subject || '';
        contentEl.value = tpl.content || tpl.html || '';
      }else{
        setMsg(msgElId, 'Không load được template (server trả dữ liệu không hợp lệ).');
      }
    }catch(err){
      setMsg(msgElId, 'Lỗi load template: ' + err.message);
    }
  }

  // Template change (students)
  const tplStudents = document.getElementById('tpl_id_students');
  tplStudents && tplStudents.addEventListener('change', ()=>{
    loadTemplate(
      tplStudents.value,
      document.getElementById('subject_students'),
      document.getElementById('content_students'),
      'msg_students'
    );
  });

  // Template change (job)
  const tplJob = document.getElementById('tpl_id_job');
  tplJob && tplJob.addEventListener('change', ()=>{
    loadTemplate(
      tplJob.value,
      document.getElementById('subject_job'),
      document.getElementById('content_job'),
      'msg_job'
    );
  });

  async function doPreview(mode){
    // mode: 'students' | 'job'
    const msgId = mode === 'students' ? 'msg_students' : 'msg_job';
    setMsg(msgId,'');

    const studentId = mode === 'students' ? (document.getElementById('student_id').value || 0) : 0;
    const jobId     = mode === 'job' ? (document.getElementById('job_order_id').value || 0) : (document.getElementById('job_order_id').value || 0);

    const subject = mode === 'students' ? document.getElementById('subject_students').value : document.getElementById('subject_job').value;
    const content = mode === 'students' ? document.getElementById('content_students').value : document.getElementById('content_job').value;

    try{
      const fd = new FormData();
      fd.append('student_id', studentId);
      fd.append('job_id', jobId);
      fd.append('subject', subject);
      // controller cũ/ mới: nhận html hoặc content
      fd.append('html', content);
      fd.append('content', content);
      fd.append(csrfName, csrfHash);

      const res = await fetch(ADMIN_URL + 'internship_management/internship_mail/ajax_preview', {
        method:'POST',
        body: fd,
        credentials:'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const json = await safeJson(res);
      if(json && json.ok){
        const bodyHtml = json.content || json.html || '';
        // update preview
        if(mode === 'students'){
          document.getElementById('pv_subject_students').textContent = json.subject || '';
          document.getElementById('pv_body_students').innerHTML = bodyHtml;
        }else{
          document.getElementById('pv_subject_job').textContent = json.subject || '';
          document.getElementById('pv_body_job').innerHTML = bodyHtml;
        }
      }else{
        setMsg(msgId, json.message || 'Preview lỗi.');
      }
    }catch(err){
      setMsg(msgId, 'Preview lỗi: ' + err.message);
    }
  }

  document.getElementById('btnPreviewStudents')?.addEventListener('click', ()=>doPreview('students'));
  document.getElementById('btnPreviewJob')?.addEventListener('click', ()=>doPreview('job'));

  // Load recipients by job
  document.getElementById('btnLoadRecipients')?.addEventListener('click', async ()=>{
    setMsg('msg_job','');
    const jobId = parseInt(document.getElementById('job_order_id').value || '0',10);
    if(!jobId){
      setMsg('msg_job','Vui lòng chọn đơn tuyển trước.');
      return;
    }

    const box = document.getElementById('recipientsBox');
    const meta = document.getElementById('recipientsMeta');
    box.innerHTML = '<div class="text-muted">Đang load danh sách...</div>';
    meta.textContent = '';

    try{
      const fd = new FormData();
      // IMPORTANT: controller của bạn đang lấy POST job_order_id
      fd.append('job_order_id', jobId);
      fd.append(csrfName, csrfHash);

      const res = await fetch(ADMIN_URL + 'internship_management/internship_mail/ajax_recipients_by_job', {
        method:'POST',
        body: fd,
        credentials:'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const json = await safeJson(res);

      if(!json || !json.ok){
        box.innerHTML = '<div class="text-danger">Không load được recipients.</div>';
        setMsg('msg_job', json?.message || 'Không load được recipients.');
        return;
      }

      const items = json.items || json.rows || json.recipients || [];
      if(!items.length){
        box.innerHTML = '<div class="text-muted">Đơn tuyển này chưa có sinh viên (hoặc thiếu email).</div>';
        meta.textContent = '0 recipients';
        return;
      }

      let html = '';
      items.forEach((it, idx)=>{
        const id = it.id || it.student_id || 0;
        const name = it.name || it.full_name || it.text || ('SV #' + id);
        const email = it.email || '';
        const safeName = String(name).replace(/</g,'&lt;').replace(/>/g,'&gt;');
        const safeEmail = String(email).replace(/</g,'&lt;').replace(/>/g,'&gt;');

        html += `
          <div class="im-rec-item">
            <input type="checkbox" name="recipient_ids[]" value="${id}" checked>
            <div>
              <div><b>${safeName}</b></div>
              <div class="im-rec-email">${safeEmail}</div>
            </div>
          </div>
        `;
      });

      box.innerHTML = html;
      meta.textContent = items.length + ' recipients đã load (mặc định tick hết).';

    }catch(err){
      box.innerHTML = '<div class="text-danger">Lỗi load recipients.</div>';
      setMsg('msg_job','Lỗi load recipients: ' + err.message);
    }
  });

  // Select2 AJAX for students search (if Select2 is available)
  if (typeof $ !== 'undefined' && $.fn.select2) {
    $('#student_id').select2({
      width: '100%',
      allowClear: true,
      placeholder: '-- Chọn sinh viên --',
      ajax: {
        url: ADMIN_URL + 'internship_management/internship_mail/ajax_search_students',
        dataType: 'json',
        delay: 250,
        data: function(params){
          return { q: params.term || '', page: params.page || 1 };
        },
        processResults: function(data){
          return data;
        },
        cache: true
      }
    });
  }

  // Auto preview on tab switch (optional small UX)
  $('a[data-toggle="tab"]').on('shown.bs.tab', function(){
    // no auto preview to avoid spam
  });

})();
</script>

<?php init_tail(); ?>