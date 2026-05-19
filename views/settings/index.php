<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div class="content">
    <div class="panel_s">
        <div class="panel-body">

            <h4 class="bold"><i class="fa fa-robot"></i> Cài đặt AI – Google Gemini</h4>
            <hr>

            <?= form_open(); ?>

            <div class="form-group">
                <label>Google Gemini API KEY</label>
                <input type="text" class="form-control"
                       name="intern_api_gemini_key"
                       value="<?= get_option('intern_api_gemini_key'); ?>"
                       placeholder="Nhập API KEY tại aistudio.google.com">
            </div>

            <div class="form-group">
                <label>Dung lượng cho phép upload (MB)</label>
                <input type="number" class="form-control"
                       name="intern_ai_upload_limit"
                       value="<?= get_option('intern_ai_upload_limit') ?: 10; ?>">
            </div>

            <div class="form-group">
                <label><input type="checkbox" name="intern_ai_enabled" value="1"
                    <?= get_option('intern_ai_enabled') == 1 ? 'checked' : '' ?>>
                    Kích hoạt AI Extract Đơn Tuyển
                </label>
            </div>

            <button class="btn btn-success"><i class="fa fa-save"></i> Lưu cài đặt</button>

            <?= form_close(); ?>

        </div>
    </div>
</div>

<?php init_tail(); ?>