<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * CRM Tab (Perfex)
 * Expect: $student_id (application_id) OR $student_obj/$student
 * This tab tries to resolve $client_id automatically.
 */
$application_id = isset($student_id) ? (int)$student_id : (isset($id) ? (int)$id : 0);

// Resolve client_id from controller if provided
$client_id = isset($client_id) ? (int)$client_id : 0;

// Fallback: try from student object
$obj = isset($student_obj) ? $student_obj : (isset($student) ? $student : null);
if ($client_id <= 0 && $obj) {
    foreach (['client_id','crm_client_id','userid'] as $k) {
        if (is_array($obj) && !empty($obj[$k])) { $client_id = (int)$obj[$k]; break; }
        if (is_object($obj) && !empty($obj->$k)) { $client_id = (int)$obj->$k; break; }
    }
}

// Final fallback: try resolve via controller helper if exists
if ($client_id <= 0 && isset($this) && method_exists($this, 'im_resolve_crm_client_id') && $application_id > 0) {
    // Try to use profile row if available
    $row = [];
    if (is_array($obj)) $row = $obj;
    $client_id = (int)$this->im_resolve_crm_client_id($application_id, $row);
}

$hasClient = $client_id > 0;

$btn_invoice_new   = $hasClient ? admin_url('invoices/invoice?customer_id=' . $client_id) : admin_url('invoices/invoice');
$btn_contract_new  = $hasClient ? admin_url('contracts/contract?customer_id=' . $client_id) : admin_url('contracts/contract');
$btn_client_open   = $hasClient ? admin_url('clients/client/' . $client_id) : null;

// Fetch invoices & contracts
$invoices = [];
$contracts = [];

if ($hasClient) {
    $tblInvoices = db_prefix().'invoices';
    if ($this->db->table_exists($tblInvoices) && $this->db->field_exists('clientid', $tblInvoices)) {
        $this->db->select('id, number, prefix, status, total, date, duedate');
        $this->db->from($tblInvoices);
        $this->db->where('clientid', $client_id);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(20);
        $invoices = $this->db->get()->result_array();
    }

    $tblContracts = db_prefix().'contracts';
    if ($this->db->table_exists($tblContracts)) {
        $clientCol = $this->db->field_exists('client', $tblContracts) ? 'client' : ($this->db->field_exists('clientid', $tblContracts) ? 'clientid' : null);
        if ($clientCol) {
            $this->db->select('id, subject, datestart, dateend, dateadded');
            $this->db->from($tblContracts);
            $this->db->where($clientCol, $client_id);
            $this->db->order_by('id', 'DESC');
            $this->db->limit(20);
            $contracts = $this->db->get()->result_array();
        }
    }
}
?>
<div class="panel_s">
  <div class="panel-body">
    <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
      <div class="tw-flex tw-items-center tw-gap-2">
        <i class="fa fa-briefcase"></i>
        <h4 class="tw-m-0 tw-font-semibold">CRM</h4>
        <?php if ($hasClient): ?>
          <span class="label label-default tw-ml-2">client_id: <?= (int)$client_id; ?></span>
        <?php endif; ?>
      </div>
      <div class="tw-flex tw-gap-2">
        <?php if ($btn_client_open): ?>
          <a href="<?= $btn_client_open; ?>" class="btn btn-default" target="_blank"><i class="fa fa-external-link"></i> Mở CRM</a>
        <?php endif; ?>
        <a href="<?= $btn_invoice_new; ?>" class="btn btn-info" target="_blank"><i class="fa fa-plus"></i> Tạo hóa đơn</a>
        <a href="<?= $btn_contract_new; ?>" class="btn btn-primary" target="_blank"><i class="fa fa-plus"></i> Tạo hợp đồng</a>
      </div>
    </div>

    <?php if (!$hasClient): ?>
      <div class="alert alert-warning">
        Chưa liên kết CRM. Vui lòng bấm <b>Đẩy CRM</b> để tạo khách hàng hoặc gán <code>client_id</code> để hiển thị hóa đơn/hợp đồng.
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-6">
        <h4 class="tw-font-semibold">Hóa đơn</h4>
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
                    <td><a href="<?= admin_url('invoices/list_invoices/'.$inv['id']); ?>" target="_blank">
                      <?= !empty($inv['prefix']) ? ($inv['prefix'].$inv['number']) : '#'.$inv['id']; ?>
                    </a></td>
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

      <div class="col-md-6">
        <h4 class="tw-font-semibold">Hợp đồng</h4>
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
  </div>
</div>
