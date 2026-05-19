<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">

   <style>
/* ==============================
   IFK PDF PREMIUM (A4)
   Safe for dompdf/mpdf
============================== */
@page{
  margin: 25px 25px 30px 25px;
}

body{
  font-family: "dejavusans", sans-serif;
  font-size: 11px;
  color: #1f2937;
  line-height: 1.45;
}

/* Brand */
:root{
  --ifk-green:#96bc17;
  --ifk-navy:#00325a;
  --ifk-blue:#00a6dc;

  --ifk-border:#cfd8e3;
  --ifk-soft:#eef4fb;
  --ifk-soft2:#f6f9fc;
  --ifk-muted:#6b7280;
}

/* Helpers */
.text-center{ text-align:center; }
.text-right{ text-align:right; }
.text-muted{ color: var(--ifk-muted); }

.mt-5{ margin-top:5px; }
.mt-10{ margin-top:10px; }
.mt-15{ margin-top:15px; }
.mt-20{ margin-top:20px; }
.mb-0{ margin-bottom:0; }
.mb-5{ margin-bottom:5px; }
.mb-10{ margin-bottom:10px; }
.mb-15{ margin-bottom:15px; }
.mb-20{ margin-bottom:20px; }

/* ================= HEADER ================= */
.header-box{
  text-align:center;
  padding-bottom:10px;
  margin-bottom:16px;

  /* brand separator */
  border-bottom: 2px solid var(--ifk-navy);
}
.logo{
  width: 140px;
  margin-bottom: 6px;
}
.title-main{
  font-size: 17px;
  font-weight: 700;
  color: var(--ifk-navy);
  margin-bottom: 3px;
}
.subtitle{
  font-size: 11px;
  color: #4b5563;
  line-height: 1.45;
}

/* ================= SECTION TITLE ================= */
.section-title{
  font-size: 12.5px;
  font-weight: 700;
  color: var(--ifk-navy);
  margin-top: 16px;
  margin-bottom: 8px;

  /* left brand bar */
  padding: 6px 8px;
  background: #ffffff;
  border: 1px solid var(--ifk-border);
  border-left: 4px solid var(--ifk-blue);
}
.section-title span.index{
  font-weight: 700;
  margin-right: 4px;
  color: var(--ifk-blue);
}

/* ================= TABLE ================= */
table{
  width:100%;
  border-collapse: collapse;
  margin-bottom: 12px;
}

th{
  background: var(--ifk-soft);
  padding: 6px 6px;
  border: 1px solid var(--ifk-border);
  font-weight: 700;
  text-align: left;
  color: var(--ifk-navy);
}

td{
  padding: 6px 6px;
  border: 1px solid var(--ifk-border);
  vertical-align: top;
  background: #ffffff;
}

.table-compact th,
.table-compact td{
  padding: 5px 5px;
  font-size: 10.5px;
}

/* Zebra for readability */
tbody tr:nth-child(even) td{
  background: var(--ifk-soft2);
}

/* ================= BADGE / CHIP ================= */
.chip{
  display:inline-block;
  padding: 2px 8px;
  font-size: 10px;
  border-radius: 999px;

  background: rgba(150,188,23,.16);
  color: #2f6f09;
  border: 1px solid rgba(150,188,23,.35);
}

/* ================= ANSWER BOXES ================= */
.rating-box{
  margin-top: 5px;
  margin-bottom: 6px;
  padding: 7px 8px;
  background: #ffffff;
  border: 1px solid var(--ifk-border);
  border-left: 3px solid rgba(0,166,220,.85);
}
.rating-item-title{
  font-weight: 700;
  color: var(--ifk-navy);
  margin-bottom: 3px;
}

/* AI / Summary */
.summary-box{
  margin-top: 6px;
  padding: 9px 10px;
  background: rgba(0,166,220,.08);
  border: 1px solid rgba(0,166,220,.30);
  border-left: 4px solid var(--ifk-blue);
  font-size: 10.8px;
  line-height: 1.55;
  color: #1f2937;
}

/* No data */
.no-data{
  font-style: italic;
  color: var(--ifk-muted);
}

/* Small spacing to avoid page break ugliness */
.section-title, table, .summary-box{
  page-break-inside: avoid;
}
</style>
</head>
<body>

<?php
    // Chuẩn hoá biến để tránh notice
    $survey          = isset($survey) ? $survey : null;
    $total_responses = isset($total_responses) ? (int)$total_responses : 0;
    $rating_stats    = isset($rating_stats) && is_array($rating_stats) ? $rating_stats : [];
    $last_submit     = isset($last_submit) ? $last_submit : null;
    $questions       = isset($questions) && is_array($questions) ? $questions : [];
    $results         = isset($results) && is_array($results) ? $results : [];
    $ai_analysis     = isset($ai_analysis) ? $ai_analysis : null;
?>

<!-- ====================== HEADER ====================== -->
<div class="header-box">
    <img class="logo"
         src="https://translationifk.com/wp-content/uploads/2020/02/logo_ifk-1.svg"
         alt="IFK Logo">
    <div class="title-main">
        Báo Cáo Kết Quả Khảo Sát Thực Tập Sinh – IFK Japan
    </div>
    <div class="subtitle">
        Mẫu khảo sát:
        <strong><?php echo $survey ? html_escape($survey->title) : ''; ?></strong><br>
        Xuất báo cáo lúc: <?php echo date('d/m/Y H:i'); ?>
    </div>
</div>

<!-- ====================== 1. THỐNG KÊ CHUNG ====================== -->
<div class="section-title">
    <span class="index">1.</span> Thống kê chung
</div>

<table class="table-compact">
    <tr>
        <th style="width: 35%;">Tổng số phản hồi</th>
        <td>
            <?php echo $total_responses; ?>
            <?php if ($total_responses > 0): ?>
                <span class="chip">Phiếu hợp lệ</span>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>Số câu hỏi dạng Rating</th>
        <td><?php echo count($rating_stats); ?></td>
    </tr>
    <tr>
        <th>Lần gửi gần nhất</th>
        <td>
            <?php
                echo $last_submit
                    ? (function_exists('_dt') ? _dt($last_submit) : date('d/m/Y H:i', strtotime($last_submit)))
                    : '—';
            ?>
        </td>
    </tr>
</table>

<?php if (!empty($survey) && !empty($survey->description)): ?>
    <div class="summary-box" style="margin-top:4px;">
        <strong>Mô tả mẫu khảo sát:</strong><br>
        <?php echo nl2br(html_escape($survey->description)); ?>
    </div>
<?php endif; ?>

<!-- ====================== 2. THỐNG KÊ RATING ====================== -->
<div class="section-title">
    <span class="index">2.</span> Thống kê chi tiết các câu hỏi Rating
</div>

<?php if (!empty($rating_stats)): ?>
    <table class="table-compact">
        <thead>
        <tr>
            <th style="width: 38%;">Câu hỏi</th>
            <th style="width: 10%;" class="text-center">Điểm TB</th>
            <th class="text-center">1★</th>
            <th class="text-center">2★</th>
            <th class="text-center">3★</th>
            <th class="text-center">4★</th>
            <th class="text-center">5★</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rating_stats as $stat): ?>
            <tr>
                <td><?php echo html_escape($stat['label']); ?></td>
                <td class="text-center">
                    <strong><?php echo number_format((float)$stat['avg'], 2); ?></strong>
                </td>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <td class="text-center">
                        <?php echo isset($stat['count'][$i]) ? (int)$stat['count'][$i] : 0; ?>
                    </td>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="no-data">Không có câu hỏi dạng rating trong mẫu khảo sát này.</p>
<?php endif; ?>


<!-- ====================== 3. CÂU HỎI MỞ / KHÔNG PHẢI RATING ====================== -->
<div class="section-title">
    <span class="index">3.</span> Tổng hợp câu hỏi mở / không phải rating
</div>

<?php
    // Xác định xem có câu hỏi non-rating không
    $has_non_rating = false;
    foreach ($questions as $q) {
        if ($q->field_type !== 'rating') {
            $has_non_rating = true;
            break;
        }
    }
?>

<?php if ($has_non_rating && !empty($results)): ?>
    <table class="table-compact">
        <thead>
        <tr>
            <th style="width: 32%;">Câu hỏi</th>
            <th>Trả lời của thực tập sinh</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($questions as $q): ?>
            <?php if ($q->field_type === 'rating') continue; ?>

            <tr>
                <td><?php echo html_escape($q->label); ?></td>
                <td>
                    <?php foreach ($results as $r): ?>
                        <?php
                            $ans_arr = json_decode($r->answers, true) ?: [];
                            $field   = 'field_' . $q->id;
                            $answer  = isset($ans_arr[$field]) ? trim($ans_arr[$field]) : '';
                        ?>
                        <?php if ($answer !== ''): ?>
                            <div class="rating-box">
                                <div class="rating-item-title">
                                    <?php echo html_escape($r->full_name); ?>
                                    <span class="text-muted">
                                        (<?php
                                            echo function_exists('_dt')
                                                ? _dt($r->submitted_at)
                                                : date('d/m/Y H:i', strtotime($r->submitted_at));
                                        ?>)
                                    </span>
                                </div>
                                <div>
                                    <?php echo nl2br(html_escape($answer)); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php
                        // Kiểm tra nếu tất cả đều trống
                        $has_answer = false;
                        foreach ($results as $r_check) {
                            $tmp = json_decode($r_check->answers, true) ?: [];
                            if (!empty($tmp['field_'.$q->id])) {
                                $has_answer = true;
                                break;
                            }
                        }
                        if (!$has_answer): ?>
                        <span class="no-data">Chưa có câu trả lời.</span>
                    <?php endif; ?>
                </td>
            </tr>

        <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif ($has_non_rating && empty($results)): ?>
    <p class="no-data">Mẫu khảo sát có câu hỏi mở, nhưng hiện chưa có phiếu trả lời.</p>
<?php else: ?>
    <p class="no-data">Mẫu khảo sát này chỉ gồm các câu hỏi dạng rating.</p>
<?php endif; ?>


<!-- ====================== 4. PHÂN TÍCH AI (GEMINI) ====================== -->
<?php if (!empty($ai_analysis)): ?>
    <div class="section-title">
        <span class="index">4.</span> Phân tích tổng quan từ AI (Gemini)
    </div>

    <div class="summary-box">
        <?php echo nl2br(html_escape($ai_analysis)); ?>
    </div>
<?php endif; ?>

</body>
</html>