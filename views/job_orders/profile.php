<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php
/**
 * Internship Job Order Profile (PRO UI)
 * - IFK brand colors
 * - Status VI mapping (fix received/approved/...)
 * - Recruiter summary always visible above tabs
 *
 * Expected variables from controller:
 *  - $title
 *  - $job_order (array/object)   [REQUIRED]
 *  - $job_order_id (int)
 *  - $crm_client_id (int)
 *  - $active_tab (string)
 *  - tab datasets: $candidates, $invoices, $contracts, $logs, $notes, ...
 */

// ---------- View-level safe getters (array/object) ----------
if (!function_exists('im_view_get')) {
    function im_view_get($row, $key) {
        if (is_array($row)) return array_key_exists($key, $row) ? $row[$key] : null;
        if (is_object($row)) return isset($row->$key) ? $row->$key : null;
        return null;
    }
}
if (!function_exists('im_view_pick_first')) {
    function im_view_pick_first($row, $keys, $default = '') {
        foreach ((array)$keys as $k) {
            $v = im_view_get($row, $k);
            if ($v === null) continue;
            if (is_string($v)) {
                $v = trim($v);
                if ($v !== '') return $v;
            } else {
                if ($v !== '' && $v !== false) return $v;
            }
        }
        return $default;
    }
}
if (!function_exists('im_status_vi')) {
    function im_status_vi($status) {
        $map = [
            'received'   => 'Đã tiếp nhận',
            'approved'   => 'Đã duyệt',
            'rejected'   => 'Từ chối',
            'processing' => 'Đang xử lý',
            'completed'  => 'Hoàn thành chương trình',
            'done'       => 'Hoàn thành chương trình',
            'pending'    => 'Chờ xử lý',
            'entry'=> 'Đã nhập cảnh',
        ];
        $key = strtolower(trim((string)$status));
        return isset($map[$key]) ? $map[$key] : (string)$status;
    }
}

// Backward compatibility: some old code used $job
if (!isset($job_order) && isset($job)) {
    $job_order = $job;
}

// Guard: avoid undefined warnings
if (!isset($job_order)) {
    $job_order = [];
}
?>

<style>
/* =====================================================
   IFK FULL STABLE VERSION
   Safe - Không phá layout
   ===================================================== */

.im-pro-wrap{
  --ifk-navy:#00325a;
  --ifk-green:#96bc17;
  --ifk-cyan:#00a6dc;
  --ifk-soft:rgba(0,166,220,.08);
  --ifk-border:rgba(0,50,90,.15);
  --ifk-text:#1e293b;
  --ifk-muted:#64748b;
  --ifk-shadow:0 8px 24px rgba(0,50,90,.12);

  font-family: inherit;
  color: var(--ifk-text);
}

/* ============ HERO ============ */

.im-pro-wrap .im-hero{
  border-radius:16px;
  padding:20px;
  background:linear-gradient(135deg,var(--ifk-navy),var(--ifk-cyan));
  color:#fff;
  box-shadow:var(--ifk-shadow);
  margin-bottom:20px;
}

.im-pro-wrap .im-hero-row{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:20px;
  flex-wrap:wrap;
}

.im-pro-wrap .im-hero-left{
  display:flex;
  align-items:center;
  gap:16px;
}

.im-pro-wrap .im-avatar{
  width:60px;
  height:60px;
  border-radius:16px;
  display:flex;
  align-items:center;
  justify-content:center;
  background:rgba(255,255,255,.15);
  border:1px solid rgba(255,255,255,.25);
  font-size:22px;
}

.im-pro-wrap .im-title{
  margin:0;
  font-size:22px;
  font-weight:700;
  color:#fff;
}

.im-pro-wrap .im-sub{
  margin-top:6px;
  color:rgba(255,255,255,.85);
  font-size:14px;
}

.im-pro-wrap .im-meta{
  margin-top:10px;
  display:flex;
  gap:8px;
  flex-wrap:wrap;
}

.im-pro-wrap .im-chip{
  background:rgba(255,255,255,.15);
  border:1px solid rgba(255,255,255,.25);
  padding:6px 12px;
  border-radius:999px;
  font-size:12px;
  color:#fff;
}

/* ============ ACTIONS ============ */

.im-pro-wrap .im-actions{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
}

.im-pro-wrap .btn{
  border-radius:999px;
  font-weight:600;
}

.im-pro-wrap .btn-primary{
  background:var(--ifk-navy);
  border-color:var(--ifk-navy);
}

.im-pro-wrap .btn-success{
  background:var(--ifk-green);
  border-color:var(--ifk-green);
}

.im-pro-wrap .btn-info{
  background:#fff;
  border-color:#fff;
  color:var(--ifk-cyan);
}

/* ============ CARDS WRAP ============ */

.im-pro-wrap .im-recruiter-wrap{
  display:flex;
  gap:20px;
  flex-wrap:wrap;
  margin-bottom:20px;
}

/* 2 column layout */
.im-pro-wrap .im-rec-card{
  flex:1;
  min-width:360px;
  background:#fff;
  border:1px solid var(--ifk-border);
  border-radius:16px;
  padding:18px;
  box-shadow:var(--ifk-shadow);
}

/* card header */
.im-pro-wrap .im-rec-head{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:16px;
  margin-bottom:14px;
}

.im-pro-wrap .im-rec-ico{
  width:46px;
  height:46px;
  border-radius:14px;
  display:flex;
  align-items:center;
  justify-content:center;
  background:var(--ifk-soft);
  border:1px solid var(--ifk-border);
}

.im-pro-wrap .im-rec-ico i{
  color:var(--ifk-navy);
}

/* grid inside card */
.im-pro-wrap .im-rec-grid{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:14px;
}

.im-pro-wrap .im-kv{
  border:1px dashed var(--ifk-border);
  border-radius:12px;
  padding:10px 12px;
  background:#fff;
}

.im-pro-wrap .im-k{
  font-size:12px;
  color:var(--ifk-muted);
  font-weight:600;
}

.im-pro-wrap .im-v{
  margin-top:4px;
  font-weight:700;
  font-size:14px;
}

/* ============ STATUS ============ */

.im-pro-wrap .im-status{
  background:var(--ifk-soft);
  color:var(--ifk-navy);
  padding:6px 12px;
  border-radius:999px;
  font-size:12px;
  font-weight:600;
}

/* ============ TABS ============ */

.im-pro-wrap .nav-tabs{
  border-bottom:1px solid var(--ifk-border);
  margin-bottom:16px;
}

.im-pro-wrap .nav-tabs>li>a{
  border:0 !important;
  font-weight:600;
  padding:10px 16px;
  border-radius:12px 12px 0 0;
}

.im-pro-wrap .nav-tabs>li.active>a{
  background:var(--ifk-soft);
  border-bottom:2px solid var(--ifk-green) !important;
  color:var(--ifk-navy);
}

/* ============ RESPONSIVE ============ */

@media(max-width:991px){
  .im-pro-wrap .im-rec-grid{
    grid-template-columns:1fr;
  }
  .im-pro-wrap .im-rec-card{
    min-width:100%;
  }
}
</style>

<div id="wrapper" class="im-pro-wrap">
  <div class="content">
    <div class="row">
      <div class="col-md-12">

        <?php
          // IDs / tab
          $job_order_id  = isset($job_order_id) ? (int)$job_order_id : (int)im_view_pick_first($job_order, ['id'], 0);
          $crm_client_id = isset($crm_client_id) ? (int)$crm_client_id : (int)im_view_pick_first($job_order, ['crm_client_id','crm_customer_id'], 0);
          $active_tab    = isset($active_tab) ? (string)$active_tab : (string)($this->input->get('tab', true) ?: 'candidates');

          // Company & Job title (schema variants)
          $company = im_view_pick_first($job_order, [
            'company_name_vi','company_name_vn','company_vi',
            'company_name_jp','company_name',
            'company','company_name_text',
            'employer_company','employer_name','customer_company','client_company',
            'ten_cong_ty','tencongty'
          ], '');

          $jobTitle = im_view_pick_first($job_order, [
            'job_title_vi','job_title_vn','job_title',
            'workplace_vi','workplace','workplace_jp',
            'position_vi','position','position_jp',
            'title','job_name'
          ], '');

          $created = im_view_pick_first($job_order, ['dateadded','created_at','datecreated','datecreated'], '');
          $updated = im_view_pick_first($job_order, ['updated_at','last_update','datemodified','dateupdated'], '');

          $status_raw = strtolower(trim((string)im_view_pick_first($job_order, ['status','order_status','trang_thai'], '')));
          $status_vi  = $status_raw !== '' ? im_status_vi($status_raw) : '-';

          $crm_label = ($crm_client_id > 0) ? ('Đã liên kết CRM #' . $crm_client_id) : 'Chưa liên kết CRM';
        ?>

        <!-- HERO -->
        <div class="im-hero">
          <div class="im-hero-row">
            <div class="im-hero-left">
              <div class="im-avatar"><i class="fa fa-briefcase"></i></div>
              <div>
                <h3 class="im-title">
                  <?php echo html_escape(isset($title) ? $title : ('Hồ sơ Đơn tuyển #' . $job_order_id)); ?>
                </h3>

                <div class="im-sub">
                  <?php if ($company !== ''): ?>
                    <i class="fa fa-building-o"></i> <?php echo html_escape($company); ?>
                  <?php else: ?>
                    <i class="fa fa-building-o"></i> <?php echo html_escape('Đơn tuyển #' . $job_order_id); ?>
                  <?php endif; ?>

                  <?php if ($jobTitle !== ''): ?>
                    &nbsp; • &nbsp;<i class="fa fa-id-badge"></i> <?php echo html_escape($jobTitle); ?>
                  <?php endif; ?>
                </div>

                <div class="im-meta">
                  <span class="im-chip"><i class="fa fa-hashtag"></i> ID: <?php echo (int)$job_order_id; ?></span>
                  <span class="im-chip"><i class="fa fa-cloud"></i> <?php echo html_escape($crm_label); ?></span>
                  <?php if ($created): ?>
                    <span class="im-chip"><i class="fa fa-calendar-plus-o"></i> Tạo: <?php echo _dt($created); ?></span>
                  <?php endif; ?>
                  <?php if ($updated): ?>
                    <span class="im-chip"><i class="fa fa-refresh"></i> Cập nhật: <?php echo _dt($updated); ?></span>
                  <?php endif; ?>
                  <?php if ($status_raw !== ''): ?>
                    <span class="im-chip"><i class="fa fa-flag"></i> Trạng thái: <?php echo html_escape($status_vi); ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="im-actions">
              <a href="<?php echo admin_url('internship_management/internship_job_orders'); ?>" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Danh sách
              </a>

              <?php if ($crm_client_id <= 0): ?>
                <a href="<?php echo admin_url('internship_management/internship_job_orders/push_crm/' . (int)$job_order_id); ?>"
                   class="btn btn-success">
                  <i class="fa fa-cloud-upload"></i> Đẩy CRM
                </a>
              <?php else: ?>
                <a href="<?php echo admin_url('clients/client/' . (int)$crm_client_id); ?>" class="btn btn-info" target="_blank">
                  <i class="fa fa-external-link"></i> Xem CRM
                </a>
              <?php endif; ?>

              <a href="<?php echo admin_url('internship_management/internship_job_orders/job_order/' . (int)$job_order_id); ?>" class="btn btn-primary">
                <i class="fa fa-pencil"></i> Chỉnh sửa
              </a>
            </div>
          </div>

          <?php if ($crm_client_id <= 0): ?>
            <div class="im-note">
              <i class="fa fa-lightbulb-o"></i>
              Mẹo: Bấm <b>Đẩy CRM</b> để tạo/liên kết khách hàng trong CRM, sau đó tab <b>Hóa đơn</b> và <b>Hợp đồng</b> sẽ hiển thị dữ liệu theo khách hàng đó.
            </div>
          <?php endif; ?>
        </div>

        <div class="panel_s">
          <div class="panel-body">

            <?php
              // Tabs map
              $tabs = [
                'candidates' => ['label' => 'Ứng viên', 'icon' => 'fa fa-users'],
                'invoices'   => ['label' => 'Hóa đơn', 'icon' => 'fa fa-file-text-o'],
                'contracts'  => ['label' => 'Hợp đồng', 'icon' => 'fa fa-handshake-o'],
                'info'       => ['label' => 'Thông tin đơn', 'icon' => 'fa fa-info-circle'],
                'logs'       => ['label' => 'Log', 'icon' => 'fa fa-history'],
                'notes'      => ['label' => 'Ghi chú', 'icon' => 'fa fa-sticky-note-o'],
              ];
            ?>

            <?php
              // Recruiter summary block (shown above all tabs)
              $im_address = im_view_pick_first($job_order, [
                'address_vi','address_vn','address','company_address','employer_address',
                'dia_chi','address_text','company_address_text'
              ], '');

              // Field & Industry: map to job_title_vi/workplace_vi if needed
              $im_field = im_view_pick_first($job_order, [
                'field_vi','field_vn','field','linh_vuc','business_field',
                'industry_group','job_field','category',
                'job_title_vi','job_title_vn'
              ], '');

              $im_industry = im_view_pick_first($job_order, [
                'industry_vi','industry_vn','industry','nganh','job_industry',
                'occupation','occupation_vi',
                'workplace_vi','workplace'
              ], '');

              $im_phone = im_view_pick_first($job_order, [
                'phone','company_phone','company_phone_vi','employer_phone','contact_phone','sdt','mobile'
              ], '');

              $im_email = im_view_pick_first($job_order, [
                'email','company_email','employer_email','contact_email'
              ], '');

              // Quantity: support quantity_total(_vi) + male/female
              $im_qty = (int)im_view_pick_first($job_order, [
                'quantity_total','quantity_total_vi','quantity','qty','so_luong','slots','number_of_people'
              ], 0);

              $im_interview_date = im_view_pick_first($job_order, ['interview_date','interview_date_vi','pv_date','ngay_pv'], '');
              $im_entry_date     = im_view_pick_first($job_order, ['entry_date','entry_date_vi','nhap_canh','immigration_date'], '');
              $im_return_date    = im_view_pick_first($job_order, ['return_date','return_date_vi','ve_nuoc','return_to_vn_date'], '');
              $im_status         = im_view_pick_first($job_order, ['status','order_status','trang_thai'], '');
              $im_status_raw     = strtolower(trim((string)$im_status));
              $im_status_vi      = ($im_status_raw !== '') ? im_status_vi($im_status_raw) : '-';
            ?>

            <div class="im-recruiter-wrap">
              <!-- Recruiter card -->
              <div class="im-rec-card">
                <div class="im-rec-head">
                  <div class="im-rec-ico"><i class="fa fa-building"></i></div>
                  <div class="im-rec-main">
                    <div class="im-rec-name"><?php echo html_escape($company ?: ('Đơn tuyển #' . $job_order_id)); ?></div>
                    <?php if (!empty($im_address)) { ?>
                      <div class="im-rec-sub"><i class="fa fa-map-marker text-muted"></i> <?php echo html_escape($im_address); ?></div>
                    <?php } ?>
                  </div>
                  <div class="im-rec-badges">
                    <span class="im-pill"><i class="fa fa-hashtag"></i> ID: <?php echo (int)$job_order_id; ?></span>
                    <?php if ($crm_client_id > 0) { ?>
                      <span class="im-pill im-pill-success"><i class="fa fa-link"></i> CRM #<?php echo (int)$crm_client_id; ?></span>
                    <?php } else { ?>
                      <span class="im-pill"><i class="fa fa-unlink"></i> Chưa CRM</span>
                    <?php } ?>
                  </div>
                </div>

                <div class="im-rec-grid">
                  <div class="im-kv">
                    <div class="im-k">Lĩnh vực</div>
                    <div class="im-v"><?php echo html_escape($im_field ?: '-'); ?></div>
                  </div>
                  <div class="im-kv">
                    <div class="im-k">Ngành</div>
                    <div class="im-v"><?php echo html_escape($im_industry ?: '-'); ?></div>
                  </div>
                  <div class="im-kv">
                    <div class="im-k">Điện thoại</div>
                    <div class="im-v"><?php echo html_escape($im_phone ?: '-'); ?></div>
                  </div>
                  <div class="im-kv">
                    <div class="im-k">Email</div>
                    <div class="im-v"><?php echo html_escape($im_email ?: '-'); ?></div>
                  </div>
                </div>
              </div>

              <!-- Summary card -->
              <div class="im-rec-card">
                <div class="im-rec-head">
                  <div class="im-rec-ico"><i class="fa fa-info-circle"></i></div>
                  <div class="im-rec-main">
                    <div class="im-rec-name">Tóm tắt đơn tuyển</div>
                    <div class="im-rec-sub">Thông tin nhanh cho tất cả tab</div>
                  </div>

                  <?php if ($im_status_raw !== ''): ?>
                    <div>
                      <span class="im-status status-<?php echo html_escape($im_status_raw); ?>">
                        <?php echo html_escape($im_status_vi); ?>
                      </span>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="im-rec-grid">
                  <div class="im-kv">
                    <div class="im-k">Số lượng</div>
                    <div class="im-v"><?php echo (int)$im_qty; ?></div>
                  </div>
                  <div class="im-kv">
                    <div class="im-k">Ngày PV</div>
                    <div class="im-v"><?php echo !empty($im_interview_date) ? _d($im_interview_date) : '-'; ?></div>
                  </div>
                  <div class="im-kv">
                    <div class="im-k">Nhập cảnh</div>
                    <div class="im-v"><?php echo !empty($im_entry_date) ? _d($im_entry_date) : '-'; ?></div>
                  </div>
                  <div class="im-kv">
                    <div class="im-k">Về nước</div>
                    <div class="im-v"><?php echo !empty($im_return_date) ? _d($im_return_date) : '-'; ?></div>
                  </div>
                  <div class="im-kv">
                    <div class="im-k">Cập nhật</div>
                    <div class="im-v"><?php echo !empty($updated) ? _dt($updated) : (!empty($created) ? _dt($created) : '-'); ?></div>
                  </div>
                  <div class="im-kv">
                    <div class="im-k">CRM</div>
                    <div class="im-v"><?php echo ($crm_client_id > 0) ? ('#' . (int)$crm_client_id) : '-'; ?></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs" role="tablist">
              <?php foreach ($tabs as $k => $t): ?>
                <?php $url = admin_url('internship_management/internship_job_orders/profile/' . (int)$job_order_id . '?tab=' . $k); ?>
                <li role="presentation" class="<?php echo ($active_tab === $k ? 'active' : ''); ?>">
                  <a href="<?php echo $url; ?>">
                    <i class="<?php echo $t['icon']; ?>"></i> <?php echo $t['label']; ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>

            <div class="tab-content" style="padding-top:14px;">
              <?php
                /**
                 * Load tab view if exists.
                 * Keep legacy structure: views/job_orders/tabs/tab_{tab}.php
                 */
                $tab_view = 'internship_management/job_orders/tabs/tab_' . $active_tab;
                $tab_file = function_exists('module_dir_path')
                  ? module_dir_path('internship_management', 'views/job_orders/tabs/tab_' . $active_tab . '.php')
                  : (APPPATH . 'views/' . $tab_view . '.php');

                if (!is_string($tab_file) || !file_exists($tab_file)) {
                    echo '<div class="alert alert-warning">Tab <b>' . html_escape($active_tab) . '</b> chưa có view (thiếu file: ' . html_escape((string)$tab_file) . ').</div>';
                } else {
                    $this->load->view($tab_view);
                }
              ?>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>
</body>
</html>
