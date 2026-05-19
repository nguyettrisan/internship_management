<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="ifk-report-wrap">

    <div class="ifk-report-box">

        <div class="ifk-box-head">
            <h4>Báo cáo quản trị theo doanh nghiệp</h4>
        </div>

        <?php if (!empty($job_orders)): ?>

        <div class="table-responsive">
            <table class="table ifk-table">
                <thead>
                    <tr>
                        <th>Doanh nghiệp</th>
                        <th>Ngành</th>
                        <th>Số lượng tuyển</th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($job_orders as $row): ?>
                    <tr>
                        <td><?php echo isset($row['company_name']) ? $row['company_name'] : ''; ?></td>
                        <td><?php echo isset($row['industry']) ? $row['industry'] : ''; ?></td>
                        <td><?php echo isset($row['quantity']) ? $row['quantity'] : 0; ?></td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <?php else: ?>

            <div class="alert alert-warning">
                Không có dữ liệu báo cáo quản trị.
            </div>

        <?php endif; ?>

    </div>

</div>