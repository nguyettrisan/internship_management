<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">
        <h4><i class="fa fa-bar-chart"></i> <?= $title; ?></h4>
        <hr>

        <!-- Bộ lọc năm -->
        <form method="get" action="">
          <div class="row">
            <div class="col-md-3">
              <label>Chọn năm</label>
              <select name="year" class="form-control" onchange="this.form.submit()">
                <?php foreach ($years as $y): ?>
                  <option value="<?= $y ?>" <?= ($year == $y ? 'selected' : '') ?>><?= $y ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </form>

        <hr>

        <!-- Tổng số -->
        <h4>Tổng số học sinh trong năm <?= $year ?>: <b><?= $total ?></b></h4>

        <!-- Biểu đồ theo tháng -->
        <canvas id="chartMonthly" height="100"></canvas>
        <br>

        <!-- Thống kê trạng thái -->
        <h5><i class="fa fa-list"></i> Thống kê theo trạng thái</h5>
        <table class="table table-bordered">
          <thead>
            <tr><th>Trạng thái</th><th class="text-right">Số lượng</th></tr>
          </thead>
          <tbody>
            <?php foreach ($status_counts as $st => $cnt): ?>
              <tr>
                <td><?= $st ?></td>
                <td class="text-right"><?= $cnt ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chartMonthly');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Th1','Th2','Th3','Th4','Th5','Th6','Th7','Th8','Th9','Th10','Th11','Th12'],
    datasets: [{
      label: 'Số học sinh nhập cảnh trong năm <?= $year ?>',
      data: <?= json_encode(array_values($monthly_counts)) ?>,
      backgroundColor: 'rgba(54, 162, 235, 0.5)',
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 1
    }]
  },
  options: {
    scales: { y: { beginAtZero: true } }
  }
});
</script>
</body>
</html>
