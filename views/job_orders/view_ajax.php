<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// Guard: controller must pass $job as array
if (!isset($job) || empty($job) || !is_array($job)) {
    echo '<div class="alert alert-danger">Không tìm thấy dữ liệu đơn tuyển.</div>';
    return;
}

// Hàm get an toàn
if (!function_exists('jo_get')) {
    function jo_get($arr, $key, $default = '—')
    {
        return (isset($arr[$key]) && $arr[$key] !== '' && $arr[$key] !== null)
            ? $arr[$key]
            : $default;
    }
}

// Xử lý ngành: ưu tiên VI -> JP -> major
$major = jo_get($job, 'major_vi',
            jo_get($job, 'major_jp',
                jo_get($job, 'major', 'Không rõ')
            )
        );

// Số lượng
$quantity_total = !empty($job['quantity'])
                    ? $job['quantity']
                    : jo_get($job, 'quantity_total', 0);
?>

<style>

/* =====================================================
   IFK POPUP STYLE - OFFICIAL
   Scoped: .popup-wrap
   ===================================================== */

.popup-wrap{
    --ifk-navy:#00325a;
    --ifk-green:#96bc17;
    --ifk-cyan:#00a6dc;
    --ifk-soft:rgba(0,166,220,.08);
    --ifk-border:rgba(0,50,90,.15);
    --ifk-text:#1e293b;
    --ifk-muted:#64748b;

    font-family: inherit; /* dùng font CRM */
    color: var(--ifk-text);
}

/* ===== TIÊU ĐỀ ===== */

.popup-title{
    font-size:20px;
    font-weight:700;
    margin-bottom:4px;
    color:var(--ifk-navy);
}

.popup-sub{
    font-size:13px;
    color:var(--ifk-muted);
    margin-bottom:10px;
}

/* ===== ĐỊA CHỈ ===== */

.popup-address{
    font-size:13px;
    color:#475569;
    margin-bottom:16px;
}

/* ===== SECTION BOX ===== */

.section-box{
    background:#ffffff;
    padding:14px 18px;
    border-radius:12px;
    margin-bottom:18px;
    border:1px solid var(--ifk-border);
    box-shadow:0 4px 12px rgba(0,50,90,.06);
}

/* NHÃN TRÁI */

.label-strong{
    font-weight:600;
    width:150px;
    display:inline-block;
    color:var(--ifk-navy);
}

/* GIÁ TRỊ */

.value-text{
    font-weight:500;
    color:var(--ifk-text);
}

/* ===== BADGE NGÀNH ===== */

.badge-major{
    display:inline-block;
    padding:6px 14px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
    color:#ffffff;
    margin-top:4px;
}

/* Mapping màu theo IFK */

.badge-blue   { background: var(--ifk-cyan); }    /* Nhật Bản học */
.badge-green  { background: var(--ifk-green); }   /* Du lịch */
.badge-purple { background: #5b3cc4; }            /* Điều dưỡng */
.badge-orange { background: #d97706; }            /* Logistic */
.badge-cyan   { background: var(--ifk-navy); }    /* IT */
.badge-gray   { background: #475569; }            /* Default */

/* Hover nhẹ cho badge */

.badge-major:hover{
    opacity:.9;
    transition:.15s ease;
}

</style>

<div>

    <!-- ====== TIÊU ĐỀ ====== -->
    <div class="popup-title">
        <?= html_escape(jo_get($job, 'company_name_vi', '(Không rõ tên công ty)')) ?>
    </div>

    <div class="popup-sub">
        <?= html_escape(jo_get($job, 'company_name_jp', '')); ?>
    </div>

    <?php if (!empty($job['address_vi']) || !empty($job['address_jp'])) : ?>
        <div class="popup-sub">
            <i class="fa fa-map-marker"></i>
            <?= html_escape(jo_get($job, 'address_vi', jo_get($job, 'address_jp', '—'))); ?>
        </div>
    <?php endif; ?>

    <!-- ====== THÔNG TIN CHUNG ====== -->
    <div class="section-box">
        <div>
            <span class="label-strong">Ngành tuyển:</span>
            <span class="value-text">
                <?= html_escape($major); ?>
            </span>
        </div>

        <div>
            <span class="label-strong">Số lượng:</span>
            <span class="value-text">
                Nam: <?= (int)jo_get($job, 'quantity_male', 0); ?> /
                Nữ: <?= (int)jo_get($job, 'quantity_female', 0); ?> /
                Tổng: <?= (int)$quantity_total; ?>
            </span>
        </div>

        <div>
            <span class="label-strong">Lương thực nhận:</span>
            <span class="value-text">
                <?= !empty($job['salary_net']) ? number_format($job['salary_net']) . " Yên" : "—"; ?>
            </span>
        </div>

        <div>
            <span class="label-strong">Thời gian tuyển:</span>
            <span class="value-text">
                <?= jo_get($job, 'contract_months', 0); ?> tháng
            </span>
        </div>
    </div>

    <!-- ====== LỊCH TRÌNH ====== -->
    <div class="section-box">
        <div>
            <span class="label-strong">Ngày phỏng vấn:</span>
            <span class="value-text">
                <?= !empty($job['interview_date']) ? _d($job['interview_date']) : '—'; ?>
            </span>
        </div>

        <div>
            <span class="label-strong">Nhập cảnh dự kiến:</span>
            <span class="value-text">
                <?= !empty($job['entry_date']) ? _d($job['entry_date']) : '—'; ?>
            </span>
        </div>

        <div>
            <span class="label-strong">Ngày về nước:</span>
            <span class="value-text">
                <?= !empty($job['return_date']) ? _d($job['return_date']) : '—'; ?>
            </span>
        </div>
    </div>
    
    <!-- -->
    <!-- ====== TRƯỜNG NHẬN ĐƠN ====== -->
    <div class="section-box">
        <span class="label-strong">Trường đã gửi đơn:</span>
        <span class="value-text">
            <?= !empty($job['sent_school_text']) ? html_escape($job['sent_school_text']) : 'Chưa chọn trường'; ?>
        </span>
    </div>
    
    

    <!-- ====== TRẠNG THÁI ====== -->
    <!-- <div class="section-box">
        <span class="label-strong">Trạng thái đơn:</span>
        <span class="value-text">
            <?= html_escape(jo_get($job, 'status_label', 'Tiếp nhận đơn')); ?>
        </span>
    </div> -->
    
    <?php $statusColor = im_job_order_status_color(jo_get($job, 'status', 'received')); ?>
    <div class="status-pill status-<?= html_escape($statusColor); ?>">
        <span class="status-dot"></span>
        <?= html_escape(im_job_order_status_label(jo_get($job, 'status', 'received'), 'vi')); ?>
    </div>


    <!-- ====== CRM & HÀNH ĐỘNG ====== -->
    <div class="section-box">
        <div class="row">
            <div class="col-md-6">
                <span class="label-strong">CRM:</span>
                <?php if (!empty($job['crm_client_id'])) { ?>
                    <span class="label label-success">Đã liên kết #<?= (int)$job['crm_client_id']; ?></span>
                    <a href="<?= admin_url('clients/client/' . (int)$job['crm_client_id']); ?>" class="ml-5">
                        Xem CRM
                    </a>
                <?php } else { ?>
                    <span class="label label-default">Chưa liên kết</span>
                <?php } ?>
            </div>

            <div class="col-md-6 text-right">
                <span class="label-strong">ID đơn:</span>
                <span class="value-text">#<?= (int)jo_get($job, 'id', 0); ?></span>
            </div>
        </div>

        <hr class="m0 mtop10 mbot10">

        <div class="text-center">
            <a href="<?= admin_url('internship_management/internship_job_orders/profile/' . (int)$job['id']); ?>"
               class="btn btn-info mright5">
                <i class="fa fa-folder-open"></i> Hồ sơ
            </a>

            <a href="<?= admin_url('internship_management/internship_job_orders/view/' . (int)$job['id']); ?>"
               class="btn btn-primary mright5">
                <i class="fa fa-eye"></i> Xem chi tiết
            </a>

            <a href="<?= admin_url('internship_management/internship_job_orders/print/' . (int)$job['id']); ?>"
               target="_blank" class="btn btn-warning mright5">
                <i class="fa fa-print"></i> In
            </a>

            <?php if (empty($job['crm_client_id'])) { ?>
                <a href="<?= admin_url('internship_management/internship_job_orders/push_crm/' . (int)$job['id']); ?>"
                   class="btn btn-success mright5">
                    <i class="fa fa-cloud-upload"></i> Đẩy CRM
                </a>
            <?php } ?>

            <a href="<?= admin_url('internship_management/internship_job_orders/edit/' . (int)$job['id']); ?>"
               class="btn btn-default mright5">
                <i class="fa fa-pencil"></i> Sửa
            </a>

            <a href="<?= admin_url('internship_management/internship_job_orders/delete/' . (int)$job['id']); ?>"
               class="btn btn-danger _delete">
                <i class="fa fa-trash"></i> Xoá
            </a>
        </div>
    </div>

</div>