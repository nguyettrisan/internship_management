<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Hóa đơn internship - <?= html_escape($invoice['invoice_code'] ?? ''); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('uploads/company/favicon.png'); ?>">

    <!-- Bootstrap + Font Awesome có sẵn trong Perfex -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/bootstrap/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/font-awesome/css/font-awesome.min.css'); ?>">

    <style>
        :root {
            --ifk-bg:        #f3f4f6;
            --ifk-card:      #ffffff;
            --ifk-border:    #e5e7eb;
            --ifk-primary:   #111827;
            --ifk-primary-d: #020617;
            --ifk-muted:     #6b7280;
            --ifk-soft:      #f9fafb;
            --ifk-accent:    #fefce8;
            --ifk-radius-lg: 16px;
            --ifk-radius-md: 12px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--ifk-bg);
            font-size: 14px;
            color: #111827;
            -webkit-font-smoothing: antialiased;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
                         "Helvetica Neue", Arial, sans-serif;
        }

        .invoice-page-wrapper {
            max-width: 1060px;
            margin: 28px auto 40px;
            padding: 0 12px;
        }

        /* ===== BRAND HEADER ===== */
        .brand-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }
        .brand-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .brand-logo-wrap {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(15, 23, 42, .10);
        }
        .brand-logo {
            max-width: 32px;
            max-height: 32px;
            object-fit: contain;
        }
        .brand-name {
            font-weight: 800;
            font-size: 18px;
            letter-spacing: .03em;
        }
        .brand-sub {
            font-size: 11px;
            color: var(--ifk-muted);
        }
        .brand-right {
            text-align: right;
            font-size: 12px;
            color: var(--ifk-muted);
        }

        /* ===== MAIN CARD ===== */
        .invoice-shell {
            background: var(--ifk-card);
            border-radius: var(--ifk-radius-lg);
            box-shadow:
                0 24px 60px rgba(15, 23, 42, .12),
                0 2px 6px rgba(15, 23, 42, .04);
            padding: 26px 28px 22px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            gap: 22px;
            margin-bottom: 24px;
        }
        .invoice-title-block { flex: 1; }
        .invoice-main-title {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .invoice-code-line {
            font-size: 13px;
            color: var(--ifk-muted);
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 11px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }
        .badge-status-unpaid { background: #fef3c7; color: #92400e; }
        .badge-status-paid   { background: #dcfce7; color: #166534; }
        .badge-status-other  { background: #e5e7eb; color: #374151; }

        .info-label-compact {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #9ca3af;
        }
        .info-value-compact {
            font-size: 13px;
            font-weight: 600;
        }
        .invoice-meta-block {
            text-align: right;
            font-size: 13px;
            min-width: 180px;
        }

        .mtop5 { margin-top: 5px; }
        .mtop10{ margin-top: 10px; }

        /* ===== STUDENT CARD ===== */
        .student-card {
            display: flex;
            gap: 16px;
            align-items: center;
            padding: 14px 16px;
            background: var(--ifk-soft);
            border-radius: var(--ifk-radius-md);
            margin-top: 10px;
            margin-bottom: 22px;
            border: 1px dashed #e5e7eb;
        }
        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 999px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            background: #ffffff;
        }
        .student-name {
            font-size: 16px;
            font-weight: 700;
        }
        .student-meta {
            font-size: 12px;
            color: var(--ifk-muted);
        }

        /* ===== SECTION TITLES ===== */
        .section-title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .section-title span.icon {
            width: 22px;
            height: 22px;
            border-radius: 999px;
            background: #eff6ff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #1d4ed8;
        }
        .info-row {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            font-size: 13px;
            margin-bottom: 6px;
        }
        .info-col { min-width: 160px; }
        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #9ca3af;
        }
        .info-value { font-weight: 600; }

        .invoice-note {
            font-size: 13px;
            color: #4b5563;
            padding: 10px 12px;
            border-radius: 10px;
            background: #f9fafb;
            border: 1px dashed #e5e7eb;
        }

        /* ===== ITEMS TABLE ===== */
        .invoice-items-table { margin-top: 8px; margin-bottom: 8px; }
        .invoice-items-table > thead > tr > th {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            border-top: none;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
        }
        .invoice-items-table tbody tr td {
            vertical-align: top;
            font-size: 13px;
        }
        .item-name { font-weight: 600; }
        .item-desc {
            font-size: 12px;
            margin-top: 2px;
            color: #6b7280;
        }

        /* ===== TOTAL & PAYMENT LAYOUT ===== */
        .totals-and-payment {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 16px;
        }
        .totals-box {
            flex: 1 1 260px;
            max-width: 330px;
            margin-left: auto;
            border-radius: var(--ifk-radius-md);
            border: 1px solid var(--ifk-border);
            padding: 12px 16px;
            background: var(--ifk-soft);
        }
        .totals-box table { margin-bottom: 0; }
        .totals-box td {
            font-size: 13px;
            padding: 4px 0;
        }
        .totals-label { color: var(--ifk-muted); }
        .totals-value {
            text-align: right;
            font-weight: 600;
        }
        .totals-grand {
            font-size: 15px;
            font-weight: 800;
        }

        /* ===== PAYMENT CARD ===== */
        .payment-card {
            flex: 1 1 260px;
            min-width: 260px;
            border-radius: var(--ifk-radius-md);
            border: 1px solid var(--ifk-border);
            padding: 12px 16px 10px;
            background: var(--ifk-accent);
        }
        .payment-title-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .payment-badge {
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            background: rgba(34, 197, 94, .12);
            color: #15803d;
            font-weight: 600;
            white-space: nowrap;
        }
        .payment-modes-list {
            max-height: 170px;
            overflow-y: auto;
            margin-bottom: 10px;
        }
        .payment-mode-item {
            padding: 7px 0;
            border-bottom: 1px dashed #e5e7eb;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: background .18s ease, transform .12s ease;
        }
        .payment-mode-item:hover {
            background: rgba(250, 250, 250, .8);
            transform: translateX(1px);
        }
        .payment-mode-item:last-child { border-bottom: none; }
        .payment-logo {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            object-fit: contain;
            background: #ffffff;
            border: 1px solid #e5e7eb;
        }
        .payment-mode-name { font-weight: 600; }
        .payment-mode-desc {
            font-size: 11px;
            color: var(--ifk-muted);
        }
        .payment-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 6px;
        }
        .btn-primary-2025 {
            background: var(--ifk-primary);
            border-color: var(--ifk-primary);
            border-radius: 999px;
            padding: 7px 18px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .22);
        }
        .btn-primary-2025:hover {
            background: var(--ifk-primary-d);
            border-color: var(--ifk-primary-d);
        }
        .btn-outline-2025 {
            border-radius: 999px;
            padding: 7px 15px;
            font-size: 13px;
        }
        .note-block {
            margin-top: 14px;
            font-size: 12px;
            color: var(--ifk-muted);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .invoice-shell { padding: 18px 16px; }
            .invoice-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .invoice-meta-block { text-align: left; }
            .totals-and-payment { flex-direction: column-reverse; }
            .totals-box {
                margin-left: 0;
                max-width: 100%;
            }
        }

        @media print {
            body { background: #ffffff; }
            .invoice-page-wrapper {
                margin: 0;
                max-width: 100%;
                padding: 0;
            }
            .brand-header,
            .payment-card,
            .btn,
            .payment-actions { display: none !important; }
            .invoice-shell {
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
<?php
// ===== HELPER FORMAT TIỀN =====
if (!function_exists('format_vn_money')) {
    function format_vn_money($amount)
    {
        $amount = (float) $amount;
        return number_format($amount, 0, ',', '.');
    }
}

// ===== NORMALIZE DATA =====
$invoice       = $invoice ?? [];
$student       = $student ?? [];
$items         = isset($items) && is_array($items) ? $items : [];
$payment_modes = isset($payment_modes) && is_array($payment_modes) ? $payment_modes : [];

$subtotal  = (float)($invoice['subtotal']  ?? 0);
$tax_total = (float)($invoice['tax_total'] ?? 0);
$total     = (float)($invoice['total']     ?? ($subtotal + $tax_total));

$status      = $invoice['status'] ?? 'draft';
$statusLabel = 'Nháp';
$statusClass = 'badge-status-other';

if ($status === 'unpaid') {
    $statusLabel = 'Chưa thanh toán';
    $statusClass = 'badge-status-unpaid';
} elseif ($status === 'paid') {
    $statusLabel = 'Đã thanh toán';
    $statusClass = 'badge-status-paid';
} elseif ($status === 'cancelled') {
    $statusLabel = 'Đã hủy';
    $statusClass = 'badge-status-other';
}

$invoice_code    = $invoice['invoice_code'] ?? '';
$raw_invoice_date = $invoice['invoice_date'] ?? $invoice['date'] ?? null;
$invoice_date     = $raw_invoice_date ? _d($raw_invoice_date) : '—';

$raw_due_date = $invoice['due_date'] ?? null;
$due_date     = $raw_due_date ? _d($raw_due_date) : '—';
?>

<div class="invoice-page-wrapper">

    <!-- BRAND HEADER -->
    <div class="brand-header">
        <div class="brand-left">
            <div class="brand-logo-wrap">
                <img class="brand-logo"
                     src="<?= base_url('uploads/company/f31a955528a927060f926976605f3d1b.png'); ?>"
                     onerror="this.src='<?= base_url('uploads/company/logo.png'); ?>';">
            </div>
            <div>
                <div class="brand-name">IFK Internship Center</div>
                <div class="brand-sub">Hệ thống hóa đơn internship trực tuyến</div>
            </div>
        </div>

        <div class="brand-right">
            <div>Mã hóa đơn: <strong><?= html_escape($invoice_code); ?></strong></div>
            <div>Ngày xem: <?= _dt(date('Y-m-d H:i:s')); ?></div>
        </div>
    </div>

    <!-- MAIN CARD -->
    <div class="invoice-shell">

        <!-- HEADER -->
        <div class="invoice-header">
            <div class="invoice-title-block">
                <div class="invoice-main-title">Hóa đơn internship</div>
                <div class="invoice-code-line">
                    Mã: <strong><?= html_escape($invoice_code); ?></strong>
                    <span class="badge-status <?= $statusClass; ?>"><?= $statusLabel; ?></span>
                </div>

                <!-- STUDENT CARD -->
                <div class="student-card">
                    <img class="student-avatar"
                         src="<?= !empty($student['avatar'])
                                ? base_url('uploads/internship_avatar/' . $student['avatar'])
                                : base_url('modules/internship_management/assets/no-image.png'); ?>"
                         alt="Avatar">
                    <div>
                        <div class="student-name">
                            <?= html_escape($student['full_name'] ?? ''); ?>
                        </div>
                        <div class="student-meta">
                            Email: <?= html_escape($student['email'] ?? '—'); ?>
                            &nbsp;•&nbsp;
                            SĐT: <?= html_escape($student['phone_student'] ?? '—'); ?>
                        </div>
                        <div class="student-meta">
                            Mã sinh viên (ứng tuyển): #<?= (int)($student['id'] ?? 0); ?>
                            <?php if (!empty($student['school_name'])): ?>
                                &nbsp;•&nbsp; Trường: <?= html_escape($student['school_name']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="invoice-meta-block">
                <div>
                    <div class="info-label-compact">Ngày hóa đơn</div>
                    <div class="info-value-compact"><?= $invoice_date; ?></div>
                </div>
                <div class="mtop5">
                    <div class="info-label-compact">Hạn thanh toán</div>
                    <div class="info-value-compact"><?= $due_date; ?></div>
                </div>
                <div class="mtop5">
                    <div class="info-label-compact">Tổng tiền</div>
                    <div class="info-value-compact"><?= format_vn_money($total); ?> đ</div>
                </div>
            </div>
        </div><!-- /invoice-header -->

        <!-- INVOICE INFO -->
        <div class="section-title mtop10">
            <span class="icon"><i class="fa fa-info"></i></span>
            <span>Thông tin hóa đơn</span>
        </div>

        <div class="info-row">
            <div class="info-col">
                <div class="info-label">Mã hóa đơn</div>
                <div class="info-value"><?= html_escape($invoice_code); ?></div>
            </div>
            <div class="info-col">
                <div class="info-label">Ngày hóa đơn</div>
                <div class="info-value"><?= $invoice_date; ?></div>
            </div>
            <div class="info-col">
                <div class="info-label">Hạn thanh toán</div>
                <div class="info-value"><?= $due_date; ?></div>
            </div>
        </div>

        <?php if (!empty($invoice['content'])): ?>
            <div class="info-row mtop5">
                <div class="info-col" style="min-width:100%;">
                    <div class="info-label">Ghi chú từ IFK</div>
                    <div class="invoice-note">
                        <?= nl2br(html_escape($invoice['content'])); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <hr style="margin:16px 0 14px 0;">

        <!-- ITEMS TABLE -->
        <div class="section-title">
            <span class="icon"><i class="fa fa-list-ul"></i></span>
            <span>Chi tiết các khoản thu</span>
        </div>

        <div class="table-responsive">
            <table class="table invoice-items-table">
                <thead>
                    <tr>
                        <th style="width:34%">Sản phẩm / dịch vụ</th>
                        <th style="width:12%">Đơn vị</th>
                        <th style="width:10%" class="text-right">Số lượng</th>
                        <th style="width:14%" class="text-right">Đơn giá</th>
                        <th style="width:12%" class="text-right">Thuế (%)</th>
                        <th style="width:18%" class="text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($items)): ?>
                    <?php foreach ($items as $row): ?>
                        <tr>
                            <td>
                                <div class="item-name">
                                    <?= html_escape($row['item_name'] ?? ''); ?>
                                </div>
                                <?php if (!empty($row['description'])): ?>
                                    <div class="item-desc">
                                        <?= nl2br(html_escape($row['description'])); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?= html_escape($row['unit'] ?? ''); ?></td>
                            <td class="text-right"><?= (float)($row['qty'] ?? 0); ?></td>
                            <td class="text-right"><?= format_vn_money($row['rate'] ?? 0); ?></td>
                            <td class="text-right">
                                <?= isset($row['tax_rate']) ? (float)$row['tax_rate'] : 0; ?>%
                            </td>
                            <td class="text-right"><?= format_vn_money($row['amount'] ?? 0); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Chưa có dòng sản phẩm / dịch vụ nào.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- TOTAL + PAYMENT SECTION -->
        <div class="totals-and-payment">

            <!-- PAYMENT CARD -->
            <div class="payment-card">
                <div class="payment-title-row">
                    <div>
                        <div class="info-label">Thanh toán trực tuyến</div>
                        <div class="info-value" style="font-size:13px;">
                            Chọn phương thức thanh toán cho hóa đơn này.
                        </div>
                    </div>
                    <span class="payment-badge">
                        Tổng: <?= format_vn_money($total); ?> đ
                    </span>
                </div>

                <?php if (!empty($payment_modes)): ?>
                    <form method="post"
                          action="<?= site_url('internship_payment/pay/' . $invoice['id']); ?>"
                          id="payment-form">

                        <!-- CSRF -->
                        <input type="hidden"
                               name="<?= $this->security->get_csrf_token_name(); ?>"
                               value="<?= $this->security->get_csrf_hash(); ?>">

                        <div class="payment-modes-list">
                            <?php foreach ($payment_modes as $mode): ?>
                                <?php
                                    if (empty($mode['id'])) { continue; }
                                    $modeId   = $mode['id'];
                                    $modeName = $mode['name'] ?? $modeId;
                                    $modeDesc = $mode['description'] ?? '';
                                    $modeIcon = $mode['icon'] ?? base_url('uploads/payment_modes/' . $modeId . '.png');
                                ?>
                                <label class="payment-mode-item">
                                    <input type="radio"
                                           name="payment_mode_id"
                                           value="<?= html_escape($modeId); ?>"
                                           required
                                           style="margin-right:6px;">

                                    <img class="payment-logo"
                                         src="<?= $modeIcon; ?>"
                                         onerror="this.style.display='none';">

                                    <div>
                                        <div class="payment-mode-name">
                                            <?= html_escape($modeName); ?>
                                        </div>
                                        <?php if (!empty($modeDesc)): ?>
                                            <div class="payment-mode-desc">
                                                <?= strip_tags($modeDesc); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="payment-actions">
                            <button type="button"
                                    class="btn btn-default btn-outline-2025"
                                    onclick="window.print();">
                                <i class="fa fa-print"></i> In / Lưu PDF
                            </button>

                            <button type="submit"
                                    class="btn btn-primary btn-primary-2025">
                                <i class="fa fa-credit-card"></i> Thanh toán ngay
                            </button>
                        </div>

                        <input type="hidden" name="invoice_id"
                               value="<?= (int)$invoice['id']; ?>">
                        <input type="hidden" name="amount"
                               value="<?= (float)$total; ?>">

                    </form>
                <?php else: ?>
                    <p class="text-muted" style="font-size:12px;">
                        Chưa kích hoạt cổng thanh toán online nào trong hệ thống.
                        Vui lòng liên hệ IFK để được hỗ trợ.
                    </p>

                    <div class="payment-actions">
                        <button type="button"
                                class="btn btn-default btn-outline-2025"
                                onclick="window.print();">
                            <i class="fa fa-print"></i> In / Lưu PDF
                        </button>
                    </div>
                <?php endif; ?>

                <div class="note-block">
                    Nếu cần hỗ trợ về thanh toán, vui lòng liên hệ
                    <strong>support@ifkgroup.net</strong>
                    hoặc hotline trên website.
                </div>
            </div>

            <!-- TOTALS BOX -->
            <div class="totals-box">
                <table class="table">
                    <tr>
                        <td class="totals-label">Tổng trước thuế</td>
                        <td class="totals-value">
                            <?= format_vn_money($subtotal); ?> đ
                        </td>
                    </tr>
                    <tr>
                        <td class="totals-label">Thuế</td>
                        <td class="totals-value">
                            <?= format_vn_money($tax_total); ?> đ
                        </td>
                    </tr>
                    <tr>
                        <td class="totals-label totals-grand">
                            Thành tiền cần thanh toán
                        </td>
                        <td class="totals-value totals-grand">
                            <?= format_vn_money($total); ?> đ
                        </td>
                    </tr>
                </table>
            </div>

        </div><!-- /totals-and-payment -->

    </div><!-- /invoice-shell -->

</div><!-- /invoice-page-wrapper -->

<script src="<?= base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
<script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.min.js'); ?>"></script>

</body>
</html>