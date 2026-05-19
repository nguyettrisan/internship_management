<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * TAB: Hóa đơn
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

$btn_invoice_new = $hasClient ? admin_url('invoices/invoice?customer_id=' . $client_id) : admin_url('invoices/invoice');
$btn_push_crm    = admin_url('internship_management/student_client/push_crm/'.$application_id);

$invoices = [];
if ($hasClient && $this->db->table_exists(db_prefix().'invoices') && $this->db->field_exists('clientid', db_prefix().'invoices')) {
    $this->db->select('id, number, prefix, status, total, date, duedate');
    $this->db->from(db_prefix().'invoices');
    $this->db->where('clientid', $client_id);
    $this->db->order_by('id', 'DESC');
    $this->db->limit(50);
    $invoices = $this->db->get()->result_array();
}
?>
<div class="panel_s">
  <div class="panel-body">
    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
      <h4 class="tw-m-0 tw-font-semibold"><i class="fa fa-file-text-o"></i> Hóa đơn</h4>
      <div class="tw-flex tw-gap-2">
        <?php if (!$hasClient): ?>
          <a href="<?= $btn_push_crm; ?>" class="btn btn-default"><i class="fa fa-cloud-upload"></i> Đẩy CRM</a>
        <?php endif; ?>
        <a href="<?= $btn_invoice_new; ?>" class="btn btn-info" target="_blank"><i class="fa fa-plus"></i> Tạo hóa đơn</a>
      </div>
    </div>

    <?php if (!$hasClient): ?>
      <div class="alert alert-warning">
        Chưa liên kết CRM. Vui lòng bấm <b>Đẩy CRM</b> để tạo khách hàng CRM, sau đó hóa đơn sẽ hiển thị ở đây.
      </div>
    <?php endif; ?>

    <?php if (empty($invoices)): ?>
      <p class="text-muted">Chưa có hóa đơn.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr><th>#</th><th>Ngày</th><th>Hạn</th><th class="text-right">Tổng</th><th>Trạng thái</th></tr>
          </thead>
          <tbody>
            <?php foreach ($invoices as $inv): ?>
              <tr>
                <td>
                  <a href="<?= admin_url('invoices/list_invoices/'.$inv['id']); ?>" target="_blank">
                    <?= !empty($inv['prefix']) ? ($inv['prefix'].$inv['number']) : '#'.$inv['id']; ?>
                  </a>
                </td>
                <td><?= _d($inv['date']); ?></td>
                <td><?= !empty($inv['duedate']) ? _d($inv['duedate']) : '-'; ?></td>
                <td class="text-right"><?= app_format_money($inv['total'], get_base_currency()); ?></td>
                <td><?= format_invoice_status($inv['status']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
