<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$total_jobs      = isset($job_orders) ? count($job_orders) : 0;
$total_apps      = isset($applications) ? count($applications) : 0;
$total_students  = isset($students) ? count($students) : 0;
$interview_pass  = isset($progress_stats['interview']) ? $progress_stats['interview'] : 0;
$entry_total     = isset($progress_stats['entry']) ? $progress_stats['entry'] : 0;
?>

<div id="ifk-reports-page" class="ifk-report-wrap">

    <!-- KPI CARDS -->
    <div class="row">

        <div class="col-md-3">
            <div class="ifk-kpi-card kpi-navy">
                <div class="kpi-accent"></div>
                <p class="kpi-title">ĐƠN TUYỂN</p>
                <h3 class="kpi-value"><?php echo $total_jobs; ?></h3>
                <div class="kpi-sub">Tổng số job orders</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="ifk-kpi-card kpi-sky">
                <div class="kpi-accent"></div>
                <p class="kpi-title">ỨNG TUYỂN</p>
                <h3 class="kpi-value"><?php echo $total_apps; ?></h3>
                <div class="kpi-sub">Tổng số lượt apply</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="ifk-kpi-card kpi-green">
                <div class="kpi-accent"></div>
                <p class="kpi-title">PHỎNG VẤN ĐẠT</p>
                <h3 class="kpi-value"><?php echo $interview_pass; ?></h3>
                <div class="kpi-sub">Đã pass PV</div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="ifk-kpi-card kpi-amber">
                <div class="kpi-accent"></div>
                <p class="kpi-title">NHẬP CẢNH</p>
                <h3 class="kpi-value"><?php echo $entry_total; ?></h3>
                <div class="kpi-sub">Đã xuất cảnh</div>
            </div>
        </div>

    </div>


    <!-- DANH SÁCH ĐƠN TUYỂN -->
    <div class="ifk-report-box">

        <div class="ifk-box-head">
            <h4>Danh sách đơn tuyển gần nhất</h4>
        </div>

        <?php if (!empty($job_orders)): ?>

            <div class="table-responsive">
                <table class="table ifk-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Doanh nghiệp</th>
                            <th>Ngành</th>
                            <th>SL Tuyển</th>
                            <th>Ngày PV</th>
                            <th>Nhập cảnh</th>
                            <th>Về nước</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($job_orders as $job): ?>
                        <tr>
                            <td><?php echo $job['id'] ?? ''; ?></td>
                            <td><?php echo $job['company_name'] ?? ''; ?></td>
                            <td><?php echo $job['industry'] ?? ''; ?></td>
                            <td><?php echo $job['quantity'] ?? 0; ?></td>
                            <td><?php echo $job['interview_date'] ?? ''; ?></td>
                            <td><?php echo $job['entry_date'] ?? ''; ?></td>
                            <td><?php echo $job['return_date'] ?? ''; ?></td>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="alert alert-info">
                Không có dữ liệu hiển thị.
            </div>

        <?php endif; ?>

    </div>


    <!-- PIPELINE THEO TRẠNG THÁI -->
    <div class="ifk-report-box">

        <div class="ifk-box-head">
            <h4>Pipeline theo trạng thái</h4>
        </div>

        <div class="ifk-pipe-row">

            <div class="ifk-pipe-item">
                <div class="ifk-pipe-top">
                    <strong>Ứng tuyển</strong>
                    <span class="status-badge st-blue"><?php echo $total_apps; ?></span>
                </div>
            </div>

            <div class="ifk-pipe-item">
                <div class="ifk-pipe-top">
                    <strong>Đạt PV</strong>
                    <span class="status-badge st-green"><?php echo $interview_pass; ?></span>
                </div>
            </div>

            <div class="ifk-pipe-item">
                <div class="ifk-pipe-top">
                    <strong>Nhập cảnh</strong>
                    <span class="status-badge st-yellow"><?php echo $entry_total; ?></span>
                </div>
            </div>

            <div class="ifk-pipe-item">
                <div class="ifk-pipe-top">
                    <strong>Sinh viên</strong>
                    <span class="status-badge st-gray"><?php echo $total_students; ?></span>
                </div>
            </div>

        </div>

    </div>

</div>