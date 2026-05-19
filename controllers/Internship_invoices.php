<?php defined('BASEPATH') or exit('No direct script access allowed');

class Internship_invoice extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('internship_management/Internship_invoices_model', 'inv_model');
        $this->load->database();
        $this->load->helper(['url', 'html']);
    }

    /**
     * ============================================================
     * 1. VIEW PUBLIC BY TOKEN
     * URL: /internship_invoice/view/{token}
     * ============================================================
     */
    public function view($token = null)
    {
        if (!$token) show_404();

        $invoice = $this->inv_model->get_by_token($token);

        // Nếu token rỗng hoặc không tồn tại → fallback qua ID
        if (!$invoice) {
            return $this->view_online($token);
        }

        return $this->render_public_invoice($invoice);
    }

    /**
     * ============================================================
     * 2. VIEW PUBLIC BY ID (fallback)
     * URL: /internship_invoice/view_online/{id}
     * ============================================================
     */
    public function view_online($id = null)
    {
        $id = (int)$id;
        if ($id <= 0) show_404();

        $invoice = $this->inv_model->get($id);
        if (!$invoice) show_404();

        return $this->render_public_invoice($invoice);
    }


    /**
     * ============================================================
     * 3. RENDER PUBLIC VIEW (dùng chung)
     * ============================================================
     */
    private function render_public_invoice($invoice)
    {
        if (!$invoice) show_404();

        // Lấy student
        $student = $this->db->where('id', $invoice['student_id'])
                            ->get('tblinternship_students')
                            ->row_array();

        // Lấy items
        $items = $this->inv_model->get_items($invoice['id']);

        // Lấy signatures đúng bảng
        $signatures = $this->db
            ->where('invoice_id', $invoice['id'])
            ->order_by('signed_at', 'ASC')
            ->get('tblinternship_invoice_signatures')
            ->result_array();

        $data = [
            'invoice'    => $invoice,
            'student'    => $student,
            'items'      => $items,
            'signatures' => $signatures,
        ];

        $this->load->view('internship_management/public/invoice_public', $data);
    }


    /**
     * ============================================================
     * 4. PAYMENT REDIRECT
     * ============================================================
     */
    public function pay($id = null)
    {
        $id = (int)$id;
        if ($id <= 0) show_404();

        echo "<h2>Trang thanh toán đang được phát triển...</h2>";
    }
}