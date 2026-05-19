<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div class="content">

<h3>BÁO CÁO NÂNG CAO</h3>

<div class="row">

    <div class="col-md-3">
        <div class="panel panel-primary">
            <div class="panel-body text-center">
                <h3><?= $kpi['total_jobs'] ?></h3>
                <p>Đơn tuyển</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-success">
            <div class="panel-body text-center">
                <h3><?= $kpi['total_apps'] ?></h3>
                <p>Ứng viên</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-info">
            <div class="panel-body text-center">
                <h3><?= $kpi['passed'] ?></h3>
                <p>Trúng tuyển</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="panel panel-warning">
            <div class="panel-body text-center">
                <h3><?= $kpi['rate'] ?>%</h3>
                <p>Tỷ lệ đậu</p>
            </div>
        </div>
    </div>

</div>

<hr>

<h4>Pipeline</h4>

<?php foreach($pipeline as $stage => $total): ?>
    <p><strong><?= ucfirst($stage) ?>:</strong> <?= $total ?></p>
<?php endforeach; ?>

<hr>

<h4>Ứng viên theo tháng</h4>
<canvas id="monthlyChart"></canvas>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
var ctx = document.getElementById('monthlyChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($monthly,'month')) ?>,
        datasets: [{
            label: 'Ứng viên',
            data: <?= json_encode(array_column($monthly,'total')) ?>
        }]
    }
});
</script>

<?php init_tail(); ?>