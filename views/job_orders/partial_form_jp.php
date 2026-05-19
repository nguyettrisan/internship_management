<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<style>
.form-section-title {
    font-weight: bold;
    font-size: 17px;
    padding-bottom: 5px;
    border-bottom: 2px solid #e5e5e5;
    margin: 25px 0 15px;
}
</style>

<div class="row">

    <!-- ===============================
         1) 会社情報
    ================================ -->
    <div class="col-md-12">
        <div class="form-section-title">① 会社情報</div>
    </div>

    <div class="col-md-6">
        <?= render_input('company_name_jp', '会社名（日本語）', $job['company_name_jp'] ?? ''); ?>
    </div>

    <div class="col-md-6">
        <?= render_input('company_name_vi', '会社名（ベトナム語）', $job['company_name_vi'] ?? ''); ?>
    </div>

    <div class="col-md-12">
        <?= render_textarea('address', '住所', $job['address'] ?? ''); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('contact_name', '担当者名', $job['contact_name'] ?? ''); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('contact_phone', '電話番号', $job['contact_phone'] ?? ''); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('contact_email', 'メールアドレス', $job['contact_email'] ?? ''); ?>
    </div>



    <!-- ===============================
         2) 仕事内容（業務内容）
    ================================ -->
    <div class="col-md-12">
        <div class="form-section-title">② 仕事内容（業務内容）</div>
    </div>

    <!-- 職種分類 -->
    <div class="col-md-6">
        <?php
            $job_categories = [
                ['id' => 1, 'name' => '飲食・ホテル'],
                ['id' => 2, 'name' => '介護'],
                ['id' => 3, 'name' => '物流'],
            ];
            echo render_select(
                'job_category',
                $job_categories,
                ['id', 'name'],
                '職種分類',
                $job['job_category'] ?? ''
            );
        ?>
    </div>

    <!-- 募集人数 -->
    <div class="col-md-6">
        <?= render_input('quantity', '募集人数', $job['quantity'] ?? 1, 'number'); ?>
    </div>

    <!-- 業務内容詳細 -->
    <div class="col-md-12">
        <?= render_textarea('job_description', '仕事内容（詳細）', $job['job_description'] ?? ''); ?>
    </div>



    <!-- ===============================
         3) 応募条件
    ================================ -->
    <div class="col-md-12">
        <div class="form-section-title">③ 応募条件</div>
    </div>

    <!-- 専攻分野 -->
    <div class="col-md-6">
        <?php
            $majors = [
                ['id' => '日本語学科', 'name' => '日本語学科'],
                ['id' => '観光学科', 'name' => '観光学科'],
                ['id' => 'ホテル・レストラン学科', 'name' => 'ホテル・レストラン学科'],
                ['id' => '看護・介護学科', 'name' => '看護・介護学科'],
                ['id' => '物流学科', 'name' => '物流学科'],
            ];
            echo render_select(
                'major',
                $majors,
                ['id', 'name'],
                '専攻分野',
                $job['major'] ?? ''
            );
        ?>
    </div>

    <!-- 日本語レベル -->
    <div class="col-md-4">
        <?php
            $jp_levels = [
                ['id' => 'N5', 'name' => 'N5'],
                ['id' => 'N4', 'name' => 'N4'],
                ['id' => 'N3', 'name' => 'N3'],
                ['id' => 'N2', 'name' => 'N2'],
                ['id' => 'N1', 'name' => 'N1'],
            ];
            echo render_select(
                'japanese_level',
                $jp_levels,
                ['id', 'name'],
                '日本語能力（Nレベル）',
                $job['japanese_level'] ?? ''
            );
        ?>
    </div>

    <div class="col-md-2">
        <label>日本語証明書 必須？</label><br>
        <input type="checkbox" name="japanese_certificate" value="1"
               <?= !empty($job['japanese_certificate']) ? 'checked' : '' ?>>
        <span>証明書必要</span>
    </div>

    <!-- 英語レベル -->
    <div class="col-md-6">
        <?= render_input('english_level', '英語レベル', $job['english_level'] ?? ''); ?>
    </div>



    <!-- ===============================
         4) 給与・福利厚生
    ================================ -->
    <div class="col-md-12">
        <div class="form-section-title">④ 給与・福利厚生</div>
    </div>

    <div class="col-md-4">
        <?= render_input('salary_total', '総支給額（¥）', $job['salary_total'] ?? '', 'number'); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('tax', '税金（¥）', $job['tax'] ?? '', 'number'); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('dormitory', '寮費（¥）', $job['dormitory'] ?? '', 'number'); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('food', '食費（¥）', $job['food'] ?? '', 'number'); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('utilities', '光熱費（¥）', $job['utilities'] ?? '', 'number'); ?>
    </div>

    <div class="col-md-4">
        <?= render_input('insurance', '保険料（¥）', $job['insurance'] ?? '', 'number'); ?>
    </div>

    <div class="col-md-12">
        <?= render_input('salary_net', '手取り額（¥）', $job['salary_net'] ?? '', 'number'); ?>
    </div>



    <!-- ===============================
         5) その他のサポート
    ================================ -->
    <div class="col-md-12">
        <div class="form-section-title">⑤ 交通費・その他サポート</div>
    </div>

    <div class="col-md-6">
        <?php
            $benefits = [
                ['id' => '支援なし', 'name' => '支援なし'],
                ['id' => 'ベトナム→日本 航空券支援', 'name' => 'ベトナム→日本 航空券支援'],
                ['id' => '日本国内航空券支援', 'name' => '日本国内航空券支援'],
            ];
            echo render_select(
                'benefit_flight',
                $benefits,
                ['id', 'name'],
                '航空券サポート',
                $job['benefit_flight'] ?? ''
            );
        ?>
    </div>

    <div class="col-md-6">
        <?= render_textarea('benefit_other', 'その他のサポート', $job['benefit_other'] ?? ''); ?>
    </div>



    <!-- ===============================
         6) 面接・入国予定
    ================================ -->
    <div class="col-md-12">
        <div class="form-section-title">⑥ 面接・入国予定日</div>
    </div>

    <div class="col-md-6">
        <?= render_date_input('interview_date', '面接予定日', $job['interview_date'] ?? ''); ?>
    </div>

    <div class="col-md-6">
        <?= render_date_input('entry_date', '入国予定日', $job['entry_date'] ?? ''); ?>
    </div>

</div>