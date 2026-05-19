<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Internship_invoice extends ClientsController
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('internship_management/Internship_invoices_model', 'invoices_model');
        $this->load->model('internship_management/Student_client_model', 'student_model');
    }

    /**
     * PUBLIC VIEW – Xem online bằng token
     * URL: /internship_invoice/view_online/{token}
     */
    public function view_online($token = '')
    {
        if (empty($token)) {
            show_404();
        }

        // Lấy hóa đơn theo token
        $invoice = $this->invoices_model->get_by_token($token);
        if (!$invoice) {
            show_404();
        }

        // Lấy thông tin sinh viên
        $student = $this->student_model->get_student($invoice['student_id']);
        if (!$student) {
            show_404();
        }

        // Lấy dòng hàng
        $items = $this->invoices_model->get_items($invoice['id']);

        // Nếu có bảng chữ ký
        if (method_exists($this->invoices_model, 'get_signatures')) {
            $signatures = $this->invoices_model->get_signatures($invoice['id']);
        } else {
            $signatures = [];
        }

        $data = [
            'title'      => 'Hóa đơn #' . $invoice['invoice_code'],
            'invoice'    => $invoice,
            'student'    => $student,
            'items'      => $items,
            'signatures' => $signatures,
        ];

        $this->load->view('internship_management/public/invoice_public', $data);
    }
}