<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$tab = isset($tab) ? (string)$tab : 'job_orders';
$filters = isset($filters) && is_array($filters) ? $filters : [];
?>
<!-- KEEP YOUR EXISTING SUMMARY UI HERE.
     This patch isolates SUMMARY vs MANAGEMENT so charts won't leak across. -->
<div class="alert alert-info">
  <strong>Summary section:</strong> giữ nguyên UI báo cáo tổng hợp (đơn tuyển / ứng tuyển / tiến độ) như hiện tại.
  <br>Chỉ cần đảm bảo các phần dashboard/charts của summary nằm trong section này.
</div>
