<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** Lấy CI Instance + CSRF */
$CI           =& get_instance();
$csrf_enabled = (bool) $CI->config->item('csrf_protection');
$csrf_name    = $csrf_enabled ? $CI->security->get_csrf_token_name() : null;
$csrf_hash    = $csrf_enabled ? $CI->security->get_csrf_hash() : null;

/** Mảng lỗi */
$errors = isset($errors) && is_array($errors) ? $errors : [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    
    <meta charset="UTF-8">
       <link rel="icon"
          type="image/png"
          href="<?php echo base_url('uploads/company/favicon.png'); ?>">
    <title><?php echo html_escape($survey->title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap + Icons -->
    <link rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>

    <style>
:root{
  --ifk-green:#96bc17;
  --ifk-navy:#00325a;
  --ifk-blue:#00a6dc;

  --ifk-bg:#f6f9fc;
  --ifk-card:#ffffff;
  --ifk-border:#e6eef6;
  --ifk-text:#1c2b3a;
  --ifk-muted:#6b7c93;

  --ifk-radius:18px;
  --ifk-shadow:0 16px 42px rgba(0,50,90,.14);
}

body{
  background: var(--ifk-bg);
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  color: var(--ifk-text);
}

/* Wrapper */
.survey-wrapper{
  max-width: 860px;
  margin: 28px auto;
  padding: 0 14px;
}

/* Card */
.survey-card{
  background: var(--ifk-card);
  border-radius: var(--ifk-radius);
  box-shadow: var(--ifk-shadow);
  overflow: hidden;
  border: 1px solid rgba(230,238,246,.9);
}

/* Header */
.survey-header{
  padding: 18px 20px;
  background: linear-gradient(135deg, var(--ifk-blue), var(--ifk-navy));
  color: #fff;
  display:flex;
  align-items:center;
  justify-content:space-between;
  flex-wrap:wrap;
  position:relative;
}

/* brand accent bar */
.survey-header:after{
  content:"";
  position:absolute;
  left:0; right:0; bottom:0;
  height:4px;
  background: linear-gradient(90deg, var(--ifk-green), var(--ifk-blue));
}

.survey-header-left{ display:flex; align-items:center; gap:12px; }
.survey-logo{ height:44px; width:auto; margin:0; filter: drop-shadow(0 8px 16px rgba(0,0,0,.18)); }

.survey-brand{
  font-weight:1000;
  font-size:18px;
  line-height:1.15;
  letter-spacing:.2px;
}
.survey-slogan{
  font-size:12px;
  opacity:.9;
}
.survey-slogan i{ color: var(--ifk-green); margin-right:6px; }

.survey-header-right{
  font-size:13px;
  text-align:right;
  opacity:.95;
}
.survey-header-right i{ color: var(--ifk-green); margin-right:6px; }

/* Body */
.survey-body{
  padding: 22px 20px 18px;
}

.survey-title{
  font-size: 20px;
  font-weight: 1000;
  margin: 0 0 6px;
  color: var(--ifk-navy);
  letter-spacing:.2px;
}
.survey-title i{ color: var(--ifk-blue); margin-right:6px; }

.survey-desc{
  font-size: 14px;
  color: var(--ifk-muted);
  margin-bottom: 14px;
}

/* Student info */
.student-info{
  background: rgba(0,166,220,.08);
  border: 1px solid rgba(0,166,220,.22);
  border-left: 4px solid var(--ifk-blue);
  border-radius: 14px;
  padding: 10px 12px;
  margin-bottom: 14px;
  font-size: 14px;
}
.student-info i{ color: var(--ifk-blue); margin-right:6px; }

/* Questions */
.q-label{
  font-weight: 900;
  font-size: 14px;
  margin-bottom: 6px;
  color: var(--ifk-navy);
}
.q-required{ color:#ef4444; margin-left:4px; }

.q-hint{
  font-size: 12px;
  color: var(--ifk-muted);
  margin-top: 6px;
}

/* Inputs */
.form-control, .custom-select{
  border-radius: 14px !important;
  border: 1px solid var(--ifk-border) !important;
  box-shadow: none !important;
  transition: all .15s ease;
}
.form-control:focus, .custom-select:focus{
  border-color: rgba(0,166,220,.55) !important;
  box-shadow: 0 0 0 .2rem rgba(0,166,220,.15) !important;
}

/* invalid */
.is-invalid{ border-color:#ef4444 !important; }
.invalid-feedback{
  display:block;
  font-size:12px;
  color:#b42318;
  margin-top:6px;
}

/* Radios/checkboxes */
.form-check{ margin-bottom:8px; }
.form-check-input{ margin-top:.25rem; }
.form-check-label{ color: var(--ifk-text); font-weight:700; }

/* Rating */
.rating-label{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding: 8px 10px;
  border: 1px solid var(--ifk-border);
  border-radius: 999px;
  background:#fff;
  margin-right:8px;
  margin-bottom:8px;
  font-size: 13px;
  font-weight: 900;
  color: var(--ifk-navy);
}
.rating-label input{ margin:0; }
.rating-label:hover{
  border-color: rgba(0,166,220,.45);
  box-shadow: 0 10px 22px rgba(0,50,90,.10);
}

/* Footer */
.survey-footer{
  padding: 14px 20px 18px;
  border-top: 1px solid rgba(230,238,246,.9);
  text-align: right;
  background: linear-gradient(180deg, #fff, rgba(0,50,90,.02));
}

.btn-submit{
  background: var(--ifk-navy);
  border-color: var(--ifk-navy);
  border-radius: 999px;
  padding: 10px 22px;
  font-weight: 1000;
  font-size: 15px;
  box-shadow: 0 14px 30px rgba(0,50,90,.18);
}
.btn-submit:hover{
  background: #062845; /* darker navy */
  border-color: #062845;
  transform: translateY(-1px);
}

/* Alerts */
.alert{
  border-radius: 14px;
  border: 1px solid var(--ifk-border);
}

/* Success */
.success-box{ text-align:center; padding: 34px 18px 26px; }
.success-icon{
  width: 74px;
  height: 74px;
  border-radius: 999px;
  background: rgba(150,188,23,.18);
  border: 1px solid rgba(150,188,23,.35);
  display:flex;
  align-items:center;
  justify-content:center;
  margin: 0 auto 14px;
  color: #2f6f09;
  font-size: 32px;
}
.success-title{
  font-size: 20px;
  font-weight: 1000;
  margin-bottom: 8px;
  color: var(--ifk-navy);
}
.success-text{ font-size: 14px; color: var(--ifk-muted); }

/* Mobile */
@media (max-width: 576px){
  .survey-header{
    flex-direction: column;
    align-items: flex-start;
    gap:10px;
  }
  .survey-header-right{
    text-align:left;
    width:100%;
  }
  .survey-body{ padding: 18px 14px 14px; }
  .survey-title{ font-size: 18px; }
  .survey-footer{ text-align:left; }
  .btn-submit{ width: 100%; }
}
</style>
</head>

<body>
<div class="survey-wrapper">
    <div class="survey-card">

        <!-- HEADER -->
        <div class="survey-header">
            <div class="survey-header-left">
                <img src="https://translationifk.com/wp-content/uploads/2020/02/logo_ifk-1.svg"
                     alt="IFK Logo"
                     class="survey-logo">
                <div>
                    <div class="survey-brand">IFK Education &amp; Translation</div>
                    <div class="survey-slogan">
                        <i class="fas fa-leaf"></i>
                        Gắn kết con người – Sáng tạo tương lai
                    </div>
                </div>
            </div>
            <div class="survey-header-right">
                <div><i class="far fa-clipboard"></i> Khảo sát trực tuyến</div>
                <div style="font-size:12px;opacity:.85;">
                    Thời gian hoàn thành ~ 5–10 phút
                </div>
            </div>
        </div>

        <?php if (!empty($submitted)): ?>

            <!-- TRẠNG THÁI ĐÃ GỬI -->
            <div class="survey-body">
                <div class="success-box">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="success-title">
                        Cảm ơn bạn đã hoàn thành khảo sát! 🎉
                    </div>
                    <p class="success-text">
                        IFK trân trọng mọi ý kiến đóng góp từ bạn
                        <strong><?php echo html_escape($student->full_name); ?></strong>.
                        Thông tin khảo sát sẽ được sử dụng để cải thiện chất lượng chương trình thực tập.
                    </p>
                </div>
            </div>

        <?php else: ?>

            <div class="survey-body">

                <!-- THÔNG TIN SINH VIÊN -->
                <div class="student-info">
                    <div>
                        <i class="fas fa-user-graduate"></i>
                        <strong>Sinh viên:</strong>
                        <?php echo html_escape($student->full_name); ?>
                    </div>
                    <div>
                        <i class="far fa-envelope"></i>
                        <strong>Email:</strong>
                        <?php echo html_escape($student->email); ?>
                    </div>
                </div>

                <!-- TIÊU ĐỀ + MÔ TẢ -->
                <h1 class="survey-title">
                    <i class="far fa-clipboard"></i>
                    <?php echo html_escape($survey->title); ?>
                </h1>

                <?php if (!empty($survey->description)): ?>
                    <p class="survey-desc">
                        <?php echo nl2br(html_escape($survey->description)); ?>
                    </p>
                <?php else: ?>
                    <p class="survey-desc">
                        Vui lòng trả lời trung thực các câu hỏi dưới đây. Thông tin của bạn sẽ được bảo mật
                        và chỉ sử dụng cho mục đích nâng cao chất lượng chương trình của IFK.
                    </p>
                <?php endif; ?>

                <!-- KHỐI LỖI CHUNG -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mt-3">
                        <strong><i class="fas fa-exclamation-circle"></i> Vui lòng kiểm tra lại:</strong>
                        <ul class="mb-0">
                            <?php foreach ($errors as $fieldName => $msg): ?>
                                <li><?php echo $msg; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <hr>

                <!-- FORM KHẢO SÁT -->
                <form method="post" id="survey_form" action="<?php echo current_url(); ?>">

                    <?php if ($csrf_enabled && $csrf_name && $csrf_hash): ?>
                        <input type="hidden"
                               name="<?php echo html_escape($csrf_name); ?>"
                               value="<?php echo html_escape($csrf_hash); ?>">
                    <?php endif; ?>

                    <?php foreach ($questions as $q): ?>
                        <?php
                        $name      = 'field_' . $q->id;
                        $postedVal = $CI->input->post($name); // có thể là string hoặc array (checkbox)
                        $q_error   = isset($errors[$name]) ? $errors[$name] : '';

                        // Chuẩn hóa danh sách options
                        $options = array_filter(array_map('trim', explode("\n", (string)$q->options)));
                        ?>

                        <div class="form-group mt-4">
                            <label class="q-label">
                                <?php echo html_escape($q->label); ?>
                                <?php if (!empty($q->required)): ?>
                                    <span class="q-required">*</span>
                                <?php endif; ?>
                            </label>

                            <?php if ($q->field_type === 'textarea'): ?>

                                <textarea
                                    name="<?php echo $name; ?>"
                                    rows="3"
                                    class="form-control <?php echo $q_error ? 'is-invalid' : ''; ?>"
                                ><?php echo html_escape(is_null($postedVal) ? '' : $postedVal); ?></textarea>

                            <?php elseif ($q->field_type === 'select'): ?>

                                <select
                                    name="<?php echo $name; ?>"
                                    class="form-control <?php echo $q_error ? 'is-invalid' : ''; ?>"
                                >
                                    <option value="">-- Chọn --</option>
                                    <?php foreach ($options as $opt): ?>
                                        <option value="<?php echo html_escape($opt); ?>"
                                            <?php echo (!is_null($postedVal) && $postedVal == $opt) ? 'selected' : ''; ?>>
                                            <?php echo html_escape($opt); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                            <?php elseif ($q->field_type === 'radio'): ?>

                                <?php foreach ($options as $opt): ?>
                                    <?php $idOpt = $name . '_' . md5($opt); ?>
                                    <div class="form-check">
                                        <input
                                            class="form-check-input <?php echo $q_error ? 'is-invalid' : ''; ?>"
                                            type="radio"
                                            name="<?php echo $name; ?>"
                                            id="<?php echo $idOpt; ?>"
                                            value="<?php echo html_escape($opt); ?>"
                                            <?php echo (!is_null($postedVal) && $postedVal == $opt) ? 'checked' : ''; ?>
                                        >
                                        <label class="form-check-label" for="<?php echo $idOpt; ?>">
                                            <?php echo html_escape($opt); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>

                            <?php elseif ($q->field_type === 'checkbox'): ?>

                                <?php
                                // postedVal có thể là array (sau submit)
                                $checkedValues = [];
                                if (is_array($postedVal)) {
                                    $checkedValues = $postedVal;
                                } elseif (is_string($postedVal) && strlen($postedVal)) {
                                    $checkedValues = array_map('trim', explode(',', $postedVal));
                                }
                                ?>
                                <?php foreach ($options as $opt): ?>
                                    <?php $idOpt = $name . '_' . md5($opt); ?>
                                    <div class="form-check">
                                        <input
                                            class="form-check-input <?php echo $q_error ? 'is-invalid' : ''; ?>"
                                            type="checkbox"
                                            name="<?php echo $name; ?>[]"
                                            id="<?php echo $idOpt; ?>"
                                            value="<?php echo html_escape($opt); ?>"
                                            <?php echo in_array($opt, $checkedValues, true) ? 'checked' : ''; ?>
                                        >
                                        <label class="form-check-label" for="<?php echo $idOpt; ?>">
                                            <?php echo html_escape($opt); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>

                            <?php elseif ($q->field_type === 'rating'): ?>

                                <div>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <label class="rating-label">
                                            <input
                                                type="radio"
                                                name="<?php echo $name; ?>"
                                                value="<?php echo $i; ?>"
                                                <?php echo (!is_null($postedVal) && (int)$postedVal === $i) ? 'checked' : ''; ?>
                                            >
                                            <?php echo $i; ?>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                                <div class="q-hint">
                                    1: Rất không hài lòng &nbsp;&nbsp;–&nbsp;&nbsp; 5: Rất hài lòng
                                </div>

                            <?php elseif ($q->field_type === 'date'): ?>

                                <input
                                    type="date"
                                    name="<?php echo $name; ?>"
                                    class="form-control <?php echo $q_error ? 'is-invalid' : ''; ?>"
                                    value="<?php echo html_escape(is_null($postedVal) ? '' : $postedVal); ?>"
                                >

                            <?php else: ?>
                                <!-- default: text -->
                                <input
                                    type="text"
                                    name="<?php echo $name; ?>"
                                    class="form-control <?php echo $q_error ? 'is-invalid' : ''; ?>"
                                    value="<?php echo html_escape(is_null($postedVal) ? '' : $postedVal); ?>"
                                >
                            <?php endif; ?>

                            <?php if ($q_error): ?>
                                <div class="invalid-feedback">
                                    <?php echo html_escape($q_error); ?>
                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>

                </form>

            </div>

            <!-- FOOTER SUBMIT -->
            <div class="survey-footer">
                <button type="submit"
                        class="btn btn-submit"
                        form="survey_form">
                    <i class="far fa-paper-plane"></i> Gửi Khảo Sát
                </button>
            </div>

        <?php endif; ?>

    </div>
</div>

</body>
</html>