<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Survey_form extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Perfex bắt buộc phải load để module hoạt động
        $this->load->library('app_modules');

        // Load model khảo sát
        $this->load->model('Internship_survey_model', 'survey');

        // Helpers
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

    /**
     * Public survey URL:
     *   https://work.ifk.io.vn/survey_form/{survey_id}/{student_id}
     */
    public function index($survey_id = null, $student_id = null)
    {
        /* ----------------------------------------------------
         * 1. Kiểm tra tham số
         * ---------------------------------------------------- */
        if (!$survey_id || !$student_id) {
            show_404();
        }

        /* ----------------------------------------------------
         * 2. Lấy template khảo sát
         * ---------------------------------------------------- */
        $survey = $this->survey->get_template($survey_id);

        if (!$survey) {
            show_404("Mẫu khảo sát không tồn tại.");
        }

        if ((int)$survey->active !== 1) {
            show_error("Mẫu khảo sát này đã bị tắt.", 403);
        }

        /* ----------------------------------------------------
         * 3. Lấy thông tin sinh viên
         * ---------------------------------------------------- */
        $student = $this->survey->get_student($student_id);

        if (!$student) {
            show_404("Không tìm thấy sinh viên.");
        }

        /* ----------------------------------------------------
         * 4. Câu hỏi khảo sát
         * ---------------------------------------------------- */
        $questions = $this->survey->get_questions($survey_id);

        /* Flags */
        $submitted = false;
        $errors    = [];

        /* ----------------------------------------------------
         * 5. Nếu SUBMIT FORM
         * ---------------------------------------------------- */
        if ($this->input->method() === 'post') {

            $answers = [];

            foreach ($questions as $q) {

                $field_name = "field_" . $q->id;
                $value = $this->input->post($field_name);

                // Checkbox → mảng → convert chuỗi
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                // Validate required
                if ((int)$q->required === 1 && ($value === null || $value === "")) {
                    $errors[] =
                        "Bạn chưa trả lời câu hỏi: <strong>" .
                        html_escape($q->label) .
                        "</strong>";
                }

                $answers[$field_name] = $value;
            }

            /* Nếu không lỗi → lưu kết quả */
            if (empty($errors)) {

                $this->survey->save_result($survey_id, $student_id, $answers);

                $submitted = true;
            }
        }

        /* ----------------------------------------------------
         * 6. Render view
         * ---------------------------------------------------- */
        $data = [
            'survey'    => $survey,
            'student'   => $student,
            'questions' => $questions,
            'submitted' => $submitted,
            'errors'    => $errors,
        ];

        $this->load->view('survey/form', $data);
    }
}