<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Internship_survey extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Internship_survey_model', 'survey_model');
        $this->load->library('app_modules');
        $this->load->helper(['url', 'form']);
        $this->load->model('internship_management/Im_audit_log_model', 'im_audit');
 $this->load->helper('internship_management/im_audit');
 if ($this->input->method() === 'post') {

    $post = $this->input->post(NULL, true);

    // lọc dữ liệu nhạy cảm
    foreach (['password','pass','smtp_pass','api_key','token'] as $k) {
        if (isset($post[$k])) $post[$k] = '***';
    }

    im_audit_log(
        'http',
        0,
        'http_post',
        'POST vào Internship Applications',
        null,
        [
            'url' => current_url(),
            'staff_id' => get_staff_user_id(),
            'post' => $post
        ]
    );
}
    }

    /* =========================================================================
       1. QUẢN LÝ MẪU KHẢO SÁT
    ========================================================================= */
    public function templates()
    {
        if ($this->input->post()) {
            $id = $this->input->post('id');

            $template = [
                'title'       => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'active'      => $this->input->post('active') ? 1 : 0,
            ];

            $labels     = $this->input->post('q_label')   ?? [];
            $types      = $this->input->post('q_type')    ?? [];
            $options    = $this->input->post('q_options') ?? [];
            $required   = $this->input->post('q_required')?? [];
            $sort_order = $this->input->post('q_sort')    ?? [];

            $questions = [];
            foreach ($labels as $i => $label) {
                if (trim($label) === '') continue;

                $questions[] = [
                    'label'      => trim($label),
                    'field_type' => $types[$i]      ?? 'text',
                    'options'    => $options[$i]    ?? '',
                    'required'   => isset($required[$i]) ? 1 : 0,
                    'sort_order' => $sort_order[$i] ?? $i,
                ];
            }

            $survey_id = $this->survey_model->save_template($template, $questions, $id);

            set_alert('success', 'Đã lưu mẫu khảo sát.');
            redirect(admin_url('internship_management/internship_survey/templates?id=' . $survey_id));
        }

        $edit_id               = $this->input->get('id');
        $data['edit_survey']   = $edit_id ? $this->survey_model->get_template($edit_id) : null;
        $data['questions']     = $edit_id ? $this->survey_model->get_questions($edit_id) : [];

        $data['templates'] = $this->survey_model->get_templates();
        $data['title']     = 'Quản lý mẫu khảo sát';

        $this->load->view('survey/templates', $data);
    }

    public function delete_template($id)
    {
        if (!$id) show_404();

        $this->survey_model->delete_template($id);
        set_alert('success', 'Đã xoá mẫu khảo sát.');
        redirect(admin_url('internship_management/internship_survey/templates'));
    }

    /* =========================================================================
       2. XEM KẾT QUẢ
    ========================================================================= */
    public function results($survey_id = null)
    {
        if (!$survey_id) show_404();

        $survey = $this->survey_model->get_template($survey_id);
        if (!$survey) show_404();

        $data['survey']    = $survey;
        $data['questions'] = $this->survey_model->get_questions($survey_id);
        $data['results']   = $this->survey_model->get_results($survey_id);
        $data['title']     = 'Kết quả khảo sát: ' . $survey->title;

        $this->load->view('survey/results', $data);
    }

    /* =========================================================================
       3. DASHBOARD
    ========================================================================= */
    public function dashboard($survey_id = null)
    {
        if (!$survey_id) show_404();

        $survey = $this->survey_model->get_template($survey_id);
        if (!$survey) show_404();

        $questions = $this->survey_model->get_questions($survey_id);
        $results   = $this->survey_model->get_results($survey_id);

        $total_responses = count($results);
        $last_submit     = null;

        $rating_stats = [];
        foreach ($questions as $q) {
            if ($q->field_type != 'rating') continue;

            $rating_stats[$q->id] = [
                'label' => $q->label,
                'sum'   => 0,
                'n'     => 0,
                'count' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
            ];
        }

        foreach ($results as $r) {
            if (!$last_submit || $r->submitted_at > $last_submit)
                $last_submit = $r->submitted_at;

            $ans = json_decode($r->answers, true) ?: [];

            foreach ($rating_stats as $id => &$st) {
                $key = "field_".$id;
                if (isset($ans[$key]) && is_numeric($ans[$key])) {
                    $v = (int) $ans[$key];
                    if ($v >= 1 && $v <= 5) {
                        $st['sum'] += $v;
                        $st['n']   += 1;
                        $st['count'][$v]++;
                    }
                }
            }
            unset($st);
        }

        foreach ($rating_stats as &$s) {
            $s['avg'] = $s['n'] ? round($s['sum'] / $s['n'], 2) : 0;
        }
        unset($s);

        $primary_rating = !empty($rating_stats)
            ? $rating_stats[array_key_first($rating_stats)]
            : null;

        $data = [
            'survey'          => $survey,
            'total_responses' => $total_responses,
            'last_submit'     => $last_submit,
            'rating_stats'    => $rating_stats,
            'primary_rating'  => $primary_rating,
            'title'           => 'Dashboard khảo sát: ' . $survey->title,
        ];

        $this->load->view('survey/dashboard', $data);
    }

    /* =========================================================================
       4. EXPORT CSV
    ========================================================================= */
    public function export_results($survey_id = null)
    {
        if (!$survey_id) show_404();

        $survey = $this->survey_model->get_template($survey_id);
        if (!$survey) show_404();

        $questions = $this->survey_model->get_questions($survey_id);
        $results   = $this->survey_model->get_results($survey_id);

        $filename = 'Survey_'.url_title($survey->title, '_', true).'_'.date('Ymd_His').'.csv';

        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: text/csv; charset=UTF-8");
        echo "\xEF\xBB\xBF"; // UTF-8 BOM

        $output = fopen("php://output", "w");

        $header = ['Thời gian gửi', 'Họ tên', 'Email'];
        foreach ($questions as $q) $header[] = $q->label;

        fputcsv($output, $header);

        foreach ($results as $r) {
            $ans = json_decode($r->answers, true) ?: [];

            $row = [$r->submitted_at, $r->full_name, $r->email];

            foreach ($questions as $q) {
                $row[] = $ans['field_'.$q->id] ?? '';
            }

            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /* =========================================================================
       5. EXPORT PDF
    ========================================================================= */
    public function export_pdf($survey_id = null)
    {
        if (!$survey_id) show_404();

        $survey = $this->survey_model->get_template($survey_id);
        if (!$survey) show_404();

        $questions = $this->survey_model->get_questions($survey_id);
        $results   = $this->survey_model->get_results($survey_id);

        $total_responses = count($results);
        $last_submit     = null;

        $rating_stats = [];
        foreach ($questions as $q) {
            if ($q->field_type != 'rating') continue;
            $rating_stats[$q->id] = [
                'label' => $q->label,
                'sum'   => 0,
                'n'     => 0,
                'count' => [1 => 0,2 => 0,3 => 0,4 => 0,5 => 0],
            ];
        }

        foreach ($results as $r) {
            if (!$last_submit || $r->submitted_at > $last_submit)
                $last_submit = $r->submitted_at;

            $ans = json_decode($r->answers, true) ?: [];
            foreach ($rating_stats as $id => &$st) {
                $key = "field_".$id;
                if (isset($ans[$key]) && is_numeric($ans[$key])) {
                    $v = (int)$ans[$key];
                    if ($v >= 1 && $v <= 5) {
                        $st['sum'] += $v;
                        $st['n']   += 1;
                        $st['count'][$v]++;
                    }
                }
            }
            unset($st);
        }

        foreach ($rating_stats as &$s)
            $s['avg'] = $s['n'] ? round($s['sum'] / $s['n'], 2) : 0;

        $data = [
            'survey'          => $survey,
            'questions'       => $questions,
            'results'         => $results,
            'total_responses' => $total_responses,
            'last_submit'     => $last_submit,
            'rating_stats'    => $rating_stats,
        ];

        $html = $this->load->view('survey/pdf_report', $data, true);

        $this->load->library('app_pdf');
        $pdf = $this->app_pdf->load();
        $pdf->SetTitle($survey->title);
        $pdf->WriteHTML($html);

        $filename = 'Bao_cao_khao_sat_' . url_title($survey->title, '_', true) . '.pdf';
        $pdf->Output($filename, 'I');
    }

    /* =========================================================================
       6. PHÂN TÍCH AI – GEMINI
    ========================================================================= */
    public function ai_generate_comment($survey_id)
    {
        if (!has_permission('internship_management', '', 'view')) {
            ajax_access_denied();
        }

        @ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        ini_set('display_errors', 0);
        error_reporting(0);

        if (!$survey_id) {
            echo json_encode(['success' => false, 'message' => 'Thiếu survey_id.']);
            exit;
        }

        $survey    = $this->survey_model->get_template($survey_id);
        $questions = $this->survey_model->get_questions($survey_id);
        $results   = $this->survey_model->get_results($survey_id);

        if (!$survey) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy mẫu khảo sát.']);
            exit;
        }
        if (empty($results)) {
            echo json_encode(['success' => false, 'message' => 'Chưa có phản hồi khảo sát.']);
            exit;
        }

        // Build text for AI
        $text = "PHÂN TÍCH KHẢO SÁT: {$survey->title}\n\n";

        foreach ($results as $r) {
            $ans = json_decode($r->answers, true) ?: [];
            $text .= "-----\nNgười trả lời: {$r->full_name}\n";
            foreach ($questions as $q) {
                $k = "field_".$q->id;
                $v = $ans[$k] ?? '';
                $text .= "{$q->label}: {$v}\n";
            }
            $text .= "\n";
        }

        $apiKey = get_option('intern_google_api_key');
        if (!$apiKey) {
            echo json_encode(['success' => false, 'message' => 'Thiếu Google API Key.']);
            exit;
        }

        // FIX URL CHUẨN
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

        $payload = [
            "contents" => [[
                "parts" => [[
                    "text" =>
"Bạn là chuyên gia phân tích khảo sát.
Hãy viết 5–8 câu:
- Mức độ hài lòng chung
- Điểm mạnh
- Điểm yếu
- Gợi ý cải thiện
Không markdown.

DỮ LIỆU:
{$text}"
                ]]
            ]]
        ];

        // CURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 60
        ]);

        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            echo json_encode(['success' => false, 'message' => "AI lỗi: $err"]);
            exit;
        }

        $json = json_decode($res, true);

        if (!isset($json['candidates'][0]['content']['parts'][0]['text'])) {
            echo json_encode(['success' => false, 'message' => 'AI không trả lại dữ liệu.']);
            exit;
        }

        $comment = trim($json['candidates'][0]['content']['parts'][0]['text']);

        // LƯU DB
        $this->db->where('id', $survey_id);
        $this->db->update(db_prefix().'internship_survey_templates', [
            'ai_comment'   => $comment,
            'ai_updated_at'=> date("Y-m-d H:i:s")
        ]);

        echo json_encode([
            'success' => true,
            'comment' => $comment
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}