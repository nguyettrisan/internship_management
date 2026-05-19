<?php defined('BASEPATH') or exit('No direct script access allowed');

class StudentFinance extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('student_contracts_model');
        $this->load->model('student_invoices_model');
    }

    /** API trả hợp đồng theo student_id */
    public function contracts($student_id)
    {
        $data = $this->student_contracts_model->get_by_student($student_id);

        echo json_encode([
            'success' => true,
            'data'    => $data
        ]);
    }

    /** API trả hóa đơn theo student_id */
    public function invoices($student_id)
    {
        $data = $this->student_invoices_model->get_by_student($student_id);

        echo json_encode([
            'success' => true,
            'data'    => $data
        ]);
    }
}