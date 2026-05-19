<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
if (!function_exists('im_row_get_any')) {
    function im_row_get_any($row, $key, $default = null) {
        if (is_array($row)) return array_key_exists($key, $row) ? $row[$key] : $default;
        if (is_object($row)) return isset($row->$key) ? $row->$key : $default;
        return $default;
    }
}
if (!function_exists('im_row_pick_any')) {
    function im_row_pick_any($row, $keys, $default = '') {
        foreach ($keys as $k) {
            $v = im_row_get_any($row, $k, null);
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

$invoices = isset($invoices) && is_array($invoices) ? $invoices : (isset($invoices) && $invoices ? (array)$invoices : []);
$crm_client_id = isset($crm_client_id) ? (int)$crm_client_id : 0;
?>

<?php if ($crm_client_id <= 0) { ?>
  <div class="alert alert-warning">
    <i class="fa fa-cloud"></i> Chưa liên kết CRM nên chưa thể hiển thị hóa đơn.
  </div>
<?php } elseif (empty($invoices)) { ?>
  <div class="alert alert-info">
    <i class="fa fa-info-circle"></i> Chưa có hóa đơn cho khách hàng CRM này.
  </div>
<?php } else { ?>
  <div class="table-responsive">
    <table id="tbl_job_tab_invoices" class="table dt-table">
      <thead>
        <tr>
          <th style="width:70px;">#</th>
          <th><?php echo _l('Số hóa đơn'); ?></th>
          <th style="width:140px;"><?php echo _l('Ngày'); ?></th>
          <th style="width:160px;"><?php echo _l('Tổng'); ?></th>
          <th style="width:160px;"><?php echo _l('Trạng thái'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($invoices as $inv) {
            $id = (int)im_row_pick_any($inv, ['id'], 0);
            $date = (string)im_row_pick_any($inv, ['date','dateadded','created_at'], '');
            $total = im_row_pick_any($inv, ['total','subtotal'], 0);
            $status = im_row_pick_any($inv, ['status'], '');
            $currency = (string)im_row_pick_any($inv, ['currency_name','currency'], '');
        ?>
        <tr>
          <td><?php echo $id; ?></td>
          <td>
            <?php if ($id > 0) { ?>
              <a href="<?php echo admin_url('invoices/list_invoices/' . $id); ?>" target="_blank">
                <?php echo function_exists('format_invoice_number') ? format_invoice_number($id) : ('INV-' . $id); ?>
              </a>
            <?php } else { echo '-'; } ?>
          </td>
          <td><?php echo $date ? _d($date) : '-'; ?></td>
          <td><?php echo function_exists('app_format_money') ? app_format_money($total, $currency) : html_escape((string)$total); ?></td>
          <td><?php echo function_exists('format_invoice_status') ? format_invoice_status($status) : html_escape((string)$status); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
<?php } ?>
