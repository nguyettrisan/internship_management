<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Internship_invoice_public extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper(['url', 'html']);
        $this->load->model('internship_management/Internship_invoices_model', 'inv');
        $this->load->model('internship_management/Student_client_model', 'stu');
        $this->load->model('payment_modes_model');
        $this->load->library('app_modules');
    }

    /** View theo token */
    public function view($token = null)
    {
        $token = trim((string)$token);

        if ($token === '') show_404();

        // Nếu nhập số → xem theo ID
        if (ctype_digit($token)) {
            return $this->view_online((int)$token);
        }

        $invoice = $this->inv->get_by_token($token);
        if (!$invoice) show_404();

        return $this->_render($invoice);
    }

    /** View theo ID fallback */
    public function view_online($id = null)
    {
        $id = (int)$id;
        if ($id <= 0) show_404();

        $invoice = $this->inv->get($id);
        if (!$invoice) show_404();

        return $this->_render($invoice);
    }

    /** Render giao diện public */
    private function _render($invoice)
    {
        $invoice = $this->_to_array($invoice);

        if (empty($invoice['id'])) show_404();

        /* ---------------- STUDENT ---------------- */
        $student = [];

        if (!empty($invoice['student_id'])) {
            $st = $this->stu->get_student($invoice['student_id']);
            if ($st) {
                $student = $this->_to_array($st);
            }
        }

        if (empty($student)) { // fallback
            $student = [
                'id'            => $invoice['student_id'] ?? null,
                'full_name'     => $invoice['student_name'] ?? '',
                'email'         => $invoice['student_email'] ?? '',
                'phone_student' => $invoice['student_phone'] ?? '',
                'school_name'   => $invoice['student_school'] ?? '',
                'avatar'        => null,
            ];
        }

        /* ---------------- ITEMS ---------------- */
        $items_raw = $this->inv->get_items($invoice['id']);
        $items = array_map([$this, '_to_array'], $items_raw ?? []);

        /* ---------------- SIGNATURES ---------------- */
        $sg_raw = $this->inv->get_signatures($invoice['id']);
        $signatures = array_map([$this, '_to_array'], $sg_raw ?? []);

        /* ---------------- PAYMENT GATEWAYS CRM ----------------
           CHUẨN:
             get_payment_gateways(true)
             → chỉ lấy gateway đang ACTIVE
        ------------------------------------------------------- */
        $payment_modes = [];
        $gateways = $this->payment_modes_model->get_payment_gateways(true);

        if (!empty($gateways)) {
            foreach ($gateways as $gw) {

                if (empty($gw['id'])) continue;

                $instance = $gw['instance'] ?? null;
                if (!$instance) continue;

                $visible = false;

                if (method_exists($instance, 'show_on_client_portal') && $instance->show_on_client_portal()) {
                    $visible = true;
                }
                if (method_exists($instance, 'show_on_invoice') && $instance->show_on_invoice()) {
                    $visible = true;
                }
                if (method_exists($instance, 'supports_invoices') && $instance->supports_invoices()) {
                    $visible = true;
                }

                if (!$visible) continue;

                $slug = $gw['id'];

                $payment_modes[] = [
                    'id'          => $slug,
                    'name'        => $gw['name'] ?? $slug,
                    'description' => $gw['description'] ?? '',
                    'icon'        => base_url('uploads/payment_modes/' . $slug . '.png'),
                ];
            }
        }

        /* ---------------- LOAD VIEW ---------------- */
        return $this->load->view('internship_management/public/invoice_public', [
            'invoice'       => $invoice,
            'student'       => $student,
            'items'         => $items,
            'signatures'    => $signatures,
            'payment_modes' => $payment_modes,
        ]);
    }

    /** Convert object → array */
    private function _to_array($obj)
    {
        return is_array($obj) ? $obj : (array)$obj;
    }
}