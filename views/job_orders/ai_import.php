<?php init_head(); ?>

<div class="content">
    <div class="panel_s">
        <div class="panel-body">

            <h4><i class="fa fa-magic"></i> AI Nhập Đơn Tuyển</h4>
            <hr>

            <?= form_open_multipart(); ?>

            <div class="form-group">
                <label>Upload File Word (JP)</label>
                <input type="file" name="file" class="form-control" required>
            </div>

            <button class="btn btn-primary">
                <i class="fa fa-upload"></i> Tải lên & Phân tích
            </button>

            <?= form_close(); ?>

            <?php if (!empty($json)): ?>
                <hr>
                <h4>Kết quả phân tích:</h4>
                <pre><?= json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php init_tail(); ?>