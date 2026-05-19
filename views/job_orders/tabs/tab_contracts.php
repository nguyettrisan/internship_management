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

$contracts = isset($contracts) && is_array($contracts) ? $contracts : (isset($contracts) && $contracts ? (array)$contracts : []);
$crm_client_id = isset($crm_client_id) ? (int)$crm_client_id : 0;
?>

<?php if ($crm_client_id <= 0) { ?>
  <div class="alert alert-warning">
    <i class="fa fa-cloud"></i> Chưa liên kết CRM nên chưa thể hiển thị hợp đồng.
  </div>
<?php } elseif (empty($contracts)) { ?>
  <div class="alert alert-info">
    <i class="fa fa-info-circle"></i> Chưa có hợp đồng cho khách hàng CRM này.
  </div>
<?php } else { ?>
  <div class="table-responsive">
    <table id="tbl_job_tab_contracts" class="table dt-table">
      <thead>
        <tr>
          <th style="width:70px;">#</th>
          <th><?php echo _l('Hợp đồng'); ?></th>
          <th style="width:160px;"><?php echo _l('Giá trị'); ?></th>
          <th style="width:140px;"><?php echo _l('Ngày'); ?></th>
          <th style="width:90px;"><?php echo _l('Xem'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($contracts as $ct) {
            $id = (int)im_row_pick_any($ct, ['id'], 0);
            $subject = (string)im_row_pick_any($ct, ['subject','name','title'], 'Contract #' . $id);
            $val = im_row_pick_any($ct, ['contract_value','value','amount'], 0);
            $date = (string)im_row_pick_any($ct, ['datestart','dateadded','created_at','start_date'], '');
        ?>
        <tr>
          <td><?php echo $id; ?></td>
          <td><?php echo html_escape($subject); ?></td>
          <td><?php echo function_exists('app_format_money') ? app_format_money($val, (string)im_row_pick_any($ct, ['currency','currency_name'], '')) : html_escape((string)$val); ?></td>
          <td><?php echo $date ? _d($date) : '-'; ?></td>
          <td>
            <?php if ($id > 0) { ?>
              <a class="btn btn-xs btn-default" href="<?php echo admin_url('contracts/contract/' . $id); ?>" target="_blank">
                <i class="fa fa-eye"></i>
              </a>
            <?php } else { echo '-'; } ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
<?php } ?>
