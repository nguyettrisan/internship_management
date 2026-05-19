<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * TAB: Hợp đồng
 * Auto resolve CRM client_id from internship_applications by application_id (student_id)
 */
$application_id = isset($student_id) ? (int)$student_id : (isset($id) ? (int)$id : 0);
$client_id      = isset($client_id) ? (int)$client_id : 0;

// --- Auto resolve client_id ---
if ($client_id <= 0 && $application_id > 0) {
    $appTable = db_prefix().'internship_applications';
    if ($this->db->table_exists($appTable)) {
        $row = $this->db->get_where($appTable, ['id' => $application_id])->row_array();
        if ($row) {
            foreach (['client_id','crm_client_id','userid'] as $k) {
                if (!empty($row[$k])) { $client_id = (int)$row[$k]; break; }
            }
            // Resolve via CRM by email if still missing
            if ($client_id <= 0) {
                $email = $row['email'] ?? ($row['student_email'] ?? '');
                if (!empty($email) && $this->db->table_exists(db_prefix().'contacts')) {
                    $ct = $this->db->get_where(db_prefix().'contacts', ['email' => $email])->row_array();
                    if ($ct && !empty($ct['userid'])) {
                        $client_id = (int)$ct['userid'];
                        // Persist back to application for next loads
                        if ($this->db->field_exists('client_id', $appTable)) {
                            $this->db->where('id', $application_id)->update($appTable, ['client_id' => $client_id]);
                        }
                    }
                }
            }
        }
    }
}

$hasClient = $client_id > 0;

$btn_contract_new = $hasClient ? admin_url('contracts/contract?customer_id=' . $client_id) : admin_url('contracts/contract');
$btn_push_crm     = admin_url('internship_management/student_client/push_crm/'.$application_id);

$contracts = [];
if ($hasClient && $this->db->table_exists(db_prefix().'contracts')) {
    $clientCol = $this->db->field_exists('client', db_prefix().'contracts') ? 'client' : ($this->db->field_exists('clientid', db_prefix().'contracts') ? 'clientid' : null);
    if ($clientCol) {
        $this->db->select('id, subject, datestart, dateend, dateadded');
        $this->db->from(db_prefix().'contracts');
        $this->db->where($clientCol, $client_id);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(50);
        $contracts = $this->db->get()->result_array();
    }
}
?>
<div class="panel_s">
  <div class="panel-body">
    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
      <h4 class="tw-m-0 tw-font-semibold"><i class="fa fa-handshake-o"></i> Hợp đồng</h4>
      <div class="tw-flex tw-gap-2">
        <?php if (!$hasClient): ?>
          <a href="<?= $btn_push_crm; ?>" class="btn btn-default"><i class="fa fa-cloud-upload"></i> Đẩy CRM</a>
        <?php endif; ?>
        <a href="<?= $btn_contract_new; ?>" class="btn btn-primary" target="_blank"><i class="fa fa-plus"></i> Tạo hợp đồng</a>
      </div>
    </div>

    <?php if (!$hasClient): ?>
      <div class="alert alert-warning">
        Chưa liên kết CRM. Vui lòng bấm <b>Đẩy CRM</b> để tạo khách hàng CRM, sau đó hợp đồng sẽ hiển thị ở đây.
      </div>
    <?php endif; ?>

    <?php if (empty($contracts)): ?>
      <p class="text-muted">Chưa có hợp đồng.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr><th>#</th><th>Tiêu đề</th><th>Bắt đầu</th><th>Kết thúc</th></tr>
          </thead>
          <tbody>
            <?php foreach ($contracts as $ct): ?>
              <tr>
                <td><a href="<?= admin_url('contracts/contract/'.$ct['id']); ?>" target="_blank">#<?= $ct['id']; ?></a></td>
                <td><?= htmlspecialchars($ct['subject'] ?? ('Contract #'.$ct['id'])); ?></td>
                <td><?= !empty($ct['datestart']) ? _d($ct['datestart']) : '-'; ?></td>
                <td><?= !empty($ct['dateend']) ? _d($ct['dateend']) : '-'; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
