<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Internship_survey_model extends App_Model
{
    protected $table_templates;
    protected $table_questions;
    protected $table_results;
    protected $table_students;

    public function __construct()
    {
        parent::__construct();

        $this->table_templates = db_prefix() . 'internship_survey_templates';
        $this->table_questions = db_prefix() . 'internship_survey_questions';
        $this->table_results   = db_prefix() . 'internship_survey_results';
        $this->table_students  = db_prefix() . 'internship_students';
    }

    /* ============================================================
     * 1. GET TEMPLATE
     * ============================================================ */
    public function get_template($id)
    {
        return $this->db->where('id', $id)->get($this->table_templates)->row();
    }

    /* ============================================================
     * 2. LIST TEMPLATES
     * ============================================================ */
    public function get_templates()
    {
        return $this->db
            ->order_by('id', 'DESC')
            ->get($this->table_templates)
            ->result();
    }

    /* ============================================================
     * 3. GET QUESTIONS
     * ============================================================ */
    public function get_questions($survey_id)
    {
        return $this->db
            ->where('survey_id', $survey_id)
            ->order_by('sort_order', 'ASC')
            ->get($this->table_questions)
            ->result();
    }

    /* ============================================================
     * 4. SAVE TEMPLATE (NO updated_at)
     * ============================================================ */
    public function save_template($template_data, $questions, $id = null)
    {
        if ($id) {

            // Không thêm updated_at vì bảng KHÔNG có cột này
            $this->db->where('id', $id)->update($this->table_templates, $template_data);
            $survey_id = $id;

            // Xóa câu hỏi cũ
            $this->db->where('survey_id', $id)->delete($this->table_questions);

        } else {

            // Chỉ có created_at (nếu bảng có)
            if ($this->db->field_exists('created_at', $this->table_templates)) {
                $template_data['created_at'] = date('Y-m-d H:i:s');
            }

            $this->db->insert($this->table_templates, $template_data);
            $survey_id = $this->db->insert_id();
        }

        // Insert câu hỏi mới
        foreach ($questions as $q) {
            $this->db->insert($this->table_questions, [
                'survey_id'  => $survey_id,
                'label'      => $q['label'],
                'field_type' => $q['field_type'],
                'options'    => $q['options'],
                'required'   => $q['required'],
                'sort_order' => $q['sort_order'],
            ]);
        }

        return $survey_id;
    }

    /* ============================================================
     * 5. DELETE TEMPLATE
     * ============================================================ */
    public function delete_template($id)
    {
        $this->db->where('survey_id', $id)->delete($this->table_questions);
        $this->db->where('survey_id', $id)->delete($this->table_results);
        $this->db->where('id', $id)->delete($this->table_templates);

        return true;
    }

    /* ============================================================
     * 6. GET STUDENT
     * ============================================================ */
    public function get_student($student_id)
    {
        return $this->db
            ->where('id', $student_id)
            ->get($this->table_students)
            ->row();
    }

    /* ============================================================
     * 7. SAVE RESULT
     * ============================================================ */
    public function save_result($survey_id, $student_id, $answers)
    {
        $data = [
            'survey_id'    => $survey_id,
            'student_id'   => $student_id,
            'answers'      => json_encode($answers, JSON_UNESCAPED_UNICODE),
            'submitted_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert($this->table_results, $data);
        return $this->db->insert_id();
    }

    /* ============================================================
     * 8. GET RESULTS
     * ============================================================ */
    public function get_results($survey_id)
    {
        $this->db->select("
            r.*,
            s.full_name,
            s.email
        ");
        $this->db->from($this->table_results . " AS r");
        $this->db->join($this->table_students . " AS s", "s.id = r.student_id", "left");
        $this->db->where('r.survey_id', $survey_id);
        $this->db->order_by('r.submitted_at', 'DESC');

        return $this->db->get()->result();
    }

    /* ============================================================
     * 9. RATING STATS
     * ============================================================ */
    public function compute_rating_stats($questions, $results)
    {
        $rating_stats = [];

        foreach ($questions as $q) {
            if ($q->field_type === 'rating') {
                $rating_stats[$q->id] = [
                    'label' => $q->label,
                    'sum'   => 0,
                    'n'     => 0,
                    'count' => [1=>0,2=>0,3=>0,4=>0,5=>0],
                ];
            }
        }

        if (empty($rating_stats)) return [];

        foreach ($results as $r) {
            $ans = json_decode($r->answers, true) ?? [];

            foreach ($rating_stats as $qid => &$stat) {
                $key = "field_$qid";

                if (isset($ans[$key]) && is_numeric($ans[$key])) {
                    $v = (int)$ans[$key];

                    if ($v >= 1 && $v <= 5) {
                        $stat['sum'] += $v;
                        $stat['n']   += 1;
                        $stat['count'][$v]++;
                    }
                }
            }
        }

        foreach ($rating_stats as &$stat) {
            $stat['avg'] = $stat['n'] > 0 ? round($stat['sum'] / $stat['n'], 2) : 0;
        }

        return $rating_stats;
    }

    /* ============================================================
     * 10. DỮ LIỆU GỌI AI (Gemini)
     * ============================================================ */
    public function prepare_ai_payload($survey, $questions, $results)
    {
        $list = [];

        foreach ($results as $r) {
            $ans = json_decode($r->answers, true) ?? [];

            $entry = [
                'student'       => $r->full_name,
                'email'         => $r->email,
                'submitted_at'  => $r->submitted_at,
                'answers'       => []
            ];

            foreach ($questions as $q) {
                $key = "field_" . $q->id;
                $entry['answers'][$q->label] = $ans[$key] ?? null;
            }

            $list[] = $entry;
        }

        return $list;
    }
    public function save_ai_comment($survey_id, $comment)
{
    return $this->db
        ->where('id', $survey_id)
        ->update($this->table_templates, [
            'ai_comment' => $comment,
            'ai_updated_at' => date('Y-m-d H:i:s')
        ]);
}
}